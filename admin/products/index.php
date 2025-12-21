<?php
// File: product/mytahfidz/check_status.php [FINAL + EXPLICIT COMMISSION TYPE]
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../libraries/doku_handler.php';

$invoice = $_GET['invoice'] ?? '';

if (empty($invoice)) {
    echo json_encode(['status' => 'error', 'message' => 'Invoice kosong']);
    exit;
}

try {
    // 1. Cek Data Order
    $stmt = $pdo->prepare("SELECT id, status, doku_request_id, affiliate_id, product_id, final_amount, invoice_number, buyer_email FROM orders WHERE invoice_number = ?");
    $stmt->execute([$invoice]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order tidak ditemukan']);
        exit;
    }

    if ($order['status'] == 'paid') {
        echo json_encode(['status' => 'success', 'payment_status' => 'PAID']);
        exit;
    }

    // 2. Tanya ke DOKU
    $doku = new DokuHandler();
    $unique_id = str_replace('INV-', '', $invoice);
    $storedRequestId = $order['doku_request_id'] ?? null;

    $response = $doku->checkStatus($unique_id, $storedRequestId);

    // 3. Jika Sukses Bayar
    if (isset($response['transaction']['status']) && $response['transaction']['status'] == 'SUCCESS') {
        
        $pdo->beginTransaction();

        try {
            // A. Update Status Order jadi PAID
            $stmtUpd = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
            $stmtUpd->execute([$order['id']]);

            // B. LOGIKA KOMISI AFFILIATE
            if (!empty($order['affiliate_id'])) {
                
                // --- CEK SELF-REFERRAL ---
                $stmtBuyer = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmtBuyer->execute([$order['buyer_email']]);
                $buyer = $stmtBuyer->fetch();

                // Jika Pembeli == Affiliate -> SKIP KOMISI
                if ($buyer && $buyer['id'] == $order['affiliate_id']) {
                    // Self referral detected, no commission.
                } else {
                    
                    // C. HITUNG KOMISI BERDASARKAN TIPE
                    // Ambil commission_type dan commission_amount
                    $stmtProd = $pdo->prepare("SELECT price, commission_type, commission_amount FROM products WHERE id = ?");
                    $stmtProd->execute([$order['product_id']]);
                    $product = $stmtProd->fetch();

                    if ($product) {
                        $commAmount = 0;
                        $rawComm = $product['commission_amount'] ?? 0;
                        $type = $product['commission_type'] ?? 'fixed'; // Default fixed jika null

                        // --- LOGIKA BARU ---
                        if ($type == 'percent') {
                            // Hitung Persentase (Contoh: 10% dari Harga)
                            $commAmount = $product['price'] * ($rawComm / 100);
                        } else {
                            // Hitung Fixed / Flat (Contoh: Rp 50.000)
                            $commAmount = $rawComm;
                        }

                        if ($commAmount > 0) {
                            // Cek Double Insert
                            $stmtCekComm = $pdo->prepare("SELECT id FROM affiliate_commissions WHERE order_id = ?");
                            $stmtCekComm->execute([$order['id']]);
                            
                            if ($stmtCekComm->rowCount() == 0) {
                                // Insert History
                                $sqlComm = "INSERT INTO affiliate_commissions (affiliate_id, order_id, commission_amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
                                $stmtInsertComm = $pdo->prepare($sqlComm);
                                $stmtInsertComm->execute([$order['affiliate_id'], $order['id'], $commAmount]);

                                // Update Saldo Affiliate
                                $sqlUpdProfile = "UPDATE affiliate_profiles SET total_sales = total_sales + 1, total_commission = total_commission + ?, available_balance = available_balance + ? WHERE user_id = ?";
                                $stmtProfile = $pdo->prepare($sqlUpdProfile);
                                $stmtProfile->execute([$commAmount, $commAmount, $order['affiliate_id']]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'payment_status' => 'PAID']);

        } catch (Exception $e) {
            $pdo->rollBack();
            // Log error commission, tapi status user tetap PAID agar tidak komplain
            echo json_encode(['status' => 'success', 'payment_status' => 'PAID', 'note' => "Paid but commission error: " . $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'pending', 'payment_status' => 'PENDING']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
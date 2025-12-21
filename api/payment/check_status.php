<?php
// File: product/mytahfidz/check_status.php [FINAL + AUTO AFFILIATE UPGRADE]
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
    $stmt = $pdo->prepare("SELECT id, status, doku_request_id, affiliate_id, product_id, final_amount, invoice_number, buyer_email, buyer_name, buyer_phone FROM orders WHERE invoice_number = ?");
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

            // ============================================================
            // B. [BARU] LOGIKA UPGRADE USER KE AFFILIATE & BUAT PROFILE
            // ============================================================
            
            // 1. Cek apakah User dengan email ini sudah ada?
            $stmtUser = $pdo->prepare("SELECT id, role, name FROM users WHERE email = ?");
            $stmtUser->execute([$order['buyer_email']]);
            $existingUser = $stmtUser->fetch();
            
            $userIdToProfile = null;

            if ($existingUser) {
                $userIdToProfile = $existingUser['id'];
                // Jika role masih 'user', UPGRADE jadi 'affiliate'
                if ($existingUser['role'] == 'user') {
                    $stmtUpgrade = $pdo->prepare("UPDATE users SET role = 'affiliate' WHERE id = ?");
                    $stmtUpgrade->execute([$userIdToProfile]);
                }
            } else {
                // Jika user belum ada (kasus tamu), Buat User Baru sebagai Affiliate
                // Password default: 123456 (Nanti bisa fitur reset password)
                $defaultPass = password_hash('123456', PASSWORD_DEFAULT);
                $stmtNewUser = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, 'affiliate', NOW())");
                $stmtNewUser->execute([$order['buyer_name'], $order['buyer_email'], $order['buyer_phone'], $defaultPass]);
                $userIdToProfile = $pdo->lastInsertId();
            }

            // 2. Cek/Buat Affiliate Profile
            if ($userIdToProfile) {
                $stmtProf = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
                $stmtProf->execute([$userIdToProfile]);
                $hasProfile = $stmtProf->fetch();

                if (!$hasProfile) {
                    // Generate Kode Referral Unik (NamaDepan + AngkaAcak)
                    $namePart = explode(' ', trim($order['buyer_name']))[0];
                    $namePart = preg_replace("/[^a-zA-Z0-9]/", "", $namePart);
                    $refCode = strtoupper(substr($namePart, 0, 5) . rand(100, 999));
                    
                    // Cek bentrok kode referral (simple check)
                    $stmtCekRef = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ?");
                    $stmtCekRef->execute([$refCode]);
                    if($stmtCekRef->rowCount() > 0) {
                        $refCode .= rand(10,99); // Tambah angka lagi kalau bentrok
                    }

                    // Insert Profile
                    $stmtCreateProf = $pdo->prepare("INSERT INTO affiliate_profiles (user_id, referral_code, available_balance, created_at) VALUES (?, ?, 0, NOW())");
                    $stmtCreateProf->execute([$userIdToProfile, $refCode]);
                }
            }
            // ============================================================

            // C. LOGIKA KOMISI AFFILIATE (Sama seperti sebelumnya)
            if (!empty($order['affiliate_id'])) {
                // Cek Self Referral
                $buyerId = $existingUser ? $existingUser['id'] : null;
                
                if ($buyerId && $buyerId == $order['affiliate_id']) {
                    // Self referral, skip komisi
                } else {
                    $stmtProd = $pdo->prepare("SELECT price, commission_type, commission_amount FROM products WHERE id = ?");
                    $stmtProd->execute([$order['product_id']]);
                    $product = $stmtProd->fetch();

                    if ($product) {
                        $commAmount = 0;
                        $rawComm = $product['commission_amount'] ?? 0;
                        $type = $product['commission_type'] ?? 'fixed';

                        if ($type == 'percent') {
                            $commAmount = $product['price'] * ($rawComm / 100);
                        } else {
                            $commAmount = $rawComm;
                        }

                        if ($commAmount > 0) {
                            $stmtCekComm = $pdo->prepare("SELECT id FROM affiliate_commissions WHERE order_id = ?");
                            $stmtCekComm->execute([$order['id']]);
                            if ($stmtCekComm->rowCount() == 0) {
                                $sqlComm = "INSERT INTO affiliate_commissions (affiliate_id, order_id, commission_amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
                                $stmtInsertComm = $pdo->prepare($sqlComm);
                                $stmtInsertComm->execute([$order['affiliate_id'], $order['id'], $commAmount]);

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
            echo json_encode(['status' => 'success', 'payment_status' => 'PAID', 'note' => "System Error: " . $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'pending', 'payment_status' => 'PENDING']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
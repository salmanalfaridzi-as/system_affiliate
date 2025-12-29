<?php
// File: api/payment/webhook.php
header('Content-Type: application/json');
require_once '../../config/database.php';

// 1. TANGKAP DATA (Support JSON Raw / POST Form)
$input = $_POST;
// Jaga-jaga jika iPaymu mengirim Raw JSON tapi header-nya tidak terdeteksi PHP
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
}

// Ambil variabel penting
$status       = $input['status'] ?? '';          // contoh: 'berhasil', 'gagal'
$trx_id       = $input['trx_id'] ?? '';          // ID Transaksi iPaymu
$invoice      = $input['reference_id'] ?? '';    // INV-xxx
$sid          = $input['sid'] ?? '';             // Session ID iPaymu (INI YANG MAU DISIMPAN)

// Debugging (Opsional)
// file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - " . json_encode($input) . PHP_EOL, FILE_APPEND);

if (empty($invoice) || empty($status)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid Data']);
    exit;
}

try {
    // 2. CARI ORDER BERDASARKAN INVOICE
    $stmt = $pdo->prepare("SELECT id, status, affiliate_id, product_id, final_amount, invoice_number, buyer_email FROM orders WHERE invoice_number = ?");
    $stmt->execute([$invoice]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Order Not Found']);
        exit;
    }

    // Jika sudah paid, return success biar iPaymu gak ngirim ulang notifikasi
    // TAPI: Kita tetap update sid-nya jaga-jaga kalau kemarin belum masuk
    if ($order['status'] == 'paid') {
        // Optional: Update SID kalau kosong
        $pdo->prepare("UPDATE orders SET ipaymu_sid = ? WHERE id = ? AND ipaymu_sid IS NULL")->execute([$sid, $order['id']]);

        echo json_encode(['status' => 'success', 'message' => 'Already Paid']);
        exit;
    }

    // 3. NORMALISASI STATUS (Ubah ke huruf kecil biar aman)
    $statusLower = strtolower($status);

    if ($statusLower == 'berhasil') {

        $pdo->beginTransaction();

        try {
            // A. UPDATE ORDER -> PAID
            // EKSEKUSI PENYIMPANAN SID ADA DI SINI (BAGIAN SUKSES)
            $stmtUpd = $pdo->prepare("UPDATE orders SET status = 'paid', trx_id = ?, ipaymu_sid = ? WHERE id = ?");
            $stmtUpd->execute([$trx_id, $sid, $order['id']]);

            // B. HITUNG KOMISI AFFILIATE
            if (!empty($order['affiliate_id'])) {

                // Cek Self-Referral (Beli pakai link sendiri)
                $stmtBuyer = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                // 1. Ambil User ID si Pembeli
                $stmtBuyer = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmtBuyer->execute([$order['buyer_email']]);
                $buyer = $stmtBuyer->fetch();

                // 2. Ambil User ID si Affiliate (Karena affiliate_id di order itu ID Profile, bukan ID User)
                $stmtAffUser = $pdo->prepare("SELECT user_id FROM affiliate_profiles WHERE id = ?");
                $stmtAffUser->execute([$order['affiliate_id']]);
                $affiliateProfile = $stmtAffUser->fetch();

                // 3. Bandingkan: Apakah User ID Pembeli == User ID Affiliate?
                if ($buyer && $affiliateProfile && $buyer['id'] == $affiliateProfile['user_id']) {
                    // INI SELF REFERRAL (Beli sendiri pakai link sendiri) -> SKIP KOMISI
                    // Tidak melakukan apa-apa
                } else {
                    // PROSES KOMISI SEPERTI BIASA
                    $stmtProd = $pdo->prepare("SELECT price, commission_amount, commission_type FROM products WHERE id = ?");
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
                            $stmtCek = $pdo->prepare("SELECT id FROM affiliate_commissions WHERE order_id = ?");
                            $stmtCek->execute([$order['id']]);

                            if ($stmtCek->rowCount() == 0) {
                                $pdo->prepare("INSERT INTO affiliate_commissions (affiliate_id, order_id, commission_amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())")
                                    ->execute([$order['affiliate_id'], $order['id'], $commAmount]);

                                $pdo->prepare("UPDATE affiliate_profiles SET total_sales = total_sales + 1, total_commission = total_commission + ?, available_balance = available_balance + ? WHERE user_id = ?")
                                    ->execute([$commAmount, $commAmount, $affiliateProfile['user_id']]); // Pakai user_id dari affiliate
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Payment processed']);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($statusLower == 'gagal') {

        // EKSEKUSI PENYIMPANAN SID ADA DI SINI JUGA (BAGIAN GAGAL)
        // Kita update status jadi 'failed', tapi tetap simpan trx_id dan sid biar ada jejaknya
        $stmtFail = $pdo->prepare("UPDATE orders SET status = 'failed', trx_id = ?, ipaymu_sid = ? WHERE id = ?");
        $stmtFail->execute([$trx_id, $sid, $order['id']]);

        echo json_encode(['status' => 'failed']);
    } elseif ($statusLower == 'expired') {

        // EKSEKUSI PENYIMPANAN SID ADA DI SINI JUGA (BAGIAN GAGAL)
        // Kita update status jadi 'failed', tapi tetap simpan trx_id dan sid biar ada jejaknya
        $stmtFail = $pdo->prepare("UPDATE orders SET status = 'expired', trx_id = ?, ipaymu_sid = ? WHERE id = ?");
        $stmtFail->execute([$trx_id, $sid, $order['id']]);

        echo json_encode(['status' => 'failed']);
    } else {
        // Status Pending (Menunggu Bayar)
        // Opsional: Update SID juga pas pending kalau mau
        $pdo->prepare("UPDATE orders SET trx_id = ?, ipaymu_sid = ? WHERE id = ?")->execute([$trx_id, $sid, $order['id']]);

        echo json_encode(['status' => 'pending']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

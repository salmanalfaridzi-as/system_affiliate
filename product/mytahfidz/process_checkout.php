<?php
// File: product/mytahfidz/process_checkout.php
session_start();
require_once '../../config/database.php';
require_once '../../libraries/doku_handler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil Data Input
    $buyer_email = filter_var($_POST['buyer_email'], FILTER_SANITIZE_EMAIL);
    $buyer_name  = htmlspecialchars($_POST['buyer_name']);
    $buyer_phone = htmlspecialchars($_POST['buyer_phone']);
    $password    = $_POST['password'] ?? '';
    $product_id  = $_POST['product_id'];
    $coupon_code = $_POST['coupon_code'] ?? '';

    // Ambil Data Tracking Cookie
    $affiliate_id = $_COOKIE['aff_ref'] ?? NULL;
    $source       = $_COOKIE['source'] ?? 'direct'; 
    $id_source    = $_COOKIE['id_source'] ?? '';   

    try {
        $pdo->beginTransaction();

        // ==========================================================
        // 2. LOGIKA USER (AUTO AFFILIATE)
        // ==========================================================
        if (isset($_SESSION['user_id'])) {
            // KASUS A: USER SUDAH LOGIN
            $user_id = $_SESSION['user_id'];
            
            // Opsional: Kalau user lama masih role 'user', mau diupgrade otomatis gak saat checkout?
            // Kalau mau, uncomment baris ini:
            // $pdo->prepare("UPDATE users SET role = 'affiliate' WHERE id = ?")->execute([$user_id]);

        } else {
            // KASUS B: USER BARU (GUEST)
            // Cek Email
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmtCheck->execute([$buyer_email]);
            $existingUser = $stmtCheck->fetch();

            if ($existingUser) {
                // Email sudah terdaftar -> Tolak & Suruh Login
                $pdo->rollBack();
                header("Location: checkout.php?product_id=$product_id&error=Email sudah terdaftar! Silakan Login Member dulu.");
                exit;
            } else {
                // Email Belum Ada -> Buat Akun Baru
                $finalPass = !empty($password) ? $password : bin2hex(random_bytes(4));
                $hashPass  = password_hash($finalPass, PASSWORD_DEFAULT);
                
                // --- PERUBAHAN DI SINI ---
                // Role langsung diset 'affiliate' (bukan 'user' lagi)
                $sqlUser   = "INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'affiliate', 'active')";
                $stmtUser  = $pdo->prepare($sqlUser);
                $stmtUser->execute([$buyer_name, $buyer_email, $hashPass, $buyer_phone]);
                
                $user_id = $pdo->lastInsertId();

                // --- BUAT PROFILE AFFILIATE OTOMATIS ---
                // Generate Referral Code Unik
                $namePart = explode(' ', trim($buyer_name))[0];
                $namePart = preg_replace("/[^a-zA-Z0-9]/", "", $namePart);
                $refCode = strtoupper(substr($namePart, 0, 5) . rand(100, 999));

                // Cek duplikat code
                $stmtCekRef = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ?");
                $stmtCekRef->execute([$refCode]);
                if ($stmtCekRef->rowCount() > 0) {
                    $refCode .= rand(1,9);
                }

                // Insert Profile
                $stmtInsProf = $pdo->prepare("INSERT INTO affiliate_profiles (user_id, referral_code, available_balance, created_at) VALUES (?, ?, 0, NOW())");
                $stmtInsProf->execute([$user_id, $refCode]);

                // Auto Login
                $_SESSION['user_id']    = $user_id;
                $_SESSION['user_name']  = $buyer_name;
                $_SESSION['user_email'] = $buyer_email;
                $_SESSION['user_role']  = 'affiliate'; // Update session role juga
            }
        }

        // ==========================================================
        // 3. HITUNG HARGA & KUPON
        // ==========================================================
        $stmtP = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmtP->execute([$product_id]);
        $prod = $stmtP->fetch();

        if (!$prod) throw new Exception("Produk tidak ditemukan");

        $final_amount = $prod['price'];
        $discount     = 0;
        $coupon_id    = NULL;

        if ($coupon_code) {
            $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date >= CURDATE())");
            $stmtC->execute([$coupon_code]);
            $coup = $stmtC->fetch();

            if ($coup) {
                $coupon_id = $coup['id'];
                if ($coup['discount_type'] == 'percent') {
                    $discount = $final_amount * ($coup['discount_amount'] / 100);
                } else {
                    $discount = $coup['discount_amount'];
                }
                $final_amount -= $discount;
                if ($final_amount < 0) $final_amount = 0;

                if (!empty($coup['affiliate_id'])) {
                    $affiliate_id = $coup['affiliate_id'];
                }
            }
        }

        // ==========================================================
        // 4. CREATE ORDER
        // ==========================================================
        $order_unique_id = date('ymd') . rand(1000, 9999);
        $invoice         = 'INV-' . $order_unique_id;
        $requestId       = 'REQ-' . date('YmdHis') . '-' . uniqid();
        
        // Default values
        $shipping_address = ''; 
        $courier          = '';
        $resi_number      = '';
        $qty              = 1;

        $sqlOrder = "INSERT INTO orders (
            invoice_number, doku_request_id, affiliate_id, product_id, 
            buyer_name, buyer_phone, buyer_email, 
            total_amount, discount_amount, final_amount, coupon_id, 
            status, created_at,
            shipping_address, courier, resi_number, qty, source, id_source
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?, ?, ?, ?, ?, ?)";

        $pdo->prepare($sqlOrder)->execute([
            $invoice, $requestId, $affiliate_id, $product_id, 
            $buyer_name, $buyer_phone, $buyer_email, 
            $prod['price'], $discount, $final_amount, $coupon_id,
            $shipping_address, $courier, $resi_number, $qty, $source, $id_source
        ]);

        $pdo->commit();

        // ==========================================================
        // 5. PAYMENT GATEWAY (DOKU)
        // ==========================================================
        $doku = new DokuHandler();
        
        // GANTI URL INI SAAT LIVE
        $baseUrl = "http://localhost/my_tahfidz_affiliator_sejoli"; 
        
        $customUrls = [
            'callback_url' => $baseUrl . '/payment/notification.php',
            'success_url'  => $baseUrl . '/product/mytahfidz/thankyou.php?inv=' . $invoice,
            'failed_url'   => $baseUrl . '/product/mytahfidz/thankyou.php?inv=' . $invoice . '&status=failed'
        ];

        $response = $doku->createPayment($order_unique_id, $final_amount, $buyer_email, $buyer_name, $customUrls, $requestId);

        if (isset($response['response']['payment']['url'])) {
            header("Location: " . $response['response']['payment']['url']);
        } else {
            header("Location: thankyou.php?inv=$invoice&err=doku_failed");
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Error Process: " . $e->getMessage());
    }
} else {
    header("Location: checkout.php");
    exit;
}
?>
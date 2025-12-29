<?php
// File: api/payment/create.php
header('Content-Type: application/json');
session_start();
require_once '../../config/database.php';
require_once '../../libraries/ipaymu_handler.php';

// Validasi Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Method']);
    exit;
}

// 1. Ambil Data (Support JSON raw body atau POST form data)
$input = $_POST;
if (empty($input)) {
    $input = json_decode(file_get_contents('php://input'), true);
}

$buyer_email = filter_var($input['buyer_email'] ?? '', FILTER_SANITIZE_EMAIL);
$buyer_name  = htmlspecialchars($input['buyer_name'] ?? '');
$buyer_phone = htmlspecialchars($input['buyer_phone'] ?? '');
$password    = $input['password'] ?? '';
$product_id  = $input['product_id'] ?? 0;
$coupon_code = $input['coupon_code'] ?? '';

// --- PERBAIKAN BUG AFFILIATE ID DI SINI ---
// Masalah: Frontend kirim string kosong "", database nolak.
// Solusi: Ubah jadi NULL jika kosong.
$affiliate_id = !empty($input['aff_ref']) ? $input['aff_ref'] : NULL;

// Validasi Tambahan: Cek apakah Affiliate ID ini BENAR-BENAR ADA di DB?
// (Mencegah error foreign key jika cookie menyimpan ID lama yang user-nya sudah dihapus)
if ($affiliate_id) {
    $stmtCheckAff = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE id = ?");
    $stmtCheckAff->execute([$affiliate_id]);
    if ($stmtCheckAff->rowCount() == 0) {
        $affiliate_id = NULL; // Kalau tidak ketemu, anggap Direct (Tanpa Affiliate)
    }
}

$source       = $input['source'] ?? 'direct'; 
$id_source    = $input['id_source'] ?? '';   

// Domain Asal (Untuk redirect balik setelah bayar)
$return_url_base = $input['return_url_base'] ?? 'http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/thankyou.php';

try {
    $pdo->beginTransaction();

    // ==========================================================
    // 2. LOGIKA USER (AUTO REGISTER)
    // ==========================================================
    $user_id = null;
    $is_new_user = false;

    // Cek User by Email
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->execute([$buyer_email]);
    $existingUser = $stmtCheck->fetch();

    if ($existingUser) {
        $user_id = $existingUser['id'];
    } else {
        // User Baru -> Auto Register
        $finalPass = !empty($password) ? $password : bin2hex(random_bytes(4));
        $hashPass  = password_hash($finalPass, PASSWORD_DEFAULT);
        
        $sqlUser   = "INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'affiliate', 'active')";
        $stmtUser  = $pdo->prepare($sqlUser);
        $stmtUser->execute([$buyer_name, $buyer_email, $hashPass, $buyer_phone]);
        $user_id = $pdo->lastInsertId();
        $is_new_user = true;

        // Create Profile Affiliate
        $namePart = preg_replace("/[^a-zA-Z0-9]/", "", explode(' ', trim($buyer_name))[0]);
        $refCode = strtoupper(substr($namePart, 0, 5) . rand(100, 999));
        
        // Cek duplikat code
        $stmtCekRef = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ?");
        $stmtCekRef->execute([$refCode]);
        if ($stmtCekRef->rowCount() > 0) $refCode .= rand(1,9);

        $pdo->prepare("INSERT INTO affiliate_profiles (user_id, referral_code, available_balance, created_at) VALUES (?, ?, 0, NOW())")->execute([$user_id, $refCode]);
    }

    // ==========================================================
    // 3. HITUNG HARGA
    // ==========================================================
    $stmtP = $pdo->prepare("SELECT price, name FROM products WHERE id = ?");
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
            $discount = ($coup['discount_type'] == 'percent') ? $final_amount * ($coup['discount_amount'] / 100) : $coup['discount_amount'];
            $final_amount -= $discount;
            if ($final_amount < 0) $final_amount = 0;
            
            // Override affiliate jika kupon punya pemilik khusus
            if (!empty($coup['affiliate_id'])) {
                // Validasi lagi ID affiliate dari kupon biar gak error foreign key
                $stmtCheckCoupAff = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE id = ?");
                $stmtCheckCoupAff->execute([$coup['affiliate_id']]);
                if ($stmtCheckCoupAff->rowCount() > 0) {
                    $affiliate_id = $coup['affiliate_id'];
                }
            }
        }
    }

    // ==========================================================
    // 4. CREATE ORDER
    // ==========================================================
    $order_unique_id = date('ymd') . rand(1000, 9999);
    $invoice         = 'INV-' . $order_unique_id;
    
    // Insert ke DB (Pastikan urutan kolom sesuai)
    $sqlOrder = "INSERT INTO orders (
        invoice_number, trx_id, affiliate_id, product_id, 
        buyer_name, buyer_phone, buyer_email, 
        total_amount, discount_amount, final_amount, coupon_id, 
        status, created_at,
        shipping_address, courier, resi_number, qty, source, id_source
    ) VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), '', '', '', 1, ?, ?)";

    // Eksekusi (affiliate_id sekarang dijamin NULL atau Valid ID)
    $pdo->prepare($sqlOrder)->execute([
        $invoice, $affiliate_id, $product_id, 
        $buyer_name, $buyer_phone, $buyer_email, 
        $prod['price'], $discount, $final_amount, $coupon_id,
        $source, $id_source
    ]);
    
    $pdo->commit();

    // ==========================================================
    // 5. CALL IPAYMU API
    // ==========================================================
    $ipaymu = new IpaymuHandler();
    
    // GANTI domain ini dengan domain backend API Anda
    $backendUrl = "https://34f8835048d6.ngrok-free.app/my_tahfidz_affiliator_sejoli"; 
    
    $successUrl = $return_url_base . '?inv=' . $invoice;
    $failedUrl  = $return_url_base . '?inv=' . $invoice . '&status=failed';
    $notifyUrl  = $backendUrl . '/api/payment/webhook.php'; 

    $response = $ipaymu->createPayment($order_unique_id, $final_amount, $buyer_name, $buyer_email, $buyer_phone, [
        'success_url'  => $successUrl,
        'failed_url'   => $failedUrl,
        'callback_url' => $notifyUrl
    ]);

    if (isset($response['Success']) && $response['Success'] && isset($response['Data']['Url'])) {
        echo json_encode([
            'status' => 'success',
            'payment_url' => $response['Data']['Url'],
            'invoice' => $invoice,
            'user_data' => [ 
                'id' => $user_id,
                'email' => $buyer_email,
                'name' => $buyer_name,
                'is_new' => $is_new_user
            ]
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => $response ?? 'Failed to create payment'
        ]);
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
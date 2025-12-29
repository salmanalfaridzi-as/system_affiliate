<?php 
// File: product/mytahfidz/checkout.php 
session_start(); 
require_once '../../config/database.php'; 

// 1. AMBIL ID PRODUK
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 1; 

// ==============================================================================
// 2. LOGIC TRACKING CLICK (COOKIE BASED - REVISI)
// ==============================================================================
if (isset($_GET['ref']) && !empty($_GET['ref'])) { 
    $ref_code   = filter_var($_GET['ref'], FILTER_SANITIZE_SPECIAL_CHARS);
    $source     = isset($_GET['src']) ? filter_var($_GET['src'], FILTER_SANITIZE_SPECIAL_CHARS) : 'direct'; 
    $cpid       = isset($_GET['cpid']) ? filter_var($_GET['cpid'], FILTER_SANITIZE_SPECIAL_CHARS) : ''; 
    $coupon     = isset($_GET['coupon']) ? filter_var($_GET['coupon'], FILTER_SANITIZE_SPECIAL_CHARS) : null; 
    
    // Config Cookie
    $cookie_time = time() + (86400 * 30); // 30 Hari 
    $cookie_path = "/"; 

    // A. Cari Affiliate ID (Support Kode atau ID)
    $stmtCheck = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ? OR user_id = ?"); 
    $stmtCheck->execute([$ref_code, $ref_code]); 
    $affiliate = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($affiliate) {
        $affiliate_id = $affiliate['id'];

        // B. Simpan Cookie Attribution (Buat Sales)
        setcookie('aff_ref', $affiliate_id, $cookie_time, $cookie_path); 
        setcookie('source', $source, $cookie_time, $cookie_path); 
        setcookie('id_source', $cpid, $cookie_time, $cookie_path); 
        if ($coupon) setcookie('aff_coupon', $coupon, $cookie_time, $cookie_path); 

        // C. Logic Counter "Unik" (Cookie Based, No IP Check)
        $trackingCookie = 'clicked_' . $affiliate_id . '_' . date('Ymd');

        // Cek Cookie Harian
        if (!isset($_COOKIE[$trackingCookie])) { 
            try { 
                $pdo->beginTransaction();

                // 1. Insert Log
                $ip = $_SERVER['REMOTE_ADDR'];
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-'; 
                $stmtClick = $pdo->prepare("INSERT INTO affiliate_clicks (affiliate_id, product_id, source, campaign_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())"); 
                $stmtClick->execute([$affiliate_id, $product_id, $source, $cpid, $ip, $ua]); 

                // 2. Update Global Counter (Profiles)
                $stmtProf = $pdo->prepare("UPDATE affiliate_profiles SET total_clicks = total_clicks + 1 WHERE id = ?");
                $stmtProf->execute([$affiliate_id]);

                // 3. Update Link Counter
                $stmtLink = $pdo->prepare("UPDATE affiliate_links SET clicks = clicks + 1 WHERE affiliate_id = ? AND product_id = ? AND (campaign_id = ? OR campaign_id IS NULL)");
                $stmtLink->execute([$affiliate_id, $product_id, $cpid]);

                $pdo->commit();

                // Set Cookie Tanda (24 Jam)
                setcookie($trackingCookie, '1', time() + 86400, "/");

            } catch (Exception $e) { 
                $pdo->rollBack();
            } 
        } 
    }
} 

// ==============================================================================
// 3. FUNGSI BANTUAN & DATA FORM
// ==============================================================================

function get_affiliate_param($get_keys, $cookie_key) { 
    if (!is_array($get_keys)) $get_keys = [$get_keys]; 
    foreach ($get_keys as $key) { 
        if (isset($_GET[$key]) && !empty($_GET[$key])) return filter_var($_GET[$key], FILTER_SANITIZE_SPECIAL_CHARS); 
    } 
    if (isset($_COOKIE[$cookie_key])) return filter_var($_COOKIE[$cookie_key], FILTER_SANITIZE_SPECIAL_CHARS); 
    return ''; 
} 

$final_ref_id = get_affiliate_param('ref', 'aff_ref'); 
$final_source = get_affiliate_param('src', 'source'); 
if(!$final_source) $final_source = 'direct'; 
$final_campaign_id = get_affiliate_param(['cpid', 'campaign_id', 'id_source'], 'id_source'); 
$final_coupon = get_affiliate_param('coupon', 'aff_coupon'); 

// 4. AMBIL DATA PRODUK
$stmtP = $pdo->prepare("SELECT * FROM products WHERE id = ?"); 
$stmtP->execute([$product_id]); 
$product = $stmtP->fetch(); 

if (!$product) die("Produk tidak ditemukan."); 

// 5. LOGIKA KUPON
$coupon_code_display = isset($_GET['coupon']) ? strtoupper(trim($_GET['coupon'])) : strtoupper($final_coupon); 
$discount = 0; 
$final_price = $product['price']; 
$msg_coupon = ""; 

if (!empty($coupon_code_display)) { 
    $stmtC = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND (end_date IS NULL OR end_date >= CURDATE())"); 
    $stmtC->execute([$coupon_code_display]); 
    $coup = $stmtC->fetch(); 

    if ($coup) { 
        if ($coup['discount_type'] == 'percent') { 
            $discount = $product['price'] * ($coup['discount_amount'] / 100); 
        } else { 
            $discount = $coup['discount_amount']; 
        } 
        $final_price = $product['price'] - $discount; 
        $msg_coupon = "<div class='alert alert-success small py-2 mb-3'><i class='bi bi-tag-fill'></i> Kupon <b>$coupon_code_display</b> diterapkan!</div>"; 
        setcookie('aff_coupon', $coupon_code_display, time() + (86400 * 30), "/"); 
    } else { 
        $msg_coupon = "<div class='alert alert-danger small py-2 mb-3'><i class='bi bi-x-circle'></i> Kupon tidak valid.</div>"; 
    } 
} 

// 6. CEK USER LOGIN
$user = null; 
if (isset($_SESSION['user_id'])) { 
    $stmtU = $pdo->prepare("SELECT * FROM users WHERE id = ?"); 
    $stmtU->execute([$_SESSION['user_id']]); 
    $user = $stmtU->fetch(); 
} 
?> 

<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Checkout - <?= htmlspecialchars($product['name']) ?></title> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
    <style> body { background-color: #f8f9fa; } .price-strike { text-decoration: line-through; color: #adb5bd; font-size: 0.9em; } </style> 
</head> 
<body> 

<div class="container py-5"> 
    <div class="row justify-content-center"> 
        <div class="col-lg-7 mb-4"> 
            <div class="card shadow-sm border-0"> 
                <div class="card-header bg-white py-3"> 
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i>Data Pemesan</h5> 
                </div> 
                <div class="card-body p-4"> 
                    <?php if (isset($_GET['error'])): ?> 
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"> 
                            <?= htmlspecialchars($_GET['error']) ?> 
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button> 
                        </div> 
                    <?php endif; ?> 

                    <form action="process_checkout.php" method="POST"> 
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>"> 
                        <input type="hidden" name="ref_id" value="<?= htmlspecialchars($final_ref_id) ?>"> 
                        <input type="hidden" name="source" value="<?= htmlspecialchars($final_source) ?>"> 
                        <input type="hidden" name="campaign_id" value="<?= htmlspecialchars($final_campaign_id) ?>"> 

                        <?php if ($user): ?> 
                            <div class="alert alert-info border-0 d-flex align-items-center mb-4"> 
                                <i class="bi bi-check-circle-fill fs-4 me-3 text-info"></i> 
                                <div> Masuk sebagai <b><?= htmlspecialchars($user['name']) ?></b>.</div> 
                            </div> 
                        <?php endif; ?> 

                        <div class="mb-3"> 
                            <label class="form-label fw-bold">Nama Lengkap</label> 
                            <input type="text" name="buyer_name" class="form-control" value="<?= $user['name'] ?? '' ?>" required <?= $user ? 'readonly' : '' ?>> 
                        </div> 
                        <div class="row"> 
                            <div class="col-md-6 mb-3"> 
                                <label class="form-label fw-bold">Email</label> 
                                <input type="email" name="buyer_email" class="form-control" value="<?= $user['email'] ?? '' ?>" required <?= $user ? 'readonly' : '' ?>> 
                            </div> 
                            <div class="col-md-6 mb-3"> 
                                <label class="form-label fw-bold">No WhatsApp</label> 
                                <input type="number" name="buyer_phone" class="form-control" value="<?= $user['phone'] ?? '' ?>" required <?= $user ? 'readonly' : '' ?>> 
                            </div> 
                        </div> 

                        <?php if (!$user): ?> 
                            <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center">
                                <span class="small">Sudah punya akun?</span>
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                            </div>
                            <div class="p-3 bg-light rounded border mb-3"> 
                                <div class="mb-2 fw-bold text-primary"><i class="bi bi-shield-lock"></i> Buat Password Akun</div> 
                                <input type="password" name="password" class="form-control" placeholder="Password baru" required> 
                            </div> 
                        <?php endif; ?> 
                        
                        <hr class="my-4"> 

                        <div class="mb-4"> 
                            <label class="form-label fw-bold">Kode Kupon</label> 
                            <div class="input-group"> 
                                <input type="text" name="coupon_code" id="input-coupon" class="form-control" placeholder="Punya kode promo?" value="<?= htmlspecialchars($coupon_code_display) ?>"> 
                                <button class="btn btn-outline-secondary" type="button" onclick="applyCoupon()">Gunakan</button> 
                            </div> 
                        </div> 
<div class="form-check mb-3 small text-muted">
    <input class="form-check-input" type="checkbox" id="agreeTerms" required checked>
    <label class="form-check-label" for="agreeTerms">
        Saya menyetujui 
        <a href="../../info/terms.php" target="_blank" class="text-decoration-none fw-bold">Syarat & Ketentuan</a> 
        dan 
        <a href="../../info/refund.php" target="_blank" class="text-decoration-none fw-bold">Kebijakan Refund</a>.
        <br>
        <span class="text-secondary" style="font-size: 0.9em;">
            Butuh bantuan? Baca <a href="../../info/faq.php" target="_blank" class="text-decoration-underline">FAQ</a> kami.
        </span>
    </label>
</div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold"> Lanjut Pembayaran <i class="bi bi-arrow-right-circle ms-1"></i> </button> 
                    </form> 
                </div> 
            </div> 
        </div> 

        <div class="col-lg-4"> 
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;"> 
                <div class="card-header bg-dark text-white py-3"> 
                    <h5 class="mb-0 fw-bold">Ringkasan</h5> 
                </div> 
                <div class="card-body bg-light"> 
                    <div class="d-flex align-items-center mb-3"> 
                        <div class="bg-white p-2 rounded border me-3 text-center" style="width: 60px;"> 
                            <i class="bi bi-box-seam fs-3 text-secondary"></i> 
                        </div> 
                        <div> 
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($product['name']) ?></h6> 
                        </div> 
                    </div> 
                    <hr> 
                    <?= $msg_coupon ?> 
                    <div class="d-flex justify-content-between mb-2"> 
                        <span>Harga</span> <span class="fw-bold">Rp <?= number_format($product['price'], 0, ',', '.') ?></span> 
                    </div> 
                    <?php if ($discount > 0): ?> 
                        <div class="d-flex justify-content-between mb-2 text-success"> 
                            <span>Diskon</span> <span>- Rp <?= number_format($discount, 0, ',', '.') ?></span> 
                        </div> 
                    <?php endif; ?> 
                    <hr> 
                    <div class="d-flex justify-content-between align-items-center"> 
                        <span class="fw-bold">Total</span> 
                        <span class="h4 fw-bold text-primary">Rp <?= number_format($final_price, 0, ',', '.') ?></span> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div> 

<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true"> 
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content"> 
            <div class="modal-header border-0 pb-0"> 
                <h5 class="modal-title fw-bold">Login Member</h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button> 
            </div> 
            <div class="modal-body p-4 pt-3"> 
                <form action="../../auth/process_login.php" method="POST"> 
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"> 
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div> 
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div> 
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Masuk</button> 
                </form> 
            </div> 
        </div> 
    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> 
<script> 
    function applyCoupon() { 
        const code = document.getElementById('input-coupon').value; 
        if(code) { 
            const url = new URL(window.location.href); 
            url.searchParams.set('coupon', code); 
            window.location.href = url.toString(); 
        } 
    } 
</script> 
</body> 
</html>
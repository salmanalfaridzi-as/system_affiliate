<?php
// File: product/mytahfidz/index.php
session_start();
require_once '../../config/database.php';

// 1. TENTUKAN ID PRODUK
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 1;

// ==============================================================================
// 2. LOGIC TRACKING CLICK (COOKIE BASED - TANPA CEK IP)
// ==============================================================================
if (isset($_GET['ref'])) {
    $ref_code   = $_GET['ref'];
    $source     = $_GET['src'] ?? 'direct';
    $campaign   = $_GET['cpid'] ?? '';
    
    // A. Cari Affiliate ID (Bisa pakai KODE REFERRAL atau USER ID)
    $stmtCheck = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ? OR user_id = ?");
    $stmtCheck->execute([$ref_code, $ref_code]);
    $affiliate = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($affiliate) {
        $affiliate_id = $affiliate['id'];

        // B. Simpan Cookie "Attribution" (PENTING BUAT SALES NANTI) - Berlaku 30 Hari
        setcookie('aff_ref', $affiliate_id, time() + (86400 * 30), "/");
        setcookie('source', $source, time() + (86400 * 30), "/");
        setcookie('id_source', $campaign, time() + (86400 * 30), "/");

        // C. Logic Counter "Unik" (PENGGANTI CEK IP)
        // Cookie "Tanda" ini berlaku 24 jam. Jadi kalau user refresh dalam 24 jam, klik tidak nambah.
        // Nama cookie unik per affiliate + per hari.
        $trackingCookie = 'clicked_' . $affiliate_id . '_' . date('Ymd');

        // Cek: Apakah browser user sudah punya cookie "tanda" hari ini?
        if (!isset($_COOKIE[$trackingCookie])) { 
            // -- JIKA BELUM ADA, BERARTI KLIK BARU (ATAU SUDAH LEWAT 24 JAM) --
            try {
                $pdo->beginTransaction();
                
                // 1. Catat Log Detail (affiliate_clicks)
                // Kita tetap simpan IP buat data admin, tapi tidak dipakai buat validasi spam lagi.
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $sqlLog = "INSERT INTO affiliate_clicks (affiliate_id, product_id, source, campaign_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $pdo->prepare($sqlLog)->execute([$affiliate_id, $product_id, $source, $campaign, $ip_address, $ua]);

                // 2. Tambah Counter Global (affiliate_profiles -> total_clicks)
                // INI YANG BIKIN ANGKA DI DASHBOARD JALAN
                $pdo->prepare("UPDATE affiliate_profiles SET total_clicks = total_clicks + 1 WHERE id = ?")->execute([$affiliate_id]);

                // 3. Tambah Counter Link Spesifik (affiliate_links -> clicks)
                $sqlLink = "UPDATE affiliate_links SET clicks = clicks + 1 WHERE affiliate_id = ? AND product_id = ? AND (campaign_id = ? OR campaign_id IS NULL)";
                $pdo->prepare($sqlLink)->execute([$affiliate_id, $product_id, $campaign]);

                $pdo->commit();

                // D. SET COOKIE "TANDA" (Berlaku 24 Jam)
                // Supaya kalau di-refresh, blok kode database di atas tidak dijalankan lagi.
                setcookie($trackingCookie, '1', time() + 86400, "/");

            } catch (Exception $e) {
                $pdo->rollBack();
                // Silent error (lanjut aja)
            }
        }
    }
}

// ==============================================================================
// 3. AMBIL DATA PRODUK
// ==============================================================================
$stmtP = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmtP->execute([$product_id]);
$product = $stmtP->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produk dengan ID $product_id tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name'] ?? 'Landing Page') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container py-5 text-center">
        <h1 class="fw-bold mb-4"><?= htmlspecialchars($product['name'] ?? 'Nama Produk') ?></h1>
        
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-body p-5">
                <img src="https://placehold.co/600x400?text=Product+Image" class="img-fluid rounded mb-4" alt="Product">
                <h3 class="fw-bold text-primary">Rp <?= number_format($product['price'], 0, ',', '.') ?></h3>
                <p class="lead mt-3">Deskripsi produk yang menarik...</p>
                <hr>
                
                <?php
                    // Oper parameter URL ke tombol beli
                    $params = $_GET;
                    $params['product_id'] = $product_id;
                ?>
                <a href="checkout.php?<?= http_build_query($params) ?>" class="btn btn-primary btn-lg w-100 fw-bold">
                    Beli Sekarang
                </a>
            </div>
        </div>
        
        <div class="mt-4 text-muted small">
            <?php if(isset($affiliate_id)): ?>
                <span class="badge bg-success">Affiliate ID: <?= $affiliate_id ?> Terlacak</span>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
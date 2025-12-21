<?php
// File: affiliate/link.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'affiliate_link';
$user_id = $_SESSION['user_id'];

// 1. Ambil Affiliate ID & Referral Code
// UPDATE: Kita ambil 'referral_code' juga
$stmt = $pdo->prepare("SELECT id, referral_code FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$affProfile = $stmt->fetch();

$affiliate_id = $affProfile['id'] ?? 0;
// Prioritas: Pakai Referral Code (String). Kalau kosong, pakai ID (Angka).
$ref_param = !empty($affProfile['referral_code']) ? $affProfile['referral_code'] : $affiliate_id;

// 2. Ambil Daftar Produk
$stmtProd = $pdo->query("SELECT id, name, price, landing_page_url FROM products WHERE status = 'active' ORDER BY name ASC");
$products = $stmtProd->fetchAll();

// 3. Ambil Daftar Kupon
$coupons = []; 
try {
    $stmtCoup = $pdo->query("SELECT id, code, discount_amount, discount_type FROM coupons WHERE status = 'active'");
    $coupons = $stmtCoup->fetchAll();
} catch (Exception $e) {
    // Tabel coupons mungkin belum dibuat
}

// 4. Proses Simpan Link ke DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $product_id  = $_POST['product_id'];
    $campaign    = $_POST['campaign']; // Sumber (Source)
    $campaign_id = filter_input(INPUT_POST, 'campaign_id', FILTER_SANITIZE_SPECIAL_CHARS); // ID Campaign
    $coupon_code = $_POST['coupon_code'] ?? '';
    
    $stmtUrl = $pdo->prepare("SELECT landing_page_url FROM products WHERE id = ?");
    $stmtUrl->execute([$product_id]);
    $prod = $stmtUrl->fetch();
    
    if ($prod) {
        $base_url = $prod['landing_page_url'];
        $sep = (strpos($base_url, '?') !== false) ? '&' : '?';
        
        // Susun Link Akhir
        // UPDATE: 'ref' sekarang menggunakan $ref_param (Kode Referral atau ID)
        $final_link = $base_url . $sep . "ref=" . $ref_param;
        
        // Tambahkan Product ID agar checkout otomatis tahu produknya
        $final_link .= "&product_id=" . $product_id;

        if (!empty($campaign)) { $final_link .= "&src=" . urlencode($campaign); }
        if (!empty($campaign_id)) { $final_link .= "&cpid=" . urlencode($campaign_id); } 
        if (!empty($coupon_code)) { $final_link .= "&coupon=" . urlencode($coupon_code); }

        // Simpan ke database
        // Kita tetap simpan affiliate_id (angka) di kolom affiliate_id untuk relasi database
        // Tapi di kolom target_url, linknya sudah pakai kode referral
        $stmtInsert = $pdo->prepare("
            INSERT INTO affiliate_links (affiliate_id, product_id, target_url, campaign, campaign_id, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$affiliate_id, $product_id, $final_link, $campaign, $campaign_id]);
        
        $success_msg = "Link berhasil disimpan ke riwayat!";
    }
}

// 5. Ambil History (Limit 10 Terakhir)
$stmtLinks = $pdo->prepare("
    SELECT al.*, p.name as product_name 
    FROM affiliate_links al 
    LEFT JOIN products p ON al.product_id = p.id 
    WHERE al.affiliate_id = ? 
    ORDER BY al.created_at DESC LIMIT 10
");
$stmtLinks->execute([$affiliate_id]);
$historyLinks = $stmtLinks->fetchAll();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Generate Link Affiliate</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-5">
                    <div class="card card-primary card-outline shadow-sm mb-4">
                        <form method="POST">
                            <div class="card-body">
                                <?php if(isset($success_msg)): ?>
                                    <div class="alert alert-success small"><?= $success_msg ?></div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Pilih Produk</label>
                                    <select name="product_id" id="productSelect" class="form-select" required>
                                        <option value="" selected disabled>-- Pilih Produk --</option>
                                        <?php foreach($products as $p): ?>
                                            <option value="<?= $p['id'] ?>" data-url="<?= $p['landing_page_url'] ?>">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Sumber (Source)</label>
                                        <select name="campaign" id="campaignSelect" class="form-select">
                                            <option value="">-- Tanpa Sumber --</option>
                                            <option value="facebook">Facebook</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="instagram_story">Instagram Story</option>
                                            <option value="tiktok">TikTok</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="youtube">YouTube</option>
                                            <option value="email">Email</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Campaign ID</label>
                                        <input type="text" name="campaign_id" id="campaignIdInput" class="form-control" placeholder="Contoh: LEBARAN01">
                                        <div class="form-text small">Opsional: ID unik iklan/post.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Gunakan Kupon (Opsional)</label>
                                    <select name="coupon_code" id="couponSelect" class="form-select">
                                        <option value="">-- Tanpa Kupon --</option>
                                        <?php foreach($coupons as $c): ?>
                                            <?php 
                                                if($c['discount_type'] == 'percent') {
                                                    $label = "Diskon " . floatval($c['discount_amount']) . "%";
                                                } else {
                                                    $label = "Potongan Rp " . number_format($c['discount_amount'], 0, ',', '.');
                                                }
                                            ?>
                                            <option value="<?= htmlspecialchars($c['code']) ?>">
                                                <?= htmlspecialchars($c['code']) ?> (<?= $label ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>  

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary border-bottom pb-1 w-100">Preview Link</label>
                                    <div class="input-group">
                                        <textarea id="previewLink" class="form-control bg-light small" rows="3" readonly>Pilih produk dahulu...</textarea>
                                        <button class="btn btn-primary d-flex align-items-center" type="button" onclick="copyToClipboard('previewLink')">
                                            <i class="bi bi-clipboard me-1"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="submit" name="generate" class="btn btn-primary">Simpan ke Riwayat</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm border-0">
                        <div class="card-header border-0 bg-white"><h3 class="card-title fw-bold">Riwayat Link</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produk</th>
                                            <th>Info Campaign</th>
                                            <th>Link</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($historyLinks as $link): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($link['product_name']) ?></td>
                                            <td>
                                                <?php if($link['campaign']): ?>
                                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($link['campaign']) ?></span>
                                                <?php endif; ?>
                                                
                                                <?php if(!empty($link['campaign_id'])): ?>
                                                    <div class="mt-1 text-muted">ID: <b><?= htmlspecialchars($link['campaign_id']) ?></b></div>
                                                <?php endif; ?>
                                                
                                                <?php if(empty($link['campaign']) && empty($link['campaign_id'])): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="min-width: 150px;">
                                                    <input type="text" class="form-control" id="cp-<?= $link['id'] ?>" value="<?= $link['target_url'] ?>" readonly>
                                                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('cp-<?= $link['id'] ?>')"><i class="bi bi-clipboard"></i></button>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $link['target_url'] ?>" target="_blank" class="btn btn-sm btn-link"><i class="bi bi-box-arrow-up-right"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // UPDATE: Variable ini sekarang berisi Referral Code (jika ada), kalau tidak ya ID
    const affRef = "<?= $ref_param ?>";
    
    const prodS = document.getElementById('productSelect');
    const campS = document.getElementById('campaignSelect');
    const campI = document.getElementById('campaignIdInput');
    const coupS = document.getElementById('couponSelect');
    const prevI = document.getElementById('previewLink');

    function updateLink() {
        const opt = prodS.options[prodS.selectedIndex];
        let url = opt.getAttribute('data-url');
        
        if (!url) {
            prevI.value = "Pilih produk dahulu...";
            return;
        }

        let prodId = prodS.value;
        let sep = url.includes('?') ? '&' : '?';
        
        // UPDATE: Gunakan affRef (Referral Code) di parameter ref=
        let link = `${url}${sep}ref=${affRef}&product_id=${prodId}`;
        
        if (campS.value) link += `&src=${encodeURIComponent(campS.value)}`;
        if (campI.value.trim()) link += `&cpid=${encodeURIComponent(campI.value.trim())}`;
        if (coupS.value) link += `&coupon=${encodeURIComponent(coupS.value)}`;
        
        prevI.value = link;
    }

    [prodS, campS, campI, coupS].forEach(el => {
        const eventType = el.tagName === 'INPUT' ? 'input' : 'change';
        el.addEventListener(eventType, updateLink);
    });

    function copyToClipboard(id) {
        const el = document.getElementById(id);
        if (el.value.includes('Pilih')) return;
        el.select();
        document.execCommand('copy');
        alert('Link disalin ke clipboard!');
    }
</script>

<?php require_once '../layout/footer.php'; ?>   
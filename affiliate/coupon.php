<?php
session_start();
require_once '../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'affiliate_coupon';
$user_id = $_SESSION['user_id'];
$message = "";

// 2. Ambil Affiliate ID
$stmt = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$affProfile = $stmt->fetch();
$affiliate_id = $affProfile['id'] ?? 0;

// 3. PROSES TAMBAH KUPON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $parent_id   = $_POST['coupon_parent_id'];
    $custom_code = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['code']))); 
    
    // Ambil input tanggal (bisa kosong)
    $start_date  = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;
    $end_date    = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;

    if (empty($custom_code)) {
        $message = '<div class="alert alert-danger">Kode kupon tidak boleh kosong.</div>';
    } elseif ($start_date && $end_date && $end_date < $start_date) {
        $message = '<div class="alert alert-danger">Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.</div>';
    } else {
        // Cek kode unik
        $stmtCheck = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmtCheck->execute([$custom_code]);
        
        if ($stmtCheck->rowCount() > 0) {
            $message = '<div class="alert alert-danger">Kode kupon <b>'.$custom_code.'</b> sudah digunakan.</div>';
        } else {
            // Ambil info parent
            $stmtParent = $pdo->prepare("SELECT discount_type, discount_amount FROM coupons WHERE id = ?");
            $stmtParent->execute([$parent_id]);
            $parent = $stmtParent->fetch();

            if ($parent) {
                // Simpan dengan Start & End Date
                $sqlInsert = "INSERT INTO coupons (affiliate_id, parent_id, code, discount_type, discount_amount, start_date, end_date, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    $affiliate_id, 
                    $parent_id, 
                    $custom_code, 
                    $parent['discount_type'], 
                    $parent['discount_amount'],
                    $start_date,
                    $end_date
                ]);
                
                $message = '<div class="alert alert-success">Kupon <b>'.$custom_code.'</b> berhasil dibuat!</div>';
            } else {
                $message = '<div class="alert alert-danger">Kupon utama tidak valid.</div>';
            }
        }
    }
}

// 4. Ambil Data Kupon (Milik Saya + Global)
$filter_code = $_GET['code'] ?? '';
$filter_status = $_GET['status'] ?? '';

// PERUBAHAN UTAMA DI SINI:
// Menggunakan grouping ( ... OR ... ) agar filter tetap jalan dengan benar
$sqlList = "
    SELECT c.*, p.code as parent_code 
    FROM coupons c 
    LEFT JOIN coupons p ON c.parent_id = p.id 
    WHERE (c.affiliate_id = ? OR c.affiliate_id IS NULL)
";
$params = [$affiliate_id];

// Tambahkan Filter
if ($filter_code) {
    $sqlList .= " AND c.code LIKE ?";
    $params[] = "%$filter_code%";
}
if ($filter_status) {
    $sqlList .= " AND c.status = ?";
    $params[] = $filter_status;
}

// Urutkan: Kupon sendiri dulu, baru global. Lalu berdasarkan tanggal terbaru.
$sqlList .= " ORDER BY c.affiliate_id DESC, c.created_at DESC";

$stmtList = $pdo->prepare($sqlList);
$stmtList->execute($params);
$myCoupons = $stmtList->fetchAll();

// 5. Ambil Parent Coupon
$stmtParents = $pdo->query("SELECT id, code, discount_type, discount_amount FROM coupons WHERE affiliate_id IS NULL AND status = 'active'");
$parentCoupons = $stmtParents->fetchAll();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Kupon Affiliate</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?= $message ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 me-auto">Daftar Kupon Anda</h5>
                    <div>
                        <button class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="bi bi-funnel"></i> Filter</button>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCouponModal"><i class="bi bi-plus-lg"></i> Tambah Kupon</button>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Kupon</th>
                                    <th>Diskon</th>
                                    <th>Masa Berlaku</th> <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
    <?php if(empty($myCoupons)): ?>
        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada kupon tersedia.</td></tr>
    <?php else: ?>
        <?php foreach($myCoupons as $c): ?>
            <?php $isGlobal = is_null($c['affiliate_id']); ?>
            
            <tr class="<?= $isGlobal ? 'bg-light' : '' ?>">
                <td>
                    <div class="d-flex align-items-center mb-1">
                        <?php if($isGlobal): ?>
                            <span class="badge bg-secondary me-2">GLOBAL</span>
                        <?php else: ?>
                            <span class="badge bg-primary me-2">MILIK ANDA</span>
                        <?php endif; ?>
                        
                        <div class="input-group input-group-sm" style="width: 180px;">
                            <input type="text" class="form-control fw-bold <?= $isGlobal ? 'text-dark' : 'text-primary' ?>" id="cp-<?= $c['id'] ?>" value="<?= $c['code'] ?>" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('cp-<?= $c['id'] ?>')"><i class="bi bi-clipboard"></i></button>
                        </div>
                    </div>
                    
                    <?php if(!$isGlobal && $c['parent_code']): ?>
                        <small class="text-muted ms-1"><i class="bi bi-link-45deg"></i> Induk: <?= $c['parent_code'] ?></small>
                    <?php elseif($isGlobal): ?>
                        <small class="text-muted ms-1 fst-italic">Disediakan oleh Admin</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['discount_type'] == 'percent'): ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><?= floatval($c['discount_amount']) ?>%</span>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Rp <?= number_format($c['discount_amount'],0,',','.') ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['start_date'] || $c['end_date']): ?>
                        <div class="small">
                            <?php if($c['start_date']): ?>
                                <div><i class="bi bi-calendar-check me-1"></i> Mulai: <?= date('d/m/y', strtotime($c['start_date'])) ?></div>
                            <?php endif; ?>
                            <?php if($c['end_date']): ?>
                                <div class="text-danger"><i class="bi bi-calendar-x me-1"></i> Sampai: <?= date('d/m/y', strtotime($c['end_date'])) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <span class="badge bg-light text-dark border">Selamanya</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php 
                        $isActive = $c['status'] == 'active';
                        if ($c['end_date'] && $c['end_date'] < date('Y-m-d')) {
                            echo '<span class="badge text-bg-secondary">Expired</span>';
                        } elseif ($isActive) {
                            echo '<span class="badge text-bg-success">Aktif</span>';
                        } else {
                            echo '<span class="badge text-bg-secondary">Tidak Aktif</span>';
                        }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Filter Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="GET">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Kode</label><input type="text" name="code" class="form-control" value="<?= htmlspecialchars($filter_code) ?>"></div>
                    <div class="mb-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="active" <?= $filter_status == 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $filter_status == 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Buat Kupon Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="alert alert-warning small d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                        <div>Kupon yang sudah dibuat tidak bisa diedit kodenya.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kupon Utama (Induk)</label>
                        <select name="coupon_parent_id" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Kupon Utama --</option>
                            <?php foreach($parentCoupons as $pc): ?>
                                <?php $val = ($pc['discount_type'] == 'percent') ? $pc['discount_amount'].'%' : 'Rp '.number_format($pc['discount_amount']); ?>
                                <option value="<?= $pc['id'] ?>"><?= htmlspecialchars($pc['code']) ?> (Diskon <?= $val ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Kupon Anda</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="CONTOH: DISKONBUDI" required pattern="[A-Za-z0-9]+">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small">Mulai Berlaku</label>
                            <input type="date" name="start_date" class="form-control">
                            <div class="form-text x-small">Opsional.</div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold small">Berakhir Pada</label>
                            <input type="date" name="end_date" class="form-control">
                            <div class="form-text x-small">Opsional.</div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_coupon" class="btn btn-primary">Simpan Kupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>

<script>
    function copyToClipboard(id) {
        var copyText = document.getElementById(id);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => { alert('Kode disalin!'); });
    }
</script>
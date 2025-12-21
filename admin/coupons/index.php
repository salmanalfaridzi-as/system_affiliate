<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
$active = 'coupons';

// TAMBAH KUPON GLOBAL
if (isset($_POST['add_coupon'])) {
    $code = strtoupper($_POST['code']);
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    
    // Insert dengan affiliate_id = NULL (Global)
    $stmt = $pdo->prepare("INSERT INTO coupons (affiliate_id, code, discount_type, discount_amount, status) VALUES (NULL, ?, ?, ?, 'active')");
    try {
        $stmt->execute([$code, $type, $amount]);
        header("Location: coupons.php"); exit;
    } catch(PDOException $e) {
        echo "<script>alert('Kode kupon sudah ada!');</script>";
    }
}

// HAPUS KUPON
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([$_GET['del']]);
    header("Location: coupons.php"); exit;
}

// Ambil Kupon Global Saja
$coupons = $pdo->query("SELECT * FROM coupons WHERE affiliate_id IS NULL ORDER BY id DESC")->fetchAll();

require_once '../layout/header.php'; require_once '../layout/navbar.php'; require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid row">
            <div class="col-6"><h3>Kupon Global</h3></div>
            <div class="col-6 text-end"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCoup"><i class="bi bi-plus-lg"></i> Buat Kupon</button></div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr><th>Kode</th><th>Diskon</th><th>Dipakai</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($coupons as $c): ?>
                            <tr>
                                <td class="fw-bold"><?= $c['code'] ?></td>
                                <td><?= $c['discount_type']=='percent' ? floatval($c['discount_amount']).'%' : 'Rp '.number_format($c['discount_amount']) ?></td>
                                <td><?= $c['usage_count'] ?>x</td>
                                <td><a href="?del=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="addCoup" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Buat Kupon Global</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3"><label>Kode Kupon</label><input type="text" name="code" class="form-control text-uppercase" required></div>
                    <div class="mb-3"><label>Tipe Diskon</label>
                        <select name="type" class="form-select"><option value="fixed">Potongan Rupiah (Fixed)</option><option value="percent">Persen (%)</option></select>
                    </div>
                    <div class="mb-3"><label>Nominal</label><input type="number" name="amount" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_coupon" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
<?php require_once '../layout/footer.php'; ?>
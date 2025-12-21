<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$active = 'access';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';

// Ambil Email
$stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$email = $stmtUser->fetchColumn();

// Ambil Produk yang sudah LUNAS (Paid)
$stmt = $pdo->prepare("
    SELECT DISTINCT p.id, p.name, p.access_url 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.buyer_email = ? AND o.status = 'paid'
");
$stmt->execute([$email]);
$products = $stmt->fetchAll();
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3 class="mb-0">Akses Produk Digital</h3></div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <?php if(empty($products)): ?>
                <div class="alert alert-warning border-0 shadow-sm">Belum ada produk yang Anda beli atau pembayaran belum lunas.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($products as $p): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card shadow-sm h-100 border-0">
                            <div class="bg-primary-subtle d-flex align-items-center justify-content-center" style="height: 140px;">
                                <i class="bi bi-box-seam-fill display-4 text-primary opacity-50"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h6 class="fw-bold text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <p class="card-text small text-muted mb-4">Klik tombol di bawah untuk mengakses materi produk ini.</p>
                                <div class="mt-auto">
                                    <a href="<?= $p['access_url'] ?>" target="_blank" class="btn btn-primary w-100 btn-sm">
                                        <i class="bi bi-box-arrow-in-right"></i> Buka Akses
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require_once '../layout/footer.php'; ?>
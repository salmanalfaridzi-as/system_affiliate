<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$active = 'subscription';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';

// Ambil Data Subscription
$stmt = $pdo->prepare("
    SELECT s.*, p.name as product_name 
    FROM subscriptions s 
    JOIN products p ON s.product_id = p.id 
    WHERE s.user_id = ? 
    ORDER BY s.end_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$subs = $stmt->fetchAll();
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3 class="mb-0">Langganan Saya</h3></div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <?php if(empty($subs)): ?>
                <div class="alert alert-info border-0 shadow-sm">Anda belum memiliki layanan berlangganan.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($subs as $s): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-top border-3 shadow-sm h-100 <?= $s['status'] == 'active' ? 'border-primary' : 'border-secondary' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($s['product_name']) ?></h5>
                                        <?php if($s['status'] == 'active'): ?>
                                            <span class="badge bg-success">AKTIF</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= strtoupper($s['status']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <i class="bi bi-calendar-check fs-3 <?= $s['status'] == 'active' ? 'text-primary' : 'text-muted' ?>"></i>
                                </div>
                                
                                <hr class="border-secondary-subtle">
                                
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <span>Biaya Rutin:</span>
                                    <span class="fw-bold text-dark">Rp <?= number_format($s['recurring_amount'], 0, ',', '.') ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span>Berakhir Pada:</span>
                                    <span class="fw-bold <?= $s['status'] == 'active' ? 'text-danger' : '' ?>">
                                        <?= date('d M Y', strtotime($s['end_date'])) ?>
                                    </span>
                                </div>

                                <?php if($s['status'] == 'active'): ?>
                                    <button class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-arrow-repeat"></i> Perpanjang</button>
                                <?php else: ?>
                                    <button class="btn btn-primary w-100 btn-sm">Langganan Ulang</button>
                                <?php endif; ?>
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
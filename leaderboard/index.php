<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$active = 'leaderboard';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';

// Query Top 10 Affiliate berdasarkan Total Komisi
$stmt = $pdo->query("
    SELECT u.name, ap.total_sales, ap.total_commission 
    FROM affiliate_profiles ap 
    JOIN users u ON ap.user_id = u.id 
    ORDER BY ap.total_commission DESC 
    LIMIT 10
");
$leaders = $stmt->fetchAll();
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3 class="mb-0">Klasemen Juara 🏆</h3></div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="text-center" width="10%">Peringkat</th>
                                    <th>Nama Affiliate</th>
                                    <th class="text-center">Penjualan</th>
                                    <th class="text-end">Total Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($leaders)): ?>
                                    <tr><td colspan="4" class="text-center py-4">Belum ada data klasemen.</td></tr>
                                <?php else: ?>
                                    <?php foreach($leaders as $index => $row): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if($index == 0): ?>
                                                <i class="bi bi-trophy-fill text-warning fs-4"></i>
                                            <?php elseif($index == 1): ?>
                                                <i class="bi bi-trophy-fill text-secondary fs-4"></i>
                                            <?php elseif($index == 2): ?>
                                                <i class="bi bi-trophy-fill text-danger fs-4"></i>
                                            <?php else: ?>
                                                <span class="fw-bold text-muted">#<?= $index + 1 ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                                        <td class="text-center"><span class="badge bg-info text-dark"><?= number_format($row['total_sales']) ?> Sales</span></td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['total_commission'], 0, ',', '.') ?></td>
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
<?php require_once '../layout/footer.php'; ?>
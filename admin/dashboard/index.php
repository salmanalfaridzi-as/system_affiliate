<?php
session_start();
require_once '../../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'dashboard';
$user_id = $_SESSION['user_id'];

// 2. Ambil Data Statistik Affiliate
// Kita ambil dari tabel affiliate_profiles agar cepat (karena sudah ada kolom total)
$stmt = $pdo->prepare("SELECT * FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Jika profile belum ada (misal baru daftar), set default 0
if (!$profile) {
    $profile = ['total_clicks' => 0, 'total_sales' => 0, 'total_commission' => 0, 'available_balance' => 0];
}

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Dashboard Affiliate</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-wallet2"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Saldo Tersedia</span>
                            <span class="info-box-number text-success fs-5">
                                Rp <?= number_format($profile['available_balance'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-cash-stack"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Komisi</span>
                            <span class="info-box-number">
                                Rp <?= number_format($profile['total_commission'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-cart-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Penjualan</span>
                            <span class="info-box-number"><?= number_format($profile['total_sales']) ?> Sales</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-mouse2"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Klik</span>
                            <span class="info-box-number"><?= number_format($profile['total_clicks']) ?> Klik</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title fw-bold">Performa Komisi Tahun Ini</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="commissionChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 bg-white d-flex justify-content-between">
                            <h3 class="card-title fw-bold">Komisi Terbaru</h3>
                            <a href="../affiliate/commission.php" class="text-decoration-none small">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                <?php
                                // Ambil 5 komisi terakhir dari database
                                $stmtCom = $pdo->prepare("
                                    SELECT c.*, o.invoice_number 
                                    FROM affiliate_commissions c 
                                    JOIN orders o ON c.order_id = o.id 
                                    JOIN affiliate_profiles ap ON c.affiliate_id = ap.id
                                    WHERE ap.user_id = ? 
                                    ORDER BY c.created_at DESC LIMIT 5
                                ");
                                $stmtCom->execute([$user_id]);
                                $recentComms = $stmtCom->fetchAll();
                                
                                if(empty($recentComms)): ?>
                                    <li class="item text-center py-4 text-muted">Belum ada komisi masuk.</li>
                                <?php else: ?>
                                    <?php foreach($recentComms as $rc): ?>
                                    <li class="item px-3 py-2 border-bottom">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="product-title font-weight-bold text-dark">
                                                    Komisi Order #<?= $rc['invoice_number'] ?>
                                                </div>
                                                <span class="text-muted small">
                                                    <?= date('d M H:i', strtotime($rc['created_at'])) ?>
                                                </span>
                                            </div>
                                            <span class="badge bg-success-subtle text-success fs-6">
                                                +Rp <?= number_format($rc['commission_amount'], 0, ',', '.') ?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('commissionChart').getContext('2d');
    const commissionChart = new Chart(ctx, {
        type: 'line',
        data: {
            // Data Dummy Bulanan (Nanti bisa diganti dinamis via PHP)
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Komisi (Rp)',
                data: [0, 500000, 1200000, 750000, 2000000, 2500000, 3000000, 2800000, 3500000, 0, 0, 0],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

<?php require_once '../layout/footer.php'; ?>
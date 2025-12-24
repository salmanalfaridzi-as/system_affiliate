<?php
// File: admin/dashboard/index.php
session_start();
require_once '../../config/database.php';

// 1. CEK LOGIN ADMIN
// Pastikan hanya role 'admin' yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'dashboard';

// ==============================================================================
// 2. HITUNG STATISTIK GLOBAL (UNTUK ADMIN)
// ==============================================================================

// A. Total Omset (Semua order status 'paid')
$stmtOmset = $pdo->query("SELECT SUM(final_amount) FROM orders WHERE status = 'paid'");
$total_omset = $stmtOmset->fetchColumn() ?: 0;

// B. Total Affiliate (Jumlah user terdaftar di tabel affiliate)
$stmtAff = $pdo->query("SELECT COUNT(id) FROM affiliate_profiles");
$total_affiliate = $stmtAff->fetchColumn() ?: 0;

// C. Pending Withdraw (Request penarikan yang butuh approval)
$stmtWD = $pdo->query("SELECT COUNT(id) FROM withdrawals WHERE status = 'pending'");
$pending_withdraw = $stmtWD->fetchColumn() ?: 0;

// D. Total Order Sukses
$stmtOrder = $pdo->query("SELECT COUNT(id) FROM orders WHERE status = 'paid'");
$total_order = $stmtOrder->fetchColumn() ?: 0;


// ==============================================================================
// 3. DATA CHART: OMSET BULANAN TAHUN INI
// ==============================================================================
$chartData = array_fill(0, 12, 0); // Array 0-11
try {
    $stmtChart = $pdo->prepare("
        SELECT MONTH(created_at) as bulan, SUM(final_amount) as total 
        FROM orders 
        WHERE status = 'paid' AND YEAR(created_at) = YEAR(CURDATE()) 
        GROUP BY MONTH(created_at)
    ");
    $stmtChart->execute();
    $rows = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $idx = $r['bulan'] - 1;
        $chartData[$idx] = (int)$r['total'];
    }
} catch (Exception $e) { }

$jsonChartData = json_encode($chartData);


// ==============================================================================
// 4. DATA ORDER TERBARU (Global)
// ==============================================================================
$stmtRecent = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recentOrders = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);


require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0 fw-bold">Administrator Dashboard</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box text-bg-primary shadow-sm">
                        <div class="inner">
                            <h3>Rp <?= number_format($total_omset, 0, ',', '.') ?></h3>
                            <p>Total Omset Masuk</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <a href="../orders/index.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box text-bg-success shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($total_affiliate) ?></h3>
                            <p>Total Affiliate</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <a href="../users/index.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Kelola User <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box text-bg-warning shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($pending_withdraw) ?></h3>
                            <p>Request Withdraw</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-exclamation-circle-fill"></i>
                        </div>
                        <a href="../withdrawals/index.php" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                            Proses Sekarang <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box text-bg-info shadow-sm">
                        <div class="inner">
                            <h3><?= number_format($total_order) ?></h3>
                            <p>Transaksi Sukses</p>
                        </div>
                        <div class="icon">
                            <i class="bi bi-cart-check-fill"></i>
                        </div>
                        <a href="../orders/index.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Lihat Semua <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title fw-bold">Grafik Omset Tahun Ini</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header border-0 bg-white d-flex justify-content-between">
                            <h3 class="card-title fw-bold">Transaksi Terbaru</h3>
                            <a href="../orders/index.php" class="text-decoration-none small">Lihat Semua</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php if(empty($recentOrders)): ?>
                                    <div class="text-center py-4 text-muted">Belum ada transaksi.</div>
                                <?php else: ?>
                                    <?php foreach($recentOrders as $order): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                            <strong class="text-truncate" style="max-width: 150px;">
                                                <?= htmlspecialchars($order['buyer_name']) ?>
                                            </strong>
                                            <small class="text-muted"><?= date('d/m H:i', strtotime($order['created_at'])) ?></small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small text-secondary"><?= $order['invoice_number'] ?></span>
                                            
                                            <?php 
                                            // Badge Status Warna-warni
                                            $badgeClass = 'bg-secondary';
                                            if($order['status'] == 'paid') $badgeClass = 'bg-success';
                                            elseif($order['status'] == 'pending') $badgeClass = 'bg-warning text-dark';
                                            elseif($order['status'] == 'failed') $badgeClass = 'bg-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?> rounded-pill">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-1 fw-bold text-success small">
                                            Rp <?= number_format($order['final_amount'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = <?= $jsonChartData ?>;

    const salesChart = new Chart(ctx, {
        type: 'bar', // Pakai Bar Chart biar beda dikit sama affiliate
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Total Omset (Rp)',
                data: salesData,
                backgroundColor: 'rgba(13, 202, 240, 0.7)', // Warna Info (Cyan)
                borderColor: 'rgba(13, 202, 240, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 2] },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(value);
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>

<?php require_once '../layout/footer.php'; ?>
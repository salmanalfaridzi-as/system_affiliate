<?php
// File: dashboard/index.php
session_start();
require_once '../config/database.php';

// 1. CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// ==========================================================
// 2. AMBIL DATA AFFILIATE USER
// ==========================================================
// Ambil data profile termasuk total_clicks yang sudah kita buat logic-nya
$stmt = $pdo->prepare("SELECT id, referral_code, available_balance, total_clicks FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$aff_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika user baru belum punya profile, set default
$affiliate_id    = $aff_data['id'] ?? 0;
$referral_code   = $aff_data['referral_code'] ?? '-';
$current_balance = $aff_data['available_balance'] ?? 0;

// ==========================================================
// 3. FUNGSI HITUNG STATISTIK UTAMA (SALES, PROFIT, COMM, CLICKS)
// ==========================================================
function get_stats($pdo, $aff_id, $period) {
    $params = [$aff_id];
    $date_cond = "";
    
    // Tentukan Filter Waktu
    if ($period === 'today') {
        $date_cond = "AND created_at BETWEEN ? AND ?";
        array_push($params, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
    } elseif ($period === 'month') {
        $date_cond = "AND created_at BETWEEN ? AND ?";
        array_push($params, date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59'));
    }

    // A. Hitung Sales & Profit (Table: orders - status 'paid')
    $sql_sales = "SELECT COUNT(id) as qty, SUM(final_amount) as omset FROM orders WHERE affiliate_id = ? AND status = 'paid' $date_cond";
    $stmt = $pdo->prepare($sql_sales);
    $stmt->execute($params);
    $sales = $stmt->fetch(PDO::FETCH_ASSOC);

    // B. Hitung Commission (Table: affiliate_commissions)
    $sql_comm = "SELECT SUM(commission_amount) FROM affiliate_commissions WHERE affiliate_id = ? AND status != 'rejected' $date_cond";
    $stmt = $pdo->prepare($sql_comm);
    $stmt->execute($params);
    $comm = $stmt->fetchColumn();

    // C. Hitung Clicks (Table: affiliate_clicks)
    $sql_click = "SELECT COUNT(id) FROM affiliate_clicks WHERE affiliate_id = ? $date_cond";
    $stmt = $pdo->prepare($sql_click);
    $stmt->execute($params);
    $clicks = $stmt->fetchColumn();

    return [
        'sales'      => (int)($sales['qty'] ?? 0),
        'profit'     => (float)($sales['omset'] ?? 0),
        'commission' => (float)($comm ?? 0),
        'clicks'     => (int)($clicks ?? 0)
    ];
}

// Eksekusi Fungsi Statistik
$stats_today = get_stats($pdo, $affiliate_id, 'today');
$stats_month = get_stats($pdo, $affiliate_id, 'month');
$stats_all   = get_stats($pdo, $affiliate_id, 'all');

// ==========================================================
// 4. FUNGSI AMBIL TOP 10 DATA (OMSET, QTY, KOMISI)
// ==========================================================
function get_top_10($pdo, $aff_id, $type, $period) {
    $col_agg = "";
    $order_by = "val DESC";
    
    if ($type == 'omset') {
        $col_agg = "SUM(o.final_amount)";
    } elseif ($type == 'qty') {
        $col_agg = "COUNT(o.id)";
    } elseif ($type == 'comm') {
        $col_agg = "SUM(ac.commission_amount)";
    }

    $date_cond = "";
    $params = [$aff_id];
    
    if ($period == 'month') {
        $date_cond = "AND o.created_at BETWEEN ? AND ?";
        array_push($params, date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59'));
    }

    $sql = "SELECT p.name, $col_agg as val
            FROM orders o
            JOIN products p ON o.product_id = p.id
            LEFT JOIN affiliate_commissions ac ON o.id = ac.order_id
            WHERE o.affiliate_id = ? AND o.status = 'paid' $date_cond
            GROUP BY p.name
            ORDER BY $order_by
            LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Ambil Data Top 10
$top_omset_all   = get_top_10($pdo, $affiliate_id, 'omset', 'all');
$top_omset_month = get_top_10($pdo, $affiliate_id, 'omset', 'month');
$top_qty_all     = get_top_10($pdo, $affiliate_id, 'qty', 'all');
$top_qty_month   = get_top_10($pdo, $affiliate_id, 'qty', 'month');
$top_comm_all    = get_top_10($pdo, $affiliate_id, 'comm', 'all');
$top_comm_month  = get_top_10($pdo, $affiliate_id, 'comm', 'month');

// ==========================================================
// 5. DATA ACQUISITION (VIEWS + LEADS + SALES)
// ==========================================================
$traffic_data = [];

// Range Waktu (Bulan Ini)
$start_date = date('Y-m-01 00:00:00');
$end_date   = date('Y-m-t 23:59:59');

try {
    // 1. Ambil Views (Clicks)
    $stmtV = $pdo->prepare("SELECT source, COUNT(id) as views FROM affiliate_clicks WHERE affiliate_id = ? AND created_at BETWEEN ? AND ? GROUP BY source");
    $stmtV->execute([$affiliate_id, $start_date, $end_date]);
    $views = $stmtV->fetchAll(PDO::FETCH_KEY_PAIR);

    // 2. Ambil Leads (Pending Orders - Checkout tapi belum bayar)
    $stmtL = $pdo->prepare("SELECT source, COUNT(id) as leads FROM orders WHERE affiliate_id = ? AND status = 'pending' AND created_at BETWEEN ? AND ? GROUP BY source");
    $stmtL->execute([$affiliate_id, $start_date, $end_date]);
    $leads = $stmtL->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Ambil Sales (Paid Orders)
    $stmtS = $pdo->prepare("SELECT source, COUNT(id) as sales, SUM(final_amount) as nilai FROM orders WHERE affiliate_id = ? AND status = 'paid' AND created_at BETWEEN ? AND ? GROUP BY source");
    $stmtS->execute([$affiliate_id, $start_date, $end_date]);
    $sales_raw = $stmtS->fetchAll(PDO::FETCH_ASSOC);

    // 4. Gabungkan Data
    $sources_sales = array_column($sales_raw, 'source');
    $all_sources = array_unique(array_merge(array_keys($views), array_keys($leads), $sources_sales));
    
    $sales_map = [];
    foreach($sales_raw as $s) {
        $k = $s['source'] ?: 'Direct / Unknown';
        $sales_map[$k] = $s;
    }

    foreach ($all_sources as $src) {
        $key = empty($src) ? 'Direct / Unknown' : $src;
        $lookupKey = $src; 
        
        $v = $views[$lookupKey] ?? 0;
        $l = $leads[$lookupKey] ?? 0;
        $s = $sales_map[$key]['sales'] ?? 0;
        $n = $sales_map[$key]['nilai'] ?? 0;
        
        $traffic_data[] = [
            'source' => $key,
            'views'  => $v,
            'leads'  => $l, 
            'sales'  => $s,
            'nilai'  => $n
        ];
    }

} catch (Exception $e) {
    $traffic_data = [];
}

// Siapkan Data Chart Acquisition
$acq_labels = [];
$acq_series = [];
foreach($traffic_data as $t) {
    if($t['sales'] > 0) {
        $acq_labels[] = $t['source'];
        $acq_series[] = (int)$t['sales'];
    }
}
$acq_labels_json = json_encode($acq_labels);
$acq_series_json = json_encode($acq_series);

// ==========================================================
// 6. DATA CHART 30 HARI & 12 BULAN
// ==========================================================
$categories_30 = []; $series_profit_30 = []; $series_comm_30 = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $categories_30[] = date('d/M', strtotime($d));
    
    $stmt = $pdo->prepare("SELECT SUM(final_amount) FROM orders WHERE affiliate_id=? AND DATE(created_at)=? AND status='paid'");
    $stmt->execute([$affiliate_id, $d]); 
    $series_profit_30[] = (int)($stmt->fetchColumn()?:0);
    
    $stmt = $pdo->prepare("SELECT SUM(commission_amount) FROM affiliate_commissions WHERE affiliate_id=? AND DATE(created_at)=? AND status!='rejected'");
    $stmt->execute([$affiliate_id, $d]); 
    $series_comm_30[] = (int)($stmt->fetchColumn()?:0);
}
$chart_30_json = json_encode(['cat'=>$categories_30, 'prof'=>$series_profit_30, 'comm'=>$series_comm_30]);

$categories_12 = []; $series_profit_12 = []; $series_comm_12 = [];
for ($i = 11; $i >= 0; $i--) {
    $ms = date('Y-m-01', strtotime("-$i months")); 
    $me = date('Y-m-t', strtotime("-$i months"));
    $categories_12[] = date('M Y', strtotime($ms));
    
    $stmt = $pdo->prepare("SELECT SUM(final_amount) FROM orders WHERE affiliate_id=? AND created_at BETWEEN ? AND ? AND status='paid'");
    $stmt->execute([$affiliate_id, $ms, $me]); 
    $series_profit_12[] = (int)($stmt->fetchColumn()?:0);
    
    $stmt = $pdo->prepare("SELECT SUM(commission_amount) FROM affiliate_commissions WHERE affiliate_id=? AND created_at BETWEEN ? AND ? AND status!='rejected'");
    $stmt->execute([$affiliate_id, $ms, $me]); 
    $series_comm_12[] = (int)($stmt->fetchColumn()?:0);
}
$chart_12_json = json_encode(['cat'=>$categories_12, 'prof'=>$series_profit_12, 'comm'=>$series_comm_12]);

function rupiah($angka) { return "Rp " . number_format($angka, 0, ',', '.'); }
$active = 'dashboard';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<style>
    .small-box { border-radius: 0.5rem; position: relative; display: block; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: #fff; overflow: hidden; transition: transform 0.2s; }
    .small-box:hover { transform: translateY(-3px); }
    .small-box .inner { padding: 20px; }
    .small-box h3 { font-size: 2rem; font-weight: 700; margin: 0 0 5px 0; }
    .small-box .icon { position: absolute; top: 15px; right: 15px; z-index: 0; font-size: 50px; color: rgba(255, 255, 255, 0.25); }
    .card-hero { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; border: none; }
    .card-balance { background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white; border: none; }
    .list-group-item { border: none; border-bottom: 1px solid #f0f0f0; padding: 0.75rem 1rem; }
    .list-group-item:last-child { border-bottom: none; }
    .badge-rank { width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.75rem; margin-right: 8px; }
</style>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold text-dark">Dashboard Afiliasi</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card card-hero shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between px-3">
                            <div>
                                <h6 class="opacity-75 fw-bold mb-1">Kode Referral</h6>
                                <h2 class="mb-0 fw-bold" id="refCodeText"><?= htmlspecialchars($referral_code) ?></h2>
                            </div>
                            <button class="btn btn-light btn-sm fw-bold shadow-sm text-primary ms-auto" onclick="copyRefCode()">Salin</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-balance shadow-sm h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="opacity-75 fw-bold mb-1">Saldo Dompet</h6>
                                <h2 class="mb-0 fw-bold"><?= rupiah($current_balance) ?></h2>
                            </div>
                            <i class="bi bi-wallet2 fs-1 opacity-50 ms-auto"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bold text-secondary">Ringkasan Statistik</h3>
                    <div class="card-tools ms-auto">
                        <ul class="nav nav-pills" id="pills-tab" role="tablist">
                            <li class="nav-item"><button class="nav-link active fw-bold" id="pills-today-tab" data-bs-toggle="pill" data-bs-target="#pills-today">Hari Ini</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" id="pills-month-tab" data-bs-toggle="pill" data-bs-target="#pills-month">Bulan Ini</button></li>
                            <li class="nav-item"><button class="nav-link fw-bold" id="pills-all-tab" data-bs-toggle="pill" data-bs-target="#pills-all">Semua</button></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-today"><?php render_stats_row($stats_today); ?></div>
                        <div class="tab-pane fade" id="pills-month"><?php render_stats_row($stats_month); ?></div>
                        <div class="tab-pane fade" id="pills-all"><?php render_stats_row($stats_all); ?></div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="card-title fw-bold">Tren 30 Hari</h6></div>
                        <div class="card-body"><div id="chart-30-days"></div></div>
                    </div>
                </div>
                <div class="col-lg-5 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="card-title fw-bold">Performa Bulanan</h6></div>
                        <div class="card-body"><div id="chart-12-months"></div></div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-trophy me-2"></i>Peringkat Top 10</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="card-title fw-bold mb-0">Top 10 All-time Turnover (Omset)</h6></div>
                        <?php render_top_list($top_omset_all, 'omset'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="card-title fw-bold mb-0">Top 10 Monthly Turnover (<?= date('M Y') ?>)</h6></div>
                        <?php render_top_list($top_omset_month, 'omset'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="card-title fw-bold mb-0">Top 10 All Time Products (Qty)</h6></div>
                        <?php render_top_list($top_qty_all, 'qty'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3"><h6 class="card-title fw-bold mb-0">Top 10 Monthly Products (<?= date('M Y') ?>)</h6></div>
                        <?php render_top_list($top_qty_month, 'qty'); ?>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-magnet me-2"></i>Acquisition Data <?= date('F Y') ?></h5>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Sumber Traffic</th>
                                            <th class="text-center">View</th>
                                            <th class="text-center">Lead (Pending)</th>
                                            <th class="text-center">Sale (Paid)</th>
                                            <th class="text-end">Nilai Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($traffic_data)): ?>
                                            <tr><td colspan="5" class="text-center py-4 text-muted">No data available</td></tr>
                                        <?php else: ?>
                                            <?php foreach($traffic_data as $td): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary me-2"><?= strtoupper(substr($td['source'],0,1)) ?></span>
                                                        <?= htmlspecialchars($td['source']) ?>
                                                    </td>
                                                    <td class="text-center"><?= number_format($td['views']) ?></td>
                                                    <td class="text-center text-warning fw-bold"><?= number_format($td['leads']) ?></td>
                                                    <td class="text-center fw-bold text-success"><?= number_format($td['sales']) ?></td>
                                                    <td class="text-end fw-bold text-primary"><?= rupiah($td['nilai']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white"><h6 class="card-title fw-bold">Top Sales Source</h6></div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <?php if(empty($traffic_data) || empty($acq_series)): ?>
                                <p class="text-muted">No sales data yet</p>
                            <?php else: ?>
                                <div id="chart-acquisition" style="width: 100%;"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</main>

<?php 
// --- HELPER FUNCTIONS HTML ---
function render_stats_row($data) { ?>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner"><h3><?= number_format($data['clicks']) ?></h3><p>Total Klik</p></div>
                <div class="icon"><i class="bi bi-mouse2-fill"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3><?= number_format($data['sales']) ?></h3><p>Sales Closing</p></div>
                <div class="icon"><i class="bi bi-cart-check-fill"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning text-dark">
                <div class="inner"><h3><?= rupiah($data['profit']) ?></h3><p>Omset Kotor</p></div>
                <div class="icon"><i class="bi bi-currency-dollar"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3><?= rupiah($data['commission']) ?></h3><p>Komisi Bersih</p></div>
                <div class="icon"><i class="bi bi-wallet-fill"></i></div>
            </div>
        </div>
    </div>
<?php } 

function render_top_list($items, $type) {
    echo '<ul class="list-group list-group-flush small">';
    if (empty($items)) {
        echo '<li class="list-group-item text-center text-muted py-4">No data</li>';
    } else {
        $i = 1;
        foreach ($items as $item) {
            $val_display = '';
            if ($type == 'qty') $val_display = number_format($item['val']) . ' Pcs';
            else $val_display = rupiah($item['val']);
            
            $badge_color = $i <= 3 ? 'bg-warning text-dark' : 'bg-light text-secondary border';
            
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<div><span class="badge-rank '.$badge_color.'">'.$i.'</span> '.htmlspecialchars($item['name']).'</div>';
            echo '<span class="fw-bold text-primary">'.$val_display.'</span>';
            echo '</li>';
            $i++;
        }
    }
    echo '</ul>';
}
?>

<?php require_once '../layout/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script>
    function copyRefCode() {
        navigator.clipboard.writeText(document.getElementById('refCodeText').innerText)
            .then(() => alert("Kode Referral berhasil disalin!"));
    }
    
    // Format Rupiah JS
    const fmtRp = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

    // Chart Data
    const chart30DaysData = <?= $chart_30_json ?>;
    const chart12MonthsData = <?= $chart_12_json ?>;
    const acqLabels = <?= $acq_labels_json ?>;
    const acqSeries = <?= $acq_series_json ?>;

    function formatRupiahJs(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    // Chart 1: Area (30 Days)
    const chart30Days = new ApexCharts(document.querySelector('#chart-30-days'), {
        series: [{ name: 'Profit Kotor', data: chart30DaysData.prof }, { name: 'Komisi Anda', data: chart30DaysData.comm }],
        chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ['#0dcaf0', '#dc3545'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: chart30DaysData.cat, tooltip: { enabled: false } },
        yaxis: { labels: { formatter: (val) => formatRupiahJs(val) } },
        tooltip: { y: { formatter: (val) => formatRupiahJs(val) } },
        legend: { position: 'top' }
    });
    chart30Days.render();

    // Chart 2: Bar (12 Months)
    const chart12Months = new ApexCharts(document.querySelector('#chart-12-months'), {
        series: [{ name: 'Profit', data: chart12MonthsData.prof }, { name: 'Komisi', data: chart12MonthsData.comm }],
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
        colors: ['#198754', '#ffc107'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: chart12MonthsData.cat },
        yaxis: { labels: { formatter: (val) => formatRupiahJs(val) } },
        tooltip: { y: { formatter: (val) => formatRupiahJs(val) } },
        legend: { position: 'top' }
    });
    chart12Months.render();

    // Render Acquisition Pie Chart
    if(acqSeries.length > 0) {
        new ApexCharts(document.querySelector('#chart-acquisition'), {
            series: acqSeries,
            chart: {type: 'pie', height: 300},
            labels: acqLabels,
            colors: ['#8d6e63', '#42a5f5', '#66bb6a', '#ffa726'],
            legend: {position: 'bottom'}
        }).render();
    }
</script>
<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'affiliate_order';
$user_id = $_SESSION['user_id'];

// Ambil Affiliate ID
$stmt = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$aff = $stmt->fetch();
$affiliate_id = $aff['id'] ?? 0;

// Filter Logic
$filter_status = $_GET['status'] ?? '';
$start_date    = $_GET['start_date'] ?? '';
$end_date      = $_GET['end_date'] ?? '';

// Query Data
// Kita ambil final_amount untuk ditampilkan di tabel
$sql = "SELECT o.*, p.name as product_name 
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        WHERE o.affiliate_id = ?";
$params = [$affiliate_id];

if ($filter_status) {
    $sql .= " AND o.status = ?";
    $params[] = $filter_status;
}
if ($start_date && $end_date) {
    $sql .= " AND DATE(o.created_at) BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
}

$sql .= " ORDER BY o.created_at DESC";
$stmtOrders = $pdo->prepare($sql);
$stmtOrders->execute($params);
$orders = $stmtOrders->fetchAll();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row"><div class="col-sm-6"><h3 class="mb-0">Order Affiliate</h3></div></div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0 me-auto">Riwayat Penjualan</h5>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Detail Order</th>
                                    <th>Pembeli</th>
                                    <th>Total Akhir</th> <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($orders)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data.</td></tr>
                                <?php else: ?>
                                    <?php foreach($orders as $o): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold d-block text-primary">#<?= $o['invoice_number'] ?></span>
                                                <small class="text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></small>
                                                <div class="small mt-1 fw-semibold">
                                                    <?= htmlspecialchars($o['product_name']) ?> <span class="badge bg-secondary ms-1">x<?= $o['qty'] ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($o['buyer_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($o['buyer_email']) ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">Rp <?= number_format($o['final_amount'], 0, ',', '.') ?></div>
                                                <?php if($o['discount_amount'] > 0): ?>
                                                    <small class="text-danger text-decoration-line-through" style="font-size: 0.75rem;">
                                                        Rp <?= number_format($o['total_amount'], 0, ',', '.') ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    $stMap = [
                                                        'pending' => ['bg-warning', 'Pending'],
                                                        'paid' => ['bg-info', 'Dibayar'],
                                                        'shipped' => ['bg-primary', 'Dikirim'],
                                                        'completed' => ['bg-success', 'Selesai'],
                                                        'cancelled' => ['bg-danger', 'Batal']
                                                    ];
                                                    $st = $stMap[$o['status']] ?? ['bg-secondary', $o['status']];
                                                ?>
                                                <span class="badge <?= $st[0] ?>"><?= $st[1] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary btn-detail" data-id="<?= $o['id'] ?>">Detail</button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Filter Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option><option value="paid">Dibayar</option><option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Filter</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Detail Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>

<script>
$(document).ready(function() {
    $('.btn-detail').on('click', function() {
        const orderId = $(this).data('id');
        const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        
        // Reset Modal Content
        $('#modalTitle').text('Detail Order #INV-' + orderId);
        $('#modalBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 small text-muted">Mengambil data...</p>
            </div>
        `);
        
        modal.show();

        // Ambil data via AJAX
        $.ajax({
            url: 'api_order_detail.php',
            type: 'GET',
            data: { id: orderId },
            dataType: 'json',
            success: function(data) {
                if(data.error) {
                    $('#modalBody').html(`<div class="alert alert-danger small">${data.error}</div>`);
                    return;
                }

                // Cek apakah ada kupon
                let couponHtml = '';
                if(data.coupon_code) {
                    couponHtml = `
                        <div class="list-group-item d-flex justify-content-between text-success">
                            <span><i class="bi bi-tag-fill me-1"></i> Diskon (${data.coupon_code})</span>
                            <span>- Rp ${data.discount_amount_fmt}</span>
                        </div>
                    `;
                }

                // Render HTML
                let html = `
                    <div class="text-center mb-3">
                        <h2 class="fw-bold text-primary mb-0">Rp ${data.final_amount_fmt}</h2>
                        <span class="badge bg-${data.status_color} text-uppercase px-3 mt-2">${data.status}</span>
                    </div>
                    
                    <div class="list-group list-group-flush small border rounded mb-3">
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">Tanggal Order</span>
                            <span class="text-end">${data.created_at}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">Pembeli</span>
                            <span class="text-end fw-bold text-dark">${data.buyer_name}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span class="fw-bold text-muted">Email / WA</span>
                            <div class="text-end">
                                <div>${data.buyer_email}</div>
                                <div>${data.buyer_phone}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2">Rincian Produk</h6>
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-white p-2 rounded border me-3 text-primary">
                                    <i class="bi bi-box-seam fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">${data.product_name}</div>
                                    <small class="text-muted">Harga: Rp ${data.total_amount_fmt}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Harga Normal</span>
                            <span>Rp ${data.total_amount_fmt}</span>
                        </div>
                        ${couponHtml}
                        <div class="list-group-item d-flex justify-content-between bg-light fw-bold">
                            <span>Total Bayar</span>
                            <span class="text-primary">Rp ${data.final_amount_fmt}</span>
                        </div>
                    </div>
                `;
                $('#modalBody').html(html);
            },
            error: function() {
                $('#modalBody').html('<div class="alert alert-danger small">Gagal mengambil data pesanan. Periksa koneksi atau API.</div>');
            }
        });
    });
});
</script>
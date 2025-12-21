<?php
session_start();
require_once '../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'affiliate_commission';
$user_id = $_SESSION['user_id'];

// 2. Ambil Affiliate ID
$stmt = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$affProfile = $stmt->fetch();
$affiliate_id = $affProfile['id'] ?? 0;

// 3. Ambil Ringkasan Komisi
// Kita hitung total komisi berdasarkan statusnya
$stmtStats = $pdo->prepare("
    SELECT 
        SUM(commission_amount) as total_all,
        SUM(CASE WHEN status = 'paid' THEN commission_amount ELSE 0 END) as total_paid,
        SUM(CASE WHEN status = 'pending' THEN commission_amount ELSE 0 END) as total_pending
    FROM affiliate_commissions 
    WHERE affiliate_id = ?
");
$stmtStats->execute([$affiliate_id]);
$stats = $stmtStats->fetch();

// 4. Ambil Data Komisi untuk Tabel
$stmtList = $pdo->prepare("
    SELECT ac.*, o.created_at, p.name as product_name, o.id as order_invoice
    FROM affiliate_commissions ac
    JOIN orders o ON ac.order_id = o.id
    JOIN products p ON o.product_id = p.id
    WHERE ac.affiliate_id = ?
    ORDER BY ac.created_at DESC
");
$stmtList->execute([$affiliate_id]);
$commissions = $stmtList->fetchAll();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Komisi Affiliate</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon text-bg-primary"><i class="bi bi-wallet2"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-secondary">Total Komisi</span>
                            <span class="info-box-number">Rp <?= number_format($stats['total_all'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon text-bg-success"><i class="bi bi-cash-stack"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-secondary">Sudah Dicairkan</span>
                            <span class="info-box-number">Rp <?= number_format($stats['total_paid'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box shadow-sm border-0">
                        <span class="info-box-icon text-bg-warning"><i class="bi bi-clock-history"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text text-secondary">Menunggu (Pending)</span>
                            <span class="info-box-number">Rp <?= number_format($stats['total_pending'] ?? 0, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <h3 class="card-title fw-bold me-auto mb-0">Riwayat Komisi</h3>

                    <div class="card-tools">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filterBox">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Detil Order</th>
                                    <th>Produk</th>
                                    <th>Tier</th>
                                    <th>Komisi</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($commissions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Tidak ada data komisi yang ditemukan.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($commissions as $c): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold d-block text-primary">#INV-<?= $c['order_invoice'] ?></span>
                                                <small class="text-muted"><i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($c['created_at'])) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($c['product_name']) ?></td>
                                            <td><span class="badge bg-light text-dark border">Tier <?= $c['tier'] ?? '1' ?></span></td>
                                            <td class="fw-bold text-success">Rp <?= number_format($c['commission_amount'], 0, ',', '.') ?></td>
                                            <td class="text-center">
                                                <?php if ($c['status'] == 'paid'): ?>
                                                    <span class="badge rounded-pill text-bg-success">Cair</span>
                                                <?php elseif ($c['status'] == 'pending'): ?>
                                                    <span class="badge rounded-pill text-bg-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill text-bg-danger">Batal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-light border btn-detail"
                                                    data-id="<?= $c['order_invoice'] ?>"
                                                    title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
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

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Detail Pesanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 small text-muted">Mengambil data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>

<script>
$(document).ready(function() {
    $('.btn-detail').on('click', function() {
        // Ambil ID dari tombol
        const orderId = $(this).data('id');
        
        // Inisialisasi Modal
        const modalEl = document.getElementById('orderDetailModal');
        const modal = new bootstrap.Modal(modalEl);
        
        // Tampilan Loading Dulu
        $('#modalTitle').text('Loading...');
        $('#modalBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 small text-muted">Sedang mengambil data...</p>
            </div>
        `);
        
        modal.show();

        // Panggil API
        $.ajax({
            url: 'api_order_detail.php',
            type: 'GET',
            data: { id: orderId },
            dataType: 'json',
            success: function(data) {
                // DEBUG: Cek di Console Browser (F12) kalau masih error
                console.log("Data Order:", data);

                if(data.error) {
                    $('#modalBody').html(`<div class="alert alert-danger text-center">${data.error}</div>`);
                    return;
                }

                // Update Judul Modal
                $('#modalTitle').text('Detail ' + data.invoice_number);

                // Cek Kupon (Biar gak undefined kalau null)
                let couponHtml = '';
                if(data.coupon_code && data.coupon_code !== null) {
                    couponHtml = `
                        <div class="list-group-item d-flex justify-content-between text-success">
                            <span><i class="bi bi-tag-fill me-1"></i> Diskon (${data.coupon_code})</span>
                            <span>- Rp ${data.discount_fmt}</span>
                        </div>
                    `;
                }

                // Render HTML (HAPUS QUANTITY KARENA GAK ADA DI DB)
                let html = `
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary mb-0">Rp ${data.final_fmt}</h2>
                        <span class="badge bg-${data.status_color} text-uppercase px-3 mt-2">${data.status}</span>
                    </div>
                    
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Informasi Pembeli</h6>
                    <div class="list-group list-group-flush small border rounded mb-3">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Tanggal</span>
                            <span class="fw-bold">${data.created_at}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Nama</span>
                            <span class="fw-bold text-dark">${data.buyer_name}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Kontak</span>
                            <div class="text-end">
                                <div>${data.buyer_email}</div>
                                <div>${data.buyer_phone}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Produk yang dibeli</h6>
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bag-check-fill fs-3 text-secondary me-3"></i>
                                <div>
                                    <div class="fw-bold text-dark">${data.product_name}</div>
                                    <small class="text-muted">Harga: Rp ${data.total_fmt}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item d-flex justify-content-between">
                            <span>Subtotal</span>
                            <span>Rp ${data.total_fmt}</span>
                        </div>
                        ${couponHtml}
                        <div class="list-group-item d-flex justify-content-between bg-light fw-bold">
                            <span>Total Bayar</span>
                            <span class="text-primary">Rp ${data.final_fmt}</span>
                        </div>
                    </div>
                `;
                $('#modalBody').html(html);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                $('#modalBody').html('<div class="alert alert-danger text-center">Terjadi kesalahan saat mengambil data.</div>');
            }
        });
    });
});
</script>
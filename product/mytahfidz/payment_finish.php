<?php
// File: product/mytahfidz/thankyou.php
require_once '../../config/database.php';

$invoice = $_GET['inv'] ?? '';
$urlStatus = $_GET['status'] ?? ''; // Tangkap status dari URL (success/failed)

$stmt = $pdo->prepare("SELECT * FROM orders WHERE invoice_number = ?");
$stmt->execute([$invoice]);
$order = $stmt->fetch();

if (!$order) die("Invoice tidak valid.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .loading-spin { animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-light">

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 text-center p-4">
                
                <h4 class="fw-bold text-secondary mb-3">Status Pesanan</h4>
                <p class="text-muted mb-4">No. Invoice: <strong><?= htmlspecialchars($invoice) ?></strong></p>

                <div id="status-container">
                    
                    <?php 
                    // LOGIC BARU:
                    // Cek Database 'paid' ATAU URL 'success'
                    if ($order['status'] == 'paid' || $urlStatus == 'success'): 
                    ?>
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill display-1"></i>
                            <h2 class="mt-3 fw-bold">Pembayaran Berhasil!</h2>
                            <p>Terima kasih, pesanan Anda telah kami terima.</p>
                            
                            <a href="../../access/index.php" class="btn btn-primary w-100 mt-3 fw-bold">
                                <i class="bi bi-box-seam me-2"></i> Akses Produk Saya
                            </a>
                            <a href="../../dashboard/" class="btn btn-outline-secondary w-100 mt-2">Ke Dashboard</a>
                        </div>

                    <?php elseif ($order['status'] == 'failed' || $urlStatus == 'failed'): ?>
                        
                        <div class="text-danger">
                            <i class="bi bi-x-circle-fill display-1"></i>
                            <h3 class="mt-3 fw-bold">Pembayaran Gagal</h3>
                            <p>Transaksi dibatalkan atau gagal diproses.</p>
                            <a href="checkout.php" class="btn btn-warning w-100 mt-3">Coba Bayar Lagi</a>
                        </div>

                    <?php else: ?>
                        
                        <div class="mb-4">
                            <i class="bi bi-hourglass-split text-warning display-1"></i>
                            <h3 class="mt-3 fw-bold">Menunggu Pembayaran</h3>
                            <p class="text-muted small">Selesaikan pembayaran Anda di iPaymu.</p>
                        </div>

                        <div id="alert-box" class="alert alert-light border d-none"></div>

                        <button onclick="verifikasiPembayaran()" id="btn-cek" class="btn btn-warning w-100 fw-bold py-3 text-dark">
                            <i class="bi bi-arrow-repeat me-2"></i> CEK STATUS MANUAL
                        </button>
                        
                        <div class="mt-3">
                            <a href="checkout.php" class="text-decoration-none text-muted small">Batal / Kembali</a>
                        </div>

                    <?php endif; ?>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
async function verifikasiPembayaran() {
    const btn = document.getElementById('btn-cek');
    const alertBox = document.getElementById('alert-box');
    const container = document.getElementById('status-container');
    const invoice = "<?= $invoice ?>";

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat loading-spin me-2"></i> Mengecek ke System...';
    alertBox.classList.add('d-none');

    try {
        // Panggil check_status.php (Pastikan file ini ada)
        const response = await fetch(`check_status.php?invoice=${invoice}`);
        const result = await response.json();
        
        if (result.status === 'success' || result.payment_status === 'PAID') {
            location.reload(); // Refresh halaman biar logic PHP jalan
        } else {
            alertBox.classList.remove('d-none');
            alertBox.className = 'alert alert-warning border small';
            alertBox.innerHTML = '<i class="bi bi-info-circle me-1"></i> Pembayaran belum lunas/masuk. Tunggu sebentar lagi.';
            
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> CEK STATUS MANUAL';
        }

    } catch (error) {
        alertBox.classList.remove('d-none');
        alertBox.className = 'alert alert-danger border small';
        alertBox.innerText = 'Gagal menghubungi server.';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> COBA LAGI';
    }
}
</script>

</body>
</html>
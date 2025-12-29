<?php
// File: info/refund.php
session_start();
require_once '../config/database.php';
$active = 'refund';
require_once '../layout/header.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container">
            <h1 class="h3 mb-4 text-center fw-bold mt-4">Kebijakan Pengembalian Dana</h1>
        </div>
    </div>

    <div class="app-content">
        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow border-0">
                        <div class="card-body p-5">
                            
                            <div class="alert alert-warning d-flex align-items-center mb-4">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Penting:</strong> Harap baca kebijakan ini dengan seksama sebelum melakukan pembelian.
                                </div>
                            </div>

                            <h5 class="fw-bold mt-4">1. Produk Digital</h5>
                            <p class="text-secondary">
                                Karena mayoritas produk kami bersifat <strong>Digital (Lisensi/Software/Ebook)</strong> yang dikirim secara instan/otomatis, maka pada dasarnya kami <strong>TIDAK MENERIMA REFUND</strong> (Pengembalian Dana) dengan alasan berubah pikiran.
                            </p>

                            <h5 class="fw-bold mt-4">2. Syarat Pengajuan Refund</h5>
                            <p class="text-secondary">Pengembalian dana HANYA dapat disetujui apabila:</p>
                            <ul class="text-secondary">
                                <li>Terjadi kesalahan sistem (Double Transfer untuk 1 invoice yang sama).</li>
                                <li>Produk/Lisensi yang dikirim rusak/tidak berfungsi dan tim teknis kami gagal memperbaikinya dalam waktu 3x24 jam.</li>
                            </ul>

                            <h5 class="fw-bold mt-4">3. Cara Mengajukan Komplain</h5>
                            <p class="text-secondary">
                                Jika Anda memenuhi syarat di atas, silakan hubungi kami melalui email: <strong>admin@admin.com</strong> dengan melampirkan:
                            </p>
                            <ul class="text-secondary">
                                <li>Nomor Invoice</li>
                                <li>Bukti Transfer</li>
                                <li>Keterangan kendala detail</li>
                            </ul>
                            
                            <hr class="my-5">
                            <div class="text-center">
                                <a href="faq.php" class="btn btn-outline-secondary">Lihat FAQ</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>
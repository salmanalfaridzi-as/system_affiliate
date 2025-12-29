<?php
// File: info/terms.php
session_start();
require_once '../config/database.php';
$active = 'terms';
require_once '../layout/header.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container">
            <h1 class="h3 mb-4 text-center fw-bold mt-4">Syarat & Ketentuan</h1>
        </div>
    </div>

    <div class="app-content">
        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow border-0">
                        <div class="card-body p-5">
                            <p class="text-muted mb-4">Terakhir diperbarui: <?= date('d F Y') ?></p>
                            
                            <h5 class="fw-bold text-dark">1. Pendahuluan</h5>
                            <p class="text-secondary text-justify">
                                Selamat datang di Affiliate System. Dengan mendaftar dan menggunakan layanan kami, Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan ini.
                            </p>

                            <h5 class="fw-bold text-dark mt-4">2. Akun & Keanggotaan</h5>
                            <ul class="text-secondary">
                                <li>Anda wajib memberikan data yang valid saat pendaftaran (Nama, Email, No WA).</li>
                                <li>Kami berhak menonaktifkan akun yang terindikasi melakukan penipuan atau spamming.</li>
                                <li>Keamanan akun dan password adalah tanggung jawab pengguna sepenuhnya.</li>
                            </ul>

                            <h5 class="fw-bold text-dark mt-4">3. Pembayaran & Transaksi</h5>
                            <p class="text-secondary text-justify">
                                Semua transaksi diproses melalui payment gateway resmi. Pesanan dianggap sah jika pembayaran telah terkonfirmasi lunas oleh sistem.
                            </p>

                            <h5 class="fw-bold text-dark mt-4">4. Program Afiliasi</h5>
                            <p class="text-secondary text-justify">
                                Affiliator dilarang keras melakukan promosi dengan cara spam, memberikan informasi palsu, atau merugikan brand Affiliate System. Pelanggaran dapat menyebabkan pemblokiran akun dan pembatalan komisi.
                            </p>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>
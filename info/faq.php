<?php
// File: info/faq.php
session_start();
// Sesuaikan path config
require_once '../config/database.php'; 

$active = 'faq';
// Pastikan path layout benar (mundur satu folder)
require_once '../layout/header.php';
// require_once '../layout/sidebar.php'; // Opsional: Matikan sidebar jika ingin tampilan full page
?>

<main class="app-main"> <div class="app-content-header">
        <div class="container">
            <h1 class="h3 mb-0 text-center fw-bold mt-4">Pusat Bantuan (FAQ)</h1>
            <p class="text-center text-muted">Pertanyaan yang sering diajukan oleh pengguna</p>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="accordion shadow-sm" id="accordionFAQ">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <i class="bi bi-cart-check me-2 text-primary"></i> Bagaimana cara membeli produk?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    Pilih produk yang Anda inginkan di halaman utama atau dashboard, klik tombol <strong>Beli Sekarang</strong>, isi data diri, dan lakukan pembayaran menggunakan metode QRIS atau Virtual Account yang tersedia.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <i class="bi bi-clock-history me-2 text-primary"></i> Berapa lama proses verifikasi pembayaran?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    Sistem kami berjalan <strong>otomatis 24 jam</strong>. Biasanya pembayaran akan terverifikasi dalam hitungan detik setelah Anda sukses melakukan transfer.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <i class="bi bi-box-seam me-2 text-primary"></i> Bagaimana cara mengakses produk yang sudah dibeli?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    Setelah pembayaran lunas, silakan login ke Dashboard Member dan buka menu <strong>"Akses Produk"</strong>. Link download atau akses materi ada di sana.
                                </div>
                            </div>
                        </div>

                         <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <i class="bi bi-people me-2 text-primary"></i> Bagaimana sistem komisinya?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-muted">
                                    Anda akan mendapatkan komisi setiap kali ada orang yang membeli produk melalui link referral Anda. Besaran komisi bervariasi tergantung produk. Komisi bisa dicairkan (withdraw) ke rekening bank Anda.
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card border-0 shadow-sm mt-5 bg-primary text-white">
                        <div class="card-body p-4 text-center">
                            <h4 class="fw-bold mb-3"><i class="bi bi-headset"></i> Masih butuh bantuan?</h4>
                            <p class="mb-4 text-white-50">
                                Jika pertanyaan Anda tidak terjawab di atas, jangan ragu untuk menghubungi tim support kami.
                            </p>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <a href="mailto:admin@admin.com" class="btn btn-light fw-bold px-4">
                                    <i class="bi bi-envelope-at-fill me-2"></i> Kirim Email
                                </a>
                                <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-success fw-bold px-4">
                                    <i class="bi bi-whatsapp me-2"></i> WhatsApp
                                </a>
                            </div>
                            <div class="mt-3 small">
                                Email: admin@admin.com
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>
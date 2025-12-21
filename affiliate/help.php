<?php
session_start();
require_once '../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'affiliate_help'; // Sesuaikan dengan key di sidebar
$user_id = $_SESSION['user_id'];

// 2. Ambil Daftar Produk untuk Dropdown
$stmtProd = $pdo->query("SELECT id, name FROM products WHERE status = 'active' ORDER BY name ASC");
$products = $stmtProd->fetchAll();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Marketing Kit</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <p class="text-muted">Pilih produk untuk menampilkan materi promosi (Copywriting, Gambar, Banner, dll).</p>
                    <form id="form-filter-help">
                        <div class="input-group">
                            <select id="select-product" class="form-select shadow-none">
                                <option value="" selected disabled>-- Pilih Produk --</option>
                                <?php foreach($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary px-4" id="btn-generate">
                                <i class="bi bi-magic me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="material-kit-holder">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>Silakan pilih produk terlebih dahulu untuk melihat bantuan pemasaran.</div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>

<script>
$(document).ready(function() {
    
    $('#form-filter-help').on('submit', function(e) {
        e.preventDefault();
        const productId = $('#select-product').val();
        
        if(!productId) {
            alert('Silakan pilih produk terlebih dahulu');
            return;
        }

        // Efek loading
        $('#material-kit-holder').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Mengambil material promosi...</p>
            </div>
        `);

        // Panggil File API yang terpisah
        $.getJSON('api_marketing_kit.php', { product_id: productId }, function(data) {
            if(data.length === 0) {
                $('#material-kit-holder').html('<div class="alert alert-warning">Belum ada marketing kit untuk produk ini.</div>');
                return;
            }

            let html = '<div class="row g-4">';
            data.forEach((item, index) => {
                const inputId = 'kit-' + index;
                html += `
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-1">${item.title}</h6>
                                <p class="text-muted small mb-3">${item.description}</p>
                                
                                <div class="input-group">
                                    <textarea class="form-control bg-light small" id="${inputId}" rows="3" readonly>${item.content}</textarea>
                                    <button class="btn btn-outline-primary d-flex align-items-center" type="button" onclick="copyKit('${inputId}')">
                                        <i class="bi bi-clipboard me-1"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            $('#material-kit-holder').html(html);

        }).fail(function() {
            $('#material-kit-holder').html('<div class="alert alert-danger">Terjadi kesalahan. Pastikan file api_marketing_kit.php sudah dibuat.</div>');
        });
    });
});

// Fungsi Copy to Clipboard
function copyKit(id) {
    const el = document.getElementById(id);
    el.select();
    el.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(el.value).then(() => {
        alert("Materi berhasil disalin!");
    });
}
</script>
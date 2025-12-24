<?php
// File: admin/products/index.php
session_start();
require_once '../../config/database.php';

// Cek Login & Role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
$active = 'products';

// --- LOGIC TAMBAH PRODUK ---
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $comm_type = $_POST['commission_type'];
    $comm_amount = $_POST['commission_amount'];
    $desc = $_POST['description'];
    
    // Simpan ke DB
    $stmt = $pdo->prepare("INSERT INTO products (name, price, description, commission_type, commission_amount, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $price, $desc, $comm_type, $comm_amount]);
    
    header("Location: index.php"); exit;
}

// --- LOGIC HAPUS PRODUK ---
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: index.php"); exit;
}

// Ambil Semua Produk
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();

require_once '../layout/header.php'; require_once '../layout/navbar.php'; require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid row">
            <div class="col-6"><h3>Kelola Produk</h3></div>
            <div class="col-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-lg"></i> Tambah Produk
                </button>
            </div>
        </div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Komisi Affiliate</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?= htmlspecialchars($p['name']) ?>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($p['description']) ?></div>
                                </td>
                                <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if($p['commission_type'] == 'percent'): ?>
                                        <span class="badge bg-info text-dark"><?= $p['commission_amount'] ?>%</span>
                                        <small class="text-muted d-block">
                                            (Rp <?= number_format($p['price'] * ($p['commission_amount']/100),0,',','.') ?>)
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-success">Rp <?= number_format($p['commission_amount'],0,',','.') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary disabled"><i class="bi bi-pencil"></i></a>
                                    <a href="?del=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tipe Komisi</label>
                            <select name="commission_type" class="form-select">
                                <option value="percent">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Jumlah Komisi</label>
                            <input type="number" name="commission_amount" class="form-control" placeholder="Contoh: 10 atau 50000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi Singkat</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_product" class="btn btn-primary">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
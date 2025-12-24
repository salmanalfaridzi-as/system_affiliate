<?php
// File: admin/orders/index.php
session_start();
require_once '../../config/database.php';

// PERBAIKAN: user_role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
$active = 'orders';

// UPDATE STATUS ORDER
if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $st = $_POST['status'];
    $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$st, $oid]);
    
    // PERBAIKAN: Redirect ke index.php
    header("Location: index.php"); 
    exit;
}

// AMBIL SEMUA ORDER
$orders = $pdo->query("
    SELECT o.*, p.name as prod_name, u.name as aff_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id
    LEFT JOIN affiliate_profiles ap ON o.affiliate_id = ap.id
    LEFT JOIN users u ON ap.user_id = u.id
    ORDER BY o.created_at DESC
")->fetchAll();

require_once '../layout/header.php'; require_once '../layout/navbar.php'; require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header"><div class="container-fluid"><h3>Data Pesanan</h3></div></div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr><th>Invoice</th><th>Pembeli</th><th>Produk</th><th>Affiliate</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $o): ?>
                                <tr>
                                    <td><span class="fw-bold">#<?= $o['invoice_number'] ?></span><br><small><?= substr($o['created_at'],0,10) ?></small></td>
                                    <td><?= htmlspecialchars($o['buyer_name']) ?><br><small><?= $o['buyer_phone'] ?></small></td>
                                    <td><?= htmlspecialchars($o['prod_name']) ?></td>
                                    <td><?= $o['aff_name'] ? '<span class="badge bg-info text-dark">'.$o['aff_name'].'</span>' : '-' ?></td>
                                    <td class="fw-bold">Rp <?= number_format($o['final_amount'],0,',','.') ?></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" 
                                                style="width:110px; background-color: <?= $o['status']=='paid'?'#d1e7dd':($o['status']=='pending'?'#fff3cd':'#f8d7da') ?>">
                                                <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                                                <option value="paid" <?= $o['status']=='paid'?'selected':'' ?>>Paid</option>
                                                <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancel</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td><button class="btn btn-sm btn-outline-secondary">Detail</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once '../layout/footer.php'; ?>
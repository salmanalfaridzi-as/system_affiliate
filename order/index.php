<?php
// File: order/index.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'order';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';

// 1. Ambil Email User Login
$stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$currentUser = $stmtUser->fetch();
$userEmail = $currentUser['email'];

// 2. Ambil Order berdasarkan Email Pembeli
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.buyer_email = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$userEmail]);
$myOrders = $stmt->fetchAll();
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3 class="mb-0">Riwayat Belanja Saya</h3></div>
    </div>
    
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice & Tanggal</th>
                                    <th>Produk</th>
                                    <th>Total Bayar</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($myOrders)): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Anda belum pernah berbelanja.</td></tr>
                                <?php else: ?>
                                    <?php foreach($myOrders as $o): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">#<?= $o['invoice_number'] ?></span><br>
                                            <small class="text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($o['product_name']) ?>
                                            <span class="badge bg-secondary ms-1">x<?= $o['qty'] ?></span>
                                        </td>
                                        <td class="fw-bold">Rp <?= number_format($o['final_amount'] > 0 ? $o['final_amount'] : $o['total_amount'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $badges = [
                                                'pending'   => 'bg-warning text-dark',
                                                'paid'      => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                                'failed'    => 'bg-danger',
                                                'refunded'  => 'bg-secondary'
                                            ];
                                            $labels = [
                                                'pending'   => 'Menunggu',
                                                'paid'      => 'Lunas',
                                                'cancelled' => 'Batal',
                                                'failed'    => 'Gagal',
                                                'refunded'  => 'Refund'
                                            ];
                                            ?>
                                            <span class="badge <?= $badges[$o['status']] ?? 'bg-secondary' ?>">
                                                <?= $labels[$o['status']] ?? ucfirst($o['status']) ?>
                                            </span>

                                            <?php if($o['status'] == 'pending'): ?>
                                                <div class="mt-2">
                                                    <a href="<?= $o['payment_url'] ?>" 
                                                       class="btn btn-sm btn-outline-dark py-0 px-2 small" style="font-size: 0.75rem;">
                                                        <i class="bi bi-credit-card me-1"></i> Bayar
                                                    </a>
                                                </div>
                                            <?php endif; ?>
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

<?php require_once '../layout/footer.php'; ?>
<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
$active = 'withdrawals';

// PROSES PAYOUT
if (isset($_POST['process_wd'])) {
    $wid = $_POST['wd_id'];
    $action = $_POST['action']; // 'paid' or 'rejected'
    
    // Update status withdrawal
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = ? WHERE id = ?");
    $stmt->execute([$action == 'approve' ? 'paid' : 'rejected', $wid]);
    
    // Jika rejected, kembalikan saldo ke affiliate (Logika tambahan diperlukan jika ada sistem saldo)
    
    header("Location: withdrawals.php"); exit;
}

// Query: Join Withdrawals -> Affiliate Profiles -> Users
$wds = $pdo->query("
    SELECT w.*, u.name, u.email, ap.bank_name, ap.bank_account_number, ap.bank_account_name 
    FROM withdrawals w
    JOIN affiliate_profiles ap ON w.affiliate_id = ap.id
    JOIN users u ON ap.user_id = u.id
    ORDER BY w.requested_at ASC
")->fetchAll();

require_once '../layout/header.php'; require_once '../layout/navbar.php'; require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header"><div class="container-fluid"><h3>Request Payout Affiliate</h3></div></div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr><th>Tanggal</th><th>Affiliate</th><th>Info Rekening</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($wds as $w): ?>
                            <tr>
                                <td><?= date('d M Y H:i', strtotime($w['requested_at'])) ?></td>
                                <td>
                                    <span class="fw-bold"><?= htmlspecialchars($w['name']) ?></span><br>
                                    <small class="text-muted"><?= $w['email'] ?></small>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= $w['bank_name'] ?> - <?= $w['bank_account_number'] ?></div>
                                    <div class="small text-muted">A.n <?= $w['bank_account_name'] ?></div>
                                </td>
                                <td class="fw-bold text-success fs-5">Rp <?= number_format($w['amount'],0,',','.') ?></td>
                                <td>
                                    <?php if($w['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">MENUNGGU</span>
                                    <?php elseif($w['status'] == 'paid'): ?>
                                        <span class="badge bg-success">SUDAH DIBAYAR</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">DITOLAK</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($w['status'] == 'pending'): ?>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="wd_id" value="<?= $w['id'] ?>">
                                        <button type="submit" name="action" value="approve" name="process_wd" class="btn btn-sm btn-success" onclick="return confirm('Sudah transfer ke rekening affiliate?')"><i class="bi bi-check-lg"></i> Bayar</button>
                                        <button type="submit" name="action" value="reject" name="process_wd" class="btn btn-sm btn-danger" onclick="return confirm('Tolak request ini?')"><i class="bi bi-x-lg"></i></button>
                                        <input type="hidden" name="process_wd" value="1">
                                    </form>
                                    <?php else: ?>
                                        <i class="bi bi-check-circle-fill text-muted"></i> Selesai
                                    <?php endif; ?>
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
<?php require_once '../layout/footer.php'; ?>
<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; }

$active = 'license';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';

// Ambil Email
$stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$email = $stmtUser->fetchColumn();

// Ambil Order Lunas (Simulasi Lisensi dari Invoice)
$stmt = $pdo->prepare("
    SELECT o.invoice_number, p.name as product_name, o.created_at 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.buyer_email = ? AND o.status = 'paid'
");
$stmt->execute([$email]);
$licenses = $stmt->fetchAll();
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid"><h3 class="mb-0">Lisensi & Key</h3></div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>License Key</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($licenses)): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada lisensi ditemukan.</td></tr>
                                <?php else: ?>
                                    <?php foreach($licenses as $l): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($l['product_name']) ?></div>
                                            <small class="text-muted">Dibeli: <?= date('d M Y', strtotime($l['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control bg-light font-monospace text-primary" value="KEY-<?= $l['invoice_number'] ?>" readonly id="key-<?= $l['invoice_number'] ?>">
                                                <button class="btn btn-outline-secondary" onclick="copyKey('key-<?= $l['invoice_number'] ?>')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-success">AKTIF</span></td>
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

<script>
function copyKey(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(() => {
        alert("License Key berhasil disalin!");
    });
}
</script>
<?php require_once '../layout/footer.php'; ?>
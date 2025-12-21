<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../auth/login.php"); exit; }
$active = 'users';

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require_once '../layout/header.php'; require_once '../layout/navbar.php'; require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header"><div class="container-fluid"><h3>Data Pengguna</h3></div></div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Terdaftar</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if($u['role'] == 'admin'): ?>
                                        <span class="badge bg-danger">ADMIN</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">AFFILIATE</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success"><?= strtoupper($u['status']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
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
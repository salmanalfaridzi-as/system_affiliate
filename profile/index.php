<?php
session_start();
require_once '../config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$active = 'profile';
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// 2. HANDLE POST REQUEST (Update Data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- A. UPDATE PROFIL & BANK ---
    if (isset($_POST['update_profile'])) {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
        
        $bank_name = filter_input(INPUT_POST, 'bank_name', FILTER_SANITIZE_SPECIAL_CHARS);
        $bank_number = filter_input(INPUT_POST, 'bank_account_number', FILTER_SANITIZE_SPECIAL_CHARS);
        $bank_holder = filter_input(INPUT_POST, 'bank_account_name', FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            $pdo->beginTransaction();

            // 1. Update Table Users
            $stmtUser = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmtUser->execute([$name, $phone, $user_id]);

            // 2. Update/Insert Table Affiliate Profiles (Data Bank)
            // Menggunakan logic: Cek dulu ada nggak, kalau ada update, kalau gak ada insert
            $checkAff = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
            $checkAff->execute([$user_id]);
            
            if ($checkAff->rowCount() > 0) {
                $sqlAff = "UPDATE affiliate_profiles SET bank_name=?, bank_account_number=?, bank_account_name=? WHERE user_id=?";
            } else {
                $sqlAff = "INSERT INTO affiliate_profiles (bank_name, bank_account_number, bank_account_name, user_id) VALUES (?, ?, ?, ?)";
            }
            $stmtAff = $pdo->prepare($sqlAff);
            $stmtAff->execute([$bank_name, $bank_number, $bank_holder, $user_id]);

            $pdo->commit();
            
            // Update Session Name jika berubah
            $_SESSION['user_name'] = $name;
            
            $msg = "Profil berhasil diperbarui!";
            $msg_type = "success";

        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Gagal update: " . $e->getMessage();
            $msg_type = "danger";
        }
    }

    // --- B. GANTI PASSWORD ---
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $cnf_pass = $_POST['confirm_password'];

        // Ambil password lama dari DB
        $stmtGet = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmtGet->execute([$user_id]);
        $currUser = $stmtGet->fetch();

        if (password_verify($old_pass, $currUser['password'])) {
            if ($new_pass === $cnf_pass) {
                if (strlen($new_pass) >= 6) {
                    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $stmtUpd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmtUpd->execute([$hash, $user_id]);
                    
                    $msg = "Password berhasil diubah!";
                    $msg_type = "success";
                } else {
                    $msg = "Password baru minimal 6 karakter.";
                    $msg_type = "warning";
                }
            } else {
                $msg = "Konfirmasi password baru tidak cocok.";
                $msg_type = "danger";
            }
        } else {
            $msg = "Password lama salah.";
            $msg_type = "danger";
        }
    }
}

// 3. AMBIL DATA USER TERBARU
// Left Join agar user yang belum punya data bank tetap muncul
$stmtData = $pdo->prepare("
    SELECT u.name, u.email, u.phone, a.bank_name, a.bank_account_number, a.bank_account_name
    FROM users u
    LEFT JOIN affiliate_profiles a ON u.id = a.user_id
    WHERE u.id = ?
");
$stmtData->execute([$user_id]);
$d = $stmtData->fetch();

require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../layout/sidebar.php';
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0">Profil Saya</h3></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <?php if($msg): ?>
                <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                    <?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card card-primary card-outline shadow-sm h-100">
                        <div class="card-body box-profile text-center">
                            <div class="text-center mb-3">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-fill display-4"></i>
                                </div>
                            </div>

                            <h3 class="profile-username text-center fw-bold"><?= htmlspecialchars($d['name']) ?></h3>
                            <p class="text-muted text-center">Affiliate Partner</p>

                            <ul class="list-group list-group-unbordered mb-3 text-start small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <b>Email</b> <span class="text-muted"><?= htmlspecialchars($d['email']) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <b>No HP</b> <span class="text-muted"><?= htmlspecialchars($d['phone'] ?? '-') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <b>Bank</b> <span class="text-muted"><?= htmlspecialchars($d['bank_name'] ?? '-') ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header p-2 bg-white border-bottom-0">
                            <ul class="nav nav-pills" id="profileTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#settings" data-bs-toggle="tab">Edit Data Diri</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#password" data-bs-toggle="tab">Ganti Password</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                
                                <div class="active tab-pane" id="settings">
                                    <form class="form-horizontal" method="POST">
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-2"></i>Informasi Akun</h6>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Nama Lengkap</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($d['name']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($d['email']) ?>" readonly>
                                                <div class="form-text">Email tidak dapat diubah. Hubungi admin jika perlu.</div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">No WhatsApp</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($d['phone']) ?>" placeholder="08xxxx">
                                            </div>
                                        </div>

                                        <hr>
                                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-credit-card me-2"></i>Informasi Rekening (Untuk Komisi)</h6>
                                        
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Nama Bank</label>
                                            <div class="col-sm-9">
                                                <select name="bank_name" class="form-select">
                                                    <option value="">-- Pilih Bank --</option>
                                                    <?php 
                                                    $banks = ['BCA', 'BRI', 'BNI', 'MANDIRI', 'BSI', 'JAGO', 'OVO', 'GOPAY', 'DANA'];
                                                    foreach($banks as $bank) {
                                                        $selected = ($d['bank_name'] == $bank) ? 'selected' : '';
                                                        echo "<option value='$bank' $selected>$bank</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">No. Rekening</label>
                                            <div class="col-sm-9">
                                                <input type="number" class="form-control" name="bank_account_number" value="<?= htmlspecialchars($d['bank_account_number'] ?? '') ?>" placeholder="Contoh: 1234567890">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Atas Nama</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" name="bank_account_name" value="<?= htmlspecialchars($d['bank_account_name'] ?? '') ?>" placeholder="Nama sesuai buku tabungan">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" id="password">
                                    <form class="form-horizontal" method="POST">
                                        <div class="alert alert-info border-0 shadow-sm mb-4">
                                            <i class="bi bi-info-circle me-2"></i> Pastikan password baru Anda kuat dan sulit ditebak.
                                        </div>

                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Password Lama</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" name="old_password" required placeholder="Masukkan password saat ini">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Password Baru</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" name="new_password" required placeholder="Minimal 6 karakter">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label class="col-sm-3 col-form-label">Konfirmasi Baru</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" name="confirm_password" required placeholder="Ulangi password baru">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" name="change_password" class="btn btn-danger">Ganti Password</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../layout/footer.php'; ?>
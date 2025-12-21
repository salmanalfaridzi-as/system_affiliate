<?php
// File: auth/register.php
session_start();
require_once '../config/database.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

$error = '';
$success = '';

// Nama Brand/Judul Halaman (Ganti di sini)
$appTitle = "Affiliate System"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
    $pass = $_POST['password'];

    if ($name && $email && $pass) {
        try {
            // 1. Cek Email & Ambil Password Hash
            // Kita butuh kolom 'password' untuk verifikasi
            $stmt = $pdo->prepare("SELECT id, role, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            $userId = null;
            $isNewUser = false;

            if ($existing) {
                // KASUS A: USER SUDAH ADA
                if ($existing['role'] === 'affiliate' || $existing['role'] === 'admin') {
                    $error = "Email sudah terdaftar sebagai Affiliate. Silakan Login.";
                } else {
                    // KASUS B: USER ADA TAPI ROLE 'USER' (Pembeli Biasa) -> UPGRADE!
                    
                    // --- LOGIC BARU: VERIFIKASI PASSWORD ---
                    if (password_verify($pass, $existing['password'])) {
                        // Password COCOK, lakukan Upgrade
                        $userId = $existing['id'];
                        
                        // Update Role jadi Affiliate, update nama & phone (Password TIDAK diubah)
                        $stmtUpd = $pdo->prepare("UPDATE users SET role = 'affiliate', name = ?, phone = ? WHERE id = ?");
                        $stmtUpd->execute([$name, $phone, $userId]);
                        
                        $success = "Verifikasi berhasil! Akun Anda telah di-upgrade menjadi Affiliate.";
                    } else {
                        // Password SALAH
                        $error = "Email sudah terdaftar, namun password salah. Masukkan password akun Anda saat ini untuk verifikasi.";
                    }
                }
            } else {
                // KASUS C: USER BARU (Belum ada di DB)
                // Buat password baru
                $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
                
                $stmtIns = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, created_at) VALUES (?, ?, ?, ?, 'affiliate', NOW())");
                $stmtIns->execute([$name, $email, $phone, $hashedPass]);
                
                $userId = $pdo->lastInsertId();
                $isNewUser = true;
                $success = "Pendaftaran berhasil!";
            }

            // 2. BUAT AFFILIATE PROFILE (Hanya jika userId terset dan tidak ada error)
            if ($userId && empty($error)) {
                // Cek profil apakah sudah ada
                $stmtProf = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE user_id = ?");
                $stmtProf->execute([$userId]);

                if ($stmtProf->rowCount() == 0) {
                    // Generate Referral Code
                    $namePart = explode(' ', trim($name))[0];
                    $namePart = preg_replace("/[^a-zA-Z0-9]/", "", $namePart);
                    $refCode = strtoupper(substr($namePart, 0, 5) . rand(100, 999));

                    // Cek duplikat code simple
                    $stmtCek = $pdo->prepare("SELECT id FROM affiliate_profiles WHERE referral_code = ?");
                    $stmtCek->execute([$refCode]);
                    if ($stmtCek->rowCount() > 0) {
                        $refCode .= rand(1,9);
                    }

                    $stmtInsProf = $pdo->prepare("INSERT INTO affiliate_profiles (user_id, referral_code, available_balance, created_at) VALUES (?, ?, 0, NOW())");
                    $stmtInsProf->execute([$userId, $refCode]);
                }

                // Auto Login setelah Register/Upgrade
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'affiliate';
                
                header("Location: ../dashboard/index.php");
                exit;
            }

        } catch (Exception $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua data.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= $appTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/adminlte/adminlte.css">
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h1 class="mb-0"><b><?= $appTitle ?></b></h1>
                <small>Bergabung Menjadi Affiliate</small>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Daftar baru atau upgrade akun Anda</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger small"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success small"><?= $success ?></div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                        <div class="input-group-text"><span class="bi bi-person"></span></div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="number" name="phone" class="form-control" placeholder="No WhatsApp" required value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                        <div class="input-group-text"><span class="bi bi-telephone"></span></div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password (Gunakan Pass Lama jika Upgrade)" required>
                        <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Daftar / Upgrade Akun</button>
                        </div>
                    </div>
                </form>
                <p class="mb-0 mt-3 text-center">
                    <a href="login.php" class="text-center">Sudah punya akun Affiliate? Login</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
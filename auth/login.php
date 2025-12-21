<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php");
    exit;
}

// Logic Auto Login via Cookie (Gunakan $pdo)
// if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
//     echo true;die();

//     require_once '../config/database.php'; // Panggil koneksi $pdo
    
//     $cookie_id = $_COOKIE['remember_user'];
//     $cookie_token = $_COOKIE['remember_token'];
//     $salt = "MyTahfidzSuperSecretKey"; // Harus sama dengan process_login.php
    
//     // Cek kecocokan token
//     if (hash('sha256', $cookie_id . $salt) === $cookie_token) {
//         try {
//             // Ambil data user terbaru dari DB
//             $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
//             $stmt->execute([$cookie_id]);
//             $user = $stmt->fetch();

//             if ($user) {
//                 $_SESSION['user_id'] = $user['id'];
//                 $_SESSION['user_name'] = $user['name'];
//                 $_SESSION['user_email'] = $user['email'];
                
//                 header("Location: ../dashboard/index.php");
//                 exit;
//             }
//         } catch (Exception $e) {
//             // Jika error, biarkan user login manual
//         }
//     }
// }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/css/adminlte.min.css" crossorigin="anonymous">
</head>

<body class="login-page bg-body-secondary">
    
    <div class="login-box">
        <div class="card card-outline card-primary shadow">
            <div class="card-header text-center">
                <a href="../index.php" class="h1"><b>Sejoli</b> Clone</a>
            </div>
            
            <div class="card-body">
                <p class="login-box-msg">Masuk untuk memulai sesi Anda</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($_GET['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="process_login.php" method="post">
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" id="loginEmail" placeholder="name@example.com" required>
                            <label for="loginEmail">Email</label>
                        </div>
                        <div class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Password" required>
                            <label for="loginPassword">Password</label>
                        </div>
                        <div class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        </div>
                    </div>
                </form>

                <div class="social-auth-links text-center mt-2 mb-3">
                    <p class="mb-1">
                        <a href="forgot-password.php">Lupa password saya</a>
                    </p>
                    <p class="mb-0">
                        <a href="register.php" class="text-center">Daftar member baru</a>
                    </p>
                </div>
            </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta2/dist/js/adminlte.min.js"></script>
</body>
</html>
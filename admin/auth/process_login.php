<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Ambil user berdasarkan email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi Password
    if ($user && password_verify($password, $user['password'])) {
        
        // Cek Status Akun
        if ($user['status'] !== 'active') {
            header("Location: login.php?error=Akun Anda dinonaktifkan.");
            exit;
        }

        // Set Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect Sesuai Role
        if ($user['role'] === 'admin') {
            header("Location: ../dashboard/");
        } else {
            header("Location: login.php?error=Akun Bukan Admin.");
        }
        exit;
        
    } else {
        header("Location: login.php?error=Email atau password salah.");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>
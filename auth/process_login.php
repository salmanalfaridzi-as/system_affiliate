<?php
session_start();
require_once '../config/database.php';

// Pastikan akses via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

try {
    // Cek User di Database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(); 

    // Verifikasi Password
    if ($user && password_verify($password, $user['password'])) {
        
        // --- 1. SET SESSION ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // --- 2. SET COOKIE (Remember Me) ---
        if (isset($_POST['remember'])) {
            $salt = "MyTahfidzSuperSecretKey"; 
            $token = hash('sha256', $user['id'] . $salt);
            // Cookie Path WAJIB '/' agar bisa dibaca global
            setcookie('remember_user', $user['id'], time() + (86400 * 30), "/");
            setcookie('remember_token', $token, time() + (86400 * 30), "/");
        }

        if ($user['role'] === 'user' && !isset($redirect_to)) { 
            // User biasa ditolak, suruh upgrade via Register
            // Kita kembalikan redirect_to juga jaga-jaga kalau dia mau register nanti
            $params = "error=" . urlencode("Akun Anda masih akun Pembeli. Silakan Daftar ulang untuk Upgrade.");
            header("Location: login.php?" . $params); 
            exit; 
        }

        // --- 3. REDIRECT SUKSES ---
        // Jika ada request redirect (dari modal checkout), balikin kesana
        if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
            header("Location: " . $_POST['redirect_to']);
            exit;
        } else {
            // Default ke dashboard
            header("Location: ../dashboard/index.php");
            exit;
        }

    } else {
        // --- 4. LOGIN GAGAL (INI YANG DIPERBAIKI) ---
        
        // Jika user login dari Modal Checkout (ada parameter redirect_to)
        if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
            
            $targetUrl = $_POST['redirect_to'];
            
            // Cek apakah URL sudah punya parameter (?) atau belum
            // Biar gak error format URL-nya (misal: checkout.php?prod=1&error=...)
            $separator = (strpos($targetUrl, '?') === false) ? '?' : '&';
            
            // Balikin ke Halaman Checkout dengan pesan Error
            header("Location: " . $targetUrl . $separator . "error=Login Gagal! Email atau Password salah.");
            exit;

        } else {
            // Jika login dari halaman login biasa
            header("Location: login.php?error=Email atau Password salah!");
            exit;
        }
    }

} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}
?>
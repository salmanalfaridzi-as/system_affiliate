<?php
session_start();

// 1. Kosongkan Session
$_SESSION = [];

// 2. Hancurkan Session Server
session_destroy();

// 3. HAPUS COOKIE (NUCLEAR METHOD)
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/'); 
    setcookie('remember_user', '', time() - 3600, '');  
    unset($_COOKIE['remember_user']);
}

if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/'); 
    setcookie('remember_token', '', time() - 3600, '');  
    unset($_COOKIE['remember_token']);
}

// 4. REDIRECT LOGIC (INI YANG BARU)
// Cek apakah ada parameter 'redirect_to' di URL?
if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
    // Balik ke halaman asalnya (misal: Checkout)
    header("Location: " . $_GET['redirect_to']);
} else {
    // Default: Ke halaman login
    header("Location: login.php");
}
exit;
?>
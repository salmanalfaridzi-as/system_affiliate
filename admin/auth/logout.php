<?php
session_start();

// 1. Hapus semua variabel session
$_SESSION = [];

// 2. Jika session menggunakan cookie, hapus juga cookie session-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session di server
session_destroy();

if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/'); // Hapus yang di Root
    setcookie('remember_user', '', time() - 3600, '');  // Hapus yang di folder ini (jaga-jaga)
    unset($_COOKIE['remember_user']);
}

// Hapus cookie Token
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/'); // Hapus yang di Root
    setcookie('remember_token', '', time() - 3600, '');  // Hapus yang di folder ini
    unset($_COOKIE['remember_token']);
}
// 5. Redirect ke halaman login
header("Location: login.php");
exit;
?>
<?php
// File: auth/logout.php
session_start();

// 1. Kosongkan semua variabel session
$_SESSION = [];

// 2. HAPUS COOKIE SESI PHP (PHPSESSID) - INI YANG KETINGGALAN
// Ini memaksa browser melupakan ID sesi yang sedang aktif
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan Session di Server
session_destroy();

// 4. HAPUS COOKIE "REMEMBER ME" (JIKA ADA)
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/'); 
}
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/'); 
}

// 5. REDIRECT LOGIC
if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
    header("Location: " . $_GET['redirect_to']);
} else {
    header("Location: login.php");
}
exit;
?>
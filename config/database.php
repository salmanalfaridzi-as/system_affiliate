<?php
// config/database.php

// --- KONFIGURASI URL UTAMA (BASE URL) ---
define('BASE_URL', 'https://34f8835048d6.ngrok-free.app/my_tahfidz_affiliator_sejoli');

// Nanti saat Deploy (Hosting), ganti jadi:
// define('BASE_URL', 'https://domainkamu.com');

$host = 'localhost';
$db   = 'mytahfidz-optimasi';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Mulai session di setiap halaman yang include file ini
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
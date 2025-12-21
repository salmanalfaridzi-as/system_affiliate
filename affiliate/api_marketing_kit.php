<?php
session_start();
require_once '../config/database.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

// Set Header JSON agar browser mengerti ini data, bukan halaman web
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

try {
    // Ambil data marketing kit dari database
    $stmt = $pdo->prepare("SELECT title, description, content FROM marketing_kits WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode([]);
}
exit;
?>
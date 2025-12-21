<?php
// File: api/payment/create.php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../libraries/doku_handler.php';

// CORS
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Ambil JSON Body atau POST Form
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$amount = $input['amount'] ?? 0;
$invoice = $input['invoice'] ?? '';
$email = $input['email'] ?? '';
$name = $input['name'] ?? '';

if (!$amount || !$invoice || !$email) {
    echo json_encode(['status' => 'error', 'message' => 'Data incomplete']);
    exit;
}

try {
    $doku = new DokuHandler();
    
    // Generate URL Doku (Ambil ID unik dari Invoice)
    $unique_id = str_replace('INV-', '', $invoice);
    
    $response = $doku->createPayment($unique_id, $amount, $email, $name);

    if (isset($response['response']['payment']['url'])) {
        echo json_encode([
            'status' => 'success',
            'payment_url' => $response['response']['payment']['url']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal generate payment link DOKU',
            'debug' => $response // Hapus di production
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
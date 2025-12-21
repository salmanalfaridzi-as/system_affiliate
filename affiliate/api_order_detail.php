<?php
// affiliate/api_order_detail.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$order_id = $_GET['id'] ?? 0;

try {
    // Kita Select satu-satu fieldnya biar yakin
    $stmt = $pdo->prepare("
        SELECT 
            o.id, 
            o.invoice_number, 
            o.created_at, 
            o.status, 
            o.buyer_name, 
            o.buyer_email, 
            o.buyer_phone, 
            o.total_amount, 
            o.discount_amount, 
            o.final_amount,
            p.name as product_name, 
            c.code as coupon_code
        FROM orders o 
        JOIN products p ON o.product_id = p.id 
        LEFT JOIN coupons c ON o.coupon_id = c.id 
        WHERE o.id = ?
    ");
    
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // PERSIAPAN DATA (Biar JS gak perlu mikir)
        $response = [
            'invoice_number' => $order['invoice_number'] ?? 'INV-'.$order['id'],
            'created_at'     => date('d M Y, H:i', strtotime($order['created_at'])),
            
            // Pakai null coalescing (??) biar kalau kosong jadi strip (-)
            'buyer_name'     => $order['buyer_name'] ?? 'Guest',
            'buyer_email'    => $order['buyer_email'] ?? '-',
            'buyer_phone'    => $order['buyer_phone'] ?? '-',
            'product_name'   => $order['product_name'] ?? 'Unknown Product',
            
            // Format Rupiah
            'total_fmt'      => number_format($order['total_amount'], 0, ',', '.'),
            'discount_fmt'   => number_format($order['discount_amount'], 0, ',', '.'),
            'final_fmt'      => number_format($order['final_amount'], 0, ',', '.'),
            
            'status'         => $order['status'],
            'coupon_code'    => $order['coupon_code'] // Bisa null
        ];

        // Warna Status
        $colors = [
            'paid'      => 'success',
            'pending'   => 'warning',
            'failed'    => 'danger',
            'cancelled' => 'secondary'
        ];
        $response['status_color'] = $colors[$order['status']] ?? 'primary';

        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan di database.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
}
?>
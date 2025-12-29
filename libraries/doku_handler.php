<?php
class DokuHandler {
    private $clientId;
    private $sharedKey;
    private $isProduction;

    public function __construct() {
        // Config Doku
        $this->clientId = 'BRN-0201-1765784030963'; 
        $this->sharedKey = 'SK-Pyhg2Hs61NwIHf4mb7ol';
        $this->isProduction = false; 
    }

    // 1. Generate Signature (Support GET & POST)
    public function generateSignature($requestId, $requestTimestamp, $requestTarget, $digest) {
        $componentSignature = "Client-Id:" . $this->clientId . "\n" .
                              "Request-Id:" . $requestId . "\n" .
                              "Request-Timestamp:" . $requestTimestamp . "\n" .
                              "Request-Target:" . $requestTarget;

        // Kalau digest ada (POST), masukkan. Kalau kosong (GET), jangan.
        if (!empty($digest)) {
            $componentSignature .= "\n" . "Digest:" . $digest;
        }

        $signature = base64_encode(hash_hmac('sha256', $componentSignature, $this->sharedKey, true));
        return 'HMACSHA256=' . $signature;
    }

    // 2. Create Payment
    public function createPayment($orderId, $amount, $customerEmail, $customerName, $customUrls = []) {
        $baseUrl = $this->isProduction ? 'https://api.doku.com' : 'https://api-sandbox.doku.com';
        $path = '/checkout/v1/payment';
        $targetUrl = $baseUrl . $path;
        
        $requestId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        
        // URL Setup
        $notifyUrl = $customUrls['callback_url'] ?? BASE_URL . '/payment/notification.php'; 
        $successUrl = $customUrls['success_url']  ?? BASE_URL . '/product/mytahfidz/thankyou.php?inv=INV-' . $orderId; 
        $failedUrl  = $customUrls['failed_url']   ?? BASE_URL . '/product/mytahfidz/thankyou.php?inv=INV-' . $orderId;

        $data = [
            'order' => [
                'amount' => $amount,
                'invoice_number' => 'INV-' . $orderId,
                'currency' => 'IDR',
                'callback_url' => $successUrl, // Redirect User ke Thankyou Page
                'auto_redirect' => true,
                'failed_url' => $failedUrl,
            ],
            'payment' => [
                'payment_due_date' => 60
            ],
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail
            ]
        ];

        $jsonBody = json_encode($data, JSON_UNESCAPED_SLASHES);
        $cleanBody = str_replace("\r", "", $jsonBody);
        $digest = base64_encode(hash('sha256', $cleanBody, true));
        $signature = $this->generateSignature($requestId, $requestTimestamp, $path, $digest);

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cleanBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Client-Id: ' . $this->clientId,
            'Request-Id: ' . $requestId,
            'Request-Timestamp: ' . $requestTimestamp,
            'Signature: ' . $signature,
            'Digest: ' . $digest,
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // 3. Check Status (Support Custom Request ID)
    public function checkStatus($invoiceNumber, $customRequestId = null) {
        // Endpoint Check Status
        $path = '/orders/v1/status/INV-' . $invoiceNumber;
        
        $baseUrl = $this->isProduction ? 'https://api.doku.com' : 'https://api-sandbox.doku.com';
        $targetUrl = $baseUrl . $path;

        // LOGIC: Jika ada ID dari DB (custom), pakai itu. Jika tidak, generate random.
        $requestId = $customRequestId ?? rand(1000, 99999);
        
        $requestTimestamp = gmdate("Y-m-d\TH:i:s\Z");
        
        // Generate Signature (Digest kosong untuk GET)
        $signature = $this->generateSignature($requestId, $requestTimestamp, $path, "");

        $ch = curl_init($targetUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Client-Id: ' . $this->clientId,
            'Request-Id: ' . $requestId, // Pakai ID yang sudah ditentukan
            'Request-Timestamp: ' . $requestTimestamp,
            'Signature: ' . $signature, 
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>
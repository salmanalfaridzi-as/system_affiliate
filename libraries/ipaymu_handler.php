<?php
// File: libraries/ipaymu_handler.php

class IpaymuHandler
{
    private $apiKey;
    private $virtualAccount;
    private $isProduction;

    public function __construct()
    {
        // --- KONFIGURASI IPAYMU ---
        // Silakan ganti dengan API Key & VA dari Dashboard iPaymu
        $this->apiKey = 'SANDBOXC298D943-DB4B-4B95-A8D2-E584828FE525';
        $this->virtualAccount = '0000005742264748';
        $this->isProduction = false; // Ubah ke true saat live
    }

    // Fungsi Request API ke iPaymu
    private function callApi($endpoint, $method, $body = [])
    {
        $baseUrl = $this->isProduction ? 'https://my.ipaymu.com' : 'https://sandbox.ipaymu.com';
        $url = $baseUrl . $endpoint;

        $jsonBody     = json_encode($body, JSON_UNESCAPED_SLASHES);
        $requestBody  = strtolower(hash('sha256', $jsonBody));
        $stringToSign = strtoupper($method) . ':' . $this->virtualAccount . ':' . $requestBody . ':' . $this->apiKey;
        $signature    = hash_hmac('sha256', $stringToSign, $this->apiKey);
        $timestamp    = Date('YmdHis');

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
            'va: ' . $this->virtualAccount,
            'signature: ' . $signature,
            'timestamp: ' . $timestamp
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, ($method == 'POST'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['Success' => false, 'Message' => 'Curl Error: ' . $err];
        }

        return json_decode($response, true);
    }

    // 1. Create Payment (Redirect)
    public function createPayment($orderId, $amount, $buyerName, $buyerEmail, $buyerPhone, $customUrls = [])
    {

        $returnUrl = $customUrls['success_url'] ?? BASE_URL . '/product/mytahfidz/thankyou.php?inv=INV-' . $orderId;
        $cancelUrl = $customUrls['failed_url']  ?? BASE_URL . '/product/mytahfidz/thankyou.php?inv=INV-' . $orderId . '&status=failed';
        $notifyUrl = $customUrls['callback_url'] ?? BASE_URL . '/payment/notification.php';

        $body = [
            'product'    => ['Produk Digital'], // Nama produk (array)
            'qty'        => ['1'],
            'price'      => [$amount],
            'amount'     => $amount,
            'returnUrl'  => $returnUrl,
            'cancelUrl'  => $cancelUrl,
            'notifyUrl'  => $notifyUrl,
            'referenceId' => 'INV-' . $orderId, // ID Invoice kita
            'buyerName'  => $buyerName,
            'buyerEmail' => $buyerEmail,
            'buyerPhone' => $buyerPhone,
            // 'paymentMethod' => 'qris' // Default method (optional), kalau dihapus user milih sendiri di halaman iPaymu
        ];

        // Hit API
        return $this->callApi('/api/v2/payment', 'POST', $body);
    }

    // 2. Check Status Transaksi
    public function checkStatus($transactionId)
    {
        $body = ['transactionId' => $transactionId];
        return $this->callApi('/api/v2/transaction', 'POST', $body);
    }
}

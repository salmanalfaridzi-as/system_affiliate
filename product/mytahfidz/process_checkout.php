<?php
// File: product/mytahfidz/process_checkout.php
session_start();

// ==============================================================================
// 1. KONFIGURASI URL
// ==============================================================================
// URL API Backend (Tempat logika payment & database berada)
// Pastikan path-nya benar mengarah ke file create.php yang baru kita buat
$api_endpoint = "http://localhost/my_tahfidz_affiliator_sejoli/api/payment/create.php";

// URL Halaman Thank You (Frontend)
// Ini dikirim ke API supaya iPaymu nanti tau harus balik kemana setelah bayar
$frontend_return_url = "http://localhost/my_tahfidz_affiliator_sejoli/product/mytahfidz/thankyou.php";


// ==============================================================================
// 2. LOGIC PENGIRIMAN DATA
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Siapkan Payload (Data yang mau dikirim ke API)
    $data_to_send = [
        // Data Form Input
        'product_id'   => $_POST['product_id'] ?? 0,
        'buyer_name'   => $_POST['buyer_name'] ?? '',
        'buyer_email'  => $_POST['buyer_email'] ?? '',
        'buyer_phone'  => $_POST['buyer_phone'] ?? '',
        'password'     => $_POST['password'] ?? '', // Password user baru (jika ada)
        'coupon_code'  => $_POST['coupon_code'] ?? '',
        
        // Data Tracking (Ambil dari Cookie Browser Frontend)
        'aff_ref'      => $_COOKIE['aff_ref'] ?? '',
        'source'       => $_COOKIE['source'] ?? 'direct',
        'id_source'    => $_COOKIE['id_source'] ?? '',
        
        // URL Return (Agar API bisa setting redirect iPaymu)
        'return_url_base' => $frontend_return_url
    ];

    // B. Kirim Request ke API (Menggunakan cURL)
    $ch = curl_init($api_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_to_send));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Matikan verifikasi SSL kalau masih di localhost (PENTING!)
    // Hapus baris ini kalau sudah upload ke hosting (https)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Cek Error Koneksi cURL
    if (curl_errno($ch)) {
        die("Gagal menghubungi API Payment: " . curl_error($ch));
    }
    curl_close($ch);

    // C. Proses Jawaban dari API
    // API akan membalas dalam format JSON
    $result = json_decode($response, true);

    // Cek Status Sukses
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        
        // FITUR TAMBAHAN: AUTO LOGIN
        // Jika API mengembalikan data user, kita set session di frontend ini
        // Biar user gak perlu login ulang setelah checkout
        if (isset($result['user_data'])) {
            $_SESSION['user_id']    = $result['user_data']['id'];
            $_SESSION['user_name']  = $result['user_data']['name'];
            $_SESSION['user_email'] = $result['user_data']['email'];
            $_SESSION['user_role']  = 'affiliate'; 
        }

        // REDIRECT UTAMA: Lempar user ke halaman pembayaran iPaymu
        header("Location: " . $result['payment_url']);
        exit;

    } else {
        // Jika API menolak (Misal: Email double, Produk habis, Error sistem)
        $error_message = $result['message'] ?? 'Terjadi kesalahan yang tidak diketahui.';
        var_dump($result);die();
        // Balikin user ke halaman checkout + pesan error
        header("Location: checkout.php?error=" . urlencode($error_message));
        exit;
    }

} else {
    // Jika user coba akses file ini langsung lewat browser (bukan submit form)
    header("Location: checkout.php");
    exit;
}
?>
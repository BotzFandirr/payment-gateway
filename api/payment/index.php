<?php
// api/payment/index.php
// FILE INI BERFUNGSI SEBAGAI ROUTER (JEMBATAN) KE FILE LOGIKA ASLI

// 1. Matikan output error ke browser agar JSON bersih
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Catatan: Header JSON jangan dipasang disini, 
// karena file deposit.php/status.php sudah punya header sendiri.

// 2. Load Helper (Hanya untuk parsing URL di bawah)
// File DB akan dipanggil oleh masing-masing script (deposit.php dll)

// 3. Routing Logic (Memecah URL)
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$endpoint = end($segments); 

// Logika untuk menangkap ID di URL (contoh: status/ORD-123)
$paramId = null;
if (strpos($endpoint, 'ORD-') === 0 || is_numeric($endpoint)) {
    $paramId = $endpoint;
    $endpoint = prev($segments); // Mundur satu langkah (jadi 'status')
}

// 4. ARAHKAN KE FILE LOGIKA ASLI
// Kita gunakan require agar variabel dan logika berjalan di file target
switch ($endpoint) {

    // --- CASE 1: BUAT DEPOSIT (Real Orkut API) ---
    case 'deposit':
        // Panggil file deposit.php yang berisi integrasi API Fandirr/Orkut
        require __DIR__ . '/deposit.php';
        break;

    // --- CASE 2: CEK STATUS (Real Database & Mutation) ---
    case 'status':
        // File status.php butuh $_GET['orderId'] atau $_GET['order_id']
        // Kita suntikkan data dari URL cantik ke variabel $_GET manual
        if ($paramId) {
            $_GET['orderId'] = $paramId;
            $_GET['order_id'] = $paramId;
        }
        
        require __DIR__ . '/status.php';
        break;

    // --- CASE 3: UPDATE STATUS (Cancel/Expire) ---
    case 'update-status':
        require __DIR__ . '/update-status.php';
        break;
        
    // --- CASE 4: TRANSFER ---
    case 'transfer':
        require __DIR__ . '/transfer.php';
        break;

    // --- CASE 5: WITHDRAW ---
    case 'withdraw':
        require __DIR__ . '/withdraw.php';
        break;

    default:
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint tidak dikenali: ' . $endpoint]);
        break;
}
?>
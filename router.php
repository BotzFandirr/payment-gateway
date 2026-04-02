<?php
// router.php (ROOT)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. ROUTING KHUSUS API (PRIORITAS)
if (strpos($uri, '/api/payment/') === 0) {
    
    $apiFile = __DIR__ . '/api/payment/index.php';
    
    // Cek apakah file benar-benar ada?
    if (file_exists($apiFile)) {
        $_SERVER['SCRIPT_NAME'] = '/api/payment/index.php';
        require $apiFile;
        exit;
    } else {
        // Jika file tidak ketemu, beri pesan JSON
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            "status" => "error", 
            "message" => "System Error: File backend API tidak ditemukan di " . $apiFile
        ]);
        exit;
    }
}

// 2. STATIC FILES
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false; 
}

// 3. HALAMAN WEB
$file = __DIR__ . $uri . '.php';
if (file_exists($file)) {
    require $file;
    exit;
}

// 4. DEFAULT
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    exit;
}

http_response_code(404);
echo "404 - Halaman tidak ditemukan";
?>
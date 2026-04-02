<?php
// api/user/apikey.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// Cek Login Session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. JIKA REQUEST ADALAH POST (GENERATE BARU)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Buat Random Key 64 Karakter
        $newApiKey = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?");
        $stmt->execute([$newApiKey, $userId]);
        
        // Update Session juga biar sinkron
        $_SESSION['api_key'] = $newApiKey;

        echo json_encode(['status' => 'success', 'data' => ['api_key' => $newApiKey], 'message' => 'API Key baru berhasil dibuat.']);
        exit;
    }

    // 2. JIKA REQUEST ADALAH GET (AMBIL DATA)
    $stmt = $pdo->prepare("SELECT api_key FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode(['status' => 'success', 'data' => ['api_key' => $user->api_key]]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>

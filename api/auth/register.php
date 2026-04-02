<?php
// api/auth/register.php
session_start();
header('Content-Type: application/json');

// [PERBAIKAN] Gunakan __DIR__
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

$user_id  = trim($_POST['user_id']);
$password = $_POST['password'];
$confirm  = $_POST['password_confirm'];

if (empty($user_id) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']);
    exit;
}

if ($password !== $confirm) {
    echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok!']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'User ID sudah digunakan!']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $apiKey = bin2hex(random_bytes(32));

    $sql = "INSERT INTO users (user_id, password, api_key, role, balance) VALUES (?, ?, ?, 'user', 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $hashedPassword, $apiKey]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registrasi Berhasil! Silakan Login.',
        'redirect' => 'login'
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mendaftar: ' . $e->getMessage()]);
}
?>

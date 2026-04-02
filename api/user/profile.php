<?php
// api/user/profile.php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    // Ambil data user dari database berdasarkan ID di session
    $stmt = $pdo->prepare("SELECT user_id, balance, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'username' => $user->user_id, // Ini yang akan muncul di "Halo, ..."
                'balance' => $user->balance,   // Ini yang akan muncul di Saldo
                'role' => $user->role
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>

<?php
// api/admin/broadcast.php

// 1. TAMPUNG OUTPUT LIAR
ob_start();

session_start();
// Hapus header Content-Type di sini
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php'; 

// Cek Admin
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $target = $input['target']; 
    $username = trim($input['username'] ?? '');
    $title = trim($input['title']);
    $message = trim($input['message']);
    $type = $input['type']; 

    try {
        $count = 0;
        $msg = "";

        if ($target === 'all') {
            $stmt = $pdo->query("SELECT id FROM users WHERE role = 'user'");
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($users as $uid) {
                sendNotification($pdo, $uid, $title, $message, $type);
                $count++;
            }
            $msg = "Berhasil dikirim ke $count member.";

        } else {
            if (empty($username)) throw new Exception('Username wajib diisi.');

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                sendNotification($pdo, $user->id, $title, $message, $type);
                $msg = "Pesan terkirim ke @$username.";
            } else {
                throw new Exception("User @$username tidak ditemukan.");
            }
        }

        // 2. BERSIHKAN SAMPAH & KIRIM JSON BERSIH
        ob_clean(); 
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => $msg]);
        exit;

    } catch (Exception $e) {
        // 3. BERSIHKAN SAMPAH JIKA ERROR
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}
?>

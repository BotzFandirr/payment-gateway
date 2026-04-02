<?php
// api/admin/settings.php
ob_start(); // Mulai buffer
session_start();
require_once __DIR__ . '/../../config/db.php';

// Cek Admin
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

// Handler Request
try {
    // 1. AMBIL SETTINGS (GET)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT web_title, running_text, maintenance_mode FROM settings WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // 2. SIMPAN SETTINGS (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);
        
        $title = trim($input['web_title']);
        $text = trim($input['running_text']);
        // Pastikan maintenance_mode jadi 1 atau 0
        $maintenance = filter_var($input['maintenance_mode'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE settings SET web_title = ?, running_text = ?, maintenance_mode = ?, updated_at = NOW() WHERE id = 1");
        $stmt->execute([$title, $text, $maintenance]);

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Pengaturan berhasil disimpan!']);
        exit;
    }

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

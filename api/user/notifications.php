<?php
// api/user/notifications.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// MODE POST: Tandai semua baca
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) { 
        echo json_encode(['status' => 'error']); 
    }
    exit;
}

// MODE GET: Ambil Data
try {
    // 1. Ambil List Notifikasi (Limit 20 biar riwayat terlihat)
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // PASTIKAN DATA ADALAH ARRAY (PENTING!)
    if (!$notifs) { $notifs = []; }

    // 2. Hitung Jumlah Belum Dibaca
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmtCount->execute([$userId]);
    $unreadCount = $stmtCount->fetchColumn();

    echo json_encode([
        'status' => 'success',
        'data' => $notifs,       // Dijamin Array
        'unread' => (int)$unreadCount
    ]);

} catch (Exception $e) {
    // Jika error database, kirim array kosong agar frontend tidak blank
    echo json_encode([
        'status' => 'error', 
        'data' => [], 
        'unread' => 0,
        'message' => $e->getMessage()
    ]);
}
?>

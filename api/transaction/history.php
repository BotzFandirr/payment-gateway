<?php
// api/transaction/history.php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. UPDATE STATUS EXPIRED OTOMATIS
    $now = date('Y-m-d H:i:s');
    $pdo->prepare("UPDATE transactions SET status = 'expire', updated_at = '$now' WHERE user_id = ? AND status = 'pending' AND expiry_time < '$now'")
        ->execute([$userId]);
    
    // 2. HITUNG RINGKASAN (SUMMARY) - Hanya menghitung yang statusnya 'settlement' (Sukses)
    $stats = [];
    
    // Total Hari Ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(base_amount), 0) FROM transactions WHERE user_id = ? AND status = 'settlement' AND DATE(created_at) = CURDATE()");
    $stmt->execute([$userId]);
    $stats['today'] = $stmt->fetchColumn();

    // Total 7 Hari Terakhir (Mingguan)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(base_amount), 0) FROM transactions WHERE user_id = ? AND status = 'settlement' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$userId]);
    $stats['week'] = $stmt->fetchColumn();

    // Total 30 Hari Terakhir (Bulanan)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(base_amount), 0) FROM transactions WHERE user_id = ? AND status = 'settlement' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute([$userId]);
    $stats['month'] = $stmt->fetchColumn();

    // 3. AMBIL LIST TRANSAKSI (Limit diperbesar agar filter tab enak)
    $sql = "SELECT order_id, amount, status, created_at, qr_url, expiry_time 
            FROM transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'summary' => $stats, // Data ringkasan baru
        'data' => $transactions
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
}
?>

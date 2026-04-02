<?php
// api/admin/transactions.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// Cek Admin
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$filter = $_GET['filter'] ?? 'all'; // all, deposit, withdraw, transfer

try {
    $queries = [];

    // 1. QUERY DEPOSIT
    if ($filter === 'all' || $filter === 'deposit') {
        $queries[] = "
            SELECT 
                t.order_id AS trx_id,
                u.username AS user,
                'deposit' AS type,
                t.amount,
                'Deposit Otomatis' AS note,
                t.status,
                t.created_at
            FROM transactions t
            LEFT JOIN users u ON t.user_id = u.id
        ";
    }

    // 2. QUERY WITHDRAW
    if ($filter === 'all' || $filter === 'withdraw') {
        $queries[] = "
            SELECT 
                CONCAT('WD-', w.id) AS trx_id,
                u.username AS user,
                'withdraw' AS type,
                w.amount,
                CONCAT('Ke: ', w.bank_name, ' - ', w.account_number) AS note,
                w.status,
                w.created_at
            FROM withdrawals w
            LEFT JOIN users u ON w.user_id = u.id
        ";
    }

    // 3. QUERY TRANSFER (Sesama User)
    if ($filter === 'all' || $filter === 'transfer') {
        // Kita perlu JOIN 2 kali ke tabel users: 
        // u1 = Pengirim (Sender), u2 = Penerima (Receiver)
        $queries[] = "
            SELECT 
                CONCAT('TF-', tr.id) AS trx_id,
                u1.username AS user,
                'transfer' AS type,
                tr.amount,
                CONCAT('Ke @', u2.username, ' | ', tr.note) AS note,
                'success' AS status,
                tr.created_at
            FROM transfers tr
            LEFT JOIN users u1 ON tr.sender_id = u1.id
            LEFT JOIN users u2 ON tr.receiver_id = u2.id
        ";
    }

    // GABUNGKAN QUERY (UNION ALL)
    if (empty($queries)) {
        echo json_encode(['status' => 'success', 'data' => []]);
        exit;
    }

    $finalSql = implode(" UNION ALL ", $queries);
    $finalSql .= " ORDER BY created_at DESC LIMIT 100";

    $stmt = $pdo->query($finalSql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    // Jika tabel 'transfers' belum ada, script tidak akan mati total (hanya error message)
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
}
?>

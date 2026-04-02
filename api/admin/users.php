<?php
// api/admin/users.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

// Cek Admin
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    // 1. GET ALL USERS (FIX: Hapus 'name' dari query)
    if ($action === 'get_users') {
        // Kita ambil SEMUA user (termasuk admin) agar Anda bisa melihat akun 'fandirr' dan 'sayang'
        $stmt = $pdo->query("SELECT id, user_id, username, balance, created_at, role FROM users ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    // 2. EDIT USER
    else if ($action === 'update_user') {
        $input = json_decode(file_get_contents("php://input"), true);
        $id = $input['id'];
        $balance = $input['balance']; 
        $password = trim($input['password'] ?? '');

        $sql = "UPDATE users SET balance = ?";
        $params = [$balance];

        if (!empty($password)) {
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $pdo->prepare($sql)->execute($params);
        echo json_encode(['status' => 'success', 'message' => 'Data user berhasil diperbarui.']);
    }

    // 3. DELETE USER
    else if ($action === 'delete_user') {
        $input = json_decode(file_get_contents("php://input"), true);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$input['id']]);
        echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus permanen.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

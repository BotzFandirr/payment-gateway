<?php
// api/payment/withdraw.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php';

// Cek Auth (Wajib Session Login)
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents("php://input"), true);

// 1. Ambil & Validasi Input
$amount = isset($input['amount']) ? intval($input['amount']) : 0;
$bankName = trim($input['bank_name'] ?? '');
$accNumber = trim($input['account_number'] ?? '');
$accName = trim($input['account_name'] ?? '');

if ($amount < 10000) {
    echo json_encode(['status' => 'error', 'message' => 'Minimal penarikan Rp 10.000']); exit;
}
if (empty($bankName) || empty($accNumber) || empty($accName)) {
    echo json_encode(['status' => 'error', 'message' => 'Data bank tidak lengkap.']); exit;
}

try {
    // Mulai Transaksi Database (PENTING AGAR SALDO AMAN)
    $pdo->beginTransaction();

    // 2. Cek Saldo User (Lock Row untuk mencegah race condition)
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || $user->balance < $amount) {
        $pdo->rollBack(); // Batalkan jika saldo kurang
        echo json_encode(['status' => 'error', 'message' => 'Saldo tidak mencukupi.']); 
        exit;
    }

    // 3. Potong Saldo User DULUAN
    $updateSaldo = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $updateSaldo->execute([$amount, $userId]);

    // 4. Catat ke Tabel Withdrawals
    $sql = "INSERT INTO withdrawals (user_id, amount, bank_name, account_number, account_name, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $amount, $bankName, $accNumber, $accName]);

    // Commit Transaksi (Simpan Perubahan)
    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Permintaan penarikan berhasil dikirim. Menunggu persetujuan admin.'
    ]);

} catch (Exception $e) {
    $pdo->rollBack(); // Batalkan semua jika error
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memproses penarikan.']);
}
?>

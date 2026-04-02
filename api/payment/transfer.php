<?php
// api/payment/transfer.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$senderId = $_SESSION['user_id'];

// --- MODE 1: CEK TUJUAN (GET) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $targetUsername = trim($_GET['username'] ?? '');
    
    if (empty($targetUsername)) {
        echo json_encode(['status' => 'error', 'message' => 'Username kosong']); exit;
    }

    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->execute([$targetUsername]);
    $receiver = $stmt->fetch();

    if ($receiver) {
        if ($receiver->id == $senderId) {
            echo json_encode(['status' => 'error', 'message' => 'Tidak bisa transfer ke diri sendiri.']);
        } else {
            echo json_encode(['status' => 'success', 'data' => ['username' => $receiver->username]]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Pengguna tidak ditemukan.']);
    }
    exit;
}

// --- MODE 2: PROSES TRANSFER (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    $targetUsername = trim($input['username'] ?? '');
    $amount = isset($input['amount']) ? intval($input['amount']) : 0;
    $note = trim($input['note'] ?? '-');

    if ($amount < 1000) { // Minimal transfer 1000
        echo json_encode(['status' => 'error', 'message' => 'Minimal transfer Rp 1.000']); exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Ambil Data Pengirim & Penerima (Lock Row)
        // Cek Penerima
        $stmtRx = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmtRx->execute([$targetUsername]);
        $receiver = $stmtRx->fetch();

        if (!$receiver) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Penerima tidak valid.']); exit;
        }
        if ($receiver->id == $senderId) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Tidak bisa transfer ke diri sendiri.']); exit;
        }

        // Cek Saldo Pengirim
        $stmtSender = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmtSender->execute([$senderId]);
        $senderData = $stmtSender->fetch();

        if ($senderData->balance < $amount) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Saldo Anda tidak mencukupi.']); exit;
        }

        // 2. Eksekusi Perpindahan Saldo
        // Kurangi Pengirim
        $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$amount, $senderId]);
        // Tambah Penerima
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$amount, $receiver->id]);

        // 3. Catat Riwayat
        $stmtLog = $pdo->prepare("INSERT INTO transfers (sender_id, receiver_id, amount, note) VALUES (?, ?, ?, ?)");
        $stmtLog->execute([$senderId, $receiver->id, $amount, $note]);

        // --- TAMBAHAN: KIRIM NOTIFIKASI KE PENERIMA ---
        // Ambil nama pengirim untuk pesan
        $stmtName = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmtName->execute([$senderId]);
        $senderName = $stmtName->fetchColumn();

        $msgTitle = "Dana Masuk";
        $msgBody  = "Anda menerima Rp " . number_format($amount, 0, ',', '.') . " dari @$senderName. Catatan: $note";
        
        // Panggil fungsi dari helper.php
        sendNotification($pdo, $receiver->id, $msgTitle, $msgBody, 'success');
        // ----------------------------------------------
        
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Transfer berhasil dikirim!']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal memproses transfer.']);
    }
}
?>

<?php
// api/admin/core.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php'; // Untuk kirim notif ke user

// Cek apakah yang akses benar-benar ADMIN
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden access']);
    exit;
}

$action = $_GET['action'] ?? '';
$adminId = $_SESSION['admin_id'];

try {
    // 1. AMBIL STATISTIK DASHBOARD
    if ($action === 'get_stats') {
        // Total User
        $totalUser = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
        
        // Total Uang Mengendap (Saldo User)
        $totalBalance = $pdo->query("SELECT SUM(balance) FROM users WHERE role='user'")->fetchColumn();

        // Total Withdraw Pending
        $pendingWd = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_user' => $totalUser,
                'total_balance' => (int)$totalBalance,
                'pending_wd' => $pendingWd
            ]
        ]);
    }

    // 2. AMBIL DAFTAR WITHDRAW (PENDING)
    else if ($action === 'get_withdrawals') {
        $sql = "SELECT w.*, u.username, u.name 
                FROM withdrawals w 
                JOIN users u ON w.user_id = u.id 
                WHERE w.status = 'pending' 
                ORDER BY w.created_at ASC";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    // 3. PROSES WITHDRAW (ACC / TOLAK)
    else if ($action === 'process_withdraw') {
        $input = json_decode(file_get_contents("php://input"), true);
        $wdId = $input['id'];
        $decision = $input['decision']; // 'approve' atau 'reject'
        $reason = $input['reason'] ?? '';

        $pdo->beginTransaction();

        // Ambil data withdraw
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? FOR UPDATE");
        $stmt->execute([$wdId]);
        $wd = $stmt->fetch();

        if (!$wd || $wd->status !== 'pending') {
            throw new Exception("Data tidak valid atau sudah diproses.");
        }

        if ($decision === 'approve') {
            // Jika ACC: Ubah status jadi success
            $pdo->prepare("UPDATE withdrawals SET status = 'success', updated_at = NOW() WHERE id = ?")->execute([$wdId]);
            
            // Kirim Notif ke User
            sendNotification($pdo, $wd->user_id, "Penarikan Berhasil", "Dana Rp " . number_format($wd->amount) . " telah dikirim ke rekening Anda.", "success");
            
            echo json_encode(['status' => 'success', 'message' => 'Penarikan berhasil disetujui.']);

        } else {
            // Jika TOLAK: Kembalikan saldo user & Ubah status jadi failed
            $pdo->prepare("UPDATE withdrawals SET status = 'failed', updated_at = NOW() WHERE id = ?")->execute([$wdId]);
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$wd->amount, $wd->user_id]);
            
            // Kirim Notif ke User
            sendNotification($pdo, $wd->user_id, "Penarikan Ditolak", "Penarikan Rp " . number_format($wd->amount) . " ditolak. Alasan: $reason. Saldo dikembalikan.", "danger");

            echo json_encode(['status' => 'success', 'message' => 'Penarikan ditolak dan saldo dikembalikan.']);
        }

        $pdo->commit();
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

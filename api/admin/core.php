<?php
// api/admin/core.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php'; 

if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden access']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    // 1. AMBIL STATISTIK UTAMA (KARTU ATAS)
    if ($action === 'get_stats') {
        $totalUser = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
        $totalBalance = $pdo->query("SELECT SUM(balance) FROM users WHERE role='user'")->fetchColumn();
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

    // 2. DATA CHART 7 HARI TERAKHIR (FITUR BARU)
    else if ($action === 'get_chart_data') {
        $labels = [];
        $depositData = [];
        $withdrawData = [];

        // Loop 7 hari ke belakang (termasuk hari ini)
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d M', strtotime($date)); // Format: 21 Des

            // Hitung Total Deposit (Transactions) pada tanggal tersebut
            $stmtDep = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE DATE(created_at) = ? AND status IN ('settlement', 'success')");
            $stmtDep->execute([$date]);
            $depositData[] = (int)$stmtDep->fetchColumn();

            // Hitung Total Withdraw pada tanggal tersebut
            $stmtWd = $pdo->prepare("SELECT SUM(amount) FROM withdrawals WHERE DATE(created_at) = ? AND status = 'success'");
            $stmtWd->execute([$date]);
            $withdrawData[] = (int)$stmtWd->fetchColumn();
        }

        echo json_encode([
            'status' => 'success',
            'labels' => $labels,
            'deposit' => $depositData,
            'withdraw' => $withdrawData
        ]);
    }

    // 3. AMBIL DAFTAR WITHDRAW PENDING
    else if ($action === 'get_withdrawals') {
        // Hapus u.name agar tidak error
        $sql = "SELECT w.*, u.username 
                FROM withdrawals w 
                JOIN users u ON w.user_id = u.id 
                WHERE w.status = 'pending' 
                ORDER BY w.created_at ASC";
        $data = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    // 4. PROSES WITHDRAW
    else if ($action === 'process_withdraw') {
        $input = json_decode(file_get_contents("php://input"), true);
        $wdId = $input['id'];
        $decision = $input['decision'];
        $reason = $input['reason'] ?? '';

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ? FOR UPDATE");
        $stmt->execute([$wdId]);
        $wd = $stmt->fetch();

        if (!$wd || $wd->status !== 'pending') {
            throw new Exception("Data tidak valid atau sudah diproses.");
        }

        if ($decision === 'approve') {
            $pdo->prepare("UPDATE withdrawals SET status = 'success', updated_at = NOW() WHERE id = ?")->execute([$wdId]);
            sendNotification($pdo, $wd->user_id, "Penarikan Berhasil", "Dana Rp " . number_format($wd->amount) . " telah dikirim ke rekening Anda.", "success");
            echo json_encode(['status' => 'success', 'message' => 'Penarikan berhasil disetujui.']);

        } else {
            $pdo->prepare("UPDATE withdrawals SET status = 'failed', updated_at = NOW() WHERE id = ?")->execute([$wdId]);
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$wd->amount, $wd->user_id]);
            sendNotification($pdo, $wd->user_id, "Penarikan Ditolak", "Penarikan Rp " . number_format($wd->amount) . " ditolak. Alasan: $reason.", "danger");
            echo json_encode(['status' => 'success', 'message' => 'Penarikan ditolak dan saldo dikembalikan.']);
        }

        $pdo->commit();
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

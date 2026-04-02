<?php
// api/payment/update-status.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php';

$user = checkAuth($pdo);
$isApiCall = isset($_GET['apikey']);

$input = json_decode(file_get_contents("php://input"), true);
$orderId = $input['orderId'] ?? '';
$newStatus = $input['newStatus'] ?? '';

// Error 400 (Bad Request)
if ($newStatus !== 'cancel' && $newStatus !== 'expire') {
    http_response_code(400);
    echo json_encode(['message' => 'Aksi status tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $user->id]);
    $tx = $stmt->fetch();

    // Error 404
    if (!$tx) {
        http_response_code(404);
        echo json_encode(['message' => 'Transaksi tidak ditemukan.']);
        exit;
    }

    // Proses Update
    if ($tx->status === 'pending') {
        $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$newStatus, $tx->id]);
        
        // Refresh Data
        $stmt->execute([$orderId, $user->id]);
        $tx = $stmt->fetch();
    }

    // Respon Sukses (200 OK)
    if ($isApiCall) {
        $response = [
            "_id" => (string)$tx->id,
            "orderId" => $tx->order_id,
            "paymentId" => $tx->payment_id,
            "user" => (string)$tx->user_id,
            "baseAmount" => (int)$tx->base_amount,
            "adminFee" => (int)$tx->admin_fee,
            "uniqueCode" => (int)$tx->unique_code,
            "amount" => (int)$tx->amount,
            "status" => $tx->status,
            "createdAt" => date('c', strtotime($tx->created_at)),
            "updatedAt" => date('c', strtotime($tx->updated_at)),
            "__v" => 0 // Tambahan agar sama persis
        ];

        if ($newStatus === 'cancel') {
            $response['mutationId'] = null;
        }

        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memperbarui status.']);
}
?>

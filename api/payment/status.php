<?php
// api/payment/status.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta'); // Wajib: Agar waktu konsisten WIB

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php';

$user = checkAuth($pdo);
$isApiCall = isset($_GET['apikey']);
$orderId = $_GET['orderId'] ?? $_GET['order_id'] ?? null;

// Validasi Order ID
if (!$orderId) {
    http_response_code(400);
    echo json_encode(['message' => 'Transaksi tidak ditemukan atau bukan milik Anda.']);
    exit;
}

try {
    // 1. Ambil Transaksi
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$orderId, $user->id]);
    $tx = $stmt->fetch();

    if (!$tx) {
        http_response_code(404);
        echo json_encode(['message' => 'Transaksi tidak ditemukan atau bukan milik Anda.']);
        exit;
    }

    // 2. Logika Cek Status (Hanya jika masih 'pending')
    if ($tx->status === 'pending') {
        
        // A. Cek Expired
        if (strtotime($tx->expiry_time) < time()) {
            // Update status ke 'expire' DAN update waktu 'updated_at'
            $pdo->prepare("UPDATE transactions SET status = 'expire', updated_at = NOW() WHERE id = ?")->execute([$tx->id]);
            
            // Update variabel lokal agar JSON response menampilkan data terbaru
            $tx->status = 'expire';
            $tx->updated_at = date('Y-m-d H:i:s'); 
        } 
        else {
            // B. Cek API Orkut (Mutasi Bank)
            $orkutId = "2136640"; 
            $orkutUsername = "aniardilla"; 
            $orkutToken = "2136640:W6nCxDeqwf9YcPkQZVzauhMSTFJv3pX1";
            
            $checkUrl = "https://apis.fandir.eu.org/api/orkut/cekstatus?username=$orkutUsername&token=$orkutToken";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $checkUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
    
            $mutationData = json_decode($response, true);
    
            if ($mutationData && isset($mutationData['status']) && $mutationData['status'] === true) {
                foreach ($mutationData['data'] as $mutasi) {
                    $jumlahMasuk = (float) preg_replace('/[^0-9]/', '', $mutasi['kredit']);
                    
                    // Syarat: Status IN & Jumlah Cocok
                    if ($mutasi['status'] === 'IN' && $jumlahMasuk == $tx->amount) {
                        
                        // Cek Double Claim
                        $cek = $pdo->prepare("SELECT id FROM transactions WHERE mutation_id = ?");
                        $cek->execute([$mutasi['id']]);
                        
                        if ($cek->rowCount() == 0) {
                            $pdo->beginTransaction();
                            
                            // Update Transaksi -> Settlement
                            $upd = $pdo->prepare("UPDATE transactions SET status = 'settlement', mutation_id = ?, updated_at = NOW() WHERE id = ?");
                            $upd->execute([$mutasi['id'], $tx->id]);
                            
                            // Tambah Saldo User
                            $saldoMasuk = $tx->base_amount + $tx->admin_fee;
                            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$saldoMasuk, $user->id]);
    
                            $pdo->commit();
                            
                            // Update variabel lokal
                            $tx->status = 'settlement';
                            $tx->mutation_id = $mutasi['id'];
                            $tx->updated_at = date('Y-m-d H:i:s');
                            break;
                        }
                    }
                }
            }
        }
    }

    // 3. Output JSON
    if ($isApiCall) {
        // Format Khusus API (Sesuai Dokumentasi)
        $response = [
            "_id" => (string)$tx->id,
            "orderId" => $tx->order_id,
            "user" => (string)$tx->user_id,
            "baseAmount" => (int)$tx->base_amount,
            "adminFee" => (int)$tx->admin_fee,
            "uniqueCode" => (int)$tx->unique_code,
            "amount" => (int)$tx->amount,
            "status" => $tx->status,
            "paymentId" => $tx->payment_id,
            "createdAt" => date('c', strtotime($tx->created_at)), // ISO 8601
            "updatedAt" => date('c', strtotime($tx->updated_at))  // ISO 8601
        ];
        
        if($tx->mutation_id) {
            $response["mutationId"] = $tx->mutation_id;
        }

        echo json_encode($response);

    } else {
        // Format Khusus Dashboard
        echo json_encode(['status' => 'success', 'data' => $tx]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memeriksa status transaksi.']);
}
?>

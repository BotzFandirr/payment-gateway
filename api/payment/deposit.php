<?php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../helper.php';

$user = checkAuth($pdo);
$isApiCall = isset($_GET['apikey']);

$input = json_decode(file_get_contents("php://input"), true);
$amount = isset($input['amount']) ? intval($input['amount']) : 0;
$fee    = isset($input['fee']) ? intval($input['fee']) : 0;

if ($amount < 100) {
    http_response_code(400);
    echo json_encode(['message' => 'Nominal deposit tidak valid (minimal Rp100).']);
    exit;
}

try {
    $uniqueCode = rand(70, 150);
    $amountToPay = $amount + $fee + $uniqueCode;
    $orderId = "ORD-" . substr(md5(uniqid()), 0, 4) . "-" . round(microtime(true) * 1000);


    $orkutCodeQr = "00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214517164986365250303UMI51440014ID.CO.QRIS.WWW0215ID20243618325510303UMI5204541153033605802ID5919ANI STORE OK21366406009INDRAMAYU61054521162070703A016304E4B9"; 
    $apiUrl = "https://apis.fandir.eu.org/api/orkut/createpayment?" . http_build_query([
        'amount' => $amountToPay,
        'codeqr' => $orkutCodeQr
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $apiResult = json_decode($response, true);

    if (!$apiResult || !isset($apiResult['status']) || $apiResult['status'] !== true) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Gagal membuat QRIS dari provider."
        ]);
        exit;
    }

    $paymentData = $apiResult['result'];
    $expiryMySql = date('Y-m-d H:i:s', strtotime($paymentData['expirationTime']));

    $sql = "INSERT INTO transactions 
            (user_id, order_id, payment_id, base_amount, admin_fee, unique_code, amount, status, qr_url, expiry_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user->id, $orderId, $paymentData['transactionId'] ?? 'QR-MANUAL', 
        $amount, $fee, $uniqueCode, $amountToPay, 
        $paymentData['qrImageUrl'], $expiryMySql
    ]);

    if ($isApiCall) {
        echo json_encode([
            "status" => "success",
            "message" => "Permintaan deposit berhasil dibuat.",
            "data" => [
                "orderId" => $orderId,
                "baseAmount" => (int)$amount,
                "adminFee" => (int)$fee,
                "uniqueCode" => $uniqueCode,
                "amountToPay" => $amountToPay,
                "qrCodeUrl" => $paymentData['qrImageUrl'],
                "expiryTime" => date('c', strtotime($expiryMySql))
            ]
        ]);
    } else {

        echo json_encode([
            'status' => 'success',
            'data' => [
                'order_id' => $orderId,
                'amount_total' => $amountToPay,
                'qr_url' => $paymentData['qrImageUrl'],
                'expiry_time' => $paymentData['expirationTime']
            ]
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal membuat QRIS dari provider."
    ]);
}
?>
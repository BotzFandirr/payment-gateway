<?php
// api/helper.php

function checkAuth($pdo) {
    // 1. Cek Session (Prioritas untuk Dashboard Internal)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user_id'])) {
        // Jika ada session, validasi user dari database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) return $user; // Auth Berhasil via Session
    }

    // 2. Cek API Key (Untuk Integrasi Pihak Ketiga)
    if (isset($_GET['apikey'])) {
        $apiKey = trim($_GET['apikey']);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE api_key = ?");
        $stmt->execute([$apiKey]);
        $user = $stmt->fetch();
        
        if ($user) return $user; // Auth Berhasil via API Key
    }

    // 3. Jika Keduanya Gagal
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Gagal: Tidak terautentikasi, API Key tidak valid.']);
    exit;
}

// Helper Format Tanggal ISO 8601 (2025-11-11T...)
function formatIsoDate($dateString) {
    if (empty($dateString)) return null;
    return date('Y-m-d\TH:i:s.v\Z', strtotime($dateString));
}

// Tambahkan di api/helper.php

function sendNotification($pdo, $userId, $title, $message, $type = 'info') {
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $message, $type]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
<?php
// api/auth/login.php

// 1. BUFFER OUTPUT (Pencegah Error Spasi/Whitespace)
ob_start();

session_start();
require_once __DIR__ . '/../../config/db.php';

// Pastikan respon selalu JSON (ditaruh setelah logic header lain aman)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
    exit;
}

// 2. TANGKAP INPUT (Mendukung JSON dari Frontend JS & Form biasa)
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    $input = $_POST; // Fallback jika pakai form biasa
}

// Ambil data (Mendukung key 'user_id' atau 'username')
$login_id = trim($input['user_id'] ?? $input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($login_id) || empty($password)) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'User ID dan Password wajib diisi.']);
    exit;
}

try {
    // 3. CEK MODE MAINTENANCE (Fitur Baru)
    // Kita cek dulu apakah website sedang dikunci admin
    $stmtSettings = $pdo->query("SELECT maintenance_mode FROM settings WHERE id = 1");
    $settings = $stmtSettings->fetch();

    if ($settings && $settings->maintenance_mode == 1) {
        // Cek apakah yang login adalah admin? (Opsional: Admin boleh lewat)
        // Disini kita cek dulu user-nya
        $stmtUser = $pdo->prepare("SELECT role, password FROM users WHERE user_id = ?");
        $stmtUser->execute([$login_id]);
        $cekAdmin = $stmtUser->fetch();

        // Jika user tidak ditemukan ATAU bukan admin, tolak akses
        if (!$cekAdmin || $cekAdmin->role !== 'admin') {
            ob_clean();
            echo json_encode([
                'status' => 'error', 
                'message' => '⚠️ Sistem sedang Maintenance. Silakan coba beberapa saat lagi.'
            ]);
            exit;
        }
    }

    // 4. PROSES LOGIN UTAMA
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$login_id]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user->password)) {
        // Set Session
        $_SESSION['user_id']  = $user->id;
        $_SESSION['username'] = $user->user_id; // Simpan username/user_id
        $_SESSION['role']     = $user->role;
        $_SESSION['api_key']  = $user->api_key;
        
        // Tentukan Redirect berdasarkan Role
        $redirectUrl = ($user->role === 'admin') ? '../admin/dashboard' : 'dashboard';

        ob_clean(); // Bersihkan sampah output sebelum kirim JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'redirect' => $redirectUrl,
            'role' => $user->role
        ]);
    } else {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'User ID atau Password salah!']);
    }

} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>

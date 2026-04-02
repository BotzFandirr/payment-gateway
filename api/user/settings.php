<?php
// api/user/settings.php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? '';

try {
    // --- ACTION 1: UPDATE PROFIL (USERNAME) ---
    if ($action === 'update_profile') {
        $newUsername = trim($input['username'] ?? '');
        
        // Validasi Sederhana
        if (empty($newUsername) || strlen($newUsername) < 3) {
            echo json_encode(['status' => 'error', 'message' => 'Username minimal 3 karakter.']); exit;
        }

        // Cek apakah username sudah dipakai orang lain
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$newUsername, $userId]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan user lain.']); exit;
        }

        // Update Database
        $pdo->prepare("UPDATE users SET username = ? WHERE id = ?")->execute([$newUsername, $userId]);
        
        echo json_encode(['status' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    } 
    
    // --- ACTION 2: GANTI PASSWORD ---
    else if ($action === 'change_password') {
        $oldPass = $input['old_password'] ?? '';
        $newPass = $input['new_password'] ?? '';

        if (strlen($newPass) < 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password baru minimal 6 karakter.']); exit;
        }

        // Ambil password lama dari DB untuk verifikasi
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!password_verify($oldPass, $user->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Password lama salah.']); exit;
        }

        // Hash password baru & Simpan
        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $userId]);

        echo json_encode(['status' => 'success', 'message' => 'Password berhasil diubah. Silakan login ulang.']);
    } 
    
    else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak valid.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>

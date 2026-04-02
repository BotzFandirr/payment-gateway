<?php
// api/auth/register.php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // 1. Ambil Data Input
    $user_id  = trim($_POST['user_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    // 2. Validasi Dasar
    if (empty($user_id) || empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']); exit;
    }
    
    // 3. Validasi Password (Minimal 6 Karakter)
    if (strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password terlalu pendek (minimal 6 karakter)!']); exit;
    }

    if ($password !== $confirm) {
        echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok!']); exit;
    }

    // 4. Validasi Username (Hanya huruf, angka, garis bawah)
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username hanya boleh huruf & angka (3-20 karakter) tanpa spasi.']); exit;
    }

    try {
        // 5. Cek Duplikasi
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ? OR username = ?");
        $stmt->execute([$user_id, $username]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'User ID atau Username sudah digunakan!']); exit;
        }

        // 6. Hash Password & Simpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $apiKey = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users (user_id, username, password, api_key, role, balance) VALUES (?, ?, ?, ?, 'user', 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $username, $hashedPassword, $apiKey]);

        echo json_encode(['status' => 'success', 'message' => 'Akun berhasil dibuat!', 'redirect' => 'login']);
        exit;

    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            echo json_encode(['status' => 'error', 'message' => 'Error: Kolom database belum diupdate.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Fandirr Pay</title>
    <?php include 'layout/header-meta.php'; ?>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 45px; /* Memberi ruang agar teks tidak tertutup ikon */
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted); /* Mengambil warna dari variabel CSS Anda */
            font-size: 1.2rem;
            transition: color 0.3s;
            z-index: 10;
        }
        .toggle-password:hover {
            color: var(--primary); /* Berubah warna saat di-hover */
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-box" data-aos="fade-up" data-aos-duration="800">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.8rem; font-weight: 700;">Buat Akun Baru</h1>
                <p style="color: var(--text-muted);">Gratis, cepat, dan aman.</p>
            </div>

            <form id="registerForm">
                <div class="form-group">
                    <label class="form-label">User ID (Untuk Login)</label>
                    <input type="text" name="user_id" class="form-control" placeholder="Contoh: fandirr01" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username (Nama Unik)</label>
                    <input type="text" name="username" class="form-control" placeholder="Contoh: fandirr (Tanpa spasi)" required>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">Digunakan orang lain untuk mengirim saldo ke Anda.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        <i class="ri-eye-off-line toggle-password" onclick="togglePassword('password', this)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password_confirm" id="confirm_password" class="form-control" placeholder="Ulangi password" required>
                        <i class="ri-eye-off-line toggle-password" onclick="togglePassword('confirm_password', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar Sekarang</button>
                
                <div class="auth-footer">
                    Sudah punya akun? <a href="login">Login di sini</a>
                </div>
            </form>
        </div>
    </div>

    <div id="modalLoading" class="modal-overlay">
        <div class="modal-box">
            <div class="spinner-wrapper"><div class="loading-spinner"></div></div>
            <h3 class="modal-title">Mendaftarkan...</h3>
            <p class="modal-message">Sedang membuat akun Anda.</p>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-shield-check-line"></i></div>
            <h3 class="modal-title">Registrasi Berhasil!</h3>
            <p class="modal-message" id="successMsg">Mengalihkan...</p>
            <button class="modal-btn" type="button" onclick="window.location.href='login'">Masuk Sekarang</button>
        </div>
    </div>

    <div id="modalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-error-warning-line"></i></div>
            <h3 class="modal-title">Gagal Mendaftar</h3>
            <p class="modal-message" id="errorMsg">Cek data Anda.</p>
            <button class="modal-btn" type="button" onclick="document.getElementById('modalError').classList.remove('active')">Perbaiki</button>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        // 1. FUNGSI TOGGLE PASSWORD (MATA)
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.classList.remove('ri-eye-off-line');
                iconElement.classList.add('ri-eye-line');
            } else {
                input.type = "password";
                iconElement.classList.remove('ri-eye-line');
                iconElement.classList.add('ri-eye-off-line');
            }
        }

        // 2. LOGIKA SUBMIT FORM
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const modalLoading = document.getElementById('modalLoading');
            const modalSuccess = document.getElementById('modalSuccess');
            const modalError = document.getElementById('modalError');

            modalLoading.classList.add('active');
            const formData = new FormData(this);

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                setTimeout(() => {
                    modalLoading.classList.remove('active');
                    if (data.status === 'success') {
                        document.getElementById('successMsg').innerText = data.message;
                        modalSuccess.classList.add('active');
                        setTimeout(() => { window.location.href = data.redirect; }, 2000);
                    } else {
                        document.getElementById('errorMsg').innerText = data.message;
                        modalError.classList.add('active');
                    }
                }, 1000);

            } catch (error) {
                modalLoading.classList.remove('active');
                document.getElementById('errorMsg').innerText = "Terjadi kesalahan sistem.";
                modalError.classList.add('active');
            }
        });
    </script>
</body>
</html>

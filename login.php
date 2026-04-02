<?php
// api/auth/login.php
session_start();
// Gunakan __DIR__ untuk keamanan path relative
require_once __DIR__ . '/config/db.php';

// Jika request adalah POST (Logika Backend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $user_id  = trim($_POST['user_id']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user_id']  = $user->id;
            $_SESSION['username'] = $user->user_id;
            $_SESSION['role']     = $user->role;
            $_SESSION['api_key']  = $user->api_key;
            
            echo json_encode(['status' => 'success', 'message' => 'Login berhasil!', 'redirect' => 'dashboard']);
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User ID atau Password salah!']);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Fandirr Pay</title>
    <?php include 'layout/header-meta.php'; ?>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 45px; /* Ruang agar teks tidak tertutup ikon */
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            font-size: 1.2rem;
            transition: color 0.3s;
            z-index: 10;
        }
        .toggle-password:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="glass-card auth-box" data-aos="zoom-in" data-aos-duration="800">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.8rem; font-weight: 700;">Welcome Back</h1>
                <p style="color: var(--text-muted);">Masuk untuk kelola aset digitalmu</p>
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label class="form-label">User ID</label>
                    <input type="text" name="user_id" class="form-control" placeholder="Masukkan ID Pengguna" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" required>
                        <i class="ri-eye-off-line toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Masuk Sekarang</button>
                
                <div class="auth-footer">
                    Belum punya akun? <a href="register">Daftar sekarang</a>
                </div>
            </form>
        </div>
    </div>

    <div id="modalLoading" class="modal-overlay">
        <div class="modal-box">
            <div class="spinner-wrapper"><div class="loading-spinner"></div></div>
            <h3 class="modal-title">Memproses...</h3>
            <p class="modal-message">Mohon tunggu sebentar.</p>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-check-line"></i></div>
            <h3 class="modal-title">Login Berhasil!</h3>
            <p class="modal-message" id="successMsg">Mengalihkan ke dashboard...</p>
        </div>
    </div>

    <div id="modalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-close-line"></i></div>
            <h3 class="modal-title">Gagal Masuk</h3>
            <p class="modal-message" id="errorMsg">Terjadi kesalahan.</p>
            <button class="modal-btn" type="button" onclick="document.getElementById('modalError').classList.remove('active')">Coba Lagi</button>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        // 1. FUNGSI TOGGLE PASSWORD
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('ri-eye-off-line');
                icon.classList.add('ri-eye-line');
            } else {
                input.type = "password";
                icon.classList.remove('ri-eye-line');
                icon.classList.add('ri-eye-off-line');
            }
        }

        // 2. LOGIKA LOGIN
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const modalLoading = document.getElementById('modalLoading');
            const modalSuccess = document.getElementById('modalSuccess');
            const modalError = document.getElementById('modalError');
            
            modalLoading.classList.add('active');

            const formData = new FormData(this);

            try {
                // Fetch ke diri sendiri
                const response = await fetch('login', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                setTimeout(() => {
                    modalLoading.classList.remove('active');

                    if (data.status === 'success') {
                        document.getElementById('successMsg').innerText = data.message;
                        modalSuccess.classList.add('active');
                        setTimeout(() => { window.location.href = data.redirect; }, 1500);
                    } else {
                        document.getElementById('errorMsg').innerText = data.message;
                        modalError.classList.add('active');
                    }
                }, 800); 

            } catch (error) {
                modalLoading.classList.remove('active');
                document.getElementById('errorMsg').innerText = "Terjadi kesalahan sistem.";
                modalError.classList.add('active');
            }
        });
    </script>
</body>
</html>

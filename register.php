<?php
session_start();
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $user_id  = trim($_POST['user_id'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';

    if (empty($user_id) || empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi!']); exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'Password terlalu pendek (minimal 6 karakter)!']); exit;
    }

    if ($password !== $confirm) {
        echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password tidak cocok!']); exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username hanya boleh huruf & angka (3-20 karakter) tanpa spasi.']); exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ? OR username = ?");
        $stmt->execute([$user_id, $username]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'User ID atau Username sudah digunakan!']); exit;
        }

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
        .register-layout {
            width: min(1100px, 100%);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 1.2rem;
        }
        .register-side {
            background: linear-gradient(140deg, rgba(240,196,25,.18), rgba(24,24,27,.15));
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .side-list { display: grid; gap: 1rem; margin-top: 1.2rem; }
        .side-item { display: flex; gap: .7rem; align-items: flex-start; color: #d4d4d8; font-size: .92rem; }
        .side-item i { color: var(--primary); margin-top: .1rem; }

        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 45px; }
        .toggle-password {
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: var(--text-muted); font-size: 1.2rem; transition: color 0.3s; z-index: 10;
        }
        .toggle-password:hover { color: var(--primary); }

        .meter-wrap { margin-top: .6rem; }
        .meter-bar {
            height: 8px; border-radius: 99px; background: rgba(255,255,255,.08); overflow: hidden;
        }
        .meter-fill {
            width: 0%; height: 100%; border-radius: 99px; transition: .2s ease;
            background: #ef4444;
        }
        .meter-label { margin-top: .45rem; font-size: .78rem; color: var(--text-muted); }

        .terms {
            display: flex; align-items: flex-start; gap: .6rem; font-size: .84rem; color: var(--text-muted);
            margin-bottom: 1.1rem;
        }
        .terms input { margin-top: .2rem; accent-color: var(--primary); }

        @media (max-width: 920px) {
            .register-layout { grid-template-columns: 1fr; }
            .register-side { order: 2; }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="register-layout">
            <div class="register-side" data-aos="fade-right" data-aos-duration="800">
                <div>
                    <span style="font-size:.75rem;color:var(--primary);font-weight:700;letter-spacing:.08em;">WELCOME TO FANDIRR PAY</span>
                    <h2 style="font-size:2rem;margin:.6rem 0 0;">Mulai terima pembayaran online dalam hitungan menit.</h2>
                    <p style="color:var(--text-muted);margin-top:.7rem;line-height:1.65;">Buat akun merchant, dapatkan API key otomatis, dan pantau transaksi dari dashboard modern kami.</p>

                    <div class="side-list">
                        <div class="side-item"><i class="ri-shield-check-line"></i><span>Keamanan berlapis dengan enkripsi modern.</span></div>
                        <div class="side-item"><i class="ri-flashlight-line"></i><span>Settlement cepat & status transaksi realtime.</span></div>
                        <div class="side-item"><i class="ri-code-s-slash-line"></i><span>Dokumentasi API siap pakai untuk developer.</span></div>
                    </div>
                </div>
                <a href="index" class="btn btn-outline" style="width:max-content;"><i class="ri-arrow-left-line"></i> Kembali ke Landing</a>
            </div>

            <div class="glass-card auth-box" data-aos="fade-up" data-aos-duration="800" style="max-width:none;">
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
                        <small style="color: var(--text-muted); font-size: 0.8rem;">Dipakai orang lain untuk mengirim saldo ke Anda.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                            <i class="ri-eye-off-line toggle-password" onclick="togglePassword('password', this)"></i>
                        </div>
                        <div class="meter-wrap">
                            <div class="meter-bar"><div id="pass-meter-fill" class="meter-fill"></div></div>
                            <div id="pass-meter-label" class="meter-label">Kekuatan password: -</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirm" id="confirm_password" class="form-control" placeholder="Ulangi password" required>
                            <i class="ri-eye-off-line toggle-password" onclick="togglePassword('confirm_password', this)"></i>
                        </div>
                    </div>

                    <label class="terms">
                        <input type="checkbox" id="termsCheck" required>
                        <span>Saya menyetujui syarat layanan dan kebijakan privasi Fandirr Pay.</span>
                    </label>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Daftar Sekarang</button>

                    <div class="auth-footer">
                        Sudah punya akun? <a href="login">Login di sini</a>
                    </div>
                </form>
            </div>
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

        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconElement.classList.remove('ri-eye-off-line');
                iconElement.classList.add('ri-eye-line');
            } else {
                input.type = 'password';
                iconElement.classList.remove('ri-eye-line');
                iconElement.classList.add('ri-eye-off-line');
            }
        }

        function updatePasswordMeter(value) {
            const fill = document.getElementById('pass-meter-fill');
            const label = document.getElementById('pass-meter-label');
            let score = 0;
            if (value.length >= 6) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            const map = [
                {w: 10, c: '#ef4444', t: 'Lemah'},
                {w: 30, c: '#f97316', t: 'Cukup'},
                {w: 55, c: '#eab308', t: 'Menengah'},
                {w: 80, c: '#22c55e', t: 'Kuat'},
                {w: 100, c: '#10b981', t: 'Sangat Kuat'}
            ][score];

            fill.style.width = map.w + '%';
            fill.style.background = map.c;
            label.textContent = 'Kekuatan password: ' + map.t;
        }

        document.getElementById('password').addEventListener('input', (e) => updatePasswordMeter(e.target.value));

        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!document.getElementById('termsCheck').checked) {
                document.getElementById('errorMsg').innerText = 'Anda harus menyetujui syarat layanan terlebih dahulu.';
                document.getElementById('modalError').classList.add('active');
                return;
            }

            const modalLoading = document.getElementById('modalLoading');
            const modalSuccess = document.getElementById('modalSuccess');
            const modalError = document.getElementById('modalError');

            modalLoading.classList.add('active');
            const formData = new FormData(this);

            try {
                const response = await fetch('register.php', { method: 'POST', body: formData });
                const data = await response.json();

                setTimeout(() => {
                    modalLoading.classList.remove('active');
                    if (data.status === 'success') {
                        document.getElementById('successMsg').innerText = data.message;
                        modalSuccess.classList.add('active');
                        setTimeout(() => { window.location.href = data.redirect; }, 1800);
                    } else {
                        document.getElementById('errorMsg').innerText = data.message;
                        modalError.classList.add('active');
                    }
                }, 650);
            } catch (error) {
                modalLoading.classList.remove('active');
                document.getElementById('errorMsg').innerText = 'Terjadi kesalahan sistem.';
                modalError.classList.add('active');
            }
        });
    </script>
</body>
</html>

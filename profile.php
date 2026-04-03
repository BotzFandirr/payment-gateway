<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

$stmt = $pdo->prepare("SELECT user_id, username, role, api_key FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login');
    exit;
}

$displayName = $user->username ?: $user->user_id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile - Fandirr Pay</title>
    <?php include 'layout/header-meta.php'; ?>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .profile-wrap { max-width: 860px; margin: 0 auto; padding: 2rem 1rem 4rem; }
        .profile-header {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .profile-identity { display: flex; align-items: center; gap: 1rem; }
        .avatar {
            width: 56px; height: 56px; border-radius: 50%; background: var(--primary);
            color: black; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        }
        .section-title { margin-bottom: 1rem; font-size: 1.05rem; }
        .stack { display: grid; gap: 1rem; }
        .btn-row { display: flex; gap: .8rem; flex-wrap: wrap; }
        .top-actions { display: flex; gap: .6rem; flex-wrap: wrap; }
        .readonly-box {
            width: 100%; padding: 1rem 1.2rem; border-radius: 12px;
            border: 1px solid var(--border-glass); background: rgba(0,0,0,.25);
            color: var(--text-main); font-family: monospace; word-break: break-all;
        }
        .helper { font-size: .82rem; color: var(--text-muted); }
        @media (max-width: 600px) {
            .profile-header { flex-direction: column; align-items: flex-start; }
            .top-actions { width: 100%; }
            .top-actions .btn { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="profile-wrap">
        <div class="profile-header">
            <div class="profile-identity">
                <div class="avatar"><i class="ri-user-fill"></i></div>
                <div>
                    <h2 style="margin-bottom:.2rem;">@<?php echo htmlspecialchars($displayName); ?></h2>
                    <p style="color:var(--text-muted); font-size:.9rem;">Role: <?php echo htmlspecialchars($user->role); ?></p>
                </div>
            </div>
            <div class="top-actions">
                <a href="dashboard" class="btn btn-outline"><i class="ri-arrow-left-line"></i> Dashboard</a>
                <button class="btn btn-primary" onclick="showConfirmLogout()"><i class="ri-logout-box-r-line"></i> Keluar</button>
            </div>
        </div>

        <div class="glass-card" style="margin-bottom:1rem;">
            <h3 class="section-title"><i class="ri-user-settings-line" style="color:var(--primary);"></i> Pengaturan Profil</h3>
            <div class="stack">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Username</label>
                    <input id="username" class="form-control" type="text" value="<?php echo htmlspecialchars($displayName); ?>" minlength="3">
                    <p class="helper">Dipakai untuk identitas transfer internal.</p>
                </div>
                <button class="btn btn-primary" onclick="updateProfile()">Simpan Profil</button>
            </div>
        </div>

        <div class="glass-card" style="margin-bottom:1rem;">
            <h3 class="section-title"><i class="ri-lock-password-line" style="color:var(--primary);"></i> Ubah Password</h3>
            <div class="stack">
                <input id="oldPassword" class="form-control" type="password" placeholder="Password lama">
                <input id="newPassword" class="form-control" type="password" placeholder="Password baru (min. 6 karakter)">
                <button class="btn btn-outline" onclick="changePassword()">Update Password</button>
            </div>
        </div>

        <div class="glass-card">
            <h3 class="section-title"><i class="ri-key-2-line" style="color:var(--primary);"></i> API Key</h3>
            <div class="stack">
                <div id="apiKey" class="readonly-box"><?php echo htmlspecialchars($user->api_key); ?></div>
                <div class="btn-row">
                    <button class="btn btn-outline" onclick="copyApiKey()"><i class="ri-file-copy-line"></i> Salin</button>
                    <button class="btn btn-primary" onclick="regenerateApiKey()"><i class="ri-refresh-line"></i> Generate Baru</button>
                </div>
            </div>
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
            <h3 class="modal-title" id="successTitle">Berhasil</h3>
            <p class="modal-message" id="successMsg">Aksi berhasil diproses.</p>
            <button class="modal-btn" type="button" onclick="closeModal('modalSuccess')">Tutup</button>
        </div>
    </div>

    <div id="modalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-error-warning-line"></i></div>
            <h3 class="modal-title" id="errorTitle">Terjadi Kesalahan</h3>
            <p class="modal-message" id="errorMsg">Silakan coba lagi.</p>
            <button class="modal-btn" type="button" onclick="closeModal('modalError')">Tutup</button>
        </div>
    </div>

    <div id="modalConfirm" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon-wrapper" style="background: rgba(245, 158, 11, 0.2); color: #F59E0B;">
                <i class="ri-question-line"></i>
            </div>
            <h3 class="modal-title" id="confirmTitle">Konfirmasi</h3>
            <p class="modal-message" id="confirmMsg">Apakah Anda yakin?</p>
            <div style="display:flex; gap:.6rem; justify-content:center;">
                <button class="modal-btn" type="button" style="background:transparent; border:1px solid var(--border-glass); color:white;" onclick="closeModal('modalConfirm')">Batal</button>
                <button class="modal-btn" type="button" id="confirmYesBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        const requestJson = async (url, payload = null, method = 'POST') => {
            const opts = { method, headers: { 'Content-Type': 'application/json' } };
            if (payload) opts.body = JSON.stringify(payload);
            const res = await fetch(url, opts);
            return res.json();
        };

        const closeModal = (id) => document.getElementById(id).classList.remove('active');
        const showLoading = () => document.getElementById('modalLoading').classList.add('active');
        const hideLoading = () => document.getElementById('modalLoading').classList.remove('active');

        const showSuccess = (title, message) => {
            document.getElementById('successTitle').innerText = title;
            document.getElementById('successMsg').innerText = message;
            document.getElementById('modalSuccess').classList.add('active');
        };

        const showError = (title, message) => {
            document.getElementById('errorTitle').innerText = title;
            document.getElementById('errorMsg').innerText = message;
            document.getElementById('modalError').classList.add('active');
        };

        const showConfirm = (title, message, onYes) => {
            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmMsg').innerText = message;

            const oldBtn = document.getElementById('confirmYesBtn');
            const newBtn = oldBtn.cloneNode(true);
            oldBtn.parentNode.replaceChild(newBtn, oldBtn);
            newBtn.addEventListener('click', () => {
                closeModal('modalConfirm');
                onYes();
            });

            document.getElementById('modalConfirm').classList.add('active');
        };

        async function updateProfile() {
            const username = document.getElementById('username').value.trim();
            if (username.length < 3) {
                showError('Validasi Gagal', 'Username minimal 3 karakter.');
                return;
            }
            showLoading();
            const result = await requestJson('api/user/settings.php?action=update_profile', { username });
            hideLoading();
            if (result.status === 'success') {
                showSuccess('Profil Diperbarui', result.message || 'Perubahan berhasil disimpan.');
                setTimeout(() => location.reload(), 900);
            } else {
                showError('Gagal Update Profil', result.message || 'Terjadi kesalahan.');
            }
        }

        async function changePassword() {
            const old_password = document.getElementById('oldPassword').value;
            const new_password = document.getElementById('newPassword').value;
            if (new_password.length < 6) {
                showError('Validasi Gagal', 'Password baru minimal 6 karakter.');
                return;
            }
            showLoading();
            const result = await requestJson('api/user/settings.php?action=change_password', { old_password, new_password });
            hideLoading();
            if (result.status === 'success') {
                showSuccess('Password Berhasil Diubah', result.message || 'Silakan login ulang.');
                setTimeout(() => window.location.href = 'login', 1000);
            } else {
                showError('Gagal Ubah Password', result.message || 'Terjadi kesalahan.');
            }
        }

        async function regenerateApiKey() {
            showConfirm('Generate API Key Baru', 'Yakin generate API key baru? Key lama akan tidak berlaku.', async () => {
                showLoading();
                const result = await requestJson('api/user/apikey.php', {}, 'POST');
                hideLoading();
                if (result.status === 'success') {
                    document.getElementById('apiKey').innerText = result.data.api_key;
                    showSuccess('API Key Diperbarui', result.message || 'API key baru berhasil dibuat.');
                } else {
                    showError('Gagal Generate Key', result.message || 'Terjadi kesalahan.');
                }
            });
        }

        function copyApiKey() {
            const key = document.getElementById('apiKey').innerText;
            navigator.clipboard.writeText(key)
                .then(() => showSuccess('Berhasil Disalin', 'API key berhasil disalin ke clipboard.'))
                .catch(() => showError('Gagal Menyalin', 'Browser menolak akses clipboard.'));
        }

        function showConfirmLogout() {
            showConfirm('Konfirmasi Logout', 'Yakin ingin keluar dari akun ini?', () => {
                window.location.href = 'api/auth/logout.php';
            });
        }
    </script>
</body>
</html>

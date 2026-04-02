<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengaturan - Fandirr Pay</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="mobile-header">
            <div class="logo-mobile">Fandirr<span>Pay</span></div>
            <button onclick="window.location.href='dashboard'" class="menu-btn" style="color:white;">
                <i class="ri-arrow-left-line"></i>
            </button>
        </div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="ri-settings-4-fill" style="color: var(--primary);"></i> Pengaturan
                </div>
            </div>
            <nav style="flex: 1;">
                <a href="dashboard" class="nav-item">
                    <i class="ri-arrow-left-circle-line"></i> Kembali ke Dashboard
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <h2 style="margin-bottom: 1.5rem;">Pengaturan Akun</h2>

            <div class="stats-grid" style="grid-template-columns: 1fr;">
                
                <div class="glass-card" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;"><i class="ri-user-settings-line"></i> Edit Profil</h3>
                    <div class="form-group">
                        <label class="form-label">Username Baru</label>
                        <input type="text" id="set-username" class="form-control" placeholder="Masukkan username baru">
                    </div>
                    <button id="btn-save-profile" onclick="updateProfile()" class="btn btn-primary">Simpan Profil</button>
                </div>

                <div class="glass-card" style="padding: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;"><i class="ri-lock-password-line"></i> Ganti Password</h3>
                    <div class="form-group">
                        <label class="form-label">Password Lama</label>
                        <input type="password" id="old-pass" class="form-control" placeholder="********">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" id="new-pass" class="form-control" placeholder="Min. 6 karakter">
                    </div>
                    <button id="btn-save-password" onclick="changePassword()" class="btn btn-outline" style="width: 100%; border-color: var(--primary); color: var(--primary);">Ubah Password</button>
                </div>

            </div>
        </main>
    </div>

    <div id="sysModalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-check-line"></i></div>
            <h3 class="modal-title" id="sysSuccessTitle">Berhasil!</h3>
            <p class="modal-message" id="sysSuccessMsg">Operasi berhasil dilakukan.</p>
            <button id="sysSuccessBtn" class="modal-btn">OK</button>
        </div>
    </div>

    <div id="sysModalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-close-line"></i></div>
            <h3 class="modal-title" id="sysErrorTitle">Gagal</h3>
            <p class="modal-message" id="sysErrorMsg">Terjadi kesalahan.</p>
            <button class="modal-btn" onclick="document.getElementById('sysModalError').classList.remove('active')">Tutup</button>
        </div>
    </div>

    <script>
        // --- HELPERS MODAL ---
        const showSuccess = (title, msg, onOk) => {
            document.getElementById('sysSuccessTitle').innerText = title;
            document.getElementById('sysSuccessMsg').innerText = msg;
            
            const btn = document.getElementById('sysSuccessBtn');
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', () => {
                document.getElementById('sysModalSuccess').classList.remove('active');
                if (onOk) onOk();
            });

            document.getElementById('sysModalSuccess').classList.add('active');
        };

        const showError = (title, msg) => {
            document.getElementById('sysErrorTitle').innerText = title;
            document.getElementById('sysErrorMsg').innerText = msg;
            document.getElementById('sysModalError').classList.add('active');
        };

        // --- FUNGSI UPDATE PROFILE ---
        async function updateProfile() {
            const username = document.getElementById('set-username').value;
            const btn = document.getElementById('btn-save-profile');

            if(!username) {
                showError("Validasi", "Username tidak boleh kosong");
                return;
            }
            
            try {
                btn.innerText = "Menyimpan...";
                btn.disabled = true;

                const res = await fetch('api/user/settings.php?action=update_profile', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username})
                });
                const data = await res.json();
                
                btn.innerText = "Simpan Profil";
                btn.disabled = false;

                if(data.status === 'success') {
                    showSuccess("Berhasil", data.message, () => {
                        location.reload(); // Reload halaman setelah klik OK
                    });
                } else {
                    showError("Gagal", data.message);
                }
            } catch(e) { 
                btn.innerText = "Simpan Profil";
                btn.disabled = false;
                showError("Error", "Gagal menghubungi server"); 
            }
        }

        // --- FUNGSI GANTI PASSWORD ---
        async function changePassword() {
            const oldPass = document.getElementById('old-pass').value;
            const newPass = document.getElementById('new-pass').value;
            const btn = document.getElementById('btn-save-password');

            if(!oldPass || !newPass) {
                showError("Validasi", "Mohon isi password lama dan baru.");
                return;
            }

            try {
                btn.innerText = "Memproses...";
                btn.disabled = true;

                const res = await fetch('api/user/settings.php?action=change_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({old_password: oldPass, new_password: newPass})
                });
                const data = await res.json();

                btn.innerText = "Ubah Password";
                btn.disabled = false;

                if(data.status === 'success') {
                    showSuccess("Password Diubah", data.message, () => {
                        window.location.href = 'login'; // Redirect ke login setelah sukses
                    });
                } else {
                    showError("Gagal", data.message);
                }
            } catch(e) { 
                btn.innerText = "Ubah Password";
                btn.disabled = false;
                showError("Error", "Gagal menghubungi server"); 
            }
        }
    </script>
</body>
</html>

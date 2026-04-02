<?php
session_start();
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header("Location: login"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Fandirr Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* --- CORE STYLES --- */
        body { background-color: #0f172a; overflow-x: hidden; }
        .sidebar { background: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); z-index: 100; transition: transform 0.3s ease; }
        .glass-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); }

        /* Form Styling */
        .form-label { color: #cbd5e1; font-size: 0.9rem; margin-bottom: 0.5rem; display: block; }
        .form-control { 
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white;
            padding: 0.8rem; border-radius: 8px; width: 100%; outline: none; transition: 0.3s;
        }
        .form-control:focus { border-color: var(--primary); background: rgba(255,255,255,0.1); }

        /* --- TOGGLE SWITCH (IOS STYLE) --- */
        .switch-container {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05); cursor: pointer;
        }
        .switch {
            position: relative; display: inline-block; width: 50px; height: 28px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #334155; transition: .4s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content: ""; height: 20px; width: 20px; left: 4px; bottom: 4px;
            background-color: white; transition: .4s; border-radius: 50%;
        }
        input:checked + .slider { background-color: #EF4444; } /* Merah saat maintenance ON */
        input:checked + .slider:before { transform: translateX(22px); }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6); z-index: 9999;
            opacity: 0; visibility: hidden; transition: 0.3s;
            backdrop-filter: blur(3px); display: flex; justify-content: center; align-items: center;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-box { transform: scale(0.95); transition: 0.2s; background: #1e293b; }
        .modal-overlay.active .modal-box { transform: scale(1); }

        /* Responsive */
        .sidebar { position: fixed; height: 100vh; width: 250px; left: 0; top: 0; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .menu-toggle { display: none; font-size: 1.5rem; color: white; background: none; border: none; cursor: pointer; }
        .sidebar-overlay-nav { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 90; display: none; }
        .sidebar-overlay-nav.active { display: block; }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 260px; }
            .sidebar.active { transform: translateX(0); box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
            .main-content { margin-left: 0; padding: 1rem; }
            .menu-toggle { display: block; margin-right: 1rem; }
        }
    </style>
</head>
<body>
    
    <div id="sidebarOverlayNav" class="sidebar-overlay-nav" onclick="toggleSidebar()"></div>

    <div class="dashboard-container">
        <aside id="adminSidebar" class="sidebar">
            <div class="sidebar-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="sidebar-brand">Fandirr<span>Admin</span></div>
                <button onclick="toggleSidebar()" class="menu-toggle" style="font-size:1.5rem;">&times;</button>
            </div>
            <nav>
                <a href="dashboard" class="nav-item"><i class="ri-dashboard-line"></i> Dashboard</a>
                <a href="users" class="nav-item"><i class="ri-user-line"></i> Data Member</a>
                <a href="transactions" class="nav-item"><i class="ri-file-list-3-line"></i> Riwayat Transaksi</a>
                <a href="broadcast" class="nav-item"><i class="ri-broadcast-line"></i> Broadcast</a>
                <a href="#" class="nav-item active"><i class="ri-settings-3-line"></i> Pengaturan</a>
                <a href="../api/auth/logout" class="nav-item" style="margin-top: auto; color: #EF4444;"><i class="ri-logout-box-line"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; align-items: center; margin-bottom: 2rem; background: #0f172a; position: sticky; top: 0; z-index: 50; padding: 1rem 0;">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="ri-menu-2-line"></i></button>
                <div style="flex: 1;">
                    <h2 style="font-weight: 700; color: white;">Pengaturan Web</h2>
                    <p style="color: var(--text-muted); margin: 0;">Kontrol global website.</p>
                </div>
            </header>

            <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
                                <form id="settingsForm">
                    
                    <div style="margin-bottom: 2rem;">
                        <label class="switch-container" for="mtSwitch" style="width: 100%; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                            <div>
                                <h4 style="margin:0; color:white; font-size:1rem;">Mode Maintenance</h4>
                                <p style="margin:0; color:var(--text-muted); font-size:0.8rem;">Jika aktif, user tidak bisa login.</p>
                            </div>
                            
                            <div class="switch">
                                <input type="checkbox" id="mtSwitch" name="maintenance_mode">
                                <span class="slider"></span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Website</label>
                        <input type="text" name="web_title" id="webTitle" class="form-control" placeholder="Contoh: Fandirr Pay" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Teks Berjalan (Running Text)</label>
                        <textarea name="running_text" id="runningText" class="form-control" rows="3" placeholder="Info untuk member..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="ri-save-3-line"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </main>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div>
            <h3 class="modal-title">Berhasil!</h3>
            <p class="modal-message">Pengaturan disimpan.</p>
            <button class="modal-btn" onclick="document.getElementById('modalSuccess').classList.remove('active')">Tutup</button>
        </div>
    </div>
    
    <div id="modalLoading" class="modal-overlay"><div class="modal-box"><div class="spinner-wrapper"><div class="loading-spinner"></div></div><h3 class="modal-title">Menyimpan...</h3></div></div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
            document.getElementById('sidebarOverlayNav').classList.toggle('active');
        }

        // LOAD DATA SAAT BUKA HALAMAN
        async function loadSettings() {
            try {
                const res = await fetch('../api/admin/settings.php');
                const result = await res.json();
                if(result.status === 'success') {
                    const data = result.data;
                    document.getElementById('webTitle').value = data.web_title;
                    document.getElementById('runningText').value = data.running_text;
                    // Checkbox maintenance (1 = checked)
                    document.getElementById('mtSwitch').checked = (data.maintenance_mode == 1);
                }
            } catch(e) { console.error(e); }
        }

        // SIMPAN DATA
        document.getElementById('settingsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            document.getElementById('modalLoading').classList.add('active');

            const payload = {
                web_title: this.web_title.value,
                running_text: this.running_text.value,
                maintenance_mode: this.maintenance_mode.checked
            };

            try {
                const res = await fetch('../api/admin/settings.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                
                document.getElementById('modalLoading').classList.remove('active');
                
                if(result.status === 'success') {
                    document.getElementById('modalSuccess').classList.add('active');
                } else {
                    alert('Gagal menyimpan: ' + result.message);
                }
            } catch(e) {
                document.getElementById('modalLoading').classList.remove('active');
                alert('Error koneksi.');
            }
        });

        loadSettings();
    </script>
</body>
</html>

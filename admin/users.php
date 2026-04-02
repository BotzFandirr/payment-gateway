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
    <title>Data Member - Fandirr Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* --- STYLE ADMIN SAMA SEPERTI SEBELUMNYA --- */
        body { background-color: #0f172a; overflow-x: hidden; }
        .sidebar { background: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); z-index: 100; transition: transform 0.3s ease; }
        .glass-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; min-width: 600px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; }
        th { font-weight: 600; color: var(--primary); text-transform: uppercase; font-size: 0.8rem; }
        tr:hover { background: rgba(255,255,255,0.02); }
        
        .btn-icon { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; border: none; cursor: pointer; margin-right: 5px; transition: 0.2s; }
        .btn-edit { background: rgba(59, 130, 246, 0.2); color: #3B82F6; }
        .btn-edit:hover { background: #3B82F6; color: white; }
        .btn-del { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
        .btn-del:hover { background: #EF4444; color: white; }

        /* Responsive Sidebar */
        .sidebar { position: fixed; height: 100vh; width: 250px; left: 0; top: 0; }
        .main-content { margin-left: 250px; padding: 2rem; }
        .menu-toggle { display: none; font-size: 1.5rem; color: white; background: none; border: none; cursor: pointer; }
        .sidebar-overlay { display: none; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 260px; }
            .sidebar.active { transform: translateX(0); box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
            .main-content { margin-left: 0; padding: 1rem; }
            .menu-toggle { display: block; margin-right: 1rem; }
            .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 90; display: none; }
            .sidebar-overlay.active { display: block; }
        }
    </style>
</head>
<body>
    
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="dashboard-container">
        <aside id="adminSidebar" class="sidebar">
            <div class="sidebar-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div class="sidebar-brand">Fandirr<span>Admin</span></div>
                <button onclick="toggleSidebar()" class="menu-toggle" style="font-size:1.5rem;">&times;</button>
            </div>
            <nav>
                <a href="dashboard" class="nav-item"><i class="ri-dashboard-line"></i> Dashboard</a>
                <a href="#" class="nav-item active"><i class="ri-user-line"></i> Data Member</a>
                <a href="transactions" class="nav-item"><i class="ri-file-list-3-line"></i> Riwayat Transaksi</a>
                <a href="broadcast" class="nav-item"><i class="ri-broadcast-line"></i> Broadcast</a>
                <a href="settings" class="nav-item"><i class="ri-settings-3-line"></i> Pengaturan</a>
                <a href="../api/auth/logout" class="nav-item" style="margin-top: auto; color: #EF4444;"><i class="ri-logout-box-line"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; align-items: center; margin-bottom: 2rem; background: #0f172a; position: sticky; top: 0; z-index: 50; padding: 1rem 0;">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="ri-menu-2-line"></i></button>
                <div style="flex: 1;">
                    <h2 style="font-weight: 700; color: white;">Data Member</h2>
                    <p style="color: var(--text-muted); margin: 0;">Kelola pengguna dan saldo.</p>
                </div>
            </header>

            <div class="glass-card">
                <div class="table-responsive">
                    <table id="user-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Saldo (Rp)</th>
                                <th>Bergabung</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="user-list">
                            <tr><td colspan="5" style="text-align:center;">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalEdit" class="modal-overlay">
        <div class="modal-box glass-card">
            <h3 style="margin-bottom: 1rem;">Edit Member</h3>
            <form id="formEdit">
                <input type="hidden" id="editId">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" id="editUsername" class="form-control" disabled style="opacity: 0.6;">
                </div>
                <div class="form-group">
                    <label class="form-label">Saldo (Rp)</label>
                    <input type="number" id="editBalance" class="form-control" placeholder="0">
                    <small style="color: var(--text-muted);">Ganti angka ini untuk tembak saldo manual.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Reset Password (Opsional)</label>
                    <input type="text" id="editPassword" class="form-control" placeholder="Isi jika ingin ubah password">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModals()" style="flex:1;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalDelete" class="modal-overlay">
        <div class="modal-box glass-card" style="border-color: #EF4444;">
            <div style="text-align: center; margin-bottom: 1rem;">
                <i class="ri-error-warning-fill" style="font-size: 3rem; color: #EF4444;"></i>
            </div>
            <h3 style="text-align: center; color: #EF4444;">Hapus Permanen?</h3>
            <p style="text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Tindakan ini akan menghapus akun <strong id="delUsername" style="color:white;"></strong> beserta saldonya dan tidak bisa dibatalkan.
            </p>
            
            <div class="form-group">
                <label class="form-label" style="text-align: center; display: block;">Ketik "CONFIRM" untuk melanjutkan</label>
                <input type="text" id="delConfirmCode" class="form-control" style="text-align: center; letter-spacing: 2px; text-transform: uppercase;">
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button class="btn btn-outline" onclick="closeModals()" style="flex:1;">Batal</button>
                <button id="btnExecuteDelete" class="btn btn-primary" style="flex:1; background: #EF4444; border-color: #EF4444; opacity: 0.5; pointer-events: none;">Hapus</button>
            </div>
        </div>
    </div>

    <div id="modalLoading" class="modal-overlay"><div class="modal-box"><div class="spinner-wrapper"><div class="loading-spinner"></div></div><h3 class="modal-title">Memproses...</h3></div></div>
    <div id="modalSuccess" class="modal-overlay"><div class="modal-box modal-success"><div class="modal-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div><h3 class="modal-title">Berhasil!</h3><p class="modal-message" id="msgSuccess">Sukses.</p><button class="modal-btn" onclick="document.getElementById('modalSuccess').classList.remove('active')">Tutup</button></div></div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        function closeModals() {
            document.getElementById('modalEdit').classList.remove('active');
            document.getElementById('modalDelete').classList.remove('active');
            document.getElementById('modalLoading').classList.remove('active');
        }

        async function loadUsers() {
            const tbody = document.getElementById('user-list');
            try {
                const res = await fetch('../api/admin/users.php?action=get_users');
                const result = await res.json();
                
                tbody.innerHTML = '';
                if(result.status === 'success' && result.data.length > 0) {
                    result.data.forEach(u => {
                        const joined = new Date(u.created_at).toLocaleDateString('id-ID');
                        const balance = parseInt(u.balance).toLocaleString('id-ID');
                        // Escape string agar aman saat dipassing ke function
                        const uStr = encodeURIComponent(JSON.stringify(u));

                        tbody.innerHTML += `
                            <tr>
                                <td style="font-family: monospace;">${u.user_id}</td>
                                <td style="color:white; font-weight:bold;">${u.username || '-'}</td>
                                <td style="color:#10B981; font-weight:bold;">Rp ${balance}</td>
                                <td>${joined}</td>
                                <td style="text-align: center;">
                                    <button onclick="openEdit('${uStr}')" class="btn-icon btn-edit"><i class="ri-pencil-line"></i></button>
                                    <button onclick="openDelete(${u.id}, '${u.username}')" class="btn-icon btn-del"><i class="ri-delete-bin-line"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem;">Belum ada member.</td></tr>';
                }
            } catch(e) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Gagal memuat.</td></tr>'; }
        }

        // --- 1. LOGIC EDIT ---
        window.openEdit = (uEncoded) => {
            const u = JSON.parse(decodeURIComponent(uEncoded));
            document.getElementById('editId').value = u.id;
            document.getElementById('editUsername').value = u.username;
            document.getElementById('editBalance').value = u.balance;
            document.getElementById('editPassword').value = ''; 
            document.getElementById('modalEdit').classList.add('active');
        }

        document.getElementById('formEdit').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('editId').value;
            const balance = document.getElementById('editBalance').value;
            const password = document.getElementById('editPassword').value;

            closeModals();
            document.getElementById('modalLoading').classList.add('active');

            try {
                const res = await fetch('../api/admin/users.php?action=update_user', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id, balance, password})
                });
                const data = await res.json();
                
                document.getElementById('modalLoading').classList.remove('active');
                if(data.status === 'success') {
                    document.getElementById('msgSuccess').innerText = "Data member berhasil diperbarui.";
                    document.getElementById('modalSuccess').classList.add('active');
                    loadUsers();
                } else {
                    alert(data.message); // Fallback kecil
                }
            } catch(e) { alert("Gagal update."); }
        });

        // --- 2. LOGIC DELETE (NEW MODAL) ---
        let deleteTargetId = null;

        window.openDelete = (id, username) => {
            deleteTargetId = id;
            document.getElementById('delUsername').innerText = "@" + username;
            document.getElementById('delConfirmCode').value = ''; // Reset input
            
            // Disable tombol hapus dulu sebelum ketik CONFIRM
            const btn = document.getElementById('btnExecuteDelete');
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';
            
            document.getElementById('modalDelete').classList.add('active');
        }

        // Cek Input "CONFIRM"
        document.getElementById('delConfirmCode').addEventListener('input', function() {
            const btn = document.getElementById('btnExecuteDelete');
            if (this.value.toUpperCase() === 'CONFIRM') {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            } else {
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
            }
        });

        document.getElementById('btnExecuteDelete').addEventListener('click', async () => {
            closeModals();
            document.getElementById('modalLoading').classList.add('active');

            try {
                const res = await fetch('../api/admin/users.php?action=delete_user', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: deleteTargetId})
                });
                const data = await res.json();
                
                document.getElementById('modalLoading').classList.remove('active');
                if(data.status === 'success') {
                    document.getElementById('msgSuccess').innerText = "User berhasil dihapus permanen.";
                    document.getElementById('modalSuccess').classList.add('active');
                    loadUsers();
                } else {
                    alert("Gagal menghapus.");
                }
            } catch(e) { alert("Error sistem."); }
        });

        loadUsers();
    </script>
</body>
</html>

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
    <title>Broadcast Pesan - Fandirr Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
        
        /* Radio Button Group */
        .radio-group { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
        .radio-option { flex: 1; position: relative; }
        .radio-option input { display: none; }
        .radio-option label {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer; transition: 0.3s; color: var(--text-muted); background: rgba(255,255,255,0.02);
        }
        .radio-option input:checked + label {
            background: rgba(59, 130, 246, 0.1); border-color: var(--primary); color: var(--primary); font-weight: bold;
        }

        /* Custom Trigger Button */
        .custom-select-trigger {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; padding: 0.8rem; cursor: pointer;
            display: flex; align-items: center; justify-content: space-between;
            transition: background 0.2s;
        }
        .custom-select-trigger:active { transform: scale(0.98); }
        
        /* Modal Type Selection */
        .type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem; }
        .type-option {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 1.5rem; text-align: center; cursor: pointer; 
            transition: transform 0.1s, background 0.1s;
        }
        .type-option:active { transform: scale(0.95); }
        .type-option i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
        .type-option h4 { font-size: 0.9rem; font-weight: 600; margin: 0; }

        .t-info:hover, .t-info.active { border-color: #3B82F6; background: rgba(59, 130, 246, 0.1); } .t-info i { color: #3B82F6; }
        .t-success:hover, .t-success.active { border-color: #10B981; background: rgba(16, 185, 129, 0.1); } .t-success i { color: #10B981; }
        .t-warning:hover, .t-warning.active { border-color: #F59E0B; background: rgba(245, 158, 11, 0.1); } .t-warning i { color: #F59E0B; }
        .t-danger:hover, .t-danger.active { border-color: #EF4444; background: rgba(239, 68, 68, 0.1); } .t-danger i { color: #EF4444; }

        /* Modal Overlay Performance */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6); z-index: 9999;
            opacity: 0; visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s; 
            backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px);
            display: flex; justify-content: center; align-items: center;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-box { transform: scale(0.95); transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); background: #1e293b; }
        .modal-overlay.active .modal-box { transform: scale(1); }

        /* Select2 Style */
        .select2-container--default .select2-selection--single { background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; height: 50px; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: white; padding-left: 15px; }
        .select2-dropdown { background-color: #1e293b; border: 1px solid rgba(255,255,255,0.1); color: white; }
        .select2-search--dropdown .select2-search__field { background-color: #0f172a; border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 6px; }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background-color: var(--primary); }

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
                <a href="#" class="nav-item active"><i class="ri-broadcast-line"></i> Broadcast</a>
                <a href="settings" class="nav-item"><i class="ri-settings-3-line"></i> Pengaturan</a>
                <a href="../api/auth/logout" class="nav-item" style="margin-top: auto; color: #EF4444;"><i class="ri-logout-box-line"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; align-items: center; margin-bottom: 2rem; background: #0f172a; position: sticky; top: 0; z-index: 50; padding: 1rem 0;">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="ri-menu-2-line"></i></button>
                <div style="flex: 1;">
                    <h2 style="font-weight: 700; color: white;">Broadcast Pesan</h2>
                    <p style="color: var(--text-muted); margin: 0;">Kirim pengumuman ke notifikasi member.</p>
                </div>
            </header>

            <div class="glass-card" style="max-width: 600px; margin: 0 auto;">
                <form id="broadcastForm">
                    
                    <div class="form-label">Kirim Kepada:</div>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="target" id="targetAll" value="all" checked onchange="toggleUserField()">
                            <label for="targetAll"><i class="ri-group-line"></i> Semua Member</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="target" id="targetUser" value="specific" onchange="toggleUserField()">
                            <label for="targetUser"><i class="ri-user-search-line"></i> User Spesifik</label>
                        </div>
                    </div>

                    <div class="form-group" id="userField" style="display: none;">
                        <label class="form-label">Cari Username Target</label>
                        <select id="userSelect" name="username" class="form-control">
                            <option value="">-- Pilih User --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Pesan</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Info Maintenance" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Isi Pesan</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Tulis pesan Anda di sini..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipe Pesan</label>
                        <div class="custom-select-trigger" onclick="openTypeModal()">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i id="triggerIcon" class="ri-information-fill" style="color: #3B82F6; font-size: 1.2rem;"></i>
                                <span id="triggerText" style="color: white; font-weight: 500;">Informasi</span>
                            </div>
                            <i class="ri-arrow-down-s-line" style="color: #cbd5e1;"></i>
                        </div>
                        <input type="hidden" id="selectedType" name="type" value="info">
                    </div>

                    <button type="button" onclick="confirmBroadcast()" class="btn btn-primary" style="width: 100%; margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <i class="ri-send-plane-fill"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </main>
    </div>

    <div id="modalType" class="modal-overlay">
        <div class="modal-box glass-card" style="max-width: 400px; background: #1e293b;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="margin:0;">Pilih Tipe Pesan</h3>
                <button onclick="closeModals()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <div class="type-grid">
                <div class="type-option t-info active" onclick="selectType('info', 'Informasi', 'ri-information-fill', '#3B82F6', this)"><i class="ri-information-fill"></i><h4>Info</h4></div>
                <div class="type-option t-success" onclick="selectType('success', 'Sukses', 'ri-checkbox-circle-fill', '#10B981', this)"><i class="ri-checkbox-circle-fill"></i><h4>Sukses</h4></div>
                <div class="type-option t-warning" onclick="selectType('warning', 'Peringatan', 'ri-alert-fill', '#F59E0B', this)"><i class="ri-alert-fill"></i><h4>Warning</h4></div>
                <div class="type-option t-danger" onclick="selectType('danger', 'Bahaya / Penting', 'ri-error-warning-fill', '#EF4444', this)"><i class="ri-error-warning-fill"></i><h4>Bahaya</h4></div>
            </div>
        </div>
    </div>

    <div id="modalConfirm" class="modal-overlay">
        <div class="modal-box glass-card" style="text-align: center;">
            <div style="margin-bottom: 1rem;"><i class="ri-question-line" style="font-size: 3rem; color: var(--primary);"></i></div>
            <h3 style="margin-bottom: 0.5rem;">Konfirmasi Broadcast</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Pesan ini akan dikirim ke <strong id="confirmTargetName" style="color: white;"></strong>. Lanjutkan?</p>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-outline" onclick="closeModals()" style="flex:1;">Batal</button>
                <button id="btnExecuteBroadcast" class="btn btn-primary" style="flex:1;">Ya, Kirim</button>
            </div>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div>
            <h3 class="modal-title">Terkirim!</h3>
            <p class="modal-message" id="msgSuccess">Pesan berhasil disiarkan.</p>
            <button class="modal-btn" onclick="closeModals()">Tutup</button>
        </div>
    </div>

    <div id="modalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-close-circle-line"></i></div>
            <h3 class="modal-title">Gagal!</h3>
            <p class="modal-message" id="msgError">Terjadi kesalahan.</p>
            <button class="modal-btn" onclick="closeModals()">Tutup</button>
        </div>
    </div>

    <div id="modalLoading" class="modal-overlay"><div class="modal-box"><div class="spinner-wrapper"><div class="loading-spinner"></div></div><h3 class="modal-title">Mengirim...</h3></div></div>
    
    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
            document.getElementById('sidebarOverlayNav').classList.toggle('active');
        }

        function closeModals() {
            document.querySelectorAll('.modal-overlay').forEach(el => el.classList.remove('active'));
        }

        $(document).ready(function() {
            $('#userSelect').select2({ placeholder: "Ketik nama atau username...", allowClear: true, width: '100%', dropdownParent: $('body') });
            fetch('../api/admin/users.php?action=get_users')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const select = $('#userSelect');
                        result.data.forEach(user => { select.append(new Option(user.username, user.username, false, false)); });
                        select.trigger('change');
                    }
                });
        });

        function toggleUserField() {
            document.getElementById('userField').style.display = document.getElementById('targetUser').checked ? 'block' : 'none';
        }

        function openTypeModal() { document.getElementById('modalType').classList.add('active'); }

        function selectType(value, label, iconClass, color, element) {
            document.getElementById('selectedType').value = value;
            document.getElementById('triggerText').innerText = label;
            const icon = document.getElementById('triggerIcon');
            icon.className = iconClass; icon.style.color = color;
            document.querySelectorAll('.type-option').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            closeModals();
        }

        let broadcastPayload = {};

        function confirmBroadcast() {
            const form = document.getElementById('broadcastForm');
            const target = form.target.value;
            const specificUsername = $('#userSelect').val();
            const title = form.title.value;
            const message = form.message.value;

            if (!title || !message) { showModal('modalError', 'Judul dan Isi Pesan wajib diisi!'); return; }
            if (target === 'specific' && !specificUsername) { showModal('modalError', 'Mohon pilih username target!'); return; }

            broadcastPayload = { target, username: specificUsername, title, message, type: form.type.value };
            document.getElementById('confirmTargetName').innerText = target === 'all' ? "SEMUA MEMBER" : "@" + specificUsername;
            document.getElementById('modalConfirm').classList.add('active');
        }

        function showModal(id, msg) {
            closeModals();
            setTimeout(() => {
                if(msg) document.getElementById(id === 'modalSuccess' ? 'msgSuccess' : 'msgError').innerText = msg;
                document.getElementById(id).classList.add('active');
            }, 300);
        }

        document.getElementById('btnExecuteBroadcast').addEventListener('click', async function() {
            closeModals();
            setTimeout(() => { document.getElementById('modalLoading').classList.add('active'); }, 100);

            try {
                const res = await fetch('../api/admin/broadcast.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(broadcastPayload)
                });
                
                // PARSE RESPON DENGAN HATI-HATI
                const rawText = (await res.text()).trim();
                console.log("Response:", rawText);
                
                let result;
                try { result = JSON.parse(rawText); } catch(e) { throw new Error("Format respon server salah. Cek Console."); }

                document.getElementById('modalLoading').classList.remove('active');

                // TAMPILKAN HASIL
                if(result.status === 'success') {
                    showModal('modalSuccess', result.message);
                    
                    // COBA RESET FORM (DALAM TRY-CATCH AGAR AMAN)
                    try {
                        document.getElementById('broadcastForm').reset();
                        $('#userSelect').val(null).trigger('change');
                        document.getElementById('targetAll').checked = true;
                        toggleUserField();
                        // Reset Tipe Pesan UI Manual (karena ini custom)
                        const defaultType = document.querySelector('.t-info');
                        if(defaultType) selectType('info', 'Informasi', 'ri-information-fill', '#3B82F6', defaultType);
                    } catch(err) { console.error("Gagal reset form:", err); }

                } else {
                    showModal('modalError', result.message);
                }

            } catch(e) {
                document.getElementById('modalLoading').classList.remove('active');
                showModal('modalError', e.message || "Gagal koneksi.");
            }
        });
    </script>
</body>
</html>

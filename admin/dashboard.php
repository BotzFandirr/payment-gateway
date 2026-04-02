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
    <title>Admin Dashboard - Fandirr Pay</title>
    <?php include '../layout/header-meta.php'; ?>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- CORE STYLES --- */
        body { background-color: #0f172a; overflow-x: hidden; }
        .sidebar { background: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); z-index: 100; transition: transform 0.3s ease; }
        .glass-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); }
        
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; min-width: 600px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; }
        th { font-weight: 600; color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: 8px; cursor: pointer; border: none; font-weight: 600; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-sm:hover { transform: translateY(-2px); }
        .btn-acc { background: rgba(16, 185, 129, 0.2); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .btn-reject { background: rgba(239, 68, 68, 0.2); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Responsive Logic */
        .sidebar { position: fixed; height: 100vh; width: 250px; left: 0; top: 0; }
        .main-content { margin-left: 250px; padding: 2rem; transition: margin 0.3s ease; }
        .menu-toggle { display: none; font-size: 1.5rem; color: white; background: none; border: none; cursor: pointer; }
        .sidebar-overlay { display: none; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); width: 260px; }
            .sidebar.active { transform: translateX(0); box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
            .main-content { margin-left: 0; padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; gap: 1rem; }
            .menu-toggle { display: block; margin-right: 1rem; }
            .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s; }
            .sidebar-overlay.active { display: block; opacity: 1; }
        }
    </style>
</head>
<body>
    
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="dashboard-container">
        <aside id="adminSidebar" class="sidebar">
            <div class="sidebar-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="sidebar-brand">Fandirr<span>Admin</span></div>
                <button onclick="toggleSidebar()" style="background:none; border:none; color:white; font-size:1.5rem; display:none;" class="mobile-close-btn">&times;</button>
            </div>
            <nav>
                <a href="#" class="nav-item active"><i class="ri-dashboard-line"></i> Dashboard</a>
                <a href="users" class="nav-item"><i class="ri-user-line"></i> Data Member</a>
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
                    <h2 style="font-weight: 700; color: white;">Admin Panel</h2>
                    <p style="color: var(--text-muted); margin: 0;">Kontrol Utama Sistem</p>
                </div>
            </header>

            <div class="stats-grid">
                <div class="glass-card">
                    <div style="display:flex; justify-content:space-between; align-items: center;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Total Member</p>
                            <h2 id="stat-users" style="font-size: 1.5rem;">...</h2>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="ri-group-fill" style="font-size: 1.5rem; color: var(--primary);"></i>
                        </div>
                    </div>
                </div>
                <div class="glass-card">
                    <div style="display:flex; justify-content:space-between; align-items: center;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Uang Beredar</p>
                            <h2 id="stat-balance" style="font-size: 1.5rem;">...</h2>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="ri-wallet-3-fill" style="font-size: 1.5rem; color: #10B981;"></i>
                        </div>
                    </div>
                </div>
                <div class="glass-card">
                    <div style="display:flex; justify-content:space-between; align-items: center;">
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">Pending WD</p>
                            <h2 id="stat-wd" style="color: #F59E0B; font-size: 1.5rem;">0</h2>
                        </div>
                        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="ri-time-fill" style="font-size: 1.5rem; color: #F59E0B;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-card" style="margin-top: 2rem; padding: 1.5rem;">
                <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; color: white;">
                    <i class="ri-bar-chart-grouped-line" style="color: var(--primary);"></i> Tren Transaksi (7 Hari Terakhir)
                </h3>
                <div style="width: 100%; height: 300px; position: relative;">
                    <canvas id="trxChart"></canvas>
                </div>
            </div>

            <div class="glass-card" style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; font-size: 1.1rem;">
                    <i class="ri-bank-card-line"></i> Permintaan Penarikan
                </h3>
                
                <div class="table-responsive">
                    <table id="wd-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Username</th>
                                <th>Bank Tujuan</th>
                                <th>Nominal</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="wd-list">
                            <tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalLoading" class="modal-overlay"><div class="modal-box"><div class="spinner-wrapper"><div class="loading-spinner"></div></div><h3 class="modal-title">Memproses...</h3></div></div>
    <div id="modalSuccess" class="modal-overlay"><div class="modal-box modal-success"><div class="modal-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div><h3 class="modal-title">Berhasil!</h3><p class="modal-message" id="msgSuccess">Sukses.</p><button class="modal-btn" onclick="document.getElementById('modalSuccess').classList.remove('active')">Tutup</button></div></div>
    <div id="modalError" class="modal-overlay"><div class="modal-box modal-error"><div class="modal-icon-wrapper"><i class="ri-close-circle-line"></i></div><h3 class="modal-title">Gagal!</h3><p class="modal-message" id="msgError">Error.</p><button class="modal-btn" onclick="document.getElementById('modalError').classList.remove('active')">Tutup</button></div></div>
    <div id="modalConfirm" class="modal-overlay"><div class="modal-box glass-card"><h3 style="text-align:center;">Konfirmasi ACC</h3><p style="text-align:center; color: #aaa; font-size:0.9rem; margin-bottom:1rem;">Pastikan sudah transfer manual.</p><div style="display:flex;gap:1rem;"><button class="btn btn-outline" onclick="closeModals()" style="flex:1;">Batal</button><button id="btnConfirmYes" class="btn btn-primary" style="flex:1;">Ya, Setujui</button></div></div></div>
    <div id="modalReject" class="modal-overlay"><div class="modal-box glass-card"><h3 style="text-align:center;">Tolak Penarikan</h3><div class="form-group"><input id="rejectReason" class="form-control" placeholder="Alasan..."></div><div style="display:flex;gap:1rem;"><button class="btn btn-outline" onclick="closeModals()" style="flex:1;">Batal</button><button id="btnSubmitReject" class="btn btn-primary" style="flex:1;background:#EF4444;border-color:#EF4444;">Tolak</button></div></div></div>

    <script>
        // --- SIDEBAR & MODALS ---
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
        function closeModals() {
            document.getElementById('modalConfirm').classList.remove('active');
            document.getElementById('modalReject').classList.remove('active');
            document.getElementById('modalLoading').classList.remove('active');
        }
        function showSuccess(msg) { document.getElementById('msgSuccess').innerText = msg; document.getElementById('modalSuccess').classList.add('active'); }
        function showError(msg) { document.getElementById('msgError').innerText = msg; document.getElementById('modalError').classList.add('active'); }

        // --- CHART JS SETUP ---
        let activityChart = null;

        async function loadChart() {
            try {
                const res = await fetch('../api/admin/core.php?action=get_chart_data');
                const result = await res.json();

                if (result.status === 'success') {
                    const ctx = document.getElementById('trxChart').getContext('2d');
                    
                    // Jika chart sudah ada, destroy dulu biar ga numpuk
                    if(activityChart) activityChart.destroy();

                    activityChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: result.labels,
                            datasets: [
                                {
                                    label: 'Uang Masuk (Deposit)',
                                    data: result.deposit,
                                    borderColor: '#10B981', // Hijau
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.4, // Garis melengkung
                                    fill: true,
                                    pointBackgroundColor: '#10B981'
                                },
                                {
                                    label: 'Uang Keluar (Withdraw)',
                                    data: result.withdraw,
                                    borderColor: '#EF4444', // Merah
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointBackgroundColor: '#EF4444'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#cbd5e1' } } // Warna teks legend
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                    ticks: { color: '#94a3b8' }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#94a3b8' }
                                }
                            }
                        }
                    });
                }
            } catch (e) { console.error("Gagal load chart", e); }
        }

        // --- STATS & TABLE LOGIC (LAMA) ---
        async function loadStats() {
            try {
                const res = await fetch('../api/admin/core.php?action=get_stats');
                const result = await res.json();
                if(result.status === 'success') {
                    document.getElementById('stat-users').innerText = result.data.total_user;
                    document.getElementById('stat-balance').innerText = "Rp " + parseInt(result.data.total_balance).toLocaleString('id-ID');
                    document.getElementById('stat-wd').innerText = result.data.pending_wd;
                }
            } catch(e) {}
        }

        async function loadWithdrawals() {
            const tbody = document.getElementById('wd-list');
            try {
                const res = await fetch('../api/admin/core.php?action=get_withdrawals');
                const result = await res.json();
                tbody.innerHTML = '';
                if(result.status === 'success' && result.data.length > 0) {
                    result.data.forEach(wd => {
                        const amount = parseInt(wd.amount).toLocaleString('id-ID');
                        const date = new Date(wd.created_at).toLocaleString('id-ID', {day:'numeric', month:'short'});
                        const row = `<tr><td>${date}</td><td><div style="font-weight:bold; color:white;">${wd.username}</div></td><td><div style="color:var(--primary);">${wd.bank_name}</div><small>${wd.account_number}</small></td><td style="color:#F59E0B;font-weight:bold;">Rp ${amount}</td><td style="text-align:center;"><div style="display:flex;gap:5px;justify-content:center;"><button onclick="openAccModal(${wd.id})" class="btn-sm btn-acc">ACC</button><button onclick="openRejectModal(${wd.id})" class="btn-sm btn-reject">Tolak</button></div></td></tr>`;
                        tbody.innerHTML += row;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--text-muted);">Tidak ada data.</td></tr>';
                }
            } catch(e) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Gagal memuat.</td></tr>'; }
        }

        // --- INIT ACTIONS ---
        let currentActionId = null;
        window.openAccModal = (id) => { currentActionId = id; document.getElementById('modalConfirm').classList.add('active'); }
        window.openRejectModal = (id) => { currentActionId = id; document.getElementById('rejectReason').value = ''; document.getElementById('modalReject').classList.add('active'); }
        
        document.getElementById('btnConfirmYes').addEventListener('click', () => processTransaction(currentActionId, 'approve'));
        document.getElementById('btnSubmitReject').addEventListener('click', () => {
            const reason = document.getElementById('rejectReason').value;
            if(!reason) return alert("Isi alasan!");
            processTransaction(currentActionId, 'reject', reason);
        });

        async function processTransaction(id, decision, reason = '') {
            closeModals(); document.getElementById('modalLoading').classList.add('active');
            try {
                const res = await fetch('../api/admin/core.php?action=process_withdraw', {
                    method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ id, decision, reason })
                });
                const data = await res.json();
                setTimeout(() => {
                    document.getElementById('modalLoading').classList.remove('active');
                    if (data.status === 'success') { showSuccess(data.message); loadStats(); loadWithdrawals(); loadChart(); } 
                    else { showError(data.message); }
                }, 800);
            } catch(e) { document.getElementById('modalLoading').classList.remove('active'); showError("Error."); }
        }

        // Jalankan saat load
        loadStats(); 
        loadWithdrawals();
        loadChart(); // Load grafik
        
        setInterval(() => { loadStats(); loadWithdrawals(); }, 10000);
    </script>
</body>
</html>

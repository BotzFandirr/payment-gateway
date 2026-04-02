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
    <title>Riwayat Transaksi - Fandirr Admin</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* --- STYLE ADMIN BASIC --- */
        body { background-color: #0f172a; overflow-x: hidden; }
        .sidebar { background: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); z-index: 100; transition: transform 0.3s ease; }
        .glass-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; min-width: 800px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); color: #cbd5e1; }
        th { font-weight: 600; color: var(--primary); text-transform: uppercase; font-size: 0.8rem; }
        tr { transition: 0.2s; }
        tr:hover { background: rgba(255,255,255,0.05) !important; cursor: pointer; }

        /* Badge Status */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .bg-green { background: rgba(16, 185, 129, 0.2); color: #10B981; }
        .bg-red { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
        .bg-purple { background: rgba(139, 92, 246, 0.2); color: #8B5CF6; }
        .bg-yellow { background: rgba(245, 158, 11, 0.2); color: #F59E0B; }

        /* Filter Tabs */
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 5px; }
        .filter-btn {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted); padding: 0.5rem 1.2rem; border-radius: 50px;
            cursor: pointer; font-size: 0.9rem; transition: 0.3s; white-space: nowrap;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--primary); color: white; border-color: var(--primary);
        }

        /* Modal Struk Custom Style */
        .receipt-details { display: flex; flex-direction: column; gap: 0.8rem; }
        .rc-row { display: flex; justify-content: space-between; align-items: flex-start; font-size: 0.9rem; color: var(--text-muted); }
        .rc-val { color: white; font-weight: 500; text-align: right; max-width: 60%; word-wrap: break-word; }

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
            .sidebar-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 90; display: block; opacity: 0; visibility: hidden; transition: 0.3s; }
            .sidebar-overlay.active { opacity: 1; visibility: visible; }
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
                <a href="users" class="nav-item"><i class="ri-user-line"></i> Data Member</a>
                <a href="#" class="nav-item active"><i class="ri-file-list-3-line"></i> Riwayat Transaksi</a>
                <a href="broadcast" class="nav-item"><i class="ri-broadcast-line"></i> Broadcast</a>
                <a href="settings" class="nav-item"><i class="ri-settings-3-line"></i> Pengaturan</a>
                <a href="../api/auth/logout" class="nav-item" style="margin-top: auto; color: #EF4444;"><i class="ri-logout-box-line"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header style="display: flex; align-items: center; margin-bottom: 2rem; background: #0f172a; position: sticky; top: 0; z-index: 50; padding: 1rem 0;">
                <button class="menu-toggle" onclick="toggleSidebar()"><i class="ri-menu-2-line"></i></button>
                <div style="flex: 1;">
                    <h2 style="font-weight: 700; color: white;">Riwayat Transaksi</h2>
                    <p style="color: var(--text-muted); margin: 0;">Klik baris untuk melihat struk detail.</p>
                </div>
            </header>

            <div class="glass-card">
                <div class="filter-tabs">
                    <button class="filter-btn active" onclick="setFilter('all', this)">Semua</button>
                    <button class="filter-btn" onclick="setFilter('deposit', this)">Deposit</button>
                    <button class="filter-btn" onclick="setFilter('withdraw', this)">Withdraw</button>
                    <button class="filter-btn" onclick="setFilter('transfer', this)">Transfer User</button>
                </div>

                <div class="table-responsive">
                    <table id="trx-table">
                        <thead>
                            <tr>
                                <th>ID Trx</th>
                                <th>User</th>
                                <th>Tipe</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody id="trx-list">
                            <tr><td colspan="7" style="text-align:center;">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="receipt-modal" class="modal-overlay" style="z-index: 200;">
        <div class="modal-box glass-card" style="padding: 0; overflow: hidden; max-width: 380px; border-radius: 20px;">
            
            <div id="rc-header" style="background: var(--primary); padding: 1.5rem; text-align: center; color: black; position: relative;">
                <button onclick="document.getElementById('receipt-modal').classList.remove('active')" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: black; font-size: 1.5rem; cursor: pointer;">&times;</button>
                
                <div style="width: 50px; height: 50px; background: rgba(0,0,0,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                    <i id="rc-icon" class="ri-check-line" style="font-size: 1.8rem;"></i>
                </div>
                <h3 id="rc-status" style="font-weight: 800; text-transform: uppercase;">BERHASIL</h3>
                <p id="rc-date" style="font-size: 0.8rem; opacity: 0.8;">-</p>
            </div>

            <div style="padding: 1.5rem; background: #151515;">
                
                <div style="text-align: center; margin-bottom: 2rem;">
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Nominal Transaksi</p>
                    <h2 id="rc-amount" style="font-size: 2rem; color: white;">Rp 0</h2>
                </div>

                <div class="receipt-details">
                    <div class="rc-row">
                        <span>Tipe</span>
                        <span id="rc-type" class="rc-val">Deposit</span>
                    </div>
                    <div class="rc-row">
                        <span>ID Transaksi</span>
                        <span id="rc-id" class="rc-val" style="font-family: monospace;">-</span>
                    </div>
                    <div class="rc-row">
                        <span>Oleh User</span>
                        <span id="rc-user" class="rc-val" style="color: var(--primary);">@username</span>
                    </div>
                    <div style="border-bottom: 1px dashed rgba(255,255,255,0.2); margin: 0.5rem 0;"></div>
                    
                    <div class="rc-row">
                        <span>Keterangan</span>
                        <span id="rc-desc" class="rc-val">Detail transaksi</span>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 5px;">Bukti Sah Transaksi Digital</p>
                    <p style="font-size: 0.65rem; color: var(--text-muted); opacity:0.5;">Fandirr Pay Admin Panel</p>
                </div>

            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        let currentFilter = 'all';
        let transactionsData = []; // Simpan data global agar bisa diakses modal

        function setFilter(type, btn) {
            currentFilter = type;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadHistory();
        }

        async function loadHistory() {
            const tbody = document.getElementById('trx-list');
            
            try {
                const res = await fetch(`../api/admin/transactions.php?filter=${currentFilter}`);
                const result = await res.json();
                
                tbody.innerHTML = '';
                
                if(result.status === 'success' && result.data.length > 0) {
                    transactionsData = result.data; // Simpan ke variabel global

                    transactionsData.forEach((t, index) => {
                        const date = new Date(t.created_at).toLocaleString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});
                        const amount = parseInt(t.amount).toLocaleString('id-ID');
                        
                        // Badge Logic
                        let typeBadge = '';
                        let amountColor = 'white';
                        
                        if (t.type === 'deposit') {
                            typeBadge = '<span class="badge bg-green"><i class="ri-arrow-down-circle-line"></i> DEPOSIT</span>';
                            amountColor = '#10B981'; 
                        } else if (t.type === 'withdraw') {
                            typeBadge = '<span class="badge bg-yellow"><i class="ri-arrow-up-circle-line"></i> WITHDRAW</span>';
                            amountColor = '#F59E0B'; 
                        } else if (t.type === 'transfer') {
                            typeBadge = '<span class="badge bg-purple"><i class="ri-exchange-line"></i> TRANSFER</span>';
                            amountColor = '#8B5CF6'; 
                        } else {
                            typeBadge = '<span class="badge">' + t.type.toUpperCase() + '</span>';
                        }

                        let statusBadge = t.status === 'settlement' || t.status === 'success' 
                            ? '<span style="color:#10B981"><i class="ri-checkbox-circle-fill"></i> Sukses</span>' 
                            : '<span style="color:#F59E0B"><i class="ri-time-fill"></i> ' + t.status + '</span>';

                        const trxIdDisplay = t.trx_id ? t.trx_id : '-';

                        // Tambahkan onclick="openReceipt(index)"
                        tbody.innerHTML += `
                            <tr onclick="openReceipt(${index})">
                                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);">#${trxIdDisplay}</td>
                                <td style="font-weight:bold; color:white;">${t.user || 'Sistem'}</td>
                                <td>${typeBadge}</td>
                                <td style="font-weight:bold; color:${amountColor};">Rp ${amount}</td>
                                <td style="font-size: 0.9rem; color: #cbd5e1;">${t.note || '-'}</td>
                                <td>${statusBadge}</td>
                                <td style="font-size: 0.8rem;">${date}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 2rem;">Tidak ada data.</td></tr>';
                }
            } catch(e) { 
                console.error(e);
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color: #EF4444;">Error: ${e.message}</td></tr>`; 
            }
        }

        // --- FUNGSI BUKA STRUK ---
        function openReceipt(index) {
            const trx = transactionsData[index];
            if (!trx) return;

            // 1. Set Warna Header & Icon
            const header = document.getElementById('rc-header');
            const icon = document.getElementById('rc-icon');
            const statusTxt = document.getElementById('rc-status');
            
            if (trx.status === 'success' || trx.status === 'settlement') {
                header.style.background = '#10B981'; // Hijau
                icon.className = 'ri-check-double-line';
                statusTxt.innerText = 'BERHASIL';
            } else if (trx.status === 'pending') {
                header.style.background = '#F59E0B'; // Kuning
                icon.className = 'ri-loader-4-line';
                statusTxt.innerText = 'PENDING';
            } else {
                header.style.background = '#EF4444'; // Merah
                icon.className = 'ri-close-circle-line';
                statusTxt.innerText = 'GAGAL';
            }

            // 2. Isi Data
            document.getElementById('rc-date').innerText = new Date(trx.created_at).toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
            document.getElementById('rc-amount').innerText = `Rp ${parseInt(trx.amount).toLocaleString('id-ID')}`;
            
            document.getElementById('rc-type').innerText = trx.type.toUpperCase();
            document.getElementById('rc-id').innerText = trx.trx_id || '-';
            document.getElementById('rc-user').innerText = '@' + (trx.user || 'system');
            document.getElementById('rc-desc').innerText = trx.note || '-';

            // 3. Tampilkan Modal
            document.getElementById('receipt-modal').classList.add('active');
        }

        loadHistory();
        setInterval(loadHistory, 10000); // Auto refresh tiap 10 detik
    </script>
</body>
</html>

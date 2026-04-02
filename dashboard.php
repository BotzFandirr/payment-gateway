<?php
session_start();

// [FIX] Panggil file database agar variabel $pdo dikenali
require_once __DIR__ . '/config/db.php';

// Cek keamanan: Jika belum login, tendang ke login
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

// 2. Ambil Data User & SETTINGS (GLOBAL)
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 3. AMBIL PENGATURAN WEBSITE
$stmtSettings = $pdo->query("SELECT * FROM settings WHERE id = 1");
$webSettings = $stmtSettings->fetch();

// 4. CEK MAINTENANCE (Tendang user jika aktif, KECUALI ADMIN)
if ($webSettings && $webSettings->maintenance_mode == 1) {
    // Jika role bukan admin, tendang keluar
    if ($user->role !== 'admin') {
        session_destroy();
        header("Location: maintenance");
        exit;
    }
}

// Default Text jika database kosong
$pageTitle = $webSettings->web_title ?? 'Fandirr Pay';
$runningText = $webSettings->running_text ?? 'Selamat datang di Fandirr Pay!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - <?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        /* Tambahan Style untuk Running Text & Admin Alert */
        @keyframes marquee {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
        .admin-alert {
            background: #F59E0B; color: black; text-align: center;
            padding: 5px; font-size: 0.8rem; font-weight: bold;
        }
    </style>
</head>
<body>
    
    <?php if ($webSettings->maintenance_mode == 1 && $user->role === 'admin'): ?>
        <div class="admin-alert">
            ⚠️ MODE MAINTENANCE AKTIF (User biasa tidak bisa akses halaman ini)
        </div>
    <?php endif; ?>

    <div style="background: rgba(59, 130, 246, 0.1); border-bottom: 1px solid rgba(59, 130, 246, 0.2); padding: 8px 0; overflow: hidden; white-space: nowrap;">
        <div style="display: inline-block; padding-left: 100%; animation: marquee 15s linear infinite; color: #cbd5e1; font-size: 0.9rem;">
            <i class="ri-volume-up-line" style="color: #3B82F6; margin-right: 5px;"></i> 
            <?php echo htmlspecialchars($runningText); ?>
        </div>
    </div>
    
    <div class="dashboard-container">
        
        <div class="mobile-header">
            <div class="logo-mobile">Fandirr<span>Pay</span></div>
            <button id="sidebar-toggle" class="menu-btn">
                <i class="ri-menu-line"></i>
            </button>
        </div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="ri-wallet-3-fill" style="color: var(--primary);"></i> Fandirr<span>Pay</span>
                </div>
                <button id="sidebar-close" class="close-sidebar-btn">
                    <i class="ri-close-line"></i>
                </button>
            </div>
            
            <nav style="flex: 1;">
                <a href="dashboard" class="nav-item active">
                    <i class="ri-dashboard-line"></i> Dashboard
                </a>
                <a href="#" class="nav-item" onclick="toggleSidebar(); document.getElementById('deposit-modal').classList.add('active')">
                    <i class="ri-arrow-up-circle-line"></i> Isi Saldo
                </a>
                <a href="#" class="nav-item" onclick="toggleSidebar(); document.getElementById('withdraw-modal').classList.add('active')">
                    <i class="ri-arrow-down-circle-line"></i> Tarik Dana
                </a>
                <a href="#" class="nav-item">
                    <i class="ri-history-line"></i> Riwayat
                </a>
                <a href="doc" class="nav-item">
                    <i class="ri-book-read-line"></i> Dokumentasi API
                </a>

            </nav>

            <div style="border-top: 1px solid var(--border-glass); padding-top: 1rem;">
                <?php if($user->role === 'admin'): ?>
                    <a href="admin/dashboard" class="nav-item" style="color: #F59E0B;">
                        <i class="ri-shield-user-line"></i> Panel Admin
                    </a>
                <?php endif; ?>
                
                <button onclick="showConfirmLogout()" class="nav-item" style="background: none; border: none; width: 100%; cursor: pointer; text-align: left;">
                    <i class="ri-logout-box-line" style="color: #EF4444;"></i> <span style="color: #EF4444;">Keluar</span>
                </button>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <main class="main-content">
            
            <header class="top-bar" data-aos="fade-down">
                
                <div>
                    <h2 style="font-weight: 700;">Halo, <span class="username-display"><?php echo htmlspecialchars($user->username); ?></span> 👋</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Selamat datang kembali.</p>
                </div>
                
                <div style="display: flex; gap: 1rem; align-items: center;">
                    
                    <div class="notif-wrapper" style="position: relative;">
                        <button onclick="toggleNotif()" class="icon-btn">
                            <i class="ri-notification-3-line" style="font-size: 1.2rem;"></i>
                            <span id="notif-badge" class="notif-badge" style="display: none;">0</span>
                        </button>

                        <div id="notif-dropdown" class="notif-dropdown">
                            <div class="notif-header">
                                <span>Notifikasi</span>
                                <button onclick="markAllRead()" style="font-size: 0.8rem; color: var(--primary); background:none; border:none; cursor:pointer;">Tandai Baca</button>
                            </div>
                            <div id="notif-list" class="notif-list">
                                <p style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem;">Tidak ada notifikasi baru</p>
                            </div>
                        </div>
                    </div>

                    <div class="user-profile" onclick="window.location.href='profile'" style="cursor: pointer;">
                        <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: black;">
                            <i class="ri-user-fill"></i>
                        </div>
                    </div>

                </div>

            </header>


            <div class="balance-card" data-aos="zoom-in">
                <i class="ri-wallet-3-line balance-bg-icon"></i>
                <h4>Saldo Aktif Anda</h4>
                <h1 id="balance-display">Rp <?php echo number_format($user->balance, 0, ',', '.'); ?></h1>
                <p style="margin-top: 0.5rem; opacity: 0.8; font-size: 0.9rem;">
                    <i class="ri-shield-check-fill"></i> Dilindungi oleh Fandirr Secure
                </p>
            </div>

            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Akses Cepat</h3>
            <div class="quick-actions" data-aos="fade-up" data-aos-delay="100">
                <div class="action-card" onclick="document.getElementById('deposit-modal').classList.add('active')">
                    <i class="ri-add-circle-fill action-icon"></i>
                    <span>Isi Saldo</span>
                </div>
                <div class="action-card" onclick="document.getElementById('transfer-modal').classList.add('active')">
                    <i class="ri-send-plane-fill action-icon"></i>
                    <span>Kirim Uang</span>
                </div>
                <div class="action-card" onclick="document.getElementById('withdraw-modal').classList.add('active')">
                    <i class="ri-bank-card-fill action-icon"></i>
                    <span>Tarik Dana</span>
                </div>
                <div class="action-card" onclick="document.getElementById('support-modal').classList.add('active')">
                    <i class="ri-customer-service-2-fill action-icon"></i>
                    <span>Bantuan</span>
                </div>
            </div>

            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">Developer & Integrasi</h3>
            <div class="glass-card" style="padding: 1.5rem;" data-aos="fade-up">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                    
                    <div style="flex: 1; min-width: 300px;">
                        <h4 style="margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ri-code-s-slash-line" style="color: var(--primary);"></i> API Key Anda
                        </h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                            Gunakan kunci ini untuk integrasi. <span style="color: #EF4444;">Rahasiakan kunci ini!</span>
                        </p>
                        
                        <div class="api-key-wrapper" style="display: flex; gap: 0.5rem;">
                            <input type="text" id="user-api-key" class="form-control" readonly value="<?php echo htmlspecialchars($user->api_key); ?>" 
                                style="font-family: monospace; letter-spacing: 1px; color: var(--primary); background: rgba(0,0,0,0.3);">
                            
                            <button onclick="copyApiKey()" class="btn btn-outline" style="padding: 0.8rem; width: auto;" title="Salin">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            
                            <button onclick="confirmRegenerateApi()" class="btn btn-primary" style="padding: 0.8rem; width: auto;" title="Generate Baru">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>

                    <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-glass); max-width: 400px; width: 100%;">
                        <h5 style="margin-bottom: 0.5rem;">Contoh Request (cURL)</h5>
                        <code style="font-size: 0.75rem; color: var(--text-muted); display: block; word-break: break-all;">
                            curl -X POST 'https://fandirr.store/api/payment/deposit?apikey=<span id="preview-key"><?php echo substr($user->api_key, 0, 8); ?>...</span>'
                        </code>
                    </div>

                </div>
            </div>

            <h3 style="margin-bottom: 1rem; font-size: 1.1rem; margin-top: 2rem;">Ringkasan Pendapatan</h3>
            <div class="stats-grid" data-aos="fade-up">
                <div class="stats-card">
                    <div class="stats-label"><i class="ri-calendar-check-line"></i> Hari Ini</div>
                    <div class="stats-value" id="stats-today">Rp 0</div>
                </div>
                <div class="stats-card">
                    <div class="stats-label"><i class="ri-calendar-2-line"></i> 7 Hari Terakhir</div>
                    <div class="stats-value" id="stats-week">Rp 0</div>
                </div>
                <div class="stats-card">
                    <div class="stats-label"><i class="ri-calendar-event-line"></i> 30 Hari Terakhir</div>
                    <div class="stats-value" id="stats-month">Rp 0</div>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; margin: 0;">Riwayat Transaksi</h3>
            </div>

            <div class="history-tabs" data-aos="fade-up" data-aos-delay="100">
                <button class="tab-btn active" onclick="filterHistory('all', this)">Semua</button>
                <button class="tab-btn" onclick="filterHistory('settlement', this)">Sukses</button>
                <button class="tab-btn" onclick="filterHistory('pending', this)">Pending</button>
                <button class="tab-btn" onclick="filterHistory('failed', this)">Batal / Expired</button>
            </div>

            <div class="table-container" data-aos="fade-up" data-aos-delay="200">
                <table>
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-list">
                        <tr><td colspan="5" style="text-align:center; padding:2rem;">Sedang memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
    
    <div id="deposit-modal" class="modal-overlay" style="z-index: 200;">
        <div class="modal-box glass-card" style="max-width: 450px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: white; font-weight: 700;">Isi Saldo</h3>
                <button id="close-modal-btn" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            
            <div id="deposit-step-1">
                <div class="form-group">
                    <label class="form-label">Nominal Deposit (Rp)</label>
                    <input type="number" id="deposit-amount" class="form-control" placeholder="Min. 1000" min="1000">
                </div>
                <div class="form-group">
                    <label class="form-label">Metode Pembayaran</label>
                    <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 12px; border: 1px solid var(--primary);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <i class="ri-qr-code-line" style="font-size: 1.5rem; color: var(--primary);"></i>
                            <div>
                                <h4 style="font-size: 0.9rem; font-weight: 600;">QRIS Instant</h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">Otomatis masuk (Kode Unik)</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="btn-process-deposit" class="btn btn-primary" style="width: 100%;">Buat Tagihan</button>
            </div>

            <div id="deposit-loading" style="display: none; padding: 2rem 0; text-align: center;">
                <div class="spinner-wrapper" style="display:flex; justify-content:center;">
                    <div class="loading-spinner"></div>
                </div>
                <p style="margin-top: 1rem; color: var(--text-muted); font-size: 0.9rem;">Menghubungkan ke Server...</p>
            </div>
    
            <div id="deposit-step-2" style="display: none; text-align: center;">
                <p style="color: var(--text-muted); font-size: 0.9rem;">Scan QRIS di bawah ini:</p>
                <div style="background: white; padding: 1rem; border-radius: 12px; margin: 1rem auto; width: 200px; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <img id="qris-image" src="" alt="QR Code" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h2 id="qris-amount" style="color: var(--primary); font-weight: 800;">Rp 0</h2>
                <p style="font-size: 0.8rem; color: #EF4444; margin-top: 0.5rem; font-weight: bold;">
                    <i class="ri-alert-fill"></i> TRANSFER HARUS TEPAT SAMPAI 3 DIGIT TERAKHIR!
                </p>
                <div class="timer-container" style="background:rgba(255,255,255,0.05); padding:0.5rem; border-radius:8px; margin-top:1rem; display:flex; justify-content:center; gap:0.5rem;">
                    <span style="color:var(--text-muted); font-size:0.9rem;">Sisa Waktu:</span>
                    <span id="countdown-timer" style="color:var(--primary); font-weight:bold; font-family:monospace;">--:--</span>
                </div>
                <button id="btn-cancel-deposit" class="btn btn-outline" style="width: 100%; margin-top: 1rem; border-color: #EF4444; color: #EF4444;">Batalkan Transaksi</button>
            </div>
        </div>
    </div>

    <div id="withdraw-modal" class="modal-overlay" style="z-index: 200;">
        <div class="modal-box glass-card" style="max-width: 450px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: white; font-weight: 700;">Tarik Dana</h3>
                <button onclick="document.getElementById('withdraw-modal').classList.remove('active')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            <form id="withdraw-form">
                <div class="form-group">
                    <label class="form-label">Jumlah Penarikan</label>
                    <input type="number" id="withdraw-amount" class="form-control" placeholder="Min. 10.000">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Bank</label>
                    <input type="text" id="bank-name" class="form-control" placeholder="Contoh: BCA / DANA">
                </div>
                <div class="form-group">
                    <label class="form-label">Nomor Rekening</label>
                    <input type="number" id="account-number" class="form-control" placeholder="Nomor Rekening Tujuan">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Pemilik</label>
                    <input type="text" id="account-name" class="form-control" placeholder="Nama sesuai rekening">
                </div>
                <button id="withdraw-button" class="btn btn-primary" style="width: 100%;">Ajukan Penarikan</button>
            </form>
        </div>
    </div>
    
    <div id="sysModalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-check-line"></i></div>
            <h3 class="modal-title" id="sysSuccessTitle">Berhasil!</h3>
            <p class="modal-message" id="sysSuccessMsg">Operasi berhasil dilakukan.</p>
            <button class="modal-btn" onclick="document.getElementById('sysModalSuccess').classList.remove('active')">Tutup</button>
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

    <div id="sysModalConfirm" class="modal-overlay">
        <div class="modal-box modal-warning">
            <div class="modal-icon-wrapper"><i class="ri-question-mark"></i></div>
            <h3 class="modal-title" id="sysConfirmTitle">Konfirmasi</h3>
            <p class="modal-message" id="sysConfirmMsg">Apakah Anda yakin?</p>
            <button id="sysConfirmBtnYes" class="modal-btn">Ya, Lanjutkan</button>
            <button class="modal-btn modal-btn-secondary" onclick="document.getElementById('sysModalConfirm').classList.remove('active')">Batal</button>
        </div>
    </div>
    
        <div id="transfer-modal" class="modal-overlay" style="z-index: 200;">
        <div class="modal-box glass-card" style="max-width: 450px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: white; font-weight: 700;">Kirim Uang</h3>
                <button onclick="document.getElementById('transfer-modal').classList.remove('active')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
            </div>
            
            <form id="transfer-form">
                <div class="form-group">
                    <label class="form-label">Username Tujuan</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" id="tf-username" class="form-control" placeholder="Contoh: fandirr">
                        <button type="button" onclick="checkTransferUser()" class="btn btn-outline" style="width: auto; padding: 0.8rem;">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                    <small id="tf-check-result" style="display: block; margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted); min-height: 1.2em;">
                        Klik ikon kaca pembesar untuk cek nama.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="number" id="tf-amount" class="form-control" placeholder="Min. 1000">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Catatan (Opsional)</label>
                    <input type="text" id="tf-note" class="form-control" placeholder="Bayar hutang, dll...">
                </div>

                <button id="btn-submit-transfer" class="btn btn-primary" style="width: 100%;" disabled>Kirim Sekarang</button>
            </form>
        </div>
    </div>
    
        <div id="support-modal" class="modal-overlay" style="z-index: 201;">
        <div class="modal-box glass-card" style="text-align: center; max-width: 400px;">
            <div style="margin-bottom: 1.5rem;">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                    <i class="ri-customer-service-2-fill" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <h3>Butuh Bantuan?</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Tim support kami siap membantu Anda 24/7 jika mengalami kendala.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20butuh%20bantuan" target="_blank" class="btn btn-outline" style="border-color: #25D366; color: #25D366; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="ri-whatsapp-line" style="font-size: 1.2rem;"></i> Chat WhatsApp
                </a>
                
                <a href="https://t.me/username_admin" target="_blank" class="btn btn-outline" style="border-color: #0088cc; color: #0088cc; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="ri-telegram-fill" style="font-size: 1.2rem;"></i> Chat Telegram
                </a>
            </div>

            <button onclick="document.getElementById('support-modal').classList.remove('active')" class="btn" style="margin-top: 1.5rem; width: 100%; background: rgba(255,255,255,0.05); color: white;">Tutup</button>
        </div>
    </div>

    <div id="receipt-modal" class="modal-overlay" style="z-index: 202;">
        <div class="modal-box glass-card" style="padding: 0; overflow: hidden; max-width: 380px; border-radius: 20px;">
            
            <div style="background: var(--primary); padding: 1.5rem; text-align: center; color: black; position: relative;">
                <button onclick="document.getElementById('receipt-modal').classList.remove('active')" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: black; font-size: 1.5rem; cursor: pointer;">&times;</button>
                
                <div style="width: 50px; height: 50px; background: rgba(0,0,0,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                    <i id="rc-icon" class="ri-check-line" style="font-size: 1.8rem;"></i>
                </div>
                <h3 id="rc-status" style="font-weight: 800; text-transform: uppercase;">BERHASIL</h3>
                <p id="rc-date" style="font-size: 0.8rem; opacity: 0.8;">18 Des 2024, 14:30 WIB</p>
            </div>

            <div style="padding: 1.5rem; background: #151515;">
                
                <div style="text-align: center; margin-bottom: 2rem;">
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Total Transaksi</p>
                    <h2 id="rc-amount" style="font-size: 2rem; color: white;">Rp 50.000</h2>
                </div>

                <div class="receipt-details">
                    <div class="rc-row">
                        <span>Tipe</span>
                        <span id="rc-type" style="color: white; font-weight: 600;">Deposit</span>
                    </div>
                    <div class="rc-row">
                        <span>ID Order</span>
                        <span id="rc-orderid" style="color: white; font-family: monospace;">#ORD-1234</span>
                    </div>
                    <div style="border-bottom: 1px dashed rgba(255,255,255,0.2); margin: 1rem 0;"></div>
                    
                    <div class="rc-row">
                        <span>Keterangan</span>
                        <span id="rc-desc" style="color: white; text-align: right; max-width: 60%;">Isi Saldo</span>
                    </div>
                    
                    <div id="rc-sn-box" style="margin-top: 1rem; background: rgba(255,255,255,0.05); padding: 0.8rem; border-radius: 8px; display: none;">
                        <span style="display: block; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 4px;">Catatan / SN:</span>
                        <span id="rc-sn" style="color: var(--primary); font-family: monospace; word-break: break-all;">-</span>
                    </div>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <img src="public/img/logo.png" alt="FandirrPay" style="height: 60px; ">
                    <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 5px;">Transaksi ini sah dan tersimpan di sistem.</p>
                </div>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
    
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const closeBtn = document.getElementById('sidebar-close');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        if(toggleBtn) toggleBtn.addEventListener('click', toggleSidebar);
        if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);
    </script>

    <script src="public/js/dashboard.js"></script>

</body>
</html>

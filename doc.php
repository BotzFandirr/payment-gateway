<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/layout/header-meta.php';

// 1. LOGIKA URL OTOMATIS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'];

// 2. Ambil API Key User
$user_api_key = "LOGIN_UNTUK_LIHAT_KEY";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT api_key FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if($u) $user_api_key = $u->api_key;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    
    <style>
        /* --- LAYOUT UTAMA --- */
        body { display: flex; background: #0f172a; color: #cbd5e1; font-family: sans-serif; margin: 0; overflow-x: hidden; }
        
        /* Sidebar */
        .doc-sidebar { 
            width: 280px; height: 100vh; position: fixed; top: 0; left: 0; 
            background: #1e293b; border-right: 1px solid rgba(255,255,255,0.05); 
            padding: 2rem 1rem; overflow-y: auto; z-index: 100; transition: transform 0.3s ease;
        }
        .doc-sidebar h3 { color: white; margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; font-size: 1.2rem; }
        .doc-menu { display: flex; flex-direction: column; gap: 5px; }
        .doc-menu a { 
            padding: 0.8rem 1rem; color: #94a3b8; text-decoration: none; border-radius: 8px; 
            transition: 0.3s; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
        }
        .doc-menu a:hover, .doc-menu a.active { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
        .doc-menu .label { 
            font-size: 0.75rem; text-transform: uppercase; color: #64748b; 
            margin: 1.5rem 0 0.5rem 0.5rem; font-weight: bold; letter-spacing: 1px;
        }

        /* Main Content */
        .doc-content { 
            margin-left: 280px; padding: 3rem 4rem; width: 100%; max-width: 1000px; 
            box-sizing: border-box; 
        }

        /* Typography */
        h1 { color: white; font-size: 2.5rem; margin-bottom: 0.5rem; }
        h2 { color: white; font-size: 1.8rem; margin-top: 4rem; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        h3 { color: #e2e8f0; font-size: 1.1rem; margin-top: 2rem; margin-bottom: 0.8rem; }
        p { line-height: 1.8; color: #94a3b8; margin-bottom: 1rem; }
        strong { color: white; }

        /* --- URL BOX & COPY BUTTON --- */
        .url-box {
            background: #020617; border: 1px solid #334155; border-radius: 8px;
            padding: 1rem; display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;
        }
        .method-badge { 
            padding: 6px 12px; border-radius: 6px; font-weight: bold; font-size: 0.85rem; 
            color: white; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .post { background: #10B981; }
        .get { background: #3B82F6; }
        
        .url-text { 
            font-family: 'Consolas', monospace; color: #e2e8f0; word-break: break-all; flex: 1;
        }

        .copy-btn {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1);
            color: white; padding: 6px 12px; border-radius: 6px; cursor: pointer;
            font-size: 0.8rem; transition: 0.2s; display: flex; align-items: center; gap: 5px;
        }
        .copy-btn:hover { background: rgba(255,255,255,0.2); }
        .copy-btn:active { transform: scale(0.95); }

        /* --- CODE BLOCKS --- */
        .code-wrapper { position: relative; margin: 1rem 0; }
        .code-wrapper pre { margin: 0; border-radius: 12px; border: 1px solid #334155; }
        .code-header {
            position: absolute; top: 10px; right: 10px; z-index: 10;
        }

        /* --- TABLE PARAMETERS --- */
        .param-table { width: 100%; border-collapse: collapse; margin: 1rem 0 2rem 0; overflow: hidden; border-radius: 8px; border: 1px solid #334155; }
        .param-table th { background: #1e293b; color: white; text-align: left; padding: 1rem; font-size: 0.9rem; border-bottom: 1px solid #334155; }
        .param-table td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; color: #cbd5e1; vertical-align: top; }
        .param-table tr:last-child td { border-bottom: none; }
        .param-table code { background: rgba(59, 130, 246, 0.1); color: #60a5fa; padding: 2px 6px; border-radius: 4px; }

        /* --- RESPONSIVE MOBILE --- */
        .menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; z-index: 200; width: 50px; height: 50px; border-radius: 50%; background: #3B82F6; border: none; color: white; font-size: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.5); cursor: pointer; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 90; backdrop-filter: blur(2px); }

        @media (max-width: 992px) {
            .doc-sidebar { transform: translateX(-100%); }
            .doc-sidebar.active { transform: translateX(0); box-shadow: 5px 0 20px rgba(0,0,0,0.5); }
            .sidebar-overlay.active { display: block; }
            .doc-content { margin-left: 0; padding: 2rem 1.5rem; width: 100%; }
            .menu-toggle { display: flex; align-items: center; justify-content: center; }
            h1 { font-size: 2rem; }
            .url-box { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .url-text { width: 100%; }
            .copy-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <button class="menu-toggle" onclick="toggleSidebar()">
        <i class="ri-menu-line"></i>
    </button>

    <aside class="doc-sidebar" id="docSidebar">
        <h3><i class="ri-book-read-fill" style="color: #3B82F6;"></i> API Docs v2</h3>
        <nav class="doc-menu">
            <a href="dashboard"><i class="ri-arrow-left-line"></i> Kembali ke Dashboard</a>
            
            <span class="label">Pendahuluan</span>
            <a href="#intro" class="active">Overview & Auth</a>

            <span class="label">Deposit</span>
            <a href="#deposit">Buat Pembayaran</a>
            <a href="#status">Cek Status (Polling)</a>
            <a href="#update">Update Status</a>
        </nav>
    </aside>

    <main class="doc-content">
        
        <section id="intro">
            <h1>Dokumentasi API</h1>
            <p>Selamat datang di dokumentasi resmi <strong>Fandirr Pay API</strong>. Layanan ini memungkinkan Anda membuat sistem pembayaran otomatis (QRIS Instant) untuk aplikasi atau website Anda.</p>
            
            <h3>Base URL</h3>
            <div class="url-box">
                <span class="url-text"><?php echo $baseUrl; ?>/api/payment</span>
                <button class="copy-btn" onclick="copyText('<?php echo $baseUrl; ?>/api/payment', this)">
                    <i class="ri-file-copy-line"></i> Salin URL
                </button>
            </div>

            <h3>Autentikasi</h3>
            <p>Setiap permintaan wajib menyertakan parameter <code>apikey</code>. Jaga kerahasiaan kunci ini.</p>
            <div class="code-wrapper">
                <div class="code-header">
                    <button class="copy-btn" onclick="copyText('<?php echo $user_api_key; ?>', this)">Salin Key</button>
                </div>
                <pre><code class="language-plaintext">apikey=<?php echo $user_api_key; ?></code></pre>
            </div>
        </section>


        <section id="deposit">
            <h2>1. Buat Pembayaran (Deposit)</h2>
            <p>Endpoint ini digunakan untuk membuat tagihan baru (QRIS). Sistem akan mengembalikan URL QR Code dan data transaksi.</p>
            
            <div class="url-box">
                <span class="method-badge post">POST</span>
                <span class="url-text"><?php echo $baseUrl; ?>/api/payment/deposit</span>
                <button class="copy-btn" onclick="copyText('<?php echo $baseUrl; ?>/api/payment/deposit', this)">
                    <i class="ri-file-copy-line"></i> Salin
                </button>
            </div>

            <h3>Parameter Header</h3>
            <table class="param-table">
                <tr><th>Key</th><th>Value</th><th>Wajib?</th></tr>
                <tr>
                    <td><code>Content-Type</code></td>
                    <td>application/json</td>
                    <td><span style="color:#10B981">Ya</span></td>
                </tr>
            </table>

            <h3>Parameter Body (JSON)</h3>
            <table class="param-table">
                <tr><th>Field</th><th>Tipe</th><th>Deskripsi</th></tr>
                <tr>
                    <td><code>amount</code></td>
                    <td>Number</td>
                    <td>Jumlah deposit (Min. 500). Contoh: <code>1000</code></td>
                </tr>
                <tr>
                    <td><code>fee</code></td>
                    <td>Number</td>
                    <td>Biaya admin tambahan (Opsional). Contoh: <code>150</code></td>
                </tr>
            </table>

            <h3>Contoh Request</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-bash">curl -X POST '<?php echo $baseUrl; ?>/api/payment/deposit?apikey=<?php echo $user_api_key; ?>' \
-H 'Content-Type: application/json' \
-d '{
    "amount": "1000",
    "fee": "150"
}'</code></pre>
            </div>

            <h3>Respon Sukses (200 OK)</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-json">{
  "status": "success",
  "message": "Permintaan deposit berhasil dibuat.",
  "data": {
    "orderId": "ORD-54af-1762872744352",
    "baseAmount": 1000,
    "adminFee": 150,
    "uniqueCode": 59,
    "amountToPay": 1209,
    "qrCodeUrl": "<?php echo $baseUrl; ?>/public/qris/demo.png",
    "expiryTime": "2025-11-11T21:57:24.346+07:00"
  }
}</code></pre>
            </div>
        </section>


        <section id="status">
            <h2>2. Cek Status (Polling)</h2>
            <p>Gunakan endpoint ini untuk mengecek status pembayaran secara berkala (Realtime). Lakukan request setiap 10-30 detik.</p>
            <p><span style="color: #F59E0B;">⚠️ Wajib menyertakan API Key.</span></p>

            <div class="url-box">
                <span class="method-badge get">GET</span>
                <span class="url-text"><?php echo $baseUrl; ?>/api/payment/status/{orderId}?apikey=...</span>
                <button class="copy-btn" onclick="copyText('<?php echo $baseUrl; ?>/api/payment/status/{orderId}?apikey=<?php echo $user_api_key; ?>', this)">
                    <i class="ri-file-copy-line"></i> Salin
                </button>
            </div>

            <h3>Contoh Request</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-bash">curl -X GET '<?php echo $baseUrl; ?>/api/payment/status/ORD-12345?apikey=<?php echo $user_api_key; ?>'</code></pre>
            </div>

            <h3>Respon: Lunas (Settlement)</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-json">{
  "_id": "6729c1d0f1a...",
  "orderId": "ORD-54af-1762254576493",
  "status": "settlement",
  "amount": 1273,
  "mutationId": "180027946",
  "paymentId": "QR-Z8L8I61O",
  "updatedAt": "2025-11-04T11:10:05.120Z"
}</code></pre>
            </div>

            <h3>Respon: Menunggu (Pending)</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-json">{
  "orderId": "ORD-54af-1762254576493",
  "status": "pending",
  "updatedAt": "2025-11-04T11:09:36.500Z"
}</code></pre>
            </div>
        </section>


        <section id="update">
            <h2>3. Update Status (Cancel/Expire)</h2>
            <p>Endpoint ini berguna jika User membatalkan pembayaran di frontend Anda, atau timer waktu habis.</p>
            <p><span style="color: #F59E0B;">⚠️ Wajib menyertakan API Key.</span></p>

            <div class="url-box">
                <span class="method-badge post">POST</span>
                <span class="url-text"><?php echo $baseUrl; ?>/api/payment/update-status?apikey=...</span>
                <button class="copy-btn" onclick="copyText('<?php echo $baseUrl; ?>/api/payment/update-status?apikey=<?php echo $user_api_key; ?>', this)">
                    <i class="ri-file-copy-line"></i> Salin
                </button>
            </div>

            <h3>Parameter Body (JSON)</h3>
            <table class="param-table">
                <tr><th>Field</th><th>Tipe</th><th>Deskripsi</th></tr>
                <tr>
                    <td><code>orderId</code></td>
                    <td>String</td>
                    <td>ID Order yang didapat saat membuat deposit.</td>
                </tr>
                <tr>
                    <td><code>newStatus</code></td>
                    <td>String</td>
                    <td>Pilih: <code>cancel</code> atau <code>expire</code></td>
                </tr>
            </table>

            <h3>Contoh Request</h3>
            <div class="code-wrapper">
                <div class="code-header"><button class="copy-btn" onclick="copyCode(this)">Salin</button></div>
                <pre><code class="language-bash">curl -X POST '<?php echo $baseUrl; ?>/api/payment/update-status?apikey=<?php echo $user_api_key; ?>' \
-H 'Content-Type: application/json' \
-d '{
    "orderId": "ORD-12345",
    "newStatus": "cancel"
}'</code></pre>
            </div>
        </section>

    </main>

    <script>
        // 1. Fungsi Toggle Sidebar Mobile
        function toggleSidebar() {
            document.getElementById('docSidebar').classList.toggle('active');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        // 2. Fungsi Copy Text Biasa (untuk URL / API Key)
        function copyText(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                showCopiedFeedback(btnElement);
            }).catch(err => {
                console.error('Gagal menyalin:', err);
            });
        }

        // 3. Fungsi Copy Code Block (Mengambil isi <code> terdekat)
        function copyCode(btnElement) {
            // Cari elemen <pre><code> di dekat tombol ini
            const wrapper = btnElement.closest('.code-wrapper');
            const codeBlock = wrapper.querySelector('code');
            
            if (codeBlock) {
                navigator.clipboard.writeText(codeBlock.innerText).then(() => {
                    showCopiedFeedback(btnElement);
                });
            }
        }

        // 4. Efek Visual Tombol (Berubah jadi "Disalin!")
        function showCopiedFeedback(btn) {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="ri-check-line"></i> Disalin!';
            btn.style.background = '#10B981';
            btn.style.borderColor = '#10B981';
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.background = '';
                btn.style.borderColor = '';
            }, 2000);
        }

        // 5. Smooth Scroll Menu
        document.querySelectorAll('.doc-menu a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                // Di mobile, tutup sidebar setelah klik menu
                if(window.innerWidth <= 992) toggleSidebar();
                
                document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
                
                document.querySelectorAll('.doc-menu a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>

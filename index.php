<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fandirr Pay - Solusi Pembayaran Digital</title>
    <?php include 'layout/header-meta.php'; ?>
    <link rel="stylesheet" href="public/css/style.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <style>
        html { scroll-behavior: smooth; }
        section { padding: 4rem 1rem; max-width: 1200px; margin: 0 auto; overflow: hidden; }

        .hero-badge {
            background: rgba(240, 196, 25, 0.1); color: var(--primary); border: 1px solid rgba(240,196,25,0.2);
            padding: .55rem 1rem; border-radius: 999px; font-size: .78rem; font-weight: 700;
            display: inline-flex; align-items: center; gap: .4rem;
        }
        .hero-actions { display:flex; gap:1rem; justify-content:center; margin-top:2rem; flex-wrap:wrap; }

        .stats-bar {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; text-align: center;
            background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass);
            padding: 1.6rem; border-radius: 18px; margin-top: -3rem; margin-bottom: 3.5rem;
            position: relative; z-index: 10; backdrop-filter: blur(10px);
        }
        .stat-item h2 { font-size: 2rem; color: var(--primary); margin: 0; }
        .stat-item p { color: var(--text-muted); font-size: .85rem; margin-top: .4rem; }

        .brand-strip {
            margin-top: 2rem;
            display: grid; grid-template-columns: repeat(5,1fr); gap: .8rem;
        }
        .brand-chip {
            border: 1px solid var(--border-glass); border-radius: 12px; padding: .85rem;
            text-align:center; color: var(--text-muted); background: rgba(255,255,255,.02); font-size: .82rem;
        }

        .feature-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-top: 1.5rem; }

        .dev-section { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center; }
        .code-window {
            background: #1e1e1e; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            font-family: 'Courier New', monospace; border: 1px solid rgba(255,255,255,0.1);
        }
        .window-header { background: #252526; padding: 10px 15px; display: flex; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .red { background: #ff5f56; } .yellow { background: #ffbd2e; } .green { background: #27c93f; }
        .code-content { padding: 20px; color: #d4d4d4; font-size: .84rem; line-height: 1.65; overflow-x: auto; }

        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-top: 2rem; }
        .pricing-card {
            background: rgba(255,255,255,0.02); border: 1px solid var(--border-glass); border-radius: 20px;
            padding: 2rem; text-align: center; transition: .3s; position: relative;
        }
        .pricing-card:hover { transform: translateY(-8px); border-color: var(--primary); }
        .pricing-card.popular { background: linear-gradient(145deg, rgba(240, 196, 25, 0.12), rgba(0,0,0,0)); border-color: var(--primary); }
        .price { font-size: 2.3rem; font-weight: 700; color: white; margin: 1rem 0; }
        .price span { font-size: .95rem; color: var(--text-muted); font-weight: 400; }
        .features-list { list-style: none; padding: 0; margin: 1.5rem 0; text-align: left; }
        .features-list li { margin-bottom: .72rem; color: #ccc; display: flex; align-items: center; gap: 10px; font-size: .9rem; }
        .features-list li i { color: var(--primary); flex-shrink: 0; }

        .testi-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-top:1.8rem; }
        .testi-card { border:1px solid var(--border-glass); border-radius:16px; padding:1.3rem; background:rgba(255,255,255,.02); }
        .testi-card p { color:#d4d4d8; font-size:.9rem; line-height:1.65; margin-bottom:.8rem; }
        .testi-name { font-size:.82rem; color:var(--text-muted); }

        .cta-block {
            margin-top:2rem; border:1px solid rgba(240,196,25,.25); border-radius:20px;
            background:linear-gradient(120deg, rgba(240,196,25,.15), rgba(255,255,255,.03)); padding:2rem;
            display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; align-items:center;
        }

        .footer-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; }

        @media (max-width: 992px) {
            .feature-grid, .testi-grid { grid-template-columns: 1fr 1fr; }
            .brand-strip { grid-template-columns: 1fr 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .navbar { padding: 1rem; }
            .logo { font-size: 1.2rem; }
            .nav-actions .btn { padding: 0.5rem 0.8rem; font-size: 0.8rem; }
            .hero { text-align: center; padding-top: 6rem; padding-bottom: 6rem; }
            .hero h1 { font-size: 2.2rem !important; line-height: 1.2; }
            .hero p { font-size: 0.9rem; padding: 0 .7rem; }
            .stats-bar { grid-template-columns: 1fr 1fr; margin-top: -2rem; }
            .dev-section, .feature-grid, .testi-grid, .footer-grid { grid-template-columns: 1fr; }
            .code-window { display: none; }
            .pricing-grid { grid-template-columns: 1fr; }
            .brand-strip { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar" data-aos="fade-down" data-aos-duration="800">
        <div class="logo">Fandirr<span>Pay</span>.</div>
        <div class="nav-actions">
            <a href="login" class="btn btn-outline" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Masuk</a>
            <a href="register" class="btn btn-primary" style="padding: 0.6rem 1.2rem;">Daftar</a>
        </div>
    </nav>

    <section class="hero">
        <div data-aos="zoom-in" data-aos-duration="1000" style="text-align: center;">
            <span class="hero-badge"><i class="ri-verified-badge-line"></i>Dipercaya merchant digital Indonesia</span>
        </div>

        <h1 data-aos="fade-up" data-aos-delay="200" class="gradient-text" style="text-align: center; margin-top: 1.2rem;">
            Payment Gateway Cepat <br>
            <span class="highlight-text">Untuk Bisnis yang Tumbuh</span>
        </h1>

        <p data-aos="fade-up" data-aos-delay="350" style="text-align: center; max-width: 680px; margin: 1.25rem auto;">
            Kelola pembayaran QRIS, transfer saldo, dan monitoring transaksi realtime dalam satu dashboard premium yang ramah pengguna & developer.
        </p>

        <div class="hero-actions" data-aos="fade-up" data-aos-delay="500">
            <a href="register" class="btn btn-primary"><i class="ri-rocket-line"></i> Mulai Gratis</a>
            <a href="doc" class="btn btn-outline"><i class="ri-book-read-line"></i> Lihat API Docs</a>
        </div>

        <div class="brand-strip" data-aos="fade-up" data-aos-delay="650">
            <div class="brand-chip">UMKM Online</div>
            <div class="brand-chip">Digital Product</div>
            <div class="brand-chip">SaaS Platform</div>
            <div class="brand-chip">Reseller Community</div>
            <div class="brand-chip">Creator Economy</div>
        </div>

        <div style="position: absolute; top: 22%; left: 50%; transform: translateX(-50%); width: 220px; height: 220px; background: var(--primary); filter: blur(130px); opacity: .22; z-index: -1;"></div>
    </section>

    <div class="stats-bar" data-aos="fade-up">
        <div class="stat-item"><h2>99.9%</h2><p>API Availability</p></div>
        <div class="stat-item"><h2>&lt; 3s</h2><p>Generate QRIS</p></div>
        <div class="stat-item"><h2>24/7</h2><p>Monitoring</p></div>
        <div class="stat-item"><h2>1 Panel</h2><p>Semua Transaksi</p></div>
    </div>

    <section id="features">
        <div style="text-align: center; margin-bottom: 1rem;" data-aos="fade-up">
            <h2 style="font-size: 2rem; margin-bottom: .7rem;">Kenapa Merchant Memilih Fandirr Pay?</h2>
            <p style="color: var(--text-muted);">Dibuat untuk performa, skalabilitas, dan kemudahan integrasi.</p>
        </div>

        <div class="feature-grid">
            <div class="glass-card" data-aos="fade-up" data-aos-delay="100" style="padding:1.7rem;">
                <i class="ri-flashlight-fill" style="font-size:2rem;color:var(--primary);"></i>
                <h3 style="margin-top:.75rem;">Settlement Cepat</h3>
                <p style="color: var(--text-muted); font-size: .9rem; margin-top:.45rem;">Dana masuk lebih cepat dengan proses verifikasi otomatis.</p>
            </div>
            <div class="glass-card" data-aos="fade-up" data-aos-delay="200" style="padding:1.7rem;">
                <i class="ri-shield-check-fill" style="font-size:2rem;color:var(--primary);"></i>
                <h3 style="margin-top:.75rem;">Aman & Terkontrol</h3>
                <p style="color: var(--text-muted); font-size: .9rem; margin-top:.45rem;">Proteksi transaksi + monitoring status realtime dari dashboard.</p>
            </div>
            <div class="glass-card" data-aos="fade-up" data-aos-delay="300" style="padding:1.7rem;">
                <i class="ri-code-box-line" style="font-size:2rem;color:var(--primary);"></i>
                <h3 style="margin-top:.75rem;">API Developer Friendly</h3>
                <p style="color: var(--text-muted); font-size: .9rem; margin-top:.45rem;">Dokumentasi jelas, endpoint ringkas, dan cepat diintegrasikan.</p>
            </div>
        </div>
    </section>

    <section class="dev-section">
        <div data-aos="fade-right">
            <span style="color: var(--primary); font-weight: 700; letter-spacing: 1px; font-size: .85rem;">DEVELOPER FRIENDLY</span>
            <h2 style="font-size: 2rem; margin: .8rem 0;">Integrasi API Tanpa Ribet</h2>
            <p style="color: var(--text-muted); margin-bottom: 1.4rem; line-height: 1.7;">
                Buat tagihan QRIS otomatis, cek status transaksi, dan update order dalam hitungan menit lewat endpoint REST JSON.
            </p>
            <a href="doc" class="btn btn-outline">Buka Dokumentasi <i class="ri-arrow-right-line"></i></a>
        </div>

        <div class="code-window" data-aos="fade-left">
            <div class="window-header"><div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div></div>
            <div class="code-content">
                curl -X POST /api/payment/deposit?apikey=YOUR_KEY \<br>
                &nbsp;&nbsp;-H "Content-Type: application/json" \<br>
                &nbsp;&nbsp;-d '{"amount": 50000, "fee": 700}'<br><br>
                // Response:<br>
                {<br>
                &nbsp;&nbsp;"status": "success",<br>
                &nbsp;&nbsp;"data": {"orderId": "ORD-...", "qrCodeUrl": "https://..."}<br>
                }
            </div>
        </div>
    </section>

    <section>
        <div style="text-align: center;" data-aos="fade-up">
            <h2 style="font-size: 2rem; margin-bottom: .8rem;">Paket Harga Transparan</h2>
            <p style="color: var(--text-muted);">Mulai gratis, upgrade saat bisnis sudah scale.</p>
        </div>

        <div class="pricing-grid">
            <div class="pricing-card" data-aos="fade-up" data-aos-delay="100">
                <h3>Starter</h3>
                <div class="price">0<span>/bulan</span></div>
                <ul class="features-list">
                    <li><i class="ri-checkbox-circle-fill"></i> Fee QRIS kompetitif</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Dashboard transaksi dasar</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Dukungan komunitas</li>
                </ul>
                <a href="register" class="btn btn-outline" style="width: 100%;">Daftar Gratis</a>
            </div>

            <div class="pricing-card popular" data-aos="fade-up" data-aos-delay="200">
                <div style="position: absolute; top: 0; right: 0; background: var(--primary); color: black; padding: 5px 15px; font-size: 0.7rem; font-weight: bold; border-bottom-left-radius: 10px;">RECOMMENDED</div>
                <h3>Business</h3>
                <div class="price">Rp 50k<span>/bulan</span></div>
                <ul class="features-list">
                    <li><i class="ri-checkbox-circle-fill"></i> API Unlimited</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Prioritas settlement</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Support prioritas</li>
                </ul>
                <a href="register" class="btn btn-primary" style="width: 100%;">Pilih Business</a>
            </div>
        </div>
    </section>

    <section>
        <div style="text-align:center;" data-aos="fade-up">
            <h2 style="font-size:2rem; margin-bottom:.6rem;">Apa Kata Pengguna?</h2>
            <p style="color:var(--text-muted);">Feedback dari merchant yang sudah memakai Fandirr Pay.</p>
        </div>
        <div class="testi-grid">
            <div class="testi-card" data-aos="fade-up" data-aos-delay="100">
                <p>"Dashboard-nya rapi banget. Tim kami jadi lebih cepat tracking transaksi harian."</p>
                <div class="testi-name">— Naufal, Digital Seller</div>
            </div>
            <div class="testi-card" data-aos="fade-up" data-aos-delay="200">
                <p>"Integrasi API cuma sebentar. Dokumentasinya jelas dan minim trial-error."</p>
                <div class="testi-name">— Raka, Backend Developer</div>
            </div>
            <div class="testi-card" data-aos="fade-up" data-aos-delay="300">
                <p>"Suka karena ada notifikasi realtime saat transfer masuk. Operasional jadi efisien."</p>
                <div class="testi-name">— Dita, UMKM Owner</div>
            </div>
        </div>

        <div class="cta-block" data-aos="zoom-in" data-aos-delay="100">
            <div>
                <h3 style="font-size:1.5rem; margin-bottom:.35rem;">Siap tingkatkan sistem pembayaran bisnismu?</h3>
                <p style="color:var(--text-muted);">Daftar sekarang dan mulai integrasi dalam hitungan menit.</p>
            </div>
            <div style="display:flex; gap:.7rem; flex-wrap:wrap;">
                <a href="register" class="btn btn-primary">Buat Akun</a>
                <a href="doc" class="btn btn-outline">Pelajari API</a>
            </div>
        </div>
    </section>

    <footer style="background: black; border-top: 1px solid var(--border-glass); padding: 3rem 1rem; margin-top: 4rem;">
        <div class="footer-grid">
            <div class="footer-col" style="text-align: left;">
                <div class="logo" style="margin-bottom: 1rem;">Fandirr<span>Pay</span>.</div>
                <p style="color: var(--text-muted); font-size: .85rem; line-height: 1.6;">Platform pembayaran digital modern untuk bisnis Anda.</p>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Menu</h4>
                <ul style="list-style:none; padding:0; display:grid; gap:.4rem;">
                    <li><a href="login" style="color:var(--text-muted);">Masuk</a></li>
                    <li><a href="register" style="color:var(--text-muted);">Daftar</a></li>
                    <li><a href="doc" style="color:var(--text-muted);">API Docs</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Legal</h4>
                <ul style="list-style:none; padding:0; display:grid; gap:.4rem;">
                    <li><a href="#" style="color:var(--text-muted);">Syarat & Ketentuan</a></li>
                    <li><a href="#" style="color:var(--text-muted);">Kebijakan Privasi</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Bantuan</h4>
                <ul style="list-style:none; padding:0; display:grid; gap:.4rem;">
                    <li><a href="#" style="color:var(--text-muted);">WhatsApp Admin</a></li>
                    <li><a href="#" style="color:var(--text-muted);">Email Support</a></li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 2rem; padding-top: 1.5rem; color: var(--text-muted); font-size: .8rem;">
            &copy; 2026 Fandirr Pay. All rights reserved.
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50, duration: 800 });
    </script>
</body>
</html>

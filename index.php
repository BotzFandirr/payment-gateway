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
        
        /* Container Setting */
        section { 
            padding: 4rem 1rem; 
            max-width: 1200px; 
            margin: 0 auto; 
            overflow: hidden; /* Mencegah scroll samping di HP */
        }
        
        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border-glass);
            padding: 2rem;
            border-radius: 20px;
            margin-top: -3rem;
            margin-bottom: 4rem;
            position: relative;
            z-index: 10;
            backdrop-filter: blur(10px);
        }
        .stat-item h2 { font-size: 2.5rem; color: var(--primary); margin: 0; }
        .stat-item p { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem; }

        /* Developer Section Layout */
        .dev-section {
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 3rem; 
            align-items: center;
        }

        /* Code Window */
        .code-window {
            background: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            font-family: 'Courier New', monospace;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .window-header { background: #252526; padding: 10px 15px; display: flex; gap: 8px; }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .red { background: #ff5f56; } .yellow { background: #ffbd2e; } .green { background: #27c93f; }
        .code-content { padding: 20px; color: #d4d4d4; font-size: 0.85rem; line-height: 1.6; overflow-x: auto; }
        .keyword { color: #569cd6; } .string { color: #ce9178; } .function { color: #dcdcaa; }

        /* Pricing Grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .pricing-card {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: 0.3s;
            position: relative;
        }
        .pricing-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .pricing-card.popular { background: linear-gradient(145deg, rgba(240, 196, 25, 0.1), rgba(0,0,0,0)); border-color: var(--primary); }
        .price { font-size: 2.5rem; font-weight: 700; color: white; margin: 1rem 0; }
        .price span { font-size: 1rem; color: var(--text-muted); font-weight: 400; }
        .features-list { list-style: none; padding: 0; margin: 2rem 0; text-align: left; }
        .features-list li { margin-bottom: 0.8rem; color: #ccc; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
        .features-list li i { color: var(--primary); flex-shrink: 0; }

        /* Footer Grid */
        .footer-grid {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem;
        }
        
        /* =========================================
           MOBILE RESPONSIVE (LAYOUT HP)
           ========================================= */
        @media (max-width: 768px) {
            /* Navbar */
            .navbar { padding: 1rem; }
            .logo { font-size: 1.2rem; }
            .nav-actions .btn { padding: 0.5rem 0.8rem; font-size: 0.8rem; }

            /* Hero */
            .hero { text-align: center; padding-top: 6rem; padding-bottom: 6rem; }
            .hero h1 { font-size: 2.2rem !important; line-height: 1.2; }
            .hero p { font-size: 0.9rem; padding: 0 1rem; }
            
            /* Stats Bar menjadi 2 kolom */
            .stats-bar { grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: -2rem; padding: 1.5rem; }
            .stat-item h2 { font-size: 1.8rem; }
            
            /* Developer Section Stack (Atas-Bawah) */
            .dev-section { grid-template-columns: 1fr; text-align: center; gap: 2rem; }
            .code-window { display: none; } /* Sembunyikan kode rumit di HP */
            
            /* Pricing */
            .pricing-grid { grid-template-columns: 1fr; padding: 0 1rem; }
            
            /* Footer Stack */
            .footer-grid { grid-template-columns: 1fr; text-align: center; gap: 2.5rem; }
            .footer-col ul { display: flex; flex-direction: column; gap: 0.5rem; align-items: center; }
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
            <span style="background: rgba(240, 196, 25, 0.1); color: var(--primary); padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(240,196,25,0.2);">
                🚀 Platform Pembayaran #1 Indonesia
            </span>
        </div>
        
        <h1 data-aos="fade-up" data-aos-delay="200" class="gradient-text" style="text-align: center; margin-top: 1.5rem;">
            Terima Pembayaran <br>
            <span class="highlight-text">Tanpa Batas.</span>
        </h1>
        
        <p data-aos="fade-up" data-aos-delay="400" style="text-align: center; max-width: 600px; margin: 1.5rem auto;">
            Solusi pembayaran digital dengan fee terendah, pencairan instan, dan integrasi QRIS otomatis. Fokus berkarya, biarkan kami mengurus sistem keuangannya.
        </p>

        <div data-aos="fade-up" data-aos-delay="600" style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
            <a href="register" class="btn btn-primary"><i class="ri-rocket-line"></i> Mulai Sekarang</a>
            <a href="#features" class="btn btn-outline"><i class="ri-arrow-down-line"></i> Info</a>
        </div>

        <div style="position: absolute; top: 20%; left: 50%; transform: translateX(-50%); width: 200px; height: 200px; background: var(--primary); filter: blur(120px); opacity: 0.25; z-index: -1;"></div>
    </section>

    <div class="stats-bar" data-aos="fade-up">
        <div class="stat-item">
            <h2>10k+</h2>
            <p>User Aktif</p>
        </div>
        <div class="stat-item">
            <h2>Rp 5M+</h2>
            <p>Transaksi</p>
        </div>
        <div class="stat-item">
            <h2>99%</h2>
            <p>Uptime</p>
        </div>
        <div class="stat-item">
            <h2>24/7</h2>
            <p>Support</p>
        </div>
    </div>

    <section id="features">
        <div style="text-align: center; margin-bottom: 3rem;" data-aos="fade-up">
            <h2 style="font-size: 2rem; margin-bottom: 1rem;">Kenapa Kami?</h2>
            <p style="color: var(--text-muted);">Teknologi terbaik untuk bisnis Anda.</p>
        </div>

        <div class="pricing-grid" style="margin-top: 0;"> <div class="glass-card" data-aos="fade-up" data-aos-delay="100" style="text-align: center; padding: 2rem;">
                <i class="ri-flashlight-fill" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i>
                <h3>Settlement Instan</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Dana masuk ke rekening dalam hitungan detik.</p>
            </div>
            <div class="glass-card" data-aos="fade-up" data-aos-delay="200" style="text-align: center; padding: 2rem;">
                <i class="ri-shield-check-fill" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i>
                <h3>Sangat Aman</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Enkripsi SSL 256-bit dan proteksi anti-fraud.</p>
            </div>
            <div class="glass-card" data-aos="fade-up" data-aos-delay="300" style="text-align: center; padding: 2rem;">
                <i class="ri-qr-code-line" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; display: block;"></i>
                <h3>QRIS Otomatis</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Generate QRIS dinamis tanpa upload manual.</p>
            </div>
        </div>
    </section>

    <section class="dev-section">
        <div data-aos="fade-right">
            <span style="color: var(--primary); font-weight: bold; letter-spacing: 1px; font-size: 0.9rem;">DEVELOPER FRIENDLY</span>
            <h2 style="font-size: 2rem; margin: 1rem 0;">Integrasi API Mudah</h2>
            <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">
                Dokumentasi lengkap dan endpoint RESTful JSON. Cukup 5 baris kode untuk membuat pembayaran QRIS otomatis.
            </p>
            <a href="register" class="btn btn-outline">Lihat Dokumentasi <i class="ri-arrow-right-line"></i></a>
        </div>
        
        <div class="code-window" data-aos="fade-left">
            <div class="window-header">
                <div class="dot red"></div> <div class="dot yellow"></div> <div class="dot green"></div>
            </div>
            <div class="code-content">
                <span class="keyword">curl</span> -X POST /api/deposit \<br>
                &nbsp;&nbsp;-d <span class="string">'{"amount": 50000}'</span> \<br>
                &nbsp;&nbsp;-H <span class="string">"Authorization: Bearer KEY"</span><br><br>
                <span style="color: #6a9955;">// Response:</span><br>
                {<br>
                &nbsp;&nbsp;<span class="string">"status"</span>: <span class="keyword">true</span>,<br>
                &nbsp;&nbsp;<span class="string">"qr_url"</span>: <span class="string">"https://qr..."</span><br>
                }
            </div>
        </div>
    </section>

    <section>
        <div style="text-align: center;" data-aos="fade-up">
            <h2 style="font-size: 2rem; margin-bottom: 1rem;">Biaya Layanan</h2>
            <p style="color: var(--text-muted);">Transparan, tanpa biaya tersembunyi.</p>
        </div>

        <div class="pricing-grid">
            <div class="pricing-card" data-aos="fade-up" data-aos-delay="100">
                <h3>Starter</h3>
                <div class="price">0%<span>/bln</span></div>
                <ul class="features-list">
                    <li><i class="ri-checkbox-circle-fill"></i> Fee QRIS 0.7%</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Settlement H+1</li>
                    <li><i class="ri-checkbox-circle-fill"></i> Dashboard Basic</li>
                </ul>
                <a href="register" class="btn btn-outline" style="width: 100%;">Daftar Gratis</a>
            </div>

            <div class="pricing-card popular" data-aos="fade-up" data-aos-delay="200">
                <div style="position: absolute; top: 0; right: 0; background: var(--primary); color: black; padding: 5px 15px; font-size: 0.7rem; font-weight: bold; border-bottom-left-radius: 10px;">POPULAR</div>
                <h3>Business</h3>
                <div class="price">Rp 50k<span>/bln</span></div>
                <ul class="features-list">
                    <li><i class="ri-checkbox-circle-fill"></i> Fee QRIS 0.4%</li>
                    <li><i class="ri-checkbox-circle-fill"></i> <strong>Settlement Instan</strong></li>
                    <li><i class="ri-checkbox-circle-fill"></i> API Unlimited</li>
                </ul>
                <a href="register" class="btn btn-primary" style="width: 100%;">Pilih Business</a>
            </div>
        </div>
    </section>

    <footer style="background: black; border-top: 1px solid var(--border-glass); padding: 3rem 1rem; margin-top: 4rem;">
        <div class="footer-grid">
            <div class="footer-col" style="text-align: left;">
                <div class="logo" style="margin-bottom: 1rem;">Fandirr<span>Pay</span>.</div>
                <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.6;">
                    Platform pembayaran digital modern untuk bisnis Anda.
                </p>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Menu</h4>
                <ul style="list-style:none; padding:0;">
                    <li><a href="login" style="color:var(--text-muted); text-decoration:none;">Masuk</a></li>
                    <li><a href="register" style="color:var(--text-muted); text-decoration:none;">Daftar</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Legal</h4>
                <ul style="list-style:none; padding:0;">
                    <li><a href="#" style="color:var(--text-muted); text-decoration:none;">Syarat & Ketentuan</a></li>
                    <li><a href="#" style="color:var(--text-muted); text-decoration:none;">Privasi</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4 style="color:white; margin-bottom:1rem;">Hubungi</h4>
                <ul style="list-style:none; padding:0;">
                    <li><a href="#" style="color:var(--text-muted); text-decoration:none;">WhatsApp Admin</a></li>
                    <li><a href="#" style="color:var(--text-muted); text-decoration:none;">Email Support</a></li>
                </ul>
            </div>
        </div>
        <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 3rem; padding-top: 2rem; color: var(--text-muted); font-size: 0.8rem;">
            &copy; 2025 Fandirr Pay. All rights reserved.
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ once: true, offset: 50, duration: 800 });
    </script>
</body>
</html>

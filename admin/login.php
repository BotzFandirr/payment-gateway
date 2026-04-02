<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $userId = trim($_POST['user_id']);
    $password = $_POST['password'];

    try {
        // Cek user dengan role admin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'admin'");
        $stmt->execute([$userId]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin->password)) {
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_role'] = 'admin';
            
            echo json_encode(['status' => 'success', 'message' => 'Akses Diterima. Mengalihkan...', 'redirect' => 'dashboard']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID atau Password salah!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error database.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Fandirr Pay</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* TEMA KHUSUS ADMIN (Nuansa Biru Gelap) */
        :root { --primary: #3B82F6; } 
        body { background: #0f172a; }
        
        /* Override Glass Card agar lebih gelap/biru */
        .glass-card { 
            background: rgba(30, 41, 59, 0.7); 
            border: 1px solid rgba(59, 130, 246, 0.2); 
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }

        /* Toggle Password Style */
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 45px; }
        .toggle-password {
            position: absolute; right: 15px; top: 50%;
            transform: translateY(-50%); cursor: pointer;
            color: #94a3b8; font-size: 1.2rem;
        }
        .toggle-password:hover { color: var(--primary); }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh;">

    <div class="glass-card" style="width: 100%; max-width: 400px; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);">
                <i class="ri-admin-line" style="font-size: 2rem; color: white;"></i>
            </div>
            <h2 style="font-weight: 700;">Admin Panel</h2>
            <p style="color: #94a3b8;">Area Terbatas. Khusus Staff.</p>
        </div>

        <form id="adminLoginForm">
            <div class="form-group">
                <label class="form-label">Admin ID</label>
                <input type="text" name="user_id" class="form-control" placeholder="Masukkan ID Admin" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="********" required>
                    <i class="ri-eye-off-line toggle-password" onclick="togglePassword()"></i>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--primary); color: white; margin-top: 1rem;">Masuk Dashboard</button>
        </form>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="../" style="color: #64748b; font-size: 0.8rem; text-decoration: none;">&larr; Kembali ke Website Utama</a>
        </div>
    </div>

    <div id="modalLoading" class="modal-overlay">
        <div class="modal-box">
            <div class="spinner-wrapper"><div class="loading-spinner"></div></div>
            <h3 class="modal-title">Memverifikasi...</h3>
            <p class="modal-message">Mengecek kredensial Anda.</p>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box modal-success">
            <div class="modal-icon-wrapper"><i class="ri-shield-check-line"></i></div>
            <h3 class="modal-title">Akses Diterima!</h3>
            <p class="modal-message" id="successMsg">Mengalihkan ke panel...</p>
        </div>
    </div>

    <div id="modalError" class="modal-overlay">
        <div class="modal-box modal-error">
            <div class="modal-icon-wrapper"><i class="ri-error-warning-line"></i></div>
            <h3 class="modal-title">Akses Ditolak</h3>
            <p class="modal-message" id="errorMsg">ID atau Password salah.</p>
            <button class="modal-btn" type="button" onclick="document.getElementById('modalError').classList.remove('active')">Coba Lagi</button>
        </div>
    </div>

    <script>
        // Toggle Password
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
            } else {
                input.type = "password";
                icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
            }
        }

        // Logic Submit Form
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const modalLoading = document.getElementById('modalLoading');
            const modalSuccess = document.getElementById('modalSuccess');
            const modalError = document.getElementById('modalError');
            
            // Tampilkan Loading
            modalLoading.classList.add('active');

            const formData = new FormData(this);

            try {
                const res = await fetch('login', { method: 'POST', body: formData });
                const data = await res.json();
                
                // Delay sedikit agar loading terlihat natural
                setTimeout(() => {
                    modalLoading.classList.remove('active');

                    if (data.status === 'success') {
                        // SUKSES
                        document.getElementById('successMsg').innerText = data.message;
                        modalSuccess.classList.add('active');
                        
                        // Redirect setelah modal sukses muncul sebentar
                        setTimeout(() => { 
                            window.location.href = data.redirect; 
                        }, 1500);

                    } else {
                        // GAGAL
                        document.getElementById('errorMsg').innerText = data.message;
                        modalError.classList.add('active');
                    }
                }, 800);

            } catch (e) {
                modalLoading.classList.remove('active');
                document.getElementById('errorMsg').innerText = "Terjadi kesalahan koneksi.";
                modalError.classList.add('active');
            }
        });
    </script>
</body>
</html>

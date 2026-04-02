// public/js/auth-handler.js

document.addEventListener('DOMContentLoaded', () => {
    
    // --- UTILITY: DELAY ---
    // Tambahkan sedikit delay buatan agar loading spinner sempat terlihat (estetika)
    // Kalau server terlalu cepat (localhost), loading cuma kedip. Ini membuatnya minimal 0.8 detik.
    const delay = (ms) => new Promise(res => setTimeout(res, ms));

    // 1. HANDLER REGISTER
    const registerForm = document.querySelector('form[action$="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            // Tampilkan Loading Dulu
            showLoading();

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            try {
                // Request ke Server + Delay Estetika
                const [response] = await Promise.all([
                    fetch('api/auth/register.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    }),
                    delay(800) // Minimal loading 0.8 detik
                ]);

                const result = await response.json();

                // Tutup Loading -> Tampilkan Hasil
                hideLoading();

                if (result.status === 'success') {
                    showModal('success', 'Registrasi Berhasil', result.message, 'login');
                } else {
                    showModal('error', 'Gagal Mendaftar', result.message);
                }
            } catch (err) {
                hideLoading();
                showModal('error', 'Error Sistem', 'Terjadi kesalahan koneksi.');
            }
        });
    }

    // 2. HANDLER LOGIN
    const loginForm = document.querySelector('form[action$="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Tampilkan Loading Dulu
            showLoading();

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const [response] = await Promise.all([
                    fetch('api/auth/login.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    }),
                    delay(800) // Minimal loading 0.8 detik
                ]);

                const result = await response.json();

                // Tutup Loading -> Tampilkan Hasil
                hideLoading();

                if (result.status === 'success') {
                    localStorage.setItem('apiKey', result.api_key);
                    showModal('success', 'Login Berhasil', 'Selamat datang kembali!', result.redirect);
                } else {
                    showModal('error', 'Gagal Masuk', result.message);
                }
            } catch (err) {
                hideLoading();
                showModal('error', 'Error Sistem', 'Tidak dapat menghubungi server.');
            }
        });
    }
});

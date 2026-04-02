document.addEventListener('DOMContentLoaded', () => {
    
    // --- GLOBAL VARS ---
    let pollingInterval = null;
    let countdownInterval = null;
    let currentOrderId = null;
    let allTransactions = []; // Menyimpan data untuk fitur Tab Filter

    // --- HELPERS: CUSTOM MODAL SYSTEM ---
    const showSuccess = (title, msg) => {
        document.getElementById('sysSuccessTitle').innerText = title;
        document.getElementById('sysSuccessMsg').innerText = msg;
        document.getElementById('sysModalSuccess').classList.add('active');
    };

    const showError = (title, msg) => {
        document.getElementById('sysErrorTitle').innerText = title;
        document.getElementById('sysErrorMsg').innerText = msg;
        document.getElementById('sysModalError').classList.add('active');
    };

    const showConfirm = (title, msg, onYes) => {
        document.getElementById('sysConfirmTitle').innerText = title;
        document.getElementById('sysConfirmMsg').innerText = msg;
        
        const btnYes = document.getElementById('sysConfirmBtnYes');
        // Clone tombol untuk menghapus event listener lama
        const newBtnYes = btnYes.cloneNode(true);
        btnYes.parentNode.replaceChild(newBtnYes, btnYes);
        
        newBtnYes.addEventListener('click', () => {
            document.getElementById('sysModalConfirm').classList.remove('active');
            onYes();
        });

        document.getElementById('sysModalConfirm').classList.add('active');
    };

    // Helper Format Rupiah (Tanpa Desimal)
    const formatRupiah = (num) => {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR', 
            minimumFractionDigits: 0, 
            maximumFractionDigits: 0 
        }).format(num);
    };

    // --- 1. LOAD DATA USER ---
    const loadUserData = async () => {
        try {
            const response = await fetch('api/user/profile.php');
            const result = await response.json();

            if (result.status === 'success') {
                document.querySelectorAll('.username-display').forEach(el => {
                    el.textContent = result.data.username;
                });
                // Update Saldo
                document.getElementById('balance-display').innerText = formatRupiah(result.data.balance);
            }
        } catch (error) { console.error(error); }
    };

    // --- 2. LOAD RIWAYAT & STATISTIK (FITUR BARU) ---
    const loadHistory = async () => {
        const tbody = document.getElementById('history-list');
        if (!tbody) return;
        
        try {
            const response = await fetch('api/transaction/history.php');
            const result = await response.json();

            if (result.status === 'success') {
                // A. UPDATE KARTU RINGKASAN (STATS)
                if (result.summary) {
                    const elToday = document.getElementById('stats-today');
                    const elWeek = document.getElementById('stats-week');
                    const elMonth = document.getElementById('stats-month');

                    if(elToday) elToday.innerText = formatRupiah(result.summary.today);
                    if(elWeek) elWeek.innerText = formatRupiah(result.summary.week);
                    if(elMonth) elMonth.innerText = formatRupiah(result.summary.month);
                }

                // B. SIMPAN DATA UNTUK FILTER TAB
                allTransactions = result.data;

                // C. RENDER TABEL (Default: Tampilkan Semua)
                renderTable('all');

                // D. AUTO RESTORE MODAL (Jika ada yang pending & belum expired)
                if (allTransactions.length > 0) {
                    const latestTrx = allTransactions[0];
                    if (latestTrx.status === 'pending' && !currentOrderId && !document.getElementById('deposit-modal').classList.contains('active')) {
                        const expiryDate = new Date(latestTrx.expiry_time.replace(' ', 'T'));
                        if (expiryDate > new Date()) {
                            restoreDepositModal(latestTrx);
                        }
                    }
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 2rem; color: var(--text-muted);">Belum ada transaksi.</td></tr>';
            }
        } catch (error) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Gagal memuat data.</td></tr>';
        }
    };

    // --- 3. LOGIKA FILTER TABEL (TAB MENU) ---
    window.filterHistory = (status, btnElement) => {
        // Update UI Tombol Aktif
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        // Render Ulang Tabel
        renderTable(status);
    };

        // --- 10. MODAL STRUK TRANSAKSI (RECEIPT) - FIXED LOGIC ---
    
    // Perbaikan: Menerima Order ID (String), bukan Index (Angka)
    window.openReceipt = (targetOrderId) => {
        // Cari data asli berdasarkan Order ID yang unik
        const trx = allTransactions.find(t => t.order_id === targetOrderId);
        
        if (!trx) {
            console.error("Data transaksi tidak ditemukan untuk ID:", targetOrderId);
            return;
        }

        // 1. Set Icon & Header Color berdasarkan Status
        const header = document.querySelector('#receipt-modal .modal-box > div:first-child');
        const icon = document.getElementById('rc-icon');
        const statusTxt = document.getElementById('rc-status');
        
        // Reset Style
        header.style.background = 'var(--primary)';
        header.style.color = 'black';

        if (trx.status === 'settlement') {
            header.style.background = '#10B981'; // Hijau
            icon.className = 'ri-check-double-line';
            statusTxt.innerText = 'BERHASIL';
        } else if (trx.status === 'pending') {
            header.style.background = '#F59E0B'; // Kuning
            header.style.color = 'black';
            icon.className = 'ri-loader-4-line';
            statusTxt.innerText = 'PENDING';
        } else {
            header.style.background = '#EF4444'; // Merah
            header.style.color = 'white';
            icon.className = 'ri-close-circle-line';
            statusTxt.innerText = 'GAGAL';
        }

        // 2. Isi Data Teks
        document.getElementById('rc-date').innerText = new Date(trx.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        document.getElementById('rc-amount').innerText = `Rp ${parseInt(trx.amount).toLocaleString('id-ID')}`;
        
        // Tentukan Tipe
        let typeName = "Transaksi";
        
        // Logika Tipe Transaksi
        if (trx.order_id.startsWith('TRX') || trx.order_id.startsWith('ORD')) {
            typeName = "Deposit Saldo";
        } else if (!trx.order_id) { 
            // Handle jika order_id kosong (kasus jarang)
            typeName = "Transaksi Lain";
        }

        document.getElementById('rc-type').innerText = typeName;
        document.getElementById('rc-orderid').innerText = `#${trx.order_id}`;
        // Gunakan Note jika ada, jika tidak gunakan Tipe
        document.getElementById('rc-desc').innerText = trx.note ? trx.note : typeName;

        // 3. Tampilkan Catatan / SN jika ada
        const snBox = document.getElementById('rc-sn-box');
        if (trx.note || trx.sn) {
             snBox.style.display = 'block';
             document.getElementById('rc-sn').innerText = trx.note || trx.sn;
        } else {
             snBox.style.display = 'none';
        }

        // 4. Buka Modal
        document.getElementById('receipt-modal').classList.add('active');
    };

    // --- RENDER TABLE FIXED ---
    const renderTable = (filterStatus) => {
        const tbody = document.getElementById('history-list');
        tbody.innerHTML = '';

        const filteredData = allTransactions.filter(trx => {
            if (filterStatus === 'all') return true;
            if (filterStatus === 'failed') return ['cancel', 'expire', 'deny'].includes(trx.status);
            return trx.status === filterStatus;
        });

        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:2rem; color: var(--text-muted);">Tidak ada data di kategori ini.</td></tr>';
            return;
        }

        filteredData.forEach(trx => {
            let badgeClass = 'pending';
            let badgeText = trx.status;

            if (trx.status === 'settlement') {
                badgeClass = 'success'; badgeText = 'Sukses';
            } else if (trx.status === 'expire') {
                badgeClass = 'failed'; badgeText = 'Expired';
            } else if (trx.status === 'cancel' || trx.status === 'deny') {
                badgeClass = 'failed'; badgeText = 'Dibatalkan';
            }

            const date = new Date(trx.created_at).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
            });
            const amount = parseInt(trx.amount).toLocaleString('id-ID');

            // PERBAIKAN DI SINI:
            // Mengirim String Order ID ('ORD-123') bukan Index (1, 2, 3)
            const row = `
                <tr onclick="openReceipt('${trx.order_id}')" style="cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                    <td><span style="font-family: monospace; color: var(--primary);">#${trx.order_id.substring(0, 12)}...</span></td>
                    <td>${trx.note ? 'Transfer/Bayar' : 'Deposit Saldo'}</td>
                    <td>${date}</td>
                    <td style="color: ${trx.status === 'settlement' ? '#10B981' : '#fff'}; font-weight: bold;">
                        Rp ${amount}
                    </td>
                    <td><span class="badge ${badgeClass}">${badgeText}</span></td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    };


    // --- 4. SISTEM DEPOSIT ---
    
    // Helper Restore Modal
    const restoreDepositModal = (trx) => {
        document.getElementById('deposit-modal').classList.add('active');
        document.getElementById('deposit-step-1').style.display = 'none';
        document.getElementById('deposit-step-2').style.display = 'block';
        document.getElementById('qris-image').src = trx.qr_url;
        
        const restoreAmount = parseInt(trx.amount).toLocaleString('id-ID');
        document.getElementById('qris-amount').innerText = `Rp ${restoreAmount}`;
        
        currentOrderId = trx.order_id;
        startTimer(trx.expiry_time);
        startPolling(currentOrderId);
    };

    const resetDepositModal = () => {
        clearInterval(pollingInterval);
        clearInterval(countdownInterval);
        currentOrderId = null;
        
        const step1 = document.getElementById('deposit-step-1');
        const loading = document.getElementById('deposit-loading');
        const step2 = document.getElementById('deposit-step-2');
        
        if(step1) step1.style.display = 'block';
        if(loading) loading.style.display = 'none';
        if(step2) step2.style.display = 'none';
        
        const amountInput = document.getElementById('deposit-amount');
        if(amountInput) amountInput.value = '';
    };

    const startTimer = (expiryTimeStr) => {
        const expiryDate = new Date(expiryTimeStr.replace(' ', 'T')); 
        const display = document.getElementById('countdown-timer');

        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = expiryDate.getTime() - now;

            if (distance < 0) {
                clearInterval(countdownInterval);
                clearInterval(pollingInterval);
                if(display) display.innerText = "EXPIRED";
                
                showError("Waktu Habis", "Batas waktu pembayaran telah berakhir.");
                resetDepositModal();
                document.getElementById('deposit-modal').classList.remove('active');
                loadHistory(); // Refresh status ke Expired
            } else {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                if(display) display.innerText = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }
        }, 1000);
    };

    const startPolling = (orderId) => {
        if (pollingInterval) clearInterval(pollingInterval);

        // Polling setiap 2 detik
        pollingInterval = setInterval(async () => {
            try {
                if (!currentOrderId) return;
                const response = await fetch(`api/payment/status.php?order_id=${orderId}`);
                const result = await response.json();

                // HANYA SUKSES JIKA status data == 'settlement'
                if (result.data && result.data.status === 'settlement') {
                    clearInterval(pollingInterval);
                    clearInterval(countdownInterval);
                    
                    showSuccess("Pembayaran Diterima!", "Saldo Anda telah ditambahkan otomatis.");
                    
                    document.getElementById('deposit-modal').classList.remove('active');
                    resetDepositModal();
                    loadUserData(); // Update saldo di header
                    loadHistory();  // Update list & stats
                }
            } catch (error) { console.error(error); }
        }, 10000);
    };

    // Fungsi Handle Tombol Close / Batal
    const handleCancelTransaction = () => {
        if (!currentOrderId) {
            // Jika tidak ada order aktif, tutup saja
            resetDepositModal();
            document.getElementById('deposit-modal').classList.remove('active');
            return;
        }

        // Konfirmasi sebelum tutup
        showConfirm("Batalkan Transaksi?", "Tagihan ini akan dihapus jika Anda menutupnya.", async () => {
            try {
                const response = await fetch('api/payment/update-status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ orderId: currentOrderId, newStatus: 'cancel' })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    clearInterval(pollingInterval);
                    clearInterval(countdownInterval);
                    resetDepositModal();
                    document.getElementById('deposit-modal').classList.remove('active');
                    loadHistory();
                }
            } catch (e) { showError("Error", "Gagal membatalkan."); }
        });
    };

    // Event Listener: Tombol Proses Deposit
    const btnProcess = document.getElementById('btn-process-deposit');
    if (btnProcess) {
        btnProcess.addEventListener('click', async () => {
            const amountInput = document.getElementById('deposit-amount');
            const amount = amountInput ? amountInput.value : 0;
            
            if (!amount || amount < 1000) {
                showError("Nominal Tidak Valid", "Minimal deposit adalah Rp 1.000");
                return;
            }

            document.getElementById('deposit-step-1').style.display = 'none';
            document.getElementById('deposit-loading').style.display = 'block';

            try {
                const response = await fetch('api/payment/deposit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ amount: amount })
                });
                const result = await response.json();

                if (result.status === 'success') {
                    document.getElementById('deposit-loading').style.display = 'none';
                    document.getElementById('deposit-step-2').style.display = 'block';
                    document.getElementById('qris-image').src = result.data.qr_url;
                    
                    const totalBayar = parseInt(result.data.amount_total).toLocaleString('id-ID');
                    document.getElementById('qris-amount').innerText = `Rp ${totalBayar}`;
                    
                    currentOrderId = result.data.order_id;
                    startTimer(result.data.expiry_time);
                    startPolling(currentOrderId);
                } else {
                    showError("Gagal", result.message);
                    resetDepositModal();
                }
            } catch (error) {
                showError("Error", "Gagal menghubungi server.");
                resetDepositModal();
            }
        });
    }

    // Event Listener: Tombol Batal & Close
    const btnCancel = document.getElementById('btn-cancel-deposit');
    if (btnCancel) btnCancel.addEventListener('click', handleCancelTransaction);

    document.querySelectorAll('#deposit-modal .close-btn, #deposit-modal #close-modal-btn').forEach(btn => {
        btn.addEventListener('click', handleCancelTransaction);
    });

    // --- 5. API KEY ---
    const loadApiKey = async () => {
        const inputKey = document.getElementById('user-api-key');
        const previewKey = document.getElementById('preview-key');
        if (!inputKey) return;

        try {
            const response = await fetch('api/user/apikey.php');
            const result = await response.json();

            if (result.status === 'success') {
                inputKey.value = result.data.api_key;
                if(previewKey) previewKey.innerText = result.data.api_key.substring(0, 10) + "...";
            } else {
                inputKey.value = "Gagal memuat API Key";
            }
        } catch (error) { inputKey.value = "Error koneksi"; }
    };

    window.copyApiKey = () => {
        const copyText = document.getElementById("user-api-key");
        if(copyText) {
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            showSuccess("Disalin", "API Key berhasil disalin ke clipboard!");
        }
    };

    window.confirmRegenerateApi = () => {
        showConfirm("Buat API Key Baru?", "API Key lama akan hangus dan aplikasi yang menggunakannya akan berhenti.", async () => {
            try {
                const response = await fetch('api/user/apikey.php', { method: 'POST' });
                const result = await response.json();
                if (result.status === 'success') {
                    document.getElementById('user-api-key').value = result.data.api_key;
                    document.getElementById('preview-key').innerText = result.data.api_key.substring(0, 10) + "...";
                    showSuccess("Berhasil", "API Key baru telah dibuat.");
                }
            } catch (error) { showError("Gagal", "Tidak bisa membuat API Key baru."); }
        });
    };

    // --- 6. LOGOUT ---
    window.showConfirmLogout = () => {
        showConfirm("Keluar?", "Anda harus login kembali untuk mengakses dashboard.", () => {
            window.location.href = 'api/auth/logout.php';
        });
    }
    
    /////Baruuuuuuuu
        // --- 7. SISTEM WITHDRAW (TARIK DANA) ---
    const btnWithdraw = document.getElementById('withdraw-button');
    if (btnWithdraw) {
        btnWithdraw.addEventListener('click', async (e) => {
            e.preventDefault(); // Mencegah reload form
            
            const amount = document.getElementById('withdraw-amount').value;
            const bankName = document.getElementById('bank-name').value;
            const accNumber = document.getElementById('account-number').value;
            const accName = document.getElementById('account-name').value;

            if (!amount || amount < 10000) {
                showError("Gagal", "Minimal penarikan Rp 10.000"); return;
            }
            if (!bankName || !accNumber || !accName) {
                showError("Gagal", "Mohon lengkapi data bank."); return;
            }

            showConfirm("Konfirmasi Penarikan", `Tarik Rp ${parseInt(amount).toLocaleString('id-ID')} ke ${bankName}?`, async () => {
                try {
                    // Tampilkan loading di tombol (opsional UX)
                    const originalText = btnWithdraw.innerText;
                    btnWithdraw.innerText = "Memproses...";
                    btnWithdraw.disabled = true;

                    const response = await fetch('api/payment/withdraw.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            amount: amount,
                            bank_name: bankName,
                            account_number: accNumber,
                            account_name: accName
                        })
                    });

                    const result = await response.json();
                    
                    btnWithdraw.innerText = originalText;
                    btnWithdraw.disabled = false;

                    if (result.status === 'success') {
                        showSuccess("Berhasil", result.message);
                        document.getElementById('withdraw-modal').classList.remove('active');
                        document.getElementById('withdraw-form').reset(); // Reset form
                        loadUserData(); // Refresh saldo otomatis
                    } else {
                        showError("Gagal", result.message);
                    }
                } catch (error) {
                    btnWithdraw.innerText = "Ajukan Penarikan";
                    btnWithdraw.disabled = false;
                    showError("Error", "Terjadi kesalahan koneksi.");
                }
            });
        });
    }
    
        // --- 8. SISTEM TRANSFER (KIRIM UANG) ---
    
    // Fungsi Cek User
    window.checkTransferUser = async () => {
        const username = document.getElementById('tf-username').value;
        const resultLabel = document.getElementById('tf-check-result');
        const btnSubmit = document.getElementById('btn-submit-transfer');

        if (!username) {
            resultLabel.style.color = '#EF4444';
            resultLabel.innerText = "Masukkan username dulu.";
            return;
        }

        resultLabel.style.color = 'var(--text-muted)';
        resultLabel.innerText = "Mengecek...";

        try {
            const response = await fetch(`api/payment/transfer.php?username=${username}`);
            const result = await response.json();

            if (result.status === 'success') {
                resultLabel.style.color = '#10B981'; // Hijau
                resultLabel.innerHTML = `<i class="ri-checkbox-circle-fill"></i> Pengguna ditemukan: <b>${result.data.username}</b>`;
                btnSubmit.disabled = false; // Buka kunci tombol kirim
            } else {
                resultLabel.style.color = '#EF4444'; // Merah
                resultLabel.innerText = `❌ ${result.message}`;
                btnSubmit.disabled = true;
            }
        } catch (error) {
            resultLabel.innerText = "Error koneksi.";
        }
    };

    // Fungsi Eksekusi Transfer
    const btnTransfer = document.getElementById('btn-submit-transfer');
    if (btnTransfer) {
        btnTransfer.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('tf-username').value;
            const amount = document.getElementById('tf-amount').value;
            const note = document.getElementById('tf-note').value;

            if (!amount || amount < 1000) {
                showError("Gagal", "Minimal transfer Rp 1.000"); return;
            }

            showConfirm("Konfirmasi Transfer", `Kirim Rp ${parseInt(amount).toLocaleString('id-ID')} ke ${username}?`, async () => {
                try {
                    btnTransfer.innerText = "Mengirim...";
                    btnTransfer.disabled = true;

                    const response = await fetch('api/payment/transfer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ username, amount, note })
                    });

                    const result = await response.json();
                    
                    btnTransfer.innerText = "Kirim Sekarang";
                    btnTransfer.disabled = false;

                    if (result.status === 'success') {
                        showSuccess("Berhasil", result.message);
                        document.getElementById('transfer-modal').classList.remove('active');
                        document.getElementById('transfer-form').reset();
                        document.getElementById('tf-check-result').innerText = "Klik ikon kaca pembesar untuk cek nama.";
                        document.getElementById('btn-submit-transfer').disabled = true; // Kunci lagi
                        loadUserData(); // Refresh saldo
                    } else {
                        showError("Gagal", result.message);
                    }
                } catch (error) {
                    btnTransfer.innerText = "Kirim Sekarang";
                    btnTransfer.disabled = false;
                    showError("Error", "Gagal menghubungi server.");
                }
            });
        });
    }
    
    // --- 9. SISTEM NOTIFIKASI & WEB PUSH (FINAL FIX) ---
    const notifBadge = document.getElementById('notif-badge');
    const notifList = document.getElementById('notif-list');
    const notifDropdown = document.getElementById('notif-dropdown');
    
    let lastUnreadCount = 0;

    // Izin Notifikasi HP
    if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }

    const showBrowserNotification = (title, body) => {
        if (Notification.permission === "granted") {
            try {
                new Notification(`Fandirr Pay: ${title}`, {
                    body: body,
                    icon: 'public/img/logo-white.png',
                    vibrate: [200, 100, 200]
                });
                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'); 
                audio.play().catch(() => {});
            } catch(e) {}
        }
    };

    window.toggleNotif = () => {
        notifDropdown.classList.toggle('active');
    };

    window.markAllRead = async () => {
        // Update Visual Dulu (Biar cepat)
        notifBadge.style.display = 'none';
        document.querySelectorAll('.notif-item.unread').forEach(item => item.classList.remove('unread'));
        lastUnreadCount = 0; // Reset local counter

        // Kirim ke Backend
        await fetch('api/user/notifications.php', { method: 'POST' });
    };

    // CLOSE DROPDOWN KETIKA KLIK DI LUAR
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.notif-wrapper')) {
            notifDropdown.classList.remove('active');
        }
    });

    // --- FUNGSI LOAD DATA (POLLING) ---
    const loadNotifications = async () => {
        try {
            const response = await fetch('api/user/notifications.php');
            const result = await response.json();

            if (result.status === 'success') {
                // 1. UPDATE BADGE
                if (result.unread > 0) {
                    notifBadge.style.display = 'flex';
                    notifBadge.innerText = result.unread > 9 ? '9+' : result.unread;

                    // Trigger Notif HP jika ada pesan BARU (unread bertambah)
                    if (result.unread > lastUnreadCount) {
                        // Ambil item pertama (terbaru) & cek apakah valid
                        if (Array.isArray(result.data) && result.data.length > 0) {
                            const latest = result.data[0];
                            // Hanya notif jika statusnya benar-benar unread
                            if (latest.is_read == 0) {
                                showBrowserNotification(latest.title, latest.message);
                            }
                        }
                    }
                } else {
                    notifBadge.style.display = 'none';
                }

                lastUnreadCount = result.unread; 

                // 2. RENDER LIST (Dengan Pengecekan Array)
                if (Array.isArray(result.data) && result.data.length > 0) {
                    let html = '';
                    result.data.forEach(notif => {
                        const isUnread = notif.is_read == 0 ? 'unread' : '';
                        
                        // Format Waktu Aman
                        let time = '-';
                        try {
                            time = new Date(notif.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        } catch(e) {}

                        let icon = 'ri-information-fill'; 
                        let color = '#3B82F6'; 

                        if(notif.type === 'success') { icon = 'ri-checkbox-circle-fill'; color = '#10B981'; }
                        if(notif.type === 'warning') { icon = 'ri-alert-fill'; color = '#F59E0B'; }
                        if(notif.type === 'danger') { icon = 'ri-close-circle-fill'; color = '#EF4444'; }

                        html += `
                            <div class="notif-item ${isUnread}">
                                <div class="notif-title" style="display:flex; align-items:center; gap:5px;">
                                    <i class="${icon}" style="color: ${color};"></i> ${notif.title}
                                </div>
                                <div class="notif-desc">${notif.message}</div>
                                <span class="notif-time">${time}</span>
                            </div>
                        `;
                    });

                    // Update HTML hanya jika ada perubahan (Mencegah kedip)
                    if (notifList.innerHTML !== html) {
                        notifList.innerHTML = html;
                    }

                } else {
                    // JIKA DATA KOSONG / ARRAY KOSONG
                    notifList.innerHTML = '<p style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem;">Belum ada notifikasi.</p>';
                }
            }
        } catch (error) { 
            console.error("Gagal load notif:", error); 
        }
    };

    // Jalankan
    loadNotifications();
    setInterval(loadNotifications, 3000);

    
    // --- INIT ---
    loadUserData();
    loadHistory();
    loadApiKey();
});
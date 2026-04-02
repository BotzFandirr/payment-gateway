// public/js/main.js

/**
 * 1. FUNGSI MENAMPILKAN LOADING
 */
function showLoading() {
    // Hapus modal lain jika ada
    const existingModal = document.getElementById('custom-modal');
    if (existingModal) existingModal.remove();

    const loadingHTML = `
        <div id="loading-modal" class="modal-overlay active" style="z-index: 10000;">
            <div class="modal-box" style="padding: 3rem 2rem;">
                <div class="spinner-wrapper">
                    <div class="loading-spinner"></div>
                    <h3 class="modal-title" style="margin:0;">Memproses...</h3>
                    <p class="modal-message" style="margin:0.5rem 0 0 0;">Mohon tunggu sebentar.</p>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHTML);
}

/**
 * 2. FUNGSI MENUTUP LOADING
 */
function hideLoading() {
    const loadingModal = document.getElementById('loading-modal');
    if (loadingModal) {
        loadingModal.remove();
    }
}

/**
 * 3. FUNGSI POPUP HASIL (Sukses/Gagal)
 */
function showModal(type, title, message, redirectUrl = null) {
    // Pastikan loading hilang dulu
    hideLoading();

    // Hapus modal lama jika ada
    const existingModal = document.getElementById('custom-modal');
    if (existingModal) existingModal.remove();

    // Tentukan Icon & Warna
    let iconClass = '';
    let modalTypeClass = '';

    if (type === 'success') {
        iconClass = 'ri-checkbox-circle-fill'; // Ikon Ceklis
        modalTypeClass = 'modal-success';
    } else {
        iconClass = 'ri-error-warning-fill';   // Ikon Tanda Seru
        modalTypeClass = 'modal-error';
    }
    
    // Template HTML Modal
    const modalHTML = `
        <div id="custom-modal" class="modal-overlay ${modalTypeClass}">
            <div class="modal-box">
                <div class="modal-icon-wrapper">
                    <i class="${iconClass}"></i>
                </div>
                <h3 class="modal-title">${title}</h3>
                <p class="modal-message">${message}</p>
                <button id="modal-close-btn" class="modal-btn">Oke, Mengerti</button>
            </div>
        </div>
    `;

    // Masukkan ke body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Animasi Muncul
    const modalElement = document.getElementById('custom-modal');
    setTimeout(() => {
        modalElement.classList.add('active');
    }, 10);

    // Logic Tombol Tutup
    const closeBtn = document.getElementById('modal-close-btn');
    closeBtn.focus(); // Fokus ke tombol agar bisa langsung di-enter
    
    closeBtn.addEventListener('click', () => {
        modalElement.classList.remove('active');
        setTimeout(() => {
            modalElement.remove(); 
            if (redirectUrl) {
                window.location.href = redirectUrl; 
            }
        }, 300);
    });
}

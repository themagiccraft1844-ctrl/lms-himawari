<?php
// File: src/settings/akun.php (DIPERBARUI)

// Ambil data user terbaru untuk halaman ini
$stmt_user = $mysqli->prepare("SELECT username, email, nim, status FROM users WHERE id = ?");
$stmt_user->bind_param("i", $_SESSION['id']);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// Ambil pesan status dari sesi (setelah redirect dari file aksi)
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$password_error = isset($_SESSION['password_error']);
$new_password_err = $_SESSION['new_password_err'] ?? '';
$confirm_password_err = $_SESSION['confirm_password_err'] ?? '';

// Hapus pesan dari sesi agar tidak muncul lagi saat refresh
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['password_error'], $_SESSION['new_password_err'], $_SESSION['confirm_password_err']);

// Tangkap konten sidebar
ob_start();
?>
<div class="sidebar-extra-content scrollable">
    <hr class="session-divider" style="margin: 10px 0;">
    <div class="expanded-settings-view">
        <h4 class="session-title" style="padding: 0 15px 10px 15px;"><?php echo lang('menu_pengaturan'); ?></h4>
        <ul class="settings-nav">
             <li class="<?php echo ($page == 'profil') ? 'active' : ''; ?>"><a href="settings.php?page=profil"><i class="fas fa-user-circle"></i> <span><?php echo lang('profil'); ?></span></a></li>
            <li class="<?php echo ($page == 'tampilan') ? 'active' : ''; ?>"><a href="settings.php?page=tampilan"><i class="fas fa-palette"></i> <span><?php echo lang('tampilan'); ?></span></a></li>
            <li class="<?php echo ($page == 'bahasa') ? 'active' : ''; ?>"><a href="settings.php?page=bahasa"><i class="fas fa-globe"></i> <span><?php echo lang('bahasa'); ?></span></a></li>
            <li class="<?php echo ($page == 'pengaturan_ai') ? 'active' : ''; ?>"><a href="settings.php?page=pengaturan_ai"><i class="fas fa-robot"></i> <span><?php echo lang('pengaturan_ai'); ?></span></a></li>
            <li class="<?php echo ($page == 'akun') ? 'active' : ''; ?>"><a href="settings.php?page=akun"><i class="fas fa-shield-alt"></i> <span><?php echo lang('akun'); ?></span></a></li>
            <li class="<?php echo ($page == 'notifikasi') ? 'active' : ''; ?>"><a href="settings.php?page=notifikasi"><i class="fas fa-bell"></i> <span><?php echo lang('notifikasi'); ?></span></a></li>
            <li class="<?php echo ($page == 'bantuan') ? 'active' : ''; ?>"><a href="settings.php?page=bantuan"><i class="fas fa-question-circle"></i> <span><?php echo lang('bantuan'); ?></span></a></li>
        </ul>
    </div>
</div>
<?php
$sidebar_extra_content = ob_get_clean();
?>

<!-- Style khusus untuk Modal di halaman ini -->
<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: none; /* Defaultnya tersembunyi */
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.modal-overlay.show {
    display: flex;
    opacity: 1;
}
.modal-content {
    background: var(--white-color);
    padding: 0;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    width: 90%;
    max-width: 500px;
    transform: scale(0.95);
    transition: transform 0.3s ease;
}
.modal-overlay.show .modal-content {
    transform: scale(1);
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
}
.modal-close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-grey);
}
.modal-body {
    padding: 1.5rem;
}
.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px;
    background-color: #f8f9fa;
    border: 2px dashed var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-grey);
    font-weight: 500;
}

.file-upload-label:hover, .file-upload-label.drag-over {
    background-color: #e9f2fe;
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.file-upload-label i {
    font-size: 1.5rem;
}

.file-name-display {
    display: block;
    margin-top: 10px;
    font-size: 0.9rem;
    color: #6c757d;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<!-- KONTEN UTAMA HALAMAN AKUN -->
<?php if ($success_message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="management-grid" style="grid-template-columns: 1fr; align-items: start; gap: 2rem;">
    <!-- Bagian Manajemen Akun -->
    <div class="form-container">
        <h3><i class="fas fa-user-edit" style="margin-right: 10px;"></i><?php echo lang('manajemen_akun'); ?></h3>
        
        <!-- Form Ganti Username -->
        <form action="settings/actions/update_username_action.php" method="POST" class="setting-form-section">
            <div class="form-group">
                <label for="username"><?php echo lang('username'); ?></label>
                <input type="text" id="username" name="new_username" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                <span class="invalid-feedback" id="username-error"></span>
                <span class="valid-feedback" id="username-success"></span>
                <small class="form-text"><?php echo lang('username_hint'); ?></small>
            </div>
            <button type="submit" class="btn btn-secondary" id="save-username-btn" disabled><?php echo lang('simpan_username'); ?></button>
        </form>

        <!-- Tombol Ganti Password -->
        <div class="setting-form-section">
            <div class="form-group">
                 <label><?php echo lang('password'); ?></label>
                 <button id="openPasswordModalBtn" class="btn btn-secondary"><?php echo lang('ubah_password'); ?></button>
            </div>
        </div>
    </div>

    <!-- Bagian Verifikasi -->
    <div class="form-container">
        <h3><i class="fas fa-user-check" style="margin-right: 10px;"></i><?php echo lang('verifikasi_akun'); ?></h3>
        
        <!-- Verifikasi Email -->
        <div class="setting-form-section">
            <label><?php echo lang('email'); ?></label>
            <div class="verification-status">
                <span><?php echo htmlspecialchars($user_data['email']); ?></span>
                <?php if ($user_data['status'] == 'active'): ?>
                    <span class="status-badge success"><i class="fas fa-check-circle"></i> <?php echo lang('terverifikasi'); ?></span>
                <?php else: ?>
                    <span class="status-badge warning"><i class="fas fa-exclamation-triangle"></i> <?php echo lang('belum_terverifikasi'); ?></span>
                    <form action="settings/actions/resend_verification_action.php" method="POST" style="display: inline;">
                        <button type="submit" class="btn-link"><?php echo lang('kirim_ulang_verifikasi'); ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Verifikasi KTM (jika NIM kosong) -->
        <div class="setting-form-section">
             <label><?php echo lang('nim_ktm'); ?></label>
            <?php if (empty($user_data['nim'])): ?>
                <div class="verification-status">
                    <span class="status-badge warning"><i class="fas fa-exclamation-triangle"></i> <?php echo lang('nim_kosong'); ?></span>
                    <button class="btn-link" id="openKtmModalBtn"><?php echo lang('verifikasi_dengan_ktm'); ?></button>
                </div>
            <?php else: ?>
                 <div class="verification-status">
                    <span><?php echo htmlspecialchars($user_data['nim']); ?></span>
                    <span class="status-badge success"><i class="fas fa-check-circle"></i> <?php echo lang('terverifikasi'); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- Modal Ganti Password -->
<div id="passwordModal" class="modal-overlay">
    <div class="modal-content">
        <header class="modal-header">
            <h2><?php echo lang('ubah_password'); ?></h2>
            <button id="closePasswordModalBtn" class="modal-close-btn"><i class="fas fa-times"></i></button>
        </header>
        <main class="modal-body">
             <form action="settings/actions/update_password_action.php" method="post">
                <div class="form-group">
                    <label for="new_password"><?php echo lang('password_baru'); ?></label>
                    <input type="password" name="new_password" class="form-control <?php if(!empty($new_password_err)) echo 'is-invalid'; ?>">
                    <span class="invalid-feedback" style="display: <?php echo !empty($new_password_err) ? 'block' : 'none'; ?>;"><?php echo $new_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><?php echo lang('konfirmasi_password_baru'); ?></label>
                    <input type="password" name="confirm_password" class="form-control <?php if(!empty($confirm_password_err)) echo 'is-invalid'; ?>">
                    <span class="invalid-feedback" style="display: <?php echo !empty($confirm_password_err) ? 'block' : 'none'; ?>;"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn" value="<?php echo lang('update_password'); ?>">
                </div>
            </form>
        </main>
    </div>
</div>


<!-- Modal Verifikasi KTM (jika diperlukan) -->
<?php if (empty($user_data['nim'])): ?>
<div id="ktmModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <header class="modal-header">
            <h2><?php echo lang('verifikasi_dengan_ktm'); ?></h2>
            <button id="closeKtmModalBtn" class="modal-close-btn"><i class="fas fa-times"></i></button>
        </header>
        <main class="modal-body">
            <div id="ocr-section">
                <p class="form-text"><?php echo lang('upload_ktm_hint'); ?></p>
                <div class="form-group">
                    <label for="ktm-upload" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span><?php echo lang('pilih_file_ktm'); ?></span>
                    </label>
                    <input type="file" id="ktm-upload" accept="image/*" style="display:none;">
                    <span id="file-name-display" class="file-name-display"></span>
                </div>
                <div id="ocr-status" style="display: none;">
                    <!-- Loader and progress text will be here -->
                </div>
                <div id="ocr-preview" style="display: none;">
                    <h4><?php echo lang('hasil_ekstraksi_data'); ?></h4>
                    <div class="form-group">
                        <label><?php echo lang('nama_lengkap'); ?></label>
                        <input type="text" id="preview-nama" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label><?php echo lang('nim'); ?></label>
                        <input type="text" id="preview-nim" class="form-control" readonly>
                    </div>
                    <p class="form-text"><?php echo lang('pastikan_data_benar'); ?></p>
                    <div class="form-group-row">
                        <button type="button" class="btn btn-secondary" id="retry-ocr-btn"><?php echo lang('unggah_ulang'); ?></button>
                        <button type="button" class="btn" id="confirm-ocr-btn"><?php echo lang('konfirmasi_data'); ?></button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php endif; ?>


<!-- JavaScript Khusus Halaman Akun -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Kontrol Modal Generik ---
    function setupModal(modalId, openBtnId, closeBtnId) {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        const closeBtn = document.getElementById(closeBtnId);

        if (modal && openBtn && closeBtn) {
            openBtn.addEventListener('click', () => modal.classList.add('show'));
            closeBtn.addEventListener('click', () => modal.classList.remove('show'));
            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        }
        return modal;
    }
    
    const passwordModal = setupModal('passwordModal', 'openPasswordModalBtn', 'closePasswordModalBtn');

    // Otomatis buka modal password HANYA JIKA ada error dari server
    <?php if ($password_error): ?>
        if(passwordModal) passwordModal.classList.add('show');
    <?php endif; ?>

    // --- Validasi Username Real-time ---
    const usernameInput = document.getElementById('username');
    const originalUsername = usernameInput.value;
    const saveUsernameBtn = document.getElementById('save-username-btn');
    const usernameError = document.getElementById('username-error');
    const usernameSuccess = document.getElementById('username-success');

    const debounce = (func, delay) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    };

    if(usernameInput) {
        usernameInput.addEventListener('input', debounce(async () => {
            const value = usernameInput.value.trim();
            usernameError.style.display = 'none';
            usernameSuccess.style.display = 'none';
            usernameInput.classList.remove('is-invalid', 'is-valid');
            saveUsernameBtn.disabled = true;

            if (value === originalUsername) {
                return;
            }

            if (value.length < 3 || !/^[a-z0-9_.]+$/.test(value)) {
                usernameError.textContent = '<?php echo lang("username_invalid"); ?>';
                usernameError.style.display = 'block';
                usernameInput.classList.add('is-invalid');
                return;
            }

            try {
                // Gunakan path relatif yang benar dari lokasi file settings.php
                const response = await fetch('settings/actions/check_availability.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `field=username&value=${encodeURIComponent(value)}`
                });
                const data = await response.json();
                if (data.available) {
                    usernameSuccess.textContent = '<?php echo lang("username_tersedia"); ?>';
                    usernameSuccess.style.display = 'block';
                    usernameInput.classList.add('is-valid');
                    saveUsernameBtn.disabled = false;
                } else {
                    usernameError.textContent = data.message;
                    usernameError.style.display = 'block';
                    usernameInput.classList.add('is-invalid');
                }
            } catch (error) {
                usernameError.textContent = 'Error saat validasi.';
                usernameError.style.display = 'block';
                usernameInput.classList.add('is-invalid');
            }
        }, 500));
    }
    
    // --- Logika Modal KTM & OCR (jika ada) ---
    <?php if (empty($user_data['nim'])): ?>
        setupModal('ktmModal', 'openKtmModalBtn', 'closeKtmModalBtn');
        
        const ktmUploadInput = document.getElementById('ktm-upload');
        const fileNameDisplay = document.getElementById('file-name-display');
        const ocrPreview = document.getElementById('ocr-preview');
        const previewNama = document.getElementById('preview-nama');
        const previewNim = document.getElementById('preview-nim');
        const retryOcrBtn = document.getElementById('retry-ocr-btn');
        const confirmOcrBtn = document.getElementById('confirm-ocr-btn');
        const fileUploadLabel = document.querySelector('.file-upload-label');


        if(ktmUploadInput) {
            ktmUploadInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                    handleFileSelect(this.files[0]);
                }
            });
        }

        async function handleFileSelect(file) {
            if (!file) return;
            ocrPreview.style.display = 'none';
            // Tampilkan loader (opsional)
            
            try {
                const worker = await Tesseract.createWorker('ind');
                const { data: { text } } = await worker.recognize(file);
                await worker.terminate();
                
                // Logika parsing sederhana (sesuaikan jika perlu)
                const nimMatch = text.match(/([A-Z]\d{8,11}|\b\d{8,12}\b)/);
                const nameLines = text.split('\n').filter(line => /^[A-Z\s'.]+$/g.test(line.trim()));
                
                previewNim.value = nimMatch ? nimMatch[0] : '<?php echo lang("tidak_terdeteksi"); ?>';
                previewNama.value = nameLines.length > 0 ? nameLines[0].trim() : '<?php echo lang("tidak_terdeteksi"); ?>';

                ocrPreview.style.display = 'block';
            } catch (error) {
                alert('Gagal memproses gambar. Silakan coba lagi dengan gambar yang lebih jelas.');
                console.error(error);
            }
        }

        if(retryOcrBtn) retryOcrBtn.addEventListener('click', () => {
             ktmUploadInput.value = '';
             fileNameDisplay.textContent = '';
             ocrPreview.style.display = 'none';
        });
        
        if(confirmOcrBtn) confirmOcrBtn.addEventListener('click', async () => {
            const nim = previewNim.value;
            const fullName = previewNama.value;

            if (nim === '<?php echo lang("tidak_terdeteksi"); ?>' || fullName === '<?php echo lang("tidak_terdeteksi"); ?>') {
                alert('<?php echo lang("data_tidak_lengkap"); ?>');
                return;
            }

            confirmOcrBtn.disabled = true;
            confirmOcrBtn.textContent = '<?php echo lang("menyimpan"); ?>...';

            try {
                 const formData = new FormData();
                 formData.append('nim', nim);
                 formData.append('full_name', fullName);

                 const response = await fetch('settings/actions/update_nim_action.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert('<?php echo lang("verifikasi_nim_berhasil"); ?>');
                    window.location.reload();
                } else {
                    alert('<?php echo lang("verifikasi_nim_gagal"); ?>: ' + data.message);
                    confirmOcrBtn.disabled = false;
                    confirmOcrBtn.textContent = '<?php echo lang("konfirmasi_data"); ?>';
                }
            } catch (error) {
                 alert('Terjadi kesalahan jaringan.');
                 confirmOcrBtn.disabled = false;
                 confirmOcrBtn.textContent = '<?php echo lang("konfirmasi_data"); ?>';
            }
        });
    <?php endif; ?>
});
</script>


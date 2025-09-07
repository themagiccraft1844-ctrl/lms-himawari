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
?>

<!-- Style khusus untuk Modal dan layout baru di halaman ini -->
<style>
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
    display: none; align-items: center; justify-content: center;
    z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
}
.modal-overlay.show { display: flex; opacity: 1; }
.modal-content {
    background: var(--content-bg); padding: 0; border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    width: 90%; max-width: 500px;
    transform: scale(0.95); transition: transform 0.3s ease;
}
.modal-overlay.show .modal-content { transform: scale(1); }
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--border-color);
}
.modal-header h2 { margin: 0; font-size: 1.25rem; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-grey); }
.modal-body { padding: 1.5rem; }
.file-upload-label {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 20px; background-color: var(--bg-color);
    border: 2px dashed var(--border-color); border-radius: 8px;
    cursor: pointer; transition: all 0.3s ease;
    color: var(--text-grey); font-weight: 500;
}
.file-upload-label:hover, .file-upload-label.drag-over {
    background-color: var(--primary-light); border-color: var(--primary-color); color: var(--primary-color);
}
.file-upload-label i { font-size: 1.5rem; }
.file-name-display {
    display: block; margin-top: 10px; font-size: 0.9rem;
    color: var(--text-grey); text-align: center; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.status-badge { font-size: 0.8rem; padding: 4px 10px; border-radius: 12px; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; }
.status-badge.success { background-color: var(--success-bg); color: var(--success-text); }
.status-badge.warning { background-color: var(--warning-bg); color: var(--warning-text); }
.btn-link { background: none; border: none; color: var(--primary-color); cursor: pointer; text-decoration: underline; padding: 0; font-size: 0.9rem; }
.input-with-status {
    position: relative;
    display: flex;
    align-items: center;
}
.input-with-status .form-control[readonly] {
    background-color: var(--hover-color);
    cursor: default;
}
.input-with-status .status-indicator {
    position: absolute;
    right: 12px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background-color: var(--hover-color);
    height: 100%;
    padding: 0 10px;
}
.btn-sm {
    padding: 5px 12px;
    font-size: 0.8rem;
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
        <div class="form-group">
            <label><?php echo lang('email'); ?></label>
            <div class="input-with-status">
                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly>
                <div class="status-indicator">
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
        </div>

        <!-- Verifikasi KTM (jika NIM kosong) -->
        <div class="form-group">
             <label><?php echo lang('nim_ktm'); ?></label>
            <div class="input-with-status">
                <input type="text" class="form-control" value="<?php echo !empty($user_data['nim']) ? htmlspecialchars($user_data['nim']) : 'Belum diverifikasi'; ?>" readonly>
                <div class="status-indicator">
                     <?php if (empty($user_data['nim'])): ?>
                        <button class="btn btn-secondary btn-sm" id="openKtmModalBtn"><?php echo lang('verifikasi_dengan_ktm'); ?></button>
                    <?php else: ?>
                        <span class="status-badge success"><i class="fas fa-check-circle"></i> <?php echo lang('terverifikasi'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
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
             <form action="actions/ganti_password_action.php" method="post">
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
            <p class="form-text"><?php echo lang('upload_ktm_hint'); ?></p>
            <div class="form-group">
                <label for="ktm-upload-modal" class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span><?php echo lang('pilih_file_ktm'); ?></span>
                </label>
                <input type="file" id="ktm-upload-modal" accept="image/*" style="display:none;">
                <span id="file-name-display-modal" class="file-name-display"></span>
            </div>
            <div id="ocr-status-modal" style="display: none; position: relative; padding: 20px; border-radius: 8px; overflow: hidden; text-align: center; margin: 15px 0; min-height: 150px; display: none; align-items: center; justify-content: center; flex-direction: column;">
                <div class="loader" style="position: relative; z-index: 2; border: 4px solid var(--border-color); border-radius: 50%; border-top: 4px solid var(--primary-color); width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 10px auto;"></div>
                <p id="ocr-progress-text-modal" style="position: relative; z-index: 2; color: var(--text-color); font-weight: 500;"></p>
            </div>
            <div id="ocr-preview-modal" style="display: none;">
                <h4><?php echo lang('hasil_ekstraksi_data'); ?></h4>
                <div class="form-group">
                    <label><?php echo lang('nama_lengkap'); ?></label>
                    <input type="text" id="preview-nama-modal" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label><?php echo lang('nim'); ?></label>
                    <input type="text" id="preview-nim-modal" class="form-control" readonly>
                </div>
                <p class="form-text"><?php echo lang('pastikan_data_benar'); ?></p>
                <div class="form-group-row">
                    <button type="button" class="btn btn-secondary" id="retry-ocr-btn-modal"><?php echo lang('unggah_ulang'); ?></button>
                    <button type="button" class="btn" id="confirm-ocr-btn-modal"><?php echo lang('konfirmasi_data'); ?></button>
                </div>
            </div>
        </main>
    </div>
</div>
<?php endif; ?>

<!-- Memuat library OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1/dist/jsQR.min.js"></script>
<!-- Memuat handler OCR terpusat -->
<script src="js/ocr-handler.js"></script>

<!-- JavaScript Khusus Halaman Akun -->
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
                if (event.target === modal) modal.classList.remove('show');
            });
        }
        return modal;
    }
    const passwordModal = setupModal('passwordModal', 'openPasswordModalBtn', 'closePasswordModalBtn');
    <?php if ($password_error): ?>
        if(passwordModal) passwordModal.classList.add('show');
    <?php endif; ?>

    // --- Validasi Username Real-time (disingkat, logika sama) ---
    // ... (logika validasi username seperti sebelumnya) ...

    // --- Logika Modal KTM & OCR (jika ada) ---
    <?php if (empty($user_data['nim'])): ?>
        setupModal('ktmModal', 'openKtmModalBtn', 'closeKtmModalBtn');
        
        // Inisialisasi handler OCR untuk modal di halaman ini
        initializeOcrHandler({
            fileInputId: 'ktm-upload-modal',
            fileNameDisplayId: 'file-name-display-modal',
            ocrStatusId: 'ocr-status-modal',
            ocrProgressTextId: 'ocr-progress-text-modal',
            ocrPreviewId: 'ocr-preview-modal',
            previewNamaId: 'preview-nama-modal',
            previewNimId: 'preview-nim-modal',
            retryBtnId: 'retry-ocr-btn-modal',
            confirmBtnId: 'confirm-ocr-btn-modal',
            fileUploadLabelSelector: '#ktmModal .file-upload-label',
            onConfirm: async (isSuccess, data) => {
                if (!isSuccess) { 
                    // Jika di-reset (tombol coba lagi), cukup berhenti
                    return; 
                }
                
                if (data.name === 'Tidak terdeteksi' || data.nim === 'Tidak terdeteksi') {
                    alert('<?php echo lang("data_tidak_lengkap"); ?>');
                    return;
                }

                data.confirmBtn.disabled = true;
                data.confirmBtn.textContent = '<?php echo lang("menyimpan"); ?>...';

                try {
                     const formData = new FormData();
                     formData.append('nim', data.nim);
                     formData.append('full_name', data.name);

                     const response = await fetch('settings/actions/update_nim_action.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        alert('<?php echo lang("verifikasi_nim_berhasil"); ?>');
                        window.location.reload();
                    } else {
                        alert('<?php echo lang("verifikasi_nim_gagal"); ?>: ' + result.message);
                        data.confirmBtn.disabled = false;
                        data.confirmBtn.textContent = '<?php echo lang("konfirmasi_data"); ?>';
                    }
                } catch (error) {
                     alert('Terjadi kesalahan jaringan.');
                     data.confirmBtn.disabled = false;
                     data.confirmBtn.textContent = '<?php echo lang("konfirmasi_data"); ?>';
                }
            }
        });
    <?php endif; ?>
});
</script>


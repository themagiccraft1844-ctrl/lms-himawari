<?php
// File: src/settings/bahasa.php (DIPERBARUI - require_once dihapus)

// Koneksi DB, loader bahasa, dan loader tema sudah dimuat oleh settings.php

// Ambil pengaturan bahasa saat ini dari sesi yang sudah dimuat
$current_language = $_SESSION['language'] ?? 'id';

$success_message = $_GET['success'] ?? null;

$current_page = basename($_SERVER['PHP_SELF']);
ob_start();
?>
<div class="sidebar-extra-content scrollable">
    <hr class="session-divider" style="margin: 10px 0;">
    <div class="expanded-settings-view">
        <h4 class="session-title" style="padding: 0 15px 10px 15px;"><?php echo lang('menu_pengaturan'); ?></h4>
        <ul class="settings-nav">
            <li class="<?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>"><a href="profil.php"><i class="fas fa-user-circle"></i> <span><?php echo lang('profil'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'tampilan.php') ? 'active' : ''; ?>"><a href="tampilan.php"><i class="fas fa-palette"></i> <span><?php echo lang('tampilan'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'bahasa.php') ? 'active' : ''; ?>"><a href="bahasa.php"><i class="fas fa-globe"></i> <span><?php echo lang('bahasa'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'pengaturan_ai.php') ? 'active' : ''; ?>"><a href="pengaturan_ai.php"><i class="fas fa-robot"></i> <span><?php echo lang('pengaturan_ai'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'akun.php') ? 'active' : ''; ?>"><a href="akun.php"><i class="fas fa-shield-alt"></i> <span><?php echo lang('akun'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'notifikasi.php') ? 'active' : ''; ?>"><a href="notifikasi.php"><i class="fas fa-bell"></i> <span><?php echo lang('notifikasi'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'bantuan.php') ? 'active' : ''; ?>"><a href="bantuan.php"><i class="fas fa-question-circle"></i> <span><?php echo lang('bantuan'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'lainnya.php') ? 'active' : ''; ?>"><a href="lainnya.php"><i class="fas fa-ellipsis-h"></i> <span><?php echo lang('lainnya'); ?></span></a></li>
        </ul>
    </div>
</div>
<?php
$sidebar_extra_content = ob_get_clean();
// Perhatikan bahwa seluruh <head> dan pembuka <body> ada di settings.php

// Konten utama halaman ini dimulai di sini
if ($success_message): ?>
    <div class="alert alert-success" style="background-color: #d4edda; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; color: #155724;">
        <?php echo lang('pengaturan_disimpan'); ?>
    </div>
<?php endif; ?>
<div class="form-container">
     <form action="settings/actions/update_language_settings_action.php" method="POST">
        <h3><i class="fas fa-globe" style="margin-right: 10px;"></i><?php echo lang('pilih_bahasa_anda'); ?></h3>
        <p class="form-text" style="margin-top: 10px; margin-bottom: 20px;"><?php echo lang('pilih_bahasa_deskripsi'); ?></p>
        
        <div class="form-group">
            <label for="language-select"><?php echo lang('bahasa'); ?></label>
            <select name="language" id="language-select" class="form-control">
                <option value="id" <?php echo ($current_language == 'id') ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                <option value="en" <?php echo ($current_language == 'en') ? 'selected' : ''; ?>>English</option>
            </select>
        </div>

        <div class="form-group" style="margin-top: 30px;">
            <button type="submit" class="btn" style="width: auto;"><?php echo lang('simpan_pengaturan'); ?></button>
        </div>
    </form>
</div>


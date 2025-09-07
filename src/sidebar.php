<?php
// sidebar.php (Diperbarui dengan fungsi lang())
require_once "language_loader.php";
// Pemuat bahasa seharusnya sudah dipanggil di file induk sebelum include ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo">
        <h2>Class Himawari</h2>
    </div>
    
    <ul class="nav-links">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-home"></i> <span><?php echo lang('dashboard'); ?></span></a>
        </li>
        <li class="<?php echo ($current_page == 'kursusku.php' || $current_page == 'view_course.php') ? 'active' : ''; ?>">
            <a href="kursusku.php"><i class="fas fa-book"></i> <span><?php echo lang('kursus_saya'); ?></span></a>
        </li>
        <li class="<?php echo ($current_page == 'chat.php') ? 'active' : ''; ?>">
            <a href="chat.php"><i class="fas fa-comments"></i> <span><?php echo lang('chat_with_ai'); ?></span></a>
        </li>
        <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'settings') !== false) ? 'active' : ''; ?>">
            <a href="settings.php"><i class="fas fa-cog"></i> <span><?php echo lang('pengaturan'); ?></span></a>
        </li>
        
        <?php
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && ($_SESSION['view_mode'] ?? 'user') === 'user'):
        ?>
            <li style="border-top: 1px solid var(--border-color); margin-top: 10px; padding-top: 10px;">
                <a href="switch_view.php" style="color: var(--primary-color); background-color: var(--primary-light);">
                    <i class="fas fa-user-shield"></i> <span><?php echo lang('masuk_panel_admin'); ?></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <?php
    if (isset($sidebar_extra_content)) {
        echo $sidebar_extra_content;
    }
    ?>
    
    <ul class="nav-links" style="margin-top: auto;">
        <li class="logout-link">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span><?php echo lang('keluar'); ?></span></a>
        </li>
    </ul>
</div>


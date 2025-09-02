<?php
// sidebar.php
// Menentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo">
        <h2>Platform Kursus</h2>
    </div>
    
    <ul class="nav-links">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
        </li>
        <li class="<?php echo ($current_page == 'kursusku.php') ? 'active' : ''; ?>">
            <a href="kursusku.php"><i class="fas fa-book"></i> <span>Kursus Saya</span></a>
        </li>
        <li class="<?php echo ($current_page == 'chat.php') ? 'active' : ''; ?>">
            <a href="chat.php"><i class="fas fa-comments"></i> <span>Chat with AI</span></a>
        </li>
        <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
            <a href="settings.php"><i class="fas fa-cog"></i> <span>Pengaturan</span></a>
        </li>
    </ul>

    <?php
    // -- TEMPAT UNTUK KONTEN TAMBAHAN DARI HALAMAN LAIN --
    if (isset($sidebar_extra_content)) {
        echo $sidebar_extra_content;
    }
    ?>
    
    <ul class="nav-links" style="margin-top: auto;"> <!-- Pindahkan logout ke bawah -->
        <li class="logout-link">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a>
        </li>
    </ul>
</div>


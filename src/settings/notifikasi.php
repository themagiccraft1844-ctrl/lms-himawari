<?php
// File: src/settings/notifikasi.php (File BARU - Placeholder)

if (session_status() == PHP_SESSION_NONE) { session_start(); }
require_once "../db.php"; 
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../index.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
ob_start();
?>
<div class="sidebar-extra-content scrollable">
    <hr class="session-divider" style="margin: 10px 0;">
    <div class="expanded-settings-view">
        <h4 class="session-title" style="padding: 0 15px 10px 15px;">Menu Pengaturan</h4>
        <ul class="settings-nav">
            <li class="<?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>"><a href="profil.php"><i class="fas fa-user-circle"></i> <span>Profil</span></a></li>
            <li class="<?php echo ($current_page == 'tampilan.php') ? 'active' : ''; ?>"><a href="tampilan.php"><i class="fas fa-palette"></i> <span>Tampilan</span></a></li>
            <li class="<?php echo ($current_page == 'bahasa.php') ? 'active' : ''; ?>"><a href="bahasa.php"><i class="fas fa-globe"></i> <span>Bahasa</span></a></li>
            <li class="<?php echo ($current_page == 'pengaturan_ai.php') ? 'active' : ''; ?>"><a href="pengaturan_ai.php"><i class="fas fa-robot"></i> <span>Pengaturan AI</span></a></li>
            <li class="<?php echo ($current_page == 'akun.php') ? 'active' : ''; ?>"><a href="akun.php"><i class="fas fa-shield-alt"></i> <span>Akun</span></a></li>
            <li class="<?php echo ($current_page == 'notifikasi.php') ? 'active' : ''; ?>"><a href="notifikasi.php"><i class="fas fa-bell"></i> <span>Notifikasi</span></a></li>
            <li class="<?php echo ($current_page == 'bantuan.php') ? 'active' : ''; ?>"><a href="bantuan.php"><i class="fas fa-question-circle"></i> <span>Bantuan</span></a></li>
            <li class="<?php echo ($current_page == 'lainnya.php') ? 'active' : ''; ?>"><a href="lainnya.php"><i class="fas fa-ellipsis-h"></i> <span>Lainnya</span></a></li>
        </ul>
    </div>
</div>
<?php
$sidebar_extra_content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Notifikasi - Platform Kursus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/settings.css?v=<?php echo time(); ?>">
</head>
<body>
   <?php include '../sidebar.php'; ?>
    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Pengaturan Notifikasi</h2>
            </div>
            <div class="header-actions">
                <div class="user-wrapper" id="user-menu-toggle">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                        <small>User</small>
                    </div>
                </div>
                <div class="dropdown-menu" id="user-dropdown">
                    <a href="/settings/profil.php" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="/logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>
        <main>
            <div class="form-container">
                <h3><i class="fas fa-bell" style="margin-right: 10px;"></i>Notifikasi</h3>
                <p>Atur preferensi notifikasi email atau notifikasi dalam aplikasi di halaman ini.</p>
            </div>
        </main>
    </div>
    <script src="/js/dashboard.js"></script>
</body>
</html>

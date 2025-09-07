<?php
// File: src/settings.php (DIPERBARUI TOTAL)
// File ini sekarang berfungsi sebagai halaman induk untuk semua pengaturan.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "db.php";
require_once "language_loader.php"; // -> Memuat fungsi lang() 

// Cek jika user belum login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// --- TAMBAHAN KODE: Mengambil data pengguna untuk header ---
$user_id = $_SESSION['id'];
$full_name = $_SESSION['username'];
$profile_picture_url = '';

if ($stmt = $mysqli->prepare("SELECT full_name, profile_picture_url FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        $full_name = $user['full_name'];
        $profile_picture_url = $user['profile_picture_url'];
    }
    $stmt->close();
}
// --------------------------------------------------------

// Tentukan halaman pengaturan mana yang akan ditampilkan. Default ke 'profil'.
$page = $_GET['page'] ?? 'profil';

// Daftar halaman pengaturan yang valid untuk keamanan
$allowed_pages = [
    'profil',
    'akun',
    'tampilan',
    'bahasa',
    'pengaturan_ai',
    'notifikasi',
    'bantuan',
];

// Cek apakah halaman yang diminta ada dalam daftar yang diizinkan
if (!in_array($page, $allowed_pages)) {
    $page = 'profil';
}

// Path ke file konten yang akan di-include
$page_content_file = "settings/{$page}.php";
$current_page = $page . '.php';

// --- KONTEN EKSTRA UNTUK SIDEBAR ---
ob_start();
?>
<div class="sidebar-extra-content scrollable">
    <hr class="session-divider" style="margin: 10px 0;">
    <div class="expanded-settings-view">
        <h4 class="session-title" style="padding: 0 15px 10px 15px;">Menu Pengaturan</h4>
        <ul class="settings-nav">
            <li class="<?php echo ($current_page == 'profil.php') ? 'active' : ''; ?>"><a href="settings.php?page=profil"><i class="fas fa-user-circle"></i> <span><?php echo lang('profil'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'tampilan.php') ? 'active' : ''; ?>"><a href="settings.php?page=tampilan"><i class="fas fa-palette"></i> <span><?php echo lang('tampilan'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'bahasa.php') ? 'active' : ''; ?>"><a href="settings.php?page=bahasa"><i class="fas fa-globe"></i> <span><?php echo lang('bahasa'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'pengaturan_ai.php') ? 'active' : ''; ?>"><a href="settings.php?page=pengaturan_ai"><i class="fas fa-robot"></i> <span><?php echo lang('pengaturan_ai'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'akun.php') ? 'active' : ''; ?>"><a href="settings.php?page=akun"><i class="fas fa-shield-alt"></i> <span><?php echo lang('akun'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'notifikasi.php') ? 'active' : ''; ?>"><a href="settings.php?page=notifikasi"><i class="fas fa-bell"></i> <span><?php echo lang('notifikasi'); ?></span></a></li>
            <li class="<?php echo ($current_page == 'bantuan.php') ? 'active' : ''; ?>"><a href="settings.php?page=bantuan"><i class="fas fa-question-circle"></i> <span><?php echo lang('bantuan'); ?></span></a></li>
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
    <title>Pengaturan <?php echo ucfirst($page); ?> - Platform Kursus</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/settings.css?v=<?php echo time(); ?>">
    <?php require_once 'theme_loader.php'; ?>
    <style>
        .profile-pic-header { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; margin-right: 15px; }
    </style>
</head>
<body>
   <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Pengaturan <?php echo ucfirst(str_replace('_', ' ', $page)); ?></h2>
            </div>
            <div class="header-actions">
                <div class="user-wrapper" id="user-menu-toggle">
                     <?php if (!empty($profile_picture_url)): ?>
                        <img src="<?php echo htmlspecialchars($profile_picture_url); ?>" alt="Foto Profil" class="profile-pic-header">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                    <div>
                        <h4><?php echo htmlspecialchars($full_name); ?></h4>
                        <small>User</small>
                    </div>
                </div>
                <div class="dropdown-menu" id="user-dropdown">
                    <a href="settings.php?page=profil" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>

        <main>
            <?php
            if (file_exists($page_content_file)) {
                include $page_content_file;
            } else {
                echo "<div class='form-container'><p>Halaman pengaturan tidak ditemukan.</p></div>";
            }
            ?>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>


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
    // Tambahkan nama file sub-pengaturan lainnya di sini tanpa .php
];

// Cek apakah halaman yang diminta ada dalam daftar yang diizinkan
if (!in_array($page, $allowed_pages)) {
    // Jika tidak valid, arahkan ke halaman profil default
    $page = 'profil';
}

// Path ke file konten yang akan di-include
$page_content_file = "settings/{$page}.php";

// Set variabel $current_page agar link aktif di sidebar berfungsi dengan benar
$current_page = $page . '.php';

// --- KONTEN EKSTRA UNTUK SIDEBAR ---
// Ini perlu didefinisikan sebelum include 'sidebar.php'
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
// ------------------------------------
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Judul halaman dinamis berdasarkan halaman yang aktif -->
    <title>Pengaturan <?php echo ucfirst($page); ?> - Platform Kursus</title>
    
    <!-- CSS sekarang dimuat terpusat di sini dengan path relatif -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/tailwind-output.css">
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/settings.css?v=<?php echo time(); ?>">
    <?php require_once 'theme_loader.php'; ?>

</head>
<body>
   <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <!-- Judul header dinamis -->
                <h2>Pengaturan <?php echo ucfirst(str_replace('_', ' ', $page)); ?></h2>
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
                    <a href="settings.php?page=profil" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>

        <main>
            <?php
            // Memuat file konten sub-pengaturan
            if (file_exists($page_content_file)) {
                include $page_content_file;
            } else {
                // Tampilkan pesan error jika file tidak ditemukan
                echo "<div class='form-container'><p>Halaman pengaturan tidak ditemukan.</p></div>";
            }
            ?>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
    <!-- Jika ada JS khusus untuk halaman profil (seperti modal), bisa diletakkan di sini atau di dalam file profil.php itu sendiri -->
    <?php if ($page === 'profil'): ?>
        <script>
        // Script spesifik untuk modal ganti password di halaman profil
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('passwordModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalBtn');
            
            if (modal && openBtn && closeBtn) {
                const modalContent = modal.querySelector('div');

                const openModal = () => {
                    modal.classList.remove('opacity-0', 'pointer-events-none');
                    modalContent.classList.remove('scale-95');
                };

                const closeModal = () => {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modalContent.classList.add('scale-95');
                };

                openBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal();
                });
                
                closeBtn.addEventListener('click', closeModal);
                modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
                document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });
                
                <?php 
                // Cek apakah ada error password dari session untuk membuka modal secara otomatis
                $password_error = isset($_SESSION['password_error']);
                if ($password_error) { echo 'openModal();'; }
                ?>
            }
        });
        </script>
    <?php endif; ?>
</body>
</html>


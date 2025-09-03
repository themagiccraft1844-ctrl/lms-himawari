<?php
// File: src/settings/profil.php (File BARU)

// Pastikan sesi dimulai di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../db.php"; 

// Cek jika user belum login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../index.php");
    exit;
}

// Ambil data user dari database
$user_id = $_SESSION['id'];
$stmt = $mysqli->prepare("SELECT username, full_name, nim, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
if ($user_result) {
    $user = $user_result->fetch_assoc();
} else {
    die("Gagal mengambil data user.");
}
$stmt->close();

if (!$user) {
    die("User tidak ditemukan.");
}

// Fungsi untuk mem-parsing NIM (diasumsikan sama seperti sebelumnya)
function parseNIM($nim) {
    $info = [
        'fakultas' => 'Tidak Diketahui', 'kampus' => 'Tidak Diketahui', 'jenjang' => 'Tidak Diketahui',
        'prodi' => 'Tidak Diketahui', 'angkatan' => 'Tidak Diketahui', 'semester_masuk' => 'Tidak Diketahui',
        'nomor_urut' => 'Tidak Diketahui'
    ];
    if (strlen($nim) < 10) return $info;
    $kodeFakultas = strtoupper(substr($nim, 0, 1));
    $fakultasMap = [ 'A' => 'Fakultas Pertanian (Faperta)', 'B' => 'Sekolah Kedokteran Hewan dan Biomedis (SKHB)', 'C' => 'Fakultas Perikanan dan Ilmu Kelautan (FPIK)', 'D' => 'Fakultas Peternakan (Fapet)', 'J' => 'Sekolah Vokasi', 'E' => 'Fakultas Kehutanan dan Lingkungan (Fahutan)', 'F' => 'Sekolah Teknik', 'G' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam (FMIPA)', 'H' => 'Fakultas Ekonomi dan Manajemen (FEM)', 'I' => 'Fakultas Ekologi Manusia (Fema)', 'K' => 'Sekolah Bisnis (SB)', 'L' => 'Fakultas Kedokteran (FK)', 'M' => 'Sekolah Sains Data, Matematika dan Informatika (SMI)' ];
    $info['fakultas'] = $fakultasMap[$kodeFakultas] ?? 'Tidak Diketahui';
    $info['kampus'] = substr($nim, 1, 1) == '0' ? 'Kampus Bogor' : 'Kampus Sukabumi';
    $info['jenjang'] = substr($nim, 2, 1) == '4' ? 'Sarjana Terapan (D4)' : 'Tidak Diketahui';
    $kodeProdi = substr($nim, 3, 2);
    if ($kodeFakultas == 'J') {
        $prodiMap = [ '01' => 'Komunikasi Digital dan Media', '03' => 'Teknologi Rekayasa Perangkat Lunak', '04' => 'Teknologi Rekayasa Komputer' ];
        $info['prodi'] = $prodiMap[$kodeProdi] ?? 'Tidak Diketahui';
    }
    $kodeTahun = substr($nim, 5, 2);
    $info['angkatan'] = '20' . $kodeTahun . '(' . (int)$kodeTahun + 2000 - 1963 . ')';
    $info['semester_masuk'] = substr($nim, 7, 1) == '1' ? 'Gasal' : 'Genap';
    $info['nomor_urut'] = substr($nim, -3);
    return $info;
}

$nim_info = parseNIM($user['nim']);
$current_page = basename($_SERVER['PHP_SELF']);

// Membuat konten ekstra untuk sidebar
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
    <title>Profil & Pengaturan - Platform Kursus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/tailwind-output.css">
    <link rel="stylesheet" href="/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/css/settings.css?v=<?php echo time(); ?>">
</head>
<body>
   <?php include '../sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Profil & Pengaturan</h2>
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
             <div class="management-grid" style="grid-template-columns: 2fr 1fr; align-items: start;">
                <div class="form-container">
                    <h3><i class="fas fa-user" style="margin-right: 10px;"></i>Profil Mahasiswa</h3>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly></div>
                    <div class="form-group"><label>NIM</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['nim']); ?>" readonly></div>
                    <div class="form-group"><label>Fakultas</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['fakultas']); ?>" readonly></div>
                    <div class="form-group"><label>Program Studi</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['prodi']); ?>" readonly></div>
                    <div class="form-group"><label>Jenjang</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['jenjang']); ?>" readonly></div>
                    <div class="form-group"><label>Angkatan</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($nim_info['angkatan']); ?>" readonly></div>
                </div>

                <div class="form-container">
                    <h3><i class="fas fa-shield-alt" style="margin-right: 10px;"></i>Akun</h3>
                    <div class="form-group"><label>Username</label><input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly></div>
                    <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly></div>
                    <div class="form-group">
                        <label>Ganti Password</label>
                        <p class="form-text" style="margin-top: 5px;">Klik tombol di bawah untuk mengganti password Anda.</p>
                        <a href="/ganti_password.php" class="btn" style="width: 100%;">Ganti Password</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="/js/dashboard.js"></script>
</body>
</html>

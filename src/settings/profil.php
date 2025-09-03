<?php
// File: src/settings/profil.php (DIPERBARUI)

// Pastikan sesi dimulai di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../db.php"; 

// Ambil pesan error/sukses dari session jika ada (setelah redirect dari action)
$password_error = isset($_SESSION['password_error']);
$new_password_err = $_SESSION['new_password_err'] ?? '';
$confirm_password_err = $_SESSION['confirm_password_err'] ?? '';
$password_success = isset($_SESSION['password_success']);

// Hapus pesan dari session agar tidak muncul lagi saat refresh
unset($_SESSION['password_error'], $_SESSION['new_password_err'], $_SESSION['confirm_password_err'], $_SESSION['password_success']);

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
            <?php if ($password_success): ?>
                <div class="alert alert-success" style="background-color: #d4edda; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">Password Anda telah berhasil diperbarui.</div>
            <?php endif; ?>
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
                        <button id="openModalBtn" class="btn" style="width: 100%;">Ganti Password</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ===== KODE POP-UP (MODAL) GANTI PASSWORD ===== -->
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 transition-opacity duration-300 opacity-0 pointer-events-none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-transform duration-300">
            <header class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Ganti Password Anda</h2>
                <button id="closeModalBtn" class="text-gray-400 hover:text-gray-700 transition-colors"><i class="fas fa-times fa-lg"></i></button>
            </header>
            <main class="p-6">
                <div class="form-container" style="box-shadow: none; padding: 0;">
                    <form action="../actions/ganti_password_action.php" method="post">
                        <div class="mb-4">
                            <label for="new_password" class="block text-gray-700 font-medium mb-2">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback" style="display: <?php echo (!empty($new_password_err)) ? 'block' : 'none'; ?>;"><?php echo $new_password_err; ?></span>
                        </div>

                        <div class="mb-6">
                            <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback" style="display: <?php echo (!empty($confirm_password_err)) ? 'block' : 'none'; ?>;"><?php echo $confirm_password_err; ?></span>
                        </div>

                        <div>
                            <input type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:bg-blue-700 cursor-pointer transition-all duration-300" value="Update Password">
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script src="/js/dashboard.js"></script>
    <!-- Script untuk mengontrol Pop-up -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('passwordModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalBtn');
            const modalContent = modal.querySelector('div');

            const openModal = () => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modalContent.classList.remove('scale-95');
            };

            const closeModal = () => {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modalContent.classList.add('scale-95');
            };

            if(openBtn) {
                openBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal();
                });
            }
            
            if(closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }

            if(modal) {
                 modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
            }
           
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeModal(); });
            
            <?php if ($password_error): ?>
                openModal();
            <?php endif; ?>
        });
    </script>
</body>
</html>


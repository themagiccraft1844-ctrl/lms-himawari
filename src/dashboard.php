<?php
// File: dashboard.php

require_once "db.php";

// Cek jika user belum login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// --- TAMBAHAN KODE: Mengambil full_name dari database ---
// Ambil ID user dari session
$user_id = $_SESSION['id'];
$full_name = $_SESSION['username']; // Default fallback jika query gagal

// Siapkan query untuk mengambil full_name
if ($stmt = $mysqli->prepare("SELECT full_name FROM users WHERE id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Jika user ditemukan, ambil full_name-nya
    if ($user = $result->fetch_assoc()) {
        $full_name = $user['full_name'];
    }
    $stmt->close();
}
// ---------------------------------------------------------

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Platform Kursus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; // <-- KODE SIDEBAR DIPANGGIL DI SINI ?>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Dashboard</h2>
            </div>
            <div class="header-actions">
                <div class="user-wrapper" id="user-menu-toggle">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <!-- PERUBAHAN 1: Menampilkan full_name di header -->
                        <h4><?php echo htmlspecialchars($full_name); ?></h4>
                        <small>User</small>
                    </div>
                </div>
                 <div class="dropdown-menu" id="user-dropdown">
                    <a href="settings.php" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>

        <main>
            <div class="welcome-card">
                <!-- PERUBAHAN 2: Menampilkan full_name di kartu selamat datang -->
                <h3>Selamat Datang Kembali, <?php echo htmlspecialchars($full_name); ?>!</h3>
                <p>Jelajahi kursus-kursus yang tersedia dan tingkatkan pengetahuan Anda.</p>
            </div>

            <h3 class="main-title">Ringkasan Aktivitas</h3>
            <div class="cards-container">
                <div class="card">
                    <div>
                        <h2>3</h2>
                        <span>Kursus Diikuti</span>
                    </div>
                    <div><i class="fas fa-book-open"></i></div>
                </div>
                <div class="card">
                    <div>
                        <h2>1</h2>
                        <span>Sertifikat</span>
                    </div>
                    <div><i class="fas fa-award"></i></div>
                </div>
                <div class="card">
                    <div>
                        <h2>75%</h2>
                        <span>Progres Rata-rata</span>
                    </div>
                    <div><i class="fas fa-tasks"></i></div>
                </div>
            </div>

            <div class="content-table" style="margin-top: 2rem;">
                 <h3 class="main-title" style="margin: 20px; padding-bottom: 0;">Lanjutkan Belajar</h3>
                 <table>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-laptop-code" style="color:var(--primary-color); margin-right: 15px;"></i> Pengenalan Machine Learning</td>
                            <td><span>Progres: 80%</span></td>
                            <td><a href="#" class="btn">Lanjutkan</a></td>
                        </tr>
                         <tr>
                            <td><i class="fas fa-database" style="color:var(--primary-color); margin-right: 15px;"></i> Dasar-Dasar SQL</td>
                            <td><span>Progres: 50%</span></td>
                            <td><a href="#" class="btn">Lanjutkan</a></td>
                        </tr>
                    </tbody>
                 </table>
            </div>

        </main>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>


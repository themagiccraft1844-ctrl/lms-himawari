<?php
// File: admin/tambah_kursus.php
// Halaman form untuk menambah kursus baru.

require_once "../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Tambah Kursus Baru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li class="active"><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
            <div class="header-title"><h2><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i></a> Tambah Kursus Baru</h2></div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="form-container">
                <form action="actions/tambah_kursus_action.php" method="post">
                    <div class="form-group">
                        <label>Judul Kursus</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Kursus</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>URL Banner Gambar (Opsional)</label>
                        <input type="text" name="banner_image_url" class="form-control" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Simpan Kursus">
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
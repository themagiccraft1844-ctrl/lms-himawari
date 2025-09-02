<?php
session_start();
require_once "../db.php";

// FIX: Menggunakan logika sesi yang benar
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../index.php");
    exit;
}

// Ambil data dari form sebelumnya
$course_id = $_POST['course_id'] ?? 0;
// FIX: Menggunakan 'title' sesuai nama input di form
$judul_konten = $_POST['title'] ?? 'Materi Baru'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Generate Materi dengan AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li class="active"><a href="kelola_kursus.php"><i class="fas fa-book"></i> <span>Kelola Kursus</span></a></li>
            <li><a href="kelola_user.php"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li><a href="ganti_password.php"><i class="fas fa-key"></i> <span>Ganti Password</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2><a href="kelola_kursus.php?id=<?php echo $course_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Generate Materi AI</h2>
            </div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="add-content-container" style="max-width: 800px; margin: auto;">
                <h3>Prompt untuk "<?php echo htmlspecialchars($judul_konten); ?>"</h3>
                <form action="actions/generate_materi_action.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($judul_konten); ?>">

                    <div class="form-group">
                        <label for="prompt_manual">Prompt Manual</label>
                        <p style="font-size: 0.9em; color: #666; margin-top: 0;">Jelaskan secara detail materi apa yang ingin Anda buat. Contoh: "Buatkan saya materi pengenalan tentang machine learning untuk pemula, jelaskan apa itu supervised dan unsupervised learning dengan contoh sederhana."</p>
                        <textarea name="prompt_manual" class="form-control" style="min-height: 150px;" placeholder="Masukkan prompt Anda di sini..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ref_url">Referensi dari Link (URL)</label>
                        <input type="text" name="ref_url" class="form-control" placeholder="https://contoh.com/artikel-referensi">
                    </div>

                    <div class="form-group">
                        <label for="ref_file">Referensi dari File (txt, docx, pptx)</label>
                        <input type="file" name="ref_file" class="form-control" accept=".txt,.docx,.pptx">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Generate Materi</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

<?php
session_start();
// FIX: Menggunakan path yang benar (satu level ke atas)
require_once '../db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../index.php");
    exit;
}

$course_id = $_POST['course_id'] ?? 0;
$judul_konten = $_POST['title'] ?? 'Kuis Baru';

$stmt = $mysqli->prepare("SELECT id, title FROM course_contents WHERE course_id = ? AND content_type = 'materi'");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$materials = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Generate Kuis Hibrida dengan AI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .material-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #fff;
            border-radius: 5px;
        }
        .question-count-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
    </style>
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
                <h2><a href="kelola_kursus.php?id=<?php echo $course_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Generate Kuis AI</h2>
            </div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="add-content-container" style="max-width: 800px; margin: auto;">
                <h3>Prompt untuk "<?php echo htmlspecialchars($judul_konten); ?>"</h3>
                <form action="actions/generate_kuis_action.php" method="POST">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <input type="hidden" name="title" value="<?php echo htmlspecialchars($judul_konten); ?>">
                    <input type="hidden" name="content_type" value="<?php echo htmlspecialchars($_POST['content_type']); ?>">

                    <div class="form-group">
                        <label>Pilih Materi Referensi</label>
                        <p style="font-size: 0.9em; color: #666; margin-top: 0;">Pilih satu atau lebih materi yang akan dijadikan dasar pembuatan soal kuis.</p>
                        <div class="material-list">
                            <?php while ($material = $materials->fetch_assoc()): ?>
                                <label>
                                    <input type="checkbox" name="materi_ids[]" value="<?php echo $material['id']; ?>">
                                    <?php echo htmlspecialchars($material['title']); ?>
                                </label><br>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <h3>Jumlah Soal per Tipe</h3>
                    <div class="question-count-grid">
                        <div class="form-group">
                            <label for="jumlah_pg">Pilihan Ganda</label>
                            <input type="number" id="jumlah_pg" name="jumlah_pg" class="form-control" value="5" min="0" max="20">
                        </div>
                        <div class="form-group">
                            <label for="jumlah_tf">Benar / Salah</label>
                            <input type="number" id="jumlah_tf" name="jumlah_tf" class="form-control" value="0" min="0" max="20">
                        </div>
                        <div class="form-group">
                            <label for="jumlah_esai">Jawaban Singkat</label>
                            <input type="number" id="jumlah_esai" name="jumlah_esai" class="form-control" value="0" min="0" max="20">
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">Generate Kuis</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

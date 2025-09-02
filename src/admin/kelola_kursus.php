<?php
session_start();
require_once "../db.php"; 

// Menggunakan logika sesi dan redirect dari kode original Anda
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../index.php");
    exit;
}

// Menggunakan parameter 'id' dari URL sesuai kode original Anda
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id > 0) {
    // --- MODE TAMPILKAN DETAIL KONTEN KURSUS ---

    // Menggunakan nama tabel 'courses' dan kolom 'title' yang benar
    $stmt_course = $mysqli->prepare("SELECT title FROM courses WHERE id = ?");
    $stmt_course->bind_param("i", $course_id);
    $stmt_course->execute();
    $course_result = $stmt_course->get_result();
    $course = $course_result->fetch_assoc();
    if (!$course) {
        die("Kursus tidak ditemukan.");
    }
    $course_title = $course['title'];
    $stmt_course->close();

    // Menggunakan nama tabel 'course_contents' dan kolom yang benar
    $stmt_contents = $mysqli->prepare("SELECT id, title, content_type FROM course_contents WHERE course_id = ? ORDER BY created_at ASC");
    $stmt_contents->bind_param("i", $course_id);
    $stmt_contents->execute();
    $contents = $stmt_contents->get_result();

} else {
    // --- MODE TAMPILKAN DAFTAR SEMUA KURSUS ---
    // Menggunakan nama tabel 'courses' dan kolom 'title', 'description' yang benar
    $all_courses = $mysqli->query("SELECT id, title, description FROM courses ORDER BY title ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kursus<?php if(isset($course_title)) echo ": " . htmlspecialchars($course_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <!-- FIX: Menggunakan struktur sidebar dari file original -->
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
        <?php if ($course_id > 0): ?>
        <!-- Tampilan Detail Konten Kursus (Struktur disesuaikan dengan original) -->
        <header>
            <div class="header-title">
                <h2><a href="kelola_kursus.php" class="back-link"><i class="fas fa-arrow-left"></i></a> Kelola: <?php echo htmlspecialchars($course_title); ?></h2>
            </div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="management-grid">
                <div class="content-list-container">
                    <h3>Daftar Konten</h3>
                    <div class="content-list">
                        <?php if($contents->num_rows === 0): ?>
                            <p>Belum ada konten di kursus ini.</p>
                        <?php else: ?>
                            <ul>
                                <?php while($content = $contents->fetch_assoc()): ?>
                                    <li>
                                        <a href="preview_konten.php?id=<?php echo $content['id']; ?>" class="content-link">
                                            <?php 
                                                $icon = 'fa-file-alt';
                                                if($content['content_type'] == 'quiz') $icon = 'fa-question-circle';
                                                if($content['content_type'] == 'ujian') $icon = 'fa-graduation-cap';
                                            ?>
                                            <i class="fas <?php echo $icon; ?>"></i>
                                            <span><?php echo htmlspecialchars($content['title']); ?></span>
                                        </a>
                                        <div class="content-actions">
                                            <?php 
                                                $edit_link = ($content['content_type'] == 'materi') 
                                                    ? 'editor_materi.php?content_id=' . $content['id'] 
                                                    : 'editor_kuis.php?content_id=' . $content['id'];
                                            ?>
                                            <a href="<?php echo $edit_link; ?>" class="btn-action-sm edit">Edit</a>
                                            <a href="actions/hapus_konten_action.php?id=<?php echo $content['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn-action-sm delete" onclick="return confirm('Apakah Anda yakin ingin menghapus konten ini?');">Hapus</a>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="add-content-container">
                    <h3>Tambah Konten Baru</h3>
                    <form id="addContentForm" method="post">
                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                        <div class="form-group">
                            <label for="title">Judul Konten</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="content_type">Jenis Konten</label>
                            <select id="content_type" name="content_type" class="form-control" required>
                                <option value="materi">Materi</option>
                                <option value="quiz">Kuis</option>
                                <option value="ujian">Ujian</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- Tombol Baru -->
                            <button type="button" class="btn" onclick="submitFormManual()">Buat & Lanjutkan</button>
                            <button type="button" class="btn" onclick="submitFormAI()" style="margin-left: 10px;">Buat Konten (AI)</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>

        <?php else: ?>
        <!-- Tampilan Daftar Semua Kursus -->
        <header>
             <div class="header-title"><h2>Kelola Kursus</h2></div>
             <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="course-list-container"> <!-- Menggunakan class yang mungkin sudah ada -->
                 <h3>Pilih Kursus untuk Dikelola</h3>
                 <table class="content-table"> <!-- Menggunakan class yang mungkin sudah ada -->
                    <thead>
                        <tr>
                            <th>Nama Kursus</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course_item = $all_courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course_item['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($course_item['description'], 0, 100)) . '...'; ?></td>
                            <td>
                                <a href="kelola_kursus.php?id=<?php echo $course_item['id']; ?>" class="btn">Kelola Konten</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php endif; ?>
    </div>

    <?php if ($course_id > 0): ?>
    <script>
    function submitFormManual() {
        const form = document.getElementById('addContentForm');
        form.action = 'actions/tambah_konten_action.php';
        form.submit();
    }

    function submitFormAI() {
        const form = document.getElementById('addContentForm');
        const contentType = document.getElementById('content_type').value;
        if (contentType === 'materi') {
            form.action = 'prompt_materi.php';
        } else {
            form.action = 'prompt_kuis.php';
        }
        form.submit();
    }
    </script>
    <?php endif; ?>
</body>
</html>

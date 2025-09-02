<?php
// File: view_course.php
session_start();
require_once "db.php";

// Cek jika user belum login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$course_id){ header("location: dashboard.php"); exit; }

// Ambil detail kursus
$sql_course = "SELECT title, description FROM courses WHERE id = ?";
$course = null;
if($stmt_course = $mysqli->prepare($sql_course)){
    $stmt_course->bind_param("i", $course_id);
    if($stmt_course->execute()){
        $result = $stmt_course->get_result();
        $course = $result->fetch_assoc();
    }
    $stmt_course->close();
}

if(!$course){ die("Kursus tidak ditemukan."); }

// Ambil semua konten untuk kursus ini, gabungkan dengan detail materi
$sql_contents = "SELECT cc.id, cc.content_type, cc.title, md.body_html 
                 FROM course_contents cc
                 LEFT JOIN materi_details md ON cc.id = md.content_id
                 WHERE cc.course_id = ? ORDER BY cc.created_at ASC";
$contents = [];
if($stmt_contents = $mysqli->prepare($sql_contents)){
    $stmt_contents->bind_param("i", $course_id);
    if($stmt_contents->execute()){
        $result_contents = $stmt_contents->get_result();
        while($row = $result_contents->fetch_assoc()){
            $contents[] = $row;
        }
    }
    $stmt_contents->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Platform Kursus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- FIX: Menggunakan stylesheet yang benar (dashboard.css) -->
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- FIX: Menggunakan struktur sidebar dari file original -->
    <div class="sidebar">
        <div class="logo">
            <h2>Platform Kursus</h2>
        </div>
        <ul class="nav-links">
            <li class="active"><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="#"><i class="fas fa-book"></i> <span>Kursus Saya</span></a></li>
            <li><a href="chat.php"><i class="fas fa-comments"></i> <span>Chat with AI</span></a></li>
            <li><a href="#"><i class="fas fa-cog"></i> <span>Pengaturan</span></a></li>
            <li class="logout-link"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
                <h2><?php echo htmlspecialchars($course['title']); ?></h2>
            </div>
            <div class="user-wrapper">
                <i class="fas fa-user-circle"></i>
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                    <small>User</small>
                </div>
            </div>
        </header>

        <main>
            <div class="course-description">
                <p><?php echo htmlspecialchars($course['description']); ?></p>
            </div>
            
            <h3 class="main-title">Materi & Kuis</h3>
            <div class="accordion">
                <?php if(!empty($contents)): ?>
                    <?php foreach($contents as $content): ?>
                        <div class="accordion-item">
                            <button class="accordion-header">
                                <?php 
                                    $icon = 'fa-file-alt'; // default untuk materi
                                    if($content['content_type'] == 'quiz') $icon = 'fa-question-circle';
                                    if($content['content_type'] == 'ujian') $icon = 'fa-graduation-cap';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                                <?php echo htmlspecialchars($content['title']); ?>
                                <i class="fas fa-chevron-down icon-toggle"></i>
                            </button>
                            <div class="accordion-content">
                                <?php if($content['content_type'] == 'materi'): ?>
                                    <div class="materi-body">
                                        <?php echo $content['body_html']; // Tampilkan HTML dari editor ?>
                                    </div>
                                <?php else: // Untuk Kuis atau Ujian ?>
                                    <div class="quiz-link-body">
                                        <p>Ini adalah sebuah <?php echo $content['content_type']; ?> untuk menguji pemahaman Anda.</p>
                                        <!-- FIX: Tombol ini sekarang berfungsi -->
                                        <a href="start_quiz.php?content_id=<?php echo $content['id']; ?>" class="btn">Mulai <?php echo ucfirst($content['content_type']); ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Konten untuk kursus ini belum tersedia.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>

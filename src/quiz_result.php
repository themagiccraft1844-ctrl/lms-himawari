<?php
session_start();

if (!isset($_SESSION['quiz_result'])) {
    header("location: dashboard.php");
    exit;
}

$result = $_SESSION['quiz_result'];
unset($_SESSION['quiz_result']);

$score = $result['score'];
$correct = $result['correct_answers'];
$total = $result['total_questions'];
$course_id = $result['course_id']; // Diambil dari submit_quiz
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Kuis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/quiz.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Platform Kursus</h2></div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li class="active"><a href="#"><i class="fas fa-book"></i> <span>Kursus Saya</span></a></li>
            <li><a href="chat.php"><i class="fas fa-comments"></i> <span>Chat with AI</span></a></li>
            <li><a href="#"><i class="fas fa-cog"></i> <span>Pengaturan</span></a></li>
            <li class="logout-link"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2>Hasil Kuis</h2>
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
            <div class="quiz-result-container">
                <h1>Selamat!</h1>
                <h2>Skor Akhir Anda: <?php echo round($score, 2); ?></h2>
                <p>Anda berhasil menjawab <?php echo $correct; ?> dari <?php echo $total; ?> soal objektif dengan benar.</p>
                <p>Jawaban esai (jika ada) akan diperiksa secara manual oleh instruktur.</p>
                <a href="view_course.php?id=<?php echo $course_id; ?>" class="btn">Kembali ke Halaman Kursus</a>
            </div>
        </main>
    </div>
</body>
</html>

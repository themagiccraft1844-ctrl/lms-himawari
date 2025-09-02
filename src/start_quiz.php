<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
if (!$content_id) {
    header("location: dashboard.php");
    exit;
}

$stmt = $mysqli->prepare("
    SELECT cc.title, q.description, q.id as quiz_id, cc.course_id
    FROM course_contents cc
    JOIN quizzes q ON cc.id = q.content_id
    WHERE cc.id = ?
");
$stmt->bind_param("i", $content_id);
$stmt->execute();
$quiz_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz_details) {
    die("Kuis tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mulai Kuis: <?php echo htmlspecialchars($quiz_details['title']); ?></title>
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
                <a href="view_course.php?id=<?php echo $quiz_details['course_id']; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a>
                <h2><?php echo htmlspecialchars($quiz_details['title']); ?></h2>
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
            <div class="quiz-start-container">
                <h1>Konfirmasi Memulai Kuis</h1>
                <p><?php echo htmlspecialchars($quiz_details['description']); ?></p>
                <form action="quiz.php" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_details['quiz_id']; ?>">
                    <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                    <button type="submit" class="btn">Mulai Sekarang</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

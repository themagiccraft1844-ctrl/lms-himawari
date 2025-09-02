<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;

if (!$quiz_id) {
    header("location: dashboard.php");
    exit;
}

$stmt_title = $mysqli->prepare("SELECT title, course_id FROM course_contents WHERE id = ?");
$stmt_title->bind_param("i", $content_id);
$stmt_title->execute();
$quiz_info = $stmt_title->get_result()->fetch_assoc();
$quiz_title = $quiz_info['title'];
$course_id = $quiz_info['course_id'];
$stmt_title->close();

$stmt_questions = $mysqli->prepare("SELECT id, question_text, question_type FROM quiz_questions WHERE quiz_id = ?");
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$questions = $stmt_questions->get_result();
$stmt_questions->close();

$all_questions = [];
while ($q = $questions->fetch_assoc()) {
    if ($q['question_type'] == 'multiple_choice' || $q['question_type'] == 'true_false') {
        $stmt_options = $mysqli->prepare("SELECT id, option_text FROM question_options WHERE question_id = ?");
        $stmt_options->bind_param("i", $q['id']);
        $stmt_options->execute();
        $options_result = $stmt_options->get_result();
        $options = [];
        while ($opt = $options_result->fetch_assoc()) {
            $options[] = $opt;
        }
        $q['options'] = $options;
        $stmt_options->close();
    }
    $all_questions[] = $q;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mengerjakan: <?php echo htmlspecialchars($quiz_title); ?></title>
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
                 <h2><?php echo htmlspecialchars($quiz_title); ?></h2>
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
            <div class="quiz-form-container">
                <form action="submit_quiz.php" method="POST">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    
                    <?php $nomor_soal = 1; foreach ($all_questions as $question): ?>
                    <div class="question-block">
                        <p class="question-text"><strong><?php echo $nomor_soal++; ?>.</strong> <?php echo htmlspecialchars($question['question_text']); ?></p>
                        
                        <?php if ($question['question_type'] == 'multiple_choice' || $question['question_type'] == 'true_false'): ?>
                            <div class="options-group">
                                <?php foreach ($question['options'] as $option): ?>
                                    <label>
                                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo $option['id']; ?>" required>
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($question['question_type'] == 'essay'): ?>
                            <textarea name="answers[<?php echo $question['id']; ?>]" class="form-control" rows="4" required></textarea>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn">Kumpulkan Jawaban</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

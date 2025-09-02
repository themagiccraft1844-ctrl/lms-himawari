<?php
// File: admin/preview_konten.php

require_once "../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

$content_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$content_id){ header("location: index.php"); exit; }

// Ambil detail konten dasar
$sql = "SELECT course_id, content_type, title FROM course_contents WHERE id = ?";
$content = null;
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $content_id);
    if($stmt->execute()){
        $result = $stmt->get_result();
        $content = $result->fetch_assoc();
    }
    $stmt->close();
}

if(!$content){
    die("Konten tidak ditemukan.");
}

// Ambil detail spesifik berdasarkan tipe konten
if($content['content_type'] == 'materi'){
    $sql_detail = "SELECT body_html FROM materi_details WHERE content_id = ?";
    if($stmt_detail = $mysqli->prepare($sql_detail)){
        $stmt_detail->bind_param("i", $content_id);
        if($stmt_detail->execute()){
            $stmt_detail->bind_result($content['body_html']);
            $stmt_detail->fetch();
        }
        $stmt_detail->close();
    }
} elseif ($content['content_type'] == 'quiz' || $content['content_type'] == 'ujian') {
    $sql_quiz = "SELECT q.id as quiz_id, q.description FROM quizzes q WHERE q.content_id = ?";
    if($stmt_quiz = $mysqli->prepare($sql_quiz)){
        $stmt_quiz->bind_param("i", $content_id);
        if($stmt_quiz->execute()){
            $result_quiz = $stmt_quiz->get_result();
            if($quiz_details = $result_quiz->fetch_assoc()){
                $content['description'] = $quiz_details['description'];
                $quiz_id = $quiz_details['quiz_id'];
                
                // Ambil soal dan opsi jawaban
                $sql_questions = "SELECT id, question_text FROM quiz_questions WHERE quiz_id = ?";
                if($stmt_q = $mysqli->prepare($sql_questions)){
                    $stmt_q->bind_param("i", $quiz_id);
                    $stmt_q->execute();
                    $result_q = $stmt_q->get_result();
                    $questions = [];
                    while($q_row = $result_q->fetch_assoc()){
                        $sql_options = "SELECT option_text, is_correct FROM question_options WHERE question_id = ?";
                        if($stmt_o = $mysqli->prepare($sql_options)){
                            $stmt_o->bind_param("i", $q_row['id']);
                            $stmt_o->execute();
                            $result_o = $stmt_o->get_result();
                            $q_row['options'] = $result_o->fetch_all(MYSQLI_ASSOC);
                            $stmt_o->close();
                        }
                        $questions[] = $q_row;
                    }
                    $content['questions'] = $questions;
                    $stmt_q->close();
                }
            }
        }
        $stmt_quiz->close();
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Preview: <?php echo htmlspecialchars($content['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="main-content" style="margin-left:0; width:100%;">
        <header>
            <div class="header-title"><h2><a href="kelola_kursus.php?id=<?php echo $content['course_id']; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Preview: <?php echo htmlspecialchars($content['title']); ?></h2></div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="preview-container">
                <?php if($content['content_type'] == 'materi'): ?>
                    <div class="preview-materi">
                        <?php echo $content['body_html']; // Tampilkan HTML langsung dari editor ?>
                    </div>
                <?php elseif(isset($content['questions'])): ?>
                    <div class="preview-quiz">
                        <?php if(!empty($content['description'])): ?>
                            <p><?php echo htmlspecialchars($content['description']); ?></p>
                        <?php endif; ?>
                        <hr>
                        <?php foreach($content['questions'] as $index => $question): ?>
                            <div class="quiz-question">
                                <h4><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?></h4>
                                <ul class="quiz-options">
                                    <?php foreach($question['options'] as $option): ?>
                                        <li class="<?php echo $option['is_correct'] ? 'correct-option' : ''; ?>">
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                            <?php if($option['is_correct']) echo ' <i class="fas fa-check-circle"></i>'; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

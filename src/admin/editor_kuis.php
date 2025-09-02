<?php
// File: admin/editor_kuis.php

require_once "../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
if(!$content_id){ header("location: index.php"); exit; }

// Ambil detail kuis
$sql_quiz = "SELECT cc.title, cc.course_id, q.id as quiz_id, q.description
             FROM course_contents cc
             JOIN quizzes q ON cc.id = q.content_id
             WHERE cc.id = ?";
$quiz_id = 0; $course_id = 0; $quiz_title = "Kuis tidak ditemukan"; $quiz_desc = "";
if($stmt_quiz = $mysqli->prepare($sql_quiz)){
    $stmt_quiz->bind_param("i", $content_id);
    if($stmt_quiz->execute()){
        $result = $stmt_quiz->get_result();
        if($row = $result->fetch_assoc()){
            $quiz_id = $row['quiz_id'];
            $course_id = $row['course_id'];
            $quiz_title = $row['title'];
            $quiz_desc = $row['description'];
        }
    }
    $stmt_quiz->close();
}

// Ambil daftar soal yang sudah ada
$questions = [];
if($quiz_id > 0){
    $sql_questions = "SELECT id, question_text FROM quiz_questions WHERE quiz_id = ?";
    if($stmt_q = $mysqli->prepare($sql_questions)){
        $stmt_q->bind_param("i", $quiz_id);
        if($stmt_q->execute()){
            $result_q = $stmt_q->get_result();
            while($row_q = $result_q->fetch_assoc()){
                $questions[] = $row_q;
            }
        }
        $stmt_q->close();
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Editor Kuis: <?php echo htmlspecialchars($quiz_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="main-content" style="margin-left:0; width:100%;">
        <header>
            <div class="header-title"><h2><a href="kelola_kursus.php?id=<?php echo $course_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Editor Kuis: <?php echo htmlspecialchars($quiz_title); ?></h2></div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="quiz-builder-container">
                <!-- FORM BARU UNTUK JUDUL & DESKRIPSI -->
                <div class="form-container" style="margin-bottom: 30px;">
                    <h3>Detail Kuis/Ujian</h3>
                    <form action="actions/simpan_kuis_detail_action.php" method="post">
                        <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                        <div class="form-group">
                            <label>Judul</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($quiz_title); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($quiz_desc); ?></textarea>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn" value="Simpan Detail">
                        </div>
                    </form>
                </div>
                <h3>Daftar Soal</h3>
                <div class="question-list-admin">
                    <?php if(empty($questions)): ?>
                        <p>Belum ada soal di kuis ini.</p>
                    <?php else: ?>
                        <?php foreach($questions as $q): ?>
                            <div class="question-item">
                                <p><?php echo htmlspecialchars($q['question_text']); ?></p>
                                <div class="question-actions">
                                    <a href="edit_soal.php?id=<?php echo $q['id']; ?>&content_id=<?php echo $content_id; ?>" class="btn-action-sm edit">Edit</a>
                                    <a href="actions/hapus_soal_action.php?id=<?php echo $q['id']; ?>&content_id=<?php echo $content_id; ?>" class="btn-action-sm delete" onclick="return confirm('Yakin ingin menghapus soal ini?');">Hapus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h3>Tambah Soal Baru</h3>
                <form action="actions/tambah_soal_action.php" method="post" class="form-container">
                    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                    <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                    <div class="form-group">
                        <label>Teks Pertanyaan</label>
                        <textarea name="question_text" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Jenis Jawaban</label>
                        <select name="question_type" class="form-control" id="question-type-select">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="true_false">Benar / Salah</option>
                            <option value="essay">Esai</option>
                        </select>
                    </div>
                    
                    <!-- Opsi Jawaban Dinamis -->
                    <div id="answer-options-container">
                        <!-- Pilihan Ganda -->
                        <div id="multiple-choice-options">
                            <label>Pilihan Jawaban (Pilih satu sebagai jawaban yang benar)</label>
                            <div id="options-wrapper">
                                <!-- Opsi akan ditambahkan oleh JS -->
                            </div>
                            <button type="button" id="add-option-btn" class="btn-sm" style="margin-top:10px;">Tambah Opsi</button>
                        </div>
                        <!-- Benar / Salah -->
                        <div id="true-false-options" style="display:none;">
                            <label>Jawaban Benar</label>
                            <div class="tf-group">
                                <input type="radio" id="true_answer" name="true_false_answer" value="1" checked> <label for="true_answer">Benar</label>
                            </div>
                             <div class="tf-group">
                                <input type="radio" id="false_answer" name="true_false_answer" value="0"> <label for="false_answer">Salah</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top:20px;">
                        <input type="submit" class="btn" value="Simpan Soal">
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="../js/dashboard.js"></script>
</body>
</html>


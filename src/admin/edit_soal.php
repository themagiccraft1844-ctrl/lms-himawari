<?php
// File: admin/edit_soal.php

require_once "../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
if(!$question_id || !$content_id){ header("location: index.php"); exit; }

// Ambil data soal yang akan diedit
$sql_q = "SELECT question_text, question_type FROM quiz_questions WHERE id = ?";
$question = null;
if($stmt_q = $mysqli->prepare($sql_q)){
    $stmt_q->bind_param("i", $question_id);
    if($stmt_q->execute()){
        $result = $stmt_q->get_result();
        $question = $result->fetch_assoc();
    }
    $stmt_q->close();
}

// Ambil opsi jawaban jika ada
$options = [];
if($question){
    $sql_o = "SELECT option_text, is_correct FROM question_options WHERE question_id = ?";
    if($stmt_o = $mysqli->prepare($sql_o)){
        $stmt_o->bind_param("i", $question_id);
        if($stmt_o->execute()){
            $result_o = $stmt_o->get_result();
            while($row_o = $result_o->fetch_assoc()){
                $options[] = $row_o;
            }
        }
        $stmt_o->close();
    }
}

if(!$question){ die("Soal tidak ditemukan."); }
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Edit Soal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="main-content" style="margin-left:0; width:100%;">
        <header>
            <div class="header-title"><h2><a href="editor_kuis.php?content_id=<?php echo $content_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Edit Soal</h2></div>
        </header>
        <main>
            <form action="actions/edit_soal_action.php" method="post" class="form-container">
                <input type="hidden" name="question_id" value="<?php echo $question_id; ?>">
                <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                <div class="form-group">
                    <label>Teks Pertanyaan</label>
                    <textarea name="question_text" class="form-control" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Jenis Jawaban</label>
                    <select name="question_type" class="form-control" id="question-type-select">
                        <option value="multiple_choice" <?php if($question['question_type'] == 'multiple_choice') echo 'selected'; ?>>Pilihan Ganda</option>
                        <option value="true_false" <?php if($question['question_type'] == 'true_false') echo 'selected'; ?>>Benar / Salah</option>
                        <option value="essay" <?php if($question['question_type'] == 'essay') echo 'selected'; ?>>Esai</option>
                    </select>
                </div>
                
                <div id="answer-options-container">
                    <div id="multiple-choice-options" style="display:none;">
                        <label>Pilihan Jawaban</label>
                        <div id="options-wrapper">
                            <?php if($question['question_type'] == 'multiple_choice'): ?>
                                <?php foreach($options as $index => $opt): ?>
                                <div class="option-input-group">
                                    <input type="radio" name="is_correct" value="<?php echo $index; ?>" <?php if($opt['is_correct']) echo 'checked'; ?> required>
                                    <input type="text" name="options[]" class="form-control" value="<?php echo htmlspecialchars($opt['option_text']); ?>" required>
                                    <button type="button" class="btn-action-sm delete remove-option">Hapus</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" id="add-option-btn" class="btn-sm" style="margin-top:10px;">Tambah Opsi</button>
                    </div>
                    <div id="true-false-options" style="display:none;">
                        <label>Jawaban Benar</label>
                        <?php $true_is_correct = false; if(!empty($options) && $options[0]['option_text'] == 'Benar' && $options[0]['is_correct']) $true_is_correct = true; ?>
                        <div class="tf-group">
                            <input type="radio" id="true_answer" name="true_false_answer" value="1" <?php if($true_is_correct) echo 'checked'; ?>> <label for="true_answer">Benar</label>
                        </div>
                         <div class="tf-group">
                            <input type="radio" id="false_answer" name="true_false_answer" value="0" <?php if(!$true_is_correct) echo 'checked'; ?>> <label for="false_answer">Salah</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top:20px;">
                    <input type="submit" class="btn" value="Update Soal">
                </div>
            </form>
        </main>
    </div>
    <script src="../js/dashboard.js"></script>
</body>
</html>

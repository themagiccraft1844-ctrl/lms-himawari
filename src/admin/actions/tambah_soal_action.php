<?php
// File: admin/actions/tambah_soal_action.php (Diperbarui & Lengkap)

require_once "../../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quiz_id = $_POST['quiz_id'];
    $content_id = $_POST['content_id'];
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];

    // 1. Masukkan pertanyaan ke tabel quiz_questions
    $sql_question = "INSERT INTO quiz_questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)";
    if ($stmt_q = $mysqli->prepare($sql_question)) {
        $stmt_q->bind_param("iss", $quiz_id, $question_text, $question_type);
        if ($stmt_q->execute()) {
            $question_id = $stmt_q->insert_id; // Dapatkan ID soal yang baru dibuat

            // 2. Proses opsi jawaban berdasarkan tipe soal
            if ($question_type == 'multiple_choice' && isset($_POST['options'])) {
                $options = $_POST['options'];
                $correct_option_index = $_POST['is_correct'];

                $sql_option = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
                if ($stmt_o = $mysqli->prepare($sql_option)) {
                    foreach ($options as $index => $option_text) {
                        if(!empty(trim($option_text))){ // Hanya simpan jika opsi tidak kosong
                            $is_correct = ($index == $correct_option_index) ? 1 : 0;
                            $stmt_o->bind_param("isi", $question_id, $option_text, $is_correct);
                            $stmt_o->execute();
                        }
                    }
                    $stmt_o->close();
                }
            } elseif ($question_type == 'true_false') {
                $correct_answer = $_POST['true_false_answer']; // 1 untuk Benar, 0 untuk Salah
                $sql_option = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
                if ($stmt_o = $mysqli->prepare($sql_option)) {
                    // Simpan opsi "Benar"
                    $option_text_true = "Benar";
                    $is_correct_true = ($correct_answer == 1) ? 1 : 0;
                    $stmt_o->bind_param("isi", $question_id, $option_text_true, $is_correct_true);
                    $stmt_o->execute();
                    
                    // Simpan opsi "Salah"
                    $option_text_false = "Salah";
                    $is_correct_false = ($correct_answer == 0) ? 1 : 0;
                    $stmt_o->bind_param("isi", $question_id, $option_text_false, $is_correct_false);
                    $stmt_o->execute();
                    
                    $stmt_o->close();
                }
            }
            // Tipe 'essay' tidak perlu menyimpan apa pun di tabel 'question_options'
        } else {
            echo "Error saat menyimpan pertanyaan: " . $stmt_q->error;
        }
        $stmt_q->close();
    }
    $mysqli->close();
    header("location: ../editor_kuis.php?content_id=" . $content_id);
    exit();
}
?>
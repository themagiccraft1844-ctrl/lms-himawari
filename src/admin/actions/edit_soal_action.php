<?php
// File: admin/actions/edit_soal_action.php

require_once "../../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_id = $_POST['question_id'];
    $content_id = $_POST['content_id'];
    $question_text = trim($_POST['question_text']);
    $question_type = $_POST['question_type'];

    // Mulai transaksi
    $mysqli->begin_transaction();

    try {
        // 1. Update pertanyaan di tabel quiz_questions
        $sql_update_q = "UPDATE quiz_questions SET question_text = ?, question_type = ? WHERE id = ?";
        $stmt_update_q = $mysqli->prepare($sql_update_q);
        $stmt_update_q->bind_param("ssi", $question_text, $question_type, $question_id);
        $stmt_update_q->execute();
        $stmt_update_q->close();

        // 2. Hapus semua opsi jawaban lama untuk soal ini
        $sql_delete_o = "DELETE FROM question_options WHERE question_id = ?";
        $stmt_delete_o = $mysqli->prepare($sql_delete_o);
        $stmt_delete_o->bind_param("i", $question_id);
        $stmt_delete_o->execute();
        $stmt_delete_o->close();

        // 3. Masukkan kembali opsi jawaban yang baru (jika ada)
        if ($question_type == 'multiple_choice' && isset($_POST['options'])) {
            $options = $_POST['options'];
            $correct_option_index = $_POST['is_correct'];
            $sql_insert_o = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
            $stmt_insert_o = $mysqli->prepare($sql_insert_o);
            foreach ($options as $index => $option_text) {
                if(!empty(trim($option_text))){
                    $is_correct = ($index == $correct_option_index) ? 1 : 0;
                    $stmt_insert_o->bind_param("isi", $question_id, $option_text, $is_correct);
                    $stmt_insert_o->execute();
                }
            }
            $stmt_insert_o->close();
        } elseif ($question_type == 'true_false') {
            $correct_answer = $_POST['true_false_answer'];
            $sql_insert_o = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
            $stmt_insert_o = $mysqli->prepare($sql_insert_o);
            // Simpan opsi "Benar"
            $option_text_true = "Benar";
            $is_correct_true = ($correct_answer == 1) ? 1 : 0;
            $stmt_insert_o->bind_param("isi", $question_id, $option_text_true, $is_correct_true);
            $stmt_insert_o->execute();
            // Simpan opsi "Salah"
            $option_text_false = "Salah";
            $is_correct_false = ($correct_answer == 0) ? 1 : 0;
            $stmt_insert_o->bind_param("isi", $question_id, $option_text_false, $is_correct_false);
            $stmt_insert_o->execute();
            $stmt_insert_o->close();
        }

        // Jika semua berhasil, commit transaksi
        $mysqli->commit();

    } catch (mysqli_sql_exception $exception) {
        $mysqli->rollback(); // Batalkan semua perubahan jika ada error
        throw $exception;
    }

    $mysqli->close();
    header("location: ../editor_kuis.php?content_id=" . $content_id);
    exit();
}
?>

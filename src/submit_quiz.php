<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['id'];
    $quiz_id = (int)$_POST['quiz_id'];
    // FIX: Mengambil course_id yang dikirim dari form quiz.php
    $course_id = (int)$_POST['course_id']; 
    $answers = $_POST['answers'] ?? [];
    
    $score = 0;
    $total_objective_questions = 0; // Hanya hitung soal yang bisa dinilai otomatis

    foreach ($answers as $question_id => $answer) {
        $question_id = (int)$question_id;

        // Cek tipe soal
        $stmt_type = $mysqli->prepare("SELECT question_type FROM quiz_questions WHERE id = ?");
        $stmt_type->bind_param("i", $question_id);
        $stmt_type->execute();
        $q_type = $stmt_type->get_result()->fetch_assoc()['question_type'];
        $stmt_type->close();

        // Pengecekan skor hanya untuk PG dan T/F
        if ($q_type == 'multiple_choice' || $q_type == 'true_false') {
            $total_objective_questions++;
            $option_id = (int)$answer;
            $stmt_check = $mysqli->prepare("SELECT is_correct FROM question_options WHERE id = ? AND question_id = ?");
            $stmt_check->bind_param("ii", $option_id, $question_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result()->fetch_assoc();
            $stmt_check->close();

            if ($result && $result['is_correct'] == 1) {
                $score++;
            }
        }
        // Soal esai tidak dinilai di sini
    }

    // Skor hanya dihitung berdasarkan soal objektif
    $final_score = ($total_objective_questions > 0) ? ($score / $total_objective_questions) * 100 : 0;

    // Simpan hasil ke session untuk ditampilkan
    $_SESSION['quiz_result'] = [
        'score' => $final_score,
        'correct_answers' => $score,
        'total_questions' => $total_objective_questions,
        // FIX: Menyimpan course_id agar bisa digunakan di halaman hasil
        'course_id' => $course_id 
    ];

    header("Location: quiz_result.php");
    exit();
}

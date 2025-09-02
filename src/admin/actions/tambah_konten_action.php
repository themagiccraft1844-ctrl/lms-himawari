<?php
// File: admin/actions/tambah_konten_action.php (File BARU)

require_once "../../db.php";
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $title = trim($_POST['title']);
    $content_type = $_POST['content_type'];

    // 1. Masukkan ke tabel course_contents
    $sql = "INSERT INTO course_contents (course_id, title, content_type) VALUES (?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iss", $course_id, $title, $content_type);
        if ($stmt->execute()) {
            $content_id = $stmt->insert_id; // Dapatkan ID dari item konten yang baru dibuat

            // 2. Buat entri di tabel detail yang sesuai
            if ($content_type == 'materi') {
                $mysqli->query("INSERT INTO materi_details (content_id, body_html) VALUES ($content_id, '')");
                header("location: ../editor_materi.php?content_id=$content_id");
            } elseif ($content_type == 'quiz' || $content_type == 'ujian') {
                $mysqli->query("INSERT INTO quizzes (content_id, description) VALUES ($content_id, '')");
                header("location: ../editor_kuis.php?content_id=$content_id");
            }
            exit();
        }
    }
}
?>
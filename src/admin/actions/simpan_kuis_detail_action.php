<?php
// File: admin/actions/simpan_kuis_detail_action.php (File BARU)

require_once "../../db.php";
// ... (Cek sesi admin) ...
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content_id = $_POST['content_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Update judul di course_contents
    $sql_title = "UPDATE course_contents SET title = ? WHERE id = ?";
    if($stmt_title = $mysqli->prepare($sql_title)){
        $stmt_title->bind_param("si", $title, $content_id);
        $stmt_title->execute();
        $stmt_title->close();
    }

    // Update deskripsi di quizzes
    $sql_desc = "UPDATE quizzes SET description = ? WHERE content_id = ?";
    if($stmt_desc = $mysqli->prepare($sql_desc)){
        $stmt_desc->bind_param("si", $description, $content_id);
        $stmt_desc->execute();
        $stmt_desc->close();
    }
    
    header("location: ../editor_kuis.php?content_id=" . $content_id);
    exit();
}
?>
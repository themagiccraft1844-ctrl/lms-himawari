<?php
// File: admin/actions/hapus_soal_action.php (File BARU)

require_once "../../db.php";
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if(isset($_GET['id']) && !empty(trim($_GET['id']))){
    $question_id = (int)$_GET['id'];
    $content_id = (int)$_GET['content_id'];

    // ON DELETE CASCADE akan menghapus opsi jawaban terkait secara otomatis
    $sql = "DELETE FROM quiz_questions WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();
    }
    $mysqli->close();
    header("location: ../editor_kuis.php?content_id=" . $content_id);
    exit();
}
?>

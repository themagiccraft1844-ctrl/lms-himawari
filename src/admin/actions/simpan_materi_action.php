<?php
// File: admin/actions/simpan_materi_action.php (File BARU)

require_once "../../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content_id = $_POST['content_id'];
    $course_id = $_POST['course_id'];
    $body_html = $_POST['body_html']; // TinyMCE akan membersihkan HTML

    if (!empty($content_id) && !empty($course_id)) {
        $sql = "UPDATE materi_details SET body_html = ? WHERE content_id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $body_html, $content_id);
            if ($stmt->execute()) {
                // Redirect kembali ke halaman kelola kursus
                header("location: ../kelola_kursus.php?id=" . $course_id);
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>
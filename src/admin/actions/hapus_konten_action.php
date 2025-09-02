<?php
// File: admin/actions/hapus_konten_action.php

require_once "../../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

// Validasi ID konten dan ID kursus dari URL
if(isset($_GET['id']) && !empty(trim($_GET['id'])) && isset($_GET['course_id']) && !empty(trim($_GET['course_id']))){
    
    $content_id = (int)$_GET['id'];
    $course_id = (int)$_GET['course_id'];

    // SQL untuk menghapus konten dari tabel utama
    // Karena kita menggunakan ON DELETE CASCADE di database,
    // semua data terkait di tabel materi_details, quizzes, quiz_questions, dll.
    // akan otomatis terhapus juga.
    $sql = "DELETE FROM course_contents WHERE id = ?";
    
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $content_id);
        
        if($stmt->execute()){
            // Jika berhasil, kembali ke halaman kelola kursus
            header("location: ../kelola_kursus.php?id=" . $course_id);
            exit();
        } else{
            echo "Oops! Terjadi kesalahan. Silakan coba lagi.";
        }
        $stmt->close();
    }
    $mysqli->close();
    
} else {
    // Jika ID tidak valid, kembali ke dashboard admin
    header("location: ../index.php");
    exit();
}
?>

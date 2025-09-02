<?php
// File: admin/actions/hapus_kursus_action.php

require_once "../../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if(isset($_GET['id']) && !empty($_GET['id'])){
    $id = trim($_GET['id']);
    $sql = "DELETE FROM courses WHERE id = ?";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            header("location: ../index.php");
            exit();
        } else {
            echo "Oops! Terjadi kesalahan.";
        }
        $stmt->close();
    }
    $mysqli->close();
} else {
    header("location: ../index.php");
    exit();
}
?>

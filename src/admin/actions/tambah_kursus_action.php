<?php
// File: admin/actions/tambah_kursus_action.php

require_once "../../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $banner_url = trim($_POST['banner_image_url']);

    if (!empty($title) && !empty($description)) {
        $sql = "INSERT INTO courses (title, description, banner_image_url) VALUES (?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sss", $title, $description, $banner_url);
            if ($stmt->execute()) {
                header("location: ../index.php");
                exit();
            }
        }
    }
}
?>
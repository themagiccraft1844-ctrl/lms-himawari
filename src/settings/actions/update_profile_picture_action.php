<?php
// File: src/actions/update_profile_picture_action.php (BARU)
session_start();
require_once "../../db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $user_id = $_SESSION['id'];
        $allowed = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["profile_picture"]["name"];
        $filetype = $_FILES["profile_picture"]["type"];
        $filesize = $_FILES["profile_picture"]["size"];

        // Verifikasi ekstensi file
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $_SESSION['error_message'] = "Error: Format file tidak valid.";
            header("location: ../../settings.php?page=profil");
            exit;
        }

        // Verifikasi ukuran file - 5MB maksimum
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $_SESSION['error_message'] = "Error: Ukuran file lebih besar dari 5MB.";
            header("location: ../../settings.php?page=profil");
            exit;
        }

        // Verifikasi tipe MIME
        if (in_array($filetype, $allowed)) {
            // Buat direktori jika belum ada
            $upload_dir = "../../uploads/avatars/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Buat nama file baru yang unik
            $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
            $filepath = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $filepath)) {
                // Simpan path relatif ke database
                $relative_path = "uploads/avatars/" . $new_filename;
                $sql = "UPDATE users SET profile_picture_url = ? WHERE id = ?";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("si", $relative_path, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['success_message'] = "Foto profil berhasil diperbarui.";
                }
            } else {
                $_SESSION['error_message'] = "Gagal memindahkan file yang diunggah.";
            }
        } else {
            $_SESSION['error_message'] = "Error: Terjadi masalah dengan tipe file.";
        }
    } else {
        $_SESSION['error_message'] = "Error: " . $_FILES["profile_picture"]["error"];
    }
}

$mysqli->close();
header("location: ../../settings.php?page=profil");
exit;
?>

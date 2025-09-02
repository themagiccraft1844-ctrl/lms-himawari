<?php
// File: admin/actions/ganti_password_action.php (KODE LENGKAP)

require_once "../../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi password baru
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Silakan masukkan password baru.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password minimal harus 6 karakter.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validasi konfirmasi password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Silakan konfirmasi password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password tidak cocok.";
        }
    }

    // Jika tidak ada error, update password di database
    if (empty($new_password_err) && empty($confirm_password_err)) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $param_password, $param_id);
            
            // Set parameter
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            if ($stmt->execute()) {
                // Password berhasil diupdate, arahkan ke dashboard admin
                $_SESSION['success_msg'] = "Password Anda telah berhasil diperbarui.";
                header("location: ../index.php");
                exit();
            } else {
                echo "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    } else {
        // Jika ada error, simpan error di session dan redirect kembali ke form
        $_SESSION['new_password_err'] = $new_password_err;
        $_SESSION['confirm_password_err'] = $confirm_password_err;
        header("location: ../ganti_password.php");
        exit();
    }
    $mysqli->close();
}
?>
<?php
// File: src/actions/ganti_password_action.php (BARU)

require_once "../db.php";

// Cek jika user belum login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../index.php");
    exit;
}

// Hanya user yang bisa mengganti passwordnya sendiri via form ini
if($_SESSION["role"] !== 'user'){
    header("location: ../dashboard.php");
    exit;
}

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

    // Jika ada error, kembali ke halaman profil dengan pesan
    if (!empty($new_password_err) || !empty($confirm_password_err)) {
        $_SESSION['password_error'] = true;
        $_SESSION['new_password_err'] = $new_password_err;
        $_SESSION['confirm_password_err'] = $confirm_password_err;
        header("location: ../settings/profil.php");
        exit();
    }

    // Jika tidak ada error, update password di database
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("si", $param_password, $param_id);
        
        $param_password = password_hash($new_password, PASSWORD_DEFAULT);
        $param_id = $_SESSION["id"];
        
        if ($stmt->execute()) {
            $_SESSION['password_success'] = true;
            header("location: ../settings/profil.php");
            exit();
        } else {
            // Sebaiknya berikan pesan error yang lebih umum di production
            $_SESSION['password_error'] = true;
            $_SESSION['confirm_password_err'] = "Oops! Terjadi kesalahan. Silakan coba lagi nanti.";
             header("location: ../settings/profil.php");
            exit();
        }
        $stmt->close();
    }
    $mysqli->close();
} else {
    // Jika bukan metode POST, redirect saja
    header("location: ../settings/profil.php");
    exit();
}
?>

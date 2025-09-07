<?php
// File: src/settings/actions/update_display_settings_action.php
session_start();
require_once "../../db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];

    // Validasi input untuk keamanan
    $allowed_modes = ['light', 'dark', 'system'];
    $allowed_colors = ['blue', 'green', 'indigo', 'orange', 'rose'];

    $theme_mode = in_array($_POST['theme_mode'], $allowed_modes) ? $_POST['theme_mode'] : 'system';
    $accent_color = in_array($_POST['accent_color'], $allowed_colors) ? $_POST['accent_color'] : 'blue';

    // Gunakan query INSERT ... ON DUPLICATE KEY UPDATE.
    // Ini akan membuat baris baru jika belum ada, atau memperbarui baris yang ada jika user sudah punya pengaturan.
    $stmt = $mysqli->prepare("
        INSERT INTO user_display_settings (user_id, theme_mode, accent_color) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE
        theme_mode = VALUES(theme_mode), accent_color = VALUES(accent_color)
    ");
    
    $stmt->bind_param("iss", $user_id, $theme_mode, $accent_color);

    if ($stmt->execute()) {
        $_SESSION['display_settings_success'] = 'Pengaturan tampilan berhasil disimpan.';
    } else {
        // Handle error jika diperlukan
    }
    
    $stmt->close();
    // Redirect kembali ke halaman tampilan dengan pesan sukses
    header("location: ../../settings.php?page=tampilan");
    exit;
}
?>


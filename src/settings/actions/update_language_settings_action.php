<?php
// File: src/settings/actions/update_language_settings_action.php (DIPERBARUI)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once "../../db.php";

// Cek jika user belum login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['id'];
    $selected_lang = $_POST['language'] ?? 'id';

    // Validasi input
    $allowed_langs = ['id', 'en'];
    if (!in_array($selected_lang, $allowed_langs)) {
        $selected_lang = 'id'; // Default ke 'id' jika tidak valid
    }

    // Gunakan ON DUPLICATE KEY UPDATE untuk menyisipkan atau memperbarui
    $stmt = $mysqli->prepare("
        INSERT INTO user_display_settings (user_id, language) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE language = VALUES(language)
    ");
    
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $selected_lang);
        $stmt->execute();
        $stmt->close();
    }

    // Perbarui sesi secara langsung agar perubahan segera terlihat
    $_SESSION['language'] = $selected_lang;

    // FIX: Redirect kembali ke wrapper settings.php dengan parameter yang benar
    header("Location: ../../settings.php?page=bahasa&success=1");
    exit;
} else {
    // Jika bukan metode POST, kembalikan ke halaman pengaturan
    header("Location: ../../settings.php?page=bahasa");
    exit;
}

?>
<?php
// File: src/switch_view.php
// File ini HANYA untuk admin, berfungsi untuk mengubah mode tampilan mereka.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan hanya admin yang sudah login yang bisa mengakses file ini.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    
    // Path dasar untuk redirect yang konsisten
    $base_path = './'; 

    // Cek mode saat ini, default ke 'user' jika tidak ada
    $current_mode = $_SESSION['view_mode'] ?? 'user';

    if ($current_mode === 'user') {
        // Jika sedang dalam mode user, alihkan ke mode admin
        $_SESSION['view_mode'] = 'admin';
        header("Location: " . $base_path . "admin/index.php");
    } else {
        // Jika sedang dalam mode admin, alihkan ke mode user
        $_SESSION['view_mode'] = 'user';
        header("Location: " . $base_path . "dashboard.php");
    }
    exit;

} else {
    // Jika bukan admin yang mencoba mengakses, tendang kembali ke halaman login utama.
    header("Location: /index.php");
    exit;
}
?>

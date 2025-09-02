<?php
// File: db.php
// Konfigurasi dan koneksi ke database.

// Mulai session di setiap halaman yang membutuhkan akses login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- SESUAIKAN DENGAN PENGATURAN DATABASE ANDA ---
define('DB_SERVER', 'localhost'); // Ganti dengan server database Anda
define('DB_USERNAME', 'root'); // Ganti dengan username database Anda
define('DB_PASSWORD', '');     // Ganti dengan password database Anda
define('DB_NAME', 'db_quiz_lms'); // Ganti dengan nama database Anda
// ------------------------------------------------

// Membuat koneksi ke database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi (Style Prosedural)
if (mysqli_connect_errno()) {
    die("ERROR: Tidak dapat terhubung ke database. " . mysqli_connect_error());
}
?>

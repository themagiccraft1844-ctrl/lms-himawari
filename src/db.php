<?php
// File: db.php
// Konfigurasi dan koneksi ke database.

// Mulai session di setiap halaman yang membutuhkan akses login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- SESUAIKAN DENGAN PENGATURAN DATABASE ANDA ---
<<<<<<< HEAD
define('DB_SERVER', 'mysql_db');
define('DB_USERNAME', 'admin_hima'); // Ganti dengan username database Anda
define('DB_PASSWORD', 'Him@war128');     // Ganti dengan password database Anda
=======
define('DB_SERVER', 'localhost'); // Ganti dengan server database Anda
define('DB_USERNAME', 'root'); // Ganti dengan username database Anda
define('DB_PASSWORD', '');     // Ganti dengan password database Anda
>>>>>>> a806d5a669174029b2dcdd5a5578b4d94d3e6805
define('DB_NAME', 'db_quiz_lms'); // Ganti dengan nama database Anda
// ------------------------------------------------

// Membuat koneksi ke database
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi (Style Prosedural)
if (mysqli_connect_errno()) {
    die("ERROR: Tidak dapat terhubung ke database. " . mysqli_connect_error());
}
?>

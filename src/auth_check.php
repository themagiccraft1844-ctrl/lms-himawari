<?php
/**
 * File: auth_check.php
 * Berfungsi sebagai pemeriksa sesi universal yang bisa dipanggil di halaman mana pun.
 * File ini mengharapkan variabel $required_role sudah di-set sebelumnya.
 * Nilai yang mungkin untuk $required_role: 'admin' atau 'user'.
 */

// Selalu mulai sesi dengan cara yang aman
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- PERBAIKAN: Definisikan base path untuk redirect yang konsisten ---
// Menentukan path absolut dari root dokumen web ke folder 'src'
// Ini membuat redirect berfungsi dari mana saja (misal: dari /src/ atau /src/admin/)
$base_path = '/'; // Sesuaikan jika aplikasi Anda ada di subfolder, misal: '/lms-project/'

// Cek apakah variabel $required_role sudah didefinisikan
if (!isset($required_role)) {
    die("Error: Tipe halaman belum ditentukan untuk pemeriksaan autentikasi.");
}

// --- Logika untuk Halaman ADMIN ---
if ($required_role === 'admin') {
    // Pengguna harus login DAN rolenya harus 'admin'
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
        // Arahkan ke login admin jika tidak memenuhi syarat
        header("location: " . $base_path . "admin_login.php");
        exit;
    }
}

// --- Logika untuk Halaman USER ---
elseif ($required_role === 'user') {
    // Pengguna CUKUP hanya dengan login. Admin otomatis boleh masuk.
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        // Arahkan ke login user jika tidak ada sesi sama sekali
        header("location: " . $base_path . "index.php");
        exit;
    }
}

// Jika lolos dari semua pemeriksaan, skrip akan lanjut ke sisa kode di halaman yang memanggil.
?>


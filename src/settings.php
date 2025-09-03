<?php
// File: src/settings.php (MODIFIKASI)
// File ini sekarang hanya berfungsi untuk mengarahkan pengguna
// ke halaman pengaturan default yaitu profil.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek jika user belum login, arahkan ke halaman login utama
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

// Arahkan pengguna ke halaman profil di dalam folder settings
header("location: settings/profil.php");
exit;
?>

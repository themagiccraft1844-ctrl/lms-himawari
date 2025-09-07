<?php
// File: src/language_loader.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variabel global untuk menampung semua teks terjemahan
$GLOBALS['LANG'] = [];

/**
 * Fungsi untuk memuat file bahasa yang benar berdasarkan pengaturan pengguna.
 * @param mysqli $db_connection Objek koneksi database mysqli.
 */
function load_language($db_connection) {
    $default_lang = 'id';
    $selected_lang = $default_lang;

    // 1. Cek sesi PHP terlebih dahulu untuk performa
    if (isset($_SESSION['language'])) {
        $selected_lang = $_SESSION['language'];
    } 
    // 2. Jika tidak ada di sesi, cek database
    elseif (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];
        $stmt = $db_connection->prepare("SELECT language FROM user_display_settings WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $selected_lang = !empty($row['language']) ? $row['language'] : $default_lang;
            }
            $stmt->close();
        }
        // Simpan ke sesi untuk request berikutnya
        $_SESSION['language'] = $selected_lang;
    }

    // Tentukan path file bahasa
    $lang_file_path = __DIR__ . '/lang/' . $selected_lang . '.php';

    // Muat file terjemahan jika ada, jika tidak, muat bahasa default
    if (file_exists($lang_file_path)) {
        require $lang_file_path;
    } else {
        require __DIR__ . '/lang/' . $default_lang . '.php';
    }

    // Masukkan array terjemahan ke variabel global
    if (isset($translations)) {
        $GLOBALS['LANG'] = $translations;
    }
}

/**
 * Fungsi helper untuk mendapatkan string terjemahan.
 * @param string $key Kunci dari string yang ingin diterjemahkan.
 * @return string String yang sudah diterjemahkan atau kunci itu sendiri jika tidak ditemukan.
 */
function lang($key) {
    return $GLOBALS['LANG'][$key] ?? $key;
}

// Panggil fungsi load_language jika koneksi $mysqli ada
if (isset($mysqli) && $mysqli instanceof mysqli) {
    load_language($mysqli);
}

?>

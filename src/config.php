<?php
// config.php
// Harap simpan API Key Anda di sini.
// Pastikan file ini tidak dapat diakses secara publik.

define('GEMINI_API_KEY', 'AIzaSyA7Z-0UBcPMQSpYXg8eIxbkZOGXI-y09Lk');

// --- PENGATURAN BARU UNTUK USER CHAT AI ---
define('DEFAULT_USER_GEMINI_API_KEY', 'AIzaSyA7Z-0UBcPMQSpYXg8eIxbkZOGXI-y09Lk'); // <-- Ganti dengan API Key Gemini Anda yang akan jadi default untuk user.
define('DEFAULT_API_CHAR_LIMIT', 10000); // Batas 10,000 karakter per hari untuk pengguna dengan API Key default.
// -----------------------------------------
// Jumlah karakter dalam riwayat sebelum sistem membuat ringkasan otomatis.
// Nilai yang baik adalah sekitar 8000-12000 untuk menjaga agar tidak melebihi batas token model.
define('CONTEXT_SUMMARY_TRIGGER_CHARS', 8000);

// --- PENGATURAN BARU UNTUK PENGIRIMAN EMAIL (SMTP) ---
// Ganti dengan detail akun email Anda.
// PENTING: Untuk Gmail, gunakan "App Password", bukan password login biasa.
// Lihat cara membuat App Password di: https://support.google.com/accounts/answer/185833
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'themagiccraft1844@gmail.com'); // Ganti dengan email Gmail Anda
define('SMTP_PASSWORD', 'yvbateymkqfsznsw'); // Ganti dengan App Password Anda
define('SMTP_PORT', 587); // Port untuk TLS
define('SMTP_SECURE', 'tls'); // Gunakan 'tls' atau 'ssl'
define('SMTP_FROM_EMAIL', 'themagiccraft1844@gmail.com'); // Email pengirim
define('SMTP_FROM_NAME', 'LMS Pro-Himawari'); // Nama pengirim
?>

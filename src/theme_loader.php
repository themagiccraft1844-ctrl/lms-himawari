<?php
// File: src/theme_loader.php (File BARU)
// File ini akan dipanggil di dalam <head> setiap halaman utama.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default jika tidak ada pengaturan atau tidak login
$theme_mode = 'system';
$accent_color = 'blue';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Gunakan koneksi $mysqli yang sudah ada jika file ini di-include setelah db.php
    global $mysqli; 
    if (!isset($mysqli)) {
        // Buat koneksi baru jika belum ada (kurang ideal, tapi sebagai fallback)
        require_once "db.php";
    }

    $user_id = $_SESSION['id'];
    $stmt = $mysqli->prepare("SELECT theme_mode, accent_color FROM user_display_settings WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($settings) {
        $theme_mode = $settings['theme_mode'];
        $accent_color = $settings['accent_color'];
    }
}

// Definisikan preset warna
$color_presets = [
    'blue' => ['primary' => '#4a90e2', 'light' => '#e9f2fe'],
    'green' => ['primary' => '#27ae60', 'light' => '#e4f6eb'],
    'indigo' => ['primary' => '#6c5ce7', 'light' => '#efedfc'],
    'orange' => ['primary' => '#f39c12', 'light' => '#fef5e7'],
    'rose' => ['primary' => '#e74c3c', 'light' => '#fceae8']
];

$selected_preset = $color_presets[$accent_color] ?? $color_presets['blue'];

// Output CSS Variables ke halaman
echo "<style>:root { --primary-color: {$selected_preset['primary']}; --primary-light: {$selected_preset['light']}; }</style>";

// Output JavaScript untuk menangani tema sistem dan mode gelap/terang
echo "
<script>
    (function() {
        const theme = '{$theme_mode}';
        const root = document.documentElement;

        function applyTheme(isDark) {
            if (isDark) {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }
        }

        if (theme === 'dark') {
            applyTheme(true);
        } else if (theme === 'light') {
            applyTheme(false);
        } else { // System theme
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            applyTheme(mediaQuery.matches);
            mediaQuery.addEventListener('change', (e) => applyTheme(e.matches));
        }
    })();
</script>
";
?>

<?php
// File: src/settings/tampilan.php (Konten yang akan dimuat oleh settings.php)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { exit('Akses ditolak.'); }

$user_id = $_SESSION['id'];
$success_message = '';

// Tampilkan pesan sukses jika ada dari proses penyimpanan
if (isset($_SESSION['display_settings_success'])) {
    $success_message = $_SESSION['display_settings_success'];
    unset($_SESSION['display_settings_success']);
}

// Ambil pengaturan saat ini dari database untuk ditampilkan di form
$stmt = $mysqli->prepare("SELECT theme_mode, accent_color FROM user_display_settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

$current_mode = $settings['theme_mode'] ?? 'system';
$current_color = $settings['accent_color'] ?? 'blue';

// Definisikan preset warna yang tersedia
$color_presets = [
    'blue' => ['name' => 'Biru Standar', 'hex' => '#4a90e2'],
    'green' => ['name' => 'Hijau Segar', 'hex' => '#27ae60'],
    'indigo' => ['name' => 'Indigo Elegan', 'hex' => '#6c5ce7'],
    'orange' => ['name' => 'Oranye Ceria', 'hex' => '#f39c12'],
    'rose' => ['name' => 'Mawar Merah', 'hex' => '#e74c3c']
];
?>
<style>
    /* Style khusus untuk halaman ini */
    .theme-options, .color-options { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem; }
    .theme-option, .color-option { border: 2px solid var(--border-color); border-radius: 8px; padding: 1rem; cursor: pointer; text-align: center; flex: 1; min-width: 120px; transition: all 0.2s ease; }
    .theme-option i, .color-option .swatch { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
    .theme-option:hover, .color-option:hover { border-color: var(--primary-color); transform: translateY(-3px); }
    .theme-option.selected, .color-option.selected { border-color: var(--primary-color); background-color: var(--primary-light); color: var(--primary-color); }
    .color-option .swatch { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 0.5rem auto; border: 1px solid var(--border-color); }
    input[type="radio"] { display: none; }

    /* Perbaikan untuk Tombol Simpan agar selalu terlihat */
    .save-settings-btn {
        display: block;
        width: 100%;
        padding: 14px 28px;
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        border: none;
        background-color: var(--primary-color) !important; /* Gunakan !important untuk memastikan override */
        color: #ffffff !important;
    }
    .save-settings-btn:hover {
        transform: translateY(-2px);
        background-color: color-mix(in srgb, var(--primary-color) 85%, black) !important;
    }
</style>

<?php if ($success_message): ?>
    <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<!-- Form ini akan mengirim data ke file action untuk disimpan -->
<form action="settings/actions/update_display_settings_action.php" method="POST">
    <div class="form-container" style="margin-bottom: 2rem;">
        <h3><i class="fas fa-palette" style="margin-right: 10px;"></i>Mode Tampilan</h3>
        <p>Pilih mode terang, gelap, atau biarkan mengikuti pengaturan sistem operasi Anda.</p>
        <div class="theme-options">
            <label class="theme-option <?php echo ($current_mode == 'light') ? 'selected' : ''; ?>">
                <input type="radio" name="theme_mode" value="light" <?php echo ($current_mode == 'light') ? 'checked' : ''; ?>>
                <i class="fas fa-sun"></i>
                <span>Terang</span>
            </label>
            <label class="theme-option <?php echo ($current_mode == 'dark') ? 'selected' : ''; ?>">
                <input type="radio" name="theme_mode" value="dark" <?php echo ($current_mode == 'dark') ? 'checked' : ''; ?>>
                <i class="fas fa-moon"></i>
                <span>Gelap</span>
            </label>
            <label class="theme-option <?php echo ($current_mode == 'system') ? 'selected' : ''; ?>">
                <input type="radio" name="theme_mode" value="system" <?php echo ($current_mode == 'system') ? 'checked' : ''; ?>>
                <i class="fas fa-desktop"></i>
                <span>Sistem</span>
            </label>
        </div>
    </div>

    <div class="form-container">
        <h3><i class="fas fa-tint" style="margin-right: 10px;"></i>Warna Aksen</h3>
        <p>Pilih warna utama yang akan digunakan di seluruh antarmuka.</p>
        <div class="color-options">
            <?php foreach ($color_presets as $key => $color): ?>
            <label class="color-option <?php echo ($current_color == $key) ? 'selected' : ''; ?>">
                <input type="radio" name="accent_color" value="<?php echo $key; ?>" <?php echo ($current_color == $key) ? 'checked' : ''; ?>>
                <div class="swatch" style="background-color: <?php echo $color['hex']; ?>;"></div>
                <span><?php echo $color['name']; ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Tombol Simpan yang akan mengirimkan form -->
    <div class="form-group" style="margin-top: 2rem;">
        <input type="submit" class="btn save-settings-btn" value="Simpan Pengaturan Tampilan">
    </div>
</form>

<script>
    // JavaScript untuk membuat pilihan lebih interaktif secara visual
    document.querySelectorAll('.theme-option, .color-option').forEach(label => {
        label.addEventListener('click', function() {
            // Hapus kelas 'selected' dari semua opsi dalam grup yang sama
            const radioGroup = this.parentElement.querySelectorAll('label');
            radioGroup.forEach(item => item.classList.remove('selected'));
            // Tambahkan kelas 'selected' ke yang baru diklik
            this.classList.add('selected');
        });
    });
</script>


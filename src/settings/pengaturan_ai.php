<?php
// File: src/settings/pengaturan_ai.php
// Konten ini akan dimuat oleh file settings.php sebagai halaman utama.

// Pastikan sesi sudah berjalan dan pengguna sudah login (diasumsikan sudah dicek oleh settings.php)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Seharusnya tidak pernah terjadi jika diakses melalui settings.php
    exit('Akses ditolak.');
}

// Ambil data user yang sedang login
$user_id = $_SESSION['id'];
$success_message = '';

// Cek apakah ada pesan sukses dari aksi update
if (isset($_SESSION['ai_settings_success'])) {
    $success_message = $_SESSION['ai_settings_success'];
    unset($_SESSION['ai_settings_success']);
}

// Ambil pengaturan AI saat ini dari database
$stmt = $mysqli->prepare("SELECT * FROM user_ai_settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_settings = $stmt->get_result()->fetch_assoc() ?? [];
$stmt->close();

// Fungsi helper untuk mendapatkan nilai setting dengan aman
function get_setting($settings, $key, $default) {
    return isset($settings[$key]) && !empty($settings[$key]) ? $settings[$key] : $default;
}
?>

<!-- KONTEN HALAMAN PENGATURAN AI -->
<?php if ($success_message): ?>
    <div class="alert alert-success" style="background-color: #d4edda; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<form action="settings/actions/update_ai_settings_action.php" method="POST">
    <!-- BAGIAN PENGATURAN CHAT AI -->
    <div class="form-container mb-6">
        <h3><i class="fas fa-comments" style="margin-right: 10px;"></i>AI Chat</h3>
        <p>Pilih provider dan model yang ingin Anda gunakan untuk percakapan.</p>
        <div class="form-group">
            <label for="chat_provider">Provider Chat</label>
            <select name="chat_provider" id="chat_provider" class="form-control provider-select">
                <option value="gemini" <?php echo (get_setting($current_settings, 'chat_provider', 'gemini') == 'gemini') ? 'selected' : ''; ?>>Google Gemini</option>
                <option value="openai" <?php echo (get_setting($current_settings, 'chat_provider', '') == 'openai') ? 'selected' : ''; ?>>OpenAI</option>
                <option value="openrouter" <?php echo (get_setting($current_settings, 'chat_provider', '') == 'openrouter') ? 'selected' : ''; ?>>OpenRouter</option>
            </select>
        </div>
        <div class="form-group model-selector-container" id="chat_model_selector">
             <div class="model-group" data-provider="gemini"><label for="chat_model_gemini">Model Gemini</label><select name="chat_model_gemini" class="form-control"><option value="gemini-1.5-flash-latest" <?php echo (get_setting($current_settings, 'chat_model', 'gemini-1.5-flash-latest') == 'gemini-1.5-flash-latest') ? 'selected' : ''; ?>>Gemini 1.5 Flash</option><option value="gemini-1.5-pro-latest" <?php echo (get_setting($current_settings, 'chat_model', '') == 'gemini-1.5-pro-latest') ? 'selected' : ''; ?>>Gemini 1.5 Pro</option></select></div>
             <div class="model-group" data-provider="openai"><label for="chat_model_openai">Model OpenAI</label><select name="chat_model_openai" class="form-control"><option value="gpt-4o-mini" <?php echo (get_setting($current_settings, 'chat_model', 'gpt-4o-mini') == 'gpt-4o-mini') ? 'selected' : ''; ?>>GPT-4o Mini</option><option value="gpt-4o" <?php echo (get_setting($current_settings, 'chat_model', '') == 'gpt-4o') ? 'selected' : ''; ?>>GPT-4o</option><option value="gpt-3.5-turbo" <?php echo (get_setting($current_settings, 'chat_model', '') == 'gpt-3.5-turbo') ? 'selected' : ''; ?>>GPT-3.5 Turbo</option></select></div>
             <div class="model-group" data-provider="openrouter"><label for="chat_model_openrouter">Model OpenRouter</label><input type="text" name="chat_model_openrouter" class="form-control" placeholder="Contoh: google/gemini-flash-1.5" value="<?php echo htmlspecialchars(get_setting($current_settings, 'chat_provider', '') == 'openrouter' ? get_setting($current_settings, 'chat_model', '') : ''); ?>"><small class="form-text" style="color: #6c757d; margin-top: 5px;">Isi dengan ID model spesifik dari OpenRouter.</small></div>
        </div>
    </div>

    <!-- BAGIAN PENGATURAN ANALISIS FILE -->
    <div class="form-container mb-6">
        <h3><i class="fas fa-file-alt" style="margin-right: 10px;"></i>Analisis File & Gambar</h3>
        <p>Pilih model untuk menganalisis dokumen (PDF, TXT) dan gambar.</p>
        <div class="form-group">
            <label for="file_provider">Provider Analisis</label>
            <select name="file_provider" id="file_provider" class="form-control provider-select">
                <option value="gemini" <?php echo (get_setting($current_settings, 'file_provider', 'gemini') == 'gemini') ? 'selected' : ''; ?>>Google Gemini</option>
                <option value="openai" <?php echo (get_setting($current_settings, 'file_provider', '') == 'openai') ? 'selected' : ''; ?>>OpenAI</option>
                <option value="openrouter" <?php echo (get_setting($current_settings, 'file_provider', '') == 'openrouter') ? 'selected' : ''; ?>>OpenRouter</option>
            </select>
        </div>
         <div class="form-group model-selector-container" id="file_model_selector">
             <div class="model-group" data-provider="gemini"><label>Model Gemini</label><select name="file_model_gemini" class="form-control"><option value="gemini-1.5-pro-latest" <?php echo (get_setting($current_settings, 'file_model', 'gemini-1.5-pro-latest') == 'gemini-1.5-pro-latest') ? 'selected' : ''; ?>>Gemini 1.5 Pro (Vision)</option></select></div>
             <div class="model-group" data-provider="openai"><label>Model OpenAI</label><select name="file_model_openai" class="form-control"><option value="gpt-4o" <?php echo (get_setting($current_settings, 'file_model', 'gpt-4o') == 'gpt-4o') ? 'selected' : ''; ?>>GPT-4o (Vision)</option><option value="gpt-4-turbo" <?php echo (get_setting($current_settings, 'file_model', '') == 'gpt-4-turbo') ? 'selected' : ''; ?>>GPT-4 Turbo (Vision)</option></select></div>
             <div class="model-group" data-provider="openrouter"><label>Model OpenRouter</label><input type="text" name="file_model_openrouter" class="form-control" placeholder="Contoh: openai/gpt-4o" value="<?php echo htmlspecialchars(get_setting($current_settings, 'file_provider', '') == 'openrouter' ? get_setting($current_settings, 'file_model', '') : ''); ?>"></div>
        </div>
    </div>

     <!-- BAGIAN PENGATURAN GENERATE GAMBAR -->
     <div class="form-container mb-6">
        <h3><i class="fas fa-image" style="margin-right: 10px;"></i>Generate Gambar</h3>
        <p>Pilih model untuk membuat gambar dari teks.</p>
         <div class="form-group">
            <label for="image_provider">Provider Generate Gambar</label>
            <select name="image_provider" id="image_provider" class="form-control provider-select">
                <option value="openai" <?php echo (get_setting($current_settings, 'image_provider', 'openai') == 'openai') ? 'selected' : ''; ?>>OpenAI (DALL-E)</option>
                <option value="openrouter" <?php echo (get_setting($current_settings, 'image_provider', '') == 'openrouter') ? 'selected' : ''; ?>>OpenRouter</option>
                <option value="gemini" <?php echo (get_setting($current_settings, 'image_provider', '') == 'gemini') ? 'selected' : ''; ?> disabled>Google Gemini (Segera Hadir)</option>
            </select>
        </div>
        <div class="form-group model-selector-container" id="image_model_selector">
             <div class="model-group" data-provider="openai"><label>Model OpenAI</label><select name="image_model_openai" class="form-control"><option value="dall-e-3" <?php echo (get_setting($current_settings, 'image_model', 'dall-e-3') == 'dall-e-3') ? 'selected' : ''; ?>>DALL-E 3</option></select></div>
             <div class="model-group" data-provider="openrouter"><label>Model OpenRouter</label><input type="text" name="image_model_openrouter" class="form-control" placeholder="Contoh: openai/dall-e-3" value="<?php echo htmlspecialchars(get_setting($current_settings, 'image_provider', '') == 'openrouter' ? get_setting($current_settings, 'image_model', '') : ''); ?>"></div>
        </div>
    </div>

    <!-- BAGIAN MANAJEMEN API KEY (DINAMIS) -->
    <div class="form-container">
        <h3><i class="fas fa-key" style="margin-right: 10px;"></i>Manajemen API Key</h3>
        <p>Masukkan API Key pribadi Anda untuk provider yang dipilih di salah satu fitur di atas.</p>
        <div id="gemini-key-container" class="api-key-group"><div class="form-group"><label for="gemini_api_key">Google Gemini API Key</label><input type="password" name="gemini_api_key" class="form-control" placeholder="Kosongkan untuk kunci default (chat saja)" value="<?php echo htmlspecialchars(get_setting($current_settings, 'gemini_api_key', '')); ?>"></div></div>
        <div id="openai-key-container" class="api-key-group"><div class="form-group"><label for="openai_api_key">OpenAI API Key</label><input type="password" name="openai_api_key" class="form-control" placeholder="Wajib diisi jika memilih OpenAI" value="<?php echo htmlspecialchars(get_setting($current_settings, 'openai_api_key', '')); ?>"></div></div>
        <div id="openrouter-key-container" class="api-key-group"><div class="form-group"><label for="openrouter_api_key">OpenRouter API Key</label><input type="password" name="openrouter_api_key" class="form-control" placeholder="Wajib diisi jika memilih OpenRouter" value="<?php echo htmlspecialchars(get_setting($current_settings, 'openrouter_api_key', '')); ?>"></div></div>
    </div>

    <div class="form-group" style="margin-top: 2rem;"><input type="submit" class="btn" value="Simpan Semua Pengaturan"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk menampilkan grup model yang relevan berdasarkan provider yang dipilih
    function updateModelFields() {
        document.querySelectorAll('.provider-select').forEach(select => {
            const task = select.id.replace('_provider', ''); // e.g., 'chat', 'file', 'image'
            const selectedProvider = select.value;
            const container = document.getElementById(task + '_model_selector');
            if (!container) return;

            // Sembunyikan semua grup model untuk tugas ini
            container.querySelectorAll('.model-group').forEach(group => {
                const isVisible = group.dataset.provider === selectedProvider;
                group.style.display = isVisible ? 'block' : 'none';
                // Aktifkan/nonaktifkan input di dalamnya agar hanya data yang terlihat yang dikirim
                group.querySelectorAll('input, select').forEach(input => input.disabled = !isVisible);
            });
        });
    }

    // Fungsi untuk menampilkan field API Key hanya jika provider-nya dipilih
    function updateApiKeyVisibility() {
        const selectedProviders = new Set();
        // Kumpulkan semua provider yang dipilih dari semua dropdown
        document.querySelectorAll('.provider-select').forEach(select => {
            selectedProviders.add(select.value);
        });

        // Tampilkan atau sembunyikan setiap field API key
        document.querySelectorAll('.api-key-group').forEach(group => {
            const provider = group.id.replace('-key-container', '');
            if (selectedProviders.has(provider)) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    }

    // Tambahkan event listener ke semua dropdown provider
    document.querySelectorAll('.provider-select').forEach(select => {
        select.addEventListener('change', () => {
            updateModelFields();
            updateApiKeyVisibility();
        });
    });

    // Jalankan fungsi saat halaman pertama kali dimuat
    updateModelFields();
    updateApiKeyVisibility();
});
</script>


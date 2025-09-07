<?php
// File: src/settings/actions/update_ai_settings_action.php (DIPERBARUI TOTAL)

session_start();
require_once "../../db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'];

    // Ambil data dari form untuk setiap tugas
    $chat_provider = $_POST['chat_provider'] ?? 'gemini';
    // Logika untuk mengambil model yang benar berdasarkan provider yang dipilih
    $chat_model = '';
    if ($chat_provider === 'gemini') {
        $chat_model = $_POST['chat_model_gemini'];
    } elseif ($chat_provider === 'openai') {
        $chat_model = $_POST['chat_model_openai'];
    } elseif ($chat_provider === 'openrouter') {
        $chat_model = $_POST['chat_model_openrouter'];
    }

    // Ambil data API Keys
    $gemini_api_key = trim($_POST['gemini_api_key']);
    $openai_api_key = trim($_POST['openai_api_key']);
    $openrouter_api_key = trim($_POST['openrouter_api_key']);
    
    // Untuk fitur masa depan (analisis file & gambar), Anda bisa menambahkan logikanya di sini
    // Untuk saat ini, kita set default saja
    $file_provider = 'gemini';
    $file_model = 'gemini-1.5-flash-latest';
    $image_provider = 'openai';
    $image_model = 'dall-e-3';

    $sql = "INSERT INTO user_ai_settings (
                user_id, 
                chat_provider, chat_model,
                file_provider, file_model,
                image_provider, image_model,
                gemini_api_key, openai_api_key, openrouter_api_key
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                chat_provider = VALUES(chat_provider), chat_model = VALUES(chat_model),
                file_provider = VALUES(file_provider), file_model = VALUES(file_model),
                image_provider = VALUES(image_provider), image_model = VALUES(image_model),
                gemini_api_key = VALUES(gemini_api_key), 
                openai_api_key = VALUES(openai_api_key), 
                openrouter_api_key = VALUES(openrouter_api_key)";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isssssssss", 
            $user_id, 
            $chat_provider, $chat_model,
            $file_provider, $file_model,
            $image_provider, $image_model,
            $gemini_api_key, $openai_api_key, $openrouter_api_key
        );
        
        if ($stmt->execute()) {
            $_SESSION['ai_settings_success'] = "Pengaturan AI berhasil diperbarui.";
        } else {
            $_SESSION['ai_settings_error'] = "Gagal memperbarui pengaturan.";
        }
        $stmt->close();
    }
    $mysqli->close();
}

header("location: ../../settings.php?page=pengaturan_ai");
exit;
?>
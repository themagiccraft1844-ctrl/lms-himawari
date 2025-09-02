<?php
session_start();
require_once "../../db.php";
require_once "../../config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title']; 
    $prompt_manual = $_POST['prompt_manual'];
    $ref_url = $_POST['ref_url'];
    
    $combined_prompt = "Anda adalah asisten ahli dalam membuat materi pembelajaran online. ";
    $context = "";

    if (!empty($prompt_manual)) {
        $context .= "Berikut adalah instruksi dari pengguna: " . $prompt_manual . "\n\n";
    }

    if (!empty($ref_url) && filter_var($ref_url, FILTER_VALIDATE_URL)) {
        $url_content = strip_tags(@file_get_contents($ref_url));
        if ($url_content) {
            $context .= "Berikut adalah konten dari referensi URL (" . $ref_url . "):\n" . substr($url_content, 0, 5000) . "\n\n";
        }
    }

    if (isset($_FILES['ref_file']) && $_FILES['ref_file']['error'] == 0) {
        $file_type = $_FILES['ref_file']['type'];
        if ($file_type == 'text/plain') {
            $file_content = file_get_contents($_FILES['ref_file']['tmp_name']);
            $context .= "Berikut adalah konten dari file referensi:\n" . $file_content . "\n\n";
        }
    }
    
    if (empty($context)) {
        die("Error: Tidak ada prompt atau referensi yang diberikan.");
    }

    $combined_prompt .= "Tolong buatkan materi pembelajaran yang terstruktur dan mudah dipahami dalam format HTML berdasarkan konteks berikut:\n\n" . $context;
    $combined_prompt .= "\nGunakan tag HTML seperti <h1>, <h2>, <p>, <ul>, <li>, dan <strong> untuk menstrukturkan konten. Jangan sertakan tag <html>, <head>, atau <body>.";

    $api_key = GEMINI_API_KEY;
    // Menggunakan model flash terbaru
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key;
    
    $data = ['contents' => [['parts' => [['text' => $combined_prompt]]]]];
    $options = ['http' => ['header'  => "Content-type: application/json\r\n", 'method'  => 'POST', 'content' => json_encode($data), 'ignore_errors' => true]];
    $context_stream = stream_context_create($options);
    $response = file_get_contents($url, false, $context_stream);
    $result = json_decode($response, true);

    $generated_content = '';
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $generated_content = $result['candidates'][0]['content']['parts'][0]['text'];
    } else {
        $error_message = $result['error']['message'] ?? 'Gagal menghubungi Gemini API.';
        die("Error: " . htmlspecialchars($error_message));
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("INSERT INTO course_contents (course_id, title, content_type) VALUES (?, ?, 'materi')");
        $stmt->bind_param("is", $course_id, $title);
        $stmt->execute();
        $content_id = $stmt->insert_id;
        $stmt->close();

        // FIX: Menggunakan nama kolom 'content_id' yang benar sesuai gambar
        $stmt = $mysqli->prepare("INSERT INTO materi_details (content_id, body_html) VALUES (?, ?)");
        $stmt->bind_param("is", $content_id, $generated_content);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();

        // Arahkan ke editor materi yang sesuai
        header("Location: ../editor_materi.php?content_id=" . $content_id);
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        die("Database error: " . $e->getMessage());
    }
}

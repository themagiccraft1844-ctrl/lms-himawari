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
    $content_type = $_POST['content_type']; // 'quiz' atau 'ujian'
    $materi_ids = $_POST['materi_ids'] ?? [];
    
    $jumlah_pg = (int)($_POST['jumlah_pg'] ?? 0);
    $jumlah_tf = (int)($_POST['jumlah_tf'] ?? 0);
    $jumlah_esai = (int)($_POST['jumlah_esai'] ?? 0);

    if (empty($materi_ids)) {
        die("Error: Silakan pilih minimal satu materi sebagai referensi.");
    }
    if (($jumlah_pg + $jumlah_tf + $jumlah_esai) <= 0) {
        die("Error: Tentukan setidaknya satu soal untuk dibuat.");
    }

    $materi_konten = "";
    $ids_placeholder = implode(',', array_fill(0, count($materi_ids), '?'));
    $types = str_repeat('i', count($materi_ids));
    
    $stmt = $mysqli->prepare("SELECT md.body_html FROM materi_details md JOIN course_contents cc ON md.content_id = cc.id WHERE cc.id IN ($ids_placeholder)");
    $stmt->bind_param($types, ...$materi_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $materi_konten .= strip_tags($row['body_html']) . "\n\n";
    }
    $stmt->close();

    $prompt = "Anda adalah asisten pembuat soal. Berdasarkan materi berikut, buatkan saya soal-soal kuis dengan rincian sebagai berikut:\n";
    if ($jumlah_pg > 0) $prompt .= "- {$jumlah_pg} soal tipe Pilihan Ganda.\n";
    if ($jumlah_tf > 0) $prompt .= "- {$jumlah_tf} soal tipe Benar/Salah.\n";
    if ($jumlah_esai > 0) $prompt .= "- {$jumlah_esai} soal tipe Esai.\n\n";
    
    $prompt .= "Materi Referensi:\n" . substr($materi_konten, 0, 10000) . "\n\n";
    $prompt .= "Format output HARUS dalam bentuk JSON array tunggal yang berisi semua objek soal. ";
    $prompt .= "Setiap objek HARUS memiliki key 'tipe' yang nilainya 'multiple_choice', 'true_false', atau 'essay'.\n";
    $prompt .= "- Untuk 'multiple_choice', sertakan key: 'soal' (string), 'pilihan' (array 4 string), dan 'jawaban_benar' (string).\n";
    $prompt .= "- Untuk 'true_false', sertakan key: 'soal' (string, berupa pernyataan), dan 'jawaban_benar' (boolean, true atau false).\n";
    $prompt .= "- Untuk 'essay', sertakan key: 'soal' (string) dan 'kunci_jawaban' (string).\n";

    $api_key = GEMINI_API_KEY;
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $api_key;
    
    $data = ['contents' => [['parts' => [['text' => $prompt]]]]];
    $options = ['http' => ['header'  => "Content-type: application/json\r\n", 'method'  => 'POST', 'content' => json_encode($data), 'ignore_errors' => true]];
    $context_stream = stream_context_create($options);
    $response = file_get_contents($url, false, $context_stream);
    $result = json_decode($response, true);
    
    $generated_text = '';
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $generated_text = str_replace(['```json', '```'], '', $result['candidates'][0]['content']['parts'][0]['text']);
    } else {
        $error_message = $result['error']['message'] ?? 'Gagal menghubungi Gemini API.';
        die("Error: " . htmlspecialchars($error_message));
    }

    $soal_array = json_decode(trim($generated_text), true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($soal_array)) {
        die("Error: Gagal mem-parsing respons JSON dari AI. Respons mentah: <pre>" . htmlspecialchars($generated_text) . "</pre>");
    }

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("INSERT INTO course_contents (course_id, title, content_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $course_id, $title, $content_type);
        $stmt->execute();
        $content_id = $stmt->insert_id;
        $stmt->close();

        $deskripsi_kuis = "Kuis ini dibuat secara otomatis oleh AI.";
        $stmt = $mysqli->prepare("INSERT INTO quizzes (content_id, description) VALUES (?, ?)");
        $stmt->bind_param("is", $content_id, $deskripsi_kuis);
        $stmt->execute();
        $quiz_id = $stmt->insert_id;
        $stmt->close();

        $stmt_soal = $mysqli->prepare("INSERT INTO quiz_questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)");
        $stmt_pilihan = $mysqli->prepare("INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");

        foreach ($soal_array as $item_soal) {
            if (!isset($item_soal['soal']) || !isset($item_soal['tipe'])) continue;

            $pertanyaan = $item_soal['soal'];
            $question_type = $item_soal['tipe'];

            $stmt_soal->bind_param("iss", $quiz_id, $pertanyaan, $question_type);
            $stmt_soal->execute();
            $question_id = $stmt_soal->insert_id;

            if ($question_type == 'multiple_choice' && isset($item_soal['pilihan'])) {
                foreach ($item_soal['pilihan'] as $pilihan) {
                    $is_correct = (strcasecmp(trim($pilihan), trim($item_soal['jawaban_benar'])) == 0) ? 1 : 0;
                    $stmt_pilihan->bind_param("isi", $question_id, $pilihan, $is_correct);
                    $stmt_pilihan->execute();
                }
            } elseif ($question_type == 'true_false' && isset($item_soal['jawaban_benar'])) {
                // FIX: Gunakan variabel untuk menampung string literal
                $option_true = "Benar";
                $option_false = "Salah";

                $is_correct_true = ($item_soal['jawaban_benar'] === true) ? 1 : 0;
                $stmt_pilihan->bind_param("isi", $question_id, $option_true, $is_correct_true);
                $stmt_pilihan->execute();
                
                $is_correct_false = ($item_soal['jawaban_benar'] === false) ? 1 : 0;
                $stmt_pilihan->bind_param("isi", $question_id, $option_false, $is_correct_false);
                $stmt_pilihan->execute();
            }
        }
        $stmt_soal->close();
        $stmt_pilihan->close();

        $mysqli->commit();

        header("Location: ../editor_kuis.php?content_id=" . $content_id);
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        die("Database error: " . $e->getMessage());
    }
}

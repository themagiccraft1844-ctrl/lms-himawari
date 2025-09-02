<?php
// File: AI/chat_handler.php (Dikembalikan ke versi sebelumnya yang berfungsi)
session_start();
require_once "../db.php";
require_once "../config.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $user_id = $_SESSION['id'];
    $request_data = json_decode(file_get_contents('php://input'), true);
    $user_prompt = $request_data['prompt'] ?? '';
    $session_id = $request_data['session_id'] ?? null;
    $context_id = $request_data['context_id'] ?? null;
    $is_editing = $request_data['is_editing'] ?? false;
    $message_id_to_edit = $request_data['message_id'] ?? null;
    $is_new_chat = false;

    if (empty($user_prompt)) {
        echo json_encode(['error' => 'Prompt is empty']);
        exit;
    }

    if ($is_editing && !empty($message_id_to_edit)) {
        // Hapus pesan user lama dan respon model setelahnya
        $stmt = $mysqli->prepare("DELETE FROM ai_chat_history WHERE id >= ? AND id <= ? AND user_id = ?");
        $next_id = $message_id_to_edit + 1;
        $stmt->bind_param("iii", $message_id_to_edit, $next_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    if (empty($session_id)) {
        $session_id = uniqid('chat_');
        $is_new_chat = true;
        $temp_title = "Percakapan Baru"; 
        $stmt = $mysqli->prepare("INSERT INTO ai_chat_sessions (session_id, user_id, title) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $session_id, $user_id, $temp_title);
        $stmt->execute();
        $stmt->close();
    }

    // Simpan pesan pengguna ke riwayat
    $stmt = $mysqli->prepare("INSERT INTO ai_chat_history (user_id, session_id, role, message) VALUES (?, ?, 'user', ?)");
    $stmt->bind_param("iss", $user_id, $session_id, $user_prompt);
    $stmt->execute();
    $user_message_id = $stmt->insert_id;
    $stmt->close();

    // Ambil pesan yang baru saja disimpan untuk dikirim kembali ke frontend
    $stmt = $mysqli->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE id = ?");
    $stmt->bind_param("i", $user_message_id);
    $stmt->execute();
    $user_message_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();


    // Bangun riwayat percakapan untuk dikirim ke AI
    $history = [];
    $stmt = $mysqli->prepare("SELECT role, message FROM ai_chat_history WHERE session_id = ? ORDER BY created_at ASC LIMIT 20");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $history[] = ['role' => $row['role'], 'parts' => [['text' => $row['message']]]];
    }
    $stmt->close();

    // Tambahkan konteks materi jika ada
    if (!empty($context_id)) {
        $stmt = $mysqli->prepare("SELECT md.body_html FROM materi_details md WHERE md.content_id = ?");
        $stmt->bind_param("i", $context_id);
        $stmt->execute();
        $material_result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($material_result) {
            $material_text = trim(strip_tags($material_result['body_html']));
            $last_message_index = count($history) - 1;
            if ($last_message_index >= 0) {
                $original_prompt = $history[$last_message_index]['parts'][0]['text'];
                $history[$last_message_index]['parts'][0]['text'] = "Berdasarkan materi berikut:\n\n---\n" . $material_text . "\n---\n\nJawab pertanyaan ini: " . $original_prompt;
            }
        }
    }


    // Panggil Gemini API untuk mendapatkan respons utama
    $api_key = GEMINI_API_KEY;
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $api_key;
    $data = ['contents' => $history];
    $options = ['http' => ['header'  => "Content-type: application/json\r\n", 'method'  => 'POST', 'content' => json_encode($data), 'ignore_errors' => true]];
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response, true);

    $ai_response_text = 'Maaf, terjadi kesalahan saat menghubungi AI.';
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $ai_response_text = $result['candidates'][0]['content']['parts'][0]['text'];
    }

    // Simpan respons AI ke riwayat
    $stmt = $mysqli->prepare("INSERT INTO ai_chat_history (user_id, session_id, role, message) VALUES (?, ?, 'model', ?)");
    $stmt->bind_param("iss", $user_id, $session_id, $ai_response_text);
    $stmt->execute();
    $ai_message_id = $stmt->insert_id;
    $stmt->close();

    // Ambil data respons AI yang baru disimpan
    $stmt = $mysqli->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE id = ?");
    $stmt->bind_param("i", $ai_message_id);
    $stmt->execute();
    $ai_response_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Jika ini adalah percakapan baru, minta AI untuk membuat judul
    $new_title = null;
    if ($is_new_chat) {
        try {
            $title_prompt = "Buatkan judul yang sangat singkat (maksimal 5 kata) untuk percakapan yang diawali dengan: \"" . $user_prompt . "\". Jawab hanya dengan judulnya saja, tanpa tanda kutip atau embel-embel lainnya.";
            $title_data = ['contents' => [['parts' => [['text' => $title_prompt]]]]];
            $title_options = ['http' => ['header'  => "Content-type: application/json\r\n", 'method'  => 'POST', 'content' => json_encode($title_data), 'ignore_errors' => true]];
            $title_context = stream_context_create($title_options);
            $title_response_raw = @file_get_contents($url, false, $title_context);
            
            if($title_response_raw) {
                $title_result = json_decode($title_response_raw, true);
                if (isset($title_result['candidates'][0]['content']['parts'][0]['text'])) {
                    $new_title = trim($title_result['candidates'][0]['content']['parts'][0]['text'], " \t\n\r\0\x0B\"*.");
                    $stmt = $mysqli->prepare("UPDATE ai_chat_sessions SET title = ? WHERE session_id = ?");
                    $stmt->bind_param("ss", $new_title, $session_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } catch (Exception $e) {
            // Biarkan judul default jika pembuatan judul gagal
        }
    }

    echo json_encode([
        'user_message' => $user_message_data,
        'ai_response' => $ai_response_data,
        'session_id' => $session_id, 
        'new_title' => $new_title
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
}
?>


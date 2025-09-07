<?php
// File: AI/chat_handler.php (Implementasi Lengkap dengan Peringkasan Konteks Cerdas)
session_start();
require_once "../db.php";
require_once "../config.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Fungsi helper untuk memanggil API (untuk menghindari duplikasi kode)
function call_ai_api($url, $headers, $payload) {
    $options = ['http' => ['header'  => $headers, 'method'  => 'POST', 'content' => json_encode($payload), 'ignore_errors' => true]];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    return $response ? json_decode($response, true) : null;
}

try {
    $user_id = $_SESSION['id'];
    
    // 1. AMBIL PENGATURAN AI PENGGUNA
    $stmt_settings = $mysqli->prepare("SELECT * FROM user_ai_settings WHERE user_id = ?");
    $stmt_settings->bind_param("i", $user_id);
    $stmt_settings->execute();
    $settings = $stmt_settings->get_result()->fetch_assoc();
    if (!$settings) { // Jika pengguna belum punya baris pengaturan, buatkan.
        $mysqli->query("INSERT INTO user_ai_settings (user_id) VALUES ($user_id)");
        $settings = [];
    }
    $stmt_settings->close();

    $chat_provider = $settings['chat_provider'] ?? 'gemini';
    $chat_model = $settings['chat_model'] ?? 'gemini-1.5-flash-latest';
    $api_key_to_use = '';
    $is_default_key = false;

    // Pilih API Key yang akan digunakan berdasarkan provider yang dipilih
    if ($chat_provider == 'gemini') {
        $api_key_to_use = $settings['gemini_api_key'] ?? '';
        if (empty($api_key_to_use)) {
            $is_default_key = true;
            $api_key_to_use = DEFAULT_USER_GEMINI_API_KEY;
        }
    } elseif ($chat_provider == 'openai') {
        $api_key_to_use = $settings['openai_api_key'] ?? '';
    } elseif ($chat_provider == 'openrouter') {
        $api_key_to_use = $settings['openrouter_api_key'] ?? '';
    }

    if (empty($api_key_to_use)) {
         echo json_encode(['error' => 'API Key untuk provider yang dipilih belum diatur. Silakan isi di halaman Pengaturan AI.']);
         exit;
    }

    // 2. JIKA MENGGUNAKAN KUNCI DEFAULT GEMINI, CEK BATAS PENGGUNAAN
    if ($is_default_key) {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        
        $stmt_usage = $mysqli->prepare("
            SELECT SUM(CHAR_LENGTH(message)) as total_chars 
            FROM ai_chat_history 
            WHERE user_id = ? AND role = 'model' AND created_at BETWEEN ? AND ?
        ");
        $stmt_usage->bind_param("iss", $user_id, $today_start, $today_end);
        $stmt_usage->execute();
        $usage_result = $stmt_usage->get_result()->fetch_assoc();
        $stmt_usage->close();

        $chars_today = $usage_result['total_chars'] ?? 0;

        if ($chars_today >= DEFAULT_API_CHAR_LIMIT) {
            echo json_encode(['error' => 'Anda telah mencapai batas penggunaan harian untuk API Key default. Silakan masukkan API Key pribadi di halaman Pengaturan AI untuk melanjutkan.']);
            exit;
        }
    }

    // 3. PROSES PROMPT PENGGUNA & BUAT SESI BARU JIKA PERLU
    $request_data = json_decode(file_get_contents('php://input'), true);
    $user_prompt = $request_data['prompt'] ?? '';
    $session_id = $request_data['session_id'] ?? null;
    $is_new_chat = false;

    if (empty($user_prompt)) { echo json_encode(['error' => 'Prompt kosong']); exit; }
    
    if (empty($session_id)) {
        $session_id = uniqid('chat_');
        $is_new_chat = true;
        $stmt = $mysqli->prepare("INSERT INTO ai_chat_sessions (session_id, user_id, title) VALUES (?, ?, 'Percakapan Baru')");
        $stmt->bind_param("si", $session_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Simpan pesan user ke database
    $stmt_user_msg = $mysqli->prepare("INSERT INTO ai_chat_history (user_id, session_id, role, message) VALUES (?, ?, 'user', ?)");
    $stmt_user_msg->bind_param("iss", $user_id, $session_id, $user_prompt);
    $stmt_user_msg->execute();
    $user_message_id = $stmt_user_msg->insert_id;
    $stmt_user_msg->close();
    
    $stmt_get_user_msg = $mysqli->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE id = ?");
    $stmt_get_user_msg->bind_param("i", $user_message_id);
    $stmt_get_user_msg->execute();
    $user_message_data = $stmt_get_user_msg->get_result()->fetch_assoc();
    $stmt_get_user_msg->close();

    // 4. MANAJEMEN KONTEKS SECARA CERDAS
    $summary_stmt = $mysqli->prepare("SELECT summary_text, summarized_up_to_msg_id FROM ai_chat_summaries WHERE session_id = ?");
    $summary_stmt->bind_param("s", $session_id);
    $summary_stmt->execute();
    $summary_result = $summary_stmt->get_result()->fetch_assoc();
    $summary_stmt->close();

    $last_summary = $summary_result['summary_text'] ?? '';
    $last_summary_msg_id = $summary_result['summarized_up_to_msg_id'] ?? 0;

    $new_messages_stmt = $mysqli->prepare("SELECT id, role, message, CHAR_LENGTH(message) as char_count FROM ai_chat_history WHERE session_id = ? AND id > ? ORDER BY created_at ASC");
    $new_messages_stmt->bind_param("si", $session_id, $last_summary_msg_id);
    $new_messages_stmt->execute();
    $new_messages = $new_messages_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $new_messages_stmt->close();

    $total_new_chars = array_sum(array_column($new_messages, 'char_count'));

    if ($total_new_chars > CONTEXT_SUMMARY_TRIGGER_CHARS) {
        $full_text_to_summarize = implode("\n", array_map(fn($m) => "{$m['role']}: {$m['message']}", $new_messages));
        $summary_prompt = "Ringkas percakapan ini secara padat untuk konteks di masa depan. Gabungkan dengan ringkasan sebelumnya jika ada.\n\nRingkasan Sebelumnya: " . ($last_summary ?: "Tidak ada.") . "\n\nPercakapan Baru untuk Diringkas:\n" . $full_text_to_summarize;
        
        $summary_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . GEMINI_API_KEY;
        $summary_payload = ['contents' => [['parts' => [['text' => $summary_prompt]]]]];
        $summary_headers = "Content-type: application/json\r\n";
        $summary_api_result = call_ai_api($summary_url, $summary_headers, $summary_payload);
        
        if (isset($summary_api_result['candidates'][0]['content']['parts'][0]['text'])) {
            $last_summary = $summary_api_result['candidates'][0]['content']['parts'][0]['text'];
            $last_summarized_message = end($new_messages);
            $last_summary_msg_id = $last_summarized_message['id'];

            $save_summary_stmt = $mysqli->prepare("INSERT INTO ai_chat_summaries (session_id, summary_text, summarized_up_to_msg_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE summary_text = VALUES(summary_text), summarized_up_to_msg_id = VALUES(summarized_up_to_msg_id)");
            $save_summary_stmt->bind_param("ssi", $session_id, $last_summary, $last_summary_msg_id);
            $save_summary_stmt->execute();
            $save_summary_stmt->close();
            $new_messages = []; 
        }
    }

    $final_context_history = [];
    if (!empty($last_summary)) {
        $final_context_history[] = ['role' => 'user', 'message' => "Ini adalah ringkasan percakapan kita sejauh ini, gunakan ini sebagai konteks utama: " . $last_summary];
        $final_context_history[] = ['role' => 'model', 'message' => "Baik, saya mengerti ringkasannya."];
    }
    foreach ($new_messages as $msg) {
        $final_context_history[] = ['role' => $msg['role'], 'message' => $msg['message']];
    }

    // 5. PEMANGGILAN API UTAMA DENGAN KONTEKS YANG SUDAH DIOPTIMALKAN
    $url = ''; $payload = []; $headers = "Content-type: application/json\r\n";
    switch ($chat_provider) {
        case 'openai':
            $url = 'https://api.openai.com/v1/chat/completions';
            $headers .= "Authorization: Bearer " . $api_key_to_use;
            $openai_messages = array_map(fn($msg) => ['role' => ($msg['role'] == 'model' ? 'assistant' : 'user'), 'content' => $msg['message']], $final_context_history);
            $payload = ['model' => $chat_model, 'messages' => $openai_messages];
            break;
        case 'openrouter':
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $headers .= "Authorization: Bearer " . $api_key_to_use . "\r\n" . "HTTP-Referer: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n" . "X-Title: LMS Pro-Himawari";
            $openrouter_messages = array_map(fn($msg) => ['role' => ($msg['role'] == 'model' ? 'assistant' : 'user'), 'content' => $msg['message']], $final_context_history);
            $payload = ['model' => $chat_model, 'messages' => $openrouter_messages];
            break;
        case 'gemini':
        default:
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $chat_model . ':generateContent?key=' . $api_key_to_use;
            $gemini_history = array_map(fn($msg) => ['role' => $msg['role'], 'parts' => [['text' => $msg['message']]]], $final_context_history);
            $payload = ['contents' => $gemini_history];
            break;
    }
    
    $api_result = call_ai_api($url, $headers, $payload);
    
    $ai_response_text = 'Maaf, terjadi kesalahan saat menghubungi AI. Periksa kembali API Key dan model yang Anda gunakan.';
    if (isset($api_result['error']['message'])) { $ai_response_text = "Error: " . $api_result['error']['message']; } 
    else {
        switch ($chat_provider) {
            case 'openai': case 'openrouter': if (isset($api_result['choices'][0]['message']['content'])) $ai_response_text = $api_result['choices'][0]['message']['content']; break;
            default: if (isset($api_result['candidates'][0]['content']['parts'][0]['text'])) $ai_response_text = $api_result['candidates'][0]['content']['parts'][0]['text']; break;
        }
    }
    
    $stmt_ai_msg = $mysqli->prepare("INSERT INTO ai_chat_history (user_id, session_id, role, message) VALUES (?, ?, 'model', ?)");
    $stmt_ai_msg->bind_param("iss", $user_id, $session_id, $ai_response_text);
    $stmt_ai_msg->execute();
    $ai_message_id = $stmt_ai_msg->insert_id; $stmt_ai_msg->close();
    
    $stmt_get_ai_msg = $mysqli->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE id = ?");
    $stmt_get_ai_msg->bind_param("i", $ai_message_id); $stmt_get_ai_msg->execute();
    $ai_response_data = $stmt_get_ai_msg->get_result()->fetch_assoc(); $stmt_get_ai_msg->close();
    
    $new_title = null;
    if ($is_new_chat) {
        $title_prompt = "Buatkan judul yang sangat singkat (maksimal 5 kata) untuk percakapan yang diawali dengan: \"" . $user_prompt . "\". Jawab hanya dengan judulnya saja, tanpa tanda kutip atau embel-embel lainnya.";
        $title_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . GEMINI_API_KEY;
        $title_payload = ['contents' => [['parts' => [['text' => $title_prompt]]]]];
        $title_headers = "Content-type: application/json\r\n";
        $title_result = call_ai_api($title_url, $title_headers, $title_payload);
        
        if (isset($title_result['candidates'][0]['content']['parts'][0]['text'])) {
            $new_title = trim($title_result['candidates'][0]['content']['parts'][0]['text'], " \t\n\r\0\x0B\"*.");
            $stmt = $mysqli->prepare("UPDATE ai_chat_sessions SET title = ? WHERE session_id = ?");
            $stmt->bind_param("ss", $new_title, $session_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo json_encode(['user_message' => $user_message_data, 'ai_response' => $ai_response_data, 'session_id' => $session_id, 'new_title' => $new_title]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan tak terduga: ' . $e->getMessage()]);
}
?>


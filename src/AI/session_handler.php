<?php
// File: AI/session_handler.php (Diperbarui dengan aksi hapus)
session_start();
require_once "../db.php";

// Cek otorisasi pengguna
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['id'];
$action = $_GET['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'fetch_sessions') {
    $stmt = $mysqli->prepare("
        SELECT s.session_id, s.title 
        FROM ai_chat_sessions s
        WHERE s.user_id = ? AND EXISTS (SELECT 1 FROM ai_chat_history h WHERE h.session_id = s.session_id)
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($sessions);
    $stmt->close();

} elseif ($action === 'load_chat' && isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    $stmt = $mysqli->prepare("SELECT id, role, message, created_at FROM ai_chat_history WHERE session_id = ? AND user_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("si", $session_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($messages);
    $stmt->close();
    
} elseif ($action === 'delete_session') { // FITUR BARU: Menangani penghapusan sesi
    $request_data = json_decode(file_get_contents('php://input'), true);
    $session_id = $request_data['session_id'] ?? null;
    if ($session_id) {
        // ON DELETE CASCADE di database akan menangani penghapusan riwayat chat terkait
        $stmt = $mysqli->prepare("DELETE FROM ai_chat_sessions WHERE session_id = ? AND user_id = ?");
        $stmt->bind_param("si", $session_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Gagal menghapus sesi.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'ID Sesi tidak valid.']);
    }
    
} else {
    echo json_encode(['error' => 'Invalid action']);
}

$mysqli->close();
?>


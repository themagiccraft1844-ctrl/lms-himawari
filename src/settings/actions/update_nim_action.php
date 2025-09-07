<?php
// File: src/settings/actions/update_nim_action.php (BARU)
session_start();
require_once "../../db.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = trim($_POST['nim'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $user_id = $_SESSION['id'];

    if (empty($nim) || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'NIM dan Nama Lengkap tidak boleh kosong.']);
        exit;
    }
    
    // Cek apakah NIM sudah digunakan oleh user lain
    $stmt_check = $mysqli->prepare("SELECT id FROM users WHERE nim = ? AND id != ?");
    $stmt_check->bind_param("si", $nim, $user_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'NIM ini sudah terdaftar pada akun lain.']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    // Update NIM dan Nama Lengkap untuk user saat ini
    $stmt_update = $mysqli->prepare("UPDATE users SET nim = ?, full_name = ? WHERE id = ?");
    $stmt_update->bind_param("ssi", $nim, $full_name, $user_id);

    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data NIM dan Nama berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data di database.']);
    }
    $stmt_update->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid.']);
}

$mysqli->close();
?>

<?php
// File: admin/actions/user_approval_action.php (File BARU)
session_start();
require_once "../../db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: ../../index.php");
    exit;
}

// Fungsi simulasi pengiriman email dari register_action.php
function sendVerificationEmail($to, $token) {
    $subject = "Akun LMS Pro-Himawari Anda Telah Disetujui";
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_path = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
    $base_url = rtrim($protocol . $host . $script_path, '/');
    $verification_link = $base_url . "/verify.php?token=" . $token;
    
    $message = "
    <html><body>
    <p>Halo,</p>
    <p>Pendaftaran Anda telah disetujui oleh administrator. Silakan klik link di bawah ini untuk mengaktifkan akun Anda:</p>
    <p><a href='$verification_link'>$verification_link</a></p>
    </body></html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= 'From: no-reply@prohimawari.com' . "\r\n";
    
    @mail($to, $subject, $message, $headers);
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        // Ambil email pengguna
        $stmt_email = $mysqli->prepare("SELECT email FROM users WHERE id = ?");
        $stmt_email->bind_param("i", $user_id);
        $stmt_email->execute();
        $user_email = $stmt_email->get_result()->fetch_assoc()['email'];
        $stmt_email->close();

        if ($user_email) {
            // Ubah status, buat token, dan kirim email
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 day'));
            
            $sql = "UPDATE users SET status = 'pending_email_verification', verification_token = ?, token_expires_at = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ssi", $token, $expires, $user_id);
            if ($stmt->execute()) {
                sendVerificationEmail($user_email, $token);
                $_SESSION['approval_message'] = "Pengguna berhasil disetujui. Email verifikasi telah dikirim.";
            }
            $stmt->close();
        }

    } elseif ($action == 'reject') {
        // Hapus pengguna dari database
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['approval_message'] = "Pendaftaran pengguna telah ditolak dan dihapus.";
        $stmt->close();
    }
}

header("location: ../approve_users.php");
exit;
?>

<?php
// File: actions/register_action.php (DIPERBARUI DENGAN PHPMailer DAN VALIDASI PASSWORD)
session_start();
require_once "../db.php";
require_once "../config.php"; // Memuat konfigurasi SMTP

// Memuat kelas-kelas PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Sesuaikan path jika Anda meletakkan folder 'src' di tempat lain
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';


/**
 * Mengirim email verifikasi menggunakan PHPMailer dan SMTP.
 * @param string $to Email tujuan.
 * @param string $token Token verifikasi.
 * @return bool True jika berhasil, false jika gagal.
 */
function sendVerificationEmail($to, $token) {
    $mail = new PHPMailer(true);

    try {
        // Konfigurasi Server
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Penerima
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Konten Email
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script_path = dirname(dirname($_SERVER['SCRIPT_NAME']));
        $base_url = rtrim($protocol . $host . $script_path, '/');
        $verification_link = $base_url . "/verify.php?token=" . $token;

        $mail->isHTML(true);
        $mail->Subject = "Aktivasi Akun LMS Pro-Himawari Anda";
        $mail->Body    = "<html><body><p>Halo,</p><p>Terima kasih telah mendaftar. Silakan klik link di bawah ini untuk mengaktifkan akun Anda:</p><p><a href='$verification_link'>$verification_link</a></p></body></html>";
        $mail->AltBody = "Silakan salin dan tempel link berikut di browser Anda untuk aktivasi: " . $verification_link;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// --- PROSES UTAMA PENDAFTARAN ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $nim = trim($_POST['nim']);

    // Validasi konfirmasi password di sisi server
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Konfirmasi password tidak cocok.";
        header("location: ../register.php");
        exit;
    }

    if (empty($username) || empty($password) || empty($email) || empty($full_name) || empty($nim)) {
        $_SESSION['register_error'] = "Semua data wajib diisi, pastikan data KTM sudah dikonfirmasi.";
        header("location: ../register.php");
        exit;
    }
    
    $required_domain = "@apps.ipb.ac.id";
    if (!str_ends_with($email, $required_domain)) {
        $_SESSION['register_error'] = "Pendaftaran hanya diizinkan menggunakan email resmi universitas ($required_domain).";
        header("location: ../register.php");
        exit;
    }

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR nim = ?");
    $stmt->bind_param("sss", $username, $email, $nim);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['register_error'] = "Username, Email, atau NIM sudah terdaftar.";
        header("location: ../register.php");
        exit;
    }
    $stmt->close();
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $status = 'pending_email_verification';
    $verification_token = bin2hex(random_bytes(32));
    $token_expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
    
    $sql = "INSERT INTO users (username, password, email, nim, full_name, status, verification_token, token_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssssssss", $username, $hashed_password, $email, $nim, $full_name, $status, $verification_token, $token_expires_at);
        
        if ($stmt->execute()) {
            if (sendVerificationEmail($email, $verification_token)) {
                header("location: ../register.php?status=success");
            } else {
                $_SESSION['register_error'] = "Pendaftaran berhasil, namun gagal mengirim email verifikasi. Hubungi admin.";
                header("location: ../register.php");
            }
            exit();
        } else {
            $_SESSION['register_error'] = "Terjadi kesalahan saat menyimpan data.";
            header("location: ../register.php");
            exit;
        }
    }
} else {
    header("location: ../register.php");
    exit;
}
?>

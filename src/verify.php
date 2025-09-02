<?php
// File: verify.php (File BARU)
require_once "db.php";

$message = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Cari pengguna dengan token yang sesuai dan belum kedaluwarsa
    $sql = "SELECT id, token_expires_at FROM users WHERE verification_token = ? AND status = 'pending_email_verification'";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $now = new DateTime();
            $expires = new DateTime($user['token_expires_at']);

            if ($now > $expires) {
                $message = '<div class="alert alert-danger">Token verifikasi sudah kedaluwarsa. Silakan coba daftar lagi.</div>';
            } else {
                // Token valid, aktifkan akun
                $update_sql = "UPDATE users SET status = 'active', verification_token = NULL, token_expires_at = NULL WHERE id = ?";
                if ($update_stmt = $mysqli->prepare($update_sql)) {
                    $update_stmt->bind_param("i", $user['id']);
                    if ($update_stmt->execute()) {
                        // Arahkan ke halaman login dengan pesan sukses
                        header("location: index.php?status=verified");
                        exit();
                    }
                }
            }
        } else {
            $message = '<div class="alert alert-danger">Token verifikasi tidak valid atau akun sudah diaktifkan.</div>';
        }
        $stmt->close();
    }
} else {
    $message = '<div class="alert alert-danger">Tidak ada token verifikasi yang diberikan.</div>';
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Akun</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Status Verifikasi</h1>
        </div>
        <div class="auth-body">
            <?php echo $message; ?>
            <p><a href="index.php">Kembali ke Halaman Login</a></p>
        </div>
    </div>
</body>
</html>

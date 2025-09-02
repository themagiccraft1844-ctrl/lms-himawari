<?php
// File: index.php
// Halaman login utama (DIPERBARUI DENGAN STATUS CHECK).

require_once "db.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Arahkan langsung ke dashboard yang sesuai
    header("location: " . ($_SESSION["role"] === 'admin' ? "admin/index.php" : "dashboard.php"));
    exit;
}

$username = $password = "";
$username_err = $password_err = $login_err = "";
$status_msg = '';

if(isset($_GET['status'])){
    if($_GET['status'] == 'verified'){
        $status_msg = '<div class="alert alert-success">Verifikasi berhasil! Silakan login.</div>';
    }
     if($_GET['status'] == 'registered' || $_GET['status'] == 'success'){ // Menangani redirect dari register
        $status_msg = '<div class="alert alert-success">Pendaftaran berhasil! Silakan periksa email Anda untuk mengaktifkan akun.</div>';
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Silakan masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }
    if (empty(trim($_POST["password"]))) {
        $password_err = "Silakan masukkan password Anda.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        // MODIFIKASI: Tambahkan "AND role = 'user'" untuk memastikan hanya user biasa yang bisa login di sini.
        $sql = "SELECT id, username, password, role, status FROM users WHERE username = ? AND role = 'user'";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password, $role, $status);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Cek status akun
                            if ($status == 'active') {
                                // Jika berhasil dan aktif, mulai session
                                session_start();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["role"] = $role;
                                
                                // MODIFIKASI: Karena sudah difilter untuk 'user', langsung arahkan ke dashboard user.
                                header("location: dashboard.php");
                                exit;
                            } elseif ($status == 'pending_email_verification') {
                                $login_err = "Akun belum aktif. Silakan periksa email Anda untuk link verifikasi.";
                            } else {
                                $login_err = "Akun Anda tidak aktif atau ditolak.";
                            }
                        } else {
                            $login_err = "Username atau password salah.";
                        }
                    }
                } else {
                    // Pesan ini akan muncul jika username tidak ditemukan atau jika username ada tapi bukan 'user'
                    $login_err = "Username atau password salah.";
                }
            } else {
                echo "Oops! Terjadi kesalahan.";
            }
            $stmt->close();
        }
    }
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Platform Kursus</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>Selamat Datang!</h1>
            <p>Silakan masuk untuk melanjutkan</p>
        </div>
        <div class="auth-body">
            <?php echo $status_msg; ?>
            <?php if(!empty($login_err)) echo '<div class="alert alert-danger">' . $login_err . '</div>'; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <p>Belum punya akun? <a href="register.php">Daftar sekarang</a>.</p>
                <p class="admin-login-link"><a href="admin_login.php">Login sebagai Admin</a></p>
            </form>
        </div>
    </div>
</body>
</html>

<?php
require_once "../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Ambil pesan error dari session jika ada (setelah redirect dari action)
$new_password_err = $_SESSION['new_password_err'] ?? '';
$confirm_password_err = $_SESSION['confirm_password_err'] ?? '';
$success_msg = $_SESSION['success_msg'] ?? '';

// Hapus pesan dari session agar tidak muncul lagi saat refresh
unset($_SESSION['new_password_err']);
unset($_SESSION['confirm_password_err']);
unset($_SESSION['success_msg']);

$is_forced = isset($_GET['force']) && $_GET['force'] == 'true';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Ganti Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/style.css"> <!-- Memanggil style.css untuk form -->
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="kelola_user.php"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li class="active"><a href="ganti_password.php"><i class="fas fa-key"></i> <span>Ganti Password</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
             <div class="header-title"><h2><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i></a> Ganti Password Anda</h2></div>
             <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
             <div class="form-container">
                <?php if($is_forced): ?>
                    <div class="alert alert-warning">Untuk keamanan, Anda harus mengganti password default sebelum melanjutkan.</div>
                <?php endif; ?>
                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>

                <form action="actions/ganti_password_action.php" method="post">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn" value="Update Password">
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
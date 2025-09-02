<?php
// File: admin/approve_users.php (File BARU)
require_once "../db.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin') {
    header("location: ../index.php");
    exit;
}

// Ambil semua pengguna yang menunggu persetujuan
$sql = "SELECT id, nim, full_name, email, created_at FROM users WHERE status = 'pending_admin_approval' ORDER BY created_at ASC";
$pending_users = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

$message = $_SESSION['approval_message'] ?? '';
unset($_SESSION['approval_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Persetujuan Pendaftaran</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="kelola_user.php"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li class="active"><a href="approve_users.php"><i class="fas fa-user-check"></i> <span>Persetujuan</span></a></li>
            <li><a href="ganti_password.php"><i class="fas fa-key"></i> <span>Ganti Password</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <h2>Persetujuan Pendaftaran Baru</h2>
            </div>
            <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                    <small>Admin</small>
                </div>
            </div>
        </header>

        <main>
            <?php if (!empty($message)): ?>
                <div class="alert alert-success" style="background-color: #d4edda; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Lengkap (PDDikti)</th>
                            <th>Email Pendaftar</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_users)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada pendaftaran yang menunggu persetujuan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['nim']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="actions/user_approval_action.php?id=<?php echo $user['id']; ?>&action=approve" class="btn-action manage">Setujui</a>
                                        <a href="actions/user_approval_action.php?id=<?php echo $user['id']; ?>&action=reject" class="btn-action delete" onclick="return confirm('Yakin ingin menolak pendaftaran ini?');">Tolak</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

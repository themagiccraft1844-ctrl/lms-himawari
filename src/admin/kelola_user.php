<?php
// File: admin/kelola_user.php (File BARU)

require_once "../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// Mengambil semua data pengguna kecuali admin yang sedang login
$sql = "SELECT id, username, role, created_at FROM users WHERE id != ?";
$users = [];
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $_SESSION['id']);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $users[] = $row;
            }
        }
    }
    $stmt->close();
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><title>Kelola Pengguna</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <!-- (Sidebar sama seperti di admin/index.php, tandai 'Kelola User' sebagai aktif) -->
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li class="active"><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <header>
             <div class="header-title"><h2><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i></a> Kelola Pengguna</h2></div>
             <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="content-table">
                <table>
                    <thead>
                        <tr><th>Username</th><th>Peran Saat Ini</th><th>Tanggal Bergabung</th><th>Ubah Peran</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <form action="actions/update_role_action.php" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="new_role" onchange="this.form.submit()">
                                        <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
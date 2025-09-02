<?php
// File: admin/index.php (Diperbarui)

require_once "../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

// --- FITUR BARU: Cek Password Default ---
// Cek apakah admin saat ini masih menggunakan password default 'admin123'
$sql_check_pass = "SELECT password FROM users WHERE id = ?";
if($stmt_check = $mysqli->prepare($sql_check_pass)){
    $stmt_check->bind_param("i", $_SESSION['id']);
    if($stmt_check->execute()){
        $stmt_check->bind_result($hashed_password);
        if($stmt_check->fetch()){
            if(password_verify('admin123', $hashed_password)){
                // Jika password masih default, paksa ganti password
                header("location: ganti_password.php?force=true");
                exit;
            }
        }
    }
    $stmt_check->close();
}
// -----------------------------------------

// Mengambil semua data kursus dari database
$sql = "SELECT id, title, description, created_at FROM courses ORDER BY created_at DESC";
$courses = [];
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $courses[] = $row;
        }
        $result->free();
    }
} else {
    echo "ERROR: Could not able to execute $sql. " . $mysqli->error;
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h2>Admin Panel</h2></div>
        <ul class="nav-links">
            <li class="active"><a href="index.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="kelola_user.php"><i class="fas fa-users"></i> <span>Kelola User</span></a></li>
            <li><a href="ganti_password.php"><i class="fas fa-key"></i> <span>Ganti Password</span></a></li>
            <li class="logout-link"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Manajemen Kursus</h2>
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
            <div class="admin-controls">
                <a href="tambah_kursus.php" class="btn"><i class="fas fa-plus"></i> Tambah Kursus Baru</a>
            </div>

            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Kursus</th>
                            <th>Deskripsi</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($courses)): ?>
                            <?php foreach($courses as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($course['created_at'])); ?></td>
                                    <td>
                                        <a href="kelola_kursus.php?id=<?php echo $course['id']; ?>" class="btn-action manage">Kelola</a>
                                        <a href="actions/hapus_kursus_action.php?id=<?php echo $course['id']; ?>" class="btn-action delete" onclick="return confirm('Apakah Anda yakin ingin menghapus kursus ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada kursus yang ditambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="../js/dashboard.js"></script>
</body>
</html>

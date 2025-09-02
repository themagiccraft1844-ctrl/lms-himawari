<?php
// File: kursusku.php

require_once "db.php";

// Cek jika user belum login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Mengambil semua data kursus dari database
$sql = "SELECT id, title, description, banner_image_url FROM courses ORDER BY created_at DESC";
$courses = [];
if($result = $mysqli->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $courses[] = $row;
        }
        $result->free();
    }
}
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kursus Saya - Platform Kursus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Platform Kursus</h2>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li class="active"><a href="kursusku.php"><i class="fas fa-book"></i> <span>Kursus Saya</span></a></li>
            <li><a href="chat.php"><i class="fas fa-comments"></i> <span>Chat with AI</span></a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> <span>Pengaturan</span></a></li>
            <li class="logout-link"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Keluar</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <div class="header-title">
                <i class="fas fa-bars" id="menu-toggle"></i>
                <h2>Kursus Saya</h2>
            </div>
             <div class="header-actions">
                <div class="user-wrapper" id="user-menu-toggle">
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                        <small>User</small>
                    </div>
                </div>
                 <div class="dropdown-menu" id="user-dropdown">
                    <a href="settings.php" class="dropdown-item">Profil & Pengaturan</a>
                    <a href="logout.php" class="dropdown-item">Keluar</a>
                </div>
            </div>
        </header>

        <main>
            <h3 class="main-title">Daftar Semua Kursus</h3>
            <div class="course-grid">
                <?php if(!empty($courses)): ?>
                    <?php foreach($courses as $course): ?>
                        <a href="view_course.php?id=<?php echo $course['id']; ?>" class="course-card">
                            <div class="card-banner" style="background-image: url('<?php echo !empty($course['banner_image_url']) ? htmlspecialchars($course['banner_image_url']) : 'https://placehold.co/600x400/e0e0e0/777?text=Kursus'; ?>');">
                            </div>
                            <div class="card-content">
                                <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                                <div class="card-footer">
                                    <span>Lihat Selengkapnya <i class="fas fa-arrow-right"></i></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Belum ada kursus yang tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>

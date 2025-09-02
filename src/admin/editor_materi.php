<?php
// File: admin/editor_materi.php

require_once "../db.php";

// Cek sesi admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../index.php");
    exit;
}

$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : 0;
if(!$content_id){ header("location: index.php"); exit; }

// Ambil judul materi dan isinya jika sudah ada
$sql = "SELECT cc.title, cc.course_id, md.body_html 
        FROM course_contents cc
        JOIN materi_details md ON cc.id = md.content_id
        WHERE cc.id = ?";

$course_id = 0;
$materi_title = "Materi Tidak Ditemukan";
$body_html = "<p>Tulis materi Anda di sini...</p>";

if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $content_id);
    if($stmt->execute()){
        $stmt->bind_result($title, $c_id, $body);
        if($stmt->fetch()){
            $materi_title = $title;
            $course_id = $c_id;
            if(!empty($body)) {
                $body_html = $body;
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
    <meta charset="UTF-8"><title>Editor Materi: <?php echo htmlspecialchars($materi_title); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <!-- GANTI YOUR_API_KEY dengan API Key TinyMCE Anda untuk fungsionalitas penuh -->
    <script src="https://cdn.tiny.cloud/1/czwrm0i1zanx543nu2d9olj2jlgzblkdqcjolymd0m0ssnla/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: 'textarea#materi-editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks code fullscreen insertdatetime media table help',
        toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | code preview',
        content_style: 'body { font-family:Inter,sans-serif; font-size:16px }'
      });
    </script>
</head>
<body>
    <div class="main-content" style="margin-left:0; width:100%;">
        <header>
            <div class="header-title">
                <h2><a href="kelola_kursus.php?id=<?php echo $course_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a> Edit Materi: <?php echo htmlspecialchars($materi_title); ?></h2>
            </div>
             <div class="user-wrapper">
                <i class="fas fa-user-shield"></i>
                <div><h4><?php echo htmlspecialchars($_SESSION["username"]); ?></h4><small>Admin</small></div>
            </div>
        </header>
        <main>
            <div class="form-container">
                <form action="actions/simpan_materi_action.php" method="post">
                    <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
                    <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                    <textarea id="materi-editor" name="body_html">
                        <?php echo htmlspecialchars($body_html); ?>
                    </textarea>
                    <div class="form-group" style="margin-top:20px;">
                        <input type="submit" class="btn" value="Simpan Materi">
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

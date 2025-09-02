<?php
// File: admin/actions/update_role_action.php (File BARU)

require_once "../../db.php";

// Cek jika user belum login atau bukan admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: ../../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'], $_POST['new_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    // Pastikan peran yang diinput valid
    if ($new_role == 'admin' || $new_role == 'user') {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $new_role, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}
header("location: ../kelola_user.php");
exit;
?>
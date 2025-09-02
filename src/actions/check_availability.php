<?php
// File: actions/check_availability.php
require_once "../db.php";

header('Content-Type: application/json');

$response = ['available' => false, 'message' => 'Permintaan tidak valid'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $field = $_POST['field'] ?? '';
    $value = trim($_POST['value'] ?? '');

    if (!empty($field) && !empty($value)) {
        // Lindungi dari SQL injection pada nama kolom
        $allowed_fields = ['username', 'email', 'nim'];
        if (in_array($field, $allowed_fields)) {
            $sql = "SELECT id FROM users WHERE $field = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $value);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows == 0) {
                    $response = ['available' => true];
                } else {
                    $response = ['available' => false, 'message' => ucfirst($field) . ' sudah digunakan.'];
                }
                $stmt->close();
            } else {
                 $response['message'] = 'Query database gagal.';
            }
        } else {
            $response['message'] = 'Kolom yang diperiksa tidak valid.';
        }
    } else {
        $response['message'] = 'Kolom atau nilai kosong.';
    }
}

$mysqli->close();
echo json_encode($response);
?>

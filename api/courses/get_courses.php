<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin', 'superadmin', 'teacher', 'student']);

if (($_SESSION['role'] ?? '') === 'superadmin') {
    $result = mysqli_query($conn, "
        SELECT id, institute_id, course_name, duration, fees, status
        FROM course ORDER BY id DESC
    ");
} else {
    $institute_id = (int) ($_SESSION['institute_id'] ?? 0);
    $stmt = mysqli_prepare($conn, "
        SELECT id, institute_id, course_name, duration, fees, status
        FROM course WHERE institute_id = ? ORDER BY id DESC
    ");
    mysqli_stmt_bind_param($stmt, 'i', $institute_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
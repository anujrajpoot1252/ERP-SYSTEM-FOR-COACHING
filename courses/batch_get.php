<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

api_require_access(['admin', 'superadmin', 'teacher']);
$role = $_SESSION['role'] ?? '';
$batch_name_column = 'name';
$batch_name_check = mysqli_query($conn, "SHOW COLUMNS FROM `batch` LIKE 'name'");
if (!$batch_name_check || mysqli_num_rows($batch_name_check) === 0) {
    $batch_name_column = 'batch_name';
}
$query = "SELECT b.id, b.`{$batch_name_column}` AS batch_name,
                 b.`{$batch_name_column}` AS name, b.course_id
          FROM batch b WHERE 1 = 1";
$params = [];
$types = '';
if ($role !== 'superadmin') {
    $query .= ' AND b.institute_id = ?';
    $types .= 'i';
    $params[] = (int) ($_SESSION['institute_id'] ?? 0);
}
if ($role === 'teacher') {
    $teacher_stmt = mysqli_prepare($conn, "
        SELECT id FROM teacher WHERE user_id = ? AND institute_id = ? AND status = 'active' LIMIT 1
    ");
    $user_id = (int) $_SESSION['user_id'];
    $institute_id = (int) ($_SESSION['institute_id'] ?? 0);
    mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
    mysqli_stmt_execute($teacher_stmt);
    $teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
    mysqli_stmt_close($teacher_stmt);
    $query .= ' AND b.teacher_id = ?';
    $types .= 'i';
    $params[] = (int) ($teacher['id'] ?? 0);
}
$query .= ' ORDER BY b.id DESC';

$stmt = mysqli_prepare($conn, $query);
if ($types !== '') {
    $bindings = [$stmt, &$types];
    foreach ($params as &$param) {
        $bindings[] = &$param;
    }
    unset($param);
    call_user_func_array('mysqli_stmt_bind_param', $bindings);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
echo json_encode($data);

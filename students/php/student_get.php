<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

api_require_access(['admin', 'superadmin', 'teacher', 'student']);
$role = $_SESSION['role'] ?? '';
$query = "
    SELECT s.id, u.name, s.phone AS Phone_number, u.email AS Email,
           s.course_id, s.batch_id, c.course_name, b.name AS batch_name
    FROM student s
    INNER JOIN users u ON u.id = s.user_id AND u.role = 'student'
    LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
    LEFT JOIN batch b ON b.id = s.batch_id AND b.institute_id = s.institute_id
    WHERE 1 = 1
";
$params = [];
$types = '';
if ($role !== 'superadmin') {
    $query .= ' AND s.institute_id = ?';
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
} elseif ($role === 'student') {
    $query .= ' AND s.user_id = ?';
    $types .= 'i';
    $params[] = (int) $_SESSION['user_id'];
}
$query .= ' ORDER BY s.id DESC';

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
$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}
echo json_encode($students);

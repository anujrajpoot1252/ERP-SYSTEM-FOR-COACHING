<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

$role = $_SESSION['role'] ?? '';
api_require_access(['admin', 'superadmin', 'teacher', 'student']);
$query = "SELECT
                        s.id,
                        s.user_id,
                        s.institute_id,
                        s.admission_no,
                        s.phone,
                        s.parent_name,
                        s.parent_phone,
                        s.course_id,
                        c.course_name,
                        s.batch_id,
                        b.name AS batch_name,
                        s.status,
                        s.created_at,
                        u.name AS student_name,
                        u.email
                    FROM student AS s
                    INNER JOIN users AS u ON u.id = s.user_id
                    LEFT JOIN course AS c ON c.id = s.course_id
                    LEFT JOIN batch AS b ON b.id = s.batch_id
                    WHERE 1 = 1";
$params = [];
$types = '';
if ($role !== 'superadmin') {
    $query .= ' AND s.institute_id = ?';
    $types .= 'i';
    $params[] = (int) ($_SESSION['institute_id'] ?? 0);
}
if ($role === 'teacher') {
    $teacher_stmt = mysqli_prepare($conn, "SELECT id FROM teacher WHERE user_id = ? AND institute_id = ? AND status = 'active'");
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
    foreach ($params as $index => &$param) {
        $bindings[] = &$param;
    }
    unset($param);
    call_user_func_array('mysqli_stmt_bind_param', $bindings);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch students",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
?>
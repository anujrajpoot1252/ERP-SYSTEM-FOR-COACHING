<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin', 'superadmin', 'teacher', 'student']);
$role = $_SESSION['role'] ?? '';

$query = "SELECT
            a.id,
            a.student_id,
            u.name AS student_name,
            s.admission_no,
            s.phone AS student_phone,
            s.parent_name,
            s.parent_phone,
            a.batch_id,
            b.name AS batch_name,
            c.course_name,
            a.date,
            a.status,
            a.phone_number_parents,
            a.marking
          FROM attendance AS a
          LEFT JOIN student AS s ON s.id = a.student_id
          LEFT JOIN users AS u ON u.id = s.user_id
          LEFT JOIN batch AS b ON b.id = a.batch_id
          LEFT JOIN course AS c ON c.id = b.course_id
          WHERE 1 = 1";
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
    $query .= ' AND u.id = ?';
    $types .= 'i';
    $params[] = (int) $_SESSION['user_id'];
}
$query .= ' ORDER BY a.date DESC, a.id DESC';

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
        "message" => "Failed to fetch attendance",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$legacy_status_map = [
    'presence' => 'present',
    'absence' => 'absent'
];

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    if (isset($row['status']) && isset($legacy_status_map[$row['status']])) {
        $row['status'] = $legacy_status_map[$row['status']];
    }

    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
?>
<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin', 'superadmin', 'teacher', 'student']);
$role = $_SESSION['role'] ?? '';

$query = "SELECT
            f.id,
            f.student_id,
            u.name AS student_name,
            s.admission_no,
            f.amount,
            f.created_at,
            f.status,
            f.payment_mode,
            f.ref_no
          FROM fees AS f
          INNER JOIN student AS s ON s.id = f.student_id
          INNER JOIN users AS u ON u.id = s.user_id
          LEFT JOIN batch b ON b.id = s.batch_id";
$params = [];
$types = '';
if ($role !== 'superadmin') {
    $query .= ' WHERE s.institute_id = ?';
    $types .= 'i';
    $params[] = (int) ($_SESSION['institute_id'] ?? 0);
}
if ($role === 'teacher') {
    $query .= ($types === '' ? ' WHERE' : ' AND') . ' b.teacher_id = ?';
    $types .= 'i';
    $teacher_stmt = mysqli_prepare($conn, "SELECT id FROM teacher WHERE user_id = ? AND institute_id = ? AND status = 'active' LIMIT 1");
    $user_id = (int) $_SESSION['user_id'];
    $institute_id = (int) ($_SESSION['institute_id'] ?? 0);
    mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
    mysqli_stmt_execute($teacher_stmt);
    $teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
    mysqli_stmt_close($teacher_stmt);
    $params[] = (int) ($teacher['id'] ?? 0);
} elseif ($role === 'student') {
    $query .= ($types === '' ? ' WHERE' : ' AND') . ' s.user_id = ?';
    $types .= 'i';
    $params[] = (int) $_SESSION['user_id'];
}
$query .= ' ORDER BY f.created_at DESC, f.id DESC';

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
        "message" => "Failed to fetch fee payments",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
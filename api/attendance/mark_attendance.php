<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

api_require_access(['admin', 'teacher']);
api_require_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed.']);
    exit;
}

$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$batch_id = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT);
$date = is_string($_POST['date'] ?? null) ? trim($_POST['date']) : '';
$status = strtolower(is_string($_POST['status'] ?? null) ? trim($_POST['status']) : '');
$status = ['presence' => 'present', 'absence' => 'absent'][$status] ?? $status;
$parent_phone = is_string($_POST['phone_number_parents'] ?? null) ? trim($_POST['phone_number_parents']) : '';
$marking = is_string($_POST['marking'] ?? null) ? trim($_POST['marking']) : '';
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$role = $_SESSION['role'] ?? '';

if (!$student_id || $student_id < 1 || !$batch_id || $batch_id < 1 ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
    !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4)) ||
    !in_array($status, ['present', 'absent'], true) ||
    strlen($parent_phone) > 50 || strlen($marking) > 20) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Attendance details are invalid.']);
    exit;
}

try {
    mysqli_begin_transaction($conn);
    $teacher_id = 0;
    if ($role === 'teacher') {
        $teacher_stmt = mysqli_prepare($conn, "
            SELECT id FROM teacher WHERE user_id = ? AND institute_id = ? AND status = 'active' LIMIT 1
        ");
        $user_id = (int) $_SESSION['user_id'];
        mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
        mysqli_stmt_execute($teacher_stmt);
        $teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
        mysqli_stmt_close($teacher_stmt);
        $teacher_id = (int) ($teacher['id'] ?? 0);
        if ($teacher_id < 1) {
            throw new InvalidArgumentException('An active teacher profile is required.');
        }
    }

    $student_stmt = mysqli_prepare($conn, "
        SELECT s.id FROM student s
        INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
        INNER JOIN batch b ON b.id = s.batch_id AND b.institute_id = s.institute_id
        WHERE s.id = ? AND s.batch_id = ? AND s.institute_id = ? AND s.status = 'active'
            AND (? = 0 OR b.teacher_id = ?)
        LIMIT 1 FOR UPDATE
    ");
    mysqli_stmt_bind_param($student_stmt, 'iiiii', $student_id, $batch_id, $institute_id, $teacher_id, $teacher_id);
    mysqli_stmt_execute($student_stmt);
    $student_exists = mysqli_stmt_get_result($student_stmt)->num_rows === 1;
    mysqli_stmt_close($student_stmt);
    if (!$student_exists) {
        throw new InvalidArgumentException('Student is not enrolled in a batch you can manage.');
    }

    $attendance_stmt = mysqli_prepare($conn, "
        SELECT id FROM attendance
        WHERE student_id = ? AND batch_id = ? AND date = ?
        ORDER BY id DESC LIMIT 1 FOR UPDATE
    ");
    mysqli_stmt_bind_param($attendance_stmt, 'iis', $student_id, $batch_id, $date);
    mysqli_stmt_execute($attendance_stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($attendance_stmt));
    mysqli_stmt_close($attendance_stmt);
    if ($existing) {
        $attendance_id = (int) $existing['id'];
        $stmt = mysqli_prepare($conn, "
            UPDATE attendance SET status = ?, phone_number_parents = NULLIF(?, ''), marking = NULLIF(?, '')
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, 'sssi', $status, $parent_phone, $marking, $attendance_id);
    } else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO attendance (student_id, batch_id, `date`, status, phone_number_parents, marking)
            VALUES (?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''))
        ");
        mysqli_stmt_bind_param($stmt, 'iissss', $student_id, $batch_id, $date, $status, $parent_phone, $marking);
    }
    mysqli_stmt_execute($stmt);
    $attendance_id = $existing ? $attendance_id : mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    mysqli_commit($conn);
    echo json_encode(['status' => true, 'message' => 'Attendance saved successfully.', 'attendance_id' => $attendance_id]);
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    error_log('Attendance API save failed: ' . $exception->getMessage());
    http_response_code($exception instanceof InvalidArgumentException ? 400 : 500);
    echo json_encode([
        'status' => false,
        'message' => $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Attendance could not be saved.'
    ]);
}

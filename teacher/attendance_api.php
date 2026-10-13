<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

function attendance_response($status_code, $success, $message, $data = []) {
    http_response_code($status_code);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $data));
    exit;
}

if (empty($_SESSION['user_id'])) {
    attendance_response(401, false, 'Please sign in to continue.');
}
if (($_SESSION['role'] ?? '') !== 'teacher') {
    attendance_response(403, false, 'Teacher access is required.');
}

$user_id = (int) $_SESSION['user_id'];
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$teacher_stmt = mysqli_prepare($conn, "
    SELECT t.id
    FROM teacher t
    INNER JOIN users u ON u.id = t.user_id
    WHERE t.user_id = ? AND t.institute_id = ? AND t.status = 'active'
        AND u.role = 'teacher' AND u.status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($teacher_stmt);
$teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
mysqli_stmt_close($teacher_stmt);
if (!$teacher) {
    attendance_response(403, false, 'An active teacher profile is required.');
}
$teacher_id = (int) $teacher['id'];

$owns_batch = static function ($batch_id) use ($conn, $teacher_id, $institute_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT id FROM batch WHERE id = ? AND teacher_id = ? AND institute_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'iii', $batch_id, $teacher_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $owns = mysqli_stmt_get_result($stmt)->num_rows === 1;
    mysqli_stmt_close($stmt);
    return $owns;
};

$valid_date = static function ($date) {
    return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) &&
        checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4));
};

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $batch_id = filter_input(INPUT_GET, 'batch_id', FILTER_VALIDATE_INT);
    $date = is_string($_GET['date'] ?? null) ? $_GET['date'] : '';
    if (!$batch_id || $batch_id < 1 || !$valid_date($date)) {
        attendance_response(400, false, 'A valid batch and date are required.');
    }
    if (!$owns_batch($batch_id)) {
        attendance_response(403, false, 'This batch is not assigned to your account.');
    }

    $stmt = mysqli_prepare($conn, "
        SELECT s.id, u.name, s.admission_no,
               (
                   SELECT a.status FROM attendance a
                   WHERE a.student_id = s.id AND a.batch_id = ? AND a.date = ?
                   ORDER BY a.id DESC LIMIT 1
               ) AS status
        FROM student s
        INNER JOIN users u ON u.id = s.user_id
        WHERE s.batch_id = ? AND s.institute_id = ? AND s.status = 'active'
            AND u.role = 'student' AND u.status = 'active'
        ORDER BY s.admission_no ASC
    ");
    mysqli_stmt_bind_param($stmt, 'isii', $batch_id, $date, $batch_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $status = $row['status'];
        $students[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'roll_no' => $row['admission_no'],
            'present' => in_array($status, ['present', 'presence'], true)
                ? true
                : (in_array($status, ['absent', 'absence'], true) ? false : null),
        ];
    }
    mysqli_stmt_close($stmt);
    attendance_response(200, true, 'Attendance list loaded.', ['students' => $students]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    attendance_response(405, false, 'Method not allowed.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    attendance_response(400, false, 'Invalid attendance request.');
}
$csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? '');
if (!validate_csrf_token($csrf_token)) {
    attendance_response(403, false, 'Your session token is invalid. Refresh and try again.');
}
$batch_id = filter_var($data['batch_id'] ?? null, FILTER_VALIDATE_INT);
$date = $data['date'] ?? null;
$attendance = $data['attendance'] ?? null;
if (!$batch_id || $batch_id < 1 || !$valid_date($date) || !is_array($attendance) || count($attendance) === 0) {
    attendance_response(400, false, 'A valid batch, date, and attendance list are required.');
}
if (!$owns_batch($batch_id)) {
    attendance_response(403, false, 'This batch is not assigned to your account.');
}

$students_stmt = mysqli_prepare($conn, "
    SELECT COUNT(*) AS student_count FROM student s
    INNER JOIN users u ON u.id = s.user_id
    WHERE s.batch_id = ? AND s.institute_id = ? AND s.status = 'active'
        AND u.role = 'student' AND u.status = 'active'
");
mysqli_stmt_bind_param($students_stmt, 'ii', $batch_id, $institute_id);
mysqli_stmt_execute($students_stmt);
$student_count = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($students_stmt))['student_count'];
mysqli_stmt_close($students_stmt);
if ($student_count === 0 || count($attendance) !== $student_count) {
    attendance_response(400, false, 'Mark attendance for every active student in this batch.');
}

$seen_student_ids = [];
foreach ($attendance as $item) {
    if (!is_array($item) || !isset($item['student_id']) ||
        filter_var($item['student_id'], FILTER_VALIDATE_INT) === false ||
        !array_key_exists('present', $item) || !is_bool($item['present'])) {
        attendance_response(400, false, 'Each attendance entry must include a valid student_id and boolean present value.');
    }
    $student_id = (int) $item['student_id'];
    if ($student_id < 1 || isset($seen_student_ids[$student_id])) {
        attendance_response(400, false, 'Attendance entries must contain unique valid student IDs.');
    }
    $seen_student_ids[$student_id] = true;
}

try {
    mysqli_begin_transaction($conn);
    $student_check = mysqli_prepare($conn, "
        SELECT s.id FROM student s
        INNER JOIN users u ON u.id = s.user_id
        WHERE s.id = ? AND s.batch_id = ? AND s.institute_id = ?
            AND s.status = 'active' AND u.role = 'student' AND u.status = 'active'
        LIMIT 1
    ");
    $attendance_check = mysqli_prepare($conn, "
        SELECT id FROM attendance
        WHERE student_id = ? AND batch_id = ? AND date = ?
        ORDER BY id DESC LIMIT 1 FOR UPDATE
    ");
    $attendance_update = mysqli_prepare($conn, 'UPDATE attendance SET status = ? WHERE id = ?');
    $attendance_insert = mysqli_prepare($conn, "
        INSERT INTO attendance (student_id, batch_id, date, status) VALUES (?, ?, ?, ?)
    ");

    foreach ($attendance as $item) {
        $student_id = (int) $item['student_id'];
        mysqli_stmt_bind_param($student_check, 'iii', $student_id, $batch_id, $institute_id);
        mysqli_stmt_execute($student_check);
        if (mysqli_stmt_get_result($student_check)->num_rows !== 1) {
            throw new RuntimeException('A submitted student is not enrolled in this batch.');
        }

        mysqli_stmt_bind_param($attendance_check, 'iis', $student_id, $batch_id, $date);
        mysqli_stmt_execute($attendance_check);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($attendance_check));
        $status = $item['present'] ? 'present' : 'absent';
        if ($existing) {
            $attendance_id = (int) $existing['id'];
            mysqli_stmt_bind_param($attendance_update, 'si', $status, $attendance_id);
            mysqli_stmt_execute($attendance_update);
        } else {
            mysqli_stmt_bind_param($attendance_insert, 'iiss', $student_id, $batch_id, $date, $status);
            mysqli_stmt_execute($attendance_insert);
        }
    }

    mysqli_stmt_close($student_check);
    mysqli_stmt_close($attendance_check);
    mysqli_stmt_close($attendance_update);
    mysqli_stmt_close($attendance_insert);
    mysqli_commit($conn);
    attendance_response(200, true, 'Attendance saved successfully.');
} catch (Throwable $exception) {
    mysqli_rollback($conn);
    error_log('Attendance save failed: ' . $exception->getMessage());
    attendance_response(500, false, 'Attendance could not be saved. Please try again.');
}

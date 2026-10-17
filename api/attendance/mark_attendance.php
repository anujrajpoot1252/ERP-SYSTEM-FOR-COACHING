<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Superadmin, admin, teacher sabhi ko allow karo
api_require_access(['admin', 'superadmin', 'teacher']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Agar request JSON format me aaye to use $_POST me merge kar lo
$json_input = json_decode(file_get_contents('php://input'), true);
if (is_array($json_input)) {
    $_POST = array_merge($_POST, $json_input);
}

// CSRF check
api_require_csrf();

$student_id = (int) ($_POST['student_id'] ?? 0);
$batch_id = (int) ($_POST['batch_id'] ?? 0);
$date = is_string($_POST['date'] ?? null) ? trim($_POST['date']) : '';
$status = strtolower(is_string($_POST['status'] ?? null) ? trim($_POST['status']) : '');

// Presence/absence ya true/false ko present/absent me convert karo
if ($status === 'presence' || $status === '1' || $status === 'true') {
    $status = 'present';
} elseif ($status === 'absence' || $status === '0' || $status === 'false') {
    $status = 'absent';
}

$parent_phone = is_string($_POST['phone_number_parents'] ?? null) ? trim($_POST['phone_number_parents']) : '';
$marking = is_string($_POST['marking'] ?? null) ? trim($_POST['marking']) : '';
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$role = $_SESSION['role'] ?? '';

// Basic validation
if ($student_id < 1 || $batch_id < 1 ||
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

    // Check karo student batch me enrolled hai aur active hai
    $is_superadmin = ($role === 'superadmin') ? 1 : 0;
    $student_stmt = mysqli_prepare($conn, "
        SELECT s.id, s.parent_phone FROM student s
        INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
        INNER JOIN batch b ON b.id = s.batch_id
        WHERE s.id = ? AND s.batch_id = ? AND (? = 1 OR s.institute_id = ?) AND s.status = 'active'
            AND (? = 0 OR b.teacher_id = ?)
        LIMIT 1 FOR UPDATE
    ");
    mysqli_stmt_bind_param($student_stmt, 'iiiiii', $student_id, $batch_id, $is_superadmin, $institute_id, $teacher_id, $teacher_id);
    mysqli_stmt_execute($student_stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt));
    mysqli_stmt_close($student_stmt);
    if (!$student) {
        throw new InvalidArgumentException('Student is not enrolled in a batch you can manage.');
    }

    // Agar parent phone alag se nahi diya to student record wala use kar lo
    if ($parent_phone === '' && !empty($student['parent_phone'])) {
        $parent_phone = $student['parent_phone'];
    }

    // Check karo ki is student ki attendance pehle se hai ya nahi
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
        // Purana record update karo (aur purana phone number preserve rakho agar naya khali ho)
        $attendance_id = (int) $existing['id'];
        $stmt = mysqli_prepare($conn, "
            UPDATE attendance 
            SET status = ?, 
                phone_number_parents = COALESCE(NULLIF(?, ''), phone_number_parents), 
                marking = COALESCE(NULLIF(?, ''), marking)
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, 'sssi', $status, $parent_phone, $marking, $attendance_id);
    } else {
        // Naya record insert karo
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

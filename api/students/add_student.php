<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

$json_input = json_decode(file_get_contents('php://input'), true);
if (is_array($json_input)) {
    $_POST = array_merge($_POST, $json_input);
}

api_require_access(['admin']);
api_require_csrf();

$user_id = (int) ($_POST['user_id'] ?? 0);
$admission_no = is_string($_POST['admission_no'] ?? null) ? trim($_POST['admission_no']) : '';
$phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
$parent_name = is_string($_POST['parent_name'] ?? null) ? trim($_POST['parent_name']) : '';
$parent_phone = is_string($_POST['parent_phone'] ?? null) ? trim($_POST['parent_phone']) : '';
$course_id = (int) ($_POST['course_id'] ?? 0);
$batch_id = (int) ($_POST['batch_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);

if (!$user_id || $user_id < 1 || $admission_no === '' || strlen($admission_no) > 50 ||
    $phone === '' || strlen($phone) > 15 || $parent_name === '' || strlen($parent_name) > 100 ||
    $parent_phone === '' || strlen($parent_phone) > 15 || $institute_id < 1) {
    http_response_code(400);

    echo json_encode([
        "status" => false,
        "message" => "Required fields are missing"
    ]);
    exit;
}

$user_check = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? AND institute_id = ? AND role = 'student'");
mysqli_stmt_bind_param($user_check, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($user_check);
$user_exists = mysqli_stmt_get_result($user_check)->num_rows === 1;
mysqli_stmt_close($user_check);

$relations_valid = $course_id === 0 && $batch_id === 0;
if ($course_id > 0) {
    $course_check = mysqli_prepare($conn, "SELECT id FROM course WHERE id = ? AND institute_id = ?");
    mysqli_stmt_bind_param($course_check, 'ii', $course_id, $institute_id);
    mysqli_stmt_execute($course_check);
    $course_exists = mysqli_stmt_get_result($course_check)->num_rows === 1;
    mysqli_stmt_close($course_check);
    if ($batch_id > 0) {
        $batch_check = mysqli_prepare($conn, "SELECT id FROM batch WHERE id = ? AND course_id = ? AND institute_id = ?");
        mysqli_stmt_bind_param($batch_check, 'iii', $batch_id, $course_id, $institute_id);
        mysqli_stmt_execute($batch_check);
        $batch_exists = mysqli_stmt_get_result($batch_check)->num_rows === 1;
        mysqli_stmt_close($batch_check);
        $relations_valid = $course_exists && $batch_exists;
    } else {
        $relations_valid = $course_exists;
    }
} elseif ($batch_id > 0) {
    $relations_valid = false;
}

if (!$user_exists || !$relations_valid) {
    http_response_code(400);
    echo json_encode(["status" => false, "message" => "The student account, course, or batch is not valid for this institute."]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO student
    (user_id, institute_id, admission_no, phone, parent_name,
     parent_phone, course_id, batch_id, status)
    VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, 0), NULLIF(?, 0), 'active')"
);

$stmt->bind_param(
    "iissssii",
    $user_id,
    $institute_id,
    $admission_no,
    $phone,
    $parent_name,
    $parent_phone,
    $course_id,
    $batch_id
);

if ($stmt->execute()) {

    echo json_encode([
        "status" => true,
        "message" => "Student Added",
        "student_id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "status" => false,
        "message" => "Student Add Failed",
        "error" => $stmt->error
    ]);
}
?>
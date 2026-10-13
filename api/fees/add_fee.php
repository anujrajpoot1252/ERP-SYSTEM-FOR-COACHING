<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin', 'teacher']);
api_require_csrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(["status" => false, "message" => "Method not allowed"]);
	exit;
}

$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$amount = is_string($_POST['amount'] ?? null) ? trim($_POST['amount']) : '';

if (!$student_id || $student_id < 1 || $amount === '') {
	echo json_encode([
		"status" => false,
		"message" => "student_id and amount are required"
	]);
	exit;
}

if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $amount) || (float) $amount <= 0) {
	echo json_encode([
		"status" => false,
		"message" => "amount must be greater than zero with up to 2 decimal places"
	]);
	exit;
}

$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$role = $_SESSION['role'] ?? '';
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
		http_response_code(403);
		echo json_encode(["status" => false, "message" => "An active teacher profile is required"]);
		exit;
	}
}

$student_sql = "
	SELECT s.course_id, c.fees AS course_fee,
	       COALESCE((
	           SELECT SUM(f.amount) FROM fees f
	           WHERE f.student_id = s.id AND f.status = 'paid'
	               AND (f.course_id = s.course_id OR f.course_id IS NULL)
	       ), 0) AS paid_amount
	FROM student s
	INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
	LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
";
if ($role === 'teacher') {
	$student_sql .= " INNER JOIN batch b ON b.id = s.batch_id AND b.teacher_id = ? AND b.institute_id = s.institute_id";
}
$student_sql .= " WHERE s.id = ? AND s.institute_id = ? AND s.status = 'active' FOR UPDATE";

try {
	mysqli_begin_transaction($conn);
	$student_stmt = mysqli_prepare($conn, $student_sql);
	if ($role === 'teacher') {
		mysqli_stmt_bind_param($student_stmt, 'iii', $teacher_id, $student_id, $institute_id);
	} else {
		mysqli_stmt_bind_param($student_stmt, 'ii', $student_id, $institute_id);
	}
	mysqli_stmt_execute($student_stmt);
	$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt));
	mysqli_stmt_close($student_stmt);
	if (!$student || $student['course_fee'] === null) {
		throw new InvalidArgumentException('Student is not in your institute or has no assigned course.');
	}
	$due = max(0, (float) $student['course_fee'] - (float) $student['paid_amount']);
	if ((float) $amount > $due) {
		throw new InvalidArgumentException('Payment amount cannot exceed the outstanding course fee.');
	}

$stmt = $conn->prepare("
	INSERT INTO fees (student_id, course_id, amount, status, payment_mode)
	VALUES (?, ?, ?, 'paid', 'Cash')
");

if (!$stmt) {
	throw new Exception('Fee payment could not be prepared.');
}

$stmt->bind_param("iis", $student_id, $student['course_id'], $amount);

if (!$stmt->execute()) {
	throw new Exception('Fee payment could not be added.');
}
$fee_id = $stmt->insert_id;
$stmt->close();
mysqli_commit($conn);

echo json_encode([
	"status" => true,
	"message" => "Fee payment added successfully",
	"fee_id" => $fee_id
]);
} catch (Throwable $exception) {
	mysqli_rollback($conn);
	$is_validation_error = $exception instanceof InvalidArgumentException;
	if (!$is_validation_error) {
		error_log('Fee API insert failed: ' . $exception->getMessage());
	}
	http_response_code($is_validation_error ? 400 : 500);
	echo json_encode([
		"status" => false,
		"message" => $is_validation_error ? $exception->getMessage() : "Fee payment could not be added"
	]);
}
?>

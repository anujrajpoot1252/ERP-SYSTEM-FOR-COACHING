<?php
header("Content-Type: application/json;");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin']);
api_require_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
$parent_name = is_string($_POST['parent_name'] ?? null) ? trim($_POST['parent_name']) : '';
$parent_phone = is_string($_POST['parent_phone'] ?? null) ? trim($_POST['parent_phone']) : '';
$course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT) ?: 0;
$batch_id = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);

if (!$id || $id < 1 || $phone === '' || strlen($phone) > 15 || $parent_name === '' ||
    strlen($parent_name) > 100 || $parent_phone === '' || strlen($parent_phone) > 15) {
	echo json_encode([
		"status" => false,
		"message" => "Required fields are missing or invalid"
	]);
	exit;
}

$valid_course = $course_id === 0;
if ($course_id > 0) {
	$course_stmt = mysqli_prepare($conn, "SELECT id FROM course WHERE id = ? AND institute_id = ?");
	mysqli_stmt_bind_param($course_stmt, 'ii', $course_id, $institute_id);
	mysqli_stmt_execute($course_stmt);
	$valid_course = mysqli_stmt_get_result($course_stmt)->num_rows === 1;
	mysqli_stmt_close($course_stmt);
}
$valid_batch = $batch_id === 0;
if ($batch_id > 0 && $course_id > 0) {
	$batch_stmt = mysqli_prepare($conn, "SELECT id FROM batch WHERE id = ? AND course_id = ? AND institute_id = ?");
	mysqli_stmt_bind_param($batch_stmt, 'iii', $batch_id, $course_id, $institute_id);
	mysqli_stmt_execute($batch_stmt);
	$valid_batch = mysqli_stmt_get_result($batch_stmt)->num_rows === 1;
	mysqli_stmt_close($batch_stmt);
}
if (!$valid_course || !$valid_batch) {
	http_response_code(400);
	echo json_encode(["status" => false, "message" => "The selected course and batch are not valid for this institute."]);
	exit;
}
$stmt = $conn->prepare(
	"UPDATE student
	 SET phone = ?, parent_name = ?, parent_phone = ?,
	     course_id = NULLIF(?, 0), batch_id = NULLIF(?, 0)
	 WHERE id = ? AND institute_id = ?"
);

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare student update",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("sssiiii", $phone, $parent_name, $parent_phone, $course_id, $batch_id, $id, $institute_id);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Student update failed",
		"error" => $stmt->error
	]);
	exit;
}

if ($stmt->affected_rows === 0) {
	$check = $conn->prepare("SELECT id FROM student WHERE id = ? AND institute_id = ?");
	$check->bind_param("ii", $id, $institute_id);
	$check->execute();
	$check->store_result();

	if ($check->num_rows === 0) {
		echo json_encode([
			"status" => false,
			"message" => "Student not found"
		]);
		exit;
	}
}

echo json_encode([
	"status" => true,
	"message" => "Student updated successfully"
]);

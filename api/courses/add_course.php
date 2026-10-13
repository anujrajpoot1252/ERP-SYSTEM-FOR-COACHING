<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin']);
api_require_csrf();

$course_name = trim($_POST['course_name'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$fees = trim($_POST['fees'] ?? $_POST['fee'] ?? '');
$status = trim($_POST['status'] ?? 'active');

if ($course_name === '' || $duration === '' || $fees === '') {
	echo json_encode([
		"status" => false,
		"message" => "course_name, duration, and fees are required"
	]);
	exit;
}

if (strlen($course_name) > 100 || strlen($duration) > 50) {
	echo json_encode([
		"status" => false,
		"message" => "course_name must be at most 100 characters and duration at most 50 characters"
	]);
	exit;
}

if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $fees)) {
	echo json_encode([
		"status" => false,
		"message" => "fees must be a non-negative amount with up to 2 decimal places"
	]);
	exit;
}

if (!in_array($status, ['active', 'inactive'], true)) {
	echo json_encode([
		"status" => false,
		"message" => "status must be active or inactive"
	]);
	exit;
}

$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
if ($institute_id < 1) {
	http_response_code(403);
	echo json_encode(["status" => false, "message" => "A valid institute account is required."]);
	exit;
}

$stmt = $conn->prepare(
	"INSERT INTO course (institute_id, course_name, duration, fees, status)
	 VALUES (?, ?, ?, ?, ?)"
);

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare course creation",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("issss", $institute_id, $course_name, $duration, $fees, $status);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Course creation failed",
		"error" => $stmt->error
	]);
	exit;
}

echo json_encode([
	"status" => true,
	"message" => "Course added successfully",
	"course_id" => $stmt->insert_id
]);
?>
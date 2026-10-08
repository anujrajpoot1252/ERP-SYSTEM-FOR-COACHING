<?php
header("Content-Type: application/json");
include("../../config/db.php");

$course_name = trim($_POST['course_name'] ?? '');
$duration = trim($_POST['duration'] ?? '');
$fees = trim($_POST['fees'] ?? $_POST['fee'] ?? '');
$institute_id_input = trim($_POST['institute_id'] ?? '');
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

$institute_id = $institute_id_input === '' ? 1 : filter_var($institute_id_input, FILTER_VALIDATE_INT);
if ($institute_id === false || $institute_id < 1) {
	echo json_encode([
		"status" => false,
		"message" => "institute_id must be a positive integer"
	]);
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
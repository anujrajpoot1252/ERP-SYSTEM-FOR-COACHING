<?php
header("Content-Type: application/json;");
include("../../config/db.php");

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$phone = trim($_POST['phone'] ?? '');
$parent_name = trim($_POST['parent_name'] ?? '');
$parent_phone = trim($_POST['parent_phone'] ?? '');
$course_id = trim($_POST['course_id'] ?? '');

if (!$id || $phone === '' || $parent_name === '' || $parent_phone === '') {
	echo json_encode([
		"status" => false,
		"message" => "Required fields are missing or invalid"
	]);
	exit;
}

if ($course_id !== '' && !ctype_digit($course_id)) {
	echo json_encode([
		"status" => false,
		"message" => "Invalid course_id"
	]);
	exit;
}

$course_id = $course_id === '' ? null : (int) $course_id;
$stmt = $conn->prepare(
	"UPDATE student
	 SET phone = ?, parent_name = ?, parent_phone = ?, course_id = ?
	 WHERE id = ?"
);

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare student update",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("sssii", $phone, $parent_name, $parent_phone, $course_id, $id);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Student update failed",
		"error" => $stmt->error
	]);
	exit;
}

if ($stmt->affected_rows === 0) {
	$check = $conn->prepare("SELECT id FROM student WHERE id = ?");
	$check->bind_param("i", $id);
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

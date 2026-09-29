<?php
header("Content-Type: application/json");
include("../../config/db.php");

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
	echo json_encode([
		"status" => false,
		"message" => "A valid student id is required"
	]);
	exit;
}

$stmt = $conn->prepare("DELETE FROM student WHERE id = ?");

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare student deletion",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Student deletion failed",
		"error" => $stmt->error
	]);
	exit;
}

if ($stmt->affected_rows === 0) {
	echo json_encode([
		"status" => false,
		"message" => "Student not found"
	]);
	exit;
}

echo json_encode([
	"status" => true,
	"message" => "Student deleted successfully"
]);
?>
<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../includes/auth_check.php";

api_require_access(['admin']);
api_require_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);

if (!$id) {
	echo json_encode([
		"status" => false,
		"message" => "A valid student id is required"
	]);
	exit;
}

$stmt = $conn->prepare("
	DELETE u FROM users u
	INNER JOIN student s ON s.user_id = u.id
	WHERE s.id = ? AND s.institute_id = ? AND u.role = 'student'
");

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare student deletion",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("ii", $id, $institute_id);

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
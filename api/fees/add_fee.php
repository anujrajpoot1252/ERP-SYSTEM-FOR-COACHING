<?php
header("Content-Type: application/json");
include("../../config/db.php");

$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$amount = trim($_POST['amount'] ?? '');

if (!$student_id || $student_id < 1 || $amount === '') {
	echo json_encode([
		"status" => false,
		"message" => "student_id and amount are required"
	]);
	exit;
}

if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amount) || (float) $amount <= 0) {
	echo json_encode([
		"status" => false,
		"message" => "amount must be greater than zero with up to 2 decimal places"
	]);
	exit;
}

$stmt = $conn->prepare("INSERT INTO fees (student_id, amount) VALUES (?, ?)");

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare fee payment",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param("is", $student_id, $amount);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Fee payment could not be added",
		"error" => $stmt->error
	]);
	exit;
}

echo json_encode([
	"status" => true,
	"message" => "Fee payment added successfully",
	"fee_id" => $stmt->insert_id
]);
?>

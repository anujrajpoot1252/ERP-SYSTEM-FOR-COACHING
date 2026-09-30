<?php
header("Content-Type: application/json");
include("../../config/db.php");

$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$batch_id = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT);
$date = trim($_POST['date'] ?? '');
$status = strtolower(trim($_POST['status'] ?? ''));
$phone_number_parents = trim($_POST['phone_number_parents'] ?? '');
$marking = trim($_POST['marking'] ?? '');

$legacy_status_map = [
    'presence' => 'present',
    'absence' => 'absent'
];

if (!$student_id || $student_id < 1 || !$batch_id || $batch_id < 1 ||
    $date === '' || $status === '') {
    echo json_encode([
        "status" => false,
        "message" => "student_id, batch_id, date, and status are required"
    ]);
    exit;
}

if (isset($legacy_status_map[$status])) {
    $status = $legacy_status_map[$status];
}

$date_parts = explode('-', $date);
if (count($date_parts) !== 3 || !checkdate((int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[0]) ||
	!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
	echo json_encode([
		"status" => false,
		"message" => "date must be a valid date in YYYY-MM-DD format"
	]);
	exit;
}

if (!in_array($status, ['present', 'absent'], true)) {
    echo json_encode([
        "status" => false,
        "message" => "status must be either present, absent, presence, or absence"
    ]);
    exit;
}

if (strlen($phone_number_parents) > 50 || strlen($marking) > 20) {
	echo json_encode([
		"status" => false,
		"message" => "phone_number_parents must be at most 50 characters and marking at most 20 characters"
	]);
	exit;
}

$phone_number_parents = $phone_number_parents === '' ? null : $phone_number_parents;
$marking = $marking === '' ? null : $marking;
$stmt = $conn->prepare(
	"INSERT INTO attendance
	 (student_id, batch_id, `date`, status, phone_number_parents, marking)
	 VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to prepare attendance record",
		"error" => $conn->error
	]);
	exit;
}

$stmt->bind_param(
	"iissss",
	$student_id,
	$batch_id,
	$date,
	$status,
	$phone_number_parents,
	$marking
);

if (!$stmt->execute()) {
	echo json_encode([
		"status" => false,
		"message" => "Failed to mark attendance",
		"error" => $stmt->error
	]);
	exit;
}

echo json_encode([
	"status" => true,
	"message" => "Attendance marked successfully",
	"attendance_id" => $stmt->insert_id
]);
?>
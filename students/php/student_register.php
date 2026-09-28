<?php
header("Content-Type: application/json");
require "../../config/db.php";


$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

$name           = trim($data["name"] ?? '');
$phone          = trim($data["phone"] ?? '');
$email          = trim($data["email"] ?? '');
$password       = $data["password"] ?? '';
$course_id      = intval($data["course_id"] ?? 0);
$batch_id       = intval($data["batch_id"] ?? 0);
$admission_date = trim($data["admission_date"] ?? '');


if (empty($name) || empty($phone) || empty($email) || empty($password) || $course_id <= 0 || $batch_id <= 0 || empty($admission_date)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required and must be valid."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    exit;
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO student (name, Phone_number, Email, Password, course_id, batch_id, admission_date) VALUES (?, ?, ?, ?, ?, ?, ?)");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssiis", $name, $phone, $email, $hashed_password, $course_id, $batch_id, $admission_date);

    if (mysqli_stmt_execute($stmt)) {
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Student registered successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to register student."]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database query preparation failed."]);
}

mysqli_close($conn);
?>


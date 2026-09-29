<?php
header("Content-Type: application/json");
include("../../config/db.php");

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$role = 'student';

if ($name == '' || $email == '' || $password == '') {
    echo json_encode([
        "status" => false,
        "message" => "Name, email and password are required"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (name, email, password, role, institute_id, status)
     VALUES (?, ?, ?, ?, 1, 'active')"
);

$stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "User Registered",
        "user_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Registration Failed",
        "error" => $stmt->error
    ]);
}
?>
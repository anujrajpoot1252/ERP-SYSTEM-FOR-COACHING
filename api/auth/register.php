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
    $userId = $stmt->insert_id;
    $admNo = "ADM-" . date("Y") . "-" . str_pad($userId, 3, "0", STR_PAD_LEFT);
    $phone = $_POST['phone'] ?? '';
    $parentName = $_POST['parent_name'] ?? ('Parent of ' . $name);
    $parentPhone = $_POST['parent_phone'] ?? '';
    $courseId = !empty($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $batchId = !empty($_POST['batch_id']) ? intval($_POST['batch_id']) : null;

    $st_stmt = $conn->prepare("INSERT INTO student (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status) VALUES (?, 1, ?, ?, ?, ?, ?, ?, 'active')");
    if ($st_stmt) {
        $st_stmt->bind_param("issssii", $userId, $admNo, $phone, $parentName, $parentPhone, $courseId, $batchId);
        $st_stmt->execute();
        $st_stmt->close();
    }

    echo json_encode([
        "status" => true,
        "message" => "User and Student Profile Registered",
        "user_id" => $userId,
        "admission_no" => $admNo
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Registration Failed",
        "error" => $stmt->error
    ]);
}
?>
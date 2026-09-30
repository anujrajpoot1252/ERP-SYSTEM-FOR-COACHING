<?php
header("Content-Type: application/json");
include("../../config/db.php");

$user_id = $_POST['user_id'] ?? '';
$admission_no = $_POST['admission_no'] ?? '';
$phone = $_POST['phone'] ?? '';
$parent_name = $_POST['parent_name'] ?? '';
$parent_phone = $_POST['parent_phone'] ?? '';
$course_id = $_POST['course_id'] ?? null;
$batch_id = $_POST['batch_id'] ?? null;

if ($user_id == '' || $admission_no == '' || $phone == '' || 
    $parent_name == '' || $parent_phone == '') {

    echo json_encode([
        "status" => false,
        "message" => "Required fields are missing"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO student
    (user_id, institute_id, admission_no, phone, parent_name,
     parent_phone, course_id, batch_id, status)
    VALUES (?, 1, ?, ?, ?, ?, ?, ?, 'active')"
);

$stmt->bind_param(
    "issssii",
    $user_id,
    $admission_no,
    $phone,
    $parent_name,
    $parent_phone,
    $course_id,
    $batch_id
);

if ($stmt->execute()) {

    echo json_encode([
        "status" => true,
        "message" => "Student Added",
        "student_id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "status" => false,
        "message" => "Student Add Failed",
        "error" => $stmt->error
    ]);
}
?>
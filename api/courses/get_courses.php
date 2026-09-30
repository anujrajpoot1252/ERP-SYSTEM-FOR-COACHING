<?php
header("Content-Type: application/json");
include("../../config/db.php");

$result = mysqli_query(
    $conn,
    "SELECT id, institute_id, course_name, duration, fees, status
     FROM course
     ORDER BY id DESC"
);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch courses",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
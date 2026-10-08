<?php
require "../config/db.php";

$result = mysqli_query($conn, "SELECT id, course_name FROM course");

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
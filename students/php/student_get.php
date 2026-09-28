<?php
require "../../config/db.php";

$result = mysqli_query($conn, "SELECT * FROM student");

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
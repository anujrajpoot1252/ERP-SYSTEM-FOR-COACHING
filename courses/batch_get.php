<?php
require "../config/db.php";

$result = mysqli_query($conn, "SELECT id, name AS batch_name, name FROM batch");

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
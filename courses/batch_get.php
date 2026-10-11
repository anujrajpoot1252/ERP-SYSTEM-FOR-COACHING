<?php
require "../config/db.php";

$batch_col_check = @mysqli_query($conn, "SHOW COLUMNS FROM `batch` LIKE 'name'");
$col = ($batch_col_check && mysqli_num_rows($batch_col_check) > 0) ? "name" : "batch_name";
$result = mysqli_query($conn, "SELECT id, {$col} AS batch_name, {$col} AS name FROM batch");

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
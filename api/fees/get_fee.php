<?php
header("Content-Type: application/json");
include("../../config/db.php");

$query = "SELECT
            f.id,
            f.student_id,
            u.name AS student_name,
            s.admission_no,
            f.amount,
            f.created_at
          FROM fees AS f
          INNER JOIN student AS s ON s.id = f.student_id
          INNER JOIN users AS u ON u.id = s.user_id
          ORDER BY f.created_at DESC, f.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch fee payments",
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
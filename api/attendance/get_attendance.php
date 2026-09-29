<?php
header("Content-Type: application/json");
include("../../config/db.php");

$query = "SELECT
            a.id,
            a.student_id,
            u.name AS student_name,
            s.admission_no,
            s.phone AS student_phone,
            s.parent_name,
            s.parent_phone,
            a.batch_id,
            b.name AS batch_name,
            c.course_name,
            a.date,
            a.status,
            a.phone_number_parents,
            a.marking
          FROM attendance AS a
          LEFT JOIN student AS s ON s.id = a.student_id
          LEFT JOIN users AS u ON u.id = s.user_id
          LEFT JOIN batch AS b ON b.id = a.batch_id
          LEFT JOIN course AS c ON c.id = b.course_id
          ORDER BY a.date DESC, a.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch attendance",
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
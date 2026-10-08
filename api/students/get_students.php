<?php
header("Content-Type: application/json");
include("../../config/db.php");

$query = "SELECT
                        s.id,
                        s.user_id,
                        s.institute_id,
                        s.admission_no,
                        s.phone,
                        s.parent_name,
                        s.parent_phone,
                        s.course_id,
                        c.course_name,
                        s.batch_id,
                        b.name AS batch_name,
                        s.status,
                        s.created_at,
                        u.name AS student_name,
                        u.email
                    FROM student AS s
                    INNER JOIN users AS u ON u.id = s.user_id
                    LEFT JOIN course AS c ON c.id = s.course_id
                    LEFT JOIN batch AS b ON b.id = s.batch_id
                    ORDER BY s.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch students",
        "error" => mysqli_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data
]);
?>
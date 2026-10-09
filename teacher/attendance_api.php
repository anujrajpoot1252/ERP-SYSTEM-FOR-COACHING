<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $batchId = isset($_GET["batch_id"])
        ? (int) $_GET["batch_id"]
        : 0;

    $date = $_GET["date"] ?? "";

    if ($batchId <= 0 || !$date) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Batch and date are required"
        ]);

        exit;
    }

    /*
     * student table:
     * id, user_id, batch_id, admission_no...
     *
     * users table se name liya ja raha hai.
     */
    $sql = "
        SELECT
            s.id,
            u.name,
            s.admission_no,
            a.status
        FROM student s

        JOIN users u
            ON u.id = s.user_id

        LEFT JOIN attendance a
            ON a.student_id = s.id
            AND a.batch_id = ?
            AND a.date = ?

        WHERE s.batch_id = ?

        ORDER BY s.admission_no ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => $conn->error
        ]);

        exit;
    }

    $stmt->bind_param(
        "isi",
        $batchId,
        $date,
        $batchId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $students = [];

    while ($row = $result->fetch_assoc()) {

        $students[] = [
            "id" => (int) $row["id"],
            "name" => $row["name"],
            "roll_no" => $row["admission_no"],
            "present" => $row["status"] === null
                ? null
                : $row["status"] === "present"
        ];
    }

    $stmt->close();

    echo json_encode([
        "success" => true,
        "students" => $students
    ]);

    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $batchId = isset($data["batch_id"])
        ? (int) $data["batch_id"]
        : 0;

    $date = $data["date"] ?? "";

    $attendance = $data["attendance"] ?? [];

    if ($batchId <= 0 || !$date || !is_array($attendance)) {

        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "Invalid attendance data"
        ]);

        exit;
    }

    $conn->begin_transaction();

    try {

        /*
         * attendance table:
         * student_id
         * batch_id
         * date
         * status
         */

        $sql = "
            INSERT INTO attendance
            (
                student_id,
                batch_id,
                date,
                status
            )
            VALUES (?, ?, ?, ?)
<<<<<<< HEAD
=======
            ON DUPLICATE KEY UPDATE status = VALUES(status)
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        foreach ($attendance as $item) {

            $studentId = isset($item["student_id"])
                ? (int) $item["student_id"]
                : 0;

            $present = !empty($item["present"]);

            if ($studentId <= 0) {
                continue;
            }

            /*
             * Student ko verify karo
             */
            $checkSql = "
                SELECT id
                FROM student
                WHERE id = ?
                AND batch_id = ?
                LIMIT 1
            ";

            $checkStmt = $conn->prepare($checkSql);

            if (!$checkStmt) {
                throw new Exception($conn->error);
            }

            $checkStmt->bind_param(
                "ii",
                $studentId,
                $batchId
            );

            $checkStmt->execute();

            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows === 0) {
                $checkStmt->close();
                continue;
            }

            $checkStmt->close();

            $status = $present
                ? "present"
                : "absent";

            $stmt->bind_param(
                "iiss",
                $studentId,
                $batchId,
                $date,
                $status
            );

            $stmt->execute();
        }

        $stmt->close();

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Attendance saved successfully"
        ]);

    } catch (Throwable $e) {

        $conn->rollback();

        http_response_code(500);

        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }

    exit;
}


http_response_code(405);

echo json_encode([
    "success" => false,
    "message" => "Method not allowed"
]);
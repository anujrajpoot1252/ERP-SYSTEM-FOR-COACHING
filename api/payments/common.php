<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/razorpay.php';
require_once __DIR__ . '/../../includes/auth_check.php';

check_access(['student']);

function payment_json_response($status, $message, $data = []) {
    http_response_code($status);
    echo json_encode(array_merge(['status' => $status >= 200 && $status < 300, 'message' => $message], $data));
    exit;
}

function require_payment_post() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        payment_json_response(405, 'Method not allowed.');
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        payment_json_response(403, 'Your session token is invalid. Refresh the page and try again.');
    }
}

function student_payment_details($conn, $lock = false) {
    $query = "
        SELECT s.id AS student_id, s.course_id, u.name, u.email, c.fees AS course_fee
        FROM student s
        INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
        LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
        WHERE s.user_id = ? AND s.institute_id = ? AND s.status = 'active'
        " . ($lock ? 'FOR UPDATE' : '');
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new RuntimeException('Could not load the student fee details.');
    }
    $user_id = (int) $_SESSION['user_id'];
    $institute_id = (int) ($_SESSION['institute_id'] ?? 0);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$student) {
        throw new RuntimeException('Student profile was not found.');
    }
    return $student;
}

function student_fee_paid_paise($conn, $student_id, $course_id) {
    $stmt = mysqli_prepare($conn, "
        SELECT COALESCE(SUM(amount), 0) AS paid_amount
        FROM fees
        WHERE student_id = ? AND (course_id = ? OR course_id IS NULL) AND status = 'paid'
    ");
    if (!$stmt) {
        throw new RuntimeException('Could not load the student fee balance.');
    }
    mysqli_stmt_bind_param($stmt, 'ii', $student_id, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return amount_to_paise($result['paid_amount']);
}

<?php
/**
 * In-depth CRUD and Workflow Testing for ERP-SYSTEM-FOR-COACHING
 */
require_once __DIR__ . '/../config/db.php';

echo "========================================================\n";
echo "STARTING DETAILED FUNCTIONAL & SECURITY TESTING\n";
echo "========================================================\n\n";

$tests_passed = 0;
$tests_failed = 0;

function report($test_name, $passed, $details = '') {
    global $tests_passed, $tests_failed;
    if ($passed) {
        $tests_passed++;
        echo "[PASS] $test_name\n";
    } else {
        $tests_failed++;
        echo "[FAIL] $test_name\n";
        if ($details) echo "       Details: $details\n";
    }
}

// 1. Check Schema file syntax and constraints
$schema_file = __DIR__ . '/../schema/schema.sql';
$schema_content = file_get_contents($schema_file);
$duplicate_key_count = substr_count($schema_content, "UNIQUE KEY `unique_student_batch_date`");
if ($duplicate_key_count > 1) {
    report("Schema SQL uniqueness constraints", false, "Found $duplicate_key_count definitions of unique_student_batch_date in schema.sql (causes fatal crash on fresh install)");
} else {
    report("Schema SQL uniqueness constraints", true);
}

// 2. Check Security: Unauthenticated access to protected pages
$baseUrl = "http://127.0.0.1/ERP-SYSTEM-FOR-COACHING";
function fetchHttp($url, $cookie = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'redirect' => $redirect];
}

$protected_pages = [
    'admin/dashboard.php',
    'admin/students.php',
    'admin/teachers.php',
    'admin/batches.php',
    'admin/institutes.php',
    'teacher/dashboard.php',
    'teacher/attendance.php',
    'teacher/exams.php',
    'teacher/fees.php',
    'teacher/payment.php',
    'teacher/result.php',
    'student/dashboard.php',
    'student/attendance.php',
    'student/fees.php',
    'student/result.php',
    'student/marksheet.php'
];

foreach ($protected_pages as $page) {
    $res = fetchHttp("$baseUrl/$page");
    // Should redirect to login (302/303) or return 401/403
    $is_protected = in_array($res['code'], [301, 302, 303, 401, 403]);
    report("Auth Protection on $page", $is_protected, "HTTP {$res['code']} returned for guest (expected redirect to login)");
}

// 3. Test Teacher Batch & Attendance Flow
echo "\n--- Testing Attendance Recording ---\n";
// Let's check teacher ID 1 (Rahul Sharma), batch 1, student 1 (Aman Verma)
$test_date = '2026-10-18';
// Test attendance insert
$check_att = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = 1 AND batch_id = 1 AND date = '$test_date'");
if ($check_att && mysqli_num_rows($check_att) > 0) {
    mysqli_query($conn, "DELETE FROM attendance WHERE student_id = 1 AND batch_id = 1 AND date = '$test_date'");
}

$stmt = mysqli_prepare($conn, "INSERT INTO attendance (student_id, batch_id, date, status, phone_number_parents, marking) VALUES (?, ?, ?, 'present', '9811223344', 'manual')");
$s_id = 1;
$b_id = 1;
mysqli_stmt_bind_param($stmt, 'iis', $s_id, $b_id, $test_date);
$exec = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
report("Teacher Attendance Manual Insert", $exec);

// Test attendance duplicate constraint (re-insert should fail or be caught)
try {
    $stmt2 = mysqli_prepare($conn, "INSERT INTO attendance (student_id, batch_id, date, status) VALUES (1, 1, '$test_date', 'absent')");
    $exec2 = mysqli_stmt_execute($stmt2);
    report("Attendance Unique Key Protection against duplicate entry", false, "Duplicate insert succeeded without constraint error");
} catch (mysqli_sql_exception $e) {
    report("Attendance Unique Key Protection against duplicate entry", true, "Correctly caught: " . $e->getMessage());
}

// 4. Test Exams & Results Flow
echo "\n--- Testing Exam Creation & Result Flow ---\n";
// Create test exam
$stmt_exam = mysqli_prepare($conn, "INSERT INTO exams (institute_id, batch_name, exam_name, exam_date, total_marks) VALUES (1, 'JEE Morning Batch A', 'Unit Test 1', '$test_date', 100)");
$exam_created = mysqli_stmt_execute($stmt_exam);
$exam_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt_exam);
report("Create Exam in DB", $exam_created && $exam_id > 0);

// Record Result for student 1
$stmt_res = mysqli_prepare($conn, "INSERT INTO results (institute_id, exam_id, student_id, obtained_marks, total_marks, grade) VALUES (1, ?, 1, 88.5, 100, 'A')");
mysqli_stmt_bind_param($stmt_res, 'i', $exam_id);
$res_recorded = mysqli_stmt_execute($stmt_res);
$result_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt_res);
report("Record Student Result in DB", $res_recorded && $result_id > 0);

// Student Result Retrieval
$check_student_res = mysqli_query($conn, "SELECT r.*, e.exam_name FROM results r JOIN exams e ON r.exam_id = e.exam_id WHERE r.student_id = 1 AND r.exam_id = $exam_id");
$found_res = mysqli_fetch_assoc($check_student_res);
report("Student Result Retrieval", !empty($found_res) && $found_res['obtained_marks'] == 88.5);

// 5. Test Fee & Payment Flow
echo "\n--- Testing Fee Payment Flow ---\n";
$stmt_fee = mysqli_prepare($conn, "INSERT INTO fees (student_id, course_id, amount, status, payment_mode, ref_no, remarks) VALUES (1, 1, 5000.00, 'paid', 'Cash', 'REF-TEST-001', 'Test payment')");
$fee_recorded = mysqli_stmt_execute($stmt_fee);
$fee_id = mysqli_insert_id($conn);
mysqli_stmt_close($stmt_fee);
report("Record Fee Payment in DB", $fee_recorded && $fee_id > 0);

// Verify total paid amount calculation for Student 1
$calc = mysqli_query($conn, "SELECT SUM(amount) as total_paid FROM fees WHERE student_id = 1 AND status = 'paid'");
$total_paid = mysqli_fetch_assoc($calc)['total_paid'] ?? 0;
report("Student Fee Calculation", $total_paid >= 5000.00, "Calculated total: $total_paid");

// Clean up test data
mysqli_query($conn, "DELETE FROM results WHERE id = $result_id");
mysqli_query($conn, "DELETE FROM exams WHERE exam_id = $exam_id");
mysqli_query($conn, "DELETE FROM attendance WHERE student_id = 1 AND batch_id = 1 AND date = '$test_date'");
mysqli_query($conn, "DELETE FROM fees WHERE id = $fee_id");

echo "\n========================================================\n";
echo "SUMMARY: $tests_passed Passed, $tests_failed Failed\n";
echo "========================================================\n";


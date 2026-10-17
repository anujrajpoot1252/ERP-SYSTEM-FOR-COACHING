<?php
/**
 * Detailed API endpoints tester
 */
$baseUrl = "http://127.0.0.1/ERP-SYSTEM-FOR-COACHING";

function loginAndGetSession($email, $password) {
    global $baseUrl;
    $cookieFile = tempnam(sys_get_temp_dir(), "erp_api_");
    
    $ch = curl_init("$baseUrl/api/auth/login.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);
    $csrf = $data['csrf_token'] ?? '';
    
    return ['cookie' => $cookieFile, 'csrf' => $csrf];
}

function callApi($url, $method = 'GET', $data = [], $cookie = null, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_string($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

$admin = loginAndGetSession('admin@erp.com', 'admin123');
$teacher = loginAndGetSession('teacher@erp.com', 'teacher123');
$student = loginAndGetSession('student@erp.com', 'student123');

echo "========================================================\n";
echo "TESTING API ENDPOINTS\n";
echo "========================================================\n\n";

// 1. api/courses/get_courses.php
$r = callApi("$baseUrl/api/courses/get_courses.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && is_array($json);
echo "[api/courses/get_courses.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 2. courses/course_get.php
$r = callApi("$baseUrl/courses/course_get.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && is_array($json);
echo "[courses/course_get.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 3. courses/batch_get.php
$r = callApi("$baseUrl/courses/batch_get.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && is_array($json);
echo "[courses/batch_get.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 4. api/students/get_students.php
$r = callApi("$baseUrl/api/students/get_students.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['status'] ?? false) === true;
echo "[api/students/get_students.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 5. students/php/student_get.php
$r = callApi("$baseUrl/students/php/student_get.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && is_array($json);
echo "[students/php/student_get.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 6. api/attendance/get_attendance.php
$r = callApi("$baseUrl/api/attendance/get_attendance.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['status'] ?? false) === true;
echo "[api/attendance/get_attendance.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 7. api/fees/get_fee.php
$r = callApi("$baseUrl/api/fees/get_fee.php", 'GET', [], $admin['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && is_array($json);
echo "[api/fees/get_fee.php] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']})") . "\n";

// 8. teacher/attendance_api.php (GET batch 1)
$r = callApi("$baseUrl/teacher/attendance_api.php?batch_id=1&date=" . date('Y-m-d'), 'GET', [], $teacher['cookie']);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['success'] ?? false) === true;
echo "[teacher/attendance_api.php (GET)] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']}) - " . ($json['message'] ?? '')) . "\n";

// 9. api/attendance/mark_attendance.php (POST)
$postData = [
    'student_id' => 1,
    'batch_id' => 1,
    'date' => date('Y-m-d'),
    'status' => 'present',
    'csrf_token' => $admin['csrf']
];
$r = callApi("$baseUrl/api/attendance/mark_attendance.php", 'POST', $postData, $admin['cookie'], [
    "X-CSRF-Token: {$admin['csrf']}"
]);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['status'] ?? false) === true;
echo "[api/attendance/mark_attendance.php (POST)] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']}) - " . ($json['message'] ?? '')) . "\n";

// 10. Test JSON payload compatibility on mark_attendance.php
$jsonPayload = json_encode([
    'student_id' => 1,
    'batch_id' => 1,
    'date' => date('Y-m-d'),
    'status' => 'present',
    'csrf_token' => $admin['csrf']
]);
$r = callApi("$baseUrl/api/attendance/mark_attendance.php", 'POST', $jsonPayload, $admin['cookie'], [
    'Content-Type: application/json',
    "X-CSRF-Token: {$admin['csrf']}"
]);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['status'] ?? false) === true;
echo "[api/attendance/mark_attendance.php (JSON payload)] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']}) - " . ($json['message'] ?? '')) . "\n";

// 11. Test JSON payload on api/courses/add_course.php
$courseJsonPayload = json_encode([
    'course_name' => 'API Test Course',
    'duration' => '3 Months',
    'fees' => '15000',
    'csrf_token' => $admin['csrf']
]);
$r = callApi("$baseUrl/api/courses/add_course.php", 'POST', $courseJsonPayload, $admin['cookie'], [
    'Content-Type: application/json',
    "X-CSRF-Token: {$admin['csrf']}"
]);
$json = json_decode($r['body'], true);
$ok = $r['code'] === 200 && ($json['status'] ?? false) === true;
echo "[api/courses/add_course.php (JSON payload)] " . ($ok ? "PASS" : "FAIL (HTTP {$r['code']}) - " . ($json['message'] ?? $r['body'])) . "\n";


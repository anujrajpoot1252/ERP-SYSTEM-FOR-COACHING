<?php
/**
 * Automated Test Runner for ERP-SYSTEM-FOR-COACHING
 * Tests authentication, page rendering, error output, and APIs
 */

$baseUrl = "http://127.0.0.1/ERP-SYSTEM-FOR-COACHING";

function makeRequest($url, $method = 'GET', $data = [], $cookieJar = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $error = curl_error($ch);
    curl_close($ch);
    
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'headers' => $headerStr,
        'body' => $body,
        'error' => $error
    ];
}

function checkErrorsInBody($body) {
    $issues = [];
    $patterns = [
        'Fatal error' => '/Fatal error:(.*?)<br/i',
        'Warning' => '/<b>Warning<\/b>:(.*?)<br/i',
        'Notice' => '/<b>Notice<\/b>:(.*?)<br/i',
        'Parse error' => '/Parse error:(.*?)<br/i',
        'Deprecated' => '/<b>Deprecated<\/b>:(.*?)<br/i',
        'mysqli_sql_exception' => '/mysqli_sql_exception:(.*?)(?:\n|<)/i'
    ];
    foreach ($patterns as $type => $pattern) {
        if (preg_match_all($pattern, $body, $matches)) {
            foreach ($matches[1] as $m) {
                $issues[] = "$type: " . strip_tags(trim($m));
            }
        }
    }
    return $issues;
}

echo "========================================================\n";
echo "1. TESTING LOGIN FOR ALL ROLES\n";
echo "========================================================\n";

$roles = [
    'superadmin' => ['email' => 'superadmin@erp.com', 'password' => 'superadmin123', 'expected' => 'superadmin/dashboard.php'],
    'admin'      => ['email' => 'admin@erp.com',      'password' => 'admin123',      'expected' => 'admin/dashboard.php'],
    'teacher'    => ['email' => 'teacher@erp.com',    'password' => 'teacher123',    'expected' => 'teacher/dashboard.php'],
    'student'    => ['email' => 'student@erp.com',    'password' => 'student123',    'expected' => 'student/dashboard.php'],
];

$sessionCookies = [];

foreach ($roles as $role => $info) {
    $cookieFile = sys_get_temp_dir() . "/erp_cookie_{$role}.txt";
    if (file_exists($cookieFile)) unlink($cookieFile);
    $sessionCookies[$role] = $cookieFile;
    
    // Step 1: GET login page to get CSRF token
    $res = makeRequest("$baseUrl/login.php", 'GET', [], $cookieFile);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $res['body'], $csrfMatches);
    $csrfToken = $csrfMatches[1] ?? '';
    
    // Step 2: POST credentials
    $postData = [
        'email' => $info['email'],
        'password' => $info['password'],
        'csrf_token' => $csrfToken
    ];
    $loginRes = makeRequest("$baseUrl/login.php", 'POST', $postData, $cookieFile);
    
    $success = strpos($loginRes['effective_url'], $info['expected']) !== false;
    $issues = checkErrorsInBody($loginRes['body']);
    
    echo "[$role] Login: " . ($success ? "SUCCESS" : "FAILED") . " -> landed at: {$loginRes['effective_url']}\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}

echo "\n========================================================\n";
echo "2. TESTING SUPERADMIN PAGES\n";
echo "========================================================\n";
$superadminPages = [
    'superadmin/dashboard.php',
    'superadmin/add_institute.php',
    'superadmin/institutes.php',
    'superadmin/subscriptions.php'
];
foreach ($superadminPages as $page) {
    $res = makeRequest("$baseUrl/$page", 'GET', [], $sessionCookies['superadmin']);
    $issues = checkErrorsInBody($res['body']);
    echo "GET $page: HTTP {$res['code']}";
    if ($res['code'] !== 200) echo " (Url: {$res['effective_url']})";
    echo "\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}

echo "\n========================================================\n";
echo "3. TESTING ADMIN PAGES\n";
echo "========================================================\n";
$adminPages = [
    'admin/dashboard.php',
    'admin/institutes.php',
    'admin/batches.php',
    'admin/students.php',
    'admin/teachers.php'
];
foreach ($adminPages as $page) {
    $res = makeRequest("$baseUrl/$page", 'GET', [], $sessionCookies['admin']);
    $issues = checkErrorsInBody($res['body']);
    echo "GET $page: HTTP {$res['code']}";
    if ($res['code'] !== 200) echo " (Url: {$res['effective_url']})";
    echo "\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}

echo "\n========================================================\n";
echo "4. TESTING TEACHER PAGES\n";
echo "========================================================\n";
$teacherPages = [
    'teacher/dashboard.php',
    'teacher/attendance.php',
    'teacher/exams.php',
    'teacher/fees.php',
    'teacher/payment.php',
    'teacher/result.php'
];
foreach ($teacherPages as $page) {
    $res = makeRequest("$baseUrl/$page", 'GET', [], $sessionCookies['teacher']);
    $issues = checkErrorsInBody($res['body']);
    echo "GET $page: HTTP {$res['code']}";
    if ($res['code'] !== 200) echo " (Url: {$res['effective_url']})";
    echo "\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}

echo "\n========================================================\n";
echo "5. TESTING STUDENT PAGES\n";
echo "========================================================\n";
$studentPages = [
    'student/dashboard.php',
    'student/attendance.php',
    'student/fees.php',
    'student/marksheet.php',
    'student/result.php'
];
foreach ($studentPages as $page) {
    $res = makeRequest("$baseUrl/$page", 'GET', [], $sessionCookies['student']);
    $issues = checkErrorsInBody($res['body']);
    echo "GET $page: HTTP {$res['code']}";
    if ($res['code'] !== 200) echo " (Url: {$res['effective_url']})";
    echo "\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}

echo "\n========================================================\n";
echo "6. TESTING PUBLIC / OTHER PAGES\n";
echo "========================================================\n";
$otherPages = [
    'index.php',
    'login.php',
    'logout.php',
    'public/auth/login.html',
    'public/auth/login.php',
    'public/institude/institute_register.php',
    'courses/batch_get.php',
    'courses/course_get.php',
    'students/php/student_get.php',
    'students/php/student_register.php'
];
foreach ($otherPages as $page) {
    $res = makeRequest("$baseUrl/$page", 'GET', []);
    $issues = checkErrorsInBody($res['body']);
    echo "GET $page: HTTP {$res['code']} (Url: {$res['effective_url']})\n";
    if (!empty($issues)) {
        foreach ($issues as $err) echo "    [!] $err\n";
    }
}


<?php
$baseUrl = "http://127.0.0.1/ERP-SYSTEM-FOR-COACHING";

function testLogin($email, $password, $expectedRole, $expectedUrl) {
    global $baseUrl;
    $cookie = tempnam(sys_get_temp_dir(), "cookie_test_");
    
    // GET login page for CSRF
    $ch = curl_init("$baseUrl/login.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    $html = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
    $csrf = $m[1] ?? '';
    
    // POST login
    $ch = curl_init("$baseUrl/login.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password,
        'csrf_token' => $csrf
    ]));
    $res = curl_exec($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $ok = (strpos($effectiveUrl, $expectedUrl) !== false);
    echo "[$expectedRole: $email] Login test: " . ($ok ? "PASS" : "FAIL") . " -> Landed at: $effectiveUrl\n";
    return $ok;
}

echo "=== TESTING NEW ROLES LOGIN ===\n";
testLogin('anuj@career.com', 'anuj123', 'ADMIN', 'admin/dashboard.php');
testLogin('kunal@career.com', 'kunal123', 'TEACHER', 'teacher/dashboard.php');

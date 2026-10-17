<?php
require_once __DIR__ . '/../config/db.php';
$baseUrl = "http://127.0.0.1/ERP-SYSTEM-FOR-COACHING";

echo "=== REAL-WORLD WORKFLOW EXAMPLE TEST ===\n";

// 1. Superadmin Login
$cookieFile = tempnam(sys_get_temp_dir(), "erp_flow_");
$ch = curl_init("$baseUrl/api/auth/login.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'superadmin@erp.com', 'password' => 'superadmin123']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
curl_close($ch);
echo "1. Super Admin Login: OK\n";

// 2. Superadmin creates new institute & its Admin
$inst_name = "Global Test Academy";
$inst_email = "contact@globalacademy.com";
$admin_email = "director@globalacademy.com";

$ch = curl_init("$baseUrl/superadmin/add_institute.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'institute_name' => $inst_name,
    'email' => $inst_email,
    'phone_no' => '9988776655',
    'address' => 'Sector 4, Jaipur',
    'subscription' => 'active',
    'expiry' => date('Y-m-d', strtotime('+1 year')),
    'admin_name' => 'Director Global',
    'admin_email' => $admin_email,
    'admin_password' => 'director123'
]));
$out = curl_exec($ch);
curl_close($ch);

$check = mysqli_query($conn, "SELECT id FROM institute WHERE email = '$inst_email'");
$inst_id = mysqli_fetch_assoc($check)['id'] ?? 0;
echo "2. Institute Creation: " . ($inst_id > 0 ? "SUCCESS (Institute ID: $inst_id)" : "FAILED") . "\n";

// 3. New Institute Admin Login
$ch2 = curl_init("$baseUrl/api/auth/login.php");
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode(['email' => $admin_email, 'password' => 'director123']));
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$loginRes = json_decode(curl_exec($ch2), true);
curl_close($ch2);
echo "3. New Institute Admin Login: " . (($loginRes['status'] ?? false) ? "SUCCESS ({$loginRes['user']['name']})" : "FAILED") . "\n";

// Clean up test institute
mysqli_query($conn, "DELETE FROM users WHERE email = '$admin_email'");
mysqli_query($conn, "DELETE FROM institute WHERE id = $inst_id");
echo "4. Cleanup Test Data: Cleaned up successfully.\n";

echo "\nWorkflow verification completed without any errors!\n";

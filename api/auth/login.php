<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
$email = is_string($input['email'] ?? null) ? trim($input['email']) : '';
$password = is_string($input['password'] ?? null) ? $input['password'] : '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255 || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Email and password are required.']);
    exit;
}

$stmt = mysqli_prepare($conn, "
    SELECT id, name, email, password, role, institute_id, status
    FROM users WHERE email = ? LIMIT 1
");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Invalid email or password.']);
    exit;
}

session_regenerate_id(true);
unset($_SESSION['csrf_token']);
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['institute_id'] = (int) $user['institute_id'];
unset($user['password']);

echo json_encode([
    'status' => true,
    'message' => 'Login successful.',
    'user' => $user,
    'csrf_token' => request_csrf_token()
]);

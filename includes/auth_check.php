<?php
// Session protection helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function request_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return is_string($token) &&
        isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}

function authenticated_user_is_active() {
    global $conn;
    $user_id = (int) ($_SESSION['user_id'] ?? 0);
    if ($user_id < 1 || !isset($conn)) {
        return false;
    }

    $stmt = mysqli_prepare($conn, "
        SELECT role, institute_id
        FROM users
        WHERE id = ? AND status = 'active'
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    $valid = $user &&
        $user['role'] === ($_SESSION['role'] ?? '') &&
        (int) $user['institute_id'] === (int) ($_SESSION['institute_id'] ?? 0);
    if (!$valid) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    return (bool) $valid;
}

function api_require_access($allowed_roles) {
    header('Content-Type: application/json');
    if (!authenticated_user_is_active()) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Authentication is required.']);
        exit;
    }
    if (!in_array($_SESSION['role'] ?? '', $allowed_roles, true)) {
        http_response_code(403);
        echo json_encode(['status' => false, 'message' => 'You are not allowed to perform this action.']);
        exit;
    }
}

function api_require_csrf($token = null) {
    if ($token === null) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    }
    if (!validate_csrf_token($token)) {
        http_response_code(403);
        echo json_encode(['status' => false, 'message' => 'Your session token is invalid. Refresh and try again.']);
        exit;
    }
}

function check_access($allowed_roles = []) {
    $role = $_SESSION['role'] ?? '';
    if (!authenticated_user_is_active() || !in_array($role, ['admin', 'superadmin', 'teacher', 'student'], true)) {
        header('Location: ' . app_url('login.php'));
        exit;
    }

    if (!empty($allowed_roles)) {
        if (!in_array($role, $allowed_roles, true)) {
            // Redirect to appropriate dashboard if role does not match
            switch ($role) {
                case 'superadmin':
                    header('Location: ' . app_url('superadmin/dashboard.php'));
                    break;
                case 'admin':
                    header('Location: ' . app_url('admin/dashboard.php'));
                    break;
                case 'teacher':
                    header('Location: ' . app_url('teacher/dashboard.php'));
                    break;
                case 'student':
                    header('Location: ' . app_url('student/dashboard.php'));
                    break;
                default:
                    header('Location: ' . app_url('login.php'));
            }
            exit;
        }
    }
}
?>

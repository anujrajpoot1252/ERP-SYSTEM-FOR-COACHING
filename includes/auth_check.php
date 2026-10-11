<?php
// Session protection helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";

function check_access($allowed_roles = []) {
    $base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: " . $base . "/login.php");
        exit;
    }

    if (!empty($allowed_roles)) {
        if (!in_array($_SESSION['role'], $allowed_roles)) {
            // Redirect to appropriate dashboard if role does not match
            switch ($_SESSION['role']) {
                case 'admin':
                case 'superadmin':
                    header("Location: " . $base . "/admin/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: " . $base . "/teacher/dashboard.php");
                    break;
                case 'student':
                    header("Location: " . $base . "/student/dashboard.php");
                    break;
                default:
                    header("Location: " . $base . "/login.php");
            }
            exit;
        }
    }
}
?>

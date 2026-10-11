<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/api.php";

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'superadmin':
            header("Location: $base/admin/dashboard.php");
            exit;
        case 'teacher':
            header("Location: $base/teacher/dashboard.php");
            exit;
        case 'student':
            header("Location: $base/student/dashboard.php");
            exit;
        default:
            header("Location: $base/login.php");
            exit;
    }
} else {
    header("Location: $base/login.php");
    exit;
}
?>
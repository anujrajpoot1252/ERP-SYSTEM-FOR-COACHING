<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/api.php";

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'superadmin':
            header("Location: /ERP-SYSTEM-FOR-COACHING/admin/dashboard.php");
            exit;
        case 'teacher':
            header("Location: /ERP-SYSTEM-FOR-COACHING/teacher/dashboard.php");
            exit;
        case 'student':
            header("Location: /ERP-SYSTEM-FOR-COACHING/student/dashboard.php");
            exit;
    }
} else {
    header("Location: /ERP-SYSTEM-FOR-COACHING/login.php");
    exit;
}
?>
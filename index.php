<?php
require_once __DIR__ . "/config/db.php";

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'superadmin':
            header("Location: /ERP/admin/dashboard.php");
            exit;
        case 'teacher':
            header("Location: /ERP/teacher/dashboard.php");
            exit;
        case 'student':
            header("Location: /ERP/student/dashboard.php");
            exit;
    }
} else {
    header("Location: /ERP/login.php");
    exit;
}
?>
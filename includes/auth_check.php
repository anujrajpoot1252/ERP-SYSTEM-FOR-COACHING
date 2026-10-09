<?php
// Session protection helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_access($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
<<<<<<< HEAD
        header("Location: /ERP/login.php");
=======
        header("Location: /ERP-SYSTEM-FOR-COACHING/login.php");
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
        exit;
    }

    if (!empty($allowed_roles)) {
        if (!in_array($_SESSION['role'], $allowed_roles)) {
            // Redirect to appropriate dashboard if role does not match
            switch ($_SESSION['role']) {
                case 'admin':
                case 'superadmin':
<<<<<<< HEAD
                    header("Location: /ERP/admin/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: /ERP/teacher/dashboard.php");
                    break;
                case 'student':
                    header("Location: /ERP/student/dashboard.php");
                    break;
                default:
                    header("Location: /ERP/login.php");
=======
                    header("Location: /ERP-SYSTEM-FOR-COACHING/admin/dashboard.php");
                    break;
                case 'teacher':
                    header("Location: /ERP-SYSTEM-FOR-COACHING/teacher/dashboard.php");
                    break;
                case 'student':
                    header("Location: /ERP-SYSTEM-FOR-COACHING/student/dashboard.php");
                    break;
                default:
                    header("Location: /ERP-SYSTEM-FOR-COACHING/login.php");
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
            }
            exit;
        }
    }
}
?>

<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/includes/auth_check.php";

$error = "";
$csrf_token = request_csrf_token();

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

// Redirect if already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    switch ($_SESSION['role'] ?? '') {
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
            $_SESSION = [];
            session_destroy();
            header("Location: /ERP-SYSTEM-FOR-COACHING/login.php");
            exit;
    }
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $submitted_csrf = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';

    if (!validate_csrf_token($submitted_csrf)) {
        $error = "Your form session expired. Please reload the page and try again.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = "Please enter both Email and Password.";
    } else {
        $stmt = mysqli_prepare($conn, "
            SELECT id, name, email, password, role, institute_id, status
            FROM users WHERE email = ? LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user['status'] !== 'active') {
                $error = "Invalid email or password.";
            } elseif (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['institute_id'] = $user['institute_id'];

                // Redirect based on role
                if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                    header("Location: $base/admin/dashboard.php");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: $base/teacher/dashboard.php");
                } elseif ($user['role'] === 'student') {
                    header("Location: $base/student/dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            mysqli_stmt_close($stmt);
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Portal - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <h2>Coaching ERP</h2>
            <p>Enter your credentials to login</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-icon">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="e.g. admin@erp.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>
    </div>
</div>

</body>
</html>

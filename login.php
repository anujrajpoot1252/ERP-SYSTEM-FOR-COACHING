<?php
require_once __DIR__ . "/config/db.php";

$error = "";

// Redirect if already logged in
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
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both Email and Password.";
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($user['status'] !== 'active') {
                $error = "Your account is inactive. Please contact admin.";
            } elseif (password_verify($password, $user['password'])) {
                // Set Session Data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['institute_id'] = $user['institute_id'];

                // Redirect based on role
                if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                    header("Location: /ERP/admin/dashboard.php");
                } elseif ($user['role'] === 'teacher') {
                    header("Location: /ERP/teacher/dashboard.php");
                } elseif ($user['role'] === 'student') {
                    header("Location: /ERP/student/dashboard.php");
                }
                exit;
            } else {
                $error = "Incorrect Password. Please try again.";
            }
        } else {
            $error = "No account found with this email address.";
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
    <link rel="stylesheet" href="/ERP/assets/css/style.css">
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

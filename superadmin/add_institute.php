<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";
check_access(['superadmin']);

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institute_name = trim($_POST['institute_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone_no       = trim($_POST['phone_no'] ?? '');
    $address        = trim($_POST['address'] ?? '');
    $subscription   = in_array($_POST['subscription'] ?? '', ['active', 'expired']) ? $_POST['subscription'] : 'active';
    $expiry         = !empty($_POST['expiry']) ? trim($_POST['expiry']) : date('Y-m-d', strtotime('+1 year'));

    $admin_name     = trim($_POST['admin_name'] ?? '');
    $admin_email    = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';

    // Simple validation
    if (empty($institute_name) || empty($email) || empty($phone_no) || empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter valid email addresses.";
    } elseif (strlen($admin_password) < 6) {
        $error = "Admin password must be at least 6 characters.";
    } else {
        // Check if admin email already exists
        $check_user = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check_user, 's', $admin_email);
        mysqli_stmt_execute($check_user);
        $user_exists = mysqli_stmt_get_result($check_user)->num_rows > 0;
        mysqli_stmt_close($check_user);

        if ($user_exists) {
            $error = "An account with the administrator email already exists.";
        } else {
            try {
                mysqli_begin_transaction($conn);

                // 1. Insert Institute
                $stmt_inst = mysqli_prepare($conn, "
                    INSERT INTO institute (name, email, phone_no, address, subscription, expiry)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                mysqli_stmt_bind_param($stmt_inst, 'ssssss', $institute_name, $email, $phone_no, $address, $subscription, $expiry);
                mysqli_stmt_execute($stmt_inst);
                $new_institute_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_inst);

                // 2. Insert Admin User
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt_user = mysqli_prepare($conn, "
                    INSERT INTO users (name, email, password, role, institute_id, status)
                    VALUES (?, ?, ?, 'admin', ?, 'active')
                ");
                mysqli_stmt_bind_param($stmt_user, 'sssi', $admin_name, $admin_email, $hashed_password, $new_institute_id);
                mysqli_stmt_execute($stmt_user);
                mysqli_stmt_close($stmt_user);

                mysqli_commit($conn);
                $message = "Institute '$institute_name' and its Administrator account created successfully!";
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = "Failed to create institute: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Institute - Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title i {
            color: var(--primary);
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }
        .btn-secondary {
            padding: 10px 20px;
            background-color: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>ERP Super Admin</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= $base ?>/superadmin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            </li>
            <li>
                <a href="<?= $base ?>/superadmin/institutes.php"><i class="fa-solid fa-building-columns"></i> <span>Institutes</span></a>
            </li>
            <li class="active">
                <a href="<?= $base ?>/superadmin/add_institute.php"><i class="fa-solid fa-plus-circle"></i> <span>Add Institute</span></a>
            </li>
            <li>
                <a href="<?= $base ?>/superadmin/subscriptions.php"><i class="fa-solid fa-credit-card"></i> <span>Subscriptions</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="<?= $base ?>/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h2>Add New Institute</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Super Admin') ?></strong>
            </div>
        </div>

        <div class="page-body">
            <?php if (!empty($message)): ?>
                <div class="alert-success" style="padding: 14px; background: #dcfce7; color: #16a34a; border-radius: 6px; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" style="padding: 14px; background: #fee2e2; color: #dc2626; border-radius: 6px; margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form action="add_institute.php" method="POST">
                    
                    <!-- Institute Details -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-school"></i> Institute Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Institute Name *</label>
                            <input type="text" name="institute_name" class="form-control" placeholder="e.g. Apex Academy" required value="<?= htmlspecialchars($_POST['institute_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Official Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. contact@apex.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone_no" class="form-control" placeholder="e.g. 9876543210" required value="<?= htmlspecialchars($_POST['phone_no'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. 123 Main St, City" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                        </div>
                    </div>

                    <!-- Subscription / Plan -->
                    <div class="form-section-title" style="margin-top: 24px;">
                        <i class="fa-solid fa-tag"></i> Subscription & Validity
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Subscription Status</label>
                            <select name="subscription" class="form-control">
                                <option value="active" <?= (($_POST['subscription'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="expired" <?= (($_POST['subscription'] ?? '') === 'expired') ? 'selected' : '' ?>>Expired</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Subscription Expiry Date</label>
                            <input type="date" name="expiry" class="form-control" value="<?= htmlspecialchars($_POST['expiry'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                        </div>
                    </div>

                    <!-- Initial Admin Credentials -->
                    <div class="form-section-title" style="margin-top: 24px;">
                        <i class="fa-solid fa-user-shield"></i> Institute Administrator Account
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Admin Full Name *</label>
                            <input type="text" name="admin_name" class="form-control" placeholder="e.g. Ramesh Sharma" required value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Admin Login Email *</label>
                            <input type="email" name="admin_email" class="form-control" placeholder="e.g. ramesh@apex.com" required value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Admin Login Password *</label>
                            <input type="password" name="admin_password" class="form-control" placeholder="Create password (min 6 chars)" required>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <a href="<?= $base ?>/superadmin/institutes.php" class="btn-secondary">View All Institutes</a>
                        <button type="submit" class="btn-primary" style="width: auto;">
                            <i class="fa-solid fa-plus"></i> Save Institute & Create Admin
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
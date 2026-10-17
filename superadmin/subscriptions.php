<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";
check_access(['superadmin']);

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

$message = "";
$error = "";

// Handle status change or renewal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_subscription') {
    $inst_id = (int)($_POST['institute_id'] ?? 0);
    $new_status = in_array($_POST['status'] ?? '', ['active', 'expired']) ? $_POST['status'] : 'active';
    $new_expiry = !empty($_POST['expiry']) ? trim($_POST['expiry']) : null;

    if ($inst_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE institute SET subscription = ?, expiry = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $new_status, $new_expiry, $inst_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Subscription updated successfully.";
        } else {
            $error = "Failed to update subscription: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch stats
$active_sub = 0;
$expired_sub = 0;
$stats_q = mysqli_query($conn, "SELECT SUM(subscription = 'active') as active_cnt, SUM(subscription = 'expired') as exp_cnt FROM institute");
if ($stats_q && $r = mysqli_fetch_assoc($stats_q)) {
    $active_sub = (int)($r['active_cnt'] ?? 0);
    $expired_sub = (int)($r['exp_cnt'] ?? 0);
}

// Fetch all institutes
$institutes_list = mysqli_query($conn, "
    SELECT i.*, u.name as admin_name, u.email as admin_email
    FROM institute i
    LEFT JOIN users u ON u.institute_id = i.id AND u.role = 'admin'
    ORDER BY i.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <style>
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .pill-active { background: #dcfce7; color: #16a34a; }
        .pill-expired { background: #fee2e2; color: #dc2626; }
        .inline-form {
            display: flex;
            align-items: center;
            gap: 6px;
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
            <li>
                <a href="<?= $base ?>/superadmin/add_institute.php"><i class="fa-solid fa-plus-circle"></i> <span>Add Institute</span></a>
            </li>
            <li class="active">
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
            <h2>Institute Subscriptions</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Super Admin') ?></strong>
            </div>
        </div>

        <div class="page-body">
            <?php if (!empty($message)): ?>
                <div class="alert-success" style="padding: 12px; background: #dcfce7; color: #16a34a; border-radius: 6px; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" style="padding: 12px; background: #fee2e2; color: #dc2626; border-radius: 6px; margin-bottom: 16px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Metric Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="info">
                        <h3><?= $active_sub ?></h3>
                        <p>Active Subscriptions</p>
                    </div>
                    <div class="icon bg-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="info">
                        <h3><?= $expired_sub ?></h3>
                        <p>Expired / Overdue</p>
                    </div>
                    <div class="icon bg-orange">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-file-invoice-dollar"></i> Manage Validity & Subscriptions</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Institute Code</th>
                                <th>Institute Name</th>
                                <th>Admin In-Charge</th>
                                <th>Current Status</th>
                                <th>Expiry Date</th>
                                <th style="text-align: right;">Update Subscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($institutes_list && mysqli_num_rows($institutes_list) > 0): ?>
                                <?php while ($inst = mysqli_fetch_assoc($institutes_list)): ?>
                                    <tr>
                                        <td><strong>INS-<?= str_pad($inst['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td><strong><?= htmlspecialchars($inst['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($inst['admin_name'] ?? 'Not Assigned') ?></td>
                                        <td>
                                            <span class="status-pill <?= ($inst['subscription'] === 'active') ? 'pill-active' : 'pill-expired' ?>">
                                                <?= ucfirst(htmlspecialchars($inst['subscription'] ?? 'active')) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($inst['expiry'] ?? 'No Expiry') ?></td>
                                        <td style="text-align: right;">
                                            <form method="POST" action="subscriptions.php" class="inline-form" style="justify-content: flex-end;">
                                                <input type="hidden" name="action" value="update_subscription">
                                                <input type="hidden" name="institute_id" value="<?= $inst['id'] ?>">
                                                <select name="status" class="form-control" style="width: auto; padding: 4px 8px; font-size: 13px;">
                                                    <option value="active" <?= ($inst['subscription'] === 'active') ? 'selected' : '' ?>>Active</option>
                                                    <option value="expired" <?= ($inst['subscription'] === 'expired') ? 'selected' : '' ?>>Expired</option>
                                                </select>
                                                <input type="date" name="expiry" value="<?= htmlspecialchars($inst['expiry'] ?? '') ?>" class="form-control" style="width: auto; padding: 4px 8px; font-size: 13px;">
                                                <button type="submit" class="btn-primary" style="width: auto; padding: 5px 12px; font-size: 13px;">
                                                    Update
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 20px; color:#94a3b8;">No institutes found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
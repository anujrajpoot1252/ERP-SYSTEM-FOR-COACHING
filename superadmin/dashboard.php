<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";
check_access(['superadmin']);

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

// Fetch real institute metrics from MySQL
$total_institutes = 0;
$active_institutes = 0;
$expired_institutes = 0;

$stats_q = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(subscription = 'active') as active_count,
        SUM(subscription = 'expired') as expired_count
    FROM institute
");
if ($stats_q && $row = mysqli_fetch_assoc($stats_q)) {
    $total_institutes = (int) ($row['total'] ?? 0);
    $active_institutes = (int) ($row['active_count'] ?? 0);
    $expired_institutes = (int) ($row['expired_count'] ?? 0);
}

// Fetch live institutes with their assigned admin
$institutes_list = mysqli_query($conn, "
    SELECT i.*, u.name as admin_name, u.email as admin_email
    FROM institute i
    LEFT JOIN users u ON u.institute_id = i.id AND u.role = 'admin'
    ORDER BY i.id DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - ERP Admin</title>
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
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>ERP Super Admin</span>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <a href="<?= $base ?>/superadmin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            </li>
            <li>
                <a href="<?= $base ?>/superadmin/institutes.php"><i class="fa-solid fa-building-columns"></i> <span>Institutes</span></a>
            </li>
            <li>
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

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="top-navbar">
            <h2>Super Admin Overview</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Super Admin') ?></strong>
            </div>
        </div>

        <div class="page-body">
            <!-- Metric Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="info">
                        <h3><?= $total_institutes ?></h3>
                        <p>Total Institutes</p>
                    </div>
                    <div class="icon bg-blue">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="info">
                        <h3><?= $active_institutes ?></h3>
                        <p>Active Subscriptions</p>
                    </div>
                    <div class="icon bg-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="info">
                        <h3><?= $expired_institutes ?></h3>
                        <p>Expired</p>
                    </div>
                    <div class="icon bg-orange">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <!-- Institutes Directory Table -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-building-columns"></i> Registered Institutes</span>
                    <a href="<?= $base ?>/superadmin/add_institute.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">
                        <i class="fa-solid fa-plus"></i> Add Institute
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Institute Name</th>
                                <th>Admin Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($institutes_list && mysqli_num_rows($institutes_list) > 0): ?>
                                <?php while ($inst = mysqli_fetch_assoc($institutes_list)): ?>
                                    <tr>
                                        <td><strong>INS-<?= str_pad($inst['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td><?= htmlspecialchars($inst['name']) ?></td>
                                        <td><?= htmlspecialchars($inst['admin_name'] ?? 'Not Assigned') ?></td>
                                        <td><?= htmlspecialchars($inst['email']) ?></td>
                                        <td><?= htmlspecialchars($inst['phone_no']) ?></td>
                                        <td><?= htmlspecialchars($inst['expiry'] ?? 'Lifetime') ?></td>
                                        <td>
                                            <span class="status-pill <?= ($inst['subscription'] === 'active') ? 'pill-active' : 'pill-expired' ?>">
                                                <?= ucfirst(htmlspecialchars($inst['subscription'] ?? 'active')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 20px; color:#94a3b8;">No institutes registered yet.</td>
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
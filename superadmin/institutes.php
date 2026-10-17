<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";
check_access(['superadmin']);

$base = defined('APP_PATH') ? APP_PATH : ((isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/ERP-SYSTEM-FOR-COACHING') === 0) ? '/ERP-SYSTEM-FOR-COACHING' : '');

$message = "";
$error = "";

// Handle Delete Institute if requested
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id > 1) { // Prevent deleting default primary institute ID 1
        $del_stmt = mysqli_prepare($conn, "DELETE FROM institute WHERE id = ?");
        mysqli_stmt_bind_param($del_stmt, 'i', $del_id);
        if (mysqli_stmt_execute($del_stmt)) {
            $message = "Institute deleted successfully.";
        } else {
            $error = "Failed to delete institute: " . mysqli_error($conn);
        }
        mysqli_stmt_close($del_stmt);
    } else {
        $error = "Default primary institute cannot be deleted.";
    }
}

// Fetch all institutes
$search = trim($_GET['search'] ?? '');
$sql = "
    SELECT i.*, u.name as admin_name, u.email as admin_email
    FROM institute i
    LEFT JOIN users u ON u.institute_id = i.id AND u.role = 'admin'
";
if ($search !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " WHERE i.name LIKE '%$safe_search%' OR i.email LIKE '%$safe_search%' OR u.name LIKE '%$safe_search%'";
}
$sql .= " ORDER BY i.id DESC";
$institutes = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutes - ERP Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
    <style>
        .filter-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-input-wrap {
            position: relative;
            min-width: 280px;
            display: flex;
            gap: 8px;
        }
        .search-input-wrap input {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
        }
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .pill-active { background: #dcfce7; color: #16a34a; }
        .pill-expired { background: #fee2e2; color: #dc2626; }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-danger { background: #ef4444; color: #fff; border: none; }
        .btn-danger:hover { background: #dc2626; }
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
            <li>
                <a href="<?= $base ?>/superadmin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            </li>
            <li class="active">
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
            <h2>All Registered Institutes</h2>
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

            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-building-columns"></i> Institutes Directory</span>
                    <a href="<?= $base ?>/superadmin/add_institute.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">
                        <i class="fa-solid fa-plus"></i> Add New Institute
                    </a>
                </div>

                <!-- Filters & Search Toolbar -->
                <form method="GET" action="institutes.php" class="filter-toolbar">
                    <div class="search-input-wrap">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by institute name, email or admin...">
                        <button type="submit" class="btn-primary" style="width: auto; padding: 8px 14px;">Search</button>
                    </div>
                </form>

                <!-- Scrollable Table Wrapper -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Institute Code</th>
                                <th>Institute Name</th>
                                <th>Admin In-Charge</th>
                                <th>Contact Email</th>
                                <th>Phone</th>
                                <th>Subscription</th>
                                <th>Expiry Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($institutes && mysqli_num_rows($institutes) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($institutes)): ?>
                                    <tr>
                                        <td><strong>INS-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['admin_name'] ?? 'Not Assigned') ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phone_no']) ?></td>
                                        <td>
                                            <span class="status-pill <?= ($row['subscription'] === 'active') ? 'pill-active' : 'pill-expired' ?>">
                                                <?= ucfirst(htmlspecialchars($row['subscription'] ?? 'active')) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['expiry'] ?? 'Lifetime') ?></td>
                                        <td style="text-align: right;">
                                            <?php if ($row['id'] > 1): ?>
                                                <a href="institutes.php?delete=<?= $row['id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this institute?');">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: #94a3b8;">Primary</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 25px; color: #94a3b8;">No institute records found.</td>
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - ERP Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP-SYSTEM-FOR-COACHING/assets/css/style.css">
    <style>
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .pill-active { background: #dcfce7; color: #16a34a; }
        .pill-trial { background: #ffedd5; color: #ea580c; }
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
                <a href="#"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
            </li>
            <li>
                <a href="#"><i class="fa-solid fa-building-columns"></i><span>Institutes</span></a>
            </li>
            <li>
                <a href="#"><i class="fa-solid fa-plus-circle"></i><span>Add Institute</span></a>
            </li>
            <li>
                <a href="#"><i class="fa-solid fa-credit-card"></i><span>Subscriptions</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/ERP-SYSTEM-FOR-COACHING/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="top-navbar">
            <h2>Super Admin Overview</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong>Super Admin</strong>
            </div>
        </div>

        <div class="page-body">
            <!-- Metric Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="info">
                        <h3>14</h3>
                        <p>Total Institutes</p>
                    </div>
                    <div class="icon bg-blue">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="info">
                        <h3>11</h3>
                        <p>Active</p>
                    </div>
                    <div class="icon bg-green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="info">
                        <h3>3</h3>
                        <p>Trial</p>
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
                    <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/institutes.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">View All</a>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Institute ID</th>
                            <th>Institute Name</th>
                            <th>Admin Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>INS-2026-001</strong></td>
                            <td>Science Academy</td>
                            <td>Ramesh Sharma</td>
                            <td>ramesh@academy.com</td>
                            <td>9876543210</td>
                            <td><span class="status-pill pill-active">Active</span></td>
                        </tr>
                        <tr>
                            <td><strong>INS-2026-002</strong></td>
                            <td>JEE Classes</td>
                            <td>Sunita Verma</td>
                            <td>admin@co.org</td>
                            <td>9123456780</td>
                            <td><span class="status-pill pill-trial">Trial</span></td>
                        </tr>
                        <tr>
                            <td><strong>INS-2026-003</strong></td>
                            <td>CA Hub</td>
                            <td>Vikram Seth</td>
                            <td>info@cahub.in</td>
                            <td>9988776655</td>
                            <td><span class="status-pill pill-active">Active</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
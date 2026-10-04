<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees - Teacher Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="fees.css">
</head>
<body>
    <div class="erp-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher Portal</span>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-clipboard-user"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="active">
                    <a href="fees.php">
                        <i class="fa-solid fa-money-bill"></i>
                        <span>Fees</span>
                    </a>
                </li>
            <li>
                <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                    <span>Exams</span>
                </a>
            </li>
            </ul>
            <div class="sidebar-footer">
                <a href="/ERP-SYSTEM-FOR-COACHING/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="page-title">
                    <h2>Fees Management</h2>
                </div>
                <div class="user-profile">
                    <span class="role-pill">TEACHER</span>
                    <i class="fa-regular fa-circle-user profile-icon"></i>
                </div>
            </header>

            <main class="content-body">
                <!-- 3 Standalone Stat Boxes -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Batch Students</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>
                        <strong class="stat-num">64</strong>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Fees Cleared</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                        <strong class="stat-num">48</strong>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Dues Pending</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <strong class="stat-num">16</strong>
                    </div>
                </div>

                <!-- Table Section -->
                <section class="card-section">
                    <div class="card-section-header">
                        <div class="header-left">
                            <h3 class="section-title"><i class="fa-solid fa-receipt"></i> Batch Students Fee Status</h3>
                            <p class="sub-counter">Showing 3 Students</p>
                        </div>
                        <a href="payment.php" class="portal-btn">Collect Payment</a>
                    </div>

                    <div class="table-wrapper">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>Admission No</th>
                                    <th>Student Name</th>
                                    <th>Batch</th>
                                    <th>Total Fee</th>
                                    <th>Paid</th>
                                    <th>Pending Due</th>
                                    <th>Status</th>
                                    <th class="th-action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-mono">ADM-2026-001</td>
                                    <td class="font-semibold">Aman Verma</td>
                                    <td>JEE Morning Batch A</td>
                                    <td>₹25,000</td>
                                    <td>₹25,000</td>
                                    <td>₹0</td>
                                    <td><span class="status-tag status-paid">Paid</span></td>
                                    <td class="td-action"><a href="#" class="action-link"><i class="fa-solid fa-print"></i> Receipt</a></td>
                                </tr>
                                <tr>
                                    <td class="font-mono">ADM-2026-004</td>
                                    <td class="font-semibold">Rhea Gupta</td>
                                    <td>NEET Evening Batch B</td>
                                    <td>₹30,000</td>
                                    <td>₹15,000</td>
                                    <td>₹15,000</td>
                                    <td><span class="status-tag status-partial">Partial</span></td>
                                    <td class="td-action"><a href="payment.php?id=ADM-2026-004" class="action-link"><i class="fa-solid fa-credit-card"></i> Collect</a></td>
                                </tr>
                                <tr>
                                    <td class="font-mono">ADM-2026-009</td>
                                    <td class="font-semibold">Rohit Verma</td>
                                    <td>JEE Morning Batch A</td>
                                    <td>₹20,000</td>
                                    <td>₹0</td>
                                    <td>₹20,000</td>
                                    <td><span class="status-tag status-pending">Pending</span></td>
                                    <td class="td-action"><a href="payment.php?id=ADM-2026-009" class="action-link"><i class="fa-solid fa-credit-card"></i> Collect</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
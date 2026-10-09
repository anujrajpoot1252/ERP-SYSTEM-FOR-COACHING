<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="attendance.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Student Portal</span>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="result.php">
                        <i class="fa-solid fa-poll"></i>
                        <span>Results</span>
                    </a>
                </li>
                <li class="active">
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
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

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <h2>My Attendance</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong>Aman Verma</strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Summary Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fa-solid fa-percent"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Attendance Rate</span>
                            <h3 class="stat-value">100%</h3>
                            <span class="stat-subtext text-success">Required: 75%</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Present</span>
                            <h3 class="stat-value">1 <span class="stat-total">Sessions</span></h3>
                            <span class="stat-subtext">Total attended</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-red">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Absent</span>
                            <h3 class="stat-value">0 <span class="stat-total">Sessions</span></h3>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-slate">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Sessions</span>
                            <h3 class="stat-value">1 <span class="stat-total">Conducted</span></h3>
                            <span class="stat-subtext">Current term</span>
                        </div>
                    </div>
                </div>

                <section class="card list-card">
                    <div class="list-header">
                        <div>
                            <h3 class="card-title">Attendance History</h3>
                        </div>
                        <div class="controls-wrap">
                            <select class="filter-select" aria-label="Select Batch">
                                <option value="batch_1" selected>JEE Morning Batch A (08:00 AM)</option>
                                <option value="batch_2">JEE Evening Batch B (04:00 PM)</option>
                                <option value="batch_3">Weekend Test Batch (10:00 AM)</option>
                            </select>
                            <input type="text" placeholder="Search date..." class="search-input">
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DATE</th>
                                    <th>SUBJECT</th>
                                    <th>SESSION TIMING</th>
                                    <th>FACULTY</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><strong>08 Oct 2026</strong></td>
                                    <td>Mathematics</td>
                                    <td>08:00 AM - 09:30 AM</td>
                                    <td>Rahul Sharma</td>
                                    <td class="text-center">
                                        <span class="status-pill status-present">
                                            <i class="fa-solid fa-check"></i> Present
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
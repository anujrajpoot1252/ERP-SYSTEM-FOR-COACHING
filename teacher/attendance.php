<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP/assets/css/style.css?v=2">
</head>
<body>
<div class="app-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Teacher Portal</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            </li>
            <li class="active">
                <a href="attendance.php"><i class="fa-solid fa-clipboard-user"></i> Attendance</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/ERP/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h2>Mark Attendance</h2>
            <div class="user-profile">
                <span class="role-badge">TEACHER</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong>Teacher</strong>
            </div>
        </div>

        <div class="page-body">
            <!-- Filter Section -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-filter"></i> Select Session</span>
                </div>
                <form class="filter-grid">
                    <div class="filter-item">
                        <label for="batch-select">Batch</label>
                        <select id="batch-select" class="form-control-simple">
                            <option value="">-- Choose Batch --</option>
                            <option value="1">JEE Morning Batch A</option>
                            <option value="2">NEET Evening Batch B</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="attendance-date">Date</label>
                        <input type="date" id="attendance-date" class="form-control-simple" value="">
                    </div>

                    <div class="filter-item">
                        <button type="button" class="btn-primary" style="height: 42px;">
                            <i class="fa-solid fa-magnifying-glass"></i> Load List
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attendance Marking Table -->
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-user-check"></i> Student Attendance</span>
                </div>

                <form>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Roll No</th>
                                <th style="width: 45%;">Student Name</th>
                                <th style="width: 40%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#101</strong></td>
                                <td>Aarav Sharma</td>
                                <td>
                                    <div class="status-options">
                                        <label class="status-label present">
                                            <input type="radio" name="status[101]" value="present" checked> Present
                                        </label>
                                        <label class="status-label absent">
                                            <input type="radio" name="status[101]" value="absent"> Absent
                                        </label>
                                        <label class="status-label late">
                                            <input type="radio" name="status[101]" value="late"> Late
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#102</strong></td>
                                <td>Priya Verma</td>
                                <td>
                                    <div class="status-options">
                                        <label class="status-label present">
                                            <input type="radio" name="status[102]" value="present" checked> Present
                                        </label>
                                        <label class="status-label absent">
                                            <input type="radio" name="status[102]" value="absent"> Absent
                                        </label>
                                        <label class="status-label late">
                                            <input type="radio" name="status[102]" value="late"> Late
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>#103</strong></td>
                                <td>Rohan Gupta</td>
                                <td>
                                    <div class="status-options">
                                        <label class="status-label present">
                                            <input type="radio" name="status[103]" value="present"> Present
                                        </label>
                                        <label class="status-label absent">
                                            <input type="radio" name="status[103]" value="absent" checked> Absent
                                        </label>
                                        <label class="status-label late">
                                            <input type="radio" name="status[103]" value="late"> Late
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="table-footer-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
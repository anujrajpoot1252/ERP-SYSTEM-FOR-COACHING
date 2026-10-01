
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Attendance</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="attendance.css">
</head>

<body>

<div class="app-container">

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

            <li class="active">
                <a href="attendance.php">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <span>Attendance</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="/ERP/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>

    </aside>

    <main class="main-content">

        <header class="top-navbar">

            <div class="page-heading">
                <h2>Mark Attendance</h2>
                <p>Manage today's student attendance</p>
            </div>

            <div class="user-profile">
                <span class="role-badge">TEACHER</span>
                <i class="fa-solid fa-circle-user user-icon"></i>
                <strong>Teacher</strong>
            </div>

        </header>

        <section class="page-body">

            <div class="card filter-card">

                <div class="card-title">
                    <span>
                        <i class="fa-solid fa-filter"></i>
                        Select Session
                    </span>
                </div>

                <div class="filter-grid">

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

                        <input
                            type="date"
                            id="attendance-date"
                            class="form-control-simple"
                        >
                    </div>

                    <div class="filter-item filter-button">
                        <button type="button" class="btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Load List
                        </button>
                    </div>

                </div>

            </div>

            <div class="card attendance-card">

                <div class="attendance-header">

                    <div class="card-title">
                        <span>
                            <i class="fa-solid fa-user-check"></i>
                            Student Attendance
                        </span>
                    </div>

                    <div class="attendance-summary">
                        <span class="summary-present">
                            <i class="fa-solid fa-circle-check"></i>
                            <b id="present-count">0</b> Present
                        </span>

                        <span class="summary-absent">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <b id="absent-count">0</b> Absent
                        </span>
                    </div>

                </div>

                <form id="attendance-form">

                    <div class="student-list">

                        <div class="student-row" data-student-id="101">

                            <div class="student-roll">
                                <span class="mobile-label">Roll No</span>
                                <strong>#101</strong>
                            </div>

                            <div class="student-info">
                                <span class="mobile-label">Student</span>
                                <strong>Aarav Sharma</strong>
                            </div>

                            <div class="status-options">

                                <span class="mobile-label">Attendance</span>

                                <label class="status-label present">
                                    <input
                                        type="radio"
                                        name="status[101]"
                                        value="true"
                                        checked
                                    >
                                    <span>
                                        <i class="fa-solid fa-check"></i>
                                        Present
                                    </span>
                                </label>

                                <label class="status-label absent">
                                    <input
                                        type="radio"
                                        name="status[101]"
                                        value="false"
                                    >
                                    <span>
                                        <i class="fa-solid fa-xmark"></i>
                                        Absent
                                    </span>
                                </label>

                            </div>

                        </div>

                        <div class="student-row" data-student-id="102">

                            <div class="student-roll">
                                <span class="mobile-label">Roll No</span>
                                <strong>#102</strong>
                            </div>

                            <div class="student-info">
                                <span class="mobile-label">Student</span>
                                <strong>Priya Verma</strong>
                            </div>

                            <div class="status-options">

                                <span class="mobile-label">Attendance</span>

                                <label class="status-label present">
                                    <input
                                        type="radio"
                                        name="status[102]"
                                        value="true"
                                        checked
                                    >
                                    <span>
                                        <i class="fa-solid fa-check"></i>
                                        Present
                                    </span>
                                </label>

                                <label class="status-label absent">
                                    <input
                                        type="radio"
                                        name="status[102]"
                                        value="false"
                                    >
                                    <span>
                                        <i class="fa-solid fa-xmark"></i>
                                        Absent
                                    </span>
                                </label>

                            </div>

                        </div>

                        <div class="student-row" data-student-id="103">

                            <div class="student-roll">
                                <span class="mobile-label">Roll No</span>
                                <strong>#103</strong>
                            </div>

                            <div class="student-info">
                                <span class="mobile-label">Student</span>
                                <strong>Rohan Gupta</strong>
                            </div>

                            <div class="status-options">

                                <span class="mobile-label">Attendance</span>

                                <label class="status-label present">
                                    <input
                                        type="radio"
                                        name="status[103]"
                                        value="true"
                                    >
                                    <span>
                                        <i class="fa-solid fa-check"></i>
                                        Present
                                    </span>
                                </label>

                                <label class="status-label absent">
                                    <input
                                        type="radio"
                                        name="status[103]"
                                        value="false"
                                        checked
                                    >
                                    <span>
                                        <i class="fa-solid fa-xmark"></i>
                                        Absent
                                    </span>
                                </label>

                            </div>

                        </div>

                    </div>

                    <div class="table-footer-actions">
                        <button type="submit" class="btn-primary save-btn">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Attendance
                        </button>
                    </div>

                </form>

            </div>

        </section>

    </main>

</div>
<script src="attendance_script.js">

</script>
</body>
</html>


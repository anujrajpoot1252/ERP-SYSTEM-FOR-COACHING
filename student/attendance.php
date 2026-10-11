<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = $_SESSION['user_id'];
$student_query = mysqli_query($conn, "SELECT s.id, u.name FROM student s JOIN users u ON s.user_id = u.id WHERE s.user_id = $user_id LIMIT 1");
$student_data = mysqli_fetch_assoc($student_query);
$student_id = $student_data['id'] ?? 0;
$student_name = $student_data['name'] ?? $_SESSION['name'] ?? 'Student';

$attendance_records = [];
$total_sessions = 0;
$present_count = 0;
$absent_count = 0;

if ($student_id > 0) {
    $batch_name_col = "b.name";
    $batch_col_check = @mysqli_query($conn, "SHOW COLUMNS FROM `batch` LIKE 'name'");
    if (!$batch_col_check || mysqli_num_rows($batch_col_check) === 0) {
        $batch_name_col = "b.batch_name";
    }

    $att_query = mysqli_query($conn, "
        SELECT 
            a.id,
            a.date,
            a.status,
            {$batch_name_col} as batch_name,
            b.timing_status,
            b.schedule,
            c.course_name,
            u_t.name as teacher_name
        FROM attendance a
        LEFT JOIN batch b ON a.batch_id = b.id
        LEFT JOIN course c ON b.course_id = c.id
        LEFT JOIN teacher t ON b.teacher_id = t.id
        LEFT JOIN users u_t ON t.user_id = u_t.id
        WHERE a.student_id = $student_id
        ORDER BY a.date DESC
    ");

    if ($att_query) {
        while ($row = mysqli_fetch_assoc($att_query)) {
            $total_sessions++;
            if ($row['status'] === 'present') {
                $present_count++;
            } else {
                $absent_count++;
            }
            $attendance_records[] = $row;
        }
    }
}

$attendance_rate = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100) : 0;
?>
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
                <a href="../logout.php">
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
                    <strong><?= htmlspecialchars($student_name) ?></strong>
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
                            <h3 class="stat-value"><?= $attendance_rate ?>%</h3>
                            <span class="stat-subtext text-success">Required: 75%</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Present</span>
                            <h3 class="stat-value"><?= $present_count ?> <span class="stat-total">Sessions</span></h3>
                            <span class="stat-subtext">Total attended</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-red">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Absent</span>
                            <h3 class="stat-value"><?= $absent_count ?> <span class="stat-total">Sessions</span></h3>
                            <span class="stat-subtext">Total missed</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-slate">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Sessions</span>
                            <h3 class="stat-value"><?= $total_sessions ?> <span class="stat-total">Conducted</span></h3>
                            <span class="stat-subtext">Current term</span>
                        </div>
                    </div>
                </div>

                <section class="card list-card">
                    <div class="list-header">
                        <div>
                            <h3 class="card-title">Attendance History</h3>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>DATE</th>
                                    <th>COURSE / BATCH</th>
                                    <th>SESSION TIMING</th>
                                    <th>FACULTY</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($attendance_records)): ?>
                                    <?php $idx = 1; foreach ($attendance_records as $rec): ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><strong><?= date("d M Y", strtotime($rec['date'])) ?></strong></td>
                                            <td><?= htmlspecialchars($rec['course_name'] ?? $rec['batch_name'] ?? 'Class') ?></td>
                                            <td><?= htmlspecialchars($rec['schedule'] ?? ($rec['timing_status'] ? date("h:i A", strtotime($rec['timing_status'])) : 'N/A')) ?></td>
                                            <td><?= htmlspecialchars($rec['teacher_name'] ?? 'Assigned Faculty') ?></td>
                                            <td class="text-center">
                                                <?php if ($rec['status'] === 'present'): ?>
                                                    <span class="status-pill status-present">
                                                        <i class="fa-solid fa-check"></i> Present
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-pill" style="background:#fee2e2; color:#b91c1c;">
                                                        <i class="fa-solid fa-xmark"></i> Absent
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 25px;">
                                            No attendance records found yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$student_stmt = mysqli_prepare($conn, "
    SELECT s.id, s.batch_id, u.name
    FROM student s
    INNER JOIN users u ON u.id = s.user_id
    WHERE s.user_id = ? AND s.institute_id = ? AND u.role = 'student' AND u.status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($student_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($student_stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt)) ?: [];
mysqli_stmt_close($student_stmt);
$student_id = (int) ($student['id'] ?? 0);
$student_name = $student['name'] ?? ($_SESSION['name'] ?? 'Student');

$present_count = 0;
$absent_count = 0;
$total_sessions = 0;
$batch_options = [];
$attendance_rows = false;
$selected_batch = filter_input(INPUT_GET, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
$selected_date = is_string($_GET['date'] ?? null) ? trim($_GET['date']) : '';
$filter_error = '';

if ($student_id > 0) {
    $stats_stmt = mysqli_prepare($conn, "
        SELECT SUM(status IN ('present', 'presence')) AS present_count,
               SUM(status IN ('absent', 'absence')) AS absent_count,
               COUNT(*) AS total_sessions
        FROM attendance WHERE student_id = ?
    ");
    mysqli_stmt_bind_param($stats_stmt, 'i', $student_id);
    mysqli_stmt_execute($stats_stmt);
    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt)) ?: [];
    mysqli_stmt_close($stats_stmt);
    $present_count = (int) ($stats['present_count'] ?? 0);
    $absent_count = (int) ($stats['absent_count'] ?? 0);
    $total_sessions = (int) ($stats['total_sessions'] ?? 0);

    $batch_stmt = mysqli_prepare($conn, "
        SELECT DISTINCT b.id, b.name, b.timing_status
        FROM batch b
        WHERE b.institute_id = ? AND (
            b.id = ? OR EXISTS (
                SELECT 1 FROM attendance a WHERE a.batch_id = b.id AND a.student_id = ?
            )
        )
        ORDER BY b.name
    ");
    mysqli_stmt_bind_param($batch_stmt, 'iii', $institute_id, $student['batch_id'], $student_id);
    mysqli_stmt_execute($batch_stmt);
    $batch_result = mysqli_stmt_get_result($batch_stmt);
    while ($batch = mysqli_fetch_assoc($batch_result)) {
        $batch_options[] = $batch;
    }
    mysqli_stmt_close($batch_stmt);

    if ($selected_batch > 0 && !array_filter($batch_options, static function ($batch) use ($selected_batch) {
        return (int) $batch['id'] === $selected_batch;
    })) {
        $selected_batch = 0;
        $filter_error = 'That batch is not associated with your attendance record.';
    }
    if ($selected_date !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date) ||
        !checkdate((int) substr($selected_date, 5, 2), (int) substr($selected_date, 8, 2), (int) substr($selected_date, 0, 4)))) {
        $selected_date = '';
        $filter_error = 'Enter a valid attendance date.';
    }

    $history_sql = "
        SELECT a.date, a.status, b.name AS batch_name, b.timing_status, c.course_name,
               t.subject, teacher_user.name AS teacher_name
        FROM attendance a
        INNER JOIN batch b ON b.id = a.batch_id AND b.institute_id = ?
        LEFT JOIN course c ON c.id = b.course_id AND c.institute_id = b.institute_id
        LEFT JOIN teacher t ON t.id = b.teacher_id AND t.institute_id = b.institute_id
        LEFT JOIN users teacher_user ON teacher_user.id = t.user_id
        WHERE a.student_id = ?
    ";
    $history_types = 'ii';
    $history_values = [$institute_id, $student_id];
    if ($selected_batch > 0) {
        $history_sql .= ' AND a.batch_id = ?';
        $history_types .= 'i';
        $history_values[] = $selected_batch;
    }
    if ($selected_date !== '') {
        $history_sql .= ' AND a.date = ?';
        $history_types .= 's';
        $history_values[] = $selected_date;
    }
    $history_sql .= ' ORDER BY a.date DESC, a.id DESC';
    $history_stmt = mysqli_prepare($conn, $history_sql);
    $history_bindings = [$history_stmt, &$history_types];
    foreach ($history_values as $index => &$history_value) {
        $history_bindings[] = &$history_value;
    }
    unset($history_value);
    call_user_func_array('mysqli_stmt_bind_param', $history_bindings);
    mysqli_stmt_execute($history_stmt);
    $attendance_rows = mysqli_stmt_get_result($history_stmt);
}
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
                <li>
                    <a href="marksheet.php">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Marksheet</span>
                    </a>
                </li>
                <li class="active">
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="fees.php">
                        <i class="fa-solid fa-money-bill"></i>
                        <span>Fees</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="<?= htmlspecialchars(app_url('logout.php')) ?>">
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
                            <h3 class="stat-value"><?= $total_sessions > 0 ? round($present_count * 100 / $total_sessions, 1) : 0 ?>%</h3>
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
                        <form method="GET" class="controls-wrap">
                            <select name="batch_id" class="filter-select" aria-label="Select Batch">
                                <option value="0">All batches</option>
                                <?php foreach ($batch_options as $batch): ?>
                                    <option value="<?= (int) $batch['id'] ?>" <?= $selected_batch === (int) $batch['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($batch['name']) ?><?= $batch['timing_status'] ? ' (' . htmlspecialchars(substr($batch['timing_status'], 0, 5)) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>" class="search-input" aria-label="Filter attendance date">
                            <button type="submit" class="filter-select">Filter</button>
                        </form>
                    </div>
                    <?php if ($filter_error !== ''): ?><p class="sub-counter"><?= htmlspecialchars($filter_error) ?></p><?php endif; ?>

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
                                <?php $row_number = 1; ?>
                                <?php if ($attendance_rows && mysqli_num_rows($attendance_rows) > 0): ?>
                                    <?php while ($attendance = mysqli_fetch_assoc($attendance_rows)): ?>
                                        <?php
                                            $attendance_status = in_array($attendance['status'], ['present', 'presence'], true) ? 'present' : 'absent';
                                            $session_time = $attendance['timing_status'] ? date('h:i A', strtotime($attendance['timing_status'])) : 'N/A';
                                        ?>
                                        <tr>
                                            <td><?= $row_number++ ?></td>
                                            <td><strong><?= htmlspecialchars(date('d M Y', strtotime($attendance['date']))) ?></strong></td>
                                            <td><?= htmlspecialchars($attendance['course_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($session_time) ?></td>
                                            <td><?= htmlspecialchars($attendance['teacher_name'] ?? 'Not assigned') ?></td>
                                            <td class="text-center">
                                                <span class="status-pill status-<?= $attendance_status ?>">
                                                    <i class="fa-solid fa-<?= $attendance_status === 'present' ? 'check' : 'xmark' ?>"></i>
                                                    <?= ucfirst($attendance_status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center">No attendance records found.</td></tr>
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
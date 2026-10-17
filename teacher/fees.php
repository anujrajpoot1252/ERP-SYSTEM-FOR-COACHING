<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$teacher_stmt = mysqli_prepare($conn, "
    SELECT id FROM teacher
    WHERE user_id = ? AND institute_id = ? AND status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($teacher_stmt);
$teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
mysqli_stmt_close($teacher_stmt);
$teacher_id = (int) ($teacher['id'] ?? 0);
$students_stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, s.admission_no, u.name AS student_name, b.name AS batch_name,
           COALESCE(c.fees, 0) AS total_course_fee,
           COALESCE((
               SELECT SUM(f.amount) FROM fees f
               WHERE f.student_id = s.id AND f.status = 'paid'
                   AND (f.course_id = s.course_id OR f.course_id IS NULL)
           ), 0) AS paid_amount
    FROM student s
    INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
    INNER JOIN batch b ON b.id = s.batch_id AND b.institute_id = s.institute_id AND b.teacher_id = ?
    LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
    WHERE s.institute_id = ? AND s.status = 'active'
    ORDER BY s.id DESC
");
mysqli_stmt_bind_param($students_stmt, 'ii', $teacher_id, $institute_id);
mysqli_stmt_execute($students_stmt);
$students_query = mysqli_stmt_get_result($students_stmt);

$students = [];
$total_students = 0;
$cleared_count = 0;
$pending_count = 0;

if ($students_query) {
    while ($row = mysqli_fetch_assoc($students_query)) {
        $total_fee = floatval($row['total_course_fee']);
        $paid = floatval($row['paid_amount']);
        $due = max(0, $total_fee - $paid);
        
        if ($due == 0 && $total_fee > 0) {
            $status = 'paid';
            $cleared_count++;
        } elseif ($paid > 0) {
            $status = 'partial';
            $pending_count++;
        } else {
            $status = 'pending';
            $pending_count++;
        }

        $row['total_fee'] = $total_fee;
        $row['paid'] = $paid;
        $row['due'] = $due;
        $row['status'] = $status;

        $students[] = $row;
    }
}
$total_students = count($students);
?>
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
                <li>
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
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
                <!-- 3 Dynamic Stat Boxes -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Batch Students</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>
                        <strong class="stat-num"><?= $total_students ?></strong>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Fees Cleared</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
                        <strong class="stat-num"><?= $cleared_count ?></strong>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Dues Pending</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <strong class="stat-num"><?= $pending_count ?></strong>
                    </div>
                </div>

                <!-- Table Section -->
                <section class="card-section">
                    <div class="card-section-header">
                        <div class="header-left">
                            <h3 class="section-title"><i class="fa-solid fa-receipt"></i> Batch Students Fee Status</h3>
                            <p class="sub-counter">Showing <?= $total_students ?> Students</p>
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
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $st): ?>
                                        <tr>
                                            <td class="font-mono"><strong><?= htmlspecialchars($st['admission_no']) ?></strong></td>
                                            <td class="font-semibold"><?= htmlspecialchars($st['student_name']) ?></td>
                                            <td><?= htmlspecialchars($st['batch_name'] ?? 'Unassigned') ?></td>
                                            <td>₹<?= number_format($st['total_fee'], 2) ?></td>
                                            <td>₹<?= number_format($st['paid'], 2) ?></td>
                                            <td>₹<?= number_format($st['due'], 2) ?></td>
                                            <td>
                                                <span class="status-tag status-<?= $st['status'] ?>">
                                                    <?= strtoupper($st['status']) ?>
                                                </span>
                                            </td>
                                            <td class="td-action">
                                                <a href="payment.php?student_id=<?= $st['student_id'] ?>" class="action-link">
                                                    <i class="fa-solid fa-credit-card"></i> Collect
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; color:#94a3b8; padding: 20px;">No student records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
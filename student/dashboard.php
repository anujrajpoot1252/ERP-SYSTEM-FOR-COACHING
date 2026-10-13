<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$student_stmt = mysqli_prepare($conn, "
    SELECT s.*, u.name as student_name, u.email, c.course_name, c.fees, b.name as batch_name, b.schedule, b.timing_status
    FROM student s 
    JOIN users u ON s.user_id = u.id AND u.role = 'student' AND u.status = 'active'
    LEFT JOIN course c ON s.course_id = c.id AND c.institute_id = s.institute_id
    LEFT JOIN batch b ON s.batch_id = b.id AND b.institute_id = s.institute_id
    WHERE s.user_id = ? AND s.institute_id = ?
");
mysqli_stmt_bind_param($student_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($student_stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt)) ?: [];
mysqli_stmt_close($student_stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/style.css')) ?>">
</head>
<body>
<div class="app-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Student Portal</span>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <a href="#"><i class="fa-solid fa-chart-line"></i>Dashboard</a>
            </li>
            <li>
                <a href="result.php"><i class="fa-solid fa-poll"></i>Result</a>
            </li>
            <li>
                <a href="marksheet.php"><i class="fa-solid fa-file-invoice"></i>Marksheet</a>
            </li>
            <li>
                <a href="attendance.php"><i class="fa-solid fa-calendar-check"></i>Attendance</a>
            </li>
            <li>
                <a href="fees.php"><i class="fa-solid fa-money-bill"></i>Fees</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="<?= htmlspecialchars(app_url('logout.php')) ?>"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h2>Student Dashboard</h2>
            <div class="user-profile">
                <span class="role-badge">STUDENT</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></strong>
            </div>
        </div>

        <div class="page-body">
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-id-card"></i> Student Profile</span>
                </div>
                <div class="info-grid">
                    <p><strong>Admission No:</strong> <?= htmlspecialchars($student['admission_no'] ?? 'N/A') ?></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($student['student_name'] ?? 'N/A') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($student['email'] ?? 'N/A') ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone'] ?? 'N/A') ?></p>
                    <p><strong>Parent Name:</strong> <?= htmlspecialchars($student['parent_name'] ?? 'N/A') ?></p>
                    <p><strong>Parent Phone:</strong> <?= htmlspecialchars($student['parent_phone'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-book-open"></i> Course & Batch Details</span>
                </div>
                <div class="info-grid">
                    <p><strong>Enrolled Course:</strong> <?= htmlspecialchars($student['course_name'] ?? 'Not Enrolled') ?></p>
                    <p><strong>Course Fee:</strong> ₹<?= number_format($student['fees'] ?? 0, 2) ?></p>
                    <p><strong>Assigned Batch:</strong> <?= htmlspecialchars($student['batch_name'] ?? 'Not Assigned') ?></p>
                    <p><strong>Class Timing:</strong> <?= htmlspecialchars($student['timing_status'] ?? 'N/A') ?> (<?= htmlspecialchars($student['schedule'] ?? 'N/A') ?>)</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

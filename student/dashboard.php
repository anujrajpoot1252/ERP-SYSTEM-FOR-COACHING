<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = $_SESSION['user_id'];
$student = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT s.*, u.name as student_name, u.email, c.course_name, c.fees, b.name as batch_name, b.schedule, b.timing_status 
    FROM student s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN course c ON s.course_id = c.id 
    LEFT JOIN batch b ON s.batch_id = b.id 
    WHERE s.user_id = $user_id
"));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP-SYSTEM-FOR-COACHING/assets/css/style.css">
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
                <a href="#"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/ERP-SYSTEM-FOR-COACHING/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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

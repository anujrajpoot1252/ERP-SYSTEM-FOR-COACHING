<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$student = null;
$results = [];
$total_max = 0;
$total_obtained = 0;

$student_stmt = mysqli_prepare($conn, "
    SELECT s.id, s.admission_no, u.name as student_name, u.email,
           c.course_name, b.name as batch_name, i.name as institute_name
    FROM student s
    JOIN users u ON s.user_id = u.id AND u.role = 'student'
    LEFT JOIN course c ON s.course_id = c.id
    LEFT JOIN batch b ON s.batch_id = b.id
    LEFT JOIN institute i ON s.institute_id = i.id
    WHERE s.user_id = ? LIMIT 1
");
if ($student_stmt) {
    mysqli_stmt_bind_param($student_stmt, 'i', $user_id);
    mysqli_stmt_execute($student_stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt));
    mysqli_stmt_close($student_stmt);
}

if ($student) {
    $res_stmt = mysqli_prepare($conn, "
        SELECT r.obtained_marks, r.total_marks, r.grade, e.exam_name, e.exam_date, e.batch_name
        FROM results r
        JOIN exams e ON r.exam_id = e.exam_id
        WHERE r.student_id = ?
        ORDER BY e.exam_date DESC
    ");
    if ($res_stmt) {
        $student_id = (int) $student['id'];
        mysqli_stmt_bind_param($res_stmt, 'i', $student_id);
        mysqli_stmt_execute($res_stmt);
        $res_data = mysqli_stmt_get_result($res_stmt);
        while ($row = mysqli_fetch_assoc($res_data)) {
            $results[] = $row;
            $total_max += (float) $row['total_marks'];
            $total_obtained += (float) $row['obtained_marks'];
        }
        mysqli_stmt_close($res_stmt);
    }
}

$percentage = $total_max > 0 ? round(($total_obtained / $total_max) * 100, 1) : 0;
$status = $percentage >= 33 ? 'PASSED' : ($total_max > 0 ? 'FAILED' : 'N/A');

if ($percentage >= 90) {
    $overall_grade = 'A+';
} elseif ($percentage >= 75) {
    $overall_grade = 'A';
} elseif ($percentage >= 60) {
    $overall_grade = 'B';
} elseif ($percentage >= 45) {
    $overall_grade = 'C';
} elseif ($percentage >= 33) {
    $overall_grade = 'D';
} else {
    $overall_grade = $total_max > 0 ? 'F' : 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Marksheet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="marksheet.css">
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
                    <a href="marksheet.php">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Marksheet</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="fees.php">
                        <i class="fa-solid fa-credit-card"></i>
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
                <h2>Official Marksheet</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong><?= htmlspecialchars($student['student_name'] ?? $_SESSION['name'] ?? 'Student') ?></strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Action / Selection Bar -->
                <div class="action-bar">
                    <div>
                        <strong style="color: #1e293b; font-size: 15px;">Academic Performance Evaluation</strong>
                    </div>
                    <button type="button" class="btn-print" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Print / Download Marksheet
                    </button>
                </div>

                <!-- Aggregate Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Score</span>
                            <h3 class="stat-value"><?= $total_obtained ?> <span style="font-size: 0.85rem; color: #94a3b8;">/ <?= $total_max ?></span></h3>
                            <span class="stat-subtext text-success"><?= $percentage ?>% Overall</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-indigo">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Final Grade</span>
                            <h3 class="stat-value"><?= $overall_grade ?></h3>
                            <span class="stat-subtext"><?= $status === 'PASSED' ? 'Passed Examination' : 'Evaluation' ?></span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Result Status</span>
                            <h3 class="stat-value <?= $status === 'PASSED' ? 'text-success' : 'text-danger' ?>"><?= $status ?></h3>
                            <span class="stat-subtext"><?= count($results) ?> Exam(s) Evaluated</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-slate">
                            <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Enrolled Course</span>
                            <h3 class="stat-value" style="font-size: 16px;"><?= htmlspecialchars($student['course_name'] ?? 'General') ?></h3>
                            <span class="stat-subtext"><?= htmlspecialchars($student['batch_name'] ?? 'Regular') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Printable Marksheet Container -->
                <div class="marksheet-card">
                    <!-- Academic Header -->
                    <div class="sheet-header">
                        <h3><?= htmlspecialchars($student['institute_name'] ?? 'Coaching Academy') ?></h3>
                        <p>Affiliated Academic Evaluation & Performance Record</p>
                        <span class="sheet-badge">STATEMENT OF MARKS</span>
                    </div>

                    <!-- Student Metadata Details -->
                    <div class="student-meta-box">
                        <div class="meta-item">
                            <span>Candidate Name: </span><strong><?= htmlspecialchars($student['student_name'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Admission No: </span><strong><?= htmlspecialchars($student['admission_no'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Email: </span><strong><?= htmlspecialchars($student['email'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Course: </span><strong><?= htmlspecialchars($student['course_name'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Batch: </span><strong><?= htmlspecialchars($student['batch_name'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="meta-item">
                            <span>Academic Session: </span><strong><?= date('Y') ?> - <?= date('Y') + 1 ?></strong>
                        </div>
                    </div>

                    <!-- Subject Marksheet Table -->
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>EXAM NAME</th>
                                    <th>DATE</th>
                                    <th class="text-center">MAX MARKS</th>
                                    <th class="text-center">PASS MARKS (33%)</th>
                                    <th class="text-center">MARKS OBTAINED</th>
                                    <th class="text-center">GRADE</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($results)): ?>
                                    <?php $i = 1; foreach ($results as $res): ?>
                                        <?php 
                                            $pass_mark = round($res['total_marks'] * 0.33, 1);
                                            $res_pass = $res['obtained_marks'] >= $pass_mark;
                                        ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><strong><?= htmlspecialchars($res['exam_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($res['exam_date']) ?></td>
                                            <td class="text-center"><?= $res['total_marks'] ?></td>
                                            <td class="text-center"><?= $pass_mark ?></td>
                                            <td class="text-center"><strong><?= $res['obtained_marks'] ?></strong></td>
                                            <td class="text-center"><span class="grade-badge"><?= htmlspecialchars($res['grade'] ?: '-') ?></span></td>
                                            <td class="text-center">
                                                <span class="status-pill <?= $res_pass ? 'status-pass' : 'status-fail' ?>">
                                                    <?= $res_pass ? 'Pass' : 'Fail' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-summary-row">
                                        <td colspan="3"><strong>GRAND TOTAL</strong></td>
                                        <td class="text-center"><strong><?= $total_max ?></strong></td>
                                        <td class="text-center"><strong><?= round($total_max * 0.33, 1) ?></strong></td>
                                        <td class="text-center"><strong><?= $total_obtained ?></strong></td>
                                        <td class="text-center"><span class="grade-badge"><?= $overall_grade ?></span></td>
                                        <td class="text-center">
                                            <span class="status-pill <?= $status === 'PASSED' ? 'status-pass' : 'status-fail' ?>">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center; padding: 30px; color: #94a3b8;">
                                            <i class="fa-solid fa-file-circle-exclamation fa-2x" style="margin-bottom: 10px; display:block;"></i>
                                            No examination results have been published for your profile yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Official Verification Signatures -->
                    <div class="sheet-signatures">
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Date of Issue: <?= date('d M Y') ?></p>
                        </div>
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Faculty In-Charge</p>
                        </div>
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Controller of Examinations</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
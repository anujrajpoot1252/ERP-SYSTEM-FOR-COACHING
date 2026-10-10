<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['student']);

$user_id = $_SESSION['user_id'];

// Get student record for logged in user
$student_query = mysqli_query($conn, "SELECT s.id, u.name FROM student s JOIN users u ON s.user_id = u.id WHERE s.user_id = $user_id LIMIT 1");
$student_data = mysqli_fetch_assoc($student_query);
$student_id = $student_data['id'] ?? 0;
$student_name = $student_data['name'] ?? $_SESSION['name'] ?? 'Student';

// Fetch Results for this student
$results_list = [];
if ($student_id > 0) {
    $res_query = mysqli_query($conn, "
        SELECT 
            r.obtained_marks,
            r.total_marks,
            r.grade,
            e.exam_name,
            e.exam_date
        FROM results r
        JOIN exams e ON r.exam_id = e.exam_id
        WHERE r.student_id = $student_id
        ORDER BY e.exam_date DESC
    ");
    if ($res_query) {
        while ($row = mysqli_fetch_assoc($res_query)) {
            $results_list[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - My Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="result.css">
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
                <li class="active">
                    <a href="result.php">
                        <i class="fa-solid fa-poll"></i>
                        <span>Results</span>
                    </a>
                </li>
            <li>
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
                <h2>My Examination Results</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong><?= htmlspecialchars($student_name) ?></strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Results List Card -->
                <section class="card list-card">
                    <div class="list-header">
                        <h3 class="card-title">Exam Performance History</h3>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>EXAM NAME</th>
                                    <th>EXAM DATE</th>
                                    <th>MARKS OBTAINED</th>
                                    <th>PERCENTAGE</th>
                                    <th class="text-center">GRADE</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($results_list) > 0): ?>
                                    <?php $idx = 1; foreach ($results_list as $r): 
                                        $obtained = floatval($r['obtained_marks']);
                                        $total = floatval($r['total_marks']);
                                        $pct = $total > 0 ? round(($obtained / $total) * 100, 1) : 0;
                                        $is_pass = $pct >= 33;
                                    ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><strong><?= htmlspecialchars($r['exam_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($r['exam_date']) ?></td>
                                            <td><span class="marks-display"><strong><?= htmlspecialchars($r['obtained_marks']) ?></strong> / <?= htmlspecialchars($r['total_marks']) ?></span></td>
                                            <td><?= $pct ?>%</td>
                                            <td class="text-center"><span class="badge badge-grade-a"><?= htmlspecialchars($r['grade'] ?: 'N/A') ?></span></td>
                                            <td class="text-center"><span class="status-pill status-<?= $is_pass ? 'pass' : 'fail' ?>"><?= $is_pass ? 'Pass' : 'Fail' ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">No examination results published yet.</td>
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
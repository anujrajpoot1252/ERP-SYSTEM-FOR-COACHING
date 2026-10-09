<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

// Fetch Students Fee Details
$students_query = mysqli_query($conn, "
    SELECT 
        s.id as student_id,
        s.admission_no,
        u.name as student_name,
        b.name as batch_name,
        COALESCE(c.fees, 0) as total_course_fee,
        COALESCE(SUM(f.amount), 0) as paid_amount
    FROM student s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN course c ON s.course_id = c.id
    LEFT JOIN batch b ON s.batch_id = b.id
    LEFT JOIN fees f ON f.student_id = s.id
    GROUP BY s.id
    ORDER BY s.id DESC
");

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
<<<<<<< HEAD
            <li>
                <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                    <span>Exams</span>
                </a>
            </li>
=======
                <li>
                    <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Exams</span>
                    </a>
                </li>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                <li>
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
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
<<<<<<< HEAD
                <!-- 3 Standalone Stat Boxes -->
=======
                <!-- 3 Dynamic Stat Boxes -->
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Batch Students</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                        </div>
<<<<<<< HEAD
                        <strong class="stat-num">64</strong>
=======
                        <strong class="stat-num"><?= $total_students ?></strong>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Fees Cleared</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>
<<<<<<< HEAD
                        <strong class="stat-num">48</strong>
=======
                        <strong class="stat-num"><?= $cleared_count ?></strong>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-top">
                            <span class="stat-title">Total Dues Pending</span>
                            <div class="stat-icon-wrap">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
<<<<<<< HEAD
                        <strong class="stat-num">16</strong>
=======
                        <strong class="stat-num"><?= $pending_count ?></strong>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                    </div>
                </div>

                <!-- Table Section -->
                <section class="card-section">
                    <div class="card-section-header">
                        <div class="header-left">
                            <h3 class="section-title"><i class="fa-solid fa-receipt"></i> Batch Students Fee Status</h3>
<<<<<<< HEAD
                            <p class="sub-counter">Showing 3 Students</p>
=======
                            <p class="sub-counter">Showing <?= $total_students ?> Students</p>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
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
<<<<<<< HEAD
                                <tr>
                                    <td class="font-mono">ADM-2026-001</td>
                                    <td class="font-semibold">Aman Verma</td>
                                    <td>JEE Morning Batch A</td>
                                    <td>₹25,000</td>
                                    <td>₹25,000</td>
                                    <td>₹0</td>
                                    <td><span class="status-tag status-paid">Paid</span></td>
                                    <td class="td-action"><a href="#" class="action-link"><i class="fa-solid fa-print"></i> Receipt</a></td>
                                </tr>
                                <tr>
                                    <td class="font-mono">ADM-2026-004</td>
                                    <td class="font-semibold">Rhea Gupta</td>
                                    <td>NEET Evening Batch B</td>
                                    <td>₹30,000</td>
                                    <td>₹15,000</td>
                                    <td>₹15,000</td>
                                    <td><span class="status-tag status-partial">Partial</span></td>
                                    <td class="td-action"><a href="payment.php?id=ADM-2026-004" class="action-link"><i class="fa-solid fa-credit-card"></i> Collect</a></td>
                                </tr>
                                <tr>
                                    <td class="font-mono">ADM-2026-009</td>
                                    <td class="font-semibold">Rohit Verma</td>
                                    <td>JEE Morning Batch A</td>
                                    <td>₹20,000</td>
                                    <td>₹0</td>
                                    <td>₹20,000</td>
                                    <td><span class="status-tag status-pending">Pending</span></td>
                                    <td class="td-action"><a href="payment.php?id=ADM-2026-009" class="action-link"><i class="fa-solid fa-credit-card"></i> Collect</a></td>
                                </tr>
=======
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
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
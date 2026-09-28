<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$user_id = $_SESSION['user_id'];
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "SELECT t.*, u.name, u.email FROM teacher t JOIN users u ON t.user_id = u.id WHERE t.user_id = $user_id"));
$teacher_id = $teacher['id'] ?? 0;

// Assigned Batches
$batches = mysqli_query($conn, "SELECT b.*, c.course_name FROM batch b LEFT JOIN course c ON b.course_id = c.id WHERE b.teacher_id = $teacher_id");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP/assets/css/style.css">
</head>
<body>
<div class="app-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Teacher Portal</span>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
                <a href="#"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/ERP/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="top-navbar">
            <h2>Teacher Dashboard</h2>
            <div class="user-profile">
                <span class="role-badge">TEACHER</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Teacher') ?></strong>
            </div>
        </div>

        <div class="page-body">
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-id-badge"></i> Faculty Profile</span>
                </div>
                <p><strong>Subject:</strong> <?= htmlspecialchars($teacher['subject'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email'] ?? 'N/A') ?></p>
                <p><strong>Joining Date:</strong> <?= htmlspecialchars($teacher['joining_date'] ?? 'N/A') ?></p>
            </div>

            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-layer-group"></i> Assigned Batches</span>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Batch Name</th>
                            <th>Course</th>
                            <th>Timing</th>
                            <th>Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($batches && mysqli_num_rows($batches) > 0): ?>
                            <?php while ($b = mysqli_fetch_assoc($batches)): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($b['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($b['course_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($b['timing_status'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($b['schedule'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color:#94a3b8;">No batches assigned yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>

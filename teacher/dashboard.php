<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);

$teacher = null;
$teacher_id = 0;
$batches = false;

if ($user_id > 0) {

    $teacherSql = "
        SELECT
            t.*,
            u.name,
            u.email
        FROM teacher t
        INNER JOIN users u
            ON t.user_id = u.id
        WHERE t.user_id = ?
        LIMIT 1
    ";

    $teacherStmt = mysqli_prepare($conn, $teacherSql);

    if ($teacherStmt) {

        mysqli_stmt_bind_param(
            $teacherStmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($teacherStmt);

        $teacherResult = mysqli_stmt_get_result(
            $teacherStmt
        );

        $teacher = mysqli_fetch_assoc(
            $teacherResult
        );

        mysqli_stmt_close($teacherStmt);
    }

    $teacher_id = (int) ($teacher['id'] ?? 0);
}

if ($teacher_id > 0) {

    $batchSql = "
        SELECT
            b.*,
            c.course_name
        FROM batch b
        LEFT JOIN course c
            ON b.course_id = c.id
        WHERE b.teacher_id = ?
        ORDER BY b.name ASC
    ";

    $batchStmt = mysqli_prepare(
        $conn,
        $batchSql
    );

    if ($batchStmt) {

        mysqli_stmt_bind_param(
            $batchStmt,
            "i",
            $teacher_id
        );

        mysqli_stmt_execute($batchStmt);

        $batches = mysqli_stmt_get_result(
            $batchStmt
        );
    }
}

$totalBatches = 0;

if ($batches) {
    $totalBatches = mysqli_num_rows($batches);
}

$teacherName = $teacher['name'] ?? 'Teacher';
$teacherEmail = $teacher['email'] ?? 'N/A';
$teacherSubject = $teacher['subject'] ?? 'N/A';
$joiningDate = $teacher['joining_date'] ?? 'N/A';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Teacher Portal - Dashboard</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
    <link rel="stylesheet" href="dashboard.css">

</head>

<body>
<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Teacher Portal</span>
        </div>
        <ul class="sidebar-menu">
            <li class="active">
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
            <li>
                <a href="fees.php">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <span>Fees</span>
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
    <main class="main-content">
        <header class="top-navbar">
            <div>
                <h2>Teacher Dashboard</h2>
                <p class="page-subtitle">
                    Manage your batches and teaching information
                </p>
            </div>
            <div class="user-profile">
                <span class="role-badge">
                    TEACHER
                </span>
                <i
                    class="fa-solid fa-circle-user fa-xl"
                    style="color:#64748b;"
                ></i>
                <strong>
                    <?= htmlspecialchars($teacherName) ?>
                </strong>
            </div>
        </header>
        <section class="page-body">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <span class="stat-label">
                            Assigned Batches
                        </span>
                        <strong class="stat-value">
                            <?= $totalBatches ?>
                        </strong>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <div>
                        <span class="stat-label">
                            Subject
                        </span>
                        <strong class="stat-value">
                            <?= htmlspecialchars($teacherSubject) ?>
                        </strong>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div>
                        <span class="stat-label">
                            Faculty Since
                        </span>
                        <strong class="stat-value">
                            <?= htmlspecialchars($joiningDate) ?>
                        </strong>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-title">
                    <span>
                        <i class="fa-solid fa-id-badge"></i>
                        Faculty Profile
                    </span>
                </div>
                <div class="profile-grid">
                    <div class="profile-item">
                        <span>Name</span>
                        <strong>
                            <?= htmlspecialchars($teacherName) ?>
                        </strong>
                    </div>
                    <div class="profile-item">
                        <span>Subject</span>
                        <strong>
                            <?= htmlspecialchars($teacherSubject) ?>
                        </strong>
                    </div>
                    <div class="profile-item">
                        <span>Email</span>
                        <strong>
                            <?= htmlspecialchars($teacherEmail) ?>
                        </strong>
                    </div>
                    <div class="profile-item">
                        <span>Joining Date</span>
                        <strong>
                            <?= htmlspecialchars($joiningDate) ?>
                        </strong>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header-row">
                    <div class="card-title">
                        <span>
                            <i class="fa-solid fa-layer-group"></i>
                            Assigned Batches
                        </span>
                    </div>
                    <span class="count-badge">
                        <?= $totalBatches ?> Batches
                    </span>
                </div>
                <?php if ($batches && $totalBatches > 0): ?>
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Course</th>
                                    <th>Timing</th>
                                    <th>Schedule</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($batch = mysqli_fetch_assoc($batches)): ?>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars(
                                                    $batch['name'] ?? 'Unnamed Batch'
                                                ) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(
                                                $batch['course_name'] ?? 'N/A'
                                            ) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(
                                                $batch['timing_status'] ?? 'N/A'
                                            ) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars(
                                                $batch['schedule'] ?? 'N/A'
                                            ) ?>
                                        </td>
                                        <td>
                                            <a
                                                href="attendance.php?batch_id=<?= (int) $batch['id'] ?>"
                                                class="table-action"
                                            >
                                                <i class="fa-solid fa-clipboard-user"></i>
                                                Attendance
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-layer-group"></i>
                        <h3>No Batches Assigned</h3>
                        <p>
                            You currently don't have any batches assigned.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
<?php
require_once __DIR__ . "/../includes/admin_layout.php";

if (($_SESSION['role'] ?? '') === 'superadmin') {
    header('Location: ' . app_url('superadmin/dashboard.php'));
    exit;
}

$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$metrics_stmt = mysqli_prepare($conn, "
    SELECT
        (SELECT COUNT(*) FROM student WHERE institute_id = ?) AS total_students,
        (SELECT COUNT(*) FROM teacher WHERE institute_id = ?) AS total_teachers,
        (SELECT COUNT(*) FROM batch WHERE institute_id = ?) AS total_batches,
        (SELECT COUNT(*) FROM course WHERE institute_id = ?) AS total_courses
");
mysqli_stmt_bind_param($metrics_stmt, 'iiii', $institute_id, $institute_id, $institute_id, $institute_id);
mysqli_stmt_execute($metrics_stmt);
$metrics = mysqli_fetch_assoc(mysqli_stmt_get_result($metrics_stmt)) ?: [];
mysqli_stmt_close($metrics_stmt);
$total_students = (int) ($metrics['total_students'] ?? 0);
$total_teachers = (int) ($metrics['total_teachers'] ?? 0);
$total_batches = (int) ($metrics['total_batches'] ?? 0);
$total_courses = (int) ($metrics['total_courses'] ?? 0);

// Fetch Recent Students
$recent_students = mysqli_prepare($conn, "
    SELECT s.admission_no, u.name, u.email, s.phone, c.course_name, b.name as batch_name 
    FROM student s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN course c ON s.course_id = c.id AND c.institute_id = s.institute_id
    LEFT JOIN batch b ON s.batch_id = b.id AND b.institute_id = s.institute_id
    WHERE s.institute_id = ?
    ORDER BY s.id DESC LIMIT 5
");
mysqli_stmt_bind_param($recent_students, 'i', $institute_id);
mysqli_stmt_execute($recent_students);
$recent_students_result = mysqli_stmt_get_result($recent_students);

render_admin_header("Dashboard Overview", "dashboard");
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="info">
            <h3><?= $total_students ?></h3>
            <p>Total Students</p>
        </div>
        <div class="icon bg-blue">
            <i class="fa-solid fa-user-graduate"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="info">
            <h3><?= $total_teachers ?></h3>
            <p>Total Teachers</p>
        </div>
        <div class="icon bg-green">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="info">
            <h3><?= $total_batches ?></h3>
            <p>Total Batches</p>
        </div>
        <div class="icon bg-purple">
            <i class="fa-solid fa-layer-group"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="info">
            <h3><?= $total_courses ?></h3>
            <p>Total Courses</p>
        </div>
        <div class="icon bg-orange">
            <i class="fa-solid fa-book-open"></i>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-title">
        <span><i class="fa-solid fa-clock-rotate-left"></i> Recently Admitted Students</span>
        <a href="students.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">View All</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Course</th>
                <th>Batch</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recent_students_result && mysqli_num_rows($recent_students_result) > 0): ?>
                <?php while ($st = mysqli_fetch_assoc($recent_students_result)): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($st['admission_no']) ?></strong></td>
                        <td><?= htmlspecialchars($st['name']) ?></td>
                        <td><?= htmlspecialchars($st['email']) ?></td>
                        <td><?= htmlspecialchars($st['phone']) ?></td>
                        <td><?= htmlspecialchars($st['course_name'] ?? 'Unassigned') ?></td>
                        <td><?= htmlspecialchars($st['batch_name'] ?? 'Unassigned') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#94a3b8;">No student records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php render_admin_footer(); ?>

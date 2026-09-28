<?php
require_once __DIR__ . "/../includes/admin_layout.php";

// Fetch Metrics
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM student"))['cnt'] ?? 0;
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM teacher"))['cnt'] ?? 0;
$total_batches  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM batch"))['cnt'] ?? 0;
$total_courses  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM course"))['cnt'] ?? 0;

// Fetch Recent Students
$recent_students = mysqli_query($conn, "
    SELECT s.admission_no, u.name, u.email, s.phone, c.course_name, b.name as batch_name 
    FROM student s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN course c ON s.course_id = c.id 
    LEFT JOIN batch b ON s.batch_id = b.id 
    ORDER BY s.id DESC LIMIT 5
");

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
            <p>Active Batches</p>
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
        <a href="/ERP/admin/students.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">View All</a>
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
            <?php if ($recent_students && mysqli_num_rows($recent_students) > 0): ?>
                <?php while ($st = mysqli_fetch_assoc($recent_students)): ?>
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

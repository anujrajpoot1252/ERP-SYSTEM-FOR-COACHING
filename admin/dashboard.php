<?php
require_once __DIR__ . "/../includes/admin_layout.php";

if (($_SESSION['role'] ?? '') === 'superadmin') {
    $total_institutes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM institute"))['cnt'] ?? 0;
    $active_subscriptions = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS cnt FROM institute
         WHERE subscription = 'active' AND (expiry IS NULL OR expiry >= CURDATE())"
    ))['cnt'] ?? 0;
    $total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM student"))['cnt'] ?? 0;
    $total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM teacher"))['cnt'] ?? 0;
    $institutes = mysqli_query($conn, "
        SELECT i.id, i.name, i.email, i.phone_no, i.subscription, i.expiry, i.created_at,
               (SELECT u.name FROM users u
                WHERE u.institute_id = i.id AND u.role = 'admin'
                ORDER BY u.id LIMIT 1) AS owner_name,
               (SELECT u.email FROM users u
                WHERE u.institute_id = i.id AND u.role = 'admin'
                ORDER BY u.id LIMIT 1) AS owner_email,
               CASE WHEN i.subscription = 'active' AND (i.expiry IS NULL OR i.expiry >= CURDATE())
                    THEN 'active' ELSE 'expired' END AS subscription_status
        FROM institute i
        ORDER BY i.id DESC
    ");

    render_admin_header("Superadmin Dashboard", "dashboard");
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="info"><h3><?= (int) $total_institutes ?></h3><p>Total Institutes</p></div>
            <div class="icon bg-blue"><i class="fa-solid fa-building"></i></div>
        </div>
        <div class="stat-card">
            <div class="info"><h3><?= (int) $active_subscriptions ?></h3><p>Active Subscriptions</p></div>
            <div class="icon bg-green"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="stat-card">
            <div class="info"><h3><?= (int) $total_students ?></h3><p>Students Across Institutes</p></div>
            <div class="icon bg-purple"><i class="fa-solid fa-user-graduate"></i></div>
        </div>
        <div class="stat-card">
            <div class="info"><h3><?= (int) $total_teachers ?></h3><p>Teachers Across Institutes</p></div>
            <div class="icon bg-orange"><i class="fa-solid fa-chalkboard-user"></i></div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-building"></i> Institutes</span>
            <a href="<?= htmlspecialchars(app_url('admin/institutes.php')) ?>" class="btn-primary dashboard-action">
                <i class="fa-solid fa-plus"></i> Create Institute
            </a>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Institute</th>
                        <th>Administrator</th>
                        <th>Contact</th>
                        <th>Subscription</th>
                        <th>Expires</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($institutes && mysqli_num_rows($institutes) > 0): ?>
                        <?php while ($institute = mysqli_fetch_assoc($institutes)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($institute['name']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($institute['owner_name'] ?? 'Not assigned') ?><br>
                                    <small class="muted"><?= htmlspecialchars($institute['owner_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($institute['email']) ?><br>
                                    <small class="muted"><?= htmlspecialchars($institute['phone_no']) ?></small>
                                </td>
                                <td><span class="badge badge-<?= $institute['subscription_status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars(strtoupper($institute['subscription_status'])) ?>
                                </span></td>
                                <td><?= $institute['expiry'] ? htmlspecialchars($institute['expiry']) : 'Lifetime' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empty-state">No institutes found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    render_admin_footer();
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
$recent_students = mysqli_query($conn, "
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

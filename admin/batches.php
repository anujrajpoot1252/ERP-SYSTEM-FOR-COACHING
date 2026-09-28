<?php
require_once __DIR__ . "/../includes/admin_layout.php";

$message = "";
$error = "";

// Handle Batch Addition
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_batch') {
    $name          = sanitize($conn, $_POST['name'] ?? '');
    $course_id     = intval($_POST['course_id'] ?? 0);
    $teacher_id    = intval($_POST['teacher_id'] ?? 0);
    $timing_status = sanitize($conn, $_POST['timing_status'] ?? '');
    $schedule      = sanitize($conn, $_POST['schedule'] ?? '');
    $start_date    = sanitize($conn, $_POST['start_date'] ?? '');
    $ending_date   = sanitize($conn, $_POST['ending_date'] ?? '');

    if (empty($name) || $course_id <= 0) {
        $error = "Batch Name and Course selection are required.";
    } else {
        $institute_id = $_SESSION['institute_id'] ?? 1;
        $teacher_val = $teacher_id > 0 ? $teacher_id : "NULL";

        $sql = "INSERT INTO batch (institute_id, course_id, teacher_id, name, start_date, ending_date, timing_status, schedule) 
                VALUES ($institute_id, $course_id, $teacher_val, '$name', '$start_date', '$ending_date', '$timing_status', '$schedule')";

        if (mysqli_query($conn, $sql)) {
            $message = "Batch '$name' created successfully!";
        } else {
            $error = "Failed to create batch: " . mysqli_error($conn);
        }
    }
}

// Fetch Courses and Teachers for dropdowns
$courses_res = mysqli_query($conn, "SELECT id, course_name FROM course WHERE status='active'");
$teachers_res = mysqli_query($conn, "SELECT t.id, u.name FROM teacher t JOIN users u ON t.user_id = u.id WHERE t.status='active'");

// Fetch All Batches List
$batches_list = mysqli_query($conn, "
    SELECT b.id, b.name as batch_name, c.course_name, u.name as teacher_name, b.timing_status, b.schedule, b.start_date 
    FROM batch b 
    LEFT JOIN course c ON b.course_id = c.id 
    LEFT JOIN teacher t ON b.teacher_id = t.id 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY b.id DESC
");

render_admin_header("Batches Management", "batches");
?>

<?php if (!empty($message)): ?>
    <div class="card" style="background: #dcfce7; border-left: 4px solid #16a34a; color: #15803d; margin-bottom: 20px;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="grid-2">
    <!-- Add Batch Form -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-plus-circle"></i> Create New Batch</span>
        </div>

        <form method="POST" action="batches.php">
            <input type="hidden" name="action" value="add_batch">

            <div class="form-group">
                <label>Batch Name *</label>
                <input type="text" name="name" class="form-control-simple" placeholder="e.g. JEE Morning Batch A" required>
            </div>

            <div class="form-group">
                <label>Select Course *</label>
                <select name="course_id" class="form-control-simple" required>
                    <option value="0">-- Select Course --</option>
                    <?php while ($c = mysqli_fetch_assoc($courses_res)): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Assign Teacher</label>
                <select name="teacher_id" class="form-control-simple">
                    <option value="0">-- Assign Faculty --</option>
                    <?php while ($t = mysqli_fetch_assoc($teachers_res)): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Class Timing</label>
                <input type="time" name="timing_status" class="form-control-simple" value="09:00">
            </div>

            <div class="form-group">
                <label>Schedule Days</label>
                <input type="text" name="schedule" class="form-control-simple" placeholder="e.g. Mon - Sat">
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control-simple" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="ending_date" class="form-control-simple">
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-plus"></i> Save Batch
            </button>
        </form>
    </div>

    <!-- Batches List Table -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-layer-group"></i> Active Batches</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Batch Name</th>
                        <th>Course</th>
                        <th>Assigned Teacher</th>
                        <th>Timing & Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($batches_list && mysqli_num_rows($batches_list) > 0): ?>
                        <?php while ($b = mysqli_fetch_assoc($batches_list)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['batch_name']) ?></strong></td>
                                <td><?= htmlspecialchars($b['course_name'] ?? 'Unassigned') ?></td>
                                <td><?= htmlspecialchars($b['teacher_name'] ?? 'Not Assigned') ?></td>
                                <td>
                                    <?= htmlspecialchars($b['timing_status'] ?? 'N/A') ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($b['schedule'] ?? '') ?></small>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#94a3b8;">No batch records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

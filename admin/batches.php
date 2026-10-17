<?php
require_once __DIR__ . "/../includes/admin_layout.php";

require_institute_admin();
$message = $error = '';
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$csrf_token = admin_csrf_token();
$form = ['id' => 0, 'name' => '', 'course_id' => 0, 'teacher_id' => 0,
    'timing_status' => '', 'schedule' => '', 'start_date' => '', 'ending_date' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if (!admin_validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($action === 'delete_batch') {
        $batch_id = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT);
        if (!$batch_id || $batch_id < 1) {
            $error = 'Select a valid batch.';
        } else {
            $stmt = mysqli_prepare($conn, 'DELETE FROM batch WHERE id = ? AND institute_id = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $batch_id, $institute_id);
            mysqli_stmt_execute($stmt);
            $message = mysqli_stmt_affected_rows($stmt) > 0
                ? 'Batch and its attendance records deleted.'
                : 'Batch was not found in this institute.';
            mysqli_stmt_close($stmt);
        }
    } elseif (in_array($action, ['add_batch', 'update_batch'], true)) {
        $form['name'] = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
        $form['schedule'] = is_string($_POST['schedule'] ?? null) ? trim($_POST['schedule']) : '';
        $form['timing_status'] = is_string($_POST['timing_status'] ?? null) ? trim($_POST['timing_status']) : '';
        $form['start_date'] = is_string($_POST['start_date'] ?? null) ? trim($_POST['start_date']) : '';
        $form['ending_date'] = is_string($_POST['ending_date'] ?? null) ? trim($_POST['ending_date']) : '';
        $form['id'] = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
        $form['course_id'] = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT) ?: 0;
        $form['teacher_id'] = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT) ?: 0;
        $is_edit = $action === 'update_batch';
        $valid_date = static function ($date) {
            return $date === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) &&
                checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4)));
        };

        if ($form['name'] === '' || strlen($form['name']) > 100 ||
            $form['course_id'] < 1 || strlen($form['schedule']) > 255 ||
            ($form['timing_status'] !== '' && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $form['timing_status'])) ||
            !$valid_date($form['start_date']) || !$valid_date($form['ending_date']) ||
            ($form['start_date'] !== '' && $form['ending_date'] !== '' && $form['ending_date'] < $form['start_date'])) {
            $error = 'Enter a batch name, valid course, and valid schedule dates and time.';
        } elseif (($is_edit && $form['id'] < 1) || (!$is_edit && $form['id'] !== 0)) {
            $error = 'Select a valid batch to update.';
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT id FROM course WHERE id = ? AND institute_id = ?');
            mysqli_stmt_bind_param($stmt, 'ii', $form['course_id'], $institute_id);
            mysqli_stmt_execute($stmt);
            $course_ok = mysqli_stmt_get_result($stmt)->num_rows === 1;
            mysqli_stmt_close($stmt);
            $teacher_ok = $form['teacher_id'] === 0;
            if (!$teacher_ok) {
                $stmt = mysqli_prepare($conn, "
                    SELECT id FROM teacher
                    WHERE id = ? AND institute_id = ? AND status = 'active'
                ");
                mysqli_stmt_bind_param($stmt, 'ii', $form['teacher_id'], $institute_id);
                mysqli_stmt_execute($stmt);
                $teacher_ok = mysqli_stmt_get_result($stmt)->num_rows === 1;
                mysqli_stmt_close($stmt);
            }
            if (!$course_ok || !$teacher_ok) {
                $error = 'Select a course and teacher from this institute.';
            } else {
                try {
                    if ($is_edit) {
                        $stmt = mysqli_prepare($conn, "
                            UPDATE batch SET course_id = ?, teacher_id = NULLIF(?, 0), name = ?,
                                start_date = NULLIF(?, ''), ending_date = NULLIF(?, ''),
                                timing_status = NULLIF(?, ''), schedule = NULLIF(?, '')
                            WHERE id = ? AND institute_id = ?
                        ");
                        mysqli_stmt_bind_param($stmt, 'iisssssii', $form['course_id'], $form['teacher_id'], $form['name'],
                            $form['start_date'], $form['ending_date'], $form['timing_status'], $form['schedule'],
                            $form['id'], $institute_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        $message = 'Batch updated successfully.';
                    } else {
                        $stmt = mysqli_prepare($conn, "
                            INSERT INTO batch (institute_id, course_id, teacher_id, name, start_date, ending_date, timing_status, schedule)
                            VALUES (?, ?, NULLIF(?, 0), ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''))
                        ");
                        mysqli_stmt_bind_param($stmt, 'iiisssss', $institute_id, $form['course_id'], $form['teacher_id'],
                            $form['name'], $form['start_date'], $form['ending_date'], $form['timing_status'], $form['schedule']);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        $message = 'Batch created successfully.';
                    }
                    $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } catch (Throwable $exception) {
                    $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'The batch could not be saved.';
                }
            }
        }
    } else {
        $error = 'Unsupported batch action.';
    }
}

$edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
if ($edit_id > 0 && $error === '') {
    $stmt = mysqli_prepare($conn, "
        SELECT id, name, course_id, teacher_id, timing_status, schedule, start_date, ending_date
        FROM batch WHERE id = ? AND institute_id = ?
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $edit_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $batch_to_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($batch_to_edit) {
        $form = array_merge($form, $batch_to_edit);
    } else {
        $error = 'Batch was not found in this institute.';
    }
}

$courses_stmt = mysqli_prepare($conn, "
    SELECT id, course_name FROM course
    WHERE institute_id = ? AND (status = 'active' OR id = ?)
    ORDER BY course_name
");
mysqli_stmt_bind_param($courses_stmt, 'ii', $institute_id, $form['course_id']);
mysqli_stmt_execute($courses_stmt);
$courses_res = mysqli_stmt_get_result($courses_stmt);
$teachers_stmt = mysqli_prepare($conn, "
    SELECT t.id, u.name FROM teacher t
    INNER JOIN users u ON u.id = t.user_id
    WHERE t.status = 'active' AND t.institute_id = ? AND u.role = 'teacher'
    ORDER BY u.name
");
mysqli_stmt_bind_param($teachers_stmt, 'i', $institute_id);
mysqli_stmt_execute($teachers_stmt);
$teachers_res = mysqli_stmt_get_result($teachers_stmt);
$batches_stmt = mysqli_prepare($conn, "
    SELECT b.id, b.name AS batch_name, c.course_name, u.name AS teacher_name,
           b.timing_status, b.schedule, b.start_date, b.ending_date
    FROM batch b
    LEFT JOIN course c ON b.course_id = c.id AND c.institute_id = b.institute_id
    LEFT JOIN teacher t ON b.teacher_id = t.id AND t.institute_id = b.institute_id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE b.institute_id = ?
    ORDER BY b.id DESC
");
mysqli_stmt_bind_param($batches_stmt, 'i', $institute_id);
mysqli_stmt_execute($batches_stmt);
$batches_list = mysqli_stmt_get_result($batches_stmt);

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
            <span><i class="fa-solid fa-plus-circle"></i> <?= $form['id'] ? 'Edit Batch' : 'Create New Batch' ?></span>
        </div>

        <form method="POST" action="batches.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="<?= $form['id'] ? 'update_batch' : 'add_batch' ?>">
            <input type="hidden" name="batch_id" value="<?= (int) $form['id'] ?>">

            <div class="form-group">
                <label>Batch Name *</label>
                <input type="text" name="name" class="form-control-simple" maxlength="100" placeholder="e.g. JEE Morning Batch A" required value="<?= htmlspecialchars($form['name']) ?>">
            </div>

            <div class="form-group">
                <label>Select Course *</label>
                <select name="course_id" class="form-control-simple" required>
                    <option value="0">-- Select Course --</option>
                    <?php while ($c = mysqli_fetch_assoc($courses_res)): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) $form['course_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Assign Teacher</label>
                <select name="teacher_id" class="form-control-simple">
                    <option value="0">-- Assign Faculty --</option>
                    <?php while ($t = mysqli_fetch_assoc($teachers_res)): ?>
                        <option value="<?= (int) $t['id'] ?>" <?= (int) $form['teacher_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Class Timing</label>
                <input type="time" name="timing_status" class="form-control-simple" value="<?= htmlspecialchars(substr((string) $form['timing_status'], 0, 5)) ?>">
            </div>

            <div class="form-group">
                <label>Schedule Days</label>
                <input type="text" name="schedule" class="form-control-simple" maxlength="255" placeholder="e.g. Mon - Sat" value="<?= htmlspecialchars($form['schedule'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control-simple" value="<?= htmlspecialchars($form['start_date'] ?: date('Y-m-d')) ?>">
            </div>

            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="ending_date" class="form-control-simple" value="<?= htmlspecialchars($form['ending_date'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-floppy-disk"></i> <?= $form['id'] ? 'Update Batch' : 'Save Batch' ?>
            </button>
            <?php if ($form['id']): ?> <a href="batches.php" class="btn-primary" style="margin-left: 8px;">Cancel</a><?php endif; ?>
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
                        <th>Dates</th>
                        <th>Actions</th>
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
                                <td><?= htmlspecialchars($b['start_date'] ?? 'N/A') ?><br><?= htmlspecialchars($b['ending_date'] ?? 'No end date') ?></td>
                                <td>
                                    <a class="btn-edit" href="batches.php?edit_id=<?= (int) $b['id'] ?>">Edit</a>
                                    <form method="POST" action="batches.php" style="display:inline" onsubmit="return confirm('Delete this batch and its attendance records?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_batch">
                                        <input type="hidden" name="batch_id" value="<?= (int) $b['id'] ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#94a3b8;">No batch records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

<?php
require_once __DIR__ . "/../includes/admin_layout.php";

require_institute_admin();
$message = $error = '';
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$csrf_token = admin_csrf_token();
$form = [
    'id' => 0, 'name' => '', 'email' => '', 'phone' => '',
    'parent_name' => '', 'parent_phone' => '', 'course_id' => 0,
    'batch_id' => 0, 'status' => 'active'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if (!admin_validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($action === 'delete_student') {
        $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        if (!$student_id || $student_id < 1) {
            $error = 'Select a valid student.';
        } else {
            $stmt = mysqli_prepare($conn, "
                DELETE u FROM users u
                INNER JOIN student s ON s.user_id = u.id
                WHERE s.id = ? AND s.institute_id = ? AND u.role = 'student'
            ");
            mysqli_stmt_bind_param($stmt, 'ii', $student_id, $institute_id);
            mysqli_stmt_execute($stmt);
            $message = mysqli_stmt_affected_rows($stmt) > 0
                ? 'Student and linked account deleted.'
                : 'Student was not found in this institute.';
            mysqli_stmt_close($stmt);
        }
    } elseif (in_array($action, ['add_student', 'update_student'], true)) {
        foreach (['name', 'email', 'phone', 'parent_name', 'parent_phone'] as $field) {
            $value = $_POST[$field] ?? '';
            $form[$field] = is_string($value) ? trim($value) : '';
        }
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $form['id'] = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?: 0;
        $form['course_id'] = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT) ?: 0;
        $form['batch_id'] = filter_input(INPUT_POST, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
        $form['status'] = is_string($_POST['status'] ?? null) ? $_POST['status'] : 'active';
        $is_edit = $action === 'update_student';

        if ($form['name'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL) ||
            $form['phone'] === '' || $form['parent_name'] === '' || $form['parent_phone'] === '' ||
            strlen($form['name']) > 100 || strlen($form['email']) > 255 ||
            strlen($form['phone']) > 15 || strlen($form['parent_name']) > 100 ||
            strlen($form['parent_phone']) > 15 ||
            !in_array($form['status'], ['active', 'inactive'], true)) {
            $error = 'Enter valid student details and status.';
        } elseif ((!$is_edit && strlen($password) < 8) || ($password !== '' && strlen($password) < 8)) {
            $error = 'Passwords must be at least 8 characters.';
        } elseif (($is_edit && $form['id'] < 1) || (!$is_edit && $form['id'] !== 0)) {
            $error = 'Select a valid student to update.';
        } else {
            $course_ok = $form['course_id'] === 0;
            if (!$course_ok) {
                $stmt = mysqli_prepare($conn, 'SELECT id FROM course WHERE id = ? AND institute_id = ?');
                mysqli_stmt_bind_param($stmt, 'ii', $form['course_id'], $institute_id);
                mysqli_stmt_execute($stmt);
                $course_ok = mysqli_stmt_get_result($stmt)->num_rows === 1;
                mysqli_stmt_close($stmt);
            }
            $batch_ok = $form['batch_id'] === 0;
            if (!$batch_ok) {
                $stmt = mysqli_prepare($conn, "
                    SELECT id FROM batch
                    WHERE id = ? AND institute_id = ? AND (? = 0 OR course_id = ?)
                ");
                mysqli_stmt_bind_param($stmt, 'iiii', $form['batch_id'], $institute_id, $form['course_id'], $form['course_id']);
                mysqli_stmt_execute($stmt);
                $batch_ok = mysqli_stmt_get_result($stmt)->num_rows === 1;
                mysqli_stmt_close($stmt);
            }

            if (!$course_ok || !$batch_ok) {
                $error = 'Select a course and matching batch from this institute.';
            } else {
                try {
                    mysqli_begin_transaction($conn);
                    if ($is_edit) {
                        $stmt = mysqli_prepare($conn, "
                            SELECT s.user_id FROM student s
                            INNER JOIN users u ON u.id = s.user_id
                            WHERE s.id = ? AND s.institute_id = ? AND u.role = 'student'
                        ");
                        mysqli_stmt_bind_param($stmt, 'ii', $form['id'], $institute_id);
                        mysqli_stmt_execute($stmt);
                        $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                        mysqli_stmt_close($stmt);
                        if (!$record) {
                            throw new RuntimeException('Student was not found in this institute.');
                        }
                        $user_id = (int) $record['user_id'];
                        $stmt = mysqli_prepare($conn, 'UPDATE users SET name = ?, email = ?, status = ? WHERE id = ?');
                        mysqli_stmt_bind_param($stmt, 'sssi', $form['name'], $form['email'], $form['status'], $user_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        if ($password !== '') {
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id = ?');
                            mysqli_stmt_bind_param($stmt, 'si', $password_hash, $user_id);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                        }
                        $stmt = mysqli_prepare($conn, "
                            UPDATE student SET phone = ?, parent_name = ?, parent_phone = ?,
                                course_id = NULLIF(?, 0), batch_id = NULLIF(?, 0), status = ?
                            WHERE id = ? AND institute_id = ?
                        ");
                        mysqli_stmt_bind_param($stmt, 'sssiisii', $form['phone'], $form['parent_name'], $form['parent_phone'],
                            $form['course_id'], $form['batch_id'], $form['status'], $form['id'], $institute_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        $message = 'Student updated successfully.';
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = mysqli_prepare($conn, "
                            INSERT INTO users (name, email, password, role, institute_id, status)
                            VALUES (?, ?, ?, 'student', ?, 'active')
                        ");
                        mysqli_stmt_bind_param($stmt, 'sssi', $form['name'], $form['email'], $password_hash, $institute_id);
                        mysqli_stmt_execute($stmt);
                        $user_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($stmt);
                        $admission_no = 'ADM-' . date('Y') . '-' . str_pad((string) $user_id, 3, '0', STR_PAD_LEFT);
                        $stmt = mysqli_prepare($conn, "
                            INSERT INTO student
                                (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status)
                            VALUES (?, ?, ?, ?, ?, ?, NULLIF(?, 0), NULLIF(?, 0), 'active')
                        ");
                        mysqli_stmt_bind_param($stmt, 'iissssii', $user_id, $institute_id, $admission_no, $form['phone'],
                            $form['parent_name'], $form['parent_phone'], $form['course_id'], $form['batch_id']);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        $message = "Student created with admission number $admission_no.";
                    }
                    mysqli_commit($conn);
                    $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } catch (Throwable $exception) {
                    mysqli_rollback($conn);
                    $error = (int) $exception->getCode() === 1062
                        ? 'An account with this email address already exists.'
                        : ($exception instanceof RuntimeException ? $exception->getMessage() : 'The student could not be saved.');
                }
            }
        }
    } else {
        $error = 'Unsupported student action.';
    }
}

$edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
if ($edit_id > 0 && $error === '') {
    $stmt = mysqli_prepare($conn, "
        SELECT s.id, s.phone, s.parent_name, s.parent_phone, s.course_id, s.batch_id, s.status,
               u.name, u.email
        FROM student s INNER JOIN users u ON u.id = s.user_id
        WHERE s.id = ? AND s.institute_id = ? AND u.role = 'student'
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $edit_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $student_to_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($student_to_edit) {
        $form = array_merge($form, $student_to_edit);
    } else {
        $error = 'Student was not found in this institute.';
    }
}

$courses_stmt = mysqli_prepare($conn, "SELECT id, course_name FROM course WHERE status = 'active' AND institute_id = ? ORDER BY course_name");
mysqli_stmt_bind_param($courses_stmt, 'i', $institute_id);
mysqli_stmt_execute($courses_stmt);
$courses_res = mysqli_stmt_get_result($courses_stmt);
$batches_stmt = mysqli_prepare($conn, "SELECT id, name FROM batch WHERE institute_id = ? ORDER BY name");
mysqli_stmt_bind_param($batches_stmt, 'i', $institute_id);
mysqli_stmt_execute($batches_stmt);
$batches_res = mysqli_stmt_get_result($batches_stmt);
$students_stmt = mysqli_prepare($conn, "
    SELECT s.id, s.admission_no, u.name, u.email, s.phone, s.parent_name, s.parent_phone,
           c.course_name, b.name AS batch_name, s.status
    FROM student s
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN course c ON s.course_id = c.id AND c.institute_id = s.institute_id
    LEFT JOIN batch b ON s.batch_id = b.id AND b.institute_id = s.institute_id
    WHERE s.institute_id = ? AND u.role = 'student'
    ORDER BY s.id DESC
");
mysqli_stmt_bind_param($students_stmt, 'i', $institute_id);
mysqli_stmt_execute($students_stmt);
$students_list = mysqli_stmt_get_result($students_stmt);

render_admin_header("Students Management", "students");
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
    <!-- Add Student Form -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-user-plus"></i> <?= $form['id'] ? 'Edit Student' : 'Add New Student' ?></span>
        </div>

        <form method="POST" action="students.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="<?= $form['id'] ? 'update_student' : 'add_student' ?>">
            <input type="hidden" name="student_id" value="<?= (int) $form['id'] ?>">

            <div class="form-group">
                <label>Student Full Name *</label>
                <input type="text" name="name" class="form-control-simple" maxlength="100" placeholder="Full Name" required value="<?= htmlspecialchars($form['name']) ?>">
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control-simple" maxlength="255" placeholder="student@example.com" required value="<?= htmlspecialchars($form['email']) ?>">
            </div>

            <div class="form-group">
                <label>Password <?= $form['id'] ? '(leave blank to keep current)' : '*' ?></label>
                <input type="password" name="password" class="form-control-simple" minlength="8" autocomplete="new-password" <?= $form['id'] ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" class="form-control-simple" maxlength="15" placeholder="10-digit mobile number" required value="<?= htmlspecialchars($form['phone']) ?>">
            </div>

            <div class="form-group">
                <label>Parent Name *</label>
                <input type="text" name="parent_name" class="form-control-simple" maxlength="100" placeholder="Father/Mother Name" required value="<?= htmlspecialchars($form['parent_name']) ?>">
            </div>

            <div class="form-group">
                <label>Parent Contact *</label>
                <input type="text" name="parent_phone" class="form-control-simple" maxlength="15" placeholder="Parent phone number" required value="<?= htmlspecialchars($form['parent_phone']) ?>">
            </div>

            <div class="form-group">
                <label>Select Course</label>
                <select name="course_id" class="form-control-simple">
                    <option value="0">-- Select Course --</option>
                    <?php while ($c = mysqli_fetch_assoc($courses_res)): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) $form['course_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Select Batch</label>
                <select name="batch_id" class="form-control-simple">
                    <option value="0">-- Select Batch --</option>
                    <?php while ($b = mysqli_fetch_assoc($batches_res)): ?>
                        <option value="<?= (int) $b['id'] ?>" <?= (int) $form['batch_id'] === (int) $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if ($form['id']): ?>
                <div class="form-group">
                    <label>Account Status</label>
                    <select name="status" class="form-control-simple">
                        <option value="active" <?= $form['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-floppy-disk"></i> <?= $form['id'] ? 'Update Student' : 'Save Student' ?>
            </button>
            <?php if ($form['id']): ?> <a href="students.php" class="btn-primary" style="margin-left: 8px;">Cancel</a><?php endif; ?>
        </form>
    </div>

    <!-- Students List Table -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-list"></i> Registered Students</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Adm No</th>
                        <th>Name</th>
                        <th>Email & Phone</th>
                        <th>Parent Details</th>
                        <th>Course & Batch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_list && mysqli_num_rows($students_list) > 0): ?>
                        <?php while ($st = mysqli_fetch_assoc($students_list)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($st['admission_no']) ?></strong></td>
                                <td><?= htmlspecialchars($st['name']) ?></td>
                                <td>
                                    <?= htmlspecialchars($st['email']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['phone']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($st['parent_name']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['parent_phone']) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($st['course_name'] ?? 'None') ?></strong><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['batch_name'] ?? 'Unassigned') ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $st['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= strtoupper(htmlspecialchars($st['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="students.php?edit_id=<?= (int) $st['id'] ?>">Edit</a>
                                    <form method="POST" action="students.php" style="display:inline" onsubmit="return confirm('Delete this student account and its linked records?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_student">
                                        <input type="hidden" name="student_id" value="<?= (int) $st['id'] ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; color:#94a3b8;">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

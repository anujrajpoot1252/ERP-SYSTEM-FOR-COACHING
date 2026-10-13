<?php
require_once __DIR__ . "/../includes/admin_layout.php";

require_institute_admin();
$message = $error = '';
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$csrf_token = admin_csrf_token();
$form = ['id' => 0, 'name' => '', 'email' => '', 'phone_no' => '', 'subject' => '',
    'joining_date' => date('Y-m-d'), 'status' => 'active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if (!admin_validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($action === 'delete_teacher') {
        $teacher_id = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT);
        if (!$teacher_id || $teacher_id < 1) {
            $error = 'Select a valid teacher.';
        } else {
            $stmt = mysqli_prepare($conn, "
                DELETE u FROM users u
                INNER JOIN teacher t ON t.user_id = u.id
                WHERE t.id = ? AND t.institute_id = ? AND u.role = 'teacher'
            ");
            mysqli_stmt_bind_param($stmt, 'ii', $teacher_id, $institute_id);
            mysqli_stmt_execute($stmt);
            $message = mysqli_stmt_affected_rows($stmt) > 0
                ? 'Teacher and linked account deleted.'
                : 'Teacher was not found in this institute.';
            mysqli_stmt_close($stmt);
        }
    } elseif (in_array($action, ['add_teacher', 'update_teacher'], true)) {
        foreach (['name', 'email', 'phone_no', 'subject', 'joining_date'] as $field) {
            $value = $_POST[$field] ?? '';
            $form[$field] = is_string($value) ? trim($value) : '';
        }
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $form['id'] = filter_input(INPUT_POST, 'teacher_id', FILTER_VALIDATE_INT) ?: 0;
        $form['status'] = is_string($_POST['status'] ?? null) ? $_POST['status'] : 'active';
        $is_edit = $action === 'update_teacher';
        $date_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['joining_date']) &&
            checkdate((int) substr($form['joining_date'], 5, 2), (int) substr($form['joining_date'], 8, 2), (int) substr($form['joining_date'], 0, 4));

        if ($form['name'] === '' || strlen($form['name']) > 100 ||
            !filter_var($form['email'], FILTER_VALIDATE_EMAIL) || strlen($form['email']) > 255 ||
            $form['phone_no'] === '' || strlen($form['phone_no']) > 15 ||
            $form['subject'] === '' || strlen($form['subject']) > 100 ||
            !$date_valid || !in_array($form['status'], ['active', 'inactive'], true)) {
            $error = 'Enter valid teacher details and status.';
        } elseif ((!$is_edit && strlen($password) < 8) || ($password !== '' && strlen($password) < 8)) {
            $error = 'Passwords must be at least 8 characters.';
        } elseif (($is_edit && $form['id'] < 1) || (!$is_edit && $form['id'] !== 0)) {
            $error = 'Select a valid teacher to update.';
        } else {
            try {
                mysqli_begin_transaction($conn);
                if ($is_edit) {
                    $stmt = mysqli_prepare($conn, "
                        SELECT t.user_id FROM teacher t
                        INNER JOIN users u ON u.id = t.user_id
                        WHERE t.id = ? AND t.institute_id = ? AND u.role = 'teacher'
                    ");
                    mysqli_stmt_bind_param($stmt, 'ii', $form['id'], $institute_id);
                    mysqli_stmt_execute($stmt);
                    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);
                    if (!$record) {
                        throw new RuntimeException('Teacher was not found in this institute.');
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
                        UPDATE teacher SET phone_no = ?, subject = ?, joining_date = ?, status = ?
                        WHERE id = ? AND institute_id = ?
                    ");
                    mysqli_stmt_bind_param($stmt, 'ssssii', $form['phone_no'], $form['subject'], $form['joining_date'],
                        $form['status'], $form['id'], $institute_id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $message = 'Teacher updated successfully.';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = mysqli_prepare($conn, "
                        INSERT INTO users (name, email, password, role, institute_id, status)
                        VALUES (?, ?, ?, 'teacher', ?, 'active')
                    ");
                    mysqli_stmt_bind_param($stmt, 'sssi', $form['name'], $form['email'], $password_hash, $institute_id);
                    mysqli_stmt_execute($stmt);
                    $user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);
                    $stmt = mysqli_prepare($conn, "
                        INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status)
                        VALUES (?, ?, ?, ?, ?, 'active')
                    ");
                    mysqli_stmt_bind_param($stmt, 'iisss', $user_id, $institute_id, $form['phone_no'], $form['subject'], $form['joining_date']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $message = 'Teacher added successfully.';
                }
                mysqli_commit($conn);
                $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Throwable $exception) {
                mysqli_rollback($conn);
                $error = (int) $exception->getCode() === 1062
                    ? 'An account with this email address already exists.'
                    : ($exception instanceof RuntimeException ? $exception->getMessage() : 'The teacher could not be saved.');
            }
        }
    } else {
        $error = 'Unsupported teacher action.';
    }
}

$edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
if ($edit_id > 0 && $error === '') {
    $stmt = mysqli_prepare($conn, "
        SELECT t.id, u.name, u.email, t.phone_no, t.subject, t.joining_date, t.status
        FROM teacher t INNER JOIN users u ON u.id = t.user_id
        WHERE t.id = ? AND t.institute_id = ? AND u.role = 'teacher'
    ");
    mysqli_stmt_bind_param($stmt, 'ii', $edit_id, $institute_id);
    mysqli_stmt_execute($stmt);
    $teacher_to_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($teacher_to_edit) {
        $form = array_merge($form, $teacher_to_edit);
    } else {
        $error = 'Teacher was not found in this institute.';
    }
}

$teachers_stmt = mysqli_prepare($conn, "
    SELECT t.id, u.name, u.email, t.phone_no, t.subject, t.joining_date, t.status
    FROM teacher t INNER JOIN users u ON t.user_id = u.id
    WHERE t.institute_id = ? AND u.role = 'teacher'
    ORDER BY t.id DESC
");
mysqli_stmt_bind_param($teachers_stmt, 'i', $institute_id);
mysqli_stmt_execute($teachers_stmt);
$teachers_list = mysqli_stmt_get_result($teachers_stmt);

render_admin_header("Teachers Management", "teachers");
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
    <!-- Add Teacher Form -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-user-plus"></i> <?= $form['id'] ? 'Edit Teacher' : 'Add New Teacher' ?></span>
        </div>

        <form method="POST" action="teachers.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="<?= $form['id'] ? 'update_teacher' : 'add_teacher' ?>">
            <input type="hidden" name="teacher_id" value="<?= (int) $form['id'] ?>">

            <div class="form-group">
                <label>Teacher Name *</label>
                <input type="text" name="name" class="form-control-simple" maxlength="100" placeholder="Full Name" required value="<?= htmlspecialchars($form['name']) ?>">
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control-simple" maxlength="255" placeholder="teacher@example.com" required value="<?= htmlspecialchars($form['email']) ?>">
            </div>

            <div class="form-group">
                <label>Password <?= $form['id'] ? '(leave blank to keep current)' : '*' ?></label>
                <input type="password" name="password" class="form-control-simple" minlength="8" autocomplete="new-password" <?= $form['id'] ? '' : 'required' ?>>
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone_no" class="form-control-simple" maxlength="15" placeholder="10-digit phone number" required value="<?= htmlspecialchars($form['phone_no']) ?>">
            </div>

            <div class="form-group">
                <label>Subject Specialization *</label>
                <input type="text" name="subject" class="form-control-simple" maxlength="100" placeholder="e.g. Mathematics, Chemistry" required value="<?= htmlspecialchars($form['subject']) ?>">
            </div>

            <div class="form-group">
                <label>Joining Date</label>
                <input type="date" name="joining_date" class="form-control-simple" required value="<?= htmlspecialchars($form['joining_date']) ?>">
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
                <i class="fa-solid fa-floppy-disk"></i> <?= $form['id'] ? 'Update Teacher' : 'Save Teacher' ?>
            </button>
            <?php if ($form['id']): ?> <a href="teachers.php" class="btn-primary" style="margin-left: 8px;">Cancel</a><?php endif; ?>
        </form>
    </div>

    <!-- Teachers List Table -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-chalkboard-user"></i> Faculty Members</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Email & Phone</th>
                        <th>Subject</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($teachers_list && mysqli_num_rows($teachers_list) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($teachers_list)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($t['email']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($t['phone_no']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($t['subject']) ?></td>
                                <td><?= htmlspecialchars($t['joining_date']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= strtoupper(htmlspecialchars($t['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="teachers.php?edit_id=<?= (int) $t['id'] ?>">Edit</a>
                                    <form method="POST" action="teachers.php" style="display:inline" onsubmit="return confirm('Delete this teacher account? Assigned batches will become unassigned.');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="action" value="delete_teacher">
                                        <input type="hidden" name="teacher_id" value="<?= (int) $t['id'] ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#94a3b8;">No faculty records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

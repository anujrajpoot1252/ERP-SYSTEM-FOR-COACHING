<?php
require_once __DIR__ . "/../includes/admin_layout.php";

if (($_SESSION['role'] ?? '') !== 'superadmin') {
    header('Location: ' . app_url('admin/dashboard.php'));
    exit;
}

$csrf_token = admin_csrf_token();
$message = "";
$error = "";
$form = [
    'id' => 0,
    'institute_name' => '',
    'owner_name' => '',
    'email' => '',
    'phone_no' => '',
    'address' => '',
    'subscription_period' => '12',
    'subscription' => 'active',
    'expiry' => '',
    'has_owner' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_institute') {
    $form['id'] = filter_input(INPUT_POST, 'institute_id', FILTER_VALIDATE_INT) ?: 0;
    foreach (['institute_name', 'owner_name', 'email', 'phone_no', 'address', 'expiry', 'subscription'] as $field) {
        $value = $_POST[$field] ?? '';
        $form[$field] = is_string($value) ? trim($value) : '';
    }
    $csrf = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
    $owner_present = false;
    if ($form['id'] > 0) {
        $owner_check = mysqli_prepare($conn, "SELECT id FROM users WHERE institute_id = ? AND role = 'admin' LIMIT 1");
        mysqli_stmt_bind_param($owner_check, 'i', $form['id']);
        mysqli_stmt_execute($owner_check);
        $owner_present = mysqli_stmt_get_result($owner_check)->num_rows > 0;
        mysqli_stmt_close($owner_check);
    }
    $date_valid = $form['expiry'] === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['expiry']) &&
        checkdate((int) substr($form['expiry'], 5, 2), (int) substr($form['expiry'], 8, 2), (int) substr($form['expiry'], 0, 4)));

    if (!admin_validate_csrf($csrf)) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($form['id'] < 1 || $form['institute_name'] === '' ||
        ($owner_present && $form['owner_name'] === '') ||
        $form['phone_no'] === '' || $form['address'] === '' ||
        !filter_var($form['email'], FILTER_VALIDATE_EMAIL) ||
        strlen($form['institute_name']) > 150 || strlen($form['owner_name']) > 100 ||
        strlen($form['email']) > 255 || strlen($form['phone_no']) > 15 ||
        !in_array($form['subscription'], ['active', 'expired'], true) || !$date_valid) {
        $error = 'Enter valid institute and administrator details.';
    } else {
        try {
            mysqli_begin_transaction($conn);
            $stmt = mysqli_prepare($conn, "
                SELECT id FROM users
                WHERE institute_id = ? AND role = 'admin'
                ORDER BY id LIMIT 1
            ");
            mysqli_stmt_bind_param($stmt, 'i', $form['id']);
            mysqli_stmt_execute($stmt);
            $owner = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($conn, "
                UPDATE institute SET name = ?, email = ?, phone_no = ?, address = ?,
                    subscription = ?, expiry = NULLIF(?, '')
                WHERE id = ?
            ");
            mysqli_stmt_bind_param($stmt, 'ssssssi', $form['institute_name'], $form['email'], $form['phone_no'],
                $form['address'], $form['subscription'], $form['expiry'], $form['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            if ($owner) {
                $owner_id = (int) $owner['id'];
                $stmt = mysqli_prepare($conn, "
                    UPDATE users SET name = ?, email = ?
                    WHERE id = ? AND institute_id = ? AND role = 'admin'
                ");
                mysqli_stmt_bind_param($stmt, 'ssii', $form['owner_name'], $form['email'], $owner_id, $form['id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            mysqli_commit($conn);
            $message = 'Institute and administrator details updated successfully.';
            $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $error = (int) $exception->getCode() === 1062
                ? 'An account with this administrator email already exists.'
                : ($exception instanceof RuntimeException ? $exception->getMessage() : 'The institute could not be updated.');
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $field => $default) {
        $value = $_POST[$field] ?? $default;
        $form[$field] = is_string($value) ? trim($value) : $default;
    }

    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirm_password = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';
    $submitted_csrf_token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
    $subscription_period = $form['subscription_period'];

    if (!admin_validate_csrf($submitted_csrf_token)) {
        $error = "Your form session expired. Please reload the page and try again.";
    } elseif (
        $form['institute_name'] === '' ||
        $form['owner_name'] === '' ||
        $form['email'] === '' ||
        $form['phone_no'] === '' ||
        $form['address'] === ''
    ) {
        $error = "Please complete all required fields.";
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid administrator email address.";
    } elseif (
        strlen($form['institute_name']) > 150 ||
        strlen($form['owner_name']) > 100 ||
        strlen($form['email']) > 255 ||
        strlen($form['phone_no']) > 15
    ) {
        $error = "One or more fields exceed the allowed length.";
    } elseif (!in_array($subscription_period, ['1', '6', '12', 'lifetime'], true)) {
        $error = "Select a valid subscription period.";
    } elseif (strlen($password) < 8) {
        $error = "The administrator password must be at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $expiry = null;
        if ($subscription_period !== 'lifetime') {
            $start_date = new DateTimeImmutable('today');
            $day = (int) $start_date->format('j');
            $expiry_month = $start_date->modify('first day of this month')
                ->modify('+' . (int) $subscription_period . ' months');
            $expiry = $expiry_month
                ->setDate(
                    (int) $expiry_month->format('Y'),
                    (int) $expiry_month->format('n'),
                    min($day, (int) $expiry_month->format('t'))
                )
                ->format('Y-m-d');
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            mysqli_begin_transaction($conn);

            $institute_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO institute (name, email, phone_no, address, subscription, expiry)
                 VALUES (?, ?, ?, ?, 'active', ?)"
            );
            mysqli_stmt_bind_param(
                $institute_stmt,
                "sssss",
                $form['institute_name'],
                $form['email'],
                $form['phone_no'],
                $form['address'],
                $expiry
            );
            mysqli_stmt_execute($institute_stmt);
            $institute_id = mysqli_insert_id($conn);
            mysqli_stmt_close($institute_stmt);

            $user_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (name, email, password, role, institute_id, status)
                 VALUES (?, ?, ?, 'admin', ?, 'active')"
            );
            mysqli_stmt_bind_param(
                $user_stmt,
                "sssi",
                $form['owner_name'],
                $form['email'],
                $password_hash,
                $institute_id
            );
            mysqli_stmt_execute($user_stmt);
            mysqli_stmt_close($user_stmt);

            mysqli_commit($conn);
            $message = "Institute and administrator account created successfully.";
            $form = [
                'institute_name' => '',
                'owner_name' => '',
                'email' => '',
                'phone_no' => '',
                'address' => '',
                'subscription_period' => '12',
            ];
            $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($conn);
            $error = (int) $exception->getCode() === 1062
                ? "An account with this administrator email already exists."
                : "The institute could not be created. Please check the details and try again.";
        }
    }
}

$edit_id = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT) ?: 0;
if ($edit_id > 0 && $error === '') {
    $stmt = mysqli_prepare($conn, "
        SELECT i.id, i.name AS institute_name, i.email, i.phone_no, i.address, i.subscription, i.expiry,
               COALESCE(u.name, '') AS owner_name, (u.id IS NOT NULL) AS has_owner
        FROM institute i LEFT JOIN users u ON u.institute_id = i.id AND u.role = 'admin'
        WHERE i.id = ? ORDER BY u.id LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $institute_to_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($institute_to_edit) {
        $form = array_merge($form, $institute_to_edit);
    } else {
        $error = 'Institute was not found.';
    }
}
$institutes_list = mysqli_query($conn, "
    SELECT id, name, email, phone_no, subscription, expiry
    FROM institute ORDER BY id DESC
");

render_admin_header($form['id'] ? "Edit Institute" : "Create Institute", "institutes");
?>

<?php if ($message !== ''): ?>
    <div class="notice-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card institute-form-card">
    <div class="card-title">
        <span><i class="fa-solid fa-building-circle-check"></i> Institute Details</span>
    </div>
    <p class="form-intro"><?= $form['id'] ? 'Update institute contact, administrator, and subscription details.' : 'Create an institute and its administrator login. The administrator can use these credentials to access the institute dashboard.' ?></p>

    <form method="POST" action="/ERP-SYSTEM-FOR-COACHING/admin/institutes.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <?php if ($form['id']): ?>
            <input type="hidden" name="action" value="update_institute">
            <input type="hidden" name="institute_id" value="<?= (int) $form['id'] ?>">
        <?php endif; ?>

        <div class="institute-form-grid">
            <div class="form-group">
                <label for="institute_name">Institute name *</label>
                <input id="institute_name" type="text" name="institute_name" class="form-control-simple" maxlength="150" required value="<?= htmlspecialchars($form['institute_name']) ?>" placeholder="e.g. Apex Coaching Academy">
            </div>
            <div class="form-group">
                <label for="owner_name">Administrator name <?= !$form['id'] || !empty($form['has_owner']) ? '*' : '(not assigned)' ?></label>
                <input id="owner_name" type="text" name="owner_name" class="form-control-simple" maxlength="100" <?= !$form['id'] || !empty($form['has_owner']) ? 'required' : 'disabled' ?> value="<?= htmlspecialchars($form['owner_name']) ?>" placeholder="Full name">
            </div>
            <div class="form-group">
                <label for="email">Administrator email / institute contact *</label>
                <input id="email" type="email" name="email" class="form-control-simple" maxlength="255" required value="<?= htmlspecialchars($form['email']) ?>" placeholder="admin@institute.com">
                <small class="field-help">This email will be used to sign in to the institute account.</small>
            </div>
            <div class="form-group">
                <label for="phone_no">Phone number *</label>
                <input id="phone_no" type="tel" name="phone_no" class="form-control-simple" maxlength="15" required value="<?= htmlspecialchars($form['phone_no']) ?>" placeholder="Institute contact number">
            </div>
            <?php if (!$form['id']): ?>
            <div class="form-group">
                <label for="subscription_period">Subscription *</label>
                <select id="subscription_period" name="subscription_period" class="form-control-simple" required>
                    <option value="1" <?= $form['subscription_period'] === '1' ? 'selected' : '' ?>>1 month</option>
                    <option value="6" <?= $form['subscription_period'] === '6' ? 'selected' : '' ?>>6 months</option>
                    <option value="12" <?= $form['subscription_period'] === '12' ? 'selected' : '' ?>>1 year</option>
                    <option value="lifetime" <?= $form['subscription_period'] === 'lifetime' ? 'selected' : '' ?>>Lifetime</option>
                </select>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label for="subscription">Subscription status *</label>
                <select id="subscription" name="subscription" class="form-control-simple" required>
                    <option value="active" <?= $form['subscription'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= $form['subscription'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>
            <div class="form-group">
                <label for="expiry">Expiry date (leave blank for lifetime)</label>
                <input id="expiry" type="date" name="expiry" class="form-control-simple" value="<?= htmlspecialchars($form['expiry'] ?? '') ?>">
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="address">Institute address *</label>
                <textarea id="address" name="address" class="form-control-simple form-textarea" rows="3" required placeholder="Full postal address"><?= htmlspecialchars($form['address']) ?></textarea>
            </div>
            <?php if (!$form['id']): ?>
            <div class="form-group">
                <label for="password">Administrator password *</label>
                <input id="password" type="password" name="password" class="form-control-simple" minlength="8" autocomplete="new-password" required>
                <small class="field-help">Use at least 8 characters.</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password *</label>
                <input id="confirm_password" type="password" name="confirm_password" class="form-control-simple" minlength="8" autocomplete="new-password" required>
            </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary form-submit">
            <i class="fa-solid fa-<?= $form['id'] ? 'floppy-disk' : 'plus' ?>"></i> <?= $form['id'] ? 'Update Institute' : 'Create Institute' ?>
        </button>
        <?php if ($form['id']): ?> <a href="/ERP-SYSTEM-FOR-COACHING/admin/institutes.php" class="btn-primary">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="card-title"><span><i class="fa-solid fa-building"></i> Institutes</span></div>
    <div class="table-scroll">
        <table class="data-table">
            <thead><tr><th>Institute</th><th>Contact</th><th>Subscription</th><th>Expiry</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if ($institutes_list && mysqli_num_rows($institutes_list) > 0): ?>
                    <?php while ($institute = mysqli_fetch_assoc($institutes_list)): ?>
                        <tr>
                            <td><?= htmlspecialchars($institute['name']) ?></td>
                            <td><?= htmlspecialchars($institute['email']) ?><br><?= htmlspecialchars($institute['phone_no']) ?></td>
                            <td><?= htmlspecialchars(strtoupper($institute['subscription'])) ?></td>
                            <td><?= $institute['expiry'] ? htmlspecialchars($institute['expiry']) : 'Lifetime' ?></td>
                            <td><a href="institutes.php?edit_id=<?= (int) $institute['id'] ?>">Edit</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty-state">No institutes found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php render_admin_footer(); ?>

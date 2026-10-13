<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$csrf_token = request_csrf_token();
$message = $error = '';
$selected_student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT)
    ?: (filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0);
$teacher_stmt = mysqli_prepare($conn, "
    SELECT id FROM teacher
    WHERE user_id = ? AND institute_id = ? AND status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($teacher_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($teacher_stmt);
$teacher = mysqli_fetch_assoc(mysqli_stmt_get_result($teacher_stmt));
mysqli_stmt_close($teacher_stmt);
$teacher_id = (int) ($teacher['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT) ?: 0;
    $amount_input = is_string($_POST['amount'] ?? null) ? trim($_POST['amount']) : '';
    $payment_mode = is_string($_POST['mode'] ?? null) ? $_POST['mode'] : '';
    $ref_no = is_string($_POST['ref_no'] ?? null) ? trim($_POST['ref_no']) : '';
    $remarks = is_string($_POST['remarks'] ?? null) ? trim($_POST['remarks']) : '';
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please reload the page and try again.';
    } elseif ($selected_student_id < 1 || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $amount_input) ||
        (float) $amount_input <= 0 || !in_array($payment_mode, ['UPI', 'Cash', 'Bank'], true) ||
        strlen($ref_no) > 100 || strlen($remarks) > 255) {
        $error = 'Enter a valid student, amount, payment method, and reference.';
    } else {
        try {
            mysqli_begin_transaction($conn);
            $stmt = mysqli_prepare($conn, "
                SELECT s.course_id, c.fees AS course_fee,
                       COALESCE((
                           SELECT SUM(f.amount) FROM fees f
                           WHERE f.student_id = s.id AND f.status = 'paid'
                               AND (f.course_id = s.course_id OR f.course_id IS NULL)
                       ), 0) AS paid_amount
                FROM student s
                INNER JOIN batch b ON b.id = s.batch_id AND b.teacher_id = ? AND b.institute_id = s.institute_id
                INNER JOIN users u ON u.id = s.user_id AND u.role = 'student'
                LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
                WHERE s.id = ? AND s.institute_id = ? AND s.status = 'active' AND u.status = 'active'
                FOR UPDATE
            ");
            mysqli_stmt_bind_param($stmt, 'iii', $teacher_id, $selected_student_id, $institute_id);
            mysqli_stmt_execute($stmt);
            $student_payment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if (!$student_payment || $student_payment['course_fee'] === null) {
                throw new RuntimeException('Student is not in one of your batches or has no assigned course.');
            }
            $due = max(0, (float) $student_payment['course_fee'] - (float) $student_payment['paid_amount']);
            $amount = (float) $amount_input;
            if ($amount > $due) {
                throw new RuntimeException('Payment amount cannot exceed the outstanding course fee.');
            }
            $stmt = mysqli_prepare($conn, "
                INSERT INTO fees (student_id, course_id, amount, status, payment_mode, ref_no, remarks)
                VALUES (?, ?, ?, 'paid', ?, NULLIF(?, ''), NULLIF(?, ''))
            ");
            mysqli_stmt_bind_param($stmt, 'iidsss', $selected_student_id, $student_payment['course_id'],
                $amount, $payment_mode, $ref_no, $remarks);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_commit($conn);
            $message = 'Payment of ₹' . number_format($amount, 2) . ' recorded successfully.';
            $csrf_token = $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Throwable $exception) {
            mysqli_rollback($conn);
            $error = $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'The payment could not be recorded.';
        }
    }
}

$students_stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, s.admission_no, u.name AS student_name, b.name AS batch_name,
           COALESCE(c.fees, 0) AS total_course_fee,
           COALESCE((
               SELECT SUM(f.amount) FROM fees f
               WHERE f.student_id = s.id AND f.status = 'paid'
                   AND (f.course_id = s.course_id OR f.course_id IS NULL)
           ), 0) AS paid_amount
    FROM student s
    INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
    INNER JOIN batch b ON b.id = s.batch_id AND b.teacher_id = ? AND b.institute_id = s.institute_id
    LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
    WHERE s.institute_id = ? AND s.status = 'active'
    ORDER BY u.name ASC
");
mysqli_stmt_bind_param($students_stmt, 'ii', $teacher_id, $institute_id);
mysqli_stmt_execute($students_stmt);
$students_res = mysqli_stmt_get_result($students_stmt);

$students_list = [];
$active_student = null;

if ($students_res) {
    while ($row = mysqli_fetch_assoc($students_res)) {
        $total_fee = floatval($row['total_course_fee']);
        $paid = floatval($row['paid_amount']);
        $due = max(0, $total_fee - $paid);
        
        $row['total_fee'] = $total_fee;
        $row['paid'] = $paid;
        $row['due'] = $due;

        $students_list[] = $row;
        if ($selected_student_id === intval($row['student_id'])) {
            $active_student = $row;
        }
    }
}

if (!$active_student && count($students_list) > 0) {
    $active_student = $students_list[0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Collect Payment</title>
    <link rel="stylesheet" href="fees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="erp-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher Portal</span>
            </div>
            <ul class="sidebar-menu">
                <li>
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
                <li class="active">
                    <a href="fees.php">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Fees</span>
                    </a>
                </li>
                <li>
                    <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li>
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="<?= htmlspecialchars(app_url('logout.php')) ?>">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <main class="main-wrapper">
            <!-- Topbar -->
            <header class="top-navbar">
                <div class="page-title">
                    <h2>Fees Management</h2>
                </div>
                <div class="user-profile">
                    <span class="role-pill">TEACHER</span>
                    <i class="fa-regular fa-circle-user profile-icon"></i>
                    <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Teacher') ?></strong>
                </div>
            </header>

            <div class="content-body">
                <div class="form-grid-layout">
                    <!-- Payment Form Card -->
                    <section class="card-section">
                        <div class="card-section-header">
                            <h3 class="section-title"><i class="fa-solid fa-money-bill-wave"></i> Payment Details</h3>
                            <a href="fees.php" class="action-link"><i class="fa-solid fa-arrow-left"></i> Back to Fees</a>
                        </div>

                        <?php if (!empty($message)): ?>
                            <div style="background: #dcfce7; color: #15803d; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form class="portal-form" method="POST" action="payment.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <div class="field-group">
                                <label>Student *</label>
                                <select name="student_id" class="portal-input" required onchange="window.location.href='payment.php?student_id='+this.value">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students_list as $st): ?>
                                        <option value="<?= $st['student_id'] ?>" <?= ($active_student && $active_student['student_id'] == $st['student_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st['admission_no']) ?> - <?= htmlspecialchars($st['student_name']) ?> (<?= htmlspecialchars($st['batch_name'] ?? 'Unassigned') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Payment Method</label>
                                <div class="payment-method-group">
                                    <label class="radio-label">
                                        <input type="radio" name="mode" value="UPI" checked>
                                        <span>UPI / QR</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="mode" value="Cash">
                                        <span>Cash</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="mode" value="Bank">
                                        <span>Net Banking / Cheque</span>
                                    </label>
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field-group">
                                    <label>Amount (₹) *</label>
                                    <input type="number" name="amount" class="portal-input" placeholder="e.g. 5000" min="0.01" max="<?= htmlspecialchars(number_format($active_student['due'] ?? 0, 2, '.', '')) ?>" step="0.01" required value="<?= $active_student ? htmlspecialchars(number_format($active_student['due'], 2, '.', '')) : '' ?>">
                                </div>
                                <div class="field-group">
                                    <label>Transaction / Ref No.</label>
                                    <input type="text" name="ref_no" class="portal-input" placeholder="e.g. UPI Ref, Receipt ID">
                                </div>
                            </div>

                            <div class="field-group">
                                <label>Note / Remarks</label>
                                <input type="text" name="remarks" class="portal-input" placeholder="e.g. Fee installment collected">
                            </div>

                            <button type="submit" class="portal-btn btn-submit">Submit Payment</button>
                        </form>
                    </section>

                    <!-- Summary Info Card -->
                    <section class="card-section">
                        <h3 class="section-title"><i class="fa-solid fa-file-invoice"></i> Student Summary</h3>
                        
                        <?php if ($active_student): ?>
                            <div class="info-row">
                                <div class="row-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                                <div class="row-text">Student <strong><?= htmlspecialchars($active_student['student_name']) ?></strong></div>
                            </div>
                            <div class="info-row">
                                <div class="row-icon"><i class="fa-solid fa-layer-group"></i></div>
                                <div class="row-text">Batch <strong><?= htmlspecialchars($active_student['batch_name'] ?? 'Unassigned') ?></strong></div>
                            </div>

                            <div class="profile-block">
                                <p><strong>Total Course Fee:</strong> ₹<?= number_format($active_student['total_fee'], 2) ?></p>
                                <p><strong>Paid So Far:</strong> ₹<?= number_format($active_student['paid'], 2) ?></p>
                                <p><strong>Current Due:</strong> <span class="highlight-text">₹<?= number_format($active_student['due'], 2) ?></span></p>
                            </div>
                        <?php else: ?>
                            <p style="color: #94a3b8; text-align: center; padding: 20px;">Please select a student from the dropdown.</p>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
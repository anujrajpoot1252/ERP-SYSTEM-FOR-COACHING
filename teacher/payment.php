<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$message = "";
$error = "";

$selected_student_id = intval($_GET['student_id'] ?? $_GET['id'] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id   = intval($_POST['student_id'] ?? 0);
    $amount       = floatval($_POST['amount'] ?? 0);
    $payment_mode = sanitize($conn, $_POST['mode'] ?? 'Cash');
    $ref_no       = sanitize($conn, $_POST['ref_no'] ?? '');
    $remarks      = sanitize($conn, $_POST['remarks'] ?? '');

    if ($student_id <= 0 || $amount <= 0) {
        $error = "Please select a valid student and enter a valid amount.";
    } else {
        $stmt = $conn->prepare("INSERT INTO fees (student_id, amount, payment_mode, ref_no, remarks, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("idsss", $student_id, $amount, $payment_mode, $ref_no, $remarks);
            if ($stmt->execute()) {
                $message = "Payment of ₹" . number_format($amount, 2) . " successfully recorded!";
                $selected_student_id = $student_id;
            } else {
                $error = "Failed to record payment: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch all students for dropdown
$students_res = mysqli_query($conn, "
    SELECT 
        s.id as student_id,
        s.admission_no,
        u.name as student_name,
        b.name as batch_name,
        COALESCE(c.fees, 0) as total_course_fee,
        COALESCE(SUM(f.amount), 0) as paid_amount
    FROM student s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN course c ON s.course_id = c.id
    LEFT JOIN batch b ON s.batch_id = b.id
    LEFT JOIN fees f ON f.student_id = s.id
    GROUP BY s.id
    ORDER BY u.name ASC
");

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
                <a href="/ERP-SYSTEM-FOR-COACHING/logout.php">
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
                                    <input type="number" name="amount" class="portal-input" placeholder="e.g. 5000" min="1" step="0.01" required value="<?= $active_student ? $active_student['due'] : '' ?>">
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
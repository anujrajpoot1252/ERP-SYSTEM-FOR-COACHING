<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

check_access(['student']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$csrf_token = request_csrf_token();

$student_stmt = mysqli_prepare($conn, "
    SELECT s.id AS student_id, s.course_id, c.course_name, c.fees AS course_fee,
           COALESCE((
               SELECT SUM(f.amount) FROM fees f
               WHERE f.student_id = s.id AND f.status = 'paid'
                   AND (f.course_id = s.course_id OR f.course_id IS NULL)
           ), 0) AS paid_amount
    FROM student s
    INNER JOIN users u ON u.id = s.user_id AND u.role = 'student' AND u.status = 'active'
    LEFT JOIN course c ON c.id = s.course_id AND c.institute_id = s.institute_id
    WHERE s.user_id = ? AND s.institute_id = ? AND s.status = 'active'
    LIMIT 1
");
mysqli_stmt_bind_param($student_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($student_stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($student_stmt)) ?: [];
mysqli_stmt_close($student_stmt);

$course_fee = (float) ($student['course_fee'] ?? 0);
$paid_amount = (float) ($student['paid_amount'] ?? 0);
$due_amount = max(0, $course_fee - $paid_amount);
$fee_rows = false;
if (!empty($student['student_id'])) {
    $fees_stmt = mysqli_prepare($conn, "
        SELECT amount, status, payment_mode, ref_no, remarks, order_id, created_at
        FROM fees
        WHERE student_id = ? AND (course_id = ? OR course_id IS NULL)
        ORDER BY created_at DESC, id DESC
    ");
    $student_id = (int) $student['student_id'];
    $course_id = (int) ($student['course_id'] ?? 0);
    mysqli_stmt_bind_param($fees_stmt, 'ii', $student_id, $course_id);
    mysqli_stmt_execute($fees_stmt);
    $fee_rows = mysqli_stmt_get_result($fees_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - Student Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/style.css')) ?>">
    <script src="https://checkout.razorpay.com/v1/checkout.js" defer></script>
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-graduation-cap"></i><span>Student Portal</span></div>
        <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="result.php">
                        <i class="fa-solid fa-poll"></i>
                        <span>Results</span>
                    </a>
                </li>
                <li>
                    <a href="marksheet.php">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Marksheet</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="active">
                    <a href="fees.php">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Fees</span>
                    </a>
                </li>
        </ul>
        <div class="sidebar-footer"><a href="<?= htmlspecialchars(app_url('logout.php')) ?>"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a></div>
    </aside>

    <main class="main-content">
        <header class="top-navbar">
            <h2>My Fees</h2>
            <div class="user-profile">
                <span class="role-badge">STUDENT</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color:#64748b"></i>
                <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></strong>
            </div>
        </header>
        <div class="page-body">
            <div class="stats-grid">
                <div class="stat-card"><div class="info"><h3>₹<?= number_format($course_fee, 2) ?></h3><p>Course Fee</p></div><div class="icon bg-blue"><i class="fa-solid fa-book-open"></i></div></div>
                <div class="stat-card"><div class="info"><h3>₹<?= number_format($paid_amount, 2) ?></h3><p>Paid</p></div><div class="icon bg-green"><i class="fa-solid fa-circle-check"></i></div></div>
                <div class="stat-card"><div class="info"><h3>₹<?= number_format($due_amount, 2) ?></h3><p>Outstanding</p></div><div class="icon bg-orange"><i class="fa-solid fa-clock"></i></div></div>
            </div>

            <section class="card">
                <div class="card-title"><span><i class="fa-solid fa-credit-card"></i> Course Fee Payment</span></div>
                <?php if (empty($student)): ?>
                    <p>Your student profile could not be found. Please contact your institute.</p>
                <?php elseif (empty($student['course_id'])): ?>
                    <p>No course is currently assigned to your account.</p>
                <?php elseif ($due_amount <= 0): ?>
                    <p>Your course fees are fully paid.</p>
                <?php else: ?>
                    <p><?= htmlspecialchars($student['course_name'] ?? 'Course') ?> has an outstanding balance of <strong>₹<?= number_format($due_amount, 2) ?></strong>.</p>
                    <button id="pay-course-fee" class="btn-primary" type="button"
                            data-course-id="<?= (int) $student['course_id'] ?>"
                            data-csrf-token="<?= htmlspecialchars($csrf_token) ?>">
                        <i class="fa-solid fa-lock"></i> Pay with Razorpay
                    </button>
                    <p id="payment-message" role="status" style="margin-top:12px"></p>
                <?php endif; ?>
            </section>

            <section class="card">
                <div class="card-title"><span><i class="fa-solid fa-receipt"></i> Payment History</span></div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Note</th></tr></thead>
                        <tbody>
                            <?php if ($fee_rows && mysqli_num_rows($fee_rows) > 0): ?>
                                <?php while ($fee = mysqli_fetch_assoc($fee_rows)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fee['created_at']) ?></td>
                                        <td>₹<?= number_format((float) $fee['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($fee['payment_mode'] ?? 'Online') ?></td>
                                        <td><?= htmlspecialchars($fee['ref_no'] ?: ($fee['order_id'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars(strtoupper($fee['status'])) ?></td>
                                        <td><?= htmlspecialchars($fee['remarks'] ?? '') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="empty-state">No fee payments recorded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>
<script>
const appBase = <?= json_encode(APP_PATH) ?>;
const payButton = document.getElementById('pay-course-fee');
if (payButton) {
    payButton.addEventListener('click', async () => {
        const message = document.getElementById('payment-message');
        const csrfToken = payButton.dataset.csrfToken;
        payButton.disabled = true;
        message.textContent = 'Preparing secure checkout…';
        try {
            const orderResponse = await fetch(`${appBase}/api/payments/create_order.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken
                },
                body: new URLSearchParams({course_id: payButton.dataset.courseId}),
                credentials: 'same-origin'
            });
            const order = await orderResponse.json();
            if (!orderResponse.ok || !order.status) {
                throw new Error(order.message || 'Unable to create a payment order.');
            }

            const checkout = new Razorpay({
                key: order.key_id,
                amount: order.amount,
                currency: order.currency,
                name: 'Coaching ERP',
                description: 'Course fee payment',
                order_id: order.order_id,
                prefill: {name: order.name, email: order.email},
                handler: async (payment) => {
                    try {
                        const verifyResponse = await fetch(`${appBase}/api/payments/verify_payment.php`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-CSRF-Token': csrfToken
                            },
                            body: new URLSearchParams({
                                razorpay_order_id: payment.razorpay_order_id,
                                razorpay_payment_id: payment.razorpay_payment_id,
                                razorpay_signature: payment.razorpay_signature
                            }),
                            credentials: 'same-origin'
                        });
                        const result = await verifyResponse.json();
                        if (!verifyResponse.ok || !result.status) {
                            throw new Error(result.message || 'Payment could not be verified.');
                        }
                        message.textContent = result.message;
                        window.location.reload();
                    } catch (error) {
                        message.textContent = error.message;
                    } finally {
                        payButton.disabled = false;
                    }
                }
            });
            checkout.on('payment.failed', (event) => {
                message.textContent = event.error?.description || 'Payment failed. You may retry.';
                payButton.disabled = false;
            });
            checkout.open();
            message.textContent = '';
        } catch (error) {
            message.textContent = error.message;
            payButton.disabled = false;
        }
    });
}
</script>
</body>
</html>

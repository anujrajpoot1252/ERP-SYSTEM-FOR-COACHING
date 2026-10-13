<?php
header('Content-Type: application/json');
require_once __DIR__ . '/common.php';

require_payment_post();
if (razorpay_key_id() === '' || razorpay_key_secret() === '') {
    payment_json_response(503, 'Razorpay is not configured on the server.');
}

$requested_course_id = filter_input(INPUT_POST, 'course_id', FILTER_VALIDATE_INT);
if (!$requested_course_id || $requested_course_id < 1) {
    payment_json_response(400, 'A valid course_id is required.');
}

$transaction_started = false;
try {
    mysqli_begin_transaction($conn);
    $transaction_started = true;
    $student = student_payment_details($conn, true);
    if ((int) $student['course_id'] !== $requested_course_id) {
        throw new RuntimeException('The selected course does not match your enrollment.');
    }
    if ($student['course_fee'] === null) {
        throw new RuntimeException('Enroll in a course before paying course fees.');
    }

    $course_fee_paise = amount_to_paise($student['course_fee']);
    $paid_paise = student_fee_paid_paise($conn, $student['student_id'], $student['course_id']);
    $balance_paise = $course_fee_paise - $paid_paise;
    if ($balance_paise <= 0) {
        throw new RuntimeException('There is no outstanding course fee.');
    }

    $stmt = mysqli_prepare($conn, "
        SELECT amount, order_id
        FROM fees
        WHERE student_id = ? AND course_id = ? AND status = 'created'
        ORDER BY id DESC
        LIMIT 1
        FOR UPDATE
    ");
    if (!$stmt) {
        throw new RuntimeException('Could not check for an existing checkout.');
    }
    mysqli_stmt_bind_param($stmt, 'ii', $student['student_id'], $student['course_id']);
    mysqli_stmt_execute($stmt);
    $pending_order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($pending_order) {
        if (amount_to_paise($pending_order['amount']) !== $balance_paise) {
            throw new RuntimeException('An earlier checkout is still pending. Complete it or contact the institute before retrying.');
        }
        $order_id = $pending_order['order_id'];
    } else {
        $order = razorpay_request('POST', '/orders', [
            'amount' => $balance_paise,
            'currency' => 'INR',
            'receipt' => 'fee-' . $student['student_id'] . '-' . bin2hex(random_bytes(8)),
            'notes' => ['student_id' => (string) $student['student_id']]
        ]);
        $order_id = $order['id'] ?? '';
        if ($order_id === '' || (int) ($order['amount'] ?? 0) !== $balance_paise || ($order['currency'] ?? '') !== 'INR') {
            throw new RuntimeException('Razorpay did not return a valid checkout order.');
        }

        $amount_rupees = number_format($balance_paise / 100, 2, '.', '');
        $stmt = mysqli_prepare($conn, "
            INSERT INTO fees (student_id, course_id, amount, status, order_id, payment_mode)
            VALUES (?, ?, ?, 'created', ?, 'Razorpay')
        ");
        if (!$stmt) {
            throw new RuntimeException('Could not save the checkout order.');
        }
        mysqli_stmt_bind_param($stmt, 'iiss', $student['student_id'], $student['course_id'], $amount_rupees, $order_id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Could not save the checkout order.');
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    $transaction_started = false;
    payment_json_response(200, 'Checkout order is ready.', [
        'key_id' => razorpay_key_id(),
        'order_id' => $order_id,
        'amount' => $balance_paise,
        'currency' => 'INR',
        'name' => $student['name'],
        'email' => $student['email']
    ]);
} catch (Throwable $error) {
    if ($transaction_started) {
        mysqli_rollback($conn);
    }
    error_log('Razorpay order creation failed: ' . $error->getMessage());
    payment_json_response(400, $error->getMessage());
}

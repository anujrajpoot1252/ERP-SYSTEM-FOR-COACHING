<?php
header('Content-Type: application/json');
require_once __DIR__ . '/common.php';

require_payment_post();

$order_id = trim($_POST['razorpay_order_id'] ?? '');
$payment_id = trim($_POST['razorpay_payment_id'] ?? '');
$signature = trim($_POST['razorpay_signature'] ?? '');

if ($order_id === '' || $payment_id === '' || $signature === '') {
    payment_json_response(400, 'Razorpay payment details are incomplete.');
}

$secret = razorpay_key_secret();
if ($secret === '') {
    payment_json_response(503, 'Razorpay is not configured on the server.');
}
$expected_signature = hash_hmac('sha256', $order_id . '|' . $payment_id, $secret);
if (!hash_equals($expected_signature, $signature)) {
    payment_json_response(400, 'Razorpay payment signature is invalid.');
}

try {
    $stmt = mysqli_prepare($conn, "
        SELECT id FROM fees
        WHERE student_id = (
            SELECT id FROM student WHERE user_id = ? AND institute_id = ?
        ) AND order_id = ?
        LIMIT 1
    ");
    if (!$stmt) {
        throw new RuntimeException('Could not validate the checkout order.');
    }
    $user_id = (int) $_SESSION['user_id'];
    $institute_id = (int) ($_SESSION['institute_id'] ?? 0);
    mysqli_stmt_bind_param($stmt, 'iis', $user_id, $institute_id, $order_id);
    mysqli_stmt_execute($stmt);
    $order_exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$order_exists) {
        payment_json_response(403, 'This checkout order does not belong to your account.');
    }

    $payment = razorpay_request('GET', '/payments/' . rawurlencode($payment_id));
    if (($payment['order_id'] ?? '') !== $order_id || ($payment['status'] ?? '') !== 'captured') {
        payment_json_response(400, 'Razorpay has not confirmed a captured payment for this order.');
    }
    settle_razorpay_payment(
        $conn,
        $order_id,
        $payment_id,
        (int) ($payment['amount'] ?? 0),
        (string) ($payment['currency'] ?? '')
    );

    payment_json_response(200, 'Payment verified and recorded.');
} catch (Throwable $error) {
    error_log('Razorpay payment verification failed: ' . $error->getMessage());
    payment_json_response(400, $error->getMessage());
}

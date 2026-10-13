<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/razorpay.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$webhook_secret = razorpay_webhook_secret();
$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
if ($webhook_secret === '' || $signature === '' ||
    !hash_equals(hash_hmac('sha256', $body, $webhook_secret), $signature)) {
    http_response_code(400);
    exit;
}

$event = json_decode($body, true);
if (!is_array($event)) {
    http_response_code(400);
    exit;
}
if (!in_array(($event['event'] ?? ''), ['payment.captured', 'payment.failed'], true)) {
    http_response_code(200);
    exit;
}

$payment = $event['payload']['payment']['entity'] ?? [];
$order_id = $payment['order_id'] ?? '';
$payment_id = $payment['id'] ?? '';
if ($order_id === '' || $payment_id === '') {
    http_response_code(400);
    exit;
}

try {
    if (($event['event'] ?? '') === 'payment.failed') {
        if (($payment['status'] ?? '') !== 'failed') {
            http_response_code(400);
            exit;
        }
        $stmt = mysqli_prepare($conn, "
            UPDATE fees
            SET status = 'failed', payment_id = ?
            WHERE order_id = ? AND status = 'created'
        ");
        if (!$stmt) {
            throw new RuntimeException('Could not record the failed payment.');
        }
        mysqli_stmt_bind_param($stmt, 'ss', $payment_id, $order_id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Could not record the failed payment.');
        }
        mysqli_stmt_close($stmt);
        http_response_code(200);
        exit;
    }

    if (($payment['status'] ?? '') !== 'captured') {
        http_response_code(400);
        exit;
    }
    settle_razorpay_payment(
        $conn,
        $order_id,
        $payment_id,
        (int) ($payment['amount'] ?? 0),
        (string) ($payment['currency'] ?? '')
    );
    http_response_code(200);
} catch (Throwable $error) {
    error_log('Razorpay webhook processing failed: ' . $error->getMessage());
    http_response_code(500);
}

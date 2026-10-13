<?php

function razorpay_key_id() {
    $key_id = getenv('RAZORPAY_KEY_ID');
    if (($key_id === false || $key_id === '') && defined('RZP_KEY_ID')) {
        $key_id = constant('RZP_KEY_ID');
    }
    return trim((string) $key_id);
}

function razorpay_key_secret() {
    $key_secret = getenv('RAZORPAY_KEY_SECRET');
    if (($key_secret === false || $key_secret === '') && defined('RZP_KEY_SECRET')) {
        $key_secret = constant('RZP_KEY_SECRET');
    }
    return trim((string) $key_secret);
}

function razorpay_webhook_secret() {
    return trim((string) getenv('RAZORPAY_WEBHOOK_SECRET'));
}

function razorpay_request($method, $path, $payload = null) {
    $key_id = razorpay_key_id();
    $key_secret = razorpay_key_secret();
    if ($key_id === '' || $key_secret === '') {
        throw new RuntimeException('Razorpay is not configured on the server.');
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for Razorpay payments.');
    }

    $curl = curl_init('https://api.razorpay.com/v1' . $path);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_USERPWD => $key_id . ':' . $key_secret,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 20
    ];
    if ($payload !== null) {
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    }
    curl_setopt_array($curl, $options);
    $response_body = curl_exec($curl);
    $curl_error = curl_error($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response_body === false) {
        throw new RuntimeException('Razorpay request failed: ' . $curl_error);
    }
    $response = json_decode($response_body, true);
    if ($status_code < 200 || $status_code >= 300 || !is_array($response)) {
        $description = $response['error']['description'] ?? 'Unexpected gateway response.';
        throw new RuntimeException('Razorpay returned HTTP ' . $status_code . ': ' . $description);
    }

    return $response;
}

function amount_to_paise($amount) {
    $amount = (string) $amount;
    if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
        throw new RuntimeException('Invalid fee amount.');
    }

    $parts = explode('.', $amount, 2);
    $rupees = (int) $parts[0];
    $paise = isset($parts[1]) ? (int) str_pad($parts[1], 2, '0') : 0;
    return $rupees * 100 + $paise;
}

function settle_razorpay_payment($conn, $order_id, $payment_id, $amount_paise, $currency) {
    if ($currency !== 'INR' || !is_int($amount_paise) || $amount_paise <= 0) {
        throw new RuntimeException('Payment amount or currency is invalid.');
    }

    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare($conn, "
            SELECT f.id, f.student_id, f.course_id, f.amount, f.status, f.payment_id,
                   c.fees AS course_fee
            FROM fees f
            INNER JOIN student s ON s.id = f.student_id
            LEFT JOIN course c ON c.id = f.course_id AND c.institute_id = s.institute_id
            WHERE f.order_id = ?
            FOR UPDATE
        ");
        if (!$stmt) {
            throw new RuntimeException('Could not load the payment order.');
        }
        mysqli_stmt_bind_param($stmt, 's', $order_id);
        mysqli_stmt_execute($stmt);
        $fee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$fee) {
            throw new RuntimeException('Payment order was not found.');
        }
        if ($fee['status'] === 'paid') {
            if ($fee['payment_id'] === $payment_id) {
                mysqli_commit($conn);
                return;
            }
            throw new RuntimeException('This order has already been settled.');
        }
        if ($fee['status'] !== 'created' || amount_to_paise($fee['amount']) !== $amount_paise) {
            throw new RuntimeException('Payment does not match the pending fee order.');
        }

        $stmt = mysqli_prepare($conn, "
            SELECT COALESCE(SUM(amount), 0) AS paid_amount
            FROM fees
            WHERE student_id = ? AND (course_id = ? OR course_id IS NULL) AND status = 'paid'
        ");
        if (!$stmt) {
            throw new RuntimeException('Could not check the student fee balance.');
        }
        mysqli_stmt_bind_param($stmt, 'ii', $fee['student_id'], $fee['course_id']);
        mysqli_stmt_execute($stmt);
        $paid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($fee['course_fee'] === null ||
            amount_to_paise($fee['course_fee']) - amount_to_paise($paid['paid_amount']) < $amount_paise) {
            throw new RuntimeException('The fee balance changed after this checkout was created.');
        }

        $stmt = mysqli_prepare($conn, "
            UPDATE fees
            SET status = 'paid', payment_id = ?
            WHERE id = ? AND status = 'created'
        ");
        if (!$stmt) {
            throw new RuntimeException('Could not record the payment.');
        }
        mysqli_stmt_bind_param($stmt, 'si', $payment_id, $fee['id']);
        if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Could not record the payment.');
        }
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}

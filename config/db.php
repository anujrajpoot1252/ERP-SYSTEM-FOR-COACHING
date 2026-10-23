<?php
// Config: Database Connection & Initialization

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $secure_cookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure_cookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!defined('APP_PATH')) {
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
define(
    'APP_PATH',
    strpos($script_name, '/ERP-SYSTEM-FOR-COACHING') === 0
        ? '/ERP-SYSTEM-FOR-COACHING'
        : ''
);
}

if (!function_exists('app_url')) {
function app_url($path = '') {
    return APP_PATH . '/' . ltrim($path, '/');
}
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$env_file = __DIR__ . '/../.env';
if (is_readable($env_file)) {
    $environment = parse_ini_file($env_file, false, INI_SCANNER_RAW);
    if (is_array($environment)) {
        foreach ($environment as $key => $value) {
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

// Detect environment: Localhost vs InfinityFree Hosting
$server_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
$is_infinityfree = (strpos($server_host, 'kesug.com') !== false) 
                || (strpos($server_host, 'epizy.com') !== false)
                || (strpos($server_host, 'infinityfree') !== false)
                || (strpos($_SERVER['DOCUMENT_ROOT'] ?? '', 'if0_') !== false);

if ($is_infinityfree) {
    // InfinityFree Live Settings (Account: if0_43254250)
    // Ignore default localhost values from .env if present
    $env_host = getenv('DB_HOST');
    $host = ($env_host && $env_host !== '127.0.0.1' && $env_host !== 'localhost') 
        ? $env_host 
        : 'sql102.infinityfree.com';

    $env_user = getenv('DB_USER');
    $user = ($env_user && $env_user !== 'root') 
        ? $env_user 
        : 'if0_43254250';

    $env_db = getenv('DB_NAME');
    $database = ($env_db && $env_db !== 'erp_system') 
        ? $env_db 
        : 'if0_43254250_erp';

    $pass = (getenv('DB_PASSWORD') !== false && getenv('DB_PASSWORD') !== '') 
        ? getenv('DB_PASSWORD') 
        : '';
} else {
    // Localhost / XAMPP Settings
    $host     = getenv('DB_HOST') ?: '127.0.0.1';
    $user     = getenv('DB_USER') ?: 'root';
    $pass     = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
    $database = getenv('DB_NAME') ?: 'erp_system';
}

if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
    throw new RuntimeException('DB_NAME contains invalid characters.');
}

try {
    // Step 1: Direct connect with database name (InfinityFree/cPanel requires direct DB connect)
    try {
        $conn = mysqli_connect($host, $user, $pass, $database);
    } catch (Throwable $e1) {
        // Step 2: Fallback for local XAMPP if database does not exist yet
        $conn = mysqli_connect($host, $user, $pass);
        @mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        mysqli_select_db($conn, $database);
    }
    mysqli_set_charset($conn, 'utf8mb4');
} catch (Throwable $exception) {
    error_log('ERP database connection failed: ' . $exception->getMessage());
    http_response_code(500);

    // Friendly diagnostic card so the user knows exactly what is wrong
    $err_msg = $exception->getMessage();
    $safe_host = htmlspecialchars($host);
    $safe_user = htmlspecialchars($user);
    $safe_db = htmlspecialchars($database);

    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Connection Error - Coaching ERP</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px 15px; margin: 0; }
            .error-box { max-width: 640px; margin: 40px auto; background: #1e293b; border: 1px solid #ef4444; border-radius: 12px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            h2 { color: #f87171; margin-top: 0; font-size: 22px; display: flex; align-items: center; gap: 10px; }
            .msg { background: #450a0a; border: 1px solid #b91c1c; color: #fca5a5; padding: 12px 16px; border-radius: 8px; font-family: monospace; font-size: 13px; margin: 18px 0; word-break: break-all; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
            td { padding: 8px 12px; border-bottom: 1px solid rgba(255,255,255,0.08); }
            td strong { color: #94a3b8; }
            .help { background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); padding: 16px; border-radius: 8px; margin-top: 20px; font-size: 13.5px; line-height: 1.6; color: #c7d2fe; }
            .help ol { margin-left: 20px; margin-top: 8px; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>⚠️ Database Connection Error</h2>
            <p>Database server se connect nahi ho paya. Error details neeche di gayi hain:</p>
            <div class='msg'>Error: " . htmlspecialchars($err_msg) . "</div>
            <table>
                <tr><td><strong>MySQL Host:</strong></td><td><code>$safe_host</code></td></tr>
                <tr><td><strong>MySQL User:</strong></td><td><code>$safe_user</code></td></tr>
                <tr><td><strong>Database Name:</strong></td><td><code>$safe_db</code></td></tr>
            </table>
            <div class='help'>
                <strong>🔧 Isko Kaise Solve Karein (Fix Steps):</strong>
                <ol>
                    <li>InfinityFree Dashboard -> <strong>MySQL Details</strong> me apna actual <strong>MySQL Hostname</strong> (e.g. <code>sql200.infinityfree.com</code>) aur <strong>Password</strong> check karein.</li>
                    <li>InfinityFree <strong>Control Panel (vPanel) -> MySQL Databases</strong> me jakar ek database banayein (e.g. <code>$safe_db</code>).</li>
                    <li>In details ko apne root <code>.env</code> file ya <code>config/db.php</code> me update karein.</li>
                </ol>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// Initialize only a truly empty database; never run the destructive schema over partial data.
$table_check = mysqli_query($conn, 'SHOW TABLES');
if (mysqli_num_rows($table_check) === 0) {
$schema_file = __DIR__ . "/../schema/schema.sql";
if (file_exists($schema_file)) {
    $sql = file_get_contents($schema_file);
    if ($sql === false || !mysqli_multi_query($conn, $sql)) {
        error_log('ERP schema initialization failed: ' . mysqli_error($conn));
        http_response_code(500);
        exit('Database schema initialization failed. Check the server error log.');
    }
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
        if (mysqli_more_results($conn) && !mysqli_next_result($conn)) {
            error_log('ERP schema initialization failed: ' . mysqli_error($conn));
            http_response_code(500);
            exit('Database schema initialization failed. Check the server error log.');
        }
    } while (mysqli_more_results($conn));
}
} else {
$users_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_table_check) === 0) {
    error_log('ERP database contains tables but is missing the users table; automatic schema import was refused.');
    http_response_code(500);
    exit('The selected database is incomplete. Import schema/schema.sql only after backing up existing data.');
}
}

// Add the fee table for databases initialized before fee tracking was introduced.
$fees_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'fees'");
if ($fees_table_check && mysqli_num_rows($fees_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `fees` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `course_id` INT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `cashback` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Cash',
        `ref_no` VARCHAR(100) NULL,
        `remarks` VARCHAR(255) NULL,
        `status` ENUM('created','paid','failed') NOT NULL DEFAULT 'paid',
        `order_id` VARCHAR(100) NULL,
        `payment_id` VARCHAR(100) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_fees_student_id` (`student_id`),
        UNIQUE KEY `unique_fees_order_id` (`order_id`),
        UNIQUE KEY `unique_fees_payment_id` (`payment_id`),
        FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} else {
    $fee_columns = [
        'course_id' => '`course_id` INT NULL',
        'cashback' => '`cashback` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'payment_mode' => "`payment_mode` VARCHAR(50) NOT NULL DEFAULT 'Cash'",
        'ref_no' => '`ref_no` VARCHAR(100) NULL',
        'remarks' => '`remarks` VARCHAR(255) NULL',
        'status' => "`status` ENUM('created','paid','failed') NOT NULL DEFAULT 'paid'",
        'order_id' => '`order_id` VARCHAR(100) NULL',
        'payment_id' => '`payment_id` VARCHAR(100) NULL'
    ];
    foreach ($fee_columns as $column => $definition) {
        $column_check = mysqli_prepare($conn, "
            SELECT 1 FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fees' AND COLUMN_NAME = ?
        ");
        mysqli_stmt_bind_param($column_check, 's', $column);
        mysqli_stmt_execute($column_check);
        $column_exists = mysqli_stmt_get_result($column_check)->num_rows > 0;
        mysqli_stmt_close($column_check);
        if (!$column_exists) {
            mysqli_query($conn, "ALTER TABLE `fees` ADD COLUMN $definition");
        }
    }

    foreach (['unique_fees_order_id' => 'order_id', 'unique_fees_payment_id' => 'payment_id'] as $index => $column) {
        $index_check = mysqli_prepare($conn, "
            SELECT 1 FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fees' AND INDEX_NAME = ?
        ");
        mysqli_stmt_bind_param($index_check, 's', $index);
        mysqli_stmt_execute($index_check);
        $index_exists = mysqli_stmt_get_result($index_check)->num_rows > 0;
        mysqli_stmt_close($index_check);
        if (!$index_exists) {
            mysqli_query($conn, "ALTER TABLE `fees` ADD UNIQUE KEY `$index` (`$column`)");
        }
    }
}

// Ensure attendance table and status enum are compatible with the application.
$attendance_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'attendance'");
if ($attendance_table_check && mysqli_num_rows($attendance_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `attendance` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `batch_id` INT NOT NULL,
        `date` DATE NOT NULL,
        `status` ENUM('present','absent') NOT NULL,
        `phone_number_parents` VARCHAR(50) NULL,
        `marking` VARCHAR(20) NULL,
        FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`batch_id`) REFERENCES `batch`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} else {
    $attendance_status_check = mysqli_query($conn, "SHOW COLUMNS FROM `attendance` LIKE 'status'");
    if ($attendance_status_check && mysqli_num_rows($attendance_status_check) > 0) {
        $status_field = mysqli_fetch_assoc($attendance_status_check);
        if (stripos($status_field['Type'], 'presence') !== false || stripos($status_field['Type'], 'absence') !== false) {
            mysqli_query($conn, "
                ALTER TABLE `attendance`
                MODIFY `status` ENUM('present','absent','presence','absence') NOT NULL
            ");
            mysqli_query($conn, "
                UPDATE `attendance`
                SET `status` = CASE `status`
                    WHEN 'presence' THEN 'present'
                    WHEN 'absence' THEN 'absent'
                    ELSE `status`
                END
            ");
            mysqli_query($conn, "ALTER TABLE `attendance` MODIFY `status` ENUM('present','absent') NOT NULL");
        }
    }
}

// Ensure exams table exists
$exams_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'exams'");
if ($exams_table_check && mysqli_num_rows($exams_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `exams` (
        `exam_id` INT AUTO_INCREMENT PRIMARY KEY,
        `institute_id` INT DEFAULT 1,
        `batch_name` VARCHAR(100) NOT NULL,
        `exam_name` VARCHAR(150) NOT NULL,
        `exam_date` DATE NOT NULL,
        `total_marks` INT NOT NULL,
        `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Ensure results table exists
$results_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'results'");
if ($results_table_check && mysqli_num_rows($results_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `results` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `institute_id` INT DEFAULT 1,
        `exam_id` INT NOT NULL,
        `student_id` INT NOT NULL,
        `obtained_marks` DECIMAL(5,2) NOT NULL,
        `total_marks` DECIMAL(5,2) NOT NULL,
        `grade` VARCHAR(10) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`exam_id`) REFERENCES `exams`(`exam_id`) ON DELETE CASCADE,
        FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Ensure UNIQUE constraint on attendance table to prevent duplicate records
$attendance_unique_check = mysqli_query($conn, "SHOW INDEX FROM `attendance` WHERE Key_name = 'unique_student_batch_date'");
if ($attendance_unique_check && mysqli_num_rows($attendance_unique_check) == 0) {
    try {
        mysqli_query($conn, "ALTER TABLE `attendance` ADD UNIQUE KEY `unique_student_batch_date` (`student_id`, `batch_id`, `date`)");
    } catch (mysqli_sql_exception $exception) {
        error_log('Could not add attendance uniqueness constraint: ' . $exception->getMessage());
    }
}

// Helper sanitize function
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
?>
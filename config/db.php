<?php
// Config: Database Connection & Initialization

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "127.0.0.1";
$user = "root";
$pass = ""; // Default XAMPP root password

// 1. Establish connection to MySQL server
$conn = @mysqli_connect($host, $user, $pass);

if (!$conn) {
    // Try with fallback password if set
    $pass = "kunal123";
    $conn = @mysqli_connect($host, $user, $pass);
    if (!$conn) {
        die("Database Connection Error: " . mysqli_connect_error());
    }
}

// 2. Create database if it does not exist
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `erp_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

// 3. Select erp_system database
mysqli_select_db($conn, "erp_system");

// 4. Ensure tables exist by checking users table
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) == 0) {
    $schema_file = __DIR__ . "/../schema/schema.sql";
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        // Multi query execution
        if (mysqli_multi_query($conn, $sql)) {
            do {
                if ($res = mysqli_store_result($conn)) {
                    mysqli_free_result($res);
                }
            } while (mysqli_more_results($conn) && mysqli_next_result($conn));
        }
    }
}

// Add the fee table for databases initialized before fee tracking was introduced.
$fees_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'fees'");
if ($fees_table_check && mysqli_num_rows($fees_table_check) == 0) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `fees` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_fees_student_id` (`student_id`),
        FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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
            mysqli_query($conn, "ALTER TABLE `attendance` MODIFY `status` ENUM('present','absent') NOT NULL");
        }
    }
}

// Helper sanitize function
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
?>
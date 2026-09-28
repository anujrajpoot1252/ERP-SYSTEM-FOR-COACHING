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

// Helper sanitize function
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}
?>
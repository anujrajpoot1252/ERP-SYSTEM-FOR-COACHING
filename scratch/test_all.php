<?php
require_once __DIR__ . '/../config/db.php';

echo "=== DATABASE TABLES CHECK ===\n";
$tables = [];
$res = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($res)) {
    $tables[] = $row[0];
}
echo "Found tables: " . implode(', ', $tables) . "\n\n";

echo "=== USERS CHECK ===\n";
$res = mysqli_query($conn, "SELECT id, name, email, role, institute_id, status, password FROM users");
while ($user = mysqli_fetch_assoc($res)) {
    $test_passwords = ['admin123', 'teacher123', 'student123', 'superadmin123', 'priya123', 'vikram123', 'ram123', 'sita123'];
    $matched = 'unknown';
    foreach ($test_passwords as $p) {
        if (password_verify($p, $user['password'])) {
            $matched = $p;
            break;
        }
    }
    echo "ID: {$user['id']} | Name: {$user['name']} | Email: {$user['email']} | Role: {$user['role']} | Status: {$user['status']} | Password: {$matched}\n";
}
echo "\n";

echo "=== INSTITUTES CHECK ===\n";
$res = mysqli_query($conn, "SELECT * FROM institute");
while ($inst = mysqli_fetch_assoc($res)) {
    echo "ID: {$inst['id']} | Name: {$inst['name']} | Subscription: {$inst['subscription']}\n";
}
echo "\n";

echo "=== TEACHERS CHECK ===\n";
$res = mysqli_query($conn, "SELECT t.*, u.name, u.email FROM teacher t JOIN users u ON t.user_id = u.id");
while ($t = mysqli_fetch_assoc($res)) {
    echo "ID: {$t['id']} | Name: {$t['name']} | Subject: {$t['subject']}\n";
}
echo "\n";

echo "=== STUDENTS CHECK ===\n";
$res = mysqli_query($conn, "SELECT s.*, u.name, u.email FROM student s JOIN users u ON s.user_id = u.id");
while ($s = mysqli_fetch_assoc($res)) {
    echo "ID: {$s['id']} | Name: {$s['name']} | Adm: {$s['admission_no']} | Batch: {$s['batch_id']}\n";
}
echo "\n";

echo "=== COURSES CHECK ===\n";
$res = mysqli_query($conn, "SELECT * FROM course");
while ($c = mysqli_fetch_assoc($res)) {
    echo "ID: {$c['id']} | Course: {$c['course_name']} | Fees: {$c['fees']}\n";
}
echo "\n";

echo "=== BATCHES CHECK ===\n";
$res = mysqli_query($conn, "SELECT * FROM batch");
while ($b = mysqli_fetch_assoc($res)) {
    echo "ID: {$b['id']} | Batch: {$b['name']} | Course: {$b['course_id']} | Teacher: {$b['teacher_id']}\n";
}
echo "\n";

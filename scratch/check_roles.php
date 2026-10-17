<?php
require_once __DIR__ . '/../config/db.php';

$res = mysqli_query($conn, "SELECT id, name, email, role, institute_id FROM users");
while ($r = mysqli_fetch_assoc($res)) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Email: {$r['email']} | Role: {$r['role']} | Institute: {$r['institute_id']}\n";
}

echo "\n--- Teacher Records ---\n";
$t = mysqli_query($conn, "SELECT t.*, u.name, u.email FROM teacher t JOIN users u ON t.user_id = u.id");
while ($row = mysqli_fetch_assoc($t)) {
    echo "Teacher ID: {$row['id']} | User ID: {$row['user_id']} | Name: {$row['name']} | Email: {$row['email']} | Inst: {$row['institute_id']}\n";
}

echo "\n--- Batches ---\n";
$b = mysqli_query($conn, "SELECT id, name, teacher_id, institute_id FROM batch");
while ($row = mysqli_fetch_assoc($b)) {
    echo "Batch ID: {$row['id']} | Name: {$row['name']} | Teacher ID: {$row['teacher_id']} | Inst: {$row['institute_id']}\n";
}

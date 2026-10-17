<?php
require_once __DIR__ . "/../config/db.php";

function create_user($conn, $name, $email, $pass, $role) {
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if ($check && mysqli_num_rows($check) > 0) {
        return mysqli_fetch_assoc($check)['id'];
    }
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (name, email, password, role, institute_id, status) VALUES ('$name', '$email', '$hash', '$role', 1, 'active')");
    return mysqli_insert_id($conn);
}

// Add Teachers
$u_priya = create_user($conn, 'Priya Sharma', 'priya@erp.com', 'priya123', 'teacher');
$t_check = mysqli_query($conn, "SELECT id FROM teacher WHERE user_id = $u_priya");
if ($t_check && mysqli_num_rows($t_check) == 0) {
    mysqli_query($conn, "INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status) VALUES ($u_priya, 1, '9876543211', 'Chemistry', '2025-02-01', 'active')");
}

$u_vikram = create_user($conn, 'Vikram Singh', 'vikram@erp.com', 'vikram123', 'teacher');
$t_check2 = mysqli_query($conn, "SELECT id FROM teacher WHERE user_id = $u_vikram");
if ($t_check2 && mysqli_num_rows($t_check2) == 0) {
    mysqli_query($conn, "INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status) VALUES ($u_vikram, 1, '9876543212', 'Mathematics', '2025-02-15', 'active')");
}

// Add Students
$u_ram = create_user($conn, 'Ram Kumar', 'ram@erp.com', 'ram123', 'student');
$s_check = mysqli_query($conn, "SELECT id FROM student WHERE user_id = $u_ram");
if ($s_check && mysqli_num_rows($s_check) == 0) {
    $adm = 'ADM-2026-002';
    mysqli_query($conn, "INSERT INTO student (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status) VALUES ($u_ram, 1, '$adm', '9123456780', 'Ramesh Kumar', '9811223300', 1, 1, 'active')");
}

$u_sita = create_user($conn, 'Sita Patel', 'sita@erp.com', 'sita123', 'student');
$s_check2 = mysqli_query($conn, "SELECT id FROM student WHERE user_id = $u_sita");
if ($s_check2 && mysqli_num_rows($s_check2) == 0) {
    $adm2 = 'ADM-2026-003';
    mysqli_query($conn, "INSERT INTO student (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status) VALUES ($u_sita, 1, '$adm2', '9123456781', 'Suresh Patel', '9811223301', 2, 2, 'active')");
}

echo "SUCCESS: Accounts created successfully!\n";
?>

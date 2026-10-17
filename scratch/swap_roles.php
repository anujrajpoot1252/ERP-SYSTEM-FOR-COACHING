<?php
require_once __DIR__ . '/../config/db.php';

echo "=== SWAPPING ROLES: ANUJ RAJPOOT -> ADMIN, KUNAL -> TEACHER ===\n";

mysqli_begin_transaction($conn);

try {
    // 1. Get user IDs
    $u_kunal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email = 'kunal@career.com'"))['id'] ?? null;
    $u_anuj = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email = 'anuj@career.com'"))['id'] ?? null;

    if (!$u_kunal || !$u_anuj) {
        throw new Exception("Users not found!");
    }

    // 2. Set Anuj Rajpoot as ADMIN
    $hash_anuj = password_hash('anuj123', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET role = 'admin', name = 'Anuj Rajpoot', password = '$hash_anuj', status = 'active' WHERE id = $u_anuj");
    echo "✓ Anuj Rajpoot updated to role 'admin' (password: anuj123)\n";

    // 3. Set Kunal as TEACHER
    $hash_kunal = password_hash('kunal123', PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET role = 'teacher', name = 'Kunal Sharma', password = '$hash_kunal', status = 'active' WHERE id = $u_kunal");
    echo "✓ Kunal updated to role 'teacher' (password: kunal123)\n";

    // 4. Update Teacher table: remove teacher record for Anuj (since he's admin now) and add/update for Kunal
    // Delete existing teacher entry for anuj
    mysqli_query($conn, "DELETE FROM teacher WHERE user_id = $u_anuj");

    // Add/Update teacher entry for kunal
    $t_check = mysqli_query($conn, "SELECT id FROM teacher WHERE user_id = $u_kunal");
    if ($t_check && mysqli_num_rows($t_check) > 0) {
        $kunal_teacher_id = mysqli_fetch_assoc($t_check)['id'];
        mysqli_query($conn, "UPDATE teacher SET subject = 'Full Stack Development', status = 'active' WHERE id = $kunal_teacher_id");
    } else {
        mysqli_query($conn, "INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status) VALUES ($u_kunal, 3, '9876500001', 'Full Stack Development', CURDATE(), 'active')");
        $kunal_teacher_id = mysqli_insert_id($conn);
    }
    echo "✓ Teacher profile created for Kunal (Teacher ID: $kunal_teacher_id)\n";

    // 5. Assign batches in institute 3 to Kunal
    mysqli_query($conn, "UPDATE batch SET teacher_id = $kunal_teacher_id WHERE institute_id = 3");
    echo "✓ Batches in Kunal Career Institute assigned to Teacher Kunal\n";

    mysqli_commit($conn);
    echo "\n=== ROLES SWAPPED SUCCESSFULLY! ===\n";
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php
require_once __DIR__ . '/../config/db.php';

echo "=================================================================\n";
echo "ADDING 3 TEACHERS, COURSES, BATCHES & STUDENTS WITH LIVE DATA\n";
echo "=================================================================\n\n";

$institute_id = 3; // Kunal Career Institute (Admin: Anuj Rajpoot)

mysqli_begin_transaction($conn);

try {
    // -------------------------------------------------------------
    // Helper to create user if not exists
    // -------------------------------------------------------------
    function get_or_create_user($conn, $name, $email, $password, $role, $inst_id) {
        $q = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($q, 's', $email);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        if ($row = mysqli_fetch_assoc($res)) {
            mysqli_stmt_close($q);
            return (int)$row['id'];
        }
        mysqli_stmt_close($q);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role, institute_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
        mysqli_stmt_bind_param($ins, 'ssssi', $name, $email, $hash, $role, $inst_id);
        mysqli_stmt_execute($ins);
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($ins);
        return $new_id;
    }

    // -------------------------------------------------------------
    // 1. ADD 3 NEW TEACHERS
    // -------------------------------------------------------------
    $teachers_data = [
        [
            'name' => 'Amit Sharma',
            'email' => 'amit@career.com',
            'password' => 'amit123',
            'subject' => 'Frontend & React.js',
            'phone' => '9876500002'
        ],
        [
            'name' => 'Neha Verma',
            'email' => 'neha@career.com',
            'password' => 'neha123',
            'subject' => 'Python & Data Science',
            'phone' => '9876500003'
        ],
        [
            'name' => 'Sanjay Patel',
            'email' => 'sanjay@career.com',
            'password' => 'sanjay123',
            'subject' => 'Java & DSA Bootcamp',
            'phone' => '9876500004'
        ]
    ];

    $teacher_ids = [];

    foreach ($teachers_data as $td) {
        $uid = get_or_create_user($conn, $td['name'], $td['email'], $td['password'], 'teacher', $institute_id);
        
        $t_q = mysqli_query($conn, "SELECT id FROM teacher WHERE user_id = $uid");
        if ($t_row = mysqli_fetch_assoc($t_q)) {
            $t_id = (int)$t_row['id'];
        } else {
            $t_stmt = mysqli_prepare($conn, "INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status) VALUES (?, ?, ?, ?, CURDATE(), 'active')");
            mysqli_stmt_bind_param($t_stmt, 'iiss', $uid, $institute_id, $td['phone'], $td['subject']);
            mysqli_stmt_execute($t_stmt);
            $t_id = mysqli_insert_id($conn);
            mysqli_stmt_close($t_stmt);
        }
        $teacher_ids[$td['email']] = $t_id;
        echo "✓ Teacher: {$td['name']} ({$td['email']}) -> Teacher ID: $t_id\n";
    }

    // -------------------------------------------------------------
    // 2. ADD COURSES & BATCHES FOR EACH TEACHER
    // -------------------------------------------------------------
    $courses_data = [
        [
            'name' => 'React & Next.js Masterclass',
            'duration' => '3 Months',
            'fees' => 18000.00,
            'batch_name' => 'React Evening Batch 1',
            'teacher_email' => 'amit@career.com',
            'timing' => '17:00:00',
            'schedule' => 'Mon - Fri'
        ],
        [
            'name' => 'Python & Data Science',
            'duration' => '4 Months',
            'fees' => 25000.00,
            'batch_name' => 'Python Weekend Batch',
            'teacher_email' => 'neha@career.com',
            'timing' => '09:00:00',
            'schedule' => 'Sat - Sun'
        ],
        [
            'name' => 'Java & DSA Bootcamp',
            'duration' => '6 Months',
            'fees' => 28000.00,
            'batch_name' => 'Java Morning Batch',
            'teacher_email' => 'sanjay@career.com',
            'timing' => '08:00:00',
            'schedule' => 'Mon - Sat'
        ]
    ];

    $course_ids = [];
    $batch_ids = [];

    foreach ($courses_data as $cd) {
        // Course
        $c_chk = mysqli_query($conn, "SELECT id FROM course WHERE course_name = '{$cd['name']}' AND institute_id = $institute_id");
        if ($c_row = mysqli_fetch_assoc($c_chk)) {
            $c_id = (int)$c_row['id'];
        } else {
            $c_stmt = mysqli_prepare($conn, "INSERT INTO course (institute_id, course_name, duration, fees, status) VALUES (?, ?, ?, ?, 'active')");
            mysqli_stmt_bind_param($c_stmt, 'issd', $institute_id, $cd['name'], $cd['duration'], $cd['fees']);
            mysqli_stmt_execute($c_stmt);
            $c_id = mysqli_insert_id($conn);
            mysqli_stmt_close($c_stmt);
        }
        $course_ids[$cd['name']] = $c_id;

        // Batch
        $t_id = $teacher_ids[$cd['teacher_email']];
        $b_chk = mysqli_query($conn, "SELECT id FROM batch WHERE name = '{$cd['batch_name']}' AND institute_id = $institute_id");
        if ($b_row = mysqli_fetch_assoc($b_chk)) {
            $b_id = (int)$b_row['id'];
            mysqli_query($conn, "UPDATE batch SET teacher_id = $t_id WHERE id = $b_id");
        } else {
            $b_stmt = mysqli_prepare($conn, "INSERT INTO batch (institute_id, course_id, teacher_id, name, timing_status, schedule, start_date, ending_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH))");
            mysqli_stmt_bind_param($b_stmt, 'iiisss', $institute_id, $c_id, $t_id, $cd['batch_name'], $cd['timing'], $cd['schedule']);
            mysqli_stmt_execute($b_stmt);
            $b_id = mysqli_insert_id($conn);
            mysqli_stmt_close($b_stmt);
        }
        $batch_ids[$cd['batch_name']] = $b_id;
        echo "✓ Course: '{$cd['name']}' (ID: $c_id) | Batch: '{$cd['batch_name']}' (ID: $b_id)\n";
    }

    // -------------------------------------------------------------
    // 3. ADD 4 NEW STUDENTS & ENROLL THEM
    // -------------------------------------------------------------
    $students_data = [
        [
            'name' => 'Aakash Mehra',
            'email' => 'aakash@career.com',
            'password' => 'aakash123',
            'phone' => '9123456791',
            'parent_name' => 'Sunil Mehra',
            'parent_phone' => '9811223311',
            'course_name' => 'React & Next.js Masterclass',
            'batch_name' => 'React Evening Batch 1',
            'exam_name' => 'React Components & Hooks Quiz',
            'marks' => 92.0,
            'total_marks' => 100.0,
            'grade' => 'A+',
            'paid_amount' => 18000.00 // Full paid
        ],
        [
            'name' => 'Pooja Singh',
            'email' => 'pooja@career.com',
            'password' => 'pooja123',
            'phone' => '9123456792',
            'parent_name' => 'Virendra Singh',
            'parent_phone' => '9811223322',
            'course_name' => 'Python & Data Science',
            'batch_name' => 'Python Weekend Batch',
            'exam_name' => 'Python Basics & Pandas Test',
            'marks' => 78.5,
            'total_marks' => 100.0,
            'grade' => 'A',
            'paid_amount' => 15000.00 // Partial (10,000 due)
        ],
        [
            'name' => 'Vikas Yadav',
            'email' => 'vikas@career.com',
            'password' => 'vikas123',
            'phone' => '9123456793',
            'parent_name' => 'Dharmendra Yadav',
            'parent_phone' => '9811223333',
            'course_name' => 'Java & DSA Bootcamp',
            'batch_name' => 'Java Morning Batch',
            'exam_name' => 'Java OOP & Arrays Assessment',
            'marks' => 84.0,
            'total_marks' => 100.0,
            'grade' => 'A',
            'paid_amount' => 10000.00 // Partial (18,000 due)
        ],
        [
            'name' => 'Sneha Nair',
            'email' => 'sneha@career.com',
            'password' => 'sneha123',
            'phone' => '9123456794',
            'parent_name' => 'Mohan Nair',
            'parent_phone' => '9811223344',
            'course_name' => 'React & Next.js Masterclass',
            'batch_name' => 'React Evening Batch 1',
            'exam_name' => 'React Components & Hooks Quiz',
            'marks' => 89.0,
            'total_marks' => 100.0,
            'grade' => 'A',
            'paid_amount' => 9000.00 // Partial (9,000 due)
        ]
    ];

    echo "\n--- Enrolling Students & Recording Academic Data ---\n";

    foreach ($students_data as $sd) {
        $uid = get_or_create_user($conn, $sd['name'], $sd['email'], $sd['password'], 'student', $institute_id);
        $c_id = $course_ids[$sd['course_name']];
        $b_id = $batch_ids[$sd['batch_name']];

        $s_chk = mysqli_query($conn, "SELECT id, admission_no FROM student WHERE user_id = $uid");
        if ($s_row = mysqli_fetch_assoc($s_chk)) {
            $s_id = (int)$s_row['id'];
            $adm = $s_row['admission_no'];
            mysqli_query($conn, "UPDATE student SET course_id = $c_id, batch_id = $b_id WHERE id = $s_id");
        } else {
            $adm = 'ADM-' . date('Y') . '-' . str_pad((string)$uid, 3, '0', STR_PAD_LEFT);
            $s_stmt = mysqli_prepare($conn, "INSERT INTO student (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            mysqli_stmt_bind_param($s_stmt, 'iissssii', $uid, $institute_id, $adm, $sd['phone'], $sd['parent_name'], $sd['parent_phone'], $c_id, $b_id);
            mysqli_stmt_execute($s_stmt);
            $s_id = mysqli_insert_id($conn);
            mysqli_stmt_close($s_stmt);
        }

        // Attendance (Mark Present)
        $att_chk = mysqli_query($conn, "SELECT id FROM attendance WHERE student_id = $s_id AND batch_id = $b_id AND date = CURDATE()");
        if (!$att_chk || mysqli_num_rows($att_chk) == 0) {
            mysqli_query($conn, "INSERT INTO attendance (student_id, batch_id, date, status, marking) VALUES ($s_id, $b_id, CURDATE(), 'present', 'auto')");
        }

        // Exam & Result
        $ex_chk = mysqli_query($conn, "SELECT exam_id FROM exams WHERE exam_name = '{$sd['exam_name']}' AND institute_id = $institute_id");
        if ($ex_row = mysqli_fetch_assoc($ex_chk)) {
            $ex_id = (int)$ex_row['exam_id'];
        } else {
            $ex_stmt = mysqli_prepare($conn, "INSERT INTO exams (institute_id, batch_name, exam_name, exam_date, total_marks) VALUES (?, ?, ?, CURDATE(), ?)");
            $t_marks = (int)$sd['total_marks'];
            mysqli_stmt_bind_param($ex_stmt, 'issi', $institute_id, $sd['batch_name'], $sd['exam_name'], $t_marks);
            mysqli_stmt_execute($ex_stmt);
            $ex_id = mysqli_insert_id($conn);
            mysqli_stmt_close($ex_stmt);
        }

        $res_chk = mysqli_query($conn, "SELECT id FROM results WHERE exam_id = $ex_id AND student_id = $s_id");
        if (!$res_chk || mysqli_num_rows($res_chk) == 0) {
            $res_stmt = mysqli_prepare($conn, "INSERT INTO results (institute_id, exam_id, student_id, obtained_marks, total_marks, grade) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($res_stmt, 'iiidds', $institute_id, $ex_id, $s_id, $sd['marks'], $sd['total_marks'], $sd['grade']);
            mysqli_stmt_execute($res_stmt);
            mysqli_stmt_close($res_stmt);
        }

        // Fee Payment
        $fee_chk = mysqli_query($conn, "SELECT id FROM fees WHERE student_id = $s_id AND course_id = $c_id");
        if (!$fee_chk || mysqli_num_rows($fee_chk) == 0) {
            $fee_stmt = mysqli_prepare($conn, "INSERT INTO fees (student_id, course_id, amount, status, payment_mode, ref_no, remarks) VALUES (?, ?, ?, 'paid', 'UPI', 'UPI-TXN-AUTO', 'Course fee installment')");
            mysqli_stmt_bind_param($fee_stmt, 'iid', $s_id, $c_id, $sd['paid_amount']);
            mysqli_stmt_execute($fee_stmt);
            mysqli_stmt_close($fee_stmt);
        }

        echo "✓ Student: {$sd['name']} ($adm) | Course: {$sd['course_name']} | Result: {$sd['marks']}/{$sd['total_marks']} ({$sd['grade']}) | Paid: ₹{$sd['paid_amount']}\n";
    }

    mysqli_commit($conn);
    echo "\n=================================================================\n";
    echo "SUCCESS: 3 NEW TEACHERS & 4 NEW STUDENTS ADDED WITH LIVE DATA!\n";
    echo "=================================================================\n";

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "ERROR: " . $e->getMessage() . "\n";
}

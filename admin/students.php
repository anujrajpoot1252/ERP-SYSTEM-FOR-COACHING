<?php
require_once __DIR__ . "/../includes/admin_layout.php";

$message = "";
$error = "";

// Handle Student Registration Form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $name         = sanitize($conn, $_POST['name'] ?? '');
    $email        = sanitize($conn, $_POST['email'] ?? '');
    $password     = $_POST['password'] ?? 'student123';
    $phone        = sanitize($conn, $_POST['phone'] ?? '');
    $parent_name  = sanitize($conn, $_POST['parent_name'] ?? '');
    $parent_phone = sanitize($conn, $_POST['parent_phone'] ?? '');
    $course_id    = intval($_POST['course_id'] ?? 0);
    $batch_id     = intval($_POST['batch_id'] ?? 0);

    if (empty($name) || empty($email) || empty($phone) || empty($parent_name) || empty($parent_phone)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "An account with this email address already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $institute_id = $_SESSION['institute_id'] ?? 1;

            // 1. Insert into Users Table
            $user_sql = "INSERT INTO users (name, email, password, role, institute_id, status) VALUES ('$name', '$email', '$hashed_password', 'student', $institute_id, 'active')";
            if (mysqli_query($conn, $user_sql)) {
                $user_id = mysqli_insert_id($conn);
                $adm_no = "ADM-" . date("Y") . "-" . str_pad($user_id, 3, "0", STR_PAD_LEFT);

                // 2. Insert into Student Table
                $student_sql = "INSERT INTO student (user_id, institute_id, admission_no, phone, parent_name, parent_phone, course_id, batch_id, status) 
                                VALUES ($user_id, $institute_id, '$adm_no', '$phone', '$parent_name', '$parent_phone', $course_id, $batch_id, 'active')";

                if (mysqli_query($conn, $student_sql)) {
                    $message = "Student successfully registered with Admission No: $adm_no!";
                } else {
                    $error = "Failed to create student details: " . mysqli_error($conn);
                }
            } else {
                $error = "Failed to create user account: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch Courses and Batches for dropdowns
$courses_res = mysqli_query($conn, "SELECT id, course_name FROM course WHERE status='active'");
$batches_res = mysqli_query($conn, "SELECT id, name FROM batch");

// Fetch All Students List
$students_list = mysqli_query($conn, "
    SELECT s.id, s.admission_no, u.name, u.email, s.phone, s.parent_name, s.parent_phone, c.course_name, b.name as batch_name, s.status 
    FROM student s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN course c ON s.course_id = c.id 
    LEFT JOIN batch b ON s.batch_id = b.id 
    ORDER BY s.id DESC
");

render_admin_header("Students Management", "students");
?>

<?php if (!empty($message)): ?>
    <div class="card" style="background: #dcfce7; border-left: 4px solid #16a34a; color: #15803d; margin-bottom: 20px;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-danger">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="grid-2">
    <!-- Add Student Form -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-user-plus"></i> Add New Student</span>
        </div>

        <form method="POST" action="students.php">
            <input type="hidden" name="action" value="add_student">

            <div class="form-group">
                <label>Student Full Name *</label>
                <input type="text" name="name" class="form-control-simple" placeholder="Full Name" required>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control-simple" placeholder="student@example.com" required>
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control-simple" placeholder="Enter password" required>
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone" class="form-control-simple" placeholder="10-digit mobile number" required>
            </div>

            <div class="form-group">
                <label>Parent Name *</label>
                <input type="text" name="parent_name" class="form-control-simple" placeholder="Father/Mother Name" required>
            </div>

            <div class="form-group">
                <label>Parent Contact *</label>
                <input type="text" name="parent_phone" class="form-control-simple" placeholder="Parent phone number" required>
            </div>

            <div class="form-group">
                <label>Select Course</label>
                <select name="course_id" class="form-control-simple">
                    <option value="0">-- Select Course --</option>
                    <?php while ($c = mysqli_fetch_assoc($courses_res)): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Select Batch</label>
                <select name="batch_id" class="form-control-simple">
                    <option value="0">-- Select Batch --</option>
                    <?php while ($b = mysqli_fetch_assoc($batches_res)): ?>
                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-plus"></i> Save Student
            </button>
        </form>
    </div>

    <!-- Students List Table -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-list"></i> Registered Students</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Adm No</th>
                        <th>Name</th>
                        <th>Email & Phone</th>
                        <th>Parent Details</th>
                        <th>Course & Batch</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_list && mysqli_num_rows($students_list) > 0): ?>
                        <?php while ($st = mysqli_fetch_assoc($students_list)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($st['admission_no']) ?></strong></td>
                                <td><?= htmlspecialchars($st['name']) ?></td>
                                <td>
                                    <?= htmlspecialchars($st['email']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['phone']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($st['parent_name']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['parent_phone']) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($st['course_name'] ?? 'None') ?></strong><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($st['batch_name'] ?? 'Unassigned') ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $st['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= strtoupper(htmlspecialchars($st['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; color:#94a3b8;">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

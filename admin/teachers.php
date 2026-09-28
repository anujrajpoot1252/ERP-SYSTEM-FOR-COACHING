<?php
require_once __DIR__ . "/../includes/admin_layout.php";

$message = "";
$error = "";

// Handle Teacher Registration Form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_teacher') {
    $name         = sanitize($conn, $_POST['name'] ?? '');
    $email        = sanitize($conn, $_POST['email'] ?? '');
    $password     = $_POST['password'] ?? 'teacher123';
    $phone_no     = sanitize($conn, $_POST['phone_no'] ?? '');
    $subject      = sanitize($conn, $_POST['subject'] ?? '');
    $joining_date = sanitize($conn, $_POST['joining_date'] ?? date('Y-m-d'));

    if (empty($name) || empty($email) || empty($phone_no) || empty($subject)) {
        $error = "Please fill in all required fields.";
    } else {
        // Check duplicate email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "An account with this email address already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $institute_id = $_SESSION['institute_id'] ?? 1;

            // 1. Insert into Users Table
            $user_sql = "INSERT INTO users (name, email, password, role, institute_id, status) VALUES ('$name', '$email', '$hashed_password', 'teacher', $institute_id, 'active')";
            if (mysqli_query($conn, $user_sql)) {
                $user_id = mysqli_insert_id($conn);

                // 2. Insert into Teacher Table
                $teacher_sql = "INSERT INTO teacher (user_id, institute_id, phone_no, subject, joining_date, status) 
                                VALUES ($user_id, $institute_id, '$phone_no', '$subject', '$joining_date', 'active')";

                if (mysqli_query($conn, $teacher_sql)) {
                    $message = "Teacher '$name' added successfully!";
                } else {
                    $error = "Failed to create teacher record: " . mysqli_error($conn);
                }
            } else {
                $error = "Failed to create user account: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch All Teachers List
$teachers_list = mysqli_query($conn, "
    SELECT t.id, u.name, u.email, t.phone_no, t.subject, t.joining_date, t.status 
    FROM teacher t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.id DESC
");

render_admin_header("Teachers Management", "teachers");
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
    <!-- Add Teacher Form -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-user-plus"></i> Add New Teacher</span>
        </div>

        <form method="POST" action="teachers.php">
            <input type="hidden" name="action" value="add_teacher">

            <div class="form-group">
                <label>Teacher Name *</label>
                <input type="text" name="name" class="form-control-simple" placeholder="Full Name" required>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control-simple" placeholder="teacher@example.com" required>
            </div>

            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control-simple" placeholder="Default: teacher123" required value="teacher123">
            </div>

            <div class="form-group">
                <label>Phone Number *</label>
                <input type="text" name="phone_no" class="form-control-simple" placeholder="10-digit phone number" required>
            </div>

            <div class="form-group">
                <label>Subject Specialization *</label>
                <input type="text" name="subject" class="form-control-simple" placeholder="e.g. Mathematics, Chemistry" required>
            </div>

            <div class="form-group">
                <label>Joining Date</label>
                <input type="date" name="joining_date" class="form-control-simple" value="<?= date('Y-m-d') ?>">
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">
                <i class="fa-solid fa-plus"></i> Save Teacher
            </button>
        </form>
    </div>

    <!-- Teachers List Table -->
    <div class="card">
        <div class="card-title">
            <span><i class="fa-solid fa-chalkboard-user"></i> Faculty Members</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Email & Phone</th>
                        <th>Subject</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($teachers_list && mysqli_num_rows($teachers_list) > 0): ?>
                        <?php while ($t = mysqli_fetch_assoc($teachers_list)): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($t['name']) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($t['email']) ?><br>
                                    <small style="color: #64748b;"><?= htmlspecialchars($t['phone_no']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($t['subject']) ?></td>
                                <td><?= htmlspecialchars($t['joining_date']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $t['status'] === 'active' ? 'success' : 'danger' ?>">
                                        <?= strtoupper(htmlspecialchars($t['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#94a3b8;">No faculty records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php render_admin_footer(); ?>

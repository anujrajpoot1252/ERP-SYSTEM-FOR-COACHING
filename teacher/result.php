<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$instituteId = $_SESSION['institute_id'] ?? 1;
$message = "";
$error = "";

// Handle Delete Result
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM results WHERE id = ? AND institute_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $delId, $instituteId);
        if ($stmt->execute()) {
            $message = "Result entry deleted successfully!";
        } else {
            $error = "Failed to delete result: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Form Submission (Add Result)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $exam_id        = intval($_POST['exam_id'] ?? 0);
    $student_id     = intval($_POST['student_id'] ?? 0);
    $obtained_marks = floatval($_POST['obtained_marks'] ?? 0);
    $total_marks    = floatval($_POST['total_marks'] ?? 0);
    $grade          = sanitize($conn, $_POST['grade'] ?? '');

    if ($exam_id <= 0 || $student_id <= 0 || $total_marks <= 0) {
        $error = "Please select an Exam, Student, and enter valid Marks.";
    } else {
        $stmt = $conn->prepare("INSERT INTO results (institute_id, exam_id, student_id, obtained_marks, total_marks, grade, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("iiidds", $instituteId, $exam_id, $student_id, $obtained_marks, $total_marks, $grade);
            if ($stmt->execute()) {
                $message = "Student result recorded successfully!";
            } else {
                $error = "Failed to save result: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch Exams List
$exams_res = mysqli_query($conn, "SELECT exam_id, exam_name, batch_name, total_marks FROM exams WHERE institute_id = $instituteId ORDER BY exam_id DESC");

// Fetch Students List
$students_res = mysqli_query($conn, "
    SELECT s.id as student_id, s.admission_no, u.name as student_name 
    FROM student s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY u.name ASC
");

// Fetch Saved Results List
$results_list = mysqli_query($conn, "
    SELECT 
        r.id as result_id,
        r.obtained_marks,
        r.total_marks,
        r.grade,
        e.exam_name,
        u.name as student_name,
        s.admission_no
    FROM results r
    JOIN exams e ON r.exam_id = e.exam_id
    JOIN student s ON r.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE r.institute_id = $instituteId
    ORDER BY r.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="result.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher Portal</span>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-clipboard-user"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="fees.php">
                        <i class="fa-solid fa-money-bill"></i>
                        <span>Fees</span>
                    </a>
                </li>
                <li>
                    <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li class="active">
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="/ERP-SYSTEM-FOR-COACHING/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="workspace">
                <section class="card form-card">
                    <h3 class="card-title">Enter Student Marks</h3>

                    <?php if (!empty($message)): ?>
                        <div style="background: #dcfce7; color: #15803d; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="result.php" method="POST">
                        <div class="form-group">
                            <label for="selectExam">Select Exam *</label>
                            <select id="selectExam" name="exam_id" required>
                                <option value="" disabled selected>-- Select Exam --</option>
                                <?php if ($exams_res): ?>
                                    <?php while ($e = mysqli_fetch_assoc($exams_res)): ?>
                                        <option value="<?= $e['exam_id'] ?>"><?= htmlspecialchars($e['exam_name']) ?> (<?= htmlspecialchars($e['batch_name']) ?>)</option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="selectStudent">Select Student *</label>
                            <select id="selectStudent" name="student_id" required>
                                <option value="" disabled selected>-- Select Student --</option>
                                <?php if ($students_res): ?>
                                    <?php while ($st = mysqli_fetch_assoc($students_res)): ?>
                                        <option value="<?= $st['student_id'] ?>"><?= htmlspecialchars($st['admission_no']) ?> - <?= htmlspecialchars($st['student_name']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="obtainedMarks">Marks Obtained *</label>
                                <input type="number" id="obtainedMarks" name="obtained_marks" placeholder="e.g. 85" min="0" step="0.5" required>
                            </div>
                            <div class="form-group flex-1">
                                <label for="totalMarks">Total Marks *</label>
                                <input type="number" id="totalMarks" name="total_marks" placeholder="e.g. 100" min="1" step="0.5" required value="100">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="selectGrade">Grade</label>
                            <select id="selectGrade" name="grade" required>
                                <option value="" disabled selected>Select Grade</option>
                                <option value="A+">A+ (Outstanding)</option>
                                <option value="A">A (Excellent)</option>
                                <option value="B">B (Good)</option>
                                <option value="C">C (Average)</option>
                                <option value="D">D (Pass)</option>
                                <option value="F">F (Fail)</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Result</button>
                            <button type="reset" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </section>

                <section class="card list-card">
                    <div class="list-header">
                        <h3 class="card-title">Result List</h3>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>STUDENT</th>
                                    <th>EXAM</th>
                                    <th>MARKS</th>
                                    <th>GRADE</th>
                                    <th class="text-center">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($results_list && mysqli_num_rows($results_list) > 0): ?>
                                    <?php $idx = 1; while ($r = mysqli_fetch_assoc($results_list)): ?>
                                        <tr>
                                            <td><?= $idx++ ?></td>
                                            <td><strong><?= htmlspecialchars($r['student_name']) ?></strong><br><small class="text-muted">Adm No: <?= htmlspecialchars($r['admission_no']) ?></small></td>
                                            <td><?= htmlspecialchars($r['exam_name']) ?></td>
                                            <td><span class="marks-display"><strong><?= htmlspecialchars($r['obtained_marks']) ?></strong> / <?= htmlspecialchars($r['total_marks']) ?></span></td>
                                            <td><span class="badge badge-grade-a"><?= htmlspecialchars($r['grade']) ?></span></td>
                                            <td class="text-center">
                                                <a href="result.php?delete=<?= $r['result_id'] ?>" class="btn-action delete-btn" style="text-decoration:none;" onclick="return confirm('Are you sure you want to delete this result entry?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; color:#94a3b8; padding: 20px;">No results recorded yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
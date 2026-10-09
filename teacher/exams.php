<?php
<<<<<<< HEAD

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

$instituteId = $_SESSION['institute_id'] ?? 1;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $examId = $_POST['exam_id']
           ?? $_POST['id']
           ?? '';

    $batchId = $_POST['batch_name']
            ?? $_POST['batch']
            ?? $_POST['batchId']
            ?? '';

    $examName = $_POST['exam_name']
             ?? $_POST['exam']
             ?? $_POST['name']
             ?? '';

    $examDate = $_POST['exam_date']
             ?? $_POST['date']
             ?? '';

    $totalMarks = $_POST['total_marks']
                ?? $_POST['marks']
                ?? '';

    // Remove extra spaces
    $examId = trim($examId);
    $batchId = trim($batchId);
    $examName = trim($examName);
    $examDate = trim($examDate);
    $totalMarks = trim($totalMarks);


    // ADD EXAM
  

    if ($examId === '') {

        $sql = "INSERT INTO exams
                (institute_id, batch_name, exam_name, exam_date, total_marks, created_date)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("Prepare Error: " . $conn->error);
        }

        $batchId = $batchId;
        $totalMarks = (int)$totalMarks;

        $stmt->bind_param(
            "isssi",
            $instituteId,
            $batchId,
            $examName,
            $examDate,
            $totalMarks
        );

        if (!$stmt->execute()) {
            die("Insert Error: " . $stmt->error);
        }

        $stmt->close();

        header("Location: exams.php");
        exit;
    }

}

  // DELETE EXAM


if (isset($_GET['delete'])) {

    $examId = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM exams
        WHERE exam_id = ?
        AND institute_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $examId,
        $instituteId
    );

    if (!$stmt->execute()) {
        die("Delete Error: " . $stmt->error);
    }

    $stmt->close();

    header("Location: exams.php");
    exit;
}


// FETCH EXAMS


$stmt = $conn->prepare("
    SELECT
        exam_id,
        batch_name,
        exam_name,
        exam_date,
        total_marks,
        created_date
    FROM exams
    WHERE institute_id = ?
    ORDER BY exam_date ASC
");

$stmt->bind_param("i", $instituteId);
$stmt->execute();
$result = $stmt->get_result();

?>

=======
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";

check_access(['teacher']);

$instituteId = $_SESSION['institute_id'] ?? 1;

$editExam = null;
$message = "";
$error = "";

// Handle Form Submission (Add / Edit Exam)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $examId     = intval($_POST['exam_id'] ?? 0);
    $batchName  = sanitize($conn, $_POST['batch_name'] ?? '');
    $examName   = sanitize($conn, $_POST['exam_name'] ?? '');
    $examDate   = sanitize($conn, $_POST['exam_date'] ?? '');
    $totalMarks = intval($_POST['total_marks'] ?? 0);

    if (empty($examName) || empty($batchName) || empty($examDate) || $totalMarks <= 0) {
        $error = "All fields are required and Total Marks must be greater than 0.";
    } else {
        if ($examId > 0) {
            // Update Exam
            $stmt = $conn->prepare("UPDATE exams SET batch_name = ?, exam_name = ?, exam_date = ?, total_marks = ? WHERE exam_id = ? AND institute_id = ?");
            if ($stmt) {
                $stmt->bind_param("sssiii", $batchName, $examName, $examDate, $totalMarks, $examId, $instituteId);
                if ($stmt->execute()) {
                    $message = "Exam updated successfully!";
                } else {
                    $error = "Failed to update exam: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Insert Exam
            $stmt = $conn->prepare("INSERT INTO exams (institute_id, batch_name, exam_name, exam_date, total_marks, created_date) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("isssi", $instituteId, $batchName, $examName, $examDate, $totalMarks);
                if ($stmt->execute()) {
                    $message = "Exam created successfully!";
                } else {
                    $error = "Failed to create exam: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Handle Delete Exam
if (isset($_GET['delete'])) {
    $delExamId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM exams WHERE exam_id = ? AND institute_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $delExamId, $instituteId);
        if ($stmt->execute()) {
            $message = "Exam deleted successfully!";
        } else {
            $error = "Failed to delete exam: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch Exam for Editing if edit param set
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ? AND institute_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $editId, $instituteId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $editExam = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

// Fetch Dynamic Batches List
$batches_res = mysqli_query($conn, "SELECT id, name FROM batch ORDER BY id ASC");

// Fetch All Scheduled Exams
$stmt = $conn->prepare("SELECT exam_id, batch_name, exam_name, exam_date, total_marks, created_date FROM exams WHERE institute_id = ? ORDER BY exam_date DESC");
$stmt->bind_param("i", $instituteId);
$stmt->execute();
$result = $stmt->get_result();
?>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Manage Exams</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="exams.css">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-chalkboard-user"></i>
                <span>Teacher Portal</span>
            </div>
            <ul class="sidebar-menu">
                <li>
<<<<<<< HEAD
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
                <li class="active">
                    <a href="exams.php">
                        <i class="fas fa-file-alt"></i>
                        <span>Exams</span>
                    </a>
                </li>
                <li>
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
                    </a>
=======
                    <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
                </li>
                <li>
                    <a href="attendance.php"><i class="fa-solid fa-clipboard-user"></i> <span>Attendance</span></a>
                </li>
                <li>
                    <a href="fees.php"><i class="fa-solid fa-money-bill"></i> <span>Fees</span></a>
                </li>
                <li class="active">
                    <a href="exams.php"><i class="fas fa-file-alt"></i> <span>Exams</span></a>
                </li>
                <li>
                    <a href="result.php"><i class="fas fa-poll"></i> <span>Result</span></a>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="/ERP-SYSTEM-FOR-COACHING/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <main class="main-content">
            <div class="exam-workspace">
                <section class="card form-card">
                    <div class="card-header">
<<<<<<< HEAD
                        <h3 id="form-title">Create New Exam</h3>
                    </div>
                    <form id="examForm" method="POST" action="exams.php">
                        <input type="hidden" id="examId" value="">

                        <div class="form-group">
                            <label for="examName">Exam Name</label>
                            <input type="text" id="examName" name="exam_name" placeholder="e.g. Unit Test 1 - Physics" required>
=======
                        <h3 id="form-title"><?= $editExam ? 'Edit Exam' : 'Create New Exam' ?></h3>
                    </div>

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

                    <form id="examForm" method="POST" action="exams.php">
                        <input type="hidden" name="exam_id" value="<?= $editExam['exam_id'] ?? '' ?>">

                        <div class="form-group">
                            <label for="examName">Exam Name</label>
                            <input type="text" id="examName" name="exam_name" placeholder="e.g. Unit Test 1 - Physics" required value="<?= htmlspecialchars($editExam['exam_name'] ?? '') ?>">
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                        </div>

                        <div class="form-group">
                            <label for="examDate">Exam Date</label>
<<<<<<< HEAD
                            <input type="date" id="examDate" name="exam_date" required>
=======
                            <input type="date" id="examDate" name="exam_date" required value="<?= htmlspecialchars($editExam['exam_date'] ?? date('Y-m-d')) ?>">
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                        </div>

                        <div class="form-group">
                            <label for="totalMarks">Total Marks</label>
<<<<<<< HEAD
                            <input type="number" id="totalMarks" name="total_marks" placeholder="e.g. 100" min="1" required>
=======
                            <input type="number" id="totalMarks" name="total_marks" placeholder="e.g. 100" min="1" required value="<?= htmlspecialchars($editExam['total_marks'] ?? '100') ?>">
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                        </div>

                        <div class="form-group">
                            <label for="batchSelect">Batch</label>
                            <select id="batchSelect" name="batch_name" required>
<<<<<<< HEAD
                                <option value="" disabled selected>Select Batch</option>
                                <option value="Batch A (Morning)">Batch A (Morning)</option>
                                <option value="Batch B (Evening)">Batch B (Evening)</option>
                                <option value="Crash Course 2026">Crash Course 2026</option>
                                <option value="Target Batch">Target Batch</option>
=======
                                <option value="" disabled <?= empty($editExam) ? 'selected' : '' ?>>Select Batch</option>
                                <?php if ($batches_res && mysqli_num_rows($batches_res) > 0): ?>
                                    <?php while ($b = mysqli_fetch_assoc($batches_res)): ?>
                                        <option value="<?= htmlspecialchars($b['name']) ?>" <?= (isset($editExam['batch_name']) && $editExam['batch_name'] === $b['name']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="General Batch" <?= (isset($editExam['batch_name']) && $editExam['batch_name'] === 'General Batch') ? 'selected' : '' ?>>General Batch</option>
                                <?php endif; ?>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                            </select>
                        </div>

                        <div class="form-actions">
<<<<<<< HEAD
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save Exam</button>
                            <button type="button" class="btn btn-secondary" id="cancelBtn" >Cancel</button>
=======
                            <button type="submit" class="btn btn-primary" id="submitBtn"><?= $editExam ? 'Update Exam' : 'Save Exam' ?></button>
                            <?php if ($editExam): ?>
                                <a href="exams.php" class="btn btn-secondary" style="text-decoration:none; display:inline-block; text-align:center; padding: 8px 16px;">Cancel</a>
                            <?php else: ?>
                                <button type="reset" class="btn btn-secondary" id="cancelBtn">Reset</button>
                            <?php endif; ?>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                        </div>
                    </form>
                </section>

                <section class="card list-card">
                    <div class="card-header list-header">
                        <h3>Scheduled Exams</h3>
<<<<<<< HEAD
                        <div class="filter-box">
                            <input type="text" id="searchInput" placeholder="Search exams..." >
                        </div>
=======
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                    </div>

                    <div class="table-responsive">
                        <table class="exam-table" id="examTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Exam Name</th>
                                    <th>Batch</th>
                                    <th>Date</th>
                                    <th>Total Marks</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="examTableBody">
<<<<<<< HEAD
                                <tbody>

<tbody>

<tbody>

                   <?php while ($row = $result->fetch_assoc()) { ?>

               <tr>
                      <td><?= htmlspecialchars($row['exam_id']) ?></td>
                      <td><?= htmlspecialchars($row['exam_name']) ?></td>
                      <td><?= htmlspecialchars($row['batch_name']) ?></td>
                      <td><?= htmlspecialchars($row['exam_date']) ?></td>
                      <td><?= htmlspecialchars($row['total_marks']) ?></td>
                      <td class="actions-cell">
                      <a href="exams.php?edit=<?= $row['exam_id'] ?>"
                         onclick="return confirm('Are you sure you want to delete this exam?')">
                         Edit</a>

                     <a href="exams.php?delete=<?= $row['exam_id'] ?>"
                       onclick="return confirm('Are you sure you want to delete this exam?')">
                         Delete</a>
                     </td>
               </tr>

                  <?php } 
                  ?>

</tbody>

</tbody>

</tbody>
                                <tr data-id="1">
                                    <td>1</td>
                                    <td><strong>Mathematics Mid-Term</strong></td>
                                    <td><span class="badge badge-batch">Batch A (Morning)</span></td>
                                    <td>2026-10-15</td>
                                    <td>100</td>
                                    <td class="actions-cell">
                                        <button class="btn-action edit-btn">Edit</button>
                                        <button class="btn-action delete-btn">Delete</button>
                                    </td>
                                </tr>
                                <tr data-id="2">
                                    <td>2</td>
                                    <td><strong>Physics Weekly Quiz</strong></td>
                                    <td><span class="badge badge-batch">Batch B (Evening)</span></td>
                                    <td>2026-10-18</td>
                                    <td>25</td>
                                    <td class="actions-cell">
                                        <button class="btn-action edit-btn">Edit</button>
                                        <button class="btn-action delete-btn">Delete</button>
                                    </td>
                                </tr>
                                <tr data-id="3">
                                    <td>3</td>
                                    <td><strong>Chemistry Organic Test</strong></td>
                                    <td><span class="badge badge-batch">Crash Course 2026</span></td>
                                    <td>2026-10-22</td>
                                    <td>50</td>
                                    <td class="actions-cell">
                                        <button class="btn-action edit-btn">Edit</button>
                                        <button class="btn-action delete-btn">Delete</button>
                                    </td>
                                </tr>
=======
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['exam_id']) ?></td>
                                            <td><strong><?= htmlspecialchars($row['exam_name']) ?></strong></td>
                                            <td><span class="badge badge-batch"><?= htmlspecialchars($row['batch_name']) ?></span></td>
                                            <td><?= htmlspecialchars($row['exam_date']) ?></td>
                                            <td><?= htmlspecialchars($row['total_marks']) ?></td>
                                            <td class="actions-cell text-center">
                                                <a href="exams.php?edit=<?= $row['exam_id'] ?>" class="btn-action edit-btn" style="text-decoration:none; display:inline-block;">Edit</a>
                                                <a href="exams.php?delete=<?= $row['exam_id'] ?>" class="btn-action delete-btn" style="text-decoration:none; display:inline-block;" onclick="return confirm('Are you sure you want to delete this exam?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">No scheduled exams found. Create your first exam above!</td>
                                    </tr>
                                <?php endif; ?>
>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>

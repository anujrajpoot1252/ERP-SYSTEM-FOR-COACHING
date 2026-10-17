<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth_check.php";
check_access(['teacher']);

$user_id = (int) ($_SESSION['user_id'] ?? 0);
$institute_id = (int) ($_SESSION['institute_id'] ?? 0);
$batches_stmt = mysqli_prepare($conn, "
    SELECT b.id, b.name FROM batch b
    INNER JOIN teacher t ON t.id = b.teacher_id AND t.institute_id = b.institute_id
    WHERE t.user_id = ? AND t.institute_id = ? AND t.status = 'active'
    ORDER BY b.id ASC
");
mysqli_stmt_bind_param($batches_stmt, 'ii', $user_id, $institute_id);
mysqli_stmt_execute($batches_stmt);
$batches_res = mysqli_stmt_get_result($batches_stmt);
$selected_batch = filter_input(INPUT_GET, 'batch_id', FILTER_VALIDATE_INT) ?: 0;
$csrf_token = request_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Attendance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="attendance.css">
</head>
<body>
    <div class="erp-container">
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
                <li class="active">
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
                <li>
                    <a href="result.php">
                        <i class="fas fa-poll"></i>
                        <span>Result</span>
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="page-title">
                    <h2>Attendance Management</h2>
                </div>
                <div class="user-profile">
                    <span class="role-pill">TEACHER</span>
                    <i class="fa-regular fa-circle-user profile-icon"></i>
                </div>
            </header>

            <main class="content-body">
                <!-- Filter Section -->
                <section class="card-section">
                    <div class="card-section-header">
                        <h3 class="section-title">
                            <i class="fa-solid fa-filter"></i> Select Session
                        </h3>
                    </div>

                    <div class="filter-grid">
                        <div class="filter-item">
                            <label for="batch-select">Batch</label>
                            <select id="batch-select" class="form-control-simple">
                                <option value="">-- Choose Batch --</option>
                                <?php if ($batches_res): ?>
                                    <?php while ($b = mysqli_fetch_assoc($batches_res)): ?>
                                        <option value="<?= $b['id'] ?>" <?= $selected_batch === intval($b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="filter-item">
                            <label for="attendance-date">Date</label>
                            <input
                                type="date"
                                id="attendance-date"
                                class="form-control-simple"
                            >
                        </div>

                        <div class="filter-item filter-button">
                            <button type="button" class="portal-btn btn-load">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Load List
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Attendance List Card -->
                <section class="card-section attendance-card">
                    <div class="card-section-header attendance-header">
                        <div class="header-left">
                            <h3 class="section-title">
                                <i class="fa-solid fa-user-check"></i> Student Attendance
                            </h3>
                            <p class="sub-counter">Mark status for this session</p>
                        </div>

                        <div class="attendance-summary">
                            <span class="summary-pill summary-present">
                                <i class="fa-solid fa-circle-check"></i>
                                <strong id="present-count">0</strong> Present
                            </span>
                            <span class="summary-pill summary-absent">
                                <i class="fa-solid fa-circle-xmark"></i>
                                <strong id="absent-count">0</strong> Absent
                            </span>
                        </div>
                    </div>

                    <form id="attendance-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="student-list">
                        </div>

                        <div class="table-footer-actions">
                            <button type="submit" class="portal-btn save-btn">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Attendance
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </div>

    <script src="attendance_script.js"></script>
</body>
</html>
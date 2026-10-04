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
                        <h3 id="form-title">Create New Exam</h3>
                    </div>
                    <form id="examForm">
                        <input type="hidden" id="examId" value="">

                        <div class="form-group">
                            <label for="examName">Exam Name</label>
                            <input type="text" id="examName" placeholder="e.g. Unit Test 1 - Physics" required>
                        </div>

                        <div class="form-group">
                            <label for="examDate">Exam Date</label>
                            <input type="date" id="examDate" required>
                        </div>

                        <div class="form-group">
                            <label for="totalMarks">Total Marks</label>
                            <input type="number" id="totalMarks" placeholder="e.g. 100" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="batchSelect">Batch</label>
                            <select id="batchSelect" required>
                                <option value="" disabled selected>Select Batch</option>
                                <option value="Batch A (Morning)">Batch A (Morning)</option>
                                <option value="Batch B (Evening)">Batch B (Evening)</option>
                                <option value="Crash Course 2026">Crash Course 2026</option>
                                <option value="Target Batch">Target Batch</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Save Exam</button>
                            <button type="button" class="btn btn-secondary" id="cancelBtn" >Cancel</button>
                        </div>
                    </form>
                </section>

                <section class="card list-card">
                    <div class="card-header list-header">
                        <h3>Scheduled Exams</h3>
                        <div class="filter-box">
                            <input type="text" id="searchInput" placeholder="Search exams..." >
                        </div>
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
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
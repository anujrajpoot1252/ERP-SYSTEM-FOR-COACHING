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
                    
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label for="selectExam">Select Exam</label>
                            <select id="selectExam" name="exam_id" required>
                                <option value="" disabled selected>Select Exam</option>
                                <option value="1">Mathematics Mid-Term (Batch A)</option>
                                <option value="2">Physics Weekly Quiz (Batch B)</option>
                                <option value="3">Chemistry Organic Test (Crash Course)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="studentName">Student Name / Roll No</label>
                            <input type="text" id="studentName" name="student_name" placeholder="e.g. Rahul Sharma (Roll 101)" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="obtainedMarks">Marks Obtained</label>
                                <input type="number" id="obtainedMarks" name="obtained_marks" placeholder="e.g. 85" min="0" required>
                            </div>
                            <div class="form-group flex-1">
                                <label for="totalMarks">Total Marks</label>
                                <input type="number" id="totalMarks" name="total_marks" placeholder="e.g. 100" min="1" required>
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
                        <div class="search-wrap">
                            <input type="text" placeholder="Search results..." class="search-input">
                        </div>
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
                                <tr>
                                    <td>1</td>
                                    <td><strong>studentName</strong><br><small class="text-muted">Roll no.: </small></td>
                                    <td>Name of exam</td>
                                    <td><span class="marks-display"><strong>obtained_marks</strong> / total_marks</span></td>
                                    <td><span class="badge">grade</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn-action edit-btn">Edit</button>
                                        <button type="button" class="btn-action delete-btn">Delete</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><strong>Rahul Sharma</strong><br><small class="text-muted">Roll: 101</small></td>
                                    <td>Mathematics Mid-Term</td>
                                    <td><span class="marks-display"><strong>88</strong> / 100</span></td>
                                    <td><span class="badge badge-grade-a">A</span></td>
                                    <td class="text-center">
                                        <button type="button" class="btn-action edit-btn">Edit</button>
                                        <button type="button" class="btn-action delete-btn">Delete</button>
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
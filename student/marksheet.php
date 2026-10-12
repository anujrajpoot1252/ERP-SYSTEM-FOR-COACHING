<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Marksheet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="marksheet.css">
</head>
<body>
    <div class="layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Student Portal</span>
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="result.php">
                        <i class="fa-solid fa-poll"></i>
                        <span>Results</span>
                    </a>
                </li>
                <li class="active">
                    <a href="marksheet.php">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Marksheet</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="fees.php">
                        <i class="fa-solid fa-credit-card"></i>
                        <span>Fees</span>
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

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <h2>Official Marksheet</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong>Student</strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Action / Selection Bar -->
                <div class="action-bar">
                    <div class="exam-switcher">
                        <label for="exam-select">Select Examination:</label>
                        <select id="exam-select" class="filter-select">
                            <option value="annual-2026">Annual Examination - 2026</option>
                            <option value="midterm-2026">Mid-Term Assessment - 2026</option>
                            <option value="quarterly-2025">Quarterly Assessment - 2025</option>
                        </select>
                    </div>
                    <button type="button" class="btn-print" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Print / Download Marksheet
                    </button>
                </div>

                <!-- Aggregate Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Score</span>
                            <h3 class="stat-value">441 <span style="font-size: 0.85rem; color: #94a3b8;">/ 500</span></h3>
                            <span class="stat-subtext text-success">88.2% Overall</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-indigo">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Final Grade</span>
                            <h3 class="stat-value">A+</h3>
                            <span class="stat-subtext">Distinction</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Result Status</span>
                            <h3 class="stat-value text-success">PASSED</h3>
                            <span class="stat-subtext text-success">Cleared All Subjects</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-slate">
                            <i class="fa-solid fa-ranking-star"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Class Rank</span>
                            <h3 class="stat-value">03</h3>
                            <span class="stat-subtext">Out of 60 students</span>
                        </div>
                    </div>
                </div>

                <!-- Printable Marksheet Container -->
                <div class="marksheet-card">
                    <!-- Academic Header -->
                    <div class="sheet-header">
                        <h3>Coaching Institute Academic Report</h3>
                        <p>Affiliated Academic Evaluation & Performance Record</p>
                        <span class="sheet-badge">STATEMENT OF MARKS</span>
                    </div>

                    <!-- Student Metadata Details -->
                    <div class="student-meta-box">
                        <div class="meta-item">
                            <span>Candidate Name: </span><strong>Student Candidate</strong>
                        </div>
                        <div class="meta-item">
                            <span>Admission No: </span><strong>ADM-2026-089</strong>
                        </div>
                        <div class="meta-item">
                            <span>Roll Number: </span><strong>261048</strong>
                        </div>
                        <div class="meta-item">
                            <span>Stream: </span><strong>Science (PCMB)</strong>
                        </div>
                        <div class="meta-item">
                            <span>Batch: </span><strong>Batch A1 (Morning)</strong>
                        </div>
                        <div class="meta-item">
                            <span>Academic Session: </span><strong>2025 - 2026</strong>
                        </div>
                    </div>

                    <!-- Subject Marksheet Table -->
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>CODE</th>
                                    <th>SUBJECT NAME</th>
                                    <th class="text-center">MAX MARKS</th>
                                    <th class="text-center">MIN PASS</th>
                                    <th class="text-center">MARKS OBTAINED</th>
                                    <th class="text-center">GRADE</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>BIO-101</td>
                                    <td><strong>Biology</strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">33</td>
                                    <td class="text-center"><strong>91</strong></td>
                                    <td class="text-center"><span class="grade-badge">A+</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                </tr>
                                <tr>
                                    <td>PHY-102</td>
                                    <td><strong>Physics</strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">33</td>
                                    <td class="text-center"><strong>85</strong></td>
                                    <td class="text-center"><span class="grade-badge">A</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                </tr>
                                <tr>
                                    <td>CHE-103</td>
                                    <td><strong>Chemistry</strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">33</td>
                                    <td class="text-center"><strong>87</strong></td>
                                    <td class="text-center"><span class="grade-badge">A</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                </tr>
                                <tr>
                                    <td>MTH-104</td>
                                    <td><strong>Mathematics</strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">33</td>
                                    <td class="text-center"><strong>84</strong></td>
                                    <td class="text-center"><span class="grade-badge">A</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                </tr>
                                <tr>
                                    <td>PRC-105</td>
                                    <td><strong>Practical (Lab & Viva)</strong></td>
                                    <td class="text-center">100</td>
                                    <td class="text-center">33</td>
                                    <td class="text-center"><strong>94</strong></td>
                                    <td class="text-center"><span class="grade-badge">A+</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                </tr>
                                <tr class="table-summary-row">
                                    <td colspan="2">GRAND TOTAL</td>
                                    <td class="text-center">500</td>
                                    <td class="text-center">165</td>
                                    <td class="text-center"><strong>441</strong></td>
                                    <td class="text-center"><span class="grade-badge">A+</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">PASS</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Official Verification Signatures -->
                    <div class="sheet-signatures">
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Date of Issue</p>
                        </div>
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Faculty Sign</p>
                        </div>
                        <div class="signature-box">
                            <div class="sig-line"></div>
                            <p>Controller of Examinations</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
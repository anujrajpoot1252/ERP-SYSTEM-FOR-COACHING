<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - My Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="result.css">
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
                <li class="active">
                    <a href="result.php">
                        <i class="fa-solid fa-poll"></i>
                        <span>Results</span>
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
            <!-- Top Navbar -->
            <div class="top-navbar">
                <h2>My Examination Results</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong>Aman Verma</strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Results List Card -->
                <section class="card list-card">
                    <div class="list-header">
                        <h3 class="card-title">Exam Performance History</h3>
                        <div class="search-wrap">
                            <input type="text" placeholder="Search exams..." class="search-input">
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>EXAM NAME</th>
                                    <th>EXAM DATE</th>
                                    <th>MARKS OBTAINED</th>
                                    <th>PERCENTAGE</th>
                                    <th class="text-center">GRADE</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><strong>Mathematics Mid-Term</strong></td>
                                    <td>15 Oct 2026</td>
                                    <td><span class="marks-display"><strong>88</strong> / 100</span></td>
                                    <td>88%</td>
                                    <td class="text-center"><span class="badge badge-grade-a">A</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                    <td class="text-center">
                                        <a href="" class="btn-download" title="Download Marksheet">
                                            <i class="fa-solid fa-download"></i> Marksheet
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><strong>Physics Weekly Quiz</strong></td>
                                    <td>18 Oct 2026</td>
                                    <td><span class="marks-display"><strong>24</strong> / 25</span></td>
                                    <td>96%</td>
                                    <td class="text-center"><span class="badge badge-grade-aplus">A+</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                    <td class="text-center">
                                        <a href="" class="btn-download" title="Download Marksheet">
                                            <i class="fa-solid fa-download"></i> Marksheet
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><strong>Chemistry Organic Test</strong></td>
                                    <td>22 Oct 2026</td>
                                    <td><span class="marks-display"><strong>38</strong> / 50</span></td>
                                    <td>76%</td>
                                    <td class="text-center"><span class="badge badge-grade-b">B</span></td>
                                    <td class="text-center"><span class="status-pill status-pass">Pass</span></td>
                                    <td class="text-center">
                                        <a href="" class="btn-download" title="Download Marksheet">
                                            <i class="fa-solid fa-download"></i> Marksheet
                                        </a>
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
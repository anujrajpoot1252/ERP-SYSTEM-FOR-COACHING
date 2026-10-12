<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Fees</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="fees.css">
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
            <li>
                <a href="marksheet.php">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Marksheet</span></a>
            </li>
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="active">
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
                <h2>Fee Details & Payments</h2>
                <div class="user-profile">
                    <span class="role-badge">STUDENT</span>
                    <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                    <strong>Student</strong>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <!-- Summary Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Course Fee</span>
                            <h3 class="stat-value">₹45,000</h3>
                            <span class="stat-subtext">Session 2026</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Total Paid</span>
                            <h3 class="stat-value text-success">₹30,000</h3>
                            <span class="stat-subtext">2 Installments paid</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-red">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Pending Dues</span>
                            <h3 class="stat-value text-danger">₹15,000</h3>
                            <span class="stat-subtext text-danger">Payment required</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-amber">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div class="stat-details">
                            <span class="stat-label">Next Due Date</span>
                            <h3 class="stat-value">25 Oct</h3>
                            <span class="stat-subtext">3rd Installment</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Fee Banner & Pay Now Action -->
                <div class="pending-alert-card">
                    <div class="pending-alert-content">
                        <div class="pending-alert-icon">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="pending-alert-text">
                            <h4>Pending Installment: ₹15,000</h4>
                            <p>3rd Installment fee payment is awaiting clearance.</p>
                        </div>
                    </div>
                    <div class="pending-alert-actions">
                        <span class="due-date"><i class="fa-regular fa-clock"></i> Due by Oct 25, 2026</span>
                        <button type="button" class="btn-pay-now">
                            <i class="fa-solid fa-shield-halved"></i> Pay Now
                        </button>
                    </div>
                </div>

                <!-- Transaction & Fee History Table -->
                <section class="card list-card">
                    <div class="list-header">
                        <h3 class="card-title">Fee Breakdown & Payment History</h3>
                        <input type="text" class="search-input" placeholder="Search receipt...">
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>FEE TITLE</th>
                                    <th>INVOICE / ID</th>
                                    <th>DUE / PAID DATE</th>
                                    <th>AMOUNT</th>
                                    <th>PAYMENT METHOD</th>
                                    <th class="text-center">STATUS</th>
                                    <th class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td><strong>1st Installment (Admission)</strong></td>
                                    <td>INV-2026-001</td>
                                    <td>10 Jan 2026</td>
                                    <td>₹15,000</td>
                                    <td>UPI / Online</td>
                                    <td class="text-center">
                                        <span class="status-pill status-paid">
                                            <i class="fa-solid fa-check"></i> Paid
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn-receipt">
                                            <i class="fa-solid fa-download"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td><strong>2nd Installment (Mid-Term)</strong></td>
                                    <td>INV-2026-048</td>
                                    <td>15 May 2026</td>
                                    <td>₹15,000</td>
                                    <td>Net Banking</td>
                                    <td class="text-center">
                                        <span class="status-pill status-paid">
                                            <i class="fa-solid fa-check"></i> Paid
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn-receipt">
                                            <i class="fa-solid fa-download"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td><strong>3rd Installment (Final)</strong></td>
                                    <td>INV-2026-102</td>
                                    <td>25 Oct 2026</td>
                                    <td>₹15,000</td>
                                    <td>Pending</td>
                                    <td class="text-center">
                                        <span class="status-pill status-pending">
                                            <i class="fa-solid fa-hourglass-half"></i> Pending
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-pay-now" style="padding: 6px 14px; font-size: 0.78rem;">
                                            Pay Now
                                        </button>
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
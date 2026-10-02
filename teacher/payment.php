<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal - Collect Payment</title>
    <link rel="stylesheet" href="fees.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li>
                    <a href="attendance.php">
                        <i class="fa-solid fa-clipboard-user"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li class="active">
                    <a href="fees.php">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Fees</span>
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

        <!-- Main Wrapper -->
        <main class="main-wrapper">
            <!-- Topbar -->
            <header class="top-navbar">
                <div class="page-title">
                    <h2>Fees Management</h2>
                </div>
                <div class="user-profile">
                    <span class="role-pill">TEACHER</span>
                    <i class="fa-regular fa-circle-user profile-icon"></i>
                    <span class="username"></span>
                </div>
            </header>

            <div class="content-body">
                <div class="form-grid-layout">
                    <!-- Payment Form Card -->
                    <section class="card-section">
                        <div class="card-section-header">
                            <h3 class="section-title"><i class="fa-solid fa-money-bill-wave"></i> Payment Details</h3>
                            <a href="fees.php" class="action-link"><i class="fa-solid fa-arrow-left"></i> Back to Fees</a>
                        </div>

                        <form class="portal-form">
                            <div class="field-group">
                                <label>Student</label>
                                <select class="portal-input">
                                    <option>ADM-2026-004 - Rhea Gupta (NEET Evening Batch B)</option>
                                    <option>ADM-2026-009 - Rohit Verma (JEE Morning Batch A)</option>
                                    <option>ADM-2026-001 - Aman Verma (JEE Morning Batch A)</option>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Payment Method</label>
                                <div class="payment-method-group">
                                    <label class="radio-label">
                                        <input type="radio" name="mode" checked>
                                        <span>UPI / QR</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="mode">
                                        <span>Cash</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="mode">
                                        <span>Net Banking / Cheque</span>
                                    </label>
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field-group">
                                    <label>Amount (₹)</label>
                                    <input type="number" class="portal-input" value="15000">
                                </div>
                                <div class="field-group">
                                    <label>Transaction / Ref No.</label>
                                    <input type="text" class="portal-input" placeholder="e.g. UPI Ref, Receipt ID">
                                </div>
                            </div>

                            <div class="field-group">
                                <label>Note / Remarks</label>
                                <input type="text" class="portal-input" placeholder="Installment 2 collected at centre">
                            </div>

                            <button type="button" class="portal-btn btn-submit">Submit Payment</button>
                        </form>
                    </section>

                    <!-- Summary Info Card -->
                    <section class="card-section">
                        <h3 class="section-title"><i class="fa-solid fa-file-invoice"></i> Student Summary</h3>
                        
                        <div class="info-row">
                            <div class="row-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                            <div class="row-text">Student <strong>Rhea Gupta</strong></div>
                        </div>
                        <div class="info-row">
                            <div class="row-icon"><i class="fa-solid fa-layer-group"></i></div>
                            <div class="row-text">Batch <strong>NEET Evening Batch B</strong></div>
                        </div>

                        <div class="profile-block">
                            <p><strong>Total Fee:</strong> ₹30,000</p>
                            <p><strong>Paid So Far:</strong> ₹15,000</p>
                            <p><strong>Current Due:</strong> ₹15,000</p>
                            <p><strong>Amount Being Paid:</strong> <span class="highlight-text">₹15,000</span></p>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
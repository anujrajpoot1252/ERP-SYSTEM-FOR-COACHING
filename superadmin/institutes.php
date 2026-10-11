<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institutes - ERP Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP-SYSTEM-FOR-COACHING/assets/css/style.css">
    <style>
        .filter-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-input-wrap {
            position: relative;
            min-width: 260px;
            flex: 1;
        }

        .search-input-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input-wrap input {
            padding-left: 36px;
        }

        .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .action-btns {
            display: flex;
            gap: 6px;
        }

        .btn-icon {
            padding: 6px 10px;
            font-size: 13px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-icon:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-icon.danger:hover {
            background: #fee2e2;
            border-color: #ef4444;
            color: #dc2626;
        }

        .institute-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .institute-meta strong {
            color: #0f172a;
            font-size: 14px;
            white-space: nowrap;
        }

        .institute-meta small {
            color: #64748b;
            font-size: 12px;
            white-space: nowrap;
        }

        /* ONLY the table scrolls horizontally */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .card-title {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .card-title a {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .filter-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .search-input-wrap {
                min-width: 100%;
                max-width: 100%;
            }

            .filter-controls {
                display: grid;
                grid-template-columns: 1fr 1fr;
                width: 100%;
                gap: 8px;
            }

            .filter-controls select {
                width: 100% !important;
            }

            .data-table {
                min-width: 700px;
            }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>ERP Super Admin</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            </li>
            <li class="active">
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/institutes.php"><i class="fa-solid fa-building-columns"></i> <span>Institutes</span></a>
            </li>
            <li>
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/add_institute.php"><i class="fa-solid fa-plus-circle"></i> <span>Add Institute</span></a>
            </li>
            <li>
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/subscriptions.php"><i class="fa-solid fa-credit-card"></i> <span>Subscriptions</span></a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <a href="/ERP-SYSTEM-FOR-COACHING/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="top-navbar">
            <h2>All Institutes</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong>Super Admin</strong>
            </div>
        </div>

        <div class="page-body">
            <div class="card">
                <div class="card-title">
                    <span><i class="fa-solid fa-building-columns"></i> Institutes Directory</span>
                    <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/add_institute.php" class="btn-primary" style="padding: 6px 14px; text-decoration: none; font-size: 13px; width: auto;">
                        <i class="fa-solid fa-plus"></i> Add Institute
                    </a>
                </div>

                <!-- Filters & Search Toolbar -->
                <div class="filter-toolbar">
                    <div class="search-input-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" class="form-control-simple" placeholder="Search by name, code or email...">
                    </div>
                    <div class="filter-controls">
                        <select class="form-control-simple">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="trial">Trial</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <select class="form-control-simple">
                            <option value="">Sort By</option>
                            <option value="newest">Newest First</option>
                            <option value="name">Name (A-Z)</option>
                        </select>
                    </div>
                </div>

                <!-- Scrollable Table Wrapper -->
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Institute</th>
                                <th>Admin In-Charge</th>
                                <th>Contact</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="institute-meta">
                                        <strong>Science Academy</strong>
                                        <small>Code: academy</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="institute-meta">
                                        <span>Ramesh Sharma</span>
                                        <small>ramesh@academy.com</small>
                                    </div>
                                </td>
                                <td>+91 98765 43210</td>
                                <td><strong>Pro</strong></td>
                                <td><span class="status-pill pill-active">Active</span></td>
                                <td>Jan 15, 2026</td>
                                <td>
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <button class="btn-icon" title="View Details"><i class="fa-solid fa-eye"></i></button>
                                        <button class="btn-icon" title="Edit Institute"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="btn-icon danger" title="Remove Institute"><i class="fa-solid fa-ban"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
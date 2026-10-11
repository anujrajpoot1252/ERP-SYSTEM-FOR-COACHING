<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Institute - Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/ERP-SYSTEM-FOR-COACHING/assets/css/style.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-title i {
            color: var(--primary);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .btn-secondary {
            padding: 10px 20px;
            background-color: transparent;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .form-actions {
                flex-direction: column-reverse;
            }
            .form-actions button,
            .form-actions a {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>ERP Super Admin</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <span>Dashboard</span></a>
            </li>
            <li>
                <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/institutes.php"><i class="fa-solid fa-building-columns"></i> <span>Institutes</span></a>
            </li>
            <li class="active">
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

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h2>Add New Institute</h2>
            <div class="user-profile">
                <span class="role-badge">SUPER ADMIN</span>
                <i class="fa-solid fa-user-circle fa-xl" style="color: #64748b;"></i>
                <strong>Super Admin</strong>
            </div>
        </div>

        <div class="page-body">
            <div class="card">
                <form action="#" method="POST" onsubmit="event.preventDefault();">
                    
                    <!-- Institute Details -->
                    <div class="form-section-title">
                        <i class="fa-solid fa-school"></i> Institute Information
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Institute Name</label>
                            <input type="text" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Institute Code / Subdomain</label>
                            <input type="text" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Official Email</label>
                            <input type="email" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" class="form-control-simple" required>
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>Address</label>
                            <input type="text" class="form-control-simple">
                        </div>
                    </div>

                    <!-- Subscription / Plan -->
                    <div class="form-section-title" style="margin-top: 16px;">
                        <i class="fa-solid fa-tag"></i> Subscription & Access
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Plan Type</label>
                            <select class="form-control-simple" required>
                                <option value="standard">Standard Plan</option>
                                <option value="trial">14-Day Free Trial</option>
                                <option value="pro">Pro Plan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Account Status</label>
                            <select class="form-control-simple" required>
                                <option value="trial">Trial</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Initial Admin Credentials -->
                    <div class="form-section-title" style="margin-top: 16px;">
                        <i class="fa-solid fa-user-shield"></i> Institute Admin Account
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Admin Full Name</label>
                            <input type="text" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Admin Username</label>
                            <input type="text" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Admin Login Email</label>
                            <input type="email" class="form-control-simple" required>
                        </div>
                        <div class="form-group">
                            <label>Admin Password</label>
                            <input type="password" class="form-control-simple" required>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <a href="/ERP-SYSTEM-FOR-COACHING/superadmin/dashboard.php" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-plus"></i> Save Institute
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
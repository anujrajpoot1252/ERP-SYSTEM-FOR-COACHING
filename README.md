# Coaching ERP — Institutional Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Design System](https://img.shields.io/badge/UI%20System-Warm%20Editorial-9a3412?style=flat)](index.html)
[![Hosting](https://img.shields.io/badge/Deploy-InfinityFree%20%7C%20Apache-22c55e?style=flat)](https://infinityfree.net/)

A comprehensive, production-ready Enterprise Resource Planning (ERP) web system tailored specifically for coaching academies, tutorial centers, and competitive examination institutes. It unifies institutional operations across four dedicated role portals: **Super Admin**, **Institute Administrator**, **Faculty & Teachers**, and **Scholars & Students**.

---

## 📌 Project Description

Coaching institutes frequently face operational challenges due to scattered paper registers, offline fee receipts, unorganized roll calls, and isolated academic performance logs.

**Coaching ERP** bridges this gap by delivering a centralized, role-based platform where:
- **Super Administrators** license, register, and supervise multi-branch institutes.
- **Institute Administrators** manage admissions, teacher allocations, batch scheduling, and student fee ledger installments.
- **Teachers & Faculty** execute daily roll-call attendance with automated parent notification tracking, conduct examinations, and submit test scores.
- **Students & Parents** monitor monthly attendance ratios, print examination marksheets, and download tuition vouchers.

The entire interface has been meticulously redesigned through the **Unslop (Warm Editorial & Archival)** design philosophy, replacing generic neon gradients and emoji clutter with disciplined typography, tabular figures, and an authentic academic atmosphere.

---

## ✨ Features

### 1. 🛡️ Role-Based Access Control (RBAC) & Security
- **Single Universal Login:** Smart authentication gateway (`login.php`) that verifies password hashes, identifies assigned roles (`superadmin`, `admin`, `teacher`, `student`), and directs the user to their authorized dashboard.
- **Hardened Security:** CSRF token verification across all modification endpoints (`X-CSRF-Token`), secure session cookies, and SQL injection prevention with prepared statements (`mysqli_prepare`).

### 2. 🏛️ Super Admin Module
- Multi-tenant institute registration, license allocation, and active status control.
- System-wide administrative overview of student counts, active faculties, and subscription periods.

### 3. 🏫 Institute Administration Module
- **Admissions Management:** Atomic student registration that creates both the user account and the schema-backed student profile in a single transaction.
- **Faculty Directory:** Instructor recruitment, subject assignment, and contact records.
- **Batch Scheduling:** Class batch allocation with start/end timing and student mapping.
- **Tuition Fee Tracking:** Installment tracking, cashback handling, payment mode records, and voucher creation.

### 4. 👨‍🏫 Faculty & Teacher Workspace
- **Daily Attendance Registrar:** Streamlined roll-call attendance marking (`present` / `absent`) with duplicate-entry prevention.
- **Parent Phone Safeguard:** Phone number retention with `COALESCE` handling for automated communication logs.
- **Examination Management:** Test series scheduling and total score configuration.
- **Marks Evaluation:** Individual student score submission and automated grade allocation.

### 5. 🎓 Student & Scholar Desk
- Real-time attendance ledger displaying percentage records and absent date breakdowns.
- Digital examination report cards with printable layout.
- Tuition payment records, fee vouchers, and settlement confirmations.

### 6. 🎨 Unslopped "Warm Editorial" UI/UX
- **Palette:** Warm paper neutrals (`#f7f4ee` page background, `#fdfbf7` card surfaces, `#efe9df` active rows) paired with a rich terracotta-brass accent (`#9a3412`).
- **Typography:** *Newsreader* editorial serif for display headlines, *Instrument Sans* for interface controls, and *JetBrains Mono* for tabular metrics and roll codes.
- **Spatial Rhythm:** Strict 4-pixel spatial grid and nested border radius mathematics (`16px` container &rarr; `12px` card &rarr; `8px` button &rarr; `4px` tag).
- **Product Craft:** An authentic **Academic Operations Ledger** hero preview showcasing batch timetables, live session attendance, and tuition settlement vouchers.

---

## 🛠️ Technologies Used

| Layer | Technologies & Tools | Description |
|---|---|---|
| **Backend** | PHP 8.1+ | Dynamic routing, session lifecycle, REST endpoints, and RBAC authentication |
| **Database** | MySQL / MariaDB (InnoDB) | Relational schema with foreign keys, transactional integrity, and unique constraints |
| **Frontend UI** | HTML5, CSS3, Modern ES6 JS | Lightweight semantic markup, flexbox & CSS grid layout, zero heavy framework bloat |
| **Typography** | Newsreader, Instrument Sans, JetBrains Mono | Editorial serifs, legible interface sans, and tabular monospaced figures |
| **Iconography** | Inline SVG & FontAwesome 6 | Strict 16px/18px stroke consistency in neutral roles |
| **Server & Hosting** | Apache (.htaccess), InfinityFree, XAMPP | `DirectoryIndex`, header security rules (`SAMEORIGIN`, `nosniff`), and file protections |
| **Payments (Ready)** | Razorpay Integration | Webhook and order verification support for online payments |

---

## 📸 Screenshots & UI Preview

### 1. Master Portal Directory & Landing Page (`index.html`)
The landing portal features an authentic coaching ledger, demonstration credentials, and access cards for all role-based portals:

```
+---------------------------------------------------------------------------------------------+
|  [ER] Coaching ERP  v2.4                              [Role Portals] [Directory] [Sign In]  |
+---------------------------------------------------------------------------------------------+
|                                                                                             |
|  Institutional management for coaching academies      +-- BATCH-XI-MED (OCT 2026) --------+ |
|  & test prep institutes                               | 32 Enrolled | 93.8% Attnd | ₹1.42L| |
|                                                       |                                   | |
|  [ Access Authentication Portal ] [ Inspect Portals ] | MED-101 Aarav Sharma  [Present]   | |
|                                                       | MED-102 Diya Patel    [Present]   | |
|                                                       | MED-103 Rohan Verma   [Absent]    | |
|                                                       +-----------------------------------+ |
+---------------------------------------------------------------------------------------------+
|  [01 Super Admin]        [02 Institute Admin]     [03 Faculty Portal]    [04 Student Desk]  |
|  Multi-branch licensing  Admissions & Batches     Daily Roll Call        Marksheet & Fees   |
+---------------------------------------------------------------------------------------------+
```

### 2. Universal Login Screen (`login.php`)
A clean, distraction-free authentication gateway styled with the warm archival palette:
- Role auto-detection from single entry point.
- Secure CSRF protection and sanitized inputs.

---

## ⚙️ Installation & Setup Guide

### Option A: Local Installation (XAMPP / WAMP)

#### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.0+ and MySQL).
- Git installed on your machine.

#### 2. Clone the Repository
Clone this repository into your XAMPP `htdocs` directory:
```bash
cd C:/xampp/htdocs
git clone https://github.com/anujrajpoot1252/ERP-SYSTEM-FOR-COACHING.git ERP-SYSTEM-FOR-COACHING
cd ERP-SYSTEM-FOR-COACHING
```

#### 3. Database Configuration
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open `http://localhost/phpmyadmin/` in your browser.
3. Create a new database named `erp_system`.
4. Import the database schema:
   - Click `erp_system` &rarr; **Import** tab.
   - Choose file: `schema/schema.sql` &rarr; click **Go**.
5. Check your `.env` configuration file in the project root:
   ```env
   DB_HOST=127.0.0.1
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=erp_system
   API_URL=http://localhost/ERP-SYSTEM-FOR-COACHING
   ```

#### 4. Run the Application
Open your browser and navigate to:
```
http://localhost/ERP-SYSTEM-FOR-COACHING/
```
Or start PHP's built-in development server directly:
```bash
php -S 127.0.0.1:8000
```
and visit `http://127.0.0.1:8000/`.

---

### Option B: Free Live Hosting (InfinityFree)

This project has built-in auto-detection for InfinityFree hosting inside `config/db.php`.

1. **Create Account & Database on InfinityFree:**
   - Log in to [InfinityFree Dashboard](https://dash.infinityfree.com/).
   - Under **Control Panel (vPanel)** &rarr; **MySQL Databases**, create a database (e.g. `if0_43254250_erp`).
   - Note your **MySQL Hostname** (e.g., `sql108.infinityfree.com`).

2. **Upload Files:**
   - Connect via FTP (FileZilla) or Monsta File Manager:
     - **Host:** `ftpupload.net`
     - **Port:** `21`
     - **Username:** Your InfinityFree username (e.g. `if0_43254250`)
   - Upload all project files directly into the **`htdocs/`** directory.
   - Ensure the included `.htaccess` file is uploaded so `index.html` loads as the root landing page.

3. **Import Database Schema:**
   - In vPanel, open **phpMyAdmin** for your database.
   - Click **Import**, choose `schema/schema.sql`, and execute.

4. **Access Live Website:**
   - Visit your assigned domain (e.g., `http://your-domain.kesug.com/` or `http://your-domain.infinityfreeapp.com/`).

---

## 🔑 Default Demonstration Credentials

| Role | Email Address | Default Password | Permissions |
|---|---|---|---|
| **Super Admin** | `admin@erp.com` | `admin123` | Complete system access & institute provisioning |
| **Institute Admin** | *(Created via Superadmin)* | *(Assigned password)* | Admissions, teachers, batches & fees |
| **Teacher** | *(Created via Admin)* | *(Assigned password)* | Attendance, exams, marks submission |
| **Student** | *(Created via Admin)* | *(Assigned password)* | Attendance history, marksheet & fees |

---

## 📂 Project Structure

```
ERP-SYSTEM-FOR-COACHING/
├── .htaccess               # Apache configuration, security guards & DirectoryIndex
├── .env                    # Database & API environment variables
├── index.html              # Redesigned Warm Editorial landing page & master navigation
├── index.php               # Dynamic session router and role redirection handler
├── login.php               # Unified universal authentication gateway
├── logout.php              # Session invalidation & security cleanup
├── README.md               # Project documentation
│
├── admin/                  # Institute Admin module (students, teachers, batches, stats)
├── api/                    # JSON REST endpoints (attendance, fees, courses, auth)
├── assets/                 # Stylesheets, custom CSS & icons
│   └── css/style.css       # Unified design system stylesheet
├── config/                 # Core configuration files
│   ├── db.php              # Smart MySQL connection with InfinityFree auto-detection
│   └── api.php             # API headers, CORS, and response helpers
├── includes/               # Reusable headers, navigation sidebars & auth guards
├── schema/                 # Database schema definitions
│   └── schema.sql          # Canonical MySQL database structure & seed data
├── student/                # Student portal (attendance, marksheet, fees)
├── superadmin/             # Multi-tenant Superadmin management portal
└── teacher/                # Faculty portal (roll-call attendance, exams, results)
```

---

## 👨‍💻 Author

**Anuj Rajpoot**  
- GitHub: [@anujrajpoot1252](https://github.com/anujrajpoot1252)  
- Repository: [https://github.com/anujrajpoot1252/ERP-SYSTEM-FOR-COACHING](https://github.com/anujrajpoot1252/ERP-SYSTEM-FOR-COACHING.git)

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

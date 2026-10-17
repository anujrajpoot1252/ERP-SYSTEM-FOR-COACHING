# ERP-SYSTEM-FOR-COACHING

<!-- NOTE -->
<!-- 
Kunal => index.php (dynamic router)

HomePage = http://localhost:8000/
Insitude-registration form =  http://localhost:8000/institude-register

--------------------------------------------------------------------
Login form = http://localhost:8000/login    
(All user can login by this single page if user's credentials is valid or not comparing by roles, and then comparing email or password then login... and showing by roles pages. )
--------------------------------------------------------------------

 -->

{
(Anuj rajpoot)
 Database & Connection:

config/db.php  MySQL connection fix kiya aur automatic database banne ka logic lagaya.
schema/schema.sql  Canonical schema for institutes, users, teachers, students, courses, batches, attendance, fees, exams, and results.
Login System:

login.php  Naya login system banaya jisse Email/Password se Auth hota hai aur Role (Admin / Teacher / Student) ke hisaab se dashboard khulta hai.
logout.php  Logout button aur session clear handler.
includes/auth_check.php  Security lock (bina login koi page direct nahi khol sakta).
}

Authentication and API notes:

- Database connection settings can be supplied through `DB_HOST`, `DB_USER`, `DB_PASSWORD`, and `DB_NAME` in the environment or a root `.env` file.
- Configure Razorpay with `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET`. The webhook endpoint must be configured in the Razorpay dashboard for `payment.captured` and `payment.failed`.
- Admin student and teacher records are created with their linked user accounts. Use the admin pages for complete student registration; standalone legacy registration endpoints are retired because they did not create schema-valid profiles.
- API writes require the session CSRF token in the `X-CSRF-Token` header (or `csrf_token` request field) and are scoped by role and institute.

Admin Panel (admin/ folder):

admin/dashboard.php  Admin ke liye Students, Teachers, Batches aur Courses ke Live Cards; superadmin ke liye institute overview.
admin/institutes.php  Superadmin-only institute creation with an administrator login and subscription expiry.
admin/students.php Student add, view, edit, and delete with linked account management.
admin/teachers.php Teacher add, view, edit, and delete with linked account management.
admin/batches.php Batch create, view, edit, and delete.

Superadmin access:

- Fresh database initialization seeds `admin@erp.com` with the `superadmin` role.
- For an existing database initialized before this change, promote the seeded account once in MySQL: `UPDATE users SET role = 'superadmin' WHERE email = 'admin@erp.com' AND name = 'Super Admin' AND role = 'admin';`
- Institute creation is available from the superadmin dashboard. It creates an institute and its `admin` login together; the administrator email is used to sign in.
Teacher & Student Dashboards:

teacher/dashboard.php  Teacher Profile aur unhe assign kiye gaye Batches.
student/dashboard.php  Student Profile, Admission No, Course aur Class Timing.
CSS Design:

assets/css/style.css  Nayi design file (Aapki kisi bhi puraani style.css file ko bina touch kiye alag se banayi gayi hai).




{
(anuj rajpoot)
(PROBLME SOLVE )


 404 & Redirection Errors Fixed: auth_check.php, attendance_script.js aur api.js ke galat folder paths aur port (8000) sahi kiye.
 Missing Tables Added: Database mein exams aur results tables ki auto-creation script jodi taaki Marks aur Exams save ho sakein.
 Student Records: Admin student management creates and updates both the user account and the schema-backed student profile in one transaction.
 Attendance Duplicate Bug Resolved: Re-marking par purana attendance update hoga, duplicate record nahi banega.
 Real Database Integration: fees.php, payment.php aur result.php se fake/dummy text hata kar live database connect kiya.
 Admin & Teacher Forms Cleaned: Add Student & Teacher form se hardcoded default password (value="student123") hata diya.
Unused Files Deleted: Faltu files/folders (test.html, index.html, styles.css, students/, public/, extra .sql files) permanently delete kar diye.

(new commit)
(Anuj rajpoot)


Project Update: Sirf 2 files change kiye hain:
• mark_attendance.php: Superadmin access add kiya, JSON payload compatibility fix ki, parent phone wipe-out issue handle kiya, aur multi-institute check fix kiya.
• dashboard.php: Line 115 par mysqli_query ko mysqli_prepare se replace karke server fatal crash issue fix kiya.
Baaki saari files unchanged hain.

(Agr samjh nhi aye to explain kar diya hai check kar lena )

1. api/attendance/mark_attendance.php
• Superadmin ko access diya (api_require_access).
• JSON + Form payload compatibility fix ki ($_POST merge kiya).
• Parent phone wipe-out fix kiya using COALESCE.
• Auto parent phone function check kiya.
• Institute check fix kiya multi-institute ke liye.
2. admin/dashboard.php
• Fatal crash fix kiya.
• Line 115 par mysqli_query() ki jagah mysqli_prepare() kiya context dynamic safety ke liye.
In 2 files ke alawa baaki koi file change nahi ki gayi hai.

}






















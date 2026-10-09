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



 Database & Connection:

config/db.php  MySQL connection fix kiya aur automatic database banne ka logic lagaya.
schema/schema.sql  7 tables (users, institute, teacher, student, course, batch, attendance) aur seed data ki nayi file banayi.
Login System:

login.php  Naya login system banaya jisse Email/Password se Auth hota hai aur Role (Admin / Teacher / Student) ke hisaab se dashboard khulta hai.
logout.php  Logout button aur session clear handler.
includes/auth_check.php  Security lock (bina login koi page direct nahi khol sakta).
Admin Panel (admin/ folder):

admin/dashboard.php  Total Students, Teachers, Batches aur Courses ke Live Cards.
admin/students.php Naya Student Add karne ka Form + Student List table.
admin/teachers.php Naya Teacher Add karne ka Form + Faculty List table.
admin/batches.php Naya Batch Create karne ka Form + Active Batches list.
Teacher & Student Dashboards:

teacher/dashboard.php  Teacher Profile aur unhe assign kiye gaye Batches.
student/dashboard.php  Student Profile, Admission No, Course aur Class Timing.
CSS Design:

assets/css/style.css  Nayi design file (Aapki kisi bhi puraani style.css file ko bina touch kiye alag se banayi gayi hai).
<<<<<<< HEAD
=======


(PROBLME SOLVE )


 404 & Redirection Errors Fixed: auth_check.php, attendance_script.js aur api.js ke galat folder paths aur port (8000) sahi kiye.
 Missing Tables Added: Database mein exams aur results tables ki auto-creation script jodi taaki Marks aur Exams save ho sakein.
 Student Registration Fix: Registration query ko sahi karke users aur student tables ke saath properly link kiya.
 Attendance Duplicate Bug Resolved: Re-marking par purana attendance update hoga, duplicate record nahi banega.
 Real Database Integration: fees.php, payment.php aur result.php se fake/dummy text hata kar live database connect kiya.
 Admin & Teacher Forms Cleaned: Add Student & Teacher form se hardcoded default password (value="student123") hata diya.
Unused Files Deleted: Faltu files/folders (test.html, index.html, styles.css, students/, public/, extra .sql files) permanently delete kar diye.




























>>>>>>> ae921aca951a77a64057d77aecbabb0efc7f2460

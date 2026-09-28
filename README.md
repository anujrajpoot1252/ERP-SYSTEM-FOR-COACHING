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

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('presence','absence') NOT NULL,
  `phone_number_parents` varchar(50) DEFAULT NULL,
  `marking` varchar(20) DEFAULT NULL
  PRIMARY KEY('id')
)
--
-- Table structure for table `batch`
--

CREATE TABLE `batch` (
  `id` int(11) AUTO_INCREMENT NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `institute_id` int(11) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `ending_date` date DEFAULT NULL,
  `timing_status` time DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL,
  PRIMARY KEY ('id')
) 
--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `id` int(11) AUTO_INCREMENT NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `institute_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `fees` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY('id')
) 
-- Table structure for table `institute`
--

CREATE TABLE `institute` (
  `id` varchar(255) AUTO_INCREMENT NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `address` varchar(255) NOT NULL,
  `subscription` enum('active','expired') NOT NULL,
  `expiry` date NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) 

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` varchar(255) AUTO_INCREMENT NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `institute_id` varchar(255) NOT NULL,
  `admission_no` varchar(50) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `parent_phone` varchar(15) NOT NULL,
  `course_id` varchar(255) NOT NULL,
  `batch_id` varchar(255) NOT NULL,
  `date_time` datetime NOT NULL,
  `status` enum('active','inactive') NOT NULL,
   PRIMARY KEY (`id`)
) 

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` varchar(255) AUTO_INCREMENT NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `institute_id` varchar(255) NOT NULL,
  `phone_no` varchar(15) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `joining` date NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  PRIMARY KEY (`id`)
) 

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(255) AUTO_INCREMENT NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','teacher','student') NOT NULL,
  `institute_id` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
)


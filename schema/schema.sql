-- Clean ERP System Schema

DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `fees`;
DROP TABLE IF EXISTS `student`;
DROP TABLE IF EXISTS `batch`;
DROP TABLE IF EXISTS `teacher`;
DROP TABLE IF EXISTS `course`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `institute`;

-- 1. Institute Table
CREATE TABLE `institute` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone_no` VARCHAR(15) NOT NULL,
  `address` TEXT NOT NULL,
  `subscription` ENUM('active','expired') DEFAULT 'active',
  `expiry` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Institute
INSERT INTO `institute` (`id`, `name`, `email`, `phone_no`, `address`, `subscription`, `expiry`) VALUES
(1, 'Apex Coaching Academy', 'contact@apex.com', '9876543210', '123 Main Street, City', 'active', '2027-12-31');

-- 2. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('superadmin','admin','teacher','student') NOT NULL,
  `institute_id` INT DEFAULT 1,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Users (Passwords: admin123, teacher123, student123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `institute_id`, `status`) VALUES
(1, 'Super Admin', 'admin@erp.com', '$2y$10$CcK51ZFvdF1vOIJLiLQBYOlB3PpU8xldxJiGBeaHqYNL0pCGtbcwC', 'admin', 1, 'active'),
(2, 'Rahul Sharma', 'teacher@erp.com', '$2y$10$8Vb1aUYzKg2BvvDyLdiME.4NgPFvLjMFOydHhRON9AfpjVVTrZLoS', 'teacher', 1, 'active'),
(3, 'Aman Verma', 'student@erp.com', '$2y$10$KnOsVc3mSylyro0u8GFmG.dfUNqAnUskwrShVeYYj5YQuMwSkoAcu', 'student', 1, 'active');

-- 3. Teacher Table
CREATE TABLE `teacher` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `institute_id` INT DEFAULT 1,
  `phone_no` VARCHAR(15) NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `joining_date` DATE NOT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Teacher
INSERT INTO `teacher` (`id`, `user_id`, `institute_id`, `phone_no`, `subject`, `joining_date`, `status`) VALUES
(1, 2, 1, '9876512345', 'Physics & Mathematics', '2025-01-15', 'active');

-- 4. Course Table
CREATE TABLE `course` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `institute_id` INT DEFAULT 1,
  `course_name` VARCHAR(100) NOT NULL,
  `duration` VARCHAR(50) NOT NULL,
  `fees` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Courses
INSERT INTO `course` (`id`, `institute_id`, `course_name`, `duration`, `fees`, `status`) VALUES
(1, 1, 'JEE Advanced Prep', '1 Year', 45000.00, 'active'),
(2, 1, 'NEET Medical', '1 Year', 50000.00, 'active'),
(3, 1, 'Class 10th Foundation', '6 Months', 25000.00, 'active');

-- 5. Batch Table
CREATE TABLE `batch` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `institute_id` INT DEFAULT 1,
  `course_id` INT NOT NULL,
  `teacher_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `start_date` DATE NULL,
  `ending_date` DATE NULL,
  `timing_status` TIME NULL,
  `schedule` VARCHAR(255) NULL,
  FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `teacher`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Batches
INSERT INTO `batch` (`id`, `institute_id`, `course_id`, `teacher_id`, `name`, `start_date`, `ending_date`, `timing_status`, `schedule`) VALUES
(1, 1, 1, 1, 'JEE Morning Batch A', '2026-04-01', '2027-03-31', '08:00:00', 'Mon - Sat'),
(2, 1, 2, 1, 'NEET Evening Batch B', '2026-04-01', '2027-03-31', '16:00:00', 'Mon - Fri');

-- 6. Student Table
CREATE TABLE `student` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `institute_id` INT DEFAULT 1,
  `admission_no` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `parent_name` VARCHAR(100) NOT NULL,
  `parent_phone` VARCHAR(15) NOT NULL,
  `course_id` INT DEFAULT NULL,
  `batch_id` INT DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`institute_id`) REFERENCES `institute`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `course`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`batch_id`) REFERENCES `batch`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Student
INSERT INTO `student` (`id`, `user_id`, `institute_id`, `admission_no`, `phone`, `parent_name`, `parent_phone`, `course_id`, `batch_id`, `status`) VALUES
(1, 3, 1, 'ADM-2026-001', '9123456789', 'Suresh Verma', '9811223344', 1, 1, 'active');

-- 7. Attendance Table
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `batch_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present','absent') NOT NULL,
  `phone_number_parents` VARCHAR(50) NULL,
  `marking` VARCHAR(20) NULL,
  FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`batch_id`) REFERENCES `batch`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Fee Payment Table
CREATE TABLE `fees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fees_student_id` (`student_id`),
  FOREIGN KEY (`student_id`) REFERENCES `student`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

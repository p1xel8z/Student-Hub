-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 08:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studenthub`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `user_name`, `action`, `details`, `created_at`) VALUES
(1, 6, 'Admin Tester', 'EDIT_STUDENT', 'Updated student: Reyansh Mehta (Student ID: 23)', '2026-10-22 14:56:01');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `category` varchar(30) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `venue` varchar(150) NOT NULL DEFAULT 'TBA',
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `description` text NOT NULL DEFAULT '',
  `poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `event_date`, `category`, `location`, `venue`, `status`, `description`, `poster`) VALUES
(1, 'Web Development Workshop', '2026-11-10', 'Workshop', 'Computer Lab', 'TBA', 'open', '', NULL),
(2, 'Cybersecurity Seminar', '2026-11-15', NULL, NULL, 'TBA', 'open', '', NULL),
(3, 'Tech Fest', '2026-10-10', 'Technical', 'Main Auditorium', 'TBA', 'open', '', NULL),
(4, 'Cultural Night', '2026-10-15', 'Cultural', 'Open Ground', 'TBA', 'open', '', NULL),
(5, 'Coding Competition', '2026-10-20', 'Technical', 'Computer Lab', 'TBA', 'open', '', NULL),
(6, 'Annual Sports Meet', '2026-11-02', 'Sports', 'Sports Complex', 'TBA', 'open', '', NULL),
(7, 'Photography Workshop', '2026-11-05', 'Workshop', 'Seminar Hall', 'TBA', 'open', '', NULL),
(8, 'Dance Competition', '2026-11-15', 'Cultural', 'Open Ground', 'TBA', 'open', '', NULL),
(9, 'Football Tournament', '2026-11-20', 'Sports', 'Sports Complex', 'TBA', 'open', '', NULL),
(10, 'Robotics Exhibition', '2026-11-25', 'Technical', 'Main Auditorium', 'TBA', 'open', '', NULL),
(11, 'Music Festival', '2026-12-01', 'Cultural', 'Open Ground', 'TBA', 'open', '', NULL),
(12, 'AI Seminar', '2026-12-05', 'Technical', 'Seminar Hall', 'TBA', 'open', '', NULL),
(13, 'Basketball Tournament', '2026-12-10', 'Sports', 'Sports Complex', 'TBA', 'open', '', NULL),
(14, 'Resume Building Session', '2026-12-15', 'Workshop', 'Seminar Hall', 'TBA', 'open', '', NULL),
(15, 'Photography Exhibition', '2026-12-20', 'Cultural', 'Art Gallery', 'TBA', 'open', '', NULL),
(16, 'Hackathon', '2026-12-25', 'Technical', 'Computer Lab', 'TBA', 'open', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `student_id`, `event_id`, `registration_date`) VALUES
(4, 3, 13, '2026-10-22 11:22:54');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `college_student_id` varchar(30) DEFAULT NULL,
  `mobile` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `program` enum('cse','it','ce') DEFAULT NULL,
  `study_year` tinyint(3) UNSIGNED DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `name`, `email`, `college_student_id`, `mobile`, `date_of_birth`, `gender`, `program`, `study_year`, `password_hash`, `role`, `last_login`) VALUES
(3, 'Dev Mehta', 'dev.mehta.p8@example.com', 'WDF2026004', '9123456780', '2005-06-22', 'male', 'cse', 1, '$2y$10$mzvJc/oz6/f0IOoZlbNWY.y.NqIyDv5FhKGtluo2ACUdN2MfuAzTS', 'student', '2026-10-22 20:36:50'),
(4, 'Kavya Desai', 'kavya.desai.p8@example.com', 'WDF2026005', '9012345678', '2004-06-16', 'female', 'it', 3, '$2y$10$kpiFXiGsvlVm1n9iTrTmhO8Oc/ZZCXUiZujptL1PXvpxXlbVkvyom', 'student', NULL),
(5, 'Aarav Shah', 'aarav.shah.p9@example.com', 'WDF202606', '9876543210', '2006-01-22', 'male', 'cse', 2, '$2y$10$E/23F/zjLaTa4Gl1eiZfseh1Ia84sGd1lBJWbRPjj3fZIuwMDSD5u', 'student', NULL),
(6, 'Admin Tester', 'admin.tester@example.com', 'WDFADMIN001', '9876543210', '2005-12-12', 'male', 'cse', 3, '$2y$10$wiqmOLe1tQBdfLzJkvVtYulfS9F29/ubouUEGLu.vZ0RTvuZ5zz96', 'admin', '2026-10-22 20:26:42'),
(8, 'Riya Patel', 'p11seed01@example.com', 'WDFP11001', '9876500101', '2005-01-12', 'female', 'cse', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(9, 'Arjun Shah', 'p11seed02@example.com', 'WDFP11002', '9876500102', '2004-05-23', 'male', 'it', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(10, 'Neha Desai', 'p11seed03@example.com', 'WDFP11003', '9876500103', '2005-03-17', 'female', 'ce', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(11, 'Karan Joshi', 'p11seed04@example.com', 'WDFP11004', '9876500104', '2003-11-08', 'male', 'cse', 3, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(12, 'Mira Trivedi', 'p11seed05@example.com', 'WDFP11005', '9876500105', '2004-07-14', 'female', 'it', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(13, 'Devansh Rao', 'p11seed06@example.com', 'WDFP11006', '9876500106', '2005-09-21', 'male', 'ce', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(14, 'Isha Mehta', 'p11seed07@example.com', 'WDFP11007', '9876500107', '2003-02-19', 'female', 'cse', 3, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(15, 'Yash Patel', 'p11seed08@example.com', 'WDFP11008', '9876500108', '2004-12-05', 'male', 'it', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(16, 'Anaya Shah', 'p11seed09@example.com', 'WDFP11009', '9876500109', '2005-06-11', 'female', 'ce', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(17, 'Dhruv Parmar', 'p11seed10@example.com', 'WDFP11010', '9876500110', '2003-08-27', 'male', 'cse', 4, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(18, 'Kiara Desai', 'p11seed11@example.com', 'WDFP11011', '9876500111', '2004-04-16', 'female', 'it', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(19, 'Vivaan Shah', 'p11seed12@example.com', 'WDFP11012', '9876500112', '2005-10-02', 'male', 'ce', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(20, 'Sara Joshi', 'p11seed13@example.com', 'WDFP11013', '9876500113', '2003-07-09', 'female', 'cse', 3, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(21, 'Manav Patel', 'p11seed14@example.com', 'WDFP11014', '9876500114', '2004-02-24', 'male', 'it', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(22, 'Pari Shah', 'p11seed15@example.com', 'WDFP11015', '9876500115', '2005-12-19', 'female', 'ce', 1, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL),
(23, 'Reyansh Mehta', 'p11seed16@example.com', 'WDFP11016', '9876500116', '2003-10-30', 'male', 'cse', 2, '$2y$10$7J3oPo9LYu2Cb4t245FSMe3Qzwhil7hhbexsm5HGE7xJh/KCKso7a', 'student', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_created_at` (`created_at`),
  ADD KEY `idx_audit_user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD UNIQUE KEY `unique_event_title` (`title`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `college_student_id` (`college_student_id`),
  ADD UNIQUE KEY `college_student_id_2` (`college_student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

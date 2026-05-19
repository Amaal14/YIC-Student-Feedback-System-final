-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 03, 2026 at 05:26 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yic_feedback_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Courses', 'Course-related feedback'),
(2, 'Campus Services', 'Services feedback'),
(3, 'Facilities', 'Labs and classrooms'),
(4, 'Library', 'Library services'),
(5, 'Cafeteria', 'Food services'),
(6, 'Transportation', 'Bus issues'),
(7, 'IT Support', 'Technical problems'),
(8, 'Registration', 'Enrollment issues'),
(9, 'Security', 'Safety concerns'),
(10, 'Other', 'General feedback');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int NOT NULL,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','reviewed','resolved') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `user_id`, `category_id`, `title`, `message`, `status`, `created_at`) VALUES
(1, 1, 1, 'Course issue', 'Need more examples', 'pending', '2026-05-03 15:57:48'),
(2, 2, 3, 'Lab slow', 'Computers are slow', 'reviewed', '2026-05-03 15:57:48'),
(3, 3, 2, 'Service delay', 'Office takes time', 'pending', '2026-05-03 15:57:48'),
(4, 4, 5, 'Food quality', 'Improve cafeteria', 'resolved', '2026-05-03 15:57:48'),
(5, 5, 7, 'WiFi weak', 'Bad internet', 'pending', '2026-05-03 15:57:48'),
(6, 6, 4, 'Library hours', 'Extend time', 'reviewed', '2026-05-03 15:57:48'),
(7, 7, 6, 'Bus timing', 'Schedule bad', 'pending', '2026-05-03 15:57:48'),
(8, 8, 8, 'Registration hard', 'System confusing', 'resolved', '2026-05-03 15:57:48'),
(9, 1, 9, 'Safety issue', 'Need signs', 'reviewed', '2026-05-03 15:57:48'),
(10, 2, 10, 'Suggestion', 'Add suggestion box', 'pending', '2026-05-03 15:57:48'),
(12, 11, 5, 'Cafeteria Food Quality', 'The food quality in the cafeteria needs improvement. It is not fresh sometimes.', 'resolved', '2026-05-03 17:14:05');

-- --------------------------------------------------------

--
-- Table structure for table `responses`
--

CREATE TABLE `responses` (
  `response_id` int NOT NULL,
  `feedback_id` int NOT NULL,
  `admin_id` int NOT NULL,
  `response_text` text NOT NULL,
  `response_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `responses`
--

INSERT INTO `responses` (`response_id`, `feedback_id`, `admin_id`, `response_text`, `response_date`) VALUES
(1, 1, 9, 'We will improve materials', '2026-05-03 15:57:48'),
(2, 2, 10, 'Maintenance scheduled', '2026-05-03 15:57:48'),
(3, 3, 9, 'We will check delays', '2026-05-03 15:57:48'),
(4, 4, 10, 'Menu updated', '2026-05-03 15:57:48'),
(5, 5, 9, 'IT team notified', '2026-05-03 15:57:48'),
(6, 6, 10, 'Library hours extended', '2026-05-03 15:57:48'),
(7, 7, 9, 'Transport team informed', '2026-05-03 15:57:48'),
(8, 8, 10, 'System update coming', '2026-05-03 15:57:48'),
(9, 9, 9, 'Safety signs added', '2026-05-03 15:57:48'),
(10, 10, 10, 'Suggestion noted', '2026-05-03 15:57:48'),
(11, 12, 9, 'Thank you for your feedback. We will review the food quality with the cafeteria management.', '2026-05-03 17:16:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Amaal Ahmed', 'amaal@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(2, 'Sara Ali', 'sara@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(3, 'Mona Khalid', 'mona@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(4, 'Lama Saad', 'lama@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(5, 'Noura Hassan', 'noura@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(6, 'Huda Omar', 'huda@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(7, 'Reem Fahad', 'reem@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(8, 'Dana Saleh', 'dana@rcjy.edu.sa', '123456', 'student', '2026-05-03 15:57:48'),
(9, 'Admin One', 'admin1@rcjy.edu.sa', 'admin123', 'admin', '2026-05-03 15:57:48'),
(10, 'Admin Two', 'admin2@rcjy.edu.sa', 'admin123', 'admin', '2026-05-03 15:57:48'),
(11, 'Raghad', 'raghaaad@rcjy.edu.sa', 'Ra654321', 'student', '2026-05-03 17:09:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `responses`
--
ALTER TABLE `responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `feedback_id` (`feedback_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `responses`
--
ALTER TABLE `responses`
  MODIFY `response_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT;

--
-- Constraints for table `responses`
--
ALTER TABLE `responses`
  ADD CONSTRAINT `responses_ibfk_1` FOREIGN KEY (`feedback_id`) REFERENCES `feedback` (`feedback_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `responses_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

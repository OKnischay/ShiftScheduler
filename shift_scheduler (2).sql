-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2024 at 12:17 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shift_scheduler`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', 'admin', '2024-08-11 08:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `picture`, `date_of_birth`) VALUES
(2, 'Ram', 'Thapa', 'ram@gmail.com', '$2y$10$GeMCNh15jo.vCqrvGEaoAeZK5PkknKllJnxnwDo9HNJHWg2QEVjPO', '9876543211', 'Kathmandu', 'uploads/man1.png', '2001-12-11'),
(5, 'Hari', 'Giri', 'hari@gmail.com', '$2y$10$YHt8lEXWle9BnnaGScGaTeVt5Qe9HXXrXgeH7Px/N4PQGysTWSWsK', '9800236590', 'Bhaktapur', 'uploads/man2.png', '2002-04-04'),
(6, 'Sita', 'Shrestha', 'sita123@gmail.com', '$2y$10$Rivf2PuTqzD4bi299m26rewoJM2XfdrYCEexBWKQ5N2.LJKJKrztu', '9812345678', 'Lalitpur', 'uploads/female.png', '1997-09-15'),
(8, 'Chandler', 'Bing', 'bing@gmail.com', '$2y$10$HhEJpfCQd.GQXOKu4yB1duhV5axBKaGqViUtVpdgyyPVtaTweEWpq', '9808513666', 'California', 'uploads/man2.png', '1985-06-04'),
(10, 'Ankit', 'Shrestha', 'ankit@gmail.com', '$2y$10$dUIiIqFcjOqal8nI0k4x9elvZq5f/y2KbK5Oj0X158mI5SvBNO1FO', '9787231970', 'Teku', 'uploads/man2.png', '2003-11-05'),
(11, 'Sushila', 'Vaidya', 'sushila8@gmail.com', '$2y$10$UuL9.hZ9McmNUOYxjO5bo.MntfnmM9eUcijmYF/aUTyWUIJ5A/0De', '9813978259', 'Bhaktapur', 'uploads/female.png', '1974-07-14'),
(12, 'Gopal', 'Balami', 'gopal@gmail.com', '$2y$10$O5QA124LQUxO26CjWf0yk.t.610euDMVtVzSwHiMb.ESM57.WAc22', '9799006231', 'Pokhara', 'uploads/man1.png', '2001-08-15'),
(13, 'Rohit', 'Maharjan', 'rohit@gmail.com', '$2y$10$tX4Hbprtj.2oe9gKvKw8heT.qw/.0ZLAOxbEp9pXlFyUbtUyLwh.u', '9732142532', 'Kathmandu', 'uploads/man2.png', '2003-01-08'),
(15, 'John', 'Wick', 'john@gmail.com', '$2y$10$IY9mmcBoRAspHTV.wE8m0OvOjzHrj8.Ukfl97.fRoXhm.R.Om4lc.', '9860024662', 'Ktm', 'uploads/man1.png', '2024-08-01'),
(16, 'You', 'You', 'you@gmail.com', '$2y$10$857MdfxIiuz/gjfGk.9r8epa.mFZ0uipDkMo5WC/I9fdDtOxGofZK', '234234113', 'Bkt', 'uploads/female.png', '2022-05-16');

-- --------------------------------------------------------

--
-- Table structure for table `employee_availability`
--

CREATE TABLE `employee_availability` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_availability`
--

INSERT INTO `employee_availability` (`id`, `employee_id`, `day_of_week`) VALUES
(1, 2, 'Sunday'),
(2, 2, 'Monday'),
(3, 2, 'Tuesday'),
(4, 2, 'Wednesday'),
(5, 2, 'Thursday'),
(6, 2, 'Friday'),
(7, 5, 'Sunday'),
(8, 5, 'Monday'),
(9, 5, 'Tuesday'),
(10, 5, 'Wednesday'),
(11, 5, 'Thursday'),
(12, 10, 'Monday'),
(13, 10, 'Tuesday'),
(14, 10, 'Wednesday'),
(15, 10, 'Thursday'),
(16, 10, 'Friday'),
(17, 10, 'Saturday'),
(22, 6, 'Wednesday'),
(23, 6, 'Thursday'),
(24, 6, 'Friday'),
(25, 6, 'Saturday');

-- --------------------------------------------------------

--
-- Table structure for table `leave_request`
--

CREATE TABLE `leave_request` (
  `id` int(11) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `employee_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_request`
--

INSERT INTO `leave_request` (`id`, `start`, `end`, `reason`, `status`, `employee_id`) VALUES
(0, '2024-08-14', '2024-08-15', 'sick', 'Rejected', 11),
(0, '2024-08-16', '2024-08-18', 'Fever', 'Rejected', 10),
(0, '2024-09-21', '2024-09-24', 'sick', 'Pending', 2);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `employee_id`, `shift_id`, `date`) VALUES
(63, 6, 4, '2024-09-26');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `start_time`, `end_time`, `note`) VALUES
(4, '10:45:00', '15:30:00', 'Day Shift'),
(8, '18:00:00', '00:00:00', 'Evening Shift'),
(12, '09:00:00', '16:00:00', 'Regular Shift'),
(17, '14:00:00', '20:00:00', 'Work horse'),
(22, '06:30:00', '14:00:00', 'Morning');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee_availability`
--
ALTER TABLE `employee_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD PRIMARY KEY (`id`,`start`,`end`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee_availability`
--
ALTER TABLE `employee_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee_availability`
--
ALTER TABLE `employee_availability`
  ADD CONSTRAINT `employee_availability_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `leave_request`
--
ALTER TABLE `leave_request`
  ADD CONSTRAINT `leave_request_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

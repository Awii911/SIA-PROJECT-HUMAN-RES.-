-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 04:07 AM
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
-- Database: `auth_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth_logs`
--

CREATE TABLE `auth_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_msg` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `auth_logs`
--

INSERT INTO `auth_logs` (`id`, `user_id`, `username`, `event_type`, `event_msg`, `ip_address`, `user_agent`, `created_at`) VALUES
(45, 16, NULL, 'fail', 'Invalid username or password.', '::1', NULL, '2025-10-16 01:48:32'),
(46, 16, NULL, 'fail', 'Invalid password. 2 attempts left.', '::1', NULL, '2025-10-16 01:48:42'),
(47, 16, NULL, 'fail', 'Invalid password. 1 attempt left. Next wrong attempt locks account.', '::1', NULL, '2025-10-16 01:49:00'),
(48, 16, NULL, 'fail', 'Wrong password. Account locked for 1 minute.', '::1', NULL, '2025-10-16 01:49:11'),
(49, 16, NULL, 'fail', 'Wrong password again. Account locked for 15 minutes.', '::1', NULL, '2025-10-16 01:49:26'),
(50, 16, NULL, 'fail', 'Invalid username or password.', '::1', NULL, '2025-10-16 01:51:01'),
(51, 16, NULL, 'fail', 'Invalid username or password.', '::1', NULL, '2025-10-16 01:51:30'),
(52, 16, NULL, 'fail', 'Invalid password. 2 attempts left.', '::1', NULL, '2025-10-16 01:53:33'),
(53, 16, NULL, 'fail', 'Invalid password. 1 attempt left. Next wrong attempt locks account.', '::1', NULL, '2025-10-16 01:53:51'),
(54, 16, NULL, 'fail', 'Wrong password. Account locked for 1 minute.', '::1', NULL, '2025-10-16 01:54:02'),
(55, 16, NULL, 'fail', 'Wrong password again. Account locked for 15 minutes.', '::1', NULL, '2025-10-16 01:54:15'),
(56, 16, NULL, 'admin_action', 'Account unlocked by admin', '::1', NULL, '2025-10-16 01:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `last_failed_at` datetime DEFAULT NULL,
  `locked_until` datetime DEFAULT NULL,
  `warning_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lock_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `attempts`, `password_hash`, `role`, `failed_attempts`, `last_failed_at`, `locked_until`, `warning_count`, `created_at`, `lock_count`) VALUES
(6, 'Dionglay', '$2y$10$MPU6sYaPO.J20cDv8mbK7OHgq1puiM1p5y2K27moL2N17wuVfIqz.', 0, '', 'user', 0, NULL, NULL, 0, '2025-09-19 09:48:27', 0),
(9, 'Gladyy', '$2y$10$kMIB/2.qlUDTQC0zavpDRuDn60Ac2aZohSpM1PshPDWDLZffhDZkC', 0, '', 'user', 0, NULL, NULL, 0, '2025-09-19 11:21:34', 0),
(10, 'Awi', '$2y$10$USxjA3ItHwppcUAxxmFzieu/B0DSdoQgeNc1VS575klvX5Eh8DwXG', 0, '', 'admin', 0, NULL, NULL, 0, '2025-09-19 12:05:36', 0),
(16, 'Perras', '$2y$10$FkRa6NzmOMhF4AwJPanKqOQO7Q2RA.iNcSr8Fq1PFNY27S6sXQ1na', 0, '', 'user', 0, NULL, NULL, 0, '2025-10-16 01:47:43', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth_logs`
--
ALTER TABLE `auth_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth_logs`
--
ALTER TABLE `auth_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

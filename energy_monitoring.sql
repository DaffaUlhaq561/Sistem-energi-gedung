-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2026 at 04:30 PM
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
-- Database: `energy_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_messages`
--

CREATE TABLE `ai_chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `role` enum('user','assistant') NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_chat_messages`
--

INSERT INTO `ai_chat_messages` (`id`, `session_id`, `role`, `content`, `created_at`) VALUES
(1, 2, 'user', 'halo', '2026-01-03 14:18:46'),
(2, 3, 'user', 'halo', '2026-01-03 14:29:23'),
(3, 3, 'assistant', 'Halo! Ada yang bisa saya bantu terkait monitoring energi gedung?', '2026-01-03 14:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_sessions`
--

CREATE TABLE `ai_chat_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Chat',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_chat_sessions`
--

INSERT INTO `ai_chat_sessions` (`id`, `user_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'Test Chat', '2026-01-03 14:07:51', '2026-01-03 14:07:51'),
(2, 1, 'Chat 03 Jan 15:18', '2026-01-03 14:18:40', '2026-01-03 14:18:40'),
(3, 1, 'Chat 03 Jan 15:29', '2026-01-03 14:29:18', '2026-01-03 14:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `energy_readings`
--

CREATE TABLE `energy_readings` (
  `id` bigint(20) NOT NULL,
  `reading_time` datetime NOT NULL,
  `energy_kwh` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `energy_readings`
--

INSERT INTO `energy_readings` (`id`, `reading_time`, `energy_kwh`) VALUES
(1, '2025-11-17 08:00:00', 42.50),
(2, '2025-11-17 10:00:00', 58.30),
(3, '2025-11-17 12:00:00', 75.20),
(4, '2025-11-17 14:00:00', 82.10),
(5, '2025-11-17 16:00:00', 68.50),
(6, '2025-11-17 18:00:00', 45.20),
(7, '2025-11-17 20:00:00', 28.10),
(8, '2025-11-18 08:00:00', 44.20),
(9, '2025-11-18 10:00:00', 61.10),
(10, '2025-11-18 12:00:00', 78.90),
(11, '2025-11-18 14:00:00', 85.30),
(12, '2025-11-18 16:00:00', 71.20),
(13, '2025-11-18 18:00:00', 47.80),
(14, '2025-11-18 20:00:00', 30.50),
(15, '2025-11-19 08:00:00', 46.10),
(16, '2025-11-19 10:00:00', 63.50),
(17, '2025-11-19 12:00:00', 81.20),
(18, '2025-11-19 14:00:00', 88.70),
(19, '2025-11-19 16:00:00', 73.90),
(20, '2025-11-19 18:00:00', 49.30),
(21, '2025-11-19 20:00:00', 32.10),
(22, '2025-11-20 08:00:00', 43.80),
(23, '2025-11-20 10:00:00', 59.90),
(24, '2025-11-20 12:00:00', 76.50),
(25, '2025-11-20 14:00:00', 83.20),
(26, '2025-11-20 16:00:00', 69.80),
(27, '2025-11-20 18:00:00', 46.50),
(28, '2025-11-20 20:00:00', 29.30),
(29, '2025-11-21 08:00:00', 45.50),
(30, '2025-11-21 10:00:00', 62.30),
(31, '2025-11-21 12:00:00', 79.80),
(32, '2025-11-21 14:00:00', 87.10),
(33, '2025-11-21 16:00:00', 72.50),
(34, '2025-11-21 18:00:00', 48.90),
(35, '2025-11-21 20:00:00', 31.70),
(36, '2025-11-22 08:00:00', 47.20),
(37, '2025-11-22 10:00:00', 64.70),
(38, '2025-11-22 12:00:00', 82.90),
(39, '2025-11-22 14:00:00', 90.30),
(40, '2025-11-22 16:00:00', 75.10),
(41, '2025-11-22 18:00:00', 50.50),
(42, '2025-11-22 20:00:00', 33.20),
(43, '2025-11-23 08:00:00', 41.90),
(44, '2025-11-23 10:00:00', 57.50),
(45, '2025-11-23 12:00:00', 74.20),
(46, '2025-11-23 14:00:00', 81.50),
(47, '2025-11-23 16:00:00', 68.20),
(48, '2025-11-23 18:00:00', 44.80),
(49, '2025-11-23 20:00:00', 27.60),
(50, '2025-11-24 08:00:00', 48.30),
(51, '2025-11-24 10:00:00', 65.90),
(52, '2025-11-24 12:00:00', 84.10),
(53, '2025-11-24 14:00:00', 91.80),
(54, '2025-11-24 16:00:00', 76.50),
(55, '2025-11-24 18:00:00', 51.90),
(56, '2025-11-24 20:00:00', 34.50),
(57, '2025-11-25 08:00:00', 44.60),
(58, '2025-11-25 10:00:00', 60.70),
(59, '2025-11-25 12:00:00', 77.80),
(60, '2025-11-25 14:00:00', 85.90),
(61, '2025-11-25 16:00:00', 71.30),
(62, '2025-11-25 18:00:00', 47.10),
(63, '2025-11-25 20:00:00', 30.20),
(64, '2025-11-26 08:00:00', 46.80),
(65, '2025-11-26 10:00:00', 63.20),
(66, '2025-11-26 12:00:00', 80.50),
(67, '2025-11-26 14:00:00', 88.10),
(68, '2025-11-26 16:00:00', 73.70),
(69, '2025-11-26 18:00:00', 49.60),
(70, '2025-11-26 20:00:00', 32.80),
(71, '2025-11-27 08:00:00', 49.10),
(72, '2025-11-27 10:00:00', 67.30),
(73, '2025-11-27 12:00:00', 85.90),
(74, '2025-11-27 14:00:00', 93.50),
(75, '2025-11-27 16:00:00', 78.20),
(76, '2025-11-27 18:00:00', 53.10),
(77, '2025-11-27 20:00:00', 35.70),
(78, '2025-11-28 08:00:00', 43.40),
(79, '2025-11-28 10:00:00', 58.90),
(80, '2025-11-28 12:00:00', 75.60),
(81, '2025-11-28 14:00:00', 82.90),
(82, '2025-11-28 16:00:00', 69.40),
(83, '2025-11-28 18:00:00', 45.70),
(84, '2025-11-28 20:00:00', 28.90),
(85, '2025-11-29 08:00:00', 45.90),
(86, '2025-11-29 10:00:00', 61.80),
(87, '2025-11-29 12:00:00', 79.30),
(88, '2025-11-29 14:00:00', 86.70),
(89, '2025-11-29 16:00:00', 72.10),
(90, '2025-11-29 18:00:00', 48.40),
(91, '2025-11-29 20:00:00', 31.50),
(92, '2025-11-30 08:00:00', 47.60),
(93, '2025-11-30 10:00:00', 64.20),
(94, '2025-11-30 12:00:00', 81.70),
(95, '2025-11-30 14:00:00', 89.30),
(96, '2025-11-30 16:00:00', 74.80),
(97, '2025-11-30 18:00:00', 50.20),
(98, '2025-11-30 20:00:00', 33.10),
(99, '2025-12-01 08:00:00', 42.30),
(100, '2025-12-01 10:00:00', 57.10),
(101, '2025-12-01 12:00:00', 73.90),
(102, '2025-12-01 14:00:00', 80.40),
(103, '2025-12-01 16:00:00', 67.50),
(104, '2025-12-01 18:00:00', 44.10),
(105, '2025-12-01 20:00:00', 27.20),
(106, '2025-12-02 08:00:00', 48.70),
(107, '2025-12-02 10:00:00', 66.10),
(108, '2025-12-02 12:00:00', 84.50),
(109, '2025-12-02 14:00:00', 92.10),
(110, '2025-12-02 16:00:00', 76.90),
(111, '2025-12-02 18:00:00', 52.30),
(112, '2025-12-02 20:00:00', 34.80),
(113, '2025-12-03 08:00:00', 44.10),
(114, '2025-12-03 10:00:00', 59.60),
(115, '2025-12-03 12:00:00', 76.80),
(116, '2025-12-03 14:00:00', 84.20),
(117, '2025-12-03 16:00:00', 70.10),
(118, '2025-12-03 18:00:00', 46.80),
(119, '2025-12-03 20:00:00', 29.70),
(120, '2025-12-04 08:00:00', 46.50),
(121, '2025-12-04 10:00:00', 62.90),
(122, '2025-12-04 12:00:00', 80.10),
(123, '2025-12-04 14:00:00', 87.60),
(124, '2025-12-04 16:00:00', 72.80),
(125, '2025-12-04 18:00:00', 48.90),
(126, '2025-12-04 20:00:00', 31.40),
(127, '2025-12-05 08:00:00', 49.20),
(128, '2025-12-05 10:00:00', 67.80),
(129, '2025-12-05 12:00:00', 86.30),
(130, '2025-12-05 14:00:00', 94.20),
(131, '2025-12-05 16:00:00', 78.50),
(132, '2025-12-05 18:00:00', 53.70),
(133, '2025-12-05 20:00:00', 36.10),
(134, '2025-12-06 08:00:00', 41.80),
(135, '2025-12-06 10:00:00', 56.90),
(136, '2025-12-06 12:00:00', 73.40),
(137, '2025-12-06 14:00:00', 80.70),
(138, '2025-12-06 16:00:00', 67.20),
(139, '2025-12-06 18:00:00', 44.30),
(140, '2025-12-06 20:00:00', 27.10),
(141, '2025-12-07 08:00:00', 47.40),
(142, '2025-12-07 10:00:00', 63.60),
(143, '2025-12-07 12:00:00', 81.20),
(144, '2025-12-07 14:00:00', 88.90),
(145, '2025-12-07 16:00:00', 73.40),
(146, '2025-12-07 18:00:00', 49.10),
(147, '2025-12-07 20:00:00', 32.20),
(148, '2025-12-08 08:00:00', 45.30),
(149, '2025-12-08 10:00:00', 61.20),
(150, '2025-12-08 12:00:00', 78.50),
(151, '2025-12-08 14:00:00', 85.70),
(152, '2025-12-08 16:00:00', 70.90),
(153, '2025-12-08 18:00:00', 47.20),
(154, '2025-12-08 20:00:00', 30.10),
(155, '2025-12-09 08:00:00', 48.90),
(156, '2025-12-09 10:00:00', 65.40),
(157, '2025-12-09 12:00:00', 83.80),
(158, '2025-12-09 14:00:00', 91.30),
(159, '2025-12-09 16:00:00', 75.60),
(160, '2025-12-09 18:00:00', 51.40),
(161, '2025-12-09 20:00:00', 34.20),
(162, '2025-12-10 08:00:00', 43.60),
(163, '2025-12-10 10:00:00', 58.40),
(164, '2025-12-10 12:00:00', 75.20),
(165, '2025-12-10 14:00:00', 82.50),
(166, '2025-12-10 16:00:00', 68.70),
(167, '2025-12-10 18:00:00', 45.30),
(168, '2025-12-10 20:00:00', 28.60),
(169, '2025-12-11 08:00:00', 46.20),
(170, '2025-12-11 10:00:00', 62.70),
(171, '2025-12-11 12:00:00', 79.90),
(172, '2025-12-11 14:00:00', 87.10),
(173, '2025-12-11 16:00:00', 72.30),
(174, '2025-12-11 18:00:00', 48.60),
(175, '2025-12-11 20:00:00', 31.80),
(176, '2025-12-12 08:00:00', 49.50),
(177, '2025-12-12 10:00:00', 68.10),
(178, '2025-12-12 12:00:00', 87.20),
(179, '2025-12-12 14:00:00', 95.60),
(180, '2025-12-12 16:00:00', 79.80),
(181, '2025-12-12 18:00:00', 54.10),
(182, '2025-12-12 20:00:00', 36.50),
(183, '2025-12-13 08:00:00', 42.10),
(184, '2025-12-13 10:00:00', 56.80),
(185, '2025-12-13 12:00:00', 73.60),
(186, '2025-12-13 14:00:00', 80.90),
(187, '2025-12-13 16:00:00', 67.40),
(188, '2025-12-13 18:00:00', 44.20),
(189, '2025-12-13 20:00:00', 27.50),
(190, '2025-12-14 08:00:00', 47.80),
(191, '2025-12-14 10:00:00', 64.50),
(192, '2025-12-14 12:00:00', 82.10),
(193, '2025-12-14 14:00:00', 89.80),
(194, '2025-12-14 16:00:00', 74.50),
(195, '2025-12-14 18:00:00', 50.30),
(196, '2025-12-14 20:00:00', 33.60),
(197, '2025-12-15 08:00:00', 44.90),
(198, '2025-12-15 10:00:00', 60.30),
(199, '2025-12-15 12:00:00', 77.50),
(200, '2025-12-15 14:00:00', 84.60),
(201, '2025-12-15 16:00:00', 70.50),
(202, '2025-12-15 18:00:00', 46.90),
(203, '2025-12-15 20:00:00', 29.80),
(204, '2025-12-16 08:00:00', 46.70),
(205, '2025-12-16 10:00:00', 63.10),
(206, '2025-12-16 12:00:00', 80.40),
(207, '2025-12-16 14:00:00', 87.90),
(208, '2025-12-16 16:00:00', 72.60),
(209, '2025-12-16 18:00:00', 48.80),
(210, '2025-12-16 20:00:00', 31.90),
(211, '2025-12-17 08:00:00', 45.10),
(212, '2025-12-17 10:00:00', 61.50),
(213, '2025-12-17 12:00:00', 78.80),
(214, '2025-12-17 14:00:00', 86.30),
(215, '2025-12-17 16:00:00', 71.40),
(216, '2025-12-17 18:00:00', 47.50),
(217, '2025-12-17 20:00:00', 30.60);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_notes`
--

CREATE TABLE `maintenance_notes` (
  `id` int(11) NOT NULL,
  `note` text NOT NULL,
  `status` enum('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_notes`
--

INSERT INTO `maintenance_notes` (`id`, `note`, `status`, `created_at`) VALUES
(1, 'Pengecekan beban panel utama lantai 1', 'in_progress', '2026-01-03 11:51:33'),
(2, 'Pembersihan ruang trafo', 'completed', '2026-01-03 11:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `settings_ai`
--

CREATE TABLE `settings_ai` (
  `id` int(11) NOT NULL DEFAULT 1,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `api_key_encrypted` text DEFAULT NULL,
  `model` varchar(100) NOT NULL DEFAULT 'gpt-4o-mini',
  `temperature` decimal(3,2) NOT NULL DEFAULT 0.20,
  `anomaly_threshold_pct` decimal(5,2) NOT NULL DEFAULT 30.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings_ai`
--

INSERT INTO `settings_ai` (`id`, `enabled`, `api_key_encrypted`, `model`, `temperature`, `anomaly_threshold_pct`, `updated_at`) VALUES
(1, 1, 'OSSLGCM:gGCiKQWezVy8n6UprFHx9vV72b7nswkZYOV9dX9+JtQMw/iv/zTb0BWj0UUTFJAfTBIOJAGKcRahvwV+L73hKlpgNl/2vyX6TaSp6PVPi/pTasM8Zrib4veosOBFkC5udmFcMHDSYIWr3UOU0HsKrOyBH4wAoyGHkJvfi5NmejgjdJgNJstdY/4qaVBPbXGclRhVbBqc/UoPGwtyj5w1SH4rXj85bBJz3eNzFWoPDH2PtG8q428Z2tSBDW3ccyO2', 'gpt-4o-mini', 0.20, 30.00, '2026-01-03 13:27:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `name`) VALUES
(1, 'admin', '$2b$10$9aiOKZm80zHBLhDKJRKHsedBtw0jetJiLK7bgRT4NhShjqCU49l.u', 'Admin Operasional');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`);

--
-- Indexes for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `energy_readings`
--
ALTER TABLE `energy_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reading_time` (`reading_time`);

--
-- Indexes for table `maintenance_notes`
--
ALTER TABLE `maintenance_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings_ai`
--
ALTER TABLE `settings_ai`
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
-- AUTO_INCREMENT for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `energy_readings`
--
ALTER TABLE `energy_readings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `maintenance_notes`
--
ALTER TABLE `maintenance_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_messages`
--
ALTER TABLE `ai_chat_messages`
  ADD CONSTRAINT `fk_ai_chat_session` FOREIGN KEY (`session_id`) REFERENCES `ai_chat_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD CONSTRAINT `fk_ai_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

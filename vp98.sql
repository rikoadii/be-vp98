-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 23, 2025 at 05:49 AM
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
-- Database: `vp98`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id_categories` int(11) NOT NULL,
  `categories_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id_categories`, `categories_name`) VALUES
(1, 'Corporate'),
(2, 'Entertainment'),
(3, 'Community');

-- --------------------------------------------------------

--
-- Table structure for table `center_image`
--

CREATE TABLE `center_image` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `center_image`
--

INSERT INTO `center_image` (`id`, `image`) VALUES
(1, 'center_1755765327.png');

-- --------------------------------------------------------

--
-- Table structure for table `child_project`
--

CREATE TABLE `child_project` (
  `id` int(11) NOT NULL,
  `id_parent_project` int(11) NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `child_project`
--

INSERT INTO `child_project` (`id`, `id_parent_project`, `image`) VALUES
(1, 1, 'child_1755766147.png'),
(2, 9, 'child_1755767551.png'),
(3, 7, 'child_1755785248.png'),
(4, 7, 'child_1755785266.png'),
(5, 9, 'child_1755785279.png'),
(6, 6, 'child_1755785296.png'),
(7, 1, 'child_1755785313.png'),
(8, 3, 'child_1755785345.png'),
(9, 6, 'child_1755785362.png');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `contact` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `contact`) VALUES
(1, '6287865800582');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `name_projects` varchar(255) NOT NULL,
  `location_projects` text DEFAULT NULL,
  `description_projects` text DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `image_project` varchar(255) DEFAULT NULL,
  `id_categories` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `name_projects`, `location_projects`, `description_projects`, `is_main`, `image_project`, `id_categories`) VALUES
(1, 'PERESMIAN2', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 1, 'Project1.webp', 1),
(2, 'PERESMIAN3', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 0, 'Project2.webp', 2),
(3, 'PERESMIAN9', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 0, 'Project3.webp', 2),
(4, 'PERESMIAN8', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 0, 'Project4.webp', 2),
(5, 'PERESMIAN7', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 0, 'Project5.webp', 3),
(6, 'PERESMIAN6', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 1, 'Project6.webp', 2),
(7, 'PERESMIAN5', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 1, 'Project7.webp', 3),
(8, 'PERESMIAN7', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 0, 'Project9.webp', 2),
(9, 'PERESMIAN4', 'GEDUNG BARU UT, JALAN LINGKAR MATARAM', 'EVENT KONSEP, EVENT PRODUCTION, 3D EVENT, SHOW MANAGEMENT, PRE EVENT MANAGEMENT PARTNER, VIP & TALENT MANAGEMENT', 1, 'Project10.webp', 2),
(10, 'test', 'test', 'test', 0, '68a454bf2e681.webp', 3),
(11, 'test', 'test', 'description', 0, '68a45592b24f5.webp', 2),
(12, 'test44', 'test44', 'description98', 0, '68a455afa75bf.webp', 3),
(13, 'test55', 'test5555', 'description', 0, '68a455dc00f79.webp', 3);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `profile`, `role`) VALUES
(1, 'VICTOR - ITHONK', 'Ithonk.webp', 'DIRECTUR - BOD'),
(2, 'ALGAMDI', 'Algam.webp', 'EVENT MANAGER'),
(3, 'SATRIA PERDANA', 'Satria.webp', 'MANAGER PRODUCTION'),
(4, 'JAMES BILLY', 'James.webp', 'VIDEOGRAPHER'),
(5, 'EVELYN - PUPUT', 'evely-puput.webp', 'TALENT MANAGEMENT'),
(6, 'NILA PASILA', 'Nila.webp', 'FINANCE');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `profile`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$DhA9qV6TTxa19QQyMlht4O8R0LSB6v.1wADo8.YUU/1ZhRDlJvw5y', '68a43c1b33bbc.webp', '2025-08-19 08:43:42', '2025-08-19 08:57:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_categories`);

--
-- Indexes for table `center_image`
--
ALTER TABLE `center_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `child_project`
--
ALTER TABLE `child_project`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parent_project` (`id_parent_project`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `idx_id_categories` (`id_categories`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_categories` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `center_image`
--
ALTER TABLE `center_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `child_project`
--
ALTER TABLE `child_project`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `child_project`
--
ALTER TABLE `child_project`
  ADD CONSTRAINT `child_project_ibfk_1` FOREIGN KEY (`id_parent_project`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_categories` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id_categories`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

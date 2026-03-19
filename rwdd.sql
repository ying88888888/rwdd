-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 19, 2026 at 08:38 AM
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
-- Database: `events`
--
CREATE DATABASE IF NOT EXISTS `events` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `events`;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `event_location` varchar(255) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sustainability_goals` text DEFAULT NULL,
  `event_image` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `update_notice` varchar(255) DEFAULT NULL,
  `show_update_notice` tinyint(1) DEFAULT 0,
  `waste_report` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_name`, `event_date`, `event_time`, `event_location`, `event_type`, `max_participants`, `description`, `sustainability_goals`, `event_image`, `status`, `created_at`, `last_updated_at`, `update_notice`, `show_update_notice`, `waste_report`) VALUES
(1, 'hi', '2000-01-01', '12:00:00', 'test', '123', 10, 'test', 'test', '', 'Approved', '2026-03-19 04:06:00', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pointsdistribution`
--

CREATE TABLE `pointsdistribution` (
  `id` int(11) NOT NULL,
  `points_activity` varchar(255) NOT NULL,
  `points_description` varchar(255) NOT NULL,
  `points_points` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pointsdistribution`
--

INSERT INTO `pointsdistribution` (`id`, `points_activity`, `points_description`, `points_points`) VALUES
(1, 'Event Completion', 'Attend an event', 10),
(2, 'Feedback Submission', 'Providing event feedback', 5);

-- --------------------------------------------------------

--
-- Table structure for table `pointshistory`
--

CREATE TABLE `pointshistory` (
  `pointsHistory_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pointsHistory_activity` varchar(255) NOT NULL,
  `pointsHistory_points` int(11) NOT NULL,
  `pointsHistory_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reward`
--

CREATE TABLE `reward` (
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(255) NOT NULL,
  `reward_points` int(11) NOT NULL,
  `reward_quantity` int(11) NOT NULL,
  `reward_image` varchar(255) NOT NULL,
  `reward_status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward`
--

INSERT INTO `reward` (`reward_id`, `reward_name`, `reward_points`, `reward_quantity`, `reward_image`, `reward_status`) VALUES
(7, 'Recycle Bag', 200, 100, 'recycle bag.jpeg', 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `user_fullname` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_username` varchar(255) NOT NULL,
  `user_phoneNumber` varchar(50) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_profilePicture` varchar(255) DEFAULT NULL,
  `user_role` varchar(20) NOT NULL,
  `user_organization` varchar(255) DEFAULT NULL,
  `user_reason` varchar(255) DEFAULT NULL,
  `user_document` varchar(255) DEFAULT NULL,
  `user_status` varchar(255) NOT NULL,
  `user_registerDate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_fullname`, `user_email`, `user_username`, `user_phoneNumber`, `user_password`, `user_profilePicture`, `user_role`, `user_organization`, `user_reason`, `user_document`, `user_status`, `user_registerDate`) VALUES
(3, 'Chai Shi Xuen', 'jessicacsx0118@gmail.com', 'sxuen', '0176869989', '$2y$10$QY56jtnNuuwqV2hrf0mnce0n2JonKGNvtRAn5Sr1jtvFEPRf.8nrm', 'defaultProfile.img', 'Admin', '', '', '', 'Active', '2026-03-19 13:45:27'),
(6, 'Yee Tze Ying', 'yingtyee@gmail.com', 'ying', '0123456789', '$2y$10$fU0cw.37hmXklhONf7WAWOQCynnbgxbMuynTIScJNp.P/eOehAb3O', 'defaultProfile.png', 'Event Organizer', 'ABC Organization', 'Want to attract more customer', 'Lab 1 - Introduction to HTML (Part 1) (1).pdf', 'Active', '2026-03-19 14:10:32'),
(7, 'Wong Jun Xi', 'izwjx21@gmail.com', 'junxii', '0123456789', '$2y$10$CGNO2s32udw.5BpP8jtGfOrICCmS1FKNhx6GHEYShyTsTB3L5HZAC', 'defaultProfile.png', 'Event Organizer', 'WJX Organization', 'Want to attract more customerssss', 'Lab 2 - Introduction to HTML (Part 2).pdf', 'Rejected', '2026-03-19 14:11:32'),
(8, 'wdwxd', 'dwd@gmail.com', 'eded', '012073927387', '$2y$10$P.wBw3eKuyQp03L/sknhMuxBxbaSJu1P7zViHJvPOy2nBrPWDIdoW', 'defaultProfile.png', 'Event Organizer', 'sdn Organization', 'djfncj', 'RWDD Assignment.pdf', 'Active', '2026-03-19 14:12:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pointsdistribution`
--
ALTER TABLE `pointsdistribution`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reward`
--
ALTER TABLE `reward`
  ADD PRIMARY KEY (`reward_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pointsdistribution`
--
ALTER TABLE `pointsdistribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reward`
--
ALTER TABLE `reward`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

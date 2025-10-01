-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 02:47 PM
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
-- Database: `votesystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_on`) VALUES
(4, 'Admin', '$2y$10$9kPOMU3iab67Co5bggZtZeUWKdrQyx7EmXj63lk60lDSccx4Kn0fa', '2025-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `program` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `position_id`, `lastname`, `firstname`, `program`) VALUES
(23, 8, 'Soriano', 'Rica Wena Jane', 'BSIS'),
(24, 8, 'Estrera', 'Krista', 'BSIS'),
(25, 8, 'Catamora', 'Elaiza Jossel', 'BSIS'),
(26, 9, 'Almacin', 'Ressevel', 'BSIS'),
(27, 9, 'Duero', 'Justine', 'BSIS'),
(28, 11, 'Delos Reyes', 'Mikha', 'BSE'),
(29, 11, 'Cruz', 'Angela', 'BTVTED'),
(30, 11, 'Santos', 'Mark Anthony', 'BTVTED'),
(31, 11, 'Gomez', 'Katrina', 'BSE'),
(32, 12, 'Navarro', 'Carlo', 'BSIS'),
(33, 12, 'Villanueva', 'Erika', 'BSE'),
(34, 12, 'Morales', 'Kevin', 'BTVTED'),
(37, 13, 'Santos', 'Marianne', 'BSE'),
(38, 13, 'Alvarez', 'Nicole', 'BSIS'),
(39, 13, 'Hernandez', 'Paolo', 'BTVTED'),
(40, 14, 'Orquia', 'Rami', 'BTVTED'),
(41, 14, 'Halbay', 'Russel', 'BSE'),
(42, 14, 'Ly', 'Clarice', 'BSIS');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `description`, `priority`) VALUES
(8, 'President', 1),
(9, 'Vice President', 2),
(11, 'Secretary', 4),
(12, 'Treasurer', 5),
(13, 'PIO', 6),
(14, 'Auditor', 7);

-- --------------------------------------------------------

--
-- Table structure for table `saved_votes`
--

CREATE TABLE `saved_votes` (
  `id` int(11) NOT NULL,
  `election_title` varchar(255) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `total_votes` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` varchar(12) NOT NULL,
  `firstName` varchar(30) NOT NULL,
  `lastName` varchar(30) NOT NULL,
  `middleName` varchar(30) NOT NULL,
  `program` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `firstName`, `lastName`, `middleName`, `program`) VALUES
('2019-01302', 'Justine', 'Duero', 'Orica', 'BSIS'),
('2022-02811', 'Krista', 'Estrera', 'Cartagena', 'BSIS'),
('2022-02900', 'Elaiza Jossel', 'Catamora', 'Dela Criuz', 'BSIS'),
('2022-03142', 'Marxis', 'Bautista', 'Estrera', 'BSIS'),
('2022-03228', 'Rica Wena Jane', 'Soriano', 'Gatpo', 'BSIS'),
('2022-03282', 'Ressevel', 'Almacin', 'Bornea', 'BSIS');

-- --------------------------------------------------------

--
-- Table structure for table `studlog`
--

CREATE TABLE `studlog` (
  `studentID` varchar(10) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `middleName` varchar(50) NOT NULL,
  `program` varchar(50) NOT NULL,
  `vote_status` enum('voted','not voted') DEFAULT 'not voted'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `studlog`
--

INSERT INTO `studlog` (`studentID`, `lastName`, `firstName`, `middleName`, `program`, `vote_status`) VALUES
('2019-01302', 'Duero', 'Justine', 'Orica', 'BSIS', 'not voted'),
('2022-02811', 'Estrera', 'Krista', 'Cartagena', 'BSIS', 'voted'),
('2022-02900', 'Catamora', 'Elaiza Jossel', 'Dela Criuz', 'BSIS', 'not voted'),
('2022-03228', 'Soriano', 'Rica Wena Jane', 'Gatpo', 'BSIS', 'voted'),
('2022-03282', 'Almacin', 'Ressevel', 'Bornea', 'BSIS', 'not voted');

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `voted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`id`, `student_id`, `candidate_id`, `position_id`, `voted_at`) VALUES
(233, '2022-02811', 24, 8, '2025-08-14 21:18:03'),
(234, '2022-02811', 27, 9, '2025-08-14 21:18:03'),
(235, '2022-02811', 30, 11, '2025-08-14 21:18:03'),
(236, '2022-02811', 33, 12, '2025-08-14 21:18:03'),
(237, '2022-02811', 38, 13, '2025-08-14 21:18:03'),
(238, '2022-02811', 41, 14, '2025-08-14 21:18:03'),
(239, '2022-03228', 25, 8, '2025-08-14 21:18:19'),
(240, '2022-03228', 26, 9, '2025-08-14 21:18:19'),
(241, '2022-03228', 31, 11, '2025-08-14 21:18:19'),
(242, '2022-03228', 34, 12, '2025-08-14 21:18:19'),
(243, '2022-03228', 39, 13, '2025-08-14 21:18:19'),
(244, '2022-03228', 40, 14, '2025-08-14 21:18:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_votes`
--
ALTER TABLE `saved_votes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`);

--
-- Indexes for table `studlog`
--
ALTER TABLE `studlog`
  ADD PRIMARY KEY (`studentID`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `saved_votes`
--
ALTER TABLE `saved_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

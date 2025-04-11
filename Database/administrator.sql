-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 03:28 PM
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
-- Database: `administrator`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_full_name` varchar(255) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('Online','In-clinic') NOT NULL,
  `department` enum('Pharmacy','Doctor','Laboratory') NOT NULL,
  `reason` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `patient_full_name`, `appointment_date`, `appointment_time`, `booking_date`, `type`, `department`, `reason`) VALUES
(4, 12345, 'Kingsley ugonna Aguagwa', '2024-08-25', '14:30:00', '2024-08-01 17:26:47', 'Online', 'Doctor', 'follow-up'),
(5, 0, 'Kingsley ugonna Aguagwa', '2024-07-08', '11:30:00', '2025-01-02 01:29:13', 'Online', 'Pharmacy', 'follow-up'),
(6, 12345, 'Kingsley ugonna Aguagwa', '2024-09-11', '12:00:00', '2024-09-08 09:44:21', 'Online', 'Doctor', 'medical condition'),
(7, 8994251, 'Onyinye  Stephanie  Aguagwa', '2024-09-11', '11:00:00', '2024-09-08 17:54:41', 'Online', 'Pharmacy', 'drug'),
(8, 12345, 'Kingsley ugonna Aguagwa', '2024-09-12', '14:30:00', '2024-09-10 22:17:44', 'Online', 'Pharmacy', 'unknown'),
(9, 0, '', '2024-09-12', '10:00:00', '2024-09-11 09:04:33', 'Online', 'Pharmacy', 'unknown'),
(10, 0, '', '2024-09-11', '12:00:00', '2024-09-11 09:05:02', 'Online', 'Pharmacy', 'unknown'),
(11, 0, '', '2024-09-11', '12:00:00', '2024-09-11 09:30:25', 'Online', 'Pharmacy', 'yes'),
(12, 0, '', '2024-09-11', '12:00:00', '2024-09-11 09:34:38', 'Online', 'Doctor', 'yes'),
(13, 0, '', '2024-09-11', '12:00:00', '2024-09-11 09:34:46', 'Online', 'Doctor', 'yes'),
(14, 0, '', '2024-09-11', '12:00:00', '2024-09-11 09:47:55', 'Online', 'Pharmacy', 'ye'),
(15, 12345, 'Kingsley ugonna Aguagwa', '2024-09-11', '12:00:00', '2024-09-11 09:49:48', 'Online', 'Pharmacy', 'yes'),
(16, 12345, 'Kingsley ugonna Aguagwa', '2024-09-19', '09:00:00', '2024-09-11 09:58:18', 'Online', 'Pharmacy', 'sucessful'),
(17, 12345, 'Kingsley ugonna Aguagwa', '2024-09-19', '09:00:00', '2024-09-11 09:59:25', 'Online', 'Pharmacy', 'sucessful'),
(18, 12345, 'Kingsley ugonna Aguagwa', '2024-09-30', '14:30:00', '2024-09-29 16:00:02', 'In-clinic', 'Pharmacy', 'medical checkup'),
(19, 12345, 'Kingsley ugonna Aguagwa', '2024-10-03', '12:00:00', '2024-10-01 12:01:42', 'Online', 'Pharmacy', 'for drug collection'),
(20, 12345, 'Kingsley ugonna Aguagwa', '2024-10-03', '12:00:00', '2024-10-01 12:03:10', 'Online', 'Pharmacy', 'ok'),
(21, 12345, 'Kingsley ugonna Aguagwa', '2024-10-03', '12:00:00', '2024-10-01 12:24:15', 'Online', 'Pharmacy', 'unknown'),
(22, 8994251, 'fabulous  grant', '2024-12-28', '23:33:00', '2024-12-25 09:03:13', 'Online', 'Doctor', 'to see doctor'),
(23, 12345, 'Kingsley ugonna Aguagwa', '2024-12-28', '07:45:00', '2024-12-28 08:30:57', 'Online', 'Doctor', 'to see a physican '),
(24, 12345, 'Kingsley ugonna Aguagwa', '2024-12-29', '00:00:00', '2024-12-28 09:09:58', 'Online', 'Doctor', 'ubnkowmn'),
(25, 12345, 'Kingsley ugonna Aguagwa', '2024-12-29', '00:00:00', '2024-12-28 09:13:57', 'Online', '', 'gg'),
(26, 12345, 'Wiilliam  Stone', '2025-01-04', '00:00:00', '2025-01-03 05:19:35', 'Online', 'Doctor', 'to see the doctor '),
(27, 25348, 'Kingsley ugonna Aguagwa', '2025-01-03', '17:30:00', '2025-01-03 17:10:23', 'Online', 'Doctor', 'stomach ache'),
(28, 25348, 'Kingsley ugonna Aguagwa', '2025-01-03', '19:00:00', '2025-01-03 17:12:26', 'Online', 'Doctor', 'leg pain'),
(29, 25348, 'Kingsley ugonna Aguagwa', '2025-01-07', '19:45:00', '2025-01-07 20:58:14', 'Online', 'Doctor', 'head pain ');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_availability`
--

CREATE TABLE `appointment_availability` (
  `id` int(11) NOT NULL,
  `department` varchar(50) NOT NULL,
  `appointment_type` enum('Online','In-clinic') NOT NULL,
  `availability_date` date DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 15,
  `time_slot` time NOT NULL,
  `slot_number` int(11) NOT NULL DEFAULT 1,
  `is_unavailable` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointment_availability`
--

INSERT INTO `appointment_availability` (`id`, `department`, `appointment_type`, `availability_date`, `time_interval`, `time_slot`, `slot_number`, `is_unavailable`) VALUES
(1, 'Doctor', 'Online', NULL, 45, '07:00:00', 1, 0),
(2, 'Doctor', 'Online', NULL, 45, '07:15:00', 1, 1),
(3, 'Doctor', 'Online', NULL, 45, '07:30:00', 1, 1),
(4, 'Doctor', 'Online', NULL, 45, '07:45:00', 1, 0),
(5, 'Doctor', 'Online', NULL, 45, '08:00:00', 1, 0),
(6, 'Doctor', 'Online', NULL, 45, '08:15:00', 1, 0),
(7, 'Doctor', 'Online', NULL, 45, '08:30:00', 1, 0),
(8, 'Doctor', 'Online', NULL, 45, '08:45:00', 1, 0),
(9, 'Doctor', 'Online', NULL, 45, '09:00:00', 1, 0),
(10, 'Doctor', 'Online', NULL, 45, '09:15:00', 1, 0),
(11, 'Doctor', 'Online', NULL, 45, '09:30:00', 1, 0),
(12, 'Doctor', 'Online', NULL, 45, '09:45:00', 1, 0),
(13, 'Doctor', 'Online', NULL, 45, '10:00:00', 1, 1),
(14, 'Doctor', 'Online', NULL, 45, '10:15:00', 1, 0),
(15, 'Doctor', 'Online', NULL, 45, '10:30:00', 1, 0),
(16, 'Doctor', 'Online', NULL, 45, '10:45:00', 1, 0),
(17, 'Doctor', 'Online', NULL, 45, '11:00:00', 1, 0),
(18, 'Doctor', 'Online', NULL, 45, '11:15:00', 1, 0),
(19, 'Doctor', 'Online', NULL, 45, '11:30:00', 1, 0),
(20, 'Doctor', 'Online', NULL, 45, '11:45:00', 1, 0),
(21, 'Doctor', 'Online', NULL, 45, '12:00:00', 1, 0),
(22, 'Doctor', 'Online', NULL, 45, '12:15:00', 1, 0),
(23, 'Doctor', 'Online', NULL, 45, '12:30:00', 1, 0),
(24, 'Doctor', 'Online', NULL, 45, '12:45:00', 1, 0),
(25, 'Doctor', 'Online', NULL, 45, '13:00:00', 1, 0),
(26, 'Doctor', 'Online', NULL, 45, '13:15:00', 1, 0),
(27, 'Doctor', 'Online', NULL, 45, '13:30:00', 1, 0),
(28, 'Doctor', 'Online', NULL, 45, '13:45:00', 1, 0),
(29, 'Doctor', 'Online', NULL, 45, '14:00:00', 1, 0),
(30, 'Doctor', 'Online', NULL, 45, '14:15:00', 1, 0),
(31, 'Doctor', 'Online', NULL, 45, '14:30:00', 1, 0),
(32, 'Doctor', 'Online', NULL, 45, '14:45:00', 1, 0),
(33, 'Doctor', 'Online', NULL, 45, '15:00:00', 1, 0),
(34, 'Doctor', 'Online', NULL, 45, '15:15:00', 1, 0),
(35, 'Doctor', 'Online', NULL, 45, '15:30:00', 1, 0),
(36, 'Doctor', 'Online', NULL, 45, '15:45:00', 1, 0),
(37, 'Doctor', 'Online', NULL, 45, '16:00:00', 1, 0),
(38, 'Doctor', 'Online', NULL, 45, '16:15:00', 1, 0),
(39, 'Doctor', 'Online', NULL, 45, '16:30:00', 1, 0),
(40, 'Doctor', 'Online', NULL, 45, '16:45:00', 1, 0),
(41, 'Doctor', 'Online', NULL, 45, '17:00:00', 1, 0),
(42, 'Doctor', 'Online', NULL, 45, '17:15:00', 1, 0),
(43, 'Doctor', 'Online', NULL, 45, '17:30:00', 1, 0),
(44, 'Doctor', 'Online', NULL, 45, '17:45:00', 1, 0),
(45, 'Doctor', 'Online', NULL, 45, '18:00:00', 1, 0),
(46, 'Doctor', 'Online', NULL, 45, '18:15:00', 1, 0),
(47, 'Doctor', 'Online', NULL, 45, '18:30:00', 1, 0),
(48, 'Doctor', 'Online', NULL, 45, '18:45:00', 1, 0),
(49, 'Doctor', 'Online', NULL, 45, '19:00:00', 1, 0),
(50, 'Doctor', 'Online', NULL, 45, '19:15:00', 1, 0),
(51, 'Doctor', 'Online', NULL, 45, '19:30:00', 1, 0),
(52, 'Doctor', 'Online', NULL, 45, '19:45:00', 1, 0),
(53, 'Doctor', 'Online', NULL, 45, '20:00:00', 1, 0),
(54, 'Doctor', 'Online', NULL, 15, '07:00:00', 1, 0),
(55, 'Doctor', 'Online', NULL, 15, '07:15:00', 4, 0),
(56, 'Doctor', 'Online', NULL, 15, '07:30:00', 4, 0),
(57, 'Doctor', 'Online', NULL, 15, '07:45:00', 1, 0),
(58, 'Doctor', 'Online', NULL, 15, '08:00:00', 1, 0),
(59, 'Doctor', 'Online', NULL, 15, '08:15:00', 6, 0),
(60, 'Doctor', 'Online', NULL, 15, '08:30:00', 1, 0),
(61, 'Doctor', 'Online', NULL, 15, '08:45:00', 1, 0),
(62, 'Doctor', 'Online', NULL, 15, '09:00:00', 1, 0),
(63, 'Doctor', 'Online', NULL, 15, '09:15:00', 1, 0),
(64, 'Doctor', 'Online', NULL, 15, '09:30:00', 1, 0),
(65, 'Doctor', 'Online', NULL, 15, '09:45:00', 1, 0),
(66, 'Doctor', 'Online', NULL, 15, '10:00:00', 1, 0),
(67, 'Doctor', 'Online', NULL, 15, '10:15:00', 1, 0),
(68, 'Doctor', 'Online', NULL, 15, '10:30:00', 1, 0),
(69, 'Doctor', 'Online', NULL, 15, '10:45:00', 1, 0),
(70, 'Doctor', 'Online', NULL, 15, '11:00:00', 1, 0),
(71, 'Doctor', 'Online', NULL, 15, '11:15:00', 1, 0),
(72, 'Doctor', 'Online', NULL, 15, '11:30:00', 1, 0),
(73, 'Doctor', 'Online', NULL, 15, '11:45:00', 1, 0),
(74, 'Doctor', 'Online', NULL, 15, '12:00:00', 1, 0),
(75, 'Doctor', 'Online', NULL, 15, '12:15:00', 1, 0),
(76, 'Doctor', 'Online', NULL, 15, '12:30:00', 1, 0),
(77, 'Doctor', 'Online', NULL, 15, '12:45:00', 1, 0),
(78, 'Doctor', 'Online', NULL, 15, '13:00:00', 1, 0),
(79, 'Doctor', 'Online', NULL, 15, '13:15:00', 1, 0),
(80, 'Doctor', 'Online', NULL, 15, '13:30:00', 1, 0),
(81, 'Doctor', 'Online', NULL, 15, '13:45:00', 1, 0),
(82, 'Doctor', 'Online', NULL, 15, '14:00:00', 1, 0),
(83, 'Doctor', 'Online', NULL, 15, '14:15:00', 1, 0),
(84, 'Doctor', 'Online', NULL, 15, '14:30:00', 1, 0),
(85, 'Doctor', 'Online', NULL, 15, '14:45:00', 1, 0),
(86, 'Doctor', 'Online', NULL, 15, '15:00:00', 1, 0),
(87, 'Doctor', 'Online', NULL, 15, '15:15:00', 1, 0),
(88, 'Doctor', 'Online', NULL, 15, '15:30:00', 1, 0),
(89, 'Doctor', 'Online', NULL, 15, '15:45:00', 1, 0),
(90, 'Doctor', 'Online', NULL, 15, '16:00:00', 1, 0),
(91, 'Doctor', 'Online', NULL, 15, '16:15:00', 1, 0),
(92, 'Doctor', 'Online', NULL, 15, '16:30:00', 1, 0),
(93, 'Doctor', 'Online', NULL, 15, '16:45:00', 1, 0),
(94, 'Doctor', 'Online', NULL, 15, '17:00:00', 1, 0),
(95, 'Doctor', 'Online', NULL, 15, '17:15:00', 1, 0),
(96, 'Doctor', 'Online', NULL, 15, '17:30:00', 1, 0),
(97, 'Doctor', 'Online', NULL, 15, '17:45:00', 1, 0),
(98, 'Doctor', 'Online', NULL, 15, '18:00:00', 1, 0),
(99, 'Doctor', 'Online', NULL, 15, '18:15:00', 1, 0),
(100, 'Doctor', 'Online', NULL, 15, '18:30:00', 1, 0),
(101, 'Doctor', 'Online', NULL, 15, '18:45:00', 1, 0),
(102, 'Doctor', 'Online', NULL, 15, '19:00:00', 1, 1),
(103, 'Doctor', 'Online', NULL, 15, '19:15:00', 1, 1),
(104, 'Doctor', 'Online', NULL, 15, '19:30:00', 1, 0),
(105, 'Doctor', 'Online', NULL, 15, '19:45:00', 1, 0),
(106, 'Doctor', 'Online', NULL, 15, '20:00:00', 1, 0),
(107, 'Doctor', 'Online', NULL, 30, '07:00:00', 1, 1),
(108, 'Doctor', 'Online', NULL, 30, '07:15:00', 1, 0),
(109, 'Doctor', 'Online', NULL, 30, '07:30:00', 1, 0),
(110, 'Doctor', 'Online', NULL, 30, '07:45:00', 1, 0),
(111, 'Doctor', 'Online', NULL, 30, '08:00:00', 1, 0),
(112, 'Doctor', 'Online', NULL, 30, '08:15:00', 1, 0),
(113, 'Doctor', 'Online', NULL, 30, '08:30:00', 1, 0),
(114, 'Doctor', 'Online', NULL, 30, '08:45:00', 1, 0),
(115, 'Doctor', 'Online', NULL, 30, '09:00:00', 1, 0),
(116, 'Doctor', 'Online', NULL, 30, '09:15:00', 1, 0),
(117, 'Doctor', 'Online', NULL, 30, '09:30:00', 1, 0),
(118, 'Doctor', 'Online', NULL, 30, '09:45:00', 1, 0),
(119, 'Doctor', 'Online', NULL, 30, '10:00:00', 1, 0),
(120, 'Doctor', 'Online', NULL, 30, '10:15:00', 1, 0),
(121, 'Doctor', 'Online', NULL, 30, '10:30:00', 1, 0),
(122, 'Doctor', 'Online', NULL, 30, '10:45:00', 1, 0),
(123, 'Doctor', 'Online', NULL, 30, '11:00:00', 1, 0),
(124, 'Doctor', 'Online', NULL, 30, '11:15:00', 1, 0),
(125, 'Doctor', 'Online', NULL, 30, '11:30:00', 1, 0),
(126, 'Doctor', 'Online', NULL, 30, '11:45:00', 1, 0),
(127, 'Doctor', 'Online', NULL, 30, '12:00:00', 1, 0),
(128, 'Doctor', 'Online', NULL, 30, '12:15:00', 1, 0),
(129, 'Doctor', 'Online', NULL, 30, '12:30:00', 1, 0),
(130, 'Doctor', 'Online', NULL, 30, '12:45:00', 1, 0),
(131, 'Doctor', 'Online', NULL, 30, '13:00:00', 1, 0),
(132, 'Doctor', 'Online', NULL, 30, '13:15:00', 1, 0),
(133, 'Doctor', 'Online', NULL, 30, '13:30:00', 1, 0),
(134, 'Doctor', 'Online', NULL, 30, '13:45:00', 1, 0),
(135, 'Doctor', 'Online', NULL, 30, '14:00:00', 1, 0),
(136, 'Doctor', 'Online', NULL, 30, '14:15:00', 1, 0),
(137, 'Doctor', 'Online', NULL, 30, '14:30:00', 1, 0),
(138, 'Doctor', 'Online', NULL, 30, '14:45:00', 1, 0),
(139, 'Doctor', 'Online', NULL, 30, '15:00:00', 1, 0),
(140, 'Doctor', 'Online', NULL, 30, '15:15:00', 1, 0),
(141, 'Doctor', 'Online', NULL, 30, '15:30:00', 1, 0),
(142, 'Doctor', 'Online', NULL, 30, '15:45:00', 1, 0),
(143, 'Doctor', 'Online', NULL, 30, '16:00:00', 1, 0),
(144, 'Doctor', 'Online', NULL, 30, '16:15:00', 1, 0),
(145, 'Doctor', 'Online', NULL, 30, '16:30:00', 1, 0),
(146, 'Doctor', 'Online', NULL, 30, '16:45:00', 1, 0),
(147, 'Doctor', 'Online', NULL, 30, '17:00:00', 1, 0),
(148, 'Doctor', 'Online', NULL, 30, '17:15:00', 1, 0),
(149, 'Doctor', 'Online', NULL, 30, '17:30:00', 1, 0),
(150, 'Doctor', 'Online', NULL, 30, '17:45:00', 1, 0),
(151, 'Doctor', 'Online', NULL, 30, '18:00:00', 1, 0),
(152, 'Doctor', 'Online', NULL, 30, '18:15:00', 1, 0),
(153, 'Doctor', 'Online', NULL, 30, '18:30:00', 1, 0),
(154, 'Doctor', 'Online', NULL, 30, '18:45:00', 1, 0),
(155, 'Doctor', 'Online', NULL, 30, '19:00:00', 1, 0),
(156, 'Doctor', 'Online', NULL, 30, '19:15:00', 1, 0),
(157, 'Doctor', 'Online', NULL, 30, '19:30:00', 1, 0),
(158, 'Doctor', 'Online', NULL, 30, '19:45:00', 1, 0),
(159, 'Doctor', 'Online', NULL, 30, '20:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `canceled_appointments`
--

CREATE TABLE `canceled_appointments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `cancellation_time` datetime DEFAULT current_timestamp(),
  `cancellation_reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `canceled_appointments`
--

INSERT INTO `canceled_appointments` (`id`, `appointment_id`, `cancellation_time`, `cancellation_reason`) VALUES
(1, 6, '2024-09-10 23:26:26', 'unknown'),
(2, 8, '2024-09-10 23:27:35', 'yes'),
(3, 9, '2024-09-11 10:05:27', 'unknown'),
(4, 10, '2024-09-11 10:59:42', 'unknown'),
(5, 16, '2024-09-12 01:28:31', 'testing'),
(6, 18, '2024-09-29 19:13:33', 'ok');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `contact` varchar(255) NOT NULL,
  `consultation_date` date NOT NULL,
  `consultation_time` time NOT NULL,
  `doctor_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `reason_for_visit` text NOT NULL,
  `history_of_illness` text NOT NULL,
  `past_medical_history` text DEFAULT NULL,
  `family_history` text DEFAULT NULL,
  `social_history` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `lab_tests` text DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `other_prescriptions` text DEFAULT NULL,
  `general_appearance` text DEFAULT NULL,
  `heent` text DEFAULT NULL,
  `cardiovascular` text DEFAULT NULL,
  `respiratory` text DEFAULT NULL,
  `gastrointestinal` text DEFAULT NULL,
  `musculoskeletal` text DEFAULT NULL,
  `neurological` text DEFAULT NULL,
  `skin` text DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `heart_rate` varchar(20) DEFAULT NULL,
  `respiratory_rate` varchar(20) DEFAULT NULL,
  `temperature` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `bmi` varchar(20) DEFAULT NULL,
  `blood_tests` text DEFAULT NULL,
  `urine_tests` text DEFAULT NULL,
  `imaging_results` text DEFAULT NULL,
  `primary_diagnosis` text NOT NULL,
  `secondary_diagnoses` text DEFAULT NULL,
  `icd_10_codes` text DEFAULT NULL,
  `procedures` text DEFAULT NULL,
  `referrals` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `symptoms_to_watch` text DEFAULT NULL,
  `emergency_instructions` text DEFAULT NULL,
  `education_materials` text DEFAULT NULL,
  `doctor_comments` text DEFAULT NULL,
  `patient_concerns` text DEFAULT NULL,
  `doctor_signature` varchar(255) NOT NULL,
  `date_of_signing` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `vital_signs_blood_pressure` varchar(255) DEFAULT NULL,
  `vital_signs_heart_rate` varchar(255) DEFAULT NULL,
  `vital_signs_respiratory_rate` varchar(255) DEFAULT NULL,
  `vital_signs_temperature` varchar(255) DEFAULT NULL,
  `vital_signs_weight` varchar(255) DEFAULT NULL,
  `vital_signs_height` varchar(255) DEFAULT NULL,
  `vital_signs_bmi` varchar(255) DEFAULT NULL,
  `physical_examination_general_appearance` text DEFAULT NULL,
  `physical_examination_heent` text DEFAULT NULL,
  `physical_examination_cardiovascular` text DEFAULT NULL,
  `physical_examination_respiratory` text DEFAULT NULL,
  `physical_examination_gastrointestinal` text DEFAULT NULL,
  `physical_examination_musculoskeletal` text DEFAULT NULL,
  `physical_examination_neurological` text DEFAULT NULL,
  `physical_examination_skin` text DEFAULT NULL,
  `initial_diagnosis` text DEFAULT NULL,
  `final_diagnosis` text DEFAULT NULL,
  `medication_ids` text DEFAULT NULL,
  `medication_names` text DEFAULT NULL,
  `medication_dosages` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`consultation_id`, `patient_id`, `patient_name`, `dob`, `gender`, `contact`, `consultation_date`, `consultation_time`, `doctor_name`, `department`, `reason_for_visit`, `history_of_illness`, `past_medical_history`, `family_history`, `social_history`, `allergies`, `lab_tests`, `medications`, `other_prescriptions`, `general_appearance`, `heent`, `cardiovascular`, `respiratory`, `gastrointestinal`, `musculoskeletal`, `neurological`, `skin`, `blood_pressure`, `heart_rate`, `respiratory_rate`, `temperature`, `weight`, `height`, `bmi`, `blood_tests`, `urine_tests`, `imaging_results`, `primary_diagnosis`, `secondary_diagnoses`, `icd_10_codes`, `procedures`, `referrals`, `recommendations`, `follow_up_date`, `symptoms_to_watch`, `emergency_instructions`, `education_materials`, `doctor_comments`, `patient_concerns`, `doctor_signature`, `date_of_signing`, `created_at`, `vital_signs_blood_pressure`, `vital_signs_heart_rate`, `vital_signs_respiratory_rate`, `vital_signs_temperature`, `vital_signs_weight`, `vital_signs_height`, `vital_signs_bmi`, `physical_examination_general_appearance`, `physical_examination_heent`, `physical_examination_cardiovascular`, `physical_examination_respiratory`, `physical_examination_gastrointestinal`, `physical_examination_musculoskeletal`, `physical_examination_neurological`, `physical_examination_skin`, `initial_diagnosis`, `final_diagnosis`, `medication_ids`, `medication_names`, `medication_dosages`, `last_updated`) VALUES
(1, 12345, '', '0000-00-00', 'male', '', '2024-07-26', '17:47:00', 'admin', '', 'stomach pain ', 'this is the first time ', '', 'none', 'none', 'Goat Meat', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-07-26 15:47:29', '110/70mmHg', '82b/m', 'none', '37c', '65kg', '174cm', '', 'looks pale', '', 'none', 'none', 'none', 'none', 'abit of mental ', 'rough', 'mental Health', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(2, 8994251, '', '0000-00-00', 'male', '', '2024-08-05', '14:31:06', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', '[\n    {\"id\": 1, \"name\": \"Aspirin\", \"dosage\": \"500mg\"},\n    {\"id\": 2, \"name\": \"Ibuprofen\", \"dosage\": \"200mg\"}\n]\n', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-05 12:31:06', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(3, 12345, '', '0000-00-00', 'male', '', '2024-08-06', '06:34:35', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', 'Array', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 04:34:35', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(4, 0, '', '0000-00-00', 'male', '', '2024-08-06', '06:59:27', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 04:59:27', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(5, 0, '', '0000-00-00', 'male', '', '2024-08-06', '07:02:05', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 05:02:05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(6, 0, '', '0000-00-00', 'male', '', '2024-08-06', '07:20:51', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 05:20:51', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(7, 0, '', '0000-00-00', 'male', '', '2024-08-06', '21:21:56', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 19:21:56', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"]', 'amplicin', '2 x 3 daily', '2024-09-28 16:09:50'),
(8, 0, '', '0000-00-00', 'male', '', '2024-08-06', '21:22:39', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 19:22:39', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', 'amplicin', '2 x 3 daily', '2024-09-28 16:09:50'),
(9, 0, '', '0000-00-00', 'male', '', '2024-08-06', '22:09:07', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', 'amplicin, hacv', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-06 20:09:07', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\",\"7\"]', 'amplicin, hacv', '2 x 3 daily, 2 x 4 daily', '2024-09-28 16:09:50'),
(10, 0, '', '0000-00-00', 'male', '', '2024-08-08', '09:14:56', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', 'amplicin', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 07:14:56', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', 'amplicin', '2 x 3 daily', '2024-09-28 16:09:50'),
(11, 12345, '', '0000-00-00', 'male', '', '2024-08-08', '12:10:41', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', '[]', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 10:10:41', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-09-28 16:09:50'),
(12, 12345, '', '0000-00-00', 'male', '', '2024-08-08', '12:21:45', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 10:21:45', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', '2024-09-28 16:09:50'),
(13, 12345, '', '0000-00-00', 'male', '', '2024-08-08', '12:22:26', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 10:22:26', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', '2024-09-28 16:09:50'),
(14, 12345, '', '0000-00-00', 'male', '', '2024-08-08', '12:27:10', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 10:27:10', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', '2024-09-28 16:09:50'),
(15, 12345, '', '0000-00-00', 'male', '', '2024-08-09', '00:48:35', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-08 22:48:35', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 4 daily\"]', '2024-09-28 16:09:50'),
(16, 12345, '', '0000-00-00', 'male', '', '2024-08-10', '19:10:08', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', 'Array', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-10 17:10:08', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(17, 12345, '', '0000-00-00', 'male', '', '2024-08-10', '19:16:43', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', 'Array', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-10 17:16:43', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, '2024-09-28 16:09:50'),
(18, 12345, '', '0000-00-00', 'male', '', '2024-08-10', '19:17:52', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-10 17:17:52', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"7\"]', '[\"hacv\"]', '[\"2 x 4 daily\"]', '2024-09-28 16:09:50'),
(19, 8994251, '', '0000-00-00', 'male', '', '2024-08-30', '08:00:28', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, 'panadol', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-08-30 06:00:28', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 3 daily for 6 days\"]', '2024-09-28 16:09:50'),
(20, 12345, '', '0000-00-00', 'male', '', '2024-09-03', '13:59:21', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', 'unknown medication', '', '', '0000-00-00', '2024-09-03 11:59:21', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"2 x4 daily for 8 days\"]', '2024-09-28 16:09:50'),
(21, 12345, '', '0000-00-00', 'male', '', '2024-09-03', '15:03:58', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-09-03 13:03:58', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"4x 5 daily\"]', '2024-09-28 16:09:50'),
(22, 8994251, '', '0000-00-00', 'male', '', '2024-09-05', '15:33:11', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-09-05 13:33:11', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-09-28 16:09:50'),
(23, 8994251, '', '0000-00-00', 'male', '', '2024-09-05', '15:33:17', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-09-05 13:33:17', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-09-28 16:09:50'),
(24, 12345, '', '0000-00-00', 'male', '', '2024-09-26', '17:15:19', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-09-26 15:15:19', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\",\"7\",\"7\"]', '[\"amplicin\",\"hacv\",\"hacv\"]', '[\"2x 8 daily\",\"15 daily\",\"2 x 4 daily\"]', '2024-09-28 16:09:50'),
(25, 12345, '', '0000-00-00', 'male', '', '2024-09-26', '22:04:53', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-09-26 20:04:53', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"7 daily\"]', '2024-09-28 16:09:50'),
(26, 12345, '', '0000-00-00', 'male', '', '2024-09-27', '00:01:59', 'admin', 'Admin', '', '', '', '', '', '', '\"1\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', 'not coming', '', '', '0000-00-00', '2024-09-26 22:01:59', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-09-28 16:09:50'),
(27, 12345, '', '0000-00-00', 'male', '', '2024-09-28', '15:49:26', 'admin', 'Admin', '', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '0', '0', '0', '0', '', '0000-00-00', '2024-09-28 13:49:26', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"],6,7,7', '[\"amplicin\"],amplicin,hacv,hacv', '[\"2 x 4 daily\"],2 x 3 daily,2 x 4 daily,8 days monthly', '2024-09-28 19:07:56'),
(28, 12345, '', '0000-00-00', 'male', '', '2024-09-29', '00:47:54', 'admin', 'Admin', '', '', '', '', '', '', '', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2024-09-30', 'fever and pneumonia ', '0', '0', '0', '0', '', '0000-00-00', '2024-09-28 22:47:54', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '5', 'amplicin', '2 x 4 daily', '2024-09-28 22:52:07'),
(29, 12345, '', '0000-00-00', 'male', '', '2024-12-24', '22:51:28', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-12-24 21:51:29', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-12-24 21:51:29'),
(30, 12345, '', '0000-00-00', 'male', '', '2024-12-24', '23:42:48', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-12-24 22:42:48', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-12-24 22:42:48'),
(31, 8108085, '', '0000-00-00', 'male', '', '2024-12-29', '02:51:57', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2024-12-29 01:51:57', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2024-12-29 01:51:57'),
(32, 12345, '', '0000-00-00', 'male', '', '2025-01-03', '05:13:42', 'admin', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:13:42', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-03 04:13:42'),
(33, 12345, '', '0000-00-00', 'male', '', '2025-01-03', '05:26:52', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:26:52', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-03 04:26:52'),
(34, 12345, '', '0000-00-00', 'male', '', '2025-01-03', '05:27:13', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:27:13', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', '2025-01-03 04:27:13'),
(35, 12345, '', '0000-00-00', 'male', '', '2025-01-03', '05:30:08', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:30:08', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-03 04:30:08'),
(36, 12345, '', '0000-00-00', 'male', '', '2025-01-03', '05:30:32', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:30:32', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', '2025-01-03 04:30:32'),
(37, 8994251, '', '0000-00-00', 'male', '', '2025-01-03', '05:40:37', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-03 04:40:37', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-03 04:40:37'),
(38, 8994251, '', '0000-00-00', 'male', '', '2025-01-04', '18:14:23', 'Mr. zid  zick', 'Doctor', '', '', '', '', '', '', '\"6,7\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 17:14:23', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 17:14:23'),
(39, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '20:36:21', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '7,6', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '0', '0', '0', '0', '', '0000-00-00', '2025-01-04 19:36:21', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 23:18:56'),
(40, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '20:50:13', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"6\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 19:50:13', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 19:50:13'),
(41, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '20:50:45', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"7\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 19:50:45', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 19:50:45'),
(42, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '20:55:05', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"7\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 19:55:05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 19:55:05'),
(43, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '21:29:17', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '\"6\"', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 20:29:17', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 20:29:17'),
(44, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '22:13:55', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '[6]', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 21:13:55', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 21:13:55'),
(45, 12345, '', '0000-00-00', 'male', '', '2025-01-04', '22:38:20', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '[6,7]', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 21:38:21', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 21:38:21'),
(46, 12345, '', '0000-00-00', 'male', '', '2025-01-05', '00:20:46', 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '', '', '', '', '', '', '[6,7]', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '', '', '', '', '', '0000-00-00', '2025-01-04 23:20:46', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-04 23:20:46'),
(47, 2246405, '', '0000-00-00', 'male', '', '2025-01-05', '17:53:47', 'Mr. zid  zick', 'Doctor', '', '', '', '', '', '', '6', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '0', '0', '0', '0', '', '0000-00-00', '2025-01-05 16:53:47', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-05 16:54:53'),
(48, 8994251, '', '0000-00-00', 'male', '', '2025-01-05', '22:03:04', 'Mr. zid  zick', 'Doctor', '', '', '', '', '', '', '6,7,8', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '0000-00-00', '', '0', '0', '0', '0', '', '0000-00-00', '2025-01-05 21:03:05', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '[]', '[]', '[]', '2025-01-06 00:30:26');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_updates`
--

CREATE TABLE `consultation_updates` (
  `id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` varchar(255) NOT NULL,
  `update_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `consultation_updates`
--

INSERT INTO `consultation_updates` (`id`, `consultation_id`, `updated_at`, `updated_by`, `update_notes`) VALUES
(1, 28, '2024-09-28 23:51:07', 'admin', 'Consultation details updated.'),
(2, 28, '2024-09-28 23:52:07', 'admin', 'Consultation details updated.'),
(3, 37, '2025-01-03 05:40:53', 'admin', 'Consultation details updated.'),
(4, 39, '2025-01-04 20:43:30', 'admin', 'Consultation details updated.'),
(5, 39, '2025-01-04 22:31:37', 'admin', 'Consultation details updated.'),
(6, 39, '2025-01-04 22:31:48', 'admin', 'Consultation details updated.'),
(7, 39, '2025-01-04 22:32:30', 'admin', 'Consultation details updated.'),
(8, 39, '2025-01-04 23:42:56', 'admin', 'Consultation details updated.'),
(9, 39, '2025-01-04 23:51:00', 'admin', 'Consultation details updated.'),
(10, 39, '2025-01-05 00:12:25', 'admin', 'Consultation details updated.'),
(11, 39, '2025-01-05 00:18:56', 'admin', 'Consultation details updated.'),
(12, 47, '2025-01-05 17:54:53', 'zid', 'Consultation details updated.'),
(13, 48, '2025-01-06 00:31:51', 'zid', 'Consultation details updated.'),
(14, 48, '2025-01-06 00:32:56', 'zid', 'Consultation details updated.'),
(15, 48, '2025-01-06 00:34:17', 'zid', 'Consultation details updated.'),
(16, 48, '2025-01-06 00:52:36', 'zid', 'Consultation details updated.'),
(17, 48, '2025-01-06 00:56:34', 'zid', 'Consultation details updated.'),
(18, 48, '2025-01-06 00:58:54', 'zid', 'Consultation details updated.'),
(19, 48, '2025-01-06 00:59:30', 'zid', 'Consultation details updated.'),
(20, 48, '2025-01-06 01:01:30', 'zid', 'Consultation details updated.'),
(21, 48, '2025-01-06 01:06:15', 'zid', 'Consultation details updated.'),
(22, 48, '2025-01-06 01:07:40', 'zid', 'Consultation details updated.'),
(23, 48, '2025-01-06 01:07:52', 'zid', 'Consultation details updated.'),
(24, 48, '2025-01-06 01:18:20', 'zid', 'Consultation details updated.'),
(25, 48, '2025-01-06 01:21:11', 'zid', 'Consultation details updated.'),
(26, 48, '2025-01-06 01:26:40', 'zid', 'Consultation details updated.'),
(27, 48, '2025-01-06 01:30:28', 'zid', 'Consultation details updated.');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `drug_id` varchar(6) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` text NOT NULL,
  `groups` text NOT NULL,
  `num_capsules` int(11) NOT NULL,
  `num_sachets` int(11) NOT NULL,
  `num_packets` int(11) NOT NULL,
  `form` varchar(50) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `mode_of_admin` varchar(50) NOT NULL,
  `injection_methods` text DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `price_per_capsule` decimal(10,2) NOT NULL,
  `special_note` text DEFAULT NULL,
  `prescription_note` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `capsules_remaining` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `drug_id`, `name`, `category`, `groups`, `num_capsules`, `num_sachets`, `num_packets`, `form`, `batch_number`, `mode_of_admin`, `injection_methods`, `expiry_date`, `price_per_capsule`, `special_note`, `prescription_note`, `image`, `capsules_remaining`) VALUES
(5, '960165', 'amplicin', 'Hallucinogens', 'Antidiabetics', 130, 2, 8, 'Tablets', '87893903', 'Oral', '', '2024-07-22', 0.67, '', '', 'uploads/music.jpg', 0),
(6, '938230', 'amplicin', 'Stimulants', 'Anesthetics', 89, 67, 5, 'Tablets', '78897', 'Oral', '', '2024-07-24', 78.00, '', '', 'uploads/support-group.jpg', 5),
(7, '797052', 'hacv', 'Hallucinogens', 'Analgesics', 56, 7, 5, 'Liquids', '78767', 'Intrathecal', '', '2024-07-28', 77.00, '', '', 'uploads/support-group.jpg', 55);

-- --------------------------------------------------------

--
-- Table structure for table `lab_requests`
--

CREATE TABLE `lab_requests` (
  `id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `requested_by` varchar(255) NOT NULL,
  `date_requested` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `result_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_requests`
--

INSERT INTO `lab_requests` (`id`, `consultation_id`, `patient_id`, `template_id`, `requested_by`, `date_requested`, `status`, `result_content`) VALUES
(1, 44, 12345, 6, 'admin', '2025-01-04 22:13:55', 'Pending', NULL),
(2, 45, 12345, 6, 'admin', '2025-01-04 22:38:21', 'Completed', '<p style=\"text-align: center;\"><span style=\"background-color: rgb(224, 62, 45);\"><strong>white&nbsp;</strong></span></p>\r\n<p style=\"text-align: left;\">This is the lab test</p>'),
(3, 45, 12345, 7, 'admin', '2025-01-04 22:38:21', 'Pending', NULL),
(4, 39, 12345, NULL, 'admin', '2025-01-04 23:51:00', 'Completed', ''),
(5, 39, 12345, NULL, 'admin', '2025-01-05 00:12:25', 'Completed', ''),
(6, 39, 12345, NULL, 'admin', '2025-01-05 00:18:56', 'Completed', ''),
(7, 46, 12345, 6, 'admin', '2025-01-05 00:20:46', 'Completed', '<p style=\"text-align: center;\"><span style=\"background-color: rgb(224, 62, 45);\"><strong>white&nbsp;</strong></span></p>'),
(8, 46, 12345, 7, 'admin', '2025-01-05 00:20:46', 'Pending', NULL),
(9, 47, 2246405, 6, 'zid', '2025-01-05 17:53:48', 'Completed', '<p>&lt;div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\"&gt;<br>&nbsp; &nbsp; &lt;div style=\"text-align: center; margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;h2 style=\"color: #007bff; text-transform: uppercase;\"&gt;Laboratory Test Report&lt;/h2&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"font-size: 1.1em; color: #555;\"&gt;Comprehensive Medical Analysis&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin-bottom: 30px;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Patient Information:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Name:&lt;/strong&gt; [Patient Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Age:&lt;/strong&gt; [Patient Age]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Gender:&lt;/strong&gt; [Patient Gender]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Date of Test:&lt;/strong&gt; [Test Date]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Patient ID:&lt;/strong&gt; [Patient ID]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Test Details:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Name:&lt;/strong&gt; [Test Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Type:&lt;/strong&gt; [Test Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Sample Collected:&lt;/strong&gt; [Sample Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Results:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;table style=\"width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.95em;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr style=\"background-color: #f8f9fa;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Parameter&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Result&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Reference Range&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;!-- Add more rows as needed --&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/table&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Comments:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"border: 1px solid #ccc; padding: 10px; background-color: #f8f9fa; font-style: italic;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [Enter any comments or observations here.]<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin: 30px 0;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"text-align: center; font-size: 0.9em; color: #555;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p&gt;Authorized By: [Lab Technician/Doctor Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Lab Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Contact Information]&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;<br>&lt;/div&gt;</p>\r\n<p style=\"text-align: center;\"><br><br></p>'),
(10, 47, 2246405, NULL, 'zid', '2025-01-05 17:54:53', 'Completed', ''),
(11, 48, 8994251, 6, 'zid', '2025-01-05 22:03:05', 'Completed', '<p>&lt;div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\"&gt;<br>&nbsp; &nbsp; &lt;div style=\"text-align: center; margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;h2 style=\"color: #007bff; text-transform: uppercase;\"&gt;Laboratory Test Report&lt;/h2&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"font-size: 1.1em; color: #555;\"&gt;Comprehensive Medical Analysis&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin-bottom: 30px;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Patient Information:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Name:&lt;/strong&gt; [Patient Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Age:&lt;/strong&gt; [Patient Age]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Gender:&lt;/strong&gt; [Patient Gender]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Date of Test:&lt;/strong&gt; [Test Date]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Patient ID:&lt;/strong&gt; [Patient ID]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Test Details:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Name:&lt;/strong&gt; [Test Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Type:&lt;/strong&gt; [Test Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Sample Collected:&lt;/strong&gt; [Sample Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Results:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;table style=\"width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.95em;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr style=\"background-color: #f8f9fa;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Parameter&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Result&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Reference Range&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;!-- Add more rows as needed --&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/table&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Comments:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"border: 1px solid #ccc; padding: 10px; background-color: #f8f9fa; font-style: italic;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [Enter any comments or observations here.]<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin: 30px 0;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"text-align: center; font-size: 0.9em; color: #555;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p&gt;Authorized By: [Lab Technician/Doctor Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Lab Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Contact Information]&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;<br>&lt;/div&gt;</p>\r\n<p style=\"text-align: center;\"><br><br></p>'),
(18, 48, 8994251, 7, 'zid', '2025-01-06 01:01:30', 'Pending', NULL),
(19, 48, 8994251, 8, 'zid', '2025-01-06 01:07:52', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `id` int(11) NOT NULL,
  `test_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `lab_tests`
--

INSERT INTO `lab_tests` (`id`, `test_name`, `description`, `created_at`) VALUES
(1, 'FBC', 'unknown ', '2024-07-26 10:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_templates`
--

CREATE TABLE `lab_test_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_test_templates`
--

INSERT INTO `lab_test_templates` (`id`, `name`, `content`, `created_at`, `updated_at`) VALUES
(6, 'White Blood ', '<p>&lt;div style=\"font-family: Arial, sans-serif; line-height: 1.6; color: #333;\"&gt;<br>&nbsp; &nbsp; &lt;div style=\"text-align: center; margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;h2 style=\"color: #007bff; text-transform: uppercase;\"&gt;Laboratory Test Report&lt;/h2&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"font-size: 1.1em; color: #555;\"&gt;Comprehensive Medical Analysis&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin-bottom: 30px;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Patient Information:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Name:&lt;/strong&gt; [Patient Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Age:&lt;/strong&gt; [Patient Age]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Gender:&lt;/strong&gt; [Patient Gender]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Date of Test:&lt;/strong&gt; [Test Date]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Patient ID:&lt;/strong&gt; [Patient ID]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Test Details:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul style=\"list-style: none; padding: 0; margin: 10px 0;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Name:&lt;/strong&gt; [Test Name]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Test Type:&lt;/strong&gt; [Test Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;&lt;strong&gt;Sample Collected:&lt;/strong&gt; [Sample Type]&lt;/li&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Results:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;table style=\"width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.95em;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr style=\"background-color: #f8f9fa;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Parameter&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Result&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;th style=\"border: 1px solid #ccc; padding: 8px; text-align: left;\"&gt;Reference Range&lt;/th&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/thead&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 1]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Parameter 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Result 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;td style=\"border: 1px solid #ccc; padding: 8px;\"&gt;[Range 2]&lt;/td&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tr&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;!-- Add more rows as needed --&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;/tbody&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/table&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"margin-bottom: 20px;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;strong&gt;Comments:&lt;/strong&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"border: 1px solid #ccc; padding: 10px; background-color: #f8f9fa; font-style: italic;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [Enter any comments or observations here.]<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;hr style=\"border: 1px solid #007bff; margin: 30px 0;\"&gt;</p>\r\n<p>&nbsp; &nbsp; &lt;div style=\"text-align: center; font-size: 0.9em; color: #555;\"&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p&gt;Authorized By: [Lab Technician/Doctor Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Lab Name]&lt;/p&gt;<br>&nbsp; &nbsp; &nbsp; &nbsp; &lt;p style=\"margin: 0;\"&gt;[Contact Information]&lt;/p&gt;<br>&nbsp; &nbsp; &lt;/div&gt;<br>&lt;/div&gt;</p>\r\n<p style=\"text-align: center;\"><br><br></p>', '2025-01-04 15:25:26', '2025-01-05 17:02:06'),
(7, 'White Blood Cell ', '<p style=\"text-align: center;\"><span style=\"background-color: rgb(45, 194, 107);\"><strong>white blood Cell&nbsp;</strong></span></p>\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 33.3333%;\"><col style=\"width: 33.3333%;\"><col style=\"width: 33.3333%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>', '2025-01-04 15:31:00', '2025-01-04 15:31:00'),
(8, 'hamoglobin level ', '<table style=\"border-collapse: collapse; width: 99.9891%;\" border=\"1\"><colgroup><col style=\"width: 33.3333%;\"><col style=\"width: 33.3333%;\"><col style=\"width: 33.3333%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>', '2025-01-06 00:07:30', '2025-01-06 00:07:30');

-- --------------------------------------------------------

--
-- Table structure for table `meetings`
--

CREATE TABLE `meetings` (
  `id` int(11) NOT NULL,
  `meeting_id` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_dob` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `department` enum('Doctors','Laboratory','Pharmacy') NOT NULL,
  `granted_departments` text DEFAULT NULL,
  `assigned_staff` varchar(255) NOT NULL,
  `expiration` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `status` varchar(50) DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `meetings`
--

INSERT INTO `meetings` (`id`, `meeting_id`, `created_by`, `patient_id`, `patient_name`, `patient_dob`, `reason`, `department`, `granted_departments`, `assigned_staff`, `expiration`, `created_at`, `password`, `status`) VALUES
(2, '30725b44f4282eb7', 'admin', 12345, 'Kingsley ugonna Aguagwa', '1992-08-17', 'yes', 'Doctors', NULL, 'nurse', '2024-07-17 00:00:00', '2024-07-16 23:53:47', 'pqeOuMm1', 'Expired'),
(4, '5e566b3dca1994f9', 'admin', 12345, 'Kingsley ugonna Aguagwa', '1992-08-17', 'unknown', 'Laboratory', NULL, 'admin', '2024-08-16 08:34:00', '2024-08-14 14:34:44', 'rNHhqHAS', 'Expired'),
(5, 'bfd638c9029ec320', 'admin', 12345, 'Kingsley ugonna Aguagwa', '1992-08-17', 'unknown', 'Laboratory', NULL, 'admin', '2024-10-01 20:44:00', '2024-08-17 17:41:02', 'oyRZLgrF', 'Expired'),
(6, '3ba275762f9722ca', 'admin', 8994251, 'Onyinye  Stephanie  Aguagwa', '1995-10-18', 'for consulation', 'Laboratory', NULL, 'admin', '2024-11-20 07:31:00', '2024-09-03 14:27:07', 'AozIN100', 'Expired'),
(8, 'f35bbbf1e11845e0', 'admin', 8994251, 'John  Smith', '2010-06-09', 'to see a doctor', 'Doctors', NULL, 'admin', '2024-11-14 20:48:00', '2024-10-09 17:46:35', 'Y6GoS5zi', 'Expired'),
(9, '9adb1cb117b6cfba', 'admin', 8994251, 'John  Smith', '2010-06-09', 'to see a doctor', 'Laboratory', NULL, 'admin2', '2024-10-31 14:28:00', '2024-10-09 21:28:13', 'jstWm0cu', 'Expired'),
(10, '26d332affaa8315e', 'admin', 8994251, 'fabulous  grant', '2010-06-09', 'to see a doctor', 'Doctors', NULL, 'admin', '2024-12-27 03:20:00', '2024-12-25 00:58:56', 'eYQN64ra', 'Expired'),
(11, 'd882985b1ba70042', 'admin', 8994251, 'fabulous  grant', '2010-06-09', 'unknown', '', NULL, 'admin', '2024-12-28 17:01:00', '2024-12-25 11:02:15', '0yZqhnKc', 'Expired'),
(12, 'b420cd936af253d7', 'admin', 12345, 'Kingsley ugonna Aguagwa', '1992-08-17', 'unknown', '', NULL, 'admin', '2024-12-28 21:34:00', '2024-12-28 08:06:20', 'tz32287j', 'Expired'),
(13, 'f964c6cbf98e0dc9', 'admin', 12345, 'Kingsley ugonna Aguagwa', '0000-00-00', 'ubnkowmn', '', NULL, 'Doctor', '2024-12-29 10:00:00', '2024-12-28 10:09:59', '9E9QfzeT', 'Expired'),
(14, '3d3c4aa99158936c', 'test', 12345, 'Kingsley ugonna Aguagwa', '0000-00-00', 'gg', '', 'Nurse,Lab Scientist', 'Nu', '2024-12-29 08:30:00', '2024-12-28 10:13:58', 'testit', 'Expired'),
(15, '0b74358ba3b04dc6', 'admin', 12345, 'Wiilliam  Stone', '0000-00-00', 'to see the doctor ', '', NULL, 'Doctor', '2025-01-03 08:30:00', '2025-01-03 06:19:35', '7L1YzmZE', 'Expired'),
(16, '720a5fd4b58a57e3', 'ezeani', 25348, 'Kingsley ugonna Aguagwa', '0000-00-00', 'stomach ache', '', NULL, 'Doctor', '2025-01-03 17:30:00', '2025-01-03 18:10:23', 'Xq7plPJS', 'Expired'),
(17, '6e5934b8c4328344', 'ezeani', 25348, 'Kingsley ugonna Aguagwa', '0000-00-00', 'leg pain', '', NULL, 'Doctor', '2025-01-03 19:00:00', '2025-01-03 18:12:27', 'NjgWeWle', 'Expired'),
(18, '78f172fc43d80d7b', 'admin', 25348, 'Kingsley ugonna Aguagwa', '0000-00-00', 'head pain ', '', NULL, 'Doctor', '2025-01-07 19:45:00', '2025-01-07 21:58:15', 'ClEBK6wV', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `status` enum('sent','draft') DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_deleted_by_sender` tinyint(1) DEFAULT 0,
  `is_deleted_by_receiver` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted_by_admin` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `parent_id`, `sender_id`, `receiver_id`, `subject`, `body`, `status`, `attachment`, `created_at`, `is_deleted_by_sender`, `is_deleted_by_receiver`, `updated_at`, `is_deleted_by_admin`, `is_read`) VALUES
(5, NULL, 1, 1, 'how are you', 'just checking ', 'sent', NULL, '2024-09-17 15:04:25', 0, 0, '2024-09-19 09:56:38', 0, 0),
(6, NULL, 1, 4, 'tested ', 'on it', 'sent', NULL, '2024-09-17 15:05:05', 0, 0, '2024-09-19 09:56:38', 0, 0),
(9, NULL, 1, 1, 'ok ', 'seen it ', 'sent', NULL, '2024-09-17 17:11:04', 0, 0, '2024-09-23 18:10:58', 0, 1),
(10, NULL, 1, 1, 'uploads', 'ahead ahead', 'sent', 'C:\\xampp\\htdocs\\My Clinic\\messaging/uploads/66e9f1c3d7596_Screenshot (2).png', '2024-09-17 22:16:52', 0, 0, '2024-09-23 18:09:56', 0, 1),
(11, 9, 1, 1, '', 'ok thats fine', 'sent', NULL, '2024-09-18 08:47:30', 0, 0, '2024-09-19 09:56:38', 0, 0),
(12, 10, 1, 1, '', 'thtas fine', 'sent', NULL, '2024-09-18 09:52:00', 0, 0, '2024-09-19 09:56:38', 0, 0),
(13, 10, 1, 1, '', 'this is it', 'sent', '../messaging/uploads/66ea94ebad699_~$NGSLEY UGONNA AGUAGWA VOD.pdf', '2024-09-18 09:52:59', 0, 0, '2024-09-19 09:56:38', 0, 0),
(17, NULL, 1, 1, 'uploads', 'seem', 'sent', NULL, '2024-09-18 14:04:48', 0, 0, '2024-09-23 18:10:10', 0, 1),
(20, NULL, 5, 1, 'hello man', 'is that all', 'sent', NULL, '2024-09-18 23:28:59', 0, 1, '2024-09-19 09:56:38', 0, 0),
(21, NULL, 5, 1, 'is that what you want', 'ok if thats the case ', 'sent', NULL, '2024-09-18 23:36:39', 0, 0, '2024-09-23 18:11:49', 0, 1),
(22, NULL, 5, 1, 'dont', 'now i see', 'sent', NULL, '2024-09-19 00:13:25', 0, 0, '2024-09-19 09:56:38', 0, 0),
(23, NULL, 5, 1, 'try ag', 'fine', 'sent', NULL, '2024-09-19 00:18:13', 0, 0, '2024-09-19 09:56:38', 0, 0),
(24, NULL, 5, 1, 'that wont', 'that wont happen', 'sent', 'C:\\xampp\\htdocs\\My Clinic\\messaging/uploads/66ebe3fe81972_certificate and transcript.pdf', '2024-09-19 09:42:38', 0, 0, '2024-09-19 09:56:38', 0, 0),
(25, NULL, 5, 1, 'so many things to test on ', 'ok thats fine ', 'sent', NULL, '2024-09-19 09:43:10', 0, 1, '2024-09-19 19:39:56', 0, 0),
(35, NULL, 1, 1, 'how are you', 'ok o', 'sent', NULL, '2024-09-19 11:10:35', 0, 1, '2024-09-19 19:31:12', 0, 0),
(49, NULL, 5, 1, 'just checking ', 'Modify the Sidebar for Admins Only:\r\nYou will add a link to the reports page that only shows for users with the Admin usergroup.\r\n\r\nIn your sidebar.php, add this:\r\n\r\nphp\r\nCopy code\r\n<!-- Sidebar Links -->\r\n<div class=\"message-sidebar\">\r\n    <a href=\"new_message.php\">New Message</a>\r\n    <a href=\"inbox.php\">Inbox (<span id=\"unreadCount\"><?= $unreadCount; ?></span>)</a>\r\n    <a href=\"sent.php\">Sent</a>\r\n    <a href=\"draft.php\">Draft</a>\r\n    \r\n    <?php if ($_SESSION[\'usergroup\'] === \'Admin\'): ?>\r\n        <a href=\"reports.php\">Reported Messages</a>\r\n    <?php endif; ?>\r\n</div>\r\n3. Create the reports Table:\r\nTo track reports, ensure you have a reports table with the following structure:\r\n\r\nsql\r\nCopy code\r\nCREATE TABLE reports (\r\n    id INT AUTO_INCREMENT PRIMARY KEY,\r\n    message_id INT NOT NULL,\r\n    reported_by INT NOT NULL,\r\n    reason VARCHAR(255),\r\n    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\r\n    FOREIGN KEY (message_id) REFERENCES messages(id),\r\n    FOREIGN KEY (reported_by) REFERENCES users(registration_id)\r\n);\r\nThis setup ensures that:\r\n\r\nAdmins can view the reported messages through reports.php.\r\nUsers can report messages using report_message.php.\r\nPagination works to display the reports with 10 entries per page.\r\nLet me know if you have further questions or run into any issues!\r\n\r\n\r\n\r\n\r\n\r\n\r\nYou said:\r\nthis is the error i had: Fatal error\r\n: Uncaught Error: Call to a member function bind_param() on bool in C:\\xampp\\htdocs\\My Clinic\\messaging\\reports.php:33 Stack trace: #0 {main} thrown in\r\nC:\\xampp\\htdocs\\My Clinic\\messaging\\reports.php\r\non line\r\n33\r\nChatGPT said:\r\nChatGPT\r\nThe error you\'re encountering indicates that the SQL query preparation failed because the bind_param() method was called on a bool, which happens when the prepare() method fails.\r\n\r\nThis usually happens because of a syntax error in the SQL query, such as referencing a non-existent table or column. To debug this, let\'s update the script to check for SQL errors when the query is prepared. I\'ll also revise the query to ensure it works as expected.\r\n\r\nHere\'s how you can update the reports.php to fix the error:\r\n\r\nUpdated reports.php', 'sent', 'C:\\xampp\\htdocs\\My Clinic\\messaging/uploads/66ec0423041bc_cover letter.docx', '2024-09-19 11:59:47', 0, 1, '2024-09-19 19:30:29', 0, 0),
(87, NULL, 1, 1, 'is that so', 'ok find it ', 'draft', NULL, '2024-09-20 06:15:43', 0, 0, '2025-01-02 01:28:05', 0, 0),
(88, NULL, 1, 1, 'ok thats ', 'fine ', 'draft', NULL, '2024-09-20 06:20:35', 0, 0, '2025-01-02 01:28:05', 0, 0),
(89, NULL, 1, 1, 'ok thats ', 'fine ', 'draft', NULL, '2024-09-20 06:20:49', 0, 0, '2025-01-02 01:28:05', 0, 0),
(91, NULL, 1, 1, 'ok thats ', 'fine ', 'draft', NULL, '2024-09-20 06:20:52', 0, 0, '2025-01-03 12:45:26', 0, 1),
(94, NULL, 1, 1, 'compos', 'ok', 'sent', NULL, '2024-09-20 06:45:31', 0, 0, '2024-09-23 17:52:39', 0, 1),
(96, NULL, 1, 1, 'tested ', 'ok', 'sent', NULL, '2024-09-20 19:38:04', 0, 0, '2024-09-23 18:09:31', 0, 1),
(98, NULL, 1, 1, 'white line removed ', 'is tha it', 'sent', NULL, '2024-09-21 23:17:35', 0, 0, '2024-09-21 22:17:35', 0, 0),
(100, NULL, 5, 1, 'is safr', 'checking to see', 'sent', NULL, '2024-09-22 09:30:32', 0, 0, '2024-09-26 21:21:37', 0, 1),
(101, 96, 1, 1, '', 'thats fine', 'sent', NULL, '2024-09-22 09:41:45', 0, 0, '2024-09-22 08:41:45', 0, 0),
(102, 96, 1, 1, '', 'thats fine ', 'sent', NULL, '2024-09-22 09:43:00', 0, 0, '2024-09-22 08:43:00', 0, 0),
(103, 96, 1, 1, '', 'make it fun', 'sent', NULL, '2024-09-22 09:43:31', 0, 0, '2024-10-01 15:06:35', 0, 1),
(104, NULL, 5, 1, 'ok thats ', 'fine by me', 'draft', NULL, '2024-09-23 11:56:36', 0, 0, '2025-01-03 12:45:12', 0, 1),
(105, NULL, 5, 1, 'ok thats ', 'fine by me', 'sent', NULL, '2024-09-23 11:56:37', 0, 0, '2024-09-23 10:56:37', 0, 0),
(106, NULL, 5, 1, 'hello man', 'teseted', 'sent', NULL, '2024-09-23 18:19:56', 0, 0, '2024-09-23 17:22:31', 0, 1),
(107, NULL, 5, 1, 'hello man', 'teseted', 'draft', NULL, '2024-09-23 18:19:57', 0, 0, '2025-01-02 01:28:05', 0, 0),
(109, NULL, 5, 1, 'dusted', 'ok', 'sent', NULL, '2024-09-23 19:05:49', 0, 0, '2024-09-23 18:07:28', 0, 1),
(110, NULL, 5, 1, 'dusted', 'ok', 'sent', NULL, '2024-09-23 19:05:49', 0, 1, '2024-09-23 18:07:10', 0, 0),
(112, NULL, 5, 1, 'tosted', 'ok', 'sent', NULL, '2024-09-23 19:06:25', 0, 0, '2024-12-29 01:01:17', 0, 1),
(113, NULL, 5, 1, 'tested it', 'bye', 'draft', NULL, '2024-09-23 20:25:13', 0, 0, '2025-01-02 01:33:47', 0, 1),
(114, NULL, 5, 1, 'tested it', 'bye', 'sent', NULL, '2024-09-23 20:25:13', 0, 0, '2024-09-24 06:02:35', 0, 1),
(115, NULL, 1, 1, 'thats fine', '', 'draft', NULL, '2024-09-23 20:46:30', 0, 0, '2025-01-02 01:28:05', 0, 0),
(116, NULL, 5, 1, 'native ', 'ok', 'sent', NULL, '2024-09-23 21:07:45', 0, 1, '2024-09-24 06:48:46', 0, 0),
(117, NULL, 5, 1, 'native ', 'ok', 'draft', NULL, '2024-09-23 21:07:45', 0, 0, '2025-01-02 01:28:05', 0, 0),
(118, NULL, 1, 1, 'tested ', 'ok', 'sent', NULL, '2025-01-02 02:34:12', 0, 0, '2025-01-02 01:34:23', 0, 1),
(120, 118, 1, 1, '', 'welll said ', 'sent', NULL, '2025-01-02 02:34:32', 0, 0, '2025-01-02 01:34:32', 0, 0),
(121, 91, 1, 1, '', 'Thats fine, i will respond later ', 'sent', NULL, '2025-01-03 13:45:59', 0, 0, '2025-01-03 12:45:59', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `patient_db`
--

CREATE TABLE `patient_db` (
  `id` int(11) NOT NULL,
  `patient_id` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `education_level` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `next_of_kin_name` varchar(50) DEFAULT NULL,
  `next_of_kin_relation` varchar(20) DEFAULT NULL,
  `next_of_kin_telephone` varchar(20) DEFAULT NULL,
  `next_of_kin_city` varchar(50) DEFAULT NULL,
  `payer` varchar(50) DEFAULT NULL,
  `sponsor` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `previous_address_uk` text DEFAULT NULL,
  `previous_gp_practice` varchar(100) DEFAULT NULL,
  `address_previous_gp` text DEFAULT NULL,
  `enlisted_address` text DEFAULT NULL,
  `enlistment_date` date DEFAULT NULL,
  `discharge_date` date DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `allergy` text DEFAULT NULL,
  `disability` varchar(50) DEFAULT NULL,
  `id_type` varchar(50) DEFAULT NULL,
  `declaration_confirm` tinyint(1) DEFAULT NULL,
  `id_upload` longblob DEFAULT NULL,
  `disability_specify` varchar(255) DEFAULT NULL,
  `id_front_image` varchar(255) DEFAULT NULL,
  `id_back_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `patient_db`
--

INSERT INTO `patient_db` (`id`, `patient_id`, `first_name`, `middle_name`, `surname`, `dob`, `age`, `gender`, `marital_status`, `education_level`, `email`, `address`, `country`, `telephone`, `next_of_kin_name`, `next_of_kin_relation`, `next_of_kin_telephone`, `next_of_kin_city`, `payer`, `sponsor`, `username`, `password`, `previous_address_uk`, `previous_gp_practice`, `address_previous_gp`, `enlisted_address`, `enlistment_date`, `discharge_date`, `profile_pic`, `allergy`, `disability`, `id_type`, `declaration_confirm`, `id_upload`, `disability_specify`, `id_front_image`, `id_back_image`) VALUES
(1, '12345', 'Wiilliam', '', 'Stone', '1992-08-17', 32, 'Male', 'Married', 'Tertiary', 'ugosonic@gmail.com', '66 All Saints Road', 'GB', '07459943902', 'JESUS', 'Parent', '', 'Sittingbourne', 'Private Cash', 'Private Cash', 'test', '$2y$10$ohzCVn2qU0qVmQMde.DneOLHYUghMnRAb1.rJemDrIKotDJHP17tW', 'unknown', 'unknown', 'unknown', '', '0000-00-00', '0000-00-00', '/My Clinic/uploads/66f2ecb161e1d_IMG-20200627-WA0015.jpg', 'none', 'No', 'International Passport', 1, NULL, '', NULL, NULL),
(2, '25348', 'Kingsley', 'ugonna', 'Aguagwa', '1995-12-04', 28, 'Female', 'Married', 'Tertiary', 'ugosonic@gmail.com', '66 All Saints Road', 'BS', '07066839330', 'ALHAJI', 'Sibling', 'UNKNOWN ', '', 'Employer', 'Insurance', 'ezeani', '$2y$10$YJcf2MpN7f4RxhuzuBuzN.GQRj.R3I4VWoVLOLEQ5FDu7aa0sImTe', '', '', '', '', '0000-00-00', '0000-00-00', NULL, 'none', 'No', 'Driving Licence', 1, NULL, 'leg pain', NULL, NULL),
(3, '8994251', 'fabulous', '', 'grant', '2010-06-09', 14, 'Male', 'Single', 'Primary', 'ugosonic@gmail.com', '66 All Saints Road', 'GB', '', 'Smith Steven', 'Sibling', '07459943902', 'Sittingbourne', 'Insurance', 'Insurance', 'test2', '$2y$10$dWSCHVRljyLckteZsIs00Os1rHVSEpz/.o00q7e9k3ds/e.acXO.O', '', '', '', '', '0000-00-00', '0000-00-00', '../uploads/profile_pictures/66f3eba075307-My photo.jpg', '', '', 'Driving Licence', 1, NULL, '', NULL, NULL),
(5, '2246405', 'John', 'smith', 'cane', '2024-07-09', 0, 'Male', 'Married', 'Secondary', 'ugosonic@gmail.com', '66 All Saints Road', 'AT', '', '', 'Spouse', '', '', 'Employer', 'Employer', 'test3', '$2y$10$4VtUXtT8Oq5yrNeY.Zqzbezr9F.o46zadegY41/ryv28imJ4FW2RG', '', '', '', '', '0000-00-00', '0000-00-00', NULL, '', 'No', 'Driving Licence', 1, NULL, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `date_prescribed` date DEFAULT NULL,
  `time_prescribed` time DEFAULT NULL,
  `medications` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `medication_ids` text DEFAULT NULL,
  `medication_names` text DEFAULT NULL,
  `medication_dosages` text DEFAULT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `medication_quantities` text DEFAULT NULL,
  `medication_statuses` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `patient_id`, `doctor_name`, `department`, `date_prescribed`, `time_prescribed`, `medications`, `status`, `medication_ids`, `medication_names`, `medication_dosages`, `consultation_id`, `medication_quantities`, `medication_statuses`) VALUES
(1, 8994251, 'admin', 'Admin', '2024-08-05', '14:31:06', '[\n    {\"id\": 1, \"name\": \"Aspirin\", \"dosage\": \"500mg\"},\n    {\"id\": 2, \"name\": \"Ibuprofen\", \"dosage\": \"200mg\"}\n]\n', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 12345, 'admin', 'Admin', '2024-08-06', '06:34:35', 'Array', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 0, 'admin', 'Admin', '2024-08-06', '06:59:27', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 0, 'admin', 'Admin', '2024-08-06', '07:02:05', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 0, 'admin', 'Admin', '2024-08-06', '07:20:51', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 0, 'admin', 'Admin', '2024-08-06', '21:21:56', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(7, 0, 'admin', 'Admin', '2024-08-06', '21:22:39', NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 0, 'admin', 'Admin', '2024-08-06', '22:09:07', 'amplicin, hacv', 'Pending', '[\"5\",\"7\"]', 'amplicin, hacv', '2 x 3 daily, 2 x 4 daily', NULL, NULL, NULL),
(9, 0, 'admin', 'Admin', '2024-08-08', '09:14:56', 'amplicin', 'Pending', '[\"6\"]', 'amplicin', '2 x 3 daily', NULL, NULL, NULL),
(10, 12345, 'admin', 'Admin', '2024-08-08', '12:10:41', '[]', 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(11, 12345, 'admin', 'Admin', '2024-08-08', '12:27:10', '[\"amplicin\"]', 'Cancelled', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', NULL, NULL, NULL),
(12, 12345, 'admin', 'Admin', '2024-08-09', '00:48:35', NULL, 'In Progress', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 4 daily\"]', NULL, NULL, NULL),
(13, 12345, 'admin', 'Admin', '2024-08-10', '19:10:08', 'Array', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 12345, 'admin', 'Admin', '2024-08-10', '19:16:43', 'Array', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 12345, 'admin', 'Admin', '2024-08-10', '19:17:52', NULL, 'Pending', '[\"7\"]', '[\"hacv\"]', '[\"2 x 4 daily\"]', NULL, NULL, NULL),
(16, 8994251, 'admin', 'Admin', '2024-08-30', '08:00:28', NULL, 'In Progress', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 3 daily for 6 days\"]', NULL, NULL, NULL),
(17, 12345, 'admin', 'Admin', '2024-09-03', '13:59:21', NULL, 'Pending', '[\"6\"]', '[\"amplicin\"]', '[\"2 x4 daily for 8 days\"]', NULL, NULL, NULL),
(18, 12345, 'admin', 'Admin', '2024-09-03', '15:03:58', NULL, 'Cancelled', '[\"6\"]', '[\"amplicin\"]', '[\"4x 5 daily\"]', NULL, NULL, NULL),
(19, 8994251, 'admin', 'Admin', '2024-09-05', '15:33:11', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(20, 8994251, 'admin', 'Admin', '2024-09-05', '15:33:17', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(21, 12345, 'admin', 'Admin', '2024-09-26', '17:15:19', NULL, 'Pending', '[\"5\",\"7\",\"7\"]', '[\"amplicin\",\"hacv\",\"hacv\"]', '[\"2x 8 daily\",\"15 daily\",\"2 x 4 daily\"]', NULL, NULL, NULL),
(22, 12345, 'admin', 'Admin', '2024-09-26', '22:04:53', NULL, 'Pending', '[\"6\"]', '[\"amplicin\"]', '[\"7 daily\"]', NULL, NULL, NULL),
(23, 12345, 'admin', 'Admin', '2024-09-27', '00:01:59', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(24, 12345, 'admin', 'Admin', '2024-09-28', '15:49:26', NULL, 'In Progress', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 4 daily\"]', NULL, NULL, NULL),
(25, 12345, 'admin', 'Admin', '2024-09-28', '21:07:57', NULL, 'In Progress', '[\"5\"],6,7,7', '[\"amplicin\"],amplicin,hacv,hacv', '[\"2 x 4 daily\"],2 x 3 daily,2 x 4 daily,8 days monthly', 27, '0,6,6,6', ',Prescribed,Out of Stock,Out of Stock'),
(26, 12345, 'admin', 'Admin', '2024-09-29', '00:47:54', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(27, 12345, 'admin', 'Admin', '2024-09-29', '00:52:07', NULL, 'Completed', '5', 'amplicin', '2 x 4 daily', 28, '0', 'Prescribed'),
(28, 12345, 'admin', 'Admin', '2024-12-24', '22:51:28', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(29, 12345, 'admin', 'Admin', '2024-12-24', '23:42:48', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(30, 8108085, 'admin', 'Admin', '2024-12-29', '02:51:57', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(31, 12345, 'admin', 'Admin', '2025-01-03', '05:13:42', NULL, 'Pending', '[]', '[]', '[]', NULL, NULL, NULL),
(32, 12345, 'Mr. Kingsley  Ugonna Aguagwa', NULL, '2025-01-03', '05:27:13', NULL, 'Pending', '[\"6\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', NULL, NULL, NULL),
(33, 12345, 'Mr. Kingsley  Ugonna Aguagwa', 'Admin', '2025-01-03', '05:30:32', NULL, 'In Progress', '[\"5\"]', '[\"amplicin\"]', '[\"2 x 3 daily\"]', NULL, '0', ''),
(34, 12345, 'admin', 'Admin', '2025-01-05', '00:18:56', NULL, 'Pending', '[]', '[]', '[]', 39, NULL, NULL),
(35, 2246405, 'zid', 'Doctor', '2025-01-05', '17:54:53', NULL, 'Pending', '[]', '[]', '[]', 47, NULL, NULL),
(36, 8994251, 'zid', 'Doctor', '2025-01-06', '01:30:27', NULL, 'Pending', '[]', '[]', '[]', 48, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `message_id`, `reported_by`, `reason`, `created_at`, `reported_at`) VALUES
(2, 35, 1, 'User reported this message.', '2024-09-19 10:58:22', '2024-09-19 10:58:22'),
(3, 49, 1, 'User reported this message.', '2024-09-19 11:00:06', '2024-09-19 11:00:06'),
(4, 35, 1, 'User reported this message.', '2024-09-19 19:30:51', '2024-09-19 19:30:51');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `scheduleid` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `docid` int(11) DEFAULT NULL,
  `scheduledate` date DEFAULT NULL,
  `scheduletime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `session_messages`
--

CREATE TABLE `session_messages` (
  `id` int(11) NOT NULL,
  `meeting_id` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('sent','read') DEFAULT 'sent',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `session_messages`
--

INSERT INTO `session_messages` (`id`, `meeting_id`, `username`, `message`, `status`, `created_at`) VALUES
(1, 'bfd638c9029ec320', 'test', 'hello', 'sent', '2024-09-01 09:58:00'),
(2, 'bfd638c9029ec320', 'admin', 'how are you', 'read', '2024-09-01 09:58:35'),
(3, 'bfd638c9029ec320', 'test', 'what are you saying', 'sent', '2024-09-01 11:14:25'),
(4, 'bfd638c9029ec320', 'admin', 'is that ok', 'read', '2024-09-01 11:38:16'),
(5, 'bfd638c9029ec320', 'test', 'whats up', 'sent', '2024-09-01 11:38:45'),
(6, 'bfd638c9029ec320', 'test', 'ok', 'sent', '2024-09-01 11:46:21'),
(7, 'bfd638c9029ec320', 'test', 'rea', 'sent', '2024-09-01 11:48:01'),
(8, 'bfd638c9029ec320', 'test', 'ok', 'sent', '2024-09-01 13:42:21'),
(9, 'bfd638c9029ec320', 'admin', 'yea', 'read', '2024-09-01 14:08:41'),
(10, 'bfd638c9029ec320', 'test', 'hello', 'sent', '2024-09-01 17:34:48'),
(11, 'bfd638c9029ec320', 'test', 'hello', 'sent', '2024-09-01 18:37:20'),
(12, NULL, 'test', 'hello', 'sent', '2024-09-01 18:37:20'),
(13, NULL, 'admin', 'thts a', 'sent', '2024-09-01 18:39:46'),
(14, 'bfd638c9029ec320', 'admin', 'thts a', 'read', '2024-09-01 18:39:46'),
(15, 'bfd638c9029ec320', 'test', 'how', 'sent', '2024-09-02 11:29:13'),
(16, NULL, 'admin', 'helo', 'sent', '2024-09-02 21:27:48'),
(17, NULL, 'test', 'hek', 'sent', '2024-09-02 21:29:01'),
(18, NULL, 'test', 'how', 'sent', '2024-09-02 21:38:55'),
(19, NULL, 'test', 'itv', 'sent', '2024-09-02 21:39:13'),
(20, 'bfd638c9029ec320', 'test', 'ITV', 'sent', '2024-09-03 10:14:29'),
(21, 'bfd638c9029ec320', 'test', 'OK', 'sent', '2024-09-03 10:15:27'),
(22, 'bfd638c9029ec320', 'test', 'ok bye', 'sent', '2024-09-03 10:33:54'),
(23, 'bfd638c9029ec320', 'admin', 'nice one ', 'read', '2024-09-03 11:09:23'),
(24, 'bfd638c9029ec320', 'admin', 'ok', 'read', '2024-09-03 11:10:51'),
(25, 'bfd638c9029ec320', 'admin', 'this it', 'read', '2024-09-03 11:11:19'),
(26, 'bfd638c9029ec320', 'admin', 'u see', 'read', '2024-09-03 11:12:27'),
(27, 'bfd638c9029ec320', 'test', 'checkin', 'sent', '2024-09-03 11:15:15'),
(28, 'bfd638c9029ec320', 'test', 'the rest', 'sent', '2024-09-03 11:15:34'),
(29, 'bfd638c9029ec320', 'test', 'working fine ', 'sent', '2024-09-03 11:15:56'),
(30, 'bfd638c9029ec320', 'admin', 'thats fineby ne', 'read', '2024-09-03 11:21:59'),
(31, 'bfd638c9029ec320', 'test', 'checking', 'sent', '2024-09-03 11:29:48'),
(32, 'bfd638c9029ec320', 'admin', 'burna boy', 'read', '2024-09-03 12:10:35'),
(33, 'bfd638c9029ec320', 'admin', 'did is crazy', 'read', '2024-09-03 12:29:33'),
(34, 'bfd638c9029ec320', 'admin', 'how far', 'read', '2024-09-03 13:12:42'),
(35, '3ba275762f9722ca', 'ezeani', 'hello ', 'read', '2024-09-03 13:30:20'),
(36, '3ba275762f9722ca', 'admin', 'how can i help you', 'read', '2024-09-03 13:30:40'),
(37, '3ba275762f9722ca', 'ezeani', 'oluwa', 'read', '2024-09-03 17:07:52'),
(38, '3ba275762f9722ca', 'admin', 'love you', 'read', '2024-09-03 17:26:09'),
(39, '3ba275762f9722ca', 'admin', 'letrs see', 'read', '2024-09-03 17:30:39'),
(40, '3ba275762f9722ca', 'ezeani', 'are we online', 'read', '2024-09-03 17:31:33'),
(41, '3ba275762f9722ca', 'admin', 'yes', 'read', '2024-09-03 17:31:45'),
(42, '3ba275762f9722ca', 'ezeani', 'is tha so ', 'read', '2024-09-03 18:03:03'),
(43, '3ba275762f9722ca', 'ezeani', 'so how will i know', 'read', '2024-09-03 18:09:33'),
(44, '3ba275762f9722ca', 'admin', 'Just find out ', 'read', '2024-09-03 18:10:52'),
(45, '3ba275762f9722ca', 'admin', 'What\'s wrong ', 'read', '2024-09-03 18:19:46'),
(46, '3ba275762f9722ca', 'admin', 'We are getting there ', 'read', '2024-09-03 18:44:29'),
(47, '3ba275762f9722ca', 'admin', 'yes', 'read', '2024-09-03 18:44:57'),
(48, '3ba275762f9722ca', 'admin', 'babay i love you', 'read', '2024-09-03 19:56:09'),
(49, '3ba275762f9722ca', 'ezeani', 'ok', 'read', '2024-09-03 23:33:42'),
(50, '3ba275762f9722ca', 'admin', 'who sent', 'read', '2024-09-03 23:42:59'),
(51, '', '', 'hello', 'sent', '2024-09-05 14:34:48'),
(52, '', '', 'hello', 'sent', '2024-09-05 14:34:50'),
(53, '', '', 'hello', 'sent', '2024-09-05 14:34:51'),
(54, '', '', 'hello', 'sent', '2024-09-05 14:34:51'),
(55, '', '', 'hello', 'sent', '2024-09-05 14:34:51'),
(56, '', '', 'hello', 'sent', '2024-09-05 14:34:51'),
(57, '', '', 'hello', 'sent', '2024-09-05 14:34:51'),
(58, '', '', 'hello', 'sent', '2024-09-05 14:34:52'),
(59, '', '', 'hello', 'sent', '2024-09-05 14:34:52'),
(60, '', '', 'hello', 'sent', '2024-09-05 14:34:52'),
(61, '', '', 'hello', 'sent', '2024-09-05 14:34:52'),
(62, '', '', 'hello', 'sent', '2024-09-05 14:34:53'),
(63, '', '', 'hello', 'sent', '2024-09-05 14:34:54'),
(64, '', '', 'hello', 'sent', '2024-09-05 14:34:54'),
(65, '', '', 'hello', 'sent', '2024-09-05 14:34:55'),
(66, '', '', 'hello', 'sent', '2024-09-05 14:34:56'),
(67, '', '', 'hello', 'sent', '2024-09-05 14:34:56'),
(68, '', '', 'hello', 'sent', '2024-09-05 14:35:00'),
(69, '', '', 'hello', 'sent', '2024-09-05 14:35:00'),
(70, '', '', 'hello', 'sent', '2024-09-05 14:35:01'),
(71, '', '', 'hello', 'sent', '2024-09-05 14:35:01'),
(72, '', '', 'hello', 'sent', '2024-09-05 14:35:01'),
(73, '', '', 'hello', 'sent', '2024-09-05 14:35:01'),
(74, '', '', 'ok', 'sent', '2024-09-05 14:39:41'),
(75, '', '', 'ok', 'sent', '2024-09-05 14:39:43'),
(76, '', '', 'ok', 'sent', '2024-09-05 14:39:43'),
(77, '', '', 'ok', 'sent', '2024-09-05 14:39:43'),
(78, '', '', 'ok', 'sent', '2024-09-05 14:39:44'),
(79, '', '', 'ok', 'sent', '2024-09-05 14:39:44'),
(80, '', '', 'ok', 'sent', '2024-09-05 14:39:44'),
(81, '', '', 'ok', 'sent', '2024-09-05 14:39:44'),
(82, '', '', 'ok', 'sent', '2024-09-05 14:39:44'),
(83, '', '', 'ok', 'sent', '2024-09-05 14:39:45'),
(84, '', '', 'ok', 'sent', '2024-09-05 14:39:45'),
(85, '', '', 'ok', 'sent', '2024-09-05 14:39:46'),
(86, '', '', 'ok', 'sent', '2024-09-05 14:39:46'),
(87, '', '', 'ok', 'sent', '2024-09-05 14:39:46'),
(88, '', '', 'ok', 'sent', '2024-09-05 14:39:46'),
(89, '', '', 'ok', 'sent', '2024-09-05 14:39:46'),
(90, '', '', 'ok', 'sent', '2024-09-05 14:39:47'),
(91, '', '', 'ok', 'sent', '2024-09-05 14:39:47'),
(92, '3ba275762f9722ca', 'ezeani', 'ok', 'read', '2024-09-05 14:41:55'),
(93, '3ba275762f9722ca', 'ezeani', 'okik', 'read', '2024-09-05 14:42:09'),
(94, '3ba275762f9722ca', 'ezeani', 'ok', 'read', '2024-09-05 14:46:54'),
(95, '3ba275762f9722ca', 'admin', 'ok', 'read', '2024-09-05 14:53:20'),
(96, '3ba275762f9722ca', 'admin', 'ok', 'read', '2024-09-05 14:53:26'),
(97, '3ba275762f9722ca', 'admin', 'yes', 'read', '2024-09-05 14:53:43'),
(98, '3ba275762f9722ca', 'admin', 'wht happened ', 'read', '2024-09-05 14:57:14'),
(99, '3ba275762f9722ca', 'ezeani', 'ok', 'read', '2024-09-05 15:10:12'),
(100, '3ba275762f9722ca', 'admin', 'at least now im happy', 'read', '2024-09-05 15:11:26'),
(101, '3ba275762f9722ca', 'admin', 'are you sure ', 'read', '2024-09-05 15:11:57'),
(102, '3ba275762f9722ca', 'admin', 'chat for you', 'read', '2024-09-05 15:31:02'),
(103, '3ba275762f9722ca', 'test', 'he left', 'read', '2024-09-05 16:20:41'),
(104, '5e566b3dca1994f9', 'admin', 'hello', 'sent', '2024-12-24 22:34:16'),
(105, '5e566b3dca1994f9', 'admin', 'yes', 'sent', '2024-12-24 22:46:54'),
(106, '5e566b3dca1994f9', 'admin', 'yes', 'sent', '2024-12-24 22:50:56'),
(107, '3ba275762f9722ca', 'admin', 'hello', 'sent', '2024-12-24 22:51:49'),
(108, '3ba275762f9722ca', 'admin', 'ok', 'sent', '2024-12-24 23:30:32'),
(109, '3ba275762f9722ca', 'admin', 'how do we solve it', 'sent', '2024-12-24 23:31:06'),
(110, '30725b44f4282eb7', 'admin', 'hello', 'read', '2024-12-24 23:48:12'),
(111, '30725b44f4282eb7', 'admin', 'ok', 'read', '2024-12-24 23:54:53'),
(112, '26d332affaa8315e', 'test2', 'hello', 'read', '2024-12-25 07:43:06'),
(113, '26d332affaa8315e', 'admin', 'what are you doing', 'read', '2024-12-25 07:44:38'),
(114, '26d332affaa8315e', 'admin', 'just checking if this chat is workling', 'read', '2024-12-25 07:45:15'),
(115, '26d332affaa8315e', 'test2', 'ok it is working', 'read', '2024-12-25 07:45:40'),
(116, '0b74358ba3b04dc6', 'admin', 'hello', 'sent', '2025-01-03 13:11:57'),
(117, '6e5934b8c4328344', 'admin', 'Good evening Kingsley', 'read', '2025-01-03 17:18:33'),
(118, '6e5934b8c4328344', 'ezeani', 'good evening dr', 'read', '2025-01-03 17:31:42'),
(119, '6e5934b8c4328344', 'zid', 'how can i help you', 'read', '2025-01-03 17:32:25'),
(120, '6e5934b8c4328344', 'ezeani', 'im having a leg pain', 'read', '2025-01-03 17:33:52'),
(121, '6e5934b8c4328344', 'zid', 'since when did it start', 'read', '2025-01-03 17:34:08'),
(122, '6e5934b8c4328344', 'ezeani', 'last night when i fell off the bed', 'read', '2025-01-03 17:34:37'),
(123, '6e5934b8c4328344', 'zid', 'do you feel any  symptoms like nausea', 'read', '2025-01-03 17:35:59'),
(124, '6e5934b8c4328344', 'ezeani', 'not really but i  feel quite some pain', 'read', '2025-01-03 17:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `usergroup`
--

CREATE TABLE `usergroup` (
  `id` int(11) NOT NULL,
  `group_name` enum('Admin','User','IT','Doctor','Nurse','Lab Scientist','Pharmacist') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `registration_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `surname` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `usergroup` enum('Admin','User','IT','Doctor','Nurse','Lab Scientist','Pharmacist') NOT NULL DEFAULT 'User',
  `profile_pic` varchar(255) DEFAULT NULL,
  `online_status` tinyint(1) DEFAULT 0,
  `status` enum('Online','In a meeting','Busy','Attending to a patient') DEFAULT 'Online',
  `title` enum('Mr.','Mrs.','Dr.','Prof.') DEFAULT 'Mr.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`registration_id`, `first_name`, `middle_name`, `surname`, `date_of_birth`, `sex`, `username`, `password`, `email`, `phone_number`, `usergroup`, `profile_pic`, `online_status`, `status`, `title`) VALUES
(1, 'Kingsley ', 'Ugonna', 'Aguagwa', '1991-08-14', 'Male', 'admin', '$2y$10$jeMiXNGhYmO7wQzG/guVkewnCy9x5M0MTkXLbVvP6hnJdZgUn//5e', 'ugosonic@gmail.com', '07459943902', 'Admin', '/My Clinic/uploads/66f9581cc8889_My photo.jpg', 1, 'Online', 'Mr.'),
(4, 'John ', '', 'Smith', '2005-06-07', 'Male', 'nurse', '$2y$10$eydmlVdMshO.mDwH.neaEegd0IsiRHJu8gSBLqdBltkl9kq9G4XYG', 'nurse@gmail.com', '', 'Nurse', '', 1, 'Online', 'Mr.'),
(5, 'samuel', 'kin', 'lawn', '2017-02-24', 'Male', 'admin2', '$2y$10$SScce.iab1egxJ1dcHuW/.6OTK2MA6CmTkEBjJKM3dh9SWsML6sdy', 'admin2@gmail.com', '07459943902', 'Admin', '', 1, 'Online', 'Mr.'),
(6, 'zid', '', 'zick', '2024-12-31', 'Female', 'zid', '$2y$10$CBSJ5tZ.pM6jEmRff.swCuALdtA/bgRkGV4mLoEWwSWiE8AF0hEWm', 'zid@gmail.com', '0789548994', 'Doctor', '', 1, 'Online', 'Mr.');

-- --------------------------------------------------------

--
-- Table structure for table `vital_signs`
--

CREATE TABLE `vital_signs` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_age` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(255) NOT NULL,
  `blood_pressure_systolic` int(11) NOT NULL,
  `blood_pressure_diastolic` int(11) NOT NULL,
  `breathing` int(11) NOT NULL,
  `pulse_rate` int(11) NOT NULL,
  `temperature` float NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waiting_room`
--

CREATE TABLE `waiting_room` (
  `waiting_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `waiting_room` enum('Nurse','Doctor','Laboratory','Pharmacy') NOT NULL,
  `check_in_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `check_out_time` timestamp NULL DEFAULT NULL,
  `staff_name` varchar(255) DEFAULT NULL,
  `status` enum('Waiting','Accepted','Cancelled') DEFAULT 'Waiting',
  `from_source` enum('Appointment','Staff') DEFAULT 'Staff',
  `appointment_type` enum('Online','In-clinic') DEFAULT 'In-clinic',
  `notes` text DEFAULT NULL,
  `priority` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `waiting_room`
--

INSERT INTO `waiting_room` (`waiting_id`, `patient_id`, `waiting_room`, `check_in_time`, `check_out_time`, `staff_name`, `status`, `from_source`, `appointment_type`, `notes`, `priority`) VALUES
(24, 0, 'Nurse', '2024-06-23 08:54:40', '2024-06-23 09:01:15', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(25, 0, 'Nurse', '2024-06-23 08:54:47', '2024-06-23 09:57:49', 'nurse', 'Accepted', 'Staff', 'In-clinic', NULL, 0),
(26, 0, 'Nurse', '2024-06-23 08:54:52', '2024-07-10 12:35:16', 'admin', 'Accepted', 'Staff', 'In-clinic', NULL, 0),
(27, 0, 'Nurse', '2024-06-23 08:56:08', '2024-06-23 09:58:17', 'nurse', 'Accepted', 'Staff', 'In-clinic', NULL, 0),
(28, 0, 'Nurse', '2024-06-23 08:56:14', '2024-06-24 07:22:13', 'nurse', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(29, 0, 'Nurse', '2024-06-23 08:56:16', '2024-06-24 07:22:19', 'nurse', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(30, 0, 'Doctor', '2024-06-23 08:57:33', '2024-07-10 12:34:38', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(31, 0, 'Doctor', '2024-06-23 09:04:23', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(32, 0, 'Doctor', '2024-06-23 09:09:53', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(33, 0, 'Doctor', '2024-06-23 09:14:26', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(34, 12345, 'Nurse', '2024-07-27 21:53:15', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(35, 12345, 'Nurse', '2024-07-28 04:29:47', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(36, 12345, 'Doctor', '2024-07-28 07:04:47', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(37, 12345, 'Laboratory', '2024-07-28 10:51:18', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(39, 12345, 'Nurse', '2024-07-29 12:50:48', '2024-07-29 12:51:03', 'admin', 'Accepted', 'Staff', 'In-clinic', NULL, 0),
(40, 8994251, 'Nurse', '2024-08-04 17:26:42', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(41, 8994251, 'Laboratory', '2024-09-05 20:19:34', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(42, 12345, 'Doctor', '2024-09-08 07:07:21', '2024-09-08 07:09:19', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(43, 12345, 'Nurse', '2024-09-08 14:17:50', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(44, 8108085, 'Laboratory', '2024-09-08 14:18:09', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(45, 8994251, 'Pharmacy', '2024-09-08 14:52:03', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(46, 8994251, 'Doctor', '2024-09-08 14:52:37', '2024-09-08 14:56:19', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(47, 8994251, 'Nurse', '2024-09-09 12:47:49', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(48, 8108085, 'Laboratory', '2024-09-09 12:48:10', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(49, 8108085, 'Pharmacy', '2024-09-09 12:48:19', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(50, 0, 'Nurse', '2024-09-09 12:48:37', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(51, 12345, 'Doctor', '2024-09-09 12:49:39', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(52, 8994251, 'Nurse', '2024-09-20 18:39:33', '2024-09-20 18:41:34', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(53, 8994251, 'Doctor', '2024-09-20 18:39:38', '2024-09-20 18:41:42', 'admin', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(54, 8994251, 'Laboratory', '2024-09-20 18:39:46', '2024-09-20 18:42:04', 'admin', 'Accepted', 'Staff', 'In-clinic', NULL, 0),
(55, 12345, 'Doctor', '2024-12-28 10:45:30', '2024-12-29 00:13:57', NULL, 'Accepted', 'Staff', 'In-clinic', 'ok', 0),
(56, 8994251, 'Doctor', '2024-12-28 17:27:21', NULL, 'From Appointment', 'Waiting', 'Appointment', 'Online', '', 1),
(57, 2246405, 'Doctor', '2024-12-28 20:25:24', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', 'OK', 0),
(58, 2246405, 'Doctor', '2024-12-29 00:42:03', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(59, 12345, 'Doctor', '2024-12-29 01:02:36', '2024-12-29 01:23:22', 'zid', 'Cancelled', 'Appointment', 'Online', NULL, 0),
(60, 12345, 'Doctor', '2024-12-29 01:23:26', '2024-12-29 01:23:45', 'zid', 'Cancelled', 'Appointment', 'Online', NULL, 0),
(61, 12345, 'Doctor', '2024-12-29 01:23:48', '2024-12-29 01:25:15', 'zid', 'Cancelled', 'Appointment', 'Online', NULL, 0),
(62, 12345, 'Doctor', '2024-12-29 01:25:28', NULL, 'From Appointment', 'Waiting', 'Appointment', 'Online', NULL, 0),
(64, 25348, 'Doctor', '2025-01-03 06:02:08', '2025-01-03 06:05:56', 'zid', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(65, 12345, 'Doctor', '2025-01-03 06:03:00', '2025-01-03 06:04:57', 'zid', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(66, 8994251, 'Doctor', '2025-01-03 06:03:25', '2025-01-03 06:05:13', 'zid', 'Cancelled', 'Staff', 'In-clinic', NULL, 0),
(67, 12345, 'Doctor', '2025-01-03 07:36:10', '2025-01-03 17:59:21', 'From Appointment', 'Accepted', 'Appointment', 'Online', NULL, 0),
(68, 8994251, 'Laboratory', '2025-01-03 16:27:26', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(69, 2246405, 'Pharmacy', '2025-01-03 16:27:45', NULL, NULL, 'Waiting', 'Staff', 'In-clinic', NULL, 0),
(70, 25348, 'Doctor', '2025-01-03 17:29:18', '2025-01-03 17:30:31', 'From Appointment', 'Accepted', 'Appointment', 'Online', NULL, 0),
(71, 25348, 'Doctor', '2025-01-03 17:38:22', '2025-01-03 18:06:01', 'From Appointment', 'Accepted', 'Appointment', 'Online', NULL, 0),
(72, 12345, 'Doctor', '2025-01-03 23:36:28', NULL, 'From Appointment', 'Waiting', 'Appointment', 'Online', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `appointment_availability`
--
ALTER TABLE `appointment_availability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `canceled_appointments`
--
ALTER TABLE `canceled_appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`consultation_id`);

--
-- Indexes for table `consultation_updates`
--
ALTER TABLE `consultation_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consultation_id` (`consultation_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_requests`
--
ALTER TABLE `lab_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_test_templates`
--
ALTER TABLE `lab_test_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `meetings`
--
ALTER TABLE `meetings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `patient_db`
--
ALTER TABLE `patient_db`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`),
  ADD KEY `reported_by` (`reported_by`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`scheduleid`);

--
-- Indexes for table `session_messages`
--
ALTER TABLE `session_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usergroup`
--
ALTER TABLE `usergroup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `waiting_room`
--
ALTER TABLE `waiting_room`
  ADD PRIMARY KEY (`waiting_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `appointment_availability`
--
ALTER TABLE `appointment_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `canceled_appointments`
--
ALTER TABLE `canceled_appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `consultation_updates`
--
ALTER TABLE `consultation_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lab_requests`
--
ALTER TABLE `lab_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_test_templates`
--
ALTER TABLE `lab_test_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `meetings`
--
ALTER TABLE `meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `patient_db`
--
ALTER TABLE `patient_db`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `scheduleid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_messages`
--
ALTER TABLE `session_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `usergroup`
--
ALTER TABLE `usergroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vital_signs`
--
ALTER TABLE `vital_signs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waiting_room`
--
ALTER TABLE `waiting_room`
  MODIFY `waiting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultation_updates`
--
ALTER TABLE `consultation_updates`
  ADD CONSTRAINT `consultation_updates_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE CASCADE;

--
-- Constraints for table `lab_requests`
--
ALTER TABLE `lab_requests`
  ADD CONSTRAINT `lab_requests_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `lab_test_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`registration_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`registration_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`registration_id`) ON DELETE CASCADE;

--
-- Constraints for table `vital_signs`
--
ALTER TABLE `vital_signs`
  ADD CONSTRAINT `vital_signs_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_db` (`id`),
  ADD CONSTRAINT `vital_signs_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`registration_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

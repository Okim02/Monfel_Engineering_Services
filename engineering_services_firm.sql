-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2026 at 01:27 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `engineering_services_firm`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','leave') NOT NULL DEFAULT 'present',
  `leave_notified_at` datetime DEFAULT NULL,
  `late_minutes` decimal(7,2) NOT NULL DEFAULT '0.00',
  `late_deduction_hours` decimal(7,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(7,2) NOT NULL DEFAULT '0.00',
  `overtime_type` enum('regular','rest_day','holiday') NOT NULL DEFAULT 'regular',
  `overtime_approved` tinyint(1) NOT NULL DEFAULT '0',
  `overtime_approved_by` int(10) UNSIGNED DEFAULT NULL,
  `overtime_approved_at` datetime DEFAULT NULL,
  `undertime_hours` decimal(7,2) NOT NULL DEFAULT '0.00',
  `undertime_deduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `project_id`, `work_date`, `time_in`, `time_out`, `status`, `leave_notified_at`, `late_minutes`, `late_deduction_hours`, `overtime_hours`, `overtime_type`, `overtime_approved`, `overtime_approved_by`, `overtime_approved_at`, `undertime_hours`, `undertime_deduction`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-09-28', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '1.00', 'regular', 1, 5, '2026-10-04 13:12:41', '0.00', '0.00', NULL, 5, '2026-10-04 13:12:41', NULL),
(2, 1, 1, '2026-10-19', '08:00:00', '20:00:00', 'present', NULL, '0.00', '0.00', '3.00', 'regular', 1, 5, '2026-10-22 09:37:50', '0.00', '0.00', NULL, 5, '2026-10-22 09:29:17', '2026-10-22 09:37:50'),
(3, 2, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:29:45', '2026-10-22 09:53:08'),
(4, 3, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:30:20', NULL),
(5, 4, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:30:50', NULL),
(6, 5, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:31:34', NULL),
(7, 6, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:32:15', NULL),
(8, 7, 1, '2026-10-19', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:32:45', NULL),
(9, 8, 1, '2026-10-19', '08:00:00', '16:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:33:24', NULL),
(10, 9, 1, '2026-10-19', '09:00:00', '16:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '2.00', '0.00', NULL, 5, '2026-10-22 09:33:48', '2026-10-22 09:36:07'),
(11, 10, 1, '2026-10-19', NULL, NULL, 'absent', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:34:27', NULL),
(12, 1, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:48:49', '2026-10-22 09:51:50'),
(14, 2, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:54:49', NULL),
(15, 3, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:55:14', NULL),
(16, 4, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:55:34', NULL),
(17, 5, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:55:58', NULL),
(18, 6, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:56:17', NULL),
(19, 7, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:56:41', NULL),
(20, 8, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:57:35', NULL),
(21, 9, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:57:48', NULL),
(22, 10, 1, '2026-10-20', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 09:58:04', NULL),
(23, 1, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:00:36', NULL),
(24, 2, 1, '2026-10-21', '09:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '1.00', '0.00', NULL, 5, '2026-10-22 10:01:06', NULL),
(25, 3, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:01:24', NULL),
(26, 4, 1, '2026-10-21', '10:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '2.00', '0.00', NULL, 5, '2026-10-22 10:01:49', NULL),
(27, 5, 1, '2026-10-21', '08:00:00', '20:00:00', 'present', NULL, '0.00', '0.00', '3.00', 'regular', 1, 5, '2026-10-22 10:05:21', '0.00', '0.00', NULL, 5, '2026-10-22 10:02:37', '2026-10-22 10:05:21'),
(28, 6, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:03:53', NULL),
(29, 7, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:04:11', NULL),
(30, 8, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:04:39', NULL),
(31, 9, 1, '2026-10-21', '08:00:00', '19:00:00', 'present', NULL, '0.00', '0.00', '2.00', 'regular', 1, 5, '2026-10-22 10:06:37', '0.00', '0.00', NULL, 5, '2026-10-22 10:06:37', NULL),
(32, 10, 1, '2026-10-21', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 10:07:01', NULL),
(33, 1, 1, '2026-10-22', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 14:47:18', NULL),
(34, 11, 1, '2026-10-22', '08:00:00', '17:00:00', 'present', NULL, '0.00', '0.00', '0.00', 'regular', 0, NULL, NULL, '0.00', '0.00', NULL, 5, '2026-10-22 14:50:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance_authorizations`
--

CREATE TABLE `attendance_authorizations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `authorized_by` int(10) UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `authorized_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_advances`
--

CREATE TABLE `cash_advances` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `advance_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `amount_deducted` decimal(12,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) DEFAULT NULL,
  `status` enum('open','settled','void') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_code` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `position_id` int(10) UNSIGNED DEFAULT NULL,
  `salary_grade_id` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_code`, `full_name`, `position_id`, `salary_grade_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'EMP-001', 'Emie Jane Ocon', 1, 2, 1, '2026-10-04 13:10:27', NULL),
(2, 'EMP-002', 'Abraham Clint Larry Bontia', 2, 2, 1, '2026-10-22 07:56:39', NULL),
(3, 'EMP-003', 'Rafael Latada jr.', 4, 6, 1, '2026-10-22 07:57:26', NULL),
(4, 'EMP-004', 'Ronnie Geronca', 5, 7, 1, '2026-10-22 07:58:55', NULL),
(5, 'EMP-005', 'William Zabala', 9, 1, 1, '2026-10-22 07:59:38', NULL),
(6, 'EMP-006', 'Concordio Doyugan jr.', 5, 6, 1, '2026-10-22 08:00:48', NULL),
(7, 'EMP-007', 'Mickoy Milistone', 6, 4, 1, '2026-10-22 08:01:17', NULL),
(8, 'EMP-008', 'Reymon Geronimo', 7, 7, 1, '2026-10-22 08:02:11', NULL),
(9, 'EMP-009', 'Nielboy Bermejo', 7, 7, 1, '2026-10-22 08:02:52', NULL),
(10, 'EMP-010', 'Jennifer Cuerbo', 6, 5, 1, '2026-10-22 08:03:31', NULL),
(11, 'EMP-011', 'Miko Vallejos', 9, 1, 1, '2026-10-22 14:49:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_project_assignments`
--

CREATE TABLE `employee_project_assignments` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `project_id` int(10) UNSIGNED NOT NULL,
  `assigned_from` date NOT NULL,
  `assigned_to` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `employee_project_assignments`
--

INSERT INTO `employee_project_assignments` (`id`, `employee_id`, `project_id`, `assigned_from`, `assigned_to`, `created_at`) VALUES
(1, 1, 1, '2026-10-04', NULL, '2026-10-04 13:10:27'),
(2, 2, 1, '2026-10-22', NULL, '2026-10-22 07:56:39'),
(3, 3, 1, '2026-10-22', NULL, '2026-10-22 07:57:26'),
(4, 4, 1, '2026-10-22', NULL, '2026-10-22 07:58:55'),
(5, 5, 1, '2026-10-22', NULL, '2026-10-22 07:59:38'),
(6, 6, 1, '2026-10-22', NULL, '2026-10-22 08:00:48'),
(7, 7, 1, '2026-10-22', NULL, '2026-10-22 08:01:17'),
(8, 8, 1, '2026-10-22', NULL, '2026-10-22 08:02:11'),
(9, 9, 1, '2026-10-22', NULL, '2026-10-22 08:02:52'),
(10, 10, 1, '2026-10-22', NULL, '2026-10-22 08:03:31'),
(11, 11, 1, '2026-10-22', NULL, '2026-10-22 14:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_adjustments`
--

CREATE TABLE `payroll_adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_item_id` int(10) UNSIGNED NOT NULL,
  `adjustment_type` enum('addition','deduction') NOT NULL,
  `label` varchar(100) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

CREATE TABLE `payroll_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `payroll_period_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `employee_code_snapshot` varchar(20) NOT NULL,
  `employee_name_snapshot` varchar(150) NOT NULL,
  `position_snapshot` varchar(80) DEFAULT NULL,
  `days_worked` decimal(6,2) NOT NULL DEFAULT '0.00',
  `daily_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(7,2) NOT NULL DEFAULT '0.00',
  `overtime_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `basic_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `overtime_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `additions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cash_advance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `store_debt` decimal(12,2) NOT NULL DEFAULT '0.00',
  `undertime` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gross_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

CREATE TABLE `payroll_periods` (
  `id` int(10) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` enum('draft','finalized') NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED NOT NULL,
  `finalized_by` int(10) UNSIGNED DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `active`, `created_at`) VALUES
(1, 'Timekeeper', 1, '2026-09-07 22:38:42'),
(2, 'Toolkeeper', 1, '2026-09-07 22:38:42'),
(3, 'Expediter', 1, '2026-09-07 22:38:42'),
(4, 'Foreman', 1, '2026-09-07 22:38:42'),
(5, 'Leadman', 1, '2026-09-07 22:38:42'),
(6, 'Masonry', 1, '2026-09-07 22:38:42'),
(7, 'Welder', 1, '2026-09-07 22:38:42'),
(8, 'Skilled', 1, '2026-09-07 22:38:42'),
(9, 'Labor', 1, '2026-09-07 22:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `location`, `active`, `created_at`) VALUES
(1, 'Cagayan de Oro City', 'CDO- Alae', 1, '2026-09-22 20:16:58'),
(2, 'Lingion Site', 'Bukidnon, Manolo', 1, '2026-10-22 08:04:59'),
(3, 'Davao site', 'Davao', 1, '2026-10-22 08:05:18'),
(4, 'Milling site', 'Luzon, Isabela', 1, '2026-10-22 08:05:40');

-- --------------------------------------------------------

--
-- Table structure for table `salary_grades`
--

CREATE TABLE `salary_grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `daily_rate` decimal(12,2) NOT NULL,
  `overtime_rate` decimal(12,2) NOT NULL,
  `undertime_rate` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `salary_grades`
--

INSERT INTO `salary_grades` (`id`, `code`, `name`, `daily_rate`, `overtime_rate`, `undertime_rate`, `active`, `created_at`) VALUES
(1, 'SG-470', 'Salary Grade 470', '470.00', '65.00', '58.75', 1, '2026-09-07 22:38:42'),
(2, 'SG-500', 'Salary Grade 500', '500.00', '70.00', '62.50', 1, '2026-09-07 22:38:42'),
(3, 'SG-550', 'Salary Grade 550', '550.00', '77.00', '68.75', 1, '2026-09-07 22:38:42'),
(4, 'SG-600', 'Salary Grade 600', '600.00', '85.00', '75.00', 1, '2026-09-07 22:38:42'),
(5, 'SG-650', 'Salary Grade 650', '650.00', '91.00', '81.25', 1, '2026-09-07 22:38:42'),
(6, 'SG-700', 'Salary Grade 700', '700.00', '98.00', '87.50', 1, '2026-09-07 22:38:42'),
(7, 'SG-750', 'Salary Grade 750', '750.00', '105.00', '93.75', 1, '2026-09-07 22:38:42'),
(8, 'SG-800', 'Salary Grade 800', '800.00', '115.00', '100.00', 1, '2026-09-07 22:38:42'),
(9, 'SG-850', 'Salary Grade 850', '850.00', '120.00', '106.25', 1, '2026-09-07 22:38:42'),
(10, 'SG-900', 'Salary Grade 900', '900.00', '130.00', '112.50', 1, '2026-09-07 22:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('afternoon_break_end', '', 'TODO: Confirm the afternoon break duration; client response was 3:00 to 3.'),
('afternoon_break_start', '', 'TODO: Confirm the afternoon break schedule.'),
('cash_advance_repayment_method', '', 'TODO: Confirm whether repayment is one-time or installment-based.'),
('late_rounding_rule', '', 'TODO: Confirm the complete late deduction rounding rule.'),
('lunch_break_end', '13:00:00', 'Confirmed lunch break end.'),
('lunch_break_start', '12:00:00', 'Confirmed lunch break start.'),
('morning_break_end', '10:30:00', 'Confirmed morning break end.'),
('morning_break_start', '10:00:00', 'Confirmed morning break start.'),
('rest_holiday_overtime_multiplier', '1.30', 'Confirmed 30% premium for rest days and holidays.'),
('store_debt_repayment_method', '', 'TODO: Confirm whether repayment is one-time or installment-based.'),
('undertime_formula', 'hours_times_undertime_rate', 'Undertime deduction is proportional to recorded undertime hours.'),
('work_end_time', '17:00:00', 'Confirmed normal work end time.'),
('work_start_time', '08:00:00', 'Confirmed normal work start time.');

-- --------------------------------------------------------

--
-- Table structure for table `store_debts`
--

CREATE TABLE `store_debts` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `debt_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `amount_deducted` decimal(12,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) DEFAULT NULL,
  `status` enum('open','settled','void') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `store_debts`
--

INSERT INTO `store_debts` (`id`, `employee_id`, `debt_date`, `amount`, `amount_deducted`, `description`, `status`, `created_at`) VALUES
(2, 6, '2026-10-21', '100.00', '0.00', 'Attendance record #28', 'open', '2026-10-22 10:03:53');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('owner','timekeeper','worker') NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `full_name`, `email`, `password_hash`, `role`, `active`, `created_at`) VALUES
(1, NULL, 'Miko Vallejos', 'ikosential02@gmail.com', '$2y$10$p2ftG4vYgIMCt.Fd6onMf.IU.78bPEa5LZZmE2NAstB5b7gwd8TEW', 'owner', 1, '2026-09-07 22:50:22'),
(4, NULL, 'Miko Vallejos', '2082886@g.cu.edu.ph', '$2y$10$jRn/nme8hz6BIgmCjqZvD.aKBYjWsXNOUb3eYhjeh3PH5Z8bJlFli', 'worker', 1, '2026-09-20 15:00:54'),
(5, NULL, 'Ikosential', 'mikovallejos@gmail.com', '$2y$10$z1.fKDXSEspYY9TpqpUYFO9enAUArOdGfqdKPmSFNb2/0mGZZvhxK', 'owner', 1, '2026-09-30 22:38:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_employee_date_project` (`employee_id`,`work_date`,`project_id`),
  ADD KEY `overtime_approved_by` (`overtime_approved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_attendance_date` (`work_date`),
  ADD KEY `idx_attendance_employee_date` (`employee_id`,`work_date`),
  ADD KEY `idx_attendance_project` (`project_id`);

--
-- Indexes for table `attendance_authorizations`
--
ALTER TABLE `attendance_authorizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_authorization_user` (`user_id`),
  ADD KEY `authorized_by` (`authorized_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `cash_advances`
--
ALTER TABLE `cash_advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cash_advances_employee_status` (`employee_id`,`status`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `salary_grade_id` (`salary_grade_id`),
  ADD KEY `idx_employees_name` (`full_name`);

--
-- Indexes for table `employee_project_assignments`
--
ALTER TABLE `employee_project_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `idx_assignments_employee_project` (`employee_id`,`project_id`),
  ADD KEY `idx_assignments_dates` (`assigned_from`,`assigned_to`);

--
-- Indexes for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_item_id` (`payroll_item_id`);

--
-- Indexes for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payroll_item_employee_period` (`payroll_period_id`,`employee_id`),
  ADD KEY `fk_payroll_items_employee_cascade` (`employee_id`);

--
-- Indexes for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payroll_period` (`period_start`,`period_end`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `finalized_by` (`finalized_by`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_projects_name` (`name`);

--
-- Indexes for table `salary_grades`
--
ALTER TABLE `salary_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `store_debts`
--
ALTER TABLE `store_debts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_store_debts_employee_status` (`employee_id`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `attendance_authorizations`
--
ALTER TABLE `attendance_authorizations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_advances`
--
ALTER TABLE `cash_advances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `employee_project_assignments`
--
ALTER TABLE `employee_project_assignments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_items`
--
ALTER TABLE `payroll_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_grades`
--
ALTER TABLE `salary_grades`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `store_debts`
--
ALTER TABLE `store_debts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`overtime_approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_attendance_employee_cascade` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_authorizations`
--
ALTER TABLE `attendance_authorizations`
  ADD CONSTRAINT `attendance_authorizations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_authorizations_ibfk_2` FOREIGN KEY (`authorized_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cash_advances`
--
ALTER TABLE `cash_advances`
  ADD CONSTRAINT `fk_cash_advances_employee_cascade` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`id`);

--
-- Constraints for table `employee_project_assignments`
--
ALTER TABLE `employee_project_assignments`
  ADD CONSTRAINT `employee_project_assignments_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `fk_assignments_employee_cascade` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_adjustments`
--
ALTER TABLE `payroll_adjustments`
  ADD CONSTRAINT `payroll_adjustments_ibfk_1` FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`);

--
-- Constraints for table `payroll_items`
--
ALTER TABLE `payroll_items`
  ADD CONSTRAINT `fk_payroll_items_employee_cascade` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_items_ibfk_1` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`);

--
-- Constraints for table `payroll_periods`
--
ALTER TABLE `payroll_periods`
  ADD CONSTRAINT `payroll_periods_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payroll_periods_ibfk_2` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `store_debts`
--
ALTER TABLE `store_debts`
  ADD CONSTRAINT `fk_store_debts_employee_cascade` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee_set_null` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

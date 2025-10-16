-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 20, 2025 at 02:05 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anaamworld`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admins/default.png',
  `added_by` int DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` enum('admin','partner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `user_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `password`, `email`, `phone`, `image`, `added_by`, `remember_token`, `deleted_at`, `created_at`, `updated_at`, `type`, `user_id`) VALUES
(1, 'Super Admin', '$2y$10$qI76UjBmQDIKuBWnrvcaVe.1qe6OudHuTxyct6iiyukYdB39minR2', 'admin@demo.com', '01127272727', 'admins/xwDDy61KoFEvcv8gN4gdertBAexGPwF5VFUDYCmT.png', NULL, 'ZjY3HXNyqEEaLzujvOULjU7cyiRNGJNWrTf1gXpY8RHaL3ZhKMXy3hJGGiQd', NULL, '2024-02-13 17:54:31', '2024-02-25 15:01:00', 'admin', NULL),
(2, 'Hedy Bartlett', '$2y$10$XlLweGRqSfqv8coKubg6Wu/sGM9q9IIVcC5djHllu84zpCXFPa6o2', 'wetubuh@mailinator.com', '+1 (334) 974-1233', 'admins/M5KMKrXC8GRdAoBLgLAhFK9xKm3vTZ3nUhi8tkdh.png', NULL, NULL, NULL, '2025-05-11 10:13:58', '2025-05-11 10:13:58', 'admin', NULL),
(3, 'Anika Wells', '$2y$10$xqOT/Wk5WDQ3Ie.a.vis6uIci9M/9uuVdCkOOcx/oS9OA9AeOjPBy', 'zejymez@mailinator.com', '23452345', 'admins/VbFIBMmwiXT9jnPxCQGmcbwhvhXq29efRDTIPqLV.png', NULL, 'enmtFtiMODJBL9cQxAjiD8Qg9QLFqncZI9DsXMkjrDfhc3kRx5Oqn1SvCkPI', NULL, '2025-05-11 10:14:21', '2025-07-27 10:40:31', 'partner', NULL),
(4, 'Conan Delaney', '$2y$10$DkfzfjmiL0K8YfnqzsE6a.hp4zctJIwQSmS6NaKqsiVJxWru7Kn0q', 'jupubeneqy@mailinator.com', '+1 (311) 518-8237', 'admins/default.png', NULL, '7cTUZjfqldY6evo6ClNXWOP4gJJyst8BEPqTPtcteYcEPZbIlmwvKpFaWGqV', NULL, '2025-05-11 11:25:43', '2025-05-11 13:05:57', 'partner', NULL),
(5, 'Matrix Clouds', '$2y$10$8PBmzIY5fwCHOcEds7rFAehKT.nXVYjzoXLQPRVQ8k810QPegFpRi', 'matrixclouds2@gmail.com', '1011609366', 'admins/N06sH4m0tlyVDLKlzu7F8LUh8ygmdI71uu4ZxJ57.jpg', NULL, '1ITjEVjfAos4NLhA7OuyRJJOj2camLFmWODj0eDj6M5f7UrPl5KhQJaUgSqX', NULL, '2025-05-12 10:53:21', '2025-05-12 10:53:21', 'partner', NULL),
(6, 'Aaron Austin', '$2y$10$MSNZRqEd0l9Ee0nYUb3zQ.TYcQRmLbsxFao02rGiPm7gOSETOMMcC', 'pukozaq@mailinator.com', '+1 (266) 312-9444', 'admins/default.png', NULL, 'eeKg5EjkFFddnswSpKyHPCgvkKmXepTwqw24S3gV0Hgt5xGP2lI10nSAdX6Z', NULL, '2025-07-13 08:19:45', '2025-07-13 08:19:45', 'partner', NULL),
(7, 'ahmed mohamed @ sdfsd', '$2y$10$.kCNa4ZCrVLenaep./Hppe68NjuAlFxQwgI0Po2gaBd/etnXJzSWa', 'eslam@gmail.com2234', '234234', 'admins/default.png', NULL, NULL, NULL, '2025-08-14 14:10:23', '2025-08-14 14:10:23', 'partner', 11),
(8, 'Lilah Dudley', '$2y$10$ypY6rlmqVX8g9m3EeI7lXuV1Zarmaepa6.lgTRpCCU8Owds5.WMxK', 'geqoqekix@mailinator.com', '+1 (413) 712-4683', 'admins/default.png', NULL, NULL, NULL, '2025-08-17 14:50:36', '2025-08-17 14:50:36', 'partner', NULL),
(9, 'Jonas Jacobson', '$2y$10$Ivt9zemCPbkg0.xaZR8ZgOOby6/6XcnT/A3SjEqthLEWvKL5xNhoC', 'rijezys@mailinator.com', '+1 (683) 129-5978', 'admins/default.png', NULL, NULL, NULL, '2025-08-17 14:55:32', '2025-08-17 14:55:32', 'partner', NULL),
(10, 'Iris Martinez', '$2y$10$UHFXYvcVxpnbj8EXE9AzE.kuEyAegPnueTG.7ce0uF58z9eqGsHsa', 'gaqicyme@mailinator.com', '+1 (349) 193-4437', 'admins/default.png', NULL, NULL, NULL, '2025-08-17 14:56:33', '2025-08-17 14:56:33', 'partner', NULL),
(11, 'Lilah Battle', '$2y$10$MZqlITjuVy2mrv7A8xPFU.wD7z5roONy95evXwpvizM64Xa0yJ90a', 'cixyha@mailinator.com', '+1 (675) 805-5594', 'admins/default.png', NULL, NULL, NULL, '2025-08-17 14:57:10', '2025-08-17 14:57:10', 'partner', 17);

-- --------------------------------------------------------

--
-- Table structure for table `ages`
--

CREATE TABLE `ages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ages`
--

INSERT INTO `ages` (`id`, `name`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `admin_id`) VALUES
(1, '{\"ar\": \"سيبيبلسيب\", \"en\": \"wafwerwe\"}', 0, NULL, '2025-05-05 10:17:00', '2025-05-05 10:17:06', NULL),
(2, '{\"ar\": \"سيبيبلسيب\", \"en\": \"wafwerwe\"}', 1, NULL, '2025-05-05 10:18:53', '2025-05-05 10:18:53', NULL),
(3, '{\"ar\": \"Raphael Grimes\", \"en\": \"Lucas Snider\"}', 0, NULL, '2025-05-11 12:42:15', '2025-05-11 12:42:15', 4);

-- --------------------------------------------------------

--
-- Table structure for table `animal_pens`
--

CREATE TABLE `animal_pens` (
  `id` bigint UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `animal_pens`
--

INSERT INTO `animal_pens` (`id`, `name`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `admin_id`) VALUES
(1, '{\"ar\": \"شسبشيسبشسيب\", \"en\": \"asdfasdfasda\"}', 1, NULL, '2025-05-05 10:39:16', '2025-05-05 10:40:33', NULL),
(2, '{\"ar\": \"Damon Rosa\", \"en\": \"Charissa Tucker\"}', 1, NULL, '2025-05-11 12:45:01', '2025-05-11 12:45:01', 4);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `admin_id`) VALUES
(1, '{\"en\":\"ertretertr\",\"ar\":\"\\u0634\\u0633\\u064a\\u0628\\u0634\\u0633\\u064a\\u0628\\u064a\\u0633\\u0628\\u0633\\u064a\\u0628\\u064a\\u0633\"}', 1, '2025-02-02 12:31:01', '2025-02-02 12:26:16', '2025-02-02 12:31:01', NULL),
(2, '{\"en\":\"wafwerwe\",\"ar\":\"\\u0633\\u064a\\u0628\\u064a\\u0628\\u0644\\u0633\\u064a\\u0628\"}', 1, NULL, '2025-02-02 12:31:16', '2025-02-02 12:31:16', NULL),
(3, '{\"en\":\"asdf\",\"ar\":\"asdfasdf\"}', 1, NULL, '2025-05-04 11:10:26', '2025-05-04 11:10:26', NULL),
(4, '{\"en\":\"Reese Sharpe\",\"ar\":\"Illana Dawson\"}', 1, NULL, '2025-05-11 12:30:10', '2025-05-11 12:30:10', 4);

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `admin_id`) VALUES
(1, '{\"ar\":\"\\u0627\\u0644\\u0631\\u064a\\u0627\\u0636\",\"en\":\"Riyadh\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(2, '{\"ar\":\"\\u062c\\u062f\\u0629\",\"en\":\"Jeddah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(3, '{\"ar\":\"\\u0645\\u0643\\u0629 \\u0627\\u0644\\u0645\\u0643\\u0631\\u0645\\u0629\",\"en\":\"Makkah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(4, '{\"ar\":\"\\u0627\\u0644\\u0645\\u062f\\u064a\\u0646\\u0629 \\u0627\\u0644\\u0645\\u0646\\u0648\\u0631\\u0629\",\"en\":\"Madinah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(5, '{\"ar\":\"\\u0627\\u0644\\u062f\\u0645\\u0627\\u0645\",\"en\":\"Dammam\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(6, '{\"ar\":\"\\u0627\\u0644\\u062e\\u0628\\u0631\",\"en\":\"Khobar\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(7, '{\"ar\":\"\\u0627\\u0644\\u0638\\u0647\\u0631\\u0627\\u0646\",\"en\":\"Dhahran\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(8, '{\"ar\":\"\\u0627\\u0644\\u0637\\u0627\\u0626\\u0641\",\"en\":\"Taif\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(9, '{\"ar\":\"\\u0628\\u0631\\u064a\\u062f\\u0629\",\"en\":\"Buraidah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(10, '{\"ar\":\"\\u062a\\u0628\\u0648\\u0643\",\"en\":\"Tabuk\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(11, '{\"ar\":\"\\u062e\\u0645\\u064a\\u0633 \\u0645\\u0634\\u064a\\u0637\",\"en\":\"Khamis Mushait\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(12, '{\"ar\":\"\\u0627\\u0644\\u0647\\u0641\\u0648\\u0641\",\"en\":\"Hofuf\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(13, '{\"ar\":\"\\u0627\\u0644\\u0645\\u0628\\u0631\\u0632\",\"en\":\"Mubarraz\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(14, '{\"ar\":\"\\u062d\\u0627\\u0626\\u0644\",\"en\":\"Hail\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(15, '{\"ar\":\"\\u0646\\u062c\\u0631\\u0627\\u0646\",\"en\":\"Najran\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(16, '{\"ar\":\"\\u0627\\u0644\\u062c\\u0628\\u064a\\u0644\",\"en\":\"Jubail\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(17, '{\"ar\":\"\\u064a\\u0646\\u0628\\u0639\",\"en\":\"Yanbu\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(18, '{\"ar\":\"\\u0623\\u0628\\u0647\\u0627\",\"en\":\"Abha\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(19, '{\"ar\":\"\\u0639\\u0631\\u0639\\u0631\",\"en\":\"Arar\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(20, '{\"ar\":\"\\u0633\\u0643\\u0627\\u0643\\u0627\",\"en\":\"Sakakah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(21, '{\"ar\":\"\\u062c\\u064a\\u0632\\u0627\\u0646\",\"en\":\"Jazan\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(22, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0637\\u064a\\u0641\",\"en\":\"Qatif\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(23, '{\"ar\":\"\\u0627\\u0644\\u0628\\u0627\\u062d\\u0629\",\"en\":\"Al Bahah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(24, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0646\\u0641\\u0630\\u0629\",\"en\":\"Al Qunfudhah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(25, '{\"ar\":\"\\u0627\\u0644\\u0644\\u064a\\u062b\",\"en\":\"Al Lith\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(26, '{\"ar\":\"\\u0631\\u0627\\u0628\\u063a\",\"en\":\"Rabigh\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(27, '{\"ar\":\"\\u0627\\u0644\\u062e\\u0631\\u062c\",\"en\":\"Al Kharj\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(28, '{\"ar\":\"\\u0627\\u0644\\u0632\\u0644\\u0641\\u064a\",\"en\":\"Az Zulfi\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(29, '{\"ar\":\"\\u0648\\u0627\\u062f\\u064a \\u0627\\u0644\\u062f\\u0648\\u0627\\u0633\\u0631\",\"en\":\"Wadi ad-Dawasir\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(30, '{\"ar\":\"\\u0639\\u0646\\u064a\\u0632\\u0629\",\"en\":\"Unaizah\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(31, '{\"ar\":\"\\u0627\\u0644\\u0631\\u0633\",\"en\":\"Ar Rass\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(32, '{\"ar\":\"\\u062a\\u064a\\u0645\\u0627\\u0621\",\"en\":\"Tayma\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(33, '{\"ar\":\"\\u0627\\u0644\\u0648\\u062c\\u0647\",\"en\":\"Al Wajh\"}', 1, NULL, '2025-07-27 10:51:24', '2025-07-27 10:51:24', NULL),
(34, '{\"ar\":\"\\u0627\\u0645\\u0644\\u062c\",\"en\":\"Umluj\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(35, '{\"ar\":\"\\u0636\\u0628\\u0627\",\"en\":\"Duba\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(36, '{\"ar\":\"\\u062d\\u0642\\u0644\",\"en\":\"Haql\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(37, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0631\\u064a\\u0627\\u062a\",\"en\":\"Al Qurayyat\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(38, '{\"ar\":\"\\u0637\\u0631\\u064a\\u0641\",\"en\":\"Turaif\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(39, '{\"ar\":\"\\u0631\\u0641\\u062d\\u0627\\u0621\",\"en\":\"Rafha\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(40, '{\"ar\":\"\\u0627\\u0644\\u0639\\u0644\\u0627\",\"en\":\"AlUla\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(41, '{\"ar\":\"\\u062e\\u064a\\u0628\\u0631\",\"en\":\"Khaybar\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(42, '{\"ar\":\"\\u0627\\u0644\\u0645\\u0647\\u062f\",\"en\":\"Al Mahd\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(43, '{\"ar\":\"\\u0628\\u062f\\u0631\",\"en\":\"Badr\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(44, '{\"ar\":\"\\u0627\\u0644\\u062d\\u0646\\u0627\\u0643\\u064a\\u0629\",\"en\":\"Al Henakiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(45, '{\"ar\":\"\\u0627\\u0644\\u063a\\u0627\\u0637\",\"en\":\"Al Ghat\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(46, '{\"ar\":\"\\u0634\\u0642\\u0631\\u0627\\u0621\",\"en\":\"Shaqra\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(47, '{\"ar\":\"\\u0627\\u0644\\u062f\\u0648\\u0627\\u062f\\u0645\\u064a\",\"en\":\"Ad Dawadimi\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(48, '{\"ar\":\"\\u0639\\u0641\\u064a\\u0641\",\"en\":\"Afif\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(49, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0648\\u064a\\u0639\\u064a\\u0629\",\"en\":\"Al Quwayiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(50, '{\"ar\":\"\\u0627\\u0644\\u0645\\u0632\\u0627\\u062d\\u0645\\u064a\\u0629\",\"en\":\"Al Muzahimiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(51, '{\"ar\":\"\\u0631\\u0645\\u0627\\u062d\",\"en\":\"Rumah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(52, '{\"ar\":\"\\u062b\\u0627\\u062f\\u0642\",\"en\":\"Thadiq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(53, '{\"ar\":\"\\u062d\\u0631\\u064a\\u0645\\u0644\\u0627\\u0621\",\"en\":\"Huraymila\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(54, '{\"ar\":\"\\u0627\\u0644\\u062f\\u0631\\u0639\\u064a\\u0629\",\"en\":\"Diriyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(55, '{\"ar\":\"\\u0627\\u0644\\u0645\\u062c\\u0645\\u0639\\u0629\",\"en\":\"Al Majmaah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(56, '{\"ar\":\"\\u0627\\u0644\\u0623\\u0631\\u0637\\u0627\\u0648\\u064a\\u0629\",\"en\":\"Artawiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(57, '{\"ar\":\"\\u0631\\u064a\\u0627\\u0636 \\u0627\\u0644\\u062e\\u0628\\u0631\\u0627\\u0621\",\"en\":\"Riyadh Al Khabra\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(58, '{\"ar\":\"\\u0627\\u0644\\u0628\\u0643\\u064a\\u0631\\u064a\\u0629\",\"en\":\"Al Bukayriyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(59, '{\"ar\":\"\\u0627\\u0644\\u0634\\u0645\\u0627\\u0633\\u064a\\u0629\",\"en\":\"Ash Shimasiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(60, '{\"ar\":\"\\u0627\\u0644\\u0646\\u0628\\u0647\\u0627\\u0646\\u064a\\u0629\",\"en\":\"An Nabhaniyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(61, '{\"ar\":\"\\u0627\\u0644\\u0623\\u0633\\u064a\\u0627\\u062d\",\"en\":\"Al Asyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(62, '{\"ar\":\"\\u0627\\u0644\\u0628\\u062f\\u0627\\u0626\\u0639\",\"en\":\"Al Badai\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(63, '{\"ar\":\"\\u0627\\u0644\\u062d\\u0627\\u0626\\u0637\",\"en\":\"Al Hait\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(64, '{\"ar\":\"\\u0627\\u0644\\u0634\\u0646\\u0627\\u0646\",\"en\":\"Ash Shinan\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(65, '{\"ar\":\"\\u0645\\u0648\\u0642\\u0642\",\"en\":\"Mawqaq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(66, '{\"ar\":\"\\u0627\\u0644\\u063a\\u0632\\u0627\\u0644\\u0629\",\"en\":\"Al Ghazalah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(67, '{\"ar\":\"\\u0628\\u0642\\u0639\\u0627\\u0621\",\"en\":\"Baqaa\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(68, '{\"ar\":\"\\u0627\\u0644\\u0634\\u0648\\u064a\\u062d\\u0637\\u064a\\u0629\",\"en\":\"Ash Shuwayhitiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(69, '{\"ar\":\"\\u0641\\u064a\\u062f\",\"en\":\"Fayd\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(70, '{\"ar\":\"\\u0627\\u0644\\u062d\\u062c\\u0631\\u0629\",\"en\":\"Al Hajrah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(71, '{\"ar\":\"\\u0623\\u0645 \\u0627\\u0644\\u062c\\u0645\\u0627\\u062c\\u0645\",\"en\":\"Umm al Jamajim\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(72, '{\"ar\":\"\\u0627\\u0644\\u062d\\u0641\\u064a\\u0631\\u0629\",\"en\":\"Al Hafr\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(73, '{\"ar\":\"\\u0642\\u0628\\u0629\",\"en\":\"Qubah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(74, '{\"ar\":\"\\u0627\\u0644\\u062d\\u0641\\u0631\",\"en\":\"Al Hafar\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(75, '{\"ar\":\"\\u0627\\u0644\\u0646\\u0639\\u064a\\u0631\\u064a\\u0629\",\"en\":\"Nairyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(76, '{\"ar\":\"\\u0631\\u0623\\u0633 \\u062a\\u0646\\u0648\\u0631\\u0629\",\"en\":\"Ras Tanura\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(77, '{\"ar\":\"\\u0628\\u0642\\u064a\\u0642\",\"en\":\"Buqayq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(78, '{\"ar\":\"\\u0627\\u0644\\u0623\\u062d\\u0633\\u0627\\u0621\",\"en\":\"Al Ahsa\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(79, '{\"ar\":\"\\u0627\\u0644\\u0639\\u064a\\u0648\\u0646\",\"en\":\"Al Oyoun\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(80, '{\"ar\":\"\\u0627\\u0644\\u062c\\u0641\\u0631\",\"en\":\"Al Jafr\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(81, '{\"ar\":\"\\u062d\\u0631\\u0636\",\"en\":\"Haradh\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(82, '{\"ar\":\"\\u0627\\u0644\\u0639\\u062f\\u064a\\u062f\",\"en\":\"Al Udeid\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(83, '{\"ar\":\"\\u0627\\u0644\\u062e\\u0641\\u062c\\u064a\",\"en\":\"Khafji\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(84, '{\"ar\":\"\\u0635\\u0641\\u0648\\u0649\",\"en\":\"Safwa\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(85, '{\"ar\":\"\\u0633\\u064a\\u0647\\u0627\\u062a\",\"en\":\"Sayhat\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(86, '{\"ar\":\"\\u062a\\u0627\\u0631\\u0648\\u062a\",\"en\":\"Tarout\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(87, '{\"ar\":\"\\u0627\\u0644\\u062c\\u0634\",\"en\":\"Al Jish\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(88, '{\"ar\":\"\\u0627\\u0644\\u0639\\u0648\\u0627\\u0645\\u064a\\u0629\",\"en\":\"Awamiyah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(89, '{\"ar\":\"\\u0627\\u0644\\u0642\\u062f\\u064a\\u062d\",\"en\":\"Qudaih\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(90, '{\"ar\":\"\\u0623\\u0646\\u0643\",\"en\":\"Anak\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(91, '{\"ar\":\"\\u0628\\u064a\\u0634\\u0629\",\"en\":\"Bisha\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(92, '{\"ar\":\"\\u062e\\u0645\\u064a\\u0633 \\u0645\\u0634\\u064a\\u0637\",\"en\":\"Khamis Mushayt\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(93, '{\"ar\":\"\\u0623\\u062d\\u062f \\u0631\\u0641\\u064a\\u062f\\u0629\",\"en\":\"Ahad Rafidah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(94, '{\"ar\":\"\\u0633\\u0631\\u0627\\u0629 \\u0639\\u0628\\u064a\\u062f\\u0629\",\"en\":\"Sarat Abidah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(95, '{\"ar\":\"\\u062a\\u0646\\u0648\\u0645\\u0629\",\"en\":\"Tanomah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(96, '{\"ar\":\"\\u0627\\u0644\\u0646\\u0645\\u0627\\u0635\",\"en\":\"Al Namas\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(97, '{\"ar\":\"\\u0628\\u0644\\u0642\\u0631\\u0646\",\"en\":\"Balqarn\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(98, '{\"ar\":\"\\u0628\\u0627\\u0631\\u0642\",\"en\":\"Bariq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(99, '{\"ar\":\"\\u0631\\u062c\\u0627\\u0644 \\u0623\\u0644\\u0645\\u0639\",\"en\":\"Rijal Almaa\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(100, '{\"ar\":\"\\u0645\\u062d\\u0627\\u064a\\u0644\",\"en\":\"Muhayil\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(101, '{\"ar\":\"\\u0627\\u0644\\u0628\\u0631\\u0643\",\"en\":\"Al Birk\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(102, '{\"ar\":\"\\u0627\\u0644\\u0639\\u0631\\u0636\\u064a\\u0627\\u062a\",\"en\":\"Al Ardiyat\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(103, '{\"ar\":\"\\u062b\\u0631\\u0628\\u0627\\u0646\",\"en\":\"Thurban\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(104, '{\"ar\":\"\\u0627\\u0644\\u0639\\u064a\\u062f\\u0627\\u0628\\u064a\",\"en\":\"Al Eidabi\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(105, '{\"ar\":\"\\u0627\\u0644\\u0645\\u0646\\u062f\\u0642\",\"en\":\"Al Mandaq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(106, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0631\\u0649\",\"en\":\"Al Qura\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(107, '{\"ar\":\"\\u063a\\u0627\\u0645\\u062f \\u0627\\u0644\\u0632\\u0646\\u0627\\u062f\",\"en\":\"Ghamid al Zinad\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(108, '{\"ar\":\"\\u0627\\u0644\\u0639\\u0642\\u064a\\u0642\",\"en\":\"Al Aqiq\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(109, '{\"ar\":\"\\u0628\\u0646\\u064a \\u062d\\u0633\\u0646\",\"en\":\"Bani Hassan\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(110, '{\"ar\":\"\\u0627\\u0644\\u0642\\u0644\\u0639\\u0629\",\"en\":\"Al Qalah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(111, '{\"ar\":\"\\u0635\\u0627\\u0645\\u0637\\u0629\",\"en\":\"Samtah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(112, '{\"ar\":\"\\u0623\\u0628\\u0648 \\u0639\\u0631\\u064a\\u0634\",\"en\":\"Abu Arish\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(113, '{\"ar\":\"\\u0635\\u0628\\u064a\\u0627\",\"en\":\"Sabya\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(114, '{\"ar\":\"\\u0628\\u064a\\u0634\",\"en\":\"Baysh\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(115, '{\"ar\":\"\\u0641\\u0631\\u0633\\u0627\\u0646\",\"en\":\"Farasan\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(116, '{\"ar\":\"\\u0627\\u0644\\u062f\\u0631\\u0628\",\"en\":\"Ad Darb\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(117, '{\"ar\":\"\\u0627\\u0644\\u062d\\u0631\\u062b\",\"en\":\"Al Harth\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(118, '{\"ar\":\"\\u0636\\u0645\\u062f\",\"en\":\"Damad\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(119, '{\"ar\":\"\\u0627\\u0644\\u0631\\u064a\\u062b\",\"en\":\"Ar Rayth\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(120, '{\"ar\":\"\\u0623\\u062d\\u062f \\u0627\\u0644\\u0645\\u0633\\u0627\\u0631\\u062d\\u0629\",\"en\":\"Ahad al Masarihah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(121, '{\"ar\":\"\\u0627\\u0644\\u0637\\u0648\\u0627\\u0644\",\"en\":\"At Tuwal\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(122, '{\"ar\":\"\\u0627\\u0644\\u0639\\u0627\\u0631\\u0636\\u0629\",\"en\":\"Al Aridah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(123, '{\"ar\":\"\\u0638\\u0647\\u0631\\u0627\\u0646 \\u0627\\u0644\\u062c\\u0646\\u0648\\u0628\",\"en\":\"Dhahran al Janub\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(124, '{\"ar\":\"\\u062d\\u0628\\u0648\\u0646\\u0627\",\"en\":\"Habuna\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(125, '{\"ar\":\"\\u0628\\u062f\\u0631 \\u0627\\u0644\\u062c\\u0646\\u0648\\u0628\",\"en\":\"Badr al Janub\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(126, '{\"ar\":\"\\u062b\\u0627\\u0631\",\"en\":\"Thar\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(127, '{\"ar\":\"\\u064a\\u062f\\u0645\\u0629\",\"en\":\"Yadamah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(128, '{\"ar\":\"\\u0627\\u0644\\u062e\\u0631\\u062e\\u064a\\u0631\",\"en\":\"Al Kharkhir\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL),
(129, '{\"ar\":\"\\u0634\\u0631\\u0648\\u0631\\u0629\",\"en\":\"Sharurah\"}', 1, NULL, '2025-07-27 10:51:25', '2025-07-27 10:51:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `is_active`, `deleted_at`, `created_at`, `updated_at`, `color`, `admin_id`) VALUES
(1, '{\"ar\": \"asdfasdf\", \"en\": \"sadfadsf\"}', 1, NULL, '2025-05-04 12:31:07', '2025-05-04 13:02:12', '#127dbf', NULL),
(2, '{\"ar\": \"asd\", \"en\": \"asd\"}', 1, NULL, '2025-05-04 12:31:17', '2025-05-04 12:31:17', NULL, NULL),
(3, '{\"ar\": \"asdf\", \"en\": \"asdfa\"}', 1, NULL, '2025-05-04 13:00:01', '2025-05-04 13:00:01', '#ff0000', NULL),
(4, '{\"ar\": null, \"en\": null}', 1, NULL, '2025-05-05 10:19:02', '2025-05-05 10:19:02', '#000000', NULL),
(5, '{\"ar\": \"sdfgfdg\", \"en\": \"asdasd\"}', 1, NULL, '2025-05-11 12:38:54', '2025-05-11 12:38:54', '#000000', 4);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follow_users`
--

CREATE TABLE `follow_users` (
  `id` bigint UNSIGNED NOT NULL,
  `follow_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `follow_users`
--

INSERT INTO `follow_users` (`id`, `follow_id`, `user_id`, `created_at`, `updated_at`) VALUES
(5, 4, 1, '2024-05-19 12:15:29', '2024-05-19 12:15:29'),
(7, 2, 58, '2024-05-22 11:06:11', '2024-05-22 11:06:11'),
(8, 64, 58, '2024-07-04 19:29:19', '2024-07-04 19:29:19'),
(13, 71, 74, '2024-08-03 12:43:54', '2024-08-03 12:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` bigint UNSIGNED NOT NULL,
  `friend_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `friend_id`, `user_id`, `type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 2, 58, 'request', '2024-05-28 11:21:28', '2024-05-28 11:21:28', NULL),
(8, 12, 58, 'request', '2024-05-28 11:31:23', '2024-05-28 11:31:23', NULL),
(9, 62, 58, 'friend', '2024-05-28 15:04:29', '2024-05-30 10:07:36', NULL),
(10, 1, 62, 'request', '2024-05-28 19:51:34', '2024-05-28 19:51:34', NULL),
(11, 35, 62, 'request', '2024-05-28 20:05:08', '2024-05-28 20:05:08', NULL),
(12, 3, 62, 'request', '2024-05-28 20:05:49', '2024-05-28 20:05:49', NULL),
(13, 25, 63, 'request', '2024-06-03 10:13:34', '2024-06-03 10:13:34', NULL),
(14, 62, 64, 'friend', '2024-06-05 13:17:19', '2024-06-05 13:24:13', NULL),
(15, 13, 64, 'request', '2024-07-04 19:44:51', '2024-07-04 19:44:51', NULL),
(16, 4, 64, 'request', '2024-07-04 19:45:12', '2024-07-04 19:45:12', NULL),
(17, 62, 58, 'request', '2024-07-07 16:21:42', '2024-07-07 16:21:42', NULL),
(18, 64, 58, 'friend', '2024-07-07 16:22:44', '2024-07-07 16:22:59', NULL),
(19, 62, 64, 'request', '2024-07-07 17:10:58', '2024-07-07 17:10:58', NULL),
(20, 12, 72, 'request', '2024-07-17 14:13:03', '2024-07-17 14:13:03', NULL),
(21, 20, 72, 'request', '2024-07-17 14:13:15', '2024-07-17 14:13:15', NULL),
(22, 34, 72, 'request', '2024-07-17 14:13:18', '2024-07-17 14:13:18', NULL),
(23, 25, 71, 'request', '2024-08-01 15:36:55', '2024-08-01 15:36:55', NULL),
(24, 29, 71, 'request', '2024-08-01 15:45:02', '2024-08-01 15:45:02', NULL),
(25, 25, 74, 'request', '2024-08-02 11:06:34', '2024-08-02 11:06:34', NULL),
(26, 71, 74, 'friend', '2024-08-03 14:42:10', '2024-08-03 14:43:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gifts`
--

CREATE TABLE `gifts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `image_png` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_svg` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coin` double DEFAULT NULL,
  `is_active` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gifts`
--

INSERT INTO `gifts` (`id`, `name`, `image_png`, `image_svg`, `coin`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"en\":\"asds\",\"ar\":\"Imelda Fuentes\"}', 'gifts/Tj6d7MWuDywun2g5ZjlD9OBXtsqp4jdizeyzTi57.png', 'gifts/jyrFILobvlS1lOX6FavgbTVmt9uR45cifcpKNTDn.zz', 34, 1, '2024-03-10 19:49:49', '2024-03-24 17:14:46', '2024-03-24 17:14:46'),
(2, '{\"en\":\"vip\",\"ar\":\"vip\"}', 'gifts/2F50WdlCrBMS8jh2CIMmExhdckJWF3v8dv0mTYhv.png', 'gifts/2zyo3CLps2SBdTSWNNFzAL2KvizMn2FhrmNoXSH7.zz', 30, 1, '2024-03-24 17:14:38', '2024-03-24 17:14:38', NULL),
(3, '{\"en\":\"frame\",\"ar\":\"\\u0628\\u0627\\u0646\\u0631\"}', 'gifts/2AdwXlndh9gKOWX9gVVY8e7mdUjOqbdXRhj9umC3.png', 'gifts/q7nfWWSe0SOtLHPsLpSBo35bkqXOlvlzQFU3gRgH.zz', 400, 1, '2024-03-24 17:17:29', '2024-03-24 17:17:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hashtags`
--

CREATE TABLE `hashtags` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hashtags`
--

INSERT INTO `hashtags` (`id`, `name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'aa', '2024-06-04 12:51:00', '2024-06-04 12:51:00', NULL),
(2, 'mmm', '2024-06-04 12:51:00', '2024-06-04 12:51:00', NULL),
(3, 'eew', '2024-06-04 12:51:00', '2024-06-04 12:51:00', NULL),
(4, 'abc', '2024-06-04 13:58:04', '2024-06-04 13:58:04', NULL),
(5, 'aaa', '2024-06-04 13:58:04', '2024-06-04 13:58:04', NULL),
(6, 'ysfhmza', '2024-07-19 09:50:37', '2024-07-19 09:50:37', NULL),
(7, 'reel_with_sound', '2024-07-19 11:42:49', '2024-07-19 11:42:49', NULL),
(8, 'video_reel', '2024-07-19 12:48:37', '2024-07-19 12:48:37', NULL),
(9, 'lab', '2024-07-23 14:40:32', '2024-07-23 14:40:32', NULL),
(10, 'yousef', '2024-08-01 18:02:39', '2024-08-01 18:02:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `hashtag_video`
--

CREATE TABLE `hashtag_video` (
  `id` bigint UNSIGNED NOT NULL,
  `video_id` bigint UNSIGNED NOT NULL,
  `hashtag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hashtag_video`
--

INSERT INTO `hashtag_video` (`id`, `video_id`, `hashtag_id`, `created_at`, `updated_at`) VALUES
(1, 10, 1, NULL, NULL),
(2, 10, 2, NULL, NULL),
(3, 10, 3, NULL, NULL),
(4, 11, 4, NULL, NULL),
(5, 11, 5, NULL, NULL),
(6, 12, 4, NULL, NULL),
(7, 14, 4, NULL, NULL),
(8, 14, 5, NULL, NULL),
(9, 16, 6, NULL, NULL),
(10, 17, 7, NULL, NULL),
(11, 18, 7, NULL, NULL),
(12, 19, 8, NULL, NULL),
(13, 20, 4, NULL, NULL),
(14, 20, 9, NULL, NULL),
(15, 21, 1, NULL, NULL),
(16, 21, 1, NULL, NULL),
(17, 21, 2, NULL, NULL),
(18, 25, 1, NULL, NULL),
(19, 25, 2, NULL, NULL),
(20, 26, 10, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `live_videos`
--

CREATE TABLE `live_videos` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `information` text COLLATE utf8mb4_unicode_ci,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_start_at` date DEFAULT NULL,
  `date_end_at` date DEFAULT NULL,
  `time_start_at` time DEFAULT NULL,
  `time_end_at` time DEFAULT NULL,
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `city_id` int DEFAULT NULL,
  `title_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `information_ar` text COLLATE utf8mb4_unicode_ci,
  `terms_conditions_ar` text COLLATE utf8mb4_unicode_ci,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('live','recorded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'live',
  `partners_type` enum('single','multiple') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_videos`
--

INSERT INTO `live_videos` (`id`, `user_id`, `status`, `end_at`, `deleted_at`, `created_at`, `updated_at`, `image`, `information`, `title`, `date_start_at`, `date_end_at`, `time_start_at`, `time_end_at`, `terms_conditions`, `city_id`, `title_ar`, `information_ar`, `terms_conditions_ar`, `admin_id`, `is_active`, `type`, `partners_type`, `video`, `partner_id`) VALUES
(1, 1, 'start', NULL, NULL, '2025-03-23 00:58:32', '2025-03-23 19:29:22', '[\"image_video\\/72538_3IjNxQWhqr2EGuZCutzq3ZX2hR1gpK9D86GOeDah.png\",\"image_video\\/57567_48c0b6991893ee6c773fa125e17a93b7.png\",\"image_video\\/58568_51adeda4-e5b5-45c7-b13b-8e52ad90a0d2.jfif\",\"image_video\\/80638_51adeda4-e5b5-45c7-b13b-8e52ad90a0d2.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'فثسسشيسيشسيشسي', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(2, 1, NULL, NULL, NULL, '2025-04-07 11:01:08', '2025-04-07 11:01:08', '[\"image_video\\/60791_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(3, 1, NULL, NULL, NULL, '2025-04-07 11:04:15', '2025-04-07 11:04:15', '[\"image_video\\/63030_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(4, 1, NULL, NULL, NULL, '2025-04-07 11:04:37', '2025-04-07 11:04:37', '[\"image_video\\/24948_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(5, 1, NULL, NULL, NULL, '2025-04-07 11:05:08', '2025-04-07 11:05:08', '[\"image_video\\/90364_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(6, 1, NULL, NULL, NULL, '2025-04-07 11:05:12', '2025-04-07 11:05:12', '[\"image_video\\/79856_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', NULL, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(7, 1, NULL, NULL, NULL, '2025-04-07 11:06:34', '2025-04-07 11:06:34', '[\"image_video\\/13268_064f7c35-e02f-40e4-a220-cb4643a37247.jpg\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(8, 4, 'start', NULL, NULL, '2025-04-13 14:49:41', '2025-04-13 14:51:22', '[\"image_video\\/66360_jhdQJEPRCMFVaFJqDgWtZ6GqfgJAJtw9xEAuHHqf.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 0, 'live', 'single', NULL, NULL),
(9, 4, 'pending', NULL, NULL, '2025-04-13 16:18:50', '2025-08-17 11:40:42', '[\"image_video\\/56310_jhdQJEPRCMFVaFJqDgWtZ6GqfgJAJtw9xEAuHHqf.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(10, 4, 'pending', NULL, NULL, '2025-04-16 12:04:38', '2025-08-17 11:40:41', '[\"image_video\\/69053_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(11, 4, 'pending', NULL, NULL, '2025-04-16 12:12:45', '2025-08-17 11:40:41', '[\"image_video\\/37892_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(12, 4, 'pending', NULL, NULL, '2025-04-16 12:12:56', '2025-08-17 11:40:40', '[\"image_video\\/78830_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(13, 4, 'pending', NULL, NULL, '2025-04-16 12:13:02', '2025-08-17 11:40:40', '[\"image_video\\/36980_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(14, 4, 'pending', NULL, NULL, '2025-04-16 12:13:25', '2025-08-17 11:40:39', '[\"image_video\\/31111_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(15, 4, 'end', '2025-04-17 10:50:35', NULL, '2025-04-16 12:13:34', '2025-08-17 11:40:39', '[\"image_video\\/18901_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(16, 4, 'start', NULL, NULL, '2025-04-16 12:17:39', '2025-08-17 11:40:38', '[\"image_video\\/25003_TOMY Logo.png\"]', 'asdadfasdf', 'sdfzdf', '2025-03-28', '2025-03-28', '18:10:00', '20:10:00', 'asdadfasdf', 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(17, 2, 'pending', NULL, NULL, '2025-05-04 14:29:16', '2025-05-04 14:29:16', '[\"image_video\\/73968_940663e8-86f7-4c4c-8612-5395de3f3a7b.jpeg\"]', NULL, 'Natus explicabo Nul', '1998-07-13', '2014-07-18', '00:45:00', '00:00:00', 'Quae architecto beat', 1, NULL, 'Id quidem ipsa volu', 'Quaerat repudiandae', NULL, 0, 'live', 'single', NULL, NULL),
(18, 2, 'pending', NULL, NULL, '2025-05-04 14:30:06', '2025-08-17 11:39:59', '[\"image_video\\/38425_image-20250421-144425.png\",\"image_video\\/13465_sess_d34e70dd62d3476e41f92a1a0affbbc3\",\"image_video\\/86836_940663e8-86f7-4c4c-8612-5395de3f3a7b.jpeg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 1, 'live', 'single', NULL, NULL),
(19, 2, 'pending', NULL, NULL, '2025-05-04 14:31:21', '2025-05-04 14:46:01', '[\"image_video\\/34484_image-20250421-144425.png\",\"image_video\\/13069_sess_d34e70dd62d3476e41f92a1a0affbbc3\",\"image_video\\/56707_940663e8-86f7-4c4c-8612-5395de3f3a7b.jpeg\"]', NULL, 'Aute dicta quasi eiusadf', '1987-08-18', '1980-09-12', '12:47:00', '00:00:00', 'Eveniet accusantium', 1, 'Autem voluptates iruasdf asdf', 'Quia cupiditate labo', 'Sit ab error dolor', NULL, 0, 'live', 'single', NULL, NULL),
(20, 2, 'pending', NULL, NULL, '2025-05-05 11:14:57', '2025-05-05 11:14:57', '[]', 'Rerum perspiciatis', 'Qui do do alias quas', '1980-11-15', '1990-08-03', '23:23:00', '20:00:00', 'Expedita qui quos fu', 1, 'Accusamus omnis do e', 'Numquam ullam numqua', 'Asperiores minima Na', NULL, 0, 'live', 'single', NULL, NULL),
(21, 2, 'pending', NULL, NULL, '2025-05-05 11:15:15', '2025-05-05 11:15:15', '[]', 'Eum porro consequatu', 'Aliquid quo culpa c', '2020-05-26', '2002-05-14', '14:12:00', '16:16:00', 'Molestiae vel dolor', 1, 'Asperiores pariatur', 'Sed soluta eligendi', 'Doloremque nulla tem', NULL, 0, 'live', 'single', NULL, NULL),
(22, 2, 'pending', NULL, NULL, '2025-05-05 11:15:44', '2025-05-05 11:15:44', '[]', 'Eum porro consequatu', 'Aliquid quo culpa c', '2020-05-26', '2002-05-14', '14:12:00', '16:16:00', 'Molestiae vel dolor', 1, 'Asperiores pariatur', 'Sed soluta eligendi', 'Doloremque nulla tem', NULL, 0, 'live', 'single', NULL, NULL),
(23, 2, 'pending', NULL, NULL, '2025-05-05 11:18:21', '2025-05-05 11:18:21', '[]', 'Eum porro consequatu', 'Aliquid quo culpa c', '2020-05-26', '2002-05-14', '14:12:00', '16:16:00', 'Molestiae vel dolor', 1, 'Asperiores pariatur', 'Sed soluta eligendi', 'Doloremque nulla tem', NULL, 0, 'live', 'single', NULL, NULL),
(24, 2, 'pending', NULL, NULL, '2025-05-05 11:18:41', '2025-05-05 11:18:41', '[]', 'Eum porro consequatu', 'Aliquid quo culpa c', '2020-05-26', '2002-05-14', '14:12:00', '16:16:00', 'Molestiae vel dolor', 1, 'Asperiores pariatur', 'Sed soluta eligendi', 'Doloremque nulla tem', NULL, 0, 'live', 'single', NULL, NULL),
(25, 2, 'pending', NULL, NULL, '2025-05-05 11:20:54', '2025-05-05 11:20:54', '[]', 'Eum porro consequatu', 'Aliquid quo culpa c', '2020-05-26', '2002-05-14', '14:12:00', '16:16:00', 'Molestiae vel dolor', 1, 'Asperiores pariatur', 'Sed soluta eligendi', 'Doloremque nulla tem', NULL, 0, 'live', 'single', NULL, NULL),
(26, 2, 'pending', NULL, NULL, '2025-05-05 11:22:01', '2025-05-05 11:22:01', '[]', 'Aut corporis enim si', 'Accusantium accusant', '2019-11-05', '2018-05-09', '03:28:00', '02:57:00', 'Voluptatem in aperia', 1, 'Ipsa voluptatem adi', 'Neque in incididunt', 'Reprehenderit est', NULL, 0, 'live', 'single', NULL, NULL),
(27, 2, 'pending', NULL, NULL, '2025-05-05 11:35:00', '2025-05-11 17:48:49', '[]', 'Velit nulla dicta c', 'Voluptates voluptate', '1992-01-25', '2020-05-20', '16:45:00', '19:38:00', 'Mollit ducimus temp', 1, 'Sint sint eos eos', 'Est reprehenderit a', 'Nisi labore officiis', NULL, 1, 'live', 'single', NULL, NULL),
(28, 2, 'pending', NULL, NULL, '2025-05-11 12:16:56', '2025-05-11 14:34:58', '[\"image_video\\/43555_81341.png\"]', 'Accusamus odio conse', 'Laborum Proident a', '1993-01-14', '1983-06-10', '06:09:00', '17:49:00', 'Officiis ipsam asper adgfsdg dsgf', 1, 'Aut quis Nam exercit', 'Quae quia mollit sun', 'Eaque maiores autem', 4, 1, 'live', 'single', NULL, NULL),
(29, NULL, 'pending', NULL, NULL, '2025-05-12 10:55:52', '2025-07-13 08:18:09', '[]', 'test', 'tittle auction', '2025-05-12', '2025-05-13', '13:55:00', '16:55:00', NULL, 1, 'مزاد', 'test', NULL, 5, 1, 'live', 'single', NULL, NULL),
(30, NULL, 'pending', NULL, NULL, '2025-05-12 15:28:58', '2025-05-12 15:28:58', '[]', 'Qui irure eos doloru', 'Non voluptate fugiat', '1985-01-21', '2024-02-03', '21:27:00', '04:36:00', 'Enim voluptatem quam', 1, 'Voluptatum delectus', 'Laboriosam voluptat', 'Quaerat sed sed a is', 5, 0, 'live', 'single', NULL, NULL),
(31, NULL, 'pending', NULL, NULL, '2025-05-12 15:29:45', '2025-08-17 11:40:36', '[]', 'Nostrud nemo minim a', 'Illo esse fugiat vo', '2025-05-11', '2004-03-03', '06:13:00', '11:42:00', 'Voluptatibus consequ', 2, 'Vel culpa reprehend', 'Odio sed ab ipsum co', 'Rerum ut sunt dolor', 5, 1, 'live', 'single', NULL, NULL),
(32, NULL, 'pending', NULL, NULL, '2025-08-11 11:36:39', '2025-08-11 11:36:39', '[]', 'Unde id nobis ut dui', 'Quisquam id volupta', '1988-08-26', '1997-02-25', '14:50:00', '12:56:00', 'Deleniti elit amet', 51, 'Quia ut mollit sed s', 'Ullamco a debitis ve', 'Earum est incidunt', 3, 0, 'live', 'single', NULL, NULL),
(33, NULL, 'pending', NULL, NULL, '2025-08-17 11:38:03', '2025-08-17 11:38:03', '[]', 'Omnis accusantium an', 'Omnis repudiandae lo', '2025-02-25', '2025-11-07', '00:48:00', '17:55:00', 'Sit ullamco labore', 2, 'Sit exercitationem q', 'Cumque sed dicta fac', 'Et laborum Assumend', 1, 0, 'recorded', 'single', NULL, 8);

-- --------------------------------------------------------

--
-- Table structure for table `live_video_items`
--

CREATE TABLE `live_video_items` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_video_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('pending','working','finished') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `end_at` timestamp NULL DEFAULT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `information` text COLLATE utf8mb4_unicode_ci,
  `weight` double DEFAULT NULL,
  `age` double DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `start_price` double DEFAULT NULL,
  `bidding` double DEFAULT NULL,
  `finished_price` double DEFAULT NULL,
  `user_finished_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `title_ar` text COLLATE utf8mb4_unicode_ci,
  `information_ar` text COLLATE utf8mb4_unicode_ci,
  `date_barth` date DEFAULT NULL,
  `type` enum('male','female') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_id` bigint UNSIGNED DEFAULT NULL,
  `age_id` bigint UNSIGNED DEFAULT NULL,
  `animal_pen_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `age_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `health_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_video_items`
--

INSERT INTO `live_video_items` (`id`, `title`, `live_video_id`, `status`, `end_at`, `image`, `category_id`, `information`, `weight`, `age`, `quantity`, `start_price`, `bidding`, `finished_price`, `user_finished_id`, `created_at`, `updated_at`, `deleted_at`, `title_ar`, `information_ar`, `date_barth`, `type`, `color_id`, `age_id`, `animal_pen_id`, `user_id`, `age_type`, `address`, `health_certificate`, `terms`, `terms_ar`, `video`) VALUES
(2, 'sdfzdf', 1, 'finished', '2025-03-23 20:17:08', '[\"image_video_item\\/47985_48c0b6991893ee6c773fa125e17a93b7.png\"]', 2, 'asdadfasdf', 34, 234, 5, 1000, 10, 100, 4, '2025-03-23 01:34:25', '2025-03-23 20:43:58', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'sdfzdf', 1, NULL, NULL, 'video_item/qziVfTSEh9vdljrRVPvOzbvPLrv4t9Suq8RThqPE.png', 2, 'asdadfasdf', 34, 234, 5, 1000, 10, NULL, 4, '2025-03-23 01:34:48', '2025-03-23 01:34:48', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'sdfzdf', 1, NULL, NULL, '[]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, 4, '2025-03-23 01:47:06', '2025-03-23 01:47:06', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'sdfzdf', 1, NULL, NULL, '[]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, 4, '2025-03-23 02:07:53', '2025-03-23 02:07:53', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'sdfzdf', 1, 'pending', NULL, '[]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, 4, '2025-03-23 02:17:50', '2025-03-23 02:17:50', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'sdfzdf', 1, 'pending', NULL, '[]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-03-23 02:18:26', '2025-03-23 02:18:26', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'sdfzdf', 1, 'pending', NULL, '[\"image_video_item\\/90418_51adeda4-e5b5-45c7-b13b-8e52ad90a0d2.jfif\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-03-23 02:19:51', '2025-03-23 02:19:51', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'sdfzdf', 1, 'pending', NULL, '[\"image_video_item\\/22619_no-data.png\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-03 13:45:53', '2025-04-03 13:45:53', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'sdfzdf', 8, 'working', NULL, '[\"image_video_item\\/98135_DwdOpigjER2YvdFKB04fRzMNpDPNZvzzVcYdMkfZ.png\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-13 14:55:25', '2025-04-13 16:15:35', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'sdfzdf', 8, 'pending', NULL, '[\"image_video_item\\/88390_DwdOpigjER2YvdFKB04fRzMNpDPNZvzzVcYdMkfZ.png\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-13 14:55:30', '2025-04-13 14:55:30', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'sdfzdf', 8, 'pending', NULL, '[\"image_video_item\\/31601_DwdOpigjER2YvdFKB04fRzMNpDPNZvzzVcYdMkfZ.png\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-13 14:55:32', '2025-04-13 14:55:32', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'sdfzdf', 16, 'pending', NULL, '[\"image_video_item\\/76319_WhatsApp Image 2025-04-08 at 1.00.55 PM.jpeg\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-16 12:38:35', '2025-04-16 12:38:35', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'sdfzdf', 16, 'finished', '2025-04-17 02:19:39', '[\"image_video_item\\/97282_WhatsApp Image 2025-04-08 at 1.00.55 PM.jpeg\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-16 12:38:48', '2025-04-17 02:19:39', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'sdfzdf', 16, 'working', NULL, '[\"image_video_item\\/85349_WhatsApp Image 2025-04-08 at 1.00.55 PM.jpeg\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, 100, 4, '2025-04-17 01:27:22', '2025-04-17 10:12:36', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'sdfzdf', 16, 'pending', NULL, '[\"image_video_item\\/29020_WhatsApp Image 2025-04-08 at 1.00.55 PM.jpeg\"]', 2, 'asdadfasdf', 34, 234, 55, 1000, 10, NULL, NULL, '2025-04-17 01:28:38', '2025-04-17 01:28:38', NULL, NULL, NULL, '0000-00-00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Rerum blanditiis obc', 27, 'pending', NULL, '[\"image_video\\/79173_WhatsApp Image 2025-04-06 at 5.46.42 PM.jpeg\",\"image_video\\/43305_WhatsApp Image 2025-04-06 at 5.46.41 PM (1).jpeg\",\"image_video\\/25529_WhatsApp Image 2025-04-06 at 5.46.41 PM.jpeg\",\"image_video\\/35841_WhatsApp Image 2025-04-06 at 5.46.40 PM.jpeg\"]', 2, 'Pariatur Velit eli', NULL, NULL, NULL, 227, NULL, NULL, NULL, '2025-05-05 13:08:53', '2025-05-05 13:08:53', NULL, 'Rerum omnis aliquip', 'Dolorem illum minim', '2019-03-30', 'male', 2, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'Rerum blanditiis obc', 27, 'pending', NULL, '[\"image_video\\/19463_WhatsApp Image 2025-04-06 at 5.46.42 PM.jpeg\",\"image_video\\/95502_WhatsApp Image 2025-04-06 at 5.46.41 PM (1).jpeg\",\"image_video\\/57064_WhatsApp Image 2025-04-06 at 5.46.41 PM.jpeg\",\"image_video\\/11590_WhatsApp Image 2025-04-06 at 5.46.40 PM.jpeg\"]', NULL, 'Pariatur Velit eli', NULL, NULL, NULL, 227, NULL, NULL, NULL, '2025-05-05 13:23:44', '2025-05-05 14:38:58', NULL, 'Rerum omnis aliquip', 'Dolorem illum minim', '2019-03-30', 'male', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 'Atque deserunt rerum', 28, 'pending', NULL, '[]', 2, 'A voluptatem beatae', NULL, NULL, NULL, 998, NULL, NULL, NULL, '2025-05-11 12:20:33', '2025-05-11 12:23:44', NULL, 'Corporis dolor dolor', 'Enim in quia adipisc', '1972-02-09', 'female', 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'Aliquip cum officiis', 28, 'pending', NULL, '[]', 4, 'Proident eos paria', NULL, NULL, NULL, 38, NULL, NULL, NULL, '2025-05-11 13:11:42', '2025-05-11 13:11:42', NULL, 'Ut consequatur Est', 'Ut dolorem sed facer', '1976-02-23', 'male', 3, 2, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'test', 29, 'pending', NULL, '[\"image_video\\/77304_black.jpeg\"]', 2, 'test', NULL, NULL, NULL, 199.98, NULL, NULL, NULL, '2025-05-12 10:56:37', '2025-05-12 10:56:37', NULL, 'تست', 'تست', '2025-05-12', 'male', 1, 2, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 'Enim aut assumenda l', 33, 'pending', NULL, '[\"image_video\\/36794_WhatsApp Image 2025-04-06 at 5.46.41 PM (1).jpeg\",\"image_video\\/33375_WhatsApp Image 2025-04-06 at 5.46.41 PM.jpeg\",\"image_video\\/58882_WhatsApp Image 2025-04-06 at 5.46.40 PM.jpeg\"]', 2, 'Non officiis eligend', NULL, 25, NULL, 575, 725, NULL, NULL, '2025-08-17 12:48:51', '2025-08-17 12:48:51', NULL, 'Autem totam minus si', 'Iste doloribus molli', NULL, 'female', 1, NULL, NULL, NULL, 'month', 'Sint dolore maxime', 'health_certificate/Discovery.jpg', 'Est excepturi rerum', 'Ea deleniti nobis om', 'video/ssvid.net--Coffee-Creative-Agency-Promo-Best-Digital-Agency_v720P.mp4');

-- --------------------------------------------------------

--
-- Table structure for table `live_video_likes`
--

CREATE TABLE `live_video_likes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `live_video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_video_likes`
--

INSERT INTO `live_video_likes` (`id`, `user_id`, `live_video_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2024-03-10 19:52:01', '2024-03-10 19:52:01'),
(2, 5, 1, '2024-03-19 17:02:02', '2024-03-19 17:02:02'),
(3, 5, 1, '2024-03-19 17:02:28', '2024-03-19 17:02:28'),
(4, 38, 1, '2024-08-03 20:49:40', '2024-08-03 20:49:40'),
(5, 71, 114, '2024-08-03 20:55:34', '2024-08-03 20:55:34'),
(6, 71, 114, '2024-08-03 20:55:39', '2024-08-03 20:55:39'),
(7, 1, 7, '2025-04-07 14:12:01', '2025-04-07 14:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `live_video_users`
--

CREATE TABLE `live_video_users` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `live_video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `leave` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_video_users`
--

INSERT INTO `live_video_users` (`id`, `user_id`, `live_video_id`, `created_at`, `updated_at`, `deleted_at`, `leave`) VALUES
(1, 1, 1, '2024-06-24 17:43:31', '2024-06-24 17:43:31', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ltm_translations`
--

CREATE TABLE `ltm_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `locale` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `key` text COLLATE utf8mb4_bin NOT NULL,
  `value` text COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_04_02_193005_create_translations_table', 1),
(2, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(3, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(4, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(5, '2016_06_01_000004_create_oauth_clients_table', 1),
(6, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(7, '2018_08_08_100000_create_telescope_entries_table', 1),
(8, '2019_08_19_000000_create_failed_jobs_table', 1),
(9, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(10, '2022_10_16_092804_create_permission_tables', 1),
(11, '2022_10_16_092946_create_admins_table', 1),
(12, '2022_10_16_124317_insert_super_admin', 1),
(13, '2022_10_17_082904_first_role_group', 1),
(14, '2022_10_17_084724_assign_role_to_default_admin', 1),
(15, '2022_12_25_145219_administrator_permission', 1),
(16, '2023_05_28_114657_create_users_table', 1),
(17, '2024_02_19_134553_create_video_table', 2),
(18, '2024_03_06_182234_create_live_video_table', 3),
(19, '2024_03_10_162244_create_gifts_table', 4),
(20, '2024_03_10_192341_create_live_video_live_table', 4),
(21, '2024_03_19_172715_create_sounds_table', 5),
(22, '2025_01_28_134557_create_telr_transactions_table', 6),
(23, '2025_01_30_123733_add_accont_type_users_table', 7),
(24, '2025_02_02_135841_create_categories_table', 8),
(25, '2025_02_02_143826_update_row_live_videos_table', 9),
(26, '2025_02_06_154436_add_row_live_videos_table', 10),
(27, '2025_03_05_045839_add_lang_users_table', 11),
(28, '2025_03_20_042602_create_live_video_items_table', 12),
(29, '2025_03_23_022855_add_new_datato_live_videos_table', 13),
(30, '2025_03_23_222610_add_live_video_item_id_to_video_comments_table', 14),
(31, '2025_04_07_121650_create_cities_table', 15),
(32, '2025_04_07_125348_add_city_live_videos_table', 16),
(33, '2025_05_04_143543_create_colors_table', 16),
(34, '2025_05_04_155247_add_color_row_colors_table', 17),
(36, '2025_05_04_172241_add_new_rows_live_videos_table', 18),
(37, '2025_05_05_130354_create_ages_table', 19),
(38, '2025_05_05_131319_create_animal_pens_table', 19),
(39, '2025_05_05_155010_add_new_rows_live_video_items_table', 20),
(40, '2025_05_11_121308_add_type_admins_table', 21),
(41, '2025_05_11_143643_add_admin_id_users_table', 22),
(42, '2025_05_11_151343_add_admin_id_live_videos_table', 23),
(43, '2025_05_11_152910_add_admin_id_categories_table', 24),
(44, '2025_05_11_153744_add_admin_id_colors_table', 25),
(45, '2025_05_11_154133_add_admin_id_ages_table', 26),
(46, '2025_05_11_154306_add_admin_id_animal_pens_table', 27),
(47, '2025_05_11_154832_add_admin_id_cities_table', 28),
(48, '2025_05_11_161034_add_user_id_live_video_items_table', 29),
(49, '2025_05_11_173349_add_is_active_live_videos_table', 30),
(50, '2025_08_14_160440_add_is_verified_users_table', 31),
(51, '2025_08_14_160854_add_files_users_table', 32),
(52, '2025_08_17_141255_add_videos_types_to_live_videos_table', 33),
(53, '2025_08_17_151449_add_rows_to_live_video_items_table', 34);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\Admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'asd', 'asdasd', '2024-06-06 09:48:15', '2024-06-06 09:48:15'),
(2, 'asd', 'asdasd', '2024-06-06 09:49:27', '2024-06-06 09:49:27'),
(3, 'asd', 'asdasd', '2024-06-06 09:49:48', '2024-06-06 09:49:48');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('00192540b22d503ee5e4b276d06bf5421f612980195b73b5305278a5cbed514e69017b6a87d33322', 69, 1, 'MyApp', '[]', 0, '2024-07-16 11:29:30', '2024-07-16 11:29:30', '2025-01-16 11:29:30'),
('00777b4e3adc7629744f16d1f3578a6339ac2c175ecf40f3f68a6810106d53474d85f4a3f65f7b43', 8, 1, 'MyApp', '[]', 0, '2024-02-14 16:48:48', '2024-02-14 16:48:48', '2024-08-14 16:48:48'),
('007d14e31ea72550368a060e0c6d18d423bedbeb47403649aa0bdff913f357d3469c4cf1decc677b', 58, 1, 'MyApp', '[]', 0, '2024-05-22 11:24:24', '2024-05-22 11:24:24', '2024-11-22 11:24:24'),
('00914888d98b5c853c78c998417087540f8284db54d117fb9179775942599b9c3d7ebc0ea3bcb75e', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:47:12', '2024-07-12 14:47:12', '2025-01-12 14:47:12'),
('00bc755e0f4cbd4a38246b6718a62e73f40a7dc250ac6e3bfdc5831f02460fda3d495482c4b1af6c', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:17:45', '2024-04-30 13:17:45', '2024-10-30 13:17:45'),
('00efe1f4edbfd630a4998e08cc831b252bd15e89ccf2cc0df86a2ce2d154d0f281022807c584a5e1', 5, 1, 'MyApp', '[]', 0, '2024-04-21 11:52:03', '2024-04-21 11:52:03', '2024-10-21 11:52:03'),
('00f4ac95aee9a0002cb0202d0bd25a0e2dcf2e9868a10fe7269c92f8fc6dd8fae46acedbdb2eeb5e', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:50:09', '2024-04-21 14:50:09', '2024-10-21 14:50:09'),
('017072de23871b6b3b317b5f5092d218b6ed3439ce8ffdc09fa1734988b3bd7652ec04a7c6b31272', 65, 1, 'MyApp', '[]', 0, '2024-07-01 16:08:10', '2024-07-01 16:08:10', '2025-01-01 16:08:10'),
('01e3f3682dad6f0cc87a65ff029c4198d4bcc3d6628e774865032993ad635a880983b892621df970', 7, 1, 'MyApp', '[]', 0, '2024-02-14 16:45:47', '2024-02-14 16:45:47', '2024-08-14 16:45:47'),
('0236689acfa10be8c4d11a4f7a67f6ee9f62da8b2408c21e402a4892e80badeff2ea5e8ba19cb18a', 62, 1, 'MyApp', '[]', 0, '2024-05-30 14:51:33', '2024-05-30 14:51:33', '2024-11-30 14:51:33'),
('02dbdd494892cf4536173e7998501bb073d20034418bde86fd3ca8ce2abb9bb774ed01a3d8c23a2e', 58, 1, 'MyApp', '[]', 0, '2024-06-05 11:19:23', '2024-06-05 11:19:23', '2024-12-05 11:19:23'),
('02e91932d712aa71b84d9cf57c54d0f33e202cabaa4c4f9d348d7e9571974a4f9d156563a81c7f32', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:54:27', '2024-04-28 14:54:27', '2024-10-28 14:54:27'),
('02ecb208bc879c11a5193e2f73a4865e170f6944cd43bd25ba6de39dd00935516f84f9ac68d338c1', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:57:54', '2024-04-29 15:57:54', '2024-10-29 15:57:54'),
('02f6dc41b2e82770a88c17bb5a8aee98e871a701ff2e354f8786dd4df644b92389fe20e86c033e0a', 63, 1, 'MyApp', '[]', 0, '2024-06-05 11:06:59', '2024-06-05 11:06:59', '2024-12-05 11:06:59'),
('03210acc90246f98319e1a00c47779097fb247c09a3f51d82efd6d9bc895c633b6517930ba4b4adc', 66, 1, 'MyApp', '[]', 0, '2024-07-13 18:01:25', '2024-07-13 18:01:25', '2025-01-13 18:01:25'),
('0326b63233a5effbad294c964b5674327ad97480f022450e04118461dc5feb9ba1cbaddcbe4b9b24', 53, 1, 'MyApp', '[]', 0, '2024-04-30 12:00:36', '2024-04-30 12:00:36', '2024-10-30 12:00:36'),
('03648c64b675e8d36e92c91741d3655efdecbdc9051431fb67c5e72cbcc6177bfe40353a0072372f', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:50:27', '2024-04-02 16:50:27', '2024-10-02 16:50:27'),
('03cba7f29aca6b7d0f425e4cd5fd57b25e4ca3ac2a50c7c89c80aaefa84d2089344efa5408e38f38', 10, 1, 'MyApp', '[]', 0, '2025-08-17 15:01:18', '2025-08-17 15:01:18', '2026-02-17 18:01:18'),
('03d160fe68c6c835d2ae8abd42ad58a5c17adca2b7315d0ae36613e3bdf2842989aea9bddc1837e6', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:41:35', '2025-08-14 13:41:35', '2026-02-14 16:41:35'),
('042559664dee8fa8ef97ecda84f6f002d603d8c347cfbd7823af3169043793e73c542bac43e815f5', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:25:51', '2024-03-26 12:25:51', '2024-09-26 12:25:51'),
('0439944e7d5eea9aada67149e364fd92e27a031c6847b016ded837b31c156720a6580fb9a1f25fdf', 5, 1, 'MyApp', '[]', 0, '2024-04-17 12:58:59', '2024-04-17 12:58:59', '2024-10-17 12:58:59'),
('046a03dc4cac82cd55ede1f0cd51f673a0e39f99f5049eaac29b679b71917c69c98abc93cd7738aa', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:49:48', '2024-04-02 20:49:48', '2024-10-02 20:49:48'),
('0483beda8dfc5671c32a844170b17f2f972f768d92856cce3d57dfa3ef11050d2c9dd2a71d97623b', 62, 1, 'MyApp', '[]', 0, '2024-05-30 13:38:50', '2024-05-30 13:38:50', '2024-11-30 13:38:50'),
('04b8714162bfc3e4fa3529d6d624a8abae3605d02f97351af06f90175cc6dccba6d50f4e91f11506', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:48:34', '2024-03-24 17:48:34', '2024-09-24 17:48:34'),
('04defee3fd279256dc396532ee56106679f8a2ecbaec84c03ef0ab954a64391d87f39766504e9125', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:24:40', '2024-04-21 13:24:40', '2024-10-21 13:24:40'),
('04f0ec3b487dc21f991a6059101100e7396066a20920c9337bfd1531ba401d5e40db3badc27f2a39', 5, 1, 'MyApp', '[]', 0, '2024-02-15 09:46:11', '2024-02-15 09:46:11', '2024-08-15 09:46:11'),
('0515de6c120d64d353071aa9d61d0d26456716034b2b72d0fb9377c4c8e0c85c678b5edf7c7fc904', 37, 1, 'MyApp', '[]', 0, '2024-03-26 15:54:32', '2024-03-26 15:54:32', '2024-09-26 15:54:32'),
('054a31c8f4d710131844eb8aa07ae9f086748df3d452c7af168358510c00651093049f3a0cd6c597', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:15:50', '2024-03-19 18:15:50', '2024-09-19 18:15:50'),
('057fcb530a37f7f64d15679c05695bc10c5a53bd3a076e3c6add1af441ba015503472ad5269014a7', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:11:18', '2024-04-29 16:11:18', '2024-10-29 16:11:18'),
('05af206cc4ebf0b00bf9630b350221edcc8ecf2fd2e4a73dff90b7b98c481731aadb6737eed8529a', 67, 1, 'MyApp', '[]', 0, '2024-07-15 09:16:07', '2024-07-15 09:16:07', '2025-01-15 09:16:07'),
('05c1487f36903e2d620b6fad00c76c4e4193439b13f4262c76a229f6198e26e64d41f9cb40cfc71a', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:47:36', '2024-04-24 15:47:36', '2024-10-24 15:47:36'),
('05efb89a21a6ee0f32e5e6e79116371da8bd3a839f23d2a62dca9343229d645957777e7e6cfa08da', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:56:02', '2024-05-02 12:56:02', '2024-11-02 12:56:02'),
('060d9db1d6f047b5bfdd0673867101ff099817531c1cb81f2f980aff02e4405e896090fb31ba076c', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:17:29', '2024-04-23 16:17:29', '2024-10-23 16:17:29'),
('061d14af7af5c177fb19574193dec241d72483eaa3889d6778e7408886621e41bbd46a040fa0d6d7', 5, 1, 'MyApp', '[]', 0, '2024-02-21 17:28:04', '2024-02-21 17:28:04', '2024-08-21 17:28:04'),
('06539280ce77e279df886e6d1883897d8c729c0c2c1472d22440d11aafd0f0934b123349287f2bfe', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:29:34', '2024-04-23 12:29:34', '2024-10-23 12:29:34'),
('0669ce0fe83fa479999266cae9ab5f199ce56b84594c6620a14ff6f3c9d3709b659916cfe32251f6', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:36:40', '2024-04-23 12:36:40', '2024-10-23 12:36:40'),
('067f1571f840029c7291ef14ed331ad8aeead83813f0ad896b2c9912c2b1d333724c84e865d4d18d', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:43:28', '2024-04-17 13:43:28', '2024-10-17 13:43:28'),
('0681c5aa1c107a28d61edcb913686faab176a4ee74819ebfb7b906a082cabe3543961541fbbee69c', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:41:03', '2024-04-18 20:41:03', '2024-10-18 20:41:03'),
('06d447219f15d509f07cae55a3c731a1f299b8b7fc08d83ac00856f68481a1edd4e2649c8d126301', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:41:18', '2024-04-18 12:41:18', '2024-10-18 12:41:18'),
('06dfe9705b3feab9cb7e15a6d0677be8182b691fb7f96e137f49989e30c378146ef26540f9bd70e8', 58, 1, 'MyApp', '[]', 0, '2024-07-03 15:11:01', '2024-07-03 15:11:01', '2025-01-03 15:11:01'),
('07a7aec167fe2d8f42c0e9176ce2c7a7d6be038a2ecf6665c81f0059b193256f304b8f699cbaaada', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:31:30', '2024-04-23 14:31:30', '2024-10-23 14:31:30'),
('07aafbfa215a4dd3e75b68d81c6b1c4da32eabe9d5e4bf2d70cc3f3158b2f8bc066cc477f69fcd31', 60, 1, 'MyApp', '[]', 0, '2024-05-13 15:07:38', '2024-05-13 15:07:38', '2024-11-13 15:07:38'),
('07b5cf408f1c91bc6463c0313a3b173c9e7ffb049359de3b8f67410049ce64bdec0b67466c230975', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:25:31', '2024-04-18 12:25:31', '2024-10-18 12:25:31'),
('07cb1b6a0961629e44c60af9ef0829c688d64fae1ec77d7030a80377e140392f331d639bff12e126', 66, 1, 'MyApp', '[]', 0, '2024-07-13 18:00:47', '2024-07-13 18:00:47', '2025-01-13 18:00:47'),
('07e4a673fa20a6197ffc38684a94c1b984a9540f290d849a65466d50a8096e07ebc9f83e107c27b1', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:39:30', '2024-04-23 13:39:30', '2024-10-23 13:39:30'),
('07f754af3c51f7dc91acc5915b2a8ed609e82323593933f34b1f59b8683a615a744f661979ccaf59', 5, 1, 'MyApp', '[]', 0, '2024-04-29 10:52:38', '2024-04-29 10:52:38', '2024-10-29 10:52:38'),
('07fb055e324b1534a269c1699bc875398f83a501111dc3e30c3e36fa4e8f6e7454053b5c7bb0a3f0', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:44:08', '2024-04-16 19:44:08', '2024-10-16 19:44:08'),
('08307ae34b65330b2552faf1fe02bfb8fc83d9119ff1c6e3d837a3fd833155019622326041a4681d', 71, 1, 'MyApp', '[]', 1, '2024-07-23 09:42:55', '2024-07-23 09:42:55', '2025-01-23 09:42:55'),
('08653ecba089de775fc1b8d49dd57e3703059c50945c1e3a396a0d5498a7d4287de04106bcbee716', 5, 1, 'MyApp', '[]', 0, '2024-04-25 19:28:06', '2024-04-25 19:28:06', '2024-10-25 19:28:06'),
('0880e037371171414f33a6fdb850e662eca499b8c47b749ccb344bb9e0a4347e0c0d7c13650c5ef1', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:59:56', '2024-04-22 17:59:56', '2024-10-22 17:59:56'),
('08aa45701354559748deda26bfcc82192a2b96a0be4cc0526c2da20f3980e0bbd70df91d59365316', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:17:44', '2024-04-30 13:17:44', '2024-10-30 13:17:44'),
('08ea78d949b6f585e89574538818669a8536400d1d3071378810f1b6d9e50a5dfb08e078f6ae2c95', 58, 1, 'MyApp', '[]', 0, '2024-06-06 12:43:25', '2024-06-06 12:43:25', '2024-12-06 12:43:25'),
('093abc5454ba420761fd9b476f7744c714b4f1fc5a70f30aa16be62df22332b7f2c567d39c1d87ce', 58, 1, 'MyApp', '[]', 0, '2024-05-21 16:09:39', '2024-05-21 16:09:39', '2024-11-21 16:09:39'),
('09491b22ccc30d2bd579d55f7e0295990f12677e8ac8ae803e01abae415a774174784bf8b50a36f6', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:50:13', '2024-03-24 17:50:13', '2024-09-24 17:50:13'),
('0972a119f2766a93fa5d8ddb868d17c282cf458adcd2add025ef3598d446b339b3536b84ff1ca9af', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:50:09', '2024-04-21 14:50:09', '2024-10-21 14:50:09'),
('099beb7f068be55b0d60f3eec88d5cc2fe2c0c3e90fdbfdbfd7f077ddb1ed8874c331f8992d0b8d8', 58, 1, 'MyApp', '[]', 0, '2024-05-28 10:45:39', '2024-05-28 10:45:39', '2024-11-28 10:45:39'),
('09b49b67c8b0aa1593f95f4fb5e4448bac95fc9183f9ba6f6084a3208208ee3dbfdf267e2603a8e3', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:45:39', '2024-04-23 14:45:39', '2024-10-23 14:45:39'),
('09dda421b41e57a57d6c754d1eed4cbba70ccc8b4381dd1f334ea892a20e2fa6da208f2a5c355735', 62, 1, 'MyApp', '[]', 0, '2024-05-30 08:29:14', '2024-05-30 08:29:14', '2024-11-30 08:29:14'),
('0a132c9b73ab9ba3aefd94f0f56a5a3c35deaa2bb7898e7a8fe32add057de9a2e7fc6da8e48688e8', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:25:30', '2024-06-05 13:25:30', '2024-12-05 13:25:30'),
('0a2068a8ec28a6ed0416814cfb93819f35c7c69398d46000ed2488fe4688802b112d87f723b3cb56', 5, 1, 'MyApp', '[]', 0, '2024-04-23 15:31:09', '2024-04-23 15:31:09', '2024-10-23 15:31:09'),
('0a36c27e3d47183b7689e6962016a8ca91e9123b53255dbc6d086f02246ce246452eb20c25b27dc3', 57, 1, 'MyApp', '[]', 0, '2024-05-02 18:31:32', '2024-05-02 18:31:32', '2024-11-02 18:31:32'),
('0a5de9f234b236c7480e3306682cbfbacea60bf08b618b5912304e858dd968e851eadf3a2a5aa247', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:20:49', '2024-07-13 13:20:49', '2025-01-13 13:20:49'),
('0a8eddee349d76a029f25dd731b71717d49dbc106b77106b06d0f5c004ba410bff08c217826c45f4', 58, 1, 'MyApp', '[]', 0, '2024-05-13 12:00:50', '2024-05-13 12:00:50', '2024-11-13 12:00:50'),
('0a9bd8fee968ac446527977a3c5db67b0664ed5be616c0c4cce6785482e423b1dbe0f35b0f3e3349', 35, 1, 'MyApp', '[]', 0, '2024-03-28 18:46:11', '2024-03-28 18:46:11', '2024-09-28 18:46:11'),
('0aa13a2502c475e8e41363e5bbeb429d9240903147a53b6758b49a9ba3fe1394967c09f34aadf85d', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:01:54', '2024-04-22 18:01:54', '2024-10-22 18:01:54'),
('0ac08cae13d0ba5d1847c52c287d468c879e6fe94236c2c7447fd47e0d1b6bdacf4d758742b3af4b', 5, 1, 'MyApp', '[]', 0, '2024-04-22 16:38:44', '2024-04-22 16:38:44', '2024-10-22 16:38:44'),
('0b296091f6bb7526ac59895c8c377008df19ab1ef6cd4cab3ded0be762acc54219c5a5309f6c3152', 30, 1, 'MyApp', '[]', 0, '2024-03-19 10:18:22', '2024-03-19 10:18:22', '2024-09-19 10:18:22'),
('0b30706bb4e1be216fa4be8518c0ea4ef0276e9d72afcb169f9fddfc34b5666b93f677d3ff19c14a', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:16:36', '2024-03-26 12:16:36', '2024-09-26 12:16:36'),
('0b47b976fd84a934197b9fac2e6066831ffc0f2f36d71b9f534a259fab5098aa51efdf196a09f150', 5, 1, 'MyApp', '[]', 0, '2024-02-18 13:55:54', '2024-02-18 13:55:54', '2024-08-18 13:55:54'),
('0b66080ebe71c6a3ba37babedb9955d63711ab4d6dc7a844f81d3049db6109ada0ef958b80b4bd50', 55, 1, 'MyApp', '[]', 0, '2024-04-30 17:53:44', '2024-04-30 17:53:44', '2024-10-30 17:53:44'),
('0b7ea1b45aa4ecdf65b4e31e2b8547dd4bc076e8fc9334b08c09772f192c1e80754f8bc557469c45', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:27:29', '2024-05-13 15:27:29', '2024-11-13 15:27:29'),
('0bbe5f0d93d8a75acfb822ed627f0112eac23c357d213d50cdb6943a0f576091e90c66bc21e25b3b', 71, 1, 'MyApp', '[]', 0, '2024-08-01 11:27:34', '2024-08-01 11:27:34', '2025-02-01 11:27:34'),
('0be78d3052ac55a66b6720660578c270a1c444f446d862457d5c5f5d3f426fa6afd789cb0c97acb2', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:08:01', '2024-03-26 04:08:01', '2024-09-26 04:08:01'),
('0bfd6a2bf8c8b028fa7bed143617055b165806dbc431817a254f8a615455bc3156027fcfd30dbf81', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:54:19', '2024-07-12 14:54:19', '2025-01-12 14:54:19'),
('0c0178c86b211326d85ce707fb9d0cd3da1c113b5c888f81a724fc21bce752b559eb58a224386a94', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:23:53', '2024-07-12 15:23:53', '2025-01-12 15:23:53'),
('0c1421deaed6293962039400ec849f603761e58ad8784848bf381c98f549372d762ab65dd274f137', 58, 1, 'MyApp', '[]', 0, '2024-05-15 09:17:36', '2024-05-15 09:17:36', '2024-11-15 09:17:36'),
('0c62a16c77a888ca41df30a9a7a240250120aa45561ca3688e97c915fd88cb6aa17ed8a68323f20e', 5, 1, 'MyApp', '[]', 0, '2024-03-06 14:32:36', '2024-03-06 14:32:36', '2024-09-06 14:32:36'),
('0cff59fe4572b94e995d8df7f5e4b64e5f1382e5307d08c6f9d8e7ad6e5eaf94e00287c3d5978d9c', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:47:48', '2024-03-20 17:47:48', '2024-09-20 17:47:48'),
('0d121f63dc31627bc8d48c4b1a54d933869d3009734edebdde3f6e4630cad018fbf6169c92007fdc', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:14:23', '2024-04-17 13:14:23', '2024-10-17 13:14:23'),
('0d4b7e1f1387f0619d23e7d668bb15920c36a531ff025f3634db91a54d461ec200e6929d0bf9208d', 58, 1, 'MyApp', '[]', 0, '2024-05-08 14:38:52', '2024-05-08 14:38:52', '2024-11-08 14:38:52'),
('0d56c0a1f4ffb956cd54ebbcded281f92dee42358fee54cf4bd3f666fb38195c39bf078eee6fce42', 5, 1, 'MyApp', '[]', 0, '2024-05-01 18:39:56', '2024-05-01 18:39:56', '2024-11-01 18:39:56'),
('0d5e7a03d2bdfc02edce57a53ab197a1ce4c5395225ae3384104eea793a82353dc6c65735b18f15d', 5, 1, 'MyApp', '[]', 0, '2024-03-06 13:49:18', '2024-03-06 13:49:18', '2024-09-06 13:49:18'),
('0dfa97af1f08926db74280098d34bb91cb504f2e0f9cc881d196af24e91b74abd9d44d3b4d15bf16', 5, 1, 'MyApp', '[]', 0, '2024-02-14 17:58:54', '2024-02-14 17:58:54', '2024-08-14 17:58:54'),
('0e08e2d98a792fbca33798ffdeec21d8aea40c9ed5ae8647898707f82a24c8ce12059f2a91cd18ce', 58, 1, 'MyApp', '[]', 0, '2024-05-28 14:05:44', '2024-05-28 14:05:44', '2024-11-28 14:05:44'),
('0e23dcf0d7a247a219337bad5d3942ba566b20a6234a27ed8e0f7912b0f7849fd820d1e1d8a4f329', 5, 1, 'MyApp', '[]', 0, '2024-04-14 17:40:21', '2024-04-14 17:40:21', '2024-10-14 17:40:21'),
('0e428a33ac5ff2291a0dd41371af9cf337ea10bdd20562d8476e7238193ea253ddccf9c50783d19f', 24, 1, 'MyApp', '[]', 0, '2024-03-06 14:05:58', '2024-03-06 14:05:58', '2024-09-06 14:05:58'),
('0e698cbfd93b02f361d77829a49ff39c4167e9d68ac59f592297d273e46a0b3b5f4e2b6ceac60a1c', 62, 1, 'MyApp', '[]', 0, '2024-07-08 10:24:38', '2024-07-08 10:24:38', '2025-01-08 10:24:38'),
('0e908580824c9c6ab3f6d76dcf5337a9a7294455fc1e7c3c904bc34dcc4f3e6cbc2c6a51205901b3', 58, 1, 'MyApp', '[]', 0, '2024-07-03 15:11:01', '2024-07-03 15:11:01', '2025-01-03 15:11:01'),
('0e9ba6bd61ee83e9762c46f6dbf55a2876b6a6832050d239d6902c5f911fd1e8f7e47c77498dfa3c', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:40:04', '2024-04-18 17:40:04', '2024-10-18 17:40:04'),
('0ea11d045a9e08adcc6e9f08b6fd7151025c2791ebc32c86910dd3ecbb1f4e8d20ac142f2313e23e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:48:18', '2024-04-28 13:48:18', '2024-10-28 13:48:18'),
('0eb0cf762bd0cd56ac0598b298e6288202aee3db4a5d7e2ead143a4501c549883e8afcec62c5893a', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:36:18', '2024-04-28 15:36:18', '2024-10-28 15:36:18'),
('0ed1f240d75ec4b6c74cf3ef3e75d48adfe4351c9a81bf08731bb01cecebc567760b83e14ec6f027', 55, 1, 'MyApp', '[]', 0, '2024-04-30 18:31:54', '2024-04-30 18:31:54', '2024-10-30 18:31:54'),
('0f0d24bf3e9ed5b67feca4e2e3172788e404998b2c7f08d3b13d58c5cf27bddbcd62cdc19fd28e09', 40, 1, 'MyApp', '[]', 0, '2024-04-02 15:43:53', '2024-04-02 15:43:53', '2024-10-02 15:43:53'),
('0f28815d3e5c10ddd60acbb32225a749ef06c8ad1d0e47bf833cf956dbb012b9454e91d40446d174', 5, 1, 'MyApp', '[]', 0, '2024-04-22 13:37:01', '2024-04-22 13:37:01', '2024-10-22 13:37:01'),
('0f2a0ad28a033a90aa9ec7a2f7d0907c67427b26f3e28a511dbeadca4bb1f279d4f01d9dc8561055', 5, 1, 'MyApp', '[]', 0, '2024-05-01 18:39:56', '2024-05-01 18:39:56', '2024-11-01 18:39:56'),
('0f47e58e576fb7b66c223bfeb62f058adf74b9d70d9a2893019fae6851820cdb3221be41be9f38ee', 66, 1, 'MyApp', '[]', 0, '2024-07-13 14:04:18', '2024-07-13 14:04:18', '2025-01-13 14:04:18'),
('0f48b6a4a40e175119573c8dec343165d070dfb5ce1f7cbb67f1aaa6de1fe8e9f06e530bede774c9', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:42:04', '2024-07-07 16:42:04', '2025-01-07 16:42:04'),
('0f48e510448c243d754d61a108e51a65027a5e35cf45bb8ea689c9e33594c355bbf60d52ec068df3', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:30:03', '2024-04-29 13:30:03', '2024-10-29 13:30:03'),
('0f4f57920745ad79864a46ea202ce90a73ed540e3c9ecb0b358ef5f313dc1ed7bcafdfec51bf0017', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:05:11', '2024-04-21 16:05:11', '2024-10-21 16:05:11'),
('0f5d2be11c77fb5457e2c123ab9b81989c1526ec2e9e4e8ad8d2ddd5985c636553ef940f762cb229', 74, 1, 'MyApp', '[]', 0, '2024-07-30 11:16:48', '2024-07-30 11:16:48', '2025-01-30 11:16:48'),
('0f6b4c516cc72b2ac05aa280595d4a25b5efdabec1d975d7f6f8978f1129147c927ed6d540bc32de', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:21:11', '2024-03-26 12:21:11', '2024-09-26 12:21:11'),
('0fa5dab46352f93b7aec76b2fbf3f6d13a7d77db62c422d196a95dbf0a420535c8aa881e87d7ec8d', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:12:07', '2024-03-19 18:12:07', '2024-09-19 18:12:07'),
('0fb7d1f1de7cab5fc0b9521d4b7d92458314411500107752a377224edd9fec1d66de0ae95776fd02', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:38:33', '2024-04-28 14:38:33', '2024-10-28 14:38:33'),
('10501727ac7a9717231beec64d4a72e516ea626f069d75d63f0e0d4db11acad87c9696d0f3b22d86', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:34:09', '2024-03-07 11:34:09', '2024-09-07 11:34:09'),
('1053b55321fbfb5fd12aca52b57acd4dd7a9055bf5a138a400f55784ee1fcbb667c23f7c46e8ba67', 5, 1, 'MyApp', '[]', 0, '2024-04-17 14:59:01', '2024-04-17 14:59:01', '2024-10-17 14:59:01'),
('106ed311654a5ecd600df181bd9b5f51d077097b790956b08e674adca8128e394ed52118c5ca579a', 16, 1, 'MyApp', '[]', 0, '2024-02-20 18:51:15', '2024-02-20 18:51:15', '2024-08-20 18:51:15'),
('1081893d2d0523d4c499328c0b7cd25fe4bcc7bce89d7c09fb0ab3cde020faaf3f9b4b5aa76c1a28', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:32:57', '2024-05-28 15:32:57', '2024-11-28 15:32:57'),
('10856cdef8403355a9a80d49534968d1612dd0fc4c628f32bade565d0b4a9fd0772e226244b196f9', 2, 1, 'MyApp', '[]', 0, '2024-07-08 10:58:46', '2024-07-08 10:58:46', '2025-01-08 10:58:46'),
('10bd371d2edda7a11cec2fc79d0efcc925d0ab50d61f53bf702fccccb41eb863beea483d60473567', 35, 1, 'MyApp', '[]', 0, '2024-03-25 12:41:36', '2024-03-25 12:41:36', '2024-09-25 12:41:36'),
('10d3938fc35671ec5902c5e3a47419e76d46e05d1081b121cb71f76790475cf752f5a38ad20d8512', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:22:07', '2024-07-13 13:22:07', '2025-01-13 13:22:07'),
('10ec156804a3ca19e5e8fb1f1a0a8386068a909da7b69d650cae452ccd910955e8747eaf89ebf605', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:30:46', '2024-06-05 13:30:46', '2024-12-05 13:30:46'),
('10fabd83f54fbec381ed1b37a39148d46c828c621f0c7f229c38eae46f6c957971405d50ab48f5d9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:32:22', '2024-04-22 18:32:22', '2024-10-22 18:32:22'),
('110428d4fdb894a3e5ddd0571cc678e0ec79ce36f9a7f32f5eb5b6cbb65ca1ddc645be930e8d4ac9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:54:45', '2024-04-22 17:54:45', '2024-10-22 17:54:45'),
('1109bc372692600d0b2dd788f3449b82e23101da806044ee1a4f8f3eb398fc25c6642cc9325a80b8', 58, 1, 'MyApp', '[]', 0, '2024-05-21 16:09:40', '2024-05-21 16:09:40', '2024-11-21 16:09:40'),
('11357b3217989c4b6f6427f183bb2fc75728e616eb0f2f7c76cc95f0125b9a84d464cf9aef507497', 64, 1, 'MyApp', '[]', 0, '2024-07-07 16:22:10', '2024-07-07 16:22:10', '2025-01-07 16:22:10'),
('1140af9f9a84439c733febb52bfbba6ca096361725584b928aa8d3ca04f224e4263466279a645a10', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:40:09', '2024-04-28 11:40:09', '2024-10-28 11:40:09'),
('1178b92d29b69ee8cd17274ad95cb5f31124a5a7f9d32d16b20f572c8f687e43f4fad24a64c1336c', 5, 1, 'MyApp', '[]', 0, '2024-04-03 20:45:58', '2024-04-03 20:45:58', '2024-10-03 20:45:58'),
('11b1117e15cb7b0f897da51a19dcbf7c13669c9b4e2d596b36dab11a8190ea5f6fdd58946eeedc31', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:18:21', '2024-07-12 12:18:21', '2025-01-12 12:18:21'),
('11b8943733203ffee94bc78aa5f7a07851cca5ef4362eb9616d85b70718fa4437f4d068feccc6bae', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:29:24', '2024-07-07 16:29:24', '2025-01-07 16:29:24'),
('121004254052ccdb15f08a59fb7a748257bf83256e38b2a14a08326dace36858979e029708a1fc51', 35, 1, 'MyApp', '[]', 0, '2024-03-31 04:51:46', '2024-03-31 04:51:46', '2024-10-01 04:51:46'),
('1240ce86e35e5154a99f9a562fa13e3fb4c618c4b9b3c6af30194a43368112f6135dbe2cff373e38', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:23:59', '2024-04-17 17:23:59', '2024-10-17 17:23:59'),
('1264f36769c2be118f082165a5d4b01febc87a6503fe8bd7bfcb9ecd0b0d25cf2084d59de50a15aa', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:51:44', '2024-04-18 17:51:44', '2024-10-18 17:51:44'),
('126cfae91239e2d4db9c7abd50a8ca5f78e72013eddf4cd73969156a580fde2b4b5f5088224c1717', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:32:29', '2024-07-12 14:32:29', '2025-01-12 14:32:29'),
('12bbb8311416bf8cf9d84ce42e55f256b8788d91be73d06b12d518a712b1adfdf5f0277028a13b00', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:35:54', '2024-05-14 10:35:54', '2024-11-14 10:35:54'),
('12ed8959e65b770da8707df12e0fc3c12e0e52b7f3dee0be32a38ec6024d30cfc53224e85c15288f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:00', '2024-04-28 14:59:00', '2024-10-28 14:59:00'),
('13053c91509dd3def91fd5dcde3ace6b2c183344543dff7f1cc795bf9dcaf015b78dcde259bd38b8', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:26:12', '2024-03-26 16:26:12', '2024-09-26 16:26:12'),
('133004baf27bb0fb1b05cd2b425bba46b3477b724cdd723af87cf8f443b52530af84a83bf05a2cbe', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:08:35', '2024-03-24 18:08:35', '2024-09-24 18:08:35'),
('13315100f8421d082ce09e8cc6d42702618c53d6a8c5ab28eb52d803e1f44031631eada21b33b113', 5, 1, 'MyApp', '[]', 0, '2024-04-25 19:12:18', '2024-04-25 19:12:18', '2024-10-25 19:12:18'),
('1337e97c16b57edc7bfa2afd84631a279fe0b3f5807ee96570ca11f458438930682906b9ab883725', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:17:47', '2024-04-25 21:17:47', '2024-10-25 21:17:47'),
('134e9c5379f05b3a557490c6d4020cb2b72370896cf71aa3e7ef8a7cf7bf2bdbc80497002f5da2fa', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:50:22', '2024-04-25 18:50:22', '2024-10-25 18:50:22'),
('1388bb9cf5b9714cb3b0bd8529416f52312cd8394a1ab719b01aaf81bfea2096b765339b51bf29e3', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:51:44', '2024-04-18 17:51:44', '2024-10-18 17:51:44'),
('13a9d633621eb792c9f40fd99a68944fa1fb7e963c06caec8b7704a333eb11b35ac0fc2b1748ed4f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:58:41', '2024-04-28 14:58:41', '2024-10-28 14:58:41'),
('13bd4e51bdf1617ec7fa877da5c4b1f90f6756fb5ab73b34034a2a0cbf85c367e17128e2554fd756', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:50:07', '2024-04-28 15:50:07', '2024-10-28 15:50:07'),
('13c34be4f3d56f13fc2b06b9c2a52ac8d884cab3564f674d40110384174567a258a5eabe1e9cb920', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:53:33', '2024-07-12 14:53:33', '2025-01-12 14:53:33'),
('13f19820d3e5aec6b6fd148d35e93ee04ab765069f13524f17aa1d7a2df44a38e8ab90f75334551b', 69, 1, 'MyApp', '[]', 1, '2024-07-16 11:14:20', '2024-07-16 11:14:20', '2025-01-16 11:14:20'),
('13f6d741d5a69752e58f723c96eae101786c4f239135f43a39e11624e3ab3733d60b3c5a529cb6b3', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:38:42', '2024-03-26 03:38:42', '2024-09-26 03:38:42'),
('142355c7869e49393a8f284800571709a441868297531c1f5c068eb5f5eddc5385fd3b9af4c0e596', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:33:11', '2024-03-27 13:33:11', '2024-09-27 13:33:11'),
('143a31cbc6c4298f7a4851ec57f2fd8bab10fa1b4d5b08f55a03a182cd480955b9c5c8b58784c40d', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:32:00', '2024-04-30 15:32:00', '2024-10-30 15:32:00'),
('1473c5c6ef29a0cb32bf38db5322f18e5fed049bd478c00b216384731a9cfd59d355613e678f6b30', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:08:38', '2024-03-24 18:08:38', '2024-09-24 18:08:38'),
('14a78a1f76f1e4b7025bb845ff0701dec814587222083af4a5e5acfb97ea901afe9a9fd531822899', 58, 1, 'MyApp', '[]', 0, '2024-05-07 11:29:04', '2024-05-07 11:29:04', '2024-11-07 11:29:04'),
('14d805728c65bbbfd17e0ab49cadd095c3c2ed5f83617af0af953635a64527164abfe237e96b825c', 5, 1, 'MyApp', '[]', 0, '2024-04-30 14:04:09', '2024-04-30 14:04:09', '2024-10-30 14:04:09'),
('14e162e79e7f4c70473655eb4a82c6413e211a2c2366a75d5e2f4fa586497e9ad4727721c3348d89', 5, 1, 'MyApp', '[]', 0, '2024-04-14 15:58:40', '2024-04-14 15:58:40', '2024-10-14 15:58:40'),
('152224938dc9c7201bbc6b338ff9d8ef84d0365416b1a0a01f23b8247fd523c3e7fc131a4974f839', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:28:03', '2024-04-18 21:28:03', '2024-10-18 21:28:03'),
('15375cdad25191e08f05e8c84d3b7bbe7393ab7a2a86c2a6a33d5d941a6cd5e1edaa2a8e120cba8b', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:22:12', '2024-03-05 11:22:12', '2024-09-05 11:22:12'),
('15484af62d99159dc9d73fa43c967477ece039fb329d343e82ad1785abfbc171ab0f99fb689c8a14', 66, 1, 'MyApp', '[]', 0, '2024-07-16 08:20:03', '2024-07-16 08:20:03', '2025-01-16 08:20:03'),
('1553d4b145c6369d477d1f1d1a6fe9e89610fa19c9b6dc2aab191cf73ae2a19cf94c77ac74cb217a', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:59:15', '2024-04-29 15:59:15', '2024-10-29 15:59:15'),
('15667f148ec332cb43591e8dccd4091ca75979b9b729e2a5b2a59e5807378154c70ae27e05316a5d', 35, 1, 'MyApp', '[]', 0, '2024-03-26 17:38:08', '2024-03-26 17:38:08', '2024-09-26 17:38:08'),
('157b20265ded62cdec1b8fa53f0895502deb44f6b667ebb6857ac2001d6cce321e2b0d8f44764908', 36, 1, 'MyApp', '[]', 0, '2024-03-24 17:52:07', '2024-03-24 17:52:07', '2024-09-24 17:52:07'),
('1590c774ab0576a53898fe1270f78698df6d06ccab4c663b12830df4c17dcf8b4322c7b32ac3d58f', 5, 1, 'MyApp', '[]', 0, '2024-03-17 17:09:35', '2024-03-17 17:09:35', '2024-09-17 17:09:35'),
('15be2633bddcddd77066ac8bf1e122d8a492ffdcd90df4b66bba9f074d890d8b2c08e4ace4b81b93', 64, 1, 'MyApp', '[]', 0, '2024-06-05 12:33:34', '2024-06-05 12:33:34', '2024-12-05 12:33:34'),
('15e1fd0cc362ec3b40671f93d87c8fee9ac53727a3bec458ea6ffd13fd34d431b2c175edf0bbcc6c', 45, 1, 'MyApp', '[]', 0, '2024-04-04 20:24:56', '2024-04-04 20:24:56', '2024-10-04 20:24:56'),
('16248a9796753071a5fbce358fb6bec6ff59dec7705ac2f6819fdbcd400dc66d49d327e63d1c0e35', 71, 1, 'MyApp', '[]', 1, '2024-07-26 15:52:56', '2024-07-26 15:52:56', '2025-01-26 15:52:56'),
('1633548680c391ccf9c0cc669c4044351f9c2682d73a4a9c1b4359b768b33e9204b148045f411314', 5, 1, 'MyApp', '[]', 0, '2024-03-24 05:05:01', '2024-03-24 05:05:01', '2024-09-24 05:05:01'),
('169539a2677ae2f64aa6a739a424a4882b52c89b1aa00fd794c1d69b6d8cfdb828ace72ebb65579e', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:23:53', '2024-04-29 15:23:53', '2024-10-29 15:23:53'),
('16a2cac029eadc27aa63f2d1c126c6363c6a551ad1dd177f51b9d7c6477a53a91d184a98bc922f7a', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:45:39', '2024-03-20 03:45:39', '2024-09-20 03:45:39'),
('16a68667a3d3059efaee05b1da66cf38bbd1ef431b68366b8bc1aac8892ba3e28ca37ee9d3366129', 58, 1, 'MyApp', '[]', 0, '2024-06-04 12:06:34', '2024-06-04 12:06:34', '2024-12-04 12:06:34'),
('16c24f90705a8f0226aa5bac14ff5bcacdf90adcf9b190fe397ab39220e7fbda04f69d77089ba2ec', 1, 1, 'MyApp', '[]', 0, '2025-01-30 11:20:41', '2025-01-30 11:20:41', '2025-07-30 13:20:41'),
('16d6671bbf9e746f6b07be6104f46789a0d9ee8f300d3302d48e120d5b62cb20bd3c21c045f5309f', 35, 1, 'MyApp', '[]', 0, '2024-03-28 16:08:09', '2024-03-28 16:08:09', '2024-09-28 16:08:09'),
('16d7a893b66a0f158944e07a1ee5d564f9252ee4f5aab2e75afaf9e5237b8a29a5d6a38dc40d87ee', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:42:18', '2024-04-23 14:42:18', '2024-10-23 14:42:18'),
('16edc119cb8357e31437a6eb33ecbf99f378bb7997d1247377145cdb8bb6a84f9da5344ec21fb6dd', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:04:15', '2024-07-13 15:04:15', '2025-01-13 15:04:15'),
('16f052b2558e757bdd62d28fb2fb4d4e0b08dc19e5f19a37aa393dfab2accc9d856ed543a3a638fe', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:57:36', '2024-04-28 13:57:36', '2024-10-28 13:57:36'),
('1701602465568635a0705011622e84091135982e2a70d0510547b1c5ec142867bd68ca4e3e379680', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:11:43', '2024-04-21 17:11:43', '2024-10-21 17:11:43'),
('177b18c11dd5e6f7f8c89c04750073e1cc5fad642d05a8ad5d28f12c939e21395a9d54a53ba845a6', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:48:28', '2024-04-23 14:48:28', '2024-10-23 14:48:28'),
('1793962f63c06f09453fbb2c02ab13492e9d669dcc7a5fd79a370024eb70415950fa3b7e623a8529', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:31:08', '2024-04-18 22:31:08', '2024-10-18 22:31:08'),
('17ecf711dfb85a08c5ddea06ff4f39734262556c795247e26cf27e162168ee8bafbf7b3881acc456', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:45:04', '2024-04-23 14:45:04', '2024-10-23 14:45:04'),
('17efde75da1511d9b5a9babbe255c0b6dc1c8a4b3538fa8259ac2914d78902a721ab01763d653a80', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:17:52', '2024-04-23 12:17:52', '2024-10-23 12:17:52'),
('17ff76b4de85792fc307fb9321539d9083621927bd99e33f1cef83817fec1b98dfedbb5fdaa44f56', 5, 1, 'MyApp', '[]', 0, '2024-03-05 18:05:18', '2024-03-05 18:05:18', '2024-09-05 18:05:18'),
('180e5af4badfdabc42e77e6b3046afb1d04a90f6311bbe8044db98f6f94fe23cf1e784220ef2688e', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:53:46', '2024-04-18 17:53:46', '2024-10-18 17:53:46'),
('182b1f24da05cf338194fd016bc1781c86b9ce54dec6133af370a03447a6d9fbe9b14cd4ad0ddec9', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:16:05', '2024-04-23 13:16:05', '2024-10-23 13:16:05'),
('183e4eeea95e5dff389977a7170491fa455cb29f40db0caebdcbf3314f2068b2e7ed44cab2bc0910', 5, 1, 'MyApp', '[]', 0, '2024-03-20 16:17:33', '2024-03-20 16:17:33', '2024-09-20 16:17:33'),
('185324ab5ff412a6c84f6c9996bc52d6ecf16c1db6be8cb7c86478c96bbf107474bd78c0d835c9fc', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:09:05', '2024-04-23 14:09:05', '2024-10-23 14:09:05'),
('1868667be400bde7c803d853893253a7473a13d117bf47cccf3bf5c4ae63424d777199742f1aee04', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:21:37', '2024-07-12 12:21:37', '2025-01-12 12:21:37'),
('18ab92be497ff318fe421d47a3fe7afc19fb9ba44f309f32ac991ccacebfd7626e1fac055eade180', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:32:24', '2024-04-16 19:32:24', '2024-10-16 19:32:24'),
('190f923960216c835fcfd9984207503994203ceb793cccc88412d666a2a539b0a92ac3a941f12413', 5, 1, 'MyApp', '[]', 0, '2024-04-23 15:22:31', '2024-04-23 15:22:31', '2024-10-23 15:22:31'),
('194c153159c56918d0720c875cbfcdc3ec849c177c3ea8884b9ad65384bbe513c4c82b2d9e736488', 66, 1, 'MyApp', '[]', 0, '2024-07-16 08:16:23', '2024-07-16 08:16:23', '2025-01-16 08:16:23'),
('1960c706d46be84642ff6cb8c7400d6297483004df0e35ed38732832a1753f9cef778e2303fa31ca', 39, 1, 'MyApp', '[]', 0, '2024-04-02 11:50:08', '2024-04-02 11:50:08', '2024-10-02 11:50:08'),
('199c563feb1f6699b893ddffba0743b6694f74d22cfe738f152839fb0c71161da7ac596d944bd38c', 5, 1, 'MyApp', '[]', 0, '2024-04-25 19:28:07', '2024-04-25 19:28:07', '2024-10-25 19:28:07'),
('19e3a036780e79604a1bee441aea8f2173ac7ff28e583857260bfe5dd007d4f1f490119664cbe567', 5, 1, 'MyApp', '[]', 0, '2024-03-19 15:17:22', '2024-03-19 15:17:22', '2024-09-19 15:17:22'),
('1a48148851a8e6882b00062b07b403211f6c9a4fb9a4bf399217fe6d86d0557ed7df0b053d0717d1', 40, 1, 'MyApp', '[]', 0, '2024-04-03 21:06:45', '2024-04-03 21:06:45', '2024-10-03 21:06:45'),
('1a805b1a71bc5b460c4fff1771acaa09904f8613063a036dac9e794bab45723f386b24077d86f533', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:36:52', '2024-04-02 16:36:52', '2024-10-02 16:36:52'),
('1aa5b41ea65f894b10ddb3a1065c743e99b80ca2cf3141f18a9cceabbe298469736ea11388fd996f', 5, 1, 'MyApp', '[]', 0, '2024-04-04 19:13:22', '2024-04-04 19:13:22', '2024-10-04 19:13:22'),
('1aa855439a65fe88f12aa2c650923795bc41372381f9965d099916556bd0ab1cab15b3ddac8e26a1', 5, 1, 'MyApp', '[]', 0, '2024-04-22 13:06:02', '2024-04-22 13:06:02', '2024-10-22 13:06:02'),
('1ab2bbbe37320c06564a20b14aeaf279ac9e916610208282bfe8c0e6fb90394333603ce26a847355', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:34:27', '2024-04-18 21:34:27', '2024-10-18 21:34:27'),
('1b017746bbff33d1b906c98d26184f1996d138a38bd60405e2f6ada5bad8135e346cbbad028f6f10', 5, 1, 'MyApp', '[]', 0, '2024-03-04 15:05:12', '2024-03-04 15:05:12', '2024-09-04 15:05:12'),
('1b143f5cb322a13c27407e3c9e87e9d75960e7b7c1bf2ff7de770c7f59a1ec6af2395ad630cfaa41', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:29:17', '2024-04-17 17:29:17', '2024-10-17 17:29:17'),
('1b27a6f1b2fee89bcf914ef21c703ae8367777256dd3eea73ffa4e4a5fba7d07f546b962107ffd25', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:48:03', '2024-04-16 18:48:03', '2024-10-16 18:48:03'),
('1b666c4a67c67275269ba19050dafca62cb940f9749fafd1a7b4ca00c0a5364a8ee7480806687324', 5, 1, 'MyApp', '[]', 0, '2024-03-19 13:21:30', '2024-03-19 13:21:30', '2024-09-19 13:21:30'),
('1b81fccdf25cfcb02443c88cb59526667418c08c91ee5bb98ab6004fe3353de8b68f7e6fd8bbde19', 35, 1, 'MyApp', '[]', 0, '2024-03-26 16:16:23', '2024-03-26 16:16:23', '2024-09-26 16:16:23'),
('1b87b57e053bba3b317bfefb11ef1e48f38137bd77d5592ba9b08f42f4a1a1172372b6ab05d8b5d8', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:33:06', '2024-04-30 13:33:06', '2024-10-30 13:33:06'),
('1bb1e64500e02a844037f13922ce18105f64a83bdafa0bb6838368c0ec55e9ae515428f1b9c24a8b', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:08:55', '2024-04-22 18:08:55', '2024-10-22 18:08:55'),
('1bef41e91d713c99244940d8f8b709554c6963fb4db97b67ac41df87d8cb4af66dd579e8ac084486', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:50:37', '2024-04-23 12:50:37', '2024-10-23 12:50:37'),
('1c24d7d6e9d4aa4fa4aac4197b159e4d24edebc32a687a230a6057cb85d7aa8273d2289e21dd7d42', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:36:02', '2024-04-23 14:36:02', '2024-10-23 14:36:02'),
('1c7894540fc9a39a1bf83bcf105de6e5b118e5053e230a6eb7c15ebb88ffc69583606a60e30a6be4', 62, 1, 'MyApp', '[]', 0, '2024-05-30 07:55:35', '2024-05-30 07:55:35', '2024-11-30 07:55:35'),
('1c7d199dbcc921f00bee957a1a697c129e0ad46a37f272a3946d525ac5add70fa43aae6f4085416e', 5, 1, 'MyApp', '[]', 0, '2024-05-01 19:46:27', '2024-05-01 19:46:27', '2024-11-01 19:46:27'),
('1c8c9e49ef988b5b0fe54ceb4f1d375d40272b1bc331b773b8cfce4d95d98cc1378e911e01b675ae', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:09:51', '2024-04-25 18:09:51', '2024-10-25 18:09:51'),
('1cc940d258d9fbd4cc0edf4dd1364d693e441326f790fb3eee6cc34de44196cdcf4c6021f934d970', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:48:59', '2024-03-05 11:48:59', '2024-09-05 11:48:59'),
('1cd9477cf7ee169a357612f055a965bb4f5e12be129ea5becafcc92b8fe3537fb3e55dfb6cb298ce', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:01:15', '2024-04-18 21:01:15', '2024-10-18 21:01:15'),
('1d02d7ed34e90db695f74e95c465a978cac58e6049f390ae8c89c798d68144458c88827ddba8e4c6', 5, 1, 'MyApp', '[]', 0, '2024-03-18 12:13:55', '2024-03-18 12:13:55', '2024-09-18 12:13:55'),
('1d07deb46ec49952c2c3a74b579069f9cb0c6f055cb692cf0387b5627d3d86b4f9aa712d3878ae77', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:39:10', '2024-04-22 18:39:10', '2024-10-22 18:39:10'),
('1d5055badafa4c3276efbc4d8f80aa55d29a108a1dca6d0ab40bb977b9cd1e450c8ca3de1a8ef046', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:49:59', '2024-04-08 05:49:59', '2024-10-08 05:49:59'),
('1d76585fdb79709c7c870d1c9360c04dbbbd2a425e8f84f6ffdaa1e6cc7e09f29417161a38809e87', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:07:42', '2024-07-12 12:07:42', '2025-01-12 12:07:42'),
('1d814f0a662c786ac2914168bfc3445edd9eca3dfbc5cf8de3bbe06bdfe35a061982ab1a7d0af62c', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:57:03', '2024-04-02 20:57:03', '2024-10-02 20:57:03'),
('1dff38439ff0bb9fcc8041c42d766375a820d9d2c980a206712b9806814921ed64332b54e03a6513', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:51:09', '2024-03-05 11:51:09', '2024-09-05 11:51:09'),
('1e1e055c499141f41d580b7f5fb0d8aba7a9ee9e5c7d07abec6440927ecc95cb0c9eff97bee08623', 5, 1, 'MyApp', '[]', 0, '2024-04-28 17:59:14', '2024-04-28 17:59:14', '2024-10-28 17:59:14'),
('1e4496932a123d35b693d6a9fc55ece80124792d3c5cad7e7bdf684197df495e9fd16fd5eb584dd7', 5, 1, 'MyApp', '[]', 0, '2024-04-23 17:56:35', '2024-04-23 17:56:35', '2024-10-23 17:56:35'),
('1e4870386a232bee60c56bbe951d106802eb0ad51f7a2a182b15b7d8a808db8e0a9245baad530f81', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:26:00', '2024-07-12 15:26:00', '2025-01-12 15:26:00'),
('1e560c380855fb4be21f6496a4ed2c19aeb9d1c42450c7f9fffe0f47d61084035029dd41d9481c3d', 5, 1, 'MyApp', '[]', 0, '2024-03-04 11:49:04', '2024-03-04 11:49:04', '2024-09-04 11:49:04'),
('1e60a8cfb86f6c4a8918c0ce0480e870689e8f59bd4a9be6db1066ead6dc01a51fb629d155c7a734', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:54:16', '2024-07-13 17:54:16', '2025-01-13 17:54:16'),
('1e785aee56f645387e84e3286a3c76de956cd8a0ea53e78c7f2d6e4eeecddf11b2660e08c0b71699', 5, 1, 'MyApp', '[]', 0, '2024-04-16 12:02:06', '2024-04-16 12:02:06', '2024-10-16 12:02:06'),
('1eb6da72c0856e5629ec20362a033b12e7c3230c30d288d49fed3de373c3796cbb25276a3bfe4084', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:41:01', '2024-03-24 17:41:01', '2024-09-24 17:41:01'),
('1f03a5b7b3d1e54cd1e6903b37912f8176f3ed005340b45829e7c2dc54115e69b92d075bccae28e2', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:50:02', '2024-07-12 14:50:02', '2025-01-12 14:50:02'),
('1f70424156784c6eeb6bbe4285515582b1046f065682cf9484790892e3c5dd7d982131d920bdad3e', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:38:21', '2024-07-12 14:38:21', '2025-01-12 14:38:21'),
('1f8b0f0ac70c0959114328290fe53dd376aef96c1635a9a198022f0d4ff73a0a1306068cd3efaa12', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:06:47', '2024-04-02 14:06:47', '2024-10-02 14:06:47'),
('1f90dbfca040a14eea32fea947e1f6c57f515615e95b2958f96fd61ae67e1c7a3f6f480721146f41', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:14:06', '2024-03-19 18:14:06', '2024-09-19 18:14:06'),
('1f9e7d20f9936ff9ab40af3a8ba55681076dd7df8b8299d892a83859d15502413f83da346d827198', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:55:11', '2024-07-12 14:55:11', '2025-01-12 14:55:11'),
('1fb8e7f884b712bff66229005727036e71f9e3b45fa9812666c03fc5b931f187b6f516a666bcc41b', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:57:14', '2024-03-20 17:57:14', '2024-09-20 17:57:14'),
('20380078d04fefe6a0081ac3d38efacbf4d2715b10bdbdd19e13962791e0d9495bc59701ab97983a', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:18:00', '2024-03-26 16:18:00', '2024-09-26 16:18:00'),
('2049f05ff25d196d851d41a97d94c467658e15782bcf969cf1a90d40dc8c4c1eca89f55105674d36', 35, 1, 'MyApp', '[]', 0, '2024-03-30 23:48:52', '2024-03-30 23:48:52', '2024-09-30 23:48:52'),
('206e3e692a3c448acb338489fec9899cf78f2fa5154980a5d32ab1c1e07ee9a9e218b8347f81dd81', 74, 1, 'MyApp', '[]', 0, '2024-07-30 11:02:09', '2024-07-30 11:02:09', '2025-01-30 11:02:09'),
('20c3ff8034a39d5b7b3d2d076ab2acf86a693570fe77010217c94281dd000efeb780d1737cbdf5b5', 63, 1, 'MyApp', '[]', 0, '2024-06-04 10:23:01', '2024-06-04 10:23:01', '2024-12-04 10:23:01'),
('211cfa494fe8353954802069c3f7d1e5e790039ae853cc88d894277e71b78228eac6ccd88480068e', 5, 1, 'MyApp', '[]', 0, '2024-04-15 14:21:13', '2024-04-15 14:21:13', '2024-10-15 14:21:13'),
('212fa3567d6c9c2b650973bee77a925e713335a279c5b1611e5aae59b7ad076f2bbe08ae3563aaa6', 5, 1, 'MyApp', '[]', 0, '2024-03-20 05:24:14', '2024-03-20 05:24:14', '2024-09-20 05:24:14'),
('2171c95f1df045fbbce08758d591e0b2af3a374437addfea3c2bcb53bbab8cf1ff5c9e9902b66e8f', 5, 1, 'MyApp', '[]', 0, '2024-04-15 17:24:55', '2024-04-15 17:24:55', '2024-10-15 17:24:55'),
('2174dab41f6476f0cdaa95e3f74a5f49736e69484c13bf72bdfc577221db6e9e8bf73d094060658a', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:40:38', '2024-04-02 20:40:38', '2024-10-02 20:40:38'),
('218a8674353550a5f68e3f3a37412b9225f112400d643ce1a5b12c7841f09677ae24544c752fff12', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:40:08', '2024-03-27 12:40:08', '2024-09-27 12:40:08'),
('218c4e5135270abee1a36d801c46291fe9b94cdb5ba2fa2fcae63a5fa57a380a8f9d3bfdfe31b65e', 5, 1, 'MyApp', '[]', 0, '2024-04-16 12:02:06', '2024-04-16 12:02:06', '2024-10-16 12:02:06'),
('21aa681edb21fca34fd681612a03746ecc47a820c350967b050fd279d117f6ae322a05fdc86ab748', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:01:30', '2024-04-02 14:01:30', '2024-10-02 14:01:30'),
('21bb997d2cb2fee23de747b4841d6ac038edc0e50366a16d671624bcdec280575bb6e7cac33139d1', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:04:12', '2024-03-31 14:04:12', '2024-10-01 14:04:12'),
('21e701b54d4f2626c9860e28629855a963a789c111eadacec736507c3f65897f4dc28a32037606e4', 5, 1, 'MyApp', '[]', 0, '2024-04-23 19:02:07', '2024-04-23 19:02:07', '2024-10-23 19:02:07'),
('21f227e3f4dd173fcb68f4da0ebf6ca6bf15cbb10909855b511a211a41c9698fbd8e2516aa0519a1', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:08:40', '2024-04-08 18:08:40', '2024-10-08 18:08:40'),
('21f413bb0e613622f01642f5cc43a0810706c03979c730186023b9750662425c3bf1ddfce435ec2f', 5, 1, 'MyApp', '[]', 0, '2024-03-20 15:44:59', '2024-03-20 15:44:59', '2024-09-20 15:44:59'),
('21fcc1aa246fbe2e9e348760186c5ef11d48fed0223d7ffbce4241f5d119222b16e0f5feb883992c', 35, 1, 'MyApp', '[]', 0, '2024-03-31 00:17:39', '2024-03-31 00:17:39', '2024-10-01 00:17:39'),
('223b7bd281ff4ccdfbc866b480d07aa4e64b0809d602cc09b03bcca40736105087feaf880154a17a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:16:06', '2024-04-23 13:16:06', '2024-10-23 13:16:06'),
('2248c270899c2cc1c729463eac3c1c37c2a8b66b7d35e00f4dca011dcff9a5eedba19784d5f40ca9', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:55:17', '2024-04-28 13:55:17', '2024-10-28 13:55:17'),
('229b85563a5600aef56a5b695a97a1e641d54db99034520bdca4dc402685b80013d23ed224b708c0', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:20:03', '2024-04-24 15:20:03', '2024-10-24 15:20:03'),
('231b6372ef41ee4ee82f3f1ee6adffa8507771b59d87db640a0d4e5ac15d0f78036cf12ba4c2ed3b', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:45:46', '2024-04-29 11:45:46', '2024-10-29 11:45:46'),
('231de38d16c53e9ce15cc96258ecb52c208834bc89147470347a56f4ab12ba556039f2df34a11702', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:30:52', '2024-06-05 10:30:52', '2024-12-05 10:30:52'),
('23422b9cdd2b9650d6c23573303cf48022e10b708612e29f5c15c4e5903908cfbe24fd62bb67cc07', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:50:09', '2024-04-22 17:50:09', '2024-10-22 17:50:09'),
('234667458b29ef3ba961fffd2153267817d1774e328ce3cef3a8b00a0af51ad24a5a476d31fb5eac', 58, 1, 'MyApp', '[]', 0, '2024-05-21 12:52:53', '2024-05-21 12:52:53', '2024-11-21 12:52:53'),
('237f42e14794291021ba4a288c3467b872d68510da8f0643e7c5683ff9922a191753d8ba907806c1', 47, 1, 'MyApp', '[]', 0, '2024-04-16 11:55:21', '2024-04-16 11:55:21', '2024-10-16 11:55:21'),
('2380842f033bdb89891e9d8cedf5663b5e67578d1f7773491f228d635c130939e46420538ef69c1a', 62, 1, 'MyApp', '[]', 0, '2024-07-07 19:51:02', '2024-07-07 19:51:02', '2025-01-07 19:51:02'),
('2390aeddbb35e77188194f3be069b0a91df3c72f4a63a6342478fd7ca4ad38ab41d3e58f6a568fdc', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:23:30', '2024-03-19 18:23:30', '2024-09-19 18:23:30'),
('2399adc8593f047ce4a041893556230a6b1f08d5012e96ef117ca0dfdbb89076e6aa1916a0debb7c', 35, 1, 'MyApp', '[]', 0, '2024-03-31 15:30:29', '2024-03-31 15:30:29', '2024-10-01 15:30:29'),
('23cda3a15ddc3a5b8591e4e4fd67657197705d3e7928d4f13daec8a850b56d8ba0b1f8bcb4729abf', 58, 1, 'MyApp', '[]', 0, '2024-06-02 13:58:14', '2024-06-02 13:58:14', '2024-12-02 13:58:14'),
('23d613b57280ffef2960da1e5a5d5bea0fd23c72dec7fdb1f836f394438b15590e109707baf975dd', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:31:20', '2024-04-28 15:31:20', '2024-10-28 15:31:20'),
('23d8fb7edaa243cf3a0bf9b80acd34e7866121f06d0cfdeaeda02d4d65c45a59be9fed3a12d8570f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:05:49', '2024-04-18 22:05:49', '2024-10-18 22:05:49'),
('241b27f21c7fed92ebf6e408618ba69163968ac8c77317477b820936934599cd24c09605e93f1821', 58, 1, 'MyApp', '[]', 0, '2024-06-02 14:04:42', '2024-06-02 14:04:42', '2024-12-02 14:04:42'),
('241b2b3877310accdbe31d1bf806256225a1a24027b1640413dc1818be31b09764baf4dfa3c461b5', 45, 1, 'MyApp', '[]', 0, '2024-04-10 16:58:08', '2024-04-10 16:58:08', '2024-10-10 16:58:08'),
('24240528ec488fae3932968bef5178bebf12a94db9b25798c4d293fc5f8ccf158fc7436d35c95fc8', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:15:09', '2024-04-28 13:15:09', '2024-10-28 13:15:09'),
('24670d2b40ed4c4c11e2dcc8bb23f7fffb6a8ffca32cba59469eef06958be8f8851dc4ea2f2488d0', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:06:14', '2024-04-28 16:06:14', '2024-10-28 16:06:14'),
('2482fc2060fafa3ca7cb23dd0576a0b82f06e260590edcf67d5d5f1a9ce2c66003b274b7b6a81ae6', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:12:04', '2024-04-02 14:12:04', '2024-10-02 14:12:04'),
('24b419d35991fe8657c02410c54401d9f112d0ed787c9998802b84b6861ebe666e57c344a9e1af3c', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:43:42', '2024-04-14 14:43:42', '2024-10-14 14:43:42'),
('24b9fc629c2058a0a8c4074645ce8d1b5153cc48f457b19e105160d3855878a1c3f2069830554b71', 5, 1, 'MyApp', '[]', 0, '2024-04-04 02:07:21', '2024-04-04 02:07:21', '2024-10-04 02:07:21'),
('24f25797f9fbee242ce1d536882ff71a5c3db0709083d2becfe5a9cc77e63d6ba0c1cd760613677a', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:50:44', '2024-05-02 10:50:44', '2024-11-02 10:50:44'),
('257adcc1a814752624818af030442713c0be28e33bc2d8a8d572d94e40c9114551ac65d25316fb35', 64, 1, 'MyApp', '[]', 0, '2024-07-07 17:10:15', '2024-07-07 17:10:15', '2025-01-07 17:10:15'),
('257bed800f2842a8fdc94881fe1663f8e6f83be107138d2f2b686f46556215600d96eaa42195c0d6', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:27:53', '2024-07-12 12:27:53', '2025-01-12 12:27:53'),
('259401f9a500b77df646b6157c4ceaa0cc01dddb08a84ed31434f2a4d07a5d7aa4f4e3ae12795e53', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:48:00', '2024-04-23 11:48:00', '2024-10-23 11:48:00'),
('25a26e59b5f44cc56bdb607b33a14eef1d628cb1e8d7c6cd3f7ed145c163320ae502f109d9519c2d', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:07:30', '2024-04-22 17:07:30', '2024-10-22 17:07:30'),
('25c0833510e4b2f4781e2e0738228ba55be6052dfec3e8876bfbb17e02c298085ab3d606bcdc74e9', 41, 1, 'MyApp', '[]', 0, '2024-04-02 20:37:43', '2024-04-02 20:37:43', '2024-10-02 20:37:43'),
('25d933e05286e02e1d6ef2aa1e48938565c88abb66703303443ae8952dfd59672f5ff78cf85d7b3e', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:29:24', '2024-07-07 16:29:24', '2025-01-07 16:29:24'),
('261e7a1f25bae5a240f1236c99f169a5c2aacd629ec1efc90b7b5af3a760c04ff854937bd60b8669', 66, 1, 'MyApp', '[]', 0, '2024-07-12 11:48:14', '2024-07-12 11:48:14', '2025-01-12 11:48:14'),
('2620ca90fb7042fcbedd8995833b7da0074fa59ee521c7c7420dd1364f4dfdd60c0a84d40114be0b', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:44:49', '2024-04-15 16:44:49', '2024-10-15 16:44:49'),
('26511809bd3d81d73815f489503a8e8522133d2cb838d82a185a61b8600e80e1697d25ecd89c09fd', 31, 1, 'MyApp', '[]', 0, '2024-03-20 03:52:08', '2024-03-20 03:52:08', '2024-09-20 03:52:08'),
('268678d8a7f5e70a02f31a9c83d801826da6888f6f3756f85c0148a082a040f3ffee9c947f0aa99c', 58, 1, 'MyApp', '[]', 0, '2024-06-02 14:04:42', '2024-06-02 14:04:42', '2024-12-02 14:04:42'),
('26a77cf382e6ce7d9b3208c6bac0a8f31addef6ffafd719f0d899776e17880a19fc59f63b0119585', 5, 1, 'MyApp', '[]', 0, '2024-04-08 03:45:08', '2024-04-08 03:45:08', '2024-10-08 03:45:08'),
('26c89881d7967079b20a66c1049ad50c1a05ba94da1e5db987956deb088210c94c7d8170823ec207', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:29:46', '2024-04-30 15:29:46', '2024-10-30 15:29:46'),
('26e13bdffa8724bb441aa929660f7906ca8673236b9c37b0f73e8f08838c47f31a79f47c588f68e5', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:47:08', '2024-03-07 11:47:08', '2024-09-07 11:47:08'),
('26f16cc6d69a35807dbc2d8495c63dcbbe6364b35ce6d58043ea1fc022f1927590bdeafdc2e9a69f', 5, 1, 'MyApp', '[]', 0, '2024-04-16 12:23:38', '2024-04-16 12:23:38', '2024-10-16 12:23:38'),
('26f61bbfe24101baeac61da9aad4b4718511a8bc24f5c37444bc1de88c7af67e261a3f3afffc2b74', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:12:18', '2024-04-29 16:12:18', '2024-10-29 16:12:18'),
('2710ab9b3be78f0cc5b7f8fb3797eb7fc55e6fa5e699dcbddbdc21c3831e718bd00686a3f0b952ac', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:41:50', '2024-04-24 14:41:50', '2024-10-24 14:41:50'),
('272df246c73127d122852377e685d92d9d858841b37a925e814d76558f73597246101151da3a48d2', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:26:34', '2024-04-24 17:26:34', '2024-10-24 17:26:34'),
('27c35d660ca82167c014a0dfc819a051bccb9cc5f6e885691092dbbaf4c92214412578b115fc0a12', 5, 1, 'MyApp', '[]', 0, '2024-04-28 12:15:07', '2024-04-28 12:15:07', '2024-10-28 12:15:07'),
('27d65683e3f99a597ef15b5a277d5e422f1884d764a4a2531fa080fd51dae937b40cee6f8471cf68', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:08:14', '2025-08-14 13:08:14', '2026-02-14 16:08:14'),
('27ef48a4ad18a94f5fa382e6c37b63b0c023cacaddedd74bceb9dda0b1929dc07993929388de4705', 5, 1, 'MyApp', '[]', 0, '2024-04-07 00:54:32', '2024-04-07 00:54:32', '2024-10-07 00:54:32'),
('27fedd21ec7145b755f5e95ea475417452e96344616ecb157cafeade8bb87c80686dc9d9489e80a3', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:36:39', '2024-07-16 10:36:39', '2025-01-16 10:36:39'),
('28419beb782c037c001e82aa2387608074ff166445b39a7cf986bdb48512a1855108ee1108c6960c', 74, 1, 'MyApp', '[]', 1, '2024-07-26 16:23:04', '2024-07-26 16:23:04', '2025-01-26 16:23:04'),
('286311277230ce3bb130ba6a565304ddf26e1b6a79164be7356f0f0a3c6662ae67338aada6768871', 69, 1, 'MyApp', '[]', 1, '2024-07-16 09:08:35', '2024-07-16 09:08:35', '2025-01-16 09:08:35'),
('288c34a628be8d9e27bddd499273aa09df555f4600bbf4d3f309bdaaadfd1caff22d61e87e7e5b47', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:45:18', '2024-04-02 20:45:18', '2024-10-02 20:45:18'),
('289833660f1d189ab67c0bfc77ec2978c96b4132ac95ecaaf1fda057473613923b5fe27aedcfb3cc', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:39:58', '2024-03-19 22:39:58', '2024-09-19 22:39:58'),
('28d0f694b81f2186adf36753a79ddfaa8bc0ea83200be079343ced45e0393f4f9b7e5e76b7336228', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:02:43', '2024-04-24 17:02:43', '2024-10-24 17:02:43');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('29ac133a8f54feb23d96a9c746aee6bcc2c6c8283daf5787c8a6781ff499b3f1202e506a81110e53', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:30:54', '2024-05-02 10:30:54', '2024-11-02 10:30:54'),
('29e727836c66f3d1c7abfa32d7729825e3dc7713463a9dbf1ac7b9d470566f47faf42fa22eb5edfd', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:44:08', '2024-04-16 19:44:08', '2024-10-16 19:44:08'),
('29ed6038298da7d8ae6588ec60b1a035a51906663d06c40991688479a5f909a981ea46de91ffbeb2', 45, 1, 'MyApp', '[]', 0, '2024-04-04 18:33:55', '2024-04-04 18:33:55', '2024-10-04 18:33:55'),
('2a188cef712be60eb53b06e7a723c9cf631450f28827295258844e961be22af5936f70999a2754a5', 5, 1, 'MyApp', '[]', 0, '2024-03-05 18:21:38', '2024-03-05 18:21:38', '2024-09-05 18:21:38'),
('2a707e22bd1730d65084c0987e9df5a5fe2efbe004f042ff2d68ee1b084f96749c3e67799afe62b2', 5, 1, 'MyApp', '[]', 0, '2024-04-16 16:22:09', '2024-04-16 16:22:09', '2024-10-16 16:22:09'),
('2a7b6199b982b0a2805148b689496a6b43feccee459cff37b219f4ed67ae2a8746d0c4f4fcdd1a15', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:36:43', '2024-04-23 14:36:43', '2024-10-23 14:36:43'),
('2a7ff5e4e00c058ccc82f7f8f17f86623a17da4d56246efb1dc1a90f8f3a10cfe1d810c2c8c20d08', 58, 1, 'MyApp', '[]', 0, '2024-06-05 13:23:03', '2024-06-05 13:23:03', '2024-12-05 13:23:03'),
('2a824b8d22431d23ddb4012b77da9dfc8d026368d966661fbdb1ba88d05f9cf7f11dc089b7bda287', 44, 1, 'MyApp', '[]', 0, '2024-04-04 02:26:07', '2024-04-04 02:26:07', '2024-10-04 02:26:07'),
('2abf2cf104a850dc9fbe8edbbecbe9fb27d9f0028097bee562b78f8e84619827d495730506ab4608', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:26:39', '2024-04-23 14:26:39', '2024-10-23 14:26:39'),
('2b14a8c6476e3bdc89bd9b2ac8dac19f647b4e78e27649e78b6a12b03a94d6a20cdb2ba232f7c9c8', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:07:47', '2024-04-24 17:07:47', '2024-10-24 17:07:47'),
('2b1b8854bab18a46440b8c407db51a335aeebfa875c94771b16ff1e89b623269ad5eb2d4b687612f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:12:53', '2024-04-28 13:12:53', '2024-10-28 13:12:53'),
('2b4d68884d09ec6e88b80cf3bcd9c2ae91ad24f667dbefa2dc63d7bb564a6c6abec22038f051a0fb', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:26:44', '2024-04-18 21:26:44', '2024-10-18 21:26:44'),
('2b52a2fc5c22411ba284a2c09272faa4b619b6f31797955276b108d2ec88b091a2a66c86a8c373e0', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:14:55', '2024-04-02 16:14:55', '2024-10-02 16:14:55'),
('2b7419f40eb256d8a423cdf2b680d5552df1f278e8a3edf703960c86caca13b5b4896a335b18c8c9', 58, 1, 'MyApp', '[]', 0, '2024-05-13 11:04:01', '2024-05-13 11:04:01', '2024-11-13 11:04:01'),
('2bb6edd4d7d62bbf542b15c96eecbcfd253ff4f798a087ba350e50a71a6249bf067342a6ff19f9d4', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:01:05', '2024-04-21 16:01:05', '2024-10-21 16:01:05'),
('2bdd8eb0ef677357ae613e3b8cdeffb0b8c39d3c2d1a79b6069adec8bad3395d1ee60011b040b34b', 5, 1, 'MyApp', '[]', 0, '2024-04-30 17:26:42', '2024-04-30 17:26:42', '2024-10-30 17:26:42'),
('2c01510ee6b637e55c3034cfba14d1174b19a50399715b4d56fdbdc16772bef72aa2d6272c8e1da2', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:11:51', '2024-04-22 18:11:51', '2024-10-22 18:11:51'),
('2c19d8f9c4fdcf07d6c68ceb290eb4094136b784c3ba063b7c02b6fb04a91d11a612915e700e7de3', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:35:40', '2024-07-12 14:35:40', '2025-01-12 14:35:40'),
('2ca30154486596942bb206d0ed7098a8d4220ffffe835ac6dd9ebde5bd2cc76e5f5e306ecd85a434', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:57:51', '2024-04-23 16:57:51', '2024-10-23 16:57:51'),
('2cc46c2c9404f4bc09399c53e70c22ef1d6e9012408472d04910193a6ed5c2a53444936947d63c1c', 35, 1, 'MyApp', '[]', 0, '2024-03-31 15:30:29', '2024-03-31 15:30:29', '2024-10-01 15:30:29'),
('2d123fe79a5da047c85afaefa25dba34e324c1f0bbe5c4a888c8b0eb6873b2c13cc297590a38c21b', 74, 1, 'MyApp', '[]', 0, '2024-07-26 16:23:26', '2024-07-26 16:23:26', '2025-01-26 16:23:26'),
('2d1d6f38d625ce44bbe1a5b819ccf4f4704f86297bb58ad6d1fdd5aadb04ffa9a49ff8b09436198a', 42, 1, 'MyApp', '[]', 0, '2024-04-03 14:01:45', '2024-04-03 14:01:45', '2024-10-03 14:01:45'),
('2d5e78439afba4112df8f9151d97e1404d747251d8c5d159124d3c0348c945dd34e62581ecb68b0f', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:08:28', '2024-04-29 14:08:28', '2024-10-29 14:08:28'),
('2d88ccfe6767436bc141de3cecf33c0bf05cd0177d9242c0fdb5fde8532f41c97dc53bd1e2080f3b', 5, 1, 'MyApp', '[]', 0, '2024-03-21 18:17:00', '2024-03-21 18:17:00', '2024-09-21 18:17:00'),
('2e3372f4090f61338e89a531a08d5112f4346c23ee901a061eceadb716e02c98ab77f7f18c0e1358', 5, 1, 'MyApp', '[]', 0, '2024-03-24 17:15:53', '2024-03-24 17:15:53', '2024-09-24 17:15:53'),
('2e383e2c2b6d6620de8a64f048192d42b8470377d3e4192c812552a78be7a87f89e56941f47bb6dc', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:29:46', '2024-04-30 15:29:46', '2024-10-30 15:29:46'),
('2e7a4041d77d180d2cfd48fd5317da3fab34f25a710cc8610ba1243eb6d733cafb39ff2e02fa7c8f', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:47:00', '2024-04-02 20:47:00', '2024-10-02 20:47:00'),
('2e7d4104f9f06f0e36d9877797ad69adb8a8f2e969295689310f6b067a995d1c1d5b2e6a600ee576', 58, 1, 'MyApp', '[]', 0, '2024-06-06 08:26:48', '2024-06-06 08:26:48', '2024-12-06 08:26:48'),
('2e8885b2c16860b2448cf166e9b4d0c695a807ca6a1eb94409a903372578672b833c6df2fee995b0', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:53:19', '2024-04-23 12:53:19', '2024-10-23 12:53:19'),
('2e891440e30820f26caefab0d605d63c70aa86ddacecae0869039a2c46f7446ab582a06c38827cab', 45, 1, 'MyApp', '[]', 0, '2024-04-07 18:43:32', '2024-04-07 18:43:32', '2024-10-07 18:43:32'),
('2ec0dd94c4b6431696670e2bcf2a9fd9aac1d2a362e2027c9d74e3065ad66d93de81b552b70898f8', 23, 1, 'MyApp', '[]', 0, '2024-02-28 15:51:06', '2024-02-28 15:51:06', '2024-08-28 15:51:06'),
('2ed92d5186b9c08cf75cd79d1610bbbb885543841ed320e4e0f630436a819428462c0129cefa3126', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:41:15', '2024-05-13 15:41:15', '2024-11-13 15:41:15'),
('2eea8a976b33519ac23ebc19b6ede48f46567a1bddec13e0d4308c857c21b6e1c50989489eab4d4f', 5, 1, 'MyApp', '[]', 0, '2024-04-17 14:17:11', '2024-04-17 14:17:11', '2024-10-17 14:17:11'),
('2f0b136f204eab39fdca439834580100d09fecee4cf6cad2cc54c1e1dcbf4c2b4a2adc109df542ca', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:49:59', '2024-04-08 05:49:59', '2024-10-08 05:49:59'),
('2f4436eff1c06517d232ca434d2ee9356486fd29fdd6dae62640133fcb40a02355f4434e1c4d2263', 38, 1, 'MyApp', '[]', 0, '2024-07-19 13:13:41', '2024-07-19 13:13:41', '2025-01-19 13:13:41'),
('2f5b4a939b09ecb323d8bd88683d9ae19bb6edea5933d51990af38bb312fd50a8cf2ecb945f5634a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:45:04', '2024-04-23 14:45:04', '2024-10-23 14:45:04'),
('2f6196d047c304c5c065e3bb395da3fd66f916b6bf14a8e4fd5c50b79291dcc0a818ab22e90c35e0', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:40:04', '2024-07-12 12:40:04', '2025-01-12 12:40:04'),
('2f995e4cb31560b366757e3be44c90f0bb2822ea5d75e92a9c7add88098c88df64335dded5d68551', 5, 1, 'MyApp', '[]', 0, '2024-04-08 04:00:37', '2024-04-08 04:00:37', '2024-10-08 04:00:37'),
('2fb0eeff5aede22ddc01d7beaf22564ca55e5370f91ab58e57e3e8db66b31b3153febdb720f020c5', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:46:03', '2024-04-29 13:46:03', '2024-10-29 13:46:03'),
('2fe6592b3d821cc6df32435a3dc9141066da226fa91f79ec2d4f04cd226227f64c5916140f6b8931', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:11:43', '2024-04-21 17:11:43', '2024-10-21 17:11:43'),
('302999633f6eb6053e4e6a24c8b7744e70071fc62a98d342f002bb4e8089c974ee45f6acbc45dc6e', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:37:04', '2024-03-24 17:37:04', '2024-09-24 17:37:04'),
('302cc45541dfdcc1dd986016b5efbee6523ef33f9b1a441ebd8fd4ebcebafc17299f9007410e1cc5', 37, 1, 'MyApp', '[]', 0, '2024-03-26 15:58:17', '2024-03-26 15:58:17', '2024-09-26 15:58:17'),
('303501efba0a380689303b8c2e9f0413c03a16983751d60321db74ca852615df4ec159f9d911ca9e', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:35:00', '2024-04-14 13:35:00', '2024-10-14 13:35:00'),
('304c1ef62e703dc38a48a2abf393d9153b3875a02d7c2d33a61d2c07d110771c53d3c1e59850929f', 5, 1, 'MyApp', '[]', 0, '2024-04-21 15:58:25', '2024-04-21 15:58:25', '2024-10-21 15:58:25'),
('3064bf241dadc0ee54bb69f4bd32f8ed1f6b82ffef738e80408bb3814a32c3ab1d7f592e0dae4049', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:03:14', '2024-07-12 12:03:14', '2025-01-12 12:03:14'),
('306978ef62ffcf2533faa17b3c204eaba81651a4f4ea8645b7d3e66103846afd1d09e423eb46a0a6', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:43:34', '2024-04-21 17:43:34', '2024-10-21 17:43:34'),
('30744b10a173809be32c987bbd87719e65fae64b7b83947db2c7547595f3390be9f94806cdace1c5', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:39:16', '2024-04-21 16:39:16', '2024-10-21 16:39:16'),
('307dc313cac5fc09f1f1ea0730e2e659941f9d12d81ba6c502e76352472875d67d3fbffbb6669f8c', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:55:37', '2024-03-27 12:55:37', '2024-09-27 12:55:37'),
('30a72766219a04ba94858dff26257ab4b756033a30fdc201fe918ce2a4ceb39a8b4b8d76a17b80eb', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:11:18', '2024-04-17 13:11:18', '2024-10-17 13:11:18'),
('30d4067adfbaedc3ee1c6a5321a70313df196878b54000c7dc7689153bbfbf466dd05689633a6288', 71, 1, 'MyApp', '[]', 1, '2024-07-23 12:06:48', '2024-07-23 12:06:48', '2025-01-23 12:06:48'),
('3114413d8e0a79d4dc695377139ff012306c1798297a22b5359d98970ee381d2c967e19a6476f77c', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:16:32', '2024-07-12 12:16:32', '2025-01-12 12:16:32'),
('318671375d5d4a7c33cba62d6fd377241c795ab3f71867e77c0f94ffd70375dbef1bce872775fd2f', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:22:42', '2024-04-22 19:22:42', '2024-10-22 19:22:42'),
('31dc8200dcb82be1d2b7f05fc800b4b981d1873119a7ee135d4d16e49af1f9c6e9184fb953736cf5', 71, 1, 'MyApp', '[]', 1, '2024-07-23 12:32:02', '2024-07-23 12:32:02', '2025-01-23 12:32:02'),
('31edf1d0d1d8262a743d27236575d88cc7a854560691f381827ae53bf63096efe2c7011908d7ea0c', 74, 1, 'MyApp', '[]', 0, '2024-08-01 10:05:54', '2024-08-01 10:05:54', '2025-02-01 10:05:54'),
('31f0e67ba7396871f01644c491c46c0fb3fe7de1426cdd3452acf194f4c351596448d6d7007e3132', 35, 1, 'MyApp', '[]', 0, '2024-03-31 00:17:39', '2024-03-31 00:17:39', '2024-10-01 00:17:39'),
('322e4ffd9707394a6f109d3e90e77f9924e346bd7e7934fb62470b0db0dfb04f474aeb1149338a21', 5, 1, 'MyApp', '[]', 0, '2024-04-16 17:22:38', '2024-04-16 17:22:38', '2024-10-16 17:22:38'),
('3244f39388888beddd099e67bd556c99c02cdfe4927419f38232f76c9b850e223d6ed70b9b453aec', 5, 1, 'MyApp', '[]', 0, '2024-03-10 12:02:52', '2024-03-10 12:02:52', '2024-09-10 12:02:52'),
('3245e29371b9a50d2a9f173c7eab9c0dbb786631e3de9aeeff3eae1fe431c5971927371879bcd368', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:52:26', '2024-04-28 14:52:26', '2024-10-28 14:52:26'),
('324c1963a17266d41abcc30f486fd9e53784104aa9103df94d19a1fda7ed9a4d128bcc56234c1f8a', 48, 1, 'MyApp', '[]', 0, '2024-04-16 14:21:26', '2024-04-16 14:21:26', '2024-10-16 14:21:26'),
('32545025d7034bbd6edba568253db617a31027182c594c79a8131991e2a427c875a0f1b7d970fa19', 5, 1, 'MyApp', '[]', 0, '2024-04-15 12:50:53', '2024-04-15 12:50:53', '2024-10-15 12:50:53'),
('32e5ed21e020cedf3a733113b74f9b91f781aac0b544c2a73968a060a078cc3fd4b9c0f1784ba7b3', 45, 1, 'MyApp', '[]', 0, '2024-04-08 03:48:13', '2024-04-08 03:48:13', '2024-10-08 03:48:13'),
('32e941c15ee4604ae5e2a4c00e51198454ccdabf17b9bb602f7bd025b4e5e0493f47fa8c7faab3f1', 5, 1, 'MyApp', '[]', 0, '2024-03-17 12:42:18', '2024-03-17 12:42:18', '2024-09-17 12:42:18'),
('333ba227a1323277d792d2ad8e1bd85d390224e224c60b33265cff783506b8a6faab574f4e2c0165', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:04:20', '2024-04-23 11:04:20', '2024-10-23 11:04:20'),
('334d7bb714f07a0324597b91a98aeab01dfb45e1291d2be766588117a8e2a67701a4ea6d49700161', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:09:30', '2024-05-28 15:09:30', '2024-11-28 15:09:30'),
('33dabf447ca60920fc61b6d0c9fe73800be20c3d45539f3e4b8ce66a45bd630e42cc5be3f78b37bf', 45, 1, 'MyApp', '[]', 0, '2024-04-08 03:14:04', '2024-04-08 03:14:04', '2024-10-08 03:14:04'),
('33e09dbc15a6b32d3421f921949b5aff30a29537adddd84e0ec719c1cd026ac608035c8ff4ed1c6b', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:27:18', '2024-04-23 11:27:18', '2024-10-23 11:27:18'),
('33fa5df3d57e4f9a5e39326e82c26fed00990edaf8550f3ad99a76b767b65056da3951bb83c7fcb0', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:18:40', '2024-04-23 16:18:40', '2024-10-23 16:18:40'),
('341188da7b94c320adb267eabe4b8b8dcb025a1a1d889cc52d3d46c11c6ed57619811864eb8b2b20', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:31:39', '2024-03-28 14:31:39', '2024-09-28 14:31:39'),
('34188d43eabb6fd593748b0b612e850c13199c94c1deee5e337772dac2912277ebe24d6066b702e6', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:25:26', '2024-05-02 10:25:26', '2024-11-02 10:25:26'),
('349f679ca23a3399fc7c236e3762e63f4d8a0e79893c099377a33cbcd61c636d60ae9475d993204f', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:23:15', '2024-04-17 15:23:15', '2024-10-17 15:23:15'),
('34a24fc84f073ea7a2cda00a5808c9f0e16900f24a3a07e572b45375a2b653d9c7b2552223421b46', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:36:33', '2024-04-28 14:36:33', '2024-10-28 14:36:33'),
('34ac2d5ade1ee11182a306b3840f66522df48d08616ebb86d43bed93ea85b784d6634c0e5b4f4b78', 2, 1, 'MyApp', '[]', 0, '2024-02-25 02:03:01', '2024-02-25 02:03:01', '2024-08-25 02:03:01'),
('34c55c318a7c4ee6f60023ac687ee747e777259ffc3266e726297268d3d93970e310d386538304f0', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:32:53', '2024-04-28 14:32:53', '2024-10-28 14:32:53'),
('34ce14487a921dbb5ce87600c4bd2ba0c880943888a28bff3d84a056d60d2603d526125f37b67ec1', 58, 1, 'MyApp', '[]', 0, '2024-06-04 17:22:00', '2024-06-04 17:22:00', '2024-12-04 17:22:00'),
('351dd0a7d47025e1d793c10294a047c1be234fb3155d25bdeb9ec81daaaa74b12b371b3a0f42c994', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:59:35', '2024-04-08 15:59:35', '2024-10-08 15:59:35'),
('35243c7df8c81b2379266da1123681d279ecc2e65d9e6dfc7b998a203276f374082c67b320c01378', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:16:35', '2024-03-26 12:16:35', '2024-09-26 12:16:35'),
('355dc513dd9fcfbbdaab2a40ffb9ba2b340848ab5e24e2777a74d66b4350c470c2baadb7f7b4695d', 5, 1, 'MyApp', '[]', 0, '2024-03-24 17:15:53', '2024-03-24 17:15:53', '2024-09-24 17:15:53'),
('356b372b6fdf1464051d5cac5799ff6b4189fc45975987eb2aebbc5d21420096af56ea34967758cf', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:02:35', '2024-04-24 15:02:35', '2024-10-24 15:02:35'),
('35a82dccf6ae11c531c660737e0d3e0c2685de8331d2c61a4ba52da1681d20867736caca55e543ef', 58, 1, 'MyApp', '[]', 0, '2024-06-02 15:28:42', '2024-06-02 15:28:42', '2024-12-02 15:28:42'),
('35aa03ee8b44cec237c700e39e6b966fdf956d8bab3b5c92d961af7ac333a0da3bb5b2aaa02e95ae', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:04:34', '2024-07-13 15:04:34', '2025-01-13 15:04:34'),
('35c7efc835c511f46b69474ecd4e5bc51921e733bfadd4d32657913d80aa68c7ee4832f533fc0c08', 22, 1, 'MyApp', '[]', 0, '2024-02-28 11:00:37', '2024-02-28 11:00:37', '2024-08-28 11:00:37'),
('35c822cfb75381bb7d79b3cfcef5fb757915f735abcb57ad685043a82443821295722e03433fcdb8', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:34:48', '2024-04-18 17:34:48', '2024-10-18 17:34:48'),
('35ce336183c09d8cabd29db58422363079afcca8f4155b496daf0b20a0924e8662a441b6e70f1445', 5, 1, 'MyApp', '[]', 0, '2024-04-02 12:55:46', '2024-04-02 12:55:46', '2024-10-02 12:55:46'),
('35ddd1a77390b45ea0330675a691f04d0471d4af5c14ae1c9196c0444a240234dd3bb579836b9f6f', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:26:35', '2024-04-30 15:26:35', '2024-10-30 15:26:35'),
('35f158ed22db7437f57bfbfa2e8fcb2f0e9d0987e71e945bbd4de7703b540f5c4cb957bc589eba44', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:10:52', '2024-04-21 13:10:52', '2024-10-21 13:10:52'),
('35fba7b28f6fa078af1a1311e3b6e52aa297c3815bac9dee45aa461b9b54c863e5a66451ae073ccc', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:29:54', '2024-04-23 14:29:54', '2024-10-23 14:29:54'),
('35ff2f2b4f48d3fca9470865aeb44ee41a9ab31b58e17db463f693508adc574ad9340963620f7ef0', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:00:42', '2024-04-23 16:00:42', '2024-10-23 16:00:42'),
('360f971505ac3d852326fe752ab5de6fb29af38c57f2f2aeb9c2e0856afb6efc50609344382849c2', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:55:04', '2024-07-12 14:55:04', '2025-01-12 14:55:04'),
('36229539efd2915bbd9d6dc675621ed48d3583414b97cfddf3c53a7e080acd0a35666c524a6ed5d1', 58, 1, 'MyApp', '[]', 0, '2024-05-28 14:44:00', '2024-05-28 14:44:00', '2024-11-28 14:44:00'),
('364008a4809ca28159230c281d47b4b77145d65edf8da2a6dee963d8982a79821c1d4a98ff6d696a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 17:45:42', '2024-04-23 17:45:42', '2024-10-23 17:45:42'),
('369929bfba6485f71b5c2a74693abf8734cf331c70912b55f0524bff753bcd21003c382a3c572b5e', 58, 1, 'MyApp', '[]', 0, '2024-07-04 10:16:51', '2024-07-04 10:16:51', '2025-01-04 10:16:51'),
('36b70178e339dbd18de6f52c9b2acfbaf9b17a41700c863e6dcf966f62d50b7a9f596615559e0607', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:41:18', '2024-04-18 12:41:18', '2024-10-18 12:41:18'),
('36da24a4919189e2c4857bd36fe1b2595efe29b4e95dec017b43f772c10bd814c43d907fe7a9be62', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:42:49', '2024-04-17 17:42:49', '2024-10-17 17:42:49'),
('3722160f539f9f01aa79cef59b1e40589d30023f414ab9e795d8be1dbe8b623b33c74383d8ddc0cb', 71, 1, 'MyApp', '[]', 1, '2024-08-03 14:35:50', '2024-08-03 14:35:50', '2025-02-03 14:35:50'),
('37505b63dd2ff152fb65cc99c67a3e675ecb502d03d940b454eae16cb8d52a5746d6dffb68575328', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:49:18', '2024-04-24 15:49:18', '2024-10-24 15:49:18'),
('3773dac3908b465980270623b24b44716b4fce25b23afa6cbc2e2093f5b53646419ac335553ea6dc', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:47:46', '2024-04-29 14:47:46', '2024-10-29 14:47:46'),
('37a82af6413a7677de92ab381c2a60b645b57ae87716963d890af1910817caab76096a3e76c41ba0', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:57:13', '2024-04-29 15:57:13', '2024-10-29 15:57:13'),
('37b0b38ca9b170f6a81f049d3ea87fded4b6bc9927d671ce77c80261e5753956ce4adec0636ac6ef', 5, 1, 'MyApp', '[]', 0, '2024-04-21 12:27:50', '2024-04-21 12:27:50', '2024-10-21 12:27:50'),
('37d4800d86cc5e2e104102e8a5b2731940870896b50019138d7dfcf590ca23675feaed1cee270b5e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:19:12', '2024-04-23 13:19:12', '2024-10-23 13:19:12'),
('37ea69caa13b8a17628c3864f873b104707141dd6f4a9bbb79be92b6ad3e58e1e279331ce4cd6b2b', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:26:34', '2024-04-24 17:26:34', '2024-10-24 17:26:34'),
('37f09ce481fc480bb79f0db719578962a63bfe6f18698ecc9a8abe4f9c86669bf40a281da3d7b256', 5, 1, 'MyApp', '[]', 0, '2024-04-16 12:23:39', '2024-04-16 12:23:39', '2024-10-16 12:23:39'),
('380854583cb6928190eafb93cc10f1be9276265c11d4eb7ac3672ca1ae61ee3e2048ec50ffaad332', 69, 1, 'MyApp', '[]', 0, '2024-07-23 15:21:04', '2024-07-23 15:21:04', '2025-01-23 15:21:04'),
('382818c00751b88502207fb42f11f8ce1169732daf786cae5affd1e8daf56ed3a6a6d408124dd84f', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:45:39', '2024-04-23 14:45:39', '2024-10-23 14:45:39'),
('3852ca18189b33b82e061e1c42974ef01886b9d38d701a83fa00d11e56f70ea99ad69c9a35a6d293', 12, 1, 'MyApp', '[]', 0, '2024-02-18 12:27:31', '2024-02-18 12:27:31', '2024-08-18 12:27:31'),
('385658e39e517a37fbcf10895b2ef293bb59701808b4f042b7775d188d09cbaf62ed61d21487c122', 74, 1, 'MyApp', '[]', 1, '2024-08-03 14:41:57', '2024-08-03 14:41:57', '2025-02-03 14:41:57'),
('388aa3621fd3d003b2bd1d6cb120b8cfad1c67a21989e6d884319e0c16d0daba7b13b3a96f3c0097', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:45:17', '2024-04-18 20:45:17', '2024-10-18 20:45:17'),
('388c4e4ce7c8cc00412adbefa5bfce52e88152b634000e5decab12ef23f7eb8a2fa014995b49dceb', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:19:23', '2024-04-17 16:19:23', '2024-10-17 16:19:23'),
('38ed835088329a6a2ccead320844bb9b2fac8cf0bb160528b80898562e7fa1efa2b95a40ca8d202e', 35, 1, 'MyApp', '[]', 0, '2024-03-27 15:39:31', '2024-03-27 15:39:31', '2024-09-27 15:39:31'),
('392b45570842ce8331d3daebd48a2f47ecdb8c4be69124f7bacd557f8fda3d46c726679a1e600dcb', 35, 1, 'MyApp', '[]', 0, '2024-03-31 04:51:47', '2024-03-31 04:51:47', '2024-10-01 04:51:47'),
('398e8ea1abde14b0704d75fdd58535784eb8a388ddb06c4c6b45aed9a84b27433153665c72a33e30', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:44:54', '2024-04-18 20:44:54', '2024-10-18 20:44:54'),
('39a3740483dd0c907307cb38bb84598309363826f010131608a1d1e39717a828055022ff2b23a05d', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:49:29', '2024-04-22 18:49:29', '2024-10-22 18:49:29'),
('39b8269f0a4801eabed5a2b9218244898f94b0d867736d11182fd98f7c67e26c150ae6428f4303ca', 66, 1, 'MyApp', '[]', 0, '2024-07-13 19:04:36', '2024-07-13 19:04:36', '2025-01-13 19:04:36'),
('39d5077d4d7865ce8020c1a8a61b5b62a682685cf9ed9caa464150e3d0da0a1452611c0df5311271', 5, 1, 'MyApp', '[]', 0, '2024-03-18 14:12:53', '2024-03-18 14:12:53', '2024-09-18 14:12:53'),
('39d584685bd6489bb4bec9da61b34ffeedd58eaf844003ac9face3a01ebf05784af0ee881ba9eedc', 35, 1, 'MyApp', '[]', 0, '2024-03-31 04:58:50', '2024-03-31 04:58:50', '2024-10-01 04:58:50'),
('39debef3ec7ba0af1c29e44c58ec197ab3ed2a5d78334a5378b94b6ebbf2d348d6fc1457db42e636', 58, 1, 'MyApp', '[]', 0, '2024-05-21 15:15:39', '2024-05-21 15:15:39', '2024-11-21 15:15:39'),
('39e395b1487b3537a875018e98e94216b8e25c2c58ed5d77292b065c83d820cc22ae30b2160b32c2', 3, 1, 'MyApp', '[]', 0, '2025-04-13 14:21:03', '2025-04-13 14:21:03', '2025-10-13 16:21:03'),
('3a06936c8ff3ca2f3ee187711192d17bfc63377b060ccf4f7ec17ed439ceea6b308cf85cabe9f7d1', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:55:39', '2024-03-27 12:55:39', '2024-09-27 12:55:39'),
('3a16f209e8ec9f98dcc5ead1c518d48d4ff3b7c4ce2bd78385e3268c4f60d414da39c9bf88f19cb1', 58, 1, 'MyApp', '[]', 0, '2024-06-06 08:54:01', '2024-06-06 08:54:01', '2024-12-06 08:54:01'),
('3a1a2b04de31d252abfe0a30c537fc37e7258e2407a99c68f18d439a112dfe61c52b24c9aa523452', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:39:13', '2024-03-31 14:39:13', '2024-10-01 14:39:13'),
('3a1b54218af5c2416e5dce6222dae6d467d627314a02955733aa9db198bb5957af15a1a2b15e9d80', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:13:31', '2024-04-29 16:13:31', '2024-10-29 16:13:31'),
('3a96810f026af93ad1094cb723105d418c75b0c116b7c0f3181abc1602202e81fb9139926626bcbd', 69, 1, 'MyApp', '[]', 1, '2024-07-16 09:55:53', '2024-07-16 09:55:53', '2025-01-16 09:55:53'),
('3aa878ec4ada81b997a310ec878cc33028e8b28101566ab41c05782f797d5c07e5e43719ba47a013', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:07:33', '2024-07-12 15:07:33', '2025-01-12 15:07:33'),
('3b3fbd9b5e52c6b3dbe66c328975a1dc45934d898340794cd233a1073aebab1caa1768c08a1bd511', 2, 1, 'MyApp', '[]', 1, '2024-02-14 15:18:24', '2024-02-14 15:18:24', '2024-08-14 15:18:24'),
('3b5769391a3e6b38def2d227677ffddef24103ae9f6cffea4c495667d7c3c231467386195958b541', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:09:38', '2024-04-29 16:09:38', '2024-10-29 16:09:38'),
('3b57beef303da6957cfd35cdf2a5853ff88cf2a4aee6e01bd7f4e0c9b816435eb6ecef06a8e41e09', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:58:37', '2024-04-23 18:58:37', '2024-10-23 18:58:37'),
('3b74669cdd4cc5f2a7482d102805d30ddbfb59835233983055701e285e9976a0e2e4436f761ddda2', 58, 1, 'MyApp', '[]', 0, '2024-05-28 10:45:40', '2024-05-28 10:45:40', '2024-11-28 10:45:40'),
('3b8ef5a29e46d70960edead4a51f360ee39951b0434b95dfe54b3057bec8a9d44b3229db048dcaca', 5, 1, 'MyApp', '[]', 0, '2024-04-03 20:50:26', '2024-04-03 20:50:26', '2024-10-03 20:50:26'),
('3bba1c658c3413a242385aaf76852a89dccfad2bedd6678e60db041843836c86f1f5a8eb4aaec5e5', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:00:44', '2024-07-13 15:00:44', '2025-01-13 15:00:44'),
('3c11e325cbbfe51b121d3b2ead92200e30a6e519eec0c0551b527bf790476b60f5755d78533223fc', 32, 1, 'MyApp', '[]', 0, '2024-03-21 02:31:34', '2024-03-21 02:31:34', '2024-09-21 02:31:34'),
('3c61aca7cc2f25b3a53fa7fce1c274985e3004d26f268089c5e2d465b4c5788285ff963967a4d8af', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:04:44', '2024-04-24 14:04:44', '2024-10-24 14:04:44'),
('3c7c0b768203ae195c6417676a044504c5f86e004f8df8d08156895e78025c30c85215e2b55bfd47', 5, 1, 'MyApp', '[]', 0, '2024-03-21 00:37:01', '2024-03-21 00:37:01', '2024-09-21 00:37:01'),
('3c94223363f6c5494247cb9bf6ce386ba5952ba45ccf8e1798f1ce4d4e6a85208c130a850e26405b', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:26:30', '2024-03-24 17:26:30', '2024-09-24 17:26:30'),
('3cfefd461078542ecb66d92eeb20d5b0dc74bff6534f81d2a0628097136eb862a47a8d6bf69857e1', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:02:55', '2024-04-14 13:02:55', '2024-10-14 13:02:55'),
('3d060ca624d6710f4f5d264bb5dd83feae62572918dba5fd6dd8349d9f3de42a97ac7866261ccff3', 11, 1, 'MyApp', '[]', 0, '2024-02-19 11:27:51', '2024-02-19 11:27:51', '2024-08-19 11:27:51'),
('3d4c5bab612d15a9fa54f95cf57527aa09bff6afd2bf2c5667571da443f79f85713ae3f50d019540', 5, 1, 'MyApp', '[]', 0, '2024-04-07 17:42:30', '2024-04-07 17:42:30', '2024-10-07 17:42:30'),
('3d6d03682b68114158f385cb16cd31768303c6bcff807f769b22202fa5a62179f6f5ef9ce031c66a', 58, 1, 'MyApp', '[]', 0, '2024-05-22 11:03:58', '2024-05-22 11:03:58', '2024-11-22 11:03:58'),
('3d6e271e1c0efe2db2e940bf70fc9102347389007ac5a0b2b57c21ae26cf9eb3570e0d7d157b11a5', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:34:08', '2024-03-24 17:34:08', '2024-09-24 17:34:08'),
('3d7fa0f962a18fa2290eae09c23806002e447abb15f31d5037731510350143b9653196e8fd3fdd74', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:55:59', '2024-04-29 15:55:59', '2024-10-29 15:55:59'),
('3dbdf26d9b5476677ca9713503d9519b276e7605b2ee0480e1dc710d9886fc191cb065c8daccb7f2', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:39:14', '2024-03-07 11:39:14', '2024-09-07 11:39:14'),
('3dcf0171f8a3566170428d0a65bf26e6fb052292e91f2987453ffdcae618188886ab2501cec93dfc', 28, 1, 'MyApp', '[]', 0, '2024-03-17 11:01:56', '2024-03-17 11:01:56', '2024-09-17 11:01:56'),
('3de695f82649c06e106c5520808d15686fb6a1c83c3bbac6e7e1ee011c28c3a96ddeb97f5aa871d3', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:10:20', '2024-04-16 19:10:20', '2024-10-16 19:10:20'),
('3de95fb7f0714b475b8a3919168ac9a6286d692860d342ec5c88c260c27eab3aa5f81d263f725195', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:38:37', '2024-04-15 15:38:37', '2024-10-15 15:38:37'),
('3df73caaac65f9cefec9c5dc4ba3c57f578ef3df0ef4742642a28d0f26b72a56ca17932b1956d55d', 68, 1, 'MyApp', '[]', 0, '2024-07-15 10:24:00', '2024-07-15 10:24:00', '2025-01-15 10:24:00'),
('3dff80b339074109b9e8972e48761c9e459b635f5eedd52a49be41f56ed88dd109af48ecb65bea00', 5, 1, 'MyApp', '[]', 0, '2024-03-20 15:44:58', '2024-03-20 15:44:58', '2024-09-20 15:44:58'),
('3e14f4d92f8993b4d75cf63281af45181b838c5b29e6ebc9128e6919c3a148ff3fbd73260566083c', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:15:09', '2024-05-02 12:15:09', '2024-11-02 12:15:09'),
('3e1c2558235e4976346991dfb8c53b6e0a2a7843853ca954e4624d9c99e281d4cfad587b13f81f67', 69, 1, 'MyApp', '[]', 0, '2024-07-25 14:41:37', '2024-07-25 14:41:37', '2025-01-25 14:41:37'),
('3e1f7d4dc121652c515bec6b747ec812a768fd2a8fa26c7429d119e520d38e2b9acfba0863911470', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:52:26', '2024-04-28 14:52:26', '2024-10-28 14:52:26'),
('3e2a7164b35c9d4f66bdf754893abfa117af3bd0f868ac6b6200854e45bcedd986832f5338b10c42', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:16:58', '2024-04-28 13:16:58', '2024-10-28 13:16:58'),
('3e953b33623269c70f4df88edaaa0c7a20ab81869cddbde29daafd8310c09feebab60629ac208814', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:59:14', '2024-04-29 15:59:14', '2024-10-29 15:59:14'),
('3eb00b1e05fae8fbfb04c8ecbecb697159f19e18f52807a58053a5f3e8243cf6fffe48854e932ca5', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:11:18', '2024-04-23 14:11:18', '2024-10-23 14:11:18'),
('3ed33d8cbfcd86a407d8e104321ec74a6e3d549b792b5edfbf131a72bcdd4521fa22f1a4ad169181', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:30:56', '2024-04-23 12:30:56', '2024-10-23 12:30:56'),
('3f57270b0d9dc9afe649b2af286767dffced6074c8c177712500869f276302001b347234187b1a46', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:28:28', '2024-07-13 17:28:28', '2025-01-13 17:28:28'),
('3f7dfc4cc724c60726edcfe65aad06c6193366c1b650d78df698bba750199f1226d4471f43fbeb1c', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:11:15', '2024-04-24 17:11:15', '2024-10-24 17:11:15'),
('3f84c389ebcabd627c842dad0faa53a270633679e9cc74020fcc84084d417ac27421ffc5d44dcec5', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:21:45', '2024-04-15 16:21:45', '2024-10-15 16:21:45'),
('3f8ae912fb481e07561356733a284346a76b2f9382abd94065ae851c84e36f8e76e36acc58e25c0d', 26, 1, 'MyApp', '[]', 0, '2024-03-14 14:53:53', '2024-03-14 14:53:53', '2024-09-14 14:53:53'),
('3fabd5f548a5bf600830cd8e667c6d1a220e6bf0fae11303bf77fa5030f62ee059beecc1b74525bc', 5, 1, 'MyApp', '[]', 0, '2024-03-05 13:57:47', '2024-03-05 13:57:47', '2024-09-05 13:57:47'),
('3fcb758a27ad323fff8462457e3b63f06e4cb26c962a86aca4cd5ccc789d2c9f7b048fe2a2d52267', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:48:11', '2024-04-16 19:48:11', '2024-10-16 19:48:11'),
('3fda8d635685ab886559fee0d8edf600d0c589af622474149ca62a2294f79c0710e9386616df2383', 35, 1, 'MyApp', '[]', 0, '2024-03-28 17:31:19', '2024-03-28 17:31:19', '2024-09-28 17:31:19'),
('3ff59f964145a5b66f63e5ca448da5102bce27ad85cb18dafa2a6016dda4099426fa2ea8ecd8c3fa', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:52:22', '2024-03-07 11:52:22', '2024-09-07 11:52:22'),
('405b19025b63be9f999e271eb27f2c9df5cd89e5aa8737041a610c48ae6f85675f32a05a06ac29f0', 9, 1, 'MyApp', '[]', 0, '2024-02-15 17:27:55', '2024-02-15 17:27:55', '2024-08-15 17:27:55'),
('40a282c50c4eb873ac3b9ca3b7dfd77ed840e80b9076f2de34d0d414aab8a785c81473329b60707b', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:18:44', '2024-03-24 18:18:44', '2024-09-24 18:18:44'),
('40b974ead042a71915d7297970024c50461cd62b19c88ba421ec1389c6ad3d3eae6c70bab9c265d7', 37, 1, 'MyApp', '[]', 0, '2024-03-26 15:54:32', '2024-03-26 15:54:32', '2024-09-26 15:54:32'),
('40fdb39cdc9e6408cf4fd63d7e67b775b0d874eb9595389096c802f95c07b363ce1abaf08108b52d', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:35:15', '2024-04-23 13:35:15', '2024-10-23 13:35:15'),
('411fc93ef354487dfacb57e376965c1e00bab82ec23c9e871fec0aba7815a751da1202e0a2acde33', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:33:41', '2024-04-21 16:33:41', '2024-10-21 16:33:41'),
('413e9ff40d7f5c305cf7c3494d3d8c67234db4f6d1d49a00d271bdee7b558fad338c0eebd72684f5', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:32:46', '2024-03-27 13:32:46', '2024-09-27 13:32:46'),
('4143e11905275acc8573e6e974ef28857f2658032d0394604118eff8840a13a8d7c4f5987e681be3', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:12:03', '2024-04-02 14:12:03', '2024-10-02 14:12:03'),
('41488da54a7322d508567b277ab2f4409ab0eb3bf5f6858992daa81adaf8d21f8e357898f0f80733', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:02:42', '2024-04-24 17:02:42', '2024-10-24 17:02:42'),
('414b6083fa57ae0cd4f17638dee36f357a81e7e9959de8b825fc1f1f752a337bd16a4670bc384751', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:22:40', '2024-05-02 10:22:40', '2024-11-02 10:22:40'),
('41539e83c7e11f1a8d3615c249f68c4e0a597c9733eb92dcacdba2fa379c8b928ba253b3ad4b8795', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:08:54', '2024-04-22 18:08:54', '2024-10-22 18:08:54'),
('4175b5a54e3c3d2b22fc386f959f55a93ca05377590665384c03260821da2d78890baa4b0d7639fb', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:01:20', '2024-04-22 18:01:20', '2024-10-22 18:01:20'),
('4176b334cccbc828d927e96129a6e0bb85282b1f6bc521d0eba5d6d0297b54549049ba423823795c', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:20:29', '2024-04-30 15:20:29', '2024-10-30 15:20:29'),
('41851ded6e9fb00d01686849c0e090ffb42d140eaf0e77312e63f9c5aabbf7ed8bfe592d277eaa5f', 5, 1, 'MyApp', '[]', 0, '2024-03-13 15:24:07', '2024-03-13 15:24:07', '2024-09-13 15:24:07'),
('41942be54836210c0ac0aba596e582f7f9f0ac2f7767863c2789ba638043c15995abb2c42b71092b', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:02:39', '2024-04-18 19:02:39', '2024-10-18 19:02:39'),
('41ae9b18ad1d9c09b9325f8bf88fbf0ff8aa12c7d1059c0d27c7125c217c856c404525eb0dd3ed43', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:43:39', '2024-07-16 10:43:39', '2025-01-16 10:43:39'),
('41b9658fec373bb3711bba1cd38e7f45ced1d84d947040046d5f6e37cfdf6e2d124b43d482f0a338', 74, 1, 'MyApp', '[]', 0, '2024-07-28 15:43:31', '2024-07-28 15:43:31', '2025-01-28 15:43:31'),
('41beec77de869cea41b4cd3b711216b067b09d83eb46d1e9dd60b1727deed2e9a38d4e13c298a057', 69, 1, 'MyApp', '[]', 0, '2024-07-23 15:04:34', '2024-07-23 15:04:34', '2025-01-23 15:04:34'),
('41d3d95dcc9f0ecf7e8d51a0a2791a073d41cc7f0f2d61c35a53bbd5821a64b5ff5d5a6c3ab1fe50', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:20:10', '2024-06-05 13:20:10', '2024-12-05 13:20:10'),
('41d899179b878ccf358ec7fa843521d48bc53807406dfb8edeb99c293009a70058834265007b1fb3', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:11:19', '2024-04-29 16:11:19', '2024-10-29 16:11:19'),
('41df9b311c61cb6027d5da152300553a903bd7517ce769f91ced7c3781e99cb7e0c563c15ef84799', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:20:12', '2024-04-28 11:20:12', '2024-10-28 11:20:12'),
('41e0671efe04416afae4739df0db1677d66448cf9536a94988eab9c88a27c3d13b668db573375282', 58, 1, 'MyApp', '[]', 0, '2024-06-02 15:28:43', '2024-06-02 15:28:43', '2024-12-02 15:28:43'),
('425d4adf253c0b905a793fc09fc6ddc224068ca85bbea3d3e05f0e7c3e36a635dc8cd1fb179446a5', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:42:45', '2024-04-21 16:42:45', '2024-10-21 16:42:45'),
('425ff89ddf8d9078a24909549860e1e27d4d35070f2e2da656f56c3b4c7e7f2690d07c6fc5d46853', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:34:53', '2024-07-13 13:34:53', '2025-01-13 13:34:53'),
('4263c89d13c1339e761baf855a5bf778c2963afb10c040aae9fe55c4ce6edfed30f12ed671f4b1fb', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:36:51', '2024-04-02 16:36:51', '2024-10-02 16:36:51'),
('4276ce0d048f55c4813102d2db0bf8d19b531a66df64351ebb873aff0e4c1993d51d048b4c8ab8f0', 17, 1, 'MyApp', '[]', 0, '2024-02-21 10:56:56', '2024-02-21 10:56:56', '2024-08-21 10:56:56'),
('429d735704e076506d5215e938729162fd60b88cf5ae820224673d14d0cac95c0deac47b25d72720', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:05:29', '2024-04-15 15:05:29', '2024-10-15 15:05:29'),
('42da61640c5c90699685416509d00fcaa848caf5bc21f27769b2f4712260ad6bc8e9baeef87878d3', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:07:49', '2024-04-14 18:07:49', '2024-10-14 18:07:49'),
('42df870e6a6294d642580ead98e2a3df61e2a906b67a1def66e6362f0d6cf22ab885123864a4765c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:28', '2024-04-28 14:59:28', '2024-10-28 14:59:28'),
('42e139ef7c23c2c145232841c373460dcb873c9ca58801ffa27ccd92c8341adb77da5d45d5773508', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:55:47', '2024-04-18 21:55:47', '2024-10-18 21:55:47'),
('430c270fd53d942f975f6a10a446502bbed880e6df7d85b0cd4474b6a335dad2b966c943f1f36d1f', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:15:53', '2024-04-30 13:15:53', '2024-10-30 13:15:53'),
('4347294c482e28ceea1b964d4eb0c37643c75d79446ce8c2bc0e145dea9244522c7183521df9515f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:40:35', '2024-04-18 20:40:35', '2024-10-18 20:40:35'),
('437af099c896d0fa6d5b77bb828f4b525742b33bb70088d2a6b91128e9744c582ac3574c7798e786', 5, 1, 'MyApp', '[]', 0, '2024-04-08 04:44:10', '2024-04-08 04:44:10', '2024-10-08 04:44:10'),
('4465603ebd4098f26a4a1d7f90994b1dd2d7b42bbc6cb5a11d65e32e9bfd821bd5a858b239f8a332', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:41:41', '2024-04-08 15:41:41', '2024-10-08 15:41:41'),
('44dae3e85a7dcac615ca8fa6b633c585a883bfaeb82792c65e8e21bf2b14db809b1e9d6a9494b247', 5, 1, 'MyApp', '[]', 0, '2024-04-07 17:34:36', '2024-04-07 17:34:36', '2024-10-07 17:34:36'),
('4556a7a8d7a2dc049f986b5c72f2f1f99b9c0daca3a62ba10ae23ba3e7bdabc7469a54b481d67eb5', 58, 1, 'MyApp', '[]', 0, '2024-06-04 12:34:48', '2024-06-04 12:34:48', '2024-12-04 12:34:48'),
('45b7054b239dddfb812899a14b60bea78d8b234c23d34140f01cdf81cb01e7eb686da54a78761c74', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:01:04', '2024-04-23 12:01:04', '2024-10-23 12:01:04'),
('45c0f67d3597b01ec0bf1eecdd641e0a792a1306355c5df9563fb13b7697300e2f6cc193ae93f2d6', 50, 1, 'MyApp', '[]', 0, '2024-04-23 15:28:58', '2024-04-23 15:28:58', '2024-10-23 15:28:58'),
('45c3cab7899caa7b2589ee4b5d79b6056598804024467ab29302a0a6bcfc7f308a998bfd2ff2a0ad', 2, 1, 'MyApp', '[]', 0, '2024-02-15 14:39:56', '2024-02-15 14:39:56', '2024-08-15 14:39:56'),
('45f528e1bdec6f7bb1072e1626c5a5fe10cc446327a8d8a602578e13d4ac5c7b93475154ed1a53ef', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:14:07', '2024-03-19 18:14:07', '2024-09-19 18:14:07'),
('460c62e3ccfe0673e842f9b6a544bae01c96676398ace596fd21dfc8d3cee56d8e118703e0773103', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:25:27', '2024-07-12 15:25:27', '2025-01-12 15:25:27'),
('4621e392739a72cad64304adb9709f6157d9ea1183784cf73de30b90d973b078182d7f8dad7bb6b0', 65, 1, 'MyApp', '[]', 0, '2024-07-01 15:19:33', '2024-07-01 15:19:33', '2025-01-01 15:19:33'),
('4653d44c8f1b67ac3b2e5dc90b7bac3b2f6ac51170250c2dc1c9bd1f87403c5b6df85f7f1badb497', 71, 1, 'MyApp', '[]', 0, '2024-07-16 10:41:21', '2024-07-16 10:41:21', '2025-01-16 10:41:21'),
('468164060e51a363bf975c52a16e4f86ab87b9d58b97ef754309de65378c7ed5d07015fe6bbe5994', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:10:52', '2024-03-28 14:10:52', '2024-09-28 14:10:52'),
('46bbc74d462291d7ec4ffc2b5c6585480d671b8c60a5e86984739f1fad46fcc333e390074e7fed1d', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:36:38', '2024-03-24 17:36:38', '2024-09-24 17:36:38'),
('46d0572d80861664fe9f8f454efcb0c83f966ecff6e1b5a26341f64fbd6caa347ec3aee9bb879e78', 5, 1, 'MyApp', '[]', 0, '2024-04-23 17:45:43', '2024-04-23 17:45:43', '2024-10-23 17:45:43'),
('46d7809b2d946e953ae09a3c6dc177c11687ba52dac7a1d311a79fb9c961a19616d0471cb49bb2fd', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:32:39', '2024-04-23 13:32:39', '2024-10-23 13:32:39'),
('46de465cb0d98878cd15971f8545385dff3506d24d10dc7c9bc2e29e00be941363cabae691275cab', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:03:32', '2024-04-17 13:03:32', '2024-10-17 13:03:32'),
('46ec7f415eb8af5fde586393b9fc9edbc1a4b6844c609cb077495663554699d992231d98503cd0f2', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:45:41', '2024-04-30 15:45:41', '2024-10-30 15:45:41'),
('47470b46f5f9bc3941e47d85c78dc7ced5a7173572787afbaf58c2c79ce5ab6ae82d25ca77b9845f', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:43:02', '2024-03-20 03:43:02', '2024-09-20 03:43:02'),
('474b83f62504f174e767b41e94c4f99beb4c301ac9f333a55ed672bd7bcf45d6e45bf299d1c42fd2', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:40:03', '2024-07-12 14:40:03', '2025-01-12 14:40:03'),
('475a3edd597020f2e1997ba318754602c47199cb0b20bb6370db60b55b936e9d3014f3c6e1d1d9dd', 35, 1, 'MyApp', '[]', 0, '2024-03-28 17:52:06', '2024-03-28 17:52:06', '2024-09-28 17:52:06'),
('477f7f7f8018204074b863e6c58fea42205720a12c4671027a6228219f6f9483aa6bb899536959ca', 69, 1, 'MyApp', '[]', 1, '2024-07-23 09:12:20', '2024-07-23 09:12:20', '2025-01-23 09:12:20'),
('47977a0efad7166e831fd14cbe8b7844c58a438244565483e896eceb03149e528e114185d2813480', 74, 1, 'MyApp', '[]', 0, '2024-08-02 14:28:39', '2024-08-02 14:28:39', '2025-02-02 14:28:39'),
('47df5bf0942fc1f3d265fab59b7704aa43d6718ef42a6f0d36a1ee667d39a62c3e1b8d8f2f048632', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:36:42', '2024-04-23 14:36:42', '2024-10-23 14:36:42'),
('47eb27e56d61e0a8473d9b17524f32ee2fce83b424740b6f6be7830ba9edfb34bfd7e92aedc700ee', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:20:27', '2024-03-26 16:20:27', '2024-09-26 16:20:27'),
('480d9fc2d1c2a046215dbe313b476b140f1b8ca6cc55f617b0749792f611eb166390057e381f9829', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:04:21', '2024-04-23 11:04:21', '2024-10-23 11:04:21'),
('482fd03ae28d5671602b9b6eebcb245dea2b0c7848500c8207279cca96d0b4bac4fb9a906f8f12e2', 5, 1, 'MyApp', '[]', 0, '2024-04-15 14:25:26', '2024-04-15 14:25:26', '2024-10-15 14:25:26'),
('483c8bd84bc0fe737911ad72748c5816c44ecc5a76b1e26898c8726ac589ccb2db4474a97247714f', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:50:27', '2024-04-02 16:50:27', '2024-10-02 16:50:27'),
('48428b55d9ed6f5c2b385a9b949f9a9b565ad09b5e68c622960c6c913ac4d8bd376cfad7117f1107', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:25:52', '2024-03-26 12:25:52', '2024-09-26 12:25:52'),
('484a77568a286ed6845e8c6e4e231b3f6725ca6d2a2df6686b687d6dd6da50c55d3c795e90dd003e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:29:53', '2024-04-23 14:29:53', '2024-10-23 14:29:53'),
('485339f894b4208e4713b61e029b7f62fcb9fee4f40b227a8a84fe777de7999075818816407e1793', 5, 1, 'MyApp', '[]', 0, '2024-05-02 11:00:04', '2024-05-02 11:00:04', '2024-11-02 11:00:04'),
('488dc69bf5241d68766803ee95e51f71bd557e403715ea3f1d106c3687cb769a70aed5ca6ef2d626', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:34:43', '2024-06-05 10:34:43', '2024-12-05 10:34:43'),
('48904514f6c569d07664b2b9b2b0f570fc88529b72f4d85fdc37b9951a2c86793bee63af4f510e37', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:07:16', '2024-04-17 13:07:16', '2024-10-17 13:07:16'),
('48aae837f5f4f149f89815246a9baa437cdd1173376774f93b192c3a180f3db094d0ba3706418a88', 5, 1, 'MyApp', '[]', 0, '2024-04-03 20:47:25', '2024-04-03 20:47:25', '2024-10-03 20:47:25'),
('48bb90246cd93f919298e239b1b5f3d0546e47a26fbea95f3692bab07519d30a6368fd7975df8bbe', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:23:16', '2024-03-27 12:23:16', '2024-09-27 12:23:16'),
('48bbc3467b9e12bf306ab454da12a5361f8afbaec7d5dc49924516f31911b97f1c6c9e36d30d03f3', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:51:12', '2024-04-30 13:51:12', '2024-10-30 13:51:12'),
('48f5cbeb719b44d738181a70037655e8e7a98dd60eb11420d0ce6d59b881279519a212edb3a30472', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:53:41', '2024-07-12 14:53:41', '2025-01-12 14:53:41'),
('497749c2abccb686ea5df6a95ee22185dc412e2441857d4ac4043708d4a551a5d1ce2fc6dbc8be19', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:30:36', '2024-04-24 15:30:36', '2024-10-24 15:30:36'),
('49944a5476e2c5969203a002447c564cd64b5e1703abb89535d4f0db0a1be5bc14c36c437d4518db', 5, 1, 'MyApp', '[]', 0, '2024-05-01 18:56:55', '2024-05-01 18:56:55', '2024-11-01 18:56:55'),
('499d4fcac6baea217da56d5d32913d6d21a4f7be12a1a67dfe5456de3f62a5c5f9413ed7aabeee82', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:24:59', '2024-04-30 15:24:59', '2024-10-30 15:24:59'),
('49a1a6d061a25df500ccb993dab940c16cf7467e0c6690c6612f133a39eb85a517172db345d50c82', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:22:02', '2024-04-15 16:22:02', '2024-10-15 16:22:02'),
('49a61f9cfc332eb2898310d8d4e55ca96c1a65c33ea066bcf00987ec6efdbcb16c5fcc1b722c8624', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:30:18', '2024-04-30 15:30:18', '2024-10-30 15:30:18'),
('4a4174e8c42a8ead836df8f2bc1dc8b644a19176148f5fab937cfb70e8af3136c7b6ceeb40a2ab15', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:52:33', '2024-07-16 09:52:33', '2025-01-16 09:52:33'),
('4a44f119831b7d64fc1f4a619ab43416347e8416778292308f57050be91fe8ea73b9ba00044712cc', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:05:35', '2024-04-23 12:05:35', '2024-10-23 12:05:35'),
('4a4936b3510af414d8e0b5ed3516ef12d126919c73f5531b346e5affc93cfa0a3b32fea67ca24ccf', 58, 1, 'MyApp', '[]', 0, '2024-05-21 15:28:13', '2024-05-21 15:28:13', '2024-11-21 15:28:13'),
('4a4f1ce08a596754696a62b58d0e446284f9da0275452c1273fc3ac219b452262399e203d6c9c309', 70, 1, 'MyApp', '[]', 0, '2024-07-16 10:39:20', '2024-07-16 10:39:20', '2025-01-16 10:39:20'),
('4a5bd42099a1b4654502e3241c5264a3c018803676bbab4bb6155466e9d24ab28ae10e482f42f795', 5, 1, 'MyApp', '[]', 0, '2024-04-16 16:55:54', '2024-04-16 16:55:54', '2024-10-16 16:55:54'),
('4a80a12382a4b1b921eed9257c7b4de39750d102a5b97d911fcd4cfb39251133dd4df5bfc039c6b7', 37, 1, 'MyApp', '[]', 0, '2024-03-26 15:58:17', '2024-03-26 15:58:17', '2024-09-26 15:58:17'),
('4a8162c92b443a3949d8cd123b211ee9b0370de7918f45dc7038e2f69e2ae0806871429a2e341d37', 40, 1, 'MyApp', '[]', 0, '2024-04-02 15:48:23', '2024-04-02 15:48:23', '2024-10-02 15:48:23'),
('4aeafda99bbf67d367d71ae8b50d196a1b1508e934e5233d2a050446b94c585e941543ed6e8bdae9', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:02:11', '2024-04-29 16:02:11', '2024-10-29 16:02:11'),
('4af0e231486798706b94d007f53d880614ae10db731a5e8be99ff4973260fa759e85c057433a75af', 71, 1, 'MyApp', '[]', 1, '2024-08-03 14:42:55', '2024-08-03 14:42:55', '2025-02-03 14:42:55'),
('4b1bbc03eaad0103933aed156ed1dd1416f2e3e0887c51fb94cf3d5bec972fdeb5c3bd1996c0ad46', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:22:52', '2024-04-08 05:22:52', '2024-10-08 05:22:52'),
('4ba15b033e5e4daf64271aa82a3da0e9ae7144d64b0bc5c12f6f093a13dfe61b557fc6a850f8be02', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:25:42', '2024-07-12 15:25:42', '2025-01-12 15:25:42'),
('4bc9025b8965bfa8ae5c1cdb8f4455dded5789666d0e4d04cec942cae0fd1109b171964d0d250079', 5, 1, 'MyApp', '[]', 0, '2024-04-15 13:37:16', '2024-04-15 13:37:16', '2024-10-15 13:37:16'),
('4bcc520e82a01bd36ffd7471d47ea1ed70553f62745ad25566dccffd632ae9b88472e71d483cc8fc', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:21:43', '2024-07-12 15:21:43', '2025-01-12 15:21:43'),
('4be4fd27e4816f2f012d8c866a3ba973ba2e5a48c8180d432fa8102ddb7448236614fa05d565a6fa', 69, 1, 'MyApp', '[]', 0, '2024-07-16 11:29:30', '2024-07-16 11:29:30', '2025-01-16 11:29:30'),
('4be5d24d9104586dfc4f2eaf97d0ca34694c4a04965abaf86d5442abdf32b419cde4ca8e24e5a184', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:05:49', '2024-07-13 15:05:49', '2025-01-13 15:05:49'),
('4c03aa465cd289b87ec4589ecb198d9ff997c1f66a97d6e6b228cdc85978853e2226745a9c840ba8', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:33:06', '2024-04-30 13:33:06', '2024-10-30 13:33:06'),
('4c1d1b41c75e576ebd509684e1107e6157732c86a96ea4c387f8a5ac9c3e5d594b3c778560458f57', 58, 1, 'MyApp', '[]', 0, '2024-06-06 08:54:01', '2024-06-06 08:54:01', '2024-12-06 08:54:01'),
('4c268b919d82a288c3556effdad4c2196e8f47cfb6e01ae5336c634b299eb2c75d4f5100a1375bd4', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:26:54', '2024-03-27 12:26:54', '2024-09-27 12:26:54'),
('4c336f05d56e5043191b9b36fa28598c584b14b9ae97ddcd3c4d66c3afa30c32355bdacbed786a04', 45, 1, 'MyApp', '[]', 0, '2024-04-07 12:41:57', '2024-04-07 12:41:57', '2024-10-07 12:41:57'),
('4cd2232c63784fd0bbfef6a7f478e6dab67dfc7a457bab12f1d8ff550d7c79b0d598aed096763a68', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:16:58', '2024-04-28 13:16:58', '2024-10-28 13:16:58'),
('4d0f8218ae8c5fb667ab9bbc265095f5dcb1215c5e8b23b89f7421a5cd1bef0f258229a7bc46012c', 5, 1, 'MyApp', '[]', 0, '2024-03-05 12:32:38', '2024-03-05 12:32:38', '2024-09-05 12:32:38'),
('4d0f87f3aa09cebe1ce0f930df6a272b6b1964636cb049fee20239468c8b64eea0f29cb4deeeb115', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:40:09', '2024-04-28 11:40:09', '2024-10-28 11:40:09'),
('4d1de6930b2f58c44ac54bbc63a6006f488f4aa06eac8317501c293c9e271ab28d6709f0f65f7edc', 5, 1, 'MyApp', '[]', 0, '2024-03-05 15:51:44', '2024-03-05 15:51:44', '2024-09-05 15:51:44'),
('4d3526619d2f342eefb90c87e15cef2a9961775ab9743a821e54b03986c404cb06c152a994eb67db', 33, 1, 'MyApp', '[]', 0, '2024-03-23 16:59:22', '2024-03-23 16:59:22', '2024-09-23 16:59:22'),
('4d4f7091a10a0572f8e54e721a3699e38615aae1d09d61177795a95d76cbfb8c8b96b49cd7e22b1e', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:52:14', '2024-04-15 16:52:14', '2024-10-15 16:52:14'),
('4d57e3b933fa3b8d26b93dff0f8bd3946c97413d4f7420ef3964e21edc3f98f4d29d7eb9a6de7304', 10, 1, 'MyApp', '[]', 0, '2025-08-17 15:01:27', '2025-08-17 15:01:27', '2026-02-17 18:01:27'),
('4d6c9bc121fc8f03169eec6e39891c1b00580ec568a1bb70d3588ffb46dcde629185929de852817e', 5, 1, 'MyApp', '[]', 0, '2024-04-14 10:23:44', '2024-04-14 10:23:44', '2024-10-14 10:23:44'),
('4d7193a9a8f6eacafcf675fe0015f95ac728faad9757d130095b64495cbd376947492e71d26726d4', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:39:03', '2024-04-28 16:39:03', '2024-10-28 16:39:03'),
('4d7975148acc767ee263f43bdfdcf270017c0a5a8be8024fc2373db514df252b55ac37265fd2c121', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:12:59', '2024-03-26 16:12:59', '2024-09-26 16:12:59'),
('4d878682099febef879ec314770392e1cac8f7174a118ef5cb548650890dda06d09c6613f005a9af', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:20:03', '2024-04-24 15:20:03', '2024-10-24 15:20:03'),
('4d9bf0df9004f10ab41287671b834760d0a2eba4c06a9e774287db0db00557b7273d00c3cdf9fe07', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:32:10', '2024-06-05 13:32:10', '2024-12-05 13:32:10'),
('4e1dbde5586e4d4b553ef2e877a936642ffd31f80ddc5c485d36cf837fc88a6f29b43e750c73262c', 37, 1, 'MyApp', '[]', 0, '2024-03-26 15:54:09', '2024-03-26 15:54:09', '2024-09-26 15:54:09'),
('4e60662c0535ee97fbb63dd1483ede833cf507e198fa19383ec7a6252309f6c125719920ba46b48d', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:09:44', '2024-04-28 14:09:44', '2024-10-28 14:09:44'),
('4e72a67126920b85b9e1473b22d7ff962e13a177e292578d52e56452dc94acfdb6465144584e9bb3', 5, 1, 'MyApp', '[]', 0, '2024-03-21 13:45:30', '2024-03-21 13:45:30', '2024-09-21 13:45:30'),
('4eca80bcccdb3faa67cf3ad6ad0d78b532ba2bb7fb6453289bfd770f8c78f832b1135552706a02de', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:16:15', '2024-04-29 16:16:15', '2024-10-29 16:16:15'),
('4eef933adfda5cc2cf07bc694174c002ab438c1ba570e17d163a77e28a6eca6a18594d8128effba3', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:47:37', '2024-04-24 15:47:37', '2024-10-24 15:47:37'),
('4f35e40f489986f3ea79a110008d94ace26bdb53229709248eb8d9f7ea765440ac183a6331b9540a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:35:34', '2024-04-23 14:35:34', '2024-10-23 14:35:34'),
('4f47265f3ab28495e4d4b6ad7c5e7fa3ce9d302b04a13fc33d380efe2a8fe12f5be228e96fa376aa', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:55:27', '2024-04-18 21:55:27', '2024-10-18 21:55:27'),
('4f48a0ebf134a7d46f189367775ad4e8ee330f542d51fa16bda85aa51677265fe527a9dc225a54b3', 58, 1, 'MyApp', '[]', 0, '2024-06-27 14:02:58', '2024-06-27 14:02:58', '2024-12-27 14:02:58'),
('4f620f24f162798669261c8d6ad18a2f214c0c159d6d50d53a7d55ceadb65eebad7c7b87f6d5fc60', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:29:05', '2024-04-23 14:29:05', '2024-10-23 14:29:05'),
('4f831986f09d1f3513e70beeef86ce2c9b7f7334e19f8692204bd25fbc5d4dfd53202a40eaff0209', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:24:59', '2024-04-30 15:24:59', '2024-10-30 15:24:59'),
('4f99f2d72cf027a24b8eba016daa3658a9a3ae50db02748ce1d0cac71f91f0ca4fcf8bd4162cf982', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:58:21', '2024-05-28 15:58:21', '2024-11-28 15:58:21'),
('4fa95c3eaa8b1c43549b13724c7c6efa1b1873304a3e0421036d48ed0bf8ed89469e1d18522b7c87', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:48:58', '2024-04-23 12:48:58', '2024-10-23 12:48:58'),
('4fdde22c60362f388c23adacafa80f078441aaac8777fc848b7732da2fa686b3322b0d572d83667a', 35, 1, 'MyApp', '[]', 0, '2024-03-27 14:10:06', '2024-03-27 14:10:06', '2024-09-27 14:10:06'),
('500f44920f762eae9315627a552d3398f1c21fe280e60f54beaffc3a96c05c7419a4d37ad6420045', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:22:26', '2024-03-26 16:22:26', '2024-09-26 16:22:26'),
('502f9d4c2825a800102e409a30f78f5feece68145de0aed6e07d5e428097d3b0702758984a70957e', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:21:30', '2024-03-27 12:21:30', '2024-09-27 12:21:30'),
('504a59596cfc3cb16e1c71d237b874f901489235782cbe7675260562b23bf3da96fb96d9e012fb79', 35, 1, 'MyApp', '[]', 0, '2024-03-26 17:35:07', '2024-03-26 17:35:07', '2024-09-26 17:35:07'),
('50699618b7dd1757888bb19cfcee95b65826a758f8eab29aafa2e805c86a72e3c42d6261cc6ff2f9', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:33:11', '2024-03-27 13:33:11', '2024-09-27 13:33:11'),
('506cba17f19d6c9bd7b260da102b0db1c5402dcd393699018b09c3e6dee880de47b64ba0e8adfd62', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:09:17', '2024-04-18 19:09:17', '2024-10-18 19:09:17'),
('507c149b91b936d43a2c62890f09bda154735e784fb69b3e1846364ccc53724c2fc696aac97190aa', 69, 1, 'MyApp', '[]', 0, '2024-07-16 16:55:18', '2024-07-16 16:55:18', '2025-01-16 16:55:18');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('508b469fe78af2160e9643caa54e95522dfc8b80c3f145e8f5090ea7e59d334c3ea7caa6020cccea', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:37:06', '2024-04-18 22:37:06', '2024-10-18 22:37:06'),
('50c21dcdfdf0e07fb83c5ea9469b94550eada0c046b799566896aa01f50ab4d927ce29b7b08e0019', 11, 1, 'MyApp', '[]', 0, '2024-02-25 11:02:12', '2024-02-25 11:02:12', '2024-08-25 11:02:12'),
('50cbe64eccd6429c4ea61bf1b329c74807fa4a2be3d8cb77af39123d03ba38781d2d110284727e2c', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:13:24', '2024-07-12 14:13:24', '2025-01-12 14:13:24'),
('51437c6a1bd626e1b848a5c15de5713f07747a012a4d00aa527192a265c0db1e894e13ad6352fd92', 58, 1, 'MyApp', '[]', 0, '2024-06-05 11:19:23', '2024-06-05 11:19:23', '2024-12-05 11:19:23'),
('514464deff037473c47c6031a7ffebc59890b2ba7fa5000d6d1cd103d6369331d29b351878de03ce', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:04:42', '2024-07-12 14:04:42', '2025-01-12 14:04:42'),
('5144d68b80150d81fe64079770c56dc6d930873a51959b8401d0212e04f7e8e2268977ac28931241', 45, 1, 'MyApp', '[]', 0, '2024-04-08 03:14:04', '2024-04-08 03:14:04', '2024-10-08 03:14:04'),
('51494259f067ae08746a627dfcc40c8817272a2bb713c20c00c8788e346a4df3cd3bf452cb305925', 58, 1, 'MyApp', '[]', 0, '2024-05-28 16:25:18', '2024-05-28 16:25:18', '2024-11-28 16:25:18'),
('514d792d1c3dae9a99c3ccea2d53335aff63afe23f038551eb34803216fb9458808b851bc9a20276', 58, 1, 'MyApp', '[]', 0, '2024-07-02 14:11:40', '2024-07-02 14:11:40', '2025-01-02 14:11:40'),
('51934e32e989086a448dcff697e1468aa3b008127268512659da129760520b7b652285ba4d3b1560', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:49:05', '2024-04-14 14:49:05', '2024-10-14 14:49:05'),
('51a3e2e264b0052d2390893c53a3bda29d1db7502456db7da923eea67707c3c2cdf1f4de47ece6c5', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:59:13', '2024-04-15 15:59:13', '2024-10-15 15:59:13'),
('51c9bec4c3ce7053e6f2190e2fb441372acd15fac97880bb0b14613856a54b0ff39acfc41582e2a2', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:49:05', '2024-04-14 14:49:05', '2024-10-14 14:49:05'),
('51ca743843483475db5b220467b2a7d6f55f27f8eb4756b74ca7c3c636b1041a32b0b0e019b290fe', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:56:54', '2024-04-28 14:56:54', '2024-10-28 14:56:54'),
('51e8e166f7ad5820d7ea0580bbe677bc00e6ea8614c7c3baee9c9d5bafef7cb31bbad4fc60fb3bd9', 5, 1, 'MyApp', '[]', 0, '2024-02-21 17:46:19', '2024-02-21 17:46:19', '2024-08-21 17:46:19'),
('52241337b2b3073cf29ce7e040bb9b0a2bec641f12a6d6bd183141c1662a1eb6995032cf01c1cbe0', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:38:39', '2024-03-24 17:38:39', '2024-09-24 17:38:39'),
('52252086bd100fd3670ccdf42faaa803c7d0ee592cd8a63db9c9976678f788793fbd65cc3434caa0', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:57:46', '2024-07-12 14:57:46', '2025-01-12 14:57:46'),
('522c114532029af687f85b212c025809681f4d77aab9149f77897fbbe53a470e0d870e6775eb8a92', 29, 1, 'MyApp', '[]', 0, '2024-03-18 02:55:48', '2024-03-18 02:55:48', '2024-09-18 02:55:48'),
('522e70e720d18fa1db69e7b2570c14fffb97cde299892e7210531fd162c71f60dcc8add82072b814', 69, 1, 'MyApp', '[]', 1, '2024-07-26 15:01:36', '2024-07-26 15:01:36', '2025-01-26 15:01:36'),
('523203a1dbf1f31f312d72c6c4c8aae8f154abea6372fe7a635cadba09b6dc18d6b24d59cedefa1b', 5, 1, 'MyApp', '[]', 0, '2024-03-05 12:58:09', '2024-03-05 12:58:09', '2024-09-05 12:58:09'),
('5233d57701878a8e9fa240afb87dbe584ded70009742a6f8e3bfc92618258eb7e2693228be31de36', 62, 1, 'MyApp', '[]', 0, '2024-05-29 20:21:23', '2024-05-29 20:21:23', '2024-11-29 20:21:23'),
('523c8a8972e15c1e1c549ca561912a70b0bfe1b2ed451566a8a80fd84dbf6fdc562f6f5132a24bd0', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:28:13', '2024-04-23 12:28:13', '2024-10-23 12:28:13'),
('5262ec1f83209594a30d73130d26336efed2ca58c7745043e701af47c3316c17a288616a2e75d247', 58, 1, 'MyApp', '[]', 0, '2024-05-20 14:14:50', '2024-05-20 14:14:50', '2024-11-20 14:14:50'),
('5263004f42cc6800eb9dfb2382a1d4650a30820ee6ec944026da44e970f36dabeffbd3acf09b53d2', 5, 1, 'MyApp', '[]', 0, '2024-04-08 17:31:04', '2024-04-08 17:31:04', '2024-10-08 17:31:04'),
('5266bdae137c7936a00336784a5177d1cb7857323543e54a08ba2d2af7ab528c56fb7d82b7f7ab43', 5, 1, 'MyApp', '[]', 0, '2024-03-21 13:44:46', '2024-03-21 13:44:46', '2024-09-21 13:44:46'),
('527a30376474c6fff1f606fbba733302abbc60f463687d3ccac37470e01a99c4bfc94324b1772fe1', 64, 1, 'MyApp', '[]', 0, '2024-06-05 12:47:25', '2024-06-05 12:47:25', '2024-12-05 12:47:25'),
('52834cf742d730213daee53fd687730d73707ccf1d04e4c148485bfbca85873fa226fa47333d10a0', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:42:39', '2024-05-02 12:42:39', '2024-11-02 12:42:39'),
('52de81600f199e20d2b55efe8f12cb4d6d52306d863db9bc58694115984bf842d7435f317f9f672d', 66, 1, 'MyApp', '[]', 0, '2024-07-11 16:36:19', '2024-07-11 16:36:19', '2025-01-11 16:36:19'),
('531cb4d853fd91cd9f4c57544e9c1bab22a472a72e5dcd6155e5aec170babdef9036ee638631440e', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:34:40', '2024-04-02 20:34:40', '2024-10-02 20:34:40'),
('53a2db8b2edc787eaeaf47e57ab428dcdf1dd8330d742038e21f49d7e491d24f1724635a61518db0', 5, 1, 'MyApp', '[]', 0, '2024-04-25 17:26:59', '2024-04-25 17:26:59', '2024-10-25 17:26:59'),
('53ba08905df5fc33bcecfa07922586ef7dfa04bff662eecd604aa327d49e9f2e32e6f35f62607535', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:12:32', '2024-04-21 14:12:32', '2024-10-21 14:12:32'),
('53d1c96b7ee04601cdd9108c24e20bc4d2b4a8563a0948e8d9e53fce79e00ad1c4658f700ffc6e05', 5, 1, 'MyApp', '[]', 0, '2024-04-02 12:51:38', '2024-04-02 12:51:38', '2024-10-02 12:51:38'),
('53f505b524a44d34addcf2f6def2664c1c6661cf1a5464c1aca3f11ab1eef49a44fe28201c133eba', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:05:35', '2024-04-23 12:05:35', '2024-10-23 12:05:35'),
('54098a383fd042de9adf4c2226c579660b1b8ad25b390f0099ad62186308e30e8a1f6c2fa3fd42d2', 5, 1, 'MyApp', '[]', 0, '2024-02-21 17:53:52', '2024-02-21 17:53:52', '2024-08-21 17:53:52'),
('55130c82ca1012b3f043d742a50dd5ef4dd2fe6c479fc46222d95ef3ee281970ccde2caf6245a8f1', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:47:42', '2024-04-22 18:47:42', '2024-10-22 18:47:42'),
('55198834ee95607854aa9806b441f938e0449bee17c3eb6f2a99daea8cb0961620013c5615dd79dd', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:44:30', '2024-04-17 17:44:30', '2024-10-17 17:44:30'),
('552320481df72cee66a0f5714fdc4fc7fd43e206f02041478f51be9b218b7c0da456812fd05eade3', 5, 1, 'MyApp', '[]', 0, '2024-04-21 11:52:03', '2024-04-21 11:52:03', '2024-10-21 11:52:03'),
('5582d434b044ab093168f77277f7fb01c8c789929d39f1d19071a23caec96292e0371caa84f4cda5', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:22:29', '2024-04-21 13:22:29', '2024-10-21 13:22:29'),
('558702fe513cb247f1961192c6fa581c291be6d0edab29ed502b4505f3027f55956207cc6f70327a', 35, 1, 'MyApp', '[]', 0, '2024-03-26 17:38:09', '2024-03-26 17:38:09', '2024-09-26 17:38:09'),
('559e5887779a68df8850507a52c7e68acb1776025359223c1493dfbe738204f8c171599de04eadf5', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:26:19', '2024-03-26 16:26:19', '2024-09-26 16:26:19'),
('55a462606d8b80fdfaf0c6e433374237be9a5942cf782d00be9d50dc4c9ce2310df36e6c6d1f86a0', 74, 1, 'MyApp', '[]', 0, '2024-08-01 10:54:53', '2024-08-01 10:54:53', '2025-02-01 10:54:53'),
('55bde40fbe429fc8f344da0e3588fc9d79edb519cc8acf065aa904991502a7e29155c4586cc09513', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:35:22', '2024-04-25 18:35:22', '2024-10-25 18:35:22'),
('55dd699518e8e1191b347e9392bd8e691b9b7094f8e8f92bfae6693a54ccc52f2f2af0b3db39bc2f', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:03:13', '2024-04-29 16:03:13', '2024-10-29 16:03:13'),
('55f115f8c9c59fd53762ac7c0e03067be52b7db791366bda8a18e0955a6cfb4336c4a8d89199d8ba', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:46:43', '2024-04-21 16:46:43', '2024-10-21 16:46:43'),
('565a9b3ebb051b9250caba7953a93b4667adc41c377f9e9e9ac1ebbba251e79b38552c995795df5d', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:37:26', '2024-04-14 14:37:26', '2024-10-14 14:37:26'),
('5665720e55234364d188d9090c3221189f67a2ce0fd3fdfcff348094bb516972b43b5beb1e5cf116', 69, 1, 'MyApp', '[]', 0, '2024-07-23 13:08:39', '2024-07-23 13:08:39', '2025-01-23 13:08:39'),
('568a0b5d7725b9ae4f8531cfc420659d3958ca2e3a2b58a0ce7ae562232c86a28c9ef94454f764a1', 5, 1, 'MyApp', '[]', 0, '2024-04-21 15:53:23', '2024-04-21 15:53:23', '2024-10-21 15:53:23'),
('5691026f46a69dc24bc4b578f2ca86604c691a2068453a9bc86a4815bf7a8ff0c602e627e2277c43', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:14:53', '2025-08-14 13:14:53', '2026-02-14 16:14:53'),
('56985d79dad7dfa80b1d1ab45c9742eaca002d11a2ebd36b0ab7609f1f5cbd79871de940b269d9ac', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:20:55', '2024-07-12 12:20:55', '2025-01-12 12:20:55'),
('56d66f99e2c0b8c62ee3c441024234d6e4c876c6769d17d3e7ec80c4edd6a715cc8a1d03307d5c61', 62, 1, 'MyApp', '[]', 0, '2024-05-30 09:12:02', '2024-05-30 09:12:02', '2024-11-30 09:12:02'),
('56d9c28dbb1133383f48f90786bf9bc0471507450890ccb014a965db2a8a78f35e28fc9c81b2a9a5', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:24:47', '2024-07-12 15:24:47', '2025-01-12 15:24:47'),
('56dd98ee7f5f22b0fc403cfe2d2913dfd540f531478c7f12554272871c1501915be8b4a443b0b157', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:40:36', '2024-04-18 20:40:36', '2024-10-18 20:40:36'),
('5722a13361ff6a8edd2b8ebdb67a4eda5b3982c30694adae10eb72f1fae791c0c66f7490308b3ddd', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:14:37', '2025-08-14 13:14:37', '2026-02-14 16:14:37'),
('5742e0424ad7ce2977c23bcd3e4c73b4d97d8bc5116684034f8f262fb5ef5dcd906c7cae267cf1de', 71, 1, 'MyApp', '[]', 1, '2024-07-23 12:52:30', '2024-07-23 12:52:30', '2025-01-23 12:52:30'),
('5759f72b26e418471af100a0a96ef93d65f50e1d85bc3d0ea36ddac3655b64b1ba1f34a311aeea14', 35, 1, 'MyApp', '[]', 0, '2024-03-31 16:35:27', '2024-03-31 16:35:27', '2024-10-01 16:35:27'),
('5773ee7c55df8f8d32ce24aef343c0b1ef4c1bc72dca6db6453bcb7192d4406940a415813a3e675d', 58, 1, 'MyApp', '[]', 0, '2024-05-28 16:25:18', '2024-05-28 16:25:18', '2024-11-28 16:25:18'),
('57ba4620430899a6949fc0dbfebcc51379fd804c2c62d2702d188dba46553e7568d2b8ababeb70e9', 10, 1, 'MyApp', '[]', 0, '2025-08-17 14:21:28', '2025-08-17 14:21:28', '2026-02-17 17:21:28'),
('57cf66fbe8af6e2ece6dc9f0734e9beeb9120f8ee444ad84751efbea9e6c1023524163725f7354fa', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:02:35', '2024-04-24 15:02:35', '2024-10-24 15:02:35'),
('57dcb3a364007dc09a978e88954bbc03db73f3b097cb9af1d76079a38263a07510f5b150a80e5e5f', 64, 1, 'MyApp', '[]', 0, '2024-07-07 17:10:15', '2024-07-07 17:10:15', '2025-01-07 17:10:15'),
('57e43aaf2e242154a78d76071ee42483e9baa44b662dffdd8684e9e362297ce21fcb34a3f3bd71b4', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:59:36', '2024-04-08 15:59:36', '2024-10-08 15:59:36'),
('57f43facc9d328e3eb5053e7c0b2040811345c1d8a3e73830bd253cefd640dc53b976c8b73789d87', 58, 1, 'MyApp', '[]', 0, '2024-05-20 14:25:53', '2024-05-20 14:25:53', '2024-11-20 14:25:53'),
('57fa539fe808bc0330732da4253e2cd229335d95b26e56acce98cfce767559716131e782e44be09a', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:12:59', '2024-03-26 16:12:59', '2024-09-26 16:12:59'),
('5824491533dfe46871a26403664f632713713104c4e4897fdb3541b0681b877553181c98d040a3fe', 35, 1, 'MyApp', '[]', 0, '2024-03-30 23:49:11', '2024-03-30 23:49:11', '2024-09-30 23:49:11'),
('5833a1615495b623ee833208a5f799239468b69dcfee91ba5b1ada2be0195e1aefcbbe7a4959dcaf', 5, 1, 'MyApp', '[]', 0, '2024-03-18 15:17:28', '2024-03-18 15:17:28', '2024-09-18 15:17:28'),
('586268eea919f1360b73cd74afdbb01e7f5e25df191d2556dd0b1052c36e8beb82a417884c2575f9', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:23:32', '2024-04-23 12:23:32', '2024-10-23 12:23:32'),
('586957b3a010d5c80e9026f9df1d11f23fd07c5c44bfc32749670f5dcba3ccfc336ef6655b3486e1', 5, 1, 'MyApp', '[]', 0, '2024-03-06 13:47:28', '2024-03-06 13:47:28', '2024-09-06 13:47:28'),
('5877894c4fa2644e2d12111effeec7d34bdce4c8f1c448dc0500245cd49e80e738e44b68cf6234dc', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:01:10', '2024-04-29 16:01:10', '2024-10-29 16:01:10'),
('58938a0eb35c1325d7e852f3e4509ba762a6a19b250254358da6c9142aca72109d87c2f81ada6e06', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:59:56', '2024-04-22 17:59:56', '2024-10-22 17:59:56'),
('58a566a7f19961eb5a177f88c97ce3aaaca80c62c41bae156abef281a2cb38af9bfa115843c41439', 58, 1, 'MyApp', '[]', 0, '2024-07-04 19:22:49', '2024-07-04 19:22:49', '2025-01-04 19:22:49'),
('58b5c1e1238fe4b6938942e4ff4d1fa2034072e070771ddc3733b9bab2a6bb3ee23f42f2cb8a7c60', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:19:47', '2024-04-17 17:19:47', '2024-10-17 17:19:47'),
('58bb3dfc1a72c9a46c636f4cc2b076a1062b5eaa75a57c39d96959ac4502e7aec5f57931b62d711c', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:51:12', '2024-04-30 13:51:12', '2024-10-30 13:51:12'),
('595cc06b784f1f00a51946e62a0b9cd71861e3ade8abe6f692a3279ac47499b225dde292219ea445', 66, 1, 'MyApp', '[]', 0, '2024-07-13 14:58:41', '2024-07-13 14:58:41', '2025-01-13 14:58:41'),
('596832b97986f37e98616791282a68961cc8c1b3e02ea2ef33e6a3456878b51ad9911ad33f9e5cec', 58, 1, 'MyApp', '[]', 0, '2024-05-08 14:21:47', '2024-05-08 14:21:47', '2024-11-08 14:21:47'),
('59965c8cae2aa3e5d7f6f846a42a797af1fc8dcf5076db467ffcb9a8dc6b47b0ada1a3b0e1e1942b', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:33:25', '2024-07-16 10:33:25', '2025-01-16 10:33:25'),
('599b31cb17c4914ff0fa01b05a9347d236dec88651042e1bc66d5fd9318a3e9869097eea332f6c33', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:43:54', '2024-03-24 17:43:54', '2024-09-24 17:43:54'),
('59aa251cc29a7b664a9eee79b57bfe741c44a8acde333e998a1be3260489dce874f00eb68267342e', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:49:19', '2024-04-24 15:49:19', '2024-10-24 15:49:19'),
('59ef6416468bc0faf77fdec27b08ac6b97fa162de452b138e15f7657d7d0d7b89cad19ba5738e603', 5, 1, 'MyApp', '[]', 0, '2024-04-14 11:20:47', '2024-04-14 11:20:47', '2024-10-14 11:20:47'),
('59fd81b1c480fb59a87d39eda9c628be7a411d55911e387015e4639fd8502811f0b37bafdda29be1', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:57:03', '2024-04-02 20:57:03', '2024-10-02 20:57:03'),
('5a2216d4175c5907614d03da9820879ee42b525b4aa889d9c3a8af69a12bb1fc5080ba1133483242', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:44:44', '2024-03-26 04:44:44', '2024-09-26 04:44:44'),
('5a3d39c93baaf3b4ebbc7f16c8249959861f44388e7df795f5e562588df4f0a507452b443a83d2b5', 35, 1, 'MyApp', '[]', 0, '2024-03-31 16:14:12', '2024-03-31 16:14:12', '2024-10-01 16:14:12'),
('5a400b0e49156eb90bc805b81e9efbc901264b87b176ec7c3deaab200f9fd8f463fd924606bb7165', 69, 1, 'MyApp', '[]', 1, '2024-07-16 10:24:40', '2024-07-16 10:24:40', '2025-01-16 10:24:40'),
('5a51cc5fd694747bc8598d6696258738e7800ae2b30a24ca6e7638c4ae6d22e02f2c01214be513de', 5, 1, 'MyApp', '[]', 0, '2024-04-22 16:56:41', '2024-04-22 16:56:41', '2024-10-22 16:56:41'),
('5a76549f9da04b82f40141c6ba1012bc39367c63f8f51a6a10ef2af3490efebe8d8fa4a5be52d1d2', 66, 1, 'MyApp', '[]', 0, '2024-07-13 19:03:37', '2024-07-13 19:03:37', '2025-01-13 19:03:37'),
('5ad449c932d547cdf647e648e077b2ad29d236ed8da0443c5daa5e534c5a7f9edd85cbef2cc1af8d', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:17:07', '2024-07-12 12:17:07', '2025-01-12 12:17:07'),
('5b0e6c36f4c63645319462d5682caf98d8874519f117cbabfaed65705bc653dd88229773154e5e82', 1, 1, 'MyApp', '[]', 0, '2025-02-05 16:43:45', '2025-02-05 16:43:45', '2025-08-05 18:43:45'),
('5b0f17c49f98b68c14466c9349ef809ff3d17a00a9c4f0511e9813eee958356b4993702a6b81baef', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:44:07', '2024-04-08 18:44:07', '2024-10-08 18:44:07'),
('5b41471fd0995649701ec7624f46ead914c3509c70010be2d596606cb99014c398422af479212cef', 35, 1, 'MyApp', '[]', 0, '2024-03-28 15:02:34', '2024-03-28 15:02:34', '2024-09-28 15:02:34'),
('5b4b3d620fa43e7d127df3850af6050b347d2090853988179345df088c61d7e970f2dd7b00bd479b', 5, 1, 'MyApp', '[]', 0, '2024-04-08 03:17:15', '2024-04-08 03:17:15', '2024-10-08 03:17:15'),
('5b55e410331a2c83adab6b3cac9a3b7ed16d5550da9b553a681d07057c400233ed57cb6e046cd6c7', 71, 1, 'MyApp', '[]', 0, '2024-07-16 11:14:46', '2024-07-16 11:14:46', '2025-01-16 11:14:46'),
('5b5b6cbe175ddbc79fe1d17f7dae6ee2b7fc88f600ca75134f13dfdff1d7cac05b3d3edfdeca782c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:20:12', '2024-04-28 11:20:12', '2024-10-28 11:20:12'),
('5b88528c20a94cea1305b33e4c124fa9c09bfeaf865f7e77a02c7dfed34dde1a5a94176d9a0632d4', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:38:48', '2024-04-28 15:38:48', '2024-10-28 15:38:48'),
('5b8e850fbfaa97dd0a52de38b4262b61dc69d4ef39fb0b3ae6a4dfe4362b4bbe4631dfaa30fd5211', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:45:18', '2024-04-02 20:45:18', '2024-10-02 20:45:18'),
('5bc30dec516c1f562ff6d3ed3faef56e08e945da176f33a93a44a74fda915931b3592d41a81ceec7', 30, 1, 'MyApp', '[]', 0, '2024-03-19 11:11:24', '2024-03-19 11:11:24', '2024-09-19 11:11:24'),
('5bc59351ad9bc88eb86953d40f184e10302294ca4896efc8f6c06bd22a100c426ce179f7425b4e4e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:06:14', '2024-04-28 16:06:14', '2024-10-28 16:06:14'),
('5bcbb34860601bea758cfea315f095366453544a7f50f056d6b78cac7cd696839249b8d3d1b4e780', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:17:56', '2024-04-16 19:17:56', '2024-10-16 19:17:56'),
('5bffccdd267d09d4c5d7226564a19d702db621003785aa341f448197d141c426e09b4ba69ca0e7c3', 5, 1, 'MyApp', '[]', 0, '2024-03-19 12:35:25', '2024-03-19 12:35:25', '2024-09-19 12:35:25'),
('5c477d542b28d8a06c319beb5d4c65200906c1c6e0b7f6eafc140ed7a6edca54250575f3c1217a0b', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:23:11', '2024-03-24 18:23:11', '2024-09-24 18:23:11'),
('5c783fb73fa2d0366572d7ea97c110808f076ffa234e738b7a87277bd814a14595ef2996c4229444', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:37:45', '2024-04-25 18:37:45', '2024-10-25 18:37:45'),
('5d2a25ecb1b673010849542581e4b65aa6fad5767ab57c3cc69f82ab3953cc87f1feabff87886a2a', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:27:33', '2024-04-21 13:27:33', '2024-10-21 13:27:33'),
('5d416a7491e62fcc3b35b65993b92dcf46089d22f9f69560449df0975facd645e73f4e5c6a7aa0a9', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:23:04', '2024-04-17 13:23:04', '2024-10-17 13:23:04'),
('5d42f353418fc8f5cc960e9a2012cb3a1fc197a430c4b29f54048f55e7e69a21ae94466a8e778dd9', 5, 1, 'MyApp', '[]', 0, '2024-03-20 15:14:38', '2024-03-20 15:14:38', '2024-09-20 15:14:38'),
('5d6eb09d9012670ce4baa019da5079ae53b92f752a8eb78d9dc507ef406868ff98367b1c9e5fe48d', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:17:27', '2024-03-28 14:17:27', '2024-09-28 14:17:27'),
('5d7afb247e253bf959fde2eb36bbd7377fe5774ae6d63af2b35969ce27cd017325f1fbaef0ec9709', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:30:42', '2024-04-25 21:30:42', '2024-10-25 21:30:42'),
('5d8b62e9e8484859e8a3cd8bf20afb365fea5096a7e3750b1d99c4ceeecd1c058baa89e50e98b760', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:26:39', '2024-04-23 14:26:39', '2024-10-23 14:26:39'),
('5dca435da26d507732fd708b24ee6a1497b7028d5c8a102b594f59ff95c59da08357a4fb3a089d9f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:53:53', '2024-04-28 14:53:53', '2024-10-28 14:53:53'),
('5e2b2d39def811d851823e88e72f90cbe67bdcd698fc0775f55a856925dcaa991000cccaf0d678b1', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:49:13', '2024-04-18 20:49:13', '2024-10-18 20:49:13'),
('5e5bde4ad2328c108bcba7be558ecac0fc921595092d551a2dc39c44052b31b5f89aba54ca087c9e', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:32:00', '2024-03-24 17:32:00', '2024-09-24 17:32:00'),
('5e775e86cec14d6a224040604f880e9ad8633d9ca10108f9879a9a297cc58f315e86885d980bb028', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:30:57', '2024-04-23 12:30:57', '2024-10-23 12:30:57'),
('5e795d3b7ee5114d1ff5715488e41660f0b402b928ac5c89b08a7ef33bc4fd44a36d5a193e3f77dc', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:55:59', '2024-04-29 15:55:59', '2024-10-29 15:55:59'),
('5e87022643fd27996f61fe0ddf5a6f67884a32bf8acf4a5bcccea75261431943b75f383d21a7a045', 32, 1, 'MyApp', '[]', 0, '2024-03-21 02:26:15', '2024-03-21 02:26:15', '2024-09-21 02:26:15'),
('5e8c1413825b2eba9723636d1cc7100b633c8106d564d9aee5ded869b9f700db18637c4e21336da9', 5, 1, 'MyApp', '[]', 0, '2024-04-04 18:17:11', '2024-04-04 18:17:11', '2024-10-04 18:17:11'),
('5eb9cd593e88d13dd65236e8abbbb759de52c63c6b57c79d2968447c48a13a63ef3c95cce1e8bb8b', 5, 1, 'MyApp', '[]', 0, '2024-04-14 17:40:20', '2024-04-14 17:40:20', '2024-10-14 17:40:20'),
('5f2178340ec48cf41b1a3df84317a7898ee6a169b6af45b8923197bcc98bb8b8abe2c77b54aeb456', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:38:41', '2024-03-26 03:38:41', '2024-09-26 03:38:41'),
('5f30df39c78390335f90552bf2a4a15cf6a8aa5fd27573e97721baa7e45b54d29bff484154b421ab', 5, 1, 'MyApp', '[]', 0, '2024-02-25 13:47:23', '2024-02-25 13:47:23', '2024-08-25 13:47:23'),
('5f3c724e92f85e3dc76a1399ccf824389edc174716250dfc0c4d5bd5016b0081b048cf20c14c927d', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:11:29', '2024-04-18 19:11:29', '2024-10-18 19:11:29'),
('5f83db990510bedcaf29b8eaecfb9a17c67be3d134ea94570225e5ea5bd6f2cefe733386696994c0', 69, 1, 'MyApp', '[]', 1, '2024-07-16 10:39:30', '2024-07-16 10:39:30', '2025-01-16 10:39:30'),
('5f98991bef97b0f6c9ec55c9144af381e391caa6f255c5bd1682f410bc1c6ab57f04cf6e283a624a', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:58:22', '2024-04-24 16:58:22', '2024-10-24 16:58:22'),
('5faa5bd75a79cac1b345dd84c0f50f118eeadb8aa41ea6441549648048f72fe0870acd3f82209a55', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:05:11', '2024-07-13 15:05:11', '2025-01-13 15:05:11'),
('6006d6a3cc7b677b2856cff81de41b323214658dc834cebea692139ab5f034fb0dab9029978dd221', 5, 1, 'MyApp', '[]', 0, '2024-02-18 13:55:10', '2024-02-18 13:55:10', '2024-08-18 13:55:10'),
('6018aac857901c99e5216dc86c119c3e429248d4d6e0a01d813a0964256d0d593e5315a430ed997a', 62, 1, 'MyApp', '[]', 0, '2024-05-30 13:40:38', '2024-05-30 13:40:38', '2024-11-30 13:40:38'),
('6082783137cd6b6c5da07aff1c2b6582f92a946703fba857f894c271a3c304fb27cb3063c7a2f543', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:25:02', '2024-07-12 15:25:02', '2025-01-12 15:25:02'),
('608cabd7d71b0d04a5d7e9e58b9f67b9dd8c4dbc86229c8f0519e5e80f0e82b55e737f9c3b0f1a7e', 69, 1, 'MyApp', '[]', 0, '2024-07-23 13:08:04', '2024-07-23 13:08:04', '2025-01-23 13:08:04'),
('60a49be37e9d6734d2c24fc3f254d4556db95908793226698b13b412e1003ebae5e360de96c71a59', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:30:03', '2024-04-29 13:30:03', '2024-10-29 13:30:03'),
('60b3d6c60a2cfa5d3a119b6380748d7b26880a0ab3c19475f52c2e4772680c3d1603a714ca3eee2f', 58, 1, 'MyApp', '[]', 0, '2024-05-15 09:30:39', '2024-05-15 09:30:39', '2024-11-15 09:30:39'),
('60d3e538252acd9b0c9ca642a07dc7dc909a0dd37045b02de669d9c6c77bb3c3d96939842fffb3db', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:22:39', '2024-04-28 15:22:39', '2024-10-28 15:22:39'),
('618c6d53316be388f37777eccdf4092c6b039c47bbab5518b8913365b690720226dfb9c641c55216', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:59:13', '2024-04-18 17:59:13', '2024-10-18 17:59:13'),
('622ceb17ae1cc97605c1bb1ff4d82f9f892668922017b9a643b6a597f327ebcf8c426323db362d98', 31, 1, 'MyApp', '[]', 0, '2024-03-20 03:52:08', '2024-03-20 03:52:08', '2024-09-20 03:52:08'),
('6231cc8880a0b37adf41e04d873fcc18a0b9c596beb80e410c08e46b10fcddc68acf8293fc358f5f', 62, 1, 'MyApp', '[]', 0, '2024-07-07 20:00:11', '2024-07-07 20:00:11', '2025-01-07 20:00:11'),
('626b729a5652e55e6e8df967589cf7edb8b3346f827898192ab742fc1106f2c51e055ce18d6db651', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:09:05', '2024-04-23 14:09:05', '2024-10-23 14:09:05'),
('6287c7a84186c8321378988bea1a42dee8e87f5446b290007c9cfdc14a0ff0ce1d9503d87d55f8a0', 58, 1, 'MyApp', '[]', 0, '2024-05-08 10:17:46', '2024-05-08 10:17:46', '2024-11-08 10:17:46'),
('62b35c9ba04890d5de880212e41f39911ace9991214369a0abedbfcd04b99186c87322fee00b5e39', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:30:32', '2024-04-21 13:30:32', '2024-10-21 13:30:32'),
('62e6686899d47787b7ee2b03081b4d3a9cf3db84c2d0d3720ccb880fa2f67291ce68be900227cde9', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:29:17', '2024-04-17 17:29:17', '2024-10-17 17:29:17'),
('63650b1461d90f4b9d74e308fca63cbd2206da6e4ba652eca2b54dbfa027d8ebb870820c90e0b7cb', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:35:21', '2024-04-14 14:35:21', '2024-10-14 14:35:21'),
('63691c861be710e1c2b1206663c03d4d665704b0f8054ca9a96f68aefdd7c02678faec5b3d70263d', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:18:19', '2024-04-28 13:18:19', '2024-10-28 13:18:19'),
('63a7ec8e7684cbef006e6624918a35648b68c5eaeb3bdc0cf4e9e141a1c1a4230987e5126f44e2c6', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:45:58', '2024-07-12 14:45:58', '2025-01-12 14:45:58'),
('63b7734f8d93400dcfc058d4aefefeace01210594d103f778bca0d87a5bfd8790b9c5f16eb9c040a', 35, 1, 'MyApp', '[]', 0, '2024-03-28 16:20:08', '2024-03-28 16:20:08', '2024-09-28 16:20:08'),
('63d29252bbfd61eb65ed53a7386fd4bc56a92a48e26ec9c3adc9cfbb6d279b5d46af8e630aeaa439', 33, 1, 'MyApp', '[]', 0, '2024-03-24 13:37:36', '2024-03-24 13:37:36', '2024-09-24 13:37:36'),
('640a864e258a0c57fbb1fc8468d6ca2b6ea57a352f59049aa2fa673886e8baa0dcf9b1668a65cde1', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:42:17', '2024-04-23 14:42:17', '2024-10-23 14:42:17'),
('640dfcd4307f8f8d921b5686ca7b13273f6e15d85a4c82eff0185f7762db3afbd1d9e7c8a2cac750', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:25:12', '2024-04-08 05:25:12', '2024-10-08 05:25:12'),
('641a0dc219663462dc35581ddea39d8241fd72f8d4b53e9449e89233183e6b39baa41dff9bd193c0', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:09:09', '2024-03-26 03:09:09', '2024-09-26 03:09:09'),
('642a1069beb48db74ed7d56f45e29cd0ef87ba8dd0aba8bb565a8999f39b1bcc95afb5903f5bc79e', 69, 1, 'MyApp', '[]', 1, '2024-07-19 12:30:58', '2024-07-19 12:30:58', '2025-01-19 12:30:58'),
('64386677efd1f460b5771a08049711c0506806b6e2d0117f2e25cbffc30c81c2c58fe1a67e9b1a1d', 5, 1, 'MyApp', '[]', 0, '2024-04-14 17:59:19', '2024-04-14 17:59:19', '2024-10-14 17:59:19'),
('6479019d67c373ff151c9b2baacbd782bb32309d562763a81c05eba3602156eee90d0cfeccb9eac0', 5, 1, 'MyApp', '[]', 0, '2024-05-01 09:59:07', '2024-05-01 09:59:07', '2024-11-01 09:59:07'),
('6479dd2a79ad6b16db588004535783a55df2fe4c5a35c56e35f199b98e9687f71cbaabd79c10c496', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:27:14', '2024-03-26 16:27:14', '2024-09-26 16:27:14'),
('64890fdce292bf95cc9f92167ec0c7ac3c55372f0c8b4c78c13298d120aa6df040dbea6b5129f2d6', 2, 1, 'MyApp', '[]', 0, '2024-05-28 10:57:29', '2024-05-28 10:57:29', '2024-11-28 10:57:29'),
('64bc812775e1fca5b64805fa121ab19994b8d3121a88ed96a85bb4718bb7c31a148d6f3509dfe77a', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:12:25', '2024-04-22 19:12:25', '2024-10-22 19:12:25'),
('64c228181c28c757757ad0d19d29a04cbce7df3b37dcb65a1bea7e89ea9bf86f48b0dc337b212400', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:56:31', '2024-04-22 18:56:31', '2024-10-22 18:56:31'),
('64e4c08c8a02db3691edd7c524f76f4f28572cb586be4f072c50ff3cd277c69a086426c91fe8e901', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:05:28', '2024-04-15 15:05:28', '2024-10-15 15:05:28'),
('651241f7f04d8914476199b6a70c6075bcddbf8b68c1dd44c7e626053d68fbe5393a39a5c1b30f52', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:10:52', '2024-03-28 14:10:52', '2024-09-28 14:10:52'),
('65660cb4c9550bbadfd7d0111cf28da1b430ed38f8262e96b8bc900dbaadbc0f8c5c786a3b12b2d1', 58, 1, 'MyApp', '[]', 0, '2024-05-22 09:49:41', '2024-05-22 09:49:41', '2024-11-22 09:49:41'),
('6577385070b3b373b2e52c31997b01eabe4f2bdbf75702681c9e7ca51e040581370d606407a469b2', 5, 1, 'MyApp', '[]', 0, '2024-04-02 15:26:22', '2024-04-02 15:26:22', '2024-10-02 15:26:22'),
('658dfbfda521fc7fa326b97aea431026ae8affc3e7a4f603de60c933b05738be91c976c5e414f357', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:22:56', '2024-04-28 13:22:56', '2024-10-28 13:22:56'),
('65b0a0e02c945fc266482a8d00ce552fd08ff61cc9e27075a253000f401fc5b369bc200809df5950', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:23:40', '2024-07-12 15:23:40', '2025-01-12 15:23:40'),
('65b92f9497d8f8bd1999f9bc16e7912bdef9efdac72eec96367ba89a8c8979de61f227b2142d8c4e', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:12:11', '2024-03-05 11:12:11', '2024-09-05 11:12:11'),
('65c2b5e96a4acecdf6a95eecbbe333622632d9a8740045ac1dd1b2c625e12402de5a61571cb25c5c', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:35:00', '2024-04-16 18:35:00', '2024-10-16 18:35:00'),
('65c639abeb4137095160d66decc99b3f5f22142016e18c70aea9d65b49f187f26c0d58ee70ba1d31', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:46:03', '2024-04-29 13:46:03', '2024-10-29 13:46:03'),
('65d806c2cafe6bd350d4d03944e78799e10d195154cef18dcff9250d6babeda79ed62f7b63d34bed', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:38:54', '2024-07-16 10:38:54', '2025-01-16 10:38:54'),
('65e3769c8e767c0fc435f9474f7fa8310e3e63cd64e188d90e37ee221ab4a91ad7db50ddcb56b6e3', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:11:29', '2024-04-18 19:11:29', '2024-10-18 19:11:29'),
('65e8534167dd80abeccd08358450ab8d39972979a4b81e61724eba0c98e44656cfaaed3662d4bac9', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:38:34', '2024-07-16 10:38:34', '2025-01-16 10:38:34'),
('65f6eda2cb92f508ff7c5d4463a4f25c638dde523a5ea488076b1a971deeeed368fa578899842715', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:15:07', '2024-04-15 16:15:07', '2024-10-15 16:15:07'),
('6601819d44a8d2e6b01a5fa3acd36008f5ec742d027bb8ea95a3344fdaeed0a02edc8228dd8f5b60', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:27:34', '2024-03-19 22:27:34', '2024-09-19 22:27:34'),
('6645633441ba83ac53618d875d99e6a345f902d86f73d4c8152a93b4b8f97b8a4e96e32e54b36f61', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:15:30', '2024-06-05 13:15:30', '2024-12-05 13:15:30'),
('664caf0965bb8ea95d586eae366643412419a1064bc580afb32657bebab1ba6bc17cc5cb462203f7', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:30:42', '2024-04-25 21:30:42', '2024-10-25 21:30:42'),
('66e921cf9b4e88ccb176e888d59cf02e4e608a94108f6d01600ae64d63e9c4e21300256e2a49b108', 58, 1, 'MyApp', '[]', 0, '2024-06-27 13:36:14', '2024-06-27 13:36:14', '2024-12-27 13:36:14'),
('673c9bad68ccea886c18852abe2a07bb7e45f46df94f1b16ae2cb40a3df63dcf99dfc56c811e0e7b', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:38:34', '2024-04-28 14:38:34', '2024-10-28 14:38:34'),
('6744840ca478c827f84836b441414c64aebff03579f4e7b72aaa94bf1876933996616f7703600016', 58, 1, 'MyApp', '[]', 0, '2024-06-04 12:34:47', '2024-06-04 12:34:47', '2024-12-04 12:34:47'),
('6746525675e256dd620f6fca0e059c5cbbcda5d03f35ff7bcbe7cd8b5f2fa045edc58c662c555145', 66, 1, 'MyApp', '[]', 0, '2024-07-13 19:43:36', '2024-07-13 19:43:36', '2025-01-13 19:43:36'),
('676a0c0c27af70ecb4555aba546dc9b03a939fb45893f6d7a8517d07d4a18212c96d93be738ce25e', 5, 1, 'MyApp', '[]', 0, '2024-03-05 12:08:08', '2024-03-05 12:08:08', '2024-09-05 12:08:08'),
('677b65810da85e229f23834a20f1c463e367f130c4605c1938bca0b9875c9fed87c3993f367cf0fa', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:46:08', '2024-03-20 17:46:08', '2024-09-20 17:46:08'),
('678ff383a3f8b3c0e0a7c5845d2b52a7bc950f6c0c84362a6e0f0a97c7168354494a7327313b2efd', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:54:00', '2024-07-12 14:54:00', '2025-01-12 14:54:00'),
('67bf36fde21c0083625aff93edc4b54ea4eeaafa1fd317717fc6604672733ac8c3c14f0b9e083d39', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:36:42', '2024-03-24 18:36:42', '2024-09-24 18:36:42'),
('67d9a90cae496b4cd4a0824d6f90f201ca9fed3106228d81210e5eb17632d47ffad30740f433aecd', 5, 1, 'MyApp', '[]', 0, '2024-03-24 15:36:24', '2024-03-24 15:36:24', '2024-09-24 15:36:24'),
('680e6cd77296353921bac2fe0a5c1c0378aa169a095306c7f32f20c6679c163ab1fd62427e07f53c', 5, 1, 'MyApp', '[]', 0, '2024-03-17 13:08:00', '2024-03-17 13:08:00', '2024-09-17 13:08:00'),
('6849dfb3ea76d251a86e4b878ed3b57ba521581d19ee12c709cdefc5d5b74ff6ce83e429988d238d', 69, 1, 'MyApp', '[]', 0, '2024-07-23 10:18:23', '2024-07-23 10:18:23', '2025-01-23 10:18:23'),
('688fe43436a78c5b00b88e145c396828a6a10aa6e078d03179a4378fa866418395b1cc0d0c72a3b1', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:18:05', '2024-04-08 05:18:05', '2024-10-08 05:18:05'),
('68a6334e6480fd6468eebead029b90f6ccd30a96bf4b868c39d5099c07d1f5a65e399f8998d021a4', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:48:15', '2024-07-12 14:48:15', '2025-01-12 14:48:15'),
('68ca1b6111d9c2f09b7fa2f025b3cdc31f2350b8bd9d34cfdefe24ea1dd5e1c6ba3c37edd1b434dd', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:34:40', '2024-04-02 20:34:40', '2024-10-02 20:34:40'),
('6916ba679735aa8b2027f45ab01014e1ebd9c134b2df0d15ce4ab9a05b71ac4eef1caae4a68e6e73', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:44:49', '2024-04-15 16:44:49', '2024-10-15 16:44:49'),
('6919efcd3a56d088576d78b114e429c2e4b1be5200750152c1890f7f0dbc5e73ab8edbbbb82d1491', 5, 1, 'MyApp', '[]', 0, '2024-04-14 12:37:49', '2024-04-14 12:37:49', '2024-10-14 12:37:49'),
('695033dac4e27c0f60430e106475b01110c29bcfd009ccad8d2ed4d3086042575f9694496787821b', 45, 1, 'MyApp', '[]', 0, '2024-04-07 12:18:26', '2024-04-07 12:18:26', '2024-10-07 12:18:26'),
('696a742385f61771f3f4894c544c15a3e8596bdebf4ac6c01043887ed8c168eeaea186a1959b3f3e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:36:40', '2024-04-23 12:36:40', '2024-10-23 12:36:40'),
('69acfc2e78c4e9c50f88ab60ea9a8011079ec00bffdb454a4923942a8440dae385f3d2588fca7c2b', 69, 1, 'MyApp', '[]', 0, '2024-07-23 15:03:58', '2024-07-23 15:03:58', '2025-01-23 15:03:58'),
('69b59d7422a8339c67cca4e7a2518046fc5314b0ac96d182a3dfeba9256e2516d8a17fe979b8a147', 5, 1, 'MyApp', '[]', 0, '2024-04-03 15:29:25', '2024-04-03 15:29:25', '2024-10-03 15:29:25'),
('69e7bde85c45a322faf66b6f2965167a246b7b27b66e5e7ddb9142f9d7c6cc7a4c1b4f079051cb43', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:27:35', '2024-03-19 22:27:35', '2024-09-19 22:27:35'),
('6a013ce26162375724bf7cecf3efc518d011b2c679666cc284f872b0561a5bec8fdf98df0b591f9e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:36:20', '2024-04-23 13:36:20', '2024-10-23 13:36:20'),
('6a097ad5b776b52e7e4b313a2f0c30d05f081adbc9a2a5644104eac3b648c1e38f32a82cc5681247', 5, 1, 'MyApp', '[]', 0, '2024-04-23 19:02:07', '2024-04-23 19:02:07', '2024-10-23 19:02:07'),
('6a2d56840a1bd4ee223fd76211a847395517171cd9512007e7fedd4292f611c42b52b68f3db3a1d0', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:51:57', '2024-04-16 18:51:57', '2024-10-16 18:51:57'),
('6a48becbcc503c5d4c9c9be95c086f0029b9b1da792605f23ad07b6898fbac82f9630003fc263c85', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:56:30', '2024-04-22 18:56:30', '2024-10-22 18:56:30'),
('6a4e4340a96e97c3888448f294b3f5a8b6cf6f22fc81641e61b446f27f7fc5c624d32642ba1fd1ea', 40, 1, 'MyApp', '[]', 0, '2024-04-02 15:25:50', '2024-04-02 15:25:50', '2024-10-02 15:25:50'),
('6a6a6ae611a1329d8721b53816677b804cb97ba638eb5e5c67b0def77854287b847ab7d2d424fd2f', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:04:13', '2024-03-31 14:04:13', '2024-10-01 14:04:13'),
('6a82cfc207c35d60efdacef99c58b209fb488c70d735f743f8b78ed04c7ca16fcb8f9a09208b121e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:22:36', '2024-04-28 15:22:36', '2024-10-28 15:22:36'),
('6a9600e0b06260ff79c7f55807ef5eddb85e4c9e9e0a7022295b23d38da8968aa460ae0729aa6627', 58, 1, 'MyApp', '[]', 0, '2024-05-13 12:00:50', '2024-05-13 12:00:50', '2024-11-13 12:00:50'),
('6a960efaceecf34a894afab9047df037a772a129b2a284decc45d8f35ae2b9c721ce74760ea61ffe', 10, 1, 'MyApp', '[]', 0, '2025-08-17 14:45:57', '2025-08-17 14:45:57', '2026-02-17 17:45:57'),
('6aad66ccffa01917f3c6ed23f7c30462730e916934e5206aa570f17d5178deb972ffeaa85c22aff3', 5, 1, 'MyApp', '[]', 0, '2024-04-15 12:12:59', '2024-04-15 12:12:59', '2024-10-15 12:12:59'),
('6b06124e9cd43d2ce64f8ea0bc516d1377fc0fee1a2498e15dc7efcce5e27d7fffa8bc6462852f97', 5, 1, 'MyApp', '[]', 0, '2024-03-19 17:45:43', '2024-03-19 17:45:43', '2024-09-19 17:45:43'),
('6b382414945ec771d3f7d83be575daf2c5cdbcadfc2ab0b20e5c541f3df500dec90ec92a07c9c85c', 71, 1, 'MyApp', '[]', 1, '2024-08-01 15:44:14', '2024-08-01 15:44:14', '2025-02-01 15:44:14'),
('6b660efacef7fe74d150ffeb6b5a3b4b5ff589bc9ae2ca10a508bfc81ff03c215c2444854455acca', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:18:02', '2024-04-21 13:18:02', '2024-10-21 13:18:02'),
('6bb6c2092e27ad0c998f64ca866743c1c7acf033aaef8ec9d4d9f025e2b6c28bb8817a4aa6f084a2', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:09:18', '2024-04-23 16:09:18', '2024-10-23 16:09:18'),
('6bda0a1c4d1625fde1425d4ac0d6db2a072b81f61a9f4a52df535b0ac24be00e0f6df8defc75e32e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:56:53', '2024-04-28 14:56:53', '2024-10-28 14:56:53'),
('6c00cf6693f2198f9b489ba6378b3c2450858c93abc96e33a06f308901a77b8023e88db87e8231f7', 5, 1, 'MyApp', '[]', 0, '2024-04-16 16:55:54', '2024-04-16 16:55:54', '2024-10-16 16:55:54'),
('6c8a3b07bdf02f960ccba0f9833424103a1305e227b6835741e754f7b9d7831f334e4f78e924775c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:25:37', '2024-04-28 13:25:37', '2024-10-28 13:25:37'),
('6c909547c6815cc5848b4186d8168a6ea4fe16a83b16d9dd3f1dd12e61107c2a01914a04cc2b1a23', 5, 1, 'MyApp', '[]', 0, '2024-04-03 20:45:59', '2024-04-03 20:45:59', '2024-10-03 20:45:59'),
('6ca3ea1e1ec918a944cf454ac14dfde7daf35571158ffbdc6e5e273a980045d9577acc0631440a4c', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:15:23', '2024-04-17 13:15:23', '2024-10-17 13:15:23'),
('6cc4d8612cc0cc43ce4c8cd6db9967f7830b7b2a70704bd00ec6d2257584ef3debdeb1ad14940265', 5, 1, 'MyApp', '[]', 0, '2024-04-28 18:23:17', '2024-04-28 18:23:17', '2024-10-28 18:23:17'),
('6d0735ffb5fdc0514150ed4f8a17fff2939c7c914ab0889e0b588aff835dd37485305b3cd85f1799', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:22:26', '2024-03-26 16:22:26', '2024-09-26 16:22:26'),
('6d162e092b4ce664153cc8ee2f54a4e93ea9182a8ad3969845d232af63c0730ef955ede09cc6f604', 35, 1, 'MyApp', '[]', 0, '2024-03-25 15:29:07', '2024-03-25 15:29:07', '2024-09-25 15:29:07'),
('6d2a305f3b7fe2765da6f6c6c0c2a53afdb6f328c9f16b835971a7c485b7d76011655941519e1b95', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:36:21', '2024-04-23 13:36:21', '2024-10-23 13:36:21'),
('6d3da80ac0fe686ce4b9128cde8280b01e1ae769cde9247fc3816125f02be8accc676ffaf21dde7f', 31, 1, 'MyApp', '[]', 0, '2024-03-20 04:01:08', '2024-03-20 04:01:08', '2024-09-20 04:01:08'),
('6d6edb3aa8f53f2f85e5376f601f56d76913d04c1f404efeeb0f31f2f93c3fc9b08e243e715acf34', 35, 1, 'MyApp', '[]', 0, '2024-03-28 17:31:19', '2024-03-28 17:31:19', '2024-09-28 17:31:19'),
('6db46bceb902ff04498c7b8afbd8918e2e54d8233188323becbd5c1c0d2e5155a3e2729e05c83fd0', 43, 1, 'MyApp', '[]', 0, '2024-04-03 21:24:31', '2024-04-03 21:24:31', '2024-10-03 21:24:31'),
('6dbb12c3aa3e4997e2859056802f418643c2969e177312d3461ec1bf3cf539011e4b8284339a6b78', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:18:43', '2024-04-22 19:18:43', '2024-10-22 19:18:43'),
('6dbbdb41b9244920468c991a5e59e46ff4774f7de65bf3127848501052b350ad8cd9ce5060f2240c', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:03:31', '2024-04-17 13:03:31', '2024-10-17 13:03:31'),
('6de9dfc1d071bf8d3addbb1149f14759d318a23989680ec7d445a58f47c41b62dd7b5922c37b17d2', 5, 1, 'MyApp', '[]', 0, '2024-03-05 17:21:10', '2024-03-05 17:21:10', '2024-09-05 17:21:10'),
('6e31034b14fe36f128677c030e5a2ce027cb8ab515872e982454326f6ad1d5a7b4d227b98648cd34', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:37:25', '2024-04-23 14:37:25', '2024-10-23 14:37:25'),
('6e426d5c5bf3f5952f9bb1472cebc1c4de19118413fcfb9988254187424d763f5d09f1205bbbd445', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:51:34', '2024-04-23 18:51:34', '2024-10-23 18:51:34'),
('6e67fc656c04a51cb7ee7f4cdfd86099ef851142a10be67e4ffff994b5d61645b3a0d6afc53f7fb9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:17:42', '2024-04-22 19:17:42', '2024-10-22 19:17:42'),
('6e6bf8cda32c842964754580adf7438cf7b3e2cc72d6d9da0a5d6111ea496d2e2a209f306fc76be0', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:25:25', '2024-05-02 10:25:25', '2024-11-02 10:25:25'),
('6eeb41873fe9f8b503d475a0deb560e713aeea9d0ed45fea0f6ea87b07deeec01fc1359ab6aa54a1', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:12:53', '2024-04-14 18:12:53', '2024-10-14 18:12:53'),
('6efdb009fa5fdd3f82c7b3e9e4c8af61de481e3f2952399fa0a49b8cfe44d5272739bc4e9ab9190b', 58, 1, 'MyApp', '[]', 0, '2024-05-28 14:44:00', '2024-05-28 14:44:00', '2024-11-28 14:44:00'),
('6efff1ac5ab08ade60934711fb8f23e5a88b3d90932e11c0c1e8a3bffd3f0abe907fd2e62f5cf9de', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:30:32', '2024-04-21 13:30:32', '2024-10-21 13:30:32'),
('6f0160c915e910bacfc5b044606b99462b80427856997b47bed73c07dfe5213419681fcf2de922bd', 5, 1, 'MyApp', '[]', 0, '2024-05-01 10:01:55', '2024-05-01 10:01:55', '2024-11-01 10:01:55'),
('6f08c43b5c70784396ef9edc27cd9664ef8abf3a34060cc51e7058e4f72d88bcca1e401daed195a5', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:35:45', '2024-04-25 21:35:45', '2024-10-25 21:35:45'),
('6f0e1b89160ec3751c0416fee1c1510494d63014aece75c7479ba3485cf237088f57ccee21008d1d', 58, 1, 'MyApp', '[]', 0, '2024-07-07 15:38:56', '2024-07-07 15:38:56', '2025-01-07 15:38:56'),
('6f17634c45dad6fb1ad2503b6f08b230368ccdc559d6e7252be8a3c0f4a9fcf5b95b626efe596655', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:48:58', '2024-04-23 12:48:58', '2024-10-23 12:48:58'),
('6f42d70b2c30a9777fb93d38a719fe081be58c8890440b9acc71d11ffc6609d77d3f2ade76fdb16b', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:05:21', '2024-04-14 18:05:21', '2024-10-14 18:05:21'),
('6f46a268bc9acafb5361804bf0f441c5a242096a553b73aaa8c2ef6d61396985145f19f56b5a8baf', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:31:57', '2024-06-05 10:31:57', '2024-12-05 10:31:57'),
('6f4f6021fe5454a657659f7564051bd95b6d8d82b0c57b7593d94afe3f0a3832114b0675bf7ca46f', 5, 1, 'MyApp', '[]', 0, '2024-05-02 11:00:05', '2024-05-02 11:00:05', '2024-11-02 11:00:05'),
('6f7bafbe00b274a17a21bfe11f7f16337d6e824ab2babad459b7c974d3efab50de4838eda80d62fb', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:16:16', '2024-03-19 18:16:16', '2024-09-19 18:16:16'),
('6f8e2e5a64bf1dd612c624954fa05c29e826f30af6e9226a3fed06befd61dd3bec0c893f6a5ff1d6', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:13:16', '2024-04-14 13:13:16', '2024-10-14 13:13:16'),
('6fcbd1cf4572e5643f0c4d9a333c5d4a8b5ba3d776964155f49799bdaeaf4fff740bb641ffa066c0', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:39:26', '2024-04-24 15:39:26', '2024-10-24 15:39:26'),
('6fd6948fd90775fe3fafe6f69aa78b17903f202705532b96d4e69d6459fb221f0e9040a66c1f04f9', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:26:45', '2024-04-18 21:26:45', '2024-10-18 21:26:45'),
('704473ce1e4ab4710d847da1519f86befa840f89b835d73ce407dd734e90e7abbf7621308aa52413', 58, 1, 'MyApp', '[]', 0, '2024-06-04 13:56:25', '2024-06-04 13:56:25', '2024-12-04 13:56:25'),
('705f5271903e80ca6f67770b7830e42930e02256358a6f31c98b808fd973f1d5177a4c9ea5074522', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:15:01', '2025-08-14 13:15:01', '2026-02-14 16:15:01'),
('7062af54882858722c32685c8d0ea4bc0b006d237561c0894593972032ab1e36babff3f6fbe53fa5', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:09:56', '2024-04-14 18:09:56', '2024-10-14 18:09:56'),
('7072e15894e15c880a050b0faa575f438f8f48733c71f7fa98fe1a9df14aa3c877c85b25a36c7209', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:12:17', '2024-04-29 16:12:17', '2024-10-29 16:12:17'),
('707884077a7fb8b41ea5e8a362df44b2647e7d8f03d79fb8201e6fc6b49d2a20bc4f907f07918568', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:51:33', '2024-04-29 15:51:33', '2024-10-29 15:51:33'),
('707e4cbe10960247cd9ff9648ced2cc02d690484be28676c4ff94d21d1248497d4243b6d876b3b3a', 43, 1, 'MyApp', '[]', 0, '2024-04-03 21:26:47', '2024-04-03 21:26:47', '2024-10-03 21:26:47'),
('70cec868791b9f9c0c9e94ec0100818577ce77726a2215a3ad3e6a11579a843ae9dca32e78d34694', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:02:02', '2024-04-22 18:02:02', '2024-10-22 18:02:02'),
('70db9c93ec4f8efee1aeeeee9bb9450fb4db9d8ef2ae4562be150157f53757d4f17d17b6d3ad99a9', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:25:14', '2024-03-19 22:25:14', '2024-09-19 22:25:14'),
('7116f7a0c71b8c20b08f5e8048d14ba1f117c06aee1e53520473bba349ffdc480ec89cacd431bcd7', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:20:38', '2024-04-22 19:20:38', '2024-10-22 19:20:38'),
('714ca3d00c4ffdbf91320144793c7757523aa2fb1caef579cea7c99530cbe8b6ae6263d2641ff04a', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:30:52', '2024-06-05 10:30:52', '2024-12-05 10:30:52'),
('718c5fc960797dc297ec3c49c9cf9ff1ba5b28fab3baca1d18b59196c6fe1e1bc962ce0e3ecc48fe', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:28:36', '2024-07-13 13:28:36', '2025-01-13 13:28:36'),
('71910a798f5e95b634465b04ca349f09ca7404d0d24786eb101622105ae87e13e3553d39df6b94b5', 58, 1, 'MyApp', '[]', 0, '2024-06-05 11:24:05', '2024-06-05 11:24:05', '2024-12-05 11:24:05'),
('719216a16d43d2f02fcb27f2f34f777c424d9cd258105cebccc9d4e8df0fb0ee27b380be25ada389', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:48:34', '2024-04-28 16:48:34', '2024-10-28 16:48:34'),
('71a61db1d962c455ec7003b2404997fd766ca5225414bced58da1f7461cc590e09f216d7d196cd35', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:11:06', '2024-03-26 04:11:06', '2024-09-26 04:11:06'),
('71abdf34979cb02d7660f783710379143fea29c0a453871a0fabcae6825b986b878a93fab41adb29', 16, 1, 'MyApp', '[]', 0, '2024-02-20 18:48:49', '2024-02-20 18:48:49', '2024-08-20 18:48:49'),
('71aee0bcee02ae3a1ffb4d114b42cb2284eb43c97bb0898910ba5a09443e5f6d7466c08ec50b9dda', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:42:04', '2024-04-28 15:42:04', '2024-10-28 15:42:04'),
('71b3964bba81f6b7612d220e600a28f3c4094dd149c5a72a31b48cd13466badfb00958219875bfb0', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:29:12', '2024-04-14 16:29:12', '2024-10-14 16:29:12'),
('71dba2574f2dfeb77c60ec69c783705077e895556a4123f8c80f7a152382a19b778bb4c829ab981f', 5, 1, 'MyApp', '[]', 0, '2024-04-04 20:23:44', '2024-04-04 20:23:44', '2024-10-04 20:23:44'),
('720e1cc2dfa51a2f2793798648a435ebd35c8db0ac1e59150c29f94aae4b52409218a4bce1924e89', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:57:48', '2024-03-26 03:57:48', '2024-09-26 03:57:48'),
('721d4cd45b811d521368e5765577f76ba9be4ddb6a54fc564f5b218475bfdaa420181a251e5b81e1', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:20:57', '2024-04-29 13:20:57', '2024-10-29 13:20:57'),
('7227d83146f83c0236679179cd2b67c7904f13f6214da7941624c8e0cb03bdcf05c53941e66cb0a5', 5, 1, 'MyApp', '[]', 0, '2024-04-22 13:37:00', '2024-04-22 13:37:00', '2024-10-22 13:37:00'),
('72887ee4147bca4a1729bd97c3fc44d232c0a993497e836e9cf738c742bff19e053be21716226170', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:09:03', '2024-04-16 18:09:03', '2024-10-16 18:09:03'),
('72ca4a7955648834866b02a1307ffb21ca9d50566627a49fb9ae66e72c10e2c7a0abda78d69ad200', 5, 1, 'MyApp', '[]', 0, '2024-03-21 00:37:02', '2024-03-21 00:37:02', '2024-09-21 00:37:02'),
('731a895fea57ff56a0e5878d8ceefab9e00229467719049b9d61ddd21465abb4e898c797ae0548cf', 15, 1, 'MyApp', '[]', 0, '2024-02-20 18:30:54', '2024-02-20 18:30:54', '2024-08-20 18:30:54'),
('73657bff84c50163da7d539f954ab0856f6df56712a5beab30ad0ec23920d78ac452fb3eee9b997f', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:49:29', '2024-04-22 18:49:29', '2024-10-22 18:49:29'),
('73750231313a7a3f99e01b98e3f8820b201674d2f86b965bee2d1701fbb8675e9862d6bb478f5225', 5, 1, 'MyApp', '[]', 0, '2024-02-28 17:51:59', '2024-02-28 17:51:59', '2024-08-28 17:51:59'),
('737814605faa687dfef7c0491764bf4fb3860e2ccb0d6e59cfa2ea506fc1b471330e4ccf42c4ff60', 5, 1, 'MyApp', '[]', 0, '2024-03-17 15:09:44', '2024-03-17 15:09:44', '2024-09-17 15:09:44'),
('737be9b7c2f3b0ac5c77cf06e55e98f3b9505ae2469602188d3abb047644c351df78fd386030310d', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:06:09', '2024-04-28 14:06:09', '2024-10-28 14:06:09'),
('73afc4b58fa8087e5451d1bfae1d6363628542e6eb8390493de961769bff4f11e698273eba83c561', 58, 1, 'MyApp', '[]', 0, '2024-05-20 14:25:53', '2024-05-20 14:25:53', '2024-11-20 14:25:53'),
('73c61c4d8a4b254157bc60bf51f0c3ffe3089d6504f9269c30a3e1a2539d7a9fdef9c43873c3ae66', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:02:09', '2024-04-23 18:02:09', '2024-10-23 18:02:09'),
('7416e071a7afa3563096e1bad1a974786ceff01fa8ee3cd3224fc6bb5aed8205d2aa4da548d588f4', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:22:15', '2024-03-27 13:22:15', '2024-09-27 13:22:15'),
('742546dcf0eaff7f01b4c62c006164195aa57182a7ece52b17c359a41cd5b8639ada4e4ffc9b4d23', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:54:03', '2024-04-22 18:54:03', '2024-10-22 18:54:03'),
('7444de52cd7b969b13877f45cd3990480c5b4db3d4be4488f70e0bdbccb3479f718ad6e7e1d1d074', 62, 1, 'MyApp', '[]', 0, '2024-05-30 12:10:53', '2024-05-30 12:10:53', '2024-11-30 12:10:53'),
('747898b6a879a7bad6915738d5055c9157786f6e0321592036eaac43b121865e488061ffd860430f', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:26:58', '2024-04-16 19:26:58', '2024-10-16 19:26:58'),
('747a5b27d3772991c6b5c7acbb3cd88ae667d206d26054b7e469b2fae71b5152ef0217d9b3abe22e', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:29:58', '2024-07-12 14:29:58', '2025-01-12 14:29:58'),
('749241f94f7190ede99deb8801df06537ddbbf61f80a810bbd818362efac32cbfb2553b97edaca01', 5, 1, 'MyApp', '[]', 0, '2024-04-23 15:31:09', '2024-04-23 15:31:09', '2024-10-23 15:31:09'),
('7498df322e9c8b901d4a48f4699d0ff0b051466ee095b40635d84845ebd861fd5d3d79dce70f55ce', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:58:51', '2024-04-02 16:58:51', '2024-10-02 16:58:51'),
('749fdc5bbe9f7b34290402419842da9718fdf14b20b5121da4887a770121ad1195a75c7896fa5a9d', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:14:47', '2024-04-29 16:14:47', '2024-10-29 16:14:47'),
('74eb64ad5bc0eb96b25c71dc6baac1a62bf058b450bd33e4cd31f2253ab3395461dea2939178a2e4', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:35:33', '2024-04-23 14:35:33', '2024-10-23 14:35:33'),
('7515e85cb3593612eabc0d863da6e159782812a28ccaf260fdbd176ccf23ebb3f3f9021f081ed348', 33, 1, 'MyApp', '[]', 0, '2024-03-23 22:24:32', '2024-03-23 22:24:32', '2024-09-23 22:24:32'),
('75550fa9dc9937bee66fe8c18ba05428d7a97a8152e6c5713272ccff5aacb332d58e5ebd20c007c5', 35, 1, 'MyApp', '[]', 0, '2024-03-31 15:37:16', '2024-03-31 15:37:16', '2024-10-01 15:37:16'),
('7566f571b3ea4a4bb6d32e9cdc17ea53dc7b858d705f91753fcc3e87cee45197a4d70f6d17414834', 63, 1, 'MyApp', '[]', 0, '2024-06-03 09:45:09', '2024-06-03 09:45:09', '2024-12-03 09:45:09'),
('75a301bf1e133d8e49b44c8ad6ae2a3e3e6aed92e8a689a62f9ce392fbaabe69cdddf9682e20f36b', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:01:14', '2024-04-18 21:01:14', '2024-10-18 21:01:14'),
('75aff4a98e15e27f2e230d1a1369bef41e8c6b1cd3317e6c2b3e6727bf32510f07ad288e74ca3325', 5, 1, 'MyApp', '[]', 0, '2024-04-30 17:30:41', '2024-04-30 17:30:41', '2024-10-30 17:30:41'),
('761f39b91af974ed6a34ede38c56e83080e3f672b9d20f5c6e4fe00b2a4e27fb66f004a2f6b23148', 33, 1, 'MyApp', '[]', 0, '2024-03-23 16:59:22', '2024-03-23 16:59:22', '2024-09-23 16:59:22'),
('762422b747311f2d60f6ef740038473b8b1ff952e091aa64daf9123e6d04faeb258654a0fbb2e279', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:53:54', '2024-04-28 14:53:54', '2024-10-28 14:53:54'),
('765c71e93c8aed76d9878fc1d9f45e54839d5f97dc4bf81d574d68210073d134d6b028dbd5a71053', 5, 1, 'MyApp', '[]', 0, '2024-04-01 13:02:20', '2024-04-01 13:02:20', '2024-10-01 13:02:20'),
('767bffdbb5cdbc4e00fc1802d01c08116d7e86caa4ad3e65b4fea0d08d889c5e90f36c3487acbb33', 58, 1, 'MyApp', '[]', 0, '2024-07-02 14:38:36', '2024-07-02 14:38:36', '2025-01-02 14:38:36'),
('76a099baad04c56e90d869c297535186eb3f0e7365be1df14c05e98aeb3295e94f14e1de34e8b137', 32, 1, 'MyApp', '[]', 0, '2024-03-23 16:48:28', '2024-03-23 16:48:28', '2024-09-23 16:48:28'),
('7711797b70528a4a4554e13e041c8a6ad2c8957aafb4afdf704b9280885941861d69027573c80a19', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:50:10', '2024-04-22 17:50:10', '2024-10-22 17:50:10'),
('772bf98dfa5b56c9a38b0cb04e03df36028e71aedb4e94afa57cb2790b38a258d04191152eaa7453', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:43:29', '2024-04-17 13:43:29', '2024-10-17 13:43:29'),
('774624de6905589ebe86ba3f4aec0989bdd4a9f7a4cf0fdf8dfb65ed41e078ba293e75ed1a09e899', 58, 1, 'MyApp', '[]', 0, '2024-07-07 15:38:56', '2024-07-07 15:38:56', '2025-01-07 15:38:56');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('77591dbecaa35c1d532c167dce8e4e982274449190152cc4dfd65707776728be8cb2b2b908020a1f', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:56:52', '2024-04-22 17:56:52', '2024-10-22 17:56:52'),
('7762ef51beb6d029d7c03d9fc5583cca3a5a4aa84dacf8efd162c846f54fca977eb7f10189493e07', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:29:31', '2024-03-27 12:29:31', '2024-09-27 12:29:31'),
('7792e0304c3fdffac4d749f702ed6e7c39cb50c64a47ed3fb559e2ed2f44ef7103d7c1cfc5d29d22', 5, 1, 'MyApp', '[]', 0, '2024-04-02 15:26:22', '2024-04-02 15:26:22', '2024-10-02 15:26:22'),
('77ab3b1f4c88b5cd288dcf034d0a95ee5e8763f74c46aa42791d8c338088cc5751e8e4026e192bc5', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:44:53', '2024-04-18 20:44:53', '2024-10-18 20:44:53'),
('77c43af0000ffbf957db6fda41ed7947cc3cebfc31b00ea11df3554dd8f479b62e9bedf4c35402e0', 62, 1, 'MyApp', '[]', 0, '2024-07-07 17:09:57', '2024-07-07 17:09:57', '2025-01-07 17:09:57'),
('77f1f486b1cd37702be1f040086e69b042fc6fd43ffabe913bff703a4ba2effecf4ae778edf3ea9c', 52, 1, 'MyApp', '[]', 0, '2024-04-30 12:10:35', '2024-04-30 12:10:35', '2024-10-30 12:10:35'),
('77f2cb1c052b9652698f7325d618303e8f63d0914dd1f0ec7532160f5688b379beb4a1d057e0620f', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:38:18', '2024-07-13 15:38:18', '2025-01-13 15:38:18'),
('782d35ef4c441a44229c23419e7c535260042f9be0fddd83adbca4734b18775c2ffbfe8bff42f190', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:45:46', '2024-04-29 11:45:46', '2024-10-29 11:45:46'),
('783e1b641bc07182b747edc456971afa225c3a3453de438bfbcec61d2254e8a1ca7116ba05b6e502', 5, 1, 'MyApp', '[]', 0, '2024-04-15 14:21:14', '2024-04-15 14:21:14', '2024-10-15 14:21:14'),
('78521600a757fa800721efdfe01fd09dcfeda54799ee908dcffd5a846141f94075bcf00688994c18', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:24:13', '2024-04-23 12:24:13', '2024-10-23 12:24:13'),
('7852b77606828d574a55e75a17498d150df8c1211302357b6a5b9c7f3170e3cfbd0307f3cd919f6b', 5, 1, 'MyApp', '[]', 0, '2024-02-20 18:24:23', '2024-02-20 18:24:23', '2024-08-20 18:24:23'),
('786c60691259a00f4c496ad9c29b876764a4342b8a58d5dc8c6ea657a4d0a578379e07bcf9385da4', 5, 1, 'MyApp', '[]', 0, '2024-03-24 18:42:38', '2024-03-24 18:42:38', '2024-09-24 18:42:38'),
('78720de2256c18793c0075113e511f6c764101ab2480db39305caadba58a237385d3819a27b24c2c', 5, 1, 'MyApp', '[]', 0, '2024-04-29 12:56:07', '2024-04-29 12:56:07', '2024-10-29 12:56:07'),
('789522a1f4a8bd245c465013e533d61fa276c8e35c8ad5c9971103d89c99cd6c9d725531cc16e91a', 63, 1, 'MyApp', '[]', 0, '2024-06-05 11:06:59', '2024-06-05 11:06:59', '2024-12-05 11:06:59'),
('78a364adbeaceb7d7eb467f1efa4fa59d2b67fae01b2c353212172446d77f349562fc6dae9373109', 5, 1, 'MyApp', '[]', 0, '2024-04-25 17:57:24', '2024-04-25 17:57:24', '2024-10-25 17:57:24'),
('78ac363c85af982ad9103b1f319a22f35c3ac11821f6c1da614ebc23709af1f84babf6b1cf79a8b2', 5, 1, 'MyApp', '[]', 0, '2024-04-16 14:56:02', '2024-04-16 14:56:02', '2024-10-16 14:56:02'),
('78fbb087c9dbac9a266e848a08ccb162216cac4b8aae56b59c1a6ac13111e1abeda251c7bf997d77', 5, 1, 'MyApp', '[]', 0, '2024-03-04 15:12:35', '2024-03-04 15:12:35', '2024-09-04 15:12:35'),
('7903ec0e2fb4dc152030390e87c35a1460d30f7389913b00fdeb7c28d8cfc09f0b3294d6f584d7d6', 45, 1, 'MyApp', '[]', 0, '2024-04-10 16:58:08', '2024-04-10 16:58:08', '2024-10-10 16:58:08'),
('79257a30dca563c14f36309cf66947db757709c3a34364d175c2ee758b15b1a139a0d02dad926470', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:41:50', '2024-04-24 14:41:50', '2024-10-24 14:41:50'),
('793d4fd48152bf580bd82b525fd46c85c91a5a75789c748eb45dac37b774c59d931050e496a03fc6', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:23:31', '2024-03-19 18:23:31', '2024-09-19 18:23:31'),
('79409e69fe4f5540c594768fe0a691e479c03dc0269b895d6e5348ed7b40ca49623cba1b0b2b3de4', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:58:27', '2024-04-23 11:58:27', '2024-10-23 11:58:27'),
('795d2e7f758ffc8a541f15586d2a8e3d1c47bd0c569f0acd88fbd64dafacd677c1d0b0fbdd4fc808', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:22:29', '2024-04-21 13:22:29', '2024-10-21 13:22:29'),
('79761f6c8c411f7d4ef550358b2d24cb6d065f8d9579b9414b5f285c33225c57fac2b8616a36f35f', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:22:41', '2024-05-02 10:22:41', '2024-11-02 10:22:41'),
('799a3f00526975659b0c9d96e3edeada3dc75a77a1d0bff89992c34ad406884bd7e498587a423bd5', 5, 1, 'MyApp', '[]', 0, '2024-02-14 18:03:50', '2024-02-14 18:03:50', '2024-08-14 18:03:50'),
('799e470969c5e1f5ffab104baab44b57c2e6a7a1a783a03bee6e2b2f49c33df3317f256c4eb8862b', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:27:09', '2024-04-23 16:27:09', '2024-10-23 16:27:09'),
('79a28fa0a55be23c96529f34f2d05908d7dd5c800bd0e30433d7bf43c1b315d879d4696324f2d000', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:19:47', '2024-04-17 17:19:47', '2024-10-17 17:19:47'),
('79eae3e0711deeb62651aeac2f79acac5ed19b1ff00e976d729e41299c41e797491e020765aeaad6', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:22:55', '2024-04-28 13:22:55', '2024-10-28 13:22:55'),
('79f9e33efa4869d7ca4239380248f02e0270b2011e719c7c244362c9317b84066dde90dd48e40c2d', 58, 1, 'MyApp', '[]', 0, '2024-06-04 13:56:26', '2024-06-04 13:56:26', '2024-12-04 13:56:26'),
('7a175a13ee746c59e3a22180d21943134c8e8c8c6dd410492433d56c9a0f1bf65cec651f2ba14660', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:33:14', '2024-04-15 16:33:14', '2024-10-15 16:33:14'),
('7a7343a9a3ba782300b64f0aed8caaa2ea9c0405a6970bdcab7e1e8fd03490a788fd7c2bd4fd9e56', 5, 1, 'MyApp', '[]', 0, '2024-04-22 12:46:59', '2024-04-22 12:46:59', '2024-10-22 12:46:59'),
('7aa8ed4b2fc7f9659b4108063662bb89182bf52437b24ea0b35fdad498d60b2ca434b097714191c3', 58, 1, 'MyApp', '[]', 0, '2024-05-13 16:53:46', '2024-05-13 16:53:46', '2024-11-13 16:53:46'),
('7abcb91dc96e20183d1f1edde77f0af4cd236a8266c64eb53859c05fef97b3cc0597066e11d52fcf', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:28:21', '2024-04-17 17:28:21', '2024-10-17 17:28:21'),
('7b1456c60b51447f68378baf995bb25d0fd2059f3fa4367eabf154a223d46515eddaebc4b6691f65', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:07:21', '2024-04-28 16:07:21', '2024-10-28 16:07:21'),
('7b3726c8a59119d3ecb85825c0fdbcc8b10657639a29b582e140dc5318c56083731c4e3763909376', 74, 1, 'MyApp', '[]', 0, '2024-07-30 09:38:56', '2024-07-30 09:38:56', '2025-01-30 09:38:56'),
('7b45f89407c86a51d448770cbbad76ad6961cdaa6f13f1a1d7036a58512bc38f8b8c6b6eae3f909c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:21:40', '2024-04-28 16:21:40', '2024-10-28 16:21:40'),
('7b613df78b31f49c7e457dd6672a6a1b6c58f908c8fe2b7fc088eee690b6424e32d43c22732ccd89', 62, 1, 'MyApp', '[]', 0, '2024-07-07 20:00:11', '2024-07-07 20:00:11', '2025-01-07 20:00:11'),
('7b6808806210674470b4b676fadacc6c5fc3e537cb209d5eb42a4f214abb7ca3e9c21ad46d30efbd', 21, 1, 'MyApp', '[]', 0, '2024-02-28 10:59:20', '2024-02-28 10:59:20', '2024-08-28 10:59:20'),
('7b7952d609f88bcf80fd0a4bba9a82b6537e5e229f4cee21633919d8d83630364dc1d8d4a00b52e4', 2, 1, 'MyApp', '[]', 0, '2024-04-29 18:30:20', '2024-04-29 18:30:20', '2024-10-29 18:30:20'),
('7bddb1e21b1f15366b576eeff9980faad37822ac33f9996c53b59fa35c98281ca998b6fb44204de5', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:05:15', '2024-04-17 16:05:15', '2024-10-17 16:05:15'),
('7bf449e2267f6afa8bcaf267418d4b219ddf8e5761f6188b82f7fd44121396b6cab619f5c6ed84d3', 5, 1, 'MyApp', '[]', 0, '2024-03-04 16:22:31', '2024-03-04 16:22:31', '2024-09-04 16:22:31'),
('7bf6a24f28d311c77cebae209d002873df4f09c2e6c5c558022a3e5f74171f0e7c73b9fdadb2b91e', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:15:41', '2024-03-05 11:15:41', '2024-09-05 11:15:41'),
('7c180210b491507df2dddf048e017887b0b3340ef2cae6d6488c62c2012a1bbad3c6ff75d5035010', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:01:20', '2024-04-22 18:01:20', '2024-10-22 18:01:20'),
('7c199ba72eba1be97d64d1b842122d14b8e64fcbcc731812f5d048c866f4e9d1c8ed3e39c55aaaeb', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:31:20', '2024-04-28 15:31:20', '2024-10-28 15:31:20'),
('7c59f22007dc5f75462e9c56220d218cfdf229728f71fafa758aa66de0d3bb06eeb8e715c33e3125', 5, 1, 'MyApp', '[]', 0, '2024-03-18 11:33:53', '2024-03-18 11:33:53', '2024-09-18 11:33:53'),
('7c69b4eb9ee9be085ccf1313c1b9cbae177411a3f58afcb566968ea65593a040f9238d0ebabbe238', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:26:36', '2024-04-30 15:26:36', '2024-10-30 15:26:36'),
('7ca153a2ec63886a6690327b58dcbe9ff641c9f19430d98b4e4ff2627535f22027e869b4f8fd355b', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:28:22', '2024-04-17 17:28:22', '2024-10-17 17:28:22'),
('7cb5730f817b1f57a3fc146ee0f03748b93cc87df269e347763f53a04db74b22e3c2c922bb42f4df', 5, 1, 'MyApp', '[]', 0, '2024-04-27 20:19:08', '2024-04-27 20:19:08', '2024-10-27 20:19:08'),
('7cbc30487ff8180264e9cb67a179013e05f0f040d989368924e080da65550434d8ce8963d6ce5232', 74, 1, 'MyApp', '[]', 0, '2024-07-30 17:57:02', '2024-07-30 17:57:02', '2025-01-30 17:57:02'),
('7ced08e8cb565c68b949816f2c6858c9c84ab4324ec1d63bd19d3847a45006c6fad779670a42cc88', 5, 1, 'MyApp', '[]', 0, '2024-03-20 05:00:10', '2024-03-20 05:00:10', '2024-09-20 05:00:10'),
('7cfda5f6ebe3b7d5b4cc4814e9b8cb852ebbb9fc372fbac0e7c113978b21688928ae930ba2c9ace4', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:51:30', '2024-04-24 14:51:30', '2024-10-24 14:51:30'),
('7d0fdc8835c42650cba23278e2fb8d4c82e152ec7978990878649a8189b4dfb7ac05d19053335928', 62, 1, 'MyApp', '[]', 0, '2024-07-07 19:51:02', '2024-07-07 19:51:02', '2025-01-07 19:51:02'),
('7d3e118e771be6751e9417f84fed03271243e3ae598f0f1dab64fed8dfd8b1d2526b5a75cbe717ca', 62, 1, 'MyApp', '[]', 0, '2024-05-28 20:26:35', '2024-05-28 20:26:35', '2024-11-28 20:26:35'),
('7d3fedf24b41e85b9018ce238bb1943014a8f51822d90c470aa92546c85806dc4fd96ebcaa306b1e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:38:47', '2024-04-28 15:38:47', '2024-10-28 15:38:47'),
('7d4cd2f6a730af61a85c85cb7d6ff6d02baa8795bb197b2d2f28568fa2adec659935fb5d65949fd7', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:42:25', '2024-04-16 19:42:25', '2024-10-16 19:42:25'),
('7d9dbc9ba10adc2a83f3411e6c4d7aa568805924899d3b3594ca23e381a142962a63685d54a1f9f1', 66, 1, 'MyApp', '[]', 0, '2024-07-10 21:24:52', '2024-07-10 21:24:52', '2025-01-10 21:24:52'),
('7da93e9ec5df8b022abcd7ba8665ce848546eda221e8f8a84d579186d191b15239f9264dd748430e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:02:09', '2024-04-23 18:02:09', '2024-10-23 18:02:09'),
('7dc90150961a1dfb03d1c057df3f8bc13cf84f8c3cf1dc77bcbaa256dbc2a7abce2806fc3c1b8b3f', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:56:13', '2024-04-08 18:56:13', '2024-10-08 18:56:13'),
('7def50f79911d0e4b55e14b401e3836f4f2ef7820b498f3122535856891b569db33b026048dbf200', 74, 1, 'MyApp', '[]', 0, '2024-08-01 11:59:44', '2024-08-01 11:59:44', '2025-02-01 11:59:44'),
('7e161e74b8c9c8cddf6bd8aa2b0cc7bdb7032470500be7da92fea598e863fcd788e2f339cb7d0547', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:35:39', '2024-04-08 15:35:39', '2024-10-08 15:35:39'),
('7f589d205d604472bb97d2da897f2d5ef3e0fd8024b0b8970caa3aae2f4f4a86ef1b139ac4f12d0b', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:28:15', '2024-04-28 13:28:15', '2024-10-28 13:28:15'),
('7f65344c66cab140633d80ca1da286bd0a0d5b1a5ca2eb499ac3f1c2247c94cf4fe7b2244d817471', 2, 1, 'MyApp', '[]', 0, '2024-03-07 01:22:00', '2024-03-07 01:22:00', '2024-09-07 01:22:00'),
('7f698fae875d7b1db1ba79a28e65c7a771d520561ecdb912d0a3ceb16b0f6886cfba018fc0581e47', 35, 1, 'MyApp', '[]', 0, '2024-03-31 02:14:12', '2024-03-31 02:14:12', '2024-10-01 02:14:12'),
('7f6c9589f4fcc8eec8f6ca1f9f9cf1f447201db46f74da5ae06230ba4e7909189817cac09ad2573f', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:45:53', '2024-07-12 14:45:53', '2025-01-12 14:45:53'),
('7f6ea3c031a9dc5761feb6b57f8fc3b90f22c7f5264f459c5b332a280d063012d1e8fee9ded70f0d', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:15:52', '2024-04-29 11:15:52', '2024-10-29 11:15:52'),
('7fb53a04ed7981b76e132ff6f00c8449d4f6d49a3b8f330b73ab6cfadd6085c9810d6909712a30c4', 4, 1, 'MyApp', '[]', 0, '2025-04-13 14:21:30', '2025-04-13 14:21:30', '2025-10-13 16:21:30'),
('801ddad8bcf55ab92ffbb58dbd290af0a238685112601ee5cc5f8f3b892721e3f1c7e1b3a958eddc', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:39:21', '2024-07-12 14:39:21', '2025-01-12 14:39:21'),
('80254e4c3e6969c4b26d5349df14095693e41a3b4f6507fa8c150e1098e126c1cd92cfa46ec02276', 5, 1, 'MyApp', '[]', 0, '2024-04-16 17:22:37', '2024-04-16 17:22:37', '2024-10-16 17:22:37'),
('8032ab46ce9ad7888dfc9df83ba6f579bc384ecb30f313ca09691838cccc89d9ef8cdeeaa769983b', 65, 1, 'MyApp', '[]', 0, '2024-07-07 12:11:40', '2024-07-07 12:11:40', '2025-01-07 12:11:40'),
('8032b5a75c90f02f6f86cfa5349c6f1df30eec03be041379ea2cd773377de300f75e1f38c20844ca', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:34:37', '2024-07-16 10:34:37', '2025-01-16 10:34:37'),
('804921beb1801003e8787341787425edc8fa35394051919bfeeeb5c9d6287c926f146d2b8cbac4da', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:26:57', '2024-04-16 19:26:57', '2024-10-16 19:26:57'),
('805453de76adba85ca4cd92ae9c47393d5aac2cdd42d1aa5452824fbf643d8777f193da9ad9609ae', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:04:00', '2024-04-24 17:04:00', '2024-10-24 17:04:00'),
('80595237702a8ec103f6a604f11b42e331cf640a3e719862f86b976351d592850ea737858ed6e2f0', 69, 1, 'MyApp', '[]', 0, '2024-07-25 14:40:55', '2024-07-25 14:40:55', '2025-01-25 14:40:55'),
('80622b5dcdbe309abf718f42198880fedbfa192e59e044bf551ede24e8087da0d1c91dced7fb9f88', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:29:27', '2024-04-28 11:29:27', '2024-10-28 11:29:27'),
('806571680bdb1a00818aaee00f7ad67a047902bf04447a805821756433c6e78d3b34119ec4f9cad0', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:54', '2024-04-28 14:59:54', '2024-10-28 14:59:54'),
('80747cb641c8672f6d2e4fae0dac6dde43dd9f4008f2a867c6bc5e1ec97322be7db06f9bf08bb6f9', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:14:24', '2024-04-17 13:14:24', '2024-10-17 13:14:24'),
('807bbb67fe0cdfbe822ac26ef10bf9583c688cbc3a3de31b2d9a44aabb5b31bd7df95a21a1ddfe42', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:08:05', '2024-04-23 14:08:05', '2024-10-23 14:08:05'),
('8099a0ce0b5770fba39076863047d13ff0a97a754cd4f2b6d4f431ae26de5eb8a7f1e172776a99e4', 58, 1, 'MyApp', '[]', 0, '2024-07-04 19:22:50', '2024-07-04 19:22:50', '2025-01-04 19:22:50'),
('809dc91833e06a5c23df4625c90e82f5ff1b7b0a24823c604987a8ba2ca97f92b9a6e7b4728ad7cd', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:41:01', '2024-03-24 17:41:01', '2024-09-24 17:41:01'),
('80d0bc24de41fbfa5f77a0e4f4a49a58f8c6c43e8bea07066de871f473721db77c75310c0c200b12', 5, 1, 'MyApp', '[]', 0, '2024-04-29 13:20:56', '2024-04-29 13:20:56', '2024-10-29 13:20:56'),
('80dfbe46b771dfcc3099686df9f4534a122652f73c5e004fbb5fea46cd62f5ef9c5e9971b9fa6145', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:23:03', '2024-04-17 13:23:03', '2024-10-17 13:23:03'),
('816f7b330f8574138bf0045006e54301819a735e819b181e86f08dbd74e622971e1012ec8058a30b', 5, 1, 'MyApp', '[]', 0, '2024-03-20 16:17:30', '2024-03-20 16:17:30', '2024-09-20 16:17:30'),
('81b0c81281628c434520cdeb4bc47815adf9584402fc65c0b9318c33fe49501d8f6ba4bec56ae5f7', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:43:42', '2024-04-14 14:43:42', '2024-10-14 14:43:42'),
('81c23f31d8479424295bdc313e20c70058e807e99b0c1220eae1623a996d41f057986c852c6514bf', 58, 1, 'MyApp', '[]', 0, '2024-05-08 13:56:20', '2024-05-08 13:56:20', '2024-11-08 13:56:20'),
('822b9e9444322f2f2e3e29ef94451eb2dff9d387addfead6c610b2d6f9a4b1a98fb35ab61b007234', 62, 1, 'MyApp', '[]', 0, '2024-05-30 14:08:10', '2024-05-30 14:08:10', '2024-11-30 14:08:10'),
('825152395b6b53e76663e638a696f12039795898751e4bc2d3d73826e1db94d5fdc65abcb7395391', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:15:03', '2024-04-25 21:15:03', '2024-10-25 21:15:03'),
('8269590141dfc1d02f8a03b233b9ddf185eba28176ffecab7da4b4232036d41e7fcb5a3bb1aa6a21', 5, 1, 'MyApp', '[]', 0, '2024-02-25 13:49:57', '2024-02-25 13:49:57', '2024-08-25 13:49:57'),
('826c783718487280c1957c0f60b57d919b4ac1f04c6d39f5f19892996b2b0b5c74161f94b97015a2', 5, 1, 'MyApp', '[]', 0, '2024-03-18 12:41:42', '2024-03-18 12:41:42', '2024-09-18 12:41:42'),
('8276ccc0159f31a038e2c451ff07fdcf2df77384bdd14be2f1c246ca86864c83e07460d41dd43405', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:20:31', '2024-05-14 10:20:31', '2024-11-14 10:20:31'),
('8287897f58a72108fcd2f1c4ad9299aea36110222ca283986379043c961fd17248b65a49bee8ec3f', 33, 1, 'MyApp', '[]', 0, '2024-03-23 16:53:39', '2024-03-23 16:53:39', '2024-09-23 16:53:39'),
('82949fdcb8a0435dca0d56062c03e3db114a90b13c3a56290a1fb913b8de8c53166f571e8d1377f9', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:12:21', '2024-06-05 10:12:21', '2024-12-05 10:12:21'),
('829ab15927479f06f55d622bde208968fdef89ed862e335363433c2fc67a4b2147eb176b5de275e0', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:45:39', '2024-03-20 03:45:39', '2024-09-20 03:45:39'),
('82a8f106644db4679267d07189143e550294d8be03e072638c72b1527237661d3a2bf7f6c93ee613', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:25:53', '2024-07-13 17:25:53', '2025-01-13 17:25:53'),
('838ae00cd5b91cd3bae7c498c6164e2bbbd5a454750a5929831b7954adc0a4e356b01e89c565dbbb', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:19:23', '2024-04-17 16:19:23', '2024-10-17 16:19:23'),
('83e8987d84d158e6c425674fb37d6950ba3766749239de835e20d474c40f98848bd3b5e595ba7286', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:15:04', '2024-04-25 21:15:04', '2024-10-25 21:15:04'),
('841ab8eadfb6426582f7a2e67c8b000fa1c7454aa6ac37bf89774cae341603aca6ce061e0fd14cbe', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:58:21', '2024-04-24 16:58:21', '2024-10-24 16:58:21'),
('841c8dbdace48a2e219a1d8ec6e57cc418dfd3195c6e6f808b6b573d4e0127c18fe80b8b50c477a9', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:06:58', '2024-04-23 14:06:58', '2024-10-23 14:06:58'),
('842e40c4baf262858116ef74b80d53177627813db750a0e18b9887f6a81c7aeffa8936b0a831d537', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:20:28', '2024-04-30 15:20:28', '2024-10-30 15:20:28'),
('8464189cf9ca37f88fffa710067f411ea5f52846f6724fecd5a8a83b3a501239553026b7bef04b57', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:10:16', '2024-04-24 14:10:16', '2024-10-24 14:10:16'),
('8473399a61ce9891b98b720efd923aa45cd06b8540fd19c3271013893cd38f5e658b3b27d666c1f3', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:40:46', '2024-04-23 12:40:46', '2024-10-23 12:40:46'),
('849b65ffea2e1629216fff0d9f6337cdbc4dd29c4924359681fb63477694ab46640a32a7be0fedef', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:11:06', '2024-04-17 15:11:06', '2024-10-17 15:11:06'),
('84d22629020701ede5c65972bc85750e73728425762975a350454f01e65659382978a38133d5edfc', 18, 1, 'MyApp', '[]', 0, '2024-02-21 18:30:18', '2024-02-21 18:30:18', '2024-08-21 18:30:18'),
('850a5b7810bcf2fcea8b2a3f4478a7e602a6970891d1ebce3cc87dba2cd7f27c90ac51da2cdbdfeb', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:37:13', '2024-03-24 18:37:13', '2024-09-24 18:37:13'),
('8512317bf682b9f365ab57798e311566df1e42c79bebd04425f2ba2303457c983b5672aed14cfb92', 67, 1, 'MyApp', '[]', 0, '2024-07-15 09:16:07', '2024-07-15 09:16:07', '2025-01-15 09:16:07'),
('85721a25145c963299bc6d8f9e39f240e51edfd6edb6bb76047f8a6cf76334b4441d5cdaca700a6d', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:27:34', '2024-04-21 13:27:34', '2024-10-21 13:27:34'),
('857477f3983e48415e5ff888c65327dcf59ad3cb215c267ef61ff2ee9223c429226809359853ef5d', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:17:47', '2024-04-25 21:17:47', '2024-10-25 21:17:47'),
('8594b1c4f37c6aa6d8fa8f74c5c6010621b81bf074b760f6039a2a167764ee5f83fdcb639af6c1d5', 5, 1, 'MyApp', '[]', 0, '2024-03-24 16:54:57', '2024-03-24 16:54:57', '2024-09-24 16:54:57'),
('85a19fa9fd9d787c45331b5a45119a158f5b2afd8cac429af091358e651eaabf106eadd80c4d9efd', 5, 1, 'MyApp', '[]', 0, '2024-03-17 14:48:34', '2024-03-17 14:48:34', '2024-09-17 14:48:34'),
('85bb3e7db97d23913f7c8ec670d17fd8072c8a7cb147ba36db9ba862fc3f6f0932e330d5753709f3', 5, 1, 'MyApp', '[]', 0, '2024-04-25 21:35:44', '2024-04-25 21:35:44', '2024-10-25 21:35:44'),
('85be392ab319811260bf019a052ca5d916874ef5f8b28a5a9b71a7919552b962c84d3bf67743295b', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:57:15', '2024-03-20 17:57:15', '2024-09-20 17:57:15'),
('85ef1992e86b3fb83bdd3683ee95476b3857f0b74e81624682f7645fdd187e242192aaf21daf6fe4', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:39:12', '2024-03-31 14:39:12', '2024-10-01 14:39:12'),
('85fc1d813ee1316341e93eccbfaf285f9309c1adcb4d6b8b3984aea285b128c5e535470f63adcdbb', 5, 1, 'MyApp', '[]', 0, '2024-03-17 16:45:21', '2024-03-17 16:45:21', '2024-09-17 16:45:21'),
('86011745c36501a70ecd5799d7d89c20f1f4d6d0a71d06ccc14738b7848559133e437b743e076540', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:22:54', '2024-04-29 16:22:54', '2024-10-29 16:22:54'),
('8624cdb9ab5b4e38c819ec6626f24e56e043f0b7ef7898b319ddef00fff8467912eb30116c81fe7f', 33, 1, 'MyApp', '[]', 0, '2024-03-24 16:50:49', '2024-03-24 16:50:49', '2024-09-24 16:50:49'),
('867c3fccc908fdc3dfac9afed18fd2229f3f1a6442d8fce3871303f908997eb7219c2797308c76ee', 40, 1, 'MyApp', '[]', 0, '2024-04-02 15:43:53', '2024-04-02 15:43:53', '2024-10-02 15:43:53'),
('86850e39119106cddcd4cd2fd865feea5b6125b2b05f47e3e11fd8eae867f2e5b93f9669d3d350d9', 5, 1, 'MyApp', '[]', 0, '2024-04-08 17:31:03', '2024-04-08 17:31:03', '2024-10-08 17:31:03'),
('86d797560d3c45983e62a66136b5f2298b02d1c56315022c4fce08fdb1ce018c007710380970f453', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:49:48', '2024-04-02 20:49:48', '2024-10-02 20:49:48'),
('870a74120b54a9f115a2231dcde68d570d3826ba446877fdb32d263c0769bfde221f82ba7f9e027b', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:31:39', '2024-03-28 14:31:39', '2024-09-28 14:31:39'),
('87142a18b17885d212c6ded00dba85402be128d65520018317d3c846fc39c9d2e47c67f42bf6f8c7', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:23:58', '2024-04-17 17:23:58', '2024-10-17 17:23:58'),
('872323f10207653ffe5d71b8e1938586454581c8ef9b5a363cc2e146478f20282c788529a468bd57', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:04:56', '2024-07-13 15:04:56', '2025-01-13 15:04:56'),
('872977b41bea44364545d8a93d27f6cf9f5c69226fb9b70d6c94abe493a465e31c05decaf7ada92d', 58, 1, 'MyApp', '[]', 0, '2024-05-08 14:38:52', '2024-05-08 14:38:52', '2024-11-08 14:38:52'),
('8736a8e67a6a7604836f07115014cc9eeee614cf8be732c5083d06e7cc8bd13497c3884a516163e7', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:34:51', '2024-04-23 14:34:51', '2024-10-23 14:34:51'),
('874f1e1e13b9c4029cbf432d75013196f120e172bb6cba4e77ec989e7eb41f1d2a58ae0b160190c1', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:48:01', '2024-04-02 20:48:01', '2024-10-02 20:48:01'),
('8758facae930f4e79f505cd2ddfa4aaf6b66570d254dbd8f748067aaa8a7c7cc3b258b8849f368b3', 58, 1, 'MyApp', '[]', 0, '2024-06-27 13:36:14', '2024-06-27 13:36:14', '2024-12-27 13:36:14'),
('876fa8c0c59b5601db9a96aa26d4d5d118a0df0339828ede10f9c522ad279463959d490e276e3a4e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:57:14', '2024-04-23 11:57:14', '2024-10-23 11:57:14'),
('8774dd26b7a4a78f06cfd64c2bdbc463331693a6a48f66af83999421b5b09f3a33418880962801de', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:33:15', '2024-07-12 15:33:15', '2025-01-12 15:33:15'),
('877ff6328c18bd79df59ca1546c9854cd8af9d0b434a19d09e3f778abfa2b1063a2dd3b94c017529', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:38:49', '2024-04-23 13:38:49', '2024-10-23 13:38:49'),
('87942625b8c4a905d2a432a2738b2ef77106d1ac5505fb57ccba2cdd4828aaf4ca3c56ae4628dbc8', 62, 1, 'MyApp', '[]', 0, '2024-07-08 10:24:37', '2024-07-08 10:24:37', '2025-01-08 10:24:37'),
('87dac13b544791c47823758635922e3c1a556ac6e9297555d6a420cc55c6bd37fde75a19a67ecd27', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:27:20', '2024-07-12 15:27:20', '2025-01-12 15:27:20'),
('88205d6668831bb38a3ede4800101b8dcfe913c5e387a2d1bd026e4b08e8b1818ec083a8bf7351a5', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:34:09', '2024-03-24 17:34:09', '2024-09-24 17:34:09'),
('882a381ee3c903b12da6095df020df82aefe2f5cfdf59ffd39cfea86ef559aa9ce7708c61b9a18b5', 74, 1, 'MyApp', '[]', 0, '2024-07-30 13:49:38', '2024-07-30 13:49:38', '2025-01-30 13:49:38'),
('8862c933f364cba6a16139c95700ec43ae161aa07d87738c9b598bb2914631a740e07700592f3aee', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:52:15', '2024-04-15 16:52:15', '2024-10-15 16:52:15'),
('8863c1f2da34200b114f26f5e128dcdf240050e72cff1abc71c363f2f4d9899a5392a1d64e29114b', 5, 1, 'MyApp', '[]', 0, '2024-03-21 13:45:29', '2024-03-21 13:45:29', '2024-09-21 13:45:29'),
('88a483be703444de29df825909cf7f6cd7c23f6640947faa03b504186ad3fe72e2b4e147ae9d68b2', 5, 1, 'MyApp', '[]', 0, '2024-04-22 13:06:01', '2024-04-22 13:06:01', '2024-10-22 13:06:01'),
('88b57e6acea2bcb0eb0de26b8d3ab26e1f90eeabfe18dad8d1fd8807d7fdd868a980533f77b36584', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:20:31', '2024-05-14 10:20:31', '2024-11-14 10:20:31'),
('88c5dad3d5c9f18f9424a2a7ac291b1e825f7783e61e997f89b8cfed988708c6eb29496bf17aea91', 5, 1, 'MyApp', '[]', 0, '2024-03-20 15:14:38', '2024-03-20 15:14:38', '2024-09-20 15:14:38'),
('88de729bf3c0bb10a0d3f6a345c7e3441650301ef81df31d1751b4599217bb6d7c91d7503492da4e', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:18:04', '2024-07-13 15:18:04', '2025-01-13 15:18:04'),
('88e99421880bab9cd9032e917c680b23bb98f786561c77494a23e12064297239d06f8d9cea326915', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:12:21', '2024-06-05 10:12:21', '2024-12-05 10:12:21'),
('897f88490bdc4ef806e307d39b90d59e47ca0dbb4e8e1dd7683e25a9e93683daec22f6c7c9373495', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:14:26', '2024-04-17 15:14:26', '2024-10-17 15:14:26'),
('89832f6b16241c8fe655a93dc51ead57167c2c78eb1df9862fb1da6049514c71c0e6970c431e784a', 61, 1, 'MyApp', '[]', 0, '2024-05-15 00:40:25', '2024-05-15 00:40:25', '2024-11-15 00:40:25'),
('89c738f6de40b514f83bcc123ee93f9cf6c06070ba6a5653d90cea9ee064dc5c25db8b93e9e3262e', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:07:16', '2024-07-13 15:07:16', '2025-01-13 15:07:16'),
('89d0d51c4202aed23cf13bef5d5ca3dcbc3469a12352a9c522257e142bd90b81639207c95d3ef52a', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:37:45', '2024-04-25 18:37:45', '2024-10-25 18:37:45'),
('8a0f78bde37b9f5ad3881a136cfa885108b0cfdba78da254d492d2eda6b3e65770faa6489bbecca3', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:23:15', '2024-03-27 12:23:15', '2024-09-27 12:23:15'),
('8a91fddd9fa55281f26c08dd09501d03eea33a7e278c9e198caa3eff8b1733e201b782594b04381b', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:16:42', '2024-07-12 15:16:42', '2025-01-12 15:16:42'),
('8ae406f7fc96c61aa9928cc57e58a390be69b3a9aff59d7a5509eb09ce898b9581f76c15fc820477', 58, 1, 'MyApp', '[]', 0, '2024-06-02 12:44:25', '2024-06-02 12:44:25', '2024-12-02 12:44:25'),
('8b39ed5454978b3e4fda3f2bb64dfeb0142a72a7405eb1f3249cb349c0f2c75b389d7afaed6a79fe', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:57:53', '2024-04-23 18:57:53', '2024-10-23 18:57:53'),
('8b510407e5293eb9d38bf6c107532e509cec4f4a389b3dfd8441acf0a99c9da857ea9451b0c20ad0', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:32:00', '2024-03-24 17:32:00', '2024-09-24 17:32:00'),
('8b7ab3dba1bd36b8be100c1757bae7a08c2c9eca6e8334e2ed3dbbddd9cff22420f2fd689ad62e67', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:15:09', '2024-05-02 12:15:09', '2024-11-02 12:15:09'),
('8b7b5f64d58eca9523b9773271db6f6ff67b6ed6eed70743a48b9785f4c41005948066feb6ef1c74', 5, 1, 'MyApp', '[]', 0, '2024-04-15 12:50:52', '2024-04-15 12:50:52', '2024-10-15 12:50:52'),
('8b8c221cb1a2b1d9505379c5965c70e57eae0a2471407a7b3ba5f4a538ecdd98939c07190f495add', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:34:52', '2024-04-22 18:34:52', '2024-10-22 18:34:52'),
('8ba00ca270da063ea1da68b1b92fdf8cd16a628dca681248bf3f869df82cdf4a2cf62a38503edd84', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:36:03', '2024-04-23 14:36:03', '2024-10-23 14:36:03'),
('8bdbafac5b0ae138534bfdf16f7cfe4e422e14f942f689ce4165a7d205b7b39a6e20557e03c9862f', 5, 1, 'MyApp', '[]', 0, '2024-04-14 12:37:50', '2024-04-14 12:37:50', '2024-10-14 12:37:50'),
('8c11b119e0cbcf6db7e2eb29e748376fd57f297741920d913c4e803d5f5f01ee05bb6f6ab8c39dca', 5, 1, 'MyApp', '[]', 0, '2024-04-30 17:26:42', '2024-04-30 17:26:42', '2024-10-30 17:26:42'),
('8c87d1a408c258370243ceb189c6af75a54a593cac062247b1cd9b660d836a008346e8ff57b607a8', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:13:17', '2024-04-14 13:13:17', '2024-10-14 13:13:17'),
('8ca3f01b340b03844fc10358e12761f09b6deda1031867c293861c33d85b320b8d3cfd9c07c6d700', 58, 1, 'MyApp', '[]', 0, '2024-07-04 19:18:47', '2024-07-04 19:18:47', '2025-01-04 19:18:47'),
('8ce3386367c89ae28ae5723f35f4bd8d71bebd9a31925b0aff84e832fb3295e5b426d115546f86ec', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:27:57', '2024-04-18 21:27:57', '2024-10-18 21:27:57'),
('8ce9bf86b8a3220d2c594a768bc48fc146fbc98f405f4f0c00a29bb573a55eab07c29463c3e0cc17', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:06:48', '2024-04-02 14:06:48', '2024-10-02 14:06:48'),
('8d08edaca896531f022253a4e1ca6d6b56c0b50d61ed8ae9c9d8f227670454084bdcae885561ab5b', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:09:21', '2024-07-16 09:09:21', '2025-01-16 09:09:21'),
('8d1ace87c3fdc2052e3589ddaeea8a9c9f2ece690642562a2d095273991cdf5b37ac0d513fce9f2e', 5, 1, 'MyApp', '[]', 0, '2024-02-14 18:03:38', '2024-02-14 18:03:38', '2024-08-14 18:03:38'),
('8d3bab7795cd1427494c2e919d6bb793f68a967b20fa72b75824c17d19caa534f6f83eae7d753f50', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:23:32', '2024-04-23 12:23:32', '2024-10-23 12:23:32'),
('8d3d02e00fafb6a7af2d4bb8a2037d2e0b12d4558bbd38da73b571110a85152afa61ca5ba64f27bb', 58, 1, 'MyApp', '[]', 0, '2024-06-04 17:22:00', '2024-06-04 17:22:00', '2024-12-04 17:22:00'),
('8d9b71cd082099e5033021c0025a306103bea65ab9150d518f811d4d0d89554b5a99f130940ad03f', 5, 1, 'MyApp', '[]', 0, '2024-04-21 12:27:55', '2024-04-21 12:27:55', '2024-10-21 12:27:55'),
('8dc22579a65dc15d3e6d201c63fd3b2e78dcc33b86fb89abc7c82e0492095f3b928ae218bb517519', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:57:44', '2024-04-23 12:57:44', '2024-10-23 12:57:44'),
('8dff9d2a50058a8d6bfbad80241b8112b10cc2684b4a6939679a33dc4734ac9c82393e1185422f92', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:39:04', '2024-07-16 10:39:04', '2025-01-16 10:39:04'),
('8e119431cb4ff173d1f3fc463ce438d85b8cdac33e8dc68110a510ffa13c529cbc436c4f31e1c129', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:41:15', '2024-05-13 15:41:15', '2024-11-13 15:41:15'),
('8e46fe6eba7875280fc6ee82e5e31d6746e05742c378cbf683eb2f385b9d7fef63e0616843efc207', 5, 1, 'MyApp', '[]', 0, '2024-03-05 15:58:46', '2024-03-05 15:58:46', '2024-09-05 15:58:46'),
('8e7e960dbb37b45f9b10059b727c0caa72999e33e81370d0e0fe1f64c24a3f7d5d15c9d449a4efac', 5, 1, 'MyApp', '[]', 0, '2024-04-28 12:15:07', '2024-04-28 12:15:07', '2024-10-28 12:15:07'),
('8e9529b9b8a55de68c407f6d049b1375137888e596e3827d07e9af1356edf2ee6dcade486f0c21ab', 45, 1, 'MyApp', '[]', 0, '2024-04-08 04:46:31', '2024-04-08 04:46:31', '2024-10-08 04:46:31'),
('8e961f09037f0b29d549332f303b212a085aad239d5661cbaf648677c8d15d9ba0e50b5b384ba4f4', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:24:40', '2024-04-21 13:24:40', '2024-10-21 13:24:40'),
('8eadd48cea993c170a081cfa1830a19286829e47bc65bd6f4ac34f8400389b589aaf805d8c823633', 5, 1, 'MyApp', '[]', 0, '2024-04-02 14:01:30', '2024-04-02 14:01:30', '2024-10-02 14:01:30'),
('8f33375155d04a70374f5e02ef10e9d2bf8aedbec7a16557681412578cd2aca4ed7218deeb7c0c75', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:29:26', '2024-03-24 17:29:26', '2024-09-24 17:29:26'),
('8f431f2e947087cf34dd7b4a486d1967ac3c49ca23d915829c03b2612669be8af0972fcc698caefd', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:46:45', '2024-04-21 16:46:45', '2024-10-21 16:46:45'),
('8f529f898baf23decdd9563bb1ae2fd800041fc4e8e0275fcbda255d2294d9e2ac4ef6623fb8e0dd', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:24:17', '2024-07-12 15:24:17', '2025-01-12 15:24:17'),
('8f5f63080cf29528baab89c639cd9ed0103d8da6260038a59e96cc1726bf516cdcfbbbd0d12a44cb', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:33:15', '2024-04-15 16:33:15', '2024-10-15 16:33:15'),
('8f6c1055845794a29d7b9ab2c75c9c68be7e211e61e28df306600d8fedbddd8c239bdb1bf2c8fed9', 32, 1, 'MyApp', '[]', 0, '2024-03-21 02:31:34', '2024-03-21 02:31:34', '2024-09-21 02:31:34'),
('90407c98f848661d77e7ab02058b3be3658e8491cfe20f4562c3e61240b8208aea23af93b4f96c91', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:37:39', '2024-03-27 12:37:39', '2024-09-27 12:37:39'),
('90606d4bb1113d9acbbf3d6c1eb13ae13b71cefcc9aabe1de31fa3d99954e0b0a989ef81893027e4', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:15:11', '2024-04-17 16:15:11', '2024-10-17 16:15:11'),
('9063ec0488f071c07140a1fe9f3b0ee461a1274b613195a8a1984ef3c3fb74fd2db7a16d6ae7e879', 71, 1, 'MyApp', '[]', 1, '2024-08-01 13:56:34', '2024-08-01 13:56:34', '2025-02-01 13:56:34'),
('9094ebc8cc511e578d27a2ec68f717f381823c46aaad7247a9ab4fe74dcb69718b5fc078d985f529', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:46:32', '2024-04-18 12:46:32', '2024-10-18 12:46:32'),
('90c244d37d4f956615eb3c74b14847ba4e51c4f2c068b29d6880b214bcc9ecd144542dbf5c2a4c36', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:41:42', '2024-04-28 14:41:42', '2024-10-28 14:41:42'),
('90d11e477763b47f87b266fd81af359541038bc6d029e84fd12411abdfbbdc13bf6ed10c0bfd6fd1', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:28:00', '2024-07-12 15:28:00', '2025-01-12 15:28:00'),
('90d1ca0daef4a3e75ba7e47ee47936e266fb9b3db91e1b8c01606a3e3a51eda435e4714c7dc1067c', 5, 1, 'MyApp', '[]', 0, '2024-04-25 17:57:24', '2024-04-25 17:57:24', '2024-10-25 17:57:24'),
('9147552ad5b5c9caa3af6547a44d5c22f699be34e38e1a6f1785b1d6e1003cf927e544aa4dd162c2', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:13:32', '2024-04-17 13:13:32', '2024-10-17 13:13:32'),
('91c015c9007d6cb9b329ea3aa980145b66671be8a44a41d8d42800f89324fe22faa408a198c75678', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:23:15', '2024-04-17 15:23:15', '2024-10-17 15:23:15'),
('91de623684b1c741cd5c81af2a360af26757740c77c66628f25f17ff44eb3bdafba1694f85e6792c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:55:39', '2024-04-28 14:55:39', '2024-10-28 14:55:39'),
('923c7563944ba991c0b885112bf99c583efa0c3520f0f0d0b6ca532ab2df13f7cb6e3db8033fd1b7', 5, 1, 'MyApp', '[]', 0, '2024-02-28 11:45:46', '2024-02-28 11:45:46', '2024-08-28 11:45:46'),
('92598b420a1eaa252ff7e694b46126b2615f976cb2f63e41d4838cf3669f2eb383db52f67288de4e', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:42:03', '2024-07-13 17:42:03', '2025-01-13 17:42:03'),
('927004a04df5d9c227a187dcddd662796d8c9558f7da4bdecc845a07d129b276cfe80f8cf4d1f7f6', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:05:20', '2024-04-14 18:05:20', '2024-10-14 18:05:20'),
('92aa4221f69e62fd6d0bf4daa6ee2a44820cb9d95f6f4dd4427faa22df2d9497c820b9d721b46bf8', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:30:00', '2024-07-16 10:30:00', '2025-01-16 10:30:00'),
('92b70753bb3bd6a6f15b9ae65c77265b3c0a366581e69f84998bfeb8df30c926a42c1d2d4715921f', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:48:00', '2024-04-23 11:48:00', '2024-10-23 11:48:00'),
('92d1dd6205a4ec76b80c6c392d8905745cad7785cb7e1c4608bdd97c2c0c8d1c444686662535d569', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:02:59', '2024-04-23 14:02:59', '2024-10-23 14:02:59'),
('92d72ab35325d1ad5ae83a751148d964cceed1171f345449bac479a75aafef35810bde7371a75b40', 5, 1, 'MyApp', '[]', 0, '2024-04-30 12:54:59', '2024-04-30 12:54:59', '2024-10-30 12:54:59'),
('932fe3e147641581fc5381fbed92b2a80066dde3b1c0846f4b6be909fbdee134fe649a8f50ad91e2', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:40:19', '2024-04-18 20:40:19', '2024-10-18 20:40:19'),
('93506670c10d943fb7ccb0bed37c32c7295abaefa64244f2011cfb6e5f1a8a41d61cfb0bc362c677', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:21:47', '2024-04-22 19:21:47', '2024-10-22 19:21:47'),
('93626dd69786a56b63790ef78079b9c57db4cf2a0379261aa395f0084346317c8d6e9f1a85475a69', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:58:28', '2024-04-23 11:58:28', '2024-10-23 11:58:28'),
('936868bb27e839435322684a08666ac04eb00716d1f1abd85dc5b0151533f8fa82fb4b5f7df8772f', 5, 1, 'MyApp', '[]', 0, '2024-03-18 12:43:32', '2024-03-18 12:43:32', '2024-09-18 12:43:32'),
('93761ca54a8805ba707a024fc056790492006641b8c4911e70d75485810c7e116a365c95a33394ba', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:13:48', '2024-04-29 14:13:48', '2024-10-29 14:13:48'),
('9377cb53e3399f75f63b15740adf1b517fb193ec2867ac59ce6ddd3356085c48bb83cc67e74bf6c3', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:35:15', '2024-04-23 13:35:15', '2024-10-23 13:35:15'),
('937e4e1f13d77a9f6dc9c678df9d80f7154b827445660d4f385fbc76288758bab6e707f3a84a1be2', 65, 1, 'MyApp', '[]', 0, '2024-07-07 09:07:47', '2024-07-07 09:07:47', '2025-01-07 09:07:47'),
('93bbb9569a02cd3bdc6e4ce48f136bbe3c4ce03a7a473029a1355b87807f538a7531d08cb7386b6a', 35, 1, 'MyApp', '[]', 0, '2024-03-26 16:16:23', '2024-03-26 16:16:23', '2024-09-26 16:16:23'),
('93c389f20d289981932ed392d33a1ff1cc6777a29829d7d7740b3bcc015bf2398f42933751ecb5b1', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:51:34', '2024-04-23 18:51:34', '2024-10-23 18:51:34'),
('9424c01ba87f727d6686d99316f128f53cb9ea353429c903f947764cba0c72ea9c46b739c444486b', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:55:17', '2024-04-28 13:55:17', '2024-10-28 13:55:17'),
('9425ce7d3a15ac117c4dda1e52e21ec6108d344cd9f0ff2d11c233cf93a1b4e6f8f41d9cbc1c95da', 5, 1, 'MyApp', '[]', 0, '2024-04-02 13:19:19', '2024-04-02 13:19:19', '2024-10-02 13:19:19'),
('94308c6b388ea98cfe0d5f2a71a9b007e380c0b229c313d1123812d571a56d47133fa701d8142914', 58, 1, 'MyApp', '[]', 0, '2024-06-02 13:36:59', '2024-06-02 13:36:59', '2024-12-02 13:36:59'),
('94446863fa414e049968ce4b70b4772e43e4e4f06614fb75d47cb92f4ffc708ed931052b453b5bb8', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:48:28', '2024-04-23 14:48:28', '2024-10-23 14:48:28'),
('944d5d5b86aded5eb48e27a7ee794f2518d78db63f7bee254a3ffd5ef90895a5b6ae3b334c67dc8b', 35, 1, 'MyApp', '[]', 0, '2024-03-25 12:41:44', '2024-03-25 12:41:44', '2024-09-25 12:41:44'),
('946aa834d90dfbceb83aeeb701cfd6c85e38a443596d2e70b3b316f0f84d6bddddb11f48a360a369', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:23:34', '2024-04-21 14:23:34', '2024-10-21 14:23:34'),
('946c3ece0092125b44c665236adb9e4a096eecf96728cdd34e90e844d713ae94bc85e35a1c5698e2', 71, 1, 'MyApp', '[]', 0, '2024-07-30 17:37:02', '2024-07-30 17:37:02', '2025-01-30 17:37:02'),
('946d4d14e27602bacdf2ea0c2efa41ace0052969bf16378510f1d3b1588d94d0f48cb23c6a7ea13c', 31, 1, 'MyApp', '[]', 0, '2024-03-20 04:01:08', '2024-03-20 04:01:08', '2024-09-20 04:01:08'),
('947bff8643c959d6f3b9929f8ae31e831bf20308341aede558a63deeaa6d0ed4c158cd983710eb97', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:17:55', '2024-04-16 19:17:55', '2024-10-16 19:17:55'),
('948b578097004243748bcabdf57b5cdc268f8ec72a756d047ec253f5269b27883709d32fdea18a93', 5, 1, 'MyApp', '[]', 0, '2024-04-14 10:23:44', '2024-04-14 10:23:44', '2024-10-14 10:23:44'),
('94b921f1eedee0266915904c582e61e388c889f3a0e5072b0bfc756828b7ff4e914cf0eb898856b1', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:14:56', '2024-04-02 16:14:56', '2024-10-02 16:14:56'),
('94be0f48783e0ed07bf8435a74fba763d763e94dba2316fbb8c2e858346439634c6a0530214a79f6', 5, 1, 'MyApp', '[]', 0, '2024-04-17 14:59:01', '2024-04-17 14:59:01', '2024-10-17 14:59:01'),
('94dfb4484f05b15b62cce8d12b4b8b6fb73bcd71ed626627c428cffdb8fa0537ed149be52a2306f7', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:28:14', '2024-04-28 13:28:14', '2024-10-28 13:28:14'),
('950247f28b53a682c5804954a0d9f7af0c60868e21394bb6ba5600aa849d0639a89c878180c698fa', 5, 1, 'MyApp', '[]', 0, '2024-05-01 19:46:28', '2024-05-01 19:46:28', '2024-11-01 19:46:28'),
('951441f63e1d0cb0b740d612d020b6f014c02ae141ea2a767b3953cb2fff2bb00f1f24c472e9a223', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:18:43', '2024-03-24 18:18:43', '2024-09-24 18:18:43'),
('9539878ff8347f7dc3ba0eea803275c3451a91bfd47cf6eaeb3db9a0b974fa5046f49c56f25c5cf6', 5, 1, 'MyApp', '[]', 0, '2024-03-19 15:17:22', '2024-03-19 15:17:22', '2024-09-19 15:17:22'),
('95aeb4bac389a63f63f177e4c8975e0891b140f40d14fc7228d222f29d169d87e18454e9993559ad', 74, 1, 'MyApp', '[]', 0, '2024-07-30 11:09:31', '2024-07-30 11:09:31', '2025-01-30 11:09:31'),
('95bfe72a4e6d86b4607c8e8f4d9b0cc82dc2112b8cadb839256159b390c66650d79fbb2922c92032', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:43:33', '2024-04-21 17:43:33', '2024-10-21 17:43:33'),
('961066517c00751bbcaa75d0631ef623f017691fdb5b5f3fd757b276f089acc44aa19a7f48962d0a', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:45:16', '2024-04-18 20:45:16', '2024-10-18 20:45:16'),
('964366dd9086df5ea0a144cb067c0c45e6cde4d08d43895b4ded69d8441aa8284c77c090cb7cc7e2', 66, 1, 'MyApp', '[]', 0, '2024-07-11 16:44:52', '2024-07-11 16:44:52', '2025-01-11 16:44:52'),
('969878a5b78864ac76591f7febdf94f647c90f3c4ed5b8900ae9c0572d3a4c92480f07ae21a79ec1', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:29:04', '2024-04-23 14:29:04', '2024-10-23 14:29:04'),
('96ea86397392bcda8a4ed55363f25f63fe84e2a748c8fede448761cabe632cc8199f41b233117d08', 58, 1, 'MyApp', '[]', 0, '2024-06-06 08:26:48', '2024-06-06 08:26:48', '2024-12-06 08:26:48'),
('96fc3c7d1f37a6297ebdf7500cf0a583c6814a6f8f1c8706c83df1984ee27116b488cad8bfcb9483', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:23:53', '2024-04-29 15:23:53', '2024-10-29 15:23:53'),
('972b358115ec29af2937e45243d812dfb918f9f8f5a18a232622092ab771fa37bf0170d6fc35da5b', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:48:00', '2024-04-02 20:48:00', '2024-10-02 20:48:00'),
('975b448c113f9ad17acc92a48bc11d68472e02a5322b40018285dc7d6c3545b4f4d469c55ef68784', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:39:22', '2024-04-28 15:39:22', '2024-10-28 15:39:22'),
('97a735f401e08123c530fdbb5e1a6d780fe5fb90f5715cfeb3795f7b99cdc86aea511b1637bb3dda', 35, 1, 'MyApp', '[]', 0, '2024-03-28 18:50:31', '2024-03-28 18:50:31', '2024-09-28 18:50:31'),
('97d884b990cf7c49c1726302177c360342b4b027216a4ee8f2df2ee99e6949bacb2afdd7e01d1aac', 75, 1, 'MyApp', '[]', 1, '2024-08-03 14:39:05', '2024-08-03 14:39:05', '2025-02-03 14:39:05'),
('97f84873d614f20c30fadd0d34e7bc33252c9dce6f79d500319f123d9a841a03490122c53c0cda5f', 5, 1, 'MyApp', '[]', 0, '2024-03-20 04:15:59', '2024-03-20 04:15:59', '2024-09-20 04:15:59'),
('980ffca71909c5c01d7cac3a805a52d684d942f5e3713decf2f27eb5e98887c13be0b57f39ba5714', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:48:03', '2024-04-16 18:48:03', '2024-10-16 18:48:03'),
('989b86b767afbfcffb81ef32f1a1119223cec87f6ac5d80cfab49fa8e2e80dc01706ba1e6d330128', 62, 1, 'MyApp', '[]', 0, '2024-05-30 07:55:35', '2024-05-30 07:55:35', '2024-11-30 07:55:35'),
('98ad287447b551cf6014b33a1c204e6b161bccda85b5cc48fb2c638c18e6331bf334226f4c9538df', 4, 1, 'MyApp', '[]', 0, '2024-02-14 12:44:17', '2024-02-14 12:44:17', '2024-08-14 12:44:17'),
('98b04abe4b7e7fb4e7517a5bd1e950b2214c1f65b38172257c6c5c5a09473224d31b5864192f7792', 48, 1, 'MyApp', '[]', 0, '2024-04-16 14:21:26', '2024-04-16 14:21:26', '2024-10-16 14:21:26'),
('98b4c2cdda2b1c49301261a3d4259380b7e147d04efc6bd3eafe999bb25eac97798ffd334dbf3118', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:37:09', '2024-07-16 10:37:09', '2025-01-16 10:37:09'),
('98c4dbde6c9f12cc25ed3b64f515c0946c87757fc0edddac0a265f93a97ead68481dd28dfedf553b', 58, 1, 'MyApp', '[]', 0, '2024-05-28 14:23:31', '2024-05-28 14:23:31', '2024-11-28 14:23:31'),
('99284664340584f1a434ade5b9aad0e68689de39d483218273268416801470c2cc1b137ced0c7273', 5, 1, 'MyApp', '[]', 0, '2024-03-24 16:54:57', '2024-03-24 16:54:57', '2024-09-24 16:54:57'),
('9928a3d9db18f002395049eea6a244c17d06ad16cbfef217fe7e9e231a94c97dd5031b8bef2bb082', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:35:54', '2024-05-14 10:35:54', '2024-11-14 10:35:54'),
('9947798924a331159ae34ab84815954b1f7c60725414494abcb417f49114afd7f31a016ee337874b', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:01:26', '2024-04-14 16:01:26', '2024-10-14 16:01:26'),
('994f7494c2a5c3e2c9637da12ca900f49b13763336e32b6b157e536b8ee9424abac074ca556cee71', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:39:38', '2024-07-12 14:39:38', '2025-01-12 14:39:38'),
('9989fa3b3a13fa8827bc6d34db1baaa0f80feac1285698c95f66296b42e6ac201e1bb583b4be3f1f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:37:07', '2024-04-18 22:37:07', '2024-10-18 22:37:07'),
('9993711a71b32281fbbfe8849dc79ecbd4fbbd9119d8651d546eb05fff13c901cd11a81645b466b0', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:24:13', '2024-04-23 12:24:13', '2024-10-23 12:24:13'),
('99a2ee169d3062537e9f5af0b7780e6129981e15728a957b2ec617185e51aea6af0ffaedcefc20b5', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:39:34', '2024-07-07 16:39:34', '2025-01-07 16:39:34'),
('99f956e707598f7f4dfd59491b729c4d825c55445ce35515f38213d95e1d8c2f0e1b0dc670bc0712', 5, 1, 'MyApp', '[]', 0, '2024-03-06 16:01:19', '2024-03-06 16:01:19', '2024-09-06 16:01:19'),
('99f9e613bb4b245fab8b1f519f9483ec8e8d2469aae5e6b9baf6d5d728c55e28a8f8f15d4f68640b', 5, 1, 'MyApp', '[]', 0, '2024-03-20 05:00:10', '2024-03-20 05:00:10', '2024-09-20 05:00:10'),
('9a138d480e01b1956778ef491e690fd6856725bfb4fe18c087c9c646d2c118d6e7002ef632d8bf0f', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:09:38', '2024-04-29 16:09:38', '2024-10-29 16:09:38'),
('9a45d1b8ba27895d8171ea8a9fbf6273cfbb3829f516cb7e0029f16ed85be964ec147b28442b0fa8', 5, 1, 'MyApp', '[]', 0, '2024-04-25 19:12:17', '2024-04-25 19:12:17', '2024-10-25 19:12:17'),
('9a48ec6c258929431bed2e70e45f2a984236afa2f3f67207d6207508b872b4642b866f584020e0b2', 35, 1, 'MyApp', '[]', 0, '2024-03-26 18:14:20', '2024-03-26 18:14:20', '2024-09-26 18:14:20'),
('9a67a1866d3b4c98481f0a3cc89f56572d040a4c4b400100cc2df49d81d4133b30b981da80ff24c5', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:25:30', '2024-03-20 17:25:30', '2024-09-20 17:25:30'),
('9a9a996a8b81e83b48ef7fb471913ec3b381ccff4098f8e193001587c8a718447cad2242159ba9ae', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:44:04', '2024-07-12 14:44:04', '2025-01-12 14:44:04'),
('9aa6361d0e10520a5c12cc30b30b43ab63c49580b18e6d9a1099855e478bda5bf46e531df43a488b', 5, 1, 'MyApp', '[]', 0, '2024-04-16 16:22:06', '2024-04-16 16:22:06', '2024-10-16 16:22:06'),
('9ae43d1b266865a3b5090ab62780ce29a66190c5868bf6e9c9d288f08db0198c42732b4e343b0682', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:27:08', '2024-04-23 16:27:08', '2024-10-23 16:27:08'),
('9b0823686c18a3f551b308b0272cedcc5295700076e5fc09c37472c640745af121edcf60156adf59', 35, 1, 'MyApp', '[]', 0, '2024-03-26 13:01:59', '2024-03-26 13:01:59', '2024-09-26 13:01:59'),
('9b12a47b8063abbee803bb5ecc2b4cbe8bda2d3ba9374827eafdf6b8b8cbd3f9fa94cc23d9746407', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:03:27', '2024-04-28 15:03:27', '2024-10-28 15:03:27'),
('9b2d37fe060d8383e2d2efbdbbdfa2c16b745222c8e7b8da20cdde9a9e8d8f428ffce8a607875979', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:21:40', '2024-04-28 16:21:40', '2024-10-28 16:21:40'),
('9b335d0e947d9e70b9ca2037534de7b52ed11d84b944d09782dacb427ca56095b1b6df735e96461c', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:01:27', '2024-04-28 13:01:27', '2024-10-28 13:01:27'),
('9b3e36e4ee36d9dc2381a82a06f79f98463ab4d6d8805580e1f30452c33ae8243b00697dfe74342c', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:40:06', '2024-04-17 13:40:06', '2024-10-17 13:40:06'),
('9b7b1e634f0c00cd3886a079b16bd88b5c649a72ac4b6cc8dcad087559ef923c740405a1ad55a7a1', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:36:52', '2024-07-16 10:36:52', '2025-01-16 10:36:52'),
('9b9435345b64a77757ee36905bb6fb27e031a5de8fee6f1993a3a73d897ec9830d9ba02bd448176d', 5, 1, 'MyApp', '[]', 0, '2024-03-04 15:51:35', '2024-03-04 15:51:35', '2024-09-04 15:51:35'),
('9ba5eee885b5990346515747ff8710df24544bb42e35a1bf36c2efb83206684cb8cc97413d066e54', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:54', '2024-04-28 14:59:54', '2024-10-28 14:59:54'),
('9bae0924a3cc02a9fb610634bf3846075d412aba3e36bfac06e3bdbd90ca23520ade0690c928912a', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:47:00', '2024-04-02 20:47:00', '2024-10-02 20:47:00'),
('9bba60d4c6dd9fa9936e2d0961a83386e16162e6782f8cfe825ac7e9443acc8a949fd55a6cae3f45', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:01:27', '2024-04-14 16:01:27', '2024-10-14 16:01:27'),
('9bcd31550c0084cae6858d32244318985543271f41dc4672c9184136af47ec933670747e312600e9', 33, 1, 'MyApp', '[]', 0, '2024-03-24 16:45:00', '2024-03-24 16:45:00', '2024-09-24 16:45:00'),
('9bf7e46d0f06a3245e24b652fa737ef086063e5167bf159e5459bc434f8cae85c357124ff4295564', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:29:11', '2024-04-14 16:29:11', '2024-10-14 16:29:11'),
('9c0e210d5e87434dc2cb90f309cecc0892fad0ecfd99e2d983c0af13da5e30e1c52ae180b5a9fdf5', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:59:47', '2024-04-18 17:59:47', '2024-10-18 17:59:47'),
('9c19f5a2695ec90953942dd59593c4f2c9a8fe129a9910f002c0194c86554061ef4e8ac3d4e87af8', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:58:27', '2024-04-23 18:58:27', '2024-10-23 18:58:27'),
('9c2e3d85de417938a6f7e2a6805da3edcacbc4efdedd6adc95405d747172de6784838e50be761dba', 63, 1, 'MyApp', '[]', 0, '2024-06-03 10:10:47', '2024-06-03 10:10:47', '2024-12-03 10:10:47'),
('9c3253c8fb0b1231aea32aa821a8701edea87b3474cdb154dde2a7704b112e73be40de50b3e7c3cb', 69, 1, 'MyApp', '[]', 0, '2024-07-16 18:45:57', '2024-07-16 18:45:57', '2025-01-16 18:45:57'),
('9c37a0146c2f6dcdb35d0e5ea27164d0f8acda479da13706996dfd1897da56188756feb5ce8a576d', 58, 1, 'MyApp', '[]', 0, '2024-06-02 11:47:16', '2024-06-02 11:47:16', '2024-12-02 11:47:16'),
('9c720c36471e3f89dd5f11b4be7909d7bf7dfae0cb9e55f2b54651448612648d18349f3ebb08a5f7', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:15:10', '2024-04-17 16:15:10', '2024-10-17 16:15:10'),
('9c83e70e247f30b94c309d8523aa6795399e991d66fcbbb76891e9de9cb3bba2bdff3a0c22a00aa4', 33, 1, 'MyApp', '[]', 0, '2024-03-24 16:50:49', '2024-03-24 16:50:49', '2024-09-24 16:50:49'),
('9ccde192fe023425dc9fe90b551c146851f4e90835cadf97fc87b397a3d9053396c8301909820039', 5, 1, 'MyApp', '[]', 0, '2024-04-15 17:24:56', '2024-04-15 17:24:56', '2024-10-15 17:24:56'),
('9d0c9ce3ba4208dcc20b4fb5cfe2165e1c25cfa0c6632bc725987e0c1aaf7cb9601abcc0287d1612', 11, 1, 'MyApp', '[]', 0, '2025-08-14 14:10:23', '2025-08-14 14:10:23', '2026-02-14 17:10:23'),
('9d1f03b2235301b6d5d6b02a3346a8840f3ee9a3ddc12e4fb5a0d168733f7f61871afd4d4b2e43f9', 65, 1, 'MyApp', '[]', 0, '2024-07-07 08:58:12', '2024-07-07 08:58:12', '2025-01-07 08:58:12'),
('9d2de26fd09401f4c77670cf6d4b620e2c007e18f30fcd71a4509ec5002e2574f517ac49ebeda8f2', 30, 1, 'MyApp', '[]', 0, '2024-03-19 11:11:24', '2024-03-19 11:11:24', '2024-09-19 11:11:24'),
('9d39e6040bb5f03baa0caccf04e72342a5432348347a3798f5cd2cc70ba8f37616abb7b533bf4938', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:51:57', '2024-04-16 18:51:57', '2024-10-16 18:51:57'),
('9d8b373f87e37d5381e40345e739b1e663467ae2c0731343243ca8a77407733e691fd65dcb99ac4d', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:08:13', '2024-04-23 16:08:13', '2024-10-23 16:08:13'),
('9d95d1f62fd75232423b383f438f00c0b83f43c6ec8bbde20d398f599e6a2243fb7060965bddfa02', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:42:48', '2024-04-17 17:42:48', '2024-10-17 17:42:48'),
('9dadbe51a13b63b73c86df6b521bcdaa88691d02b37e303224d011e5fb594a4f368af87fd6d45922', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:31:58', '2024-06-05 10:31:58', '2024-12-05 10:31:58'),
('9dbd88adfc1b2cd8b63be64540f8d4ea32545cf02388ceebc2d6bbfaef1082a192ac8a5dc7399eb8', 71, 1, 'MyApp', '[]', 1, '2024-07-26 16:22:20', '2024-07-26 16:22:20', '2025-01-26 16:22:20'),
('9dc36a6654f25891c67b4cbc419c078dcdef9943dd40b105778bc35d1cc3d8d603c3ed549b9ff0f7', 5, 1, 'MyApp', '[]', 0, '2024-04-04 18:40:22', '2024-04-04 18:40:22', '2024-10-04 18:40:22'),
('9dc7a81253c7e6b54005f3ead5ab7c2b3be0830b39a045cba98a88d0015c1e8d50d4e0fe6f91289e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:15:10', '2024-04-28 13:15:10', '2024-10-28 13:15:10'),
('9e1f55eeef8556875a9dc7ba34cb568558031f882a5a9f46f6ddd852839987c9a52f3c1e20f20605', 5, 1, 'MyApp', '[]', 0, '2024-03-05 13:03:46', '2024-03-05 13:03:46', '2024-09-05 13:03:46');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('9e8fe632b540dce7683f544336cee74d3a5f8d113927ba9fb1637f657df9dbf313694c39682b3636', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:26:39', '2024-04-23 12:26:39', '2024-10-23 12:26:39'),
('9e951e5e476b634c91dd317f9913b157bc148743cd9199c6e2cf53e21e178bf38e412014223852b3', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:32:39', '2024-04-23 13:32:39', '2024-10-23 13:32:39'),
('9ece63bac8afd1814049135328e28bc94389c5e2234a3492e6f8cf3d72ab70c2c765a8cfddc66fa7', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:01:05', '2024-04-23 12:01:05', '2024-10-23 12:01:05'),
('9ee205e30a74867cc26b4bc90b21a248629ab8db2ec5d6b26c17ba250bdef450895223efcb6c3646', 5, 1, 'MyApp', '[]', 0, '2024-03-15 15:00:47', '2024-03-15 15:00:47', '2024-09-15 15:00:47'),
('9ee89a0084f158697d702fd856483f803657a807bb804d65c685a1c5c81d934e0c7e7f4bbab45cb8', 5, 1, 'MyApp', '[]', 0, '2024-04-04 18:56:42', '2024-04-04 18:56:42', '2024-10-04 18:56:42'),
('9f008da3b9dc9a7a22096fa628200363e16b11ee29193a37f80ec2c7354be9f4805f6979a4cabac9', 64, 1, 'MyApp', '[]', 0, '2024-07-04 19:42:43', '2024-07-04 19:42:43', '2025-01-04 19:42:43'),
('9f33b5bf2dcdba854937f16818272d5fc42b8bd8c7e0d3d2ee019638f016a1f9759f0621ace7484e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:39:22', '2024-04-28 15:39:22', '2024-10-28 15:39:22'),
('9f6fefa596142226b879e8ab64c33505c9b4f4406a470bedb55e0ee77463b55da9cf717abbb71b54', 35, 1, 'MyApp', '[]', 0, '2024-03-28 16:20:08', '2024-03-28 16:20:08', '2024-09-28 16:20:08'),
('9faa9877438be96defc87fd10439c9d9bab5616c264d4f60d757dbdae53cc348aa365069aff3662a', 5, 1, 'MyApp', '[]', 0, '2024-03-17 16:47:53', '2024-03-17 16:47:53', '2024-09-17 16:47:53'),
('9fad97d7b51efe6964d0067c5d40ecdf93c7ce8de32229f369ac1777467c1cc82b40c96f8bc99794', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:57:33', '2024-04-22 18:57:33', '2024-10-22 18:57:33'),
('9fae73caa9d7a2583403265bad0c8d8c8384bceaca4e6b5e9ab929bcb1d873dfa15706f85ec06ec5', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:47:48', '2024-03-20 17:47:48', '2024-09-20 17:47:48'),
('a01267e1251088aedece93b076b7194f4fde6b0206d6900f426480b85aaf7471c7cd536018ccb61e', 35, 1, 'MyApp', '[]', 0, '2024-03-24 17:49:50', '2024-03-24 17:49:50', '2024-09-24 17:49:50'),
('a016643137f2dc9ffe9edf39ff1fdb445858e9912c33aad763fd77e27aab45bfb40d11f807283032', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:11:16', '2024-04-24 17:11:16', '2024-10-24 17:11:16'),
('a0210042ac044986416d6b51a89207218ac9ff0933528b43ab8c041edca1fc24209d122a0cfee043', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:59:13', '2024-04-15 15:59:13', '2024-10-15 15:59:13'),
('a0767ae3724e9caa9ed11a98d303b6ddbaf6fc861b922154f6e6ffa36260b819e3db839be29b0380', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:52:21', '2024-04-15 16:52:21', '2024-10-15 16:52:21'),
('a095de8c6c2604bbfa9f92e6fac3f826551d90580c5153d99320631f09f2457f437b51229252a685', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:48:18', '2024-04-28 13:48:18', '2024-10-28 13:48:18'),
('a0b9cc9a6afb9c95370b0e9885b2deb00b8c4b53a30ee09909f65f5a697bd961eb08e55267a17b7f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:33:24', '2024-04-18 21:33:24', '2024-10-18 21:33:24'),
('a104583b03115e8e3dfc5269adf908f0b9315ad29a536b8e58ed45aee4d83171298d64f5f29ac070', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:26:28', '2024-04-29 11:26:28', '2024-10-29 11:26:28'),
('a1a7a97510f30ab6edbe103779e532b2d7b2d04f01fe5099dcd821df3a31cf01761c5b3f483af580', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:51:32', '2024-04-29 15:51:32', '2024-10-29 15:51:32'),
('a1ec13dc68dd11d1aa52373ec244438619cb6fa27e46cb4126b66320a7cf6a964e12a0d0a302aba4', 30, 1, 'MyApp', '[]', 0, '2024-03-19 10:17:11', '2024-03-19 10:17:11', '2024-09-19 10:17:11'),
('a1ffdcee5bcde5df67465f6305bd7036ecc11da1d4dc3802e79e24c6f3e19df9aa23a48c8d6c0dfd', 38, 1, 'MyApp', '[]', 0, '2024-07-13 13:54:15', '2024-07-13 13:54:15', '2025-01-13 13:54:15'),
('a21eaa18213b0f5a44201d8b1e766366797f458c550a5feec17f319bc7a9a71df2bf7dbcea315470', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:29:28', '2024-04-29 14:29:28', '2024-10-29 14:29:28'),
('a27422cb7f64a7a7d19e28658c10f09353fc859e59e872bdbd6987e4790368b1d5f8e8d44c7bb419', 54, 1, 'MyApp', '[]', 0, '2024-04-30 16:42:22', '2024-04-30 16:42:22', '2024-10-30 16:42:22'),
('a297a67517feaae22d79757f9d530d327b34779acd0c7b67aa11a05273063f2a72061cd60c32bedb', 5, 1, 'MyApp', '[]', 0, '2024-04-03 21:01:32', '2024-04-03 21:01:32', '2024-10-03 21:01:32'),
('a2d2f755d13eea490789918df9e107873dc094858ed242559f33f38942edaa65c42d0f91ffcb4307', 5, 1, 'MyApp', '[]', 0, '2024-04-22 16:56:41', '2024-04-22 16:56:41', '2024-10-22 16:56:41'),
('a2e53a802309be3134912e4b286adafb649d43401be0178f4f4891f3cc752924e85b9dca5c82fe35', 5, 1, 'MyApp', '[]', 0, '2024-03-19 12:35:24', '2024-03-19 12:35:24', '2024-09-19 12:35:24'),
('a329ad07ac34aac1656588a4ab02b828f43d6eafd6f11d307f63a78dcaf838bceaca7c333571f46d', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:09:09', '2024-03-26 03:09:09', '2024-09-26 03:09:09'),
('a359065aa0e1db30ea8c74d05ce755bf6bb479ae57131a880101b29d8a15fe355e1cb27cf3e5ce7d', 5, 1, 'MyApp', '[]', 0, '2024-03-06 12:49:50', '2024-03-06 12:49:50', '2024-09-06 12:49:50'),
('a371481e2f11b3028bab65fc32bbf1b20c137a8faf68e169a1ef8165e1c8e4e9025eb7fc3868b2a5', 5, 1, 'MyApp', '[]', 0, '2024-04-15 15:38:37', '2024-04-15 15:38:37', '2024-10-15 15:38:37'),
('a38c5a215355927c4313c4b3cadf5fadc5b1bb900f3a0613e976f80b52f6d46d6efdea317a772cbb', 2, 1, 'MyApp', '[]', 0, '2024-02-19 17:31:02', '2024-02-19 17:31:02', '2024-08-19 17:31:02'),
('a436ef94fbaa27f66ae83a8302c9ae1210133ccf19b2d170c96998089770eccb94940016ba51d1d7', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:54:04', '2024-04-22 18:54:04', '2024-10-22 18:54:04'),
('a44078e1cf0fe0dee84ade641cb9a4294b49eaef67433cfa61c85d05c1c62a3033c08b26c221c639', 58, 1, 'MyApp', '[]', 0, '2024-07-02 14:38:35', '2024-07-02 14:38:35', '2025-01-02 14:38:35'),
('a44169a7c5c46f3322a190c91c9f459da7953a95e27b66950cff03b26d6bf3fd8f71fde6b361135b', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:04:21', '2024-04-23 14:04:21', '2024-10-23 14:04:21'),
('a45f040ba2a743b7415e18e0866492cd103d2dec71b7eab83da4de04d03e2a8cb020625f7137df91', 35, 1, 'MyApp', '[]', 0, '2024-03-26 17:35:07', '2024-03-26 17:35:07', '2024-09-26 17:35:07'),
('a4629fe52a988c8ffeaf7e24c05339680ea698c87c45fafb8f3f853a97ba284b548e167509ceec78', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:30:04', '2024-04-21 16:30:04', '2024-10-21 16:30:04'),
('a46c709c8840083d509fe08e021361aee159ee1992ae393f3859fc3f72c4d61cd176c15a4d635754', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:08:28', '2024-07-16 09:08:28', '2025-01-16 09:08:28'),
('a492ec10abaaf49a846a035c56d4d48ad8e6f3899da30be0c59a574eaa6e67135b804c83fa6c219e', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:26:53', '2024-03-27 12:26:53', '2024-09-27 12:26:53'),
('a4951be35fb39d1fcb0aa867b1d9cc15135b2999eedcf9c445f36af8acd001198c80dee64330fb83', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:09:03', '2024-04-16 18:09:03', '2024-10-16 18:09:03'),
('a49c48882f5d547e3bd3837cf770a7eb8c2a058a389ad8ae8947a292da4a1318028fe94cb62228d1', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:23:22', '2024-03-27 13:23:22', '2024-09-27 13:23:22'),
('a4ed39c4a4037f08f639ad56c33b710b7072a6b21df7e17fa3c27cdf7c00d33a35c5f6fc70008b26', 62, 1, 'MyApp', '[]', 0, '2024-05-30 15:06:17', '2024-05-30 15:06:17', '2024-11-30 15:06:17'),
('a4f57c097cde90ae688275263fe7d6f20ad7292e8afb5c894ed89db5ac35a30dbae2d4dfd72a28d9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:02:02', '2024-04-22 18:02:02', '2024-10-22 18:02:02'),
('a52484a83c3a8c59cdfb7afc6a0841afe55dbdc488ada35c05d6da605e9f4c9bc270200b6a695d94', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:07:39', '2024-04-17 16:07:39', '2024-10-17 16:07:39'),
('a53380b0a18ce5b9b18a5561cb25fdf2936897e0c3e22e8ba9394bc48d1df1fed931ca02399041e3', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:32:46', '2024-03-27 13:32:46', '2024-09-27 13:32:46'),
('a5590880dfafaf1d40670a5e4b8dea04fcd726dd97fb24b725e98337d2986519661d0c42d3d2ca0f', 5, 1, 'MyApp', '[]', 0, '2024-04-30 16:40:01', '2024-04-30 16:40:01', '2024-10-30 16:40:01'),
('a59e8bca7e53d9f7c953c20585a61aac2592f7998852d7f7c23c2d9c22af0125c497ab8843076ba1', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:28:26', '2024-03-31 14:28:26', '2024-10-01 14:28:26'),
('a5fbe2ae734fa1a948556cf0d9acefbf3fd2a07680067e3ce8fe42fd37eb153929b563eff1181444', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:54:29', '2024-04-18 20:54:29', '2024-10-18 20:54:29'),
('a621f2f7d07fc0f34ba0aba8b63145c96303cfb718f66d54b820dc6ee435a779b4881c7fe0a3c594', 35, 1, 'MyApp', '[]', 0, '2024-03-28 18:46:11', '2024-03-28 18:46:11', '2024-09-28 18:46:11'),
('a662c80d282b5cd8700770bb8f5ed66c04273945cacf67c2a1a38cd5c2ad79f4e7bd285dc11796b9', 2, 1, 'MyApp', '[]', 0, '2024-02-13 18:02:19', '2024-02-13 18:02:19', '2024-08-13 18:02:19'),
('a6739d460774c280c409aa49ff31c0b559b0428046fb83f3ec2a4ea88e18ff7bcda2bdfb053eb6a2', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:08:01', '2024-03-26 04:08:01', '2024-09-26 04:08:01'),
('a68abe4f12d92053886fa9dcf3a23f96fc5d6a066aad43517a2fd173fdde82a622b4fc8d50347e7f', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:41:41', '2024-07-13 17:41:41', '2025-01-13 17:41:41'),
('a6929076d3fdc6307f3e39ce600016c5c5be27db1097987d015e71a2fb7d3ec8b83c6fa1fe2e1927', 5, 1, 'MyApp', '[]', 0, '2024-04-30 17:30:41', '2024-04-30 17:30:41', '2024-10-30 17:30:41'),
('a761dc42e46d2d0633d49a702d83927da005d1c6135a75297403ab10b8f1d8b5426154afb68b59c1', 5, 1, 'MyApp', '[]', 0, '2024-03-20 04:15:59', '2024-03-20 04:15:59', '2024-09-20 04:15:59'),
('a78da63c721fec5ac28873cf5d22198e5b775f7359e91abddec133b2fdee09558bcbf37e3dbc2270', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:09:42', '2024-04-18 19:09:42', '2024-10-18 19:09:42'),
('a7e2fffc1457fdae82dd252de1b4cd0e458bb48eeb9fdcf5a6fe28a4ac02516159ecd1f0caa355d8', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:50:43', '2024-05-02 10:50:43', '2024-11-02 10:50:43'),
('a7f8d330b539c622f338af24bd79f846292c6df54c27f812e858bdc3fd7f76c7cfbbf1c8f32b0423', 5, 1, 'MyApp', '[]', 0, '2024-03-24 05:05:02', '2024-03-24 05:05:02', '2024-09-24 05:05:02'),
('a82a0ba5d6d3d885461f4735e0d21beb5a8c437cec66b9b04ed84c51a4f96ee601794e7835fe9de3', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:25:06', '2024-04-23 14:25:06', '2024-10-23 14:25:06'),
('a82f7a3996b52ea7a4f2b64765d6abda540f58b78e0c516fb50f1194301da62016a96a097a0c73ef', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:46:00', '2024-04-16 19:46:00', '2024-10-16 19:46:00'),
('a83af6af3d93372e8a0c5bd2fc40a86a5a81496031952fd0cf3b9465085427f52d3167b82433d4e9', 48, 1, 'MyApp', '[]', 0, '2024-04-16 11:57:22', '2024-04-16 11:57:22', '2024-10-16 11:57:22'),
('a88f09476264303b4c7c964befa05502e960acef0fcb93846a40451e5790ee4af1d4971bb8fb4435', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:20:08', '2024-06-05 13:20:08', '2024-12-05 13:20:08'),
('a8aea808fe0be6fb086c5bfbc71613951b0aae6b37cbf33359805280a98c7a0eacaf7049a94b57e7', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:40:19', '2024-03-20 03:40:19', '2024-09-20 03:40:19'),
('a8b0b075621e8ed1e1c6e263425fa1d5d4aff0f81d980138bcc3aa3f80c96e80e0b57f0dbbfbadab', 5, 1, 'MyApp', '[]', 0, '2024-03-05 16:11:52', '2024-03-05 16:11:52', '2024-09-05 16:11:52'),
('a8d8c3198c4a40f8f01cbd7391265904958c01a9372e72bf06d4cbe0ab5b10bf4b86ac7f7595e3df', 5, 1, 'MyApp', '[]', 0, '2024-05-01 18:56:57', '2024-05-01 18:56:57', '2024-11-01 18:56:57'),
('a8f9e1bb3363a8e5ecdc82775b36e2f7b40329fda757da7b5cafe7941a1096223592828dbc058f7d', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:13:32', '2024-04-17 13:13:32', '2024-10-17 13:13:32'),
('a9700d5b3fa857c503db483c0c41321d40d326afb1893b2813c52cd2250804c2fc81307236dbbf51', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:11:20', '2024-04-23 14:11:20', '2024-10-23 14:11:20'),
('a99c735c25551048ebc5a1bff7c1a219fe7eb8b1ebdae6236146fc6a58051720e28adf95a29db7bb', 5, 1, 'MyApp', '[]', 0, '2024-04-29 12:56:07', '2024-04-29 12:56:07', '2024-10-29 12:56:07'),
('a9eb75cc9d3b3c9c61fcd30466f09a7fe8d437093751fc69be2bed1ab7ef5a0515e17b434f088117', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:46:00', '2024-04-16 19:46:00', '2024-10-16 19:46:00'),
('aaa73acaf9d07dc0f9c4253e0c0f62f766616177d39c6ffe63a6e9ac2bb4366d4975032d1ce51188', 12, 1, 'MyApp', '[]', 0, '2024-02-18 12:30:53', '2024-02-18 12:30:53', '2024-08-18 12:30:53'),
('aaaddd3d11ccdd57b98d04edd505e5f5e7c3f4cc3120c594181daa702d8e0026accf5e10b7701c6a', 5, 1, 'MyApp', '[]', 0, '2024-04-30 14:32:37', '2024-04-30 14:32:37', '2024-10-30 14:32:37'),
('aab62f13239b10affd895f49fd62a1344c48a0a868da96141312003a243dd742ac6eda61d8fcd3ba', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:21:47', '2024-04-22 19:21:47', '2024-10-22 19:21:47'),
('aab7ac4bb704f52ab85159a98ee6114560f975afb5f088d9d89a6192d3fc0f3ad3339438a873b387', 74, 1, 'MyApp', '[]', 0, '2024-08-04 12:03:39', '2024-08-04 12:03:39', '2025-02-04 12:03:39'),
('aacf8517a0f4aaa54cbe8d8f6dabf8b9a9d608c174769b1c827380f3cc7a5ded4184ff8fb06522a4', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:22:54', '2024-04-29 16:22:54', '2024-10-29 16:22:54'),
('ab16ff14297a1d7c0e2af25f10801d137914d52797e56b49b92f3c17dc6d07e9f8dc31738c089869', 5, 1, 'MyApp', '[]', 0, '2024-02-18 13:53:41', '2024-02-18 13:53:41', '2024-08-18 13:53:41'),
('ab198e43ab78ca1c0bebb24ca4d0ef110874443444b2b41a99ea69131f248382172b1749eb455b7a', 58, 1, 'MyApp', '[]', 0, '2024-06-05 14:02:16', '2024-06-05 14:02:16', '2024-12-05 14:02:16'),
('ab22a647fefafa7e185df96f9d046097bb3e49590b1d5837065ad09de9e7250954c9088d5fef82f7', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:06:08', '2024-04-28 14:06:08', '2024-10-28 14:06:08'),
('ab2518a417f70e55fbd14969206d250cf9c4b94dd892a1cab83378c469c28597388a2b5c990a34e1', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:57:33', '2024-04-22 18:57:33', '2024-10-22 18:57:33'),
('ab5c5f087fd8dadb082d72a2c9288fe4cd7ff08ef1e5ecfb1c098790ef0b6944571a75cb6275b11d', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:00:20', '2024-07-13 15:00:20', '2025-01-13 15:00:20'),
('abd686d75c0805f72f348ef7078ff72e3217ad26c205b0784c6dcf4c44db54899e2254fbca3f2255', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:48:33', '2024-04-28 16:48:33', '2024-10-28 16:48:33'),
('ac121ffb7e644bd00b0d2d5476a4a07bda9cecd339df6f73b36594355e61d7d183da0cbe95cf6b0e', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:40:19', '2024-03-20 03:40:19', '2024-09-20 03:40:19'),
('ac1fa8722f1f24704d461f40677da0ff5747c424306022711b71bb5394a496d2c740ab764bea2a8c', 38, 1, 'MyApp', '[]', 0, '2024-07-19 12:59:53', '2024-07-19 12:59:53', '2025-01-19 12:59:53'),
('ac3fcd0778fbdb8b38e988ce81269778f902b3c7c0a3802b4ddcf99b21c40236d7f0adea43f265ce', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:30:19', '2024-04-30 15:30:19', '2024-10-30 15:30:19'),
('ac40e040dbc6e9e59ab430c924f05b6c8019f45331631d1adde4cb1b0b37d3f9c81b05550e34a3ab', 72, 1, 'MyApp', '[]', 0, '2024-07-17 12:53:41', '2024-07-17 12:53:41', '2025-01-17 12:53:41'),
('ac5f1da03f7b62725da840237b7034d2670255febe4b1e0b5e9dbabcc655aabc904b3bc215d0808e', 10, 1, 'MyApp', '[]', 0, '2024-02-15 22:05:56', '2024-02-15 22:05:56', '2024-08-15 22:05:56'),
('ac7b133911f33a020ec11ce2e10822cec4c5c281c97ee1b8ac50b246c368c531b98a1d41b69d814b', 58, 1, 'MyApp', '[]', 0, '2024-06-06 12:43:24', '2024-06-06 12:43:24', '2024-12-06 12:43:24'),
('ac8e30c9c1f12c704ed794a7442d98bbf299ba6f633c74df67e26cf9c5d33f92810babd04a4b0efd', 58, 1, 'MyApp', '[]', 0, '2024-06-02 13:36:59', '2024-06-02 13:36:59', '2024-12-02 13:36:59'),
('ad04d3ee3dbf2fa23769bc6675ec95882a556a8764b2ed6ec74f3214ac656ffd26bc0d43cf97d042', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:40:09', '2024-03-27 12:40:09', '2024-09-27 12:40:09'),
('ad111e30249b57d864b2fd37030b647c9046377301dd5a42b63da444bec048a5819baa62768d397e', 5, 1, 'MyApp', '[]', 0, '2024-04-07 12:41:24', '2024-04-07 12:41:24', '2024-10-07 12:41:24'),
('ad16c55ca0581f6317d9271fae64ab3781d7cf34d99c9e5df8d4c99b6506f3d35522accef3ac1cd7', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:32:24', '2024-04-16 19:32:24', '2024-10-16 19:32:24'),
('ad2898b56c0bf4e3d7b9e7c6856306ff94aa3263b8ad9de077ff7fda4d433c66fe848d56d884b8ec', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:38:49', '2024-04-23 13:38:49', '2024-10-23 13:38:49'),
('ad4212ccc4661bb2179d649302f9fb8bc457443408ff06381ace3dd6aa985a01c271c61699f4b733', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:06:18', '2024-07-13 15:06:18', '2025-01-13 15:06:18'),
('ad56073dc740778696f1b24cbc6ffeeb1812e152a80fe7e1c14907eef27e1b93a63c7c36904a2e96', 69, 1, 'MyApp', '[]', 0, '2024-07-26 13:16:21', '2024-07-26 13:16:21', '2025-01-26 13:16:21'),
('ad5eb14b00a0e70d205110f639c1f9f220328986ee093ec75c4521f7a6706a290cf01a82167bfc51', 5, 1, 'MyApp', '[]', 0, '2024-03-05 14:09:07', '2024-03-05 14:09:07', '2024-09-05 14:09:07'),
('ad60454e5d71e0b0289811842288cce9e35d6784d4743083cbb9a350e26b70c4fef2f87c3eb85d11', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:43:42', '2024-03-24 17:43:42', '2024-09-24 17:43:42'),
('adce210ffae69dfb486d7aa4a84291a47c5938330b9dc113488c789b755b65b853d06bb43d6ed85b', 64, 1, 'MyApp', '[]', 0, '2024-07-07 16:22:10', '2024-07-07 16:22:10', '2025-01-07 16:22:10'),
('add63d56545279222bfa8099ac94d8a06e3f62fa261466eb37b0fe908a04ae861128585940db2d55', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:46:08', '2024-03-20 17:46:08', '2024-09-20 17:46:08'),
('ade893cca08cb8f33f4d5e32b9a32d44ba8926b25122a6bc8f6796ee694d3c4282e92a744e9011c7', 58, 1, 'MyApp', '[]', 0, '2024-05-13 11:10:40', '2024-05-13 11:10:40', '2024-11-13 11:10:40'),
('ae3d1fcd4fbc4c66797c0d9fe659841f273a9948760bedebaa420e282c932c50846d1fc6124552d3', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:11:00', '2024-04-24 16:11:00', '2024-10-24 16:11:00'),
('ae671faad0fb505581f7500dde9f0f3f08164cde8a76e0bba6e7164a3f0b16712b11d318dc8b7f9a', 62, 1, 'MyApp', '[]', 0, '2024-05-30 14:51:32', '2024-05-30 14:51:32', '2024-11-30 14:51:32'),
('ae67cb6988b7ff88089fcde0ea99c413aba324e64f76f08d14af08a09f1173a5c5d986e7acc00bb7', 58, 1, 'MyApp', '[]', 0, '2024-06-27 14:02:59', '2024-06-27 14:02:59', '2024-12-27 14:02:59'),
('ae8675321a21fe775e5f4148f0884fcba303ff82872463df388a494d1fe80180694a2de41732fd27', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:10:58', '2024-04-24 16:10:58', '2024-10-24 16:10:58'),
('ae97f599e18287e319129bcab21e6ed5a1775870ed9d3a747818ca392546c3662d1bae54141a3bbc', 58, 1, 'MyApp', '[]', 0, '2024-06-27 13:32:24', '2024-06-27 13:32:24', '2024-12-27 13:32:24'),
('aeb4d50dac1bd45056da6b7f80160b1381a9c547807dcf0e57e9dd7a86adcb5c71ffdf99dd855976', 5, 1, 'MyApp', '[]', 0, '2024-04-23 15:22:28', '2024-04-23 15:22:28', '2024-10-23 15:22:28'),
('aec03daaabf35c983d4cdb9cc6ed3cac3bdb2ac3ca958490d26da09fd97bed08d5b7c27afa448b30', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:31:31', '2024-04-23 14:31:31', '2024-10-23 14:31:31'),
('aed2dee353918da830f45ef656276a0a0f0e3d88817f4eeb0f8ef0481b4009181fbc58f5bdccfd17', 71, 1, 'MyApp', '[]', 1, '2024-07-16 10:44:20', '2024-07-16 10:44:20', '2025-01-16 10:44:20'),
('af0a5d5f5da4ae32d80f808e35a2f34d63c71a3228eea86182cce2af5d026d740a207fe370428ac9', 5, 1, 'MyApp', '[]', 0, '2024-03-19 14:53:07', '2024-03-19 14:53:07', '2024-09-19 14:53:07'),
('af1c79de9b91afc103595860022cea9b1d576062c19adc9758c38cf2a012460abeba295d898764b6', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:20:31', '2024-04-23 13:20:31', '2024-10-23 13:20:31'),
('af3a266a6c895235f6fb9f81205bf39a0b261c76c166a2e10d10d69bd9d77dd98db845274f26ea94', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:12:25', '2024-04-22 19:12:25', '2024-10-22 19:12:25'),
('af53f02357050e0b171d6fbecaf6e5f2f1ee920b1305b8357c83bf297b0f0b23c9aa33cdaa649387', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:06:33', '2024-03-24 18:06:33', '2024-09-24 18:06:33'),
('af68db44c4db6cbd2a90c07c87a08ea4cb5c56b35a28b47b079dd4eb4e236464b4a8b2ed2a4f2e5c', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:39:10', '2024-04-17 15:39:10', '2024-10-17 15:39:10'),
('af903b5dd25ba38492028752e61e493395d255a3f05d5f747381b4e94b6675a238fed509f67b36de', 35, 1, 'MyApp', '[]', 0, '2024-03-26 18:16:13', '2024-03-26 18:16:13', '2024-09-26 18:16:13'),
('afcb99a78e785277ddb9092c8d0bc917dffb0dd2569f96649141421622b804395604b56ec88733cc', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:43:51', '2024-05-14 10:43:51', '2024-11-14 10:43:51'),
('afdfecb37c3863456b7447d763457e2a897c323f90d9a6586ea8fbc7549c05e59230ffecf8be7c47', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:25:15', '2024-07-12 15:25:15', '2025-01-12 15:25:15'),
('afe2bd603f958559f687792cf4d376f862d9cd1beb53b067364f704ed12eba993a2dc6c048e83672', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:32:56', '2024-04-18 22:32:56', '2024-10-18 22:32:56'),
('afe30efb59c4d14093c5acc9ea9ae8b85d619a4bfa31231a09eaa1263503d297723bcca0f295b2e7', 32, 1, 'MyApp', '[]', 0, '2024-03-23 16:48:28', '2024-03-23 16:48:28', '2024-09-23 16:48:28'),
('b05ea606c1656ce7b48299ef50d7163db09c0fcdc7893cbdfc6bdbacdebb634b80ec6fa02708b50b', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:02:53', '2024-04-18 19:02:53', '2024-10-18 19:02:53'),
('b07d60f3fd1198da1de09ff50baa68e90af92fd80fe94338db14910ff3126d12ce5643dd08e938fd', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:23:18', '2024-03-26 16:23:18', '2024-09-26 16:23:18'),
('b0d065e8fcda485e5f8c61ce4eb0f901cb3314500d036079bc6db44b73b3067bc11902c3aee4fd0e', 5, 1, 'MyApp', '[]', 0, '2024-04-16 18:35:00', '2024-04-16 18:35:00', '2024-10-16 18:35:00'),
('b0e4ec975478cdf16452445624bb0d7d2051d61d6b36babff61a69713e5c10f3d7ce404818200d4d', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:16:15', '2024-04-29 16:16:15', '2024-10-29 16:16:15'),
('b0f25e96351889a3c61085357d93b6df001afa0a31bb86d03c0801ddffa053b76f75ae0e51627cb9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:39:09', '2024-04-22 18:39:09', '2024-10-22 18:39:09'),
('b0f4ee66996acfb1d4f02901f84429e609ed9505a0c8a46edc6dbaf950a8b578fb1ab66dab18ea8b', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:14:00', '2024-04-24 16:14:00', '2024-10-24 16:14:00'),
('b10b3acf3517b8a1be2506c73a4469bda3c2638bae47ff9ad1d3b01694543d381b27bee9244b8c0f', 20, 1, 'MyApp', '[]', 0, '2024-02-26 10:46:47', '2024-02-26 10:46:47', '2024-08-26 10:46:47'),
('b124eb08b2f090adbb49650a422bfca3965c8fff7967ba4974f066719d2d4fa8e0d3197c7de06511', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:15:52', '2024-04-29 11:15:52', '2024-10-29 11:15:52'),
('b1b449cb4fd342fa7d2419f85527d5eecdb6362c9c2ceda0790da3c8bad7641dcce616d96b08aba9', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:31:00', '2024-07-12 14:31:00', '2025-01-12 14:31:00'),
('b1f5336ada9c22bc16a06015047b59e3fecf84fa0b45c42baeb926a2ec97b1a75152b0baae29fba0', 5, 1, 'MyApp', '[]', 0, '2024-03-21 18:16:59', '2024-03-21 18:16:59', '2024-09-21 18:16:59'),
('b22c817591d971c8d8f8d04eba6c5bb862bfdfdf214555e91b8501c3e94e2a1944d4dcc0b2945fda', 35, 1, 'MyApp', '[]', 0, '2024-03-28 16:08:08', '2024-03-28 16:08:08', '2024-09-28 16:08:08'),
('b25f833299deb806bab79cb3b237d47e3c0ff5491358615cce5e7dbcb1dbfd86f3490c882cf52c16', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:10:16', '2024-04-24 14:10:16', '2024-10-24 14:10:16'),
('b28258a68aece7ecc2997efbc2fee73ffd916afa3e7db0d3a93a4ab4b4918d7618924789e87f5f11', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:09:50', '2024-04-25 18:09:50', '2024-10-25 18:09:50'),
('b2854b981bc33e7d3fbd21b824482b8a675fa3057606a3d8e1a863744c5f83055db4ae48a19f9548', 58, 1, 'MyApp', '[]', 0, '2024-05-13 13:02:18', '2024-05-13 13:02:18', '2024-11-13 13:02:18'),
('b2de347eb60e44a4b15478b0da781be4cfe576e0ac2a818091abc73567344a2e8968566b2111d378', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:58:41', '2024-04-28 14:58:41', '2024-10-28 14:58:41'),
('b2df903b91fe7739c433ae78c26df21e7f4f74d1cb41605d7473f051e9e081e8b6aefe0cfa76cf55', 35, 1, 'MyApp', '[]', 0, '2024-03-27 15:39:31', '2024-03-27 15:39:31', '2024-09-27 15:39:31'),
('b2e61676c93c74f380002f0d3a818f635806771f83858c9505f328543ee902d748170b658721e2c0', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:42:26', '2024-04-16 19:42:26', '2024-10-16 19:42:26'),
('b2e6860bbaecb2dc12545884aa7e11cfc696e9cef238576f74c38b87eefb7ec6a5c6f9a8d636a00d', 69, 1, 'MyApp', '[]', 0, '2024-07-23 14:27:02', '2024-07-23 14:27:02', '2025-01-23 14:27:02'),
('b2f0843fd2a81376c85d79bb5d8deb0f93cf72cf1b3137f173bb37996995c566641a67fdffc34fe7', 5, 1, 'MyApp', '[]', 0, '2024-03-19 12:42:20', '2024-03-19 12:42:20', '2024-09-19 12:42:20'),
('b33d03826f5346e508c30d2a3f449b31572ffaf0cc9697419493a99a8d1dcaeb326d29dff3ea4083', 71, 1, 'MyApp', '[]', 0, '2024-08-01 10:55:05', '2024-08-01 10:55:05', '2025-02-01 10:55:05'),
('b370f56ff9abc1bd6c840875125bde95cea2986badedae88933aabf931bb48554f497281e1a3369b', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:37:40', '2024-03-27 12:37:40', '2024-09-27 12:37:40'),
('b3749d5445dd9ff9aedc1f93ed916e5deee962ae1702b2878bfb30c6a44f5050fb24b9ddfedafb52', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:39:35', '2024-07-07 16:39:35', '2025-01-07 16:39:35'),
('b3b0d25ab2715cafd0c5d294a9d1692c056142cb53c7092676b7b4c2034208dcdfd14d99475936cd', 5, 1, 'MyApp', '[]', 0, '2024-03-04 18:50:38', '2024-03-04 18:50:38', '2024-09-04 18:50:38'),
('b3bdb0e8689cfe4ea1b600831f456c2d753743e05bc9a1fcb94f0ea004ff4c8a358d86921d88e4c1', 58, 1, 'MyApp', '[]', 0, '2024-05-21 15:15:40', '2024-05-21 15:15:40', '2024-11-21 15:15:40'),
('b3cd54d672cdf30a72ba01d5a6aa1571cb0374b8a1c6722fe437748723267209f4fe3b0d5e12bf2b', 35, 1, 'MyApp', '[]', 0, '2024-03-27 14:10:07', '2024-03-27 14:10:07', '2024-09-27 14:10:07'),
('b43298dcea0f3275d3ed360b7c9dd9b35210d56a16a28a2c9799c785d7cfca08ecefe848591ff848', 5, 1, 'MyApp', '[]', 0, '2024-03-05 18:18:54', '2024-03-05 18:18:54', '2024-09-05 18:18:54'),
('b4404bb762d76e5e7d142a1cc9792730a13be37814e62825210b9c510b5ee9b0a599cda10133749f', 5, 1, 'MyApp', '[]', 0, '2024-04-22 16:38:45', '2024-04-22 16:38:45', '2024-10-22 16:38:45'),
('b4c1b32c64928cb0304561c23bb8718023a207001f016c725360148f7843d317866765d1d4b8e1a2', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:16:26', '2024-04-14 16:16:26', '2024-10-14 16:16:26'),
('b4cdbb4ec1ab5a2d9547464884427d084dcd00adda5b282cea1b4c36c717afd810622ac24222acbd', 62, 1, 'MyApp', '[]', 0, '2024-05-28 19:19:33', '2024-05-28 19:19:33', '2024-11-28 19:19:33'),
('b4f865b228d50a925e086d2f9ebaa91840b0537ac11c82b3264668b4e4e3f064cadd0f0a140397db', 5, 1, 'MyApp', '[]', 0, '2024-04-17 12:58:59', '2024-04-17 12:58:59', '2024-10-17 12:58:59'),
('b55834cc78fc0bcbece7d1354f5e87de85b3c736de5c7265e48d4e456388a5c269aa607d876fb348', 69, 1, 'MyApp', '[]', 0, '2024-07-19 12:58:08', '2024-07-19 12:58:08', '2025-01-19 12:58:08'),
('b560669278daef66efd6d063da3c4a7d4432fe9b0c73223dc79c74c5a6e9e98938fe2cdaca44a49c', 63, 1, 'MyApp', '[]', 0, '2024-06-03 10:10:46', '2024-06-03 10:10:46', '2024-12-03 10:10:46'),
('b568d238ccafddd6e1d42514a16b2b46d00088f31c2d47faf15cc0aad587ec8d8414d1abfc251b12', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:36:27', '2024-04-22 18:36:27', '2024-10-22 18:36:27'),
('b5777692425b8c2fe88c4d86833c17281a4b3d941fe408e4661103394057c0f52752d5eaac986705', 64, 1, 'MyApp', '[]', 0, '2024-06-05 12:41:29', '2024-06-05 12:41:29', '2024-12-05 12:41:29'),
('b5986e615e6f1e8c862b3bc0ac28c19fdaf43c2b0008e21e59822b009ab95568768c11e44fa10ab1', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:20:27', '2024-03-26 16:20:27', '2024-09-26 16:20:27'),
('b5a68c3cb832fa357bedf25113db3ee0c044c918a14a0b48385eaa2c9a67c2c0f7a877d1411bf303', 5, 1, 'MyApp', '[]', 0, '2024-04-22 12:47:24', '2024-04-22 12:47:24', '2024-10-22 12:47:24'),
('b5d59b94428f32bf5672ef7c777c0ec2bbbd6328d08d47a1447bbca654996f53aba88bce4cd42e23', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:23:13', '2024-07-12 15:23:13', '2025-01-12 15:23:13'),
('b5d82361150f86f4664c266f68d434a241649521889918b910e6e8b006f72ab0f43e78763a8e936c', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:53:11', '2024-04-18 17:53:11', '2024-10-18 17:53:11'),
('b620d55e000629fcbe8723088a54f353b7862ec81c67124170f8974c1e2c923887040cefbe9a5c8b', 5, 1, 'MyApp', '[]', 0, '2024-04-07 17:42:31', '2024-04-07 17:42:31', '2024-10-07 17:42:31'),
('b62d17ff2c5d17d1465cf4ff27103a48d2683e601d2426282cf5d30824f18434c9fbecfcf0a8f160', 35, 1, 'MyApp', '[]', 0, '2024-03-31 04:58:50', '2024-03-31 04:58:50', '2024-10-01 04:58:50'),
('b6753158ec654b04abf05702834b4fae80c63c7fb476ae2e0019c1052edc21dd1a635a0ec268e3af', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:31:08', '2024-04-18 22:31:08', '2024-10-18 22:31:08'),
('b6be4c30d56a573f435710c93281ec299baff3f2d53187703d07de1c011bbc7c08b94cacb6543d3b', 71, 1, 'MyApp', '[]', 1, '2024-08-01 13:39:36', '2024-08-01 13:39:36', '2025-02-01 13:39:36'),
('b6ddab30973a80194baa65a769d365a2fb8abfa1ed6e4acbebc11730c973fbe9ce22e759ead04a8f', 58, 1, 'MyApp', '[]', 0, '2024-05-22 10:43:10', '2024-05-22 10:43:10', '2024-11-22 10:43:10'),
('b6e3faf2fb3ac5e2f78b06643494595369e1a64cf8ef67bff5155fb20df1f5a427d1a0bf252d0f09', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:08:28', '2024-04-29 14:08:28', '2024-10-29 14:08:28'),
('b702b080a997238a5508f906569aac055a130645f6f04606daf6320dd098df9194b2729391e3bc55', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:58:36', '2024-04-23 18:58:36', '2024-10-23 18:58:36'),
('b71198749df25b9f112c02c2169156b302089170e893d90f7ebde279cbebd8dfe6c276af3d976433', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:03:09', '2024-04-18 21:03:09', '2024-10-18 21:03:09'),
('b73ff0760f994421c75f6967523e24e6f95fcc20df754ed1807a3d67ef0c9d75c928227309759dab', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:49:13', '2024-04-18 20:49:13', '2024-10-18 20:49:13'),
('b75119f6fa89a9cdc8dad957e524b6ebeb249287c4118a0e6a5648159c66584b831c0e059bef7861', 55, 1, 'MyApp', '[]', 0, '2024-04-30 18:31:53', '2024-04-30 18:31:53', '2024-10-30 18:31:53'),
('b7b0d8feeef759aa158c777c410f392b07f2e0d85bf661d061d733a6a9ba329f39ba9d829fb55f77', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:29:35', '2024-05-13 15:29:35', '2024-11-13 15:29:35'),
('b7d609a43e8edbd3398030188a37590898b7275fba800e90053f33ad142ca9aaeed0b5e5f9b361aa', 74, 1, 'MyApp', '[]', 0, '2024-07-26 17:29:23', '2024-07-26 17:29:23', '2025-01-26 17:29:23'),
('b7d82eb626d7a0b1996886be01b6e25112b974a525caea55aa619851d7abd48148dfebaa43b2721a', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:37:24', '2024-07-13 13:37:24', '2025-01-13 13:37:24'),
('b7ec63c03bf257e284035c4b6fccbf18fedbe0ca9c8e46b2bcad6984aa7be6fc3c21c79e0d58243d', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:39:25', '2024-04-24 15:39:25', '2024-10-24 15:39:25'),
('b8092cf8e598fd8cbbc2335e61647f8a63df00fc5ce43909098770fb4c30902b72e0d3993fa2d8cf', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:29:28', '2024-04-29 14:29:28', '2024-10-29 14:29:28'),
('b84532fa86bd79002a66622ec934dc38d1906f73cbaf4edef0e98d0c741d6bee1cbcbbfcadd2d544', 11, 1, 'MyApp', '[]', 0, '2024-02-27 10:00:00', '2024-02-27 10:00:00', '2024-08-27 10:00:00'),
('b8526d0120fb748c364d5022bad2836f55482a9b4f29b7bdcbba3fec965eaf20542f1052e75d6c9d', 5, 1, 'MyApp', '[]', 0, '2024-04-14 15:58:40', '2024-04-14 15:58:40', '2024-10-14 15:58:40'),
('b856e4b995c753ba60cfc4cc9a932cb072310393828a40731b34c912b582bf263dd216ea6ec95b1f', 35, 1, 'MyApp', '[]', 0, '2024-03-31 16:14:14', '2024-03-31 16:14:14', '2024-10-01 16:14:14'),
('b8743643434c902d089eabde65047cd3ab5a70ddea085a4c472737186232b4260bea777a873b22dc', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:14:46', '2024-04-25 18:14:46', '2024-10-25 18:14:46'),
('b8dee2903ba74e1efe29207847f575670594e7015573485d3d13f390ac9d312ad5b8b73fb32fe37d', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:18:37', '2024-07-12 12:18:37', '2025-01-12 12:18:37'),
('b91655f831e458d25bd0406d96f2a4678db908d01928d4ac1282348989ef4eebaa6e658a2cb3c1a9', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:44:45', '2024-03-26 04:44:45', '2024-09-26 04:44:45'),
('b9289f57cfb62d3a6451b08c933e0eacb5dfdd6b972f63d98b48ed3449972f5fb79ba66725d3f0fa', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:12:53', '2024-04-28 13:12:53', '2024-10-28 13:12:53'),
('b92ed5dc246787cb3133de47734e525b9587685d44bad863b7fbdbf3cb1641bce65c791091eef8cd', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:38:28', '2024-05-13 15:38:28', '2024-11-13 15:38:28'),
('b94d8bac3859a8141c9f872566007b68c4d5122ad63bcff662c94ecf74e9004a95b57eaa47237873', 5, 1, 'MyApp', '[]', 0, '2024-02-18 14:27:01', '2024-02-18 14:27:01', '2024-08-18 14:27:01'),
('b9f51e531aedede9e4d4c69e6e387bd94ccb9277e8e502dd2e5d71baa29d39e707a6109e7b907523', 5, 1, 'MyApp', '[]', 0, '2024-04-14 16:16:24', '2024-04-14 16:16:24', '2024-10-14 16:16:24'),
('ba4cb0ffdc276532b15ce7d7202bf2bb9d84f9106643f359a4be855cf79dbb229a42f5318c89fdf8', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:36:08', '2024-07-16 10:36:08', '2025-01-16 10:36:08'),
('ba4e3e342b3f9b6a62054538062f34f145466ea13c32a9fc8704171efc5b8d536b9acc1e075e0e0a', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:53:50', '2024-07-16 09:53:50', '2025-01-16 09:53:50'),
('ba893c71e49ebc31b68746b38b9f58e03069b0d1fd486d6dc6686719801057c5b2959626dbcedc57', 5, 1, 'MyApp', '[]', 0, '2024-02-21 14:26:10', '2024-02-21 14:26:10', '2024-08-21 14:26:10'),
('baacbcabbe5accbfd71f055b7f42755b895ac654c1ce8a7a6696ef2b21335149b6000aec1f4f3911', 5, 1, 'MyApp', '[]', 0, '2024-04-03 20:50:26', '2024-04-03 20:50:26', '2024-10-03 20:50:26'),
('bab880de482e1a776c401c612e9ed3a45ae420a63f8ef34a03fc982d2360b6ef7f030d74e275ce0a', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:08:39', '2024-04-08 18:08:39', '2024-10-08 18:08:39'),
('baf8bd7da51488ceee8756fd21181f860cdc3875f9af7fc1ea653649d09d47b1ec375e558f74967a', 35, 1, 'MyApp', '[]', 0, '2024-03-26 02:58:50', '2024-03-26 02:58:50', '2024-09-26 02:58:50'),
('bb5bc0aa6b7733ea0b5e850f84a3ee656d1cf6b1b6ea8255f0757421a470a697d7f1cb5d6fd13967', 5, 1, 'MyApp', '[]', 0, '2024-04-15 12:12:59', '2024-04-15 12:12:59', '2024-10-15 12:12:59'),
('bb865975871da63b50b6711794699fb8fcf70022fbe5ba707debb74ccaaf9ce15a6672e74eb1f471', 5, 1, 'MyApp', '[]', 0, '2024-04-23 17:56:34', '2024-04-23 17:56:34', '2024-10-23 17:56:34'),
('bbe08979b20a94fbb7c035a26f1eb430ff4287be782b0a8263e717be1ee264972c58e6bef31633ac', 58, 1, 'MyApp', '[]', 0, '2024-06-27 11:00:59', '2024-06-27 11:00:59', '2024-12-27 11:00:59'),
('bc2d751df653cf73c8108e88ff2d1217b0c18111fe5b104c9e43b126f15481bf1c060ed7472559f4', 5, 1, 'MyApp', '[]', 0, '2024-03-12 17:56:57', '2024-03-12 17:56:57', '2024-09-12 17:56:57'),
('bc4ba492dc35b59df177031b9c39028c362675203d2b9af155d77d142ccf00dc92a1c7028d3a1d5c', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:07:14', '2024-04-17 13:07:14', '2024-10-17 13:07:14'),
('bc71d5939d03d98c5d7c49e72e154f90625a280404416ca7478786782826dd2a74d09668a5eb08d2', 5, 1, 'MyApp', '[]', 0, '2024-04-17 14:17:11', '2024-04-17 14:17:11', '2024-10-17 14:17:11'),
('bca4676845ad8513a898eae633b205a7c29f82070e9b19727664f02c11849f220c2e6b59ccd488e6', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:41:40', '2024-04-08 15:41:40', '2024-10-08 15:41:40'),
('bca8683c93a84bb926c0c84861464b52e5185ff88aeb5456210b8e403cf20d93fbf97d0097a7f153', 66, 1, 'MyApp', '[]', 0, '2024-07-13 16:47:50', '2024-07-13 16:47:50', '2025-01-13 16:47:50'),
('bd67b916b1081d5b63686bba7ae5e174ca24462f7c49c8c7300594172269b081a8bd5d28292b6b88', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:49:37', '2024-04-18 20:49:37', '2024-10-18 20:49:37'),
('bd903e457bcff64e9d63f9a3a1d28c433f1cd9c966ce0be13540d07d6cbb5f99c20230122e66df83', 58, 1, 'MyApp', '[]', 0, '2024-06-05 14:02:16', '2024-06-05 14:02:16', '2024-12-05 14:02:16'),
('bdb0a7190ae2d980be1e410db80e4a04312b8e9f071ea60093e006eca0a4d8379d349ad3a2b0bd9d', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:02:59', '2024-04-23 14:02:59', '2024-10-23 14:02:59'),
('be066fd555190ff388245173dc03cafb4eb5e7606364c2b43792decdc448adc84baad8dc1f4e1c4f', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:37:25', '2024-04-23 14:37:25', '2024-10-23 14:37:25'),
('be2713ed7abd4eb5e3e8477dfb4ed4ba1984a6b64e37f402c0b29236644187ca059dd7da03a31d00', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:01:09', '2024-04-21 16:01:09', '2024-10-21 16:01:09'),
('be357517f0735726e5d3eb781924d1c593cc7e1994d6617aa976c76ce75dcdd942e87175673254d8', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:23:11', '2024-03-24 18:23:11', '2024-09-24 18:23:11'),
('be674a2d3883f254523572aabe9d82fe4d6f8384ec0e1a92aa8f3671b070041cf833e5c7721754e9', 11, 1, 'MyApp', '[]', 0, '2024-02-18 12:50:02', '2024-02-18 12:50:02', '2024-08-18 12:50:02'),
('be84f68bad301cf4c8158de9f9481ad132d55b6a6ba81033f462a7e324a4ba3536bc6c74f52982fc', 35, 1, 'MyApp', '[]', 0, '2024-03-26 18:14:18', '2024-03-26 18:14:18', '2024-09-26 18:14:18'),
('be97146b5c5a2ea4c6a98524b88492e545c1176f1a4942a5f50f58584ab0de6f1734f7bfff9fa2a3', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:54:46', '2024-04-22 17:54:46', '2024-10-22 17:54:46'),
('be99a0665e5ecf6291eb216fb5d9eb90f6b684090e29cbd5d365b79f8285adf9424e0f343e4372a1', 45, 1, 'MyApp', '[]', 0, '2024-04-08 03:48:13', '2024-04-08 03:48:13', '2024-10-08 03:48:13'),
('be99ef81ab60fe8114f70b653558b7817ca78a40d883a00fc46638566df4d3545216e10078076bc9', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:29:29', '2024-04-22 18:29:29', '2024-10-22 18:29:29'),
('bec43842771ab49debc70389d69947c1039afb7a38b86380340cd8d36336389271726b74f30662a4', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:34:48', '2024-04-18 17:34:48', '2024-10-18 17:34:48'),
('bf19472b7c7994d6d1d4a5dcad50b055f186afa4c60f489fb8c40358dc0d100e8eb1c3699414d3e5', 11, 1, 'MyApp', '[]', 0, '2024-02-18 12:42:43', '2024-02-18 12:42:43', '2024-08-18 12:42:43'),
('bf3e06802dc1ba5541af368439b71f23e2670bebf84c4203aebef0ee4d91a0e37b23dea742867fde', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:48:34', '2024-03-24 17:48:34', '2024-09-24 17:48:34'),
('bf3e490f97b26ef68e70ea60688543db3aa10042da602e38c881b8aa7f019c4431704bef44e3420b', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:11:18', '2024-04-17 13:11:18', '2024-10-17 13:11:18'),
('bf50c933cbb86035cb78d1e320de4ec31505726e80f2387549fee024e8545ee9ece4a16fdbc04e4b', 58, 1, 'MyApp', '[]', 0, '2024-05-14 10:43:51', '2024-05-14 10:43:51', '2024-11-14 10:43:51'),
('bfce3aa62178113862aa5f0ae162b085ea8c7f97547bd2435c44118a496567615e5cacdf023712c1', 5, 1, 'MyApp', '[]', 0, '2024-03-20 04:35:26', '2024-03-20 04:35:26', '2024-09-20 04:35:26'),
('bfe44a61ca573958fc99792d1772ec743cb79c8ae79a4c87b2a5e6b76a880942e43dba7af8f8e89c', 5, 1, 'MyApp', '[]', 0, '2024-03-20 04:35:25', '2024-03-20 04:35:25', '2024-09-20 04:35:25'),
('c05d74a67d76a25853cc13ccac3ef5c2c297aae4a016bb28fda7cefa4d09360036fe059c42dbd7aa', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:25:31', '2024-04-18 12:25:31', '2024-10-18 12:25:31'),
('c061bab53e692b67ff19033a436681ce44b948879dbca1e70148cc8e52edc425ed47f6139358833b', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:24:52', '2024-03-19 22:24:52', '2024-09-19 22:24:52'),
('c08154f50e2be0dda446fecbd268b3130958a20eef95e00ae61b82afdc6e0e02b3d869557d150ec7', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:32:00', '2024-04-30 15:32:00', '2024-10-30 15:32:00'),
('c09304c124243479f5b25f82ca63453eef0f6f28f39319cc3792d67a1f3cf6c0e1939f9e6011d8fb', 38, 1, 'MyApp', '[]', 0, '2024-04-01 01:34:14', '2024-04-01 01:34:14', '2024-10-01 01:34:14'),
('c0c8219b400561f4a4f392b71d65dcf833537bd3a083665c191c126177d3378b1d68ab7a41da27a5', 5, 1, 'MyApp', '[]', 0, '2024-03-21 16:54:35', '2024-03-21 16:54:35', '2024-09-21 16:54:35'),
('c0e126ade8c28adb13d96e77b9a289856ab94431d6599421849ae9c380e2aecfb31cfec3096ea6b5', 5, 1, 'MyApp', '[]', 0, '2024-03-07 17:51:38', '2024-03-07 17:51:38', '2024-09-07 17:51:38'),
('c0ede88a54b058f22cf30819eb3c172f1149481000482834d06daaecedd0cdde7ec9421721455f64', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:22:42', '2024-04-22 19:22:42', '2024-10-22 19:22:42'),
('c0f3e2e303ce49052626e007105e77044519cf126da9655a6bed2751042bf14a6a003bdb82321809', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:42:03', '2024-04-28 15:42:03', '2024-10-28 15:42:03'),
('c0f89be9396d5c4db63142a4371ac4474e61be03735d96aefa1bd0dc28fa89af0b4eeb42bc51fa27', 5, 1, 'MyApp', '[]', 0, '2024-05-02 10:30:53', '2024-05-02 10:30:53', '2024-11-02 10:30:53'),
('c10e749cc9340726f4fb4d7ed2c1b8f07fb30345f020160d62bc0db90f07466107faa33199ed066a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:08:05', '2024-04-23 14:08:05', '2024-10-23 14:08:05'),
('c11b289f9fba578e65a2999b45a7eb3fd9a9a55956268bcfb3e3b65178d3a4c2024be26463654ed8', 66, 1, 'MyApp', '[]', 1, '2024-07-16 09:05:31', '2024-07-16 09:05:31', '2025-01-16 09:05:31'),
('c12c122ce42b17fd259643761d9db85b9c96cd2797148499bf834b52222763d4d004f77152686b54', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:36:26', '2024-04-22 18:36:26', '2024-10-22 18:36:26'),
('c1a34b861d701d19779e7c23d60f969f86c739a813cfe912e5c089672c283d17c608d91acfa59fab', 74, 1, 'MyApp', '[]', 0, '2024-08-01 13:41:40', '2024-08-01 13:41:40', '2025-02-01 13:41:40'),
('c1c6d8952e721595761f8ea251b016f1bc3ada994fc63033c0fdb292ffe990c78f68e1cdfd9e14a1', 5, 1, 'MyApp', '[]', 0, '2024-04-30 16:50:07', '2024-04-30 16:50:07', '2024-10-30 16:50:07'),
('c1c96280dfefaaebaf94a0efa92d8ca9d01f239ac25f4b67cdab20bc2bb271c0dbd1183e51469ba3', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:47:06', '2024-04-15 16:47:06', '2024-10-15 16:47:06'),
('c1e1398cfec1bf4838a616709d7deca1d42936c6d3665a62457ad8c0f005ead5e8d632300a585ebc', 5, 1, 'MyApp', '[]', 0, '2024-04-30 14:04:09', '2024-04-30 14:04:09', '2024-10-30 14:04:09'),
('c21bfe5367fde4711b7ef6fdb0a061728e0b24a36b566620db6bf7d59debe4b94a5eeea801b3d371', 5, 1, 'MyApp', '[]', 0, '2024-03-04 16:15:27', '2024-03-04 16:15:27', '2024-09-04 16:15:27'),
('c22e2030a6ac2496ce66aec86dc39641dc1dd0a49df061497ff23583718115970d588b45c403d9b6', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:32:53', '2024-04-28 14:32:53', '2024-10-28 14:32:53'),
('c25ac3fbfa414e3c569f499f31e954fdce85912216e240fcc2928df8e57e51537e97c124e74618a7', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:54:46', '2024-03-07 11:54:46', '2024-09-07 11:54:46'),
('c261decc0c1966170e6aad4845795a2cc53f541dd0099a00cab16a5abd40ec3329e80b743cc6f45c', 5, 1, 'MyApp', '[]', 0, '2024-03-20 03:43:03', '2024-03-20 03:43:03', '2024-09-20 03:43:03'),
('c263117d91dd6cb5fe3c55e03830b8a19871d17c6f4ea54e072406fca36c23c653a028b18fb983d1', 62, 1, 'MyApp', '[]', 0, '2024-05-30 14:08:09', '2024-05-30 14:08:09', '2024-11-30 14:08:09'),
('c27a705efba68ca50bea74f661d22cbf9beb7e33cd5f9c6a0a11a00df8c2c4f6ae8554f8c577fc7e', 5, 1, 'MyApp', '[]', 0, '2024-03-06 14:30:09', '2024-03-06 14:30:09', '2024-09-06 14:30:09'),
('c2899941697550140b62abda7efc6d4c94c7ef086f109e1c88cce813effdbc943b9f6163bec3c969', 58, 1, 'MyApp', '[]', 0, '2024-05-28 09:47:36', '2024-05-28 09:47:36', '2024-11-28 09:47:36'),
('c292991bf3964398d8ae46f977b83671e842e26d8ee24412a9d154e6a31299d17a26b1ca7d25e407', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:49:36', '2024-04-23 14:49:36', '2024-10-23 14:49:36'),
('c2af57524a3f32cd199913ede79e1918e8d1e2ab0c3cd17bc0325901c43dc6cf023451be356552c9', 58, 1, 'MyApp', '[]', 0, '2024-05-08 14:21:47', '2024-05-08 14:21:47', '2024-11-08 14:21:47'),
('c2fb2ad2d50a1590dcb7c967277d9b4cfd86a4891612321d9083ab9af1a82be96a856d281340f303', 11, 1, 'MyApp', '[]', 0, '2024-02-15 22:08:05', '2024-02-15 22:08:05', '2024-08-15 22:08:05'),
('c31e367a47962832379de2fca09d741eb37685ac9a1b767273c0d6e397f7af3493292f7c2bc01efb', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:55:55', '2024-04-23 12:55:55', '2024-10-23 12:55:55'),
('c325e8738000c0ca4ae25c3f497436c4c8a1beb3589282d3cd61ea09280a72d72084eaa1c578fd09', 2, 1, 'MyApp', '[]', 0, '2024-02-15 11:15:04', '2024-02-15 11:15:04', '2024-08-15 11:15:04'),
('c335ac4bb1f8288f9070888f2624cc1d976fd892ed79e2bfb17e56ede1b619e66116c61a6b4734f4', 74, 1, 'MyApp', '[]', 0, '2024-08-01 11:00:06', '2024-08-01 11:00:06', '2025-02-01 11:00:06'),
('c3394a36bd94d3fd96f87d4fb7d5212289c939946db0cdc79c80eb6251467467e57986b38310b050', 58, 1, 'MyApp', '[]', 0, '2024-05-13 16:53:46', '2024-05-13 16:53:46', '2024-11-13 16:53:46'),
('c3482366415c2be66fb6318c8607f171bc2651306e7405513c44bc666133b5f7c7ea37d2ef592705', 5, 1, 'MyApp', '[]', 0, '2024-04-02 13:19:18', '2024-04-02 13:19:18', '2024-10-02 13:19:18'),
('c3952a1aca60b129e55d001c947a913afa1672bf91153df013b6101f5bed73e5d971a9562968a0d7', 35, 1, 'MyApp', '[]', 0, '2024-03-31 16:35:28', '2024-03-31 16:35:28', '2024-10-01 16:35:28'),
('c396cfc3bc6e2267ba9a4617a2c213ad2db3c2aa5c9c487f286d1b7683583a87221f8648caa49d65', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:20:18', '2024-04-18 19:20:18', '2024-10-18 19:20:18'),
('c3d0e4bdd0ec0768f15f1532404cd1059eadac2b239c34cc9efc19b07f3d352744319695cba89b30', 5, 1, 'MyApp', '[]', 0, '2024-04-07 17:19:22', '2024-04-07 17:19:22', '2024-10-07 17:19:22'),
('c455e8de918d632b0f82166f310ced762cf287962579358e64397baa67597ca1f31b799095972990', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:48:00', '2024-04-28 14:48:00', '2024-10-28 14:48:00'),
('c45c0bb76bf53a27a7e64ab22dfe0bd73111fd193b0e31c7dcfefc42baf8f039a0a94d6372a5a855', 35, 1, 'MyApp', '[]', 0, '2024-03-25 15:29:06', '2024-03-25 15:29:06', '2024-09-25 15:29:06'),
('c47a852c60d1ed79d663cd65cfb2dc1ecfd623e5f07f082e6277a246bef043516909f9eddc7921cd', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:55:55', '2024-04-23 12:55:55', '2024-10-23 12:55:55'),
('c4a2bcf64e8b8e8ab33b6aa39c2e64ec9a5b6f1729c9a4dc10ec5c2c4853e6a7f69bba5c9aaa4dae', 43, 1, 'MyApp', '[]', 0, '2024-04-03 21:26:04', '2024-04-03 21:26:04', '2024-10-03 21:26:04'),
('c4af30f727fa6b93af2ff9664876d58f84579d153c779968793a01455121d9a7d60a3b0349cfc1c4', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:50:24', '2024-07-12 14:50:24', '2025-01-12 14:50:24'),
('c4b5bca99509de69150261e797e28e9a9247cc4f255854a5d7231fc8486fc2a7b12ffe4b4f96457b', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:36:38', '2024-03-24 17:36:38', '2024-09-24 17:36:38'),
('c4c14c3dc8d048f9812e226b6781e68b5e0e1187731fbc9ac314045f2fe20142b7009e9246c0c175', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:36:10', '2024-04-28 14:36:10', '2024-10-28 14:36:10'),
('c512d3bf68bd13bca77116e5c319866b3548c7aa296f26328cb8b7221d719fc358014bca302b51d1', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:26:12', '2024-03-26 16:26:12', '2024-09-26 16:26:12'),
('c51396b1764affaf967fe901eb771ef525cf22601438526a0a6b4b3cdc438ca9d430bbfe0e03fabe', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:12:55', '2024-04-30 13:12:55', '2024-10-30 13:12:55'),
('c5265ef5e52a3fbfeeec48fe47337f317efe9a9a4716fbe8979069751c920a2f53d5ea61de0a8284', 35, 1, 'MyApp', '[]', 0, '2024-03-28 17:52:05', '2024-03-28 17:52:05', '2024-09-28 17:52:05'),
('c52b3b871d5c7188165d596a0c769ac48fa3d48ba98a0dcdc814a5919c2eb8259d58ea128a6bf498', 58, 1, 'MyApp', '[]', 0, '2024-05-08 13:56:21', '2024-05-08 13:56:21', '2024-11-08 13:56:21'),
('c55111c46f9f7db243917c1ea8b83e75851218648e94843d7d5d023cd6b6f2e4f09807fda0a52aea', 33, 1, 'MyApp', '[]', 0, '2024-03-23 22:21:36', '2024-03-23 22:21:36', '2024-09-23 22:21:36'),
('c554db02ba72902980830ac8e3b7b88fc0e02db72c2161a83b3af367035869107aa49687b47a91ee', 5, 1, 'MyApp', '[]', 0, '2024-04-24 14:51:30', '2024-04-24 14:51:30', '2024-10-24 14:51:30'),
('c59703c9b62bc9d940230290d0dc3310c5202008a9069bb38daead51401da2b1ad6f5fab0bacedbe', 5, 1, 'MyApp', '[]', 0, '2024-03-20 17:25:29', '2024-03-20 17:25:29', '2024-09-20 17:25:29'),
('c5b14989e1213131fa54a5379c0a63d84ae3a7624704e1e943b57a4895592160bac793b38499d470', 5, 1, 'MyApp', '[]', 0, '2024-03-04 14:43:57', '2024-03-04 14:43:57', '2024-09-04 14:43:57'),
('c5b28b236c0dc6f6ea94634920c2fd2a95b713fea57ee2aa0f055bb86433858bbf4f53ef0fedce19', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:39:39', '2024-04-02 20:39:39', '2024-10-02 20:39:39'),
('c5ea9bfb2d86d7a06a446f80061013e8047349512a824a12c28b72cffa5694be7069b8d8516605bf', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:25:29', '2024-06-05 13:25:29', '2024-12-05 13:25:29'),
('c6805b2a3018dbda0886f4f6cef05ef7753ee1d98c1aac5e08a73bbd867310c3af852c70a695f349', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:57:53', '2024-04-29 15:57:53', '2024-10-29 15:57:53'),
('c69daf0e1fd1cf589b7d2738b7cd11d712564fe04bd80f2a683237abfc53f4fe449fc2df4567923e', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:48:49', '2024-04-15 16:48:49', '2024-10-15 16:48:49'),
('c6d5b1adfeeb3bafb13545293c543f082ce90e85b616945b687d08c1ecc054fa519adc84b6830972', 5, 1, 'MyApp', '[]', 0, '2024-04-25 17:27:30', '2024-04-25 17:27:30', '2024-10-25 17:27:30'),
('c71e6a5c8ce54380f0d689c67be896ee5f4e834d7d6e146ae94cc4f839786c0c7ea3da61fff4529f', 5, 1, 'MyApp', '[]', 0, '2024-04-02 12:55:47', '2024-04-02 12:55:47', '2024-10-02 12:55:47'),
('c7203315cc269cbb5561b9f0038dd87ac2a6b25bab90efda2558c7aee3352ada84c3a899baab261b', 10, 1, 'MyApp', '[]', 0, '2025-08-17 14:46:00', '2025-08-17 14:46:00', '2026-02-17 17:46:00'),
('c759d59bb08cce668f429023d32a2ef87f03e114f71fb8c7afa3c12f424f3472c066382522b53ea2', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:33:25', '2024-04-18 21:33:25', '2024-10-18 21:33:25'),
('c766470fc4b45c23d2d27e417a9749733932d1a9fa19fc2be0b7c174c57e9980ce7002857edcb01f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:40:27', '2024-04-18 21:40:27', '2024-10-18 21:40:27'),
('c76894c2faff65d9de758f0c5d63e04d937374a41b57c93d674acefaa74254b73b8728d5cbacd18f', 5, 1, 'MyApp', '[]', 0, '2024-04-08 15:35:38', '2024-04-08 15:35:38', '2024-10-08 15:35:38'),
('c79681d6cd6f322e24a9b252c394d096d28899395c9af0795df087c135b6287d6fce9c2660e0b7c8', 58, 1, 'MyApp', '[]', 0, '2024-06-02 11:28:50', '2024-06-02 11:28:50', '2024-12-02 11:28:50'),
('c7e7168897886ce4f443d0929f63595652691313accdcc9aed5723ae0d8f448809dd144004ba70cd', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:55:37', '2024-07-16 09:55:37', '2025-01-16 09:55:37'),
('c809bb309febb8e54ff2fa6c67c4ab481770e554d166442e57677510fe15b4800c87ae89dad421ba', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:28:34', '2024-07-12 15:28:34', '2025-01-12 15:28:34'),
('c81502e49a228bb0d42f6bf94fa4e9992a74fa35664d1d99c08ebc16923b4e5974fb0b7b18b49f3a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:26:39', '2024-04-23 12:26:39', '2024-10-23 12:26:39'),
('c81bf1bfba3e339f8f20f1a3484ff720e96351d99860212e421db6b5ae687b5647c4ac3d71f9df62', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:31:30', '2024-07-12 15:31:30', '2025-01-12 15:31:30'),
('c82ba446e3fef65e6fc00a1e725fb3ea4e88a44a7ee911415ebe7b42c120d2d8b985dbfeb22c9fcb', 64, 1, 'MyApp', '[]', 0, '2024-06-05 13:32:11', '2024-06-05 13:32:11', '2024-12-05 13:32:11'),
('c845dded94f93c039fdd47d6bb40b6da70ff283a782218bfa0b138f4316a4ad73d60bf41b08a475b', 5, 1, 'MyApp', '[]', 0, '2024-04-29 11:26:27', '2024-04-29 11:26:27', '2024-10-29 11:26:27'),
('c88a8fb7cdd62e1fd58fce3568bc918ded00e8d77a0966f4fed89ecaaf7b535aba3b3c1f75296b75', 9, 1, 'MyApp', '[]', 0, '2024-02-15 17:28:33', '2024-02-15 17:28:33', '2024-08-15 17:28:33'),
('c89bc4d56da10743772b8aab58022e10d76233d19e9b08a0091e1b81a2c25e2ca4c1aa86aaab0e2d', 30, 1, 'MyApp', '[]', 0, '2024-03-19 10:17:11', '2024-03-19 10:17:11', '2024-09-19 10:17:11'),
('c8a2e36a5c9dd1faec872884b80fc446d0f1cfba70f02b5e7e79f5b325bf510182d6c8658343989a', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:22:02', '2024-04-15 16:22:02', '2024-10-15 16:22:02'),
('c8ac257aaca3b6d08fa44a3f54d3d90d551ec130ea505f9a30300889d0ca04cbe71fd1b9b2e7f09d', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:39:08', '2024-07-12 14:39:08', '2025-01-12 14:39:08'),
('c8af9e589487251a8be478d761898575e7a6587a42846f036273768889ddabf8d2ea7350682f4c8f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:29', '2024-04-28 14:59:29', '2024-10-28 14:59:29'),
('c8d6876cd6b59a8e28845a5246c673e3cb59ba37bc7e17402ac7d0ffc56fb5ee0588d9d58386b690', 35, 1, 'MyApp', '[]', 0, '2024-03-28 18:50:32', '2024-03-28 18:50:32', '2024-09-28 18:50:32'),
('c8d9a46f6eaf65f8e8ad1cba00149f88d7009240d5012d7e5072b30602bf99f302bc09580266c52c', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:20:31', '2024-07-12 12:20:31', '2025-01-12 12:20:31');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('c8e515450b3a570beed0c56ee763692fd70a0d0d9671748ba76eb7d55dd22775fc5d50dac3cc4fc9', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:55:43', '2024-07-13 17:55:43', '2025-01-13 17:55:43'),
('c8ec85a496f9cfae3dc80d0de3f55dcc8b2e59ee37a9b904d5d31dbe469a800855a56b448524cb28', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:05:19', '2024-04-29 16:05:19', '2024-10-29 16:05:19'),
('c90e386c93cffc49259872bbb6461be165259b4e5ae9569e9c00b504a6d189ca8420d3ff1331e143', 35, 1, 'MyApp', '[]', 0, '2024-03-26 18:16:14', '2024-03-26 18:16:14', '2024-09-26 18:16:14'),
('c92934504196e733131e1af00fd12b51cab5727326a708d4daf3f1d0c6ac1ccb3f9736eb9794a36f', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:29:46', '2024-07-12 14:29:46', '2025-01-12 14:29:46'),
('c92dc1a3178ae8b0c80a73ec662d5a5e6fb8a33f69e6d152d1e362f61350a14a58dd8956783da098', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:51:28', '2024-07-12 14:51:28', '2025-01-12 14:51:28'),
('c93b4d9a00c67ddad872960e96d830b569c64cf25d6cfcdbba4f5b07eec2ba30c9f35e074c63ab81', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:20:19', '2024-04-18 19:20:19', '2024-10-18 19:20:19'),
('c95c863904087ded3bd373d30ca6ea12dec47c0825ecdc9bc17fee648b66f9ea01a257dd41f2421e', 5, 1, 'MyApp', '[]', 0, '2024-03-17 14:44:41', '2024-03-17 14:44:41', '2024-09-17 14:44:41'),
('c9ddf0cd4c5a998606a0fc75989d79b563896ed933e5dfe276c224e4a9806492800714f5cc0e2329', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:25:06', '2024-04-23 14:25:06', '2024-10-23 14:25:06'),
('c9e5e8872780e3fa3644aa54a2dd5b6dee69b5e834864b076fbe35365d679cb775144aa2c01e28cf', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:55:11', '2024-04-21 17:55:11', '2024-10-21 17:55:11'),
('c9e952a4c54a92db652f02f1a1490fa9b884c38e50677c74c8544b259b363480c3fe2246598dcb83', 62, 1, 'MyApp', '[]', 0, '2024-05-30 09:12:25', '2024-05-30 09:12:25', '2024-11-30 09:12:25'),
('c9f45f07f8aa8c85e0a1feae81d2a5be5cc28b0a92ea4c18356ba8e243d5a17ac4f799f24189da85', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:20:39', '2024-04-22 19:20:39', '2024-10-22 19:20:39'),
('ca32663d2ba7ab91f010cf5089be020b66441ef8064f149b2828b4ee3431fae99a5db5bebadceb94', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:02:54', '2024-04-14 13:02:54', '2024-10-14 13:02:54'),
('ca32fa77dc46ead09cadaa1f568d1d754b2abb3b9a463dcdf2ef79891c863239f08dd5500ff0e445', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:25:13', '2024-03-19 22:25:13', '2024-09-19 22:25:13'),
('ca33f47f5da6ec599ae6fa9e1b300cd7974a2aaa37290a529ee759a309eeab67f1f0be98030fd45b', 68, 1, 'MyApp', '[]', 0, '2024-07-15 11:47:38', '2024-07-15 11:47:38', '2025-01-15 11:47:38'),
('ca7f53f6b831d6151ce79c9001e99af29b5eb677760edc5538ff4ad901872351efe2536d8203654f', 35, 1, 'MyApp', '[]', 0, '2024-03-26 13:02:00', '2024-03-26 13:02:00', '2024-09-26 13:02:00'),
('cb09adc81b92409723f380e17987f907e6f06c90577127766865dc8bf3ae7dccc5c19aabb4ec39a6', 74, 1, 'MyApp', '[]', 0, '2024-07-30 16:36:29', '2024-07-30 16:36:29', '2025-01-30 16:36:29'),
('cb3414df3dc30cec6b6077968e790e0c8a396596f711528fdb322f312df37e7b922d10a529843067', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:07:48', '2024-04-24 17:07:48', '2024-10-24 17:07:48'),
('cb35675e88c1bcbef1f7e603fa25f7b46b2ff411d21689d1d324ed37abc2836c14a6950788baab1f', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:34:26', '2024-04-18 21:34:26', '2024-10-18 21:34:26'),
('cb4333f52e01148ff995ce18867bbb7eb7602dabbd23cd8c6be20418d71b37f50d75793118e248e9', 58, 1, 'MyApp', '[]', 0, '2024-05-21 12:52:53', '2024-05-21 12:52:53', '2024-11-21 12:52:53'),
('cb4791db03a7e61d1ddb8d114e2fb10206fd2263a3589d06729b3cb6de977a1008d874ea96513d3e', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:35:22', '2024-04-25 18:35:22', '2024-10-25 18:35:22'),
('cb7439c8d9df02f639ae2a586657b012cac834a4e5aa6c811bcda51941704f4739e99df9d32800a7', 5, 1, 'MyApp', '[]', 0, '2024-03-18 14:12:53', '2024-03-18 14:12:53', '2024-09-18 14:12:53'),
('cb7fbbb75a3c61a242f9680c0cbf20b57135d4ec50f625596c5fb149c0fe6d9e093e40bc514c5ad1', 35, 1, 'MyApp', '[]', 0, '2024-03-31 14:28:25', '2024-03-31 14:28:25', '2024-10-01 14:28:25'),
('cb8434c43850d8a40fe0004393473bad52ca0c70a859ccf20066d0c80a1dadced16e9c2e36a9b7ed', 62, 1, 'MyApp', '[]', 0, '2024-05-28 14:45:31', '2024-05-28 14:45:31', '2024-11-28 14:45:31'),
('cbf236bb90e3f46f7b22bc2c2acab0088a82be157a6435520d6df2ae68292ab0f4e052f5664a54c5', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:07:40', '2024-04-17 16:07:40', '2024-10-17 16:07:40'),
('cc077e6e47f455726a382a07587c635c83b37db21829b10bcb0d78ec32cdf2ba7efd9b0a2d25d50e', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:08:20', '2025-08-14 13:08:20', '2026-02-14 16:08:20'),
('cc2c4042c80433b6b8fd21e30e78d64499a841fb6c9a071353cafd453fea141b46783fcef2f40242', 5, 1, 'MyApp', '[]', 0, '2024-04-02 17:01:21', '2024-04-02 17:01:21', '2024-10-02 17:01:21'),
('cc56e5509a036cdfe5148f32b40495e7a9e82ad90e10181a86356be897ca831bc6f19689307cb9a7', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:40:05', '2024-04-17 13:40:05', '2024-10-17 13:40:05'),
('cc71c5efb49d37fb47df3c24eadd0e80c22b6c488f25b02a75d46e3a27c655686d18963194b9188a', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:05:50', '2024-04-18 22:05:50', '2024-10-18 22:05:50'),
('ccb68e558fdacbb0c7bd6a398a8ea50fec738b4ad2a3362e063f8b3e97730b9388d609bb58449f4b', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:50:06', '2024-04-28 15:50:06', '2024-10-28 15:50:06'),
('cce5fc05438d0b5780231ea5f78eac96eed90385dd14d99b98e837bb2b78804ef10f9888a355ca65', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:35:24', '2024-03-24 18:35:24', '2024-09-24 18:35:24'),
('cceb9d28911080037bfa6b7c7802a104f427b59689c06840a3bb3dd4c1d82fb98a19a0ab4e0326bc', 5, 1, 'MyApp', '[]', 0, '2024-03-07 20:17:38', '2024-03-07 20:17:38', '2024-09-07 20:17:38'),
('cd2d5c1d43b3e064e105d456621e22dba10f2668103d676b385d6ff7d586fa8e9d646773158f673f', 5, 1, 'MyApp', '[]', 0, '2024-04-14 17:59:20', '2024-04-14 17:59:20', '2024-10-14 17:59:20'),
('cd3b31069f73682bbebbc7c881f824aee2b39079358623a508ae9e209bdcdb000d0d069fe513c3db', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:58:21', '2024-05-28 15:58:21', '2024-11-28 15:58:21'),
('cd51b5c496d02aa47bd9137ffeed33465305e7f636d5a68fcf9225958b26b21aeaff2a1955d2bb87', 39, 1, 'MyApp', '[]', 0, '2024-04-02 11:38:23', '2024-04-02 11:38:23', '2024-10-02 11:38:23'),
('cdaa6c3e7d8f27afbc528fae286f1c1f661909ea0d55bb7899dad357ccf7682587f6a0994fee582a', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:50:36', '2024-04-23 12:50:36', '2024-10-23 12:50:36'),
('cdd07b737fa7fdb6342fa5794233711b2e40434c466069e0f7a0e26f92e1b29b592065580c64c51b', 5, 1, 'MyApp', '[]', 0, '2024-03-05 18:25:37', '2024-03-05 18:25:37', '2024-09-05 18:25:37'),
('cdfbdacd24c8ad794e0693bda7ec1b319bcb3be1b8e48aa922df272b78a5ef805cab3b0ff6a61b17', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:48:49', '2024-04-16 19:48:49', '2024-10-16 19:48:49'),
('ce27b9def1b4993f6d3784bd5b32edf7f049fa4a0654f569773503cf162658969c4f2deb4ecbb6b6', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:59:46', '2024-04-18 17:59:46', '2024-10-18 17:59:46'),
('ce3fe3e8d28bbdee743a60232d6aded5724aad6d5f251b201a0abd8bd6b7ce08696c7f5a27656771', 5, 1, 'MyApp', '[]', 0, '2024-04-25 17:26:59', '2024-04-25 17:26:59', '2024-10-25 17:26:59'),
('ce5ceefb4c5e2044bc2af1f0a4c10f989e11380645cb9e0f088c7d204a6fdfabd75999bdb98b3dac', 69, 1, 'MyApp', '[]', 1, '2024-07-23 09:27:13', '2024-07-23 09:27:13', '2025-01-23 09:27:13'),
('ce61e926e8a035e87222b61562708c13f17ca190a1d406f028389ccf0d65666d24c548f562af498a', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:20:37', '2024-07-07 16:20:37', '2025-01-07 16:20:37'),
('ce7837bafb1232c76c88d258509f0ef552a466522c5b61d1e27ac77c6d7b44c5e07f133c9694d59f', 35, 1, 'MyApp', '[]', 0, '2024-03-26 03:57:48', '2024-03-26 03:57:48', '2024-09-26 03:57:48'),
('ce83400b8b0fabf623a47e12837e62e335e5d2f711f3f47d470474cb7e9afdce51e3c8c6d4e6aab8', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:57:50', '2024-04-23 16:57:50', '2024-10-23 16:57:50'),
('ceddbbfa9fe34cd3f24f863a9661e753dc10d5e7bbc5193add15df702df493cba366ff2b6837dbf3', 62, 1, 'MyApp', '[]', 0, '2024-06-05 13:23:59', '2024-06-05 13:23:59', '2024-12-05 13:23:59'),
('ceeb278856d3696ff1fb5ea1762bffda309a843e499b1dbebfd70de6b5782f08d6b5c1a1c842d4d9', 58, 1, 'MyApp', '[]', 0, '2024-05-28 09:47:26', '2024-05-28 09:47:26', '2024-11-28 09:47:26'),
('cf297eaf5150888b59c4c239b58a130bdc474886dbfe2a074c2c975a527934ae712c5747e1d3bfe2', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:00:42', '2024-04-23 16:00:42', '2024-10-23 16:00:42'),
('cf6f2f9a36896491ba17ba0685e76d2bab27cc0e6727fc88d6f188acfb1dc1d5a22fde905f0230b1', 69, 1, 'MyApp', '[]', 1, '2024-07-23 13:33:08', '2024-07-23 13:33:08', '2025-01-23 13:33:08'),
('cfa8beb5e046efc1f42fb13ad1d2302f70feb665393c4d5230e7c18305fb46565f6d82fe7932a41e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:58:22', '2024-04-23 18:58:22', '2024-10-23 18:58:22'),
('d0129880cc0b8c5ed8990d50be72bb3b84e4876cb4bc89883b6986422f879aa9faece20a56eb0f67', 5, 1, 'MyApp', '[]', 0, '2024-04-15 16:15:07', '2024-04-15 16:15:07', '2024-10-15 16:15:07'),
('d05cb62e7fd41732f4e5404bee1e4925ba16c5ec202926bd30a93104f1edac2319496d4fe03174df', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:39:09', '2024-04-17 15:39:09', '2024-10-17 15:39:09'),
('d069307e34ae608ca68a51e9a6190d0db0695022363f935c7b70f0a5048b5ae17832b89bc2761ce4', 5, 1, 'MyApp', '[]', 0, '2024-04-14 11:20:47', '2024-04-14 11:20:47', '2024-10-14 11:20:47'),
('d07c6bcc13dda101d9b8261a90f4a9548a8864279d6d1351cb49cc1753ad2341d4a371a346dc770b', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:49:36', '2024-04-18 20:49:36', '2024-10-18 20:49:36'),
('d0c4ea057ad16603be98ee37b834fe78ac5692ea88ac2f832c63cbd1e38b5704b918c8cb021c5c19', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:33:02', '2024-07-16 10:33:02', '2025-01-16 10:33:02'),
('d0f48ce87c4fc4df3f1a26b1c38f090f5b0207d2874a7246ed0d3029c224ec8bbfbae48c478406a4', 10, 1, 'MyApp', '[]', 0, '2025-08-17 14:46:19', '2025-08-17 14:46:19', '2026-02-17 17:46:19'),
('d121af29d6a547d8b906335423674268bb52ec36350c1e3a3d2d4f80388d2402460765566f1bdd9a', 31, 1, 'MyApp', '[]', 0, '2024-03-20 03:49:46', '2024-03-20 03:49:46', '2024-09-20 03:49:46'),
('d123e186b6f974528be7b28117f7e7bb0ccc9e7817423b042646935b1d3a9eed11e961ef4b01a2aa', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:36:34', '2024-04-28 14:36:34', '2024-10-28 14:36:34'),
('d14a4748d62c8269a09ce4d4e6b98106eaf40b389146e07fe13a316480c061526ddcb10110b7d6dc', 58, 1, 'MyApp', '[]', 0, '2024-05-22 11:03:57', '2024-05-22 11:03:57', '2024-11-22 11:03:57'),
('d16cee84b2f04fa838aa2f582176d62f26bf57a59ab3493617c2ee31ec4b5bf43f4c4e96ac3ff360', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:57:15', '2024-04-23 11:57:15', '2024-10-23 11:57:15'),
('d172b9b3a724fcc3692a0212576294008073c91b57632c03480526d604f2c94067dcda16fd561120', 5, 1, 'MyApp', '[]', 0, '2024-04-17 13:15:23', '2024-04-17 13:15:23', '2024-10-17 13:15:23'),
('d192a5a295758fcd06026b3e26d4a77872fa9a946608e6e673bee49bad08d408c8758146dd03c3fd', 35, 1, 'MyApp', '[]', 0, '2024-03-24 17:49:49', '2024-03-24 17:49:49', '2024-09-24 17:49:49'),
('d199f6805dc1a5d26e671bda959360d55969a4c6147e4c9227256d4c39085de48271a777f66b82ce', 10, 1, 'MyApp', '[]', 0, '2025-08-17 14:59:18', '2025-08-17 14:59:18', '2026-02-17 17:59:18'),
('d20c7369def8051c9fcdfff49171b07c3346ab7c5f13b50e4e91c3c0e346930d0191a88ecf67395c', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:33:41', '2024-04-21 16:33:41', '2024-10-21 16:33:41'),
('d216367c48b4a50b977a070cbf938fb35300ecaf0092be6843d44ea342b752521c67a554c887a06c', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:29:34', '2024-04-23 12:29:34', '2024-10-23 12:29:34'),
('d26287284bc3eac057b821d15fdb3748a2200b24a66c77cee93fb7c47af0ba9a24fd7e0768c59c2f', 47, 1, 'MyApp', '[]', 0, '2024-04-15 16:48:51', '2024-04-15 16:48:51', '2024-10-15 16:48:51'),
('d27be29c00237276e04db3f3a55501f583c51d2b39aacee411f683eb3e6b635f4417463407ef9e24', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:49:42', '2024-04-21 14:49:42', '2024-10-21 14:49:42'),
('d28294204a1c0032ab4ca665dcbdb3a35f7018ee2cb2b89fff29b43c339a71dcefde9cdc21726d46', 35, 1, 'MyApp', '[]', 0, '2024-03-24 17:32:37', '2024-03-24 17:32:37', '2024-09-24 17:32:37'),
('d2ba11805342f66349887d58d50a2becfd93f3da20c1bd0e21c81da19ffc92d9d92f86db5a6bc9f0', 5, 1, 'MyApp', '[]', 0, '2024-04-16 15:26:44', '2024-04-16 15:26:44', '2024-10-16 15:26:44'),
('d2c27d5f910221fc01b7f1ccbfc0699984ad81f6b660790459a8b485da747478da5ced1541bb31b9', 58, 1, 'MyApp', '[]', 0, '2024-06-02 11:28:51', '2024-06-02 11:28:51', '2024-12-02 11:28:51'),
('d335ad1adb1f07853fbdea946759a44d019f7fb97aa426d717cc9c9dc695cfdd09d0dec7bb21abc1', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:08:12', '2024-07-12 12:08:12', '2025-01-12 12:08:12'),
('d34c30f8fe212df3b346d54dc7499fd688f95da49bd592eb8b0b1805600ee8f8df07220fe7e2d03f', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:15:10', '2024-03-19 18:15:10', '2024-09-19 18:15:10'),
('d37ed149c3398c758d95954a92ba0d0f3c83a846ccb87d88c3ff1ff5350210ae19f905ef4d60e678', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:09:18', '2024-04-18 19:09:18', '2024-10-18 19:09:18'),
('d3829050b166d22d376ebaca9dc7bd2b12879e037d4a22dc636fcf39e1ec529636041d01d18fbb25', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:40:38', '2024-04-02 20:40:38', '2024-10-02 20:40:38'),
('d3c06f4073158a0f17f94df03b5a80c4807344f1cac1f7ff6321b0700dda84ef7336c101151d9244', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:50:22', '2024-04-25 18:50:22', '2024-10-25 18:50:22'),
('d3c97475892db9c15f686e1f88f8bf0c0b42fe84dc733986cff14273dc52c40f4ac94b06ab288eed', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:03:59', '2024-07-13 15:03:59', '2025-01-13 15:03:59'),
('d3edcbb60e99fde4a608a4939c1814d2dc82f70d183381f3fcc5f6feb171b8da4533bb6365dd3ec3', 5, 1, 'MyApp', '[]', 0, '2024-04-29 10:52:38', '2024-04-29 10:52:38', '2024-10-29 10:52:38'),
('d408a0b0f374baef30602f35e5ac716c9853ce5c1d18ed6781a990e9771d25a510768826e438e941', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:03:27', '2024-04-28 15:03:27', '2024-10-28 15:03:27'),
('d4219a19cbc48dd61d8cec36b6607f128846a54dddf7bb1dad2e3aa4b2b535d0da8a238e191c080a', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:09:24', '2024-04-02 16:09:24', '2024-10-02 16:09:24'),
('d430d2bc1a3b9d0dbfe9a87d00797a9ab9f7002ab6dd58695a56bcef2852efd41c28e071d77d0810', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:36:47', '2024-07-13 15:36:47', '2025-01-13 15:36:47'),
('d448aeaf1dc577bc13b76ccff10324328631f40811d4bd48b168e46d754ceecd51c54ece172d1dab', 45, 1, 'MyApp', '[]', 0, '2024-04-08 03:43:12', '2024-04-08 03:43:12', '2024-10-08 03:43:12'),
('d46275da71a760c4c22b11ab57de9fa9a7910ce82a86942681c06c527f28a03644b1dea88c708968', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:06:10', '2024-04-14 13:06:10', '2024-10-14 13:06:10'),
('d4689a5356ac692c3aabdfeb564ff5906feb010babc2a359af683f5b90b03a8fd3689c85542f3e52', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:39:14', '2024-04-21 16:39:14', '2024-10-21 16:39:14'),
('d4757596aa9fb9453966fd0c11d9d9458511eefd06c07fc50e611080fdfcd877a41a71e79afcb52d', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:40:27', '2024-04-18 21:40:27', '2024-10-18 21:40:27'),
('d48cef08de0526f2d89b9665004ace5c770e607fc5d945f4f0a8592503872a4316bf217b00ae47d2', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:10:16', '2024-04-24 15:10:16', '2024-10-24 15:10:16'),
('d4939387a53c79271b1110387b9432394562fc470d606500be6bf64c1f7f9ec3208647299d3b1c67', 5, 1, 'MyApp', '[]', 0, '2024-04-23 11:27:18', '2024-04-23 11:27:18', '2024-10-23 11:27:18'),
('d49a27b60d46caf9cadd87b890baeebb86fd3949f1cb0e2b59927ffed8f9e30d2585364e7e49b24d', 58, 1, 'MyApp', '[]', 0, '2024-05-13 13:02:19', '2024-05-13 13:02:19', '2024-11-13 13:02:19'),
('d49a57fcf796012b972c441154c2013f323e2a3fe2e597475453d22551e41da91cebf427413a7e12', 40, 1, 'MyApp', '[]', 0, '2024-04-02 15:48:23', '2024-04-02 15:48:23', '2024-10-02 15:48:23'),
('d50b9cd058be822e4300970e8de3071f74d2ec1ee6fe17f7e4b025d364213a239a62f7080fe73872', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:38:34', '2024-03-24 17:38:34', '2024-09-24 17:38:34'),
('d51d76b31bd421a878f04838044cdbd6897117cc4aba6d0ab5e86b56918250de74956a27a5b5cfb8', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:14:00', '2024-04-24 16:14:00', '2024-10-24 16:14:00'),
('d52b51054d0129939b96d9718c75708f7128dbd661f3fec53fa6b339b566a5069d7c3dc34cfdf7e6', 40, 1, 'MyApp', '[]', 0, '2024-04-03 21:06:45', '2024-04-03 21:06:45', '2024-10-03 21:06:45'),
('d52d52d3dbd72a9c7ab625ba1960f4c1e0d94df3d359a1ac3283100daa5986f2f3f43356fc1a8bcf', 65, 1, 'MyApp', '[]', 0, '2024-07-07 08:58:12', '2024-07-07 08:58:12', '2025-01-07 08:58:12'),
('d53402445fa3dff302b244612556d87fae64bac5825878890d1784d6e162b2b6be985c03dc3b964b', 5, 1, 'MyApp', '[]', 0, '2024-03-19 17:45:44', '2024-03-19 17:45:44', '2024-09-19 17:45:44'),
('d5d26a3451348140b11b91878662a8223a7c63945cbcdfbd05228c44778e6bbd275fa7291350c46b', 66, 1, 'MyApp', '[]', 0, '2024-07-13 18:06:36', '2024-07-13 18:06:36', '2025-01-13 18:06:36'),
('d5d4721daf4af6f3d9656c8f92971c81c1add47ecf7dca41a593d18284ea565019809a47919476dd', 13, 1, 'MyApp', '[]', 0, '2024-02-18 16:18:22', '2024-02-18 16:18:22', '2024-08-18 16:18:22'),
('d622a3cd0d29d05c4af3cc16518fb5d7e4df2cb7ded150bbb340454a76556cb0f66d6c01fce21f1f', 62, 1, 'MyApp', '[]', 0, '2024-05-30 15:06:17', '2024-05-30 15:06:17', '2024-11-30 15:06:17'),
('d6247acb26f3cb8790c38739ae1e9c50c6800c87256a64ecb76a92c92800710021c3f3432c2157ad', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:05:20', '2024-04-29 16:05:20', '2024-10-29 16:05:20'),
('d64815d3c9b59aff54d2b97be59c6b3d876e5b0da2536dbf440d5ed464896d8e193f74a8fd459765', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:44:02', '2024-04-02 20:44:02', '2024-10-02 20:44:02'),
('d64f5f0b950862d04574ccf3e7802c008fce650c5bd962e38b94c6ae71bb07c0bc4545e642db048c', 48, 1, 'MyApp', '[]', 0, '2024-04-16 14:24:37', '2024-04-16 14:24:37', '2024-10-16 14:24:37'),
('d6a114e536ca2f3d529b27bde3a0b1c766225f695be6032fc6e92479fc28b6cea685cd3422e1bed0', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:28:45', '2024-04-18 17:28:45', '2024-10-18 17:28:45'),
('d6c7b46be6be4ee5c2aed0aa3ecf64f38ee0eff5e2dd59d7addad97f28209615773b72abde029b23', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:32:57', '2024-05-28 15:32:57', '2024-11-28 15:32:57'),
('d6d2818c83fc6bb427fa845c270d11c7a375081c60b8e17422c466a8d0bdd9d821d17994124bb1e2', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:35:16', '2024-07-16 10:35:16', '2025-01-16 10:35:16'),
('d6e3c26d5f68bdfe6c35a3b55cac0a4103c927f48e21f15853e785b380c3db9604e20d96c2d3f9f4', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:18:30', '2024-07-12 15:18:30', '2025-01-12 15:18:30'),
('d6f84e23b0e9b991b1ec266878e47452df0b7b9b7f25a91e719206ba8fbdda3dc773bd2311271477', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:56:08', '2024-04-08 18:56:08', '2024-10-08 18:56:08'),
('d701525d705bcd7f198fbbf3b7e5a5e8f83cb6de10f7b386ce0e7622f651f08121dabe425b3c6061', 58, 1, 'MyApp', '[]', 0, '2024-07-04 10:16:51', '2024-07-04 10:16:51', '2025-01-04 10:16:51'),
('d727b0f588a2fc25732ed1e8f997b6532fea03eba4cd28a25c7ff5f0b66d1e2ccf765cb94407be84', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:57:37', '2024-04-28 13:57:37', '2024-10-28 13:57:37'),
('d7450a8fe1a8b4927394e3fd98c2c314e0321ffd6f6a8d6da0fd30fc567cb57e67e6e39a639625b9', 62, 1, 'MyApp', '[]', 0, '2024-05-30 12:10:54', '2024-05-30 12:10:54', '2024-11-30 12:10:54'),
('d7a333fac16434454db5862dfc8e790947f805357ac9fc676f5ed6e529a90a92a197aaaf7c15b45f', 58, 1, 'MyApp', '[]', 0, '2024-05-08 10:17:47', '2024-05-08 10:17:47', '2024-11-08 10:17:47'),
('d7accde7d3a652c8a1de4df94ce26889bb22160ee0e12fa1d4a48ae27a65fcc18e6d597228bb1488', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:09:19', '2024-04-23 16:09:19', '2024-10-23 16:09:19'),
('d7b6fb588643e4de7632224f4ed33705fe5c7301116dfcb37eaaf244005763e593c4998cc8e461f3', 52, 1, 'MyApp', '[]', 0, '2024-04-30 12:10:35', '2024-04-30 12:10:35', '2024-10-30 12:10:35'),
('d7e783ec80239a07ee423f2e2987b55f7393f82a9d977de765a1fcfe0ffc92c364fdfb7b5482033c', 58, 1, 'MyApp', '[]', 0, '2024-07-07 16:20:36', '2024-07-07 16:20:36', '2025-01-07 16:20:36'),
('d809b176c4faf617736ee9178e370b5e3dc265bf4a929cc9592292c02a6db6558ee544a62e59df4c', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:39:30', '2024-04-23 13:39:30', '2024-10-23 13:39:30'),
('d983b7c26267cf09166c0529c85b46eb0292d4016a47b043db3f5ea15657da32c027e04a74fbad90', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:37:49', '2024-07-16 10:37:49', '2025-01-16 10:37:49'),
('d9c6b8b6b1780709c647b6d2626181b6d091e400a449f8893197e07a0ad605e5b2a59f5b3804bd04', 66, 1, 'MyApp', '[]', 0, '2024-07-16 08:24:31', '2024-07-16 08:24:31', '2025-01-16 08:24:31'),
('d9f12fccc049f09f1b233735a296f0737c7e74982e1baef4885df04dc87b38209c3cf661bed1e831', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:30:04', '2024-04-21 16:30:04', '2024-10-21 16:30:04'),
('da02306f2c5687811b44e2ce60c7366964e0677778582a9501c0499e6e902fac2b32bd0a95cf2b20', 5, 1, 'MyApp', '[]', 0, '2024-04-16 14:56:02', '2024-04-16 14:56:02', '2024-10-16 14:56:02'),
('da97c9809bed6ed35bba172063bfe920204d4dfafe8e5e9abeddcf6f9462b5426ab5643dc5cc6df4', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:07:00', '2024-04-23 14:07:00', '2024-10-23 14:07:00'),
('daa16d954ddc8bc6a21a422bff70b0a098508579d0c8f4181b6b813c5fafce642641d23c5df36fc0', 27, 1, 'MyApp', '[]', 0, '2024-03-14 14:55:46', '2024-03-14 14:55:46', '2024-09-14 14:55:46'),
('daa83875495d99f87143da29a997a279a69f7bcf0cc2d78178b64bacc64c3add5a295f056bba9fc2', 64, 1, 'MyApp', '[]', 0, '2024-06-05 12:41:29', '2024-06-05 12:41:29', '2024-12-05 12:41:29'),
('dad496eb438f4d75ab11d9ca4d8b01c3340122ba6221a4dab264009a4ea5d9b882cb2b7a87e0bd6f', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:48:49', '2024-04-15 16:48:49', '2024-10-15 16:48:49'),
('dae19f65e344bf4e2cd2c0c64534b6f20b374509e1d67550cdf94e4bddade503516f07b1a94d04d6', 58, 1, 'MyApp', '[]', 0, '2024-05-22 10:43:09', '2024-05-22 10:43:09', '2024-11-22 10:43:09'),
('db2e186c8bad668fa4429be0c38fa6f407bea6bb8e4ef1a01e8be8fb4217423917ea024270f1b989', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:53:24', '2024-03-05 11:53:24', '2024-09-05 11:53:24'),
('db4e5fc473c1d4a3be0353192dbf508dc367f286c5b9f04828cc73e14aac85977a42c4cbba66ea96', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:28:14', '2024-04-23 12:28:14', '2024-10-23 12:28:14'),
('db885b299e7f91f0488e808621e5613107bd30ebc4b00eaddbfbb8907ebb57991bf973f6a681dd13', 51, 1, 'MyApp', '[]', 0, '2024-04-28 11:12:34', '2024-04-28 11:12:34', '2024-10-28 11:12:34'),
('db97e7b27e16d55309345fbae20a0aa930c5c588485dec957268ced3482f683aec4d2f7dd4d6b77f', 10, 1, 'MyApp', '[]', 0, '2025-08-17 15:00:56', '2025-08-17 15:00:56', '2026-02-17 18:00:56'),
('dbab24f01e927c00b836da0bcc9ac14dbe881deddf073e4b880e8cae79d1f04b6abbc401108330d5', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:34:44', '2024-06-05 10:34:44', '2024-12-05 10:34:44'),
('dbbd5d93aaafadc8d3869c4a9712b12a196da51fc0f527efd53402b0763edcc583c7db8bea2cf3e4', 5, 1, 'MyApp', '[]', 0, '2024-03-05 11:51:49', '2024-03-05 11:51:49', '2024-09-05 11:51:49'),
('dbce82fbfb554d898299d5b1838f7f9f021c562afd0f112ec06159320f2e5e4d5473f428799ac464', 5, 1, 'MyApp', '[]', 0, '2024-04-18 22:32:56', '2024-04-18 22:32:56', '2024-10-18 22:32:56'),
('dbe72adcb01597b12e071fc407839e9167a0e5c845662f46d42b8856925ea5ee12b7a07009100c5f', 58, 1, 'MyApp', '[]', 0, '2024-05-13 11:04:01', '2024-05-13 11:04:01', '2024-11-13 11:04:01'),
('dc3c945dc69832cf0c5d321e3203ad3be96359dbd1d8a20aa8c9eaff0f98d6dfec59bf466168bda5', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:35:23', '2025-08-14 13:35:23', '2026-02-14 16:35:23'),
('dc45a44a534369f47e8dc603938fe7b6426e87426bf23ae9381cd92a82f463df55991edb96f4c6b5', 5, 1, 'MyApp', '[]', 0, '2024-04-21 15:53:22', '2024-04-21 15:53:22', '2024-10-21 15:53:22'),
('dc65e1f74509950a9225d813fa809496fb8cdb5945b42a8d61314b82630d0d255e13138444c65b91', 5, 1, 'MyApp', '[]', 0, '2024-03-24 15:36:24', '2024-03-24 15:36:24', '2024-09-24 15:36:24'),
('dca380db1e4dbac394ee20d96cafbf79f538434d8ae7b8196171934e1ba273fb4af96b5273f52a48', 66, 1, 'MyApp', '[]', 0, '2024-07-12 15:34:56', '2024-07-12 15:34:56', '2025-01-12 15:34:56'),
('dcaa75066f7bca9f3dcfbe098d422cf9b87dc5012e9080b1a7b7e8a7f1723487649346494b11e98e', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:29:29', '2024-04-22 18:29:29', '2024-10-22 18:29:29'),
('dd1feeabbd0ca3282edaa0f943ef336be4bcbaf180fa4f9fd8c25d3c4412b7c9b070057cd36bbe58', 74, 1, 'MyApp', '[]', 0, '2024-07-30 11:20:41', '2024-07-30 11:20:41', '2025-01-30 11:20:41'),
('dd48367e0ce06f4023a753c95d70f3e1b01e6381f3f5a78bde7d1cb2616a93575d94404da1a572be', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:13:48', '2024-04-29 14:13:48', '2024-10-29 14:13:48'),
('ddb065e5ce3e4ef6d173d89e8a571a4f1d9a49ba778ea02d3d4026388490a0f8eaa688f2b35aca3c', 5, 1, 'MyApp', '[]', 0, '2024-03-18 15:17:28', '2024-03-18 15:17:28', '2024-09-18 15:17:28'),
('de604c0d8e1e2f689702697f2874d4e09f6846240f2174f313aa5a5d5796e89b05b874403e477a57', 69, 1, 'MyApp', '[]', 0, '2024-07-23 13:58:27', '2024-07-23 13:58:27', '2025-01-23 13:58:27'),
('de9705486663eacec01fbd1097477cf57c8a58178ae189acfb03906116ae136d89b3c1b0153d702e', 2, 1, 'MyApp', '[]', 0, '2024-04-29 18:14:53', '2024-04-29 18:14:53', '2024-10-29 18:14:53'),
('debad07b3296e3662fbc9052cc9e10d764bc67b5cdd442c865b6d296130e8e4692ca57df3ef45cf2', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:12:41', '2024-04-18 17:12:41', '2024-10-18 17:12:41'),
('debc22a7e4047919f21d940bce0adaf5879003895f6da3395d157ba382ab17b73e8cb916be4834d5', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:53:19', '2024-04-23 12:53:19', '2024-10-23 12:53:19'),
('def9505294c77cd898cab31740364daeb174f940fa520fd56cf86404543a025ffdb009cc1cc3b876', 5, 1, 'MyApp', '[]', 0, '2024-04-02 12:58:19', '2024-04-02 12:58:19', '2024-10-02 12:58:19'),
('df9a2185a890a679d9aa04eb49038a4cf0ba635eb34917db89f136f6d46d35480fb50abb9769ddbc', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:21:30', '2024-03-27 12:21:30', '2024-09-27 12:21:30'),
('dfddaf10ea6f875a5a04e8c909091dbd44dfbff15179ec0d7dff4d28a233feab506b23cec4b8b72c', 5, 1, 'MyApp', '[]', 0, '2024-04-30 15:45:41', '2024-04-30 15:45:41', '2024-10-30 15:45:41'),
('dffa2b70fa9a8f71a0c2a624fcf3550795530cd20ce57ec692efc0f98d9a4d8a0331db649ccff8bb', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:24:38', '2024-04-15 16:24:38', '2024-10-15 16:24:38'),
('dffe17c690d8d93367409919cc86c631b688f431bcc02ba61fc6b30aa0972b71dc1d25e4fe773b47', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:52:21', '2024-04-15 16:52:21', '2024-10-15 16:52:21'),
('e0087742cf948b82bdf1085541c92cf46387bf1eedef09337c4a381e649933189ce53e5e6a178302', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:20:31', '2024-04-23 13:20:31', '2024-10-23 13:20:31'),
('e010c5c3fb0486a8e5832e6c5208c42cd6ca2e87eafdfc23597d3889048e75361e9c589b30961e34', 58, 1, 'MyApp', '[]', 0, '2024-07-04 19:18:46', '2024-07-04 19:18:46', '2025-01-04 19:18:46'),
('e0161b68c4c9e1107943944f56179425aa6fda4f7b0348b1ada98201420a0abcd2d7814e745dd56a', 5, 1, 'MyApp', '[]', 0, '2024-03-21 03:38:37', '2024-03-21 03:38:37', '2024-09-21 03:38:37'),
('e027a08bb30b258a9f4c5f83d7d457e0d1295f5e2e5259793f61918c1d21ab7b7d842aaf4fb6be89', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:32:15', '2024-07-12 14:32:15', '2025-01-12 14:32:15'),
('e027a4a30d62195da7d5124eb524ae114c40c398ba83ed43e833c078d8d3d1d952b7cefb05236f47', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:37:25', '2024-04-14 14:37:25', '2024-10-14 14:37:25'),
('e028d0d8018e5989139694be9c804f3c1b4056f22baa7bf4d13b31b1673e92aeb6038a898cba465a', 5, 1, 'MyApp', '[]', 0, '2024-05-01 10:01:56', '2024-05-01 10:01:56', '2024-11-01 10:01:56'),
('e038b9923ea435a825f7dfd5e81e4f58ce81f0954e2578aefa56a0be4aa2d22b9414886a4ad60348', 66, 1, 'MyApp', '[]', 0, '2024-07-10 21:16:29', '2024-07-10 21:16:29', '2025-01-10 21:16:29'),
('e0aeaecdedaf9a5099f115c6e44b1f182f4b8950e920ca1bd47e1327c09fb83c55919c6a269a8e73', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:36:11', '2024-07-12 14:36:11', '2025-01-12 14:36:11'),
('e12b2e3c52353c2a0a088052f64a62fcd92395d5a0949a7cf1c2c5b176ceff9d9809d9f9e80b44fa', 76, 1, 'MyApp', '[]', 0, '2025-01-30 11:15:51', '2025-01-30 11:15:51', '2025-07-30 13:15:51'),
('e143202efc212d576f123c75fe85388d289c79a9b400d44085754e29c1665f6b3aa5fefe15c1f484', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:53:45', '2024-04-18 17:53:45', '2024-10-18 17:53:45'),
('e1a83eff4644ce132b5497d053efd05e9ea5bd1602455c9884652400a1174912021921ffa5b9c443', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:54:27', '2024-04-28 14:54:27', '2024-10-28 14:54:27'),
('e1b6803b5622d7e469d9e2817f8885857b45be675493e081253d894f6cef573fe2b1cc620af9e0df', 5, 1, 'MyApp', '[]', 0, '2024-05-01 09:59:07', '2024-05-01 09:59:07', '2024-11-01 09:59:07'),
('e20177c079f4e41d763830b317c61c1498669e2fdfb00fae36b23b73fc3d9659a1679052c1f2c2f5', 25, 1, 'MyApp', '[]', 0, '2024-03-13 14:25:13', '2024-03-13 14:25:13', '2024-09-13 14:25:13'),
('e2568269f0e5a566e5a92971825baaa311d240608fe594ff86e4b3c59f6792960ce5e898682332e1', 65, 1, 'MyApp', '[]', 0, '2024-07-07 09:07:47', '2024-07-07 09:07:47', '2025-01-07 09:07:47'),
('e2aa9b479366c60bc962a64ae2b831b71308c4ab24d3450911c746e9a793723c63427c716a75a529', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:12:53', '2024-04-14 18:12:53', '2024-10-14 18:12:53'),
('e2d18af00c926554b30d009b2b60b3fde86bdad3f7c2e5b3ee2ec2ca7ce1586c831810cf6aaaa094', 58, 1, 'MyApp', '[]', 0, '2024-06-05 13:23:03', '2024-06-05 13:23:03', '2024-12-05 13:23:03'),
('e2d26fc0918779d873f814e8aba77b6d6ad74558d3715f7f604f2d79190c44fd2668b4aafd89e5ac', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:29:26', '2024-03-24 17:29:26', '2024-09-24 17:29:26'),
('e2d7d81e946872304884b850bfe637ffba621277edf5d6d68f19f3a15efa2a730a48cfb2dac0c1c9', 5, 1, 'MyApp', '[]', 0, '2024-04-24 13:49:53', '2024-04-24 13:49:53', '2024-10-24 13:49:53'),
('e309e5a833320b9562bd9eb58a539350d6bf824cca45914caca84dbf42e80f1e2bd1e4287ebb1a19', 5, 1, 'MyApp', '[]', 0, '2024-03-04 16:59:58', '2024-03-04 16:59:58', '2024-09-04 16:59:58'),
('e32f5c2daddb1015a608c1e9433005f017f110d6760447d043bd4d6ff1323ba71c35a62160789e79', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:30:35', '2024-04-24 15:30:35', '2024-10-24 15:30:35'),
('e33ce92875e076206cf2099d2fa2c1376185240532b60078f3746017845f1179f49e5a5fadcbadd7', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:18:19', '2024-04-28 13:18:19', '2024-10-28 13:18:19'),
('e3869beeab47a85de4d11337188c6f6b31bd29913ba281b5406eb34345ad1a2f004e4552cfb5252d', 62, 1, 'MyApp', '[]', 0, '2024-05-28 15:09:29', '2024-05-28 15:09:29', '2024-11-28 15:09:29'),
('e391d3c5fcdff567f608b3322ad456a1906bffae25df211b115c42c769858a0793a0fc02a3b9b585', 69, 1, 'MyApp', '[]', 1, '2024-07-16 10:43:46', '2024-07-16 10:43:46', '2025-01-16 10:43:46'),
('e3ed9c73971a738626720018a3480f3c83d2a03343ae76147926983668e93d918f89587c47c6a852', 68, 1, 'MyApp', '[]', 0, '2024-07-15 11:47:38', '2024-07-15 11:47:38', '2025-01-15 11:47:38'),
('e3f4f6aa7436c0d2dd09fcf23039a50ba1fa9aa4084a9c8153800fd2c41cd462994b60205dce8058', 5, 1, 'MyApp', '[]', 0, '2024-04-28 15:36:18', '2024-04-28 15:36:18', '2024-10-28 15:36:18'),
('e3f64efc5e5093ada73ee9586fd72d33c39efbe214182e8b9197cb5c856bb83aaee5417ba7e70ea4', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:26:19', '2024-03-26 16:26:19', '2024-09-26 16:26:19'),
('e3fe197284b91aeaa24f858d04f0a483a529264d5b67b3c7a1084013d58d584e9769eac51ccdd7d6', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:35:00', '2024-04-14 13:35:00', '2024-10-14 13:35:00'),
('e43a5e81b15c9ba8f5a20aa59ec3768f5dd15127cc25fabefe77958de79b21d943ae07cd31959312', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:01:10', '2024-04-29 16:01:10', '2024-10-29 16:01:10'),
('e442002a25c0d472cb4fc0a148d25369f93e9a579683a0161a3cc3dc88ee99d27c55d1700c6c4ce4', 5, 1, 'MyApp', '[]', 0, '2024-03-05 10:54:02', '2024-03-05 10:54:02', '2024-09-05 10:54:02'),
('e45646189c4c9170b7e07d3dde36ff1805b5df1a4f1a461941be862212957872ef7c1b792e02bbe1', 46, 1, 'MyApp', '[]', 0, '2024-04-15 16:24:39', '2024-04-15 16:24:39', '2024-10-15 16:24:39'),
('e4647afc4e1c935caaa4c6cbde479f5a83088b6abe952bbb2c53d409ef3b5199613df36768f174b7', 5, 1, 'MyApp', '[]', 0, '2024-02-21 17:46:53', '2024-02-21 17:46:53', '2024-08-21 17:46:53'),
('e4a951f3c3214d075a1bb6e0ccb2a81fcf3a17fee6b121dc21d5390a0eb09a5223b90d5296d745d8', 5, 1, 'MyApp', '[]', 0, '2024-04-02 12:58:20', '2024-04-02 12:58:20', '2024-10-02 12:58:20'),
('e4c6ffb2b188d475cb37824db24f733e701d92b3659e9a7caeacec09b88bbba08419c88f548d8e66', 58, 1, 'MyApp', '[]', 0, '2024-05-22 11:24:24', '2024-05-22 11:24:24', '2024-11-22 11:24:24'),
('e4e01ce04418bc8b242f375216854727204f4c5e93f1a500dfdaadad55f8fc97d393f9612056495f', 40, 1, 'MyApp', '[]', 0, '2024-04-02 20:43:18', '2024-04-02 20:43:18', '2024-10-02 20:43:18'),
('e4f5b7402ff73c5b4e3776842edcbbf8031e6c723fd0d5cbd543ded350a84b83c432253777f060f1', 67, 1, 'MyApp', '[]', 0, '2024-07-11 11:45:39', '2024-07-11 11:45:39', '2025-01-11 11:45:39'),
('e50567cd6107b152538f7634d38073978862c707dcceaee7189c32e8083bdb03dc2fc01d1a13f22e', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:58:51', '2024-04-02 16:58:51', '2024-10-02 16:58:51'),
('e5581aa670c4244aca2488eea77ab9a00d05b692b334edfdf780ca42d4507c62b028b1dcaecd83b7', 5, 1, 'MyApp', '[]', 0, '2024-04-18 12:46:32', '2024-04-18 12:46:32', '2024-10-18 12:46:32'),
('e57a1f885748aa5e8ec8cac81f302cfb931ff4638aae703690012c1b44477f95d4db4b2898ec6d75', 5, 1, 'MyApp', '[]', 0, '2024-03-20 05:24:14', '2024-03-20 05:24:14', '2024-09-20 05:24:14'),
('e58d6d96298a95bc7c7570d2758d365cdfc7b01039e6c29acbab320ca9371d256b91e115fb2b1391', 5, 1, 'MyApp', '[]', 0, '2024-04-02 16:09:23', '2024-04-02 16:09:23', '2024-10-02 16:09:23'),
('e5acda1927ef6832fc455822be2c5ed0718f1d66efc4883731d8d9a26bb14b510d0f7f531369e77e', 51, 1, 'MyApp', '[]', 0, '2024-04-28 10:25:06', '2024-04-28 10:25:06', '2024-10-28 10:25:06'),
('e5b4b59bedb3b41713231959388b3722522494e37b77db3b22b0e457837a62bb74af49da68f3329f', 34, 1, 'MyApp', '[]', 0, '2024-03-26 16:27:14', '2024-03-26 16:27:14', '2024-09-26 16:27:14'),
('e5ba90ce1d41551cdd49ebdd88b1e8e0ccf3c5d56c6bfccc8fc53c41782b6ea3633074125ca26210', 5, 1, 'MyApp', '[]', 0, '2024-04-17 17:44:28', '2024-04-17 17:44:28', '2024-10-17 17:44:28'),
('e656f3118f37004411275ff972797bdcd021046a7cc5c4d12cc1ce7021eb9a0234eb2fdb6d65de89', 5, 1, 'MyApp', '[]', 0, '2024-04-21 17:55:12', '2024-04-21 17:55:12', '2024-10-21 17:55:12'),
('e6675b41064f8adec54d89eaf3f826c00915612cc730b2bf35fd36da22450b519930323618d7aa9d', 5, 1, 'MyApp', '[]', 0, '2024-04-08 18:44:06', '2024-04-08 18:44:06', '2024-10-08 18:44:06'),
('e6782be9be0931b90200d31ea27b82d3ff4f2ab7ca76200304148d2d300a3b1ecfc633be8d22ed26', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:18:43', '2024-04-22 19:18:43', '2024-10-22 19:18:43'),
('e6819fecb2ca70422fffc735f293f7c4af3b57ac800f41e5f6e3fc148a20c9b26ceab628acc4225f', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:44:02', '2024-04-02 20:44:02', '2024-10-02 20:44:02'),
('e693cc59b1eb4ff6b090709140187c0ad9977ce41f3fcc125d7dcf22c5ced46b2e8efc359910f817', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:31:36', '2024-07-12 14:31:36', '2025-01-12 14:31:36'),
('e6b62e8fbf5897969b6f4d6dc043304a92d0116aa4b7776a0d7e322236e55070243fead2066f898a', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:12:31', '2024-04-21 14:12:31', '2024-10-21 14:12:31'),
('e6b78ac80667f9558cd8d56289cb0e8fca19cf645cf9af5de319b2ad3b8ac51ffb7eb1c9846ee115', 35, 1, 'MyApp', '[]', 0, '2024-03-27 15:15:13', '2024-03-27 15:15:13', '2024-09-27 15:15:13'),
('e7329b199a34fe71e73a13e9076f933f806817e2b274fd9812e37770dfe69024331c84830ff73500', 58, 1, 'MyApp', '[]', 0, '2024-05-13 12:24:08', '2024-05-13 12:24:08', '2024-11-13 12:24:08'),
('e7424f41d224b9dbb69842b0683d889f5ce45e5379c6f37ccdc0308aac710657ae2104373d5d79a3', 5, 1, 'MyApp', '[]', 0, '2024-04-30 14:32:34', '2024-04-30 14:32:34', '2024-10-30 14:32:34'),
('e74cdfbfc599db1c1907ab21719fac3425c8be435a4d4e370116913796c353b6bcc0a0082a2b32e3', 5, 1, 'MyApp', '[]', 0, '2024-04-28 17:59:21', '2024-04-28 17:59:21', '2024-10-28 17:59:21'),
('e76d248461e8990b2ee6c28f004b2689862cb3ae2d7c206127d0e31a1bfd367927e4f5fab4fbf4bf', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:22:57', '2024-03-24 18:22:57', '2024-09-24 18:22:57'),
('e788b8dbfa6bd910221ad6e6515fcec76325f99623fc0595cc6ee5171e21c055d7071e9ad0c9c28b', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:41:04', '2024-04-17 16:41:04', '2024-10-17 16:41:04'),
('e79ee64be017af01070a5e06d3596f44415039506a726df66b46752dd6bcd245ec00c08ca8afb64f', 10, 1, 'MyApp', '[]', 0, '2025-08-14 12:34:01', '2025-08-14 12:34:01', '2026-02-14 15:34:01'),
('e7a1dea55e0d549419eec5c55ae4d7d22cab820b18d9a87d9ac6e206b943400eadd3d39115589c69', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:27:29', '2024-05-13 15:27:29', '2024-11-13 15:27:29'),
('e7d2a0dac74a3943a5025096164a79d2a37b19b10186fccf92d0a42aa8fe37c78f1d58fda8857282', 5, 1, 'MyApp', '[]', 0, '2024-04-30 16:40:02', '2024-04-30 16:40:02', '2024-10-30 16:40:02'),
('e7ec604aacd7645807200a785f1d59f2c7f630eb56c7b4fc6e3ee7d71bb4fd3bb49cc7f3c4084904', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:28:45', '2024-04-18 17:28:45', '2024-10-18 17:28:45'),
('e80dbbcc1e7f639be7ca1a3e90875c5dfa9d6feb19a48127b92a829ae5089601471bbc4a25f29fcf', 58, 1, 'MyApp', '[]', 0, '2024-05-07 13:28:53', '2024-05-07 13:28:53', '2024-11-07 13:28:53'),
('e81d03d8ee8e7bb11f0ccd189547084160f52e800a2826ca70220724b5dd173d6ebb3ebc39b437e7', 5, 1, 'MyApp', '[]', 0, '2024-03-07 18:59:11', '2024-03-07 18:59:11', '2024-09-07 18:59:11'),
('e82a46146cbfcd8e5a0eb8111ac785082c413f221a99b7cee641008d237aeeb2c874ab5bf877920d', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:40:18', '2024-04-18 20:40:18', '2024-10-18 20:40:18'),
('e82f178975dd16c3edad2f62067d0d7438eb061a74db3add69eb6f37ccb147b62f4599a20a69dc5e', 5, 1, 'MyApp', '[]', 0, '2024-03-06 17:26:10', '2024-03-06 17:26:10', '2024-09-06 17:26:10'),
('e8705e7ce8432aa96c3876253ee1afb961695f00cc91e734a3b7900f11bee998c20fa4e70b411715', 5, 1, 'MyApp', '[]', 0, '2024-04-29 15:57:12', '2024-04-29 15:57:12', '2024-10-29 15:57:12'),
('e8858cd211af56f7a2a71f1a198c57259283d824cafb612995bb20e026257fd70317caf4be316b13', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:56:52', '2024-04-22 17:56:52', '2024-10-22 17:56:52'),
('e934337eaa9e9e68ab3c9db7353576e157f35a09fe0f3858b779bb808a3a0c41034fd6127ae74984', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:37:14', '2024-03-24 18:37:14', '2024-09-24 18:37:14'),
('e969e08c3c56d3a2a45f819e467bd00f7e3a2fc56d7f156ccbc997418158aaac118db8c3ca9bb24a', 39, 1, 'MyApp', '[]', 0, '2024-04-02 11:50:08', '2024-04-02 11:50:08', '2024-10-02 11:50:08'),
('e992c294b2387472f165ea30ea7dfc63ce51c9ee4b6d13a37ac805c9fccaae81e1d7ad59fb81c937', 5, 1, 'MyApp', '[]', 0, '2024-04-02 20:39:38', '2024-04-02 20:39:38', '2024-10-02 20:39:38'),
('e99390ae52754d4bfa09844898b0502608ea1e9c284e756bebadea7f75d545c638144df5d0bac5ba', 66, 1, 'MyApp', '[]', 0, '2024-07-13 13:35:17', '2024-07-13 13:35:17', '2025-01-13 13:35:17'),
('e9a75c367efcdeb5f343d60f78d91b3b00a380c8009ebba619461e5935fb13ef22a9998fd2d4ea51', 58, 1, 'MyApp', '[]', 0, '2024-05-21 15:28:13', '2024-05-21 15:28:13', '2024-11-21 15:28:13'),
('e9aaed2954fa54cafd56c487f887813480346c534e0c1a6a7c4932c4ac1cf7206a1b31635a5c0295', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:35:41', '2024-07-16 10:35:41', '2025-01-16 10:35:41'),
('e9ab04eecaef62efcf50786f1617f355147e1a461b48a5bd1db9f3b8424da852ad19368611d71ff8', 5, 1, 'MyApp', '[]', 0, '2024-02-14 16:26:41', '2024-02-14 16:26:41', '2024-08-14 16:26:41'),
('e9f6f552ca22a7d87f38d15e938a351b1c3f0300c99d1e8be92fe504e4281615a1f5677e28057820', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:21:18', '2024-07-12 12:21:18', '2025-01-12 12:21:18'),
('ea16fb876886f3c5f3f9e63b4c62a4379f4f182ae519af035db3b603e0e068f17dac9c80e91961ac', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:33:59', '2024-03-19 22:33:59', '2024-09-19 22:33:59'),
('ea1e775b58ef43c1ba45aefaaa7d5816fb45b5e1eb176a41a127152a579fb1f84e576c3ff95f7d9c', 10, 1, 'MyApp', '[]', 0, '2024-02-15 22:05:14', '2024-02-15 22:05:14', '2024-08-15 22:05:14'),
('ea4e66bf422f16edb07d73514e11bdf4565e818ee9c86e49e0924ef5566da1df1fdc6ef901224477', 18, 1, 'MyApp', '[]', 0, '2024-02-21 18:29:59', '2024-02-21 18:29:59', '2024-08-21 18:29:59'),
('ea5b4617fff3b4c4ae613bff9f88b8fef24f365e24f19b513d67d697e9e3eb347d7f768b16a261ca', 5, 1, 'MyApp', '[]', 0, '2024-04-16 15:26:43', '2024-04-16 15:26:43', '2024-10-16 15:26:43'),
('ea5be0f15c8d73af2b352e031ebdef2299e3d27715824ad74d90f173428ddfcb9a12a3527b358e7f', 35, 1, 'MyApp', '[]', 0, '2024-03-27 15:15:12', '2024-03-27 15:15:12', '2024-09-27 15:15:12'),
('ea6d16d351d7573210a8bdd33e106f07e9771c374bab71199aada30e74516f286992c0197834061a', 5, 1, 'MyApp', '[]', 0, '2024-03-05 12:04:43', '2024-03-05 12:04:43', '2024-09-05 12:04:43'),
('ea8465d0e6da848e1bbf70c82974a110240aefce953bb20d43f85ba6dd86bf714c5abea26643b822', 5, 1, 'MyApp', '[]', 0, '2024-03-21 13:40:20', '2024-03-21 13:40:20', '2024-09-21 13:40:20'),
('ea9170092a169fe961dbbad74c236fe2039395a0fe31f4d73815f6e29d710ddae39a8affa7eca67a', 35, 1, 'MyApp', '[]', 0, '2024-03-27 12:29:32', '2024-03-27 12:29:32', '2024-09-27 12:29:32'),
('ea975b8f383dada809b421b0998160dd7fd334e2fdc5511928393308ae0c32ce9db0591a53b7f8bb', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:33:31', '2024-06-05 10:33:31', '2024-12-05 10:33:31'),
('eabfc62db347db71378b38f14b8a2c22e860e1f9e4a3ab46a6bb6c8538c376b0661512145d70043e', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:35:24', '2024-03-24 18:35:24', '2024-09-24 18:35:24'),
('eacc9df6fb25e6bf7f1b11c41875cdfcddcbf5722fbf3e4163a1bf38f5737e42d17f56b018501cc6', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:09:43', '2024-04-28 14:09:43', '2024-10-28 14:09:43'),
('eae3f2fb06197a7ff92686dfa2c5d49abb35c2f808cde59c8e25251b124838b5fcb51094f55cbf7e', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:04:21', '2024-04-23 14:04:21', '2024-10-23 14:04:21'),
('eae6a0368c870267ea793c784d9ddc715e1c4fa300c63af91d3fd5ac44adab85481f1ce5e1dbc484', 1, 1, 'MyApp', '[]', 0, '2025-01-30 11:17:26', '2025-01-30 11:17:26', '2025-07-30 13:17:26'),
('eaef08893f767d3a23d3a1b7f676a8ee10bd5094a82fc865f17cf15f25be979b04d5c15b876527d1', 5, 1, 'MyApp', '[]', 0, '2024-04-15 13:37:16', '2024-04-15 13:37:16', '2024-10-15 13:37:16'),
('eafa4fc54a0fa6e8e4e17a4f5d6171ec83c6361199d86be8d9ca8db9e94806c8f6aed57e0af927d5', 33, 1, 'MyApp', '[]', 0, '2024-03-23 22:21:36', '2024-03-23 22:21:36', '2024-09-23 22:21:36'),
('eb0da33206ba7fce7a965e37894def6d3a44563ccefb005969510643ca6c6ada5a7376601d754041', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:05:15', '2024-04-17 16:05:15', '2024-10-17 16:05:15'),
('eb0e11c2046440165d4181e1eadc2ca91b42dd48588c9adeb10241f0c6b7d6933b128a7b98835620', 35, 1, 'MyApp', '[]', 0, '2024-03-26 12:21:10', '2024-03-26 12:21:10', '2024-09-26 12:21:10'),
('eb0e3ffbf17e2fa9cd68636698b0550c3e9d74b5e7de8531148883f6f486652685c52550503450b8', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:22:14', '2024-03-27 13:22:14', '2024-09-27 13:22:14'),
('eb3ae65c206d01ae91fb8d8153231ca08bf8c67006bd4c3f58b709bee61f1ac129f09521a508e8f0', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:00:21', '2024-04-29 16:00:21', '2024-10-29 16:00:21'),
('eb41b08c78b5a5b1ac73aa821ece6661b1b9cfe0eff181455cd95b99e49472ae0d9dde0be8b329cd', 11, 1, 'MyApp', '[]', 0, '2024-02-15 23:11:00', '2024-02-15 23:11:00', '2024-08-15 23:11:00'),
('eb671f12d52843b5f8c884ee078ecea87e35d774499693e119130359b7f1ca746f2729ee58876b16', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:18:01', '2024-04-21 13:18:01', '2024-10-21 13:18:01'),
('eb6b827084f5cb2c7c20bd39404a1e6cda69dbf1e7b0a082798c83c4c3b148202f8b6f346b67aca3', 62, 1, 'MyApp', '[]', 0, '2024-05-28 20:26:35', '2024-05-28 20:26:35', '2024-11-28 20:26:35'),
('eb8c0e2386456c1fb04a9dfa34495f6303526fa8a7ad208ddc57050cb3b958318023cbf3b82f8336', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:24:52', '2024-03-19 22:24:52', '2024-09-19 22:24:52'),
('eb8f1677ceca509b2d32eb831100cdb75bde1f933e491fb8b9ce0f337e1cddf487ce998d20420a35', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:40:04', '2024-04-18 17:40:04', '2024-10-18 17:40:04'),
('eba9bb38e4d3c5aa4ec24f11288866fd7877e3f1286e5afbe7c36111aa0656bd475db8a121db9ea2', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:15:54', '2024-04-30 13:15:54', '2024-10-30 13:15:54'),
('ebb392bfb7d4ddb067f40c20b3f06b017144b71afd43972db29a2c982224e2de2ded430864baf7ef', 5, 1, 'MyApp', '[]', 0, '2024-04-17 16:41:04', '2024-04-17 16:41:04', '2024-10-17 16:41:04'),
('ebbfa20f5bfad121c7af1f1c68c5191315533ad99142c4723d2fca3d410aea407ef4a95317351824', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:54:30', '2024-04-18 20:54:30', '2024-10-18 20:54:30'),
('ebc3484fc0e1830895dd10f72b021562f0b7c8b6bcde87b72728881d1c30bc580942947ceac54d98', 14, 1, 'MyApp', '[]', 0, '2024-02-19 12:08:29', '2024-02-19 12:08:29', '2024-08-19 12:08:29'),
('ebc8ad7d758924115defa60e87e157f5368df891be2974d23789ad5ef8901c5a5e692491eb3c860a', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:09:32', '2024-03-24 18:09:32', '2024-09-24 18:09:32'),
('ec1336ec44dad7903a2d29aef282e892c000a11d7880875e2966f6d8cfdab313428a7ae53feccd4f', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:59:00', '2024-04-28 14:59:00', '2024-10-28 14:59:00'),
('ec379de667ec77f6954266c97f753926840698b572eacaef52711139c4ba303b7cd033702494023c', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:48:49', '2024-04-16 19:48:49', '2024-10-16 19:48:49'),
('ec4cad81027d12ef93a89353ab02ef7ff0fe9a068ba4bbef89f415ce5d4e1bfcb05fb09fa4bfbb62', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:43:54', '2024-03-24 17:43:54', '2024-09-24 17:43:54'),
('ecbf6c44587888254d018d829fa1961d9757ae52edd122d702ec29bfb55107158e1b21e7d0eaaec1', 58, 1, 'MyApp', '[]', 0, '2024-06-02 13:58:14', '2024-06-02 13:58:14', '2024-12-02 13:58:14'),
('eceb957239fd0f56052219e06c786137f68db426c51ecf73ca4b58e33d708c76076cff7eb75d7d39', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:08:13', '2024-04-23 16:08:13', '2024-10-23 16:08:13'),
('ecfba0e3ca24ebf9e1899f7fbd405c51a3fd5900413bb19506bda55f39076607f73945113979ce0b', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:51:47', '2024-04-23 14:51:47', '2024-10-23 14:51:47'),
('ed237191dd8aa1c4436f9343aa7a5e90d992353b9369e0b2c9d42d5da4b7377d9fd9c4e1766df1ba', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:11:06', '2024-04-17 15:11:06', '2024-10-17 15:11:06'),
('ed4979d2d0378be0e03a7fe92079cef9517e27fe75672b3c8fddd21b58dc71cf617c5067518953cb', 35, 1, 'MyApp', '[]', 0, '2024-03-31 15:37:16', '2024-03-31 15:37:16', '2024-10-01 15:37:16'),
('ed79d9ca7cfee6c723f02f13d12a87172a1f933fc361dd2c9a65858d74da9e017d0eca3ca8120f9e', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:39:00', '2024-04-28 16:39:00', '2024-10-28 16:39:00'),
('ed92777477de7d39c1d5204b18c7a8c8352de5fa29a9440163c45582479a4f69804da9c5d83d6f8c', 5, 1, 'MyApp', '[]', 0, '2024-04-18 21:03:09', '2024-04-18 21:03:09', '2024-10-18 21:03:09'),
('eda906341793381fe9afd11720441b453572b6bc7f88af0d4205895fb169f38fad59efd925423c65', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:09:56', '2024-04-14 18:09:56', '2024-10-14 18:09:56'),
('ede415d3836673117ef5f09db7ffa7b9fe11c9be8a1e2fa96deb563b3f85a846deeabfb5584691e3', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:59:13', '2024-04-18 17:59:13', '2024-10-18 17:59:13'),
('edf4a6e3bf5d74c126048ed27b065625d71fa14d9b37610e7dcb0873a3b19c3fe71829e50d71ce7c', 35, 1, 'MyApp', '[]', 0, '2024-03-24 18:06:32', '2024-03-24 18:06:32', '2024-09-24 18:06:32'),
('ee079a5643082f3c33ec63f46a1d255725fc47c02aee59122eed1c3cf37c8935e6766733e269152f', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:00:22', '2024-04-29 16:00:22', '2024-10-29 16:00:22'),
('ee1c0506a500fbdba703ba7bf609371b77a6fee0b4c72a3f6356501333b3e1b17a99a3e4232016e8', 5, 1, 'MyApp', '[]', 0, '2024-03-05 12:44:25', '2024-03-05 12:44:25', '2024-09-05 12:44:25'),
('ee2dcb9888518948379f4a6131f5b66f023cf4b72bb95c9d42d3d211ae26ae4984463f5308132262', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:03:14', '2024-04-29 16:03:14', '2024-10-29 16:03:14'),
('ee7900872e8579f1209dbbbb448345fef9ec5d593a75d7aeb5a5bec7fa5b90954c1f16942ab19a29', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:51:46', '2024-04-23 14:51:46', '2024-10-23 14:51:46'),
('ee83b1009c1b852c00e98dd707fbf6284dd3d0ff5261b062f39d32cd1e8276a928fcbea4a7135f92', 48, 1, 'MyApp', '[]', 0, '2024-04-16 14:29:53', '2024-04-16 14:29:53', '2024-10-16 14:29:53'),
('ee88f448f2c981e4d86cd939db97dfdf95ee07f40eb5a58477475ce20a7d5208bbc05470ca6db3a0', 11, 1, 'MyApp', '[]', 0, '2024-02-15 22:07:49', '2024-02-15 22:07:49', '2024-08-15 22:07:49'),
('eea8dfa707c796b3ca83ea2bdb1f37501efae40fef07d20ce0cce614627bd1fb6d7c62d72d12c13b', 69, 1, 'MyApp', '[]', 0, '2024-07-16 10:34:27', '2024-07-16 10:34:27', '2025-01-16 10:34:27'),
('eeb68f99bb6bb082de1f6031792865742492ca6429268764a97a538e656eaef42376969070ade16c', 5, 1, 'MyApp', '[]', 0, '2024-04-22 12:38:51', '2024-04-22 12:38:51', '2024-10-22 12:38:51'),
('eec7888d9dc7f737dbc20fdb70a467ec0d0ea034968cafc83ffdd77b1c8deca56ba84a722c7d5383', 62, 1, 'MyApp', '[]', 0, '2024-05-29 20:21:23', '2024-05-29 20:21:23', '2024-11-29 20:21:23'),
('eeca5f7bf52e663dbfd0e67dc6bd22c5f230a5bf66375823f2708e0219598e63540702bfc5da55ed', 33, 1, 'MyApp', '[]', 0, '2024-03-24 13:37:36', '2024-03-24 13:37:36', '2024-09-24 13:37:36'),
('eecec95a4523f7b9b54f7dd338dd75a7a58f3d6de53d89f16be0fc277ea7b3be85f3c7e5af504958', 35, 1, 'MyApp', '[]', 0, '2024-03-27 13:23:21', '2024-03-27 13:23:21', '2024-09-27 13:23:21'),
('eee3a126bc778748ff7cc7135478a1970e52ca605cd74fdbd6fd123303c4af098d2a4f2071addc44', 5, 1, 'MyApp', '[]', 0, '2024-04-30 13:12:54', '2024-04-30 13:12:54', '2024-10-30 13:12:54'),
('eeedb6913761c9952d3af00d184ded166e0a12e9dd3ab520fb39b2f2fdbe9893aa3e3d3634008cb8', 5, 1, 'MyApp', '[]', 0, '2024-04-22 19:17:42', '2024-04-22 19:17:42', '2024-10-22 19:17:42'),
('ef18ceef3b00a9eb079425b6dc82c3b208876c8f144fd2bfee0525cd0cfaf096694fd00c905cf0a4', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:38:28', '2024-05-13 15:38:28', '2024-11-13 15:38:28'),
('ef372d499ea7bf914e4811e4f2d163ac7f05878148ff9c804d8cf52c7a5b501ae27cc7bb6a95424b', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:42:38', '2024-05-02 12:42:38', '2024-11-02 12:42:38'),
('ef839eccc4dd7770f6d9111c74bf629a5aaa7f5eebd4e11c70ee016b650c2c2736cfa1ad3fe69443', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:55:39', '2024-04-28 14:55:39', '2024-10-28 14:55:39'),
('ef9c2ee05fca8be392da53820f7b61b8dd94639dbe18b6b57bb0725af3e075f7f3c81046cce830fa', 58, 1, 'MyApp', '[]', 0, '2024-06-05 11:24:06', '2024-06-05 11:24:06', '2024-12-05 11:24:06'),
('efab52f93fec2578ab26b265740cae8663777d111d5cdff22ffed7b3d1e27f04b9caf03ea2583ca9', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:48:01', '2024-04-28 14:48:01', '2024-10-28 14:48:01'),
('f00fe917714391380a0b6308fe9b0b4650d0311aca6be1f4c16c289963dc421ff024d68d88582c47', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:52:49', '2024-07-12 14:52:49', '2025-01-12 14:52:49'),
('f07eb687582f25ba12b4f0ded82020c4c3694546fe681fa7e3c6aacbba6711f2adb6fdbbc8ba6638', 35, 1, 'MyApp', '[]', 0, '2024-03-31 02:14:12', '2024-03-31 02:14:12', '2024-10-01 02:14:12'),
('f09c12f2d88b592834a0bd0c7ea45a9effc24b8e6e7a858ae691c9e989b54b61b8a8899a68f248e3', 5, 1, 'MyApp', '[]', 0, '2024-04-02 17:01:20', '2024-04-02 17:01:20', '2024-10-02 17:01:20'),
('f0a198e48d12371d6a5cd9a2c1c4f1740f7ef5173eb23ae29396127c8ba1328a3c6ae03dd25dad0c', 5, 1, 'MyApp', '[]', 0, '2024-02-15 10:19:12', '2024-02-15 10:19:12', '2024-08-15 10:19:12');
INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('f0e22e5772d9fdb314e5e93afa8a913c0dc215300cfbc1f8efd40f1d5de99b3c50bea8dbf3d9c41b', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:40:46', '2024-04-23 12:40:46', '2024-10-23 12:40:46'),
('f0ff1db3e4083057f760cde13bbb70d321617b0965dcdab14c85718a853430eec47f70d27a4ff82f', 69, 1, 'MyApp', '[]', 0, '2024-07-23 12:07:09', '2024-07-23 12:07:09', '2025-01-23 12:07:09'),
('f139ea5677abd683ce682462f187808f6dd019b0077a04e4e22abb0f9d3fd8888a99115e14b4d786', 5, 1, 'MyApp', '[]', 0, '2024-04-21 13:10:52', '2024-04-21 13:10:52', '2024-10-21 13:10:52'),
('f1568ca97d5069fbab1af94d6aaa6203634423dbd64f44188b1d13958379ef95a85b06fc5ad85572', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:18:40', '2024-04-23 16:18:40', '2024-10-23 16:18:40'),
('f17362966b840818519cc3c255fc7187fee2591fb83ff7adf2076c6db24ebac2f9f05f408ce2d5c6', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:22:11', '2024-04-24 16:22:11', '2024-10-24 16:22:11'),
('f1794302f721bfb716aa7b49668d557ad380ed344996726d8b0de4cd46dbbb3c2d8526bdc330ac24', 5, 1, 'MyApp', '[]', 0, '2024-04-22 17:07:30', '2024-04-22 17:07:30', '2024-10-22 17:07:30'),
('f1aafdebad4796c8386fcdea8cf7d256cdc553a88c4ea56bb9b30a2e098ac382c64d1d66a948cc8a', 59, 1, 'MyApp', '[]', 0, '2024-05-13 11:37:30', '2024-05-13 11:37:30', '2024-11-13 11:37:30'),
('f1ae8cf208769f3033e22e3335273c773cd3fd892eccc7b27a208527b3d1b8adbd33389a9c7cb0b2', 5, 1, 'MyApp', '[]', 0, '2024-05-01 10:53:53', '2024-05-01 10:53:53', '2024-11-01 10:53:53'),
('f22183ddd81ef66ef8829a82cbad44859cd2bcde4942e5496dbb1e5e77931a8bf094494b534909b6', 30, 1, 'MyApp', '[]', 0, '2024-03-19 10:18:22', '2024-03-19 10:18:22', '2024-09-19 10:18:22'),
('f2249300cb99cfbc59cd26cd79fcbeb925562932ac32132eb89bfd96b033de80311ee1cd41255bc9', 5, 1, 'MyApp', '[]', 0, '2024-04-28 16:07:20', '2024-04-28 16:07:20', '2024-10-28 16:07:20'),
('f253c64391cc2d1e59a3df8dcfe2bab6a97506dbbacda8005638c0e7a75a3687c93942145ff00616', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:50:12', '2024-03-24 17:50:12', '2024-09-24 17:50:12'),
('f261a6ba878675ed5c643f17e59706d4d9a053cb30c163af920d7f47fb43d1ce7a912e89a01fff22', 5, 1, 'MyApp', '[]', 0, '2024-04-25 18:14:44', '2024-04-25 18:14:44', '2024-10-25 18:14:44'),
('f28c7a2d571f04b38efd30e89d9a459f9e6026726fe271b5311395c9d053a5cdb3e87f9c8d459fd5', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:11:50', '2024-04-22 18:11:50', '2024-10-22 18:11:50'),
('f28dee468e181b18843dea388e244265941cb2f0a59967fa2604b8fb9f6b7b64a3c1d1845a49ee01', 5, 1, 'MyApp', '[]', 0, '2024-05-02 12:56:02', '2024-05-02 12:56:02', '2024-11-02 12:56:02'),
('f2abc586cff95d078ce345b630304abb8fb8266ea38cad7125bace4025da5a6d35c0bf90b59bcff9', 5, 1, 'MyApp', '[]', 0, '2024-04-08 05:25:11', '2024-04-08 05:25:11', '2024-10-08 05:25:11'),
('f2cc858ec21edcd9c5be2c4ade37fb81af3e757caa1262c177fb042761d77577991484c0650973ac', 5, 1, 'MyApp', '[]', 0, '2024-03-21 16:54:35', '2024-03-21 16:54:35', '2024-09-21 16:54:35'),
('f2cd3ecac9f11935eae81ab382ba2dccfcbb0b2ae1f87e0e2b74869f475e522307551d50e3543e5f', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:14:46', '2024-04-29 16:14:46', '2024-10-29 16:14:46'),
('f2d1a6585a7980b42c207227398e9a62e899b36dfd13c725937fda5a23d37581e8bd04ef1bd2454e', 74, 1, 'MyApp', '[]', 1, '2024-08-01 17:51:09', '2024-08-01 17:51:09', '2025-02-01 17:51:09'),
('f2da80bf3b6ee585ecca82c8c620fb29e0a8dda484960478a02287b7b266ce4ad6b19a24df6c8e7f', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:17:52', '2024-04-23 12:17:52', '2024-10-23 12:17:52'),
('f2e0b9e9d1b2eec4fcde20a80b6f555c6c40c1a6b00653d8d730cc9c0cd00dfee5194d0dedfde1ab', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:10:18', '2024-04-16 19:10:18', '2024-10-16 19:10:18'),
('f2ed94d4b0344cf57fc7a4ca12d2e26cf8e1f6d8c89f7aabc278d244f82f179a60661753af690bef', 5, 1, 'MyApp', '[]', 0, '2024-04-28 11:29:27', '2024-04-28 11:29:27', '2024-10-28 11:29:27'),
('f2f496fd33026511c46d6e8f87eb427723882d355b6db5056e18f995d1530b903563d8167256e310', 58, 1, 'MyApp', '[]', 0, '2024-05-15 09:30:40', '2024-05-15 09:30:40', '2024-11-15 09:30:40'),
('f35238f9c004554c98f08dc0e9908a5448a7d40a30ec1eb397a2d8929b6aa2bc60a43d8e0399b962', 5, 1, 'MyApp', '[]', 0, '2024-04-30 16:50:07', '2024-04-30 16:50:07', '2024-10-30 16:50:07'),
('f360585b6aeca46c7dfaabc23ec3456e5a0317e28cacc550ace54060afb027562a91abbb9efbe793', 49, 1, 'MyApp', '[]', 0, '2024-04-22 12:19:14', '2024-04-22 12:19:14', '2024-10-22 12:19:14'),
('f3871d669316107deb13d5ff8fae4d475d485851014b9072a34e2d87d0bb062c0d24ecebf34c7b5b', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:23:34', '2024-04-21 14:23:34', '2024-10-21 14:23:34'),
('f39048cb1af32f3439a289095498bbda8d4d928e99c04074747bf577827a182592aa79fe416cb5c5', 5, 1, 'MyApp', '[]', 0, '2024-03-07 11:56:36', '2024-03-07 11:56:36', '2024-09-07 11:56:36'),
('f3a881376c3ffbfc3a16efec54622e0a379e5f5bcf43ded602b4c5f293464e54befc7882415718bd', 2, 1, 'MyApp', '[]', 0, '2024-04-17 13:54:16', '2024-04-17 13:54:16', '2024-10-17 13:54:16'),
('f3c526a9aaf3d49d56c3d96d22e0767d5c91052e6f81451523bd99d2185980e46ebe38ba985eb992', 45, 1, 'MyApp', '[]', 0, '2024-04-07 18:49:33', '2024-04-07 18:49:33', '2024-10-07 18:49:33'),
('f3c6970751510dedfed54c1b46cd70f603172a23688ec2370a0fa28261af81abf0ee13790768af7c', 62, 1, 'MyApp', '[]', 0, '2024-05-30 13:40:38', '2024-05-30 13:40:38', '2024-11-30 13:40:38'),
('f452cc7338216da1d1bfd8de4159e17858005c5fdd7fec507897745674f78dcc9c20760999b49bc9', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:53:11', '2024-04-18 17:53:11', '2024-10-18 17:53:11'),
('f48851170f04d2573e419cf41fa52ccb8bf290b6050bc981b0af84d08bb9f53ffa2a830aab5149a8', 62, 1, 'MyApp', '[]', 0, '2024-05-30 08:29:17', '2024-05-30 08:29:17', '2024-11-30 08:29:17'),
('f498871b1daba6f571e303e84657aae63a90403d032fb3221e03fb447dcbb802b94e2e741a2fba75', 33, 1, 'MyApp', '[]', 0, '2024-03-24 17:43:42', '2024-03-24 17:43:42', '2024-09-24 17:43:42'),
('f4a6d1244873ada50aae92c09280384fce025ba2508c2e4667acea5ecb45abb835c171e6c84dfd40', 5, 1, 'MyApp', '[]', 0, '2024-04-18 19:09:42', '2024-04-18 19:09:42', '2024-10-18 19:09:42'),
('f4bf113fe46849adceb052958e522e4a2df975461905817c8d46ef6bbdacad2f26dc4d053b1a88fa', 62, 1, 'MyApp', '[]', 0, '2024-05-30 13:38:50', '2024-05-30 13:38:50', '2024-11-30 13:38:50'),
('f4d88c17be878a7285563df2547d4484db56d90ec30f6dff8f46e3977c9b49ca0c9d947625fe3782', 3, 1, 'MyApp', '[]', 0, '2024-02-14 12:25:07', '2024-02-14 12:25:07', '2024-08-14 12:25:07'),
('f4e9f249284e477e8d44d866a17738deae1a4106b949f9be8f084ad5ecc32afc4506fea7bd73332c', 5, 1, 'MyApp', '[]', 0, '2024-04-23 16:17:29', '2024-04-23 16:17:29', '2024-10-23 16:17:29'),
('f4f80b99bc7fb0e93d79b553d9a1a2cb0c1a7cb6ad080c5441ace3df17bf1afa0ed48edea6624c5d', 63, 1, 'MyApp', '[]', 0, '2024-06-04 10:23:01', '2024-06-04 10:23:01', '2024-12-04 10:23:01'),
('f53e654e45ea1d675724474deb0d154bc10930da81045b0354d0c25cb680d57e25e28f24676db557', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:49:37', '2024-04-23 14:49:37', '2024-10-23 14:49:37'),
('f549d0196f3592c05ed1dd53aeb257a59be450da91c26327899a27f783e208be9630b87737ecccc8', 45, 1, 'MyApp', '[]', 0, '2024-04-07 18:49:33', '2024-04-07 18:49:33', '2024-10-07 18:49:33'),
('f5514088c7497a3c7714ae7c89c28e9990e3a66dc28cb4f7c0616baf15adaff6d38a832a8aa9804a', 5, 1, 'MyApp', '[]', 0, '2024-04-14 14:35:21', '2024-04-14 14:35:21', '2024-10-14 14:35:21'),
('f57b66672869fa43b761342cdda31d30387615e4041bc2f9c4c5f5b7b6a3dc0cab3b2598ced08012', 5, 1, 'MyApp', '[]', 0, '2024-03-04 14:34:14', '2024-03-04 14:34:14', '2024-09-04 14:34:14'),
('f584aa7f3322b2b535aa368b3c5af21778ff4fc40b598e8eb05d8f80ac183b7211d66e8f08a39bcd', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:41:42', '2024-04-28 14:41:42', '2024-10-28 14:41:42'),
('f586b92b2d68cb1486d10da6edabc403871f13d5a3377553b9a50d4333bfa41f542d0ad3d46a1740', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:53:24', '2024-07-16 09:53:24', '2025-01-16 09:53:24'),
('f5885e5529fbe25751b4e7c018bbccee75aff18fdfa93e5137e4c6244c5a98c425fc8ce349d8e727', 34, 1, 'MyApp', '[]', 0, '2024-03-24 17:37:04', '2024-03-24 17:37:04', '2024-09-24 17:37:04'),
('f5e20b3f007f162e7175ae6ca4feed4cf1d544dc7c319ccdf6853ea2ebb09710d22af61311ef79eb', 5, 1, 'MyApp', '[]', 0, '2024-04-22 12:47:25', '2024-04-22 12:47:25', '2024-10-22 12:47:25'),
('f5edc902401fe9a4c384d537bddf14366be7ebac97028d76c4a38be1e1964887e7ee4bfb40f62cdf', 35, 1, 'MyApp', '[]', 0, '2024-03-26 02:58:50', '2024-03-26 02:58:50', '2024-09-26 02:58:50'),
('f6032eeef5b46e13efaef32cab3af007237c38c1b29bea81ec090ae4c4c6f635fcbf64725376c1b9', 5, 1, 'MyApp', '[]', 0, '2024-04-21 14:49:42', '2024-04-21 14:49:42', '2024-10-21 14:49:42'),
('f605765f388e2b738cb714d5ca50586aaa64fae9ced0aa8778ad5359ff4a3df1b764b8b5ed97f642', 5, 1, 'MyApp', '[]', 0, '2024-04-14 18:07:50', '2024-04-14 18:07:50', '2024-10-14 18:07:50'),
('f6276ef4b799b636a7db427f3a6a7a36738f4bab4ea04c66aaa8eef9001e64b48bb0d6b7936147b7', 5, 1, 'MyApp', '[]', 0, '2024-04-23 13:19:11', '2024-04-23 13:19:11', '2024-10-23 13:19:11'),
('f6441f0a7113b1a3b4a982cd947a30ab26ccb3791b4c466c6530f4a2fa8a44d8215492dd23e954c1', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:34:46', '2024-07-12 14:34:46', '2025-01-12 14:34:46'),
('f674dfae30956570b2328ce79e08fa5df5998273778c8a64703d024e6ed0c81ced22e9c4d3b9de89', 5, 1, 'MyApp', '[]', 0, '2024-04-23 14:34:52', '2024-04-23 14:34:52', '2024-10-23 14:34:52'),
('f68fd4aba1735f93a479bb7c8e2f0884570f3a6e5cec19c35c6001b9d874e3519a9ea782dde8c286', 73, 1, 'MyApp', '[]', 0, '2024-07-23 14:13:50', '2024-07-23 14:13:50', '2025-01-23 14:13:50'),
('f6947de8349f07563ba6764949840deb069e4714bccda02b3c07868cb54e348f73125f93789149cd', 58, 1, 'MyApp', '[]', 0, '2024-06-05 15:06:04', '2024-06-05 15:06:04', '2024-12-05 15:06:04'),
('f6d06ba1c820cbb579d07202d19ecae8c43f6f20e377ee3b343bd3c93a16d50d770b717c36b49a70', 58, 1, 'MyApp', '[]', 0, '2024-06-05 15:06:04', '2024-06-05 15:06:04', '2024-12-05 15:06:04'),
('f6d750861accf800bd6bab751f2d345383f8349d7c0306cb5279e424296e4a6a8706266829eb26de', 5, 1, 'MyApp', '[]', 0, '2024-03-19 13:21:31', '2024-03-19 13:21:31', '2024-09-19 13:21:31'),
('f70457eb50a66f500e38c7e7593ab77ff14801968609ee59bb084b2e35cc8a6291fd859c80312296', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:33:59', '2024-03-19 22:33:59', '2024-09-19 22:33:59'),
('f7045e2ccfe548af0d4c659bdd74c119ce4751a3ce2028f8f97d7fa07a0a3525031664aef0991a1f', 5, 1, 'MyApp', '[]', 0, '2024-04-22 18:47:42', '2024-04-22 18:47:42', '2024-10-22 18:47:42'),
('f752acc3bb0f4fb9eebf74b977b4c725c3ef5f07485b18d94bd0d92f76c2c43b7619a3ff7d2d2bd2', 5, 1, 'MyApp', '[]', 0, '2024-04-14 13:06:11', '2024-04-14 13:06:11', '2024-10-14 13:06:11'),
('f7b0b4ce5a9a739dffe300a0aca50aaea09b939aca0068302266772a338a99c6fda0942de3c746c7', 5, 1, 'MyApp', '[]', 0, '2024-03-19 18:12:07', '2024-03-19 18:12:07', '2024-09-19 18:12:07'),
('f7bbe5c95b29b7c039fd3e5a531e3365f2a232c3bc442336290b22435b2b5106a494cec0b7f04752', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:25:37', '2024-04-28 13:25:37', '2024-10-28 13:25:37'),
('f7c3aa70b92c04488ea9dcb83c7e8ef1f7a3f37fde3cb2a028ca152d54c123ccf1b5bccb1c10f536', 6, 1, 'MyApp', '[]', 0, '2024-02-14 16:40:31', '2024-02-14 16:40:31', '2024-08-14 16:40:31'),
('f7da02fb929d3c73701b902a97b009685aa3d148b9fcc4907034af115e3989e37c931a2085c6b43d', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:33:40', '2024-07-13 15:33:40', '2025-01-13 15:33:40'),
('f7e1678e0ac788f30a76e37cbb135edd13b0e7ad206a9603faee517f8b337daba1a88ded336a7208', 5, 1, 'MyApp', '[]', 0, '2024-04-18 17:12:29', '2024-04-18 17:12:29', '2024-10-18 17:12:29'),
('f7eaf0916f77f0f1dc4ec514bd8e20d2ffadab3ef8b3623ea842847f29195790efde9e86284eeeb3', 58, 1, 'MyApp', '[]', 0, '2024-06-05 10:33:31', '2024-06-05 10:33:31', '2024-12-05 10:33:31'),
('f7f4759dc676e6bd38911750d0b84bb8815ee898f053a8318fda434cf52aabc614dd0739c18099e3', 51, 1, 'MyApp', '[]', 0, '2024-04-28 11:12:34', '2024-04-28 11:12:34', '2024-10-28 11:12:34'),
('f7f6d1d5671fcdec27559da3e791accae84c487c2836deed28b0afe3f59183dc7e60096f56c09d69', 5, 1, 'MyApp', '[]', 0, '2024-03-18 14:05:33', '2024-03-18 14:05:33', '2024-09-18 14:05:33'),
('f7fff8dcf28a1be512ee08b6a2982e1e80b185fba07da2a66920a71a73fb0b310967bf03eb4c04f9', 5, 1, 'MyApp', '[]', 0, '2024-03-06 18:01:49', '2024-03-06 18:01:49', '2024-09-06 18:01:49'),
('f82a9c8576e26cbe45ab1206377469af7eecda2fe3ed49009323c39685194ee904e65d049ed30fab', 66, 1, 'MyApp', '[]', 0, '2024-07-12 14:42:18', '2024-07-12 14:42:18', '2025-01-12 14:42:18'),
('f87eff253355d4804f5b17e8137ce64393c193f37c7ffd73580655d2cfa5b058789ae36f1a8852f3', 58, 1, 'MyApp', '[]', 0, '2024-05-22 09:49:41', '2024-05-22 09:49:41', '2024-11-22 09:49:41'),
('f884623b4cb3e038e76c61cb4661d912d5fa50ad4785ed91b82f51a3005fa1d9be10284933401d25', 5, 1, 'MyApp', '[]', 0, '2024-03-07 12:02:52', '2024-03-07 12:02:52', '2024-09-07 12:02:52'),
('f896efb64fd90440044b5bd6bcb4feb26cac01b95f16e6f044e647b1523cc7dda0df72bde3d734bb', 58, 1, 'MyApp', '[]', 0, '2024-06-02 11:47:16', '2024-06-02 11:47:16', '2024-12-02 11:47:16'),
('f8998cc912c4e92a413bd72c2231a5e274d9f7deb827ca8ec3d5eca599fcfc1b654fe67280ce3ba2', 5, 1, 'MyApp', '[]', 0, '2024-04-21 15:58:24', '2024-04-21 15:58:24', '2024-10-21 15:58:24'),
('f8b885a513cc1769abdbad9f0c0eb9530dc38a674eb65cb722fcf3b9ba4faa51f4ef683b6b5875f3', 56, 1, 'MyApp', '[]', 0, '2024-05-01 14:29:22', '2024-05-01 14:29:22', '2024-11-01 14:29:22'),
('f8cc975689f5457025ef513a7b113b5e3781f8e28f27c550dbd2776bc47d4e95c4e1b69b1ca6d0ba', 43, 1, 'MyApp', '[]', 0, '2024-04-03 21:12:55', '2024-04-03 21:12:55', '2024-10-03 21:12:55'),
('f8feebae231b85bcda37b77fa88fe9cdbaeb20abb3e175d6f2b585d910e615342281d829fdc88455', 5, 1, 'MyApp', '[]', 0, '2024-03-19 22:39:59', '2024-03-19 22:39:59', '2024-09-19 22:39:59'),
('f910a190df72a1af7286f269c3d89cd037078669ff20bf71741b1d71eb7895c065b2596585e8e9e2', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:02:12', '2024-04-29 16:02:12', '2024-10-29 16:02:12'),
('f942d008cba8cf4be8319bfc1a2bc6cbd05f39ca6f171f3e90279d4eb00c6f9c209013e43d424a0f', 5, 1, 'MyApp', '[]', 0, '2024-04-16 19:48:10', '2024-04-16 19:48:10', '2024-10-16 19:48:10'),
('f96fc68caec4063a10611ac9accaa1f3904ffce1230fc287987eee47948753d2adb16a8da39180f3', 19, 1, 'MyApp', '[]', 0, '2024-02-25 19:59:26', '2024-02-25 19:59:26', '2024-08-25 19:59:26'),
('f971de1a57e18f347b50b74ec908827e3e402f6ca41d41a919c4ec78787a33a7916da9c8f1dadcee', 69, 1, 'MyApp', '[]', 0, '2024-07-16 09:09:29', '2024-07-16 09:09:29', '2025-01-16 09:09:29'),
('f981b040210a498f9085a11841fdb89fd7c0cccb573d5372dc3c4aa9ee75e48ff1b84f18ae9a977d', 5, 1, 'MyApp', '[]', 0, '2024-04-29 14:47:44', '2024-04-29 14:47:44', '2024-10-29 14:47:44'),
('f985a068e7b0fa2744718a4f4d675e6055fe30acb7332e96f99b81578f62cc08d91a50ff84dcfa12', 5, 1, 'MyApp', '[]', 0, '2024-04-07 17:34:35', '2024-04-07 17:34:35', '2024-10-07 17:34:35'),
('f9d93d4451fee927f1e593312edbb1b634e34461a61718cfcde8f8b4433bbb4a15cef3ed26a0947d', 58, 1, 'MyApp', '[]', 0, '2024-05-13 12:24:08', '2024-05-13 12:24:08', '2024-11-13 12:24:08'),
('fa644a34627790b34f078fbcb2684094c91bc79f8abe1426d0c6e82391ca9e413fe500a06ebe50a4', 5, 1, 'MyApp', '[]', 0, '2024-02-18 14:00:20', '2024-02-18 14:00:20', '2024-08-18 14:00:20'),
('fa66b9df40972af8a57d91086c586487dd6308d86776a4edf0ef8d66855d5ae1cc03a25505dfe88b', 2, 1, 'MyApp', '[]', 0, '2024-02-15 14:41:21', '2024-02-15 14:41:21', '2024-08-15 14:41:21'),
('fa7c8d1b807b57140e10a89e4d639bc2a481800b9e10b1cbcb0e70c457952bde0d9bdd195e626508', 35, 1, 'MyApp', '[]', 0, '2024-03-28 14:17:27', '2024-03-28 14:17:27', '2024-09-28 14:17:27'),
('faaac7fb1bb2eea42a24d08853621dc15e7f5061094a479b5f845f428b79c8a168b99279623467ce', 5, 1, 'MyApp', '[]', 0, '2024-04-24 17:04:01', '2024-04-24 17:04:01', '2024-10-24 17:04:01'),
('fab17386ef0fc099deb549eb647f966f9252ad59a4725b5885007397f8ce3a630020948c3ea04ec2', 5, 1, 'MyApp', '[]', 0, '2024-04-21 16:42:46', '2024-04-21 16:42:46', '2024-10-21 16:42:46'),
('fad360cfc5948186e9868d86ef17553e719cb85e0f5ad6e15bcf1147fee5b459b17e5350c1c51b2d', 5, 1, 'MyApp', '[]', 0, '2024-04-28 14:36:10', '2024-04-28 14:36:10', '2024-10-28 14:36:10'),
('faeb3b057c31a794ebae9267ef83458cad522f815ab7af0759ba748c537937a32a20a80bbaf0392e', 52, 1, 'MyApp', '[]', 0, '2024-04-30 11:52:17', '2024-04-30 11:52:17', '2024-10-30 11:52:17'),
('fb2baf43579e5068e8a4dce1a474bf1c5d6f7c140d3b14bd5368470e9937fb20bec059ac98afa352', 5, 1, 'MyApp', '[]', 0, '2024-04-24 16:22:10', '2024-04-24 16:22:10', '2024-10-24 16:22:10'),
('fb2c4cdebb1dadb26255927defe605efa8c2f655234e4b13d6501f54f29d823f02ea94bdf61fba84', 66, 1, 'MyApp', '[]', 0, '2024-07-13 17:33:18', '2024-07-13 17:33:18', '2025-01-13 17:33:18'),
('fb79b2f41e77cfbcb37b555cb87423453b4948f3dd4bff31875a4a75a51c9c3426edfb8df6c80281', 5, 1, 'MyApp', '[]', 0, '2024-04-23 12:57:43', '2024-04-23 12:57:43', '2024-10-23 12:57:43'),
('fb7a4eaee449f058366ea453a89cd71dfea34ad680ae7c5d6097df9f43771abfb47694a38b8a1385', 5, 1, 'MyApp', '[]', 0, '2024-04-28 18:23:19', '2024-04-28 18:23:19', '2024-10-28 18:23:19'),
('fbabb862c7aa971d7027a85d215b8896104dee720539ed38021bdfab3c7543d71966fc546c1067b6', 71, 1, 'MyApp', '[]', 1, '2024-08-01 10:59:48', '2024-08-01 10:59:48', '2025-02-01 10:59:48'),
('fbdd1fb01fd9e9272778998f7de1b903f162ea4999c45b1ffc513e768f2e6951c5357065e2e7d308', 5, 1, 'MyApp', '[]', 0, '2024-04-28 13:01:18', '2024-04-28 13:01:18', '2024-10-28 13:01:18'),
('fc47fbc191b6bf1acfef6427c1430798116d1ce42346690d17f2fe1826b2c78e6f82af0bf7a5a8b3', 35, 1, 'MyApp', '[]', 0, '2024-03-26 04:11:06', '2024-03-26 04:11:06', '2024-09-26 04:11:06'),
('fcf2efd5aeec7bff9268cdaa1f3db1084928c109ebd8207f120cc8b1de1a397dfa065e0f6ddc0567', 35, 1, 'MyApp', '[]', 0, '2024-03-28 15:02:34', '2024-03-28 15:02:34', '2024-09-28 15:02:34'),
('fd0cd624f5067076d1ff839d7412fd64cc5ff444f44b4c5bcef21e6213c16d4712afa6f2c752fa31', 5, 1, 'MyApp', '[]', 0, '2024-04-18 20:41:04', '2024-04-18 20:41:04', '2024-10-18 20:41:04'),
('fd1bfb63881c8929af96a3c6530078a43f411a1b2e8a445b23b3d5483bcef5cbedbae6dbd7fbfcdc', 58, 1, 'MyApp', '[]', 0, '2024-06-27 13:32:23', '2024-06-27 13:32:23', '2024-12-27 13:32:23'),
('fd4bce83eb8bfa46b2aace8515a206d494a4dae4d22ab78d9bd54585aff74f88015b8a0c2a180091', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:17:50', '2024-07-12 12:17:50', '2025-01-12 12:17:50'),
('fd80c613a2c79eb8da3b231578d29c294191c3c925b86a8d670ea20c6c962661d7574a5b1b06525e', 37, 1, 'MyApp', '[]', 0, '2024-03-26 16:23:18', '2024-03-26 16:23:18', '2024-09-26 16:23:18'),
('fdb5dc9e6ff5f8d8f17659e33154db94218481118216d42a33dd4398b907c3069277f979e4d818c4', 5, 1, 'MyApp', '[]', 0, '2024-04-17 15:14:26', '2024-04-17 15:14:26', '2024-10-17 15:14:26'),
('fdbaf12d56ff4684770d79d4a9f1a05ffe53ffc5b78eca079073803f8219c51d3ef5872e464697ad', 2, 1, 'MyApp', '[]', 0, '2024-02-15 11:14:35', '2024-02-15 11:14:35', '2024-08-15 11:14:35'),
('fdc19cbe5694dc2aff8761a12a664e1c8f17f3fa18326eb68429ccaea177984f0d9cfa618ac78487', 2, 1, 'MyApp', '[]', 0, '2024-04-02 01:22:45', '2024-04-02 01:22:45', '2024-10-02 01:22:45'),
('fdcbcdda2a54800fed2b6385257cc94d6d6c23a09aa9557fd603ed5fe509a862c9110b0db0bcf81b', 5, 1, 'MyApp', '[]', 0, '2024-02-15 13:50:43', '2024-02-15 13:50:43', '2024-08-15 13:50:43'),
('fdd5673c1b1309bde8cade0d90cc3370f5f2baac0945b3b1e460d212649689ee7a0a6214a85d70d8', 1, 1, 'MyApp', '[]', 0, '2024-02-14 12:25:31', '2024-02-14 12:25:31', '2024-08-14 12:25:31'),
('fe0abf4e63d7014a5782a1b8100782cbd0e4f14caf0169c18f3b49b1c48406ed5e377a40f200e501', 5, 1, 'MyApp', '[]', 0, '2024-04-24 15:10:16', '2024-04-24 15:10:16', '2024-10-24 15:10:16'),
('fe1ad73b09698a17375a3921eaf6ae1fcd7432b4440b8926700424911f9aeb2859483d06cd34386d', 62, 1, 'MyApp', '[]', 0, '2024-05-28 19:19:33', '2024-05-28 19:19:33', '2024-11-28 19:19:33'),
('fe74e28dc804023d815ca032086beee4340748cc8cbda489bddf0cd994f42b3965d9d86e998fc421', 58, 1, 'MyApp', '[]', 0, '2024-06-02 12:44:25', '2024-06-02 12:44:25', '2024-12-02 12:44:25'),
('feb02e54769e0a09ac6cf3cbcd4aaee66fd34c1821ae9bbc16fae77c01be3e441130e86f688ca109', 58, 1, 'MyApp', '[]', 0, '2024-05-13 15:29:35', '2024-05-13 15:29:35', '2024-11-13 15:29:35'),
('feba37fe5e3b2f628a2b517cacf068e6862d47b387479019cf4aa500ef7e92d4ce8f06f644e52c0c', 66, 1, 'MyApp', '[]', 0, '2024-07-13 15:06:37', '2024-07-13 15:06:37', '2025-01-13 15:06:37'),
('fedb75405e6a056c9929cfe26e032cb9bd49adf353567db3ef2ab2200bf3617a51fd36e7799e545a', 5, 1, 'MyApp', '[]', 0, '2024-02-14 17:59:02', '2024-02-14 17:59:02', '2024-08-14 17:59:02'),
('ff077061cca861b9030b409aba99cbb5959cee78b0aebfc94b222a4392d7bcf993e4f917d6ad1aa0', 69, 1, 'MyApp', '[]', 1, '2024-07-16 18:46:10', '2024-07-16 18:46:10', '2025-01-16 18:46:10'),
('ff07d49d631fd9744754e75d9d2d473939f683560c95b3e0cfbd49b70951270049a134e0d638e348', 2, 1, 'MyApp', '[]', 1, '2024-02-14 15:17:51', '2024-02-14 15:17:51', '2024-08-14 15:17:51'),
('ff206ca489b9531cafa3764c4b91f559fa2b574d167ae2c804593fe5b09b2262f93991c44656bbcd', 5, 1, 'MyApp', '[]', 0, '2024-04-29 16:13:32', '2024-04-29 16:13:32', '2024-10-29 16:13:32'),
('ff30ebb714a79483e1790b1eb1a54dc42c79ba8fd7b3a689e0b85bb5f28deda36d133ceda535266c', 30, 1, 'MyApp', '[]', 0, '2024-03-19 10:10:43', '2024-03-19 10:10:43', '2024-09-19 10:10:43'),
('ff310d79488bc151fe5ee5802dfd63712b4df0cd8e8f730c488767f1d88375fba936c7802b2a7332', 74, 1, 'MyApp', '[]', 1, '2024-07-30 09:43:04', '2024-07-30 09:43:04', '2025-01-30 09:43:04'),
('ff4e45e0c77f5e907e211f707a1163dbcb29f947d219c34fed617d95c7208162e45c8e536cf950ce', 5, 1, 'MyApp', '[]', 0, '2024-04-03 21:01:32', '2024-04-03 21:01:32', '2024-10-03 21:01:32'),
('ff56b7ae7541d5d2d6e3a8a52798acb8f8575e83e97f5213ef1548883c6afac47170fe99f53275fd', 10, 1, 'MyApp', '[]', 0, '2025-08-14 13:07:41', '2025-08-14 13:07:41', '2026-02-14 16:07:41'),
('ff5e95dda7117825c0530304f59c9a278d035ae1a5b1017ffa45055ab63e812d1ac17cab7160c805', 66, 1, 'MyApp', '[]', 0, '2024-07-12 12:28:15', '2024-07-12 12:28:15', '2025-01-12 12:28:15'),
('ffab24a885e53dec2c5e024428c40febe8f179d3b54021aa0356fbd69938ca86f0d63e1c9ccef416', 5, 1, 'MyApp', '[]', 0, '2024-04-23 18:57:54', '2024-04-23 18:57:54', '2024-10-23 18:57:54');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'vlog Personal Access Client', 'SrlT1LfgRa8Kqr68YpUSGTipGXYnIrApo5kaWiPp', NULL, 'http://localhost', 1, 0, 0, '2024-02-13 18:02:02', '2024-02-13 18:02:02'),
(2, NULL, 'vlog Password Grant Client', '0VVLarfU4MJH3ni9OnfqIW0M405UoV0PSYIe11uj', 'users', 'http://localhost', 0, 1, 0, '2024-02-13 18:02:02', '2024-02-13 18:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2024-02-13 18:02:02', '2024-02-13 18:02:02');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` double DEFAULT NULL,
  `image` text COLLATE utf8mb4_unicode_ci,
  `coin` double DEFAULT NULL,
  `is_active` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `price`, `image`, `coin`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"en\":\"asdasdsss\",\"ar\":\"\\u0634\\u0633\\u0628\\u064a\\u0633\\u064a\\u0628\"}', '{\"en\":\"ssssssss\",\"ar\":\"\\u0633\\u064a\\u0628\\u0633\\u064a\\u0628\\u0633\\u064a\\u0628\\u0633\\u064a\\u0628\\u0633\\u064a\\u0628\\u0633\\u064a\\u0628\"}', 10, 'packages/fuWUEjSjrqLTMHb3GMrs45hU3qxnHAhy75N4PD4T.jpg', 555, 0, '2024-06-24 12:39:59', '2025-02-02 12:27:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `image` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, '{\"en\":\"About us\",\"ar\":\"\\u0645\\u0646 \\u0646\\u062d\\u0646\"}', '{\"en\":\"About us About usAbout usAbout usAbout usAbout usAbout usAbout us\",\"ar\":\"\\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646 \\u0645\\u0646 \\u0646\\u062d\\u0646\"}', 'pages/REetWVzmO8IqUxHeQDXcSAZNJpCecXT1elRSWMhY.jpg', NULL, '2024-07-16 03:12:24');
INSERT INTO `pages` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(2, '{\"en\":\"termsss\",\"ar\":\"\\u0634\\u0631\\u0648\\u0637\"}', '{\"en\":\"Terms of Service\\r\\n(If you are a user having your residence in the European Economic Area, MENA)\\r\\nWelcome to Vlog.\\r\\nThese Terms of Service (\\u201cTerms\\u201d) contain the agreement between you and Vlog in respect of the Platform described below. Please read these Terms carefully. Please also read our privacy .\\r\\nYou form a contract with us when you confirm that you accept these Terms or when you otherwise use the Platform.\\r\\nWhile we encourage you to read these Terms in full, contact with us Vlogapp2024@gmail.com for a summary of these Terms and the additional terms and policies which also form part of your contact with us.\\r\\nIn short: This is a contract between you and Vlog. You should read it.\\r\\n1. Who you are contracting with\\r\\nThe company that you are contracting with depends on where you are resident or have your principal place of business:\\r\\nIf you are resident in one of the countries that form the European Economic or MENA, your contract is with Vlog Technology Limited \\r\\nIn short: The specific Vlog company that you have a contract with depends on where you are resident. If we say \\u201cVlog\\u201d, \\u201cwe\\u201d or \\u201cus\\u201d in these Terms, we are referring to that specific company unless we say otherwise.\\r\\n2. What services are covered by these Terms\\r\\nThese Terms govern your use of Vlog services, which includes Vlog apps (including Vlog and Vlog Now), websites, software and related services, accessed via any platform or device (the \\u201cPlatform\\u201d). Our Community Guidelines also form part of our contract with you and, by agreeing to these terms, you promise to us that you will comply with them.\\r\\nThe products and services you access through your Vlog For Business, Vlog Ads Manager and Vlog Business Center accounts are governed by the separate Commercial Terms of Service.\\r\\nIn short: These Terms apply to your use of Vlog apps (including Vlog and Vlog Now), websites, software and related services however you access them. As part of complying with these Terms, you must also follow our Community Guidelines.\\r\\n3. Additional terms and policies that might apply depending on the features you use\\r\\nAdditional terms and policies may apply to certain products or features of the Platform that we may make available to you, if you post or live stream certain kinds of content, or if you use the Platform for business or commercial purposes. The main ones you should be aware of, and which form part of these terms, are set out below for your information:\\r\\nCoins Policy: This policy governs the purchase and use of virtual coins to activate or access featureson thee platform.\\r\\nRewards Policy: This policy governs the receipt of rewards from Vlog, such asvirtual diamonds.\\r\\nMusic Terms: These terms apply when you post content onthethe platformm that includes music sourced from the music library or your personal device.\\r\\nVlog Shop Buyer Terms: These terms apply when you buy items on Vlog Shop (where available).\\r\\nThis policy applies when you post content ona platformm that promotes a third party brand or its products or services in exchange for payment or any other incentive..\\r\\nIn short: These Terms cover your use of the Platform. There may be some additional terms and policies that will apply if you want to access additional functionality, post or live stream certain kinds of content, or use the platform for business or commercial purposes.\\r\\n4. Using the Platform\\r\\n4.1 Our Platform and business model\\r\\nThe platform allows you and others to create, view, interact with, and share content, and interact with others. We also personalise parts of your experience on the platform, such as providing your \\u201cFor You\\u201d feed in the Vlog app. The \\u201cFor You\\u201d feed is a unique Vlog feature that uses a recommendation system to allow you to discover a breadth of content, creators, and topics that are likely to be interesting for you. In determining what gets recommended, the system takes into account factors such as likes, shares, comments, searches, diversity of content, and popular videos. Learn more about the recommendation system and the tools you can use to customise your feed Vlogapp2024@gmail.com.\\r\\nWe want the Platform to be a safe place, so people can be who they are and have fun. We work with our Affiliates and use a mix of technology (including through automated means), human moderation, and reports from our users to identify infringements of and enforce these Terms and our Community Guidelines in order to protect you and all of our users.\\r\\nWe don\'t charge you a fee to use most of the features of the Platform. Instead, some individuals and businesses pay us to show advertisements on the Platform, and we may also charge sellers a commission on products sold on Vlog Shop.\\r\\nInformation on how we use data that we collect about you can be found in our privacy Policy. These do not form part of these Terms but are important documents which we strongly recommend that you read.\\r\\nWe charge users for some features of the Platform, for example, if you buy virtual coins but such features are voluntary and any costs will be made clear to you before you are charged anything.\\r\\nIn short: You do not have to pay for your use of most of the features on our Platform but, in exchange, we allow some individuals and businesses to advertise on the Platform and we receive payment for this.\\r\\n4.2 Account details\\r\\nYou can use some basic features of the Platform without having an account. If you use the Platform without an account, then these Terms will still apply to such use and we will still process your personal data in accordance with our Privacy Policy.\\r\\nTo access the full functionality of the Platform, you must create an account with us. We may offer different types of accounts.\\r\\nWhen you create an account, your account details will sync across the Platform, including across each Vlog app. For example, when you create an account via Vlog, you will be able to access any other Vlog apps available in your country, such as Vlog Now, using that account. Your account details, content and Platform settings (including your privacy settings), and any changes you make, will also sync across each Vlog app.\\r\\nWhen you create an account to access and use the Platform, you must provide accurate and up-to-date information about yourself (such as your date of birth). You agree to maintain and promptly update your details if they change. See our Vlogapp2024@gmail.com for information on opening an account.\\r\\nIt is important that you take reasonable steps to keep your account password confidential and that you do not disclose it to any third party. If you know or suspect that any third party knows your password or has accessed your account, please let us know straight awayVlogapp2024@gmail.com .\\r\\nIn short: Your account is important. Keep it safe. Keep your details up to date. When you create an account in one Vlog app, you will be able to access other Vlog apps using that account (provided those apps are available in your country) and your account details, content and settings will sync across those apps.\\r\\n4.3 Minimum age\\r\\nYou can only use the Platform if you are 13 years of age or older. We monitor for underage use and we will terminate your account if we reasonably suspect that you are underage or are allowing someone underage to use your account. You can appeal our decision to terminate your account if you think we have made a mistake about your age.\\r\\nIn short: You need to be 13 or over to use our Platform.\\r\\n4.4 What you can do on the Platform\\r\\nUnder these Terms, you can use the Platform in order to:\\r\\ncreate and share content;\\r\\ninteract with other users;\\r\\nview content created by others; and\\r\\nuse the features and functionality on the Platform as provided to you from time to time.\\r\\nThe permission we give to you:\\r\\nis limited to what we have said we will allow in these Terms;\\r\\nis only for you;\\r\\ncannot be given to anyone else by you; and\\r\\ncan be withdrawn for the reasons allowed in these Terms.\\r\\nAccess to certain features of the Platform depends on your age. For example:\\r\\nDirect Messaging: You must be aged 16 or older to use the direct messaging functionality.\\r\\nVlog LIVE: You must be aged 18 or older to live stream and use live streaming features.\\r\\nVirtual Items: You must be aged 18 or older to interact with virtual items.\\r\\nIn short: You can do many things on our Platform but you might not be able to use every feature if you are not old enough\\r\\n What you can\\u2019t do on the Platform\\r\\nIf you want to use the Platform, you can\\u2019t create, post, share, link to or otherwise interact with content in breach of our Community Guidelines.\\r\\nIn any event, you must not use the Platform to:\\r\\ndo anything illegal (this includes posting, live steaming or distributing illegal content);\\r\\ndo anything that violates applicable anti-money laundering, counter terrorist financing, export controls and economic sanctions laws or regulations;\\r\\nengage with minors in an exploitative or inappropriate way;\\r\\nundermine the Platform\\u2019s operations or security;\\r\\nengage in inauthentic commercial behaviours such as operating spam or impersonation accounts or by any other means further detailed in our Community Guidelines;\\r\\nsubmit appeals, reports, notices or complaints which are manifestly unfounded;\\r\\nextract any data or content from the Platform using any automated system or software that is not provided by Vlog or approved in writing by Vlog; or\\r\\nuse or attempt to use another user\\u2019s account without authorisation.\\r\\nYou must also not post, live stream or otherwise distribute any content on the Platform which:\\r\\ninfringes anyone else\\u2019s rights (such as intellectual property, privacy and\\/or personality rights of living or deceased people);\\r\\nconstitutes, encourages or provides instructions for a criminal offence, or dangerous activities that may lead to serious injury or death or self-harm;\\r\\nspreads harmful misinformation such as misinformation that incites hate or prejudice or that misleads about or improperly influences elections or other civic processes;\\r\\ncontains a threat of any kind or which intimidates or harasses others, including posting any material that is intended to mock, humiliate, embarrass, intimidate, or hurt an individual;\\r\\nis obscene, pornographic or which promotes sexually explicit material (e.g. by linking to adult or pornographic websites);\\r\\nis hateful or inflammatory;\\r\\ncontains or promotes violence or discrimination based on race, ethnicity, national origin, religion, caste, sexual orientation, sex, gender identity, serious disease, disability, immigration status or age; or\\r\\notherwise contains harmful content (such as content that causes physical, mental or moral detriment to minors).\\r\\nYou can report suspected illegal content or content that otherwise breaches these Terms or our Community Guidelines through the reporting functionalities provided on the Platform.\\r\\nIn short: Enjoy using our Platform but, for the benefit of all our users, there are rules you need to follow. If you see something that should not be on our Platform, please tell us.\\r\\n4.6 Your content\\r\\nIt is important that you understand what happens to the content that you create, post or share on the Platform:\\r\\nYou are responsible for the content that you make available on the Platform and you should have all the rights needed to create, post or share content on the Platform.\\r\\nWhen you create, post or share content via one Vlog app, that content may be automatically posted and shared across other Vlog apps (although your privacy settings will still apply across each app). For example, when you post a video on Vlog Now, that video will also be posted and available on Vlog.\\r\\nWe review content proactively (through systems we have in place which detect illegal and harmful content) and reactively (for example, on receipt of notice from users or authorities). To do this we deploy a combination of technology and human moderators. Our approach to content moderation.\\r\\nWe may remove or restrict access to any content, including yours, if we reasonably believe (i) it is in breach of these Terms; or (ii) it causes harm to us, Affiliates, our users or other third parties. Our Community Guidelines set out how content might be removed or restricted on the Platform.\\r\\nIf we remove or restrict access to your content, we will notify you without undue delay and state the reasons for our decision, unless it is not appropriate for us to do so (for example, we are legally prevented from doing so).\\r\\nIf you think we have made a mistake in removing or restricting access to your content, you can appeal through the appeal functionalities provided on the Platform and we will review our decision and decide again..\\r\\nYou are free to remove your content from the Platform at any time. When you remove your content from one Vlog app, it will also be removed from other Vlog apps, for example, when you delete a video you have posted on Vlog Now, it will also be deleted from Vlog.\\r\\nDepending on your Platform settings, if other users of the Platform have used your content to create new content (e.g. by using Duet or Stitch) or shared your content on third party services, then that new content may stay on the Platform or those third party services even if you subsequently delete your content or your account. You may make a separate request for Duet or Stitch videos that contain your content to be deleted.\\r\\nYou can restrict how other users interact with and use your content in your Platform settings. You should familiarise yourself with these settings before you post content on the Platform.\\r\\n5. What we promise to you\\r\\nWe promise to provide the Platform to you with reasonable skill and care and to act with professional diligence for so long as we choose to offer the Platform. We will also take all reasonable steps to keep the Platform a safe and secure environment for our users. We do not promise to offer the Platform forever or in its current form for any particular period of time.\\r\\nThe content on the Platform is mostly user generated content provided by the individuals and businesses that use our Platform. In other words, Vlog is not the creator of most of the content on the Platform (although Vlog may produce some content). Therefore, subject to any mandatory regulations or laws (including sectorial regulations or laws) applicable to Vlog, Vlog cannot and does not promise that any of the content generated by users that you find on the Platform:\\r\\nis accurate, complete or up-to-date;\\r\\ndoes not infringe third party rights;\\r\\nis legal; or that it\\r\\nwill not offend you.\\r\\nYou understand and agree that the content you may see on the Platform does not represent our views or values and may not be suited to your purpose.\\r\\nThe Platform may contain links to third party websites, advertisements, services, offers or other events or activities that are not provided, owned or controlled by Vlog. We do not endorse any such third party websites, advertisements, services, offers, events, activities, information, materials or products. You use them at your own risk.\\r\\nProvided that we have acted with professional diligence, we do not take responsibility for loss or damage caused by us, unless it is:\\r\\ncaused by our breach of these Terms; or\\r\\nreasonably foreseeable at the time of entering into these Terms (i.e. either it is obvious that it will happen or, at the time of this contract, it is known that it might happen).\\r\\nWe do not take responsibility for loss or damage if it is caused by events beyond our reasonable control.\\r\\nNothing in these Terms affects any statutory rights that you cannot contractually agree to alter or give up, or are legally always entitled to, for example, because you are a consumer.\\r\\nWe do not exclude or limit in any way our liability to you where it would be unlawful to do so. You will always have the full protections of the laws that apply to you.\\r\\nIf you are an EEA-based consumer, then EEA consumer laws provide you with a legal guarantee covering the Platform. If you have any questions about your legal guarantee, please contact us Vlogapp2024@gmail.com.\\r\\nIn short: We will always seek to provide you with a great and safe user experience, but you take the Platform as it is and understand that we do not control everything that is on it. We also cannot promise that all the content posted on the Platform is to your liking. If you are an EEA consumer, you have a legal guarantee covering the Platform, and you can make a guarantee claim by contacting us\\r\\n. Suspending or ending our relationship\\r\\n6.1 Your rights\\r\\nYou may end your relationship with Vlog at any time by simply closing your account and stopping your use of the Platform. Instructions for how to do this are in our. If you are an EEA-based consumer, then you can also close your account and withdraw from this contract at any time, either in-app or using the model withdrawal form system.\\r\\nYour account applies across the Vlog app and website, Vlog Now, and any other Vlog services to which you have linked your account, so when you close your account within one Vlog app, you will lose access to your content and account across all other Vlog services which are connected to that account.\\r\\nHowever, depending on your Platform settings, some of your content may still be available on the Platform after you delete your account.\\r\\nIn short: We want you to stay, but you can go whenever you want if you close your account and stop using the Platform.\\r\\n6.2 Vlog\\u2019s rights\\r\\nIn the event of any suspected breach of these Terms or our Community Guidelines, we may investigate. While we do so, we are allowed to take down some or all of your content, or suspend your access to some or all the features of the Platform, acting reasonably and objectively depending on the seriousness of the suspected breach.\\r\\nWe, subsequently, might determine to temporarily suspend or permanently terminate your account, or impose limits on, or restrict your access to features of the Platform if:\\r\\nwe determine, acting reasonably and objectively, that you are in material or repeated breach of these Terms or our Community Guidelines;\\r\\nwe have objective grounds to reasonably believe that you are about to seriously breach these Terms or our Community Guidelines;\\r\\nwe are legally required to do so; or\\r\\nit is reasonably required in response to dealing with a serious technical or security issue.\\r\\nWe record the number of times your account has violated our Terms. Repeated violations or a single severe violation may result in a permanent account ban.\\r\\nIf we have previously terminated your account for breaches of these Terms or Community Guidelines, but you use our Platform again (for example, by opening another account), we are entitled to suspend or terminate any such accounts.\\r\\nWe will notify you in advance in order to allow you time to download your data in-app (more information about how to do this is available Vlogapp2024@gmail.com , unless it is not appropriate for us to do so or we reasonably believe that continued access to your account will cause damage to us, Affiliates, our users or other third parties, or we are legally prevented from doing so.\\r\\nIf you think we have made a mistake in suspending or terminating your account, you can appeal through the appeal functionalities provided on the Platform and we will review our decision and decide again.\\r\\nFor the avoidance of doubt, if we suspend or terminate your account, or you delete your account, you will lose access to all Vlog apps, including Vlog and Vlog Now.\\r\\nIn short: We have rules and, if you break them, Vlog can take action against you which may include terminating your account.\\r\\n7. Changes to these Terms or the Platform\\r\\n7.1 What happens when we make changes\\r\\nWe may make changes to these Terms or the Platform from time to time. If we do, we will consider your reasonable interests before doing so.\\r\\nWe will also give you reasonable advance notice, in a transparent manner, of significant changes which will impact you and the date that they will come into force. The changes will only apply to our relationship going forward.\\r\\nWhere we need to make urgent changes for security, safety, legal or regulatory requirements, we may not be able to provide you with advance notice, but we will let you know as soon as we are able to.\\r\\nIf you do not agree to the changes to the Terms or the Platform, you will have to stop using the Platform.\\r\\nIn short: If these Terms change, we will tell you. It won\\u2019t change anything between us that has happened already but, if you want to keep using the Platform in the future, you will need to agree to the changes. Our Platform will evolve as we improve it.\\r\\n7.2 Reasons for changes\\r\\nReasons that we might make changes to these Terms or the Platform are:\\r\\nchanges in circumstances beyond our reasonable control;\\r\\nchanges in the law;\\r\\nchanges we make to the Platform in the usual course of developing our product;\\r\\nto adapt to new technologies;\\r\\nto reflect changes in the number of people who use the Platform or any relevant feature or functionality of the Platform; or\\r\\nto address a security issue.\\r\\nIn short: Our Platform will not stay the same forever, but we will be transparent when we make major changes\\r\\nResolving disputes\\r\\nThese Terms are governed by the law of the jurisdiction in which you are a resident.\\r\\nIf we have a dispute, we will first try and resolve it with you amicably.\\r\\nIf we cannot resolve our dispute, you or we can go to your local courts. You can also go to the following courts:\\r\\nthe courts of the Republic of Ireland will have non-exclusive jurisdiction over disputes with Vlog Technology Limited; and\\r\\nthe courts of England & Wales will have non-exclusive jurisdiction over disputes with Vlog Information Technologies UK Limited.\\r\\nIn short: We hope we do not get into a dispute but, if we do, there are a couple of ways we can try to resolve it.\\r\\n9. Other\\r\\nThese Terms, and any rights and permissions granted in them, may not be transferred or assigned by you, but may be assigned by Vlog without restriction. If we do so, this will not affect any rights you may have as a consumer. And, if you are not happy, you always have the right to terminate this contract and stop using the Platform at any time.\\r\\nIn short: We have no plans to do so, but, if in the future we sell all or part of, or re-organise our business, another company may end up providing the Platform to you.\\r\\nWe may reclaim your account name and might make it available to other users when you have not logged into your account for 6 months or if we reasonably believe that your account name violates our Terms and\\/or Community Guidelines (e.g. your account name violates third party trademark).\\r\\nIn short: We may reclaim your account name in certain circumstances.\\r\\nEven if we or you delay in enforcing a provision of these Terms, either of us can still enforce it later. If we or you do not insist immediately that you or we do anything the other is required to do under these Terms, or if there is a delay in taking steps against the other in respect of breaching these Terms, that will not mean that we or you do not have to do those things and it will not prevent us or you from taking steps against the other at a later date.\\r\\nIn short: Just because you or we do not rely on one of these Terms, this does not change the fact that we both agree that these Terms, as written, are the agreement between us.\\r\\n10. Contacting Vlog\\r\\nYou can contact us here: Vlogapp2024@gmail.com\",\"ar\":\"\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629\\r\\n(\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064b\\u0627 \\u0648\\u062a\\u0642\\u064a\\u0645 \\u0625\\u0642\\u0627\\u0645\\u062a\\u0643 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629 \\u0648\\u0627\\u0644\\u0634\\u0631\\u0642 \\u0627\\u0644\\u0623\\u0648\\u0633\\u0637 \\u0648\\u0634\\u0645\\u0627\\u0644 \\u0623\\u0641\\u0631\\u064a\\u0642\\u064a\\u0627)\\r\\n\\u0645\\u0631\\u062d\\u0628\\u064b\\u0627 \\u0628\\u0643 \\u0641\\u064a \\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648.\\r\\n\\u062a\\u062d\\u062a\\u0648\\u064a \\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0647\\u0630\\u0647 (\\\"\\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\\") \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0627\\u062a\\u0641\\u0627\\u0642\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0628\\u0631\\u0645\\u0629 \\u0628\\u064a\\u0646\\u0643 \\u0648\\u0628\\u064a\\u0646 Vlog \\u0641\\u064a\\u0645\\u0627 \\u064a\\u062a\\u0639\\u0644\\u0642 \\u0628\\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0627\\u0644\\u0645\\u0648\\u0636\\u062d \\u0623\\u062f\\u0646\\u0627\\u0647. \\u064a\\u0631\\u062c\\u0649 \\u0642\\u0631\\u0627\\u0621\\u0629 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0628\\u0639\\u0646\\u0627\\u064a\\u0629. \\u064a\\u0631\\u062c\\u0649 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0642\\u0631\\u0627\\u0621\\u0629 \\u062e\\u0635\\u0648\\u0635\\u064a\\u062a\\u0646\\u0627.\\r\\n\\u062a\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0628\\u0631\\u0627\\u0645 \\u0639\\u0642\\u062f \\u0645\\u0639\\u0646\\u0627 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0624\\u0643\\u062f \\u0642\\u0628\\u0648\\u0644\\u0643 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0628\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0628\\u064a\\u0646\\u0645\\u0627 \\u0646\\u0634\\u062c\\u0639\\u0643 \\u0639\\u0644\\u0649 \\u0642\\u0631\\u0627\\u0621\\u0629 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0628\\u0627\\u0644\\u0643\\u0627\\u0645\\u0644\\u060c \\u062a\\u0648\\u0627\\u0635\\u0644 \\u0645\\u0639\\u0646\\u0627 \\u0639\\u0644\\u0649 Vlogapp2024@gmail.com \\u0644\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0645\\u0644\\u062e\\u0635 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0643\\u0644 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u062c\\u0632\\u0621\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u062a\\u0635\\u0627\\u0644\\u0643 \\u0645\\u0639\\u0646\\u0627.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0647\\u0630\\u0627 \\u0639\\u0642\\u062f \\u0628\\u064a\\u0646\\u0643 \\u0648\\u0628\\u064a\\u0646 Vlog. \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0642\\u0631\\u0627\\u0621\\u062a\\u0647\\u0627.\\r\\n1. \\u0645\\u0639 \\u0645\\u0646 \\u062a\\u062a\\u0639\\u0627\\u0642\\u062f\\r\\n\\u062a\\u0639\\u062a\\u0645\\u062f \\u0627\\u0644\\u0634\\u0631\\u0643\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062a\\u0639\\u0627\\u0642\\u062f \\u0645\\u0639\\u0647\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0643\\u0627\\u0646 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0642\\u064a\\u0645 \\u0641\\u064a\\u0647 \\u0623\\u0648 \\u0645\\u0642\\u0631 \\u0639\\u0645\\u0644\\u0643 \\u0627\\u0644\\u0631\\u0626\\u064a\\u0633\\u064a:\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0645\\u0642\\u064a\\u0645\\u064b\\u0627 \\u0641\\u064a \\u0625\\u062d\\u062f\\u0649 \\u0627\\u0644\\u062f\\u0648\\u0644 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0643\\u0644 \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629 \\u0623\\u0648 \\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0634\\u0631\\u0642 \\u0627\\u0644\\u0623\\u0648\\u0633\\u0637 \\u0648\\u0634\\u0645\\u0627\\u0644 \\u0623\\u0641\\u0631\\u064a\\u0642\\u064a\\u0627\\u060c \\u0641\\u0625\\u0646 \\u0639\\u0642\\u062f\\u0643 \\u064a\\u0643\\u0648\\u0646 \\u0645\\u0639 Vlog Technology Limited\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u062a\\u0639\\u062a\\u0645\\u062f \\u0634\\u0631\\u0643\\u0629 Vlog \\u0627\\u0644\\u0645\\u062d\\u062f\\u062f\\u0629 \\u0627\\u0644\\u062a\\u064a \\u0623\\u0628\\u0631\\u0645\\u062a \\u0645\\u0639\\u0647\\u0627 \\u0639\\u0642\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0643\\u0627\\u0646 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0642\\u064a\\u0645 \\u0641\\u064a\\u0647. \\u0625\\u0630\\u0627 \\u0642\\u0644\\u0646\\u0627 \\\"Vlog\\\" \\u0623\\u0648 \\\"\\u0646\\u062d\\u0646\\\" \\u0623\\u0648 \\\"\\u0646\\u0627\\\" \\u0641\\u064a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u0634\\u064a\\u0631 \\u0625\\u0644\\u0649 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062f\\u062f\\u0629 \\u0645\\u0627 \\u0644\\u0645 \\u0646\\u0642\\u0648\\u0644 \\u062e\\u0644\\u0627\\u0641 \\u0630\\u0644\\u0643.\\r\\n2. \\u0645\\u0627 \\u0647\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u063a\\u0637\\u064a\\u0647\\u0627 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u061f\\r\\n\\u062a\\u062d\\u0643\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u062e\\u062f\\u0645\\u0627\\u062a Vlog\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u062a\\u062a\\u0636\\u0645\\u0646 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 Vlog \\u0648Vlog Now) \\u0648\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628 \\u0648\\u0627\\u0644\\u0628\\u0631\\u0627\\u0645\\u062c \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0630\\u0627\\u062a \\u0627\\u0644\\u0635\\u0644\\u0629\\u060c \\u0627\\u0644\\u062a\\u064a \\u064a\\u0645\\u0643\\u0646 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u0627 \\u0639\\u0628\\u0631 \\u0623\\u064a \\u0646\\u0638\\u0627\\u0645 \\u0623\\u0633\\u0627\\u0633\\u064a \\u0623\\u0648 \\u062c\\u0647\\u0627\\u0632 (\\\"\\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\\"). \\u062a\\u0634\\u0643\\u0644 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u062c\\u0632\\u0621\\u064b\\u0627 \\u0645\\u0646 \\u0639\\u0642\\u062f\\u0646\\u0627 \\u0645\\u0639\\u0643\\u060c \\u0648\\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0641\\u0642\\u0629 \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0625\\u0646\\u0643 \\u062a\\u0639\\u062f\\u0646\\u0627 \\u0628\\u0623\\u0646\\u0643 \\u0633\\u062a\\u0644\\u062a\\u0632\\u0645 \\u0628\\u0647\\u0627.\\r\\n\\u062a\\u062e\\u0636\\u0639 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0635\\u0644 \\u0625\\u0644\\u064a\\u0647\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u062d\\u0633\\u0627\\u0628\\u0627\\u062a Vlog For Business \\u0648Vlog Ads Manager \\u0648Vlog Business Center \\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0627\\u0644\\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0646\\u0641\\u0635\\u0644\\u0629.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0639\\u0644\\u0649 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 Vlog \\u0648Vlog Now) \\u0648\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628 \\u0648\\u0627\\u0644\\u0628\\u0631\\u0627\\u0645\\u062c \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0630\\u0627\\u062a \\u0627\\u0644\\u0635\\u0644\\u0629 \\u0628\\u0627\\u0644\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0627\\u0644\\u062a\\u064a \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0628\\u0647\\u0627 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u0627. \\u0643\\u062c\\u0632\\u0621 \\u0645\\u0646 \\u0627\\u0644\\u0627\\u0644\\u062a\\u0632\\u0627\\u0645 \\u0628\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u062a\\u0628\\u0627\\u0639 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627.\\r\\n3. \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u0642\\u062f \\u064a\\u062a\\u0645 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0647\\u0627 \\u0648\\u0641\\u0642\\u064b\\u0627 \\u0644\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645\\u0647\\u0627\\r\\n\\u0642\\u062f \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0634\\u0631\\u0648\\u0637 \\u0648\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0623\\u0648 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u0627\\u0644\\u062a\\u064a \\u0642\\u062f \\u0646\\u0648\\u0641\\u0631\\u0647\\u0627 \\u0644\\u0643\\u060c \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u0646\\u0634\\u0631 \\u0623\\u0646\\u0648\\u0627\\u0639 \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u0628\\u062b\\u0647\\u0627 \\u0645\\u0628\\u0627\\u0634\\u0631\\u0629\\u060c \\u0623\\u0648 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u0623\\u0648 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629. \\u0623\\u0647\\u0645 \\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u062a\\u064a \\u064a\\u062c\\u0628 \\u0623\\u0646 \\u062a\\u0643\\u0648\\u0646 \\u0639\\u0644\\u0649 \\u062f\\u0631\\u0627\\u064a\\u0629 \\u0628\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0643\\u0644 \\u062c\\u0632\\u0621\\u064b\\u0627 \\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0645\\u0648\\u0636\\u062d\\u0629 \\u0623\\u062f\\u0646\\u0627\\u0647 \\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643:\\r\\n\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0645\\u0639\\u062f\\u0646\\u064a\\u0629: \\u062a\\u062d\\u0643\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0634\\u0631\\u0627\\u0621 \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u0629 \\u0644\\u062a\\u0646\\u0634\\u064a\\u0637 \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0623\\u0648 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u0627.\\r\\n\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u0645\\u0643\\u0627\\u0641\\u0622\\u062a: \\u062a\\u062d\\u0643\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0643\\u0627\\u0641\\u0622\\u062a \\u0645\\u0646 \\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648\\u060c \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u0627\\u0633 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a.\\r\\n\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u0645\\u0648\\u0633\\u064a\\u0642\\u0649: \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0639\\u0646\\u062f \\u0646\\u0634\\u0631 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0627\\u0644\\u0630\\u064a \\u064a\\u062a\\u0636\\u0645\\u0646 \\u0645\\u0648\\u0633\\u064a\\u0642\\u0649 \\u0645\\u0635\\u062f\\u0631\\u0647\\u0627 \\u0645\\u0643\\u062a\\u0628\\u0629 \\u0627\\u0644\\u0645\\u0648\\u0633\\u064a\\u0642\\u0649 \\u0623\\u0648 \\u062c\\u0647\\u0627\\u0632\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a.\\r\\n\\u0634\\u0631\\u0648\\u0637 \\u0645\\u0634\\u062a\\u0631\\u064a Vlog Shop: \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0639\\u0646\\u062f \\u0634\\u0631\\u0627\\u0621 \\u0639\\u0646\\u0627\\u0635\\u0631 \\u0645\\u0646 Vlog Shop (\\u062d\\u064a\\u062b\\u0645\\u0627 \\u0643\\u0627\\u0646 \\u0630\\u0644\\u0643 \\u0645\\u062a\\u0627\\u062d\\u064b\\u0627).\\r\\n\\u062a\\u0646\\u0637\\u0628\\u0642 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0639\\u0646\\u062f \\u0642\\u064a\\u0627\\u0645\\u0643 \\u0628\\u0646\\u0634\\u0631 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u0635\\u0629 \\u062a\\u0631\\u0648\\u062c \\u0644\\u0639\\u0644\\u0627\\u0645\\u0629 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u062c\\u0647\\u0629 \\u062e\\u0627\\u0631\\u062c\\u064a\\u0629 \\u0623\\u0648 \\u0645\\u0646\\u062a\\u062c\\u0627\\u062a\\u0647\\u0627 \\u0623\\u0648 \\u062e\\u062f\\u0645\\u0627\\u062a\\u0647\\u0627 \\u0645\\u0642\\u0627\\u0628\\u0644 \\u062f\\u0641\\u0639 \\u0623\\u0648 \\u0623\\u064a \\u062d\\u0627\\u0641\\u0632 \\u0622\\u062e\\u0631.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u062a\\u063a\\u0637\\u064a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0642\\u062f \\u062a\\u0643\\u0648\\u0646 \\u0647\\u0646\\u0627\\u0643 \\u0628\\u0639\\u0636 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u0633\\u064a\\u062a\\u0645 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0647\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0631\\u063a\\u0628 \\u0641\\u064a \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0625\\u0636\\u0627\\u0641\\u064a\\u0629\\u060c \\u0623\\u0648 \\u0646\\u0634\\u0631 \\u0623\\u0646\\u0648\\u0627\\u0639 \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u0628\\u062b\\u0647\\u0627 \\u0645\\u0628\\u0627\\u0634\\u0631\\u0629\\u060c \\u0623\\u0648 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u0623\\u0648 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629.\\r\\n4. \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\r\\n4.1 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0648\\u0646\\u0645\\u0648\\u0630\\u062c \\u0623\\u0639\\u0645\\u0627\\u0644\\u0646\\u0627\\r\\n\\u064a\\u0633\\u0645\\u062d \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0644\\u0643 \\u0648\\u0644\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0628\\u0625\\u0646\\u0634\\u0627\\u0621 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0639\\u0631\\u0636\\u0647 \\u0648\\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639\\u0647 \\u0648\\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0648\\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646. \\u0646\\u0642\\u0648\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u062a\\u062e\\u0635\\u064a\\u0635 \\u0623\\u062c\\u0632\\u0627\\u0621 \\u0645\\u0646 \\u062a\\u062c\\u0631\\u0628\\u062a\\u0643 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0645\\u062b\\u0644 \\u062a\\u0648\\u0641\\u064a\\u0631 \\u062e\\u0644\\u0627\\u0635\\u0629 \\\"\\u0645\\u0646 \\u0623\\u062c\\u0644\\u0643\\\" \\u0641\\u064a \\u062a\\u0637\\u0628\\u064a\\u0642 Vlog. \\u062a\\u0639\\u062f \\u062e\\u0644\\u0627\\u0635\\u0629 \\\"\\u0645\\u0646 \\u0623\\u062c\\u0644\\u0643\\\" \\u0625\\u062d\\u062f\\u0649 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u0641\\u0631\\u064a\\u062f\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0646\\u0638\\u0627\\u0645 \\u062a\\u0648\\u0635\\u064a\\u0627\\u062a \\u0644\\u0644\\u0633\\u0645\\u0627\\u062d \\u0644\\u0643 \\u0628\\u0627\\u0643\\u062a\\u0634\\u0627\\u0641 \\u0645\\u062c\\u0645\\u0648\\u0639\\u0629 \\u0648\\u0627\\u0633\\u0639\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0627\\u0644\\u0645\\u0628\\u062f\\u0639\\u064a\\u0646 \\u0648\\u0627\\u0644\\u0645\\u0648\\u0636\\u0648\\u0639\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0645\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0645\\u0644 \\u0623\\u0646 \\u062a\\u0643\\u0648\\u0646 \\u0645\\u062b\\u064a\\u0631\\u0629 \\u0644\\u0644\\u0627\\u0647\\u062a\\u0645\\u0627\\u0645 \\u0628\\u0627\\u0644\\u0646\\u0633\\u0628\\u0629 \\u0644\\u0643. \\u0639\\u0646\\u062f \\u062a\\u062d\\u062f\\u064a\\u062f \\u0645\\u0627 \\u064a\\u062a\\u0645 \\u0627\\u0644\\u062a\\u0648\\u0635\\u064a\\u0629 \\u0628\\u0647\\u060c \\u064a\\u0623\\u062e\\u0630 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0641\\u064a \\u0627\\u0644\\u0627\\u0639\\u062a\\u0628\\u0627\\u0631 \\u0639\\u0648\\u0627\\u0645\\u0644 \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0625\\u0639\\u062c\\u0627\\u0628\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0648\\u0627\\u0644\\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0648\\u0639\\u0645\\u0644\\u064a\\u0627\\u062a \\u0627\\u0644\\u0628\\u062d\\u062b \\u0648\\u062a\\u0646\\u0648\\u0639 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u0634\\u0627\\u0626\\u0639\\u0629. \\u062a\\u0639\\u0631\\u0641 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0632\\u064a\\u062f \\u062d\\u0648\\u0644 \\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u062a\\u0648\\u0635\\u064a\\u0627\\u062a \\u0648\\u0627\\u0644\\u0623\\u062f\\u0648\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0647\\u0627 \\u0644\\u062a\\u062e\\u0635\\u064a\\u0635 \\u062e\\u0644\\u0627\\u0635\\u062a\\u0643 Vlogapp2024@gmail.com.\\r\\n\\u0646\\u0631\\u064a\\u062f \\u0623\\u0646 \\u062a\\u0643\\u0648\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0645\\u0643\\u0627\\u0646\\u064b\\u0627 \\u0622\\u0645\\u0646\\u064b\\u0627\\u060c \\u062d\\u062a\\u0649 \\u064a\\u062a\\u0645\\u0643\\u0646 \\u0627\\u0644\\u0623\\u0634\\u062e\\u0627\\u0635 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u0645\\u062a\\u0639 \\u0628\\u0637\\u0628\\u064a\\u0639\\u062a\\u0647\\u0645 \\u0648\\u0627\\u0644\\u0627\\u0633\\u062a\\u0645\\u062a\\u0627\\u0639. \\u0646\\u062d\\u0646 \\u0646\\u0639\\u0645\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0646\\u0627 \\u0648\\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0632\\u064a\\u062c\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u0643\\u0646\\u0648\\u0644\\u0648\\u062c\\u064a\\u0627 (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u0648\\u0633\\u0627\\u0626\\u0644 \\u0627\\u0644\\u0622\\u0644\\u064a\\u0629)\\u060c \\u0648\\u0627\\u0644\\u0625\\u0634\\u0631\\u0627\\u0641 \\u0627\\u0644\\u0628\\u0634\\u0631\\u064a\\u060c \\u0648\\u0627\\u0644\\u062a\\u0642\\u0627\\u0631\\u064a\\u0631 \\u0627\\u0644\\u0648\\u0627\\u0631\\u062f\\u0629 \\u0645\\u0646 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627 \\u0644\\u062a\\u062d\\u062f\\u064a\\u062f \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643\\u0627\\u062a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0648\\u0625\\u0646\\u0641\\u0627\\u0630\\u0647\\u0627 \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u062d\\u0645\\u0627\\u064a\\u062a\\u0643 \\u0648\\u062d\\u0645\\u0627\\u064a\\u0629 \\u062c\\u0645\\u064a\\u0639 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627.\\r\\n\\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u0641\\u0631\\u0636 \\u0639\\u0644\\u064a\\u0643 \\u0623\\u064a \\u0631\\u0633\\u0648\\u0645 \\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u0639\\u0638\\u0645 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0648\\u0628\\u062f\\u0644\\u0627\\u064b \\u0645\\u0646 \\u0630\\u0644\\u0643\\u060c \\u064a\\u062f\\u0641\\u0639 \\u0644\\u0646\\u0627 \\u0628\\u0639\\u0636 \\u0627\\u0644\\u0623\\u0641\\u0631\\u0627\\u062f \\u0648\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0645\\u0642\\u0627\\u0628\\u0644 \\u0639\\u0631\\u0636 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0648\\u0642\\u062f \\u0646\\u0641\\u0631\\u0636 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0628\\u0627\\u0626\\u0639\\u064a\\u0646 \\u0639\\u0645\\u0648\\u0644\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0627\\u0644\\u0645\\u0628\\u0627\\u0639\\u0629 \\u0639\\u0644\\u0649 Vlog Shop.\\r\\n\\u064a\\u0645\\u0643\\u0646 \\u0627\\u0644\\u0639\\u062b\\u0648\\u0631 \\u0639\\u0644\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0646\\u0627 \\u0644\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0645\\u0639\\u0647\\u0627 \\u0639\\u0646\\u0643 \\u0641\\u064a \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627. \\u0644\\u0627 \\u062a\\u0634\\u0643\\u0644 \\u0647\\u0630\\u0647 \\u062c\\u0632\\u0621\\u064b\\u0627 \\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0644\\u0643\\u0646\\u0647\\u0627 \\u0645\\u0633\\u062a\\u0646\\u062f\\u0627\\u062a \\u0645\\u0647\\u0645\\u0629 \\u0646\\u0648\\u0635\\u064a \\u0628\\u0634\\u062f\\u0629 \\u0628\\u0642\\u0631\\u0627\\u0621\\u062a\\u0647\\u0627.\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u0641\\u0631\\u0636 \\u0631\\u0633\\u0648\\u0645\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0645\\u0642\\u0627\\u0628\\u0644 \\u0628\\u0639\\u0636 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u0634\\u0631\\u0627\\u0621 \\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u0629 \\u0648\\u0644\\u0643\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0637\\u0648\\u0639\\u064a\\u0629 \\u0648\\u0633\\u064a\\u062a\\u0645 \\u0641\\u0631\\u0636 \\u0623\\u064a \\u062a\\u0643\\u0627\\u0644\\u064a\\u0641 \\u0648\\u0627\\u0636\\u062d \\u0644\\u0643 \\u0642\\u0628\\u0644 \\u0623\\u0646 \\u064a\\u062a\\u0645 \\u062a\\u062d\\u0635\\u064a\\u0644 \\u0623\\u064a \\u0634\\u064a\\u0621 \\u0645\\u0646\\u0643.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0644\\u0627 \\u064a\\u062a\\u0639\\u064a\\u0646 \\u0639\\u0644\\u064a\\u0643 \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0645\\u0642\\u0627\\u0628\\u0644 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0645\\u0639\\u0638\\u0645 \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0642\\u0627\\u0628\\u0644\\u060c \\u0646\\u0633\\u0645\\u062d \\u0644\\u0628\\u0639\\u0636 \\u0627\\u0644\\u0623\\u0641\\u0631\\u0627\\u062f \\u0648\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0628\\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u0646\\u062a\\u0644\\u0642\\u0649 \\u0645\\u062f\\u0641\\u0648\\u0639\\u0627\\u062a \\u0645\\u0642\\u0627\\u0628\\u0644 \\u0630\\u0644\\u0643.\\r\\n4.2 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0628\\u0639\\u0636 \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u0629 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629 \\u062f\\u0648\\u0646 \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0644\\u062f\\u064a\\u0643 \\u062d\\u0633\\u0627\\u0628. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0628\\u062f\\u0648\\u0646 \\u062d\\u0633\\u0627\\u0628\\u060c \\u0641\\u0633\\u062a\\u0638\\u0644 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0633\\u0627\\u0631\\u064a\\u0629 \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0648\\u0633\\u0646\\u0633\\u062a\\u0645\\u0631 \\u0641\\u064a \\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629 \\u0648\\u0641\\u0642\\u064b\\u0627 \\u0644\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627.\\r\\n\\u0644\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0643\\u0627\\u0645\\u0644\\u0629 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0625\\u0646\\u0634\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628 \\u0645\\u0639\\u0646\\u0627. \\u0642\\u062f \\u0646\\u0642\\u062f\\u0645 \\u0623\\u0646\\u0648\\u0627\\u0639\\u064b\\u0627 \\u0645\\u062e\\u062a\\u0644\\u0641\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628\\u0627\\u062a.\\r\\n\\u0639\\u0646\\u062f \\u0625\\u0646\\u0634\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628\\u060c \\u0633\\u062a\\u062a\\u0645 \\u0645\\u0632\\u0627\\u0645\\u0646\\u0629 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0628\\u0631 \\u0643\\u0644 \\u062a\\u0637\\u0628\\u064a\\u0642 Vlog. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646\\u062f \\u0625\\u0646\\u0634\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628 \\u0639\\u0628\\u0631 Vlog\\u060c \\u0633\\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0623\\u064a \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog \\u0623\\u062e\\u0631\\u0649 \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0641\\u064a \\u0628\\u0644\\u062f\\u0643\\u060c \\u0645\\u062b\\u0644 Vlog Now\\u060c \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628. \\u0633\\u062a\\u062a\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0645\\u0632\\u0627\\u0645\\u0646\\u0629 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629)\\u060c \\u0648\\u0623\\u064a \\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u062a\\u062c\\u0631\\u064a\\u0647\\u0627\\u060c \\u0639\\u0628\\u0631 \\u0643\\u0644 \\u062a\\u0637\\u0628\\u064a\\u0642 \\u0645\\u0646 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog.\\r\\n\\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0646\\u0634\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628 \\u0644\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0647\\u0627\\u060c \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062f\\u0642\\u064a\\u0642\\u0629 \\u0648\\u062d\\u062f\\u064a\\u062b\\u0629 \\u0639\\u0646 \\u0646\\u0641\\u0633\\u0643 (\\u0645\\u062b\\u0644 \\u062a\\u0627\\u0631\\u064a\\u062e \\u0645\\u064a\\u0644\\u0627\\u062f\\u0643). \\u0623\\u0646\\u062a \\u062a\\u0648\\u0627\\u0641\\u0642 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062d\\u0641\\u0627\\u0638 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0648\\u062a\\u062d\\u062f\\u064a\\u062b\\u0647\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0641\\u0648\\u0631 \\u0625\\u0630\\u0627 \\u062a\\u063a\\u064a\\u0631\\u062a. \\u0631\\u0627\\u062c\\u0639 Vlogapp2024@gmail.com \\u0644\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0641\\u062a\\u062d \\u062d\\u0633\\u0627\\u0628.\\r\\n\\u0648\\u0645\\u0646 \\u0627\\u0644\\u0645\\u0647\\u0645 \\u0623\\u0646 \\u062a\\u062a\\u062e\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0645\\u0639\\u0642\\u0648\\u0644\\u0629 \\u0644\\u0644\\u062d\\u0641\\u0627\\u0638 \\u0639\\u0644\\u0649 \\u0633\\u0631\\u064a\\u0629 \\u0643\\u0644\\u0645\\u0629 \\u0627\\u0644\\u0645\\u0631\\u0648\\u0631 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0639\\u062f\\u0645 \\u0627\\u0644\\u0643\\u0634\\u0641 \\u0639\\u0646\\u0647\\u0627 \\u0644\\u0623\\u064a \\u0637\\u0631\\u0641 \\u062b\\u0627\\u0644\\u062b. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0639\\u0631\\u0641 \\u0623\\u0648 \\u062a\\u0634\\u0643 \\u0641\\u064a \\u0623\\u0646 \\u0623\\u064a \\u0637\\u0631\\u0641 \\u062b\\u0627\\u0644\\u062b \\u064a\\u0639\\u0631\\u0641 \\u0643\\u0644\\u0645\\u0629 \\u0627\\u0644\\u0645\\u0631\\u0648\\u0631 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0623\\u0648 \\u0642\\u0627\\u0645 \\u0628\\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628\\u0643\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0625\\u062e\\u0628\\u0627\\u0631\\u0646\\u0627 \\u0628\\u0630\\u0644\\u0643 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0641\\u0648\\u0631Vlogapp2024@gmail.com.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0645\\u0647\\u0645. \\u0627\\u062d\\u062a\\u0641\\u0638 \\u0628\\u0647\\u0627 \\u0622\\u0645\\u0646\\u0629. \\u062d\\u0627\\u0641\\u0638 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u062d\\u062a\\u0649 \\u0627\\u0644\\u0622\\u0646. \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0646\\u0634\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628 \\u0641\\u064a \\u0623\\u062d\\u062f \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog\\u060c \\u0633\\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628 (\\u0634\\u0631\\u064a\\u0637\\u0629 \\u0623\\u0646 \\u062a\\u0643\\u0648\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0641\\u064a \\u0628\\u0644\\u062f\\u0643) \\u0648\\u0633\\u062a\\u062a\\u0645 \\u0645\\u0632\\u0627\\u0645\\u0646\\u0629 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0627\\u0644\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0639\\u0628\\u0631 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a.\\r\\n4.3 \\u0627\\u0644\\u062d\\u062f \\u0627\\u0644\\u0623\\u062f\\u0646\\u0649 \\u0644\\u0644\\u0633\\u0646\\r\\n\\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0625\\u0644\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0639\\u0645\\u0631\\u0643 13 \\u0639\\u0627\\u0645\\u064b\\u0627 \\u0623\\u0648 \\u0623\\u0643\\u0628\\u0631. \\u0646\\u062d\\u0646 \\u0646\\u0631\\u0627\\u0642\\u0628 \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u062f\\u0648\\u0646 \\u0627\\u0644\\u0633\\u0646 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0648\\u0633\\u0646\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0646\\u0647\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0625\\u0630\\u0627 \\u0627\\u0634\\u062a\\u0628\\u0647\\u0646\\u0627 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0641\\u064a \\u0623\\u0646\\u0643 \\u062f\\u0648\\u0646 \\u0627\\u0644\\u0633\\u0646 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0623\\u0648 \\u062a\\u0633\\u0645\\u062d \\u0644\\u0634\\u062e\\u0635 \\u062f\\u0648\\u0646 \\u0627\\u0644\\u0633\\u0646 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u062d\\u0633\\u0627\\u0628\\u0643. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0642\\u0631\\u0627\\u0631\\u0646\\u0627 \\u0628\\u0625\\u0646\\u0647\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0639\\u062a\\u0642\\u062f \\u0623\\u0646\\u0646\\u0627 \\u0627\\u0631\\u062a\\u0643\\u0628\\u0646\\u0627 \\u062e\\u0637\\u0623\\u064b \\u0628\\u0634\\u0623\\u0646 \\u0639\\u0645\\u0631\\u0643.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u064a\\u062c\\u0628 \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0639\\u0645\\u0631\\u0643 13 \\u0639\\u0627\\u0645\\u064b\\u0627 \\u0623\\u0648 \\u0623\\u0643\\u062b\\u0631 \\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627.\\r\\n4.4 \\u0645\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\r\\n\\u0628\\u0645\\u0648\\u062c\\u0628 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0645\\u0646 \\u0623\\u062c\\u0644:\\r\\n\\u0625\\u0646\\u0634\\u0627\\u0621 \\u0648\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649\\u061b\\r\\n\\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646\\u061b\\r\\n\\u0639\\u0631\\u0636 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u0623\\u0646\\u0634\\u0623\\u0647 \\u0627\\u0644\\u0622\\u062e\\u0631\\u0648\\u0646\\u061b \\u0648\\r\\n\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0648\\u0627\\u0644\\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0643\\u0645\\u0627 \\u064a\\u062a\\u0645 \\u062a\\u0648\\u0641\\u064a\\u0631\\u0647\\u0627 \\u0644\\u0643 \\u0645\\u0646 \\u0648\\u0642\\u062a \\u0644\\u0622\\u062e\\u0631.\\r\\n\\u0627\\u0644\\u0625\\u0630\\u0646 \\u0627\\u0644\\u0630\\u064a \\u0646\\u0645\\u0646\\u062d\\u0647 \\u0644\\u0643:\\r\\n\\u064a\\u0642\\u062a\\u0635\\u0631 \\u0639\\u0644\\u0649 \\u0645\\u0627 \\u0642\\u0644\\u0646\\u0627 \\u0623\\u0646\\u0646\\u0627 \\u0633\\u0646\\u0633\\u0645\\u062d \\u0628\\u0647 \\u0641\\u064a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u061b\\r\\n\\u0647\\u0648 \\u0641\\u0642\\u0637 \\u0644\\u0623\\u062c\\u0644\\u0643\\u061b\\r\\n\\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646 \\u0623\\u0646 \\u062a\\u0639\\u0637\\u0649 \\u0644\\u0623\\u064a \\u0634\\u062e\\u0635 \\u0622\\u062e\\u0631 \\u0628\\u0648\\u0627\\u0633\\u0637\\u062a\\u0643\\u061b \\u0648\\r\\n\\u064a\\u0645\\u0643\\u0646 \\u0633\\u062d\\u0628\\u0647\\u0627 \\u0644\\u0644\\u0623\\u0633\\u0628\\u0627\\u0628 \\u0627\\u0644\\u0645\\u0633\\u0645\\u0648\\u062d \\u0628\\u0647\\u0627 \\u0641\\u064a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637.\\r\\n\\u064a\\u0639\\u062a\\u0645\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0628\\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0639\\u0644\\u0649 \\u0639\\u0645\\u0631\\u0643. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644:\\r\\n\\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0629 \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631\\u0629: \\u064a\\u062c\\u0628 \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0639\\u0645\\u0631\\u0643 16 \\u0639\\u0627\\u0645\\u064b\\u0627 \\u0623\\u0648 \\u0623\\u0643\\u062b\\u0631 \\u062d\\u062a\\u0649 \\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0648\\u0638\\u064a\\u0641\\u0629 \\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0629 \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631\\u0629.\\r\\nVlog LIVE: \\u064a\\u062c\\u0628 \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0639\\u0645\\u0631\\u0643 18 \\u0639\\u0627\\u0645\\u064b\\u0627 \\u0623\\u0648 \\u0623\\u0643\\u062b\\u0631 \\u0644\\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u0628\\u062b \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631 \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0628\\u062b \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631.\\r\\n\\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u0629: \\u064a\\u062c\\u0628 \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0639\\u0645\\u0631\\u0643 18 \\u0639\\u0627\\u0645\\u064b\\u0627 \\u0623\\u0648 \\u0623\\u0643\\u062b\\u0631 \\u0644\\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u0629.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0627\\u0644\\u0639\\u062f\\u064a\\u062f \\u0645\\u0646 \\u0627\\u0644\\u0623\\u0634\\u064a\\u0627\\u0621 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0648\\u0644\\u0643\\u0646 \\u0642\\u062f \\u0644\\u0627 \\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0643\\u0644 \\u0645\\u064a\\u0632\\u0629 \\u0625\\u0630\\u0627 \\u0644\\u0645 \\u062a\\u0643\\u0646 \\u0643\\u0628\\u064a\\u0631\\u064b\\u0627 \\u0628\\u0645\\u0627 \\u064a\\u0643\\u0641\\u064a.\\r\\n4.5 \\u0645\\u0627 \\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0641\\u0639\\u0644\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0631\\u063a\\u0628 \\u0641\\u064a \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0641\\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0625\\u0646\\u0634\\u0627\\u0621 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u0646\\u0634\\u0631\\u0647 \\u0623\\u0648 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0623\\u0648 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637 \\u0628\\u0647 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639\\u0647 \\u0628\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0623\\u062e\\u0631\\u0649 \\u0628\\u0645\\u0627 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627.\\r\\n\\u0641\\u064a \\u0623\\u064a \\u062d\\u0627\\u0644 \\u0645\\u0646 \\u0627\\u0644\\u0623\\u062d\\u0648\\u0627\\u0644\\u060c \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0639\\u062f\\u0645 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0644\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0645\\u0627 \\u064a\\u0644\\u064a:\\r\\n\\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0623\\u064a \\u0634\\u064a\\u0621 \\u063a\\u064a\\u0631 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a (\\u0648\\u0647\\u0630\\u0627 \\u064a\\u0634\\u0645\\u0644 \\u0627\\u0644\\u0646\\u0634\\u0631 \\u0623\\u0648 \\u0627\\u0644\\u0628\\u062b \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631 \\u0623\\u0648 \\u062a\\u0648\\u0632\\u064a\\u0639 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u063a\\u064a\\u0631 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a)\\u061b\\r\\n\\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0623\\u064a \\u0634\\u064a\\u0621 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0623\\u0648 \\u0644\\u0648\\u0627\\u0626\\u062d \\u0645\\u0643\\u0627\\u0641\\u062d\\u0629 \\u063a\\u0633\\u0644 \\u0627\\u0644\\u0623\\u0645\\u0648\\u0627\\u0644 \\u0648\\u0645\\u0643\\u0627\\u0641\\u062d\\u0629 \\u062a\\u0645\\u0648\\u064a\\u0644 \\u0627\\u0644\\u0625\\u0631\\u0647\\u0627\\u0628 \\u0648\\u0636\\u0648\\u0627\\u0628\\u0637 \\u0627\\u0644\\u062a\\u0635\\u062f\\u064a\\u0631 \\u0648\\u0627\\u0644\\u0639\\u0642\\u0648\\u0628\\u0627\\u062a \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629\\u061b\\r\\n\\u0627\\u0644\\u062a\\u0639\\u0627\\u0645\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u0642\\u0627\\u0635\\u0631\\u064a\\u0646 \\u0628\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0627\\u0633\\u062a\\u063a\\u0644\\u0627\\u0644\\u064a\\u0629 \\u0623\\u0648 \\u063a\\u064a\\u0631 \\u0645\\u0646\\u0627\\u0633\\u0628\\u0629\\u061b\\r\\n\\u062a\\u0642\\u0648\\u064a\\u0636 \\u0639\\u0645\\u0644\\u064a\\u0627\\u062a \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0623\\u0648 \\u0623\\u0645\\u0646\\u0647\\u0627\\u061b\\r\\n\\u0627\\u0644\\u0627\\u0646\\u062e\\u0631\\u0627\\u0637 \\u0641\\u064a \\u0633\\u0644\\u0648\\u0643\\u064a\\u0627\\u062a \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u063a\\u064a\\u0631 \\u062d\\u0642\\u064a\\u0642\\u064a\\u0629 \\u0645\\u062b\\u0644 \\u062a\\u0634\\u063a\\u064a\\u0644 \\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0639\\u0634\\u0648\\u0627\\u0626\\u064a \\u0623\\u0648 \\u062d\\u0633\\u0627\\u0628\\u0627\\u062a \\u0627\\u0646\\u062a\\u062d\\u0627\\u0644 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629 \\u0623\\u0648 \\u0628\\u0623\\u064a \\u0648\\u0633\\u064a\\u0644\\u0629 \\u0623\\u062e\\u0631\\u0649 \\u0645\\u0641\\u0635\\u0644\\u0629 \\u0628\\u0634\\u0643\\u0644 \\u0623\\u0643\\u0628\\u0631 \\u0641\\u064a \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627\\u061b\\r\\n\\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0637\\u0639\\u0648\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0642\\u0627\\u0631\\u064a\\u0631 \\u0623\\u0648 \\u0627\\u0644\\u0625\\u0634\\u0639\\u0627\\u0631\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u0634\\u0643\\u0627\\u0648\\u0649 \\u0627\\u0644\\u062a\\u064a \\u0644\\u0627 \\u0623\\u0633\\u0627\\u0633 \\u0644\\u0647\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0635\\u062d\\u0629 \\u0628\\u0634\\u0643\\u0644 \\u0648\\u0627\\u0636\\u062d\\u061b\\r\\n\\u0627\\u0633\\u062a\\u062e\\u0631\\u0627\\u062c \\u0623\\u064a \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0623\\u0648 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0645\\u0646 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0623\\u064a \\u0646\\u0638\\u0627\\u0645 \\u0623\\u0648 \\u0628\\u0631\\u0646\\u0627\\u0645\\u062c \\u0622\\u0644\\u064a \\u0644\\u0627 \\u062a\\u0648\\u0641\\u0631\\u0647 Vlog \\u0623\\u0648 \\u062a\\u062a\\u0645 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0641\\u0642\\u0629 \\u0639\\u0644\\u064a\\u0647 \\u0643\\u062a\\u0627\\u0628\\u064a\\u064b\\u0627 \\u0645\\u0646 Vlog\\u061b \\u0623\\u0648\\r\\n\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0623\\u0648 \\u0645\\u062d\\u0627\\u0648\\u0644\\u0629 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u062d\\u0633\\u0627\\u0628 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0622\\u062e\\u0631 \\u062f\\u0648\\u0646 \\u0625\\u0630\\u0646.\\r\\n\\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0639\\u062f\\u0645 \\u0646\\u0634\\u0631 \\u0623\\u0648 \\u0628\\u062b \\u0645\\u0628\\u0627\\u0634\\u0631 \\u0623\\u0648 \\u062a\\u0648\\u0632\\u064a\\u0639 \\u0623\\u064a \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u0627\\u0644\\u0630\\u064a:\\r\\n\\u064a\\u0646\\u062a\\u0647\\u0643 \\u062d\\u0642\\u0648\\u0642 \\u0623\\u064a \\u0634\\u062e\\u0635 \\u0622\\u062e\\u0631 (\\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u0644\\u0643\\u064a\\u0629 \\u0627\\u0644\\u0641\\u0643\\u0631\\u064a\\u0629 \\u0648\\/\\u0623\\u0648 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0648\\/\\u0623\\u0648 \\u0627\\u0644\\u062d\\u0642\\u0648\\u0642 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629 \\u0644\\u0644\\u0623\\u0634\\u062e\\u0627\\u0635 \\u0627\\u0644\\u0623\\u062d\\u064a\\u0627\\u0621 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u062a\\u0648\\u0641\\u064a\\u0646)\\u061b\\r\\n\\u064a\\u0634\\u0643\\u0644 \\u0623\\u0648 \\u064a\\u0634\\u062c\\u0639 \\u0623\\u0648 \\u064a\\u0642\\u062f\\u0645 \\u062a\\u0639\\u0644\\u064a\\u0645\\u0627\\u062a \\u0644\\u062c\\u0631\\u064a\\u0645\\u0629 \\u062c\\u0646\\u0627\\u0626\\u064a\\u0629 \\u0623\\u0648 \\u0623\\u0646\\u0634\\u0637\\u0629 \\u062e\\u0637\\u064a\\u0631\\u0629 \\u0642\\u062f \\u062a\\u0624\\u062f\\u064a \\u0625\\u0644\\u0649 \\u0625\\u0635\\u0627\\u0628\\u0629 \\u062e\\u0637\\u064a\\u0631\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0648\\u0641\\u0627\\u0629 \\u0623\\u0648 \\u0625\\u064a\\u0630\\u0627\\u0621 \\u0627\\u0644\\u0646\\u0641\\u0633\\u061b\\r\\n\\u064a\\u0646\\u0634\\u0631 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0636\\u0644\\u0644\\u0629 \\u0636\\u0627\\u0631\\u0629 \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0637\\u0626\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062d\\u0631\\u0636 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0643\\u0631\\u0627\\u0647\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u062d\\u064a\\u0632 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0636\\u0644\\u0644 \\u0627\\u0644\\u0627\\u0646\\u062a\\u062e\\u0627\\u0628\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u0639\\u0645\\u0644\\u064a\\u0627\\u062a \\u0627\\u0644\\u0645\\u062f\\u0646\\u064a\\u0629 \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0623\\u0648 \\u062a\\u0624\\u062b\\u0631 \\u0639\\u0644\\u064a\\u0647\\u0627 \\u0628\\u0634\\u0643\\u0644 \\u063a\\u064a\\u0631 \\u0644\\u0627\\u0626\\u0642\\u061b\\r\\n\\u064a\\u062d\\u062a\\u0648\\u064a \\u0639\\u0644\\u0649 \\u062a\\u0647\\u062f\\u064a\\u062f \\u0645\\u0646 \\u0623\\u064a \\u0646\\u0648\\u0639 \\u0623\\u0648 \\u064a\\u062e\\u064a\\u0641 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0623\\u0648 \\u064a\\u0636\\u0627\\u064a\\u0642\\u0647\\u0645\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0646\\u0634\\u0631 \\u0623\\u064a \\u0631\\u0633\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u0648\\u0627\\u062f \\u0627\\u0644\\u062a\\u064a \\u062a\\u0647\\u062f\\u0641 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0633\\u062e\\u0631\\u064a\\u0629 \\u0645\\u0646 \\u0634\\u062e\\u0635 \\u0645\\u0627 \\u0623\\u0648 \\u0625\\u0630\\u0644\\u0627\\u0644\\u0647 \\u0623\\u0648 \\u0625\\u062d\\u0631\\u0627\\u062c\\u0647 \\u0623\\u0648 \\u062a\\u062e\\u0648\\u064a\\u0641\\u0647 \\u0623\\u0648 \\u0625\\u064a\\u0630\\u0627\\u0626\\u0647\\u061b\\r\\n\\u0641\\u0627\\u062d\\u0634\\u0629 \\u0623\\u0648 \\u0625\\u0628\\u0627\\u062d\\u064a\\u0629 \\u0623\\u0648 \\u062a\\u0631\\u0648\\u062c \\u0644\\u0645\\u0648\\u0627\\u062f \\u062c\\u0646\\u0633\\u064a\\u0629 \\u0635\\u0631\\u064a\\u062d\\u0629 (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637 \\u0628\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0648\\u064a\\u0628 \\u0644\\u0644\\u0628\\u0627\\u0644\\u063a\\u064a\\u0646 \\u0623\\u0648 \\u0645\\u0648\\u0627\\u0642\\u0639 \\u0625\\u0628\\u0627\\u062d\\u064a\\u0629)\\u061b\\r\\n\\u0628\\u063a\\u064a\\u0636\\u0629 \\u0623\\u0648 \\u062a\\u062d\\u0631\\u064a\\u0636\\u064a\\u0629.\\r\\n\\u064a\\u062d\\u062a\\u0648\\u064a \\u0639\\u0644\\u0649 \\u0623\\u0648 \\u064a\\u0634\\u062c\\u0639 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0639\\u0646\\u0641 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0645\\u064a\\u064a\\u0632 \\u0639\\u0644\\u0649 \\u0623\\u0633\\u0627\\u0633 \\u0627\\u0644\\u0639\\u0631\\u0642 \\u0623\\u0648 \\u0627\\u0644\\u0623\\u0635\\u0644 \\u0627\\u0644\\u0639\\u0631\\u0642\\u064a \\u0623\\u0648 \\u0627\\u0644\\u0623\\u0635\\u0644 \\u0627\\u0644\\u0642\\u0648\\u0645\\u064a \\u0623\\u0648 \\u0627\\u0644\\u062f\\u064a\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u0637\\u0628\\u0642\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0648\\u062c\\u0647 \\u0627\\u0644\\u062c\\u0646\\u0633\\u064a \\u0623\\u0648 \\u0627\\u0644\\u062c\\u0646\\u0633 \\u0623\\u0648 \\u0627\\u0644\\u0647\\u0648\\u064a\\u0629 \\u0627\\u0644\\u062c\\u0646\\u0633\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0631\\u0636 \\u0627\\u0644\\u062e\\u0637\\u064a\\u0631 \\u0623\\u0648 \\u0627\\u0644\\u0625\\u0639\\u0627\\u0642\\u0629 \\u0623\\u0648 \\u062d\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0647\\u062c\\u0631\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0639\\u0645\\u0631\\u061b \\u0623\\u0648\\r\\n\\u064a\\u062d\\u062a\\u0648\\u064a \\u0628\\u062e\\u0644\\u0627\\u0641 \\u0630\\u0644\\u0643 \\u0639\\u0644\\u0649 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0636\\u0627\\u0631 (\\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u064a\\u0633\\u0628\\u0628 \\u0636\\u0631\\u0631\\u064b\\u0627 \\u062c\\u0633\\u062f\\u064a\\u064b\\u0627 \\u0623\\u0648 \\u0639\\u0642\\u0644\\u064a\\u064b\\u0627 \\u0623\\u0648 \\u0645\\u0639\\u0646\\u0648\\u064a\\u064b\\u0627 \\u0644\\u0644\\u0642\\u0627\\u0635\\u0631\\u064a\\u0646).\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0625\\u0628\\u0644\\u0627\\u063a \\u0639\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u063a\\u064a\\u0631 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a \\u0627\\u0644\\u0645\\u0634\\u062a\\u0628\\u0647 \\u0628\\u0647 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0625\\u0628\\u0644\\u0627\\u063a \\u0627\\u0644\\u0645\\u062a\\u0648\\u0641\\u0631\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0627\\u0633\\u062a\\u0645\\u062a\\u0639 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u0645\\u0635\\u0644\\u062d\\u0629 \\u062c\\u0645\\u064a\\u0639 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627\\u060c \\u0647\\u0646\\u0627\\u0643 \\u0642\\u0648\\u0627\\u0639\\u062f \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0627\\u062a\\u0628\\u0627\\u0639\\u0647\\u0627. \\u0625\\u0630\\u0627 \\u0631\\u0623\\u064a\\u062a \\u0634\\u064a\\u0626\\u064b\\u0627 \\u0644\\u0627 \\u064a\\u0646\\u0628\\u063a\\u064a \\u0623\\u0646 \\u064a\\u0643\\u0648\\u0646 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0625\\u062e\\u0628\\u0627\\u0631\\u0646\\u0627 \\u0628\\u0630\\u0644\\u0643\\r\\n\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\r\\n\\u0645\\u0646 \\u0627\\u0644\\u0645\\u0647\\u0645 \\u0623\\u0646 \\u062a\\u0641\\u0647\\u0645 \\u0645\\u0627 \\u064a\\u062d\\u062f\\u062b \\u0644\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0646\\u0634\\u0627\\u0626\\u0647 \\u0623\\u0648 \\u0646\\u0634\\u0631\\u0647 \\u0623\\u0648 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629:\\r\\n\\u0623\\u0646\\u062a \\u0645\\u0633\\u0624\\u0648\\u0644 \\u0639\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0648\\u0641\\u0631\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u064a\\u062c\\u0628 \\u0623\\u0646 \\u062a\\u062a\\u0645\\u062a\\u0639 \\u0628\\u062c\\u0645\\u064a\\u0639 \\u0627\\u0644\\u062d\\u0642\\u0648\\u0642 \\u0627\\u0644\\u0644\\u0627\\u0632\\u0645\\u0629 \\u0644\\u0625\\u0646\\u0634\\u0627\\u0621 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u0646\\u0634\\u0631\\u0647 \\u0623\\u0648 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a.\\r\\n\\u0639\\u0646\\u062f \\u0625\\u0646\\u0634\\u0627\\u0621 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u0646\\u0634\\u0631\\u0647 \\u0623\\u0648 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0639\\u0628\\u0631 \\u0623\\u062d\\u062f \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog\\u060c \\u0642\\u062f \\u064a\\u062a\\u0645 \\u0646\\u0634\\u0631 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u062a\\u0644\\u0642\\u0627\\u0626\\u064a\\u064b\\u0627 \\u0639\\u0628\\u0631 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 (\\u0639\\u0644\\u0649 \\u0627\\u0644\\u0631\\u063a\\u0645 \\u0645\\u0646 \\u0623\\u0646 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0633\\u062a\\u0638\\u0644 \\u0645\\u0637\\u0628\\u0642\\u0629 \\u0639\\u0628\\u0631 \\u0643\\u0644 \\u062a\\u0637\\u0628\\u064a\\u0642). \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0646\\u0634\\u0631 \\u0645\\u0642\\u0637\\u0639 \\u0641\\u064a\\u062f\\u064a\\u0648 \\u0639\\u0644\\u0649 Vlog Now\\u060c \\u0633\\u064a\\u062a\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0646\\u0634\\u0631 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0648\\u0633\\u064a\\u0643\\u0648\\u0646 \\u0645\\u062a\\u0627\\u062d\\u064b\\u0627 \\u0639\\u0644\\u0649 Vlog.\\r\\n\\u0646\\u0642\\u0648\\u0645 \\u0628\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0628\\u0634\\u0643\\u0644 \\u0627\\u0633\\u062a\\u0628\\u0627\\u0642\\u064a (\\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u0623\\u0646\\u0638\\u0645\\u0629 \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0648\\u0627\\u0644\\u062a\\u064a \\u062a\\u0643\\u062a\\u0634\\u0641 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u063a\\u064a\\u0631 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a \\u0648\\u0627\\u0644\\u0636\\u0627\\u0631) \\u0648\\u0628\\u0634\\u0643\\u0644 \\u062a\\u0641\\u0627\\u0639\\u0644\\u064a (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646\\u062f \\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0625\\u0634\\u0639\\u0627\\u0631 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u0633\\u0644\\u0637\\u0627\\u062a). \\u0644\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643\\u060c \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0646\\u0634\\u0631 \\u0645\\u062c\\u0645\\u0648\\u0639\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u0643\\u0646\\u0648\\u0644\\u0648\\u062c\\u064a\\u0627 \\u0648\\u0627\\u0644\\u0645\\u0634\\u0631\\u0641\\u064a\\u0646 \\u0627\\u0644\\u0628\\u0634\\u0631\\u064a\\u064a\\u0646. \\u0646\\u0647\\u062c\\u0646\\u0627 \\u0641\\u064a \\u0627\\u0644\\u0625\\u0634\\u0631\\u0627\\u0641 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649.\\r\\n\\u064a\\u062c\\u0648\\u0632 \\u0644\\u0646\\u0627 \\u0625\\u0632\\u0627\\u0644\\u0629 \\u0623\\u0648 \\u062a\\u0642\\u064a\\u064a\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0623\\u064a \\u0645\\u062d\\u062a\\u0648\\u0649\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\u060c \\u0625\\u0630\\u0627 \\u0643\\u0646\\u0627 \\u0646\\u0639\\u062a\\u0642\\u062f \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 (1) \\u0623\\u0646\\u0647 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u061b \\u0623\\u0648 (2) \\u0623\\u0646\\u0647\\u0627 \\u062a\\u0633\\u0628\\u0628 \\u0636\\u0631\\u0631\\u064b\\u0627 \\u0644\\u0646\\u0627 \\u0623\\u0648 \\u0644\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0646\\u0627 \\u0623\\u0648 \\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627 \\u0623\\u0648 \\u0644\\u0623\\u0637\\u0631\\u0627\\u0641 \\u062b\\u0627\\u0644\\u062b\\u0629 \\u0623\\u062e\\u0631\\u0649. \\u062a\\u062d\\u062f\\u062f \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0625\\u0632\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0623\\u0648 \\u062a\\u0642\\u064a\\u064a\\u062f\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a.\\r\\n\\u0625\\u0630\\u0627 \\u0642\\u0645\\u0646\\u0627 \\u0628\\u0625\\u0632\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0623\\u0648 \\u062a\\u0642\\u064a\\u064a\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u060c \\u0641\\u0633\\u0646\\u062e\\u0637\\u0631\\u0643 \\u062f\\u0648\\u0646 \\u062a\\u0623\\u062e\\u064a\\u0631 \\u063a\\u064a\\u0631 \\u0645\\u0628\\u0631\\u0631 \\u0648\\u0646\\u0630\\u0643\\u0631 \\u0623\\u0633\\u0628\\u0627\\u0628 \\u0642\\u0631\\u0627\\u0631\\u0646\\u0627\\u060c \\u0645\\u0627 \\u0644\\u0645 \\u064a\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0627\\u0633\\u0628 \\u0644\\u0646\\u0627 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u064a\\u064f\\u0645\\u0646\\u0639\\u0646\\u0627 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643).\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0639\\u062a\\u0642\\u062f \\u0623\\u0646\\u0646\\u0627 \\u0627\\u0631\\u062a\\u0643\\u0628\\u0646\\u0627 \\u062e\\u0637\\u0623\\u064b \\u0641\\u064a \\u0625\\u0632\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0623\\u0648 \\u062a\\u0642\\u064a\\u064a\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u060c \\u0641\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0627\\u0644\\u0645\\u062a\\u0648\\u0641\\u0631\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u0633\\u0646\\u0631\\u0627\\u062c\\u0639 \\u0642\\u0631\\u0627\\u0631\\u0646\\u0627 \\u0648\\u0646\\u062a\\u062e\\u0630 \\u0627\\u0644\\u0642\\u0631\\u0627\\u0631 \\u0645\\u0631\\u0629 \\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0644\\u0643 \\u0627\\u0644\\u062d\\u0631\\u064a\\u0629 \\u0641\\u064a \\u0625\\u0632\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0641\\u064a \\u0623\\u064a \\u0648\\u0642\\u062a. \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0632\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0645\\u0646 \\u0623\\u062d\\u062f \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog\\u060c \\u0633\\u062a\\u062a\\u0645 \\u0625\\u0632\\u0627\\u0644\\u062a\\u0647 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0645\\u0646 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649\\u060c \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u062d\\u0630\\u0641 \\u0645\\u0642\\u0637\\u0639 \\u0641\\u064a\\u062f\\u064a\\u0648 \\u0646\\u0634\\u0631\\u062a\\u0647 \\u0639\\u0644\\u0649 Vlog Now\\u060c \\u0633\\u064a\\u062a\\u0645 \\u062d\\u0630\\u0641\\u0647 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0645\\u0646 Vlog.\\r\\n\\u0627\\u0639\\u062a\\u0645\\u0627\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0644\\u062f\\u064a\\u0643\\u060c \\u0625\\u0630\\u0627 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u0648\\u0646 \\u0622\\u062e\\u0631\\u0648\\u0646 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0644\\u0625\\u0646\\u0634\\u0627\\u0621 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u062c\\u062f\\u064a\\u062f (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 Duet \\u0623\\u0648 Stitch) \\u0623\\u0648 \\u0634\\u0627\\u0631\\u0643\\u0648\\u0627 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0639\\u0644\\u0649 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062c\\u0647\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0631\\u062c\\u064a\\u0629\\u060c \\u0641\\u0642\\u062f \\u064a\\u0628\\u0642\\u0649 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062c\\u062f\\u064a\\u062f \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0623\\u0648 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u062c\\u0647\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0631\\u062c\\u064a\\u0629. \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u062d\\u062a\\u0649 \\u0644\\u0648 \\u0642\\u0645\\u062a \\u0644\\u0627\\u062d\\u0642\\u064b\\u0627 \\u0628\\u062d\\u0630\\u0641 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0623\\u0648 \\u062d\\u0633\\u0627\\u0628\\u0643. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0637\\u0644\\u0628 \\u0645\\u0646\\u0641\\u0635\\u0644 \\u0644\\u062d\\u0630\\u0641 \\u0645\\u0642\\u0627\\u0637\\u0639 \\u0641\\u064a\\u062f\\u064a\\u0648 Duet \\u0623\\u0648 Stitch \\u0627\\u0644\\u062a\\u064a \\u062a\\u062d\\u062a\\u0648\\u064a \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643.\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u0642\\u064a\\u064a\\u062f \\u0643\\u064a\\u0641\\u064a\\u0629 \\u062a\\u0641\\u0627\\u0639\\u0644 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0645\\u0639 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0647 \\u0641\\u064a \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643. \\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0627\\u0644\\u062a\\u0639\\u0631\\u0641 \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0642\\u0628\\u0644 \\u0646\\u0634\\u0631 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\r\\n. \\u0645\\u0627 \\u0646\\u0639\\u062f\\u0643 \\u0628\\u0647\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u0639\\u062f\\u0643 \\u0628\\u062a\\u0632\\u0648\\u064a\\u062f\\u0643 \\u0628\\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0628\\u0645\\u0647\\u0627\\u0631\\u0629 \\u0648\\u0631\\u0639\\u0627\\u064a\\u0629 \\u0645\\u0639\\u0642\\u0648\\u0644\\u0629 \\u0648\\u0628\\u0627\\u0644\\u062a\\u0635\\u0631\\u0641 \\u0628\\u062d\\u0631\\u0635 \\u0645\\u0647\\u0646\\u064a \\u0637\\u0627\\u0644\\u0645\\u0627 \\u0627\\u062e\\u062a\\u0631\\u0646\\u0627 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0633\\u0646\\u062a\\u062e\\u0630 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u062c\\u0645\\u064a\\u0639 \\u0627\\u0644\\u062e\\u0637\\u0648\\u0627\\u062a \\u0627\\u0644\\u0645\\u0639\\u0642\\u0648\\u0644\\u0629 \\u0644\\u0644\\u062d\\u0641\\u0627\\u0638 \\u0639\\u0644\\u0649 \\u0628\\u064a\\u0626\\u0629 \\u0622\\u0645\\u0646\\u0629 \\u0648\\u0645\\u0623\\u0645\\u0648\\u0646\\u0629 \\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627. \\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u0639\\u062f \\u0628\\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0623\\u0628\\u062f \\u0623\\u0648 \\u0628\\u0634\\u0643\\u0644\\u0647\\u0627 \\u0627\\u0644\\u062d\\u0627\\u0644\\u064a \\u0644\\u0623\\u064a \\u0641\\u062a\\u0631\\u0629 \\u0632\\u0645\\u0646\\u064a\\u0629 \\u0645\\u0639\\u064a\\u0646\\u0629.\\r\\n\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0647\\u0648 \\u0641\\u064a \\u0627\\u0644\\u063a\\u0627\\u0644\\u0628 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0645\\u0646 \\u0625\\u0646\\u0634\\u0627\\u0621 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u064a\\u0642\\u062f\\u0645\\u0647 \\u0627\\u0644\\u0623\\u0641\\u0631\\u0627\\u062f \\u0648\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627. \\u0628\\u0645\\u0639\\u0646\\u0649 \\u0622\\u062e\\u0631\\u060c Vlog \\u0644\\u064a\\u0633 \\u0645\\u0646\\u0634\\u0626 \\u0645\\u0639\\u0638\\u0645 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 (\\u0639\\u0644\\u0649 \\u0627\\u0644\\u0631\\u063a\\u0645 \\u0645\\u0646 \\u0623\\u0646 Vlog \\u0642\\u062f \\u064a\\u0646\\u062a\\u062c \\u0628\\u0639\\u0636 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649). \\u0644\\u0630\\u0644\\u0643\\u060c \\u0648\\u0645\\u0639 \\u0645\\u0631\\u0627\\u0639\\u0627\\u0629 \\u0623\\u064a \\u0644\\u0648\\u0627\\u0626\\u062d \\u0623\\u0648 \\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0625\\u0644\\u0632\\u0627\\u0645\\u064a\\u0629 (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0644\\u0648\\u0627\\u0626\\u062d \\u0623\\u0648 \\u0627\\u0644\\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0627\\u0644\\u0642\\u0637\\u0627\\u0639\\u064a\\u0629) \\u0627\\u0644\\u0645\\u0637\\u0628\\u0642\\u0629 \\u0639\\u0644\\u0649 Vlog\\u060c \\u0644\\u0627 \\u062a\\u0633\\u062a\\u0637\\u064a\\u0639 Vlog \\u0648\\u0644\\u0627 \\u062a\\u0639\\u062f \\u0628\\u0623\\u0646 \\u0623\\u064a\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u0623\\u0646\\u0634\\u0623\\u0647 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u0648\\u0646 \\u0648\\u0627\\u0644\\u0630\\u064a \\u062a\\u062c\\u062f\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a:\\r\\n\\u062f\\u0642\\u064a\\u0642\\u0629 \\u0623\\u0648 \\u0643\\u0627\\u0645\\u0644\\u0629 \\u0623\\u0648 \\u062d\\u062f\\u064a\\u062b\\u0629\\u061b\\r\\n\\u0644\\u0627 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u062d\\u0642\\u0648\\u0642 \\u0627\\u0644\\u0637\\u0631\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b\\u061b\\r\\n\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u061b \\u0623\\u0648 \\u0623\\u0646\\u0647\\r\\n\\u0644\\u0646 \\u064a\\u0633\\u064a\\u0621 \\u0625\\u0644\\u064a\\u0643.\\r\\n\\u0623\\u0646\\u062a \\u062a\\u0641\\u0647\\u0645 \\u0648\\u062a\\u0648\\u0627\\u0641\\u0642 \\u0639\\u0644\\u0649 \\u0623\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u0642\\u062f \\u062a\\u0631\\u0627\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0644\\u0627 \\u064a\\u0645\\u062b\\u0644 \\u0648\\u062c\\u0647\\u0627\\u062a \\u0646\\u0638\\u0631\\u0646\\u0627 \\u0623\\u0648 \\u0642\\u064a\\u0645\\u0646\\u0627 \\u0648\\u0642\\u062f \\u0644\\u0627 \\u064a\\u0643\\u0648\\u0646 \\u0645\\u0646\\u0627\\u0633\\u0628\\u064b\\u0627 \\u0644\\u063a\\u0631\\u0636\\u0643.\\r\\n\\u0642\\u062f \\u062a\\u062d\\u062a\\u0648\\u064a \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0639\\u0644\\u0649 \\u0631\\u0648\\u0627\\u0628\\u0637 \\u0644\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0648\\u064a\\u0628 \\u0623\\u0648 \\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0623\\u0648 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0623\\u0648 \\u0639\\u0631\\u0648\\u0636 \\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u062c\\u0647\\u0627\\u062a \\u062e\\u0627\\u0631\\u062c\\u064a\\u0629 \\u0623\\u0648 \\u0623\\u062d\\u062f\\u0627\\u062b \\u0623\\u0648 \\u0623\\u0646\\u0634\\u0637\\u0629 \\u0623\\u062e\\u0631\\u0649 \\u0644\\u0627 \\u062a\\u0648\\u0641\\u0631\\u0647\\u0627 Vlog \\u0623\\u0648 \\u062a\\u0645\\u0644\\u0643\\u0647\\u0627 \\u0623\\u0648 \\u062a\\u0633\\u064a\\u0637\\u0631 \\u0639\\u0644\\u064a\\u0647\\u0627. \\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u0624\\u064a\\u062f \\u0623\\u064a \\u0645\\u0648\\u0627\\u0642\\u0639 \\u0648\\u064a\\u0628 \\u0623\\u0648 \\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0623\\u0648 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0623\\u0648 \\u0639\\u0631\\u0648\\u0636 \\u0623\\u0648 \\u0623\\u062d\\u062f\\u0627\\u062b \\u0623\\u0648 \\u0623\\u0646\\u0634\\u0637\\u0629 \\u0623\\u0648 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0623\\u0648 \\u0645\\u0648\\u0627\\u062f \\u0623\\u0648 \\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u062c\\u0647\\u0627\\u062a \\u062e\\u0627\\u0631\\u062c\\u064a\\u0629. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0647\\u0627 \\u0639\\u0644\\u0649 \\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u062a\\u0643 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629.\\r\\n\\u0634\\u0631\\u064a\\u0637\\u0629 \\u0623\\u0646\\u0646\\u0627 \\u062a\\u0635\\u0631\\u0641\\u0646\\u0627 \\u0628\\u0639\\u0646\\u0627\\u064a\\u0629 \\u0645\\u0647\\u0646\\u064a\\u0629\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0644\\u0627 \\u0646\\u062a\\u062d\\u0645\\u0644 \\u0627\\u0644\\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0629 \\u0639\\u0646 \\u0627\\u0644\\u062e\\u0633\\u0627\\u0631\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0636\\u0631\\u0631 \\u0627\\u0644\\u0630\\u064a \\u0646\\u0633\\u0628\\u0628\\u0647\\u060c \\u0625\\u0644\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646:\\r\\n\\u0627\\u0644\\u0646\\u0627\\u062c\\u0645\\u0629 \\u0639\\u0646 \\u062e\\u0631\\u0642\\u0646\\u0627 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u061b \\u0623\\u0648\\r\\n\\u064a\\u0645\\u0643\\u0646 \\u062a\\u0648\\u0642\\u0639\\u0647 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0641\\u064a \\u0648\\u0642\\u062a \\u0625\\u0628\\u0631\\u0627\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 (\\u0623\\u064a \\u0625\\u0645\\u0627 \\u0623\\u0646\\u0647 \\u0645\\u0646 \\u0627\\u0644\\u0648\\u0627\\u0636\\u062d \\u0623\\u0646\\u0647 \\u0633\\u064a\\u062d\\u062f\\u062b \\u0623\\u0648\\u060c \\u0641\\u064a \\u0648\\u0642\\u062a \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0639\\u0642\\u062f\\u060c \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0648\\u0641 \\u0623\\u0646\\u0647 \\u0642\\u062f \\u064a\\u062d\\u062f\\u062b).\\r\\n\\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u062a\\u062d\\u0645\\u0644 \\u0627\\u0644\\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0629 \\u0639\\u0646 \\u0627\\u0644\\u062e\\u0633\\u0627\\u0631\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0636\\u0631\\u0631 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0646\\u0627\\u062c\\u0645\\u0627 \\u0639\\u0646 \\u0623\\u062d\\u062f\\u0627\\u062b \\u062e\\u0627\\u0631\\u062c\\u0629 \\u0639\\u0646 \\u0633\\u064a\\u0637\\u0631\\u062a\\u0646\\u0627 \\u0627\\u0644\\u0645\\u0639\\u0642\\u0648\\u0644\\u0629.\\r\\n\\u0644\\u0627 \\u064a\\u0648\\u062c\\u062f \\u0641\\u064a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0645\\u0627 \\u064a\\u0624\\u062b\\u0631 \\u0639\\u0644\\u0649 \\u0623\\u064a \\u062d\\u0642\\u0648\\u0642 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0641\\u0642\\u0629 \\u062a\\u0639\\u0627\\u0642\\u062f\\u064a\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u062a\\u063a\\u064a\\u064a\\u0631\\u0647\\u0627 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0646\\u0627\\u0632\\u0644 \\u0639\\u0646\\u0647\\u0627\\u060c \\u0623\\u0648 \\u0627\\u0644\\u062a\\u064a \\u064a\\u062d\\u0642 \\u0644\\u0643 \\u0627\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u064a\\u0647\\u0627 \\u062f\\u0627\\u0626\\u0645\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0646\\u0627\\u062d\\u064a\\u0629 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629\\u060c \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0644\\u0623\\u0646\\u0643 \\u0645\\u0633\\u062a\\u0647\\u0644\\u0643.\\r\\n\\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u0633\\u062a\\u0628\\u0639\\u062f \\u0623\\u0648 \\u0646\\u062d\\u062f \\u0628\\u0623\\u064a \\u0634\\u0643\\u0644 \\u0645\\u0646 \\u0627\\u0644\\u0623\\u0634\\u0643\\u0627\\u0644 \\u0645\\u0646 \\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u062a\\u0646\\u0627 \\u062a\\u062c\\u0627\\u0647\\u0643 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u064a\\u0643\\u0648\\u0646 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 \\u063a\\u064a\\u0631 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a. \\u0633\\u062a\\u062a\\u0645\\u062a\\u0639 \\u062f\\u0627\\u0626\\u0645\\u064b\\u0627 \\u0628\\u0627\\u0644\\u062d\\u0645\\u0627\\u064a\\u0629 \\u0627\\u0644\\u0643\\u0627\\u0645\\u0644\\u0629 \\u0644\\u0644\\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0639\\u0644\\u064a\\u0643.\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0645\\u0633\\u062a\\u0647\\u0644\\u0643\\u064b\\u0627 \\u0645\\u0642\\u064a\\u0645\\u064b\\u0627 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629\\u060c \\u0641\\u0625\\u0646 \\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0627\\u0644\\u0645\\u0633\\u062a\\u0647\\u0644\\u0643 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629 \\u062a\\u0648\\u0641\\u0631 \\u0644\\u0643 \\u0636\\u0645\\u0627\\u0646\\u064b\\u0627 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u064b\\u0627 \\u064a\\u063a\\u0637\\u064a \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646\\u062a \\u0644\\u062f\\u064a\\u0643 \\u0623\\u064a \\u0623\\u0633\\u0626\\u0644\\u0629 \\u062d\\u0648\\u0644 \\u0636\\u0645\\u0627\\u0646\\u0643 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627 \\u0639\\u0644\\u0649 Vlogapp2024@gmail.com.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0633\\u0646\\u0633\\u0639\\u0649 \\u062f\\u0627\\u0626\\u0645\\u064b\\u0627 \\u0625\\u0644\\u0649 \\u062a\\u0632\\u0648\\u064a\\u062f\\u0643 \\u0628\\u062a\\u062c\\u0631\\u0628\\u0629 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0631\\u0627\\u0626\\u0639\\u0629 \\u0648\\u0622\\u0645\\u0646\\u0629\\u060c \\u0644\\u0643\\u0646 \\u0639\\u0644\\u064a\\u0643 \\u0623\\u0646 \\u062a\\u0623\\u062e\\u0630 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0643\\u0645\\u0627 \\u0647\\u064a \\u0648\\u062a\\u0641\\u0647\\u0645 \\u0623\\u0646\\u0646\\u0627 \\u0644\\u0627 \\u0646\\u062a\\u062d\\u0643\\u0645 \\u0641\\u064a \\u0643\\u0644 \\u0645\\u0627 \\u0647\\u0648 \\u0645\\u0648\\u062c\\u0648\\u062f \\u0639\\u0644\\u064a\\u0647\\u0627. \\u0648\\u0644\\u0627 \\u064a\\u0645\\u0643\\u0646\\u0646\\u0627 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0623\\u0646 \\u0646\\u0639\\u062f\\u0643 \\u0628\\u0623\\u0646 \\u0643\\u0644 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0634\\u0648\\u0631 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u064a\\u0631\\u0636\\u064a\\u0643. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0645\\u0646 \\u0645\\u0633\\u062a\\u0647\\u0644\\u0643\\u064a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629\\u060c \\u0641\\u0644\\u062f\\u064a\\u0643 \\u0636\\u0645\\u0627\\u0646 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a \\u064a\\u063a\\u0637\\u064a \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0648\\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0645\\u0637\\u0627\\u0644\\u0628\\u0629 \\u0628\\u0627\\u0644\\u0636\\u0645\\u0627\\u0646 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627\\r\\n\\r\\n. \\u062a\\u0639\\u0644\\u064a\\u0642 \\u0623\\u0648 \\u0625\\u0646\\u0647\\u0627\\u0621 \\u0639\\u0644\\u0627\\u0642\\u062a\\u0646\\u0627\\r\\n6.1 \\u062d\\u0642\\u0648\\u0642\\u0643\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0625\\u0646\\u0647\\u0627\\u0621 \\u0639\\u0644\\u0627\\u0642\\u062a\\u0643 \\u0645\\u0639 Vlog \\u0641\\u064a \\u0623\\u064a \\u0648\\u0642\\u062a \\u0628\\u0645\\u062c\\u0631\\u062f \\u0625\\u063a\\u0644\\u0627\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0625\\u064a\\u0642\\u0627\\u0641 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629. \\u062a\\u0639\\u0644\\u064a\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 \\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u0641\\u064a \\u0645\\u0648\\u0642\\u0639\\u0646\\u0627. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0645\\u0633\\u062a\\u0647\\u0644\\u0643\\u064b\\u0627 \\u0645\\u0642\\u064a\\u0645\\u064b\\u0627 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0627\\u0642\\u062a\\u0635\\u0627\\u062f\\u064a\\u0629 \\u0627\\u0644\\u0623\\u0648\\u0631\\u0648\\u0628\\u064a\\u0629\\u060c \\u0641\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0625\\u063a\\u0644\\u0627\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0627\\u0644\\u0627\\u0646\\u0633\\u062d\\u0627\\u0628 \\u0645\\u0646 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0639\\u0642\\u062f \\u0641\\u064a \\u0623\\u064a \\u0648\\u0642\\u062a\\u060c \\u0625\\u0645\\u0627 \\u062f\\u0627\\u062e\\u0644 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 \\u0623\\u0648 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0646\\u0638\\u0627\\u0645 \\u0646\\u0645\\u0648\\u0630\\u062c \\u0627\\u0644\\u0633\\u062d\\u0628.\\r\\n\\u064a\\u0646\\u0637\\u0628\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0639\\u0628\\u0631 \\u062a\\u0637\\u0628\\u064a\\u0642 Vlog \\u0648\\u0645\\u0648\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628\\u060c \\u0648Vlog Now\\u060c \\u0648\\u0623\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a Vlog \\u0623\\u062e\\u0631\\u0649 \\u0642\\u0645\\u062a \\u0628\\u0631\\u0628\\u0637 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0628\\u0647\\u0627\\u060c \\u0644\\u0630\\u0644\\u0643 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u063a\\u0644\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u062f\\u0627\\u062e\\u0644 \\u062a\\u0637\\u0628\\u064a\\u0642 Vlog \\u0648\\u0627\\u062d\\u062f\\u060c \\u0633\\u062a\\u0641\\u0642\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u062d\\u0633\\u0627\\u0628\\u0643 \\u0639\\u0628\\u0631 \\u062c\\u0645\\u064a\\u0639 Vlog \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0631\\u062a\\u0628\\u0637\\u0629 \\u0628\\u0647\\u0630\\u0627 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628.\\r\\n\\u0648\\u0645\\u0639 \\u0630\\u0644\\u0643\\u060c \\u0627\\u0639\\u062a\\u0645\\u0627\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0644\\u062f\\u064a\\u0643\\u060c \\u0642\\u062f \\u064a\\u0638\\u0644 \\u0628\\u0639\\u0636 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0645\\u062a\\u0627\\u062d\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0628\\u0639\\u062f \\u062d\\u0630\\u0641 \\u062d\\u0633\\u0627\\u0628\\u0643.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0646\\u0631\\u064a\\u062f \\u0645\\u0646\\u0643 \\u0627\\u0644\\u0628\\u0642\\u0627\\u0621\\u060c \\u0648\\u0644\\u0643\\u0646 \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0630\\u0647\\u0627\\u0628 \\u0648\\u0642\\u062a\\u0645\\u0627 \\u062a\\u0634\\u0627\\u0621 \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u0625\\u063a\\u0644\\u0627\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u062a\\u0648\\u0642\\u0641\\u062a \\u0639\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n6.2 \\u062d\\u0642\\u0648\\u0642 \\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648\\r\\n\\u0641\\u064a \\u062d\\u0627\\u0644\\u0629 \\u0648\\u062c\\u0648\\u062f \\u0623\\u064a \\u062e\\u0631\\u0642 \\u0645\\u0634\\u062a\\u0628\\u0647 \\u0628\\u0647 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627\\u060c \\u0641\\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0627\\u0644\\u062a\\u062d\\u0642\\u064a\\u0642 \\u0641\\u064a \\u0627\\u0644\\u0623\\u0645\\u0631. \\u0623\\u062b\\u0646\\u0627\\u0621 \\u0642\\u064a\\u0627\\u0645\\u0646\\u0627 \\u0628\\u0630\\u0644\\u0643\\u060c \\u064a\\u064f\\u0633\\u0645\\u062d \\u0644\\u0646\\u0627 \\u0628\\u0625\\u0632\\u0627\\u0644\\u0629 \\u0628\\u0639\\u0636 \\u0623\\u0648 \\u0643\\u0644 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\u060c \\u0623\\u0648 \\u062a\\u0639\\u0644\\u064a\\u0642 \\u0648\\u0635\\u0648\\u0644\\u0643 \\u0625\\u0644\\u0649 \\u0628\\u0639\\u0636 \\u0623\\u0648 \\u0643\\u0644 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0645\\u0639 \\u0627\\u0644\\u062a\\u0635\\u0631\\u0641 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0648\\u0645\\u0648\\u0636\\u0648\\u0639\\u064a \\u0627\\u0639\\u062a\\u0645\\u0627\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u062e\\u0637\\u0648\\u0631\\u0629 \\u0627\\u0644\\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0627\\u0644\\u0645\\u0634\\u062a\\u0628\\u0647 \\u0628\\u0647.\\r\\n\\u0642\\u062f \\u0646\\u0642\\u0631\\u0631\\u060c \\u0644\\u0627\\u062d\\u0642\\u064b\\u0627\\u060c \\u062a\\u0639\\u0644\\u064a\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0645\\u0624\\u0642\\u062a\\u064b\\u0627 \\u0623\\u0648 \\u0625\\u0646\\u0647\\u0627\\u0626\\u0647 \\u0628\\u0634\\u0643\\u0644 \\u062f\\u0627\\u0626\\u0645\\u060c \\u0623\\u0648 \\u0641\\u0631\\u0636 \\u0642\\u064a\\u0648\\u062f \\u0639\\u0644\\u0649\\u060c \\u0623\\u0648 \\u062a\\u0642\\u064a\\u064a\\u062f \\u0648\\u0635\\u0648\\u0644\\u0643 \\u0625\\u0644\\u0649 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0625\\u0630\\u0627:\\r\\n\\u0646\\u0642\\u0631\\u0631\\u060c \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u062a\\u0635\\u0631\\u0641 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0648\\u0645\\u0648\\u0636\\u0648\\u0639\\u064a\\u060c \\u0623\\u0646\\u0643 \\u0627\\u0631\\u062a\\u0643\\u0628\\u062a \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643\\u064b\\u0627 \\u0645\\u0627\\u062f\\u064a\\u064b\\u0627 \\u0623\\u0648 \\u0645\\u062a\\u0643\\u0631\\u0631\\u064b\\u0627 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627\\u061b\\r\\n\\u0644\\u062f\\u064a\\u0646\\u0627 \\u0623\\u0633\\u0628\\u0627\\u0628 \\u0645\\u0648\\u0636\\u0648\\u0639\\u064a\\u0629 \\u0644\\u0644\\u0627\\u0639\\u062a\\u0642\\u0627\\u062f \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0623\\u0646\\u0643 \\u0639\\u0644\\u0649 \\u0648\\u0634\\u0643 \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0628\\u0634\\u0643\\u0644 \\u062e\\u0637\\u064a\\u0631\\u061b\\r\\n\\u0646\\u062d\\u0646 \\u0645\\u0637\\u0627\\u0644\\u0628\\u0648\\u0646 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064b\\u0627 \\u0628\\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643\\u061b \\u0623\\u0648\\r\\n\\u0641\\u0647\\u0648 \\u0645\\u0637\\u0644\\u0648\\u0628 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0631\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062a\\u0639\\u0627\\u0645\\u0644 \\u0645\\u0639 \\u0645\\u0634\\u0643\\u0644\\u0629 \\u0641\\u0646\\u064a\\u0629 \\u0623\\u0648 \\u0623\\u0645\\u0646\\u064a\\u0629 \\u062e\\u0637\\u064a\\u0631\\u0629.\\r\\n\\u0646\\u0642\\u0648\\u0645 \\u0628\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0639\\u062f\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0627\\u0646\\u062a\\u0647\\u0643 \\u0641\\u064a\\u0647\\u0627 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0634\\u0631\\u0648\\u0637\\u0646\\u0627. \\u0642\\u062f \\u062a\\u0624\\u062f\\u064a \\u0627\\u0644\\u0627\\u0646\\u062a\\u0647\\u0627\\u0643\\u0627\\u062a \\u0627\\u0644\\u0645\\u062a\\u0643\\u0631\\u0631\\u0629 \\u0623\\u0648 \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u062e\\u0637\\u064a\\u0631 \\u0648\\u0627\\u062d\\u062f \\u0625\\u0644\\u0649 \\u062d\\u0638\\u0631 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628 \\u0628\\u0634\\u0643\\u0644 \\u062f\\u0627\\u0626\\u0645.\\r\\n\\u0625\\u0630\\u0627 \\u0642\\u0645\\u0646\\u0627 \\u0628\\u0625\\u0646\\u0647\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0645\\u0633\\u0628\\u0642\\u064b\\u0627 \\u0628\\u0633\\u0628\\u0628 \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639\\u060c \\u0648\\u0644\\u0643\\u0646\\u0643 \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0645\\u0631\\u0629 \\u0623\\u062e\\u0631\\u0649 (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0641\\u062a\\u062d \\u062d\\u0633\\u0627\\u0628 \\u0622\\u062e\\u0631)\\u060c \\u0641\\u064a\\u062d\\u0642 \\u0644\\u0646\\u0627 \\u062a\\u0639\\u0644\\u064a\\u0642 \\u0623\\u0648 \\u0625\\u0646\\u0647\\u0627\\u0621 \\u0623\\u064a \\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628\\u0627\\u062a.\\r\\n\\u0633\\u0646\\u0642\\u0648\\u0645 \\u0628\\u0625\\u062e\\u0637\\u0627\\u0631\\u0643 \\u0645\\u0633\\u0628\\u0642\\u064b\\u0627 \\u0644\\u0625\\u062a\\u0627\\u062d\\u0629 \\u0627\\u0644\\u0648\\u0642\\u062a \\u0644\\u0643 \\u0644\\u062a\\u0646\\u0632\\u064a\\u0644 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\\u0643 \\u062f\\u0627\\u062e\\u0644 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 (\\u0645\\u0632\\u064a\\u062f \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 \\u0645\\u062a\\u0627\\u062d Vlogapp2024@gmail.com\\u060c \\u0645\\u0627 \\u0644\\u0645 \\u064a\\u0643\\u0646 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0627\\u0633\\u0628 \\u0644\\u0646\\u0627 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 \\u0623\\u0648 \\u0646\\u0639\\u062a\\u0642\\u062f \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0623\\u0646 \\u0633\\u064a\\u0624\\u062f\\u064a \\u0627\\u0633\\u062a\\u0645\\u0631\\u0627\\u0631 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0625\\u0644\\u0649 \\u0625\\u0644\\u062d\\u0627\\u0642 \\u0627\\u0644\\u0636\\u0631\\u0631 \\u0628\\u0646\\u0627 \\u0623\\u0648 \\u0628\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0646\\u0627 \\u0623\\u0648 \\u0628\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627 \\u0623\\u0648 \\u0628\\u0623\\u0637\\u0631\\u0627\\u0641 \\u062b\\u0627\\u0644\\u062b\\u0629 \\u0623\\u062e\\u0631\\u0649\\u060c \\u0623\\u0648 \\u064a\\u064f\\u0645\\u0646\\u0639\\u0646\\u0627 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643.\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0639\\u062a\\u0642\\u062f \\u0623\\u0646\\u0646\\u0627 \\u0627\\u0631\\u062a\\u0643\\u0628\\u0646\\u0627 \\u062e\\u0637\\u0623\\u064b \\u0641\\u064a \\u062a\\u0639\\u0644\\u064a\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0623\\u0648 \\u0625\\u0646\\u0647\\u0627\\u0626\\u0647\\u060c \\u0641\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0627\\u0644\\u0645\\u062a\\u0648\\u0641\\u0631\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u0633\\u0646\\u0631\\u0627\\u062c\\u0639 \\u0642\\u0631\\u0627\\u0631\\u0646\\u0627 \\u0648\\u0646\\u062a\\u062e\\u0630 \\u0627\\u0644\\u0642\\u0631\\u0627\\u0631 \\u0645\\u0631\\u0629 \\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0644\\u062a\\u062c\\u0646\\u0628 \\u0627\\u0644\\u0634\\u0643\\u060c \\u0625\\u0630\\u0627 \\u0642\\u0645\\u0646\\u0627 \\u0628\\u062a\\u0639\\u0644\\u064a\\u0642 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0623\\u0648 \\u0625\\u0646\\u0647\\u0627\\u0626\\u0647\\u060c \\u0623\\u0648 \\u0642\\u0645\\u062a \\u0628\\u062d\\u0630\\u0641 \\u062d\\u0633\\u0627\\u0628\\u0643\\u060c \\u0641\\u0633\\u0648\\u0641 \\u062a\\u0641\\u0642\\u062f \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062c\\u0645\\u064a\\u0639 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 Vlog \\u0648Vlog Now.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0642\\u0648\\u0627\\u0639\\u062f\\u060c \\u0648\\u0625\\u0630\\u0627 \\u062e\\u0631\\u0642\\u062a\\u0647\\u0627\\u060c \\u064a\\u0645\\u0643\\u0646 \\u0623\\u0646 \\u062a\\u062a\\u062e\\u0630 Vlog \\u0625\\u062c\\u0631\\u0627\\u0621\\u0627\\u062a \\u0636\\u062f\\u0643 \\u0642\\u062f \\u062a\\u0634\\u0645\\u0644 \\u0625\\u0646\\u0647\\u0627\\u0621 \\u062d\\u0633\\u0627\\u0628\\u0643\\r\\n\\r\\n. \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\r\\n7.1 \\u0645\\u0627\\u0630\\u0627 \\u064a\\u062d\\u062f\\u062b \\u0639\\u0646\\u062f\\u0645\\u0627 \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0625\\u062c\\u0631\\u0627\\u0621 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a\\r\\n\\u064a\\u062c\\u0648\\u0632 \\u0644\\u0646\\u0627 \\u0625\\u062c\\u0631\\u0627\\u0621 \\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0645\\u0646 \\u0648\\u0642\\u062a \\u0644\\u0622\\u062e\\u0631. \\u0625\\u0630\\u0627 \\u0641\\u0639\\u0644\\u0646\\u0627 \\u0630\\u0644\\u0643\\u060c \\u0641\\u0633\\u0648\\u0641 \\u0646\\u0623\\u062e\\u0630 \\u0641\\u064a \\u0627\\u0644\\u0627\\u0639\\u062a\\u0628\\u0627\\u0631 \\u0627\\u0647\\u062a\\u0645\\u0627\\u0645\\u0627\\u062a\\u0643 \\u0627\\u0644\\u0645\\u0639\\u0642\\u0648\\u0644\\u0629 \\u0642\\u0628\\u0644 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643.\\r\\n\\u0648\\u0633\\u0646\\u0642\\u062f\\u0645 \\u0644\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0625\\u0634\\u0639\\u0627\\u0631\\u064b\\u0627 \\u0645\\u0633\\u0628\\u0642\\u064b\\u0627 \\u0645\\u0639\\u0642\\u0648\\u0644\\u0627\\u064b\\u060c \\u0648\\u0628\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0634\\u0641\\u0627\\u0641\\u0629\\u060c \\u0628\\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629 \\u0627\\u0644\\u062a\\u064a \\u0633\\u062a\\u0624\\u062b\\u0631 \\u0639\\u0644\\u064a\\u0643 \\u0648\\u0639\\u0644\\u0649 \\u062a\\u0627\\u0631\\u064a\\u062e \\u062f\\u062e\\u0648\\u0644\\u0647\\u0627 \\u062d\\u064a\\u0632 \\u0627\\u0644\\u062a\\u0646\\u0641\\u064a\\u0630. \\u0644\\u0646 \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0625\\u0644\\u0627 \\u0639\\u0644\\u0649 \\u0639\\u0644\\u0627\\u0642\\u062a\\u0646\\u0627 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0642\\u0628\\u0644.\\r\\n\\u0639\\u0646\\u062f\\u0645\\u0627 \\u0646\\u062d\\u062a\\u0627\\u062c \\u0625\\u0644\\u0649 \\u0625\\u062c\\u0631\\u0627\\u0621 \\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0639\\u0627\\u062c\\u0644\\u0629 \\u062a\\u062a\\u0639\\u0644\\u0642 \\u0628\\u0627\\u0644\\u0623\\u0645\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u0633\\u0644\\u0627\\u0645\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u062a\\u0637\\u0644\\u0628\\u0627\\u062a \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0646\\u0638\\u064a\\u0645\\u064a\\u0629\\u060c \\u0642\\u062f \\u0644\\u0627 \\u0646\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u062a\\u0632\\u0648\\u064a\\u062f\\u0643 \\u0628\\u0625\\u0634\\u0639\\u0627\\u0631 \\u0645\\u0633\\u0628\\u0642\\u060c \\u0648\\u0644\\u0643\\u0646\\u0646\\u0627 \\u0633\\u0646\\u062e\\u0628\\u0631\\u0643 \\u0628\\u0645\\u062c\\u0631\\u062f \\u0623\\u0646 \\u0646\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0630\\u0644\\u0643.\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0644\\u0627 \\u062a\\u0648\\u0627\\u0641\\u0642 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0645 \\u0625\\u062c\\u0631\\u0627\\u0624\\u0647\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0641\\u0633\\u064a\\u062a\\u0639\\u064a\\u0646 \\u0639\\u0644\\u064a\\u0643 \\u0627\\u0644\\u062a\\u0648\\u0642\\u0641 \\u0639\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0625\\u0630\\u0627 \\u062a\\u063a\\u064a\\u0631\\u062a \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0633\\u0646\\u062e\\u0628\\u0631\\u0643 \\u0628\\u0630\\u0644\\u0643. \\u0644\\u0646 \\u064a\\u063a\\u064a\\u0631 \\u0623\\u064a \\u0634\\u064a\\u0621 \\u0628\\u064a\\u0646\\u0646\\u0627 \\u0642\\u062f \\u062d\\u062f\\u062b \\u0628\\u0627\\u0644\\u0641\\u0639\\u0644\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0631\\u063a\\u0628 \\u0641\\u064a \\u0627\\u0644\\u0627\\u0633\\u062a\\u0645\\u0631\\u0627\\u0631 \\u0641\\u064a \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0642\\u0628\\u0644\\u060c \\u0641\\u0633\\u0648\\u0641 \\u062a\\u062d\\u062a\\u0627\\u062c \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0641\\u0642\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a. \\u0633\\u0648\\u0641 \\u062a\\u062a\\u0637\\u0648\\u0631 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0645\\u0639 \\u062a\\u062d\\u0633\\u064a\\u0646\\u0647\\u0627.\\r\\n7.2 \\u0623\\u0633\\u0628\\u0627\\u0628 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a\\r\\n\\u0627\\u0644\\u0623\\u0633\\u0628\\u0627\\u0628 \\u0627\\u0644\\u062a\\u064a \\u0642\\u062f \\u062a\\u062c\\u0639\\u0644\\u0646\\u0627 \\u0646\\u062c\\u0631\\u064a \\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0647\\u064a:\\r\\n\\u0627\\u0644\\u062a\\u063a\\u064a\\u0631\\u0627\\u062a \\u0641\\u064a \\u0627\\u0644\\u0638\\u0631\\u0648\\u0641 \\u0627\\u0644\\u062e\\u0627\\u0631\\u062c\\u0629 \\u0639\\u0646 \\u0633\\u064a\\u0637\\u0631\\u062a\\u0646\\u0627 \\u0627\\u0644\\u0645\\u0639\\u0642\\u0648\\u0644\\u0629\\u061b\\r\\n\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0641\\u064a \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u061b\\r\\n\\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0631\\u064a\\u0647\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0633\\u0627\\u0631 \\u0627\\u0644\\u0645\\u0639\\u062a\\u0627\\u062f \\u0644\\u062a\\u0637\\u0648\\u064a\\u0631 \\u0645\\u0646\\u062a\\u062c\\u0646\\u0627\\u061b\\r\\n\\u0648\\u0627\\u0644\\u062a\\u0643\\u064a\\u0641 \\u0645\\u0639 \\u0627\\u0644\\u062a\\u0643\\u0646\\u0648\\u0644\\u0648\\u062c\\u064a\\u0627\\u062a \\u0627\\u0644\\u062c\\u062f\\u064a\\u062f\\u0629\\u061b\\r\\n\\u0644\\u062a\\u0639\\u0643\\u0633 \\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0641\\u064a \\u0639\\u062f\\u062f \\u0627\\u0644\\u0623\\u0634\\u062e\\u0627\\u0635 \\u0627\\u0644\\u0630\\u064a\\u0646 \\u064a\\u0633\\u062a\\u062e\\u062f\\u0645\\u0648\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0623\\u0648 \\u0623\\u064a \\u0645\\u064a\\u0632\\u0629 \\u0623\\u0648 \\u0648\\u0638\\u064a\\u0641\\u0629 \\u0630\\u0627\\u062a \\u0635\\u0644\\u0629 \\u0628\\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u061b \\u0623\\u0648\\r\\n\\u0644\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0645\\u0634\\u0643\\u0644\\u0629 \\u0623\\u0645\\u0646\\u064a\\u0629.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0644\\u0646 \\u062a\\u0638\\u0644 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0643\\u0645\\u0627 \\u0647\\u064a \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0623\\u0628\\u062f\\u060c \\u0648\\u0644\\u0643\\u0646\\u0646\\u0627 \\u0633\\u0646\\u0644\\u062a\\u0632\\u0645 \\u0628\\u0627\\u0644\\u0634\\u0641\\u0627\\u0641\\u064a\\u0629 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u0646\\u062c\\u0631\\u064a \\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0643\\u0628\\u064a\\u0631\\u0629.\\r\\n8. \\u062d\\u0644 \\u0627\\u0644\\u0646\\u0632\\u0627\\u0639\\u0627\\u062a\\r\\n\\u062a\\u062e\\u0636\\u0639 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0644\\u0642\\u0627\\u0646\\u0648\\u0646 \\u0627\\u0644\\u0648\\u0644\\u0627\\u064a\\u0629 \\u0627\\u0644\\u0642\\u0636\\u0627\\u0626\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0642\\u064a\\u0645 \\u0641\\u064a\\u0647\\u0627.\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0646\\u0632\\u0627\\u0639\\u060c \\u0641\\u0633\\u0646\\u062d\\u0627\\u0648\\u0644 \\u0623\\u0648\\u0644\\u0627\\u064b \\u062d\\u0644\\u0647 \\u0648\\u062f\\u064a\\u064b\\u0627 \\u0645\\u0639\\u0643.\\r\\n\\u0625\\u0630\\u0627 \\u0644\\u0645 \\u0646\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u062d\\u0644 \\u0646\\u0632\\u0627\\u0639\\u0646\\u0627\\u060c \\u0641\\u064a\\u0645\\u0643\\u0646\\u0646\\u0627 \\u0623\\u0646\\u062a \\u0623\\u0648 \\u064a\\u0645\\u0643\\u0646\\u0646\\u0627 \\u0627\\u0644\\u0630\\u0647\\u0627\\u0628 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u0627\\u0643\\u0645 \\u0627\\u0644\\u0645\\u062d\\u0644\\u064a\\u0629 \\u0644\\u062f\\u064a\\u0643. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u0644\\u0630\\u0647\\u0627\\u0628 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u0627\\u0643\\u0645 \\u0627\\u0644\\u062a\\u0627\\u0644\\u064a\\u0629:\\r\\n\\u0633\\u064a\\u0643\\u0648\\u0646 \\u0644\\u0645\\u062d\\u0627\\u0643\\u0645 \\u062c\\u0645\\u0647\\u0648\\u0631\\u064a\\u0629 \\u0623\\u064a\\u0631\\u0644\\u0646\\u062f\\u0627 \\u0648\\u0644\\u0627\\u064a\\u0629 \\u0642\\u0636\\u0627\\u0626\\u064a\\u0629 \\u063a\\u064a\\u0631 \\u062d\\u0635\\u0631\\u064a\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0632\\u0627\\u0639\\u0627\\u062a \\u0645\\u0639 Vlog Technology Limited\\u061b \\u0648\\r\\n\\u0633\\u064a\\u0643\\u0648\\u0646 \\u0644\\u0645\\u062d\\u0627\\u0643\\u0645 \\u0625\\u0646\\u062c\\u0644\\u062a\\u0631\\u0627 \\u0648\\u0648\\u064a\\u0644\\u0632 \\u0633\\u0644\\u0637\\u0629 \\u0642\\u0636\\u0627\\u0626\\u064a\\u0629 \\u063a\\u064a\\u0631 \\u062d\\u0635\\u0631\\u064a\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0632\\u0627\\u0639\\u0627\\u062a \\u0645\\u0639 Vlog Information Technologies UK Limited.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0646\\u0623\\u0645\\u0644 \\u0623\\u0644\\u0627 \\u0646\\u062f\\u062e\\u0644 \\u0641\\u064a \\u0646\\u0632\\u0627\\u0639\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0625\\u0630\\u0627 \\u062d\\u062f\\u062b \\u0630\\u0644\\u0643\\u060c \\u0641\\u0647\\u0646\\u0627\\u0643 \\u0637\\u0631\\u064a\\u0642\\u062a\\u0627\\u0646 \\u064a\\u0645\\u0643\\u0646\\u0646\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644\\u0647\\u0645\\u0627 \\u0645\\u062d\\u0627\\u0648\\u0644\\u0629 \\u062d\\u0644 \\u0627\\u0644\\u0646\\u0632\\u0627\\u0639\\r\\n\\r\\n. \\u0622\\u062e\\u0631\\r\\n\\u0644\\u0627 \\u064a\\u062c\\u0648\\u0632 \\u0644\\u0643 \\u0646\\u0642\\u0644 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0646\\u0627\\u0632\\u0644 \\u0639\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0623\\u064a \\u062d\\u0642\\u0648\\u0642 \\u0648\\u0623\\u0630\\u0648\\u0646\\u0627\\u062a \\u0645\\u0645\\u0646\\u0648\\u062d\\u0629 \\u0641\\u064a\\u0647\\u0627\\u060c \\u0648\\u0644\\u0643\\u0646 \\u064a\\u062c\\u0648\\u0632 \\u0623\\u0646 \\u064a\\u062a\\u0645 \\u0627\\u0644\\u062a\\u0646\\u0627\\u0632\\u0644 \\u0639\\u0646\\u0647\\u0627 \\u0628\\u0648\\u0627\\u0633\\u0637\\u0629 Vlog \\u062f\\u0648\\u0646 \\u0642\\u064a\\u0648\\u062f. \\u0625\\u0630\\u0627 \\u0642\\u0645\\u0646\\u0627 \\u0628\\u0630\\u0644\\u0643\\u060c \\u0641\\u0644\\u0646 \\u064a\\u0624\\u062b\\u0631 \\u0630\\u0644\\u0643 \\u0639\\u0644\\u0649 \\u0623\\u064a \\u062d\\u0642\\u0648\\u0642 \\u0642\\u062f \\u062a\\u0643\\u0648\\u0646 \\u0644\\u062f\\u064a\\u0643 \\u0643\\u0645\\u0633\\u062a\\u0647\\u0644\\u0643. \\u0648\\u0625\\u0630\\u0627 \\u0644\\u0645 \\u062a\\u0643\\u0646 \\u0633\\u0639\\u064a\\u062f\\u064b\\u0627\\u060c \\u0641\\u0644\\u062f\\u064a\\u0643 \\u062f\\u0627\\u0626\\u0645\\u064b\\u0627 \\u0627\\u0644\\u062d\\u0642 \\u0641\\u064a \\u0625\\u0646\\u0647\\u0627\\u0621 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0639\\u0642\\u062f \\u0648\\u0627\\u0644\\u062a\\u0648\\u0642\\u0641 \\u0639\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0641\\u064a \\u0623\\u064a \\u0648\\u0642\\u062a.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0644\\u064a\\u0633 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0623\\u064a \\u062e\\u0637\\u0637 \\u0644\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0625\\u0630\\u0627 \\u0642\\u0645\\u0646\\u0627 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0642\\u0628\\u0644 \\u0628\\u0628\\u064a\\u0639 \\u0623\\u0639\\u0645\\u0627\\u0644\\u0646\\u0627 \\u0643\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u062c\\u0632\\u0621 \\u0645\\u0646\\u0647\\u0627 \\u0623\\u0648 \\u0625\\u0639\\u0627\\u062f\\u0629 \\u062a\\u0646\\u0638\\u064a\\u0645\\u0647\\u0627\\u060c \\u0641\\u0642\\u062f \\u062a\\u0642\\u0648\\u0645 \\u0634\\u0631\\u0643\\u0629 \\u0623\\u062e\\u0631\\u0649 \\u0641\\u064a \\u0646\\u0647\\u0627\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0637\\u0627\\u0641 \\u0628\\u062a\\u0648\\u0641\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0644\\u0643.\\r\\n\\u064a\\u062c\\u0648\\u0632 \\u0644\\u0646\\u0627 \\u0627\\u0633\\u062a\\u0639\\u0627\\u062f\\u0629 \\u0627\\u0633\\u0645 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0642\\u062f \\u0646\\u062c\\u0639\\u0644\\u0647 \\u0645\\u062a\\u0627\\u062d\\u064b\\u0627 \\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0622\\u062e\\u0631\\u064a\\u0646 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u0644\\u0627 \\u062a\\u0642\\u0648\\u0645 \\u0628\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0627\\u0644\\u062f\\u062e\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0644\\u0645\\u062f\\u0629 6 \\u0623\\u0634\\u0647\\u0631 \\u0623\\u0648 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u0627 \\u0646\\u0639\\u062a\\u0642\\u062f \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0623\\u0646 \\u0627\\u0633\\u0645 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\/\\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0627\\u0633\\u0645 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u064a\\u0646\\u062a\\u0647\\u0643 \\u0627\\u0644\\u0637\\u0631\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b). \\u0639\\u0644\\u0627\\u0645\\u0629 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629).\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0642\\u062f \\u0646\\u0633\\u062a\\u0639\\u064a\\u062f \\u0627\\u0633\\u0645 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0641\\u064a \\u0638\\u0631\\u0648\\u0641 \\u0645\\u0639\\u064a\\u0646\\u0629.\\r\\n\\u062d\\u062a\\u0649 \\u0625\\u0630\\u0627 \\u062a\\u0623\\u062e\\u0631\\u0646\\u0627 \\u0646\\u062d\\u0646 \\u0623\\u0648 \\u0623\\u0646\\u062a \\u0641\\u064a \\u062a\\u0646\\u0641\\u064a\\u0630 \\u0623\\u062d\\u062f \\u0623\\u062d\\u0643\\u0627\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0644\\u0627 \\u064a\\u0632\\u0627\\u0644 \\u0628\\u0625\\u0645\\u0643\\u0627\\u0646 \\u0623\\u064a \\u0645\\u0646\\u0627 \\u062a\\u0646\\u0641\\u064a\\u0630\\u0647 \\u0644\\u0627\\u062d\\u0642\\u064b\\u0627. \\u0625\\u0630\\u0627 \\u0644\\u0645 \\u0646\\u0635\\u0631 \\u0646\\u062d\\u0646 \\u0623\\u0648 \\u0623\\u0646\\u062a \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0641\\u0648\\u0631 \\u0639\\u0644\\u0649 \\u0642\\u064a\\u0627\\u0645\\u0643 \\u0623\\u0646\\u062a \\u0623\\u0648 \\u0646\\u062d\\u0646 \\u0628\\u0623\\u064a \\u0634\\u064a\\u0621 \\u064a\\u064f\\u0637\\u0644\\u0628 \\u0645\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0647 \\u0628\\u0645\\u0648\\u062c\\u0628 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0623\\u0648 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0647\\u0646\\u0627\\u0643 \\u062a\\u0623\\u062e\\u064a\\u0631 \\u0641\\u064a \\u0627\\u062a\\u062e\\u0627\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0636\\u062f \\u0627\\u0644\\u0622\\u062e\\u0631 \\u0641\\u064a\\u0645\\u0627 \\u064a\\u062a\\u0639\\u0644\\u0642 \\u0628\\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0647\\u0630\\u0627 \\u0644\\u0627 \\u064a\\u0639\\u0646\\u064a \\u0623\\u0646\\u0646\\u0627 \\u0623\\u0648 \\u0644\\u064a\\u0633 \\u0639\\u0644\\u064a\\u0643 \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0623\\u0634\\u064a\\u0627\\u0621 \\u0648\\u0644\\u0646 \\u064a\\u0645\\u0646\\u0639\\u0646\\u0627 \\u0623\\u0648 \\u064a\\u0645\\u0646\\u0639\\u0643 \\u0645\\u0646 \\u0627\\u062a\\u062e\\u0627\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0636\\u062f \\u0627\\u0644\\u0622\\u062e\\u0631 \\u0641\\u064a \\u0648\\u0642\\u062a \\u0644\\u0627\\u062d\\u0642.\\r\\n\\u0628\\u0627\\u062e\\u062a\\u0635\\u0627\\u0631: \\u0644\\u0645\\u062c\\u0631\\u062f \\u0623\\u0646\\u0643 \\u0623\\u0648 \\u0646\\u062d\\u0646 \\u0644\\u0627 \\u0646\\u0639\\u062a\\u0645\\u062f \\u0639\\u0644\\u0649 \\u0623\\u062d\\u062f \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0641\\u0625\\u0646 \\u0647\\u0630\\u0627 \\u0644\\u0627 \\u064a\\u063a\\u064a\\u0631 \\u062d\\u0642\\u064a\\u0642\\u0629 \\u0623\\u0646\\u0646\\u0627 \\u0645\\u062a\\u0641\\u0642\\u0627\\u0646 \\u0639\\u0644\\u0649 \\u0623\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637\\u060c \\u0643\\u0645\\u0627 \\u0647\\u064a \\u0645\\u0643\\u062a\\u0648\\u0628\\u0629\\u060c \\u0647\\u064a \\u0627\\u0644\\u0627\\u062a\\u0641\\u0627\\u0642\\u064a\\u0629 \\u0628\\u064a\\u0646\\u0646\\u0627.\\r\\n10. \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627 \\u0647\\u0646\\u0627: Vlogapp2024@gmail.com\"}', 'pages/syFKWo1N8DT8FFRopHJGTTzWFswM7WuAkiksUohv.jpg', NULL, '2024-07-18 15:54:19');
INSERT INTO `pages` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(3, '{\"en\":\"privacy police\",\"ar\":\"\\u0627\\u0644\\u0634\\u0631\\u0637\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629\"}', '{\"en\":\"Privacy Policy\\r\\n\\r\\nWelcome to Vlog. This Privacy Policy applies to Vlog services (the \\u201cPlatform\\u201d), which include Vlog apps, websites, software and related services accessed via any platform or device that link to this Privacy Policy. The Platform is provided and controlled by Vlog Pte. Ltd., with its registered address at 1 Raffles Quay, #26-10, South Tower, Singapore 048583 (\\u201cVlog\\u201d, \\u201cwe\\u201d or \\u201cus\\u201d).\\r\\n\\r\\nWe are committed to protecting and respecting your privacy. This Privacy Policy explains how we collect, use, share, and otherwise process the personal information of users, and other individuals in connection with our Platform. If you do not agree with this policy, you should not use the Platform.\\r\\nWhat information we collect\\r\\nWe may collect the following information about you:\\r\\nInformation You Provide\\r\\nYour profile information. You give us information when you register on the Platform, including your username, password, date of birth (where applicable), email address and\\/or telephone number, information you disclose in your user profile, and your photograph or profile video.\\r\\nUser content. We process the content you generate on the Platform, including photographs, audios and videos you upload or create, comments, hashtags, feedback, reviews, and livestreams you make, and the associated metadata, such as when, where, and by whom the content was created (\\u201cUser Content\\u201d). Even if you are not a user, information about you may appear in User Content created or published by users on the Platform. We collect User Content through pre-loading at the time of creation, import, or upload, regardless of whether you choose to save or upload that User Content, in order to recommend audio options and provide other personalized recommendations. If you apply an effect to your User Content, we may collect a version of your User Content that does not include the effect.\\r\\nMessages. We collect information you provide when you compose, send, or receive messages through the Platform\\u2019s messaging functionalities. They include messages you send or receive through our chat functionality when communicating with merchants who sell goods to you, and your use of virtual assistants when purchasing items through the Platform. That information includes the content of the message and information about the message, such as when it was sent, received, or read, and message participants. Please be aware that messages you choose to send to other users of the Platform will be accessible by those users and that we are not responsible for the manner in which those users use or share the messages.\\r\\nWe may access content, including text, images, and video, found in your device\\u2019s clipboard, with your permission. For example, if you choose to initiate content sharing with a third-party platform, or choose to paste content from the clipboard into the Platform, we access this information stored in your clipboard in order to fulfill your request.\\r\\nPurchase information. When you make a purchase or payment on or through the Platform, including when you buy Vlog Coins or purchase goods through our shopping features, we collect information about the purchase or payment transaction, such as payment card information, billing, delivery, and contact information, and items you purchased.\\r\\nYour phone and social network contacts. If you choose to sync your phone contacts, we will access and collect information such as names, phone numbers, and email addresses, and match that information against existing users of the Platform. If you choose to share your social network contacts, we will collect your public profile information as well as names and profiles of your social network contacts.\\r\\nProof of your identity or age. We sometimes ask you to provide proof of identity or age in order to use certain features, such as livestream or verified accounts, or when you apply for a Business Account, ensure that you are old enough to use the Platform, or in other instances where verification may be required.\\r\\nInformation in correspondence you send to us, including when you contact us for support or feedback.\\r\\nInformation through surveys, research, promotion, contests, marketing campaigns, challenges, competitions or events conducted or sponsored by us, in which you participate.\\r\\n\\r\\n\\r\\nAutomatically Collected Information\\r\\nUsage Information. We collect information regarding your use of the Platform, e.g., how you engage with the Platform, including how you interact with content we show to you, the advertisements you view, videos you watch and problems encountered, browsing and search history, the content you like, the content you save to \\u2018My Favourites\\u2019, the users you follow and how you engage with mutual followers.\\r\\nInferred Information. We also infer your attributes, including your interests, gender and age range for the purpose of personalising content.\\r\\nTechnical Information we collect about you. We collect certain information about the device you use to access the Platform, such as your IP address, user agent, mobile carrier, time zone settings, identifiers for advertising purposes, model of your device, the device system, network type, device IDs, your screen resolution and operating system, app and file names and types, keystroke patterns or rhythms, battery state, audio settings and connected audio devices. Where you log-in from multiple devices, we will be able to use your profile information to identify your activity across devices. We may also associate you with information collected from devices other than those you use to log-in to the Platform.\\r\\nLocation Information. We collect information about your approximate location, including location information based on your SIM card and\\/or IP address. With your permission, we may also collect precise location data (such as GPS). In addition, we collect location information (such as tourist attractions, shops, or other points of interest) if you choose to add location information to your User Content.\\r\\nImage and Audio Information. We may collect information about the videos, images and audio that are a part of your User Content, such as identifying the objects and scenery that appear, the existence and location within an image of face and body features and attributes, the nature of the audio, and the text of the words spoken in your User Content. We may collect this information to enable special video effects, for content moderation, for demographic classification, for content and ad recommendations, and for other non-personally-identifying operations.\\r\\n\\r\\n\\r\\nInformation From Other Sources\\r\\nWe may receive the information described in this Privacy Policy from other sources, such as:\\r\\nIf you choose to register or use the Platform using a third-party social network account details (e.g., Google) or login service, you will provide us or allow to provide us with your username, public profile, and other possible information related to such account. We will likewise share certain information with your social network such as your app ID, access token and the referring URL. If you link your Vlog account to another service, we may receive information about your use of that service.\\r\\nAdvertisers, measurement and other partners share information with us about you and the actions you have taken outside of the Platform, such as your activities on other websites and apps or in stores, including the products or services you purchased, online or in person. These partners also share information with us, such as mobile identifiers for advertising, hashed email addresses and phone numbers, and cookie identifiers, which we use to help match you and your actions outside of the Platform with your Vlog account. \\r\\nWe may receive information about you from others, including where you are included or mentioned in User Content, direct messages, in a complaint, appeal, request or feedback submitted to us, or if your contact information is provided to us. We may collect information about you from other publicly available sources.\\r\\nWe may receive information from merchants and payment and transaction fulfillment providers about you, such as payment confirmation details, and information about the delivery of products you have purchased through our shopping features.\\r\\nHow we use your information\\r\\nAs explained below, we use your information to improve, support and administer the Platform, to allow you to use its functionalities, and to fulfill and enforce our Terms of Service. We may also use your information to, among other things, personalise content you see on the Platform, promote the Platform, and customize your ad experience. We generally use the information we collect in the following ways:\\r\\nTo fulfill requests for products, services, Platform functionality, support and information for internal operations, including troubleshooting, data analysis, testing, research, statistical, and survey purposes and to solicit your feedback.\\r\\nTo provide our shopping features and facilitate the purchase and delivery of products, goods and services, including sharing your information with merchants, payment and transaction fulfillment providers, and other service providers in order to process your orders.\\r\\nTo personalise the content you see when you use the Platform. For example, we may provide you with services based on the country settings you have chosen or show you content that is similar to content that you have liked or interacted with.\\r\\nTo send promotional materials, including by instant messaging or email, from us or on behalf of our affiliates and trusted third parties.\\r\\nTo improve and develop our Platform and conduct product development.\\r\\nTo measure and understand the effectiveness of the advertisements and other content we serve to you and others, and to deliver advertising, including targeted advertising, to you on the Platform.\\r\\nTo support the social functions of the Platform, including to permit you and others to connect with each other (for example, through our Find Friends function) and to share whether you are active on the Platform (and other information which you choose to share) with your friends, to provide our messaging service if you choose to use this function, to suggest accounts to you and others, and for you and others to share, download, and otherwise interact with User Content posted through the Platform.\\r\\nTo enable you to participate in the virtual items program.\\r\\nTo allow you to participate in interactive features of the Platform, such as enabling your content to be used in other users\\u2019 videos.\\r\\nTo use User Content as part of our advertising and marketing campaigns to promote the Platform, to invite you to participate in an event, and to promote popular topics, hashtags and campaigns on the Platform.\\r\\nTo understand how you use the Platform, including across your devices.\\r\\nTo infer additional information about you, such as your age range, gender, and interests.\\r\\nTo help us detect and combat abuse, harmful activity, fraud, spam, and illegal activity on the Platform.\\r\\nTo ensure content is presented in the most effective manner for you and your device.\\r\\nTo promote the safety, security of the Platform, including by scanning, analyzing, and reviewing User Content, messages and associated metadata for violations of our Terms of Service, Community Guidelines, or other conditions and policies.\\r\\nTo facilitate research conducted by independent researchers that meets certain criteria.\\r\\nTo verify your identity or age.\\r\\nTo communicate with you, including to notify you about changes in our services.\\r\\nTo announce you as a winner of our contests or promotions if permitted by the promotion rule, and to send you any applicable prizes.\\r\\nTo enforce our Terms of Service, and other conditions and policies.\\r\\nConsistent with your permissions, to provide you with location-based services, such as advertising and other personalized content.\\r\\nTo train and improve our technology, such as our machine learning models and algorithms.\\r\\nTo facilitate and fulfill sales, promotion, and purchases of goods and services and to provide user support.\\r\\nHow we share your information\\r\\nWe share your information with the following parties:\\r\\nBusiness Partners\\r\\nIf you choose to register to use the Platform using your social network account details (e.g., Google), you will provide us or allow your social network to provide us with your phone number, email address, username and public profile. We will likewise share certain information with the relevant social network such as your app ID, access token and the referring URL. If you choose to allow a third-party service to access your account, we will share certain information about you with the third party. Depending on the permissions you grant, the third party may be able to obtain your account information and other information you choose to provide.\\r\\nWhere you opt to share content on social media platforms, the video, username and accompanying text will be shared on that platform or, in the case of sharing via instant messaging platforms such as Whatsapp, a link to the content will be shared.\\r\\nService Providers\\r\\nWe provide information and content to service providers who support our business, such as cloud service providers and providers of content moderation services to ensure that the Platform is a safe and enjoyable place and service providers that assist us in marketing the Platform.\\r\\nPayment processors and transaction fulfillment providers: If you choose to buy Coins or conduct other payment related transactions, we will share data with the relevant payment provider to facilitate this transaction. For Coin transactions, we share a transaction ID to enable us to identify you and credit your account with the correct value in coins once you have made the payment.\\r\\nAnalytics providers: We use analytics providers to help us in the optimisation and improvement of the Platform. Our third-party analytics providers also help us serve targeted advertisements.\\r\\nIndependent Researchers\\r\\nWe share your information with independent researchers to facilitate research that meets certain criteria.\\r\\nOur Corporate Group\\r\\nWe may also share your information with other members, subsidiaries, or affiliates of our corporate group, including to provide the Platform, to improve and optimise the Platform, to prevent illegal use and to support users.\\r\\nFor Legal Reasons\\r\\nWe will share your information with law enforcement agencies, public authorities or other organisations if legally required to do so, or if such use is reasonably necessary to:\\r\\ncomply with legal obligation, process or request;\\r\\nenforce our Terms of Service and other agreements, policies, and standards, including investigation of any potential violation thereof;\\r\\ndetect, prevent or otherwise address security, fraud or technical issues; or\\r\\nprotect the rights, property or safety of us, our users, a third party or the public as required or permitted by law (including exchanging information with other companies and organisations for the purposes of fraud protection and credit risk reduction).\\r\\nPublic Profiles\\r\\nPlease note that if your profile is public, your content will be visible to anyone on the Platform and may also be accessed or shared by your friends and followers as well as third parties such as search engines, content aggregators and news sites. You can change who can see a video each time you upload a video. Alternatively, you can change your profile to default private by changing your settings to \'Private Account\' in \\u201cManage my account\\u201d settings.\\r\\nMerchants, Payment and Transaction Fulfillment Providers, and Other Service Providers\\r\\nWhen you make a purchase through our shopping features, we share the information related to the transaction with the merchant, payment and transaction fulfillment providers, and other service providers. For example, we will share the order items, contact details and delivery information so your order can be processed. These entities may use the information shared in accordance with their privacy policies.\\r\\nWhere we store your information\\r\\nYour information may be stored on servers located outside the country where you live, such as in Singapore, Malaysia, Ireland and the United States. We maintain major servers around the world to bring you our services globally and continuously.\\r\\nYour rights and choices\\r\\nYou have rights and choices when it comes to your information. You may be afforded certain rights under applicable laws, which may include the right to access, delete, update, or rectify your data, to be informed of the processing of your data, to file complaints with authorities, and potentially other rights. You may submit a request to exercise your rights under applicable laws .\\r\\nYou may appeal any decision we have made about your request by following the instructions in the communication you receive from us notifying you of our decision. Please also see the Supplemental Terms below on whether a local representative or local contact is available for your country.\\r\\nYou can access and edit most of your profile information by signing into Vlog. You can delete the User Content you uploaded. We also provide a number of tools in Settings that allow you to control, among others, who can view your videos, send you messages, or post comments to your videos. Should you choose to do so, you may delete your entire account in Settings.\\r\\nYou may be able to refuse or disable Cookies by adjusting your device browser settings. Because each browser is different, please consult the instructions provided by your browser. Please note that you may need to take additional steps to refuse or disable certain types of Cookies. For example, due to differences in how browsers and mobile apps function, you may need to take different steps to opt out of Cookies used for targeted advertising in a browser and to opt out of targeted advertising for a mobile application, which you may control through your device settings or mobile app permissions. In addition, your opt-out selection is specific to the particular browser or device that you are using when you opt out, so you may need to opt-out separately for each of browser or device. If you choose to refuse, disable, or delete Cookies, some of the functionality of the Platform may no longer be available to you.\\r\\nThe security of your information\\r\\nWe take steps to ensure that your information is treated securely and in accordance with this policy. Unfortunately, the transmission of information via the internet is not completely secure. Although we will use reasonable measures to protect your personal data, for example, by encryption, we cannot guarantee the security of your information transmitted via the Platform; any transmission is at your own risk.\\r\\nWe have appropriate technical and organizational measures to ensure a level of security appropriate to the risk of varying likelihood and severity for the rights and freedoms of you and other users. We maintain these technical and organizational measures and will amend them from time to time to improve the overall security of our systems.\\r\\nWe will, from time to time, include links to and from the websites of our partner networks, advertisers and affiliates. If you follow a link to any of these websites, please note that these websites have their own privacy policies and that we do not accept any responsibility or liability for these policies. Please check these policies before you submit any information to these websites.\\r\\nHow long we keep your information\\r\\nWe retain information for as long as necessary to provide the Platform and for the other purposes set out in this Privacy Policy. We also retain information when necessary to comply with contractual and legal obligations, when we have a legitimate business interest to do so (such as improving and developing the Platform, and enhancing its safety, security and stability), and for the exercise or defence of legal claims.\\r\\nThe retention periods are different depending on different criteria, such as the type of information and the purposes for which we use the information. For example, when we process your information such as your profile information to provide you with the Platform, we keep this information for as long as you have an account. If you violate our Terms of Service, Community Guidelines, or other conditions or policies, we may remove your profile and User Content from public view immediately, but may keep other information about you to process the violation.\\r\\nInformation relating to children and teens\\r\\nVlog is not directed at children under the age of 13. In certain cases this age may be higher due to local regulatory requirements, please see your local supplemental terms for more information. If you believe that there is a user who is below this minimum age, please contact us at Vlogapp2024@gmail.com\\r\\n\\r\\n\\r\\n\\r\\nContact\\r\\nIf you have questions, comments, complaints or requests regarding this Privacy Policy, please contact us at: Vlogapp2024@gmail.com\\r\\nPlease also see the supplemental terms below on whether a local representative or local contact is available for your country.\\r\\nWe will endeavour to deal with your request as soon as possible. This is without prejudice to your right to make a complaint with a relevant data protection authority, where applicable.\",\"ar\":\"\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629\\r\\n\\r\\n\\u0645\\u0631\\u062d\\u0628\\u064b\\u0627 \\u0628\\u0643 \\u0641\\u064a \\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648. \\u062a\\u0646\\u0637\\u0628\\u0642 \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647 \\u0639\\u0644\\u0649 \\u062e\\u062f\\u0645\\u0627\\u062a Vlog (\\\"\\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\\")\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0645\\u0644 \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a Vlog \\u0648\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628 \\u0648\\u0627\\u0644\\u0628\\u0631\\u0627\\u0645\\u062c \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0630\\u0627\\u062a \\u0627\\u0644\\u0635\\u0644\\u0629 \\u0627\\u0644\\u062a\\u064a \\u064a\\u062a\\u0645 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647\\u0627 \\u0639\\u0628\\u0631 \\u0623\\u064a \\u0646\\u0638\\u0627\\u0645 \\u0623\\u0633\\u0627\\u0633\\u064a \\u0623\\u0648 \\u062c\\u0647\\u0627\\u0632 \\u064a\\u0631\\u062a\\u0628\\u0637 \\u0628\\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647. \\u064a\\u062a\\u0645 \\u062a\\u0648\\u0641\\u064a\\u0631 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u0627\\u0644\\u062a\\u062d\\u0643\\u0645 \\u0641\\u064a\\u0647 \\u0628\\u0648\\u0627\\u0633\\u0637\\u0629 Vlog Pte. Ltd.\\u060c \\u0648\\u0639\\u0646\\u0648\\u0627\\u0646\\u0647\\u0627 \\u0627\\u0644\\u0645\\u0633\\u062c\\u0644 \\u0647\\u0648 1 Raffles Quay, #26-10, South Tower, Singapore 048583 (\\\"Vlog\\\" \\u0623\\u0648 \\\"\\u0646\\u062d\\u0646\\\" \\u0623\\u0648 \\\"\\u0646\\u062d\\u0646\\\").\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u062a\\u0639\\u0647\\u062f \\u0639\\u0644\\u0649 \\u062d\\u0641\\u0638 \\u0648 \\u0627\\u062d\\u062a\\u0631\\u0627\\u0645 \\u062e\\u0635\\u0648\\u0635\\u064a\\u0627\\u062a\\u0643. \\u062a\\u0634\\u0631\\u062d \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647 \\u0643\\u064a\\u0641 \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0648\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0648\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629 \\u0644\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0648\\u0627\\u0644\\u0623\\u0641\\u0631\\u0627\\u062f \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0641\\u064a\\u0645\\u0627 \\u064a\\u062a\\u0639\\u0644\\u0642 \\u0628\\u0645\\u0646\\u0635\\u062a\\u0646\\u0627. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0644\\u0627 \\u062a\\u0648\\u0627\\u0641\\u0642 \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0629\\u060c \\u0641\\u064a\\u062c\\u0628 \\u0639\\u0644\\u064a\\u0643 \\u0639\\u062f\\u0645 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\u0645\\u0627 \\u0647\\u064a \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0645\\u0639\\u0647\\u0627\\r\\n\\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0644\\u064a\\u0629 \\u0639\\u0646\\u0643:\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0642\\u062f\\u0645\\u0647\\u0627\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a. \\u0623\\u0646\\u062a \\u062a\\u0642\\u062f\\u0645 \\u0644\\u0646\\u0627 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u062f \\u0627\\u0644\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0633\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0648\\u0643\\u0644\\u0645\\u0629 \\u0627\\u0644\\u0645\\u0631\\u0648\\u0631 \\u0648\\u062a\\u0627\\u0631\\u064a\\u062e \\u0627\\u0644\\u0645\\u064a\\u0644\\u0627\\u062f (\\u062d\\u064a\\u062b\\u0645\\u0627 \\u064a\\u0646\\u0637\\u0628\\u0642 \\u0630\\u0644\\u0643) \\u0648\\u0639\\u0646\\u0648\\u0627\\u0646 \\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0625\\u0644\\u0643\\u062a\\u0631\\u0648\\u0646\\u064a \\u0648\\/\\u0623\\u0648 \\u0631\\u0642\\u0645 \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0643\\u0634\\u0641 \\u0639\\u0646\\u0647\\u0627 \\u0641\\u064a \\u0645\\u0644\\u0641 \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u0635\\u0648\\u0631\\u062a\\u0643 \\u0627\\u0644\\u0641\\u0648\\u062a\\u0648\\u063a\\u0631\\u0627\\u0641\\u064a\\u0629 \\u0623\\u0648 \\u0645\\u0642\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a.\\r\\n\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0646\\u0634\\u0626\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0635\\u0648\\u0631 \\u0648\\u0627\\u0644\\u062a\\u0633\\u062c\\u064a\\u0644\\u0627\\u062a \\u0627\\u0644\\u0635\\u0648\\u062a\\u064a\\u0629 \\u0648\\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0642\\u0648\\u0645 \\u0628\\u062a\\u062d\\u0645\\u064a\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u0625\\u0646\\u0634\\u0627\\u0626\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0648\\u0639\\u0644\\u0627\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u0635\\u0646\\u064a\\u0641 \\u0648\\u0627\\u0644\\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0627\\u062a \\u0648\\u0623\\u062d\\u062f\\u0627\\u062b \\u0627\\u0644\\u0628\\u062b \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062c\\u0631\\u064a\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0648\\u0635\\u0641\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0631\\u062a\\u0628\\u0637\\u0629 \\u0628\\u0647\\u0627\\u060c \\u0645\\u062b\\u0644 \\u0645\\u062a\\u0649 \\u0648\\u0623\\u064a\\u0646 \\u0648\\u0645\\u0646 \\u0642\\u0627\\u0645 \\u0628\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u062a\\u0645 \\u0625\\u0646\\u0634\\u0627\\u0624\\u0647 (\\\"\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\\"). \\u062d\\u062a\\u0649 \\u0644\\u0648 \\u0644\\u0645 \\u062a\\u0643\\u0646 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064b\\u0627\\u060c \\u0641\\u0642\\u062f \\u062a\\u0638\\u0647\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0641\\u064a \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0630\\u064a \\u0623\\u0646\\u0634\\u0623\\u0647 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u0648\\u0646 \\u0623\\u0648 \\u0646\\u0634\\u0631\\u0648\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u062a\\u062d\\u0645\\u064a\\u0644 \\u0627\\u0644\\u0645\\u0633\\u0628\\u0642 \\u0641\\u064a \\u0648\\u0642\\u062a \\u0627\\u0644\\u0625\\u0646\\u0634\\u0627\\u0621 \\u0623\\u0648 \\u0627\\u0644\\u0627\\u0633\\u062a\\u064a\\u0631\\u0627\\u062f \\u0623\\u0648 \\u0627\\u0644\\u062a\\u062d\\u0645\\u064a\\u0644\\u060c \\u0628\\u063a\\u0636 \\u0627\\u0644\\u0646\\u0638\\u0631 \\u0639\\u0645\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0627\\u062e\\u062a\\u0631\\u062a \\u062d\\u0641\\u0638 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0623\\u0648 \\u062a\\u062d\\u0645\\u064a\\u0644\\u0647\\u060c \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u0627\\u0644\\u062a\\u0648\\u0635\\u064a\\u0629 \\u0628\\u062e\\u064a\\u0627\\u0631\\u0627\\u062a \\u0627\\u0644\\u0635\\u0648\\u062a \\u0648\\u062a\\u0642\\u062f\\u064a\\u0645 \\u062a\\u0648\\u0635\\u064a\\u0627\\u062a \\u0645\\u062e\\u0635\\u0635\\u0629 \\u0623\\u062e\\u0631\\u0649. \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u062a\\u0637\\u0628\\u064a\\u0642 \\u062a\\u0623\\u062b\\u064a\\u0631 \\u0639\\u0644\\u0649 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\u060c \\u0641\\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0646\\u0633\\u062e\\u0629 \\u0645\\u0646 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0644\\u0627 \\u062a\\u062a\\u0636\\u0645\\u0646 \\u0627\\u0644\\u062a\\u0623\\u062b\\u064a\\u0631.\\r\\n\\u0631\\u0633\\u0627\\u0626\\u0644. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0642\\u062f\\u0645\\u0647\\u0627 \\u0639\\u0646\\u062f \\u0625\\u0646\\u0634\\u0627\\u0621 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0623\\u0648 \\u0625\\u0631\\u0633\\u0627\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u062a\\u0644\\u0642\\u064a\\u0647\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0629 \\u0628\\u0627\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0648\\u0647\\u064a \\u062a\\u062a\\u0636\\u0645\\u0646 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0631\\u0633\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u062a\\u0633\\u062a\\u0642\\u0628\\u0644\\u0647\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u064a\\u0641\\u0629 \\u0627\\u0644\\u062f\\u0631\\u062f\\u0634\\u0629 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0639\\u0646\\u062f \\u0627\\u0644\\u062a\\u0648\\u0627\\u0635\\u0644 \\u0645\\u0639 \\u0627\\u0644\\u062a\\u062c\\u0627\\u0631 \\u0627\\u0644\\u0630\\u064a\\u0646 \\u064a\\u0628\\u064a\\u0639\\u0648\\u0646 \\u0627\\u0644\\u0628\\u0636\\u0627\\u0626\\u0639 \\u0644\\u0643\\u060c \\u0648\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0644\\u0645\\u0633\\u0627\\u0639\\u062f\\u064a\\u0646 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u064a\\u0646 \\u0639\\u0646\\u062f \\u0634\\u0631\\u0627\\u0621 \\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a. \\u062a\\u062a\\u0636\\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629 \\u0648\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629\\u060c \\u0645\\u062b\\u0644 \\u0648\\u0642\\u062a \\u0625\\u0631\\u0633\\u0627\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\\u0647\\u0627 \\u0623\\u0648 \\u0642\\u0631\\u0627\\u0621\\u062a\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u064a\\u0646 \\u0641\\u064a \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629. \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0639\\u0644\\u0645 \\u0623\\u0646 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062e\\u062a\\u0627\\u0631 \\u0625\\u0631\\u0633\\u0627\\u0644\\u0647\\u0627 \\u0625\\u0644\\u0649 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0622\\u062e\\u0631\\u064a\\u0646 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0633\\u064a\\u0643\\u0648\\u0646 \\u0641\\u064a \\u0645\\u062a\\u0646\\u0627\\u0648\\u0644 \\u0647\\u0624\\u0644\\u0627\\u0621 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0648\\u0623\\u0646\\u0646\\u0627 \\u0644\\u0633\\u0646\\u0627 \\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0646 \\u0639\\u0646 \\u0627\\u0644\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0627\\u0644\\u062a\\u064a \\u064a\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0628\\u0647\\u0627 \\u0647\\u0624\\u0644\\u0627\\u0621 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u0648\\u0646 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0623\\u0648 \\u064a\\u0634\\u0627\\u0631\\u0643\\u0648\\u0646\\u0647\\u0627.\\r\\n\\u064a\\u062c\\u0648\\u0632 \\u0644\\u0646\\u0627 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0646\\u0635\\u0648\\u0635 \\u0648\\u0627\\u0644\\u0635\\u0648\\u0631 \\u0648\\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648\\u060c \\u0627\\u0644\\u0645\\u0648\\u062c\\u0648\\u062f \\u0641\\u064a \\u062d\\u0627\\u0641\\u0638\\u0629 \\u062c\\u0647\\u0627\\u0632\\u0643\\u060c \\u0628\\u0639\\u062f \\u0627\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0625\\u0630\\u0646 \\u0645\\u0646\\u0643. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0628\\u062f\\u0621 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0645\\u0639 \\u0646\\u0638\\u0627\\u0645 \\u0623\\u0633\\u0627\\u0633\\u064a \\u062a\\u0627\\u0628\\u0639 \\u0644\\u062c\\u0647\\u0629 \\u062e\\u0627\\u0631\\u062c\\u064a\\u0629\\u060c \\u0623\\u0648 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0644\\u0635\\u0642 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0645\\u0646 \\u0627\\u0644\\u062d\\u0627\\u0641\\u0638\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u0635\\u0644 \\u0625\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u062e\\u0632\\u0646\\u0629 \\u0641\\u064a \\u0627\\u0644\\u062d\\u0627\\u0641\\u0638\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u062a\\u0644\\u0628\\u064a\\u0629 \\u0637\\u0644\\u0628\\u0643.\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0634\\u0631\\u0627\\u0621. \\u0639\\u0646\\u062f \\u0625\\u062c\\u0631\\u0627\\u0621 \\u0639\\u0645\\u0644\\u064a\\u0629 \\u0634\\u0631\\u0627\\u0621 \\u0623\\u0648 \\u062f\\u0641\\u0639 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0623\\u0648 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644\\u0647\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0646\\u062f \\u0634\\u0631\\u0627\\u0621 \\u0639\\u0645\\u0644\\u0627\\u062a Vlog \\u0623\\u0648 \\u0634\\u0631\\u0627\\u0621 \\u0627\\u0644\\u0628\\u0636\\u0627\\u0626\\u0639 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0648\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0645\\u0639\\u0627\\u0645\\u0644\\u0629 \\u0627\\u0644\\u0634\\u0631\\u0627\\u0621 \\u0623\\u0648 \\u0627\\u0644\\u062f\\u0641\\u0639\\u060c \\u0645\\u062b\\u0644 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0628\\u0637\\u0627\\u0642\\u0629 \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u0627\\u0644\\u0641\\u0648\\u0627\\u062a\\u064a\\u0631 \\u0648\\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645 \\u0648\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0648\\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u062a\\u064a \\u0627\\u0634\\u062a\\u0631\\u064a\\u062a\\u0647\\u0627.\\r\\n\\u0647\\u0627\\u062a\\u0641\\u0643 \\u0648\\u0627\\u062a\\u0635\\u0627\\u0644\\u0627\\u062a \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629. \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0645\\u0632\\u0627\\u0645\\u0646\\u0629 \\u062c\\u0647\\u0627\\u062a \\u0627\\u062a\\u0635\\u0627\\u0644 \\u0647\\u0627\\u062a\\u0641\\u0643\\u060c \\u0641\\u0633\\u0646\\u0642\\u0648\\u0645 \\u0628\\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0648\\u062c\\u0645\\u0639\\u0647\\u0627 \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0623\\u0633\\u0645\\u0627\\u0621 \\u0648\\u0623\\u0631\\u0642\\u0627\\u0645 \\u0627\\u0644\\u0647\\u0648\\u0627\\u062a\\u0641 \\u0648\\u0639\\u0646\\u0627\\u0648\\u064a\\u0646 \\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0625\\u0644\\u0643\\u062a\\u0631\\u0648\\u0646\\u064a\\u060c \\u0648\\u0645\\u0637\\u0627\\u0628\\u0642\\u0629 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u062d\\u0627\\u0644\\u064a\\u064a\\u0646 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u062c\\u0647\\u0627\\u062a \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629\\u060c \\u0641\\u0633\\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0627\\u0644\\u0639\\u0627\\u0645 \\u0628\\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u0629 \\u0625\\u0644\\u0649 \\u0623\\u0633\\u0645\\u0627\\u0621 \\u0648\\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u062c\\u0647\\u0627\\u062a \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629.\\r\\n\\u0625\\u062b\\u0628\\u0627\\u062a \\u0647\\u0648\\u064a\\u062a\\u0643 \\u0623\\u0648 \\u0639\\u0645\\u0631\\u0643. \\u0646\\u0637\\u0644\\u0628 \\u0645\\u0646\\u0643 \\u0623\\u062d\\u064a\\u0627\\u0646\\u064b\\u0627 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0625\\u062b\\u0628\\u0627\\u062a \\u0644\\u0644\\u0647\\u0648\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0639\\u0645\\u0631 \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629\\u060c \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0628\\u062b \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631 \\u0623\\u0648 \\u0627\\u0644\\u062d\\u0633\\u0627\\u0628\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0645 \\u0627\\u0644\\u062a\\u062d\\u0642\\u0642 \\u0645\\u0646\\u0647\\u0627\\u060c \\u0623\\u0648 \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u062a\\u0642\\u062f\\u0645 \\u0628\\u0637\\u0644\\u0628 \\u0644\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628 \\u062a\\u062c\\u0627\\u0631\\u064a\\u060c \\u062a\\u0623\\u0643\\u062f \\u0645\\u0646 \\u0623\\u0646\\u0643 \\u0643\\u0628\\u064a\\u0631 \\u0628\\u0645\\u0627 \\u064a\\u0643\\u0641\\u064a \\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0623\\u0648 \\u0641\\u064a \\u062d\\u0627\\u0644\\u0627\\u062a \\u0623\\u062e\\u0631\\u0649 \\u062d\\u064a\\u062b \\u0642\\u062f \\u062a\\u0643\\u0648\\u0646 \\u0647\\u0646\\u0627\\u0643 \\u062d\\u0627\\u062c\\u0629 \\u0644\\u0644\\u062a\\u062d\\u0642\\u0642.\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0648\\u0627\\u0631\\u062f\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0631\\u0633\\u0644\\u0647\\u0627 \\u0625\\u0644\\u064a\\u0646\\u0627\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0646\\u062f \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627 \\u0644\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u062f\\u0639\\u0645 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a.\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0637\\u0644\\u0627\\u0639\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u0623\\u0628\\u062d\\u0627\\u062b \\u0623\\u0648 \\u0627\\u0644\\u0639\\u0631\\u0648\\u0636 \\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0633\\u0627\\u0628\\u0642\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u062d\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0648\\u064a\\u0642\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u062a\\u062d\\u062f\\u064a\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0633\\u0627\\u0628\\u0642\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u0623\\u062d\\u062f\\u0627\\u062b \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0631\\u064a\\u0647\\u0627 \\u0623\\u0648 \\u0646\\u0631\\u0639\\u0627\\u0647\\u0627 \\u0648\\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0627\\u0631\\u0643 \\u0641\\u064a\\u0647\\u0627.\\r\\n\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u064a\\u062a\\u0645 \\u062c\\u0645\\u0639\\u0647\\u0627 \\u062a\\u0644\\u0642\\u0627\\u0626\\u064a\\u0627\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062a\\u062a\\u0639\\u0644\\u0642 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0643\\u064a\\u0641\\u064a\\u0629 \\u062a\\u0641\\u0627\\u0639\\u0644\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u062a\\u0641\\u0627\\u0639\\u0644\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u0646\\u0639\\u0631\\u0636\\u0647 \\u0644\\u0643\\u060c \\u0648\\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0627\\u0647\\u062f\\u0647\\u0627\\u060c \\u0648\\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0627\\u0647\\u062f\\u0647\\u0627 \\u0648\\u0627\\u0644\\u0645\\u0634\\u0643\\u0644\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0648\\u0627\\u062c\\u0647\\u0647\\u0627\\u060c \\u0648\\u0633\\u062c\\u0644 \\u0627\\u0644\\u062a\\u0635\\u0641\\u062d \\u0648\\u0627\\u0644\\u0628\\u062d\\u062b\\u060c \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0634\\u0627\\u0647\\u062f\\u0647. \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u062d\\u0641\\u0638\\u0647 \\u0641\\u064a \\\"\\u0627\\u0644\\u0645\\u0641\\u0636\\u0644\\u0629\\\"\\u060c \\u0648\\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u0630\\u064a\\u0646 \\u062a\\u062a\\u0627\\u0628\\u0639\\u0647\\u0645 \\u0648\\u0643\\u064a\\u0641\\u064a\\u0629 \\u062a\\u0641\\u0627\\u0639\\u0644\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0645\\u062a\\u0627\\u0628\\u0639\\u064a\\u0646 \\u0627\\u0644\\u0645\\u0634\\u062a\\u0631\\u0643\\u064a\\u0646.\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0646\\u0628\\u0637\\u0629. \\u0646\\u0633\\u062a\\u0646\\u062a\\u062c \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0633\\u0645\\u0627\\u062a\\u0643\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0647\\u062a\\u0645\\u0627\\u0645\\u0627\\u062a\\u0643 \\u0648\\u062c\\u0646\\u0633\\u0643 \\u0648\\u0641\\u0626\\u062a\\u0643 \\u0627\\u0644\\u0639\\u0645\\u0631\\u064a\\u0629 \\u0628\\u063a\\u0631\\u0636 \\u062a\\u062e\\u0635\\u064a\\u0635 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649.\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0641\\u0646\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0645\\u0639\\u0647\\u0627 \\u0639\\u0646\\u0643. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u062d\\u0648\\u0644 \\u0627\\u0644\\u062c\\u0647\\u0627\\u0632 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645\\u0647 \\u0644\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0645\\u062b\\u0644 \\u0639\\u0646\\u0648\\u0627\\u0646 IP \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\u060c \\u0648\\u0648\\u0643\\u064a\\u0644 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u060c \\u0648\\u0645\\u0634\\u063a\\u0644 \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062d\\u0645\\u0648\\u0644\\u060c \\u0648\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0642\\u0629 \\u0627\\u0644\\u0632\\u0645\\u0646\\u064a\\u0629\\u060c \\u0648\\u0627\\u0644\\u0645\\u0639\\u0631\\u0641\\u0627\\u062a \\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u060c \\u0648\\u0637\\u0631\\u0627\\u0632 \\u062c\\u0647\\u0627\\u0632\\u0643\\u060c \\u0648\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u062c\\u0647\\u0627\\u0632\\u060c \\u0648\\u0646\\u0648\\u0639 \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629\\u060c \\u0648\\u0645\\u0639\\u0631\\u0641\\u0627\\u062a \\u0627\\u0644\\u062c\\u0647\\u0627\\u0632\\u060c \\u062f\\u0642\\u0629 \\u0627\\u0644\\u0634\\u0627\\u0634\\u0629 \\u0648\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u062a\\u0634\\u063a\\u064a\\u0644 \\u0648\\u0623\\u0633\\u0645\\u0627\\u0621 \\u0648\\u0623\\u0646\\u0648\\u0627\\u0639 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0644\\u0641\\u0627\\u062a \\u0648\\u0623\\u0646\\u0645\\u0627\\u0637 \\u0636\\u063a\\u0637\\u0627\\u062a \\u0627\\u0644\\u0645\\u0641\\u0627\\u062a\\u064a\\u062d \\u0623\\u0648 \\u0627\\u0644\\u0625\\u064a\\u0642\\u0627\\u0639\\u0627\\u062a \\u0648\\u062d\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0628\\u0637\\u0627\\u0631\\u064a\\u0629 \\u0648\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0635\\u0648\\u062a \\u0648\\u0623\\u062c\\u0647\\u0632\\u0629 \\u0627\\u0644\\u0635\\u0648\\u062a \\u0627\\u0644\\u0645\\u062a\\u0635\\u0644\\u0629. \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0642\\u0648\\u0645 \\u0628\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0627\\u0644\\u062f\\u062e\\u0648\\u0644 \\u0645\\u0646 \\u0623\\u062c\\u0647\\u0632\\u0629 \\u0645\\u062a\\u0639\\u062f\\u062f\\u0629\\u060c \\u0633\\u0646\\u0643\\u0648\\u0646 \\u0642\\u0627\\u062f\\u0631\\u064a\\u0646 \\u0639\\u0644\\u0649 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0644\\u062a\\u062d\\u062f\\u064a\\u062f \\u0646\\u0634\\u0627\\u0637\\u0643 \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0623\\u062c\\u0647\\u0632\\u0629. \\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u0631\\u0628\\u0637\\u0643 \\u0628\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0645 \\u062c\\u0645\\u0639\\u0647\\u0627 \\u0645\\u0646 \\u0623\\u062c\\u0647\\u0632\\u0629 \\u0623\\u062e\\u0631\\u0649 \\u063a\\u064a\\u0631 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645\\u0647\\u0627 \\u0644\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0627\\u0644\\u062f\\u062e\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a.\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639. \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0645\\u0648\\u0642\\u0639\\u0643 \\u0627\\u0644\\u062a\\u0642\\u0631\\u064a\\u0628\\u064a\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639 \\u0628\\u0646\\u0627\\u0621\\u064b \\u0639\\u0644\\u0649 \\u0628\\u0637\\u0627\\u0642\\u0629 SIM \\u0648\\/\\u0623\\u0648 \\u0639\\u0646\\u0648\\u0627\\u0646 IP \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643. \\u0628\\u0639\\u062f \\u0627\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0625\\u0630\\u0646 \\u0645\\u0646\\u0643\\u060c \\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u062c\\u0645\\u0639 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639 \\u0627\\u0644\\u062f\\u0642\\u064a\\u0642\\u0629 (\\u0645\\u062b\\u0644 \\u0646\\u0638\\u0627\\u0645 \\u062a\\u062d\\u062f\\u064a\\u062f \\u0627\\u0644\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0639\\u0627\\u0644\\u0645\\u064a). \\u0628\\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u0629 \\u0625\\u0644\\u0649 \\u0630\\u0644\\u0643\\u060c \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639 (\\u0645\\u062b\\u0644 \\u0645\\u0646\\u0627\\u0637\\u0642 \\u0627\\u0644\\u062c\\u0630\\u0628 \\u0627\\u0644\\u0633\\u064a\\u0627\\u062d\\u064a \\u0623\\u0648 \\u0627\\u0644\\u0645\\u062d\\u0644\\u0627\\u062a \\u0627\\u0644\\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u0623\\u0648 \\u0646\\u0642\\u0627\\u0637 \\u0627\\u0644\\u0627\\u0647\\u062a\\u0645\\u0627\\u0645 \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649) \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0625\\u0636\\u0627\\u0641\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639 \\u0625\\u0644\\u0649 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643.\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0635\\u0648\\u0631\\u0629 \\u0648\\u0627\\u0644\\u0635\\u0648\\u062a. \\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0648\\u0627\\u0644\\u0635\\u0648\\u0631 \\u0648\\u0627\\u0644\\u062a\\u0633\\u062c\\u064a\\u0644\\u0627\\u062a \\u0627\\u0644\\u0635\\u0648\\u062a\\u064a\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0634\\u0643\\u0644 \\u062c\\u0632\\u0621\\u064b\\u0627 \\u0645\\u0646 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643\\u060c \\u0645\\u062b\\u0644 \\u062a\\u062d\\u062f\\u064a\\u062f \\u0627\\u0644\\u0643\\u0627\\u0626\\u0646\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0646\\u0627\\u0638\\u0631 \\u0627\\u0644\\u062a\\u064a \\u062a\\u0638\\u0647\\u0631\\u060c \\u0648\\u0648\\u062c\\u0648\\u062f \\u0648\\u0645\\u0648\\u0642\\u0639 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0648\\u0633\\u0645\\u0627\\u062a \\u0627\\u0644\\u0648\\u062c\\u0647 \\u0648\\u0627\\u0644\\u062c\\u0633\\u0645\\u060c \\u0648\\u0637\\u0628\\u064a\\u0639\\u0629 \\u0627\\u0644\\u0635\\u0648\\u062a. \\u0648\\u0646\\u0635 \\u0627\\u0644\\u0643\\u0644\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0646\\u0637\\u0648\\u0642\\u0629 \\u0641\\u064a \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643. \\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0644\\u062a\\u0645\\u0643\\u064a\\u0646 \\u062a\\u0623\\u062b\\u064a\\u0631\\u0627\\u062a \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629\\u060c \\u0648\\u0627\\u0644\\u0625\\u0634\\u0631\\u0627\\u0641 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649\\u060c \\u0648\\u0627\\u0644\\u062a\\u0635\\u0646\\u064a\\u0641 \\u0627\\u0644\\u062f\\u064a\\u0645\\u0648\\u063a\\u0631\\u0627\\u0641\\u064a\\u060c \\u0648\\u062a\\u0648\\u0635\\u064a\\u0627\\u062a \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a\\u060c \\u0648\\u0627\\u0644\\u0639\\u0645\\u0644\\u064a\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u062a\\u064a \\u0644\\u0627 \\u062a\\u062d\\u062f\\u062f \\u0627\\u0644\\u0647\\u0648\\u064a\\u0629 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629.\\r\\n\\r\\n\\r\\n\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0646 \\u0645\\u0635\\u0627\\u062f\\u0631 \\u0623\\u062e\\u0631\\u0649\\r\\n\\u0642\\u062f \\u0646\\u062a\\u0644\\u0642\\u0649 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0648\\u0636\\u062d\\u0629 \\u0641\\u064a \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647 \\u0645\\u0646 \\u0645\\u0635\\u0627\\u062f\\u0631 \\u0623\\u062e\\u0631\\u0649\\u060c \\u0645\\u062b\\u0644:\\r\\n\\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0627\\u0644\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0623\\u0648 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062d\\u0633\\u0627\\u0628 \\u0634\\u0628\\u0643\\u0629 \\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u062c\\u0647\\u0629 \\u062e\\u0627\\u0631\\u062c\\u064a\\u0629 (\\u0645\\u062b\\u0644 Google) \\u0623\\u0648 \\u062e\\u062f\\u0645\\u0629 \\u062a\\u0633\\u062c\\u064a\\u0644 \\u0627\\u0644\\u062f\\u062e\\u0648\\u0644\\u060c \\u0641\\u0633\\u0648\\u0641 \\u062a\\u0632\\u0648\\u062f\\u0646\\u0627 \\u0623\\u0648 \\u062a\\u0633\\u0645\\u062d \\u0644\\u0646\\u0627 \\u0628\\u062a\\u0632\\u0648\\u064a\\u062f\\u0646\\u0627 \\u0628\\u0627\\u0633\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0627\\u0644\\u0639\\u0627\\u0645 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0645\\u0644\\u0629 \\u0627\\u0644\\u0645\\u062a\\u0639\\u0644\\u0642\\u0629 \\u0628\\u0647\\u0630\\u0627 \\u062d\\u0633\\u0627\\u0628. \\u0633\\u0646\\u0642\\u0648\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0645\\u0639 \\u0634\\u0628\\u0643\\u062a\\u0643 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0645\\u062b\\u0644 \\u0645\\u0639\\u0631\\u0641 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u0631\\u0645\\u0632 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0648\\u0639\\u0646\\u0648\\u0627\\u0646 URL \\u0627\\u0644\\u0645\\u0631\\u062c\\u0639\\u064a. \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u0631\\u0628\\u0637 \\u062d\\u0633\\u0627\\u0628 Vlog \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0628\\u062e\\u062f\\u0645\\u0629 \\u0623\\u062e\\u0631\\u0649\\u060c \\u0641\\u0642\\u062f \\u0646\\u062a\\u0644\\u0642\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u062a\\u0644\\u0643 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629.\\r\\n\\u064a\\u0634\\u0627\\u0631\\u0643 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0646\\u0648\\u0646 \\u0648\\u0627\\u0644\\u0642\\u064a\\u0627\\u0633 \\u0648\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u0621 \\u0627\\u0644\\u0622\\u062e\\u0631\\u0648\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u0643 \\u0648\\u0639\\u0646 \\u0627\\u0644\\u0625\\u062c\\u0631\\u0627\\u0621\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0627\\u062a\\u062e\\u0630\\u062a\\u0647\\u0627 \\u062e\\u0627\\u0631\\u062c \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0645\\u062b\\u0644 \\u0623\\u0646\\u0634\\u0637\\u062a\\u0643 \\u0639\\u0644\\u0649 \\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628 \\u0648\\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0623\\u0648 \\u0641\\u064a \\u0627\\u0644\\u0645\\u062a\\u0627\\u062c\\u0631\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0623\\u0648 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0627\\u0634\\u062a\\u0631\\u064a\\u062a\\u0647\\u0627 \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0625\\u0646\\u062a\\u0631\\u0646\\u062a \\u0623\\u0648 \\u0634\\u062e\\u0635\\u064a\\u064b\\u0627. \\u064a\\u0634\\u0627\\u0631\\u0643 \\u0647\\u0624\\u0644\\u0627\\u0621 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u0621 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639\\u0646\\u0627\\u060c \\u0645\\u062b\\u0644 \\u0645\\u0639\\u0631\\u0641\\u0627\\u062a \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062d\\u0645\\u0648\\u0644 \\u0644\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646 \\u0648\\u0639\\u0646\\u0627\\u0648\\u064a\\u0646 \\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0625\\u0644\\u0643\\u062a\\u0631\\u0648\\u0646\\u064a \\u0648\\u0623\\u0631\\u0642\\u0627\\u0645 \\u0627\\u0644\\u0647\\u0648\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062c\\u0632\\u0623\\u0629 \\u0648\\u0645\\u0639\\u0631\\u0641\\u0627\\u062a \\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645\\u0647\\u0627 \\u0644\\u0644\\u0645\\u0633\\u0627\\u0639\\u062f\\u0629 \\u0641\\u064a \\u0645\\u0637\\u0627\\u0628\\u0642\\u062a\\u0643 \\u0648\\u0625\\u062c\\u0631\\u0627\\u0621\\u0627\\u062a\\u0643 \\u062e\\u0627\\u0631\\u062c \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0645\\u0639 \\u062d\\u0633\\u0627\\u0628 Vlog \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643.\\r\\n\\u0642\\u062f \\u0646\\u062a\\u0644\\u0642\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u0643 \\u0645\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0645\\u0643\\u0627\\u0646 \\u0625\\u062f\\u0631\\u0627\\u062c\\u0643 \\u0623\\u0648 \\u0630\\u0643\\u0631\\u0643 \\u0641\\u064a \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u060c \\u0623\\u0648 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0627\\u0644\\u0645\\u0628\\u0627\\u0634\\u0631\\u0629\\u060c \\u0623\\u0648 \\u0641\\u064a \\u0634\\u0643\\u0648\\u0649 \\u0623\\u0648 \\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0623\\u0648 \\u0637\\u0644\\u0628 \\u0623\\u0648 \\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0645\\u0642\\u062f\\u0645\\u0629 \\u0625\\u0644\\u064a\\u0646\\u0627\\u060c \\u0623\\u0648 \\u0625\\u0630\\u0627 \\u062a\\u0645 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0625\\u0644\\u064a\\u0646\\u0627. \\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062c\\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u0643 \\u0645\\u0646 \\u0645\\u0635\\u0627\\u062f\\u0631 \\u0623\\u062e\\u0631\\u0649 \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0644\\u0644\\u0639\\u0627\\u0645\\u0629.\\r\\n\\u0642\\u062f \\u0646\\u062a\\u0644\\u0642\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u0643 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u062c\\u0627\\u0631 \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u062a\\u0646\\u0641\\u064a\\u0630 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a\\u060c \\u0645\\u062b\\u0644 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062a\\u0623\\u0643\\u064a\\u062f \\u0627\\u0644\\u062f\\u0641\\u0639\\u060c \\u0648\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0648\\u0644 \\u062a\\u0633\\u0644\\u064a\\u0645 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0627\\u0634\\u062a\\u0631\\u064a\\u062a\\u0647\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0648\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627.\\r\\n\\r\\n\\u0643\\u064a\\u0641 \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643\\r\\n\\u0643\\u0645\\u0627 \\u0647\\u0648 \\u0645\\u0648\\u0636\\u062d \\u0623\\u062f\\u0646\\u0627\\u0647\\u060c \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0644\\u062a\\u062d\\u0633\\u064a\\u0646 \\u0648\\u062f\\u0639\\u0645 \\u0648\\u0625\\u062f\\u0627\\u0631\\u0629 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0644\\u0644\\u0633\\u0645\\u0627\\u062d \\u0644\\u0643 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0648\\u0638\\u0627\\u0626\\u0641\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u0648\\u0641\\u0627\\u0621 \\u0628\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627 \\u0648\\u062a\\u0646\\u0641\\u064a\\u0630\\u0647\\u0627. \\u0642\\u062f \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0623\\u064a\\u0636\\u064b\\u0627\\u060c \\u0645\\u0646 \\u0628\\u064a\\u0646 \\u0623\\u0645\\u0648\\u0631 \\u0623\\u062e\\u0631\\u0649\\u060c \\u0644\\u062a\\u062e\\u0635\\u064a\\u0635 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0631\\u0627\\u0647 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0648\\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0648\\u062a\\u062e\\u0635\\u064a\\u0635 \\u062a\\u062c\\u0631\\u0628\\u062a\\u0643 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u064a\\u0629. \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0628\\u0634\\u0643\\u0644 \\u0639\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u0646\\u062c\\u0645\\u0639\\u0647\\u0627 \\u0628\\u0627\\u0644\\u0637\\u0631\\u0642 \\u0627\\u0644\\u062a\\u0627\\u0644\\u064a\\u0629:\\r\\n\\u0644\\u062a\\u0644\\u0628\\u064a\\u0629 \\u0637\\u0644\\u0628\\u0627\\u062a \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0648\\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u0627\\u0644\\u062f\\u0639\\u0645 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0644\\u0644\\u0639\\u0645\\u0644\\u064a\\u0627\\u062a \\u0627\\u0644\\u062f\\u0627\\u062e\\u0644\\u064a\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0633\\u062a\\u0643\\u0634\\u0627\\u0641 \\u0627\\u0644\\u0623\\u062e\\u0637\\u0627\\u0621 \\u0648\\u0625\\u0635\\u0644\\u0627\\u062d\\u0647\\u0627 \\u0648\\u062a\\u062d\\u0644\\u064a\\u0644 \\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0648\\u0627\\u0644\\u0627\\u062e\\u062a\\u0628\\u0627\\u0631 \\u0648\\u0627\\u0644\\u0628\\u062d\\u062b \\u0648\\u0627\\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u0627\\u0644\\u0625\\u062d\\u0635\\u0627\\u0626\\u064a\\u0629 \\u0648\\u0627\\u0644\\u0627\\u0633\\u062a\\u0642\\u0635\\u0627\\u0626\\u064a\\u0629 \\u0648\\u0627\\u0644\\u062a\\u0645\\u0627\\u0633 \\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a\\u0643.\\r\\n\\u0644\\u062a\\u0648\\u0641\\u064a\\u0631 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0648\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627 \\u0648\\u062a\\u0633\\u0647\\u064a\\u0644 \\u0634\\u0631\\u0627\\u0621 \\u0648\\u062a\\u0633\\u0644\\u064a\\u0645 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a \\u0648\\u0627\\u0644\\u0633\\u0644\\u0639 \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u062a\\u062c\\u0627\\u0631 \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u0627\\u0633\\u062a\\u064a\\u0641\\u0627\\u0621 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0645\\u0646 \\u0623\\u062c\\u0644 \\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0637\\u0644\\u0628\\u0627\\u062a\\u0643.\\r\\n\\u0644\\u062a\\u062e\\u0635\\u064a\\u0635 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0631\\u0627\\u0647 \\u0639\\u0646\\u062f \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0642\\u062f \\u0646\\u0642\\u062f\\u0645 \\u0644\\u0643 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0628\\u0646\\u0627\\u0621\\u064b \\u0639\\u0644\\u0649 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0628\\u0644\\u062f \\u0627\\u0644\\u062a\\u064a \\u0627\\u062e\\u062a\\u0631\\u062a\\u0647\\u0627 \\u0623\\u0648 \\u0646\\u0639\\u0631\\u0636 \\u0644\\u0643 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0645\\u0634\\u0627\\u0628\\u0647\\u064b\\u0627 \\u0644\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0630\\u064a \\u0623\\u0639\\u062c\\u0628\\u0643 \\u0623\\u0648 \\u062a\\u0641\\u0627\\u0639\\u0644\\u062a \\u0645\\u0639\\u0647.\\r\\n\\u0644\\u0625\\u0631\\u0633\\u0627\\u0644 \\u0645\\u0648\\u0627\\u062f \\u062a\\u0631\\u0648\\u064a\\u062c\\u064a\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0627\\u0644\\u0641\\u0648\\u0631\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0625\\u0644\\u0643\\u062a\\u0631\\u0648\\u0646\\u064a\\u060c \\u0645\\u0646\\u0627 \\u0623\\u0648 \\u0628\\u0627\\u0644\\u0646\\u064a\\u0627\\u0628\\u0629 \\u0639\\u0646 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0646\\u0627 \\u0648\\u0627\\u0644\\u0623\\u0637\\u0631\\u0627\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b\\u0629 \\u0627\\u0644\\u0645\\u0648\\u062b\\u0648\\u0642\\u0629.\\r\\n\\u0644\\u062a\\u062d\\u0633\\u064a\\u0646 \\u0648\\u062a\\u0637\\u0648\\u064a\\u0631 \\u0645\\u0646\\u0635\\u062a\\u0646\\u0627 \\u0648\\u0625\\u062c\\u0631\\u0627\\u0621 \\u062a\\u0637\\u0648\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0646\\u062a\\u062c\\u0627\\u062a.\\r\\n\\u0644\\u0642\\u064a\\u0627\\u0633 \\u0648\\u0641\\u0647\\u0645 \\u0641\\u0639\\u0627\\u0644\\u064a\\u0629 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u064a\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u062a\\u064a \\u0646\\u062e\\u062f\\u0645\\u0647\\u0627 \\u0644\\u0643 \\u0648\\u0644\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646\\u060c \\u0648\\u0644\\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0647\\u062f\\u0641\\u0629\\u060c \\u0644\\u0643 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\u0644\\u062f\\u0639\\u0645 \\u0627\\u0644\\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u0633\\u0645\\u0627\\u062d \\u0644\\u0643 \\u0648\\u0644\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0628\\u0627\\u0644\\u062a\\u0648\\u0627\\u0635\\u0644 \\u0645\\u0639 \\u0628\\u0639\\u0636\\u0643\\u0645 \\u0627\\u0644\\u0628\\u0639\\u0636 (\\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0648\\u0638\\u064a\\u0641\\u0629 \\u0627\\u0644\\u0628\\u062d\\u062b \\u0639\\u0646 \\u0627\\u0644\\u0623\\u0635\\u062f\\u0642\\u0627\\u0621) \\u0648\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u0646\\u0634\\u0637\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 (\\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062e\\u062a\\u0627\\u0631 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647\\u0627) \\u0645\\u0639 \\u0623\\u0635\\u062f\\u0642\\u0627\\u0626\\u0643\\u060c \\u0644\\u062a\\u0648\\u0641\\u064a\\u0631 \\u062e\\u062f\\u0645\\u0629 \\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627 \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0648\\u0638\\u064a\\u0641\\u0629\\u060c \\u0648\\u0644\\u0627\\u0642\\u062a\\u0631\\u0627\\u062d \\u062d\\u0633\\u0627\\u0628\\u0627\\u062a \\u0644\\u0643 \\u0648\\u0644\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646\\u060c \\u0648\\u0644\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0623\\u0646\\u062a \\u0648\\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0628\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0634\\u0648\\u0631 \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u062a\\u0646\\u0632\\u064a\\u0644\\u0647 \\u0648\\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644 \\u0645\\u0639\\u0647 \\u0628\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0644\\u062a\\u0645\\u0643\\u064a\\u0646\\u0643 \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0641\\u064a \\u0628\\u0631\\u0646\\u0627\\u0645\\u062c \\u0627\\u0644\\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a\\u0629.\\r\\n\\u0644\\u0644\\u0633\\u0645\\u0627\\u062d \\u0644\\u0643 \\u0628\\u0627\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u0641\\u0627\\u0639\\u0644\\u064a\\u0629 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0645\\u062b\\u0644 \\u062a\\u0645\\u0643\\u064a\\u0646 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0641\\u064a \\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646.\\r\\n\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0643\\u062c\\u0632\\u0621 \\u0645\\u0646 \\u062d\\u0645\\u0644\\u0627\\u062a\\u0646\\u0627 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u064a\\u0629 \\u0648\\u0627\\u0644\\u062a\\u0633\\u0648\\u064a\\u0642\\u064a\\u0629 \\u0644\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0648\\u062f\\u0639\\u0648\\u062a\\u0643 \\u0644\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0641\\u064a \\u062d\\u062f\\u062b\\u060c \\u0648\\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c \\u0644\\u0644\\u0645\\u0648\\u0627\\u0636\\u064a\\u0639 \\u0627\\u0644\\u0634\\u0627\\u0626\\u0639\\u0629 \\u0648\\u0639\\u0644\\u0627\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u0635\\u0646\\u064a\\u0641 \\u0648\\u0627\\u0644\\u062d\\u0645\\u0644\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\u0644\\u0641\\u0647\\u0645 \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645\\u0643 \\u0644\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0628\\u0631 \\u0623\\u062c\\u0647\\u0632\\u062a\\u0643.\\r\\n\\u0644\\u0627\\u0633\\u062a\\u0646\\u062a\\u0627\\u062c \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0639\\u0646\\u0643\\u060c \\u0645\\u062b\\u0644 \\u0641\\u0626\\u062a\\u0643 \\u0627\\u0644\\u0639\\u0645\\u0631\\u064a\\u0629 \\u0648\\u062c\\u0646\\u0633\\u0643 \\u0648\\u0627\\u0647\\u062a\\u0645\\u0627\\u0645\\u0627\\u062a\\u0643.\\r\\n\\u0644\\u0645\\u0633\\u0627\\u0639\\u062f\\u062a\\u0646\\u0627 \\u0641\\u064a \\u0627\\u0643\\u062a\\u0634\\u0627\\u0641 \\u0648\\u0645\\u0643\\u0627\\u0641\\u062d\\u0629 \\u0625\\u0633\\u0627\\u0621\\u0629 \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0648\\u0627\\u0644\\u0646\\u0634\\u0627\\u0637 \\u0627\\u0644\\u0636\\u0627\\u0631 \\u0648\\u0627\\u0644\\u0627\\u062d\\u062a\\u064a\\u0627\\u0644 \\u0648\\u0627\\u0644\\u0628\\u0631\\u064a\\u062f \\u0627\\u0644\\u0639\\u0634\\u0648\\u0627\\u0626\\u064a \\u0648\\u0627\\u0644\\u0623\\u0646\\u0634\\u0637\\u0629 \\u063a\\u064a\\u0631 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\u0644\\u0636\\u0645\\u0627\\u0646 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0628\\u0627\\u0644\\u0637\\u0631\\u064a\\u0642\\u0629 \\u0627\\u0644\\u0623\\u0643\\u062b\\u0631 \\u0641\\u0639\\u0627\\u0644\\u064a\\u0629 \\u0644\\u0643 \\u0648\\u0644\\u062c\\u0647\\u0627\\u0632\\u0643.\\r\\n\\u0644\\u062a\\u0639\\u0632\\u064a\\u0632 \\u0633\\u0644\\u0627\\u0645\\u0629 \\u0648\\u0623\\u0645\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0645\\u0633\\u062d \\u0648\\u062a\\u062d\\u0644\\u064a\\u0644 \\u0648\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0648\\u0627\\u0644\\u0631\\u0633\\u0627\\u0626\\u0644 \\u0648\\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0648\\u0635\\u0641\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0631\\u062a\\u0628\\u0637\\u0629 \\u0628\\u0647\\u0627 \\u0628\\u062d\\u062b\\u064b\\u0627 \\u0639\\u0646 \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643\\u0627\\u062a \\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0623\\u0648 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0644\\u062a\\u0633\\u0647\\u064a\\u0644 \\u0627\\u0644\\u0623\\u0628\\u062d\\u0627\\u062b \\u0627\\u0644\\u062a\\u064a \\u064a\\u062c\\u0631\\u064a\\u0647\\u0627 \\u0628\\u0627\\u062d\\u062b\\u0648\\u0646 \\u0645\\u0633\\u062a\\u0642\\u0644\\u0648\\u0646 \\u064a\\u0633\\u062a\\u0648\\u0641\\u0648\\u0646 \\u0645\\u0639\\u0627\\u064a\\u064a\\u0631 \\u0645\\u0639\\u064a\\u0646\\u0629.\\r\\n\\u0644\\u0644\\u062a\\u062d\\u0642\\u0642 \\u0645\\u0646 \\u0647\\u0648\\u064a\\u062a\\u0643 \\u0623\\u0648 \\u0639\\u0645\\u0631\\u0643.\\r\\n\\u0644\\u0644\\u062a\\u0648\\u0627\\u0635\\u0644 \\u0645\\u0639\\u0643\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0625\\u062e\\u0637\\u0627\\u0631\\u0643 \\u0628\\u0627\\u0644\\u062a\\u063a\\u064a\\u064a\\u0631\\u0627\\u062a \\u0641\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a\\u0646\\u0627.\\r\\n\\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646 \\u0639\\u0646 \\u0641\\u0627\\u0626\\u0632\\u0643 \\u0641\\u064a \\u0645\\u0633\\u0627\\u0628\\u0642\\u0627\\u062a\\u0646\\u0627 \\u0623\\u0648 \\u0639\\u0631\\u0648\\u0636\\u0646\\u0627 \\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c\\u064a\\u0629 \\u0625\\u0630\\u0627 \\u0633\\u0645\\u062d\\u062a \\u0628\\u0630\\u0644\\u0643 \\u0642\\u0627\\u0639\\u062f\\u0629 \\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c\\u060c \\u0648\\u0625\\u0631\\u0633\\u0627\\u0644 \\u0623\\u064a \\u062c\\u0648\\u0627\\u0626\\u0632 \\u0633\\u0627\\u0631\\u064a\\u0629 \\u0625\\u0644\\u064a\\u0643.\\r\\n\\u0644\\u062a\\u0646\\u0641\\u064a\\u0630 \\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0648\\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0628\\u0645\\u0627 \\u064a\\u062a\\u0648\\u0627\\u0641\\u0642 \\u0645\\u0639 \\u0623\\u0630\\u0648\\u0646\\u0627\\u062a\\u0643\\u060c \\u0644\\u062a\\u0632\\u0648\\u064a\\u062f\\u0643 \\u0628\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0646\\u062f\\u0629 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0648\\u0642\\u0639\\u060c \\u0645\\u062b\\u0644 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u064a\\u0627\\u062a \\u0627\\u0644\\u0645\\u062e\\u0635\\u0635\\u0629 \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649.\\r\\n\\u0644\\u062a\\u062f\\u0631\\u064a\\u0628 \\u0627\\u0644\\u062a\\u0643\\u0646\\u0648\\u0644\\u0648\\u062c\\u064a\\u0627 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0648\\u062a\\u062d\\u0633\\u064a\\u0646\\u0647\\u0627\\u060c \\u0645\\u062b\\u0644 \\u0646\\u0645\\u0627\\u0630\\u062c \\u0627\\u0644\\u062a\\u0639\\u0644\\u0645 \\u0627\\u0644\\u0622\\u0644\\u064a \\u0648\\u0627\\u0644\\u062e\\u0648\\u0627\\u0631\\u0632\\u0645\\u064a\\u0627\\u062a.\\r\\n\\u0644\\u062a\\u0633\\u0647\\u064a\\u0644 \\u0648\\u062a\\u0646\\u0641\\u064a\\u0630 \\u0627\\u0644\\u0645\\u0628\\u064a\\u0639\\u0627\\u062a \\u0648\\u0627\\u0644\\u062a\\u0631\\u0648\\u064a\\u062c \\u0648\\u0634\\u0631\\u0627\\u0621 \\u0627\\u0644\\u0633\\u0644\\u0639 \\u0648\\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0648\\u062a\\u0642\\u062f\\u064a\\u0645 \\u062f\\u0639\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645.\\r\\n\\u0643\\u064a\\u0641 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0623\\u0637\\u0631\\u0627\\u0641 \\u0627\\u0644\\u062a\\u0627\\u0644\\u064a\\u0629:\\r\\n\\u0634\\u0631\\u0643\\u0627\\u0621 \\u0627\\u0644\\u0639\\u0645\\u0644\\r\\n\\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0627\\u0644\\u062a\\u0633\\u062c\\u064a\\u0644 \\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0628\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u062d\\u0633\\u0627\\u0628 \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 (\\u0645\\u062b\\u0644 Google)\\u060c \\u0641\\u0633\\u0648\\u0641 \\u062a\\u0632\\u0648\\u062f\\u0646\\u0627 \\u0623\\u0648 \\u062a\\u0633\\u0645\\u062d \\u0644\\u0634\\u0628\\u0643\\u062a\\u0643 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0628\\u062a\\u0632\\u0648\\u064a\\u062f\\u0646\\u0627 \\u0628\\u0631\\u0642\\u0645 \\u0647\\u0627\\u062a\\u0641\\u0643 \\u0648\\u0639\\u0646\\u0648\\u0627\\u0646 \\u0628\\u0631\\u064a\\u062f\\u0643 \\u0627\\u0644\\u0625\\u0644\\u0643\\u062a\\u0631\\u0648\\u0646\\u064a \\u0648\\u0627\\u0633\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0648\\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0627\\u0644\\u0639\\u0627\\u0645. \\u0633\\u0646\\u0642\\u0648\\u0645 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0645\\u0639 \\u0627\\u0644\\u0634\\u0628\\u0643\\u0629 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u0629 \\u0630\\u0627\\u062a \\u0627\\u0644\\u0635\\u0644\\u0629 \\u0645\\u062b\\u0644 \\u0645\\u0639\\u0631\\u0641 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0648\\u0631\\u0645\\u0632 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0648\\u0639\\u0646\\u0648\\u0627\\u0646 URL \\u0627\\u0644\\u0645\\u0631\\u062c\\u0639\\u064a. \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0627\\u0644\\u0633\\u0645\\u0627\\u062d \\u0644\\u062e\\u062f\\u0645\\u0629 \\u0637\\u0631\\u0641 \\u062b\\u0627\\u0644\\u062b \\u0628\\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628\\u0643\\u060c \\u0641\\u0633\\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0639\\u0646\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0637\\u0631\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b. \\u0627\\u0639\\u062a\\u0645\\u0627\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0623\\u0630\\u0648\\u0646\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0645\\u0646\\u062d\\u0647\\u0627\\u060c \\u0642\\u062f \\u064a\\u062a\\u0645\\u0643\\u0646 \\u0627\\u0644\\u0637\\u0631\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b \\u0645\\u0646 \\u0627\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062e\\u062a\\u0627\\u0631 \\u062a\\u0642\\u062f\\u064a\\u0645\\u0647\\u0627.\\r\\n\\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u062e\\u062a\\u0627\\u0631 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0639\\u0644\\u0649 \\u0645\\u0646\\u0635\\u0627\\u062a \\u0627\\u0644\\u062a\\u0648\\u0627\\u0635\\u0644 \\u0627\\u0644\\u0627\\u062c\\u062a\\u0645\\u0627\\u0639\\u064a\\u060c \\u0633\\u062a\\u062a\\u0645 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0648\\u0627\\u0633\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0648\\u0627\\u0644\\u0646\\u0635 \\u0627\\u0644\\u0645\\u0635\\u0627\\u062d\\u0628 \\u0639\\u0644\\u0649 \\u062a\\u0644\\u0643 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0623\\u0648\\u060c \\u0641\\u064a \\u062d\\u0627\\u0644\\u0629 \\u0627\\u0644\\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0639\\u0628\\u0631 \\u0645\\u0646\\u0635\\u0627\\u062a \\u0627\\u0644\\u0645\\u0631\\u0627\\u0633\\u0644\\u0629 \\u0627\\u0644\\u0641\\u0648\\u0631\\u064a\\u0629 \\u0645\\u062b\\u0644 Whatsapp\\u060c \\u0633\\u062a\\u062a\\u0645 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0631\\u0627\\u0628\\u0637 \\u0644\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649.\\r\\n\\u0645\\u0642\\u062f\\u0645\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u0642\\u062f\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0644\\u0645\\u0642\\u062f\\u0645\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0630\\u064a\\u0646 \\u064a\\u062f\\u0639\\u0645\\u0648\\u0646 \\u0623\\u0639\\u0645\\u0627\\u0644\\u0646\\u0627\\u060c \\u0645\\u062b\\u0644 \\u0645\\u0642\\u062f\\u0645\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0633\\u062d\\u0627\\u0628\\u064a\\u0629 \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0625\\u0634\\u0631\\u0627\\u0641 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0644\\u0644\\u062a\\u0623\\u0643\\u062f \\u0645\\u0646 \\u0623\\u0646 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0645\\u0643\\u0627\\u0646 \\u0622\\u0645\\u0646 \\u0648\\u0645\\u0645\\u062a\\u0639 \\u0648\\u0645\\u0642\\u062f\\u0645\\u0648 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0630\\u064a\\u0646 \\u064a\\u0633\\u0627\\u0639\\u062f\\u0648\\u0646\\u0646\\u0627 \\u0641\\u064a \\u062a\\u0633\\u0648\\u064a\\u0642 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629.\\r\\n\\u0645\\u0639\\u0627\\u0644\\u062c\\u0648 \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u0645\\u0642\\u062f\\u0645\\u0648 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0625\\u062a\\u0645\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a: \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0634\\u0631\\u0627\\u0621 \\u0627\\u0644\\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0645\\u0639\\u062f\\u0646\\u064a\\u0629 \\u0623\\u0648 \\u0625\\u062c\\u0631\\u0627\\u0621 \\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a \\u0623\\u062e\\u0631\\u0649 \\u0645\\u062a\\u0639\\u0644\\u0642\\u0629 \\u0628\\u0627\\u0644\\u062f\\u0641\\u0639\\u060c \\u0641\\u0633\\u0646\\u0634\\u0627\\u0631\\u0643 \\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0645\\u0639 \\u0645\\u0632\\u0648\\u062f \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0630\\u064a \\u0627\\u0644\\u0635\\u0644\\u0629 \\u0644\\u062a\\u0633\\u0647\\u064a\\u0644 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0629. \\u0628\\u0627\\u0644\\u0646\\u0633\\u0628\\u0629 \\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0645\\u0639\\u062f\\u0646\\u064a\\u0629\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0631\\u0641 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0629 \\u0644\\u062a\\u0645\\u0643\\u064a\\u0646\\u0646\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u062a\\u0639\\u0631\\u0641 \\u0639\\u0644\\u064a\\u0643 \\u0648\\u0625\\u0636\\u0627\\u0641\\u0629 \\u0627\\u0644\\u0642\\u064a\\u0645\\u0629 \\u0627\\u0644\\u0635\\u062d\\u064a\\u062d\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0639\\u0645\\u0644\\u0627\\u062a \\u0627\\u0644\\u0645\\u0639\\u062f\\u0646\\u064a\\u0629 \\u0625\\u0644\\u0649 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0628\\u0645\\u062c\\u0631\\u062f \\u0642\\u064a\\u0627\\u0645\\u0643 \\u0628\\u0627\\u0644\\u062f\\u0641\\u0639.\\r\\n\\u0645\\u0648\\u0641\\u0631\\u0648 \\u0627\\u0644\\u062a\\u062d\\u0644\\u064a\\u0644\\u0627\\u062a: \\u0646\\u062d\\u0646 \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0648\\u0641\\u0631\\u064a \\u0627\\u0644\\u062a\\u062d\\u0644\\u064a\\u0644\\u0627\\u062a \\u0644\\u0645\\u0633\\u0627\\u0639\\u062f\\u062a\\u0646\\u0627 \\u0641\\u064a \\u062a\\u062d\\u0633\\u064a\\u0646 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u062a\\u062d\\u0633\\u064a\\u0646\\u0647. \\u0643\\u0645\\u0627 \\u064a\\u0633\\u0627\\u0639\\u062f\\u0646\\u0627 \\u0645\\u0648\\u0641\\u0631\\u0648 \\u0627\\u0644\\u062a\\u062d\\u0644\\u064a\\u0644\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0631\\u062c\\u064a\\u0648\\u0646 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0641\\u064a \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0647\\u062f\\u0641\\u0629.\\r\\n\\u0628\\u0627\\u062d\\u062b\\u0648\\u0646 \\u0645\\u0633\\u062a\\u0642\\u0644\\u0648\\u0646\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u0639 \\u0628\\u0627\\u062d\\u062b\\u064a\\u0646 \\u0645\\u0633\\u062a\\u0642\\u0644\\u064a\\u0646 \\u0644\\u062a\\u0633\\u0647\\u064a\\u0644 \\u0627\\u0644\\u0628\\u062d\\u062b \\u0627\\u0644\\u0630\\u064a \\u064a\\u0633\\u062a\\u0648\\u0641\\u064a \\u0645\\u0639\\u0627\\u064a\\u064a\\u0631 \\u0645\\u0639\\u064a\\u0646\\u0629.\\r\\n\\u0645\\u062c\\u0645\\u0648\\u0639\\u0629 \\u0634\\u0631\\u0643\\u0627\\u062a\\u0646\\u0627\\r\\n\\u064a\\u062c\\u0648\\u0632 \\u0644\\u0646\\u0627 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0645\\u0634\\u0627\\u0631\\u0643\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u0639 \\u0627\\u0644\\u0623\\u0639\\u0636\\u0627\\u0621 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0645\\u062c\\u0645\\u0648\\u0639\\u0629 \\u0634\\u0631\\u0643\\u0627\\u062a\\u0646\\u0627\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u062a\\u0648\\u0641\\u064a\\u0631 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u062a\\u062d\\u0633\\u064a\\u0646 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0648\\u062a\\u062d\\u0633\\u064a\\u0646\\u0647 \\u0648\\u0645\\u0646\\u0639 \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u063a\\u064a\\u0631 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a \\u0648\\u062f\\u0639\\u0645 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646.\\r\\n\\r\\n\\u0644\\u0623\\u0633\\u0628\\u0627\\u0628 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629\\r\\n\\u0633\\u0646\\u0634\\u0627\\u0631\\u0643 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u0639 \\u0648\\u0643\\u0627\\u0644\\u0627\\u062a \\u0625\\u0646\\u0641\\u0627\\u0630 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646 \\u0623\\u0648 \\u0627\\u0644\\u0633\\u0644\\u0637\\u0627\\u062a \\u0627\\u0644\\u0639\\u0627\\u0645\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0646\\u0638\\u0645\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0630\\u0644\\u0643 \\u0645\\u0637\\u0644\\u0648\\u0628\\u064b\\u0627 \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u064b\\u0627\\u060c \\u0623\\u0648 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0636\\u0631\\u0648\\u0631\\u064a\\u064b\\u0627 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0639\\u0642\\u0648\\u0644 \\u0645\\u0646 \\u0623\\u062c\\u0644:\\r\\n\\u0627\\u0644\\u0627\\u0645\\u062a\\u062b\\u0627\\u0644 \\u0644\\u0644\\u0627\\u0644\\u062a\\u0632\\u0627\\u0645 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a \\u0623\\u0648 \\u0627\\u0644\\u0639\\u0645\\u0644\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0637\\u0644\\u0628\\u061b\\r\\n\\u0625\\u0646\\u0641\\u0627\\u0630 \\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0648\\u0627\\u0644\\u0627\\u062a\\u0641\\u0627\\u0642\\u064a\\u0627\\u062a \\u0648\\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0639\\u0627\\u064a\\u064a\\u0631 \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649\\u060c \\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u0627\\u0644\\u062a\\u062d\\u0642\\u064a\\u0642 \\u0641\\u064a \\u0623\\u064a \\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0645\\u062d\\u062a\\u0645\\u0644 \\u0644\\u0647\\u0627\\u061b\\r\\n\\u0627\\u0643\\u062a\\u0634\\u0627\\u0641 \\u0623\\u0648 \\u0645\\u0646\\u0639 \\u0623\\u0648 \\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0627\\u0644\\u0645\\u0634\\u0643\\u0644\\u0627\\u062a \\u0627\\u0644\\u0623\\u0645\\u0646\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0627\\u062d\\u062a\\u064a\\u0627\\u0644\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0641\\u0646\\u064a\\u0629\\u061b \\u0623\\u0648\\r\\n\\u062d\\u0645\\u0627\\u064a\\u0629 \\u062d\\u0642\\u0648\\u0642\\u0646\\u0627 \\u0623\\u0648 \\u0645\\u0645\\u062a\\u0644\\u0643\\u0627\\u062a\\u0646\\u0627 \\u0623\\u0648 \\u0633\\u0644\\u0627\\u0645\\u062a\\u0646\\u0627 \\u0623\\u0648 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646\\u0627 \\u0623\\u0648 \\u0627\\u0644\\u0637\\u0631\\u0641 \\u0627\\u0644\\u062b\\u0627\\u0644\\u062b \\u0623\\u0648 \\u0627\\u0644\\u062c\\u0645\\u0647\\u0648\\u0631 \\u0648\\u0641\\u0642\\u064b\\u0627 \\u0644\\u0645\\u0627 \\u064a\\u0642\\u062a\\u0636\\u064a\\u0647 \\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646 \\u0623\\u0648 \\u064a\\u0633\\u0645\\u062d \\u0628\\u0647 (\\u0628\\u0645\\u0627 \\u0641\\u064a \\u0630\\u0644\\u0643 \\u062a\\u0628\\u0627\\u062f\\u0644 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0639 \\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0648\\u0627\\u0644\\u0645\\u0624\\u0633\\u0633\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u0627\\u0644\\u062d\\u0645\\u0627\\u064a\\u0629 \\u0645\\u0646 \\u0627\\u0644\\u0627\\u062d\\u062a\\u064a\\u0627\\u0644 \\u0648\\u0627\\u0644\\u062d\\u062f \\u0645\\u0646 \\u0645\\u062e\\u0627\\u0637\\u0631 \\u0627\\u0644\\u0627\\u0626\\u062a\\u0645\\u0627\\u0646).\\r\\n\\u0627\\u0644\\u0645\\u0644\\u0641\\u0627\\u062a \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629 \\u0627\\u0644\\u0639\\u0627\\u0645\\u0629\\r\\n\\u064a\\u0631\\u062c\\u0649 \\u0645\\u0644\\u0627\\u062d\\u0638\\u0629 \\u0623\\u0646\\u0647 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0639\\u0627\\u0645\\u064b\\u0627\\u060c \\u0641\\u0633\\u064a\\u0643\\u0648\\u0646 \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0628\\u0643 \\u0645\\u0631\\u0626\\u064a\\u064b\\u0627 \\u0644\\u0623\\u064a \\u0634\\u062e\\u0635 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u064a\\u0645\\u0643\\u0646 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u064a\\u0647 \\u0623\\u0648 \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647 \\u0645\\u0646 \\u0642\\u0628\\u0644 \\u0623\\u0635\\u062f\\u0642\\u0627\\u0626\\u0643 \\u0648\\u0645\\u062a\\u0627\\u0628\\u0639\\u064a\\u0643 \\u0648\\u0643\\u0630\\u0644\\u0643 \\u0627\\u0644\\u062c\\u0647\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0631\\u062c\\u064a\\u0629 \\u0645\\u062b\\u0644 \\u0645\\u062d\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u0628\\u062d\\u062b \\u0648\\u0645\\u062c\\u0645\\u0639\\u064a \\u0627\\u0644\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0648\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0623\\u062e\\u0628\\u0627\\u0631. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u063a\\u064a\\u064a\\u0631 \\u0645\\u0646 \\u064a\\u0645\\u0643\\u0646\\u0647 \\u0645\\u0634\\u0627\\u0647\\u062f\\u0629 \\u0645\\u0642\\u0637\\u0639 \\u0641\\u064a\\u062f\\u064a\\u0648 \\u0641\\u064a \\u0643\\u0644 \\u0645\\u0631\\u0629 \\u062a\\u0642\\u0648\\u0645 \\u0641\\u064a\\u0647\\u0627 \\u0628\\u062a\\u062d\\u0645\\u064a\\u0644 \\u0645\\u0642\\u0637\\u0639 \\u0641\\u064a\\u062f\\u064a\\u0648. \\u0648\\u0628\\u062f\\u0644\\u0627\\u064b \\u0645\\u0646 \\u0630\\u0644\\u0643\\u060c \\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u063a\\u064a\\u064a\\u0631 \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0648\\u0636\\u0639 \\u0627\\u0644\\u0627\\u0641\\u062a\\u0631\\u0627\\u0636\\u064a \\u0627\\u0644\\u062e\\u0627\\u0635 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u062a\\u063a\\u064a\\u064a\\u0631 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a\\u0643 \\u0625\\u0644\\u0649 \\\"\\u062d\\u0633\\u0627\\u0628 \\u062e\\u0627\\u0635\\\" \\u0641\\u064a \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\\"\\u0625\\u062f\\u0627\\u0631\\u0629 \\u062d\\u0633\\u0627\\u0628\\u064a\\\".\\r\\n\\u0627\\u0644\\u062a\\u062c\\u0627\\u0631 \\u0648\\u0645\\u0642\\u062f\\u0645\\u0648 \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a \\u0648\\u0645\\u0642\\u062f\\u0645\\u0648 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0622\\u062e\\u0631\\u0648\\u0646\\r\\n\\u0639\\u0646\\u062f \\u0625\\u062c\\u0631\\u0627\\u0621 \\u0639\\u0645\\u0644\\u064a\\u0629 \\u0634\\u0631\\u0627\\u0621 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0645\\u064a\\u0632\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0648\\u0642 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0646\\u0627\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u062a\\u0639\\u0644\\u0642\\u0629 \\u0628\\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0629 \\u0645\\u0639 \\u0627\\u0644\\u062a\\u0627\\u062c\\u0631 \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u062f\\u0641\\u0639 \\u0648\\u0625\\u062a\\u0645\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0627\\u0645\\u0644\\u0627\\u062a \\u0648\\u0645\\u0642\\u062f\\u0645\\u064a \\u0627\\u0644\\u062e\\u062f\\u0645\\u0627\\u062a \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0633\\u0648\\u0641 \\u0646\\u0634\\u0627\\u0631\\u0643 \\u0639\\u0646\\u0627\\u0635\\u0631 \\u0627\\u0644\\u0637\\u0644\\u0628 \\u0648\\u062a\\u0641\\u0627\\u0635\\u064a\\u0644 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0648\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645 \\u062d\\u062a\\u0649 \\u064a\\u0645\\u0643\\u0646 \\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0637\\u0644\\u0628\\u0643. \\u064a\\u062c\\u0648\\u0632 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0643\\u064a\\u0627\\u0646\\u0627\\u062a \\u0627\\u0633\\u062a\\u062e\\u062f\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0645\\u062a \\u0645\\u0634\\u0627\\u0631\\u0643\\u062a\\u0647\\u0627 \\u0648\\u0641\\u0642\\u064b\\u0627 \\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0647\\u0627.\\r\\n\\u062d\\u064a\\u062b \\u0646\\u0642\\u0648\\u0645 \\u0628\\u062a\\u062e\\u0632\\u064a\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643\\r\\n\\u0642\\u062f \\u064a\\u062a\\u0645 \\u062a\\u062e\\u0632\\u064a\\u0646 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0639\\u0644\\u0649 \\u062e\\u0648\\u0627\\u062f\\u0645 \\u0645\\u0648\\u062c\\u0648\\u062f\\u0629 \\u062e\\u0627\\u0631\\u062c \\u0627\\u0644\\u0628\\u0644\\u062f \\u0627\\u0644\\u0630\\u064a \\u062a\\u0639\\u064a\\u0634 \\u0641\\u064a\\u0647\\u060c \\u0645\\u062b\\u0644 \\u0633\\u0646\\u063a\\u0627\\u0641\\u0648\\u0631\\u0629 \\u0648\\u0645\\u0627\\u0644\\u064a\\u0632\\u064a\\u0627 \\u0648\\u0623\\u064a\\u0631\\u0644\\u0646\\u062f\\u0627 \\u0648\\u0627\\u0644\\u0648\\u0644\\u0627\\u064a\\u0627\\u062a \\u0627\\u0644\\u0645\\u062a\\u062d\\u062f\\u0629. \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0628\\u062e\\u0648\\u0627\\u062f\\u0645 \\u0631\\u0626\\u064a\\u0633\\u064a\\u0629 \\u062d\\u0648\\u0644 \\u0627\\u0644\\u0639\\u0627\\u0644\\u0645 \\u0644\\u0646\\u0642\\u062f\\u0645 \\u0644\\u0643 \\u062e\\u062f\\u0645\\u0627\\u062a\\u0646\\u0627 \\u0639\\u0627\\u0644\\u0645\\u064a\\u064b\\u0627 \\u0648\\u0628\\u0634\\u0643\\u0644 \\u0645\\u0633\\u062a\\u0645\\u0631.\\r\\n\\u062d\\u0642\\u0648\\u0642\\u0643 \\u0648\\u0627\\u062e\\u062a\\u064a\\u0627\\u0631\\u0627\\u062a\\u0643\\r\\n\\u0644\\u062f\\u064a\\u0643 \\u062d\\u0642\\u0648\\u0642 \\u0648\\u062e\\u064a\\u0627\\u0631\\u0627\\u062a \\u0639\\u0646\\u062f\\u0645\\u0627 \\u064a\\u062a\\u0639\\u0644\\u0642 \\u0627\\u0644\\u0623\\u0645\\u0631 \\u0628\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643. \\u0642\\u062f \\u064a\\u062a\\u0645 \\u0645\\u0646\\u062d\\u0643 \\u062d\\u0642\\u0648\\u0642\\u064b\\u0627 \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0628\\u0645\\u0648\\u062c\\u0628 \\u0627\\u0644\\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0645\\u0648\\u0644 \\u0628\\u0647\\u0627\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u0642\\u062f \\u062a\\u0634\\u0645\\u0644 \\u0627\\u0644\\u062d\\u0642 \\u0641\\u064a \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\\u0643 \\u0623\\u0648 \\u062d\\u0630\\u0641\\u0647\\u0627 \\u0623\\u0648 \\u062a\\u062d\\u062f\\u064a\\u062b\\u0647\\u0627 \\u0623\\u0648 \\u062a\\u0635\\u062d\\u064a\\u062d\\u0647\\u0627\\u060c \\u0648\\u0625\\u0628\\u0644\\u0627\\u063a\\u0643 \\u0628\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\\u0643\\u060c \\u0648\\u062a\\u0642\\u062f\\u064a\\u0645 \\u0634\\u0643\\u0627\\u0648\\u0649 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0633\\u0644\\u0637\\u0627\\u062a\\u060c \\u0648\\u0631\\u0628\\u0645\\u0627 \\u062d\\u0642\\u0648\\u0642 \\u0623\\u062e\\u0631\\u0649. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0637\\u0644\\u0628 \\u0644\\u0645\\u0645\\u0627\\u0631\\u0633\\u0629 \\u062d\\u0642\\u0648\\u0642\\u0643 \\u0628\\u0645\\u0648\\u062c\\u0628 \\u0627\\u0644\\u0642\\u0648\\u0627\\u0646\\u064a\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0645\\u0648\\u0644 \\u0628\\u0647\\u0627.\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0633\\u062a\\u0626\\u0646\\u0627\\u0641 \\u0623\\u064a \\u0642\\u0631\\u0627\\u0631 \\u0627\\u062a\\u062e\\u0630\\u0646\\u0627\\u0647 \\u0628\\u0634\\u0623\\u0646 \\u0637\\u0644\\u0628\\u0643 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u062a\\u0628\\u0627\\u0639 \\u0627\\u0644\\u062a\\u0639\\u0644\\u064a\\u0645\\u0627\\u062a \\u0627\\u0644\\u0648\\u0627\\u0631\\u062f\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0631\\u0633\\u0627\\u0644\\u0629 \\u0627\\u0644\\u062a\\u064a \\u062a\\u062a\\u0644\\u0642\\u0627\\u0647\\u0627 \\u0645\\u0646\\u0627 \\u0644\\u0625\\u0639\\u0644\\u0627\\u0645\\u0643 \\u0628\\u0642\\u0631\\u0627\\u0631\\u0646\\u0627. \\u064a\\u0631\\u062c\\u0649 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u0644\\u0627\\u0637\\u0644\\u0627\\u0639 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062a\\u0643\\u0645\\u064a\\u0644\\u064a\\u0629 \\u0623\\u062f\\u0646\\u0627\\u0647 \\u0644\\u0645\\u0639\\u0631\\u0641\\u0629 \\u0645\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0647\\u0646\\u0627\\u0643 \\u0645\\u0645\\u062b\\u0644 \\u0645\\u062d\\u0644\\u064a \\u0623\\u0648 \\u062c\\u0647\\u0629 \\u0627\\u062a\\u0635\\u0627\\u0644 \\u0645\\u062d\\u0644\\u064a\\u0629 \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0644\\u0628\\u0644\\u062f\\u0643.\\r\\n\\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u0648\\u0635\\u0648\\u0644 \\u0625\\u0644\\u0649 \\u0645\\u0639\\u0638\\u0645 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0648\\u062a\\u0639\\u062f\\u064a\\u0644\\u0647\\u0627 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u062a\\u0633\\u062c\\u064a\\u0644 \\u0627\\u0644\\u062f\\u062e\\u0648\\u0644 \\u0625\\u0644\\u0649 Vlog. \\u064a\\u0645\\u0643\\u0646\\u0643 \\u062d\\u0630\\u0641 \\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0630\\u064a \\u0642\\u0645\\u062a \\u0628\\u062a\\u062d\\u0645\\u064a\\u0644\\u0647. \\u0646\\u0648\\u0641\\u0631 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0639\\u062f\\u062f\\u064b\\u0627 \\u0645\\u0646 \\u0627\\u0644\\u0623\\u062f\\u0648\\u0627\\u062a \\u0641\\u064a \\u0627\\u0644\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u062a\\u0633\\u0645\\u062d \\u0644\\u0643 \\u0628\\u0627\\u0644\\u062a\\u062d\\u0643\\u0645\\u060c \\u0645\\u0646 \\u0628\\u064a\\u0646 \\u0623\\u0645\\u0648\\u0631 \\u0623\\u062e\\u0631\\u0649\\u060c \\u0641\\u064a \\u0645\\u0646 \\u064a\\u0645\\u0643\\u0646\\u0647 \\u0645\\u0634\\u0627\\u0647\\u062f\\u0629 \\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0623\\u0648 \\u0625\\u0631\\u0633\\u0627\\u0644 \\u0631\\u0633\\u0627\\u0626\\u0644 \\u0625\\u0644\\u064a\\u0643 \\u0623\\u0648 \\u0646\\u0634\\u0631 \\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0639\\u0644\\u0649 \\u0645\\u0642\\u0627\\u0637\\u0639 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643. \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0627\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643\\u060c \\u0641\\u064a\\u0645\\u0643\\u0646\\u0643 \\u062d\\u0630\\u0641 \\u062d\\u0633\\u0627\\u0628\\u0643 \\u0628\\u0627\\u0644\\u0643\\u0627\\u0645\\u0644 \\u0641\\u064a \\u0627\\u0644\\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a.\\r\\n\\u0642\\u062f \\u062a\\u062a\\u0645\\u0643\\u0646 \\u0645\\u0646 \\u0631\\u0641\\u0636 \\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637 \\u0623\\u0648 \\u062a\\u0639\\u0637\\u064a\\u0644\\u0647\\u0627 \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0636\\u0628\\u0637 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u0645\\u062a\\u0635\\u0641\\u062d \\u062c\\u0647\\u0627\\u0632\\u0643. \\u0648\\u0646\\u0638\\u0631\\u064b\\u0627 \\u0644\\u0627\\u062e\\u062a\\u0644\\u0627\\u0641 \\u0643\\u0644 \\u0645\\u062a\\u0635\\u0641\\u062d\\u060c \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0631\\u062c\\u0648\\u0639 \\u0625\\u0644\\u0649 \\u0627\\u0644\\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u062a\\u064a \\u064a\\u0648\\u0641\\u0631\\u0647\\u0627 \\u0645\\u062a\\u0635\\u0641\\u062d\\u0643. \\u064a\\u0631\\u062c\\u0649 \\u0645\\u0644\\u0627\\u062d\\u0638\\u0629 \\u0623\\u0646\\u0643 \\u0642\\u062f \\u062a\\u062d\\u062a\\u0627\\u062c \\u0625\\u0644\\u0649 \\u0627\\u062a\\u062e\\u0627\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0625\\u0636\\u0627\\u0641\\u064a\\u0629 \\u0644\\u0631\\u0641\\u0636 \\u0623\\u0648 \\u062a\\u0639\\u0637\\u064a\\u0644 \\u0623\\u0646\\u0648\\u0627\\u0639 \\u0645\\u0639\\u064a\\u0646\\u0629 \\u0645\\u0646 \\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0646\\u0638\\u0631\\u064b\\u0627 \\u0644\\u0644\\u0627\\u062e\\u062a\\u0644\\u0627\\u0641\\u0627\\u062a \\u0641\\u064a \\u0643\\u064a\\u0641\\u064a\\u0629 \\u0639\\u0645\\u0644 \\u0627\\u0644\\u0645\\u062a\\u0635\\u0641\\u062d\\u0627\\u062a \\u0648\\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062d\\u0645\\u0648\\u0644\\u060c \\u0642\\u062f \\u062a\\u062d\\u062a\\u0627\\u062c \\u0625\\u0644\\u0649 \\u0627\\u062a\\u062e\\u0627\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0645\\u062e\\u062a\\u0644\\u0641\\u0629 \\u0644\\u0625\\u0644\\u063a\\u0627\\u0621 \\u0627\\u0644\\u0627\\u0634\\u062a\\u0631\\u0627\\u0643 \\u0641\\u064a \\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u0629 \\u0644\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0647\\u062f\\u0641\\u0629 \\u0641\\u064a \\u0627\\u0644\\u0645\\u062a\\u0635\\u0641\\u062d \\u0648\\u0625\\u0644\\u063a\\u0627\\u0621 \\u0627\\u0644\\u0627\\u0634\\u062a\\u0631\\u0627\\u0643 \\u0641\\u064a \\u0627\\u0644\\u0625\\u0639\\u0644\\u0627\\u0646\\u0627\\u062a \\u0627\\u0644\\u0645\\u0633\\u062a\\u0647\\u062f\\u0641\\u0629 \\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062d\\u0645\\u0648\\u0644\\u060c \\u0648\\u0627\\u0644\\u062a\\u064a \\u064a\\u0645\\u0643\\u0646\\u0643 \\u0627\\u0644\\u062a\\u062d\\u0643\\u0645 \\u0641\\u064a\\u0647\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0625\\u0639\\u062f\\u0627\\u062f\\u0627\\u062a \\u062c\\u0647\\u0627\\u0632\\u0643 \\u0623\\u0648 \\u0623\\u0630\\u0648\\u0646\\u0627\\u062a \\u062a\\u0637\\u0628\\u064a\\u0642\\u0627\\u062a \\u0627\\u0644\\u0647\\u0627\\u062a\\u0641 \\u0627\\u0644\\u0645\\u062d\\u0645\\u0648\\u0644. \\u0628\\u0627\\u0644\\u0625\\u0636\\u0627\\u0641\\u0629 \\u0625\\u0644\\u0649 \\u0630\\u0644\\u0643\\u060c \\u064a\\u0643\\u0648\\u0646 \\u0627\\u062e\\u062a\\u064a\\u0627\\u0631 \\u0625\\u0644\\u063a\\u0627\\u0621 \\u0627\\u0644\\u0627\\u0634\\u062a\\u0631\\u0627\\u0643 \\u062e\\u0627\\u0635\\u064b\\u0627 \\u0628\\u0627\\u0644\\u0645\\u062a\\u0635\\u0641\\u062d \\u0623\\u0648 \\u0627\\u0644\\u062c\\u0647\\u0627\\u0632 \\u0627\\u0644\\u0645\\u0639\\u064a\\u0646 \\u0627\\u0644\\u0630\\u064a \\u062a\\u0633\\u062a\\u062e\\u062f\\u0645\\u0647 \\u0639\\u0646\\u062f \\u0625\\u0644\\u063a\\u0627\\u0621 \\u0627\\u0644\\u0627\\u0634\\u062a\\u0631\\u0627\\u0643\\u060c \\u0644\\u0630\\u0644\\u0643 \\u0642\\u062f \\u062a\\u062d\\u062a\\u0627\\u062c \\u0625\\u0644\\u0649 \\u0625\\u0644\\u063a\\u0627\\u0621 \\u0627\\u0644\\u0627\\u0634\\u062a\\u0631\\u0627\\u0643 \\u0628\\u0634\\u0643\\u0644 \\u0645\\u0646\\u0641\\u0635\\u0644 \\u0644\\u0643\\u0644 \\u0645\\u062a\\u0635\\u0641\\u062d \\u0623\\u0648 \\u062c\\u0647\\u0627\\u0632. \\u0625\\u0630\\u0627 \\u0627\\u062e\\u062a\\u0631\\u062a \\u0631\\u0641\\u0636 \\u0645\\u0644\\u0641\\u0627\\u062a \\u062a\\u0639\\u0631\\u064a\\u0641 \\u0627\\u0644\\u0627\\u0631\\u062a\\u0628\\u0627\\u0637 \\u0623\\u0648 \\u062a\\u0639\\u0637\\u064a\\u0644\\u0647\\u0627 \\u0623\\u0648 \\u062d\\u0630\\u0641\\u0647\\u0627\\u060c \\u0641\\u0642\\u062f \\u0644\\u0627 \\u062a\\u0643\\u0648\\u0646 \\u0628\\u0639\\u0636 \\u0648\\u0638\\u0627\\u0626\\u0641 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0644\\u0643.\\r\\n\\u0623\\u0645\\u0627\\u0646 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u062a\\u062e\\u0630 \\u062e\\u0637\\u0648\\u0627\\u062a \\u0644\\u0636\\u0645\\u0627\\u0646 \\u0627\\u0644\\u062a\\u0639\\u0627\\u0645\\u0644 \\u0645\\u0639 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0628\\u0634\\u0643\\u0644 \\u0622\\u0645\\u0646 \\u0648\\u0648\\u0641\\u0642\\u064b\\u0627 \\u0644\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0629. \\u0648\\u0644\\u0633\\u0648\\u0621 \\u0627\\u0644\\u062d\\u0638\\u060c \\u0641\\u0625\\u0646 \\u0646\\u0642\\u0644 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0625\\u0646\\u062a\\u0631\\u0646\\u062a \\u0644\\u064a\\u0633 \\u0622\\u0645\\u0646\\u064b\\u0627 \\u062a\\u0645\\u0627\\u0645\\u064b\\u0627. \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0631\\u063a\\u0645 \\u0645\\u0646 \\u0623\\u0646\\u0646\\u0627 \\u0633\\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u062a\\u062f\\u0627\\u0628\\u064a\\u0631 \\u0645\\u0639\\u0642\\u0648\\u0644\\u0629 \\u0644\\u062d\\u0645\\u0627\\u064a\\u0629 \\u0628\\u064a\\u0627\\u0646\\u0627\\u062a\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a\\u0629\\u060c \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646 \\u0637\\u0631\\u064a\\u0642 \\u0627\\u0644\\u062a\\u0634\\u0641\\u064a\\u0631\\u060c \\u0625\\u0644\\u0627 \\u0623\\u0646\\u0646\\u0627 \\u0644\\u0627 \\u0646\\u0633\\u062a\\u0637\\u064a\\u0639 \\u0636\\u0645\\u0627\\u0646 \\u0623\\u0645\\u0627\\u0646 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0627\\u0644\\u0645\\u0646\\u0642\\u0648\\u0644\\u0629 \\u0639\\u0628\\u0631 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u061b \\u0623\\u064a \\u0646\\u0642\\u0644 \\u0647\\u0648 \\u0639\\u0644\\u0649 \\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u062a\\u0643 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629.\\r\\n\\u0644\\u062f\\u064a\\u0646\\u0627 \\u0627\\u0644\\u062a\\u062f\\u0627\\u0628\\u064a\\u0631 \\u0627\\u0644\\u0641\\u0646\\u064a\\u0629 \\u0648\\u0627\\u0644\\u062a\\u0646\\u0638\\u064a\\u0645\\u064a\\u0629 \\u0627\\u0644\\u0645\\u0646\\u0627\\u0633\\u0628\\u0629 \\u0644\\u0636\\u0645\\u0627\\u0646 \\u0645\\u0633\\u062a\\u0648\\u0649 \\u0645\\u0646 \\u0627\\u0644\\u0623\\u0645\\u0627\\u0646 \\u0645\\u0646\\u0627\\u0633\\u0628 \\u0644\\u0644\\u0645\\u062e\\u0627\\u0637\\u0631 \\u0627\\u0644\\u0645\\u062a\\u0641\\u0627\\u0648\\u062a\\u0629 \\u0627\\u0644\\u0627\\u062d\\u062a\\u0645\\u0627\\u0644\\u064a\\u0629 \\u0648\\u0627\\u0644\\u0634\\u062f\\u0629 \\u0639\\u0644\\u0649 \\u062d\\u0642\\u0648\\u0642 \\u0648\\u062d\\u0631\\u064a\\u0627\\u062a\\u0643 \\u0648\\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064a\\u0646 \\u0627\\u0644\\u0622\\u062e\\u0631\\u064a\\u0646. \\u0646\\u062d\\u0646 \\u0646\\u062d\\u0627\\u0641\\u0638 \\u0639\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u062a\\u062f\\u0627\\u0628\\u064a\\u0631 \\u0627\\u0644\\u0641\\u0646\\u064a\\u0629 \\u0648\\u0627\\u0644\\u062a\\u0646\\u0638\\u064a\\u0645\\u064a\\u0629 \\u0648\\u0633\\u0646\\u0642\\u0648\\u0645 \\u0628\\u062a\\u0639\\u062f\\u064a\\u0644\\u0647\\u0627 \\u0645\\u0646 \\u0648\\u0642\\u062a \\u0644\\u0622\\u062e\\u0631 \\u0644\\u062a\\u062d\\u0633\\u064a\\u0646 \\u0627\\u0644\\u0623\\u0645\\u0627\\u0646 \\u0627\\u0644\\u0639\\u0627\\u0645 \\u0644\\u0623\\u0646\\u0638\\u0645\\u062a\\u0646\\u0627.\\r\\n\\u0633\\u0646\\u0642\\u0648\\u0645\\u060c \\u0645\\u0646 \\u0648\\u0642\\u062a \\u0644\\u0622\\u062e\\u0631\\u060c \\u0628\\u062a\\u0636\\u0645\\u064a\\u0646 \\u0631\\u0648\\u0627\\u0628\\u0637 \\u0645\\u0646 \\u0648\\u0625\\u0644\\u0649 \\u0645\\u0648\\u0627\\u0642\\u0639 \\u0627\\u0644\\u0648\\u064a\\u0628 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0634\\u0628\\u0643\\u0627\\u062a\\u0646\\u0627 \\u0627\\u0644\\u0634\\u0631\\u064a\\u0643\\u0629 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0646\\u064a\\u0646 \\u0648\\u0627\\u0644\\u0634\\u0631\\u0643\\u0627\\u062a \\u0627\\u0644\\u062a\\u0627\\u0628\\u0639\\u0629 \\u0644\\u0646\\u0627. \\u0625\\u0630\\u0627 \\u0627\\u062a\\u0628\\u0639\\u062a \\u0631\\u0627\\u0628\\u0637\\u064b\\u0627 \\u0644\\u0623\\u064a \\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0642\\u0639\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0645\\u0644\\u0627\\u062d\\u0638\\u0629 \\u0623\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0642\\u0639 \\u0644\\u062f\\u064a\\u0647\\u0627 \\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u062e\\u0627\\u0635\\u0629 \\u0628\\u0647\\u0627 \\u0648\\u0623\\u0646\\u0646\\u0627 \\u0644\\u0627 \\u0646\\u0642\\u0628\\u0644 \\u0623\\u064a \\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0629\\r\\n\\u0627\\u0644\\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u0645\\u0633\\u0624\\u0648\\u0644\\u064a\\u0629 \\u0639\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a. \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u062a\\u062d\\u0642\\u0642 \\u0645\\u0646 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0642\\u0628\\u0644 \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0623\\u064a \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0625\\u0644\\u0649 \\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0648\\u0627\\u0642\\u0639.\\r\\n\\u0625\\u0644\\u0649 \\u0645\\u062a\\u0649 \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0628\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643\\r\\n\\u0646\\u062d\\u0646 \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0628\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0637\\u0627\\u0644\\u0645\\u0627 \\u0643\\u0627\\u0646 \\u0630\\u0644\\u0643 \\u0636\\u0631\\u0648\\u0631\\u064a\\u064b\\u0627 \\u0644\\u062a\\u0648\\u0641\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0629 \\u0648\\u0644\\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649 \\u0627\\u0644\\u0645\\u0646\\u0635\\u0648\\u0635 \\u0639\\u0644\\u064a\\u0647\\u0627 \\u0641\\u064a \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647. \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0628\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0639\\u0646\\u062f \\u0627\\u0644\\u0636\\u0631\\u0648\\u0631\\u0629 \\u0644\\u0644\\u0627\\u0645\\u062a\\u062b\\u0627\\u0644 \\u0644\\u0644\\u0627\\u0644\\u062a\\u0632\\u0627\\u0645\\u0627\\u062a \\u0627\\u0644\\u062a\\u0639\\u0627\\u0642\\u062f\\u064a\\u0629 \\u0648\\u0627\\u0644\\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629\\u060c \\u0639\\u0646\\u062f\\u0645\\u0627 \\u062a\\u0643\\u0648\\u0646 \\u0644\\u062f\\u064a\\u0646\\u0627 \\u0645\\u0635\\u0644\\u062d\\u0629 \\u062a\\u062c\\u0627\\u0631\\u064a\\u0629 \\u0645\\u0634\\u0631\\u0648\\u0639\\u0629 \\u0644\\u0644\\u0642\\u064a\\u0627\\u0645 \\u0628\\u0630\\u0644\\u0643 (\\u0645\\u062b\\u0644 \\u062a\\u062d\\u0633\\u064a\\u0646 \\u0648\\u062a\\u0637\\u0648\\u064a\\u0631 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645 \\u0627\\u0644\\u0623\\u0633\\u0627\\u0633\\u064a\\u060c \\u0648\\u062a\\u0639\\u0632\\u064a\\u0632 \\u0633\\u0644\\u0627\\u0645\\u062a\\u0647 \\u0648\\u0623\\u0645\\u0646\\u0647 \\u0648\\u0627\\u0633\\u062a\\u0642\\u0631\\u0627\\u0631\\u0647)\\u060c \\u0648\\u0645\\u0645\\u0627\\u0631\\u0633\\u0629 \\u0623\\u0648 \\u0627\\u0644\\u062f\\u0641\\u0627\\u0639 \\u0639\\u0646 \\u0645\\u0637\\u0627\\u0644\\u0628\\u0627\\u062a \\u0642\\u0627\\u0646\\u0648\\u0646\\u064a\\u0629.\\r\\n\\u062a\\u062e\\u062a\\u0644\\u0641 \\u0641\\u062a\\u0631\\u0627\\u062a \\u0627\\u0644\\u0627\\u062d\\u062a\\u0641\\u0627\\u0638 \\u0627\\u0639\\u062a\\u0645\\u0627\\u062f\\u064b\\u0627 \\u0639\\u0644\\u0649 \\u0645\\u0639\\u0627\\u064a\\u064a\\u0631 \\u0645\\u062e\\u062a\\u0644\\u0641\\u0629\\u060c \\u0645\\u062b\\u0644 \\u0646\\u0648\\u0639 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0648\\u0627\\u0644\\u0623\\u063a\\u0631\\u0627\\u0636 \\u0627\\u0644\\u062a\\u064a \\u0646\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0646 \\u0623\\u062c\\u0644\\u0647\\u0627. \\u0639\\u0644\\u0649 \\u0633\\u0628\\u064a\\u0644 \\u0627\\u0644\\u0645\\u062b\\u0627\\u0644\\u060c \\u0639\\u0646\\u062f\\u0645\\u0627 \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\\u0643 \\u0645\\u062b\\u0644 \\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0644\\u062a\\u0632\\u0648\\u064a\\u062f\\u0643 \\u0628\\u0627\\u0644\\u0645\\u0646\\u0635\\u0629\\u060c \\u0641\\u0625\\u0646\\u0646\\u0627 \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0628\\u0647\\u0630\\u0647 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0637\\u0627\\u0644\\u0645\\u0627 \\u0643\\u0627\\u0646 \\u0644\\u062f\\u064a\\u0643 \\u062d\\u0633\\u0627\\u0628. \\u0625\\u0630\\u0627 \\u0642\\u0645\\u062a \\u0628\\u0627\\u0646\\u062a\\u0647\\u0627\\u0643 \\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062e\\u062f\\u0645\\u0629 \\u0623\\u0648 \\u0625\\u0631\\u0634\\u0627\\u062f\\u0627\\u062a \\u0627\\u0644\\u0645\\u062c\\u062a\\u0645\\u0639 \\u0623\\u0648 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0623\\u0648 \\u0627\\u0644\\u0633\\u064a\\u0627\\u0633\\u0627\\u062a \\u0627\\u0644\\u0623\\u062e\\u0631\\u0649\\u060c \\u0641\\u0642\\u062f \\u0646\\u0642\\u0648\\u0645 \\u0628\\u0625\\u0632\\u0627\\u0644\\u0629 \\u0645\\u0644\\u0641\\u0643 \\u0627\\u0644\\u0634\\u062e\\u0635\\u064a \\u0648\\u0645\\u062d\\u062a\\u0648\\u0649 \\u0627\\u0644\\u0645\\u0633\\u062a\\u062e\\u062f\\u0645 \\u0645\\u0646 \\u0627\\u0644\\u0639\\u0631\\u0636 \\u0627\\u0644\\u0639\\u0627\\u0645 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0641\\u0648\\u0631\\u060c \\u0648\\u0644\\u0643\\u0646 \\u0642\\u062f \\u0646\\u062d\\u062a\\u0641\\u0638 \\u0628\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0623\\u062e\\u0631\\u0649 \\u0639\\u0646\\u0643 \\u0644\\u0645\\u0639\\u0627\\u0644\\u062c\\u0629 \\u0627\\u0644\\u0627\\u0646\\u062a\\u0647\\u0627\\u0643.\\r\\n\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a \\u0627\\u0644\\u0645\\u062a\\u0639\\u0644\\u0642\\u0629 \\u0628\\u0627\\u0644\\u0623\\u0637\\u0641\\u0627\\u0644 \\u0648\\u0627\\u0644\\u0645\\u0631\\u0627\\u0647\\u0642\\u064a\\u0646\\r\\n\\u0645\\u062f\\u0648\\u0646\\u0629 \\u0627\\u0644\\u0641\\u064a\\u062f\\u064a\\u0648 \\u063a\\u064a\\u0631 \\u0645\\u0648\\u062c\\u0647\\u0629 \\u0644\\u0644\\u0623\\u0637\\u0641\\u0627\\u0644 \\u0627\\u0644\\u0630\\u064a\\u0646 \\u062a\\u0642\\u0644 \\u0623\\u0639\\u0645\\u0627\\u0631\\u0647\\u0645 \\u0639\\u0646 13 \\u0639\\u0627\\u0645\\u064b\\u0627. \\u0641\\u064a \\u0628\\u0639\\u0636 \\u0627\\u0644\\u062d\\u0627\\u0644\\u0627\\u062a\\u060c \\u0642\\u062f \\u064a\\u0643\\u0648\\u0646 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u0639\\u0645\\u0631 \\u0623\\u0639\\u0644\\u0649 \\u0628\\u0633\\u0628\\u0628 \\u0627\\u0644\\u0645\\u062a\\u0637\\u0644\\u0628\\u0627\\u062a \\u0627\\u0644\\u062a\\u0646\\u0638\\u064a\\u0645\\u064a\\u0629 \\u0627\\u0644\\u0645\\u062d\\u0644\\u064a\\u0629\\u060c \\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0627\\u0637\\u0644\\u0627\\u0639 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062a\\u0643\\u0645\\u064a\\u0644\\u064a\\u0629 \\u0627\\u0644\\u0645\\u062d\\u0644\\u064a\\u0629 \\u0627\\u0644\\u062e\\u0627\\u0635\\u0629 \\u0628\\u0643 \\u0644\\u0644\\u062d\\u0635\\u0648\\u0644 \\u0639\\u0644\\u0649 \\u0645\\u0632\\u064a\\u062f \\u0645\\u0646 \\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a. \\u0625\\u0630\\u0627 \\u0643\\u0646\\u062a \\u062a\\u0639\\u062a\\u0642\\u062f \\u0623\\u0646 \\u0647\\u0646\\u0627\\u0643 \\u0645\\u0633\\u062a\\u062e\\u062f\\u0645\\u064b\\u0627 \\u0623\\u0642\\u0644 \\u0645\\u0646 \\u0647\\u0630\\u0627 \\u0627\\u0644\\u062d\\u062f \\u0627\\u0644\\u0623\\u062f\\u0646\\u0649 \\u0644\\u0644\\u0639\\u0645\\u0631\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627 \\u0639\\u0644\\u0649 Vlogapp2024@gmail.com\\r\\n\\r\\n\\r\\n\\r\\n\\u0627\\u062a\\u0635\\u0627\\u0644\\r\\n\\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646\\u062a \\u0644\\u062f\\u064a\\u0643 \\u0623\\u0633\\u0626\\u0644\\u0629 \\u0623\\u0648 \\u062a\\u0639\\u0644\\u064a\\u0642\\u0627\\u062a \\u0623\\u0648 \\u0634\\u0643\\u0627\\u0648\\u0649 \\u0623\\u0648 \\u0637\\u0644\\u0628\\u0627\\u062a \\u0628\\u062e\\u0635\\u0648\\u0635 \\u0633\\u064a\\u0627\\u0633\\u0629 \\u0627\\u0644\\u062e\\u0635\\u0648\\u0635\\u064a\\u0629 \\u0647\\u0630\\u0647\\u060c \\u0641\\u064a\\u0631\\u062c\\u0649 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0628\\u0646\\u0627 \\u0639\\u0644\\u0649: Vlogapp2024@gmail.com\\r\\n\\u064a\\u0631\\u062c\\u0649 \\u0623\\u064a\\u0636\\u064b\\u0627 \\u0627\\u0644\\u0627\\u0637\\u0644\\u0627\\u0639 \\u0639\\u0644\\u0649 \\u0627\\u0644\\u0634\\u0631\\u0648\\u0637 \\u0627\\u0644\\u062a\\u0643\\u0645\\u064a\\u0644\\u064a\\u0629 \\u0623\\u062f\\u0646\\u0627\\u0647 \\u0644\\u0645\\u0639\\u0631\\u0641\\u0629 \\u0645\\u0627 \\u0625\\u0630\\u0627 \\u0643\\u0627\\u0646 \\u0627\\u0644\\u0645\\u0645\\u062b\\u0644 \\u0627\\u0644\\u0645\\u062d\\u0644\\u064a \\u0623\\u0648 \\u062c\\u0647\\u0629 \\u0627\\u0644\\u0627\\u062a\\u0635\\u0627\\u0644 \\u0627\\u0644\\u0645\\u062d\\u0644\\u064a\\u0629 \\u0645\\u062a\\u0627\\u062d\\u0629 \\u0644\\u0628\\u0644\\u062f\\u0643.\\r\\n\\u0633\\u0646\\u0633\\u0639\\u0649 \\u0644\\u0644\\u062a\\u0639\\u0627\\u0645\\u0644 \\u0645\\u0639 \\u0637\\u0644\\u0628\\u0643 \\u0641\\u064a \\u0623\\u0642\\u0631\\u0628 \\u0648\\u0642\\u062a \\u0645\\u0645\\u0643\\u0646. \\u0648\\u0647\\u0630\\u0627 \\u062f\\u0648\\u0646 \\u0627\\u0644\\u0645\\u0633\\u0627\\u0633 \\u0628\\u062d\\u0642\\u0643 \\u0641\\u064a \\u062a\\u0642\\u062f\\u064a\\u0645 \\u0634\\u0643\\u0648\\u0649 \\u0625\\u0644\\u0649 \\u0647\\u064a\\u0626\\u0629 \\u062d\\u0645\\u0627\\u064a\\u0629 \\u0627\\u0644\\u0628\\u064a\\u0627\\u0646\\u0627\\u062a \\u0630\\u0627\\u062a \\u0627\\u0644\\u0635\\u0644\\u0629\\u060c \\u062d\\u064a\\u062b\\u0645\\u0627 \\u064a\\u0646\\u0637\\u0628\\u0642 \\u0630\\u0644\\u0643.\"}', 'pages/ouyw3LbPcKadgszRxVSivMTds8EWq8yFXA4WHft3.jpg', NULL, '2024-07-18 15:51:32');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'add city', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(2, 'edit city', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(3, 'delete city', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(4, 'view cities', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(5, 'add country', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(6, 'edit country', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(7, 'delete country', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(8, 'view countries', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(9, 'view admins', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(10, 'add admin', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(11, 'edit admin', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(12, 'delete admin', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(13, 'view roles', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(14, 'add role', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(15, 'edit role', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(16, 'delete role', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(17, 'view users', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(18, 'add user', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(19, 'edit user', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(20, 'delete user', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(21, 'view gifts', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(22, 'add gift', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(23, 'edit gift', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(24, 'delete gift', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(25, 'view sounds', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(26, 'add sound', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(27, 'edit sound', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(28, 'delete sound', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(29, 'view reports', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(30, 'add report', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(31, 'edit report', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(32, 'delete report', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(33, 'view notifications', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(34, 'add notification', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(35, 'edit notification', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(36, 'delete notification', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(37, 'view packages', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(38, 'add package', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(39, 'edit package', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(40, 'delete package', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(41, 'edit page', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(42, 'add category', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(43, 'edit category', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(44, 'delete category', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(45, 'view categories', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(46, 'view vendors', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(47, 'add vendor', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(48, 'edit vendor', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(49, 'delete vendor', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(50, 'view videos', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(51, 'add video', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(52, 'edit video', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(53, 'delete video', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(54, 'add color', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(55, 'edit color', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(56, 'delete color', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(57, 'view colors', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(58, 'add age', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(59, 'edit age', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(60, 'delete age', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(61, 'view ages', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(62, 'add animal_pen', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(63, 'edit animal_pen', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(64, 'delete animal_pen', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(65, 'view animal_pens', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(66, 'add partner', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(67, 'edit partner', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(68, 'delete partner', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(69, 'view partners', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(70, 'add department', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(71, 'edit department', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(72, 'delete department', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(73, 'view departments', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(74, 'add provider', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(75, 'edit provider', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(76, 'delete provider', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(77, 'add client', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(78, 'edit client', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(79, 'delete client', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16'),
(80, 'view client', 'admin', '2025-07-27 10:49:16', '2025-07-27 10:49:16');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile_views`
--

CREATE TABLE `profile_views` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `viewer_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile_views`
--

INSERT INTO `profile_views` (`id`, `user_id`, `viewer_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 41, 74, '2024-08-01 10:20:19', '2024-08-01 10:20:19', NULL),
(10, 25, 74, '2024-08-02 20:31:33', '2024-08-02 20:31:33', NULL),
(11, 71, 74, '2024-08-03 12:39:21', '2024-08-03 12:39:21', NULL),
(12, 74, 71, '2024-08-03 14:37:22', '2024-08-03 14:37:22', NULL),
(13, 73, 71, '2024-08-03 15:17:04', '2024-08-03 15:17:04', NULL),
(14, 75, 71, '2024-08-03 15:30:01', '2024-08-03 15:30:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `is_active` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `name`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"en\":\"sdfsfsdfsdfsadfsdf\",\"ar\":\"sdfsdf\"}', 1, NULL, '2024-04-28 13:27:21', '2024-04-28 13:27:21'),
(2, '{\"en\":\"eslam sdfsdf\",\"ar\":\"werewv\"}', 1, NULL, '2024-04-28 13:30:13', NULL),
(3, '{\"en\":\"sdfsd\",\"ar\":\"sdfdsfsdfdsf\"}', 1, '2024-04-28 13:22:08', '2024-04-28 13:22:08', NULL),
(4, '{\"en\":\"sfsdf\",\"ar\":\"sdfdsf\"}', 1, '2024-04-28 17:00:51', '2024-04-28 17:00:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin', '2024-02-13 17:54:31', '2024-02-13 17:54:31'),
(2, 'admin', 'admin', '2024-02-18 12:13:50', '2024-02-18 12:13:50');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(41, 1),
(50, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(9, 2),
(10, 2),
(12, 2),
(13, 2),
(14, 2),
(16, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sounds`
--

CREATE TABLE `sounds` (
  `id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci,
  `sound` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `image` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `artist_name` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sounds`
--

INSERT INTO `sounds` (`id`, `name`, `sound`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `image`, `artist_name`) VALUES
(1, '{\"en\":\"srfwer\",\"ar\":\"werwer\"}', 'sounds/sV137WiItOxRyii51Yxitw9g4o43EJySKgvmBoxM.wav', 1, '2024-03-20 02:40:57', '2024-04-02 01:20:23', NULL, 'sounds/320xZ5QG0MZNUVcln4bgBmvUvMKBriHgkO3Cbuzb.png', '{\"en\":\"eslam\",\"ar\":\"34sdfsdf\"}'),
(2, '{\"en\":\"Sound 1\",\"ar\":\"\\u0627\\u063a\\u0646\\u064a\\u0647 1\"}', 'sounds/mhOtF6S00DAwot2q0rAZhBvyYjKICy4PqvY2Z1h0.mp3', 1, '2024-03-26 15:28:16', '2024-03-26 15:28:16', NULL, NULL, NULL),
(3, '{\"en\":\"Sound 2\",\"ar\":\"\\u0627\\u063a\\u0646\\u064a\\u0647 2\"}', 'sounds/pNDk0QWxqK6PwnXNuD8EkvNxrr40ZSFvVWU2oMg0.mp3', 1, '2024-03-26 15:39:05', '2024-03-26 15:39:05', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` text COLLATE utf8mb4_unicode_ci,
  `sound` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` datetime DEFAULT NULL,
  `end_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `user_id`, `type`, `file`, `sound`, `start_at`, `end_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 'image', 'user_sound/81051_59acce00-f444-4bb9-8372-cd7dd3f63352.jpeg', 'user_sound/33645_mixkit-arcade-retro-game-over-213.wav', '2024-07-03 16:22:08', '2024-07-30 16:22:08', '2024-07-03 13:22:08', '2024-07-03 13:22:08', NULL),
(3, 1, 'image', 'story/11165_59acce00-f444-4bb9-8372-cd7dd3f63352.jpeg', 'story_sound/32623_mixkit-arcade-retro-game-over-213.wav', '2024-07-03 16:23:03', '2024-07-30 16:23:03', '2024-07-03 13:23:03', '2024-07-03 13:23:03', NULL),
(5, 38, 'image', 'story/22426_images.jpeg', NULL, '2024-07-13 14:21:33', '2024-07-14 14:21:33', '2024-07-13 14:21:33', '2024-07-13 14:21:33', NULL),
(6, 38, 'video', 'story/89909_sunset.mp4', NULL, '2024-07-13 17:44:10', '2024-07-14 17:44:10', '2024-07-13 17:44:10', '2024-07-13 17:44:10', NULL),
(11, 38, 'image', 'story/55283_image_picker_9C22F64B-3BEA-4E9D-BDD7-1D16D8AECF42-57791-0000006DEE48CF5D.jpg', NULL, '2024-07-19 14:12:00', '2024-07-20 14:12:00', '2024-07-19 14:12:00', '2024-07-19 14:12:00', NULL),
(12, 38, 'image', 'story/32361_image_picker_56F8BE08-FB35-45D4-A0A7-54DA3A36171C-57791-0000006E02C71D81.jpg', NULL, '2024-07-19 14:12:33', '2024-07-20 14:12:33', '2024-07-19 14:12:33', '2024-07-19 14:12:33', NULL),
(13, 38, 'image', 'story/62054_image_picker_FA527676-9833-4278-A752-49B801DDC691-57791-0000006F19E2C99E.jpg', NULL, '2024-07-19 14:15:47', '2024-07-26 14:15:47', '2024-07-19 14:15:47', '2024-07-19 14:15:47', NULL),
(14, 69, 'image', 'story/47292_images.jpeg', NULL, '2024-07-23 10:39:09', '2024-07-24 10:39:09', '2024-07-23 10:39:09', '2024-07-23 10:39:09', NULL),
(15, 71, 'image', 'story/32498_image_picker_16B71900-4885-45E6-BE76-8F9849201ECD-27654-000000348A2002FF.jpg', NULL, '2024-07-23 11:15:13', '2024-07-24 11:15:13', '2024-07-23 11:15:13', '2024-07-23 11:15:13', NULL),
(16, 71, 'image', 'story/74550_image_picker_4FCE10AE-813B-4AF8-9011-C8F8BE1666CB-27654-00000034F6EA6041.jpg', NULL, '2024-07-23 11:16:42', '2024-07-24 11:16:42', '2024-07-23 11:16:42', '2024-07-23 11:16:42', NULL),
(17, 71, 'image', 'story/34697_image_picker_83F53110-6225-4817-A330-18FE12620583-27654-000000379D436019.jpg', NULL, '2024-07-23 11:24:16', '2024-07-24 11:24:16', '2024-07-23 11:24:16', '2024-07-23 11:24:16', NULL),
(18, 71, 'image', 'story/29997_image_picker_BCBC0871-DD77-4E98-ABB9-7ACF8BAD904B-27654-00000038059A4DC1.jpg', NULL, '2024-07-23 11:25:33', '2024-07-24 11:25:33', '2024-07-23 11:25:33', '2024-07-23 11:25:33', NULL),
(19, 71, 'image', 'story/72390_image_picker_35BA1A86-BC6E-4907-9B88-CBCEEECBF61A-27654-0000004328872163.jpg', NULL, '2024-07-23 11:58:53', '2024-07-24 11:58:53', '2024-07-23 11:58:53', '2024-07-23 11:58:53', NULL),
(20, 71, 'image', 'story/63305_image_picker_5ACDD87B-480E-4940-913A-E0A64BD76A39-27654-00000043E5C63395.jpg', NULL, '2024-07-23 12:00:54', '2024-07-24 12:00:54', '2024-07-23 12:00:54', '2024-07-23 12:00:54', NULL),
(21, 74, 'image', 'story/19273_image_picker_E3EE39D9-A6C4-4B7D-8940-97E67F5CFED2-19971-0000050AC613F765.jpg', NULL, '2024-07-30 09:44:40', '2024-07-31 09:44:40', '2024-07-30 09:44:40', '2024-07-30 09:44:40', NULL),
(22, 71, 'image', 'story/85161_image_picker_1D2F9215-DABC-404C-BF08-609FD0B0F58A-13188-00000027394E6E22.png', NULL, '2024-08-03 16:25:05', '2024-08-04 16:25:05', '2024-08-03 16:25:05', '2024-08-03 16:25:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `store_views`
--

CREATE TABLE `store_views` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_views`
--

INSERT INTO `store_views` (`id`, `user_id`, `store_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 2, '2024-07-03 18:09:19', '2024-07-03 18:09:19', NULL),
(3, 69, 20, '2024-07-23 12:14:37', '2024-07-23 12:14:37', NULL),
(4, 69, 19, '2024-07-23 12:14:48', '2024-07-23 12:14:48', NULL),
(5, 69, 18, '2024-07-23 12:22:03', '2024-07-23 12:22:03', NULL),
(6, 69, 17, '2024-07-23 12:22:10', '2024-07-23 12:22:10', NULL),
(7, 69, 16, '2024-07-23 12:22:17', '2024-07-23 12:22:17', NULL),
(8, 69, 15, '2024-07-23 12:22:25', '2024-07-23 12:22:25', NULL),
(9, 69, 13, '2024-07-23 12:22:32', '2024-07-23 12:22:32', NULL),
(10, 69, 3, '2024-07-23 12:22:39', '2024-07-23 12:22:39', NULL),
(11, 69, 2, '2024-07-23 12:24:02', '2024-07-23 12:24:02', NULL),
(12, 69, 14, '2024-07-23 12:24:21', '2024-07-23 12:24:21', NULL),
(13, 74, 3, '2024-07-30 09:43:36', '2024-07-30 09:43:36', NULL),
(14, 74, 2, '2024-07-30 09:43:39', '2024-07-30 09:43:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `cart_id` varchar(63) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The unique cart id to be submit for telr',
  `order_id` int DEFAULT NULL COMMENT 'Should be the foreign key for items',
  `store_id` int NOT NULL COMMENT 'Map to ivp_store',
  `test_mode` tinyint(1) NOT NULL DEFAULT '0',
  `amount` decimal(8,2) NOT NULL COMMENT 'Map to ivp_amount the total or purchase',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Description should be limit to 64',
  `success_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The success URL',
  `canceled_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The canceled URL',
  `declined_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'The declined URL',
  `billing_fname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing first name',
  `billing_sname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing sur name',
  `billing_address_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing address 1',
  `billing_address_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing address 2',
  `billing_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing city',
  `billing_region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing region',
  `billing_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing zip',
  `billing_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing country',
  `billing_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Billing email',
  `lang_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Transaction Request lang',
  `trx_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'The transaction reference',
  `approved` tinyint(1) DEFAULT NULL COMMENT 'The transaction status is approved or failed',
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'The transaction response',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'The transaction status is updated or not',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `password_otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apple_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fcm_token` text COLLATE utf8mb4_unicode_ci,
  `user_type` enum('buyer','vendor','buyer_vendor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `account_type` enum('individual','company') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `app_lang` enum('en','ar') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `commercial_register` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_certificate` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `user_name`, `email`, `phone`, `password`, `gender`, `birth_date`, `image`, `otp`, `bio`, `password_otp`, `facebook_id`, `google_id`, `apple_id`, `remember_token`, `deleted_at`, `created_at`, `updated_at`, `fcm_token`, `user_type`, `account_type`, `is_active`, `app_lang`, `admin_id`, `is_verified`, `commercial_register`, `tax_certificate`, `license`) VALUES
(1, 'ahmed mohamed', 'qwerqrqewreqwrqwrqwr', 'eslam.webdesigner@gmail.com', '5612s60777s', '$2y$10$d7AI0OLnWwNZb9rF9xly9.YqQBZ2eHMpm9eOh0aqNOGUc4e3hjtmO', 'male', NULL, NULL, '1791', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-30 11:17:26', '2025-04-07 13:18:03', NULL, 'buyer', 'individual', 1, 'ar', NULL, 0, NULL, NULL, NULL),
(2, 'ahmed mohamed dsfsdfd', 'qwerqrqewreqwrqwrqwr', 'eslam.webdesigner@gmail.com', '561260777', '$2y$10$d7AI0OLnWwNZb9rF9xly9.YqQBZ2eHMpm9eOh0aqNOGUc4e3hjtmO', 'male', NULL, NULL, '1791', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-01-30 11:17:26', '2025-02-09 14:34:04', NULL, 'vendor', 'individual', 1, 'en', NULL, 0, NULL, NULL, NULL),
(3, 'ahmed mohamed @ sdfsd', 'qwerqrqewss @123 reqwrqwrqwr', 'eslam.webdesignear@gmail.com', '561651551', '$2y$10$q3ZlFv4RpENuOAECLKTuVuCrXxafag/OunBCuh3HxJCGkOuC7P0FC', 'male', NULL, NULL, '5524', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-13 14:21:03', '2025-04-13 14:21:03', NULL, 'buyer', 'individual', 1, 'en', NULL, 0, NULL, NULL, NULL),
(4, 'ahmed mohamed @ sdfsd', 'qwerqrqewss @123reqwrqwrqwr', 'eslam.webdesignear@gmail.com2', '5616515513', '$2y$10$bS.HZekda5jMY2hflxbgI.INEFf8WgGkfAu52TUXbTPNw5NFr/zYq', 'male', NULL, NULL, '1901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-13 14:21:30', '2025-04-13 14:21:30', NULL, 'buyer', 'individual', 1, 'en', NULL, 0, NULL, NULL, NULL),
(5, 'Amos Goodwin', '', 'tylose@mailinator.com', '+1 (121) 132-3459', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-11 12:07:11', '2025-05-11 12:07:11', NULL, 'buyer', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(6, 'Amaya Pennington', '', 'zosurez@mailinator.com', '+1 (569) 446-7911', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-11 12:08:00', '2025-05-11 12:08:00', NULL, 'buyer', NULL, 1, 'en', 4, 0, NULL, NULL, NULL),
(7, 'Hoyt Woodard', '', 'ketap@mailinator.com', '+1 (988) 498-3853', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-11 12:09:16', '2025-05-11 12:09:30', NULL, 'vendor', NULL, 1, 'en', 4, 0, NULL, NULL, NULL),
(8, 'Eslam Salah sdfsdf', '', 'eslam.webdevlopre@gmail.com', '01000907612', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-11 13:35:48', '2025-05-11 13:41:15', NULL, 'vendor', NULL, 1, 'en', 4, 0, NULL, NULL, NULL),
(9, 'Giacomo Sandoval', '', 'xocabon@mailinator.com', '+1 (673) 834-7274', NULL, 'male', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-12 15:27:01', '2025-05-12 15:27:01', NULL, 'vendor', NULL, 1, 'en', 5, 0, NULL, NULL, NULL),
(10, 'ahmed mohamed @ sdfsd', 'qwerqrqewss @', 'eslam@gmail.com2', '561651551332', '$2y$10$CldZHFqweiDnUYyaGrcpnOtSBVjaKb6UfEouYze6bG7APYMQ50oNK', 'male', NULL, NULL, '3152', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-14 12:34:00', '2025-08-14 13:45:44', NULL, 'vendor', NULL, 1, 'en', NULL, 1, 'user/17592_Infinity-Tomato.jpg', NULL, NULL),
(11, 'ahmed mohamed @ sdfsd', '23423423423@', 'eslam@gmail.com2234', '234234', '$2y$10$V4ANUZ/bFWVpVYDeQo2HMOcUHz2YuCk9mqRZ2g7ClNqII1zhNLGyW', 'male', NULL, NULL, '5031', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-14 14:10:23', '2025-08-17 14:59:11', NULL, 'buyer', NULL, 1, 'en', NULL, 1, NULL, NULL, NULL),
(12, 'Lilah Dudley', NULL, 'geqoqekix@mailinator.com', '+1 (413) 712-4683', '$2y$10$/gTc2E3U4o96JjAnLOCxLe49zx/BLSw5Saxcr1Qf2BNn.jRgaVTm6', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:50:36', '2025-08-17 14:50:36', NULL, 'vendor', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(13, 'Jonas Jacobson', NULL, 'rijezys@mailinator.com', '+1 (683) 129-5978', '$2y$10$gmHEgoxQguu9Tz7/z7Jxmur9REtr2loc1TImj/rBeWHl0NegzMm3a', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:55:32', '2025-08-17 14:55:32', NULL, 'vendor', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(14, 'Iris Martinez', NULL, 'gaqicyme@mailinator.com', '+1 (349) 193-4437', '$2y$10$9MX4tGVPB4WcjmeCRMfr9.tO8pd.qV9yTaVgb84no9vYgu.0xinxa', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:56:03', '2025-08-17 14:56:03', NULL, 'vendor', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(15, 'Iris Martinez', NULL, 'gaqicyme@mailinator.com', '+1 (349) 193-4437', '$2y$10$.syoz5fFJsX3E/6YaL9IuuCxZmp2BitZ7dDcJn9TOMLHHwpZZKhgO', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:56:21', '2025-08-17 14:56:21', NULL, 'vendor', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(16, 'Iris Martinez', NULL, 'gaqicyme@mailinator.com', '+1 (349) 193-4437', '$2y$10$BITkOmeA5wUlVS6SWeG19OHj.fl.7daW5zjRZUJS/LntaSp7VzG3u', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:56:33', '2025-08-17 14:56:33', NULL, 'vendor', NULL, 1, 'en', NULL, 0, NULL, NULL, NULL),
(17, 'Lilah Battle', NULL, 'cixyha@mailinator.com', '+1 (675) 805-5594', '$2y$10$1iOJ5k0PiQhS0bgxHz9zEOD93TMKWJ43jinSpe6RXrWfgIjXy0dEu', 'male', NULL, 'partners/default.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-17 14:57:10', '2025-08-17 14:58:53', NULL, 'vendor', NULL, 1, 'en', NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_blocks`
--

CREATE TABLE `user_blocks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `blocked_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_blocks`
--

INSERT INTO `user_blocks` (`id`, `user_id`, `blocked_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 62, 58, '2024-07-07 21:01:36', '2024-07-07 21:01:36', NULL),
(2, 71, 75, '2024-08-03 15:30:03', '2024-08-03 15:30:03', NULL),
(3, 71, 73, '2024-08-03 15:57:45', '2024-08-03 15:57:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_coins`
--

CREATE TABLE `user_coins` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `coin` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_coins`
--

INSERT INTO `user_coins` (`id`, `user_id`, `coin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 84, '2024-06-24 14:53:31', '2024-06-24 17:31:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_gifts`
--

CREATE TABLE `user_gifts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `user_video_id` bigint UNSIGNED DEFAULT NULL,
  `gift_id` bigint UNSIGNED DEFAULT NULL,
  `gift_coin` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_gifts`
--

INSERT INTO `user_gifts` (`id`, `user_id`, `video_id`, `user_video_id`, `gift_id`, `gift_coin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 2, 42, '2024-06-24 16:50:57', '2024-06-24 16:50:57', NULL),
(2, 1, 1, 1, 2, 42, '2024-06-24 17:31:26', '2024-06-24 17:31:26', NULL),
(3, 1, 1, 1, 2, 42, '2024-06-24 17:31:35', '2024-06-24 17:31:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `package_id` bigint UNSIGNED DEFAULT NULL,
  `coin` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `image` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `package_id`, `coin`, `price`, `image`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 555, 10, 'user/transaction_image/12699_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:38:35', '2024-06-24 14:38:35', NULL),
(2, 1, 1, 555, 10, 'user/transaction_image/18627_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:39:45', '2024-06-24 14:39:45', NULL),
(3, 1, 1, 555, 10, 'user/transaction_image/62450_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:01', '2024-06-24 14:40:01', NULL),
(4, 1, 1, 555, 10, 'user/transaction_image/87184_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:02', '2024-06-24 14:40:02', NULL),
(5, 1, 1, 555, 10, 'user/transaction_image/70532_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:03', '2024-06-24 14:40:03', NULL),
(6, 1, 1, 555, 10, 'user/transaction_image/46762_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:20', '2024-06-24 14:40:20', NULL),
(7, 1, 1, 555, 10, 'user/transaction_image/45779_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:30', '2024-06-24 14:40:30', NULL),
(8, 1, 1, 555, 10, 'user/transaction_image/37756_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:40:31', '2024-06-24 14:40:31', NULL),
(9, 1, 1, 555, 10, 'user/transaction_image/17359_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:50:22', '2024-06-24 14:50:22', NULL),
(10, 1, 1, 555, 10, 'user/transaction_image/78733_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:51:45', '2024-06-24 14:51:45', NULL),
(11, 1, 1, 555, 10, 'user/transaction_image/91107_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:53:25', '2024-06-24 14:53:25', NULL),
(12, 1, 1, 555, 10, 'user/transaction_image/63697_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:53:31', '2024-06-24 14:53:31', NULL),
(13, 1, 1, 555, 10, 'user/transaction_image/15630_6bd1d9a2-42ab-48fd-91fe-baa04ab5af3f.jpg', '2024-06-24 14:53:42', '2024-06-24 14:53:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sound` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `view_permissions` int DEFAULT NULL,
  `comment_permissions` int DEFAULT NULL,
  `lat` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lng` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mention` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_comments`
--

CREATE TABLE `video_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `comment` double DEFAULT NULL,
  `comment_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `live_video_item_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_comments`
--

INSERT INTO `video_comments` (`id`, `comment`, `comment_id`, `user_id`, `video_id`, `created_at`, `updated_at`, `deleted_at`, `live_video_item_id`) VALUES
(1, 100, NULL, 1, 11, '2025-02-05 16:44:49', '2025-02-05 16:44:49', NULL, NULL),
(2, 100, NULL, 1, 11, '2025-02-05 16:44:51', '2025-02-05 16:44:51', NULL, NULL),
(3, 1500, NULL, 1, 11, '2025-02-05 16:59:21', '2025-02-05 16:59:21', NULL, NULL),
(4, 100, NULL, 1, 11, '2025-03-05 01:38:45', '2025-03-05 01:38:45', NULL, NULL),
(5, 100, NULL, 1, 1, '2025-03-23 20:33:37', '2025-03-23 20:33:37', NULL, 2),
(6, 100, NULL, 4, 8, '2025-04-13 16:19:29', '2025-04-13 16:19:29', NULL, 10),
(7, 100, NULL, 4, 16, '2025-04-17 02:28:40', '2025-04-17 02:28:40', NULL, 15),
(8, 100, NULL, 4, 16, '2025-04-17 02:29:09', '2025-04-17 02:29:09', NULL, 15),
(9, 100, NULL, 4, 16, '2025-04-17 02:29:43', '2025-04-17 02:29:43', NULL, 15),
(10, 100, NULL, 4, 16, '2025-04-17 02:29:47', '2025-04-17 02:29:47', NULL, 15),
(11, 100, NULL, 4, 16, '2025-04-17 02:29:57', '2025-04-17 02:29:57', NULL, 15),
(12, 100, NULL, 4, 16, '2025-04-17 02:31:25', '2025-04-17 02:31:25', NULL, 15),
(13, 100, NULL, 4, 16, '2025-04-17 02:32:30', '2025-04-17 02:32:30', NULL, 15),
(14, 100, NULL, 4, 16, '2025-04-17 02:32:39', '2025-04-17 02:32:39', NULL, 15),
(15, 100, NULL, 4, 16, '2025-04-17 02:33:18', '2025-04-17 02:33:18', NULL, 15),
(16, 100, NULL, 4, 16, '2025-04-17 02:33:35', '2025-04-17 02:33:35', NULL, 15),
(17, 100, NULL, 4, 16, '2025-04-17 02:33:54', '2025-04-17 02:33:54', NULL, 15),
(18, 100, NULL, 4, 16, '2025-04-17 02:35:20', '2025-04-17 02:35:20', NULL, 15),
(19, 100, NULL, 4, 16, '2025-04-17 10:07:24', '2025-04-17 10:07:24', NULL, 15),
(20, 100, NULL, 4, 16, '2025-04-17 10:16:12', '2025-04-17 10:16:12', NULL, 15);

-- --------------------------------------------------------

--
-- Table structure for table `video_comment_likes`
--

CREATE TABLE `video_comment_likes` (
  `id` bigint UNSIGNED NOT NULL,
  `comment_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_comment_likes`
--

INSERT INTO `video_comment_likes` (`id`, `comment_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 58, '2024-05-13 11:14:30', '2024-05-15 09:28:03', '2024-05-15 09:28:03'),
(2, 2, 58, '2024-05-15 09:22:58', '2024-05-15 09:26:00', '2024-05-15 09:26:00'),
(3, 3, 58, '2024-05-15 09:25:27', '2024-05-15 09:25:35', '2024-05-15 09:25:35'),
(4, 2, 58, '2024-05-15 09:27:50', '2024-05-15 09:27:55', '2024-05-15 09:27:55'),
(5, 1, 58, '2024-05-15 09:28:05', '2024-05-15 09:28:05', NULL),
(6, 2, 58, '2024-05-15 09:28:10', '2024-05-22 10:24:55', '2024-05-22 10:24:55'),
(7, 3, 58, '2024-05-15 09:28:12', '2024-05-15 09:28:12', NULL),
(8, 14, 63, '2024-06-03 10:09:47', '2024-06-03 10:09:47', NULL),
(9, 28, 69, '2024-07-16 17:23:16', '2024-07-16 17:23:36', '2024-07-16 17:23:36'),
(10, 30, 69, '2024-07-16 17:23:39', '2024-07-16 17:23:44', '2024-07-16 17:23:44'),
(11, 28, 69, '2024-07-16 17:23:41', '2024-07-16 17:23:43', '2024-07-16 17:23:43'),
(12, 28, 69, '2024-07-16 17:23:51', '2024-07-16 17:23:51', NULL),
(13, 31, 72, '2024-07-17 14:01:24', '2024-07-17 14:01:24', NULL),
(14, 32, 72, '2024-07-17 14:01:28', '2024-07-17 14:01:28', NULL),
(15, 33, 38, '2024-07-19 14:02:09', '2024-07-19 14:02:09', NULL),
(16, 38, 38, '2024-07-19 14:19:36', '2024-07-19 14:19:36', NULL),
(17, 41, 74, '2024-08-04 12:39:53', '2024-08-04 12:39:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_favorites`
--

CREATE TABLE `video_favorites` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_favorites`
--

INSERT INTO `video_favorites` (`id`, `user_id`, `video_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 1, '2024-04-29 15:24:56', '2024-04-29 15:24:57', '2024-04-29 15:24:57'),
(2, 5, 4, '2024-04-29 16:16:44', '2024-04-30 14:33:22', '2024-04-30 14:33:22'),
(3, 5, 3, '2024-04-29 16:16:50', '2024-04-30 14:05:23', '2024-04-30 14:05:23'),
(4, 2, 2, '2024-04-29 18:31:11', '2024-04-29 18:31:11', NULL),
(5, 53, 3, '2024-04-30 12:01:38', '2024-04-30 12:01:38', NULL),
(6, 53, 2, '2024-04-30 12:02:08', '2024-04-30 12:02:08', NULL),
(7, 52, 4, '2024-04-30 12:10:49', '2024-04-30 12:10:49', NULL),
(8, 5, 2, '2024-04-30 12:55:27', '2024-04-30 14:05:21', '2024-04-30 14:05:21'),
(9, 5, 3, '2024-04-30 14:33:13', '2024-04-30 15:25:43', '2024-04-30 15:25:43'),
(10, 5, 4, '2024-04-30 15:27:17', '2024-04-30 15:32:26', '2024-04-30 15:32:26'),
(11, 5, 3, '2024-04-30 15:32:37', '2024-04-30 15:46:05', '2024-04-30 15:46:05'),
(12, 5, 2, '2024-04-30 15:33:06', '2024-04-30 15:46:08', '2024-04-30 15:46:08'),
(13, 5, 4, '2024-04-30 15:33:56', '2024-04-30 15:46:04', '2024-04-30 15:46:04'),
(14, 5, 4, '2024-04-30 15:48:36', '2024-05-01 09:59:55', '2024-05-01 09:59:55'),
(15, 54, 4, '2024-04-30 16:42:53', '2024-04-30 16:44:16', '2024-04-30 16:44:16'),
(16, 54, 3, '2024-04-30 16:43:00', '2024-04-30 16:44:18', '2024-04-30 16:44:18'),
(17, 54, 4, '2024-04-30 16:45:00', '2024-04-30 16:47:13', '2024-04-30 16:47:13'),
(18, 54, 2, '2024-04-30 16:45:41', '2024-04-30 16:47:47', '2024-04-30 16:47:47'),
(19, 54, 4, '2024-04-30 16:47:19', '2024-04-30 16:47:44', '2024-04-30 16:47:44'),
(20, 54, 4, '2024-04-30 16:48:23', '2024-04-30 16:48:35', '2024-04-30 16:48:35'),
(21, 54, 3, '2024-04-30 16:48:47', '2024-04-30 16:48:47', NULL),
(22, 55, 4, '2024-04-30 17:54:10', '2024-04-30 17:54:10', NULL),
(23, 55, 3, '2024-04-30 17:54:59', '2024-04-30 17:54:59', NULL),
(24, 5, 4, '2024-05-01 09:59:56', '2024-05-01 09:59:56', NULL),
(25, 5, 2, '2024-05-01 10:03:10', '2024-05-01 10:03:10', NULL),
(26, 56, 4, '2024-05-01 14:29:31', '2024-05-01 14:29:31', NULL),
(27, 57, 5, '2024-05-02 18:31:49', '2024-05-02 18:31:49', NULL),
(28, 57, 4, '2024-05-02 18:32:07', '2024-05-02 18:32:07', NULL),
(29, 58, 5, '2024-05-07 13:10:14', '2024-05-07 13:10:21', '2024-05-07 13:10:21'),
(30, 58, 5, '2024-05-07 13:10:21', '2024-05-07 13:12:08', '2024-05-07 13:12:08'),
(31, 58, 5, '2024-05-07 13:12:19', '2024-05-07 13:12:19', NULL),
(32, 58, 6, '2024-05-08 14:43:14', '2024-05-08 14:43:17', '2024-05-08 14:43:17'),
(33, 63, 8, '2024-06-03 10:10:55', '2024-06-03 10:10:55', NULL),
(34, 72, 14, '2024-07-17 14:00:36', '2024-07-17 14:00:36', NULL),
(35, 72, 15, '2024-07-17 14:05:49', '2024-07-17 14:05:49', NULL),
(36, 69, 19, '2024-07-23 13:33:42', '2024-07-23 13:33:42', NULL),
(37, 73, 19, '2024-07-23 15:14:53', '2024-07-23 15:14:53', NULL),
(38, 74, 25, '2024-08-01 10:32:42', '2024-08-01 10:32:42', NULL),
(39, 71, 25, '2024-08-01 15:34:37', '2024-08-01 15:34:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_likes`
--

CREATE TABLE `video_likes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_likes`
--

INSERT INTO `video_likes` (`id`, `user_id`, `video_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 1, '2024-04-29 15:24:56', '2024-04-29 15:24:57', '2024-04-29 15:24:57'),
(2, 5, 4, '2024-04-29 16:16:48', '2024-04-29 16:16:48', NULL),
(3, 53, 3, '2024-04-30 12:02:03', '2024-04-30 12:02:03', NULL),
(4, 53, 2, '2024-04-30 12:02:06', '2024-04-30 12:02:06', NULL),
(5, 54, 4, '2024-04-30 16:42:50', '2024-04-30 16:43:34', '2024-04-30 16:43:34'),
(6, 54, 3, '2024-04-30 16:42:59', '2024-04-30 16:44:18', '2024-04-30 16:44:18'),
(7, 54, 4, '2024-04-30 16:44:57', '2024-04-30 16:47:22', '2024-04-30 16:47:22'),
(8, 54, 3, '2024-04-30 16:45:38', '2024-04-30 16:47:45', '2024-04-30 16:47:45'),
(9, 57, 5, '2024-05-02 18:31:47', '2024-05-02 18:31:47', NULL),
(10, 58, 6, '2024-05-08 14:43:18', '2024-05-13 15:30:55', '2024-05-13 15:30:55'),
(11, 58, 6, '2024-05-13 15:31:01', '2024-05-13 15:31:01', NULL),
(12, 63, 8, '2024-06-03 10:07:30', '2024-06-03 10:07:30', NULL),
(13, 69, 12, '2024-07-16 17:27:50', '2024-07-16 17:28:06', '2024-07-16 17:28:06'),
(14, 72, 14, '2024-07-17 14:02:06', '2024-07-17 14:02:06', NULL),
(15, 72, 15, '2024-07-17 14:05:53', '2024-07-17 14:05:53', NULL),
(16, 69, 19, '2024-07-23 13:33:33', '2024-07-23 13:33:33', NULL),
(17, 73, 19, '2024-07-23 15:14:42', '2024-07-23 15:14:42', NULL),
(18, 74, 25, '2024-08-01 10:32:43', '2024-08-01 10:32:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `video_reports`
--

CREATE TABLE `video_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `report_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_reports`
--

INSERT INTO `video_reports` (`id`, `report_id`, `user_id`, `video_id`, `comment`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 25, 'asfdsffdsgfdg', NULL, '2024-04-28 12:41:38', '2024-04-28 12:41:38'),
(2, 1, 1, 25, NULL, NULL, '2024-04-28 12:42:24', '2024-04-28 12:42:24'),
(3, 3, 5, 4, 'hghfgh', NULL, '2024-05-02 11:26:03', '2024-05-02 11:26:03'),
(4, 2, 5, 5, NULL, NULL, '2024-05-02 11:27:02', '2024-05-02 11:27:02'),
(5, 2, 5, 5, 'ggff', NULL, '2024-05-02 12:15:38', '2024-05-02 12:15:38'),
(6, 2, 5, 5, 'cvv', NULL, '2024-05-02 12:30:31', '2024-05-02 12:30:31'),
(7, 3, 5, 5, 'DM msm', NULL, '2024-05-02 12:42:52', '2024-05-02 12:42:52'),
(8, 3, 5, 5, 'hvvb', NULL, '2024-05-02 12:56:16', '2024-05-02 12:56:16'),
(9, 3, 57, 5, NULL, NULL, '2024-05-02 18:31:41', '2024-05-02 18:31:41');

-- --------------------------------------------------------

--
-- Table structure for table `video_shares`
--

CREATE TABLE `video_shares` (
  `id` bigint UNSIGNED NOT NULL,
  `device_id` text COLLATE utf8mb4_unicode_ci,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_views`
--

CREATE TABLE `video_views` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `video_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `video_views`
--

INSERT INTO `video_views` (`id`, `user_id`, `video_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 7, '2025-04-07 14:04:36', '2025-04-07 14:04:36', NULL),
(3, 1, 6, '2025-04-07 14:05:07', '2025-04-07 14:05:07', NULL),
(4, 4, 16, '2025-04-17 01:42:14', '2025-04-17 01:42:14', NULL),
(5, 1, 16, '2025-04-17 01:42:14', '2025-04-17 01:42:14', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ages`
--
ALTER TABLE `ages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ages_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `animal_pens`
--
ALTER TABLE `animal_pens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `animal_pens_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colors_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `follow_users`
--
ALTER TABLE `follow_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `follow_users_follow_id_foreign` (`follow_id`),
  ADD KEY `follow_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `friends_friend_id_foreign` (`friend_id`),
  ADD KEY `friends_user_id_foreign` (`user_id`);

--
-- Indexes for table `gifts`
--
ALTER TABLE `gifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hashtags`
--
ALTER TABLE `hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hashtags_name_unique` (`name`);

--
-- Indexes for table `hashtag_video`
--
ALTER TABLE `hashtag_video`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hashtag_videos_video_id_foreign` (`video_id`),
  ADD KEY `hashtag_videos_hashtag_id_foreign` (`hashtag_id`);

--
-- Indexes for table `live_videos`
--
ALTER TABLE `live_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `live_videos_user_id_foreign` (`user_id`),
  ADD KEY `live_videos_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `live_video_items`
--
ALTER TABLE `live_video_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `live_video_items_category_id_foreign` (`category_id`),
  ADD KEY `live_video_items_user_finished_id_foreign` (`user_finished_id`),
  ADD KEY `live_video_items_live_video_id_foreign` (`live_video_id`),
  ADD KEY `live_video_items_color_id_foreign` (`color_id`),
  ADD KEY `live_video_items_age_id_foreign` (`age_id`),
  ADD KEY `live_video_items_animal_pen_id_foreign` (`animal_pen_id`),
  ADD KEY `live_video_items_user_id_foreign` (`user_id`);

--
-- Indexes for table `live_video_likes`
--
ALTER TABLE `live_video_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `live_video_likes_user_id_foreign` (`user_id`),
  ADD KEY `live_video_likes_live_video_id_foreign` (`live_video_id`);

--
-- Indexes for table `live_video_users`
--
ALTER TABLE `live_video_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `live_video_users_user_id_foreign` (`user_id`),
  ADD KEY `live_video_users_live_video_id_foreign` (`live_video_id`);

--
-- Indexes for table `ltm_translations`
--
ALTER TABLE `ltm_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `profile_views`
--
ALTER TABLE `profile_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `profile_views_user_id_foreign` (`user_id`),
  ADD KEY `profile_views_viewer_id_foreign` (`viewer_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sounds`
--
ALTER TABLE `sounds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stores_user_id_foreign` (`user_id`);

--
-- Indexes for table `store_views`
--
ALTER TABLE `store_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_views_user_id_foreign` (`user_id`),
  ADD KEY `store_views_store_id_foreign` (`store_id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `user_blocks`
--
ALTER TABLE `user_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_blocks_user_id_foreign` (`user_id`),
  ADD KEY `user_blocks_blocked_id_foreign` (`blocked_id`);

--
-- Indexes for table `user_coins`
--
ALTER TABLE `user_coins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_coins_user_id_foreign` (`user_id`);

--
-- Indexes for table `user_gifts`
--
ALTER TABLE `user_gifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_subscriptions_user_id_foreign` (`user_id`),
  ADD KEY `user_subscriptions_package_id_foreign` (`package_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `videos_user_id_foreign` (`user_id`);

--
-- Indexes for table `video_comments`
--
ALTER TABLE `video_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_comments_live_video_item_id_foreign` (`live_video_item_id`);

--
-- Indexes for table `video_comment_likes`
--
ALTER TABLE `video_comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_comment_likes_comment_id_foreign` (`comment_id`),
  ADD KEY `video_comment_likes_user_id_foreign` (`user_id`);

--
-- Indexes for table `video_favorites`
--
ALTER TABLE `video_favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_favorites_user_id_foreign` (`user_id`),
  ADD KEY `video_favorites_video_id_foreign` (`video_id`);

--
-- Indexes for table `video_likes`
--
ALTER TABLE `video_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_likes_user_id_foreign` (`user_id`),
  ADD KEY `video_likes_video_id_foreign` (`video_id`);

--
-- Indexes for table `video_reports`
--
ALTER TABLE `video_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_reports_user_id_foreign` (`user_id`),
  ADD KEY `video_reports_video_id_foreign` (`video_id`),
  ADD KEY `video_reports_report_id_foreign` (`report_id`);

--
-- Indexes for table `video_shares`
--
ALTER TABLE `video_shares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_shares_video_id_foreign` (`video_id`);

--
-- Indexes for table `video_views`
--
ALTER TABLE `video_views`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ages`
--
ALTER TABLE `ages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `animal_pens`
--
ALTER TABLE `animal_pens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follow_users`
--
ALTER TABLE `follow_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `gifts`
--
ALTER TABLE `gifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hashtags`
--
ALTER TABLE `hashtags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `hashtag_video`
--
ALTER TABLE `hashtag_video`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `live_videos`
--
ALTER TABLE `live_videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `live_video_items`
--
ALTER TABLE `live_video_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `live_video_likes`
--
ALTER TABLE `live_video_likes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `live_video_users`
--
ALTER TABLE `live_video_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ltm_translations`
--
ALTER TABLE `ltm_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile_views`
--
ALTER TABLE `profile_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sounds`
--
ALTER TABLE `sounds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `store_views`
--
ALTER TABLE `store_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_blocks`
--
ALTER TABLE `user_blocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_coins`
--
ALTER TABLE `user_coins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_gifts`
--
ALTER TABLE `user_gifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `video_comments`
--
ALTER TABLE `video_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `video_comment_likes`
--
ALTER TABLE `video_comment_likes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `video_favorites`
--
ALTER TABLE `video_favorites`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `video_likes`
--
ALTER TABLE `video_likes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `video_reports`
--
ALTER TABLE `video_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `video_shares`
--
ALTER TABLE `video_shares`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `video_views`
--
ALTER TABLE `video_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ages`
--
ALTER TABLE `ages`
  ADD CONSTRAINT `ages_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `animal_pens`
--
ALTER TABLE `animal_pens`
  ADD CONSTRAINT `animal_pens_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `colors`
--
ALTER TABLE `colors`
  ADD CONSTRAINT `colors_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_videos`
--
ALTER TABLE `live_videos`
  ADD CONSTRAINT `live_videos_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_video_items`
--
ALTER TABLE `live_video_items`
  ADD CONSTRAINT `live_video_items_age_id_foreign` FOREIGN KEY (`age_id`) REFERENCES `ages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_animal_pen_id_foreign` FOREIGN KEY (`animal_pen_id`) REFERENCES `animal_pens` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_color_id_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_live_video_id_foreign` FOREIGN KEY (`live_video_id`) REFERENCES `live_videos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_user_finished_id_foreign` FOREIGN KEY (`user_finished_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_video_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_coins`
--
ALTER TABLE `user_coins`
  ADD CONSTRAINT `user_coins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 08, 2026 at 02:12 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `anchorplan_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
CREATE TABLE IF NOT EXISTS `attendance_records` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `clock_in_time` datetime DEFAULT NULL,
  `clock_out_time` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `late_minutes` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_user_id_date_unique` (`user_id`,`date`),
  KEY `attendance_records_date_index` (`date`),
  KEY `attendance_records_status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`id`, `user_id`, `date`, `clock_in_time`, `clock_out_time`, `latitude`, `longitude`, `status`, `late_minutes`, `created_at`, `updated_at`) VALUES
('019ea6ea-be45-73b2-8e65-bac0f3d7439c', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-02', '2026-06-02 08:55:00', '2026-06-02 17:05:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be46-73f5-8644-8ff1619b0793', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-03', '2026-06-03 09:15:00', '2026-06-03 17:30:00', 37.77491000, -122.41940000, 'late', 15, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be48-70f8-b97a-9bfc1e73ef0d', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-04', '2026-06-04 08:58:00', '2026-06-04 18:02:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be4a-7260-9f37-d73fd0d388f5', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-05', '2026-06-05 09:00:00', '2026-06-05 17:00:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be4d-70c4-917a-88989c8db8fb', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-06', '2026-06-06 09:45:00', '2026-06-06 17:00:00', 37.77492000, -122.41941000, 'late', 45, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ee-1c89-7012-bcf4-f1d79c01e4f2', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0', '2026-06-02', '2026-06-02 08:55:00', '2026-06-02 17:05:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1c8b-7167-99db-66db16b777e7', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0', '2026-06-03', '2026-06-03 09:15:00', '2026-06-03 17:30:00', 37.77491000, -122.41940000, 'late', 15, '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1c8c-706e-9f0c-cbe896b5b620', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0', '2026-06-04', '2026-06-04 08:58:00', '2026-06-04 18:02:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1c8e-72a2-b2bd-ae64bf5d3940', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0', '2026-06-05', '2026-06-05 09:00:00', '2026-06-05 17:00:00', 37.77492900, -122.41941600, 'present', 0, '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1c8f-721a-afcd-31182c2c846c', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0', '2026-06-06', '2026-06-06 09:45:00', '2026-06-06 17:00:00', 37.77492000, -122.41941000, 'late', 45, '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea71d-ff02-73db-addd-c33972f4db55', '019ea71c-9549-7245-af0c-6470925465d2', '2026-06-08', '2026-06-08 12:03:41', NULL, 16.44953600, 120.59279360, 'late', -184, '2026-06-08 04:03:41', '2026-06-08 04:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-jonel.uligan7@gmail.com|127.0.0.1:timer', 'i:1780918001;', 1780918001),
('laravel-cache-jonel.uligan7@gmail.com|127.0.0.1', 'i:1;', 1780918001);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'meeting',
  `created_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `calendar_events_created_by_foreign` (`created_by`),
  KEY `calendar_events_start_time_index` (`start_time`),
  KEY `calendar_events_end_time_index` (`end_time`),
  KEY `calendar_events_type_index` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `description`, `start_time`, `end_time`, `type`, `created_by`, `created_at`, `updated_at`) VALUES
('019ea6ea-be6b-730e-a587-126876e00e05', 'Weekly Sprint Planning', 'Engineering sprint kickoff, ticket allocation, and goal mapping.', '2026-06-08 10:00:00', '2026-06-08 11:30:00', 'meeting', '019ea6ea-b5a6-7267-9941-039d82156436', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be72-7079-89ef-92b0b4ffbd60', 'Memorial Day Holiday', 'National holiday - Office closed.', '2026-06-15 09:00:00', '2026-06-15 17:00:00', 'holiday', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be75-71fb-80af-b46f21a87002', 'Bob Sick Leave', 'Approved sick leave request.', '2026-06-02 09:00:00', '2026-06-02 17:00:00', 'leave', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `devotional_records`
--

DROP TABLE IF EXISTS `devotional_records`;
CREATE TABLE IF NOT EXISTS `devotional_records` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `monitored_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monitored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devotional_records_user_id_date_unique` (`user_id`,`date`),
  KEY `devotional_records_monitored_by_foreign` (`monitored_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devotional_records`
--

INSERT INTO `devotional_records` (`id`, `user_id`, `date`, `status`, `notes`, `monitored_by`, `monitored_at`, `created_at`, `updated_at`) VALUES
('68009579-47e3-491f-9f5a-44a6f72d6953', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('8997b029-dd50-480a-b935-142a8395be2d', '019ea6ea-b5a6-7267-9941-039d82156436', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('eca3bfab-0c62-447f-808d-d3b18086f3a3', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:09:53'),
('968f73f5-9ca6-4cbc-a043-01fe99114d89', '019ea6ea-b74e-70ef-acb8-0b347cd06ff0', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('fe500352-670c-4ae8-a3c1-9c7806803385', '019ea6ea-b821-713e-9496-aec7e971564c', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('4b73b942-f2fb-47e8-b8d9-10c194f813e2', '019ea6ea-b8f6-727d-8f2a-83be457cb023', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('56d27a23-82f2-4df7-be14-36bef180787a', '019ea6ea-b9d6-72bd-9637-d6282b97dbb3', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('252dc2c4-5885-417a-930b-1e48bf5fb197', '019ea6ea-bab9-7318-a109-6f5f9a200596', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('8a5cbe7e-5dd1-4b15-b486-412d2e674785', '019ea6ea-bb8c-7084-977f-9bedadd0d603', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('cf30c39f-8671-4130-a4ea-c4660c3d8ae5', '019ea6ea-bc82-7341-8cd8-85941ff3b20a', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20'),
('d2365707-e053-4284-bddc-c85dd62dfbc4', '019ea6ea-bd64-72c3-96eb-0c248512057e', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:09:53'),
('781784cf-bdc9-4dcb-b4e0-48594bcd30c9', '019ea6ea-be3d-72be-8264-52cba90579db', '2026-06-08', 'none', NULL, NULL, NULL, '2026-06-08 03:09:53', '2026-06-08 03:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `eod_reports`
--

DROP TABLE IF EXISTS `eod_reports`;
CREATE TABLE IF NOT EXISTS `eod_reports` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_date` date NOT NULL,
  `accomplishments` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tomorrow_plan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `blockers` text COLLATE utf8mb4_unicode_ci,
  `hours_logged` decimal(5,2) DEFAULT NULL,
  `task_ids_completed` json DEFAULT NULL,
  `mood_rating` tinyint UNSIGNED DEFAULT NULL,
  `status` enum('draft','submitted','late','reviewed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `manager_feedback` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eod_reports_user_id_report_date_unique` (`user_id`,`report_date`),
  KEY `eod_reports_reviewed_by_foreign` (`reviewed_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

DROP TABLE IF EXISTS `event_attendees`;
CREATE TABLE IF NOT EXISTS `event_attendees` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_attendees_event_id_user_id_unique` (`event_id`,`user_id`),
  KEY `event_attendees_user_id_foreign` (`user_id`),
  KEY `event_attendees_response_index` (`response`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_attendees`
--

INSERT INTO `event_attendees` (`id`, `event_id`, `user_id`, `response`, `created_at`, `updated_at`) VALUES
(1, '019ea6ea-be6b-730e-a587-126876e00e05', '019ea6ea-b5a6-7267-9941-039d82156436', 'accepted', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
(2, '019ea6ea-be6b-730e-a587-126876e00e05', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', 'accepted', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
(3, '019ea6ea-be6b-730e-a587-126876e00e05', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc', 'pending', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
(4, '019ea6ea-be75-71fb-80af-b46f21a87002', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', 'accepted', '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `movement_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `stock_before` int NOT NULL,
  `stock_after` int NOT NULL,
  `reason` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_product_id_foreign` (`product_id`),
  KEY `inventory_movements_movement_type_index` (`movement_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `product_id`, `movement_type`, `quantity`, `stock_before`, `stock_after`, `reason`, `created_at`, `updated_at`) VALUES
('019ea6ea-be56-718f-bf21-8b16228bca86', '019ea6ea-be50-70ce-9981-0cc55b9c6217', 'in', 100, 0, 100, 'Initial inventory setup', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be58-70fc-9280-b366ea972d3a', '019ea6ea-be52-717e-923d-9496b7682442', 'in', 20, 0, 20, 'Initial inventory setup', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be59-7375-b794-c03f1bdada38', '019ea6ea-be52-717e-923d-9496b7682442', 'out', 12, 20, 8, 'Stock dispatched for shipping department', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be5a-73d7-a344-0fc7c0bbd583', '019ea6ea-be54-7228-9555-c7b2c2308fc1', 'in', 5, 0, 5, 'Initial inventory setup', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be5c-708c-9f81-3655439ffd7c', '019ea6ea-be54-7228-9555-c7b2c2308fc1', 'out', 3, 5, 2, 'Used in warehouse packaging operations', '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_user_id_foreign` (`user_id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  KEY `leave_requests_type_index` (`type`),
  KEY `leave_requests_start_date_index` (`start_date`),
  KEY `leave_requests_end_date_index` (`end_date`),
  KEY `leave_requests_status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_21_000000_create_portal_tables', 1),
(5, '2026_05_21_000002_create_permission_tables', 1),
(6, '2026_05_21_000003_add_position_and_manager_to_users', 1),
(7, '2026_06_08_000000_add_registration_profile_fields_to_users', 1),
(8, '2026_06_08_000001_add_ea_fields_to_users_table', 1),
(9, '2026_06_08_000010_create_spiritual_records_tables', 1),
(10, '2026_06_08_000011_create_ministry_involvement_table', 1),
(11, '2026_06_08_000012_add_spiritual_fields_to_users', 1),
(12, '2026_06_09_000000_create_eod_reports_table', 1),
(13, '2026_06_09_000001_add_eod_settings_to_users', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ministry_involvement`
--

DROP TABLE IF EXISTS `ministry_involvement`;
CREATE TABLE IF NOT EXISTS `ministry_involvement` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eod_date` date NOT NULL,
  `ministry_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ministry_involvement_user_id_foreign` (`user_id`),
  KEY `ministry_involvement_eod_date_index` (`eod_date`),
  KEY `ministry_involvement_ministry_type_index` (`ministry_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc'),
(1, 'App\\Models\\User', '019ea6ee-128b-7060-9117-eaa52fe9a991'),
(2, 'App\\Models\\User', '019ea6ea-b74e-70ef-acb8-0b347cd06ff0'),
(2, 'App\\Models\\User', '019ea6ee-1585-7064-9f20-099c4534d377'),
(3, 'App\\Models\\User', '019ea6ea-b8f6-727d-8f2a-83be457cb023'),
(3, 'App\\Models\\User', '019ea6ee-174b-72c1-9b58-1adba8f95602'),
(5, 'App\\Models\\User', '019ea6ea-b5a6-7267-9941-039d82156436'),
(5, 'App\\Models\\User', '019ea6ee-13b0-73b2-9f1b-40fa90744571'),
(6, 'App\\Models\\User', '019ea6ea-b821-713e-9496-aec7e971564c'),
(6, 'App\\Models\\User', '019ea6ee-1668-7274-8e32-4d060b7701d4'),
(9, 'App\\Models\\User', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67'),
(9, 'App\\Models\\User', '019ea6ee-14a6-70b8-858e-d0dc0b0770d0'),
(9, 'App\\Models\\User', '019ea71c-9549-7245-af0c-6470925465d2'),
(10, 'App\\Models\\User', '019ea6ea-bab9-7318-a109-6f5f9a200596'),
(10, 'App\\Models\\User', '019ea6ee-1906-71c7-9a0d-b158e061a506'),
(11, 'App\\Models\\User', '019ea6ea-bb8c-7084-977f-9bedadd0d603'),
(11, 'App\\Models\\User', '019ea6ee-19e7-70a0-aef4-2e7829bdd5b3'),
(12, 'App\\Models\\User', '019ea6ea-b9d6-72bd-9637-d6282b97dbb3'),
(12, 'App\\Models\\User', '019ea6ee-182d-70bd-bd04-aaaa34b74cba'),
(13, 'App\\Models\\User', '019ea6ea-be3d-72be-8264-52cba90579db'),
(13, 'App\\Models\\User', '019ea6ee-1c7f-71ba-b9ff-1a44623afd47'),
(14, 'App\\Models\\User', '019ea6ea-bc82-7341-8cd8-85941ff3b20a'),
(14, 'App\\Models\\User', '019ea6ee-1ac8-70d2-a289-a6fdca95dab8'),
(15, 'App\\Models\\User', '019ea6ea-bd64-72c3-96eb-0c248512057e'),
(15, 'App\\Models\\User', '019ea6ee-1ba6-726c-bd01-81ca735d884b');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_items`
--

DROP TABLE IF EXISTS `payroll_items`;
CREATE TABLE IF NOT EXISTS `payroll_items` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payroll_period_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regular_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `base_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_items_payroll_period_id_user_id_unique` (`payroll_period_id`,`user_id`),
  KEY `payroll_items_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_periods`
--

DROP TABLE IF EXISTS `payroll_periods`;
CREATE TABLE IF NOT EXISTS `payroll_periods` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_periods_name_unique` (`name`),
  KEY `payroll_periods_start_date_index` (`start_date`),
  KEY `payroll_periods_end_date_index` (`end_date`),
  KEY `payroll_periods_status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_all_spiritual_records', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(2, 'monitor_devotionals', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(3, 'monitor_wednesday_prayer', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(4, 'monitor_sunday_service', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(5, 'send_spiritual_reminders', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(6, 'view_spiritual_reports', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(7, 'manage_cell_groups', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(8, 'view_executive_calendar', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(9, 'manage_executive_calendar', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(10, 'view_team_eod', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(11, 'create_tasks_for_executive', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(12, 'take_meeting_minutes', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_stock` int NOT NULL DEFAULT '0',
  `min_stock_threshold` int NOT NULL DEFAULT '5',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_name_index` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `current_stock`, `min_stock_threshold`, `cost_price`, `created_at`, `updated_at`) VALUES
('019ea6ea-be50-70ce-9981-0cc55b9c6217', 'PROD-001', 'Eco-friendly Packaging Boxes', 100, 20, 1.50, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be52-717e-923d-9496b7682442', 'PROD-002', 'Heavy Duty Bubble Wrap Roll', 8, 15, 12.00, '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be54-7228-9555-c7b2c2308fc1', 'PROD-003', 'Biodegradable Shipping Bags (1000pcs)', 2, 5, 35.00, '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(2, 'hr_manager', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(3, 'finance', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(4, 'general_manager', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(5, 'department_manager', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(6, 'warehouse_manager', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(7, 'warehouse_staff', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(8, 'team_lead', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(9, 'employee', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(10, 'field_staff', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(11, 'intern', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(12, 'payroll_processor', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(13, 'executive_assistant', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(14, 'ceo', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(15, 'cto', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39'),
(16, 'pastoral_lead', 'web', '2026-06-08 03:07:39', '2026-06-08 03:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 16),
(2, 16),
(3, 16),
(4, 16),
(5, 16),
(6, 16),
(7, 16),
(8, 1),
(8, 13),
(9, 1),
(9, 13),
(10, 1),
(10, 13),
(11, 1),
(11, 13),
(12, 1),
(12, 13);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('FjDvUIJ4xvVD3x9wnjTeyvueUpRUvKFpVGgcV67U', '019ea71c-9549-7245-af0c-6470925465d2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', 'eyJfdG9rZW4iOiI0MlE3THFvSDQ1M0NlUjZiNHFUSzE1QWV0cDFQN0Z1OU5COHdaZVhVIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDFcL3NwaXJpdHVhbCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDFcL2Rhc2hib2FyZCIsInJvdXRlIjoiZGFzaGJvYXJkIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOiIwMTllYTcxYy05NTQ5LTcyNDUtYWYwYy02NDcwOTI1NDY1ZDIifQ==', 1780920276);

-- --------------------------------------------------------

--
-- Table structure for table `sunday_service_records`
--

DROP TABLE IF EXISTS `sunday_service_records`;
CREATE TABLE IF NOT EXISTS `sunday_service_records` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sunday_date` date NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `absence_reason` text COLLATE utf8mb4_unicode_ci,
  `monitored_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monitored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sunday_service_records_user_id_sunday_date_unique` (`user_id`,`sunday_date`),
  KEY `sunday_service_records_monitored_by_foreign` (`monitored_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sunday_service_records`
--

INSERT INTO `sunday_service_records` (`id`, `user_id`, `sunday_date`, `attended`, `status`, `absence_reason`, `monitored_by`, `monitored_at`, `created_at`, `updated_at`) VALUES
('3304b38a-2fec-4d8e-9737-268d6977e01e', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('98a34e4b-17fe-4c7f-8ea8-477da2b4d3bb', '019ea6ea-b5a6-7267-9941-039d82156436', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('b0412566-1f02-4d7f-9f8e-bf4a90836350', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('514cc2ec-0191-4781-a507-19be35a52da8', '019ea6ea-b74e-70ef-acb8-0b347cd06ff0', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('93c50b4f-cd97-4dfc-8e6b-276c670606eb', '019ea6ea-b821-713e-9496-aec7e971564c', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('e4448b26-6abc-4c75-b90f-03baf4c5ec5b', '019ea6ea-b8f6-727d-8f2a-83be457cb023', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('87e1e309-ffe1-4b9a-92ff-075561931131', '019ea6ea-b9d6-72bd-9637-d6282b97dbb3', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('6bd602d0-3ee2-47c5-9b19-90329ff610d7', '019ea6ea-bab9-7318-a109-6f5f9a200596', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('6164d68a-b91c-494e-b91d-92bc58c47c40', '019ea6ea-bb8c-7084-977f-9bedadd0d603', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('86a18883-90bf-42be-b3e2-7542dd0736f0', '019ea6ea-bc82-7341-8cd8-85941ff3b20a', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('f950a4bf-9b42-4cf2-930e-1f5b4d9dad11', '019ea6ea-bd64-72c3-96eb-0c248512057e', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('f2e083e7-5453-49b8-b468-b8f19ad33b22', '019ea6ea-be3d-72be-8264-52cba90579db', '2026-06-14', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:11:20', '2026-06-08 03:11:20');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_priority_index` (`priority`),
  KEY `tasks_status_index` (`status`),
  KEY `tasks_due_date_index` (`due_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to`, `priority`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
('019ea6ea-be60-7223-87d6-bae180b13ce9', 'Complete Employee Portal Scaffolding', 'Implement the backend controllers, Eloquent models, and Vite-Vue frontend scaffold for clock-in/out and timesheets.', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', 'high', 'in_progress', '2026-06-11', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be63-739d-9afb-c8d886e2900a', 'Review Q2 Budget Draft', 'Analyze the financial forecast, department costs, and payroll projection spreadsheets for approval.', '019ea6ea-b5a6-7267-9941-039d82156436', 'medium', 'pending', '2026-06-15', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be64-7219-bbe5-bb615bf98437', 'Update Safety Equipment Inventory', 'Perform a physical stock audit of warehouses, verify safety glove and vest counts, and record movements.', '019ea6ea-b821-713e-9496-aec7e971564c', 'critical', 'completed', '2026-06-07', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
('019ea6ea-be65-7063-9b5d-67d4703ddb85', 'Organize Summer Team Building Event', 'Draft proposed activity list, gather venue quotes, and send out invitations to all department managers.', '019ea6ea-b74e-70ef-acb8-0b347cd06ff0', 'low', 'pending', '2026-06-22', '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_task_id_foreign` (`task_id`),
  KEY `task_comments_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`, `updated_at`) VALUES
(1, '019ea6ea-be60-7223-87d6-bae180b13ce9', '019ea6ea-b5a6-7267-9941-039d82156436', 'Please ensure GPS coordinates are captured automatically during the clock-in/out form submission.', '2026-06-08 03:07:42', '2026-06-08 03:07:42'),
(2, '019ea6ea-be60-7223-87d6-bae180b13ce9', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', 'Working on it! Using HTML5 browser geolocation API to populate lat/long coordinates seamlessly.', '2026-06-08 03:07:42', '2026-06-08 03:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civil_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincial_address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_full_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_occupation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_full_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mother_occupation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guardian_contact_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_employed` date DEFAULT NULL,
  `employee_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Trainee',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supports_executive_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contract','intern') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full_time',
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `monthly_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `department` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipleship_lead_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cell_group_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cell_group_role` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spiritual_birthday` date DEFAULT NULL,
  `prayer_partner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receive_devotional_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `receive_prayer_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_eod` tinyint(1) NOT NULL DEFAULT '1',
  `eod_cutoff_time` time NOT NULL DEFAULT '18:00:00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_employee_code_unique` (`employee_code`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_manager_id_foreign` (`manager_id`),
  KEY `users_supports_executive_id_foreign` (`supports_executive_id`),
  KEY `users_discipleship_lead_id_foreign` (`discipleship_lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_code`, `last_name`, `first_name`, `middle_name`, `sex`, `birth_date`, `birth_place`, `nationality`, `civil_status`, `current_address`, `provincial_address`, `contact_number`, `father_full_name`, `father_occupation`, `mother_full_name`, `mother_occupation`, `guardian`, `guardian_contact_number`, `date_employed`, `employee_status`, `name`, `email`, `email_verified_at`, `password`, `role`, `position`, `supports_executive_id`, `employment_type`, `contract_start_date`, `contract_end_date`, `hourly_rate`, `monthly_salary`, `department`, `manager_id`, `discipleship_lead_id`, `cell_group_name`, `cell_group_role`, `spiritual_birthday`, `prayer_partner`, `receive_devotional_reminders`, `receive_prayer_reminders`, `is_active`, `remember_token`, `requires_eod`, `eod_cutoff_time`, `created_at`, `updated_at`) VALUES
('019ea6ee-1c7f-71ba-b9ff-1a44623afd47', 'EMP-012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Emma Assistant', 'ea@example.com', NULL, '$2y$12$0cnN65HtNNcMY9C6aZQ5VOq24t9xN7SLqyDkqFdJqhqyUNuXKc7lm', 'executive_assistant', 'Executive Assistant to CEO', '019ea6ee-1ac8-70d2-a289-a6fdca95dab8', 'full_time', '2026-06-08', NULL, 35.00, 5000.00, 'Executive Office', '019ea6ee-1ac8-70d2-a289-a6fdca95dab8', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1ba6-726c-bd01-81ca735d884b', 'EMP-011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Teresa CTO', 'cto@example.com', NULL, '$2y$12$sUJapS8yIubgOqqXxJvcVOfcEWZ9itDYET47cfKLrH.MokQlzmrFq', 'cto', 'Chief Technology Officer', NULL, 'full_time', NULL, NULL, 90.00, 13000.00, 'Executive Office', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:23', '2026-06-08 03:11:23'),
('019ea6ee-1ac8-70d2-a289-a6fdca95dab8', 'EMP-010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Vincent CEO', 'ceo@example.com', NULL, '$2y$12$7Q3KVeTSRrfvaeGgHpTY0eoFt/d3m16c1XTesJ/ANgU5.eg1KdikK', 'ceo', 'Chief Executive Officer', NULL, 'full_time', NULL, NULL, 100.00, 15000.00, 'Executive Office', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:22', '2026-06-08 03:11:22'),
('019ea6ee-19e7-70a0-aef4-2e7829bdd5b3', 'EMP-009', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Ian Intern', 'intern@example.com', NULL, '$2y$12$NeyfxvzPJQRW1wOnRoWn4uACSEzd8EbezPDsHEMk.ZuNSt/k3G6dy', 'intern', 'Engineering Intern', NULL, 'full_time', NULL, NULL, 15.00, 0.00, 'Engineering', '019ea6ee-13b0-73b2-9f1b-40fa90744571', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:22', '2026-06-08 03:11:22'),
('019ea6ee-1906-71c7-9a0d-b158e061a506', 'EMP-008', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Frank Field', 'field@example.com', NULL, '$2y$12$Isw0R9H8m4ZN8hAy4jzHu.15XqOGhYUkaf60hnEea4tmhiIOHFWbu', 'field_staff', 'Field Technician', NULL, 'full_time', NULL, NULL, 22.00, 0.00, 'Operations', '019ea6ee-13b0-73b2-9f1b-40fa90744571', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:22', '2026-06-08 03:11:22'),
('019ea6ee-182d-70bd-bd04-aaaa34b74cba', 'EMP-007', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Peter Payroll', 'payroll@example.com', NULL, '$2y$12$bBXpSHvn61iVUSo4vCNVoeSYj5EFfDNYgIf9yQJFW5Tq/9uuOco2y', 'payroll_processor', 'Payroll Processor', NULL, 'full_time', NULL, NULL, 40.00, 6500.00, 'Finance', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:22', '2026-06-08 03:11:22'),
('019ea6ee-174b-72c1-9b58-1adba8f95602', 'EMP-006', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Fiona Finance', 'finance@example.com', NULL, '$2y$12$2Njv3V2cLGzpChSNb/xtaebiJXXIjumFeZmXVuWtMfKY7MjRwTzQO', 'finance', 'Finance Specialist', NULL, 'full_time', NULL, NULL, 45.00, 7000.00, 'Finance', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:21', '2026-06-08 03:11:21'),
('019ea6ee-1668-7274-8e32-4d060b7701d4', 'EMP-005', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Charlie Warehouse', 'warehouse@example.com', NULL, '$2y$12$veHKyjgLXuBgUveto/IQnu3.PQU0j4Itr1dAOrVDHzVAd6HyLRT/W', 'warehouse_manager', 'Warehouse Manager', NULL, 'full_time', NULL, NULL, 20.00, 3500.00, 'Logistics', '019ea6ee-128b-7060-9117-eaa52fe9a991', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:21', '2026-06-08 03:11:22'),
('019ea6ee-1585-7064-9f20-099c4534d377', 'EMP-004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Alice HR', 'hr@example.com', NULL, '$2y$12$iXmsFCIS9PaYBBzwm156Y.mIvY2xvVdtO32uHXfrFpZxB8f9idHTu', 'hr_manager', 'HR Manager', NULL, 'full_time', NULL, NULL, 30.00, 4500.00, 'Human Resources', '019ea6ee-128b-7060-9117-eaa52fe9a991', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:21', '2026-06-08 03:11:22'),
('019ea6ee-14a6-70b8-858e-d0dc0b0770d0', 'EMP-003', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Bob Employee', 'employee@example.com', NULL, '$2y$12$GaqYXPYt/lWxdJkpEEzVGOQtBZrScmMeHxAC5pzVo.uqIu8ru7Lia', 'employee', 'Software Engineer', NULL, 'full_time', NULL, NULL, 25.00, 0.00, 'Engineering', '019ea6ee-13b0-73b2-9f1b-40fa90744571', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:21', '2026-06-08 03:11:22'),
('019ea6ee-128b-7060-9117-eaa52fe9a991', 'EMP-001', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'John Admin', 'admin@example.com', NULL, '$2y$12$7NOR44SdbFG3IvCZ8epAuO8XhbFdPbMs.nm5xa6Q9hJOELszx1ebW', 'super_admin', 'Chief Executive Officer', NULL, 'full_time', NULL, NULL, 50.00, 8000.00, 'Executive', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:20', '2026-06-08 03:11:20'),
('019ea6ee-13b0-73b2-9f1b-40fa90744571', 'EMP-002', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Trainee', 'Jane Manager', 'manager@example.com', NULL, '$2y$12$rCQWANEbjFZRm5uNUB8Tn.qUCaCabtkPEeB4khVsJMO9wnmR7JEBm', 'department_manager', 'Engineering Manager', NULL, 'full_time', NULL, NULL, 40.00, 6000.00, 'Engineering', '019ea6ee-128b-7060-9117-eaa52fe9a991', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 03:11:21', '2026-06-08 03:11:22'),
('019ea71c-9549-7245-af0c-6470925465d2', 'EMP-013', 'Uligan', 'Jonel', 'Sapul', 'Male', '2001-04-05', 'Baguio', 'Filipino', 'Single', 'Betag, La Trinidad, Benguet', 'Tabaan Sur, Tuba, Benguet', '09694581299', 'George Uligan', 'Construction and Maintenance Man', 'Judith Uligan', 'Barangay Health Worker', 'Judelyn Uligan', 'N/A', '2025-07-01', 'Full Time', 'Jonel Sapul Uligan', 'jonel.uligan7@gmail.com', NULL, '$2y$12$MjqlJuSy8djGgEMlucZLTOBRG.oZ3EVNO9YG5Jvg9KesMyJpUCbZS', 'super_admin', NULL, NULL, 'full_time', NULL, NULL, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 1, '18:00:00', '2026-06-08 04:02:08', '2026-06-08 04:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `wednesday_prayer_records`
--

DROP TABLE IF EXISTS `wednesday_prayer_records`;
CREATE TABLE IF NOT EXISTS `wednesday_prayer_records` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wednesday_date` date NOT NULL,
  `attended` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `absence_reason` text COLLATE utf8mb4_unicode_ci,
  `monitored_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monitored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wednesday_prayer_records_user_id_wednesday_date_unique` (`user_id`,`wednesday_date`),
  KEY `wednesday_prayer_records_monitored_by_foreign` (`monitored_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wednesday_prayer_records`
--

INSERT INTO `wednesday_prayer_records` (`id`, `user_id`, `wednesday_date`, `attended`, `status`, `absence_reason`, `monitored_by`, `monitored_at`, `created_at`, `updated_at`) VALUES
('47c6917f-a867-48ea-ae38-43d75b8f31f7', '019ea6ea-b4ce-7192-83f1-7e4f68c9a2dc', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('86fed0a2-001b-455c-ba91-a913c9944a8d', '019ea6ea-b5a6-7267-9941-039d82156436', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('6fa1ffd7-fa71-4a6d-b2eb-2a7cd01f52af', '019ea6ea-b67a-70ba-91e7-67b78f4a5c67', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('ac36f12c-41bc-4f1d-91f3-107dae348ee5', '019ea6ea-b74e-70ef-acb8-0b347cd06ff0', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('59310e0e-f369-4150-91d5-1ed8a5100c59', '019ea6ea-b821-713e-9496-aec7e971564c', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('ebd6124e-0518-498e-8149-f9c0675bb32b', '019ea6ea-b8f6-727d-8f2a-83be457cb023', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('a76adae3-2595-4e71-a519-caac390baac0', '019ea6ea-b9d6-72bd-9637-d6282b97dbb3', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('4d1486b8-df17-4feb-a825-c0b9a7e0a507', '019ea6ea-bab9-7318-a109-6f5f9a200596', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('0eb8bd7f-27bd-4c0b-bfc1-22dc65cb86f0', '019ea6ea-bb8c-7084-977f-9bedadd0d603', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('43eb94a2-ae4b-47c1-a7fd-4a37982f2779', '019ea6ea-bc82-7341-8cd8-85941ff3b20a', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('f2d7e8dd-bb33-4e4b-b5a6-5756f79927e3', '019ea6ea-bd64-72c3-96eb-0c248512057e', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17'),
('e63ae913-f901-4d94-ae0c-3c9c0a7c5090', '019ea6ea-be3d-72be-8264-52cba90579db', '2026-06-10', 0, 'absent', NULL, NULL, NULL, '2026-06-08 03:10:17', '2026-06-08 03:10:17');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

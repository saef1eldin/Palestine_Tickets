-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 25 أغسطس 2025 الساعة 12:17
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tickets_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin_activity_log`
--

CREATE TABLE `admin_activity_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action_type`, `target_type`, `target_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 07:59:41'),
(2, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:01:26'),
(3, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:02:41'),
(4, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:23:43'),
(5, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:27:24'),
(6, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:27:38'),
(7, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-01 07:34:01'),
(8, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:44:51'),
(9, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:45:41'),
(10, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:45:52'),
(11, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:46:44'),
(12, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:46:58'),
(13, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:47:47'),
(14, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:47:53'),
(15, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:48:05'),
(16, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:48:11'),
(17, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:48:14'),
(18, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:48:20'),
(19, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:49:14'),
(20, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:49:26'),
(21, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:49:38'),
(22, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:49:45'),
(23, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:49:50'),
(24, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:51:54'),
(25, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:51:58'),
(26, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:53:36'),
(27, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:53:47'),
(28, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 07:54:15'),
(29, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:00:08'),
(30, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:00:15'),
(31, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:04:40'),
(32, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:06:07'),
(33, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:07:33'),
(34, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:07:46'),
(35, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:08:19'),
(36, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:09:22'),
(37, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:09:49'),
(38, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:11:16'),
(39, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:11:37'),
(40, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:15:00'),
(41, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:15:01'),
(42, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:17:07'),
(43, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:17:31'),
(44, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:30:58'),
(45, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:34:38'),
(46, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:34:45'),
(47, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:35:47'),
(48, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:35:56'),
(49, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:36:00'),
(50, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:37:18'),
(51, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:38:14'),
(52, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:38:45'),
(53, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:38:54'),
(54, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:00'),
(55, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:12'),
(56, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:21'),
(57, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:36'),
(58, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:45'),
(59, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:39:48'),
(60, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:40:29'),
(61, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 08:40:31'),
(62, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:10:08'),
(63, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:10:31'),
(64, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:28:28'),
(65, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:29:43'),
(66, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:31:28'),
(67, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:31:34'),
(68, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:32:18'),
(69, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:33:48'),
(70, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:34:08'),
(71, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:34:48'),
(72, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-24 09:35:13'),
(73, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-25 08:18:07'),
(74, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-25 08:18:33'),
(75, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-25 10:13:14'),
(76, 1, 'system_cleanup', NULL, NULL, 'تم تنظيف 1 فعالية، 2 رحلة، 1 تذكرة، 2 حجز مواصلات', NULL, NULL, '2025-08-25 10:13:23');

-- --------------------------------------------------------

--
-- بنية الجدول `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','error','success') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 'تم حذف رحلة منتهية تلقائياً: خزاعة إلى مهرجان فلسطين للتراث والفنون الشعبية وقت المغادرة: 2025-07-20 14:04:00', 'warning', 1, '2025-08-23 07:36:10'),
(2, 'تم حذف رحلة منتهية تلقائياً: خزاعة إلى مؤتمر إعادة إعمار غزة وقت المغادرة: 2025-06-15 07:28:00', 'warning', 1, '2025-08-23 07:36:10'),
(3, 'تم حذف رحلة منتهية تلقائياً: عبسان إلى معرض غزة للصناعات اليدوية وقت المغادرة: 2025-07-05 07:37:00', 'warning', 1, '2025-08-23 07:36:10'),
(4, 'تم حذف 3 رحلة منتهية تلقائياً', 'info', 1, '2025-08-23 07:36:10'),
(5, 'تقرير النظام اليومي:\n- الرحلات النشطة: 0\n- الحجوزات المعلقة: 0\n- الإشعارات غير المقروءة: 0', 'info', 1, '2025-08-23 07:42:54'),
(6, 'تقرير النظام اليومي:\n- الرحلات النشطة: 21\n- الحجوزات المعلقة: 0\n- الإشعارات غير المقروءة: 1', 'info', 1, '2025-08-23 07:51:09'),
(7, 'تم حذف رحلة منتهية: البريج1 إلى معرض غزة للصناعات اليدوية - 2025-07-05 08:49:00', 'warning', 1, '2025-08-23 08:31:55'),
(8, 'تم حذف رحلة منتهية: البريج1 إلى مهرجان غزة للأطفال - 2025-06-01 07:46:00', 'warning', 1, '2025-08-23 08:31:55'),
(9, 'تم حذف رحلة منتهية: المغازي إلى معرض غزة الدولي للكتاب - 2025-08-10 06:20:00', 'warning', 1, '2025-08-23 08:31:55'),
(10, 'تم حذف رحلة منتهية: بيت حانون إلى معرض غزة الدولي للكتاب - 2025-08-10 07:49:00', 'warning', 1, '2025-08-23 08:31:55'),
(11, 'تم حذف رحلة منتهية: بيت حانون إلى مهرجان فلسطين للتراث والفنون الشعبية - 2025-07-20 14:59:00', 'warning', 1, '2025-08-23 08:31:55'),
(12, 'تم حذف رحلة منتهية: بيت لاهيا إلى مهرجان غزة للأطفال - 2025-06-01 08:49:00', 'warning', 1, '2025-08-23 08:31:55'),
(13, 'تم حذف رحلة منتهية: بيت لاهيا إلى مؤتمر إعادة إعمار غزة - 2025-06-15 05:51:00', 'warning', 1, '2025-08-23 08:31:55'),
(14, 'تم حذف رحلة منتهية: جباليا إلى مؤتمر غزة للطب والصحة - 2025-08-20 05:50:00', 'warning', 1, '2025-08-23 08:31:55'),
(15, 'تم حذف رحلة منتهية: جباليا إلى معرض غزة للصناعات اليدوية - 2025-07-05 07:44:00', 'warning', 1, '2025-08-23 08:31:55'),
(16, 'تم حذف رحلة منتهية: دير البلح إلى مهرجان فلسطين للتراث والفنون الشعبية - 2025-07-20 15:21:00', 'warning', 1, '2025-08-23 08:31:55'),
(17, 'تم حذف رحلة منتهية: غزة إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(18, 'تم حذف رحلة منتهية: غزة إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(19, 'تم حذف رحلة منتهية: رفح إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(20, 'تم حذف رحلة منتهية: رفح إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(21, 'تم حذف رحلة منتهية: النصيرات إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(22, 'تم حذف رحلة منتهية: النصيرات إلى مهرجان غزة للموسيقى والغناء - 0000-00-00 00:00:00', 'warning', 1, '2025-08-23 08:31:55'),
(23, 'تم حذف رحلة منتهية: غزة إلى مهرجان غزة للموسيقى والغناء - 2025-08-23 09:12:40', 'warning', 1, '2025-08-23 08:31:55');

-- --------------------------------------------------------

--
-- بنية الجدول `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_type` enum('transport','notifications','site','super') NOT NULL,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `user_id`, `permission_type`, `granted_by`, `granted_at`, `is_active`) VALUES
(1, 6, 'transport', NULL, '2025-05-30 07:49:41', 1),
(2, 8, 'super', NULL, '2025-05-30 08:02:41', 1),
(3, 8, 'transport', NULL, '2025-05-30 08:02:41', 1),
(4, 8, 'notifications', NULL, '2025-05-30 08:02:41', 1),
(5, 8, 'site', NULL, '2025-05-30 08:02:41', 1),
(10, 9, 'transport', 8, '2025-05-30 08:01:26', 1),
(11, 10, 'notifications', 8, '2025-05-30 08:01:26', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `admin_profile`
--

CREATE TABLE `admin_profile` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL DEFAULT 'المدير',
  `email` varchar(255) NOT NULL DEFAULT 'admin@transport.com',
  `profile_image` varchar(500) DEFAULT 'assets/12.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_profile`
--

INSERT INTO `admin_profile` (`id`, `admin_id`, `name`, `email`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'المدير11', 'admin@transport.com', 'uploads/profile/admin_profile_1756114475.jpg', '2025-08-24 06:19:59', '2025-08-25 09:58:49');

-- --------------------------------------------------------

--
-- بنية الجدول `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`) VALUES
(1, 'ةةة', 'admin@example.com', 'ةةة', 'ةة', '2025-04-28 07:51:28', 1);

-- --------------------------------------------------------

--
-- بنية الجدول `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `expiry_date`, `usage_limit`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, '2023-12-30', 100, '2025-04-21 09:41:54', '2025-04-23 07:07:54'),
(2, 'SUMMER50', 'fixed', 50.00, '2023-09-30', 50, '2025-04-21 09:41:54', '2025-04-21 09:41:54'),
(3, 'VIP25', 'percentage', 25.00, '2023-12-30', 20, '2025-04-21 09:41:54', '2025-04-23 07:02:00');

-- --------------------------------------------------------

--
-- بنية الجدول `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `date_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(11) DEFAULT 100,
  `original_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `organizer` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `available_tickets` int(11) NOT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `location`, `date_time`, `end_time`, `price`, `capacity`, `original_price`, `category`, `organizer`, `contact_email`, `contact_phone`, `is_featured`, `status`, `is_active`, `image`, `available_tickets`, `featured`, `created_at`, `updated_at`) VALUES
(1, 'مهرجان غزة للموسيقى والغناء', 'مهرجان موسيقي رائع يضم أفضل الفنانين الفلسطينيين في أمسية لا تُنسى مليئة بالموسيقى والغناء التراثي والمعاصر', 'مسرح غزة الثقافي - شارع الوحدة', '2025-06-06 14:13:34', '2025-06-06 17:13:34', 30.00, 100, NULL, 'حفلات موسيقية', 'وزارة الثقافة الفلسطينية', 'info@culture.ps', '08-2345678', 1, 'expired', 1, 'https://via.placeholder.com/400x300/4CAF50/white?text=مهرجان+غزة+للموسيقى', 97, 1, '2025-05-30 11:13:34', '2025-08-24 07:44:51'),
(2, 'أمسية شعرية فلسطينية', 'أمسية شعرية تضم نخبة من الشعراء الفلسطينيين المعاصرين', 'رفح - المركز الثقافي', '2024-02-20 18:00:00', '2024-02-20 21:00:00', 15.00, 200, 20.00, 'أدبي', 'اتحاد الكتاب الفلسطينيين', 'writers@palestine.ps', '+970-8-234-5678', 0, 'expired', 1, 'poetry_evening.jpg', 180, 0, '2025-05-31 10:02:13', '2025-08-24 07:44:51'),
(3, 'حفل موسيقي تراثي', 'حفل موسيقي يقدم التراث الموسيقي الفلسطيني الأصيل', 'خان يونس - قاعة الشهداء', '2024-02-25 20:00:00', '2024-02-25 23:30:00', 30.00, 300, 40.00, 'موسيقي', 'فرقة الأصالة الفلسطينية', 'music@palestine.ps', '+970-8-345-6789', 1, 'expired', 1, 'traditional_music.jpg', 250, 1, '2025-05-31 10:02:13', '2025-08-24 07:44:51'),
(14, 'مؤتمر إعادة إعمار غزة', 'مؤتمر دولي لمناقشة خطط وآليات إعادة إعمار قطاع غزة بعد الحرب. يجمع المؤتمر خبراء في مجالات البناء والتخطيط العمراني والاقتصاد والتنمية المستدامة، بالإضافة إلى ممثلين عن المنظمات الدولية والمؤسسات المانحة. سيتم خلال المؤتمر عرض مشاريع إعادة الإعمار وفرص الاستثمار في قطاع غزة.', 'قاعة المؤتمرات الكبرى - فندق المشتل - غزة', '2025-06-15 09:00:00', '0000-00-00 00:00:00', 70.00, 500, 100.00, 'مؤتمرات', 'وزارة الأشغال العامة والإسكان', 'info@reconstruction-gaza.ps', '+970 8 2823400', 1, 'expired', 1, 'uploads/events/1746622139_8c7ee2fc-1bac-470f-894b-32872f692183.png', 997, 0, '2025-05-07 10:08:40', '2025-08-24 07:44:51'),
(15, 'مهرجان فلسطين للتراث والفنون الشعبية', 'مهرجان سنوي يحتفي بالتراث والفنون الشعبية الفلسطينية. يتضمن المهرجان عروضاً للدبكة الشعبية والأغاني التراثية، بالإضافة إلى معارض للأزياء والحرف اليدوية والمأكولات الشعبية. يهدف المهرجان إلى الحفاظ على الهوية الثقافية الفلسطينية ونقلها للأجيال القادمة.', 'ساحة الكتيبة - مدينة غزة', '2025-07-20 17:00:00', '0000-00-00 00:00:00', 20.00, 2000, 22.00, 'مهرجانات', 'وزارة الثقافة الفلسطينية', 'info@palestine-heritage.ps', '+970 8 2829777', 1, 'expired', 1, 'uploads/events/1746621032_0fb87bad-636a-4211-a0c5-3d3d5bbb6ebc.png', 58, 0, '2025-05-07 10:08:40', '2025-08-24 07:44:51'),
(16, 'معرض غزة الدولي للكتاب', 'معرض سنوي للكتاب يقام في مدينة غزة، ويضم أجنحة لدور نشر محلية وعربية ودولية. يتضمن المعرض فعاليات ثقافية متنوعة مثل ندوات أدبية وحفلات توقيع كتب وورش عمل للأطفال. يهدف المعرض إلى تشجيع القراءة ونشر الثقافة في المجتمع الفلسطيني.', 'صالة رشاد الشوا الثقافية - غزة', '2025-08-10 10:00:00', '0000-00-00 00:00:00', 7.00, 1500, NULL, 'معارض', 'اتحاد الناشرين الفلسطينيين', 'info@gaza-bookfair.ps', '+970 8 2845566', 1, 'expired', 1, 'uploads/events/1746620685_8712179f-b4a5-41bf-a0d9-d051b2782401.png', 300, 0, '2025-05-07 10:08:40', '2025-08-24 07:44:51'),
(17, 'مؤتمر غزة للتكنولوجيا والابتكار', 'مؤتمر تقني يجمع خبراء ومختصين في مجالات التكنولوجيا والبرمجة والذكاء الاصطناعي. يهدف المؤتمر إلى تعزيز ثقافة الابتكار وريادة الأعمال في قطاع غزة، وتوفير منصة للشباب لعرض مشاريعهم التقنية والتواصل مع المستثمرين والشركات العالمية.', 'الجامعة الإسلامية - غزة', '2025-09-05 09:30:00', '0000-00-00 00:00:00', 30.00, 800, 35.00, 'مؤتمرات', 'حاضنة الأعمال والتكنولوجيا - الجامعة الإسلامية', 'info@gazatech.ps', '+970 8 2644400', 0, 'active', 1, 'uploads/events/1746619369_f755bb97-9ad2-483a-a884-7f3f41742a71.png', 75, 0, '2025-05-07 10:08:40', '2025-05-07 12:03:08'),
(18, 'مهرجان غزة السينمائي', 'مهرجان سينمائي يعرض أفلاماً فلسطينية وعربية وعالمية، ويسلط الضوء على قضايا المجتمع الفلسطيني والواقع المعاش في قطاع غزة. يتضمن المهرجان مسابقة للأفلام القصيرة وورش عمل في مجال صناعة السينما وندوات نقدية.', 'مسرح وسينما السعادة - غزة', '2025-10-12 18:00:00', '0000-00-00 00:00:00', 15.00, 400, 30.00, 'مهرجانات', 'مؤسسة السينما الفلسطينية', 'info@gazafilmfest.ps', '+970 8 2861122', 0, 'active', 1, 'uploads/events/1746617175_3b197966-9853-417f-a491-eac76e5de25f.png', 250, 0, '2025-05-07 10:08:40', '2025-05-07 11:26:15'),
(19, 'معرض فلسطين للفنون التشكيلية', 'معرض فني يضم أعمالاً لفنانين تشكيليين من غزة والضفة الغربية والشتات. يعرض المعرض لوحات ومنحوتات وأعمالاً فنية تعبر عن الهوية الفلسطينية والواقع المعاش. يهدف المعرض إلى دعم الفنانين الفلسطينيين وإبراز إبداعاتهم للعالم.', 'متحف فلسطين - غزة', '2025-11-01 16:00:00', '0000-00-00 00:00:00', 21.00, 300, 30.00, 'معارض', 'جمعية الفنانين التشكيليين الفلسطينيين', 'info@palestine-art.ps', '+970 8 2833344', 0, 'active', 1, 'uploads/events/1746616997_0e9a6880-bf14-4d14-af1a-55decb3cb08f.png', 120, 0, '2025-05-07 10:08:40', '2025-05-07 11:23:17'),
(20, 'مؤتمر غزة للتنمية المستدامة', 'مؤتمر علمي يناقش قضايا التنمية المستدامة وتحدياتها في قطاع غزة، مع التركيز على مجالات الطاقة المتجددة والزراعة المستدامة وإدارة الموارد المائية. يجمع المؤتمر باحثين وخبراء محليين ودوليين لتبادل الخبرات وعرض التجارب الناجحة.', 'جامعة الاسلامية- غزة', '2025-12-07 09:00:00', '0000-00-00 00:00:00', 25.00, 600, 30.00, 'مؤتمرات', 'مركز دراسات التنمية - جامعة الأزهر', 'info@sustainable-gaza.ps', '+970 8 2641884', 0, 'active', 1, 'uploads/events/1746615626_42ecd886-b11b-47e3-a2f9-3020df0befca.png', 80, 0, '2025-05-07 10:08:40', '2025-05-07 11:32:27'),
(21, 'مهرجان غزة للأطفال', 'مهرجان سنوي مخصص للأطفال في قطاع غزة، يتضمن فعاليات ترفيهية وتعليمية متنوعة. يشمل المهرجان عروض مسرحية وألعاب تفاعلية وورش رسم وأنشطة رياضية. يهدف المهرجان إلى إدخال البهجة على قلوب الأطفال وتنمية مهاراتهم الإبداعية والاجتماعية في ظل الظروف الصعبة التي يعيشونها.', 'حديقة البلدية المركزية - غزة', '2025-06-01 10:00:00', '0000-00-00 00:00:00', 5.00, 1000, 7.00, 'مهرجانات', 'مؤسسة تامر للتعليم المجتمعي', 'info@children-festival.ps', '+970 8 2822494', 1, 'expired', 1, 'uploads/events/1746622368_dda97cd3-37bf-4b85-af09-8511e526388a.png', 0, 0, '2025-05-07 10:21:06', '2025-08-24 07:44:51'),
(22, 'معرض غزة للصناعات اليدوية', 'معرض يجمع الحرفيين والفنانين من مختلف أنحاء قطاع غزة لعرض منتجاتهم اليدوية التقليدية والمعاصرة. يضم المعرض أقساماً متنوعة للتطريز الفلسطيني والخزف والنحت على الخشب والزجاج والصدف وصناعة السجاد والبسط. يهدف المعرض إلى الحفاظ على الحرف التقليدية الفلسطينية ودعم الحرفيين اقتصادياً.', 'قاعة المعارض - جامعة فلسطين - غزة', '2025-07-05 11:00:00', '0000-00-00 00:00:00', 10.00, 500, 13.00, 'معارض', 'اتحاد الحرفيين الفلسطينيين', 'info@gaza-crafts.ps', '+970 8 2865577', 0, 'expired', 1, 'uploads/events/1746621493_796806aa-bc85-4c51-a00e-17b46243bbbf.png', 68, 0, '2025-05-07 10:21:06', '2025-08-24 07:44:51'),
(23, 'مؤتمر غزة للطب والصحة', 'مؤتمر علمي يجمع الأطباء والباحثين في المجال الصحي لمناقشة آخر المستجدات والتحديات الصحية في قطاع غزة. يتضمن المؤتمر محاضرات وورش عمل في مختلف التخصصات الطبية، بالإضافة إلى معرض للأجهزة والمستلزمات الطبية. يهدف المؤتمر إلى تبادل الخبرات وتطوير القطاع الصحي في ظل الظروف الصعبة.', 'مستشفى الشفاء - غزة', '2025-08-20 09:00:00', '0000-00-00 00:00:00', 40.00, 400, 70.00, 'مؤتمرات', 'نقابة الأطباء الفلسطينيين', 'info@medical-conference.ps', '+970 8 2866990', 1, 'expired', 1, 'uploads/events/1746620534_30960317-0416-49f7-8e1c-d48488b22295.png', 35, 0, '2025-05-07 10:21:06', '2025-08-24 07:44:51'),
(24, 'مهرجان غزة للمأكولات الشعبية', 'مهرجان يحتفي بالمطبخ الفلسطيني التقليدي ويعرض مجموعة متنوعة من الأطباق والحلويات الشعبية. يشارك في المهرجان طهاة محترفون وربات بيوت من مختلف مناطق قطاع غزة، ويتضمن مسابقات طهي وعروض تحضير الأطباق الشهيرة مثل المسخن والمقلوبة والكنافة. يهدف المهرجان إلى الحفاظ على التراث الغذائي الفلسطيني.', 'ساحة السرايا - وسط مدينة غزة', '2025-09-15 16:00:00', '0000-00-00 00:00:00', 18.00, 1500, 20.00, 'مهرجانات', 'جمعية الطهاة الفلسطينيين', 'info@gaza-food.ps', '+970 8 2877788', 1, 'active', 1, 'uploads/events/1746617783_a5de241b-5e8a-4fb8-8be6-f190a9776ccc.png', 68, 0, '2025-05-07 10:21:06', '2025-05-07 11:36:23'),
(25, 'مؤتمر غزة للزراعة المستدامة', 'مؤتمر متخصص في مجال الزراعة المستدامة وتقنيات الزراعة الحديثة المناسبة لظروف قطاع غزة. يناقش المؤتمر قضايا الأمن الغذائي وإدارة الموارد المائية الشحيحة وطرق زيادة الإنتاج الزراعي. يشارك في المؤتمر خبراء محليون ودوليون لتبادل الخبرات والتجارب الناجحة في مجال الزراعة في المناطق ذات الظروف المشابهة.', 'كلية الزراعة - جامعة الاسلامية - غزة', '2025-10-10 09:30:00', '0000-00-00 00:00:00', 25.00, 300, NULL, 'مؤتمرات', 'وزارة الزراعة الفلسطينية', 'info@sustainable-agriculture.ps', '+970 8 2855566', 0, 'active', 1, 'uploads/events/1746617521_17829497-5919-4575-a236-7955951739c8.png', 300, 0, '2025-05-07 10:21:06', '2025-05-07 11:32:01'),
(26, 'معرض غزة للفنون المعاصرة', 'معرض فني يضم أعمالاً لفنانين شباب من قطاع غزة في مجالات الرسم والنحت والتصوير الفوتوغرافي والفن الرقمي. يسلط المعرض الضوء على الإبداعات الفنية المعاصرة التي تعبر عن الواقع والتحديات والطموحات في غزة. يهدف المعرض إلى دعم الفنانين الشباب وتوفير منصة لعرض أعمالهم وتبادل الخبرات.', 'مركز رشاد الشوا الثقافي - غزة', '2025-11-15 17:00:00', '0000-00-00 00:00:00', 15.00, 250, 20.00, 'معارض', 'مجموعة الفنانين الشباب', 'info@gaza-art.ps', '+970 8 2833344', 0, 'active', 1, 'uploads/events/1746616718_6f72ac15-0da1-4305-aaac-c4ce1d63a3b3.png', 75, 0, '2025-05-07 10:21:06', '2025-05-07 11:18:38'),
(27, 'مهرجان غزة للموسيقى والغناء', 'مهرجان موسيقي يجمع فرقاً وفنانين من قطاع غزة والضفة الغربية لتقديم عروض موسيقية وغنائية متنوعة. يشمل المهرجان أمسيات للموسيقى الشرقية والغربية والفلكلور الفلسطيني، بالإضافة إلى ورش موسيقية للأطفال والشباب. يهدف المهرجان إلى إحياء التراث الموسيقي الفلسطيني وتشجيع المواهب الشابة.', 'مسرح وسينما السعادة - غزة', '2025-12-20 18:00:00', '0000-00-00 00:00:00', 22.00, 600, 30.00, 'مهرجانات', 'فرقة العاشقين للفنون الشعبية', 'info@gaza-music.ps', '+970 8 2844455', 1, 'active', 1, 'uploads/events/1746614744_e58e1dee-a17e-4837-bb1b-1a6be3d4bd9b.png', 29, 0, '2025-05-07 10:21:06', '2025-05-30 10:14:57');

-- --------------------------------------------------------

--
-- بنية الجدول `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('paid','pending','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `user_agent` text NOT NULL,
  `browser` varchar(100) NOT NULL,
  `os` varchar(100) NOT NULL,
  `device` varchar(100) NOT NULL,
  `login_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `login_logs`
--

INSERT INTO `login_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `browser`, `os`, `device`, `login_time`, `created_at`) VALUES
(1, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-04-28 10:48:38', '2025-04-28 07:48:38'),
(2, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-04-29 10:03:33', '2025-04-29 07:03:33'),
(3, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-04-29 10:52:16', '2025-04-29 07:52:16'),
(4, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-04-29 11:12:56', '2025-04-29 08:12:56'),
(5, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-04-30 09:20:25', '2025-04-30 06:20:25'),
(6, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:20:30', '2025-05-01 17:20:30'),
(7, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:25:49', '2025-05-01 17:25:49'),
(8, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:27:02', '2025-05-01 17:27:02'),
(9, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:45:13', '2025-05-01 17:45:13'),
(10, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:48:26', '2025-05-01 17:48:26'),
(11, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:53:55', '2025-05-01 17:53:55'),
(12, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-26 08:16:45', '2025-05-26 05:16:45'),
(13, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-26 10:18:21', '2025-05-26 07:18:21'),
(14, 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-26 10:19:18', '2025-05-26 07:19:18'),
(15, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-26 10:19:29', '2025-05-26 07:19:29'),
(16, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-27 09:11:32', '2025-05-27 06:11:32'),
(17, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-05-27 09:53:28', '2025-05-27 06:53:28'),
(18, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-27 11:10:34', '2025-05-27 08:10:34'),
(19, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-28 12:27:15', '2025-05-28 09:27:15'),
(20, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-29 07:46:06', '2025-05-29 04:46:06'),
(21, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-30 10:22:56', '2025-05-30 07:22:56'),
(22, 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-30 11:03:26', '2025-05-30 08:03:26'),
(23, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-30 11:29:05', '2025-05-30 08:29:05'),
(24, 10, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-30 11:30:22', '2025-05-30 08:30:22'),
(25, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-30 11:31:22', '2025-05-30 08:31:22'),
(26, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-31 10:32:14', '2025-05-31 07:32:14'),
(27, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-31 11:47:52', '2025-05-31 08:47:52'),
(28, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-31 14:46:48', '2025-05-31 12:46:48'),
(29, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-31 15:13:03', '2025-05-31 13:13:03'),
(30, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-31 15:15:09', '2025-05-31 13:15:09'),
(31, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-06-01 08:53:53', '2025-06-01 06:53:53'),
(32, 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-06-01 09:33:58', '2025-06-01 07:33:58'),
(33, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-23 08:50:27', '2025-08-23 06:50:27'),
(34, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:08:26', '2025-08-24 05:08:26'),
(35, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:24:57', '2025-08-24 05:24:57'),
(36, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:27:05', '2025-08-24 05:27:05'),
(37, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:29:41', '2025-08-24 05:29:41'),
(38, 11, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:45:41', '2025-08-24 05:45:41'),
(39, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 07:55:55', '2025-08-24 05:55:55'),
(40, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-08-24 09:24:30', '2025-08-24 07:24:30'),
(41, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 09:49:45', '2025-08-24 07:49:45'),
(42, 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 10:38:45', '2025-08-24 08:38:45'),
(43, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 10:39:36', '2025-08-24 08:39:36'),
(44, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'Microsoft Edge (Chromium)', 'Windows 10', 'Desktop', '2025-08-24 11:10:30', '2025-08-24 09:10:30'),
(45, 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-08-25 10:18:33', '2025-08-25 08:18:33');

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` enum('ticket','event','system') DEFAULT 'system',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `type`, `is_read`, `created_at`) VALUES
(4, 5, 'خصم خاص', 'احصل على خصم 20% على جميع الفعاليات باستخدام كود STUDENT20', NULL, '', 0, '2025-05-29 05:05:29'),
(5, 5, 'اختبار النظام', 'تم إصلاح جداول الإشعارات بنجاح', NULL, '', 0, '2025-05-30 08:01:22'),
(6, 11, 'حجز مواصلات', 'لديك تذكرة صالحة للحدث. ستدفع رسوم المواصلات فقط (32 ₪)', 'checkout.php?event_id=27&with_transport=1', '', 1, '2025-05-30 10:25:22'),
(7, 11, 'تم دفع رسوم المواصلات', 'تم دفع رسوم المواصلات بنجاح (32 ₪). سيتم تأكيد الحجز قريباً.', 'my-tickets.php', '', 1, '2025-05-30 10:27:46'),
(8, 11, 'تم دفع رسوم المواصلات', 'تم دفع رسوم المواصلات بنجاح (32 ₪). سيتم تأكيد الحجز قريباً.', 'my-tickets.php', '', 1, '2025-05-30 10:27:58'),
(9, 11, 'تم دفع رسوم المواصلات', 'تم دفع رسوم المواصلات بنجاح (32 ₪). سيتم تأكيد الحجز قريباً.', 'my-tickets.php', '', 1, '2025-05-30 10:35:51'),
(10, 11, 'تم تأكيد حجز التذكرة', 'تم تأكيد حجز تذكرتك لحدث: مهرجان غزة للموسيقى والغناء. المبلغ المدفوع: 30 ₪', 'my-tickets.php', '', 1, '2025-05-30 11:13:47'),
(11, 11, 'اختبار', 'هذا إشعار تجريبي', 'index.php', '', 1, '2025-05-31 08:17:46'),
(12, 11, 'حجز مواصلات', 'لديك تذكرة صالحة للحدث. ستدفع رسوم المواصلات فقط (32 ₪)', 'checkout.php?event_id=27&with_transport=1', '', 1, '2025-05-31 08:27:29'),
(13, 11, 'تم دفع رسوم المواصلات', 'تم دفع رسوم المواصلات بنجاح (32 ₪). سيتم تأكيد الحجز قريباً.', 'my-tickets.php', '', 1, '2025-05-31 08:29:11'),
(14, 11, 'تم دفع رسوم المواصلات', 'تم دفع رسوم المواصلات بنجاح (32 ₪). سيتم تأكيد الحجز قريباً.', 'my-tickets.php', '', 1, '2025-05-31 08:32:57'),
(15, 11, 'تم حجز التذكرة بنجاح', 'تم حجز تذكرة مهرجان غزة للموسيقى والغناء بنجاح. رقم الطلب: 24', 'my-tickets.php', '', 1, '2025-05-31 08:34:26'),
(16, 11, 'تم حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 70 ₪', 'my-tickets.php', '', 1, '2025-05-31 09:43:33'),
(17, 1, 'اختبار الإشعارات', 'هذا إشعار تجريبي للتأكد من عمل النظام', '', '', 0, '2025-05-31 10:31:04'),
(18, 1, 'حجز تذكرة ناجح', 'تم حجز تذكرة مهرجان غزة للموسيقى والغناء بنجاح. رقم الطلب: ORDER_1748687981_1', '', '', 0, '2025-05-31 10:39:41'),
(19, 1, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 70 ₪', '', '', 0, '2025-05-31 11:00:27'),
(20, 1, 'خطأ في حجز المواصلات', 'تم الدفع بنجاح ولكن حدث خطأ في حجز المواصلات: حدث خطأ في النظام: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`tickets_db`.`transport_bookings`, CONSTRAINT `transport_bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `transport_trips` (`id`) ON DELETE CASCADE)', '', '', 0, '2025-05-31 11:00:27'),
(21, 1, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 70 ₪', '', '', 0, '2025-05-31 11:01:45'),
(22, 1, 'خطأ في حجز المواصلات', 'تم الدفع بنجاح ولكن حدث خطأ في حجز المواصلات: حدث خطأ في النظام: SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails (`tickets_db`.`transport_bookings`, CONSTRAINT `transport_bookings_ibfk_2` FOREIGN KEY (`trip_id`) REFERENCES `transport_trips` (`id`) ON DELETE CASCADE)', '', '', 0, '2025-05-31 11:01:45'),
(23, 1, 'حجز تذكرة ناجح', 'تم حجز تذكرة مؤتمر إعادة إعمار غزة بنجاح. رقم الطلب: ORDER_1748695378_1', '', '', 0, '2025-05-31 12:42:58'),
(24, 11, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 150 ₪', '', '', 1, '2025-05-31 13:28:22'),
(25, 11, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 150 ₪. رقم حجز المواصلات: TRAD18B7EC', '', '', 1, '2025-05-31 13:28:22'),
(26, 11, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 78 ₪', '', '', 1, '2025-06-01 07:09:17'),
(27, 11, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 78 ₪. رقم حجز المواصلات: TR632398D8', '', '', 1, '2025-06-01 07:09:17'),
(28, 9, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 120 ₪', '', '', 1, '2025-08-23 08:26:22'),
(29, 9, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 120 ₪. رقم حجز المواصلات: TR41F92950', '', '', 1, '2025-08-23 08:26:22'),
(30, 9, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 65 ₪', '', '', 1, '2025-08-23 08:29:29'),
(31, 9, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 65 ₪. رقم حجز المواصلات: TR7C356B2D', '', '', 1, '2025-08-23 08:29:29'),
(32, 9, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 195 ₪', '', '', 1, '2025-08-23 09:14:19'),
(33, 9, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 195 ₪. رقم حجز المواصلات: TR8281E54D', '', '', 1, '2025-08-23 09:14:19'),
(34, 11, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 130 ₪', '', '', 1, '2025-08-24 05:22:38'),
(35, 11, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 130 ₪. رقم حجز المواصلات: TR0BD4C168', '', '', 1, '2025-08-24 05:22:38'),
(36, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:44:51'),
(37, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:44:51'),
(38, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:44:51'),
(39, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:44:51'),
(40, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:05'),
(41, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:05'),
(42, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:05'),
(43, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:05'),
(44, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:11'),
(45, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:11'),
(46, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:11'),
(47, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:11'),
(48, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:14'),
(49, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:14'),
(50, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:14'),
(51, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:14'),
(52, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:20'),
(53, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:20'),
(54, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:48:20'),
(55, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:48:20'),
(56, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:49:14'),
(57, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:49:14'),
(58, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:49:14'),
(59, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:49:14'),
(60, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:49:26'),
(61, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:49:26'),
(62, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:49:26'),
(63, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:49:26'),
(64, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:51:54'),
(65, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:51:54'),
(66, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 07:51:54'),
(67, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 07:51:54'),
(68, 9, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 150 ₪', '', '', 1, '2025-08-24 07:53:06'),
(69, 9, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 150 ₪. رقم حجز المواصلات: TR568FFF0E', '', '', 1, '2025-08-24 07:53:06'),
(70, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:07:46'),
(71, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:07:46'),
(72, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:07:46'),
(73, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:07:46'),
(74, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:08:19'),
(75, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:08:19'),
(76, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:08:19'),
(77, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:08:19'),
(78, 9, 'حجز تذكرة ومواصلات ناجح', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 105 ₪', '', '', 1, '2025-08-24 08:10:45'),
(79, 9, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 105 ₪. رقم حجز المواصلات: TRF4CC53E6', '', '', 1, '2025-08-24 08:10:45'),
(80, 9, 'تم قبول حجز المواصلات', 'مبروك! تم قبول حجزك رقم TRF4CC53E6. عدد المقاعد: 3، المبلغ: 105.00 ₪', NULL, '', 1, '2025-08-24 08:11:27'),
(81, 9, 'حجز تذكرة ناجح', 'تم حجز تذكرة مؤتمر غزة للزراعة المستدامة بنجاح. رقم الطلب: ORDER_1756023263_9', '', '', 1, '2025-08-24 08:14:23'),
(82, 9, 'حجز مواصلات ناجح', 'تم دفع رسوم المواصلات بنجاح (102 ₪). سيتم تأكيد الحجز قريباً.', '', '', 1, '2025-08-24 08:16:11'),
(83, 9, 'تأكيد حجز المواصلات', 'تم دفع رسوم المواصلات بنجاح (102 ₪). رقم حجز المواصلات: TR673578D7', '', '', 1, '2025-08-24 08:16:11'),
(84, 9, 'تم رفض حجز المواصلات', 'نعتذر، تم رفض حجزك رقم TR673578D7. سبب الرفض: عدم توفر وسيلة النقل المطلوبة. يمكنك محاولة حجز رحلة أخرى.', NULL, '', 1, '2025-08-24 08:16:56'),
(85, 6, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:30:58'),
(86, 8, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:30:58'),
(87, 9, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 1, '2025-08-24 08:30:58'),
(88, 10, 'تقرير تنظيف العناصر المنتهية الصلاحية', 'تم تنظيف العناصر التالية:\n- 1 فعالية منتهية\n- 2 رحلة منتهية\n- 1 تذكرة منتهية\n- 2 حجز مواصلات منتهي', NULL, 'system', 0, '2025-08-24 08:30:58'),
(89, 9, 'حجز تذكرة ناجح', 'تم حجز تذكرة مؤتمر غزة للتكنولوجيا والابتكار بنجاح. رقم الطلب: ORDER_1756027919_9', '', '', 1, '2025-08-24 09:31:59'),
(90, 9, 'حجز مواصلات ناجح', 'تم دفع رسوم المواصلات بنجاح (45 ₪). سيتم تأكيد الحجز قريباً.', '', '', 1, '2025-08-24 09:33:20'),
(91, 9, 'تأكيد حجز المواصلات', 'تم دفع رسوم المواصلات بنجاح (45 ₪). رقم حجز المواصلات: TRF705EB47', '', '', 1, '2025-08-24 09:33:20'),
(92, 9, 'تم قبول حجز المواصلات', 'مبروك! تم قبول حجزك رقم TRF705EB47. عدد المقاعد: 3، المبلغ: 45.00 ₪', NULL, '', 1, '2025-08-24 09:33:40');

-- --------------------------------------------------------

--
-- بنية الجدول `notification_settings`
--

CREATE TABLE `notification_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) DEFAULT 1,
  `mobile_enabled` tinyint(1) DEFAULT 0,
  `upcoming_tickets` tinyint(1) DEFAULT 1,
  `event_changes` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `transport_updates` tinyint(1) DEFAULT 1,
  `payment_notifications` tinyint(1) DEFAULT 1,
  `admin_announcements` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notification_settings`
--

INSERT INTO `notification_settings` (`id`, `user_id`, `email_enabled`, `mobile_enabled`, `upcoming_tickets`, `event_changes`, `created_at`, `updated_at`, `transport_updates`, `payment_notifications`, `admin_announcements`) VALUES
(1, 6, 1, 1, 1, 1, '2025-05-01 17:52:12', '2025-05-01 17:52:12', 1, 1, 1),
(2, 5, 1, 0, 1, 1, '2025-05-30 08:01:22', '2025-05-30 08:01:22', 1, 1, 1),
(3, 8, 1, 0, 1, 1, '2025-05-30 08:01:26', '2025-05-30 08:01:26', 1, 1, 1),
(4, 8, 1, 0, 1, 1, '2025-05-30 08:02:41', '2025-05-30 08:02:41', 1, 1, 1),
(5, 1, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1),
(6, 2, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1),
(7, 10, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1),
(8, 12, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1),
(9, 9, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1),
(10, 11, 1, 0, 1, 1, '2025-06-01 07:14:59', '2025-06-01 07:14:59', 1, 1, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `event_id`, `quantity`, `total_amount`, `coupon_id`, `discount_amount`, `payment_status`, `payment_method`, `transaction_id`, `payment_details`, `created_at`, `updated_at`) VALUES
(19, 6, 14, 1, 70.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-29 05:15:57', '2025-05-29 05:15:57'),
(20, 6, 14, 1, 70.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-29 05:16:00', '2025-05-29 05:16:00'),
(21, 6, 14, 1, 70.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-29 05:18:15', '2025-05-29 05:18:15'),
(22, 11, 27, 1, 22.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-30 10:14:57', '2025-05-30 10:14:57'),
(23, 11, 1, 1, 30.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-30 11:13:47', '2025-05-30 11:13:47'),
(24, 11, 1, 1, 30.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-31 08:34:26', '2025-05-31 08:34:26'),
(25, 11, 1, 1, 30.00, NULL, 0.00, 'completed', NULL, NULL, NULL, '2025-05-31 09:43:33', '2025-05-31 09:43:33'),
(26, 1, 1, 2, 50.00, NULL, 0.00, 'completed', 'credit_card', 'TEST_ORDER_1748686830', NULL, '2025-05-31 10:20:30', '2025-05-31 10:20:30'),
(27, 1, 1, 2, 50.00, NULL, 0.00, 'completed', 'credit_card', 'FINAL_TEST_1748687117', NULL, '2025-05-31 10:25:17', '2025-05-31 10:25:17'),
(28, 1, 1, 2, 50.00, NULL, 0.00, 'completed', 'credit_card', 'TEST_ORDER_1748687464', NULL, '2025-05-31 10:31:04', '2025-05-31 10:31:04'),
(29, 1, 1, 1, 30.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748687981_1', NULL, '2025-05-31 10:39:41', '2025-05-31 10:39:41'),
(30, 1, 1, 1, 70.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748689227_1', NULL, '2025-05-31 11:00:27', '2025-05-31 11:00:27'),
(31, 1, 1, 1, 70.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748689305_1', NULL, '2025-05-31 11:01:45', '2025-05-31 11:01:45'),
(32, 1, 14, 1, 70.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748695378_1', NULL, '2025-05-31 12:42:58', '2025-05-31 12:42:58'),
(33, 11, 1, 2, 150.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748698102_11', NULL, '2025-05-31 13:28:22', '2025-05-31 13:28:22'),
(34, 11, 26, 1, 78.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748761757_11', NULL, '2025-06-01 07:09:17', '2025-06-01 07:09:17'),
(35, 9, 22, 2, 120.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1755937582_9', NULL, '2025-08-23 08:26:22', '2025-08-23 08:26:22'),
(36, 9, 22, 1, 65.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1755937769_9', NULL, '2025-08-23 08:29:29', '2025-08-23 08:29:29'),
(37, 9, 22, 3, 195.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1755940459_9', NULL, '2025-08-23 09:14:19', '2025-08-23 09:14:19'),
(38, 11, 22, 2, 130.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1756012958_11', NULL, '2025-08-24 05:22:38', '2025-08-24 05:22:38'),
(39, 9, 19, 2, 150.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1756021986_9', NULL, '2025-08-24 07:53:06', '2025-08-24 07:53:06'),
(40, 9, 24, 3, 105.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1756023045_9', NULL, '2025-08-24 08:10:45', '2025-08-24 08:10:45'),
(41, 9, 25, 1, 25.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1756023263_9', NULL, '2025-08-24 08:14:23', '2025-08-24 08:14:23'),
(42, 9, 17, 1, 30.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1756027919_9', NULL, '2025-08-24 09:31:59', '2025-08-24 09:31:59');

-- --------------------------------------------------------

--
-- بنية الجدول `payment_cards`
--

CREATE TABLE `payment_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `card_number` varchar(255) NOT NULL,
  `card_holder` varchar(100) NOT NULL,
  `expiry_date` varchar(10) NOT NULL,
  `cvv` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('credit_card','paypal') NOT NULL,
  `card_number` varchar(255) DEFAULT NULL,
  `card_brand` varchar(50) DEFAULT NULL,
  `card_holder` varchar(255) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paypal_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `user_id`, `type`, `card_number`, `card_brand`, `card_holder`, `expiry_date`, `cvv`, `is_default`, `created_at`, `updated_at`, `paypal_email`) VALUES
(2, 1, 'credit_card', '4111111111111111', 'visa', 'Test User', '12/25', '123', 0, '2025-04-24 08:54:16', '2025-04-27 14:14:19', NULL),
(3, 2, 'credit_card', '4154 6444 0089 5349', 'visa', 'erwr', '12/27', '758', 0, '2025-04-24 08:58:36', '2025-04-24 08:58:36', NULL),
(4, 1, 'paypal', NULL, NULL, NULL, NULL, NULL, 1, '2025-04-27 14:14:19', '2025-04-27 14:14:19', 'mrlovalovs@gmail.com'),
(5, 6, 'credit_card', '2221966017512386', 'mastercard', 'Eldon Heidenreich', '11/25', '595', 0, '2025-05-01 17:47:55', '2025-05-01 17:47:55', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `payment_technical_info`
--

CREATE TABLE `payment_technical_info` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `os` varchar(100) NOT NULL,
  `device` varchar(100) NOT NULL,
  `user_agent` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `paypal_technical_info`
--

CREATE TABLE `paypal_technical_info` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `os` varchar(100) NOT NULL,
  `device` varchar(100) NOT NULL,
  `user_agent` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `paypal_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `paypal_technical_info`
--

INSERT INTO `paypal_technical_info` (`id`, `payment_id`, `user_id`, `ip_address`, `browser`, `os`, `device`, `user_agent`, `email`, `paypal_email`, `created_at`) VALUES
(1, 4, 1, '::1', 'Chrome', 'Windows', 'كمبيوتر', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'admin@example.com', 'mrlovalovs@gmail.com', '2025-04-27 14:14:40');

-- --------------------------------------------------------

--
-- بنية الجدول `registration_logs`
--

CREATE TABLE `registration_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `user_agent` text NOT NULL,
  `browser` varchar(100) NOT NULL,
  `os` varchar(100) NOT NULL,
  `device` varchar(100) NOT NULL,
  `registration_time` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `registration_logs`
--

INSERT INTO `registration_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `browser`, `os`, `device`, `registration_time`, `created_at`) VALUES
(1, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:19:27', '2025-05-01 17:19:27');

-- --------------------------------------------------------

--
-- بنية الجدول `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_code` varchar(20) NOT NULL,
  `status` enum('active','used','cancelled','confirmed') DEFAULT 'active',
  `used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `tickets`
--

INSERT INTO `tickets` (`id`, `order_id`, `event_id`, `user_id`, `ticket_code`, `status`, `used`, `used_at`, `created_at`, `updated_at`) VALUES
(17, 19, 14, 6, 'L70DP5QES2', '', 0, NULL, '2025-05-29 05:15:57', '2025-08-24 07:44:51'),
(18, 20, 14, 6, 'LYAHBHWBM5', '', 0, NULL, '2025-05-29 05:16:00', '2025-08-24 07:44:51'),
(19, 21, 14, 6, 'VRZIRGQKFM', '', 0, NULL, '2025-05-29 05:18:15', '2025-08-24 07:44:51'),
(20, 22, 27, 11, 'Y1VXY3KSH6', 'active', 0, NULL, '2025-05-30 10:14:57', '2025-05-30 10:14:57'),
(21, 23, 1, 11, '4DJEW7BJIN', '', 0, NULL, '2025-05-30 11:13:47', '2025-08-24 07:44:51'),
(22, 24, 1, 11, 'RWPB5C0NBY', '', 0, NULL, '2025-05-31 08:34:26', '2025-08-24 07:44:51'),
(23, 25, 1, 11, '41QSVN99QB', '', 0, NULL, '2025-05-31 09:43:33', '2025-08-24 07:44:51'),
(31, 30, 1, 1, 'TICKET_5304D13F26', '', 0, NULL, '2025-05-31 11:00:27', '2025-08-24 07:44:51'),
(32, 31, 1, 1, 'TICKET_589045531B', '', 0, NULL, '2025-05-31 11:01:45', '2025-08-24 07:44:51'),
(33, 32, 14, 1, 'TICKET_679DD5255B', '', 0, NULL, '2025-05-31 12:42:58', '2025-08-24 07:44:51'),
(34, 33, 1, 11, 'TICKET_1C8A413C51', '', 0, NULL, '2025-05-31 13:28:22', '2025-08-24 07:44:51'),
(35, 33, 1, 11, 'TICKET_9A39897EF8', '', 0, NULL, '2025-05-31 13:28:22', '2025-08-24 07:44:51'),
(36, 34, 26, 11, 'TICKET_960390169F', 'active', 0, NULL, '2025-06-01 07:09:17', '2025-06-01 07:09:17'),
(37, 35, 22, 9, 'TICKET_7DB144BDAF', '', 0, NULL, '2025-08-23 08:26:22', '2025-08-24 07:44:51'),
(38, 35, 22, 9, 'TICKET_0092D2A44F', '', 0, NULL, '2025-08-23 08:26:22', '2025-08-24 07:44:51'),
(39, 36, 22, 9, 'TICKET_CE448E3B59', '', 0, NULL, '2025-08-23 08:29:29', '2025-08-24 07:44:51'),
(40, 37, 22, 9, 'TICKET_DA0253D4F3', '', 0, NULL, '2025-08-23 09:14:19', '2025-08-24 07:44:51'),
(41, 37, 22, 9, 'TICKET_5BF110937A', '', 0, NULL, '2025-08-23 09:14:19', '2025-08-24 07:44:51'),
(42, 37, 22, 9, 'TICKET_70E5CB2842', '', 0, NULL, '2025-08-23 09:14:19', '2025-08-24 07:44:51'),
(43, 38, 22, 11, 'TICKET_AC4EFE23D3', '', 0, NULL, '2025-08-24 05:22:38', '2025-08-24 07:44:51'),
(44, 38, 22, 11, 'TICKET_0F7AB0DF04', '', 0, NULL, '2025-08-24 05:22:38', '2025-08-24 07:44:51'),
(45, 39, 19, 9, 'TICKET_84928DA406', 'active', 0, NULL, '2025-08-24 07:53:06', '2025-08-24 07:53:06'),
(46, 39, 19, 9, 'TICKET_5609A9F9F6', 'active', 0, NULL, '2025-08-24 07:53:06', '2025-08-24 07:53:06'),
(47, 40, 24, 9, 'TICKET_91968DF11A', 'active', 0, NULL, '2025-08-24 08:10:45', '2025-08-24 08:10:45'),
(48, 40, 24, 9, 'TICKET_B3BCD065D6', 'active', 0, NULL, '2025-08-24 08:10:45', '2025-08-24 08:10:45'),
(49, 40, 24, 9, 'TICKET_B01658761A', 'active', 0, NULL, '2025-08-24 08:10:45', '2025-08-24 08:10:45'),
(50, 41, 25, 9, 'TICKET_062AEF5156', 'active', 0, NULL, '2025-08-24 08:14:23', '2025-08-24 08:14:23'),
(51, 42, 17, 9, 'TICKET_ECF3D3112A', 'active', 0, NULL, '2025-08-24 09:31:59', '2025-08-24 09:31:59');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_bookings`
--

CREATE TABLE `transport_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `trip_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `seats_count` int(11) DEFAULT 1,
  `passenger_name` varchar(255) NOT NULL,
  `passenger_phone` varchar(20) NOT NULL,
  `passenger_email` varchar(255) DEFAULT NULL,
  `passengers_count` int(11) NOT NULL DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `payment_method` enum('bank_transfer','cash_on_delivery','mobile_pay','credit_card') NOT NULL,
  `payment_status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `cancellation_reason` text DEFAULT NULL,
  `cancellation_date` datetime DEFAULT NULL,
  `notification_sent` tinyint(1) NOT NULL DEFAULT 0,
  `payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_details`)),
  `booking_code` varchar(20) NOT NULL,
  `status` enum('pending','confirmed','cancelled','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `response_date` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_bookings`
--

INSERT INTO `transport_bookings` (`id`, `user_id`, `trip_id`, `event_id`, `ticket_id`, `customer_name`, `customer_phone`, `customer_email`, `seats_count`, `passenger_name`, `passenger_phone`, `passenger_email`, `passengers_count`, `total_amount`, `special_notes`, `payment_method`, `payment_status`, `cancellation_reason`, `cancellation_date`, `notification_sent`, `payment_details`, `booking_code`, `status`, `rejection_reason`, `response_date`, `admin_id`, `created_at`, `updated_at`) VALUES
(13, 9, 143, 22, NULL, 'محمد عبد', '0598844538', NULL, 2, '', '', NULL, 1, 120.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR41F92950', '', NULL, NULL, NULL, '2025-08-23 08:26:22', '2025-08-24 07:44:51'),
(14, 9, 142, 22, NULL, 'محمد محمود الخال', '0548899651', NULL, 1, '', '', NULL, 1, 65.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR7C356B2D', '', NULL, NULL, NULL, '2025-08-23 08:29:29', '2025-08-24 07:44:51'),
(15, 9, 142, 22, NULL, 'يوسف عبدالله', '0596543210', NULL, 3, '', '', NULL, 1, 195.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR8281E54D', '', NULL, NULL, NULL, '2025-08-23 09:14:19', '2025-08-24 07:44:51'),
(16, 11, 142, 22, NULL, 'محمد عبد', '0596621455', NULL, 1, '', '', NULL, 2, 130.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR0BD4C168', '', NULL, '2025-08-24 08:26:21', 1, '2025-08-24 05:22:38', '2025-08-24 07:44:51'),
(17, 9, 46, 19, NULL, 'عبد الكريم', '0596621455', NULL, 1, '', '', NULL, 2, 150.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR568FFF0E', 'rejected', 'تم إلغاء الرحلة لظروف طارئة', '2025-08-24 10:54:00', 1, '2025-08-24 07:53:06', '2025-08-24 07:54:00'),
(18, 9, 45, 24, NULL, 'عبد الكريم', '0596621455', NULL, 1, '', '', NULL, 3, 105.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TRF4CC53E6', 'confirmed', NULL, '2025-08-24 11:11:27', 1, '2025-08-24 08:10:45', '2025-08-24 08:11:27'),
(19, 9, 21, 25, NULL, 'محمد عبد', '0596621455', NULL, 1, '', '', NULL, 3, 102.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR673578D7', 'rejected', 'عدم توفر وسيلة النقل المطلوبة', '2025-08-24 11:16:56', 1, '2025-08-24 08:16:11', '2025-08-24 08:16:56'),
(20, 9, 145, 17, NULL, 'محمد عبد', '0596621455', NULL, 1, '', '', NULL, 3, 45.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TRF705EB47', 'confirmed', NULL, '2025-08-24 12:33:40', 1, '2025-08-24 09:33:20', '2025-08-24 09:33:40');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_drivers`
--

CREATE TABLE `transport_drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `license_type` enum('private','public','commercial','heavy') DEFAULT 'private',
  `license_expiry` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `governorate` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` enum('north','center','south') DEFAULT NULL,
  `status` enum('available','busy','offline') DEFAULT 'available',
  `experience_years` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 5.00,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_drivers`
--

INSERT INTO `transport_drivers` (`id`, `name`, `phone`, `license_number`, `license_type`, `license_expiry`, `address`, `governorate`, `city`, `region`, `status`, `experience_years`, `rating`, `photo`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'محمود علي', '0598765432', 'DL005678', 'private', NULL, NULL, 'غزة', 'غزة', 'center', 'available', 12, 4.90, 'uploads/drivers/driver1.jpg', 1, '2025-05-26 06:03:12', '2025-08-24 09:19:48'),
(3, 'خالد حسن', '0597654321', 'DL009876', 'private', NULL, NULL, 'غزة', 'غزة', 'center', 'available', 6, 4.70, 'uploads/drivers/driver2.jpg', 1, '2025-05-26 06:03:12', '2025-08-24 09:05:20'),
(4, 'يوسف عبدالله', '0596543210', 'DL004321', 'private', NULL, '', 'غزة', 'غزة', 'center', 'available', 10, 4.90, 'uploads/drivers/driver3.jpg', 1, '2025-05-26 06:03:12', '2025-08-24 09:05:20'),
(5, 'عمر سالم', '0595432109', 'DL008765', 'private', NULL, NULL, 'غزة', 'غزة', 'center', 'available', 5, 4.60, 'uploads/drivers/driver4.jpg', 1, '2025-05-26 06:03:12', '2025-08-24 09:05:20'),
(16, 'أحمد محمد', '0599123456', 'DL001234', 'private', NULL, NULL, 'غزة', 'غزة', 'center', 'available', 8, 4.80, 'uploads/drivers/driver5.jpg', 1, '2025-05-26 06:08:17', '2025-08-24 09:05:20'),
(18, 'محمد محمود الخال', '4929977584', '111111111', 'private', NULL, 'رفح', 'غزة', 'غزة', 'center', 'available', 3, 5.00, 'uploads/drivers/driver6.jpg', 1, '2025-08-23 07:09:45', '2025-08-24 09:05:20');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_prices`
--

CREATE TABLE `transport_prices` (
  `id` int(11) NOT NULL,
  `transport_type_id` int(11) NOT NULL,
  `starting_point_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `transport_starting_points`
--

CREATE TABLE `transport_starting_points` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `region` enum('north','center','south') NOT NULL,
  `icon` varchar(50) DEFAULT 'city',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_starting_points`
--

INSERT INTO `transport_starting_points` (`id`, `name`, `region`, `icon`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'غزة', 'north', 'city', 'العاصمة ومركز المحافظة', 1, '2025-05-26 06:02:07', '2025-08-23 07:48:55'),
(2, 'رفح', 'south', 'border-crossing', 'أقصى جنوب قطاع غزة', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:21'),
(3, 'النصيرات', 'center', 'camp', 'من أكبر مخيمات اللاجئين', 1, '2025-05-26 06:02:07', '2025-08-24 07:29:33'),
(4, 'جباليا', 'north', 'city', 'شمال مدينة غزة', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:41'),
(5, 'خانيونس', 'south', 'city', 'ثاني أكبر مدينة في الجنوب', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:32'),
(7, 'بيت لاهيا', 'north', 'farm', 'منطقة زراعية شمالية', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:46'),
(8, 'بيت حانون', 'north', 'border-crossing', 'أقصى شمال القطاع', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:49'),
(9, 'المغازي', 'center', 'camp', 'من مخيمات وسط القطاع', 1, '2025-05-26 06:02:07', '2025-08-24 07:27:30'),
(11, 'خزاعة', 'south', 'village', 'قرية جنوب شرق خانيونس', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:29'),
(12, 'عبسان', 'south', 'village', 'قريتان شرق خانيونس', 1, '2025-05-26 06:02:07', '2025-08-23 07:50:17'),
(18, 'دير البلح', 'center', 'palm-tree', 'مدينة دير البلح - وسط القطاع', 1, '2025-05-31 11:33:03', '2025-08-23 07:50:23'),
(19, 'رفح-تل السلطان', 'center', 'map-marker-alt', 'مفترق غريز', 1, '2025-08-25 09:56:25', '2025-08-25 09:56:25');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_trips`
--

CREATE TABLE `transport_trips` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `starting_point_id` int(11) NOT NULL,
  `transport_type_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `transport_price_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` time NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_seats` int(11) NOT NULL,
  `available_seats` int(11) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_trips`
--

INSERT INTO `transport_trips` (`id`, `event_id`, `starting_point_id`, `transport_type_id`, `driver_id`, `transport_price_id`, `vehicle_id`, `departure_time`, `arrival_time`, `price`, `total_seats`, `available_seats`, `features`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(21, 25, 9, 3, 3, NULL, 3, '2025-10-10 08:00:00', '09:56:00', 34.00, 4, 4, NULL, '', 1, '2025-05-30 09:01:44', '2025-08-25 10:05:20'),
(45, 24, 5, 2, 2, NULL, 2, '2025-09-15 12:12:00', '14:14:00', 17.00, 12, 9, NULL, '', 1, '2025-05-30 09:01:44', '2025-08-25 07:12:16'),
(46, 19, 5, 4, 16, NULL, 7, '2025-11-01 12:13:00', '13:30:00', 25.00, 50, 50, NULL, '', 1, '2025-05-30 09:01:44', '2025-08-25 10:05:20'),
(66, 24, 12, 4, 5, NULL, 5, '2025-09-15 12:46:00', '14:01:00', 27.00, 15, 15, NULL, '', 1, '2025-05-30 09:01:44', '2025-08-25 07:12:07'),
(145, 17, 2, 21, 18, NULL, 8, '2025-09-05 12:55:00', '13:15:00', 15.00, 12, 9, NULL, '', 1, '2025-08-24 09:31:15', '2025-08-25 10:05:20');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_types`
--

CREATE TABLE `transport_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bus',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_types`
--

INSERT INTO `transport_types` (`id`, `name`, `description`, `icon`, `is_active`, `created_at`) VALUES
(1, 'باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 1, '2025-05-26 06:02:07'),
(2, 'فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 1, '2025-05-26 06:02:07'),
(3, 'سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, '2025-05-26 06:02:07'),
(4, 'باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 1, '2025-05-26 06:02:07'),
(21, 'حافلة سياحية', 'حافلة مكيفة مريحة', 'fa-bus', 1, '2025-05-31 11:33:03'),
(31, 'باص عادي', 'حافلة كبيرة لنقل الركاب', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(32, 'باص فاخر', 'حافلة مكيفة ومريحة', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(33, 'حافلة سياحية', 'حافلة مخصصة للرحلات السياحية', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(34, 'سيارة خاصة', 'سيارة خاصة صغيرة', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(35, 'فان', 'مركبة متوسطة الحجم', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(36, 'ميكروباص', 'حافلة صغيرة', 'fas fa-bus', 1, '2025-08-25 08:58:03'),
(37, 'شاحنة صغيرة', 'مركبة لنقل البضائع', 'fas fa-bus', 1, '2025-08-25 08:58:03');

-- --------------------------------------------------------

--
-- بنية الجدول `transport_vehicles`
--

CREATE TABLE `transport_vehicles` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `transport_type_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `transport_vehicles`
--

INSERT INTO `transport_vehicles` (`id`, `driver_id`, `transport_type_id`, `plate_number`, `model`, `year`, `color`, `capacity`, `photo`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 'غ ن 5678', 'هيونداي H1', 2019, 'فضي', 12, 'vehicles/van1.jpg', '[\"ac\", \"luggage_space\", \"comfortable_seats\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(3, 3, 3, 'غ ن 9876', 'تويوتا كامري', 2021, 'أسود', 4, 'vehicles/car1.jpg', '[\"ac\", \"leather_seats\", \"gps\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(4, 4, 1, 'غ ن 4321', 'إيفيكو ديلي', 2018, 'أزرق', 30, 'vehicles/bus2.jpg', '[\"ac\", \"comfortable_seats\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(5, 5, 4, 'غ ن 8765', 'كيا بونجو', 2017, 'أحمر', 15, 'vehicles/minibus1.jpg', '[\"ac\", \"basic_comfort\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(7, 16, 4, '225985356', 'ي', 2012, 'احمر', 50, NULL, NULL, 1, '2025-08-24 09:11:51', '2025-08-24 09:11:51'),
(8, 18, 21, '325889511', 'k', 2016, 'اسود', 45, NULL, NULL, 1, '2025-08-24 09:13:58', '2025-08-24 09:13:58');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hashed` varchar(255) NOT NULL,
  `role` enum('user','transport_admin','notifications_admin','site_admin','super_admin') DEFAULT 'user',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `timezone` varchar(100) DEFAULT 'Asia/Jerusalem',
  `preferred_language` varchar(10) DEFAULT 'ar',
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `password_hashed`, `role`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `profile_image`, `last_login`, `last_login_ip`, `timezone`, `preferred_language`, `two_factor_enabled`, `status`, `email_verified`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$fMgeeCfcZ7nhHWFWKNbCK.SVauLNd3DA50oopV/YYAr6MRh4INu0.', '0501234567', '$2y$10$fMgeeCfcZ7nhHWFWKNbCK.SVauLNd3DA50oopV/YYAr6MRh4INu0.', '', NULL, NULL, '2025-04-21 09:41:54', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(2, 'ahamd', 'asdwqd@sdfd.fs', '$2y$10$i0CEnCjXipNZLxwfg14il.eF93nxFb6W8RnPIKo0/7.63A9uITiIq', '+64033550355', '$2y$10$i0CEnCjXipNZLxwfg14il.eF93nxFb6W8RnPIKo0/7.63A9uITiIq', 'user', NULL, NULL, '2025-04-21 12:35:58', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(5, 'Admin User', 'admin@admin.com', '$2y$10$dpMa8.2MV.j6nP4jujkfreYkwMtsrD1mo5MMC3ab8MQHIxGNzqZdK', '0501234567', '$2y$10$dpMa8.2MV.j6nP4jujkfreYkwMtsrD1mo5MMC3ab8MQHIxGNzqZdK', '', NULL, NULL, '2025-04-28 07:44:13', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(6, 'tran', 'tran@gmail.com', 'tran123', '25412114521', 'tran123', 'transport_admin', NULL, NULL, '2025-05-01 17:19:27', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(8, 'المدير العام', 'superadmin@palestine-tickets.com', 'SuperAdmin2024!', '0599123456', 'SuperAdmin2024!', 'super_admin', NULL, NULL, '2025-05-30 07:59:41', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(9, 'المدير11', 'admin@transport.com', 'Transport2024!', '0599000000', 'Transport2024!', 'transport_admin', NULL, NULL, '2025-05-30 08:01:26', '2025-08-25 10:10:34', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(10, 'مدير الإشعارات', 'notifications@palestine-tickets.com', 'Notifications2024!', '0599000000', 'Notifications2024!', 'notifications_admin', NULL, NULL, '2025-05-30 08:01:26', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(11, 'مستخدم عادي', 'user@palestine-tickets.com', 'User2024!', '0599000000', 'User2024!', 'user', NULL, NULL, '2025-05-30 08:01:26', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(12, 'مستخدم تجريبي', 'test@example.com', '$2y$10$EVbgwGwaLHGQDLkzRXRwLuzHRT8pGDXrMZtDoCSrNX.fIcEksfPSe', '', '', 'user', NULL, NULL, '2025-05-31 08:13:45', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_activity_admin_id` (`admin_id`),
  ADD KEY `idx_admin_activity_created_at` (`created_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_type`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_admin_permissions_user_id` (`user_id`),
  ADD KEY `idx_admin_permissions_type` (`permission_type`);

--
-- Indexes for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_admin` (`admin_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`),
  ADD KEY `idx_notifications_created_at` (`created_at`),
  ADD KEY `idx_notifications_type` (`type`);

--
-- Indexes for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `payment_cards`
--
ALTER TABLE `payment_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment_technical_info`
--
ALTER TABLE `payment_technical_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `paypal_technical_info`
--
ALTER TABLE `paypal_technical_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registration_logs`
--
ALTER TABLE `registration_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_code` (`ticket_code`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `trip_id` (`trip_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `idx_user_trip` (`user_id`,`trip_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_ticket_id` (`ticket_id`);

--
-- Indexes for table `transport_drivers`
--
ALTER TABLE `transport_drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_number` (`license_number`);

--
-- Indexes for table `transport_prices`
--
ALTER TABLE `transport_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transport_starting_point` (`transport_type_id`,`starting_point_id`),
  ADD KEY `transport_type_id` (`transport_type_id`),
  ADD KEY `starting_point_id` (`starting_point_id`);

--
-- Indexes for table `transport_starting_points`
--
ALTER TABLE `transport_starting_points`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_trips`
--
ALTER TABLE `transport_trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `starting_point_id` (`starting_point_id`),
  ADD KEY `transport_type_id` (`transport_type_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `idx_event_departure` (`event_id`,`departure_time`),
  ADD KEY `idx_transport_price_id` (`transport_price_id`),
  ADD KEY `fk_trip_driver` (`driver_id`);

--
-- Indexes for table `transport_types`
--
ALTER TABLE `transport_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `transport_type_id` (`transport_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `admin_profile`
--
ALTER TABLE `admin_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `payment_cards`
--
ALTER TABLE `payment_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_technical_info`
--
ALTER TABLE `payment_technical_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `paypal_technical_info`
--
ALTER TABLE `paypal_technical_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registration_logs`
--
ALTER TABLE `registration_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `transport_drivers`
--
ALTER TABLE `transport_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `transport_prices`
--
ALTER TABLE `transport_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_starting_points`
--
ALTER TABLE `transport_starting_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `transport_trips`
--
ALTER TABLE `transport_trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `transport_types`
--
ALTER TABLE `transport_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payment_cards`
--
ALTER TABLE `payment_cards`
  ADD CONSTRAINT `payment_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_cards_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payment_technical_info`
--
ALTER TABLE `payment_technical_info`
  ADD CONSTRAINT `payment_technical_info_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment_cards` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `transport_bookings`
--
ALTER TABLE `transport_bookings`
  ADD CONSTRAINT `transport_bookings_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_bookings_ibfk_4` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `transport_prices`
--
ALTER TABLE `transport_prices`
  ADD CONSTRAINT `transport_prices_ibfk_1` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_prices_ibfk_2` FOREIGN KEY (`starting_point_id`) REFERENCES `transport_starting_points` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `transport_trips`
--
ALTER TABLE `transport_trips`
  ADD CONSTRAINT `fk_trip_driver` FOREIGN KEY (`driver_id`) REFERENCES `transport_drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transport_trips_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_trips_ibfk_2` FOREIGN KEY (`starting_point_id`) REFERENCES `transport_starting_points` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_3` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_4` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_5` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_6` FOREIGN KEY (`transport_price_id`) REFERENCES `transport_prices` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD CONSTRAINT `transport_vehicles_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `transport_drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_vehicles_ibfk_2` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

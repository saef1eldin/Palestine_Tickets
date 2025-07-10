-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 03:46 PM
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
-- Database: `tickets_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_log`
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
-- Dumping data for table `admin_activity_log`
--

INSERT INTO `admin_activity_log` (`id`, `admin_id`, `action_type`, `target_type`, `target_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 07:59:41'),
(2, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:01:26'),
(3, 8, 'account_created', 'user', 8, 'إنشاء حساب المدير العام للاختبار', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:02:41'),
(4, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:23:43'),
(5, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:27:24'),
(6, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-05-30 08:27:38'),
(7, 8, 'access_dashboard', 'admin_panel', NULL, 'دخول لوحة تحكم الموقع', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', '2025-06-01 07:34:01');

-- --------------------------------------------------------

--
-- Table structure for table `admin_permissions`
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
-- Dumping data for table `admin_permissions`
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
-- Table structure for table `contact_messages`
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
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_read`) VALUES
(1, 'ةةة', 'admin@example.com', 'ةةة', 'ةة', '2025-04-28 07:51:28', 1);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
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
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `expiry_date`, `usage_limit`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, '2023-12-30', 100, '2025-04-21 09:41:54', '2025-04-23 07:07:54'),
(2, 'SUMMER50', 'fixed', 50.00, '2023-09-30', 50, '2025-04-21 09:41:54', '2025-04-21 09:41:54'),
(3, 'VIP25', 'percentage', 25.00, '2023-12-30', 20, '2025-04-21 09:41:54', '2025-04-23 07:02:00');

-- --------------------------------------------------------

--
-- Table structure for table `events`
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
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `location`, `date_time`, `end_time`, `price`, `capacity`, `original_price`, `category`, `organizer`, `contact_email`, `contact_phone`, `is_featured`, `status`, `is_active`, `image`, `available_tickets`, `featured`, `created_at`, `updated_at`) VALUES
(1, 'مهرجان غزة للموسيقى والغناء', 'مهرجان موسيقي رائع يضم أفضل الفنانين الفلسطينيين في أمسية لا تُنسى مليئة بالموسيقى والغناء التراثي والمعاصر', 'مسرح غزة الثقافي - شارع الوحدة', '2025-06-06 14:13:34', '2025-06-06 17:13:34', 30.00, 100, NULL, 'حفلات موسيقية', 'وزارة الثقافة الفلسطينية', 'info@culture.ps', '08-2345678', 1, 'active', 1, 'https://via.placeholder.com/400x300/4CAF50/white?text=مهرجان+غزة+للموسيقى', 97, 1, '2025-05-30 11:13:34', '2025-05-31 09:43:33'),
(2, 'أمسية شعرية فلسطينية', 'أمسية شعرية تضم نخبة من الشعراء الفلسطينيين المعاصرين', 'رفح - المركز الثقافي', '2024-02-20 18:00:00', '2024-02-20 21:00:00', 15.00, 200, 20.00, 'أدبي', 'اتحاد الكتاب الفلسطينيين', 'writers@palestine.ps', '+970-8-234-5678', 0, 'active', 1, 'poetry_evening.jpg', 180, 0, '2025-05-31 10:02:13', '2025-05-31 10:02:13'),
(3, 'حفل موسيقي تراثي', 'حفل موسيقي يقدم التراث الموسيقي الفلسطيني الأصيل', 'خان يونس - قاعة الشهداء', '2024-02-25 20:00:00', '2024-02-25 23:30:00', 30.00, 300, 40.00, 'موسيقي', 'فرقة الأصالة الفلسطينية', 'music@palestine.ps', '+970-8-345-6789', 1, 'active', 1, 'traditional_music.jpg', 250, 1, '2025-05-31 10:02:13', '2025-05-31 10:02:13'),
(14, 'مؤتمر إعادة إعمار غزة', 'مؤتمر دولي لمناقشة خطط وآليات إعادة إعمار قطاع غزة بعد الحرب. يجمع المؤتمر خبراء في مجالات البناء والتخطيط العمراني والاقتصاد والتنمية المستدامة، بالإضافة إلى ممثلين عن المنظمات الدولية والمؤسسات المانحة. سيتم خلال المؤتمر عرض مشاريع إعادة الإعمار وفرص الاستثمار في قطاع غزة.', 'قاعة المؤتمرات الكبرى - فندق المشتل - غزة', '2025-06-15 09:00:00', '0000-00-00 00:00:00', 70.00, 500, 100.00, 'مؤتمرات', 'وزارة الأشغال العامة والإسكان', 'info@reconstruction-gaza.ps', '+970 8 2823400', 1, 'active', 1, 'uploads/events/1746622139_8c7ee2fc-1bac-470f-894b-32872f692183.png', 997, 0, '2025-05-07 10:08:40', '2025-05-29 05:18:15'),
(15, 'مهرجان فلسطين للتراث والفنون الشعبية', 'مهرجان سنوي يحتفي بالتراث والفنون الشعبية الفلسطينية. يتضمن المهرجان عروضاً للدبكة الشعبية والأغاني التراثية، بالإضافة إلى معارض للأزياء والحرف اليدوية والمأكولات الشعبية. يهدف المهرجان إلى الحفاظ على الهوية الثقافية الفلسطينية ونقلها للأجيال القادمة.', 'ساحة الكتيبة - مدينة غزة', '2025-07-20 17:00:00', '0000-00-00 00:00:00', 20.00, 2000, 22.00, 'مهرجانات', 'وزارة الثقافة الفلسطينية', 'info@palestine-heritage.ps', '+970 8 2829777', 1, 'active', 1, 'uploads/events/1746621032_0fb87bad-636a-4211-a0c5-3d3d5bbb6ebc.png', 58, 0, '2025-05-07 10:08:40', '2025-05-07 12:30:32'),
(16, 'معرض غزة الدولي للكتاب', 'معرض سنوي للكتاب يقام في مدينة غزة، ويضم أجنحة لدور نشر محلية وعربية ودولية. يتضمن المعرض فعاليات ثقافية متنوعة مثل ندوات أدبية وحفلات توقيع كتب وورش عمل للأطفال. يهدف المعرض إلى تشجيع القراءة ونشر الثقافة في المجتمع الفلسطيني.', 'صالة رشاد الشوا الثقافية - غزة', '2025-08-10 10:00:00', '0000-00-00 00:00:00', 7.00, 1500, NULL, 'معارض', 'اتحاد الناشرين الفلسطينيين', 'info@gaza-bookfair.ps', '+970 8 2845566', 1, 'active', 1, 'uploads/events/1746620685_8712179f-b4a5-41bf-a0d9-d051b2782401.png', 300, 0, '2025-05-07 10:08:40', '2025-05-07 12:24:45'),
(17, 'مؤتمر غزة للتكنولوجيا والابتكار', 'مؤتمر تقني يجمع خبراء ومختصين في مجالات التكنولوجيا والبرمجة والذكاء الاصطناعي. يهدف المؤتمر إلى تعزيز ثقافة الابتكار وريادة الأعمال في قطاع غزة، وتوفير منصة للشباب لعرض مشاريعهم التقنية والتواصل مع المستثمرين والشركات العالمية.', 'الجامعة الإسلامية - غزة', '2025-09-05 09:30:00', '0000-00-00 00:00:00', 30.00, 800, 35.00, 'مؤتمرات', 'حاضنة الأعمال والتكنولوجيا - الجامعة الإسلامية', 'info@gazatech.ps', '+970 8 2644400', 0, 'active', 1, 'uploads/events/1746619369_f755bb97-9ad2-483a-a884-7f3f41742a71.png', 75, 0, '2025-05-07 10:08:40', '2025-05-07 12:03:08'),
(18, 'مهرجان غزة السينمائي', 'مهرجان سينمائي يعرض أفلاماً فلسطينية وعربية وعالمية، ويسلط الضوء على قضايا المجتمع الفلسطيني والواقع المعاش في قطاع غزة. يتضمن المهرجان مسابقة للأفلام القصيرة وورش عمل في مجال صناعة السينما وندوات نقدية.', 'مسرح وسينما السعادة - غزة', '2025-10-12 18:00:00', '0000-00-00 00:00:00', 15.00, 400, 30.00, 'مهرجانات', 'مؤسسة السينما الفلسطينية', 'info@gazafilmfest.ps', '+970 8 2861122', 0, 'active', 1, 'uploads/events/1746617175_3b197966-9853-417f-a491-eac76e5de25f.png', 250, 0, '2025-05-07 10:08:40', '2025-05-07 11:26:15'),
(19, 'معرض فلسطين للفنون التشكيلية', 'معرض فني يضم أعمالاً لفنانين تشكيليين من غزة والضفة الغربية والشتات. يعرض المعرض لوحات ومنحوتات وأعمالاً فنية تعبر عن الهوية الفلسطينية والواقع المعاش. يهدف المعرض إلى دعم الفنانين الفلسطينيين وإبراز إبداعاتهم للعالم.', 'متحف فلسطين - غزة', '2025-11-01 16:00:00', '0000-00-00 00:00:00', 21.00, 300, 30.00, 'معارض', 'جمعية الفنانين التشكيليين الفلسطينيين', 'info@palestine-art.ps', '+970 8 2833344', 0, 'active', 1, 'uploads/events/1746616997_0e9a6880-bf14-4d14-af1a-55decb3cb08f.png', 120, 0, '2025-05-07 10:08:40', '2025-05-07 11:23:17'),
(20, 'مؤتمر غزة للتنمية المستدامة', 'مؤتمر علمي يناقش قضايا التنمية المستدامة وتحدياتها في قطاع غزة، مع التركيز على مجالات الطاقة المتجددة والزراعة المستدامة وإدارة الموارد المائية. يجمع المؤتمر باحثين وخبراء محليين ودوليين لتبادل الخبرات وعرض التجارب الناجحة.', 'جامعة الاسلامية- غزة', '2025-12-07 09:00:00', '0000-00-00 00:00:00', 25.00, 600, 30.00, 'مؤتمرات', 'مركز دراسات التنمية - جامعة الأزهر', 'info@sustainable-gaza.ps', '+970 8 2641884', 0, 'active', 1, 'uploads/events/1746615626_42ecd886-b11b-47e3-a2f9-3020df0befca.png', 80, 0, '2025-05-07 10:08:40', '2025-05-07 11:32:27'),
(21, 'مهرجان غزة للأطفال', 'مهرجان سنوي مخصص للأطفال في قطاع غزة، يتضمن فعاليات ترفيهية وتعليمية متنوعة. يشمل المهرجان عروض مسرحية وألعاب تفاعلية وورش رسم وأنشطة رياضية. يهدف المهرجان إلى إدخال البهجة على قلوب الأطفال وتنمية مهاراتهم الإبداعية والاجتماعية في ظل الظروف الصعبة التي يعيشونها.', 'حديقة البلدية المركزية - غزة', '2025-06-01 10:00:00', '0000-00-00 00:00:00', 5.00, 1000, 7.00, 'مهرجانات', 'مؤسسة تامر للتعليم المجتمعي', 'info@children-festival.ps', '+970 8 2822494', 1, 'active', 1, 'uploads/events/1746622368_dda97cd3-37bf-4b85-af09-8511e526388a.png', 0, 0, '2025-05-07 10:21:06', '2025-05-07 12:52:48'),
(22, 'معرض غزة للصناعات اليدوية', 'معرض يجمع الحرفيين والفنانين من مختلف أنحاء قطاع غزة لعرض منتجاتهم اليدوية التقليدية والمعاصرة. يضم المعرض أقساماً متنوعة للتطريز الفلسطيني والخزف والنحت على الخشب والزجاج والصدف وصناعة السجاد والبسط. يهدف المعرض إلى الحفاظ على الحرف التقليدية الفلسطينية ودعم الحرفيين اقتصادياً.', 'قاعة المعارض - جامعة فلسطين - غزة', '2025-07-05 11:00:00', '0000-00-00 00:00:00', 10.00, 500, 13.00, 'معارض', 'اتحاد الحرفيين الفلسطينيين', 'info@gaza-crafts.ps', '+970 8 2865577', 0, 'active', 1, 'uploads/events/1746621493_796806aa-bc85-4c51-a00e-17b46243bbbf.png', 68, 0, '2025-05-07 10:21:06', '2025-05-07 12:38:44'),
(23, 'مؤتمر غزة للطب والصحة', 'مؤتمر علمي يجمع الأطباء والباحثين في المجال الصحي لمناقشة آخر المستجدات والتحديات الصحية في قطاع غزة. يتضمن المؤتمر محاضرات وورش عمل في مختلف التخصصات الطبية، بالإضافة إلى معرض للأجهزة والمستلزمات الطبية. يهدف المؤتمر إلى تبادل الخبرات وتطوير القطاع الصحي في ظل الظروف الصعبة.', 'مستشفى الشفاء - غزة', '2025-08-20 09:00:00', '0000-00-00 00:00:00', 40.00, 400, 70.00, 'مؤتمرات', 'نقابة الأطباء الفلسطينيين', 'info@medical-conference.ps', '+970 8 2866990', 1, 'active', 1, 'uploads/events/1746620534_30960317-0416-49f7-8e1c-d48488b22295.png', 35, 0, '2025-05-07 10:21:06', '2025-05-07 12:22:14'),
(24, 'مهرجان غزة للمأكولات الشعبية', 'مهرجان يحتفي بالمطبخ الفلسطيني التقليدي ويعرض مجموعة متنوعة من الأطباق والحلويات الشعبية. يشارك في المهرجان طهاة محترفون وربات بيوت من مختلف مناطق قطاع غزة، ويتضمن مسابقات طهي وعروض تحضير الأطباق الشهيرة مثل المسخن والمقلوبة والكنافة. يهدف المهرجان إلى الحفاظ على التراث الغذائي الفلسطيني.', 'ساحة السرايا - وسط مدينة غزة', '2025-09-15 16:00:00', '0000-00-00 00:00:00', 18.00, 1500, 20.00, 'مهرجانات', 'جمعية الطهاة الفلسطينيين', 'info@gaza-food.ps', '+970 8 2877788', 1, 'active', 1, 'uploads/events/1746617783_a5de241b-5e8a-4fb8-8be6-f190a9776ccc.png', 68, 0, '2025-05-07 10:21:06', '2025-05-07 11:36:23'),
(25, 'مؤتمر غزة للزراعة المستدامة', 'مؤتمر متخصص في مجال الزراعة المستدامة وتقنيات الزراعة الحديثة المناسبة لظروف قطاع غزة. يناقش المؤتمر قضايا الأمن الغذائي وإدارة الموارد المائية الشحيحة وطرق زيادة الإنتاج الزراعي. يشارك في المؤتمر خبراء محليون ودوليون لتبادل الخبرات والتجارب الناجحة في مجال الزراعة في المناطق ذات الظروف المشابهة.', 'كلية الزراعة - جامعة الاسلامية - غزة', '2025-10-10 09:30:00', '0000-00-00 00:00:00', 25.00, 300, NULL, 'مؤتمرات', 'وزارة الزراعة الفلسطينية', 'info@sustainable-agriculture.ps', '+970 8 2855566', 0, 'active', 1, 'uploads/events/1746617521_17829497-5919-4575-a236-7955951739c8.png', 300, 0, '2025-05-07 10:21:06', '2025-05-07 11:32:01'),
(26, 'معرض غزة للفنون المعاصرة', 'معرض فني يضم أعمالاً لفنانين شباب من قطاع غزة في مجالات الرسم والنحت والتصوير الفوتوغرافي والفن الرقمي. يسلط المعرض الضوء على الإبداعات الفنية المعاصرة التي تعبر عن الواقع والتحديات والطموحات في غزة. يهدف المعرض إلى دعم الفنانين الشباب وتوفير منصة لعرض أعمالهم وتبادل الخبرات.', 'مركز رشاد الشوا الثقافي - غزة', '2025-11-15 17:00:00', '0000-00-00 00:00:00', 15.00, 250, 20.00, 'معارض', 'مجموعة الفنانين الشباب', 'info@gaza-art.ps', '+970 8 2833344', 0, 'active', 1, 'uploads/events/1746616718_6f72ac15-0da1-4305-aaac-c4ce1d63a3b3.png', 75, 0, '2025-05-07 10:21:06', '2025-05-07 11:18:38'),
(27, 'مهرجان غزة للموسيقى والغناء', 'مهرجان موسيقي يجمع فرقاً وفنانين من قطاع غزة والضفة الغربية لتقديم عروض موسيقية وغنائية متنوعة. يشمل المهرجان أمسيات للموسيقى الشرقية والغربية والفلكلور الفلسطيني، بالإضافة إلى ورش موسيقية للأطفال والشباب. يهدف المهرجان إلى إحياء التراث الموسيقي الفلسطيني وتشجيع المواهب الشابة.', 'مسرح وسينما السعادة - غزة', '2025-12-20 18:00:00', '0000-00-00 00:00:00', 22.00, 600, 30.00, 'مهرجانات', 'فرقة العاشقين للفنون الشعبية', 'info@gaza-music.ps', '+970 8 2844455', 1, 'active', 1, 'uploads/events/1746614744_e58e1dee-a17e-4837-bb1b-1a6be3d4bd9b.png', 29, 0, '2025-05-07 10:21:06', '2025-05-30 10:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
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
-- Table structure for table `login_logs`
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
-- Dumping data for table `login_logs`
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
(32, 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-06-01 09:33:58', '2025-06-01 07:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
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
-- Dumping data for table `notifications`
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
(27, 11, 'تأكيد حجز التذكرة والمواصلات', 'تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: 78 ₪. رقم حجز المواصلات: TR632398D8', '', '', 1, '2025-06-01 07:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `notification_settings`
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
-- Dumping data for table `notification_settings`
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
-- Table structure for table `orders`
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
-- Dumping data for table `orders`
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
(34, 11, 26, 1, 78.00, NULL, 0.00, 'completed', 'credit_card', 'ORDER_1748761757_11', NULL, '2025-06-01 07:09:17', '2025-06-01 07:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `payment_cards`
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
-- Table structure for table `payment_methods`
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
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `user_id`, `type`, `card_number`, `card_brand`, `card_holder`, `expiry_date`, `cvv`, `is_default`, `created_at`, `updated_at`, `paypal_email`) VALUES
(2, 1, 'credit_card', '4111111111111111', 'visa', 'Test User', '12/25', '123', 0, '2025-04-24 08:54:16', '2025-04-27 14:14:19', NULL),
(3, 2, 'credit_card', '4154 6444 0089 5349', 'visa', 'erwr', '12/27', '758', 0, '2025-04-24 08:58:36', '2025-04-24 08:58:36', NULL),
(4, 1, 'paypal', NULL, NULL, NULL, NULL, NULL, 1, '2025-04-27 14:14:19', '2025-04-27 14:14:19', 'mrlovalovs@gmail.com'),
(5, 6, 'credit_card', '2221966017512386', 'mastercard', 'Eldon Heidenreich', '11/25', '595', 0, '2025-05-01 17:47:55', '2025-05-01 17:47:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_technical_info`
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
-- Table structure for table `paypal_technical_info`
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
-- Dumping data for table `paypal_technical_info`
--

INSERT INTO `paypal_technical_info` (`id`, `payment_id`, `user_id`, `ip_address`, `browser`, `os`, `device`, `user_agent`, `email`, `paypal_email`, `created_at`) VALUES
(1, 4, 1, '::1', 'Chrome', 'Windows', 'كمبيوتر', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'admin@example.com', 'mrlovalovs@gmail.com', '2025-04-27 14:14:40');

-- --------------------------------------------------------

--
-- Table structure for table `registration_logs`
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
-- Dumping data for table `registration_logs`
--

INSERT INTO `registration_logs` (`id`, `user_id`, `ip_address`, `user_agent`, `browser`, `os`, `device`, `registration_time`, `created_at`) VALUES
(1, 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'Google Chrome', 'Windows 10', 'Desktop', '2025-05-01 20:19:27', '2025-05-01 17:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
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
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `order_id`, `event_id`, `user_id`, `ticket_code`, `status`, `used`, `used_at`, `created_at`, `updated_at`) VALUES
(17, 19, 14, 6, 'L70DP5QES2', 'active', 0, NULL, '2025-05-29 05:15:57', '2025-05-29 05:15:57'),
(18, 20, 14, 6, 'LYAHBHWBM5', 'active', 0, NULL, '2025-05-29 05:16:00', '2025-05-29 05:16:00'),
(19, 21, 14, 6, 'VRZIRGQKFM', 'active', 0, NULL, '2025-05-29 05:18:15', '2025-05-29 05:18:15'),
(20, 22, 27, 11, 'Y1VXY3KSH6', 'active', 0, NULL, '2025-05-30 10:14:57', '2025-05-30 10:14:57'),
(21, 23, 1, 11, '4DJEW7BJIN', 'active', 0, NULL, '2025-05-30 11:13:47', '2025-05-30 11:13:47'),
(22, 24, 1, 11, 'RWPB5C0NBY', 'active', 0, NULL, '2025-05-31 08:34:26', '2025-05-31 08:34:26'),
(23, 25, 1, 11, '41QSVN99QB', 'active', 0, NULL, '2025-05-31 09:43:33', '2025-05-31 09:43:33'),
(31, 30, 1, 1, 'TICKET_5304D13F26', 'active', 0, NULL, '2025-05-31 11:00:27', '2025-05-31 11:00:27'),
(32, 31, 1, 1, 'TICKET_589045531B', 'active', 0, NULL, '2025-05-31 11:01:45', '2025-05-31 11:01:45'),
(33, 32, 14, 1, 'TICKET_679DD5255B', 'active', 0, NULL, '2025-05-31 12:42:58', '2025-05-31 12:42:58'),
(34, 33, 1, 11, 'TICKET_1C8A413C51', 'active', 0, NULL, '2025-05-31 13:28:22', '2025-05-31 13:28:22'),
(35, 33, 1, 11, 'TICKET_9A39897EF8', 'active', 0, NULL, '2025-05-31 13:28:22', '2025-05-31 13:28:22'),
(36, 34, 26, 11, 'TICKET_960390169F', 'active', 0, NULL, '2025-06-01 07:09:17', '2025-06-01 07:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `transport_bookings`
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
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transport_bookings`
--

INSERT INTO `transport_bookings` (`id`, `user_id`, `trip_id`, `event_id`, `ticket_id`, `customer_name`, `customer_phone`, `customer_email`, `seats_count`, `passenger_name`, `passenger_phone`, `passenger_email`, `passengers_count`, `total_amount`, `special_notes`, `payment_method`, `payment_status`, `cancellation_reason`, `cancellation_date`, `notification_sent`, `payment_details`, `booking_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 11, 61, 27, NULL, '', '', NULL, 1, 'Rochelle Clarkson', '0567985004', 'physicist-1@hotmail.co.uk', 1, 32.00, '', 'credit_card', 'pending', NULL, NULL, 0, '{\"card_number\":\"************6750\",\"payment_with_event\":true,\"event_order_id\":\"TRANSPORT_1748600866\"}', 'TR5G2MEUCE', 'pending', '2025-05-30 10:27:46', '2025-05-30 10:27:46'),
(2, 11, 61, 27, NULL, '', '', NULL, 1, 'Rochelle Clarkson', '0567985004', 'physicist-1@hotmail.co.uk', 1, 32.00, '', 'credit_card', 'pending', NULL, NULL, 0, '{\"card_number\":\"************6750\",\"payment_with_event\":true,\"event_order_id\":\"TRANSPORT_1748600878\"}', 'TRNDOK9LXX', 'pending', '2025-05-30 10:27:58', '2025-05-30 10:27:58'),
(3, 11, 61, 27, NULL, '', '', NULL, 1, 'Rochelle Clarkson', '0567985004', 'physicist-1@hotmail.co.uk', 1, 32.00, '', 'credit_card', 'pending', NULL, NULL, 0, '{\"card_number\":\"************6750\",\"payment_with_event\":true,\"event_order_id\":\"TRANSPORT_1748601351\"}', 'TRLWFICG1D', 'pending', '2025-05-30 10:35:51', '2025-05-30 10:35:51'),
(4, 11, 61, 27, NULL, '', '', NULL, 1, 'Rochelle Clarkson', '0567985004', 'physicist-1@hotmail.co.uk', 1, 32.00, '', 'credit_card', 'pending', NULL, NULL, 0, '{\"card_number\":\"************6750\",\"payment_with_event\":true,\"event_order_id\":\"TRANSPORT_1748680151\"}', 'TRZPD81F0R', 'pending', '2025-05-31 08:29:11', '2025-05-31 08:29:11'),
(11, 11, 72, 1, NULL, 'أحمد محمد', '05012345675454', NULL, 2, '', '', NULL, 1, 150.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TRAD18B7EC', 'confirmed', '2025-05-31 13:28:22', '2025-05-31 13:28:22'),
(12, 11, 25, 26, NULL, 'Anonymous Sources', '0567985004', NULL, 1, '', '', NULL, 1, 78.00, NULL, 'credit_card', 'pending', NULL, NULL, 0, NULL, 'TR632398D8', 'confirmed', '2025-06-01 07:09:17', '2025-06-01 07:09:17');

-- --------------------------------------------------------

--
-- Table structure for table `transport_drivers`
--

CREATE TABLE `transport_drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `status` enum('available','busy','offline') DEFAULT 'available',
  `experience_years` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 5.00,
  `photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transport_drivers`
--

INSERT INTO `transport_drivers` (`id`, `name`, `phone`, `license_number`, `address`, `status`, `experience_years`, `rating`, `photo`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'محمود علي', '0598765432', 'DL005678', NULL, 'available', 12, 4.90, 'drivers/mahmoud.jpg', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(3, 'خالد حسن', '0597654321', 'DL009876', NULL, 'available', 6, 4.70, 'drivers/khaled.jpg', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(4, 'يوسف عبدالله', '0596543210', 'DL004321', NULL, 'available', 10, 4.90, 'drivers/youssef.jpg', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(5, 'عمر سالم', '0595432109', 'DL008765', NULL, 'available', 5, 4.60, 'drivers/omar.jpg', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(16, 'أحمد محمد', '0599123456', 'DL001234', NULL, 'available', 8, 4.80, 'drivers/ahmed.jpg', 1, '2025-05-26 06:08:17', '2025-05-26 06:08:17');

-- --------------------------------------------------------

--
-- Table structure for table `transport_prices`
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
-- Table structure for table `transport_starting_points`
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
-- Dumping data for table `transport_starting_points`
--

INSERT INTO `transport_starting_points` (`id`, `name`, `region`, `icon`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'غزة', 'north', 'city', 'العاصمة ومركز المحافظة', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(2, 'رفح', 'south', 'border-crossing', 'أقصى جنوب قطاع غزة', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(3, 'النصيرات', 'center', 'camp', 'من أكبر مخيمات اللاجئين', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(4, 'جباليا', 'north', 'city', 'شمال مدينة غزة', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(5, 'خانيونس', 'south', 'city', 'ثاني أكبر مدينة في الجنوب', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(6, 'دير البلح', 'center', 'palm-tree', 'تشتهر بالنخيل والزراعة', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(7, 'بيت لاهيا', 'north', 'farm', 'منطقة زراعية شمالية', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(8, 'بيت حانون', 'north', 'border-crossing', 'أقصى شمال القطاع', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(9, 'المغازي', 'center', 'camp', 'من مخيمات وسط القطاع', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(10, 'البريج', 'center', 'camp', 'منطقة سكنية في الوسط', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(11, 'خزاعة', 'south', 'village', 'قرية جنوب شرق خانيونس', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(12, 'عبسان', 'south', 'village', 'قريتان شرق خانيونس', 1, '2025-05-26 06:02:07', '2025-05-26 06:02:07'),
(13, 'مدينة غزة', 'center', 'city', 'وسط مدينة غزة - ميدان الجندي المجهول', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(14, 'جباليا', 'north', 'camp', 'مخيم جباليا - المحطة الرئيسية', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(15, 'رفح', 'south', 'border-crossing', 'مدينة رفح - المعبر الحدودي', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(16, 'خان يونس', 'south', 'city', 'مدينة خان يونس - الساحة المركزية', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(17, 'بيت لاهيا', 'north', 'farm', 'بيت لاهيا - المنطقة الزراعية', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(18, 'دير البلح', 'center', 'palm-tree', 'مدينة دير البلح - وسط القطاع', 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `transport_trips`
--

CREATE TABLE `transport_trips` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `starting_point_id` int(11) NOT NULL,
  `transport_type_id` int(11) NOT NULL,
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
-- Dumping data for table `transport_trips`
--

INSERT INTO `transport_trips` (`id`, `event_id`, `starting_point_id`, `transport_type_id`, `transport_price_id`, `vehicle_id`, `departure_time`, `arrival_time`, `price`, `total_seats`, `available_seats`, `features`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(12, 26, 10, 2, NULL, 3, '2025-11-15 14:15:00', '15:37:00', 23.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(13, 27, 10, 1, NULL, 4, '2025-12-20 17:58:00', '20:20:00', 27.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(14, 22, 10, 10, NULL, 5, '2025-07-05 08:49:00', '10:23:00', 19.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(15, 16, 10, 6, NULL, 3, '2025-08-10 09:40:00', '11:38:00', 29.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(16, 21, 10, 2, NULL, 5, '2025-06-01 07:46:00', '09:58:00', 27.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(17, 27, 9, 1, NULL, 5, '2025-12-20 17:01:00', '18:01:00', 34.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(18, 20, 9, 17, NULL, 3, '2025-12-07 07:56:00', '08:56:00', 35.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(19, 16, 9, 17, NULL, 5, '2025-08-10 06:20:00', '07:21:00', 29.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(20, 26, 9, 10, NULL, 4, '2025-11-15 15:24:00', '16:48:00', 37.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(21, 25, 9, 17, NULL, 3, '2025-10-10 08:00:00', '09:56:00', 34.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(22, 15, 3, 7, NULL, 3, '2025-07-20 16:55:00', '19:35:00', 33.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(23, 24, 3, 13, NULL, 4, '2025-09-15 14:27:00', '17:25:00', 32.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(24, 16, 3, 5, NULL, 4, '2025-08-10 09:00:00', '11:34:00', 31.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(25, 26, 3, 6, NULL, 2, '2025-11-15 13:36:00', '16:29:00', 28.00, 12, 12, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(26, 18, 3, 4, NULL, 2, '2025-10-12 16:14:00', '18:51:00', 34.00, 12, 12, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(27, 16, 8, 20, NULL, 4, '2025-08-10 07:49:00', '10:30:00', 24.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(28, 15, 8, 19, NULL, 5, '2025-07-20 14:59:00', '17:29:00', 30.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(29, 26, 8, 5, NULL, 4, '2025-11-15 14:23:00', '15:32:00', 36.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(30, 26, 8, 18, NULL, 3, '2025-11-15 13:10:00', '15:01:00', 32.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(31, 25, 8, 1, NULL, 4, '2025-10-10 06:02:00', '07:20:00', 23.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(32, 17, 7, 2, NULL, 5, '2025-09-05 07:06:00', '08:17:00', 27.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(33, 20, 7, 10, NULL, 3, '2025-12-07 08:42:00', '10:44:00', 23.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(34, 25, 7, 4, NULL, 4, '2025-10-10 07:57:00', '09:18:00', 35.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(35, 21, 7, 10, NULL, 3, '2025-06-01 08:49:00', '09:56:00', 21.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(36, 14, 7, 19, NULL, 5, '2025-06-15 05:51:00', '07:02:00', 36.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(37, 24, 4, 20, NULL, 5, '2025-09-15 15:08:00', '17:23:00', 31.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(38, 23, 4, 14, NULL, 4, '2025-08-20 05:50:00', '07:03:00', 36.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(39, 22, 4, 17, NULL, 4, '2025-07-05 07:44:00', '09:49:00', 36.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(40, 25, 4, 18, NULL, 4, '2025-10-10 06:58:00', '09:06:00', 20.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(41, 19, 4, 9, NULL, 3, '2025-11-01 13:41:00', '15:17:00', 18.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(42, 27, 5, 19, NULL, 4, '2025-12-20 15:03:00', '16:09:00', 32.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(43, 26, 5, 20, NULL, 4, '2025-11-15 13:40:00', '14:43:00', 26.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(44, 22, 5, 19, NULL, 5, '2025-07-05 09:05:00', '10:27:00', 26.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(45, 24, 5, 17, NULL, 5, '2025-09-15 12:12:00', '14:14:00', 17.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(46, 19, 5, 12, NULL, 2, '2025-11-01 12:13:00', '13:30:00', 25.00, 12, 12, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(47, 23, 11, 4, NULL, 4, '2025-08-20 05:29:00', '07:33:00', 37.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(48, 15, 11, 9, NULL, 3, '2025-07-20 14:04:00', '16:21:00', 30.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(49, 14, 11, 6, NULL, 3, '2025-06-15 07:28:00', '08:35:00', 21.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(50, 18, 11, 18, NULL, 4, '2025-10-12 17:28:00', '18:47:00', 25.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(51, 19, 11, 9, NULL, 5, '2025-11-01 15:09:00', '16:38:00', 31.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(52, 15, 6, 12, NULL, 2, '2025-07-20 15:21:00', '17:19:00', 36.00, 12, 12, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(53, 20, 6, 12, NULL, 2, '2025-12-07 08:00:00', '09:51:00', 32.00, 12, 12, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(54, 19, 6, 14, NULL, 5, '2025-11-01 14:36:00', '15:50:00', 35.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(55, 18, 6, 11, NULL, 5, '2025-10-12 14:08:00', '16:18:00', 18.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(56, 25, 6, 15, NULL, 5, '2025-10-10 08:04:00', '09:26:00', 27.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(57, 26, 2, 5, NULL, 5, '2025-11-15 16:51:00', '19:10:00', 18.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(58, 25, 2, 9, NULL, 4, '2025-10-10 05:46:00', '07:40:00', 18.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(59, 17, 2, 3, NULL, 5, '2025-09-05 07:23:00', '08:33:00', 17.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(60, 20, 2, 9, NULL, 5, '2025-12-07 06:40:00', '07:54:00', 16.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(61, 27, 2, 12, NULL, 3, '2025-12-20 16:17:00', '17:46:00', 32.00, 4, 0, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-31 08:29:11'),
(62, 18, 12, 7, NULL, 4, '2025-10-12 14:09:00', '16:51:00', 38.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(63, 27, 12, 6, NULL, 4, '2025-12-20 16:47:00', '17:50:00', 29.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(64, 22, 12, 20, NULL, 4, '2025-07-05 07:37:00', '09:52:00', 37.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(65, 20, 12, 6, NULL, 3, '2025-12-07 08:15:00', '10:07:00', 31.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(66, 24, 12, 9, NULL, 5, '2025-09-15 12:46:00', '14:01:00', 27.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(67, 15, 1, 11, NULL, 3, '2025-07-20 13:59:00', '16:11:00', 16.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(68, 20, 1, 5, NULL, 4, '2025-12-07 06:52:00', '09:51:00', 31.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(69, 26, 1, 5, NULL, 5, '2025-11-15 15:21:00', '16:40:00', 26.00, 15, 15, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(70, 24, 1, 15, NULL, 4, '2025-09-15 15:30:00', '16:33:00', 22.00, 30, 30, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(71, 16, 1, 5, NULL, 3, '2025-08-10 07:36:00', '09:17:00', 31.00, 4, 4, NULL, NULL, 1, '2025-05-30 09:01:44', '2025-05-30 09:01:44'),
(72, 1, 1, 1, NULL, NULL, '0000-00-00 00:00:00', '20:01:00', 25.00, 0, 9, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(73, 1, 1, 2, NULL, NULL, '0000-00-00 00:00:00', '21:55:00', 25.00, 0, 14, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(74, 1, 1, 3, NULL, NULL, '0000-00-00 00:00:00', '20:07:00', 30.00, 0, 18, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(75, 1, 1, 4, NULL, NULL, '0000-00-00 00:00:00', '19:19:00', 30.00, 0, 16, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(76, 1, 1, 5, NULL, NULL, '0000-00-00 00:00:00', '19:52:00', 23.00, 0, 25, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(77, 1, 1, 6, NULL, NULL, '0000-00-00 00:00:00', '20:27:00', 21.00, 0, 12, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(78, 1, 1, 7, NULL, NULL, '0000-00-00 00:00:00', '20:41:00', 15.00, 0, 18, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(79, 1, 1, 8, NULL, NULL, '0000-00-00 00:00:00', '20:28:00', 15.00, 0, 26, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(80, 1, 1, 9, NULL, NULL, '0000-00-00 00:00:00', '19:52:00', 22.00, 0, 10, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(81, 1, 1, 10, NULL, NULL, '0000-00-00 00:00:00', '19:23:00', 34.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(82, 1, 1, 11, NULL, NULL, '0000-00-00 00:00:00', '19:53:00', 16.00, 0, 29, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(83, 1, 1, 12, NULL, NULL, '0000-00-00 00:00:00', '21:07:00', 27.00, 0, 11, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(84, 1, 1, 13, NULL, NULL, '0000-00-00 00:00:00', '20:20:00', 34.00, 0, 23, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(85, 1, 1, 14, NULL, NULL, '0000-00-00 00:00:00', '20:43:00', 15.00, 0, 30, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(86, 1, 1, 15, NULL, NULL, '0000-00-00 00:00:00', '20:57:00', 29.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(87, 1, 1, 16, NULL, NULL, '0000-00-00 00:00:00', '20:02:00', 18.00, 0, 22, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(88, 1, 1, 17, NULL, NULL, '0000-00-00 00:00:00', '19:50:00', 22.00, 0, 19, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(89, 1, 1, 18, NULL, NULL, '0000-00-00 00:00:00', '19:08:00', 33.00, 0, 17, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(90, 1, 1, 19, NULL, NULL, '0000-00-00 00:00:00', '19:09:00', 18.00, 0, 20, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(91, 1, 1, 20, NULL, NULL, '0000-00-00 00:00:00', '19:52:00', 18.00, 0, 26, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(92, 1, 1, 21, NULL, NULL, '0000-00-00 00:00:00', '21:18:00', 34.00, 0, 14, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(93, 1, 1, 22, NULL, NULL, '0000-00-00 00:00:00', '20:58:00', 18.00, 0, 17, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(94, 1, 1, 23, NULL, NULL, '0000-00-00 00:00:00', '19:43:00', 26.00, 0, 8, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(95, 1, 2, 1, NULL, NULL, '0000-00-00 00:00:00', '21:45:00', 16.00, 0, 14, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(96, 1, 2, 2, NULL, NULL, '0000-00-00 00:00:00', '20:03:00', 29.00, 0, 13, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(97, 1, 2, 3, NULL, NULL, '0000-00-00 00:00:00', '21:13:00', 32.00, 0, 21, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(98, 1, 2, 4, NULL, NULL, '0000-00-00 00:00:00', '21:03:00', 30.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(99, 1, 2, 5, NULL, NULL, '0000-00-00 00:00:00', '19:56:00', 32.00, 0, 13, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(100, 1, 2, 6, NULL, NULL, '0000-00-00 00:00:00', '19:01:00', 30.00, 0, 17, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(101, 1, 2, 7, NULL, NULL, '0000-00-00 00:00:00', '21:35:00', 27.00, 0, 25, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(102, 1, 2, 8, NULL, NULL, '0000-00-00 00:00:00', '19:43:00', 18.00, 0, 10, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(103, 1, 2, 9, NULL, NULL, '0000-00-00 00:00:00', '19:46:00', 27.00, 0, 14, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(104, 1, 2, 10, NULL, NULL, '0000-00-00 00:00:00', '21:01:00', 19.00, 0, 19, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(105, 1, 2, 11, NULL, NULL, '0000-00-00 00:00:00', '20:51:00', 15.00, 0, 29, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(106, 1, 2, 12, NULL, NULL, '0000-00-00 00:00:00', '20:41:00', 25.00, 0, 18, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(107, 1, 2, 13, NULL, NULL, '0000-00-00 00:00:00', '21:35:00', 19.00, 0, 29, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(108, 1, 2, 14, NULL, NULL, '0000-00-00 00:00:00', '20:41:00', 20.00, 0, 16, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(109, 1, 2, 15, NULL, NULL, '0000-00-00 00:00:00', '21:38:00', 27.00, 0, 15, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(110, 1, 2, 16, NULL, NULL, '0000-00-00 00:00:00', '21:29:00', 23.00, 0, 19, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(111, 1, 2, 17, NULL, NULL, '0000-00-00 00:00:00', '19:40:00', 17.00, 0, 28, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(112, 1, 2, 18, NULL, NULL, '0000-00-00 00:00:00', '21:51:00', 19.00, 0, 22, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(113, 1, 2, 19, NULL, NULL, '0000-00-00 00:00:00', '20:45:00', 25.00, 0, 18, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(114, 1, 2, 20, NULL, NULL, '0000-00-00 00:00:00', '19:53:00', 33.00, 0, 10, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(115, 1, 2, 21, NULL, NULL, '0000-00-00 00:00:00', '21:54:00', 28.00, 0, 30, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(116, 1, 2, 22, NULL, NULL, '0000-00-00 00:00:00', '21:41:00', 16.00, 0, 30, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(117, 1, 2, 23, NULL, NULL, '0000-00-00 00:00:00', '20:28:00', 19.00, 0, 22, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(118, 1, 3, 1, NULL, NULL, '0000-00-00 00:00:00', '19:32:00', 31.00, 0, 27, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(119, 1, 3, 2, NULL, NULL, '0000-00-00 00:00:00', '19:27:00', 29.00, 0, 13, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(120, 1, 3, 3, NULL, NULL, '0000-00-00 00:00:00', '19:51:00', 18.00, 0, 13, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(121, 1, 3, 4, NULL, NULL, '0000-00-00 00:00:00', '20:48:00', 18.00, 0, 26, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(122, 1, 3, 5, NULL, NULL, '0000-00-00 00:00:00', '20:09:00', 26.00, 0, 9, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(123, 1, 3, 6, NULL, NULL, '0000-00-00 00:00:00', '20:42:00', 26.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(124, 1, 3, 7, NULL, NULL, '0000-00-00 00:00:00', '21:52:00', 28.00, 0, 13, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(125, 1, 3, 8, NULL, NULL, '0000-00-00 00:00:00', '19:10:00', 30.00, 0, 16, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(126, 1, 3, 9, NULL, NULL, '0000-00-00 00:00:00', '19:37:00', 17.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(127, 1, 3, 10, NULL, NULL, '0000-00-00 00:00:00', '21:50:00', 29.00, 0, 21, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(128, 1, 3, 11, NULL, NULL, '0000-00-00 00:00:00', '20:56:00', 28.00, 0, 12, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(129, 1, 3, 12, NULL, NULL, '0000-00-00 00:00:00', '20:29:00', 20.00, 0, 26, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(130, 1, 3, 13, NULL, NULL, '0000-00-00 00:00:00', '19:48:00', 22.00, 0, 18, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(131, 1, 3, 14, NULL, NULL, '0000-00-00 00:00:00', '19:59:00', 21.00, 0, 15, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(132, 1, 3, 15, NULL, NULL, '0000-00-00 00:00:00', '20:50:00', 22.00, 0, 16, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(133, 1, 3, 16, NULL, NULL, '0000-00-00 00:00:00', '21:10:00', 20.00, 0, 12, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(134, 1, 3, 17, NULL, NULL, '0000-00-00 00:00:00', '19:34:00', 34.00, 0, 22, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(135, 1, 3, 18, NULL, NULL, '0000-00-00 00:00:00', '20:40:00', 17.00, 0, 8, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(136, 1, 3, 19, NULL, NULL, '0000-00-00 00:00:00', '19:33:00', 18.00, 0, 24, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(137, 1, 3, 20, NULL, NULL, '0000-00-00 00:00:00', '19:13:00', 17.00, 0, 9, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(138, 1, 3, 21, NULL, NULL, '0000-00-00 00:00:00', '20:28:00', 24.00, 0, 25, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(139, 1, 3, 22, NULL, NULL, '0000-00-00 00:00:00', '20:04:00', 21.00, 0, 23, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03'),
(140, 1, 3, 23, NULL, NULL, '0000-00-00 00:00:00', '19:42:00', 32.00, 0, 15, NULL, NULL, 1, '2025-05-31 11:33:03', '2025-05-31 11:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `transport_types`
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
-- Dumping data for table `transport_types`
--

INSERT INTO `transport_types` (`id`, `name`, `description`, `icon`, `is_active`, `created_at`) VALUES
(1, 'باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 1, '2025-05-26 06:02:07'),
(2, 'فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 1, '2025-05-26 06:02:07'),
(3, 'سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, '2025-05-26 06:02:07'),
(4, 'باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 1, '2025-05-26 06:02:07'),
(5, 'ميكروباص', 'مركبة صغيرة للمجموعات الصغيرة', 'van-shuttle', 1, '2025-05-26 06:02:07'),
(6, 'باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 1, '2025-05-26 06:03:12'),
(7, 'فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 1, '2025-05-26 06:03:12'),
(8, 'سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, '2025-05-26 06:03:12'),
(9, 'باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 1, '2025-05-26 06:03:12'),
(10, 'ميكروباص', 'مركبة صغيرة للمجموعات الصغيرة', 'van-shuttle', 1, '2025-05-26 06:03:12'),
(11, 'باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 1, '2025-05-26 06:05:48'),
(12, 'فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 1, '2025-05-26 06:05:48'),
(13, 'سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, '2025-05-26 06:05:48'),
(14, 'باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 1, '2025-05-26 06:05:48'),
(15, 'ميكروباص', 'مركبة صغيرة للمجموعات الصغيرة', 'van-shuttle', 1, '2025-05-26 06:05:48'),
(16, 'باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 1, '2025-05-26 06:07:11'),
(17, 'فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 1, '2025-05-26 06:07:11'),
(18, 'سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, '2025-05-26 06:07:11'),
(19, 'باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 1, '2025-05-26 06:07:11'),
(20, 'ميكروباص', 'مركبة صغيرة للمجموعات الصغيرة', 'van-shuttle', 1, '2025-05-26 06:07:11'),
(21, 'حافلة سياحية', 'حافلة مكيفة مريحة', 'fa-bus', 1, '2025-05-31 11:33:03'),
(22, 'ميكروباص', 'ميكروباص سريع ومريح', 'fa-van-shuttle', 1, '2025-05-31 11:33:03'),
(23, 'حافلة عادية', 'حافلة اقتصادية', 'fa-bus-simple', 1, '2025-05-31 11:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `transport_vehicles`
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
-- Dumping data for table `transport_vehicles`
--

INSERT INTO `transport_vehicles` (`id`, `driver_id`, `transport_type_id`, `plate_number`, `model`, `year`, `color`, `capacity`, `photo`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 2, 2, 'غ ن 5678', 'هيونداي H1', 2019, 'فضي', 12, 'vehicles/van1.jpg', '[\"ac\", \"luggage_space\", \"comfortable_seats\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(3, 3, 3, 'غ ن 9876', 'تويوتا كامري', 2021, 'أسود', 4, 'vehicles/car1.jpg', '[\"ac\", \"leather_seats\", \"gps\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(4, 4, 1, 'غ ن 4321', 'إيفيكو ديلي', 2018, 'أزرق', 30, 'vehicles/bus2.jpg', '[\"ac\", \"comfortable_seats\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12'),
(5, 5, 4, 'غ ن 8765', 'كيا بونجو', 2017, 'أحمر', 15, 'vehicles/minibus1.jpg', '[\"ac\", \"basic_comfort\"]', 1, '2025-05-26 06:03:12', '2025-05-26 06:03:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `password_hashed`, `role`, `reset_token`, `reset_expires`, `created_at`, `updated_at`, `profile_image`, `last_login`, `last_login_ip`, `timezone`, `preferred_language`, `two_factor_enabled`, `status`, `email_verified`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$fMgeeCfcZ7nhHWFWKNbCK.SVauLNd3DA50oopV/YYAr6MRh4INu0.', '0501234567', '$2y$10$fMgeeCfcZ7nhHWFWKNbCK.SVauLNd3DA50oopV/YYAr6MRh4INu0.', '', NULL, NULL, '2025-04-21 09:41:54', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(2, 'ahamd', 'asdwqd@sdfd.fs', '$2y$10$i0CEnCjXipNZLxwfg14il.eF93nxFb6W8RnPIKo0/7.63A9uITiIq', '+64033550355', '$2y$10$i0CEnCjXipNZLxwfg14il.eF93nxFb6W8RnPIKo0/7.63A9uITiIq', 'user', NULL, NULL, '2025-04-21 12:35:58', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(5, 'Admin User', 'admin@admin.com', '$2y$10$dpMa8.2MV.j6nP4jujkfreYkwMtsrD1mo5MMC3ab8MQHIxGNzqZdK', '0501234567', '$2y$10$dpMa8.2MV.j6nP4jujkfreYkwMtsrD1mo5MMC3ab8MQHIxGNzqZdK', '', NULL, NULL, '2025-04-28 07:44:13', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(6, 'tran', 'tran@gmail.com', 'tran123', '25412114521', 'tran123', 'transport_admin', NULL, NULL, '2025-05-01 17:19:27', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(8, 'المدير العام', 'superadmin@palestine-tickets.com', 'SuperAdmin2024!', '0599123456', 'SuperAdmin2024!', 'super_admin', NULL, NULL, '2025-05-30 07:59:41', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
(9, 'مدير المواصلات', 'transport@palestine-tickets.com', 'Transport2024!', '0599000000', 'Transport2024!', 'transport_admin', NULL, NULL, '2025-05-30 08:01:26', '2025-05-31 08:13:45', NULL, NULL, NULL, 'Asia/Jerusalem', 'ar', 0, 'active', 0),
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
-- Indexes for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_type`),
  ADD KEY `granted_by` (`granted_by`),
  ADD KEY `idx_admin_permissions_user_id` (`user_id`),
  ADD KEY `idx_admin_permissions_type` (`permission_type`);

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
  ADD KEY `idx_transport_price_id` (`transport_price_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `notification_settings`
--
ALTER TABLE `notification_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transport_drivers`
--
ALTER TABLE `transport_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `transport_prices`
--
ALTER TABLE `transport_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_starting_points`
--
ALTER TABLE `transport_starting_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `transport_trips`
--
ALTER TABLE `transport_trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `transport_types`
--
ALTER TABLE `transport_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_activity_log`
--
ALTER TABLE `admin_activity_log`
  ADD CONSTRAINT `admin_activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_permissions_ibfk_2` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_settings`
--
ALTER TABLE `notification_settings`
  ADD CONSTRAINT `notification_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_cards`
--
ALTER TABLE `payment_cards`
  ADD CONSTRAINT `payment_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_cards_ibfk_2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_technical_info`
--
ALTER TABLE `payment_technical_info`
  ADD CONSTRAINT `payment_technical_info_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment_cards` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_bookings`
--
ALTER TABLE `transport_bookings`
  ADD CONSTRAINT `transport_bookings_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_bookings_ibfk_4` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transport_prices`
--
ALTER TABLE `transport_prices`
  ADD CONSTRAINT `transport_prices_ibfk_1` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_prices_ibfk_2` FOREIGN KEY (`starting_point_id`) REFERENCES `transport_starting_points` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transport_trips`
--
ALTER TABLE `transport_trips`
  ADD CONSTRAINT `transport_trips_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_trips_ibfk_2` FOREIGN KEY (`starting_point_id`) REFERENCES `transport_starting_points` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_3` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_4` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_5` FOREIGN KEY (`vehicle_id`) REFERENCES `transport_vehicles` (`id`),
  ADD CONSTRAINT `transport_trips_ibfk_6` FOREIGN KEY (`transport_price_id`) REFERENCES `transport_prices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transport_vehicles`
--
ALTER TABLE `transport_vehicles`
  ADD CONSTRAINT `transport_vehicles_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `transport_drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transport_vehicles_ibfk_2` FOREIGN KEY (`transport_type_id`) REFERENCES `transport_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

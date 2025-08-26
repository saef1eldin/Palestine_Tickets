<?php
// تضمين ملفات الإعداد وقاعدة البيانات
require_once '../includes/init.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// جلب بيانات المستخدم الحالي مع الملف الشخصي
$current_user = null;
$admin_profile = null;
try {
    // التحقق من تسجيل الدخول
    if (isset($_SESSION['user_id'])) {
        // جلب بيانات المستخدم
        $db->query("SELECT * FROM users WHERE id = :user_id");
        $db->bind(':user_id', $_SESSION['user_id']);
        $current_user = $db->single();

        if ($current_user) {
            // جلب الملف الشخصي للإدمن
            $db->query("
                SELECT ap.*, u.email
                FROM admin_profiles ap
                JOIN users u ON ap.user_id = u.id
                WHERE ap.user_id = :user_id
            ");
            $db->bind(':user_id', $_SESSION['user_id']);
            $admin_profile = $db->single();

            // إذا لم يتم العثور على ملف شخصي، استخدم بيانات المستخدم الأساسية
            if (!$admin_profile) {
                $admin_profile = [
                    'display_name' => $current_user['name'],
                    'email' => $current_user['email'],
                    'profile_image' => '../assets/12.jpg',
                    'department' => 'إدارة النقل'
                ];
            } else {
                // تأكد من أن مسار الصورة صحيح
                if (!empty($admin_profile['profile_image']) && !str_starts_with($admin_profile['profile_image'], '../')) {
                    $admin_profile['profile_image'] = '../' . $admin_profile['profile_image'];
                }
            }
        }
    }
    } catch (Exception $e) {
        // في حالة حدوث خطأ، حاول استخدام بيانات الجلسة إن وجدت أو قيم افتراضية آمنة
        if (isset($_SESSION['user_id'])) {
            $current_user = [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? 'المدير',
                'email' => $_SESSION['user_email'] ?? 'admin@transport.com'
            ];
        } else {
            $current_user = [
                'id' => 0,
                'name' => 'المدير',
                'email' => 'admin@transport.com'
            ];
        }

        $admin_profile = [
            'display_name' => $current_user['name'] ?? 'المدير',
            'email' => $current_user['email'] ?? 'admin@transport.com',
            'profile_image' => '../assets/12.jpg',
            'department' => 'إدارة النقل'
        ];

        error_log("Error fetching current user: " . $e->getMessage());
    }

// جلب الإحصائيات من قاعدة البيانات
try {
    // إجمالي الإيرادات
    $db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE status = 'confirmed'");
    $revenue_result = $db->single();
    $total_revenue = $revenue_result['total_revenue'] ?? 0;

    // إجمالي الرحلات
    $db->query("SELECT COUNT(*) as total_trips FROM transport_trips WHERE is_active = 1");
    $trips_result = $db->single();
    $total_trips = $trips_result['total_trips'] ?? 0;

    // عدد السائقين النشطين
    $db->query("SELECT COUNT(*) as active_drivers FROM transport_drivers WHERE is_active = 1 AND status = 'available'");
    $drivers_result = $db->single();
    $active_drivers = $drivers_result['active_drivers'] ?? 0;

    // عدد الحجوزات الجديدة
    $db->query("SELECT COUNT(*) as new_bookings FROM transport_bookings WHERE status = 'pending' OR status = 'confirmed'");
    $bookings_result = $db->single();
    $new_bookings = $bookings_result['new_bookings'] ?? 0;

    // جلب آخر الأنشطة
    $db->query("
        SELECT
            tb.booking_code,
            tb.customer_name,
            tb.created_at,
            tt.departure_time,
            tsp.name as starting_point_name,
            e.title as event_title
        FROM transport_bookings tb
        LEFT JOIN transport_trips tt ON tb.trip_id = tt.id
        LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
        LEFT JOIN events e ON tb.event_id = e.id
        ORDER BY tb.created_at DESC
        LIMIT 5
    ");
    $recent_activities = $db->resultSet();

    // إعدادات التصفح بالصفحات
    $items_per_page = 5;
    $trips_page = isset($_GET['trips_page']) ? max(1, (int)$_GET['trips_page']) : 1;
    $points_page = isset($_GET['points_page']) ? max(1, (int)$_GET['points_page']) : 1;
    $inactive_trips_page = isset($_GET['inactive_page']) ? max(1, (int)$_GET['inactive_page']) : 1;

    $trips_offset = ($trips_page - 1) * $items_per_page;
    $points_offset = ($points_page - 1) * $items_per_page;
    $inactive_trips_offset = ($inactive_trips_page - 1) * $items_per_page;

    // جلب عدد نقاط الانطلاق الإجمالي
    $db->query("SELECT COUNT(*) as total FROM transport_starting_points");
    $total_points = $db->single()['total'];
    $total_points_pages = ceil($total_points / $items_per_page);

    // جلب نقاط الانطلاق مع التصفح
    $db->query("SELECT * FROM transport_starting_points ORDER BY is_active DESC, name ASC LIMIT :limit OFFSET :offset");
    $db->bind(':limit', $items_per_page);
    $db->bind(':offset', $points_offset);
    $departure_points = $db->resultSet();

    // جلب جميع نقاط الانطلاق النشطة للنماذج
    $db->query("SELECT * FROM transport_starting_points WHERE is_active = 1 ORDER BY name ASC");
    $all_departure_points = $db->resultSet();

    // جلب عدد الرحلات النشطة الإجمالي
    $db->query("SELECT COUNT(*) as total FROM transport_trips WHERE is_active = 1");
    $total_active_trips = $db->single()['total'];
    $total_trips_pages = ceil($total_active_trips / $items_per_page);

    // تحديث المقاعد المتاحة لجميع الرحلات قبل العرض
    $db->query("
        UPDATE transport_trips tt
        SET available_seats = total_seats - (
            SELECT COALESCE(SUM(passengers_count), 0)
            FROM transport_bookings tb
            WHERE tb.trip_id = tt.id AND tb.status IN ('confirmed', 'pending')
        )
        WHERE tt.is_active = 1
    ");
    $db->execute();

    // جلب الرحلات النشطة مع التصفح
    $db->query("
        SELECT
            tt.*,
            tsp.name as starting_point_name,
            tty.name as transport_type_name,
            e.title as event_title,
            e.date_time as event_date,
            d.name as driver_name,
            d.phone as driver_phone,
            d.rating as driver_rating,
            d.is_active as driver_active
        FROM transport_trips tt
        LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
        LEFT JOIN transport_types tty ON tt.transport_type_id = tty.id
        LEFT JOIN events e ON tt.event_id = e.id
        LEFT JOIN transport_drivers d ON tt.driver_id = d.id
        WHERE tt.is_active = 1
        ORDER BY tt.departure_time DESC
        LIMIT :limit OFFSET :offset
    ");
    $db->bind(':limit', $items_per_page);
    $db->bind(':offset', $trips_offset);
    $trips = $db->resultSet();

    // جلب عدد الرحلات غير النشطة الإجمالي
    $db->query("SELECT COUNT(*) as total FROM transport_trips WHERE is_active = 0");
    $total_inactive_trips = $db->single()['total'];
    $total_inactive_pages = ceil($total_inactive_trips / $items_per_page);

    // جلب السائقين مع معلومات المركبات والمنطقة (المفعلين أولاً ثم غير المفعلين)
    $db->query("
        SELECT
            td.*,
            tv.plate_number,
            tv.model,
            tv.year,
            tv.color,
            tv.capacity,
            tty.name as vehicle_type,
            tty.icon as vehicle_icon,
            COUNT(tv.id) as vehicles_count
        FROM transport_drivers td
        LEFT JOIN transport_vehicles tv ON td.id = tv.driver_id
        LEFT JOIN transport_types tty ON tv.transport_type_id = tty.id
        GROUP BY td.id
        ORDER BY td.is_active DESC, td.name ASC
    ");
    $drivers = $db->resultSet();

    // جلب أنواع وسائل النقل للنماذج بدون تكرار
$db->query("SELECT id, name, description, icon FROM transport_types WHERE is_active = 1 ORDER BY name");
$transport_types = $db->resultSet();


    // جلب الحجوزات النشطة (استثناء المنتهية والملغية)
    $db->query("
        SELECT
            tb.*,
            tt.departure_time,
            tsp.name as starting_point_name,
            e.title as event_title
        FROM transport_bookings tb
        LEFT JOIN transport_trips tt ON tb.trip_id = tt.id
        LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
        LEFT JOIN events e ON tb.event_id = e.id
        WHERE tb.status NOT IN ('expired', 'cancelled')
            AND (e.status != 'expired' OR e.status IS NULL)
            AND (e.date_time > NOW() OR e.date_time IS NULL)
        ORDER BY
            CASE
                WHEN tb.status = 'pending' THEN 1
                WHEN tb.status = 'confirmed' THEN 2
                WHEN tb.status = 'rejected' THEN 3
                ELSE 4
            END,
            tb.created_at DESC
        LIMIT 20
    ");
    $bookings = $db->resultSet();

    // جلب الحجوزات المنتهية
    $db->query("
        SELECT
            tb.*,
            tt.departure_time,
            tsp.name as starting_point_name,
            e.title as event_title
        FROM transport_bookings tb
        LEFT JOIN transport_trips tt ON tb.trip_id = tt.id
        LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
        LEFT JOIN events e ON tb.event_id = e.id
        WHERE tb.status = 'expired'
            OR e.status = 'expired'
            OR e.date_time < NOW()
        ORDER BY tb.created_at DESC
        LIMIT 10
    ");
    $expired_bookings = $db->resultSet();

} catch (Exception $e) {
    // في حالة حدوث خطأ، استخدم قيم افتراضية
    $total_revenue = 0;
    $total_trips = 0;
    $active_drivers = 0;
    $new_bookings = 0;
    $recent_activities = [];
    $departure_points = [];
    $trips = [];
    $drivers = [];
    $bookings = [];
    $expired_bookings = [];
    $transport_types = [];
    error_log("Dashboard Error: " . $e->getMessage());
}

// دالة لتنسيق التاريخ والوقت
function formatDateTime($datetime) {
    if (!$datetime) return 'غير محدد';
    $date = new DateTime($datetime);
    return $date->format('Y-m-d H:i');
}

// دالة لتنسيق المبلغ
function formatPrice($amount) {
    return number_format($amount, 2) . ' ₪';
}

// دالة لتنسيق حالة الحجز
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>قيد الانتظار</span>',
        'confirmed' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i>مؤكد</span>',
        'rejected' => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i>مرفوض</span>',
        'cancelled' => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"><i class="fas fa-ban mr-1"></i>ملغي</span>',
        'completed' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800"><i class="fas fa-check-circle mr-1"></i>مكتمل</span>'
    ];
    return $badges[$status] ?? '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">' . $status . '</span>';
}

// دالة لتنسيق حالة السائق
function getDriverStatusBadge($status, $is_active = true) {
    if (!$is_active) {
        return '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">غير مفعل</span>';
    }

    $badges = [
        'available' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">متاح</span>',
        'busy' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">مشغول</span>',
        'offline' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">غير متصل</span>'
    ];
    return $badges[$status] ?? '<span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">' . $status . '</span>';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard-rtl.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                            800: '#6b21a8',
                            900: '#581c87',
                        },
                        secondary: {
                            100: '#f0f9ff',
                            200: '#e0f2fe',
                            300: '#bae6fd',
                            400: '#7dd3fc',
                            500: '#38bdf8',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .dashboard-section {
            display: none;
        }
        .dashboard-section.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .nav-item.active {
            border-bottom: 3px solid white;
            font-weight: bold;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .inactive-item {
            opacity: 0.6;
            background-color: #f9f9f9;
        }
        .inactive-item:hover {
            opacity: 0.8;
        }

        /* Delete Modal Animations */
        #deleteModalContent {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #deleteModal.hidden #deleteModalContent {
            transform: scale(0.95);
            opacity: 0;
        }

        /* Button hover effects */
        #confirmDeleteBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }

        #cancelDeleteBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Admin Dropdown Menu */
        #adminDropdownMenu {
            animation: slideDown 0.2s ease-out;
            transform-origin: top;
        }

        #adminDropdownMenu.hidden {
            animation: slideUp 0.2s ease-in;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
        }

        #adminMenuIcon {
            transition: transform 0.2s ease;
        }

        #adminMenuButton:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        #adminMenuButton img {
            transition: all 0.3s ease;
        }

        #adminMenuButton:hover img {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Profile Modal Styles */
        #profileModalContent {
            transition: all 0.3s ease;
        }

        #profileModal input:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .profile-image-container {
            position: relative;
            transition: all 0.3s ease;
        }

        .profile-image-container:hover {
            transform: scale(1.05);
        }

        .camera-icon {
            transition: all 0.3s ease;
        }

        .camera-icon:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Main Container -->
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-gradient-to-r from-primary-700 to-primary-900 text-white p-4 shadow-lg">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <i class="fas fa-bus text-3xl"></i>
                    <h1 class="text-2xl font-bold">لوحة تحكم المواصلات</h1>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="relative">
                        <a href="admin_notifications.php" class="text-white hover:text-gray-200 transition" title="إشعارات الإدمن">
                            <i class="fas fa-bell text-xl"></i>
                            <?php
                            // جلب عدد الإشعارات غير المقروءة
                            try {
                                $db->query("SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0");
                                $unread_notifications = $db->single()['count'] ?? 0;
                            } catch (Exception $e) {
                                $unread_notifications = 0;
                            }
                            if ($unread_notifications > 0):
                            ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $unread_notifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="relative">
                        <button onclick="toggleAdminMenu()" class="flex items-center space-x-2 space-x-reverse hover:bg-primary-600 rounded-lg px-3 py-2 transition-all duration-200" id="adminMenuButton">
                            <div class="w-8 h-8 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="font-medium"><?php echo htmlspecialchars($admin_profile['display_name']); ?></span>
                            <i class="fas fa-chevron-down text-sm transition-transform duration-200" id="adminMenuIcon"></i>
                        </button>

                        <!-- Admin Dropdown Menu -->
                        <div id="adminDropdownMenu" class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50 hidden">
                            <div class="py-2">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600 text-xs"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($admin_profile['display_name']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($admin_profile['email']); ?></p>
                                        </div>
                                    </div>
                                </div>

                                <button onclick="openProfileModal()" class="flex items-center w-full px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-user-edit text-green-500 ml-3 w-4"></i>
                                    <span>تعديل الملف الشخصي</span>
                                </button>

                                <a href="../index.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-home text-blue-500 ml-3 w-4"></i>
                                    <span>العودة للموقع الرئيسي</span>
                                </a>

                                <a href="admin_notifications.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-bell text-yellow-500 ml-3 w-4"></i>
                                    <span>الإشعارات</span>
                                    <?php if ($unread_notifications > 0): ?>
                                        <span class="mr-auto bg-red-500 text-white text-xs rounded-full px-2 py-1"><?php echo $unread_notifications; ?></span>
                                    <?php endif; ?>
                                </a>

                                <a href="scheduled_tasks_interface.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                    <i class="fas fa-cogs text-green-500 ml-3 w-4"></i>
                                    <span>المهام المجدولة</span>
                                </a>

                                <?php if (isset($current_user['last_login']) && $current_user['last_login']): ?>
                                <div class="px-4 py-2 border-t border-gray-100">
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-clock ml-1"></i>
                                        آخر دخول: <?php echo date('Y-m-d H:i', strtotime($current_user['last_login'])); ?>
                                    </p>
                                </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Horizontal Navigation -->
        <nav class="bg-gradient-to-r from-primary-600 to-primary-800 text-white shadow-md">
            <div class="container mx-auto overflow-x-auto">
                <div class="flex space-x-1 space-x-reverse py-2 px-1">
                    <button onclick="showSection('overview')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition active">
                        <i class="fas fa-home ml-2"></i> نظرة عامة
                    </button>
                    <button onclick="showSection('revenue')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-chart-line ml-2"></i> الإيرادات
                    </button>
                    <button onclick="showSection('departure-points')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-map-marker-alt ml-2"></i> نقاط الانطلاق
                    </button>
                    <button onclick="showSection('trips')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-route ml-2"></i> الرحلات
                    </button>
                    <button onclick="showSection('drivers')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-id-card-alt ml-2"></i> السائقين
                    </button>
                    <button onclick="showSection('bookings')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-calendar-check ml-2"></i> الحجوزات
                    </button>
                    <button onclick="showSection('analytics')" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition">
                        <i class="fas fa-chart-pie ml-2"></i> التحليلات
                    </button>
                    <a href="scheduled_tasks_interface.php" class="nav-item px-4 py-3 rounded-lg hover:bg-green-500 transition bg-green-600 inline-block">
                        <i class="fas fa-cogs ml-2"></i> تنظيف تلقائي
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto p-4">
            <!-- Overview Section -->
            <section id="overview" class="dashboard-section active">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">نظرة عامة على لوحة التحكم</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">إجمالي الإيرادات</p>
                                <h3 class="text-2xl font-bold text-primary-700"><?php echo formatPrice($total_revenue); ?></h3>
                                <p class="text-blue-500 text-sm mt-1"><i class="fas fa-info-circle mr-1"></i> من الحجوزات المؤكدة</p>
                            </div>
                            <div class="bg-primary-100 p-3 rounded-full">
                                <i class="fas fa-shekel-sign text-primary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">إجمالي الرحلات</p>
                                <h3 class="text-2xl font-bold text-secondary-700"><?php echo $total_trips; ?></h3>
                                <p class="text-blue-500 text-sm mt-1"><i class="fas fa-info-circle mr-1"></i> الرحلات النشطة</p>
                            </div>
                            <div class="bg-secondary-100 p-3 rounded-full">
                                <i class="fas fa-route text-secondary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">السائقين المتاحين</p>
                                <h3 class="text-2xl font-bold text-amber-700"><?php echo $active_drivers; ?></h3>
                                <p class="text-green-500 text-sm mt-1"><i class="fas fa-check-circle mr-1"></i> جاهزين للعمل</p>
                            </div>
                            <div class="bg-amber-100 p-3 rounded-full">
                                <i class="fas fa-id-card-alt text-amber-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">الحجوزات الجديدة</p>
                                <h3 class="text-2xl font-bold text-emerald-700"><?php echo $new_bookings; ?></h3>
                                <p class="text-blue-500 text-sm mt-1"><i class="fas fa-info-circle mr-1"></i> قيد المعالجة</p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-full">
                                <i class="fas fa-calendar-check text-emerald-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">الأنشطة الحديثة</h3>
                        <button class="text-primary-600 hover:text-primary-800">عرض الكل</button>
                    </div>
                    <div class="space-y-4">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="flex items-start space-x-4 space-x-reverse">
                                    <div class="bg-primary-100 p-2 rounded-full">
                                        <i class="fas fa-calendar-check text-primary-600"></i>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="font-medium">حجز جديد #<?php echo htmlspecialchars($activity['booking_code']); ?></p>
                                        <p class="text-gray-500 text-sm">
                                            <?php echo htmlspecialchars($activity['customer_name']); ?>
                                            حجز رحلة إلى <?php echo htmlspecialchars($activity['event_title'] ?? 'فعالية'); ?>
                                            <?php if ($activity['starting_point_name']): ?>
                                                من <?php echo htmlspecialchars($activity['starting_point_name']); ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <span class="text-gray-400 text-sm">
                                        <?php
                                        $time_diff = time() - strtotime($activity['created_at']);
                                        if ($time_diff < 3600) {
                                            echo floor($time_diff / 60) . ' دقيقة';
                                        } elseif ($time_diff < 86400) {
                                            echo floor($time_diff / 3600) . ' ساعة';
                                        } else {
                                            echo floor($time_diff / 86400) . ' يوم';
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p>لا توجد أنشطة حديثة</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


            </section>

            <!-- Revenue Section -->
            <section id="revenue" class="dashboard-section">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">
                    <i class="fas fa-chart-line text-green-600 ml-2"></i>
                    إدارة الإيرادات
                </h2>

                <?php
                // جلب إحصائيات الإيرادات
                $db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE status = 'confirmed'");
                $revenue_total = $db->single()['total_revenue'] ?? 0;

                $db->query("SELECT COUNT(*) as confirmed_bookings FROM transport_bookings WHERE status = 'confirmed'");
                $revenue_confirmed_bookings = $db->single()['confirmed_bookings'];

                $db->query("SELECT AVG(total_amount) as avg_revenue FROM transport_bookings WHERE status = 'confirmed'");
                $revenue_avg = $db->single()['avg_revenue'] ?? 0;

                // الإيرادات الشهرية
                $db->query("
                    SELECT
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        DATE_FORMAT(created_at, '%M %Y') as month_name,
                        SUM(total_amount) as monthly_revenue,
                        COUNT(*) as monthly_bookings
                    FROM transport_bookings
                    WHERE status = 'confirmed'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC
                ");
                $revenue_monthly_data = $db->resultSet();

                // أفضل المسارات من ناحية الإيرادات
                $db->query("
                    SELECT
                        sp.name as starting_point_name,
                        e.title as event_title,
                        SUM(tb.total_amount) as route_revenue,
                        COUNT(tb.id) as bookings_count
                    FROM transport_bookings tb
                    JOIN transport_trips t ON tb.trip_id = t.id
                    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
                    JOIN events e ON tb.event_id = e.id
                    WHERE tb.status = 'confirmed'
                    GROUP BY t.starting_point_id, tb.event_id
                    ORDER BY route_revenue DESC
                    LIMIT 5
                ");
                $revenue_top_routes = $db->resultSet();
                ?>

                <!-- إحصائيات الإيرادات الرئيسية -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">إجمالي الإيرادات</h3>
                            <i class="fas fa-dollar-sign text-green-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-green-600 mb-2"><?php echo number_format($revenue_total, 2); ?> ₪</div>
                        <p class="text-gray-500 text-sm">من <?php echo $revenue_confirmed_bookings; ?> حجز مؤكد</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">متوسط الإيراد</h3>
                            <i class="fas fa-chart-bar text-blue-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo number_format($revenue_avg, 2); ?> ₪</div>
                        <p class="text-gray-500 text-sm">للحجز الواحد</p>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">الإيراد الشهري</h3>
                            <i class="fas fa-calendar text-purple-500"></i>
                        </div>
                        <?php
                        $current_month_revenue = !empty($revenue_monthly_data) ? $revenue_monthly_data[0]['monthly_revenue'] : 0;
                        ?>
                        <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo number_format($current_month_revenue, 2); ?> ₪</div>
                        <p class="text-gray-500 text-sm">هذا الشهر</p>
                    </div>
                </div>

                <!-- الإيرادات الشهرية -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">الإيرادات الشهرية (آخر 6 أشهر)</h3>
                    <?php if (!empty($revenue_monthly_data)): ?>
                        <div class="space-y-4">
                            <?php foreach ($revenue_monthly_data as $month): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-semibold text-gray-800"><?php echo $month['month_name']; ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo $month['monthly_bookings']; ?> حجز</p>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-lg font-bold text-green-600"><?php echo number_format($month['monthly_revenue'], 2); ?> ₪</div>
                                        <div class="text-sm text-gray-500">
                                            متوسط: <?php echo number_format($month['monthly_revenue'] / max($month['monthly_bookings'], 1), 2); ?> ₪
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">لا توجد بيانات إيرادات متاحة</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- أفضل المسارات -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">أفضل المسارات (حسب الإيرادات)</h3>
                    <?php if (!empty($revenue_top_routes)): ?>
                        <div class="space-y-4">
                            <?php foreach ($revenue_top_routes as $index => $route): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center ml-3">
                                            <span class="text-blue-600 font-bold"><?php echo $index + 1; ?></span>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($route['starting_point_name']); ?></h4>
                                            <p class="text-sm text-gray-600">إلى: <?php echo htmlspecialchars($route['event_title']); ?></p>
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-lg font-bold text-green-600"><?php echo number_format($route['route_revenue'], 2); ?> ₪</div>
                                        <div class="text-sm text-gray-500"><?php echo $route['bookings_count']; ?> حجز</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-route text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">لا توجد بيانات مسارات متاحة</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Departure Points Section -->
            <section id="departure-points" class="dashboard-section">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-900">نقاط الانطلاق</h2>
                    <button onclick="openAddDepartureModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center">
                        <i class="fas fa-plus ml-2"></i> إضافة جديد
                    </button>
                </div>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المعرف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الموقع</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الوصف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($departure_points)): ?>
                                    <?php foreach ($departure_points as $point): ?>
                                        <tr class="hover:bg-gray-50 <?php echo !$point['is_active'] ? 'inactive-item' : ''; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">DP-<?php echo str_pad($point['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium"><?php echo htmlspecialchars($point['name']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($point['description'] ?? 'لا يوجد وصف'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button onclick="toggleDeparturePointStatus(<?php echo $point['id']; ?>, <?php echo $point['is_active'] ? 'false' : 'true'; ?>)"
                                                        class="px-3 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $point['is_active'] ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'; ?>"
                                                        title="انقر لتغيير الحالة">
                                                    <?php echo $point['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                                    <i class="fas fa-sync-alt mr-1"></i>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button onclick="editDeparturePoint(<?php echo $point['id']; ?>)" class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteDeparturePoint(<?php echo $point['id']; ?>, '<?php echo htmlspecialchars($point['name']); ?>')" class="text-red-600 hover:text-red-800" title="حذف">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-map-marker-alt text-4xl mb-4"></i>
                                            <p>لا توجد نقاط انطلاق مسجلة</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- أزرار التصفح لنقاط الانطلاق -->
                    <?php if ($total_points_pages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <div class="flex justify-center items-center space-x-2 space-x-reverse">
                            <?php if ($points_page > 1): ?>
                                <a href="?section=departure-points&points_page=<?php echo $points_page - 1; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">السابق</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_points_pages; $i++): ?>
                                <?php if ($i == $points_page): ?>
                                    <span class="px-3 py-1 bg-primary-600 text-white rounded"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?section=departure-points&points_page=<?php echo $i; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($points_page < $total_points_pages): ?>
                                <a href="?section=departure-points&points_page=<?php echo $points_page + 1; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">التالي</a>
                            <?php endif; ?>
                        </div>
                        <p class="text-center text-sm text-gray-500 mt-2">
                            صفحة <?php echo $points_page; ?> من <?php echo $total_points_pages; ?> (<?php echo $total_points; ?> نقطة انطلاق)
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Trips Section -->
            <section id="trips" class="dashboard-section">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-900">إدارة الرحلات</h2>
                    <div class="flex space-x-3 space-x-reverse">
                        <button onclick="updateAllSeats()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center">
                            <i class="fas fa-sync-alt ml-2"></i> تحديث المقاعد
                        </button>
                        <button id="toggleInactiveTripsBtn" onclick="toggleInactiveTrips()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center">
                            <i class="fas fa-eye-slash ml-2"></i> إظهار الرحلات غير النشطة
                        </button>
                        <button onclick="openAddTripModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center">
                            <i class="fas fa-plus ml-2"></i> إضافة رحلة جديدة
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">معرف الرحلة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المسار</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">السائق</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">وقت المغادرة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">وقت الوصول</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">السعر</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المقاعد</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($trips)): ?>
                                    <?php foreach ($trips as $trip): ?>
                                        <tr class="hover:bg-gray-50 <?php echo !$trip['is_active'] ? 'inactive-item' : ''; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">TR-<?php echo str_pad($trip['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo htmlspecialchars($trip['starting_point_name'] ?? 'نقطة انطلاق'); ?>
                                                إلى
                                                <?php echo htmlspecialchars($trip['event_title'] ?? 'الفعالية'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($trip['driver_name']): ?>
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <i class="fas fa-user text-gray-600 text-sm"></i>
                                                            </div>
                                                        </div>
                                                        <div class="mr-3">
                                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($trip['driver_name']); ?></p>
                                                            <?php if ($trip['driver_rating']): ?>
                                                                <p class="text-xs text-gray-500">⭐ <?php echo $trip['driver_rating']; ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!$trip['driver_active']): ?>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                                غير نشط
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-400 text-sm">غير محدد</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo formatDateTime($trip['departure_time']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo htmlspecialchars($trip['arrival_time']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo formatPrice($trip['price']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($trip['available_seats'] == 0): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">مكتمل</span>
                                                <?php else: ?>
                                                    <button onclick="toggleTripStatus(<?php echo $trip['id']; ?>, <?php echo $trip['is_active'] ? 'false' : 'true'; ?>)"
                                                            class="px-3 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $trip['is_active'] ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'; ?>"
                                                            title="انقر لتغيير الحالة">
                                                        <?php echo $trip['is_active'] ? 'نشط' : 'غير نشط'; ?>
                                                        <i class="fas fa-sync-alt mr-1"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button onclick="editTrip(<?php echo $trip['id']; ?>)" class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteTrip(<?php echo $trip['id']; ?>, '<?php echo htmlspecialchars($trip['starting_point_name'] . ' إلى ' . $trip['event_title']); ?>')" class="text-red-600 hover:text-red-800" title="حذف">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-route text-4xl mb-4"></i>
                                            <p>لا توجد رحلات مسجلة</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- أزرار التصفح للرحلات النشطة -->
                    <?php if ($total_trips_pages > 1): ?>
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <div class="flex justify-center items-center space-x-2 space-x-reverse">
                            <?php if ($trips_page > 1): ?>
                                <a href="?section=trips&trips_page=<?php echo $trips_page - 1; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">السابق</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_trips_pages; $i++): ?>
                                <?php if ($i == $trips_page): ?>
                                    <span class="px-3 py-1 bg-primary-600 text-white rounded"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?section=trips&trips_page=<?php echo $i; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($trips_page < $total_trips_pages): ?>
                                <a href="?section=trips&trips_page=<?php echo $trips_page + 1; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">التالي</a>
                            <?php endif; ?>
                        </div>
                        <p class="text-center text-sm text-gray-500 mt-2">
                            صفحة <?php echo $trips_page; ?> من <?php echo $total_trips_pages; ?> (<?php echo $total_active_trips; ?> رحلة نشطة)
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Inactive Trips Table -->
                <div id="inactiveTripsSection" class="bg-white rounded-xl shadow-md overflow-hidden mt-6 hidden">
                    <div class="bg-gray-100 px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-eye-slash text-gray-600 ml-2"></i>
                            الرحلات غير النشطة
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">معرف الرحلة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المسار</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">وقت المغادرة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">وقت الوصول</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">السعر</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المقاعد</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="inactiveTripsTableBody">
                                <!-- سيتم ملء هذا الجدول بواسطة JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Drivers Section -->
            <section id="drivers" class="dashboard-section">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-900">إدارة السائقين</h2>
                    <button onclick="openAddDriverModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center">
                        <i class="fas fa-plus ml-2"></i> إضافة سائق جديد
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if (!empty($drivers)): ?>
                        <?php foreach ($drivers as $driver): ?>
                            <!-- Driver Card -->
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition <?php echo !$driver['is_active'] ? 'inactive-item' : ''; ?>">
                                <div class="p-6">
                                    <div class="flex items-center space-x-4 space-x-reverse mb-4">
                                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white border-3 border-primary-200">
                                            <i class="fas fa-user text-2xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($driver['name']); ?></h3>
                                            <p class="text-gray-500 text-sm">معرف: DR-<?php echo str_pad($driver['id'], 4, '0', STR_PAD_LEFT); ?></p>
                                            <?php if ($driver['governorate'] || $driver['city']): ?>
                                            <p class="text-xs text-blue-600">
                                                <i class="fas fa-map-marker-alt ml-1"></i>
                                                <?php echo htmlspecialchars($driver['governorate'] ?? ''); ?>
                                                <?php if ($driver['city']): ?>
                                                    - <?php echo htmlspecialchars($driver['city']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-phone-alt text-green-600 ml-2"></i>
                                            <span class="text-gray-700"><?php echo htmlspecialchars($driver['phone']); ?></span>
                                        </div>

                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-id-card text-blue-600 ml-2"></i>
                                            <span class="text-gray-700">رخصة: <?php echo htmlspecialchars($driver['license_number']); ?></span>
                                            <?php if ($driver['license_type']): ?>
                                                <span class="text-xs bg-gray-100 px-2 py-1 rounded mr-2">
                                                    <?php
                                                    $license_types = [
                                                        'private' => 'خاصة',
                                                        'public' => 'عامة',
                                                        'commercial' => 'تجارية',
                                                        'heavy' => 'ثقيلة'
                                                    ];
                                                    echo $license_types[$driver['license_type']] ?? $driver['license_type'];
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($driver['region']): ?>
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-compass text-purple-600 ml-2"></i>
                                            <span class="text-gray-700">
                                                <?php
                                                $regions = [
                                                    'north' => 'الشمال',
                                                    'center' => 'الوسط',
                                                    'south' => 'الجنوب'
                                                ];
                                                echo $regions[$driver['region']] ?? $driver['region'];
                                                ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>

                                        <?php if ($driver['plate_number']): ?>
                                            <div class="bg-gray-50 p-3 rounded-lg">
                                                <div class="flex items-center text-sm mb-1">
                                                    <i class="fas fa-car text-blue-600 ml-2"></i>
                                                    <span class="font-medium text-gray-800"><?php echo htmlspecialchars($driver['plate_number']); ?></span>
                                                </div>
                                                <div class="text-xs text-gray-600">
                                                    <?php echo htmlspecialchars($driver['model'] ?? ''); ?>
                                                    <?php if ($driver['year']): ?>
                                                        (<?php echo $driver['year']; ?>)
                                                    <?php endif; ?>
                                                    <?php if ($driver['color']): ?>
                                                        - <?php echo htmlspecialchars($driver['color']); ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($driver['vehicle_type']): ?>
                                                <div class="text-xs text-purple-600 mt-1">
                                                    <i class="fas fa-<?php echo $driver['vehicle_icon'] ?? 'bus'; ?> ml-1"></i>
                                                    <?php echo htmlspecialchars($driver['vehicle_type']); ?>
                                                    <?php if ($driver['capacity']): ?>
                                                        (<?php echo $driver['capacity']; ?> مقعد)
                                                    <?php endif; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                                                <div class="flex items-center text-sm text-yellow-800">
                                                    <i class="fas fa-exclamation-triangle ml-2"></i>
                                                    <span>لا توجد مركبة مسجلة</span>
                                                </div>
                                                <button onclick="addVehicleToDriver(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['name']); ?>')"
                                                        class="text-xs text-blue-600 hover:text-blue-800 mt-1">
                                                    <i class="fas fa-plus ml-1"></i>إضافة مركبة
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <div class="flex justify-between items-center text-sm pt-2">
                                            <div class="flex items-center">
                                                <i class="fas fa-star text-yellow-500 ml-1"></i>
                                                <span class="text-gray-700"><?php echo number_format($driver['rating'], 1); ?>/5</span>
                                            </div>
                                            <?php if ($driver['experience_years']): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-clock text-gray-500 ml-1"></i>
                                                <span class="text-gray-700"><?php echo $driver['experience_years']; ?> سنة</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="flex justify-between items-center mb-2">
                                            <div class="flex space-x-2 space-x-reverse">
                                                <button onclick="toggleDriverStatus(<?php echo $driver['id']; ?>, 'available')"
                                                        class="px-2 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $driver['status'] == 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600 hover:bg-green-50'; ?>"
                                                        title="متاح">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button onclick="toggleDriverStatus(<?php echo $driver['id']; ?>, 'busy')"
                                                        class="px-2 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $driver['status'] == 'busy' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600 hover:bg-yellow-50'; ?>"
                                                        title="مشغول">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                                <button onclick="toggleDriverStatus(<?php echo $driver['id']; ?>, 'offline')"
                                                        class="px-2 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $driver['status'] == 'offline' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600 hover:bg-red-50'; ?>"
                                                        title="غير متصل">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </div>
                                            <button onclick="toggleDriverActiveStatus(<?php echo $driver['id']; ?>, <?php echo $driver['is_active'] ? 'false' : 'true'; ?>)"
                                                    class="px-3 py-1 text-xs rounded-full cursor-pointer transition-all duration-200 <?php echo $driver['is_active'] ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'; ?>"
                                                    title="تفعيل/إلغاء تفعيل السائق">
                                                <?php echo $driver['is_active'] ? 'مفعل' : 'غير مفعل'; ?>
                                                <i class="fas fa-power-off mr-1"></i>
                                            </button>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <?php echo getDriverStatusBadge($driver['status'], $driver['is_active']); ?>
                                            <div>
                                                <button onclick="editDriver(<?php echo $driver['id']; ?>)" class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteDriver(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['name']); ?>')" class="text-red-600 hover:text-red-800" title="حذف">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center text-gray-500 py-12">
                            <i class="fas fa-users text-6xl mb-4"></i>
                            <p class="text-xl">لا يوجد سائقين مسجلين</p>
                            <p class="text-sm mt-2">قم بإضافة سائق جديد للبدء</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Bookings Section -->
            <section id="bookings" class="dashboard-section">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">إدارة الحجوزات</h2>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">رقم الحجز</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">العميل</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الرحلة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">التاريخ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المقاعد</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المبلغ</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                <?php echo htmlspecialchars($booking['booking_code']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo htmlspecialchars($booking['customer_name']); ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($booking['customer_phone']); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php echo htmlspecialchars($booking['starting_point_name'] ?? 'نقطة انطلاق'); ?>
                                                إلى
                                                <?php echo htmlspecialchars($booking['event_title'] ?? 'الفعالية'); ?>
                                                <div class="text-sm text-gray-500">
                                                    TR-<?php echo str_pad($booking['trip_id'], 4, '0', STR_PAD_LEFT); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo formatDateTime($booking['departure_time']); ?>
                                                <div class="text-sm text-gray-500">
                                                    حُجز في: <?php echo date('Y-m-d', strtotime($booking['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo $booking['passengers_count'] ?? $booking['seats_count']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo formatPrice($booking['total_amount']); ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php
                                                    $payment_methods = [
                                                        'bank_transfer' => 'تحويل بنكي',
                                                        'cash_on_delivery' => 'دفع عند الاستلام',
                                                        'mobile_pay' => 'دفع إلكتروني',
                                                        'credit_card' => 'بطاقة ائتمان'
                                                    ];
                                                    echo $payment_methods[$booking['payment_method']] ?? $booking['payment_method'];
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo getStatusBadge($booking['status']); ?>
                                                <?php if ($booking['status'] == 'rejected' && !empty($booking['rejection_reason'])): ?>
                                                    <div class="text-xs text-red-600 mt-1" title="<?php echo htmlspecialchars($booking['rejection_reason']); ?>">
                                                        <i class="fas fa-info-circle"></i> سبب الرفض
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <?php if ($booking['status'] == 'pending'): ?>
                                                    <div class="flex space-x-2 space-x-reverse justify-center">
                                                        <button onclick="approveBooking(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_code']); ?>')"
                                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                title="قبول الحجز">
                                                            <i class="fas fa-check"></i> قبول
                                                        </button>
                                                        <button onclick="rejectBooking(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['booking_code']); ?>')"
                                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition-colors duration-200"
                                                                title="رفض الحجز">
                                                            <i class="fas fa-times"></i> رفض
                                                        </button>
                                                    </div>
                                                <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                    <span class="text-green-600 text-sm">
                                                        <i class="fas fa-check-circle"></i> تم القبول
                                                    </span>
                                                    <?php if ($booking['response_date']): ?>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <?php echo date('Y-m-d H:i', strtotime($booking['response_date'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php elseif ($booking['status'] == 'rejected'): ?>
                                                    <span class="text-red-600 text-sm">
                                                        <i class="fas fa-times-circle"></i> تم الرفض
                                                    </span>
                                                    <?php if ($booking['response_date']): ?>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            <?php echo date('Y-m-d H:i', strtotime($booking['response_date'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-gray-500 text-sm">
                                                        <i class="fas fa-ban"></i> ملغي
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                            <p>لا توجد حجوزات</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($bookings)): ?>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    عرض <span class="font-medium">1</span> إلى <span class="font-medium"><?php echo count($bookings); ?></span> من <span class="font-medium"><?php echo count($bookings); ?></span> حجز
                                </div>
                                <div class="flex space-x-2 space-x-reverse">
                                    <button class="px-3 py-1 border rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
                                        السابق
                                    </button>
                                    <button class="px-3 py-1 border rounded-md text-sm bg-primary-600 text-white hover:bg-primary-700">
                                        1
                                    </button>
                                    <button class="px-3 py-1 border rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
                                        التالي
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- قسم الحجوزات المنتهية -->
                <?php if (!empty($expired_bookings)): ?>
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-history text-red-500 ml-2"></i>
                        الحجوزات المنتهية
                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full mr-2">
                            <?php echo count($expired_bookings); ?>
                        </span>
                    </h3>
                    <div class="bg-red-50 rounded-xl shadow-md overflow-hidden border border-red-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-red-200">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">رقم الحجز</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">العميل</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">الرحلة</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">المبلغ</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">تاريخ الانتهاء</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-800 uppercase tracking-wider">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-red-100">
                                    <?php foreach ($expired_bookings as $booking): ?>
                                    <tr class="hover:bg-red-25 opacity-75">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-600">
                                            <?php echo htmlspecialchars($booking['booking_code']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['customer_phone']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($booking['starting_point_name'] ?? 'نقطة انطلاق'); ?>
                                                إلى
                                                <?php echo htmlspecialchars($booking['event_title'] ?? 'الفعالية'); ?>
                                            </div>
                                            <?php if ($booking['departure_time']): ?>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-clock ml-1"></i>
                                                <?php echo formatDateTime($booking['departure_time']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <?php echo formatPrice($booking['total_amount']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatDateTime($booking['created_at']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle ml-1"></i>منتهية
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </section>

            <!-- Analytics Section -->
            <section id="analytics" class="dashboard-section">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">
                    <i class="fas fa-chart-pie text-blue-600 ml-2"></i>
                    لوحة التحليلات
                </h2>

                <?php
                // جلب الإحصائيات الأساسية
                $db->query("SELECT COUNT(*) as total_trips FROM transport_trips");
                $analytics_total_trips = $db->single()['total_trips'];

                $db->query("SELECT COUNT(*) as total_bookings FROM transport_bookings");
                $analytics_total_bookings = $db->single()['total_bookings'];

                $db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE status = 'confirmed'");
                $analytics_total_revenue = $db->single()['total_revenue'] ?? 0;

                $db->query("SELECT AVG(total_amount) as avg_booking_value FROM transport_bookings WHERE status = 'confirmed'");
                $analytics_avg_booking_value = $db->single()['avg_booking_value'] ?? 0;

                // معدل الإشغال
                $db->query("
                    SELECT
                        SUM(tb.passengers_count) as booked_seats,
                        SUM(t.total_seats) as total_seats
                    FROM transport_bookings tb
                    JOIN transport_trips t ON tb.trip_id = t.id
                    WHERE tb.status = 'confirmed'
                ");
                $analytics_occupancy_data = $db->single();
                $analytics_occupancy_rate = $analytics_occupancy_data['total_seats'] > 0 ?
                    ($analytics_occupancy_data['booked_seats'] / $analytics_occupancy_data['total_seats']) * 100 : 0;

                // أشهر المسارات
                $db->query("
                    SELECT
                        sp.name as starting_point_name,
                        e.title as event_title,
                        COUNT(tb.id) as booking_count,
                        SUM(tb.total_amount) as route_revenue
                    FROM transport_bookings tb
                    JOIN transport_trips t ON tb.trip_id = t.id
                    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
                    JOIN events e ON tb.event_id = e.id
                    WHERE tb.status = 'confirmed'
                    GROUP BY t.starting_point_id, tb.event_id
                    ORDER BY booking_count DESC
                    LIMIT 5
                ");
                $analytics_popular_routes = $db->resultSet();

                // البيانات الشهرية
                $db->query("
                    SELECT
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        SUM(total_amount) as monthly_revenue,
                        COUNT(*) as monthly_bookings
                    FROM transport_bookings
                    WHERE status = 'confirmed'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month
                ");
                $analytics_monthly_data = $db->resultSet();

                // إحصائيات طرق الدفع
                $db->query("
                    SELECT
                        payment_method,
                        COUNT(*) as count,
                        SUM(total_amount) as total_amount,
                        AVG(total_amount) as avg_amount
                    FROM transport_bookings
                    WHERE status = 'confirmed'
                    GROUP BY payment_method
                    ORDER BY count DESC
                ");
                $analytics_payment_methods = $db->resultSet();
                ?>

                <!-- الإحصائيات الرئيسية -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- إجمالي الإيرادات -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">إجمالي الإيرادات</h3>
                            <i class="fas fa-dollar-sign text-green-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-green-600 mb-2"><?php echo number_format($analytics_total_revenue, 2); ?> ₪</div>
                        <p class="text-gray-500 text-sm">من الحجوزات المؤكدة</p>
                    </div>

                    <!-- إجمالي الحجوزات -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">إجمالي الحجوزات</h3>
                            <i class="fas fa-ticket-alt text-blue-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $analytics_total_bookings; ?></div>
                        <p class="text-gray-500 text-sm">حجز إجمالي</p>
                    </div>

                    <!-- متوسط قيمة الحجز -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">متوسط قيمة الحجز</h3>
                            <i class="fas fa-chart-line text-orange-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-orange-600 mb-2"><?php echo number_format($analytics_avg_booking_value, 2); ?> ₪</div>
                        <p class="text-gray-500 text-sm">للحجز الواحد</p>
                    </div>

                    <!-- معدل الإشغال -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">معدل الإشغال</h3>
                            <i class="fas fa-users text-purple-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo number_format($analytics_occupancy_rate, 1); ?>%</div>
                        <p class="text-gray-500 text-sm">من المقاعد المتاحة</p>
                    </div>
                </div>

                <!-- أشهر المسارات -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">
                        <i class="fas fa-route text-blue-500 ml-2"></i>
                        أشهر المسارات
                    </h3>
                    <?php if (!empty($analytics_popular_routes)): ?>
                        <div class="space-y-4">
                            <?php foreach ($analytics_popular_routes as $route): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($route['starting_point_name']); ?></h4>
                                        <p class="text-sm text-gray-600">إلى: <?php echo htmlspecialchars($route['event_title']); ?></p>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-lg font-bold text-blue-600"><?php echo $route['booking_count']; ?> حجز</div>
                                        <div class="text-sm text-green-600"><?php echo number_format($route['route_revenue'], 2); ?> ₪</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-route text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">لا توجد بيانات مسارات متاحة</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- إحصائيات طرق الدفع -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">
                        <i class="fas fa-credit-card text-blue-500 ml-2"></i>
                        إحصائيات طرق الدفع
                    </h3>

                    <?php if (!empty($analytics_payment_methods)): ?>
                        <?php
                        $total_payments = array_sum(array_column($analytics_payment_methods, 'count'));
                        $total_amount_all = array_sum(array_column($analytics_payment_methods, 'total_amount'));
                        ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <?php foreach ($analytics_payment_methods as $method): ?>
                                <?php
                                $percentage = $total_payments > 0 ? ($method['count'] / $total_payments) * 100 : 0;
                                $method_name = [
                                    'credit_card' => 'بطاقة ائتمان',
                                    'paypal' => 'PayPal',
                                    'cash' => 'نقداً',
                                    'bank_transfer' => 'تحويل بنكي'
                                ][$method['payment_method']] ?? $method['payment_method'];

                                $icon = [
                                    'credit_card' => 'fas fa-credit-card text-blue-500',
                                    'paypal' => 'fab fa-paypal text-blue-600',
                                    'cash' => 'fas fa-money-bill-wave text-green-500',
                                    'bank_transfer' => 'fas fa-university text-purple-500'
                                ][$method['payment_method']] ?? 'fas fa-payment text-gray-500';
                                ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-semibold text-gray-800"><?php echo $method_name; ?></h4>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">عدد الحجوزات:</span>
                                            <span class="font-bold"><?php echo $method['count']; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">النسبة:</span>
                                            <span class="font-bold text-blue-600"><?php echo number_format($percentage, 1); ?>%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">إجمالي المبلغ:</span>
                                            <span class="font-bold text-green-600"><?php echo number_format($method['total_amount'], 2); ?> ₪</span>
                                        </div>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-chart-bar text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">لا توجد بيانات دفع متاحة حالياً</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-100 border-t border-gray-200 py-4">
            <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
                <p>© 2024 لوحة تحكم المواصلات. جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </div>

    <!-- Add/Edit Departure Point Modal -->
    <div id="departureModal" class="fixed inset-0 modal-overlay flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="departureModalTitle" class="text-xl font-bold text-gray-800">إضافة نقطة انطلاق جديدة</h3>
                    <button onclick="closeDepartureModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="departureForm" onsubmit="saveDeparturePoint(event)">
                    <input type="hidden" id="departureId" name="id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم الموقع *</label>
                            <input type="text" id="departureName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف *</label>
                            <textarea id="departureDescription" name="description" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500" rows="2"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="departureActive" name="is_active" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="departureActive" class="mr-2 block text-sm text-gray-900">نشط</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                        <button type="button" onclick="closeDepartureModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Trip Modal -->
    <div id="tripModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="tripModalTitle" class="text-xl font-bold text-gray-800">إضافة رحلة جديدة</h3>
                    <button onclick="closeTripModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="tripForm" onsubmit="saveTrip(event)">
                    <input type="hidden" id="tripId" name="id">
                    <input type="hidden" id="tripFullDateTime" name="full_departure_time">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">الفعالية *</label>
                            <select id="tripEvent" name="event_id" required onchange="updateDepartureTimeFromEvent()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">اختر الفعالية</option>
                                <?php
                                try {
                                    $db->query("SELECT id, title, date_time FROM events WHERE is_active = 1 AND date_time > NOW() ORDER BY date_time ASC");
                                    $events = $db->resultSet();
                                    foreach ($events as $event) {
                                        echo '<option value="' . $event['id'] . '" data-event-datetime="' . $event['date_time'] . '">' . htmlspecialchars($event['title']) . ' - ' . date('Y-m-d H:i', strtotime($event['date_time'])) . '</option>';
                                    }
                                } catch (Exception $e) {
                                    echo '<option value="1">فعالية تجريبية</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">نقطة الانطلاق *</label>
                            <select id="tripStartingPoint" name="starting_point_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">اختر نقطة الانطلاق</option>
                                <?php foreach ($all_departure_points as $point): ?>
                                    <option value="<?php echo $point['id']; ?>"><?php echo htmlspecialchars($point['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">السائق والمركبة *</label>
                            <select id="tripDriver" name="driver_id" required onchange="updateSeatsFromVehicle()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">اختر السائق مع مركبته</option>
                                <?php
                                // جلب السائقين مع مركباتهم
                                try {
                                    $db->query("
                                        SELECT
                                            d.id as driver_id,
                                            d.name as driver_name,
                                            d.rating,
                                            d.is_active as driver_active,
                                            v.id as vehicle_id,
                                            v.model,
                                            v.year,
                                            v.plate_number,
                                            v.color,
                                            v.capacity,
                                            tt.name as transport_type_name
                                        FROM transport_drivers d
                                        LEFT JOIN transport_vehicles v ON d.id = v.driver_id
                                        LEFT JOIN transport_types tt ON v.transport_type_id = tt.id
                                        WHERE d.is_active = 1 AND v.id IS NOT NULL
                                        ORDER BY d.name ASC
                                    ");
                                    $drivers_with_vehicles = $db->resultSet();

                                    foreach ($drivers_with_vehicles as $driver):
                                ?>
                                    <option value="<?php echo $driver['driver_id']; ?>"
                                            data-vehicle-id="<?php echo $driver['vehicle_id']; ?>"
                                            data-capacity="<?php echo $driver['capacity']; ?>">
                                        👤 <?php echo htmlspecialchars($driver['driver_name']); ?>
                                        <?php if ($driver['rating']): ?>
                                            (⭐ <?php echo $driver['rating']; ?>)
                                        <?php endif; ?>
                                        🚗 <?php echo $driver['model'] . ' ' . $driver['year']; ?>
                                        (<?php echo $driver['plate_number']; ?>)
                                        - <?php echo $driver['transport_type_name']; ?>
                                        - <?php echo $driver['capacity']; ?> مقعد
                                    </option>
                                <?php
                                    endforeach;
                                } catch (Exception $e) {
                                    echo '<option value="">خطأ في تحميل البيانات</option>';
                                }
                                ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">السائق مطلوب - سيتم تحديد عدد المقاعد تلقائياً من المركبة</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ المغادرة *</label>
                                <input type="date" id="tripDepartureDate" name="departure_date" required readonly class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 bg-gray-100">
                                <p class="text-xs text-gray-500 mt-1">يتم تحديده تلقائياً من تاريخ الفعالية</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">وقت المغادرة *</label>
                                <input type="time" id="tripDepartureTime" name="departure_time" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <p class="text-xs text-gray-500 mt-1">يجب أن يكون قبل بداية الفعالية بـ 15 دقيقة على الأقل</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">وقت الوصول *</label>
                            <input type="time" id="tripArrivalTime" name="arrival_time" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="text-xs text-gray-500 mt-1">يجب أن يكون بعد وقت المغادرة وقبل بداية الفعالية بـ 15 دقيقة</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">السعر (₪) *</label>
                                <input type="number" id="tripPrice" name="price" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">عدد المقاعد *</label>
                                <input type="number" id="tripSeats" name="total_seats" min="1" max="100" required readonly class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 bg-gray-100">
                                <p class="text-xs text-gray-500 mt-1">يتم تحديده تلقائياً من سعة المركبة</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">وصف الرحلة</label>
                            <textarea id="tripDescription" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500" rows="2"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="tripActive" name="is_active" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="tripActive" class="mr-2 block text-sm text-gray-900">نشط</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                        <button type="button" onclick="closeTripModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Driver Modal -->
    <div id="driverModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="driverModalTitle" class="text-xl font-bold text-gray-800">إضافة سائق جديد</h3>
                    <button onclick="closeDriverModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="driverForm" onsubmit="saveDriver(event)">
                    <input type="hidden" id="driverId" name="id">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- معلومات السائق الأساسية -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">معلومات السائق</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل *</label>
                                <input type="text" id="driverName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف *</label>
                                <input type="tel" id="driverPhone" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الرخصة *</label>
                                <input type="text" id="driverLicense" name="license_number" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">نوع الرخصة</label>
                                <select id="driverLicenseType" name="license_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="private">رخصة خاصة</option>
                                    <option value="public">رخصة عامة</option>
                                    <option value="commercial">رخصة تجارية</option>
                                    <option value="heavy">رخصة مركبات ثقيلة</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ انتهاء الرخصة</label>
                                <input type="date" id="driverLicenseExpiry" name="license_expiry" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">سنوات الخبرة</label>
                                    <input type="number" id="driverExperience" name="experience_years" min="0" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
                                    <select id="driverStatus" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <option value="available">متاح</option>
                                        <option value="busy">مشغول</option>
                                        <option value="offline">غير متصل</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- معلومات الموقع والمركبة -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">معلومات الموقع</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المحافظة</label>
                                <select id="driverGovernorate" name="governorate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">اختر المحافظة</option>
                                    <option value="غزة">غزة</option>
                                    <option value="الشمال">الشمال</option>
                                    <option value="الوسطى">الوسطى</option>
                                    <option value="خان يونس">خان يونس</option>
                                    <option value="رفح">رفح</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المدينة/المنطقة</label>
                                <input type="text" id="driverCity" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>

                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mt-6">معلومات المركبة</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">نوع المركبة *</label>
                                <select id="driverVehicleType" name="transport_type_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">اختر نوع المركبة</option>
                                    <?php foreach ($transport_types as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">السعة *</label>
                                <input type="number" id="driverVehicleCapacity" name="vehicle_capacity" min="1" max="100" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="driverActive" name="is_active" checked class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="driverActive" class="mr-2 block text-sm text-gray-900">نشط</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                        <button type="button" onclick="closeDriverModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            حفظ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full hidden z-50 backdrop-blur-sm">
        <div class="relative top-20 mx-auto p-0 border-0 max-w-full w-full sm:w-96 shadow-2xl rounded-xl bg-white transform transition-all duration-300 scale-95 opacity-0 mx-4" id="deleteModalContent">
            <div class="relative">
                <!-- Header with gradient background -->
                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-t-xl p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-white bg-opacity-20 backdrop-blur-sm">
                        <i id="deleteModalIcon" class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mt-4" id="deleteModalTitle">تأكيد الحذف</h3>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="text-center mb-6">
                        <p class="text-gray-700 leading-relaxed" id="deleteModalMessage">
                            هل أنت متأكد من أنك تريد حذف هذا العنصر؟ لا يمكن التراجع عن هذا الإجراء.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-center space-x-3 space-x-reverse">
                        <button id="confirmDeleteBtn"
                                class="px-8 py-3 bg-red-600 text-white font-semibold rounded-lg shadow-lg hover:bg-red-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-red-300 transform hover:scale-105 transition-all duration-200">
                            <i class="fas fa-trash-alt ml-2"></i>
                            نعم، احذف
                        </button>
                        <button id="cancelDeleteBtn"
                                class="px-8 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg shadow-lg hover:bg-gray-200 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-gray-300 transform hover:scale-105 transition-all duration-200">
                            <i class="fas fa-times ml-2"></i>
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navigation functionality
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // Find the button that was clicked and make it active
            const buttons = document.querySelectorAll('.nav-item');
            buttons.forEach(button => {
                if (button.getAttribute('onclick') === `showSection('${sectionId}')`) {
                    button.classList.add('active');
                }
            });

            // Save current section to localStorage and URL
            localStorage.setItem('activeSection', sectionId);

            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('section', sectionId);
            window.history.replaceState({}, '', url);
        }

        // Admin Menu Functions
        function toggleAdminMenu() {
            const menu = document.getElementById('adminDropdownMenu');
            const icon = document.getElementById('adminMenuIcon');
            const button = document.getElementById('adminMenuButton');

            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
                button.classList.add('bg-primary-600');
            } else {
                menu.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
                button.classList.remove('bg-primary-600');
            }
        }

        function showLogoutConfirm() {
            if (confirm('هل أنت متأكد من تسجيل الخروج؟')) {
                // يمكن إضافة منطق تسجيل الخروج هنا
                window.location.href = '../login.php';
            }
        }

        // Close admin menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('adminDropdownMenu');
            const button = document.getElementById('adminMenuButton');
            const icon = document.getElementById('adminMenuIcon');

            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
                button.classList.remove('bg-primary-600');
            }
        });

        // Booking Management Functions
        function approveBooking(bookingId, bookingCode) {
            if (confirm(`هل أنت متأكد من قبول الحجز ${bookingCode}؟\n\nسيتم إرسال إشعار للعميل بالقبول.`)) {
                fetch('actions/bookings_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=approve&booking_id=${bookingId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        // عرض رسالة الإشعار المرسلة
                        if (data.notification_message) {
                            setTimeout(() => {
                                alert('رسالة الإشعار المرسلة للعميل:\n\n' + data.notification_message);
                            }, 1000);
                        }
                        reloadWithActiveSection(2000, 'bookings');
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('حدث خطأ في الاتصال', 'error');
                });
            }
        }

        function rejectBooking(bookingId, bookingCode) {
            const rejectionReasons = [
                'الرحلة ممتلئة بالكامل',
                'تم إلغاء الرحلة لظروف طارئة',
                'بيانات الحجز غير مكتملة',
                'عدم توفر وسيلة النقل المطلوبة',
                'مشكلة في طريقة الدفع',
                'أخرى (يرجى التواصل معنا)'
            ];

            let reasonsHtml = '<select id="rejectionReason" class="w-full p-2 border rounded">';
            reasonsHtml += '<option value="">اختر سبب الرفض...</option>';
            rejectionReasons.forEach(reason => {
                reasonsHtml += `<option value="${reason}">${reason}</option>`;
            });
            reasonsHtml += '</select>';
            reasonsHtml += '<textarea id="customReason" placeholder="أو اكتب سبب مخصص..." class="w-full p-2 border rounded mt-2" rows="3"></textarea>';

            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-bold mb-4 text-red-600">رفض الحجز ${bookingCode}</h3>
                    <p class="text-gray-600 mb-4">يرجى اختيار سبب الرفض:</p>
                    ${reasonsHtml}
                    <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                            إلغاء
                        </button>
                        <button onclick="confirmReject(${bookingId}, '${bookingCode}')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            تأكيد الرفض
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function confirmReject(bookingId, bookingCode) {
            const selectedReason = document.getElementById('rejectionReason').value;
            const customReason = document.getElementById('customReason').value.trim();
            const finalReason = customReason || selectedReason;

            if (!finalReason) {
                alert('يرجى اختيار أو كتابة سبب الرفض');
                return;
            }

            fetch('actions/bookings_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=reject&booking_id=${bookingId}&rejection_reason=${encodeURIComponent(finalReason)}`
            })
            .then(response => response.json())
            .then(data => {
                document.querySelector('.fixed').remove(); // إغلاق المودال
                if (data.success) {
                    showMessage(data.message, 'success');
                    // عرض رسالة الإشعار المرسلة
                    if (data.notification_message) {
                        setTimeout(() => {
                            alert('رسالة الإشعار المرسلة للعميل:\n\n' + data.notification_message);
                        }, 1000);
                    }
                    reloadWithActiveSection(2000, 'bookings');
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                document.querySelector('.fixed').remove();
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        // Initialize page with correct section
        document.addEventListener('DOMContentLoaded', function() {
            // Check URL parameter first, then localStorage, then default to overview
            const urlParams = new URLSearchParams(window.location.search);
            const urlSection = urlParams.get('section');
            const savedSection = localStorage.getItem('activeSection');
            const activeSection = urlSection || savedSection || 'overview';

            // Show the correct section
            showSectionById(activeSection);
        });

        function showSectionById(sectionId) {
            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });

            // Remove active class from all nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            // Show selected section
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');

                // Find and activate corresponding nav item
                const navButtons = document.querySelectorAll('.nav-item');
                navButtons.forEach(button => {
                    if (button.getAttribute('onclick') === `showSection('${sectionId}')`) {
                        button.classList.add('active');
                    }
                });

                // Save to localStorage
                localStorage.setItem('activeSection', sectionId);
            }
        }

        // Utility functions
        function reloadWithActiveSection(delay = 1000, defaultSection = null) {
            // Save current section before reload
            const currentSection = localStorage.getItem('activeSection') || defaultSection || 'overview';
            localStorage.setItem('activeSection', currentSection);
            setTimeout(() => location.reload(), delay);
        }

        // دالة لإنشاء أزرار التصفح
        function createPaginationButtons(currentPage, totalPages, pageParam) {
            if (totalPages <= 1) return '';

            let pagination = '<div class="flex justify-center items-center space-x-2 space-x-reverse mt-4">';

            // زر الصفحة السابقة
            if (currentPage > 1) {
                pagination += `<button onclick="changePage(${currentPage - 1}, '${pageParam}')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">السابق</button>`;
            }

            // أرقام الصفحات
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    pagination += `<button class="px-3 py-1 bg-primary-600 text-white rounded">${i}</button>`;
                } else {
                    pagination += `<button onclick="changePage(${i}, '${pageParam}')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">${i}</button>`;
                }
            }

            // زر الصفحة التالية
            if (currentPage < totalPages) {
                pagination += `<button onclick="changePage(${currentPage + 1}, '${pageParam}')" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">التالي</button>`;
            }

            pagination += '</div>';
            return pagination;
        }

        // دالة لتغيير الصفحة
        function changePage(page, pageParam) {
            const url = new URL(window.location);
            url.searchParams.set(pageParam, page);
            url.searchParams.set('section', getCurrentActiveSection());
            window.location.href = url.toString();
        }

        // Delete Modal Functions
        let deleteCallback = null;

        function showDeleteModal(title, message, onConfirm, icon = 'fa-exclamation-triangle') {
            document.getElementById('deleteModalTitle').textContent = title;
            document.getElementById('deleteModalMessage').textContent = message;
            document.getElementById('deleteModalIcon').className = `fas ${icon} text-white text-2xl`;
            deleteCallback = onConfirm;

            const modal = document.getElementById('deleteModal');
            const modalContent = document.getElementById('deleteModalContent');

            modal.classList.remove('hidden');

            // Animate modal appearance
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function hideDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = document.getElementById('deleteModalContent');

            // Animate modal disappearance
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                deleteCallback = null;
            }, 300);
        }

        // Event listeners for delete modal
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                if (deleteCallback) {
                    deleteCallback();
                }
                hideDeleteModal();
            });

            document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
                hideDeleteModal();
            });

            // Close modal when clicking outside
            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideDeleteModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !document.getElementById('deleteModal').classList.contains('hidden')) {
                    hideDeleteModal();
                }
            });
        });

        function showMessage(message, type = 'success') {
            const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            const alertHtml = `
                <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 ${alertClass} border px-6 py-4 rounded-lg shadow-lg alert-message">
                    <div class="flex items-center">
                        <i class="${icon} ml-2"></i>
                        <span class="block sm:inline font-medium">${message}</span>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', alertHtml);

            setTimeout(() => {
                const alert = document.querySelector('.alert-message');
                if (alert) {
                    alert.style.animation = 'slideInDown 0.3s ease-out reverse';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 3000);
        }



        // Departure Points Functions
        function openAddDepartureModal() {
            document.getElementById('departureModalTitle').textContent = 'إضافة نقطة انطلاق جديدة';
            document.getElementById('departureForm').reset();
            document.getElementById('departureId').value = '';
            document.getElementById('departureActive').checked = true;
            document.getElementById('departureModal').classList.remove('hidden');
        }

        function closeDepartureModal() {
            document.getElementById('departureModal').classList.add('hidden');
        }

        function editDeparturePoint(id) {
            fetch('actions/departure_points_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('departureModalTitle').textContent = 'تعديل نقطة الانطلاق';
                    document.getElementById('departureId').value = data.data.id;
                    document.getElementById('departureName').value = data.data.name;
                    document.getElementById('departureDescription').value = data.data.description || '';
                    document.getElementById('departureActive').checked = data.data.is_active == 1;
                    document.getElementById('departureModal').classList.remove('hidden');
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function deleteDeparturePoint(id, name) {
            showDeleteModal(
                'حذف نقطة الانطلاق',
                `هل أنت متأكد من حذف نقطة الانطلاق "${name}"؟\n\n⚠️ تحذير: سيتم حذف جميع الرحلات والحجوزات المرتبطة بهذه النقطة تلقائياً!\n\nهذا الإجراء لا يمكن التراجع عنه.`,
                function() {
                    fetch('actions/departure_points_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');
                        if (data.success) {
                            reloadWithActiveSection(1500, 'departure-points');
                        }
                    })
                    .catch(error => {
                        showMessage('حدث خطأ في الاتصال', 'error');
                    });
                },
                'fa-map-marker-alt'
            );
        }

        function saveDeparturePoint(event) {
            event.preventDefault();
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // إظهار حالة التحميل
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
            submitBtn.disabled = true;

            const formData = new FormData(event.target);
            const action = formData.get('id') ? 'update' : 'add';
            formData.append('action', action);

            fetch('actions/departure_points_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeDepartureModal();
                    reloadWithActiveSection(1500, 'departure-points');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            })
            .finally(() => {
                // إعادة الزر لحالته الطبيعية
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // Driver Functions
        function openAddDriverModal() {
            document.getElementById('driverModalTitle').textContent = 'إضافة سائق جديد';
            document.getElementById('driverForm').reset();
            document.getElementById('driverId').value = '';
            document.getElementById('driverActive').checked = true;
            document.getElementById('driverModal').classList.remove('hidden');
        }

        function closeDriverModal() {
            document.getElementById('driverModal').classList.add('hidden');
        }

        function editDriver(id) {
            fetch('actions/drivers_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('driverModalTitle').textContent = 'تعديل بيانات السائق';
                    document.getElementById('driverId').value = data.data.id;
                    document.getElementById('driverName').value = data.data.name;
                    document.getElementById('driverPhone').value = data.data.phone;
                    document.getElementById('driverLicense').value = data.data.license_number;
                    document.getElementById('driverExperience').value = data.data.experience_years || 0;
                    document.getElementById('driverStatus').value = data.data.status;
                    document.getElementById('driverGovernorate').value = data.data.governorate || '';
                    document.getElementById('driverCity').value = data.data.city || '';
                    document.getElementById('driverVehicleType').value = data.data.transport_type_id || '';
                    document.getElementById('driverVehicleCapacity').value = data.data.vehicle_capacity || '';
                    document.getElementById('driverActive').checked = data.data.is_active == 1;
                    document.getElementById('driverModal').classList.remove('hidden');
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function deleteDriver(id, name) {
            showDeleteModal(
                'حذف السائق',
                `هل أنت متأكد من حذف السائق "${name}"؟ سيتم حذف جميع المركبات والرحلات المرتبطة به أيضاً. لا يمكن التراجع عن هذا الإجراء.`,
                function() {
                    fetch('actions/drivers_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');
                        if (data.success) {
                            reloadWithActiveSection(1500, 'drivers');
                        }
                    })
                    .catch(error => {
                        showMessage('حدث خطأ في الاتصال', 'error');
                    });
                },
                'fa-user-times'
            );
        }

        function saveDriver(event) {
            event.preventDefault();
            // event.target might be the button or another element; ensure we get the form
            const form = (event.target && event.target.tagName === 'FORM')
                ? event.target
                : (event.target && event.target.closest ? event.target.closest('form') : document.getElementById('driverForm'));

            const submitBtn = form ? form.querySelector('button[type="submit"]') : document.querySelector('#driverForm button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
                submitBtn.disabled = true;
            }

            const formData = new FormData(form || document.getElementById('driverForm'));
            const action = formData.get('id') ? 'update' : 'add';
            formData.append('action', action);

            // التعامل مع checkbox is_active
            const isActiveCheckbox = document.getElementById('driverActive');
            if (isActiveCheckbox && isActiveCheckbox.checked) {
                formData.set('is_active', '1');
            } else {
                formData.set('is_active', '0');
            }

            // تشخيص البيانات المرسلة
            console.log('Action:', action);
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            fetch('actions/drivers_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response:', data); // للتشخيص
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeDriverModal();
                    reloadWithActiveSection(1500, 'drivers');
                }
            })
            .catch(error => {
                console.error('Error:', error); // للتشخيص
                showMessage('حدث خطأ في الاتصال: ' + error.message, 'error');
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // Trip Functions
        function openAddTripModal() {
            document.getElementById('tripModalTitle').textContent = 'إضافة رحلة جديدة';
            document.getElementById('tripForm').reset();
            document.getElementById('tripId').value = '';
            document.getElementById('tripActive').checked = true;

            // إعادة تعيين حقول التاريخ والوقت
            const departureDateInput = document.getElementById('tripDepartureDate');
            const departureTimeInput = document.getElementById('tripDepartureTime');
            const arrivalTimeInput = document.getElementById('tripArrivalTime');
            const fullDateTimeInput = document.getElementById('tripFullDateTime');

            departureDateInput.value = '';
            departureTimeInput.value = '';
            arrivalTimeInput.value = '';
            fullDateTimeInput.value = '';
            departureTimeInput.removeAttribute('max');
            arrivalTimeInput.removeAttribute('max');

            document.getElementById('tripModal').classList.remove('hidden');
        }

        function closeTripModal() {
            document.getElementById('tripModal').classList.add('hidden');
        }

        function editTrip(id) {
            fetch('actions/trips_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tripModalTitle').textContent = 'تعديل الرحلة';
                    document.getElementById('tripId').value = data.data.id;
                    document.getElementById('tripEvent').value = data.data.event_id;
                    document.getElementById('tripStartingPoint').value = data.data.starting_point_id;
                    document.getElementById('tripDriver').value = data.data.driver_id || '';

                    // تقسيم التاريخ والوقت
                    const departureDateTime = new Date(data.data.departure_time);
                    const departureDate = departureDateTime.toISOString().split('T')[0];
                    const departureTime = departureDateTime.toTimeString().split(' ')[0].substring(0, 5);

                    document.getElementById('tripDepartureDate').value = departureDate;
                    document.getElementById('tripDepartureTime').value = departureTime;
                    document.getElementById('tripArrivalTime').value = data.data.arrival_time;
                    document.getElementById('tripPrice').value = data.data.price;
                    document.getElementById('tripSeats').value = data.data.total_seats;
                    document.getElementById('tripDescription').value = data.data.description || '';
                    document.getElementById('tripActive').checked = data.data.is_active == 1;

                    // تحديث قيود الوقت بناءً على الفعالية المحددة
                    updateDepartureTimeFromEvent();

                    // تحديث الحقل المخفي
                    updateFullDateTime();

                    document.getElementById('tripModal').classList.remove('hidden');
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function deleteTrip(id, route) {
            showDeleteModal(
                'حذف الرحلة',
                `هل أنت متأكد من حذف الرحلة "${route}"؟ سيتم حذف جميع الحجوزات المرتبطة بها أيضاً. لا يمكن التراجع عن هذا الإجراء.`,
                function() {
                    fetch('actions/trips_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');
                        if (data.success) {
                            reloadWithActiveSection(1500, 'trips');
                        }
                    })
                    .catch(error => {
                        showMessage('حدث خطأ في الاتصال', 'error');
                    });
                },
                'fa-route'
            );
        }

        // دالة لتحديث عدد المقاعد تلقائياً من المركبة المختارة
        function updateSeatsFromVehicle() {
            const driverSelect = document.getElementById('tripDriver');
            const seatsInput = document.getElementById('tripSeats');

            if (driverSelect.value) {
                const selectedOption = driverSelect.options[driverSelect.selectedIndex];
                const capacity = selectedOption.getAttribute('data-capacity');

                if (capacity) {
                    seatsInput.value = capacity;
                } else {
                    seatsInput.value = '';
                }
            } else {
                seatsInput.value = '';
            }
        }

        // دالة لتحديث تاريخ المغادرة تلقائياً من الفعالية المختارة
        function updateDepartureTimeFromEvent() {
            const eventSelect = document.getElementById('tripEvent');
            const departureDateInput = document.getElementById('tripDepartureDate');
            const departureTimeInput = document.getElementById('tripDepartureTime');
            const arrivalTimeInput = document.getElementById('tripArrivalTime');

            if (eventSelect.value) {
                const selectedOption = eventSelect.options[eventSelect.selectedIndex];
                const eventDateTime = selectedOption.getAttribute('data-event-datetime');

                if (eventDateTime) {
                    // تحويل تاريخ الفعالية إلى كائن Date
                    const eventDate = new Date(eventDateTime);

                    // تحديد تاريخ المغادرة (نفس تاريخ الفعالية)
                    const year = eventDate.getFullYear();
                    const month = String(eventDate.getMonth() + 1).padStart(2, '0');
                    const day = String(eventDate.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;
                    departureDateInput.value = formattedDate;

                    // تحديد وقت المغادرة الافتراضي (قبل ساعة من الفعالية)
                    const defaultDepartureDate = new Date(eventDate.getTime() - (60 * 60 * 1000));
                    const defaultHours = String(defaultDepartureDate.getHours()).padStart(2, '0');
                    const defaultMinutes = String(defaultDepartureDate.getMinutes()).padStart(2, '0');
                    departureTimeInput.value = `${defaultHours}:${defaultMinutes}`;

                    // تحديد الحد الأقصى لوقت المغادرة (قبل 15 دقيقة من الفعالية)
                    const maxDepartureDate = new Date(eventDate.getTime() - (15 * 60 * 1000));
                    const maxDepartureTime = `${String(maxDepartureDate.getHours()).padStart(2, '0')}:${String(maxDepartureDate.getMinutes()).padStart(2, '0')}`;
                    departureTimeInput.max = maxDepartureTime;

                    // تحديد الحد الأقصى لوقت الوصول (قبل 15 دقيقة من الفعالية)
                    arrivalTimeInput.max = maxDepartureTime;

                    // مسح وقت الوصول المحدد مسبقاً
                    arrivalTimeInput.value = '';

                    // تحديث الحقل المخفي
                    updateFullDateTime();
                } else {
                    departureDateInput.value = '';
                    departureTimeInput.value = '';
                    arrivalTimeInput.value = '';
                    departureTimeInput.removeAttribute('max');
                    arrivalTimeInput.removeAttribute('max');
                }
            } else {
                departureDateInput.value = '';
                departureTimeInput.value = '';
                arrivalTimeInput.value = '';
                departureTimeInput.removeAttribute('max');
                arrivalTimeInput.removeAttribute('max');
            }
        }

        // دالة لتحديث الحقل المخفي بالتاريخ والوقت الكامل
        function updateFullDateTime() {
            const departureDateInput = document.getElementById('tripDepartureDate');
            const departureTimeInput = document.getElementById('tripDepartureTime');
            const fullDateTimeInput = document.getElementById('tripFullDateTime');

            if (departureDateInput.value && departureTimeInput.value) {
                fullDateTimeInput.value = `${departureDateInput.value} ${departureTimeInput.value}:00`;
            } else {
                fullDateTimeInput.value = '';
            }
        }

        // إضافة مستمعين للأحداث لتحديث الحقل المخفي
        document.addEventListener('DOMContentLoaded', function() {
            const departureTimeInput = document.getElementById('tripDepartureTime');
            if (departureTimeInput) {
                departureTimeInput.addEventListener('change', updateFullDateTime);
            }
        });

        // دالة للتحقق من صحة التواريخ والأوقات
        async function validateTripDateTime() {
            const departureDate = document.getElementById('tripDepartureDate').value;
            const departureTime = document.getElementById('tripDepartureTime').value;
            const arrivalTime = document.getElementById('tripArrivalTime').value;
            const eventId = document.getElementById('tripEvent').value;

            if (!departureDate) {
                return { valid: false, message: 'تاريخ المغادرة مطلوب' };
            }

            if (!departureTime) {
                return { valid: false, message: 'وقت المغادرة مطلوب' };
            }

            if (!arrivalTime) {
                return { valid: false, message: 'وقت الوصول مطلوب' };
            }

            if (!eventId) {
                return { valid: false, message: 'يجب اختيار الفعالية أولاً' };
            }

            try {
                // جلب تاريخ الفعالية
                const response = await fetch('actions/events_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=get_event_date&id=${eventId}`
                });
                const eventData = await response.json();

                if (!eventData.success) {
                    return { valid: false, message: 'خطأ في جلب بيانات الفعالية' };
                }

                const eventDate = new Date(eventData.date);
                const fullDepartureDateTime = new Date(`${departureDate}T${departureTime}`);

                // التحقق من أن الرحلة ليست في الماضي
                const now = new Date();
                if (fullDepartureDateTime <= now) {
                    return { valid: false, message: 'لا يمكن إنشاء رحلة في الماضي' };
                }

                // التحقق من أن وقت الوصول بعد وقت المغادرة
                if (arrivalTime <= departureTime) {
                    return { valid: false, message: 'وقت الوصول يجب أن يكون بعد وقت المغادرة' };
                }

                // التحقق من أن وقت المغادرة قبل بداية الفعالية بـ 15 دقيقة على الأقل
                const eventTimeOnly = eventDate.toTimeString().split(' ')[0].substring(0, 5); // HH:MM
                const eventMinutes = parseInt(eventTimeOnly.split(':')[0]) * 60 + parseInt(eventTimeOnly.split(':')[1]);
                const departureMinutes = parseInt(departureTime.split(':')[0]) * 60 + parseInt(departureTime.split(':')[1]);

                if (departureMinutes >= (eventMinutes - 15)) {
                    return { valid: false, message: 'وقت المغادرة يجب أن يكون قبل بداية الفعالية بـ 15 دقيقة على الأقل' };
                }

                // التحقق من أن وقت الوصول قبل بداية الفعالية بـ 15 دقيقة على الأقل
                const arrivalMinutes = parseInt(arrivalTime.split(':')[0]) * 60 + parseInt(arrivalTime.split(':')[1]);

                if (arrivalMinutes >= (eventMinutes - 15)) {
                    return { valid: false, message: 'وقت الوصول يجب أن يكون قبل بداية الفعالية بـ 15 دقيقة على الأقل' };
                }

                return { valid: true };
            } catch (error) {
                return { valid: false, message: 'خطأ في التحقق من البيانات' };
            }
        }

        async function saveTrip(event) {
            event.preventDefault();

            // تحديث الحقل المخفي قبل التحقق
            updateFullDateTime();

            // التحقق من صحة التواريخ والأوقات
            const validation = await validateTripDateTime();
            if (!validation.valid) {
                showMessage(validation.message, 'error');
                return;
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
            submitBtn.disabled = true;

            const formData = new FormData(event.target);
            const action = formData.get('id') ? 'update' : 'add';
            formData.append('action', action);

            // استخدام الحقل المخفي للتاريخ والوقت الكامل
            formData.set('departure_time', formData.get('full_departure_time'));

            fetch('actions/trips_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeTripModal();
                    reloadWithActiveSection(1500, 'trips');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

        // دالة تحديث المقاعد المتاحة لجميع الرحلات
        function updateAllSeats() {
            const btn = event.target;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري التحديث...';
            btn.disabled = true;

            fetch('actions/trips_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=update_all_seats'
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // إعادة تحميل الصفحة لإظهار التحديثات
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // Inactive Trips Functions
        let inactiveTripsVisible = false;

        function toggleInactiveTrips() {
            const section = document.getElementById('inactiveTripsSection');
            const btn = document.getElementById('toggleInactiveTripsBtn');

            if (!inactiveTripsVisible) {
                // إظهار الرحلات غير النشطة
                loadInactiveTrips();
                section.classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-eye ml-2"></i> إخفاء الرحلات غير النشطة';
                btn.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                btn.classList.add('bg-red-600', 'hover:bg-red-700');
                inactiveTripsVisible = true;
            } else {
                // إخفاء الرحلات غير النشطة
                section.classList.add('hidden');
                btn.innerHTML = '<i class="fas fa-eye-slash ml-2"></i> إظهار الرحلات غير النشطة';
                btn.classList.remove('bg-red-600', 'hover:bg-red-700');
                btn.classList.add('bg-gray-600', 'hover:bg-gray-700');
                inactiveTripsVisible = false;
            }
        }

        function loadInactiveTrips(page = 1) {
            fetch('actions/trips_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=get_inactive&page=${page}&limit=5`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayInactiveTrips(data.trips, data.pagination);
                } else {
                    showMessage('فشل في جلب الرحلات غير النشطة', 'error');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function displayInactiveTrips(trips, pagination = null) {
            const tbody = document.getElementById('inactiveTripsTableBody');
            const paginationContainer = document.getElementById('inactiveTripsSection').querySelector('.pagination-container');

            if (trips.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                            <p>لا توجد رحلات غير نشطة</p>
                        </td>
                    </tr>
                `;
                if (paginationContainer) paginationContainer.innerHTML = '';
                return;
            }

            tbody.innerHTML = trips.map(trip => `
                <tr class="hover:bg-gray-50 inactive-item">
                    <td class="px-6 py-4 whitespace-nowrap">TR-${String(trip.id).padStart(4, '0')}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${trip.starting_point_name || 'نقطة انطلاق'} إلى ${trip.event_title || 'الفعالية'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">${formatDateTimeJS(trip.departure_time)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${trip.arrival_time || 'غير محدد'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${formatPriceJS(trip.price)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${trip.available_seats}/${trip.total_seats}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-800">
                            غير نشط
                            <i class="fas fa-times-circle mr-1"></i>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button onclick="reactivateTrip(${trip.id})" class="text-green-600 hover:text-green-800 ml-3" title="إعادة تنشيط">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button onclick="editTrip(${trip.id})" class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteTrip(${trip.id}, '${trip.starting_point_name} إلى ${trip.event_title}')" class="text-red-600 hover:text-red-800" title="حذف">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            // إضافة أزرار التصفح إذا كانت متوفرة
            if (pagination && pagination.total_pages > 1) {
                if (!paginationContainer) {
                    // إنشاء حاوية التصفح إذا لم تكن موجودة
                    const table = document.getElementById('inactiveTripsSection').querySelector('table');
                    const newPaginationContainer = document.createElement('div');
                    newPaginationContainer.className = 'pagination-container px-6 py-4 bg-gray-50 border-t';
                    table.parentNode.appendChild(newPaginationContainer);
                }

                const container = document.getElementById('inactiveTripsSection').querySelector('.pagination-container');
                container.innerHTML = createInactiveTripspagination(pagination);
            } else if (paginationContainer) {
                paginationContainer.innerHTML = '';
            }
        }

        function createInactiveTripspagination(pagination) {
            let html = '<div class="flex justify-center items-center space-x-2 space-x-reverse">';

            // زر الصفحة السابقة
            if (pagination.current_page > 1) {
                html += `<button onclick="loadInactiveTrips(${pagination.current_page - 1})" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">السابق</button>`;
            }

            // أرقام الصفحات
            for (let i = 1; i <= pagination.total_pages; i++) {
                if (i === pagination.current_page) {
                    html += `<button class="px-3 py-1 bg-primary-600 text-white rounded">${i}</button>`;
                } else {
                    html += `<button onclick="loadInactiveTrips(${i})" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">${i}</button>`;
                }
            }

            // زر الصفحة التالية
            if (pagination.current_page < pagination.total_pages) {
                html += `<button onclick="loadInactiveTrips(${pagination.current_page + 1})" class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">التالي</button>`;
            }

            html += '</div>';
            html += `<p class="text-center text-sm text-gray-500 mt-2">صفحة ${pagination.current_page} من ${pagination.total_pages} (${pagination.total_items} رحلة غير نشطة)</p>`;

            return html;
        }

        function reactivateTrip(id) {
            fetch('actions/trips_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_status&id=${id}&new_status=true`
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    reloadWithActiveSection(1000, 'trips');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function formatDateTimeJS(datetime) {
            if (!datetime) return 'غير محدد';
            const date = new Date(datetime);
            return date.getFullYear() + '-' +
                   String(date.getMonth() + 1).padStart(2, '0') + '-' +
                   String(date.getDate()).padStart(2, '0') + ' ' +
                   String(date.getHours()).padStart(2, '0') + ':' +
                   String(date.getMinutes()).padStart(2, '0');
        }

        function formatPriceJS(amount) {
            return parseFloat(amount).toFixed(2) + ' ₪';
        }



        // Status Toggle Functions
        function toggleDeparturePointStatus(id, newStatus) {
            const action = newStatus === 'true' ? 'تنشيط' : 'إلغاء تنشيط';
            const statusText = newStatus === 'true' ? 'نشطة' : 'غير نشطة';
            const tripsAction = newStatus === 'true' ? 'تنشيط' : 'إلغاء تنشيط';

            const confirmMessage = `هل أنت متأكد من ${action} نقطة الانطلاق؟\n\n` +
                                 `⚠️ تنبيه: سيتم ${tripsAction} جميع الرحلات المرتبطة بهذه النقطة تلقائياً!\n\n` +
                                 `نقطة الانطلاق ستصبح: ${statusText}\n` +
                                 `الرحلات المرتبطة ستصبح: ${statusText}`;

            if (confirm(confirmMessage)) {
                fetch('actions/departure_points_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=toggle_status&id=${id}&new_status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    showMessage(data.message, data.success ? 'success' : 'error');
                    if (data.success) {
                        reloadWithActiveSection(1000, 'departure-points');
                    }
                })
                .catch(error => {
                    showMessage('حدث خطأ في الاتصال', 'error');
                });
            }
        }

        function toggleTripStatus(id, newStatus) {
            fetch('actions/trips_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_status&id=${id}&new_status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    reloadWithActiveSection(1000, 'trips');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function toggleDriverStatus(id, newStatus) {
            fetch('actions/drivers_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_status&id=${id}&new_status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    reloadWithActiveSection(1000, 'drivers');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        function toggleDriverActiveStatus(id, newActiveStatus) {
            fetch('actions/drivers_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_active_status&id=${id}&new_active_status=${newActiveStatus}`
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    reloadWithActiveSection(1000, 'drivers');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }

        // Profile Modal Functions
        function openProfileModal() {
            const modal = document.getElementById('profileModal');
            const modalContent = document.getElementById('profileModalContent');

            modal.classList.remove('hidden');

            // تأثير الظهور
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);

            // إغلاق قائمة الإدمن
            toggleAdminMenu();
        }

        function closeProfileModal() {
            const modal = document.getElementById('profileModal');
            const modalContent = document.getElementById('profileModalContent');

            // تأثير الاختفاء
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
                // إعادة تعيين معاينة الصورة
                document.getElementById('imagePreview').classList.add('hidden');
                document.getElementById('uploadText').classList.remove('hidden');
                document.getElementById('currentProfileImage').style.opacity = '1';
                document.getElementById('profileImage').value = '';
            }, 300);
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                // التحقق من نوع الملف
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showMessage('يرجى اختيار صورة صحيحة (JPG, PNG, GIF)', 'error');
                    return;
                }

                // التحقق من حجم الملف (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showMessage('حجم الصورة يجب أن يكون أقل من 5 ميجابايت', 'error');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    // إخفاء الصورة الحالية وإظهار المعاينة
                    document.getElementById('currentProfileImage').style.opacity = '0.5';
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('uploadText').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }

        function saveProfile() {
            const nameInput = document.getElementById('adminName');
            const emailInput = document.getElementById('adminEmail');
            const departmentInput = document.getElementById('adminDepartment');

            // التحقق من صحة البيانات
            if (!nameInput.value.trim()) {
                showMessage('الاسم مطلوب', 'error');
                nameInput.focus();
                return;
            }

            if (!emailInput.value.trim() || !isValidEmail(emailInput.value)) {
                showMessage('البريد الإلكتروني غير صحيح', 'error');
                emailInput.focus();
                return;
            }

            // إظهار مؤشر التحميل
            const saveButton = document.querySelector('#profileModal button[onclick="saveProfile()"]');
            const originalText = saveButton.innerHTML;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>جاري الحفظ...';
            saveButton.disabled = true;

            const formData = new FormData();
            formData.append('action', 'update_profile');
            formData.append('name', nameInput.value.trim());
            formData.append('email', emailInput.value.trim());
            formData.append('department', departmentInput.value.trim());

            fetch('actions/profile_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('تم تحديث الملف الشخصي بنجاح', 'success');
                    closeProfileModal();
                    // تحديث الصفحة لإظهار التغييرات
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(data.message || 'حدث خطأ في التحديث', 'error');
                }
            })
            .catch(error => {
                showMessage('حدث خطأ في الاتصال', 'error');
            })
            .finally(() => {
                // إعادة تعيين زر الحفظ
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            });
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // إغلاق النافذة عند النقر خارجها
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('profileModal');
            const modalContent = document.getElementById('profileModalContent');

            if (modal && !modal.classList.contains('hidden') &&
                event.target === modal && !modalContent.contains(event.target)) {
                closeProfileModal();
            }
        });

        // إضافة مركبة لسائق موجود
        function addVehicleToDriver(driverId, driverName) {
            const transportTypes = <?php echo json_encode($transport_types); ?>;

            let typeOptions = '';
            transportTypes.forEach(type => {
                typeOptions += `<option value="${type.id}">${type.name}</option>`;
            });

            const vehicleForm = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع المركبة *</label>
                        <select id="vehicleType" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">اختر نوع المركبة</option>
                            ${typeOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم اللوحة *</label>
                        <input type="text" id="vehiclePlate" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">موديل المركبة *</label>
                        <input type="text" id="vehicleModel" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">سنة الصنع</label>
                            <input type="number" id="vehicleYear" min="1990" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">السعة *</label>
                            <input type="number" id="vehicleCapacity" required min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                        <input type="text" id="vehicleColor" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
            `;

            // إنشاء modal مؤقت
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">إضافة مركبة للسائق: ${driverName}</h3>
                    ${vehicleForm}
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                        <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button onclick="submitVehicle(${driverId})" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            حفظ المركبة
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // إرسال بيانات المركبة
        function submitVehicle(driverId) {
            const formData = new FormData();
            formData.append('action', 'add_vehicle');
            formData.append('driver_id', driverId);
            formData.append('transport_type_id', document.getElementById('vehicleType').value);
            formData.append('plate_number', document.getElementById('vehiclePlate').value);
            formData.append('model', document.getElementById('vehicleModel').value);
            formData.append('year', document.getElementById('vehicleYear').value);
            formData.append('capacity', document.getElementById('vehicleCapacity').value);
            formData.append('color', document.getElementById('vehicleColor').value);

            fetch('actions/drivers_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.querySelector('.fixed').remove();
                    reloadWithActiveSection(1500, 'drivers');
                } else {
                    showMessage('خطأ: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ في الاتصال', 'error');
            });
        }
    </script>

    <!-- Profile Edit Modal -->
    <div id="profileModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="profileModalContent">
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">تعديل الملف الشخصي</h3>
                <button onclick="closeProfileModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="p-6">
                <!-- الاسم -->
                <div class="mb-4">
                    <label for="adminName" class="block text-sm font-medium text-gray-700 mb-2">الاسم *</label>
                    <input type="text" id="adminName" value="<?php echo htmlspecialchars($admin_profile['display_name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="mb-4">
                    <label for="adminEmail" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                    <input type="email" id="adminEmail" value="<?php echo htmlspecialchars($admin_profile['email']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" required>
                </div>

                <!-- القسم/الدائرة -->
                <div class="mb-6">
                    <label for="adminDepartment" class="block text-sm font-medium text-gray-700 mb-2">القسم/الدائرة</label>
                    <input type="text" id="adminDepartment" value="<?php echo htmlspecialchars($admin_profile['department'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="مثال: إدارة النقل">
                </div>

                <!-- أزرار الحفظ والإلغاء -->
                <div class="flex justify-end space-x-3 space-x-reverse">
                    <button onclick="closeProfileModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                        إلغاء
                    </button>
                    <button onclick="saveProfile()" class="px-4 py-2 bg-primary-500 text-white rounded-md hover:bg-primary-600 transition">
                        حفظ التغييرات
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
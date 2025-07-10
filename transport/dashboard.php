<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_functions.php';

$auth = new Auth();

// التحقق من تسجيل الدخول ودور المستخدم
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

// التحقق من صلاحيات إدارة المواصلات
require_admin_permission('transport');

// جلب الإحصائيات من قاعدة البيانات
$db = new Database();

// إجمالي الإيرادات
$db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE payment_status = 'confirmed'");
$total_revenue = $db->single()['total_revenue'] ?? 0;

// إجمالي الرحلات
$db->query("SELECT COUNT(*) as total_trips FROM transport_trips WHERE is_active = 1");
$total_trips = $db->single()['total_trips'] ?? 0;

// السائقين النشطين
$db->query("SELECT COUNT(*) as active_drivers FROM transport_drivers WHERE is_active = 1");
$active_drivers = $db->single()['active_drivers'] ?? 0;

// الحجوزات الجديدة (آخر 30 يوم)
$db->query("SELECT COUNT(*) as new_bookings FROM transport_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$new_bookings = $db->single()['new_bookings'] ?? 0;

// نقاط الانطلاق
$starting_points = get_starting_points();

// السائقين مع مركباتهم
$db->query("
    SELECT
        d.*,
        v.plate_number,
        v.model,
        v.year,
        v.color,
        v.capacity,
        tt.name as transport_type_name
    FROM transport_drivers d
    LEFT JOIN transport_vehicles v ON d.id = v.driver_id
    LEFT JOIN transport_types tt ON v.transport_type_id = tt.id
    ORDER BY d.created_at DESC
");
$drivers = $db->resultSet();

// الرحلات مع التفاصيل
$db->query("
    SELECT
        t.*,
        sp.name as starting_point_name,
        tt.name as transport_type_name,
        e.title as event_title,
        v.plate_number,
        d.name as driver_name
    FROM transport_trips t
    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
    JOIN transport_types tt ON t.transport_type_id = tt.id
    JOIN events e ON t.event_id = e.id
    LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
    LEFT JOIN transport_drivers d ON v.driver_id = d.id
    ORDER BY t.created_at DESC
    LIMIT 20
");
$trips = $db->resultSet();

// الحجوزات الأخيرة
$db->query("
    SELECT
        tb.*,
        t.departure_time,
        sp.name as starting_point_name,
        e.title as event_title,
        d.name as driver_name
    FROM transport_bookings tb
    JOIN transport_trips t ON tb.trip_id = t.id
    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
    JOIN events e ON tb.event_id = e.id
    LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
    LEFT JOIN transport_drivers d ON v.driver_id = d.id
    ORDER BY tb.created_at DESC
    LIMIT 20
");
$bookings = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
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
        body {
            font-family: 'Tajawal', sans-serif;
        }

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
                        <i class="fas fa-bell text-xl cursor-pointer"></i>
                        <?php if($new_bookings > 0): ?>
                        <span class="absolute -top-1 -left-1 bg-secondary-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?php echo $new_bookings; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <span class="font-medium"><?php echo $_SESSION['user_name']; ?></span>
                    </div>
                    <a href="../index.php" class="text-primary-200 hover:text-white transition">
                        <i class="fas fa-home ml-1"></i>
                        العودة للموقع
                    </a>
                </div>
            </div>
        </header>

        <!-- Horizontal Navigation -->
        <nav class="bg-gradient-to-r from-primary-600 to-primary-800 text-white shadow-md">
            <div class="container mx-auto overflow-x-auto">
                <div class="flex space-x-1 space-x-reverse py-2 px-1">
                    <a href="dashboard.php" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (!isset($_GET['section']) || $_GET['section'] == 'overview') ? 'active' : ''; ?>">
                        <i class="fas fa-home ml-2"></i> نظرة عامة
                    </a>
                    <a href="dashboard.php?section=revenue" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'revenue') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line ml-2"></i> الإيرادات
                    </a>
                    <a href="dashboard.php?section=departure-points" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'departure-points') ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt ml-2"></i> نقاط الانطلاق
                    </a>
                    <a href="dashboard.php?section=trips" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'trips') ? 'active' : ''; ?>">
                        <i class="fas fa-route ml-2"></i> الرحلات
                    </a>
                    <a href="dashboard.php?section=drivers" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'drivers') ? 'active' : ''; ?>">
                        <i class="fas fa-id-card-alt ml-2"></i> السائقين
                    </a>
                    <a href="dashboard.php?section=bookings" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'bookings') ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check ml-2"></i> الحجوزات
                    </a>
                    <a href="dashboard.php?section=analytics" class="nav-item px-4 py-3 rounded-lg hover:bg-primary-500 transition <?php echo (isset($_GET['section']) && $_GET['section'] == 'analytics') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie ml-2"></i> التحليلات
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto p-4">
            <?php
            // تحديد القسم المطلوب عرضه
            $current_section = $_GET['section'] ?? 'overview';
            ?>

            <!-- Overview Section -->
            <section id="overview" class="dashboard-section <?php echo ($current_section == 'overview') ? 'active' : ''; ?>"<?php echo ($current_section != 'overview') ? ' style="display: none;"' : ''; ?>>
                <h2 class="text-2xl font-bold text-primary-900 mb-6">نظرة عامة على لوحة التحكم</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">إجمالي الإيرادات</p>
                                <h3 class="text-2xl font-bold text-primary-700"><?php echo number_format($total_revenue, 2); ?> ₪</h3>
                                <p class="text-green-500 text-sm mt-1"><i class="fas fa-arrow-up ml-1"></i> من الحجوزات المؤكدة</p>
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
                                <p class="text-blue-500 text-sm mt-1"><i class="fas fa-route ml-1"></i> رحلة نشطة</p>
                            </div>
                            <div class="bg-secondary-100 p-3 rounded-full">
                                <i class="fas fa-route text-secondary-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6 card-hover transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500">السائقين النشطين</p>
                                <h3 class="text-2xl font-bold text-amber-700"><?php echo $active_drivers; ?></h3>
                                <p class="text-amber-500 text-sm mt-1"><i class="fas fa-user-check ml-1"></i> سائق متاح</p>
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
                                <p class="text-green-500 text-sm mt-1"><i class="fas fa-calendar-plus ml-1"></i> آخر 30 يوم</p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-full">
                                <i class="fas fa-calendar-check text-emerald-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">الأنشطة الأخيرة</h3>
                        <button onclick="showSection('bookings')" class="text-primary-600 hover:text-primary-800">عرض الكل</button>
                    </div>
                    <div class="space-y-4">
                        <?php if (!empty($bookings)): ?>
                            <?php foreach (array_slice($bookings, 0, 5) as $booking): ?>
                            <div class="flex items-start space-x-4 space-x-reverse">
                                <div class="bg-primary-100 p-2 rounded-full">
                                    <i class="fas fa-calendar-check text-primary-600"></i>
                                </div>
                                <div class="flex-grow">
                                    <p class="font-medium">حجز جديد #<?php echo $booking['booking_code']; ?></p>
                                    <p class="text-gray-500 text-sm"><?php echo $booking['passenger_name']; ?> حجز رحلة إلى <?php echo $booking['event_title']; ?></p>
                                    <p class="text-gray-400 text-xs">من <?php echo $booking['starting_point_name']; ?> - <?php echo $booking['passengers_count']; ?> راكب</p>
                                </div>
                                <span class="text-gray-400 text-sm"><?php echo time_elapsed_string($booking['created_at']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p>لا توجد أنشطة حديثة</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Revenue Overview</h3>
                        <div class="h-64">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Trip Distribution</h3>
                        <div class="h-64">
                            <canvas id="tripChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Revenue Section -->
            <section id="revenue" class="dashboard-section <?php echo ($current_section == 'revenue') ? 'active' : ''; ?>"<?php echo ($current_section != 'revenue') ? ' style="display: none;"' : ''; ?>>
                <h2 class="text-2xl font-bold text-primary-900 mb-6">Revenue Management</h2>
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Monthly Revenue</h3>
                        <div class="flex space-x-2">
                            <button class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">Export</button>
                            <select class="border rounded-lg px-3 py-2">
                                <option>Last 6 Months</option>
                                <option>This Year</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="h-96">
                        <canvas id="detailedRevenueChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Revenue by Trip Type</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">Standard</td>
                                    <td class="px-6 py-4 whitespace-nowrap">124</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$12,450</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$100.40</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">Premium</td>
                                    <td class="px-6 py-4 whitespace-nowrap">56</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$8,750</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$156.25</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">Luxury</td>
                                    <td class="px-6 py-4 whitespace-nowrap">7</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$3,580</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$511.43</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Departure Points Section -->
            <section id="departure-points" class="dashboard-section <?php echo ($current_section == 'departure-points') ? 'active' : ''; ?>"<?php echo ($current_section != 'departure-points') ? ' style="display: none;"' : ''; ?>>
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
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المنطقة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الوصف</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($starting_points as $point): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">SP-<?php echo str_pad($point['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $point['name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo get_region_name($point['region']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?php echo $point['description']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($point['is_active']): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">نشط</span>
                                        <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">غير نشط</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-primary-600 hover:text-primary-800 ml-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Trips Section -->
            <section id="trips" class="dashboard-section <?php echo ($current_section == 'trips') ? 'active' : ''; ?>"<?php echo ($current_section != 'trips') ? ' style="display: none;"' : ''; ?>>
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-primary-900">إدارة الرحلات</h2>
                    <button onclick="openAddTripModal()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center">
                        <i class="fas fa-plus ml-2"></i> إضافة رحلة جديدة
                    </button>
                </div>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary-600 text-white">
                                <tr>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">معرف الرحلة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المسار</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">وقت المغادرة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">السعر</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">المقاعد</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">السائق</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">الحالة</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($trips)): ?>
                                    <?php foreach ($trips as $trip): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">TR-<?php echo str_pad($trip['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium"><?php echo $trip['starting_point_name']; ?> → <?php echo $trip['event_title']; ?></div>
                                                <div class="text-gray-500 text-xs"><?php echo $trip['transport_type_name']; ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('Y-m-d H:i', strtotime($trip['departure_time'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            <?php echo number_format($trip['price'], 2); ?> ₪
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php
                                            $booked_seats = get_booked_seats_count($trip['id']);
                                            $total_seats = $trip['available_seats'];
                                            echo $booked_seats . '/' . $total_seats;
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $trip['driver_name'] ?? 'غير محدد'; ?>
                                            <?php if ($trip['plate_number']): ?>
                                                <div class="text-xs text-gray-400"><?php echo $trip['plate_number']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($trip['is_active']): ?>
                                                <?php if ($booked_seats >= $total_seats): ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">مكتملة</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">نشطة</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">غير نشطة</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-blue-600 hover:text-blue-800 ml-3" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800" title="حذف">
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
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
                            <div class="p-6">
                                <div class="flex items-center space-x-4 space-x-reverse mb-4">
                                    <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center">
                                        <i class="fas fa-user text-primary-600 text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold"><?php echo $driver['name']; ?></h3>
                                        <p class="text-gray-500">DR-<?php echo str_pad($driver['id'], 4, '0', STR_PAD_LEFT); ?></p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <i class="fas fa-phone-alt text-primary-600"></i>
                                        <span class="text-sm"><?php echo $driver['phone']; ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <i class="fas fa-envelope text-primary-600"></i>
                                        <span class="text-sm"><?php echo $driver['email']; ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <i class="fas fa-id-card text-primary-600"></i>
                                        <span class="text-sm">رخصة: <?php echo $driver['license_number']; ?></span>
                                    </div>
                                    <?php if ($driver['plate_number']): ?>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <i class="fas fa-car text-primary-600"></i>
                                        <span class="text-sm">
                                            <?php echo $driver['model']; ?> <?php echo $driver['year']; ?> (<?php echo $driver['plate_number']; ?>)
                                            <?php if ($driver['transport_type_name']): ?>
                                                <div class="text-xs text-gray-500"><?php echo $driver['transport_type_name']; ?> - <?php echo $driver['capacity']; ?> مقعد</div>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
                                    <?php if ($driver['is_active']): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">نشط</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">غير نشط</span>
                                    <?php endif; ?>
                                    <div class="space-x-2 space-x-reverse">
                                        <button class="text-primary-600 hover:text-primary-800" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="text-blue-600 hover:text-blue-800" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-800" title="حذف">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">لا يوجد سائقين مسجلين</p>
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
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($bookings)): ?>
                                    <?php foreach ($bookings as $booking): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $booking['booking_code']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium"><?php echo $booking['passenger_name']; ?></div>
                                                <div class="text-gray-500 text-xs"><?php echo $booking['passenger_phone']; ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium"><?php echo $booking['starting_point_name']; ?> → <?php echo $booking['event_title']; ?></div>
                                                <div class="text-gray-500 text-xs"><?php echo date('Y-m-d H:i', strtotime($booking['departure_time'])); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('Y-m-d', strtotime($booking['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $booking['passengers_count']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            <?php echo number_format($booking['total_amount'], 2); ?> ₪
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_colors = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'confirmed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                'completed' => 'bg-blue-100 text-blue-800'
                                            ];
                                            $status_names = [
                                                'pending' => 'في الانتظار',
                                                'confirmed' => 'مؤكد',
                                                'cancelled' => 'ملغي',
                                                'completed' => 'مكتمل'
                                            ];
                                            $color_class = $status_colors[$booking['payment_status']] ?? 'bg-gray-100 text-gray-800';
                                            $status_name = $status_names[$booking['payment_status']] ?? $booking['payment_status'];
                                            ?>
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $color_class; ?>"><?php echo $status_name; ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button class="text-primary-600 hover:text-primary-800 ml-3" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-blue-600 hover:text-blue-800 ml-3" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 ml-3" title="تأكيد">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800" title="إلغاء">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                                            <p>لا توجد حجوزات</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                عرض <span class="font-medium"><?php echo min(count($bookings), 20); ?></span> من <span class="font-medium"><?php echo count($bookings); ?></span> حجز
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
                </div>
            </section>

            <!-- Revenue Section -->
            <section id="revenue" class="dashboard-section">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">تحليل الإيرادات</h2>

                <!-- Revenue Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <?php
                    // إيرادات هذا الشهر
                    $db->query("SELECT SUM(total_amount) as monthly_revenue FROM transport_bookings WHERE payment_status = 'confirmed' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
                    $monthly_revenue = $db->single()['monthly_revenue'] ?? 0;

                    // إيرادات الشهر الماضي
                    $db->query("SELECT SUM(total_amount) as last_month_revenue FROM transport_bookings WHERE payment_status = 'confirmed' AND MONTH(created_at) = MONTH(NOW()) - 1 AND YEAR(created_at) = YEAR(NOW())");
                    $last_month_revenue = $db->single()['last_month_revenue'] ?? 0;

                    // متوسط سعر الرحلة
                    $db->query("SELECT AVG(total_amount) as avg_trip_price FROM transport_bookings WHERE payment_status = 'confirmed'");
                    $avg_trip_price = $db->single()['avg_trip_price'] ?? 0;
                    ?>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">إيرادات هذا الشهر</p>
                                <h3 class="text-2xl font-bold text-green-600"><?php echo number_format($monthly_revenue, 2); ?> ₪</h3>
                                <?php
                                $growth = $last_month_revenue > 0 ? (($monthly_revenue - $last_month_revenue) / $last_month_revenue) * 100 : 0;
                                ?>
                                <p class="text-sm <?php echo $growth >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                    <i class="fas fa-arrow-<?php echo $growth >= 0 ? 'up' : 'down'; ?> ml-1"></i>
                                    <?php echo abs(round($growth, 1)); ?>% من الشهر الماضي
                                </p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="fas fa-chart-line text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">إجمالي الإيرادات</p>
                                <h3 class="text-2xl font-bold text-blue-600"><?php echo number_format($total_revenue, 2); ?> ₪</h3>
                                <p class="text-sm text-blue-500">
                                    <i class="fas fa-coins ml-1"></i>
                                    من جميع الحجوزات المؤكدة
                                </p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-shekel-sign text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">متوسط سعر الرحلة</p>
                                <h3 class="text-2xl font-bold text-purple-600"><?php echo number_format($avg_trip_price, 2); ?> ₪</h3>
                                <p class="text-sm text-purple-500">
                                    <i class="fas fa-calculator ml-1"></i>
                                    لكل حجز
                                </p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-calculator text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue by Route -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">الإيرادات حسب المسار</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-right py-3 px-4 font-medium text-gray-700">المسار</th>
                                    <th class="text-right py-3 px-4 font-medium text-gray-700">عدد الحجوزات</th>
                                    <th class="text-right py-3 px-4 font-medium text-gray-700">إجمالي الإيرادات</th>
                                    <th class="text-right py-3 px-4 font-medium text-gray-700">متوسط السعر</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $db->query("
                                    SELECT
                                        CONCAT(sp.name, ' → ', e.title) as route_name,
                                        COUNT(tb.id) as booking_count,
                                        SUM(tb.total_amount) as total_revenue,
                                        AVG(tb.total_amount) as avg_price
                                    FROM transport_bookings tb
                                    JOIN transport_trips t ON tb.trip_id = t.id
                                    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
                                    JOIN events e ON tb.event_id = e.id
                                    WHERE tb.payment_status = 'confirmed'
                                    GROUP BY t.starting_point_id, tb.event_id
                                    ORDER BY total_revenue DESC
                                ");
                                $revenue_by_route = $db->resultSet();
                                ?>
                                <?php if (!empty($revenue_by_route)): ?>
                                    <?php foreach ($revenue_by_route as $route): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-4"><?php echo $route['route_name']; ?></td>
                                        <td class="py-3 px-4"><?php echo $route['booking_count']; ?></td>
                                        <td class="py-3 px-4 font-medium text-green-600"><?php echo number_format($route['total_revenue'], 2); ?> ₪</td>
                                        <td class="py-3 px-4"><?php echo number_format($route['avg_price'], 2); ?> ₪</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-gray-500">لا توجد بيانات إيرادات</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Analytics Section -->
            <section id="analytics" class="dashboard-section">
                <h2 class="text-2xl font-bold text-primary-900 mb-6">لوحة التحليلات</h2>

                <!-- Analytics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <?php
                    // معدل الإشغال
                    $db->query("
                        SELECT
                            AVG((booked_seats / total_seats) * 100) as occupancy_rate
                        FROM (
                            SELECT
                                t.available_seats as total_seats,
                                COALESCE(SUM(tb.passengers_count), 0) as booked_seats
                            FROM transport_trips t
                            LEFT JOIN transport_bookings tb ON t.id = tb.trip_id AND tb.payment_status != 'cancelled'
                            WHERE t.is_active = 1
                            GROUP BY t.id
                        ) as trip_stats
                    ");
                    $occupancy_rate = $db->single()['occupancy_rate'] ?? 0;

                    // معدل الإلغاء
                    $db->query("
                        SELECT
                            (COUNT(CASE WHEN payment_status = 'cancelled' THEN 1 END) / COUNT(*)) * 100 as cancellation_rate
                        FROM transport_bookings
                    ");
                    $cancellation_rate = $db->single()['cancellation_rate'] ?? 0;

                    // أكثر نقطة انطلاق شعبية
                    $db->query("
                        SELECT sp.name, COUNT(tb.id) as booking_count
                        FROM transport_bookings tb
                        JOIN transport_trips t ON tb.trip_id = t.id
                        JOIN transport_starting_points sp ON t.starting_point_id = sp.id
                        WHERE tb.payment_status = 'confirmed'
                        GROUP BY sp.id
                        ORDER BY booking_count DESC
                        LIMIT 1
                    ");
                    $popular_point = $db->single();

                    // متوسط عدد الركاب لكل رحلة
                    $db->query("SELECT AVG(passengers_count) as avg_passengers FROM transport_bookings WHERE payment_status = 'confirmed'");
                    $avg_passengers = $db->single()['avg_passengers'] ?? 0;
                    ?>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-center">
                            <div class="bg-blue-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-percentage text-blue-600 text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-blue-600"><?php echo round($occupancy_rate, 1); ?>%</h3>
                            <p class="text-gray-500 text-sm">معدل الإشغال</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-center">
                            <div class="bg-red-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-red-600"><?php echo round($cancellation_rate, 1); ?>%</h3>
                            <p class="text-gray-500 text-sm">معدل الإلغاء</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-center">
                            <div class="bg-green-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-green-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-green-600"><?php echo $popular_point['name'] ?? 'غير محدد'; ?></h3>
                            <p class="text-gray-500 text-sm">أشهر نقطة انطلاق</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="text-center">
                            <div class="bg-purple-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-users text-purple-600 text-xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-purple-600"><?php echo round($avg_passengers, 1); ?></h3>
                            <p class="text-gray-500 text-sm">متوسط الركاب/رحلة</p>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">اتجاهات الحجوزات</h3>
                        <div class="space-y-4">
                            <?php
                            // الحجوزات حسب الشهر (آخر 6 أشهر)
                            $db->query("
                                SELECT
                                    DATE_FORMAT(created_at, '%Y-%m') as month,
                                    COUNT(*) as booking_count,
                                    SUM(total_amount) as revenue
                                FROM transport_bookings
                                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                AND payment_status = 'confirmed'
                                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                                ORDER BY month DESC
                                LIMIT 6
                            ");
                            $monthly_stats = array_reverse($db->resultSet());
                            ?>
                            <?php foreach ($monthly_stats as $stat): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium"><?php echo date('F Y', strtotime($stat['month'] . '-01')); ?></span>
                                <div class="text-left">
                                    <div class="text-sm text-gray-600"><?php echo $stat['booking_count']; ?> حجز</div>
                                    <div class="text-sm font-medium text-green-600"><?php echo number_format($stat['revenue'], 0); ?> ₪</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">أداء نقاط الانطلاق</h3>
                        <div class="space-y-4">
                            <?php
                            $db->query("
                                SELECT
                                    sp.name,
                                    COUNT(tb.id) as booking_count,
                                    SUM(tb.total_amount) as revenue
                                FROM transport_starting_points sp
                                LEFT JOIN transport_trips t ON sp.id = t.starting_point_id
                                LEFT JOIN transport_bookings tb ON t.id = tb.trip_id AND tb.payment_status = 'confirmed'
                                GROUP BY sp.id
                                ORDER BY booking_count DESC
                                LIMIT 5
                            ");
                            $point_stats = $db->resultSet();
                            $max_bookings = !empty($point_stats) ? $point_stats[0]['booking_count'] : 1;
                            ?>
                            <?php foreach ($point_stats as $point): ?>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-medium"><?php echo $point['name']; ?></span>
                                    <span class="text-sm text-gray-600"><?php echo $point['booking_count']; ?> حجز</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: <?php echo ($point['booking_count'] / $max_bookings) * 100; ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">توزيع حالات الحجوزات</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <?php
                        $db->query("
                            SELECT
                                payment_status,
                                COUNT(*) as count,
                                (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM transport_bookings)) as percentage
                            FROM transport_bookings
                            GROUP BY payment_status
                        ");
                        $status_stats = $db->resultSet();

                        $status_config = [
                            'pending' => ['name' => 'في الانتظار', 'color' => 'yellow', 'icon' => 'clock'],
                            'confirmed' => ['name' => 'مؤكد', 'color' => 'green', 'icon' => 'check-circle'],
                            'cancelled' => ['name' => 'ملغي', 'color' => 'red', 'icon' => 'times-circle'],
                            'completed' => ['name' => 'مكتمل', 'color' => 'blue', 'icon' => 'flag-checkered']
                        ];
                        ?>
                        <?php foreach ($status_stats as $status): ?>
                        <?php
                        $config = $status_config[$status['payment_status']] ?? ['name' => $status['payment_status'], 'color' => 'gray', 'icon' => 'question'];
                        ?>
                        <div class="text-center p-4 border border-gray-200 rounded-lg">
                            <div class="bg-<?php echo $config['color']; ?>-100 p-3 rounded-full w-16 h-16 mx-auto mb-3 flex items-center justify-center">
                                <i class="fas fa-<?php echo $config['icon']; ?> text-<?php echo $config['color']; ?>-600 text-xl"></i>
                            </div>
                            <h4 class="text-2xl font-bold text-<?php echo $config['color']; ?>-600"><?php echo $status['count']; ?></h4>
                            <p class="text-gray-600 text-sm"><?php echo $config['name']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo round($status['percentage'], 1); ?>%</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-100 border-t border-gray-200 py-4">
            <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
                <p>© <?php echo date('Y'); ?> لوحة تحكم المواصلات. جميع الحقوق محفوظة.</p>
            </div>
        </footer>
    </div>

    <!-- Add Departure Point Modal -->
    <div id="addDepartureModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Add New Departure Point</h3>
                    <button onclick="closeAddDepartureModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>Active</option>
                                <option>Maintenance</option>
                                <option>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeAddDepartureModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Trip Modal -->
    <div id="addTripModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Add New Trip</h3>
                    <button onclick="closeAddTripModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>New York to Boston</option>
                                <option>Los Angeles to San Francisco</option>
                                <option>Chicago to Detroit</option>
                                <option>Miami to Orlando</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departure Date</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Departure Time</label>
                                <input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Seats</label>
                            <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>John Smith</option>
                                <option>Sarah Johnson</option>
                                <option>Michael Brown</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeAddTripModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div id="addDriverModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Add New Driver</h3>
                    <button onclick="closeAddDriverModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver License</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Vehicle</label>
                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option>Ford Transit (ABC123)</option>
                                <option>Mercedes Sprinter (XYZ789)</option>
                                <option>Chevrolet Express (DEF456)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeAddDriverModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Navigation functionality
        function showSection(sectionId) {
            console.log('Switching to section:', sectionId); // للتشخيص

            // Hide all sections
            document.querySelectorAll('.dashboard-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
            } else {
                console.error('Section not found:', sectionId);
                return;
            }

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

            // Initialize charts when section is shown
            setTimeout(() => {
                if (sectionId === 'overview') {
                    initOverviewCharts();
                } else if (sectionId === 'revenue') {
                    initRevenueCharts();
                } else if (sectionId === 'analytics') {
                    initAnalyticsCharts();
                }
            }, 100);
        }

        // Modal functions
        function openAddDepartureModal() {
            document.getElementById('addDepartureModal').classList.remove('hidden');
        }

        function closeAddDepartureModal() {
            document.getElementById('addDepartureModal').classList.add('hidden');
        }

        function openAddTripModal() {
            document.getElementById('addTripModal').classList.remove('hidden');
        }

        function closeAddTripModal() {
            document.getElementById('addTripModal').classList.add('hidden');
        }

        function openAddDriverModal() {
            document.getElementById('addDriverModal').classList.remove('hidden');
        }

        function closeAddDriverModal() {
            document.getElementById('addDriverModal').classList.add('hidden');
        }

        // Chart initialization functions
        function initOverviewCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12000, 19000, 15000, 20000, 22000, 24780],
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Trip Chart
            const tripCtx = document.getElementById('tripChart').getContext('2d');
            new Chart(tripCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Standard', 'Premium', 'Luxury'],
                    datasets: [{
                        data: [124, 56, 7],
                        backgroundColor: [
                            'rgba(168, 85, 247, 0.7)',
                            'rgba(56, 189, 248, 0.7)',
                            'rgba(245, 158, 11, 0.7)'
                        ],
                        borderColor: [
                            'rgba(168, 85, 247, 1)',
                            'rgba(56, 189, 248, 1)',
                            'rgba(245, 158, 11, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        function initRevenueCharts() {
            // Detailed Revenue Chart
            const detailedRevenueCtx = document.getElementById('detailedRevenueChart').getContext('2d');
            new Chart(detailedRevenueCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Standard',
                            data: [8000, 12000, 9000, 11000, 13000, 12450],
                            backgroundColor: 'rgba(168, 85, 247, 0.7)'
                        },
                        {
                            label: 'Premium',
                            data: [3000, 5000, 4000, 7000, 6000, 8750],
                            backgroundColor: 'rgba(56, 189, 248, 0.7)'
                        },
                        {
                            label: 'Luxury',
                            data: [1000, 2000, 2000, 2000, 3000, 3580],
                            backgroundColor: 'rgba(245, 158, 11, 0.7)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            stacked: false,
                        },
                        y: {
                            stacked: false,
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function initAnalyticsCharts() {
            // Booking Trends Chart
            const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
            new Chart(bookingTrendsCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Bookings',
                            data: [45, 78, 62, 89, 102, 120],
                            borderColor: 'rgba(168, 85, 247, 1)',
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Cancellations',
                            data: [5, 8, 12, 6, 9, 7],
                            borderColor: 'rgba(239, 68, 68, 1)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Revenue by Route Chart
            const revenueByRouteCtx = document.getElementById('revenueByRouteChart').getContext('2d');
            new Chart(revenueByRouteCtx, {
                type: 'bar',
                data: {
                    labels: ['NY-Boston', 'LA-SF', 'Chicago-Detroit', 'Miami-Orlando'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12450, 8750, 3000, 1770],
                        backgroundColor: [
                            'rgba(168, 85, 247, 0.7)',
                            'rgba(56, 189, 248, 0.7)',
                            'rgba(245, 158, 11, 0.7)',
                            'rgba(16, 185, 129, 0.7)'
                        ],
                        borderColor: [
                            'rgba(168, 85, 247, 1)',
                            'rgba(56, 189, 248, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Age Distribution Chart
            const ageDistributionCtx = document.getElementById('ageDistributionChart').getContext('2d');
            new Chart(ageDistributionCtx, {
                type: 'bar',
                data: {
                    labels: ['18-24', '25-34', '35-44', '45-54', '55+'],
                    datasets: [{
                        label: 'Customers',
                        data: [15, 45, 30, 25, 10],
                        backgroundColor: 'rgba(168, 85, 247, 0.7)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Gender Distribution Chart
            const genderDistributionCtx = document.getElementById('genderDistributionChart').getContext('2d');
            new Chart(genderDistributionCtx, {
                type: 'pie',
                data: {
                    labels: ['Male', 'Female', 'Other'],
                    datasets: [{
                        data: [55, 40, 5],
                        backgroundColor: [
                            'rgba(56, 189, 248, 0.7)',
                            'rgba(244, 114, 182, 0.7)',
                            'rgba(16, 185, 129, 0.7)'
                        ],
                        borderColor: [
                            'rgba(56, 189, 248, 1)',
                            'rgba(244, 114, 182, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Test function to verify JavaScript is working
        function testJS() {
            alert('JavaScript is working!');
        }

        // Initialize overview charts on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing dashboard...');

            // Test if navigation buttons exist
            const navButtons = document.querySelectorAll('.nav-item');
            console.log('Found navigation buttons:', navButtons.length);

            // Add click event listeners to navigation buttons
            navButtons.forEach((button, index) => {
                console.log(`Setting up button ${index}:`, button.textContent.trim());

                button.addEventListener('click', function(e) {
                    console.log('Button clicked:', this.textContent.trim());
                    e.preventDefault();
                    e.stopPropagation();

                    const onclick = this.getAttribute('onclick');
                    if (onclick) {
                        // Extract section name from onclick attribute
                        const match = onclick.match(/showSection\('([^']+)'\)/);
                        if (match) {
                            console.log('Switching to section:', match[1]);
                            showSection(match[1]);
                        }
                    }
                });

                // Also ensure onclick still works
                button.style.cursor = 'pointer';
            });

            // Initialize overview charts
            setTimeout(() => {
                try {
                    initOverviewCharts();
                } catch (error) {
                    console.error('Error initializing charts:', error);
                }
            }, 500);
        });
    </script>
</body>
</html>

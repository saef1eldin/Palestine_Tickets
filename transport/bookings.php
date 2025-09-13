<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';
require_once '../includes/auth.php';

$auth = new Auth();

// التحقق من تسجيل الدخول ودور المستخدم
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'transport_admin') {
    redirect('../index.php');
}

$db = new Database();

// جلب إحصائيات الحجوزات
$db->query("SELECT COUNT(*) as total_bookings FROM transport_bookings");
$total_bookings = $db->single()['total_bookings'];

$db->query("SELECT COUNT(*) as confirmed_bookings FROM transport_bookings WHERE payment_status = 'confirmed'");
$confirmed_bookings = $db->single()['confirmed_bookings'];

$db->query("SELECT COUNT(*) as pending_bookings FROM transport_bookings WHERE payment_status = 'pending'");
$pending_bookings = $db->single()['pending_bookings'];

// جلب قائمة الحجوزات
$db->query("
    SELECT tb.*, sp.name as starting_point_name, e.title as event_title, 
           CONCAT(sp.name, ' → ', e.title) as route_name
    FROM transport_bookings tb
    LEFT JOIN transport_trips t ON tb.trip_id = t.id
    LEFT JOIN transport_starting_points sp ON t.starting_point_id = sp.id
    LEFT JOIN events e ON tb.event_id = e.id
    ORDER BY tb.created_at DESC
");
$bookings = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحجوزات - لوحة تحكم المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-l from-purple-600 to-purple-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <i class="fas fa-calendar-check text-2xl"></i>
                    <h1 class="text-2xl font-bold">إدارة الحجوزات</h1>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="dashboard.php" class="text-purple-200 hover:text-white transition">
                        <i class="fas fa-arrow-right ml-1"></i>
                        العودة للوحة التحكم
                    </a>
                    <a href="../index.php" class="text-purple-200 hover:text-white transition">
                        <i class="fas fa-home ml-1"></i>
                        الموقع الرئيسي
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">إجمالي الحجوزات</p>
                        <h3 class="text-2xl font-bold text-blue-600"><?php echo $total_bookings; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">الحجوزات المؤكدة</p>
                        <h3 class="text-2xl font-bold text-green-600"><?php echo $confirmed_bookings; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">الحجوزات المعلقة</p>
                        <h3 class="text-2xl font-bold text-yellow-600"><?php echo $pending_bookings; ?></h3>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">الحجوزات الملغاة</p>
                        <h3 class="text-2xl font-bold text-red-600"><?php echo $total_bookings - $confirmed_bookings - $pending_bookings; ?></h3>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bookings Management -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">قائمة الحجوزات</h2>
                    <div class="flex space-x-2 space-x-reverse">
                        <select class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <option>جميع الحجوزات</option>
                            <option>المؤكدة</option>
                            <option>المعلقة</option>
                            <option>الملغاة</option>
                        </select>
                        <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                            <i class="fas fa-download ml-1"></i> تصدير
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الحجز</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">العميل</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المسار</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">عدد المقاعد</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ الإجمالي</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">حالة الدفع</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الحجز</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $booking['customer_name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $booking['customer_phone']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $booking['route_name']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $booking['seats_count']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo number_format($booking['total_amount'], 2); ?> ₪
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($booking['payment_status'] == 'confirmed'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">مؤكد</span>
                                    <?php elseif ($booking['payment_status'] == 'pending'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">معلق</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">ملغي</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('Y-m-d H:i', strtotime($booking['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 ml-3">عرض</button>
                                    <button class="text-green-600 hover:text-green-900 ml-3">تأكيد</button>
                                    <button class="text-red-600 hover:text-red-900">إلغاء</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">لا توجد حجوزات</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

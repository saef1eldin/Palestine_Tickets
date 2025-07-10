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

// جلب إحصائيات التحليلات
$db->query("SELECT COUNT(*) as total_trips FROM transport_trips WHERE is_active = 1");
$total_trips = $db->single()['total_trips'];

$db->query("SELECT COUNT(*) as total_bookings FROM transport_bookings");
$total_bookings = $db->single()['total_bookings'];

$db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE payment_status = 'confirmed'");
$total_revenue = $db->single()['total_revenue'] ?? 0;

$db->query("SELECT AVG(total_amount) as avg_booking_value FROM transport_bookings WHERE payment_status = 'confirmed'");
$avg_booking_value = $db->single()['avg_booking_value'] ?? 0;

// معدل الإشغال
$db->query("
    SELECT 
        SUM(tb.seats_count) as booked_seats,
        SUM(t.available_seats) as total_seats
    FROM transport_bookings tb
    JOIN transport_trips t ON tb.trip_id = t.id
    WHERE tb.payment_status = 'confirmed'
");
$occupancy_data = $db->single();
$occupancy_rate = $occupancy_data['total_seats'] > 0 ? ($occupancy_data['booked_seats'] / $occupancy_data['total_seats']) * 100 : 0;

// أشهر المسارات
$db->query("
    SELECT 
        CONCAT(sp.name, ' → ', e.title) as route_name,
        COUNT(tb.id) as booking_count,
        SUM(tb.total_amount) as route_revenue
    FROM transport_bookings tb
    JOIN transport_trips t ON tb.trip_id = t.id
    JOIN transport_starting_points sp ON t.starting_point_id = sp.id
    JOIN events e ON tb.event_id = e.id
    WHERE tb.payment_status = 'confirmed'
    GROUP BY t.starting_point_id, tb.event_id
    ORDER BY booking_count DESC
    LIMIT 5
");
$popular_routes = $db->resultSet();

// الإيرادات الشهرية
$db->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as monthly_revenue,
        COUNT(*) as monthly_bookings
    FROM transport_bookings 
    WHERE payment_status = 'confirmed' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month
");
$monthly_data = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحليلات - لوحة تحكم المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-l from-purple-600 to-purple-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <i class="fas fa-chart-bar text-2xl"></i>
                    <h1 class="text-2xl font-bold">التحليلات والإحصائيات</h1>
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
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">إجمالي الرحلات</p>
                        <h3 class="text-2xl font-bold text-blue-600"><?php echo $total_trips; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-route text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">إجمالي الحجوزات</p>
                        <h3 class="text-2xl font-bold text-green-600"><?php echo $total_bookings; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">إجمالي الإيرادات</p>
                        <h3 class="text-2xl font-bold text-purple-600"><?php echo number_format($total_revenue, 2); ?> ₪</h3>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-shekel-sign text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">معدل الإشغال</p>
                        <h3 class="text-2xl font-bold text-orange-600"><?php echo number_format($occupancy_rate, 1); ?>%</h3>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-percentage text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Revenue Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">الإيرادات الشهرية</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Popular Routes -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">أشهر المسارات</h3>
                <div class="space-y-4">
                    <?php foreach ($popular_routes as $index => $route): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm ml-3">
                                <?php echo $index + 1; ?>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900"><?php echo $route['route_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $route['booking_count']; ?> حجز</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-green-600"><?php echo number_format($route['route_revenue'], 2); ?> ₪</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Average Booking Value -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">متوسط قيمة الحجز</h3>
                    <i class="fas fa-calculator text-blue-500"></i>
                </div>
                <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo number_format($avg_booking_value, 2); ?> ₪</div>
                <p class="text-gray-500 text-sm">متوسط المبلغ لكل حجز</p>
            </div>

            <!-- Booking Success Rate -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">معدل نجاح الحجوزات</h3>
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <?php
                $db->query("SELECT COUNT(*) as confirmed FROM transport_bookings WHERE payment_status = 'confirmed'");
                $confirmed = $db->single()['confirmed'];
                $success_rate = $total_bookings > 0 ? ($confirmed / $total_bookings) * 100 : 0;
                ?>
                <div class="text-3xl font-bold text-green-600 mb-2"><?php echo number_format($success_rate, 1); ?>%</div>
                <p class="text-gray-500 text-sm">من إجمالي الحجوزات</p>
            </div>

            <!-- Active Trips -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">الرحلات النشطة</h3>
                    <i class="fas fa-route text-purple-500"></i>
                </div>
                <?php
                $db->query("SELECT COUNT(*) as active_trips FROM transport_trips WHERE is_active = 1 AND departure_time > NOW()");
                $active_trips = $db->single()['active_trips'];
                ?>
                <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo $active_trips; ?></div>
                <p class="text-gray-500 text-sm">رحلات قادمة</p>
            </div>
        </div>
    </main>

    <script>
        // Monthly Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_data); ?>;
        
        const labels = monthlyData.map(item => item.month);
        const revenues = monthlyData.map(item => parseFloat(item.monthly_revenue));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'الإيرادات الشهرية (₪)',
                    data: revenues,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.1,
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
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ₪';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

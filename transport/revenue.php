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

// جلب إحصائيات الإيرادات
$db->query("SELECT SUM(total_amount) as total_revenue FROM transport_bookings WHERE payment_status = 'confirmed'");
$total_revenue = $db->single()['total_revenue'] ?? 0;

// إيرادات هذا الشهر
$db->query("SELECT SUM(total_amount) as monthly_revenue FROM transport_bookings WHERE payment_status = 'confirmed' AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
$monthly_revenue = $db->single()['monthly_revenue'] ?? 0;

// إيرادات الشهر الماضي
$db->query("SELECT SUM(total_amount) as last_month_revenue FROM transport_bookings WHERE payment_status = 'confirmed' AND MONTH(created_at) = MONTH(NOW()) - 1 AND YEAR(created_at) = YEAR(NOW())");
$last_month_revenue = $db->single()['last_month_revenue'] ?? 0;

// متوسط سعر الرحلة
$db->query("SELECT AVG(total_amount) as avg_trip_price FROM transport_bookings WHERE payment_status = 'confirmed'");
$avg_trip_price = $db->single()['avg_trip_price'] ?? 0;

// الإيرادات حسب المسار
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

// الإيرادات اليومية للأسبوع الماضي
$db->query("
    SELECT 
        DATE(created_at) as date,
        SUM(total_amount) as daily_revenue,
        COUNT(*) as daily_bookings
    FROM transport_bookings 
    WHERE payment_status = 'confirmed' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");
$daily_revenue = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحليل الإيرادات - لوحة تحكم المواصلات</title>
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
                    <i class="fas fa-chart-line text-2xl"></i>
                    <h1 class="text-2xl font-bold">تحليل الإيرادات</h1>
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
        <!-- Revenue Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Daily Revenue Chart -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">الإيرادات اليومية (آخر 7 أيام)</h3>
                <canvas id="dailyRevenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Revenue Distribution -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">توزيع الإيرادات حسب المسار</h3>
                <canvas id="routeRevenueChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Revenue by Route -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">الإيرادات حسب المسار</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-right py-3 px-4 font-medium text-gray-700">المسار</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-700">عدد الحجوزات</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-700">إجمالي الإيرادات</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-700">متوسط السعر</th>
                            <th class="text-right py-3 px-4 font-medium text-gray-700">النسبة من الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($revenue_by_route)): ?>
                            <?php foreach ($revenue_by_route as $route): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4"><?php echo $route['route_name']; ?></td>
                                <td class="py-3 px-4"><?php echo $route['booking_count']; ?></td>
                                <td class="py-3 px-4 font-medium text-green-600"><?php echo number_format($route['total_revenue'], 2); ?> ₪</td>
                                <td class="py-3 px-4"><?php echo number_format($route['avg_price'], 2); ?> ₪</td>
                                <td class="py-3 px-4">
                                    <?php 
                                    $percentage = $total_revenue > 0 ? ($route['total_revenue'] / $total_revenue) * 100 : 0;
                                    echo number_format($percentage, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">لا توجد بيانات إيرادات</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Daily Revenue Chart
        const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
        const dailyData = <?php echo json_encode($daily_revenue); ?>;
        
        const dailyLabels = dailyData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('ar-EG', { weekday: 'short', month: 'short', day: 'numeric' });
        });
        const dailyRevenues = dailyData.map(item => parseFloat(item.daily_revenue));
        
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'الإيرادات اليومية (₪)',
                    data: dailyRevenues,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgb(34, 197, 94)',
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

        // Route Revenue Pie Chart
        const routeCtx = document.getElementById('routeRevenueChart').getContext('2d');
        const routeData = <?php echo json_encode(array_slice($revenue_by_route, 0, 5)); ?>;
        
        const routeLabels = routeData.map(item => item.route_name);
        const routeRevenues = routeData.map(item => parseFloat(item.total_revenue));
        
        new Chart(routeCtx, {
            type: 'doughnut',
            data: {
                labels: routeLabels,
                datasets: [{
                    data: routeRevenues,
                    backgroundColor: [
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
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
    </script>
</body>
</html>

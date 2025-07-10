<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$auth = new Auth();

// التحقق من تسجيل الدخول وصلاحيات المدير
if(!$auth->isLoggedIn()) {
    redirect('../login.php');
}

if(!$auth->isAdmin()) {
    redirect('../index.php');
}

// عنوان الصفحة
$page_title = 'تقارير المبيعات';

// تضمين رأس الصفحة الخاص بلوحة الإدارة
require_once 'includes/admin_header.php';

// Fetch sales data using functions
$today_sales = get_sales_summary('today');
$month_sales = get_sales_summary('month');
$year_sales = get_sales_summary('year');
$recent_orders = get_recent_orders(15); // Get latest 15 orders

// Placeholder data
// $today_sales = ['count' => 15, 'total' => 750.50];
// $month_sales = ['count' => 210, 'total' => 10500.75];
// $year_sales = ['count' => 1500, 'total' => 85000.00];

?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">تقارير المبيعات</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h2 class="text-xl font-semibold mb-2 text-purple-700">مبيعات اليوم</h2>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($today_sales['total'] ?? 0, 2); ?> ₪</p>
            <p class="text-gray-500">(<?php echo $today_sales['count'] ?? 0; ?> تذاكر)</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h2 class="text-xl font-semibold mb-2 text-purple-700">مبيعات هذا الشهر</h2>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($month_sales['total'] ?? 0, 2); ?> ₪</p>
            <p class="text-gray-500">(<?php echo $month_sales['count'] ?? 0; ?> تذاكر)</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h2 class="text-xl font-semibold mb-2 text-purple-700">مبيعات هذه السنة</h2>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($year_sales['total'] ?? 0, 2); ?> ₪</p>
            <p class="text-gray-500">(<?php echo $year_sales['count'] ?? 0; ?> تذاكر)</p>
        </div>
    </div>

    <!-- الرسوم البيانية -->
    <div class="row mb-8">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">مبيعات الأشهر الأخيرة</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- رسم بياني للمبيعات حسب الفعالية -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">أفضل الفعاليات مبيعاً</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="eventsSalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed sales list -->
     <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <h2 class="text-xl font-semibold p-4 border-b">أحدث الطلبات</h2>
        <table class="min-w-max w-full table-auto">
            <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">رقم الطلب</th>
                    <th class="py-3 px-6 text-left">الفعالية</th>
                    <th class="py-3 px-6 text-center">العميل</th>
                    <th class="py-3 px-6 text-center">المبلغ</th>
                    <th class="py-3 px-6 text-center">التاريخ</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                 <?php if (!empty($recent_orders)): ?>
                    <?php foreach($recent_orders as $order): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left whitespace-nowrap">#<?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td class="py-3 px-6 text-left"><?php echo htmlspecialchars($order['event_title'] ?? 'N/A'); ?></td>
                        <td class="py-3 px-6 text-center"><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                        <td class="py-3 px-6 text-center"><?php echo number_format($order['total_amount'] ?? 0, 2); ?> ₪</td>
                        <td class="py-3 px-6 text-center"><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                 <?php else: ?>
                 <tr>
                     <td colspan="5" class="py-3 px-6 text-center text-gray-500">لا توجد بيانات مبيعات لعرضها حالياً.</td>
                 </tr>
                 <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// تهيئة الرسم البياني للمبيعات الشهرية
var ctx = document.getElementById('monthlySalesChart').getContext('2d');
var monthlySalesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'المبيعات الشهرية',
            data: [1000, 1500, 2000, 1800, 2200, 2500, 3000, 2800, 3200, 3500, 3800, 4000],
            backgroundColor: 'rgba(78, 115, 223, 0.05)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointRadius: 3,
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: 'rgba(78, 115, 223, 1)',
            pointHoverRadius: 5,
            pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
            pointHitRadius: 10,
            pointBorderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
        },
        scales: {
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    callback: function(value) {
                        return value + ' ₪';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y + ' ₪';
                    }
                }
            }
        }
    }
});

// تهيئة الرسم البياني للمبيعات حسب الفعالية
var ctx2 = document.getElementById('eventsSalesChart').getContext('2d');
var eventsSalesChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['فعالية 1', 'فعالية 2', 'فعالية 3', 'فعالية 4', 'فعالية 5'],
        datasets: [{
            data: [5000, 3000, 2000, 1500, 1000],
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
            ],
            hoverBackgroundColor: [
                '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'
            ],
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + ' ₪';
                    }
                }
            }
        }
    }
});
</script>

<?php
// تضمين تذييل الصفحة الخاص بلوحة الإدارة
require_once 'includes/admin_footer.php';
?>
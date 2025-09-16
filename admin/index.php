<?php
// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/init.php';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/admin_functions.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

// التحقق من صلاحيات إدارة الموقع
require_admin_permission('site');

// تسجيل النشاط
log_admin_activity($_SESSION['user_id'], 'access_dashboard', 'admin_panel', null, 'دخول لوحة تحكم الموقع');

// Set page title
$page_title = 'لوحة تحكم الموقع';

// Include admin header
include 'includes/admin_header.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// Get statistics
// Total events
try {
    $db->query("SELECT COUNT(*) as count FROM events");
    $result = $db->single();
    $totalEvents = $result['count'] ?: 0;
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $totalEvents = 0;
}

// Total users
try {
    $db->query("SELECT COUNT(*) as count FROM users");
    $result = $db->single();
    $totalUsers = $result['count'] ?: 0;
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $totalUsers = 0;
}

// Total tickets
try {
    $db->query("SELECT COUNT(*) as count FROM tickets");
    $result = $db->single();
    $totalTickets = $result['count'] ?: 0;
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $totalTickets = 0;
}

// Total sales
try {
    $db->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
    $result = $db->single();
    $totalSales = $result['total'] ?: 0;
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $totalSales = 0;
}

// Recent sales
try {
    $db->query("
        SELECT o.*, u.name as user_name, e.title as event_title
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN events e ON o.event_id = e.id
        WHERE o.payment_status = 'completed'
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $recentSales = $db->resultSet();
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $recentSales = [];
}

// Monthly sales for chart
try {
    $db->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total
        FROM orders
        WHERE payment_status = 'completed'
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
        LIMIT 12
    ");
    $monthlySales = $db->resultSet();
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $monthlySales = [];
}

// Event types for chart
try {
    $db->query("
        SELECT category as type, COUNT(*) as count
        FROM events
        GROUP BY category
        ORDER BY count DESC
        LIMIT 5
    ");
    $eventTypes = $db->resultSet();
} catch (Exception $e) {
    error_log("SQL Error: " . $e->getMessage());
    $eventTypes = [];
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['dashboard']; ?></h1>
    </div>

    <!-- Admin Navigation -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    <a href="index.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-tachometer-alt"></i><br>
                        <?php echo $lang['dashboard']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="events.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-calendar-alt"></i><br>
                        <?php echo $lang['manage_events']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="users.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-users"></i><br>
                        <?php echo $lang['manage_users']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="tickets.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-ticket-alt"></i><br>
                        <?php echo $lang['manage_tickets']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="discounts.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-percent"></i><br>
                        <?php echo $lang['manage_discounts']; ?>
                    </a>
                </div>

                <div class="col-md-2 text-center">
                    <a href="messages.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-envelope"></i><br>
                        <?php echo $lang['contact_messages'] ?? 'Contact Messages'; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="../index.php" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-home"></i><br>
                        <?php echo $lang['back_to_site']; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="dashboard-card bg-primary text-white">
                <i class="fas fa-calendar-alt"></i>
                <h3><?php echo $totalEvents; ?></h3>
                <p><?php echo $lang['total_events']; ?></p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="dashboard-card bg-success text-white">
                <i class="fas fa-users"></i>
                <h3><?php echo $totalUsers; ?></h3>
                <p><?php echo $lang['total_users']; ?></p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="dashboard-card bg-info text-white">
                <i class="fas fa-ticket-alt"></i>
                <h3><?php echo $totalTickets; ?></h3>
                <p><?php echo $lang['total_tickets']; ?></p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="dashboard-card bg-warning text-dark">
                <i class="fas fa-money-bill-wave"></i>
                <h3><?php echo formatPrice($totalSales); ?></h3>
                <p><?php echo $lang['total_sales']; ?></p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Sales Chart -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $lang['sales_statistics']; ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="sales-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Event Types Chart -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $lang['event_types']; ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="event-type-chart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $lang['recent_sales']; ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo $lang['event']; ?></th>
                            <th><?php echo $lang['user']; ?></th>
                            <th><?php echo $lang['ticket_quantity']; ?></th>
                            <th><?php echo $lang['total_price']; ?></th>
                            <th><?php echo $lang['purchase_date']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSales)): ?>
                            <tr>
                                <td colspan="5" class="text-center"><?php echo $lang['no_sales_found']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentSales as $sale): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['user_name']); ?></td>
                                    <td><?php echo $sale['quantity']; ?></td>
                                    <td><?php echo formatPrice($sale['total_amount']); ?></td>
                                    <td><?php echo formatDate($sale['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <a href="tickets.php" class="btn btn-primary btn-sm"><?php echo $lang['view_all_tickets']; ?></a>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesChartCtx = document.getElementById('sales-chart').getContext('2d');
    const salesChart = new Chart(salesChartCtx, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($monthlySales as $sale): ?>
                    '<?php echo date('M Y', strtotime($sale['month'] . '-01')); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: '<?php echo $lang['monthly_sales']; ?>',
                data: [
                    <?php foreach ($monthlySales as $sale): ?>
                        <?php echo $sale['total']; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Event Types Chart
    const eventTypeChartCtx = document.getElementById('event-type-chart').getContext('2d');
    const eventTypeChart = new Chart(eventTypeChartCtx, {
        type: 'doughnut',
        data: {
            labels: [
                <?php foreach ($eventTypes as $type): ?>
                    '<?php echo $type['type']; ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($eventTypes as $type): ?>
                        <?php echo $type['count']; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(13, 202, 240, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

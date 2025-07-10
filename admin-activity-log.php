<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/admin_functions.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    redirect('login.php');
}

// التحقق من صلاحية السوبر أدمن
require_admin_permission('super');

// معاملات البحث والتصفية
$admin_filter = $_GET['admin_id'] ?? '';
$action_filter = $_GET['action_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$limit = (int)($_GET['limit'] ?? 50);

// الحصول على قائمة المدراء للفلترة
$admin_users = get_admin_users();

// بناء استعلام السجل
$db = new Database();
$where_conditions = [];
$params = [];

if ($admin_filter) {
    $where_conditions[] = "aal.admin_id = :admin_id";
    $params[':admin_id'] = $admin_filter;
}

if ($action_filter) {
    $where_conditions[] = "aal.action_type = :action_type";
    $params[':action_type'] = $action_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(aal.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(aal.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT aal.*, u.name as admin_name, u.role as admin_role
        FROM admin_activity_log aal
        JOIN users u ON aal.admin_id = u.id
        {$where_clause}
        ORDER BY aal.created_at DESC
        LIMIT :limit";

$db->query($sql);

foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$db->bind(':limit', $limit);

$activity_logs = $db->resultSet();

// إحصائيات سريعة
$db->query("SELECT 
    COUNT(*) as total_activities,
    COUNT(DISTINCT admin_id) as active_admins,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_activities,
    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_activities
    FROM admin_activity_log");
$stats = $db->single();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-purple-800 mb-8">سجل أنشطة المدراء</h1>

        <!-- إحصائيات سريعة -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">إجمالي الأنشطة</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total_activities']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">المدراء النشطون</h3>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['active_admins']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-calendar-day text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">أنشطة اليوم</h3>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['today_activities']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-calendar-week text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">أنشطة الأسبوع</h3>
                        <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['week_activities']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- فلاتر البحث -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">فلترة السجل</h2>
            
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-1">المدير</label>
                    <select id="admin_id" name="admin_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">جميع المدراء</option>
                        <?php foreach ($admin_users as $admin): ?>
                        <option value="<?php echo $admin['id']; ?>" <?php echo $admin_filter == $admin['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($admin['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="action_type" class="block text-sm font-medium text-gray-700 mb-1">نوع النشاط</label>
                    <select id="action_type" name="action_type" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="">جميع الأنشطة</option>
                        <option value="grant_permission" <?php echo $action_filter == 'grant_permission' ? 'selected' : ''; ?>>منح صلاحية</option>
                        <option value="revoke_permission" <?php echo $action_filter == 'revoke_permission' ? 'selected' : ''; ?>>سحب صلاحية</option>
                        <option value="create_event" <?php echo $action_filter == 'create_event' ? 'selected' : ''; ?>>إنشاء حدث</option>
                        <option value="update_event" <?php echo $action_filter == 'update_event' ? 'selected' : ''; ?>>تحديث حدث</option>
                        <option value="delete_event" <?php echo $action_filter == 'delete_event' ? 'selected' : ''; ?>>حذف حدث</option>
                        <option value="send_notification" <?php echo $action_filter == 'send_notification' ? 'selected' : ''; ?>>إرسال إشعار</option>
                        <option value="manage_transport" <?php echo $action_filter == 'manage_transport' ? 'selected' : ''; ?>>إدارة مواصلات</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        بحث
                    </button>
                </div>
            </form>
        </div>

        <!-- سجل الأنشطة -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">سجل الأنشطة</h2>
            </div>

            <?php if (!empty($activity_logs)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المدير</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النشاط</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($activity_logs as $log): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-purple-600">
                                                <?php echo strtoupper(substr($log['admin_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($log['admin_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo get_role_name($log['admin_role']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $action_icons = [
                                    'grant_permission' => 'fas fa-user-plus text-green-600',
                                    'revoke_permission' => 'fas fa-user-minus text-red-600',
                                    'create_event' => 'fas fa-plus-circle text-blue-600',
                                    'update_event' => 'fas fa-edit text-yellow-600',
                                    'delete_event' => 'fas fa-trash text-red-600',
                                    'send_notification' => 'fas fa-bell text-purple-600',
                                    'manage_transport' => 'fas fa-bus text-blue-600'
                                ];
                                $icon = $action_icons[$log['action_type']] ?? 'fas fa-cog text-gray-600';
                                ?>
                                <div class="flex items-center">
                                    <i class="<?php echo $icon; ?> mr-2"></i>
                                    <span class="text-sm text-gray-900"><?php echo htmlspecialchars($log['action_type']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($log['description'] ?? 'لا يوجد وصف'); ?></div>
                                <?php if ($log['target_type'] && $log['target_id']): ?>
                                <div class="text-xs text-gray-500">الهدف: <?php echo htmlspecialchars($log['target_type']); ?> #<?php echo $log['target_id']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div><?php echo date('Y-m-d', strtotime($log['created_at'])); ?></div>
                                <div><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($log['ip_address']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="p-8 text-center">
                <i class="fas fa-history text-gray-300 text-4xl mb-4"></i>
                <p class="text-gray-500">لا توجد أنشطة مطابقة للفلاتر المحددة</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- أزرار التصدير والإجراءات -->
        <div class="mt-6 flex justify-between items-center">
            <div>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['limit' => 100])); ?>" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    عرض المزيد (100)
                </a>
            </div>
            
            <div class="space-x-2 space-x-reverse">
                <button onclick="exportToCSV()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    تصدير CSV
                </button>
                
                <a href="admin-management.php" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-users-cog mr-2"></i>
                    إدارة المدراء
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    // تحويل الجدول إلى CSV
    const table = document.querySelector('table');
    if (!table) {
        alert('لا توجد بيانات للتصدير');
        return;
    }
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [];
        const cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // تنزيل الملف
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'admin_activity_log_' + new Date().toISOString().slice(0, 10) + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

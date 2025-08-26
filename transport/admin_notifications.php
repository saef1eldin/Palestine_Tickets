<?php
require_once '../includes/init.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// معالجة الإجراءات
if ($_POST['action'] ?? '' === 'mark_read') {
    $notification_id = (int)($_POST['id'] ?? 0);
    if ($notification_id > 0) {
        $db->query("UPDATE admin_notifications SET is_read = 1 WHERE id = :id");
        $db->bind(':id', $notification_id);
        $db->execute();
    }
    header('Location: admin_notifications.php');
    exit;
}

if ($_POST['action'] ?? '' === 'mark_all_read') {
    $db->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    $db->execute();
    header('Location: admin_notifications.php');
    exit;
}

if ($_POST['action'] ?? '' === 'delete_old') {
    $db->query("DELETE FROM admin_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) AND is_read = 1");
    $db->execute();
    header('Location: admin_notifications.php');
    exit;
}

// جلب الإشعارات
try {
    $db->query("
        SELECT * FROM admin_notifications 
        ORDER BY created_at DESC 
        LIMIT 100
    ");
    $notifications = $db->resultSet();
    
    // عدد الإشعارات غير المقروءة
    $db->query("SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0");
    $unread_count = $db->single()['count'];
    
} catch (Exception $e) {
    $notifications = [];
    $unread_count = 0;
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'error': return 'fas fa-exclamation-triangle text-red-500';
        case 'warning': return 'fas fa-exclamation-circle text-yellow-500';
        case 'success': return 'fas fa-check-circle text-green-500';
        default: return 'fas fa-info-circle text-blue-500';
    }
}

function getNotificationBg($type, $is_read) {
    $opacity = $is_read ? 'opacity-60' : '';
    switch ($type) {
        case 'error': return "bg-red-50 border-red-200 {$opacity}";
        case 'warning': return "bg-yellow-50 border-yellow-200 {$opacity}";
        case 'success': return "bg-green-50 border-green-200 {$opacity}";
        default: return "bg-blue-50 border-blue-200 {$opacity}";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعارات إدمن المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-bell text-primary-600 ml-3"></i>
                        إشعارات إدمن المواصلات
                    </h1>
                    <p class="text-gray-600 mt-2">
                        إجمالي الإشعارات غير المقروءة: 
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-semibold"><?php echo $unread_count; ?></span>
                    </p>
                </div>
                <div class="flex space-x-3 space-x-reverse">
                    <a href="dashboard.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-arrow-right ml-2"></i> العودة للوحة التحكم
                    </a>
                    <a href="scheduled_tasks_interface.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-cogs ml-2"></i> إدارة المهام المجدولة
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex space-x-3 space-x-reverse">
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-check-double ml-2"></i> تحديد الكل كمقروء
                    </button>
                </form>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="delete_old">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition" 
                            onclick="return confirm('هل تريد حذف الإشعارات المقروءة الأقدم من 7 أيام؟')">
                        <i class="fas fa-trash ml-2"></i> حذف الإشعارات القديمة
                    </button>
                </form>
            </div>
        </div>

        <!-- Notifications -->
        <div class="space-y-4">
            <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <i class="fas fa-bell-slash text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">لا توجد إشعارات</h3>
                    <p class="text-gray-500">لم يتم العثور على أي إشعارات حتى الآن</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="border rounded-lg p-4 <?php echo getNotificationBg($notification['type'], $notification['is_read']); ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex items-start space-x-3 space-x-reverse flex-grow">
                                <i class="<?php echo getNotificationIcon($notification['type']); ?> text-xl mt-1"></i>
                                <div class="flex-grow">
                                    <p class="text-gray-800 <?php echo $notification['is_read'] ? 'opacity-70' : 'font-semibold'; ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="fas fa-clock ml-1"></i>
                                        <?php echo date('Y-m-d H:i:s', strtotime($notification['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="id" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" class="text-gray-500 hover:text-gray-700 transition" title="تحديد كمقروء">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

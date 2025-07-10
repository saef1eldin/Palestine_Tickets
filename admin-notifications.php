<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/notification_functions.php';
require_once 'includes/admin_functions.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    redirect('login.php');
}

// التحقق من صلاحيات إدارة الإشعارات
require_admin_permission('notifications');

$success = '';
$error = '';

// معالجة إرسال إشعار إداري
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'خطأ في التحقق من الأمان';
    } else {
        $title = sanitize_input($_POST['title'] ?? '');
        $message = sanitize_input($_POST['message'] ?? '');
        $link = sanitize_input($_POST['link'] ?? '');
        $target_users = $_POST['target_users'] ?? 'all';

        if (empty($title) || empty($message)) {
            $error = 'الرجاء إدخال العنوان والرسالة';
        } else {
            if ($target_users === 'all') {
                // إرسال لجميع المستخدمين
                if (notify_admin_announcement($title, $message, $link)) {
                    $success = 'تم إرسال الإشعار لجميع المستخدمين بنجاح';

                    // تسجيل النشاط
                    log_admin_activity($_SESSION['user_id'], 'send_notification', 'all_users', null,
                                     "إرسال إشعار إداري لجميع المستخدمين: {$title}");
                } else {
                    $error = 'فشل في إرسال الإشعار';
                }
            } else {
                // إرسال لمستخدمين محددين
                $user_ids = explode(',', $target_users);
                $user_ids = array_map('intval', $user_ids);
                $user_ids = array_filter($user_ids);

                if (!empty($user_ids)) {
                    if (add_notification_to_multiple_users($user_ids, $title, $message, $link, 'admin')) {
                        $success = 'تم إرسال الإشعار للمستخدمين المحددين بنجاح';

                        // تسجيل النشاط
                        log_admin_activity($_SESSION['user_id'], 'send_notification', 'selected_users', count($user_ids),
                                         "إرسال إشعار إداري لـ " . count($user_ids) . " مستخدم: {$title}");
                    } else {
                        $error = 'فشل في إرسال الإشعار';
                    }
                } else {
                    $error = 'الرجاء تحديد المستخدمين المراد إرسال الإشعار إليهم';
                }
            }
        }
    }
}

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// الحصول على قائمة المستخدمين
$db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
$users = $db->resultSet();

// الحصول على إحصائيات الإشعارات
$db->query("SELECT
    COUNT(*) as total_notifications,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_notifications,
    COUNT(DISTINCT user_id) as users_with_notifications
    FROM notifications");
$stats = $db->single();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-purple-800 mb-8">إدارة الإشعارات</h1>

        <!-- إحصائيات الإشعارات -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">إجمالي الإشعارات</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total_notifications']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-bell-slash text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">غير مقروءة</h3>
                        <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['unread_notifications']); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">المستخدمون النشطون</h3>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['users_with_notifications']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- رسائل النجاح والخطأ -->
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
            <p><?php echo $success; ?></p>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>

        <!-- نموذج إرسال إشعار جديد -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">إرسال إشعار جديد</h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">عنوان الإشعار *</label>
                    <input type="text" id="title" name="title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="مثال: إعلان مهم" required>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">نص الإشعار *</label>
                    <textarea id="message" name="message" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                              placeholder="اكتب نص الإشعار هنا..." required></textarea>
                </div>

                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700 mb-2">رابط الإشعار (اختياري)</label>
                    <input type="url" id="link" name="link"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="https://example.com">
                    <p class="text-xs text-gray-500 mt-1">رابط يتم توجيه المستخدم إليه عند النقر على الإشعار</p>
                </div>

                <div>
                    <label for="target_users" class="block text-sm font-medium text-gray-700 mb-2">المستخدمون المستهدفون</label>
                    <select id="target_users" name="target_users"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            onchange="toggleUserSelection()">
                        <option value="all">جميع المستخدمين النشطين</option>
                        <option value="custom">مستخدمون محددون</option>
                    </select>
                </div>

                <div id="custom-users" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">اختر المستخدمين</label>
                    <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
                        <?php foreach ($users as $user): ?>
                        <label class="flex items-center mb-2">
                            <input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>"
                                   class="mr-2 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm"><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <button type="button" onclick="previewNotification()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        معاينة
                    </button>

                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        إرسال الإشعار
                    </button>
                </div>
            </form>
        </div>

        <!-- الإشعارات الحديثة -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">الإشعارات الحديثة</h2>

            <?php
            $db->query("SELECT n.*, u.name as user_name
                       FROM notifications n
                       JOIN users u ON n.user_id = u.id
                       WHERE n.type = 'admin'
                       ORDER BY n.created_at DESC
                       LIMIT 10");
            $recent_notifications = $db->resultSet();
            ?>

            <?php if (!empty($recent_notifications)): ?>
            <div class="space-y-4">
                <?php foreach ($recent_notifications as $notification): ?>
                <div class="border-l-4 border-purple-500 bg-purple-50 p-4 rounded">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($notification['title']); ?></h4>
                            <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <p class="text-xs text-gray-500 mt-2">
                                إلى: <?php echo htmlspecialchars($notification['user_name']); ?> •
                                <?php echo timeAgo($notification['created_at']); ?>
                            </p>
                        </div>
                        <div class="flex-shrink-0 mr-4">
                            <?php if ($notification['is_read']): ?>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">مقروء</span>
                            <?php else: ?>
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">غير مقروء</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-center py-8">لا توجد إشعارات إدارية حتى الآن</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal للمعاينة -->
<div id="preview-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">معاينة الإشعار</h3>
                <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas fa-megaphone text-indigo-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p id="preview-title" class="text-sm font-medium text-gray-900"></p>
                        <p id="preview-message" class="text-xs text-gray-500 mt-1"></p>
                        <p class="text-xs text-gray-400 mt-1">الآن</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserSelection() {
    const select = document.getElementById('target_users');
    const customUsers = document.getElementById('custom-users');

    if (select.value === 'custom') {
        customUsers.classList.remove('hidden');
    } else {
        customUsers.classList.add('hidden');
    }
}

function previewNotification() {
    const title = document.getElementById('title').value;
    const message = document.getElementById('message').value;

    if (!title || !message) {
        alert('الرجاء إدخال العنوان والرسالة أولاً');
        return;
    }

    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-message').textContent = message;
    document.getElementById('preview-modal').classList.remove('hidden');
}

function closePreview() {
    document.getElementById('preview-modal').classList.add('hidden');
}

// إغلاق المودال عند النقر خارجه
document.getElementById('preview-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

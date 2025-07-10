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

$success = '';
$error = '';

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'خطأ في التحقق من الأمان';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'grant_permission':
                $user_id = (int)($_POST['user_id'] ?? 0);
                $permission_type = $_POST['permission_type'] ?? '';
                
                if ($user_id && $permission_type) {
                    if (grant_admin_permission($user_id, $permission_type, $_SESSION['user_id'])) {
                        $success = 'تم منح الصلاحية بنجاح';
                    } else {
                        $error = 'فشل في منح الصلاحية';
                    }
                } else {
                    $error = 'بيانات غير صحيحة';
                }
                break;
                
            case 'revoke_permission':
                $user_id = (int)($_POST['user_id'] ?? 0);
                $permission_type = $_POST['permission_type'] ?? '';
                
                if ($user_id && $permission_type) {
                    if (revoke_admin_permission($user_id, $permission_type, $_SESSION['user_id'])) {
                        $success = 'تم سحب الصلاحية بنجاح';
                    } else {
                        $error = 'فشل في سحب الصلاحية';
                    }
                } else {
                    $error = 'بيانات غير صحيحة';
                }
                break;
        }
    }
}

// الحصول على قائمة جميع المستخدمين
$db = new Database();
$db->query("SELECT id, name, email, role, status, created_at FROM users WHERE status = 'active' ORDER BY role DESC, name");
$all_users = $db->resultSet();

// الحصول على قائمة المدراء
$admin_users = get_admin_users();

require_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-purple-800 mb-8">إدارة المدراء والصلاحيات</h1>

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- قائمة المدراء الحاليين -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">المدراء الحاليون</h2>
                
                <?php if (!empty($admin_users)): ?>
                <div class="space-y-4">
                    <?php foreach ($admin_users as $admin): ?>
                    <div class="border rounded-lg p-4 <?php echo $admin['role'] === 'super_admin' ? 'border-red-200 bg-red-50' : 'border-gray-200'; ?>">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($admin['name']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($admin['email']); ?></p>
                                <div class="mt-2">
                                    <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                        <?php echo get_role_name($admin['role']); ?>
                                    </span>
                                </div>
                                
                                <?php if ($admin['permissions']): ?>
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">الصلاحيات:</p>
                                    <div class="flex flex-wrap gap-1">
                                        <?php 
                                        $permissions = explode(',', $admin['permissions']);
                                        foreach ($permissions as $permission): 
                                        ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                            <?php echo get_permission_name($permission); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($admin['role'] !== 'super_admin' || $admin['id'] != $_SESSION['user_id']): ?>
                            <div class="flex-shrink-0 mr-4">
                                <button onclick="showRevokeModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['name']); ?>')" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-user-minus mr-1"></i>
                                    سحب الصلاحيات
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-8">لا يوجد مدراء حالياً</p>
                <?php endif; ?>
            </div>

            <!-- منح صلاحيات جديدة -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">منح صلاحيات إدارية</h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="grant_permission">
                    
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">اختر المستخدم</label>
                        <select id="user_id" name="user_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                            <option value="">-- اختر مستخدم --</option>
                            <?php foreach ($all_users as $user): ?>
                            <?php if ($user['role'] === 'user'): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="permission_type" class="block text-sm font-medium text-gray-700 mb-2">نوع الصلاحية</label>
                        <select id="permission_type" name="permission_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" required>
                            <option value="">-- اختر نوع الصلاحية --</option>
                            <option value="transport">مدير المواصلات</option>
                            <option value="notifications">مدير الإشعارات</option>
                            <option value="site">مدير الموقع</option>
                            <option value="super">المدير العام</option>
                        </select>
                    </div>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <h4 class="font-medium text-blue-800 mb-2">وصف الصلاحيات:</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li><strong>مدير المواصلات:</strong> إدارة الرحلات والحجوزات والمركبات</li>
                            <li><strong>مدير الإشعارات:</strong> إرسال وإدارة الإشعارات للمستخدمين</li>
                            <li><strong>مدير الموقع:</strong> إدارة الأحداث والمحتوى والمستخدمين</li>
                            <li><strong>المدير العام:</strong> جميع الصلاحيات + إدارة المدراء</li>
                        </ul>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>
                        منح الصلاحية
                    </button>
                </form>
            </div>
        </div>

        <!-- إحصائيات سريعة -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <?php
            $stats = [
                'super_admin' => 0,
                'site_admin' => 0,
                'notifications_admin' => 0,
                'transport_admin' => 0
            ];
            
            foreach ($admin_users as $admin) {
                if (isset($stats[$admin['role']])) {
                    $stats[$admin['role']]++;
                }
            }
            ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-2xl font-bold text-red-600"><?php echo $stats['super_admin']; ?></div>
                <div class="text-sm text-gray-600">مدير عام</div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-2xl font-bold text-purple-600"><?php echo $stats['site_admin']; ?></div>
                <div class="text-sm text-gray-600">مدير موقع</div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-2xl font-bold text-yellow-600"><?php echo $stats['notifications_admin']; ?></div>
                <div class="text-sm text-gray-600">مدير إشعارات</div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo $stats['transport_admin']; ?></div>
                <div class="text-sm text-gray-600">مدير مواصلات</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal سحب الصلاحيات -->
<div id="revoke-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-red-600">سحب الصلاحيات الإدارية</h3>
                <button onclick="closeRevokeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-6">
                <p class="text-gray-700">هل أنت متأكد من سحب جميع الصلاحيات الإدارية من:</p>
                <p class="font-medium text-gray-900 mt-2" id="revoke-user-name"></p>
                <p class="text-sm text-red-600 mt-2">⚠️ سيتم تحويل المستخدم إلى مستخدم عادي</p>
            </div>
            
            <form method="POST" id="revoke-form">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="revoke_permission">
                <input type="hidden" name="user_id" id="revoke-user-id">
                <input type="hidden" name="permission_type" value="all">
                
                <div class="flex justify-end space-x-3 space-x-reverse">
                    <button type="button" onclick="closeRevokeModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        سحب الصلاحيات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRevokeModal(userId, userName) {
    document.getElementById('revoke-user-id').value = userId;
    document.getElementById('revoke-user-name').textContent = userName;
    document.getElementById('revoke-modal').classList.remove('hidden');
}

function closeRevokeModal() {
    document.getElementById('revoke-modal').classList.add('hidden');
}

// إغلاق المودال عند النقر خارجه
document.getElementById('revoke-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRevokeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

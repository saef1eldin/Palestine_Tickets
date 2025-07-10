<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if(!$auth->isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// استرجاع بيانات المستخدم الحالي
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// تحديث كلمة المرور
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من كلمة المرور الحالية
    if(!password_verify($current_password, $user['password_hashed'])) {
        $error = $lang['current_password_incorrect'] ?? 'كلمة المرور الحالية غير صحيحة';
    } else if(empty($new_password)) {
        $error = $lang['new_password_required'] ?? 'يرجى إدخال كلمة المرور الجديدة';
    } else if($new_password !== $confirm_password) {
        $error = $lang['passwords_not_match'] ?? 'كلمات المرور غير متطابقة';
    } else if(strlen($new_password) < PASSWORD_MIN_LENGTH) {
        $error = sprintf($lang['password_min_length'] ?? 'يجب أن تكون كلمة المرور %d أحرف على الأقل', PASSWORD_MIN_LENGTH);
    } else {
        // تحديث كلمة المرور
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $db->query("UPDATE users SET password_hashed = :password WHERE id = :id");
        $db->bind(':password', $hashed_password);
        $db->bind(':id', $_SESSION['user_id']);

        if($db->execute()) {
            $success = $lang['password_changed'] ?? 'تم تغيير كلمة المرور بنجاح';
        } else {
            $error = $lang['password_update_error'] ?? 'حدث خطأ أثناء تحديث كلمة المرور، يرجى المحاولة لاحقاً';
        }
    }
}

// تفعيل/تعطيل المصادقة الثنائية
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_two_factor'])) {
    $two_factor_enabled = isset($_POST['two_factor_enabled']) ? 1 : 0;

    $db->query("UPDATE users SET two_factor_enabled = :two_factor_enabled WHERE id = :id");
    $db->bind(':two_factor_enabled', $two_factor_enabled);
    $db->bind(':id', $_SESSION['user_id']);

    if($db->execute()) {
        $success = $lang['two_factor_updated'] ?? 'تم تحديث إعدادات المصادقة الثنائية بنجاح';

        // إعادة تحميل بيانات المستخدم
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $_SESSION['user_id']);
        $user = $db->single();
    } else {
        $error = $lang['two_factor_update_error'] ?? 'حدث خطأ أثناء تحديث إعدادات المصادقة الثنائية، يرجى المحاولة لاحقاً';
    }
}

// التحقق من صلاحيات المستخدم (هل هو مدير؟)
$is_admin = isset($user['role']) && $user['role'] === 'admin';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row">
        <!-- القائمة الجانبية -->
        <div class="w-full md:w-1/4 mb-6 md:mb-0">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- معلومات المستخدم -->
                <div class="bg-purple-100 p-6 text-center">
                    <div class="w-20 h-20 bg-purple-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <?php if(!empty($user['profile_image'])): ?>
                            <img src="<?php echo $user['profile_image']; ?>" alt="<?php echo $user['name']; ?>" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <span class="text-3xl text-purple-700"><?php echo strtoupper(substr($user['name'] ?? 'User', 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-semibold text-purple-800"><?php echo $user['name']; ?></h3>
                    <p class="text-gray-600 text-sm"><?php echo $user['email']; ?></p>
                    <a href="profile.php" class="mt-3 inline-flex items-center text-purple-600 hover:text-purple-800 text-sm">
                        <i class="fas fa-user-edit ml-1"></i>
                        <?php echo $lang['edit_profile_info'] ?? 'تعديل المعلومات الشخصية'; ?>
                    </a>
                </div>

                <!-- قائمة الصفحات -->
                <nav class="py-2">
                    <ul>
                        <li>
                            <a href="my-tickets.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-ticket-alt text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="payment-methods.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-credit-card text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="invoices.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-file-invoice text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['invoices'] ?? 'الفواتير'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-bell text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-cog text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['account_preferences'] ?? 'تفضيلات الحساب'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="security.php" class="flex items-center px-6 py-3 bg-purple-50 text-purple-700 font-medium">
                                <i class="fas fa-shield-alt text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['security'] ?? 'الأمان'; ?></span>
                            </a>
                        </li>
                        <li class="border-t border-gray-200">
                            <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['logout'] ?? 'تسجيل الخروج'; ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="w-full md:w-3/4 md:pr-8">
            <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $success; ?></p>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-purple-800 mb-6"><?php echo $lang['change_password']; ?></h2>

                <form method="post" class="space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['current_password']; ?></label>
                        <input type="password" id="current_password" name="current_password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['new_password']; ?></label>
                        <input type="password" id="new_password" name="new_password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        <p class="text-xs text-gray-500 mt-1"><?php echo sprintf($lang['password_min_length'], PASSWORD_MIN_LENGTH); ?></p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['confirm_new_password']; ?></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="update_password" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <?php echo $lang['change_password']; ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-purple-800 mb-6"><?php echo $lang['enable_two_factor']; ?></h2>

                <form method="post" class="space-y-6">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-800"><?php echo $lang['enable_two_factor']; ?></h4>
                            <p class="text-sm text-gray-600"><?php echo $lang['two_factor_desc']; ?></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="two_factor_enabled" class="sr-only peer" <?php echo $user['two_factor_enabled'] ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="update_two_factor" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <?php echo $lang['save_changes']; ?>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-purple-800 mb-6"><?php echo $lang['login_activity']; ?></h2>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 border rounded-lg">
                        <div>
                            <h4 class="font-medium text-gray-800"><?php echo $lang['last_login']; ?></h4>
                            <p class="text-sm text-gray-600">
                                <?php
                                if(!empty($user['last_login'])) {
                                    echo date('d/m/Y H:i', strtotime($user['last_login']));
                                    if(!empty($user['last_login_ip'])) {
                                        echo ' - ' . $lang['ip_address'] . ': ' . $user['last_login_ip'];
                                    }
                                } else {
                                    echo $lang['no_login_data'];
                                }
                                ?>
                            </p>
                        </div>
                        <div class="text-green-500">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    </div>

                    <div class="p-4 border rounded-lg">
                        <h4 class="font-medium text-gray-800 mb-2"><?php echo $lang['security_tips']; ?></h4>
                        <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
                            <li><?php echo $lang['security_tip_1']; ?></li>
                            <li><?php echo $lang['security_tip_2']; ?></li>
                            <li><?php echo $lang['security_tip_3']; ?></li>
                            <li><?php echo $lang['security_tip_4']; ?></li>
                            <li><?php echo $lang['security_tip_5']; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

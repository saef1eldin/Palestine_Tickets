<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/notification_functions.php';
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

// تحديث الملف الشخصي
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // التحقق من كلمة المرور الجديدة إذا تم إدخالها
    if(!empty($new_password)) {
        if($new_password !== $confirm_password) {
            $error = 'كلمة المرور وتأكيدها غير متطابقين';
        } else {
            // تحديث البيانات مع كلمة المرور الجديدة
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $db->query("UPDATE users SET name = :name, phone = :phone, password_hashed = :password WHERE id = :id");
            $db->bind(':password', $hashed_password);
        }
    } else {
        // تحديث البيانات بدون تغيير كلمة المرور
        $db->query("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
    }

    if(empty($error)) {
        $db->bind(':name', $name);
        $db->bind(':phone', $phone);
        $db->bind(':id', $_SESSION['user_id']);

        if($db->execute()) {
            $success = $lang['profile_updated'] ?? 'تم تحديث الملف الشخصي بنجاح';
            // تحديث اسم المستخدم في الجلسة
            $_SESSION['user_name'] = $name;

            // إرسال إشعار تحديث الملف الشخصي
            if (!empty($new_password)) {
                notify_password_reset($_SESSION['user_id']);
            } else {
                notify_profile_updated($_SESSION['user_id']);
            }

            // إعادة تحميل بيانات المستخدم
            $db->query("SELECT * FROM users WHERE id = :id");
            $db->bind(':id', $_SESSION['user_id']);
            $user = $db->single();
        } else {
            $error = 'حدث خطأ أثناء تحديث البيانات، يرجى المحاولة لاحقاً';
        }
    }
}

// تنسيق تاريخ التسجيل
$registration_date = date('d/m/Y', strtotime($user['created_at'] ?? date('Y-m-d')));

// التحقق من صلاحيات المستخدم (هل هو مدير؟)
$is_admin = isset($user['role']) && $user['role'] === 'admin';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-center text-purple-800 mb-8 text-improved"><?php echo $lang['profile'] ?? 'الملف الشخصي'; ?></h1>

        <?php if($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p><?php echo $lang['profile_updated'] ?? 'تم تحديث الملف الشخصي بنجاح'; ?></p>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="md:flex">
                <!-- معلومات الحساب -->
                <div class="md:w-1/3 bg-purple-50 p-6">
                    <h2 class="text-xl font-semibold text-purple-800 mb-4 text-improved"><?php echo $lang['account_info'] ?? 'معلومات الحساب'; ?></h2>

                    <div class="mb-6">
                        <div class="bg-purple-100 w-24 h-24 rounded-full mx-auto flex items-center justify-center">
                            <span class="text-3xl text-purple-700"><?php echo strtoupper(substr($user['name'] ?? 'User', 0, 1)); ?></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-gray-700"><strong><?php echo $lang['name'] ?? 'الاسم'; ?>:</strong> <?php echo $user['name'] ?? ''; ?></p>
                        <p class="text-gray-700"><strong><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?>:</strong> <?php echo $user['email'] ?? ''; ?></p>
                        <p class="text-gray-700"><strong><?php echo $lang['phone'] ?? 'رقم الهاتف'; ?>:</strong> <?php echo $user['phone'] ?? ''; ?></p>
                        <p class="text-gray-700"><strong><?php echo $lang['member_since'] ?? 'عضو منذ'; ?>:</strong> <?php echo $registration_date; ?></p>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-purple-800 mb-3 text-improved"><?php echo $lang['quick_links'] ?? 'روابط سريعة'; ?></h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="my-tickets.php" class="flex items-center text-purple-600 hover:text-purple-800 transition-colors">
                                    <i class="fas fa-ticket-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                                </a>
                            </li>
                            <?php if($is_admin): ?>
                            <li>
                                <a href="admin/index.php" class="flex items-center text-purple-600 hover:text-purple-800 transition-colors">
                                    <i class="fas fa-cog <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['admin_panel'] ?? 'لوحة الإدارة'; ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                            <li>
                                <a href="#update-form" class="flex items-center text-purple-600 hover:text-purple-800 transition-colors">
                                    <i class="fas fa-user-edit <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['update_profile'] ?? 'تحديث الملف الشخصي'; ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- نموذج تحديث الملف الشخصي -->
                <div class="md:w-2/3 p-6">
                    <h2 id="update-form" class="text-xl font-semibold text-purple-800 mb-4 text-improved"><?php echo $lang['update_profile'] ?? 'تحديث الملف الشخصي'; ?></h2>

                    <form method="post" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['name'] ?? 'الاسم'; ?></label>
                            <input type="text" id="name" name="name" value="<?php echo $user['name'] ?? ''; ?>" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?></label>
                            <input type="email" id="email" value="<?php echo $user['email'] ?? ''; ?>" class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-100" disabled>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $lang['email_cannot_be_changed'] ?? 'لا يمكن تغيير البريد الإلكتروني'; ?></p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['phone'] ?? 'رقم الهاتف'; ?></label>
                            <input type="tel" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-purple-800 mb-3 text-improved"><?php echo $lang['change_password'] ?? 'تغيير كلمة المرور'; ?></h3>
                            <p class="text-sm text-gray-500 mb-4"><?php echo $lang['leave_blank_to_keep_current_password'] ?? 'اترك الحقل فارغًا للاحتفاظ بكلمة المرور الحالية'; ?></p>

                            <div class="space-y-4">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['new_password'] ?? 'كلمة المرور الجديدة'; ?></label>
                                    <input type="password" id="new_password" name="new_password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500 mt-1"><?php echo $lang['password_requirements'] ?? 'يجب أن تكون كلمة المرور 6 أحرف على الأقل'; ?></p>
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['confirm_password'] ?? 'تأكيد كلمة المرور'; ?></label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                                <?php echo $lang['save_changes'] ?? 'حفظ التغييرات'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

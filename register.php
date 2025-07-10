<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/notification_functions.php';
require_once 'includes/header.php';

$auth = new Auth();

if($auth->isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password) {
        $error = 'كلمة المرور وتأكيدها غير متطابقين';
    } else {
        $register_result = $auth->register($name, $email, $phone, $password);
        if($register_result) {
            $success = true;

            // الحصول على معرف المستخدم الجديد
            $db = new Database();
            $db->query("SELECT id FROM users WHERE email = :email");
            $db->bind(':email', $email);
            $user = $db->single();

            if ($user) {
                // إرسال إشعار ترحيب للمستخدم الجديد
                notify_account_created($user['id'], $name);
            }
        } else {
            $error = 'حدث خطأ أثناء التسجيل، يرجى المحاولة لاحقاً';
        }
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $lang['register_title'] ?? 'تسجيل حساب جديد'; ?></h2>
                <p class="text-gray-500 text-sm"><?php echo $lang['register_subtitle'] ?? 'أنشئ حسابك للوصول إلى جميع الميزات'; ?></p>
            </div>

            <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $lang['register_error'] ?? $error; ?></p>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $lang['register_success'] ?? 'تم تسجيل الحساب بنجاح.'; ?>
                    <a href="login.php" class="font-medium underline"><?php echo $lang['register_login_link'] ?? 'اضغط هنا لتسجيل الدخول'; ?></a>
                </p>
            </div>
            <?php else: ?>

            <form method="post" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['full_name'] ?? 'الاسم الكامل'; ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="name" name="name" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" id="email" name="email" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['phone'] ?? 'رقم الهاتف'; ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input type="tel" id="phone" name="phone" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['password'] ?? 'كلمة المرور'; ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="password" name="password" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['confirm_password'] ?? 'تأكيد كلمة المرور'; ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" id="confirm_password" name="confirm_password" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                        <?php echo $lang['register'] ?? 'تسجيل الحساب'; ?>
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600"><?php echo $lang['have_account_login'] ?? 'لديك حساب بالفعل؟'; ?>
                    <a href="login.php" class="font-medium text-purple-600 hover:text-purple-800 transition-colors duration-300"><?php echo $lang['login'] ?? 'سجل الدخول'; ?></a>
                </p>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
    } catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
require_once 'includes/auth.php';
$auth = new Auth();

if($auth->isLoggedIn()) {
    // إذا كان المستخدم مسجل دخول بالفعل، توجيهه للصفحة المطلوبة أو الرئيسية
    $redirect_url = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    redirect($redirect_url);
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if($auth->login($email, $password)) {
        // بعد تسجيل الدخول بنجاح، توجيه المستخدم للصفحة المطلوبة
        $redirect_url = $_SESSION['redirect_after_login'] ?? 'index.php';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect_url);
    } else {
        $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $lang['login_title'] ?? 'تسجيل الدخول'; ?></h2>
                <p class="text-gray-500 text-sm"><?php echo $lang['login_subtitle'] ?? 'أدخل بيانات حسابك للوصول إلى حسابك'; ?></p>
            </div>

            <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $lang['login_error'] ?? $error; ?></p>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
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
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700"><?php echo $lang['password'] ?? 'كلمة المرور'; ?></label>
                        <a href="#" class="text-xs text-purple-600 hover:text-purple-800"><?php echo $lang['forgot_password'] ?? 'نسيت كلمة المرور؟'; ?></a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                        <?php echo $lang['login'] ?? 'تسجيل الدخول'; ?>
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600"><?php echo $lang['no_account_register'] ?? 'ليس لديك حساب؟'; ?>
                    <a href="register.php" class="font-medium text-purple-600 hover:text-purple-800 transition-colors duration-300"><?php echo $lang['register'] ?? 'سجل الآن'; ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
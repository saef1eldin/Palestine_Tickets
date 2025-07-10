<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if($auth->isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = false;
$token_valid = false;
$user_id = null;

// التحقق من صحة الرمز
if(isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    $db = new Database();
    $db->query("SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()");
    $db->bind(':token', $token);
    $user = $db->single();
    
    if($user) {
        $token_valid = true;
        $user_id = $user['id'];
    } else {
        $error = 'رمز إعادة التعيين غير صالح أو منتهي الصلاحية';
    }
} else {
    $error = 'رمز إعادة التعيين مفقود';
}

// معالجة النموذج
if($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(strlen($password) < 6) {
        $error = 'يجب أن تكون كلمة المرور 6 أحرف على الأقل';
    } elseif($password !== $confirm_password) {
        $error = 'كلمة المرور وتأكيدها غير متطابقين';
    } else {
        // تحديث كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $db = new Database();
        $db->query("UPDATE users SET password_hashed = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id");
        $db->bind(':password', $hashed_password);
        $db->bind(':id', $user_id);
        
        if($db->execute()) {
            $success = true;
        } else {
            $error = 'حدث خطأ أثناء تحديث كلمة المرور، يرجى المحاولة لاحقاً';
        }
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 text-improved"><?php echo $lang['reset_password_title'] ?? 'إعادة تعيين كلمة المرور'; ?></h2>
                <p class="text-gray-500 text-sm"><?php echo $lang['reset_password_subtitle'] ?? 'أدخل كلمة المرور الجديدة'; ?></p>
            </div>
            
            <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $error; ?></p>
                <?php if(!$token_valid): ?>
                <div class="mt-4">
                    <a href="forgot-password.php" class="text-red-700 font-medium hover:text-red-800 underline">
                        <?php echo $lang['request_new_reset_link'] ?? 'طلب رابط إعادة تعيين جديد'; ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $lang['password_reset_success'] ?? 'تم إعادة تعيين كلمة المرور بنجاح.'; ?></p>
                <div class="mt-4">
                    <a href="login.php" class="text-green-700 font-medium hover:text-green-800 underline">
                        <?php echo $lang['login_with_new_password'] ?? 'تسجيل الدخول باستخدام كلمة المرور الجديدة'; ?>
                    </a>
                </div>
            </div>
            <?php elseif($token_valid): ?>
            
            <form method="post" class="space-y-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['new_password'] ?? 'كلمة المرور الجديدة'; ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><?php echo $lang['password_requirements'] ?? 'يجب أن تكون كلمة المرور 6 أحرف على الأقل'; ?></p>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['confirm_password'] ?? 'تأكيد كلمة المرور'; ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                        <?php echo $lang['reset_password'] ?? 'إعادة تعيين كلمة المرور'; ?>
                    </button>
                </div>
            </form>
            
            <?php endif; ?>
            
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    <a href="login.php" class="font-medium text-purple-600 hover:text-purple-800 transition-colors duration-300">
                        <i class="fas fa-arrow-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?>"></i>
                        <?php echo $lang['back_to_login'] ?? 'العودة إلى تسجيل الدخول'; ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

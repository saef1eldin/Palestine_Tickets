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

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // التحقق من وجود البريد الإلكتروني
    $db = new Database();
    $db->query("SELECT * FROM users WHERE email = :email");
    $db->bind(':email', $email);
    $user = $db->single();
    
    if($user) {
        // إنشاء رمز إعادة تعيين كلمة المرور
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // تخزين الرمز في قاعدة البيانات
        $db->query("UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id");
        $db->bind(':token', $reset_token);
        $db->bind(':expires', $reset_expires);
        $db->bind(':id', $user['id']);
        
        if($db->execute()) {
            // إنشاء رابط إعادة تعيين كلمة المرور
            $reset_link = APP_URL . 'reset-password.php?token=' . $reset_token;
            
            // في بيئة الإنتاج، يجب إرسال بريد إلكتروني بالرابط
            // لكن هنا سنعرض الرابط مباشرة للتجربة
            $success = true;
            
            // يمكن إضافة كود إرسال البريد الإلكتروني هنا
            // sendEmail($email, 'إعادة تعيين كلمة المرور', 'رابط إعادة تعيين كلمة المرور: ' . $reset_link);
        } else {
            $error = 'حدث خطأ أثناء معالجة طلبك، يرجى المحاولة لاحقاً';
        }
    } else {
        // لا نخبر المستخدم بأن البريد الإلكتروني غير موجود لأسباب أمنية
        $success = true;
    }
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 text-improved"><?php echo $lang['forgot_password_title'] ?? 'استعادة كلمة المرور'; ?></h2>
                <p class="text-gray-500 text-sm"><?php echo $lang['forgot_password_subtitle'] ?? 'أدخل بريدك الإلكتروني لإرسال رابط إعادة تعيين كلمة المرور'; ?></p>
            </div>
            
            <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $lang['reset_link_sent'] ?? 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني. يرجى التحقق من صندوق الوارد الخاص بك.'; ?></p>
                <?php if(isset($reset_link)): ?>
                <div class="mt-4 p-3 bg-gray-100 rounded text-sm">
                    <p class="mb-2"><?php echo $lang['demo_reset_link'] ?? 'رابط إعادة التعيين (للتجربة فقط):'; ?></p>
                    <a href="<?php echo $reset_link; ?>" class="text-blue-600 break-all"><?php echo $reset_link; ?></a>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            
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
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                        <?php echo $lang['send_reset_link'] ?? 'إرسال رابط إعادة التعيين'; ?>
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

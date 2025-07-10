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

// تحديث تفضيلات الحساب
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    $preferred_language = $_POST['preferred_language'];
    $timezone = $_POST['timezone'];

    $db->query("UPDATE users SET preferred_language = :preferred_language, timezone = :timezone WHERE id = :id");
    $db->bind(':preferred_language', $preferred_language);
    $db->bind(':timezone', $timezone);
    $db->bind(':id', $_SESSION['user_id']);

    if($db->execute()) {
        $success = $lang['preferences_updated'] ?? 'تم تحديث تفضيلات الحساب بنجاح';

        // تحديث اللغة في الجلسة
        $_SESSION['lang'] = $preferred_language;

        // إعادة تحميل بيانات المستخدم
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $_SESSION['user_id']);
        $user = $db->single();

        // إعادة تحميل ملف اللغة المحدد
        if (file_exists("lang/{$preferred_language}.php")) {
            $lang = require "lang/{$preferred_language}.php";
        }
    } else {
        $error = $lang['preferences_update_error'] ?? 'حدث خطأ أثناء تحديث تفضيلات الحساب، يرجى المحاولة لاحقاً';
    }
}

// التحقق من صلاحيات المستخدم (هل هو مدير؟)
$is_admin = isset($user['role']) && $user['role'] === 'admin';

// قائمة المناطق الزمنية (دول الشرق الأوسط، بالإضافة إلى دول أمريكا الشمالية والجنوبية وأوروبا)
$timezones = [
    // الشرق الأوسط
    'Asia/Gaza' => $lang['timezone_jerusalem'] ?? 'القدس (توقيت فلسطين)',
    'Asia/Dubai' => $lang['timezone_dubai'] ?? 'دبي (توقيت الإمارات)',
    'Asia/Bahrain' => $lang['timezone_bahrain'] ?? 'المنامة (توقيت البحرين)',
    'Asia/Riyadh' => $lang['timezone_riyadh'] ?? 'الرياض (توقيت السعودية)',
    'Africa/Cairo' => $lang['timezone_cairo'] ?? 'القاهرة (توقيت مصر)',
    'Africa/Casablanca' => $lang['timezone_casablanca'] ?? 'الدار البيضاء (توقيت المغرب)',
    'Africa/Khartoum' => $lang['timezone_khartoum'] ?? 'الخرطوم (توقيت السودان)',

    // أوروبا
    'Europe/London' => $lang['timezone_london'] ?? 'لندن (توقيت غرينتش)',
    'Europe/Paris' => $lang['timezone_paris'] ?? 'باريس (توقيت وسط أوروبا)',
    'Europe/Berlin' => $lang['timezone_berlin'] ?? 'برلين (توقيت وسط أوروبا)',
    'Europe/Madrid' => $lang['timezone_madrid'] ?? 'مدريد (توقيت وسط أوروبا)',
    'Europe/Rome' => $lang['timezone_rome'] ?? 'روما (توقيت وسط أوروبا)',

    // أمريكا الشمالية
    'America/New_York' => $lang['timezone_new_york'] ?? 'نيويورك (التوقيت الشرقي)',
    'America/Chicago' => $lang['timezone_chicago'] ?? 'شيكاغو (التوقيت المركزي)',
    'America/Denver' => $lang['timezone_denver'] ?? 'دنفر (توقيت الجبال)',
    'America/Los_Angeles' => $lang['timezone_los_angeles'] ?? 'لوس أنجلوس (التوقيت الباسيفيكي)',
    'America/Toronto' => $lang['timezone_toronto'] ?? 'تورونتو (التوقيت الشرقي)',

    // أمريكا الجنوبية
    'America/Sao_Paulo' => $lang['timezone_sao_paulo'] ?? 'ساو باولو (توقيت البرازيل)',
    'America/Buenos_Aires' => $lang['timezone_buenos_aires'] ?? 'بوينس آيرس (توقيت الأرجنتين)',
    'America/Santiago' => $lang['timezone_santiago'] ?? 'سانتياغو (توقيت تشيلي)',
    'America/Bogota' => $lang['timezone_bogota'] ?? 'بوغوتا (توقيت كولومبيا)'
];
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
                        <i class="fas <?php echo get_icon('edit_profile'); ?> <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?>"></i>
                        <?php echo $lang['edit_profile_info']; ?>
                    </a>
                </div>

                <!-- قائمة الصفحات -->
                <nav class="py-2">
                    <ul>
                        <li>
                            <a href="my-tickets.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('my_tickets'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['my_tickets']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="payment-methods.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('payment_methods'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['payment_methods']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="invoices.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('invoices'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['invoices']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('notifications'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['notifications']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php" class="flex items-center px-6 py-3 bg-purple-50 text-purple-700 font-medium">
                                <i class="fas <?php echo get_icon('account_preferences'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['account_preferences']; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="security.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('security'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['security']; ?></span>
                            </a>
                        </li>
                        <li class="border-t border-gray-200">
                            <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas <?php echo get_icon('logout'); ?> <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['logout']; ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="w-full md:w-3/4 md:pr-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-purple-800 mb-6"><?php echo $lang['account_preferences']; ?></h1>

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

                <form method="post" class="space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label for="preferred_language" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['preferred_language']; ?></label>
                            <select id="preferred_language" name="preferred_language" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="ar" <?php echo ($user['preferred_language'] ?? 'ar') == 'ar' ? 'selected' : ''; ?>>العربية</option>
                                <option value="en" <?php echo ($user['preferred_language'] ?? 'ar') == 'en' ? 'selected' : ''; ?>>English</option>
                                <option value="he" <?php echo ($user['preferred_language'] ?? 'ar') == 'he' ? 'selected' : ''; ?>>עברית</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $lang['language_preference_note']; ?></p>
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['timezone']; ?></label>
                            <select id="timezone" name="timezone" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <?php foreach($timezones as $tz_value => $tz_name): ?>
                                <option value="<?php echo $tz_value; ?>" <?php echo ($user['timezone'] ?? 'Asia/Gaza') == $tz_value ? 'selected' : ''; ?>><?php echo $tz_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1"><?php echo $lang['timezone_preference_note']; ?></p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="update_preferences" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <?php echo $lang['save_changes']; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

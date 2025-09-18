<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// متغيرات أساسية
$selected_lang = 'ar';
$lang = [
    'site_name' => 'تذاكر فلسطين',
    'home' => 'الرئيسية',
    'events' => 'الفعاليات',
    'about' => 'من نحن',
    'contact' => 'اتصل بنا',
    'login' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'language' => 'العربية',
    'notifications' => 'الإشعارات',
    'my_tickets' => 'تذاكري',
    'payment_methods' => 'طرق الدفع',
    'invoices' => 'الفواتير',
    'account_preferences' => 'تفضيلات الحساب',
    'security' => 'الأمان',
    'logout' => 'تسجيل الخروج',
    'edit_profile_info' => 'تعديل المعلومات الشخصية',
    'mark_all_read' => 'تعليم الكل كمقروء',
    'no_notifications' => 'لا توجد إشعارات',
    'no_notifications_message' => 'ستظهر إشعاراتك هنا عند وصولها',
    'mark_read' => 'تعليم كمقروء',
    'delete' => 'حذف',
    'confirm_delete_notification' => 'هل أنت متأكد من رغبتك في حذف هذا التنبيه؟',
    'notification_settings' => 'إعدادات التنبيهات',
    'notification_channels' => 'قنوات التنبيهات',
    'enable_email_notifications' => 'تفعيل التنبيهات عبر البريد الإلكتروني',
    'email_notifications_desc' => 'استلام التنبيهات عبر البريد الإلكتروني المسجل',
    'enable_mobile_notifications' => 'تفعيل التنبيهات عبر الجوال',
    'mobile_notifications_desc' => 'استلام التنبيهات عبر الرسائل النصية على رقم الجوال المسجل',
    'notification_types' => 'أنواع التنبيهات',
    'upcoming_tickets' => 'تنبيهات التذاكر القادمة',
    'upcoming_tickets_desc' => 'تلقي تنبيهات قبل موعد الفعاليات التي لديك تذاكر لها',
    'event_changes' => 'تغييرات الفعاليات',
    'event_changes_desc' => 'تلقي تنبيهات عند حدوث تغييرات في الفعاليات التي لديك تذاكر لها',
    'save_changes' => 'حفظ التغييرات'
];

// استخدام دالة get_icon من includes/icons.php

// دالة timeAgo
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'منذ لحظات';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return 'منذ ' . $mins . ' دقيقة';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'منذ ' . $hours . ' ساعة';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'منذ ' . $days . ' يوم';
    } else {
        return date('d/m/Y', $time);
    }
}

$error = '';
$success = '';

// تحميل ملفات قاعدة البيانات والإشعارات
require_once 'includes/init.php';
require_once 'includes/icons.php';
require_once 'includes/notification_functions.php';

// بيانات المستخدم
$user = [
    'name' => $_SESSION['user_name'] ?? 'المستخدم',
    'email' => $_SESSION['user_email'] ?? 'user@example.com',
    'profile_image' => ''
];

$user_id = $_SESSION['user_id'];

// جلب الإشعارات من قاعدة البيانات
$notifications = get_user_notifications($user_id, false, 50);

// جلب إعدادات الإشعارات من قاعدة البيانات
$notification_settings = get_user_notification_settings($user_id);

// إذا لم توجد إعدادات، إنشاء إعدادات افتراضية
if (!$notification_settings) {
    create_default_notification_settings($user_id);
    $notification_settings = get_user_notification_settings($user_id);
}

// معالجة العمليات
if(isset($_GET['mark_all_read'])) {
    if(mark_all_notifications_read($user_id)) {
        $success = 'تم تعليم جميع التنبيهات كمقروءة';
        // إعادة تحميل الإشعارات
        $notifications = get_user_notifications($user_id, false, 50);
    } else {
        $error = 'حدث خطأ أثناء تعليم الإشعارات كمقروءة';
    }
}

if(isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = (int)$_GET['mark_read'];
    if(mark_notification_read($notification_id, $user_id)) {
        $success = 'تم تعليم التنبيه كمقروء';
        // إعادة تحميل الإشعارات
        $notifications = get_user_notifications($user_id, false, 50);
    } else {
        $error = 'حدث خطأ أثناء تعليم التنبيه كمقروء';
    }
}

if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notification_id = (int)$_GET['delete'];
    if(delete_notification($notification_id, $user_id)) {
        $success = 'تم حذف التنبيه بنجاح';
        // إعادة تحميل الإشعارات
        $notifications = get_user_notifications($user_id, false, 50);
    } else {
        $error = 'حدث خطأ أثناء حذف التنبيه';
    }
}

// معالجة تحديث إعدادات الإشعارات
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notifications'])) {
    $settings = [
        'email_enabled' => isset($_POST['email_enabled']) ? 1 : 0,
        'mobile_enabled' => isset($_POST['mobile_enabled']) ? 1 : 0,
        'upcoming_tickets' => isset($_POST['upcoming_tickets']) ? 1 : 0,
        'event_changes' => isset($_POST['event_changes']) ? 1 : 0,
        'transport_updates' => isset($_POST['transport_updates']) ? 1 : 0,
        'payment_notifications' => isset($_POST['payment_notifications']) ? 1 : 0,
        'admin_announcements' => isset($_POST['admin_announcements']) ? 1 : 0
    ];

    if(update_user_notification_settings($user_id, $settings)) {
        $success = 'تم تحديث إعدادات التنبيهات بنجاح';
        // إعادة تحميل الإعدادات
        $notification_settings = get_user_notification_settings($user_id);
    } else {
        $error = 'حدث خطأ أثناء تحديث إعدادات التنبيهات';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang; ?>" dir="<?php echo ($selected_lang == 'en') ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإشعارات - تذاكر فلسطين</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .text-improved {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-2xl font-bold text-purple-800 flex items-center text-improved">
                        <i class="fas fa-ticket-alt ml-2"></i>
                        <span><?php echo $lang['site_name']; ?></span>
                    </a>
                </div>

                <!-- Navigation Menu -->
                <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                    <a href="index.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['home']; ?></a>
                    <a href="events.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['events']; ?></a>
                    <a href="about.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['about']; ?></a>
                    <a href="contact.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['contact']; ?></a>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200">
                            <i class="fas fa-user ml-1"></i>
                            <span class="text-improved"><?php echo $user['name']; ?></span>
                            <i class="fas fa-chevron-down mr-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 top-full bg-white rounded-lg shadow-lg z-10 hidden group-hover:block w-48">
                            <a href="my-tickets.php" class="block px-4 py-2 hover:bg-gray-100 text-improved">
                                <i class="fas fa-ticket-alt text-purple-600 w-5 ml-2"></i>
                                تذاكري
                            </a>
                            <a href="notifications.php" class="block px-4 py-2 bg-purple-50 text-purple-700 text-improved">
                                <i class="fas fa-bell text-purple-600 w-5 ml-2"></i>
                                الإشعارات
                            </a>
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-improved text-red-600">
                                <i class="fas fa-sign-out-alt w-5 ml-2"></i>
                                تسجيل الخروج
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">

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
                        <?php echo $lang['edit_profile_info'] ?? 'تعديل المعلومات الشخصية'; ?>
                    </a>
                </div>

                <!-- قائمة الصفحات -->
                <nav class="py-2">
                    <ul>
                        <li>
                            <a href="my-tickets.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('my_tickets'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="payment-methods.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('payment_methods'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="invoices.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('invoices'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['invoices'] ?? 'الفواتير'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="flex items-center px-6 py-3 bg-purple-50 text-purple-700 font-medium">
                                <i class="fas <?php echo get_icon('notifications'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('account_preferences'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['account_preferences'] ?? 'تفضيلات الحساب'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="security.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('security'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['security'] ?? 'الأمان'; ?></span>
                            </a>
                        </li>
                        <li class="border-t border-gray-200">
                            <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas <?php echo get_icon('logout'); ?> <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-purple-800"><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></h1>

                    <?php if(!empty($notifications)): ?>
                    <a href="?mark_all_read=1" class="text-sm text-purple-600 hover:text-purple-800">
                        <?php echo $lang['mark_all_read'] ?? 'تعليم الكل كمقروء'; ?>
                    </a>
                    <?php endif; ?>
                </div>

                <?php if(empty($notifications)): ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-bell-slash text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-2"><?php echo $lang['no_notifications']; ?></h3>
                    <p class="text-gray-500"><?php echo $lang['no_notifications_message']; ?></p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($notifications as $notification): ?>
                    <div class="border rounded-lg p-4 <?php echo $notification['is_read'] ? 'bg-white' : 'bg-purple-50 border-purple-200'; ?>">
                        <div class="flex justify-between">
                            <div class="flex items-start">
                                <div class="<?php echo $notification['is_read'] ? 'text-gray-500' : 'text-purple-600'; ?> ml-3">
                                    <?php if($notification['type'] == 'ticket'): ?>
                                    <i class="fas fa-ticket-alt"></i>
                                    <?php elseif($notification['type'] == 'event'): ?>
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php else: ?>
                                    <i class="fas fa-bell"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?php echo $notification['title']; ?></h3>
                                    <p class="text-gray-600 text-sm"><?php echo $notification['message']; ?></p>
                                    <p class="text-gray-500 text-xs mt-1"><?php echo timeAgo($notification['created_at']); ?></p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <?php if(!$notification['is_read']): ?>
                                <a href="?mark_read=<?php echo $notification['id']; ?>" class="text-sm text-purple-600 hover:text-purple-800">
                                    <?php echo $lang['mark_read'] ?? 'تعليم كمقروء'; ?>
                                </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $notification['id']; ?>" class="text-sm text-red-600 hover:text-red-800 mr-3" onclick="return confirm('<?php echo $lang['confirm_delete_notification'] ?? 'هل أنت متأكد من رغبتك في حذف هذا التنبيه؟'; ?>')">
                                    <?php echo $lang['delete'] ?? 'حذف'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-purple-800 mb-6"><?php echo $lang['notification_settings'] ?? 'إعدادات التنبيهات'; ?></h2>

                <form method="post" class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-700"><?php echo $lang['notification_channels'] ?? 'قنوات التنبيهات'; ?></h3>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800"><?php echo $lang['enable_email_notifications'] ?? 'تفعيل التنبيهات عبر البريد الإلكتروني'; ?></h4>
                                <p class="text-sm text-gray-600"><?php echo $lang['email_notifications_desc'] ?? 'استلام التنبيهات عبر البريد الإلكتروني المسجل'; ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_enabled" class="sr-only peer" <?php echo $notification_settings['email_enabled'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800"><?php echo $lang['enable_mobile_notifications'] ?? 'تفعيل التنبيهات عبر الجوال'; ?></h4>
                                <p class="text-sm text-gray-600"><?php echo $lang['mobile_notifications_desc'] ?? 'استلام التنبيهات عبر الرسائل النصية على رقم الجوال المسجل'; ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="mobile_enabled" class="sr-only peer" <?php echo $notification_settings['mobile_enabled'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-700"><?php echo $lang['notification_types'] ?? 'أنواع التنبيهات'; ?></h3>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800"><?php echo $lang['upcoming_tickets'] ?? 'تنبيهات التذاكر القادمة'; ?></h4>
                                <p class="text-sm text-gray-600"><?php echo $lang['upcoming_tickets_desc'] ?? 'تلقي تنبيهات قبل موعد الفعاليات التي لديك تذاكر لها'; ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="upcoming_tickets" class="sr-only peer" <?php echo $notification_settings['upcoming_tickets'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800"><?php echo $lang['event_changes'] ?? 'تغييرات الفعاليات'; ?></h4>
                                <p class="text-sm text-gray-600"><?php echo $lang['event_changes_desc'] ?? 'تلقي تنبيهات عند حدوث تغييرات في الفعاليات التي لديك تذاكر لها'; ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="event_changes" class="sr-only peer" <?php echo $notification_settings['event_changes'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800">تحديثات المواصلات</h4>
                                <p class="text-sm text-gray-600">تلقي تنبيهات حول حجوزات المواصلات وتغييرات مواعيد الرحلات</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="transport_updates" class="sr-only peer" <?php echo $notification_settings['transport_updates'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800">إشعارات الدفع</h4>
                                <p class="text-sm text-gray-600">تلقي تنبيهات حول عمليات الدفع والفواتير</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="payment_notifications" class="sr-only peer" <?php echo $notification_settings['payment_notifications'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-800">الإعلانات الإدارية</h4>
                                <p class="text-sm text-gray-600">تلقي إعلانات وتحديثات من إدارة الموقع</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="admin_announcements" class="sr-only peer" <?php echo $notification_settings['admin_announcements'] ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="update_notifications" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <?php echo $lang['save_changes'] ?? 'حفظ التغييرات'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2025 تذاكر فلسطين. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>

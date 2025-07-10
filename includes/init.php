<?php
// بدء الجلسة إذا لم تكن نشطة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملف الإعدادات
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config/database.php';

// تضمين ملف الترجمة
require_once __DIR__ . '/translate.php';

// تحديد اللغة المطلوبة من الرابط أو الجلسة أو الافتراضية
if (isset($_GET['lang'])) {
    $selected_lang = $_GET['lang'];
    $_SESSION['lang'] = $selected_lang;
} elseif (isset($_SESSION['lang'])) {
    $selected_lang = $_SESSION['lang'];
} else {
    $selected_lang = defined('APP_LANG') ? APP_LANG : 'ar';
}

// قائمة اللغات المتاحة
$available_langs = ['ar', 'en', 'he'];
if (!in_array($selected_lang, $available_langs)) {
    $selected_lang = 'ar';
}

// تحميل ملف اللغة المناسب
$lang_file = __DIR__ . '/../lang/' . $selected_lang . '.php';
if (file_exists($lang_file)) {
    $lang = include $lang_file;
} else {
    // إنشاء مصفوفة لغة افتراضية بسيطة
    $lang = [
        'welcome' => 'مرحباً',
        'events' => 'الفعاليات',
        'tickets' => 'التذاكر',
        'transport' => 'المواصلات',
        'login' => 'تسجيل الدخول',
        'register' => 'التسجيل',
        'logout' => 'تسجيل الخروج',
        'profile' => 'الملف الشخصي',
        'notifications' => 'الإشعارات',
        'admin' => 'الإدارة'
    ];
}

// تضمين ملف الأيقونات
require_once __DIR__ . '/icons.php';

// تضمين دوال الإشعارات إذا كانت موجودة
if (file_exists(__DIR__ . '/notification_functions.php')) {
    require_once __DIR__ . '/notification_functions.php';
} else {
    // دوال إشعارات بديلة بسيطة
    function get_unread_notifications_count($user_id) {
        try {
            $db = new Database();
            $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0");
            $db->bind(':user_id', $user_id);
            $result = $db->single();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    function get_user_notifications($user_id, $unread_only = false, $limit = 20) {
        try {
            $db = new Database();
            $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
            if ($unread_only) {
                $sql .= " AND is_read = 0";
            }
            $sql .= " ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT :limit";
            }

            $db->query($sql);
            $db->bind(':user_id', $user_id);
            if ($limit) {
                $db->bind(':limit', $limit);
            }

            return $db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    function add_notification($user_id, $title, $message, $link = null, $type = 'info') {
        try {
            $db = new Database();
            $db->query("INSERT INTO notifications (user_id, title, message, link, type) VALUES (:user_id, :title, :message, :link, :type)");
            $db->bind(':user_id', $user_id);
            $db->bind(':title', $title);
            $db->bind(':message', $message);
            $db->bind(':link', $link);
            $db->bind(':type', $type);

            return $db->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
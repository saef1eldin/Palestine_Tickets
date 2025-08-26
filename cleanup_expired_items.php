<?php
/**
 * ملف تنظيف العناصر المنتهية الصلاحية
 * يتم تشغيله تلقائياً كل ساعة عبر cron job
 */

// تضمين ملفات الإعداد
require_once 'includes/init.php';
require_once 'includes/functions.php';

// التحقق من أن الملف يتم تشغيله من سطر الأوامر أو من خلال cron
if (php_sapi_name() !== 'cli' && !isset($_GET['manual_run'])) {
    // السماح بالتشغيل اليدوي فقط للمدراء
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin'])) {
        die('غير مسموح بالوصول');
    }
}

// تسجيل بداية العملية
$start_time = microtime(true);
$log_file = 'logs/cleanup_' . date('Y-m-d') . '.log';

function write_log($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    
    // إنشاء مجلد logs إذا لم يكن موجوداً
    if (!is_dir('logs')) {
        mkdir('logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
    
    // طباعة الرسالة إذا كان التشغيل يدوياً
    if (isset($_GET['manual_run']) || php_sapi_name() === 'cli') {
        echo $log_message;
    }
}

write_log("بدء عملية تنظيف العناصر المنتهية الصلاحية");

try {
    // تشغيل عملية التنظيف
    $results = run_expiry_cleanup();
    
    if ($results['success']) {
        write_log("تمت عملية التنظيف بنجاح: " . $results['summary']);
        
        // تفاصيل النتائج
        write_log("تفاصيل النتائج:");
        write_log("- الفعاليات المنتهية: " . $results['events']['count']);
        write_log("- الرحلات المنتهية: " . $results['trips']['total']);
        write_log("- التذاكر المنتهية: " . $results['tickets']);
        write_log("- حجوزات المواصلات المنتهية: " . $results['transport_bookings']['total']);
        
        // إرسال إشعار للمدراء إذا كان هناك عناصر منتهية
        $total_expired = $results['events']['count'] + $results['trips']['total'] + 
                        $results['tickets'] + $results['transport_bookings']['total'];
        
        if ($total_expired > 0) {
            send_admin_notification($results);
        }
        
    } else {
        write_log("فشلت عملية التنظيف: " . $results['error']);
    }
    
} catch (Exception $e) {
    write_log("خطأ في عملية التنظيف: " . $e->getMessage());
}

// حساب وقت التنفيذ
$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 2);
write_log("انتهت عملية التنظيف في $execution_time ثانية");

/**
 * إرسال إشعار للمدراء حول نتائج التنظيف
 */
function send_admin_notification($results) {
    try {
        $db = new Database();
        
        // جلب المدراء
        $db->query("SELECT DISTINCT user_id FROM admin_permissions WHERE is_active = 1");
        $admins = $db->resultSet();
        
        $title = "تقرير تنظيف العناصر المنتهية الصلاحية";
        $message = "تم تنظيف العناصر التالية:\n";
        $message .= "- " . $results['events']['count'] . " فعالية منتهية\n";
        $message .= "- " . $results['trips']['total'] . " رحلة منتهية\n";
        $message .= "- " . $results['tickets'] . " تذكرة منتهية\n";
        $message .= "- " . $results['transport_bookings']['total'] . " حجز مواصلات منتهي";
        
        foreach ($admins as $admin) {
            $db->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (:user_id, :title, :message, 'system', NOW())");
            $db->bind(':user_id', $admin['user_id']);
            $db->bind(':title', $title);
            $db->bind(':message', $message);
            $db->execute();
        }
        
        write_log("تم إرسال إشعارات للمدراء");
        
    } catch (Exception $e) {
        write_log("خطأ في إرسال الإشعارات: " . $e->getMessage());
    }
}

// إذا كان التشغيل يدوياً، عرض النتائج في صفحة ويب
if (isset($_GET['manual_run'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تنظيف العناصر المنتهية الصلاحية</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-gray-100 min-h-screen py-8">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-broom text-blue-600 ml-2"></i>
                    تنظيف العناصر المنتهية الصلاحية
                </h1>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h2 class="text-lg font-semibold text-green-800 mb-2">تمت العملية بنجاح</h2>
                    <p class="text-green-700">تم تنظيف العناصر المنتهية الصلاحية بنجاح.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <i class="fas fa-calendar-times text-blue-600 text-2xl mb-2"></i>
                        <h3 class="font-semibold text-blue-800">الفعاليات</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $results['events']['count'] ?? 0; ?></p>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <i class="fas fa-route text-yellow-600 text-2xl mb-2"></i>
                        <h3 class="font-semibold text-yellow-800">الرحلات</h3>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $results['trips']['total'] ?? 0; ?></p>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <i class="fas fa-ticket-alt text-red-600 text-2xl mb-2"></i>
                        <h3 class="font-semibold text-red-800">التذاكر</h3>
                        <p class="text-2xl font-bold text-red-600"><?php echo $results['tickets'] ?? 0; ?></p>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <i class="fas fa-bus text-purple-600 text-2xl mb-2"></i>
                        <h3 class="font-semibold text-purple-800">حجوزات المواصلات</h3>
                        <p class="text-2xl font-bold text-purple-600"><?php echo $results['transport_bookings']['total'] ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">سجل العملية</h3>
                    <pre class="text-sm text-gray-600 whitespace-pre-wrap"><?php echo file_get_contents($log_file); ?></pre>
                </div>
                
                <div class="mt-6 text-center">
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        العودة للصفحة الرئيسية
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>

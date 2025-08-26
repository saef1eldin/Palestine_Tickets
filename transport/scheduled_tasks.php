<?php
require_once '../includes/init.php';

/**
 * ملف المهام المجدولة لنظام المواصلات
 * يتم تشغيله بشكل دوري لتنظيف البيانات وإرسال الإشعارات
 */

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// دالة لإرسال إشعار للإدمن
function sendAdminNotification($message, $type = 'info') {
    // يمكن تطوير هذه الدالة لإرسال إشعارات عبر:
    // - البريد الإلكتروني
    // - تيليجرام
    // - SMS
    // - إشعارات داخل النظام
    
    global $db;
    
    try {
        // حفظ الإشعار في قاعدة البيانات
        $db->query("
            INSERT INTO admin_notifications (message, type, created_at) 
            VALUES (:message, :type, NOW())
        ");
        $db->bind(':message', $message);
        $db->bind(':type', $type);
        $db->execute();
        
        // إنشاء جدول الإشعارات إذا لم يكن موجوداً
    } catch (Exception $e) {
        // إنشاء جدول الإشعارات
        try {
            $db->query("
                CREATE TABLE IF NOT EXISTS admin_notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message TEXT NOT NULL,
                    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
                    is_read BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->execute();
            
            // إعادة المحاولة
            $db->query("
                INSERT INTO admin_notifications (message, type, created_at) 
                VALUES (:message, :type, NOW())
            ");
            $db->bind(':message', $message);
            $db->bind(':type', $type);
            $db->execute();
        } catch (Exception $e2) {
            error_log("فشل في حفظ الإشعار: " . $e2->getMessage());
        }
    }
    
    // يمكن إضافة إرسال إشعارات فورية هنا (بريد إلكتروني، تيليجرام، إلخ)
    error_log("إشعار إدمن المواصلات: " . $message);
}

// دالة لحذف الرحلات المنتهية
function deleteExpiredTrips() {
    global $db;
    
    try {
        // جلب الرحلات المنتهية (التي مضى على وقت مغادرتها أكثر من ساعة)
        $db->query("
            SELECT 
                tt.id,
                tt.departure_time,
                tsp.name as starting_point_name,
                e.title as event_title,
                COUNT(tb.id) as bookings_count
            FROM transport_trips tt
            LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
            LEFT JOIN events e ON tt.event_id = e.id
            LEFT JOIN transport_bookings tb ON tt.id = tb.trip_id
            WHERE tt.departure_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND tt.is_active = 1
            GROUP BY tt.id
        ");
        $expired_trips = $db->resultSet();
        
        if (empty($expired_trips)) {
            return "لا توجد رحلات منتهية للحذف";
        }
        
        $deleted_count = 0;
        $total_bookings_deleted = 0;
        
        foreach ($expired_trips as $trip) {
            // حذف الحجوزات المرتبطة أولاً
            if ($trip['bookings_count'] > 0) {
                $db->query("DELETE FROM transport_bookings WHERE trip_id = :trip_id");
                $db->bind(':trip_id', $trip['id']);
                $db->execute();
                $total_bookings_deleted += $trip['bookings_count'];
            }
            
            // حذف الرحلة
            $db->query("DELETE FROM transport_trips WHERE id = :trip_id");
            $db->bind(':trip_id', $trip['id']);
            
            if ($db->execute()) {
                $deleted_count++;
                
                // إرسال إشعار للإدمن عن كل رحلة محذوفة
                $message = "تم حذف رحلة منتهية تلقائياً: " . 
                          "{$trip['starting_point_name']} إلى {$trip['event_title']} " .
                          "وقت المغادرة: {$trip['departure_time']}";
                
                if ($trip['bookings_count'] > 0) {
                    $message .= " (تم حذف {$trip['bookings_count']} حجز مرتبط)";
                }
                
                sendAdminNotification($message, 'warning');
            }
        }
        
        // إرسال ملخص عام
        if ($deleted_count > 0) {
            $summary = "تم حذف {$deleted_count} رحلة منتهية تلقائياً";
            if ($total_bookings_deleted > 0) {
                $summary .= " مع {$total_bookings_deleted} حجز مرتبط";
            }
            sendAdminNotification($summary, 'info');
        }
        
        return "تم حذف {$deleted_count} رحلة منتهية";
        
    } catch (Exception $e) {
        $error_message = "خطأ في حذف الرحلات المنتهية: " . $e->getMessage();
        sendAdminNotification($error_message, 'error');
        return $error_message;
    }
}

// دالة لتنظيف الإشعارات القديمة (أكثر من 30 يوم)
function cleanupOldNotifications() {
    global $db;
    
    try {
        $db->query("DELETE FROM admin_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $deleted = $db->execute();
        return "تم حذف الإشعارات القديمة";
    } catch (Exception $e) {
        return "خطأ في تنظيف الإشعارات: " . $e->getMessage();
    }
}

// تشغيل المهام
if (php_sapi_name() === 'cli' || isset($_GET['run_tasks'])) {
    echo "بدء تشغيل المهام المجدولة...\n";
    echo date('Y-m-d H:i:s') . " - " . deleteExpiredTrips() . "\n";
    echo date('Y-m-d H:i:s') . " - " . cleanupOldNotifications() . "\n";
    echo "انتهاء المهام المجدولة.\n";
} else {
    echo "هذا الملف مخصص للمهام المجدولة. لتشغيله يدوياً، أضف ?run_tasks=1 إلى الرابط";
}
?>

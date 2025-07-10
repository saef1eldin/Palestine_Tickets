<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/notification_functions.php';

/**
 * ملف إرسال التذكيرات التلقائية
 * يمكن تشغيله عبر CRON job أو استدعاؤه يدوياً
 */

$db = new Database();
$sent_count = 0;
$errors = [];

try {
    // 1. تذكيرات الأحداث (24 ساعة قبل الحدث)
    $db->query("
        SELECT DISTINCT t.user_id, e.id as event_id, e.title, e.date_time, u.name as user_name
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        JOIN users u ON t.user_id = u.id
        WHERE e.date_time BETWEEN NOW() + INTERVAL 23 HOUR AND NOW() + INTERVAL 25 HOUR
        AND t.status = 'confirmed'
        AND NOT EXISTS (
            SELECT 1 FROM notifications n 
            WHERE n.user_id = t.user_id 
            AND n.type = 'reminder' 
            AND n.message LIKE CONCAT('%', e.title, '%')
            AND DATE(n.created_at) = CURDATE()
        )
    ");
    $upcoming_events = $db->resultSet();

    foreach ($upcoming_events as $event) {
        if (notify_event_reminder($event['user_id'], $event['title'], $event['date_time'], 24)) {
            $sent_count++;
            echo "✓ تم إرسال تذكير حدث إلى {$event['user_name']} للحدث: {$event['title']}\n";
        } else {
            $errors[] = "فشل إرسال تذكير حدث إلى {$event['user_name']} للحدث: {$event['title']}";
        }
    }

    // 2. تذكيرات المواصلات (ساعة واحدة قبل الانطلاق)
    $db->query("
        SELECT DISTINCT tb.user_id, tb.trip_id, tr.departure_time, e.title as event_title, 
               sp.name as starting_point, u.name as user_name
        FROM transport_bookings tb
        JOIN transport_trips tr ON tb.trip_id = tr.id
        JOIN events e ON tr.event_id = e.id
        JOIN transport_starting_points sp ON tr.starting_point_id = sp.id
        JOIN users u ON tb.user_id = u.id
        WHERE tr.departure_time BETWEEN NOW() + INTERVAL 50 MINUTE AND NOW() + INTERVAL 70 MINUTE
        AND tb.status = 'confirmed'
        AND NOT EXISTS (
            SELECT 1 FROM notifications n 
            WHERE n.user_id = tb.user_id 
            AND n.type = 'reminder' 
            AND n.message LIKE CONCAT('%', e.title, '%')
            AND n.message LIKE '%انطلاق%'
            AND DATE(n.created_at) = CURDATE()
        )
    ");
    $upcoming_trips = $db->resultSet();

    foreach ($upcoming_trips as $trip) {
        if (notify_transport_reminder($trip['user_id'], $trip['event_title'], $trip['departure_time'], $trip['starting_point'])) {
            $sent_count++;
            echo "✓ تم إرسال تذكير مواصلات إلى {$trip['user_name']} للحدث: {$trip['event_title']}\n";
        } else {
            $errors[] = "فشل إرسال تذكير مواصلات إلى {$trip['user_name']} للحدث: {$trip['event_title']}";
        }
    }

    // 3. تذكيرات الأحداث المنتهية الصلاحية (تنظيف)
    $db->query("
        SELECT COUNT(*) as expired_count
        FROM notifications 
        WHERE created_at < NOW() - INTERVAL 30 DAY
        AND type IN ('reminder', 'info')
    ");
    $expired = $db->single();

    if ($expired['expired_count'] > 0) {
        $db->query("
            DELETE FROM notifications 
            WHERE created_at < NOW() - INTERVAL 30 DAY
            AND type IN ('reminder', 'info')
        ");
        $db->execute();
        echo "✓ تم حذف {$expired['expired_count']} إشعار منتهي الصلاحية\n";
    }

    // 4. إشعارات الأحداث الملغاة أو المؤجلة
    $db->query("
        SELECT e.id, e.title, e.status, e.date_time,
               GROUP_CONCAT(DISTINCT t.user_id) as user_ids
        FROM events e
        JOIN tickets t ON e.id = t.event_id
        WHERE e.status IN ('cancelled', 'postponed')
        AND e.updated_at >= NOW() - INTERVAL 1 HOUR
        AND t.status = 'confirmed'
        GROUP BY e.id
    ");
    $changed_events = $db->resultSet();

    foreach ($changed_events as $event) {
        $user_ids = array_map('intval', explode(',', $event['user_ids']));
        
        if ($event['status'] === 'cancelled') {
            if (notify_event_cancelled($user_ids, $event['title'])) {
                $sent_count += count($user_ids);
                echo "✓ تم إرسال إشعار إلغاء الحدث: {$event['title']} إلى " . count($user_ids) . " مستخدم\n";
            }
        }
    }

    // 5. تذكيرات دورية للمستخدمين غير النشطين
    $db->query("
        SELECT u.id, u.name, u.email
        FROM users u
        WHERE u.last_login < NOW() - INTERVAL 7 DAY
        AND u.status = 'active'
        AND NOT EXISTS (
            SELECT 1 FROM notifications n 
            WHERE n.user_id = u.id 
            AND n.type = 'reminder'
            AND n.title LIKE '%نشاط%'
            AND n.created_at >= NOW() - INTERVAL 7 DAY
        )
        LIMIT 50
    ");
    $inactive_users = $db->resultSet();

    foreach ($inactive_users as $user) {
        $title = "نفتقدك في تذاكر فلسطين!";
        $message = "مرحباً {$user['name']}، لم نراك منذ فترة. تصفح الأحداث الجديدة واحجز تذكرتك الآن!";
        $link = "events.php";
        
        if (add_notification($user['id'], $title, $message, $link, 'reminder')) {
            $sent_count++;
            echo "✓ تم إرسال تذكير نشاط إلى {$user['name']}\n";
        }
    }

    // إحصائيات النهائية
    echo "\n=== ملخص التذكيرات ===\n";
    echo "إجمالي التذكيرات المرسلة: {$sent_count}\n";
    echo "عدد الأخطاء: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nالأخطاء:\n";
        foreach ($errors as $error) {
            echo "❌ {$error}\n";
        }
    }

    // تسجيل في ملف السجل
    $log_message = "Reminders sent: {$sent_count}, Errors: " . count($errors);
    error_log($log_message);

    // إذا تم استدعاء الملف من المتصفح
    if (isset($_SERVER['HTTP_HOST'])) {
        echo "<br><strong>تم إرسال {$sent_count} تذكير بنجاح!</strong><br>";
        if (!empty($errors)) {
            echo "<br><strong>الأخطاء:</strong><br>";
            foreach ($errors as $error) {
                echo "❌ {$error}<br>";
            }
        }
        echo "<br><a href='admin-notifications.php'>العودة لإدارة الإشعارات</a>";
    }

} catch (Exception $e) {
    $error_message = "خطأ في إرسال التذكيرات: " . $e->getMessage();
    echo "❌ {$error_message}\n";
    error_log($error_message);
    
    if (isset($_SERVER['HTTP_HOST'])) {
        echo "<br>❌ {$error_message}<br>";
        echo "<br><a href='admin-notifications.php'>العودة لإدارة الإشعارات</a>";
    }
}

/**
 * لتشغيل هذا الملف تلقائياً عبر CRON، أضف السطر التالي إلى crontab:
 * 
 * # تشغيل كل ساعة
 * 0 * * * * /usr/bin/php /path/to/your/project/send_reminders.php
 * 
 * # أو تشغيل كل 30 دقيقة
 * */30 * * * * /usr/bin/php /path/to/your/project/send_reminders.php
 * 
 * # أو تشغيل عبر wget/curl
 * 0 * * * * wget -q -O - http://yoursite.com/send_reminders.php
 */
?>

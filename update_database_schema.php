<?php
/**
 * ملف تحديث قاعدة البيانات لإضافة الحقول المطلوبة لنظام انتهاء الصلاحية
 */

require_once 'includes/init.php';

// التحقق من صلاحيات المدير
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin'])) {
    die('غير مسموح بالوصول');
}

$db = new Database();
$updates = [];
$errors = [];

try {
    // التحقق من وجود عمود status في جدول events
    $db->query("SHOW COLUMNS FROM events LIKE 'status'");
    $status_column = $db->single();
    
    if (!$status_column) {
        $db->query("ALTER TABLE events ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER is_active");
        $db->execute();
        $updates[] = "تم إضافة عمود status إلى جدول events";
    } else {
        $updates[] = "عمود status موجود بالفعل في جدول events";
    }
    
    // التحقق من وجود قيمة 'expired' في enum status للجدول tickets
    $db->query("SHOW COLUMNS FROM tickets WHERE Field = 'status'");
    $ticket_status = $db->single();
    
    if ($ticket_status && strpos($ticket_status['Type'], 'expired') === false) {
        $db->query("ALTER TABLE tickets MODIFY COLUMN status ENUM('active','used','cancelled','confirmed','expired') DEFAULT 'active'");
        $db->execute();
        $updates[] = "تم تحديث عمود status في جدول tickets لإضافة 'expired'";
    } else {
        $updates[] = "عمود status في جدول tickets محدث بالفعل";
    }
    
    // التحقق من وجود قيمة 'expired' في enum status للجدول transport_bookings
    $db->query("SHOW COLUMNS FROM transport_bookings WHERE Field = 'status'");
    $booking_status = $db->single();
    
    if ($booking_status && strpos($booking_status['Type'], 'expired') === false) {
        $db->query("ALTER TABLE transport_bookings MODIFY COLUMN status ENUM('pending','confirmed','cancelled','completed','expired') DEFAULT 'pending'");
        $db->execute();
        $updates[] = "تم تحديث عمود status في جدول transport_bookings لإضافة 'expired'";
    } else {
        $updates[] = "عمود status في جدول transport_bookings محدث بالفعل";
    }
    
    // إنشاء جدول cleanup_logs إذا لم يكن موجوداً
    $db->query("SHOW TABLES LIKE 'cleanup_logs'");
    $cleanup_table = $db->single();
    
    if (!$cleanup_table) {
        $db->query("
            CREATE TABLE cleanup_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cleanup_date DATETIME NOT NULL,
                events_count INT DEFAULT 0,
                trips_count INT DEFAULT 0,
                tickets_count INT DEFAULT 0,
                bookings_count INT DEFAULT 0,
                execution_time DECIMAL(5,2) DEFAULT 0,
                status ENUM('success', 'error') DEFAULT 'success',
                error_message TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
        $updates[] = "تم إنشاء جدول cleanup_logs";
    } else {
        $updates[] = "جدول cleanup_logs موجود بالفعل";
    }
    
    // تحديث الفعاليات الحالية المنتهية
    $db->query("UPDATE events SET status = 'expired' WHERE date_time < NOW() AND status = 'active'");
    $expired_events = $db->execute();
    if ($expired_events > 0) {
        $updates[] = "تم تحديث $expired_events فعالية منتهية الصلاحية";
    }
    
    // تحديث التذاكر المرتبطة بالفعاليات المنتهية
    $db->query("
        UPDATE tickets t 
        JOIN events e ON t.event_id = e.id 
        SET t.status = 'expired' 
        WHERE e.status = 'expired' AND t.status = 'active'
    ");
    $expired_tickets = $db->execute();
    if ($expired_tickets > 0) {
        $updates[] = "تم تحديث $expired_tickets تذكرة منتهية الصلاحية";
    }
    
    // تحديث حجوزات المواصلات المرتبطة بالفعاليات المنتهية
    $db->query("
        UPDATE transport_bookings tb 
        JOIN events e ON tb.event_id = e.id 
        SET tb.status = 'expired' 
        WHERE e.status = 'expired' AND tb.status IN ('pending', 'confirmed')
    ");
    $expired_bookings = $db->execute();
    if ($expired_bookings > 0) {
        $updates[] = "تم تحديث $expired_bookings حجز مواصلات منتهي الصلاحية";
    }
    
} catch (Exception $e) {
    $errors[] = "خطأ في تحديث قاعدة البيانات: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث قاعدة البيانات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-database text-blue-600 ml-2"></i>
                تحديث قاعدة البيانات
            </h1>
            
            <?php if (!empty($updates)): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-green-800 mb-2">
                    <i class="fas fa-check-circle ml-2"></i>
                    التحديثات المنجزة
                </h2>
                <ul class="list-disc list-inside text-green-700">
                    <?php foreach ($updates as $update): ?>
                    <li><?php echo htmlspecialchars($update); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-red-800 mb-2">
                    <i class="fas fa-exclamation-triangle ml-2"></i>
                    الأخطاء
                </h2>
                <ul class="list-disc list-inside text-red-700">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle ml-2"></i>
                    الخطوات التالية
                </h2>
                <ul class="list-disc list-inside text-blue-700">
                    <li>تم تحديث قاعدة البيانات بنجاح</li>
                    <li>يمكنك الآن إعداد cron job لتشغيل التنظيف التلقائي</li>
                    <li>راجع ملف setup_cron.txt للتعليمات</li>
                    <li>يمكنك تشغيل التنظيف يدوياً من لوحة تحكم المواصلات</li>
                </ul>
            </div>
            
            <div class="flex space-x-4 space-x-reverse">
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-home ml-2"></i>
                    الصفحة الرئيسية
                </a>
                <a href="transport/dashboard.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-tachometer-alt ml-2"></i>
                    لوحة تحكم المواصلات
                </a>
                <a href="cleanup_expired_items.php?manual_run=1" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg" target="_blank">
                    <i class="fas fa-broom ml-2"></i>
                    تشغيل التنظيف يدوياً
                </a>
            </div>
        </div>
    </div>
</body>
</html>

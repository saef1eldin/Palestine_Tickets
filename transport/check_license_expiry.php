<?php
/**
 * نظام مراقبة انتهاء رخص السائقين
 * يتم تشغيله دورياً للتحقق من الرخص المنتهية أو التي ستنتهي قريباً
 */

require_once '../includes/init.php';

$db = new Database();

try {
    // التحقق من وجود حقل license_expiry
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'license_expiry'");
    $license_expiry_column = $db->single();
    
    if (!$license_expiry_column) {
        echo json_encode(['success' => false, 'message' => 'حقل تاريخ انتهاء الرخصة غير موجود في قاعدة البيانات']);
        exit;
    }

    $today = date('Y-m-d');
    $warning_date = date('Y-m-d', strtotime('+30 days')); // تحذير قبل 30 يوم
    
    // البحث عن الرخص المنتهية
    $db->query("
        SELECT id, name, license_number, license_expiry, phone 
        FROM transport_drivers 
        WHERE license_expiry IS NOT NULL 
        AND license_expiry < :today 
        AND is_active = 1
        ORDER BY license_expiry ASC
    ");
    $db->bind(':today', $today);
    $expired_licenses = $db->resultSet();
    
    // البحث عن الرخص التي ستنتهي خلال 30 يوم
    $db->query("
        SELECT id, name, license_number, license_expiry, phone,
               DATEDIFF(license_expiry, :today) as days_remaining
        FROM transport_drivers 
        WHERE license_expiry IS NOT NULL 
        AND license_expiry >= :today 
        AND license_expiry <= :warning_date 
        AND is_active = 1
        ORDER BY license_expiry ASC
    ");
    $db->bind(':today', $today);
    $db->bind(':warning_date', $warning_date);
    $expiring_soon = $db->resultSet();
    
    $notifications = [];
    
    // إنشاء إشعارات للرخص المنتهية
    foreach ($expired_licenses as $driver) {
        $title = "رخصة سائق منتهية الصلاحية";
        $message = "رخصة السائق {$driver['name']} (رقم الرخصة: {$driver['license_number']}) منتهية الصلاحية منذ " . 
                   date('Y-m-d', strtotime($driver['license_expiry'])) . ". يرجى تجديد الرخصة فوراً.";
        
        // التحقق من عدم وجود إشعار مماثل خلال آخر 7 أيام
        $db->query("
            SELECT id FROM admin_notifications 
            WHERE title = :title 
            AND message LIKE :message_pattern 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $db->bind(':title', $title);
        $db->bind(':message_pattern', "%{$driver['name']}%{$driver['license_number']}%");
        $existing = $db->single();
        
        if (!$existing) {
            // إضافة إشعار جديد
            $db->query("
                INSERT INTO admin_notifications (title, message, type, priority, created_at) 
                VALUES (:title, :message, 'license_expired', 'high', NOW())
            ");
            $db->bind(':title', $title);
            $db->bind(':message', $message);
            $db->execute();
            
            $notifications[] = [
                'type' => 'expired',
                'driver' => $driver,
                'message' => $message
            ];
        }
    }
    
    // إنشاء إشعارات للرخص التي ستنتهي قريباً
    foreach ($expiring_soon as $driver) {
        $title = "تحذير: رخصة سائق ستنتهي قريباً";
        $message = "رخصة السائق {$driver['name']} (رقم الرخصة: {$driver['license_number']}) ستنتهي خلال {$driver['days_remaining']} يوم في " . 
                   date('Y-m-d', strtotime($driver['license_expiry'])) . ". يرجى التجديد قبل انتهاء الصلاحية.";
        
        // التحقق من عدم وجود إشعار مماثل خلال آخر 7 أيام
        $db->query("
            SELECT id FROM admin_notifications 
            WHERE title = :title 
            AND message LIKE :message_pattern 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $db->bind(':title', $title);
        $db->bind(':message_pattern', "%{$driver['name']}%{$driver['license_number']}%");
        $existing = $db->single();
        
        if (!$existing) {
            // إضافة إشعار جديد
            $db->query("
                INSERT INTO admin_notifications (title, message, type, priority, created_at) 
                VALUES (:title, :message, 'license_warning', 'medium', NOW())
            ");
            $db->bind(':title', $title);
            $db->bind(':message', $message);
            $db->execute();
            
            $notifications[] = [
                'type' => 'warning',
                'driver' => $driver,
                'message' => $message
            ];
        }
    }
    
    // إنشاء جدول admin_notifications إذا لم يكن موجوداً
    $db->query("SHOW TABLES LIKE 'admin_notifications'");
    $table_exists = $db->single();
    
    if (!$table_exists) {
        $db->query("
            CREATE TABLE admin_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                type VARCHAR(50) DEFAULT 'general',
                priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $db->execute();
    }
    
    $result = [
        'success' => true,
        'expired_count' => count($expired_licenses),
        'expiring_soon_count' => count($expiring_soon),
        'new_notifications' => count($notifications),
        'expired_licenses' => $expired_licenses,
        'expiring_soon' => $expiring_soon,
        'notifications' => $notifications
    ];
    
    // إذا تم استدعاء الملف من المتصفح، عرض النتائج بشكل مرئي
    if (isset($_GET['display']) && $_GET['display'] == 'html') {
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>مراقبة رخص السائقين</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 p-6">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">تقرير مراقبة رخص السائقين</h1>
                
                <?php if (count($expired_licenses) > 0): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <h2 class="text-lg font-semibold text-red-800 mb-3">رخص منتهية الصلاحية (<?php echo count($expired_licenses); ?>)</h2>
                    <?php foreach ($expired_licenses as $driver): ?>
                    <div class="bg-white p-3 rounded border-l-4 border-red-500 mb-2">
                        <p><strong><?php echo htmlspecialchars($driver['name']); ?></strong></p>
                        <p>رقم الرخصة: <?php echo htmlspecialchars($driver['license_number']); ?></p>
                        <p>انتهت في: <?php echo $driver['license_expiry']; ?></p>
                        <p>الهاتف: <?php echo htmlspecialchars($driver['phone']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (count($expiring_soon) > 0): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <h2 class="text-lg font-semibold text-yellow-800 mb-3">رخص ستنتهي قريباً (<?php echo count($expiring_soon); ?>)</h2>
                    <?php foreach ($expiring_soon as $driver): ?>
                    <div class="bg-white p-3 rounded border-l-4 border-yellow-500 mb-2">
                        <p><strong><?php echo htmlspecialchars($driver['name']); ?></strong></p>
                        <p>رقم الرخصة: <?php echo htmlspecialchars($driver['license_number']); ?></p>
                        <p>ستنتهي في: <?php echo $driver['license_expiry']; ?> (خلال <?php echo $driver['days_remaining']; ?> يوم)</p>
                        <p>الهاتف: <?php echo htmlspecialchars($driver['phone']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (count($expired_licenses) == 0 && count($expiring_soon) == 0): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800">✅ جميع رخص السائقين سارية المفعول</p>
                </div>
                <?php endif; ?>
                
                <div class="mt-6">
                    <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">العودة للوحة التحكم</a>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        // إرجاع JSON للاستدعاءات البرمجية
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    $error_result = [
        'success' => false,
        'message' => 'حدث خطأ: ' . $e->getMessage()
    ];
    
    if (isset($_GET['display']) && $_GET['display'] == 'html') {
        echo "<h3 style='color: red;'>خطأ: " . $e->getMessage() . "</h3>";
    } else {
        header('Content-Type: application/json');
        echo json_encode($error_result);
    }
    
    error_log("License expiry check error: " . $e->getMessage());
}
?>

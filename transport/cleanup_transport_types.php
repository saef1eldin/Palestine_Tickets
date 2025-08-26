<?php
/**
 * تنظيف البيانات المكررة في جدول transport_types
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>تنظيف البيانات المكررة في جدول أنواع المركبات...</h2>";
    
    // جلب جميع أنواع المركبات
    $db->query("SELECT * FROM transport_types ORDER BY id");
    $all_types = $db->resultSet();
    
    echo "<h3>أنواع المركبات الموجودة:</h3>";
    foreach ($all_types as $type) {
        echo "- ID: {$type['id']}, الاسم: {$type['name']}<br>";
    }
    
    // البحث عن التكرارات
    $db->query("
        SELECT name, COUNT(*) as count
        FROM transport_types
        GROUP BY name
        HAVING COUNT(*) > 1
    ");
    $duplicates = $db->resultSet();

    // حذف جميع السجلات المكررة والاحتفاظ بواحد فقط لكل نوع
    $unique_types = [
        'باص عادي' => 'حافلة كبيرة لنقل الركاب',
        'باص فاخر' => 'حافلة مكيفة ومريحة',
        'حافلة سياحية' => 'حافلة مخصصة للرحلات السياحية',
        'حافلة عادية' => 'حافلة عادية لنقل الركاب',
        'سيارة خاصة' => 'سيارة خاصة صغيرة',
        'فان' => 'مركبة متوسطة الحجم',
        'ميكروباص' => 'حافلة صغيرة'
    ];

    // حذف جميع السجلات الموجودة
    $db->query("DELETE FROM transport_types");
    $db->execute();

    // إعادة إدراج البيانات الصحيحة
    foreach ($unique_types as $name => $description) {
        $db->query("
            INSERT INTO transport_types (name, description, icon, is_active, created_at)
            VALUES (:name, :description, 'fas fa-bus', 1, NOW())
        ");
        $db->bind(':name', $name);
        $db->bind(':description', $description);
        $db->execute();
        echo "✅ تم إضافة: $name<br>";
    }
    
    if (empty($duplicates)) {
        echo "<h3 style='color: green;'>✅ لا توجد بيانات مكررة</h3>";
    } else {
        echo "<h3 style='color: orange;'>تم العثور على بيانات مكررة:</h3>";
        
        foreach ($duplicates as $duplicate) {
            echo "<h4>اسم المركبة: {$duplicate['name']} (مكرر {$duplicate['count']} مرات)</h4>";
            
            // جلب جميع السجلات المكررة لهذا الاسم
            $db->query("SELECT * FROM transport_types WHERE name = :name ORDER BY id");
            $db->bind(':name', $duplicate['name']);
            $duplicate_records = $db->resultSet();
            
            // الاحتفاظ بأول سجل وحذف الباقي
            $keep_record = array_shift($duplicate_records);
            echo "✅ سيتم الاحتفاظ بالسجل ID: {$keep_record['id']}<br>";
            
            foreach ($duplicate_records as $record) {
                echo "🗑️ سيتم حذف السجل ID: {$record['id']}<br>";
                
                // تحديث المراجع في الجداول الأخرى
                $db->query("UPDATE transport_vehicles SET transport_type_id = :keep_id WHERE transport_type_id = :delete_id");
                $db->bind(':keep_id', $keep_record['id']);
                $db->bind(':delete_id', $record['id']);
                $db->execute();
                
                $db->query("UPDATE transport_trips SET transport_type_id = :keep_id WHERE transport_type_id = :delete_id");
                $db->bind(':keep_id', $keep_record['id']);
                $db->bind(':delete_id', $record['id']);
                $db->execute();
                
                // حذف السجل المكرر
                $db->query("DELETE FROM transport_types WHERE id = :id");
                $db->bind(':id', $record['id']);
                $db->execute();
                
                echo "✅ تم حذف السجل وتحديث المراجع<br>";
            }
            echo "<br>";
        }
        
        echo "<h3 style='color: green;'>✅ تم تنظيف جميع البيانات المكررة</h3>";
    }
    
    // عرض النتيجة النهائية
    echo "<h3>أنواع المركبات بعد التنظيف:</h3>";
    $db->query("SELECT * FROM transport_types WHERE is_active = 1 ORDER BY name");
    $final_types = $db->resultSet();
    
    foreach ($final_types as $type) {
        echo "- {$type['name']} (ID: {$type['id']})<br>";
    }
    
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Transport types cleanup error: " . $e->getMessage());
}
?>

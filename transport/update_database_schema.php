<?php
/**
 * تحديث هيكل قاعدة البيانات لإضافة الحقول المطلوبة
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>تحديث هيكل قاعدة البيانات...</h2>";
    
    // تحديث جدول transport_drivers
    echo "<h3>تحديث جدول السائقين...</h3>";
    
    // إضافة حقل نوع الرخصة
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'license_type'");
    $license_type_column = $db->single();
    
    if (!$license_type_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN license_type ENUM('private','public','commercial','heavy') DEFAULT 'private' AFTER license_number");
        $db->execute();
        echo "✅ تم إضافة حقل نوع الرخصة<br>";
    } else {
        echo "✅ حقل نوع الرخصة موجود بالفعل<br>";
    }
    
    // إضافة حقل تاريخ انتهاء الرخصة
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'license_expiry'");
    $license_expiry_column = $db->single();
    
    if (!$license_expiry_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN license_expiry DATE AFTER license_type");
        $db->execute();
        echo "✅ تم إضافة حقل تاريخ انتهاء الرخصة<br>";
    } else {
        echo "✅ حقل تاريخ انتهاء الرخصة موجود بالفعل<br>";
    }
    
    // إضافة حقل المحافظة
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'governorate'");
    $governorate_column = $db->single();
    
    if (!$governorate_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN governorate VARCHAR(100) AFTER address");
        $db->execute();
        echo "✅ تم إضافة حقل المحافظة<br>";
    } else {
        echo "✅ حقل المحافظة موجود بالفعل<br>";
    }
    
    // إضافة حقل المنطقة/المدينة
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'city'");
    $city_column = $db->single();
    
    if (!$city_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN city VARCHAR(100) AFTER governorate");
        $db->execute();
        echo "✅ تم إضافة حقل المدينة<br>";
    } else {
        echo "✅ حقل المدينة موجود بالفعل<br>";
    }
    
    // إضافة حقل المنطقة (شمال، وسط، جنوب)
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'region'");
    $region_column = $db->single();
    
    if (!$region_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN region ENUM('north','center','south') DEFAULT 'center' AFTER city");
        $db->execute();
        echo "✅ تم إضافة حقل المنطقة<br>";
    } else {
        echo "✅ حقل المنطقة موجود بالفعل<br>";
    }
    
    // تحديث جدول transport_trips لإضافة driver_id
    echo "<h3>تحديث جدول الرحلات...</h3>";
    
    $db->query("SHOW COLUMNS FROM transport_trips LIKE 'driver_id'");
    $driver_id_column = $db->single();
    
    if (!$driver_id_column) {
        $db->query("ALTER TABLE transport_trips ADD COLUMN driver_id INT AFTER vehicle_id");
        $db->execute();
        echo "✅ تم إضافة حقل السائق للرحلات<br>";
        
        // إضافة foreign key constraint
        $db->query("ALTER TABLE transport_trips ADD CONSTRAINT fk_trips_driver FOREIGN KEY (driver_id) REFERENCES transport_drivers(id) ON DELETE SET NULL");
        $db->execute();
        echo "✅ تم إضافة قيد المفتاح الخارجي للسائق<br>";
    } else {
        echo "✅ حقل السائق موجود بالفعل في جدول الرحلات<br>";
    }
    
    // تحديث البيانات الموجودة بقيم افتراضية
    echo "<h3>تحديث البيانات الموجودة...</h3>";
    
    $db->query("UPDATE transport_drivers SET governorate = 'غزة', city = 'غزة', region = 'center' WHERE governorate IS NULL OR governorate = ''");
    $db->execute();
    echo "✅ تم تحديث بيانات السائقين بقيم افتراضية<br>";
    
    // ربط الرحلات الموجودة بالسائقين من خلال المركبات
    $db->query("
        UPDATE transport_trips tt 
        JOIN transport_vehicles tv ON tt.vehicle_id = tv.id 
        SET tt.driver_id = tv.driver_id 
        WHERE tt.driver_id IS NULL AND tv.driver_id IS NOT NULL
    ");
    $db->execute();
    echo "✅ تم ربط الرحلات الموجودة بالسائقين<br>";
    
    echo "<h3 style='color: green;'>تم تحديث قاعدة البيانات بنجاح!</h3>";
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Database schema update error: " . $e->getMessage());
}
?>

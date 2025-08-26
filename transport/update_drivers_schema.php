<?php
/**
 * تحديث هيكل جدول السائقين لإضافة المحافظة والمنطقة
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>تحديث هيكل جدول السائقين...</h2>";
    
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
        $db->query("ALTER TABLE transport_drivers ADD COLUMN region ENUM('north','center','south') AFTER city");
        $db->execute();
        echo "✅ تم إضافة حقل المنطقة<br>";
    } else {
        echo "✅ حقل المنطقة موجود بالفعل<br>";
    }
    
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
    
    // تحديث البيانات الموجودة بقيم افتراضية
    $db->query("UPDATE transport_drivers SET governorate = 'غزة', city = 'غزة', region = 'center' WHERE governorate IS NULL OR governorate = ''");
    $db->execute();
    echo "✅ تم تحديث البيانات الموجودة بقيم افتراضية<br>";
    
    echo "<h3 style='color: green;'>تم تحديث هيكل جدول السائقين بنجاح!</h3>";
    echo "<a href='drivers.php'>العودة لصفحة السائقين</a>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Driver schema update error: " . $e->getMessage());
}
?>

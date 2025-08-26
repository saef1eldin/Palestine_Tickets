<?php
/**
 * سكربت لإنشاء صور تلقائية للسائقين
 */

require_once '../includes/init.php';

$db = new Database();

// مصفوفة أسماء الصور للرجال
$male_photos = [
    'driver1.jpg',
    'driver2.jpg', 
    'driver3.jpg',
    'driver4.jpg',
    'driver5.jpg',
    'driver6.jpg',
    'driver7.jpg',
    'driver8.jpg',
    'driver9.jpg',
    'driver10.jpg'
];

// إنشاء مجلد الصور إذا لم يكن موجود
$upload_dir = 'uploads/drivers/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    echo "تم إنشاء مجلد الصور: $upload_dir<br>";
}

try {
    // جلب جميع السائقين
    $db->query("SELECT id, name, photo FROM transport_drivers");
    $drivers = $db->resultSet();
    
    echo "<h2>تحديث صور السائقين...</h2>";
    
    foreach ($drivers as $index => $driver) {
        // اختيار صورة عشوائية من المصفوفة
        $random_photo = $male_photos[$index % count($male_photos)];
        $photo_path = 'uploads/drivers/' . $random_photo;
        
        // تحديث قاعدة البيانات
        $db->query("UPDATE transport_drivers SET photo = :photo WHERE id = :id");
        $db->bind(':photo', $photo_path);
        $db->bind(':id', $driver['id']);
        $db->execute();
        
        echo "✅ تم تحديث صورة السائق: {$driver['name']} → $photo_path<br>";
    }
    
    echo "<h3 style='color: green;'>تم تحديث صور جميع السائقين بنجاح!</h3>";
    
    // إنشاء ملف CSS للصور الافتراضية
    $css_content = "
/* صور السائقين الافتراضية */
.driver-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e5e7eb;
}

.driver-avatar-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.driver-avatar-placeholder {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2em;
}
";
    
    file_put_contents('assets/driver-avatars.css', $css_content);
    echo "✅ تم إنشاء ملف CSS للصور<br>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Driver photos error: " . $e->getMessage());
}

echo "<br><a href='dashboard.php'>العودة للوحة التحكم</a>";
?>

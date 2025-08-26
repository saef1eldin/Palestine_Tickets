<?php
/**
 * إعداد نظام الملفات الشخصية للإدمن
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>إعداد نظام الملفات الشخصية للإدمن...</h2>";
    
    // إنشاء جدول admin_profiles
    $db->query("
        CREATE TABLE IF NOT EXISTS admin_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            display_name VARCHAR(255) NOT NULL,
            profile_image VARCHAR(255) DEFAULT 'assets/default-admin.jpg',
            bio TEXT,
            phone VARCHAR(20),
            department VARCHAR(100) DEFAULT 'إدارة النقل',
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    $db->execute();
    echo "✅ تم إنشاء جدول admin_profiles بنجاح<br>";
    
    // التحقق من وجود بيانات في الجدول
    $db->query("SELECT COUNT(*) as count FROM admin_profiles");
    $count = $db->single()['count'];
    
    if ($count == 0) {
        echo "<h3>إضافة ملفات شخصية افتراضية...</h3>";
        
        // جلب المستخدمين الموجودين
        $db->query("SELECT * FROM users LIMIT 5");
        $users = $db->resultSet();
        
        if (empty($users)) {
            // إنشاء مستخدم إدمن افتراضي إذا لم يوجد
            $db->query("
                INSERT INTO users (name, email, password, role, is_active, created_at) 
                VALUES ('مدير النقل', 'transport@admin.com', :password, 'admin', 1, NOW())
            ");
            $db->bind(':password', password_hash('admin123', PASSWORD_DEFAULT));
            $db->execute();
            
            $user_id = $db->lastInsertId();
            echo "✅ تم إنشاء مستخدم إدمن افتراضي<br>";
            
            // إضافة ملف شخصي للمستخدم الجديد
            $db->query("
                INSERT INTO admin_profiles (user_id, display_name, profile_image, bio, department) 
                VALUES (:user_id, 'مدير النقل الرئيسي', 'assets/admin-1.jpg', 'مسؤول عن إدارة نظام النقل والمواصلات', 'إدارة النقل')
            ");
            $db->bind(':user_id', $user_id);
            $db->execute();
            echo "✅ تم إنشاء ملف شخصي افتراضي<br>";
        } else {
            // إضافة ملفات شخصية للمستخدمين الموجودين
            $default_profiles = [
                ['display_name' => 'مدير النقل الرئيسي', 'image' => 'assets/admin-1.jpg'],
                ['display_name' => 'مساعد مدير النقل', 'image' => 'assets/admin-2.jpg'],
                ['display_name' => 'مشرف الرحلات', 'image' => 'assets/admin-3.jpg'],
                ['display_name' => 'مشرف السائقين', 'image' => 'assets/admin-4.jpg'],
                ['display_name' => 'مدير الحجوزات', 'image' => 'assets/admin-5.jpg']
            ];
            
            foreach ($users as $index => $user) {
                // التحقق من عدم وجود ملف شخصي بالفعل
                $db->query("SELECT id FROM admin_profiles WHERE user_id = :user_id");
                $db->bind(':user_id', $user['id']);
                $existing = $db->single();
                
                if (!$existing) {
                    $profile = $default_profiles[$index] ?? $default_profiles[0];
                    
                    $db->query("
                        INSERT INTO admin_profiles (user_id, display_name, profile_image, department) 
                        VALUES (:user_id, :display_name, :profile_image, 'إدارة النقل')
                    ");
                    $db->bind(':user_id', $user['id']);
                    $db->bind(':display_name', $profile['display_name']);
                    $db->bind(':profile_image', $profile['image']);
                    $db->execute();
                    
                    echo "✅ تم إنشاء ملف شخصي للمستخدم: " . htmlspecialchars($user['name']) . "<br>";
                }
            }
        }
    } else {
        echo "✅ توجد ملفات شخصية بالفعل ($count ملف)<br>";
    }
    
    // إنشاء مجلد الصور إذا لم يكن موجوداً
    $assets_dir = 'assets';
    if (!is_dir($assets_dir)) {
        mkdir($assets_dir, 0755, true);
        echo "✅ تم إنشاء مجلد الصور<br>";
    }
    
    // نسخ الصورة الافتراضية الموجودة
    $existing_image = 'assets/12.jpg';
    $default_images = ['admin-1.jpg', 'admin-2.jpg', 'admin-3.jpg', 'admin-4.jpg', 'admin-5.jpg', 'default-admin.jpg'];
    
    foreach ($default_images as $image) {
        $image_path = $assets_dir . '/' . $image;
        if (!file_exists($image_path) && file_exists($existing_image)) {
            copy($existing_image, $image_path);
            echo "✅ تم نسخ صورة افتراضية: $image<br>";
        }
    }
    
    echo "<h3 style='color: green;'>تم إعداد نظام الملفات الشخصية بنجاح!</h3>";
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Admin profiles setup error: " . $e->getMessage());
}
?>

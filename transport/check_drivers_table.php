<?php
/**
 * فحص هيكل جدول السائقين وإصلاح المشاكل
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>فحص هيكل جدول السائقين...</h2>";
    
    // 1. عرض هيكل الجدول الحالي
    echo "<h3>1. هيكل الجدول الحالي:</h3>";
    $db->query("DESCRIBE transport_drivers");
    $columns = $db->resultSet();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>اسم العمود</th><th>النوع</th><th>NULL</th><th>المفتاح</th><th>القيمة الافتراضية</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table><br>";
    
    // 2. التحقق من الحقول المطلوبة
    echo "<h3>2. التحقق من الحقول المطلوبة:</h3>";
    
    $required_fields = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(255) NOT NULL',
        'phone' => 'VARCHAR(20) NOT NULL',
        'license_number' => 'VARCHAR(50) NOT NULL UNIQUE',
        'address' => 'TEXT',
        'status' => "ENUM('available', 'busy', 'offline') DEFAULT 'available'",
        'experience_years' => 'INT DEFAULT 0',
        'rating' => 'DECIMAL(3,2) DEFAULT 5.00',
        'is_active' => 'TINYINT(1) DEFAULT 1',
        'license_type' => "VARCHAR(50) DEFAULT 'رخصة خاصة'",
        'license_expiry' => 'DATE NULL',
        'governorate' => "VARCHAR(100) DEFAULT ''",
        'city' => "VARCHAR(100) DEFAULT ''",
        'region' => "VARCHAR(100) DEFAULT ''",
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    $existing_columns = array_column($columns, 'Field');
    
    foreach ($required_fields as $field => $definition) {
        if (in_array($field, $existing_columns)) {
            echo "✅ الحقل '$field' موجود<br>";
        } else {
            echo "❌ الحقل '$field' مفقود - سيتم إضافته<br>";
            
            try {
                $db->query("ALTER TABLE transport_drivers ADD COLUMN $field $definition");
                $db->execute();
                echo "✅ تم إضافة الحقل '$field' بنجاح<br>";
            } catch (Exception $e) {
                echo "❌ فشل في إضافة الحقل '$field': " . $e->getMessage() . "<br>";
            }
        }
    }
    
    // 3. اختبار إدراج بيانات تجريبية
    echo "<h3>3. اختبار إدراج بيانات تجريبية:</h3>";
    
    try {
        $test_data = [
            'name' => 'سائق تجريبي',
            'phone' => '0501234567',
            'license_number' => 'TEST' . time(),
            'address' => 'عنوان تجريبي',
            'status' => 'available',
            'experience_years' => 5,
            'is_active' => 1,
            'license_type' => 'رخصة خاصة',
            'governorate' => 'الرياض',
            'city' => 'الرياض',
            'region' => 'الوسط'
        ];
        
        $db->query("
            INSERT INTO transport_drivers (
                name, phone, license_number, address, status, experience_years,
                is_active, license_type, governorate, city, region, created_at
            ) VALUES (
                :name, :phone, :license_number, :address, :status, :experience_years,
                :is_active, :license_type, :governorate, :city, :region, NOW()
            )
        ");
        
        foreach ($test_data as $key => $value) {
            $db->bind(":$key", $value);
        }
        
        if ($db->execute()) {
            $test_id = $db->lastInsertId();
            echo "✅ تم إدراج البيانات التجريبية بنجاح (ID: $test_id)<br>";
            
            // حذف البيانات التجريبية
            $db->query("DELETE FROM transport_drivers WHERE id = :id");
            $db->bind(':id', $test_id);
            $db->execute();
            echo "✅ تم حذف البيانات التجريبية<br>";
        } else {
            echo "❌ فشل في إدراج البيانات التجريبية<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ خطأ في اختبار الإدراج: " . $e->getMessage() . "<br>";
    }
    
    // 4. فحص ملف drivers_actions.php
    echo "<h3>4. فحص ملف drivers_actions.php:</h3>";
    
    $actions_file = 'actions/drivers_actions.php';
    if (file_exists($actions_file)) {
        echo "✅ ملف drivers_actions.php موجود<br>";
        
        $file_content = file_get_contents($actions_file);
        if (strpos($file_content, 'case \'add\'') !== false) {
            echo "✅ حالة 'add' موجودة في الملف<br>";
        } else {
            echo "❌ حالة 'add' مفقودة في الملف<br>";
        }
        
        if (strpos($file_content, 'case \'update\'') !== false) {
            echo "✅ حالة 'update' موجودة في الملف<br>";
        } else {
            echo "❌ حالة 'update' مفقودة في الملف<br>";
        }
    } else {
        echo "❌ ملف drivers_actions.php مفقود<br>";
    }
    
    // 5. عرض البيانات الحالية
    echo "<h3>5. السائقين الموجودين حالياً:</h3>";
    $db->query("SELECT id, name, phone, license_number, status, is_active FROM transport_drivers ORDER BY id DESC LIMIT 10");
    $drivers = $db->resultSet();
    
    if (empty($drivers)) {
        echo "لا توجد بيانات سائقين<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>الاسم</th><th>الهاتف</th><th>رقم الرخصة</th><th>الحالة</th><th>نشط</th></tr>";
        foreach ($drivers as $driver) {
            echo "<tr>";
            echo "<td>{$driver['id']}</td>";
            echo "<td>{$driver['name']}</td>";
            echo "<td>{$driver['phone']}</td>";
            echo "<td>{$driver['license_number']}</td>";
            echo "<td>{$driver['status']}</td>";
            echo "<td>" . ($driver['is_active'] ? 'نعم' : 'لا') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3 style='color: green;'>✅ تم فحص الجدول بنجاح!</h3>";
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Check drivers table error: " . $e->getMessage());
}
?>

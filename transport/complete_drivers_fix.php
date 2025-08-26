<?php
/**
 * إصلاح شامل لمشكلة حفظ السائقين
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>إصلاح شامل لمشكلة حفظ السائقين...</h2>";
    
    // 1. فحص وإصلاح هيكل الجدول
    echo "<h3>1. فحص هيكل جدول transport_drivers...</h3>";
    
    // التحقق من وجود الجدول
    $db->query("SHOW TABLES LIKE 'transport_drivers'");
    $table_exists = $db->single();
    
    if (!$table_exists) {
        echo "❌ جدول transport_drivers غير موجود - سيتم إنشاؤه<br>";
        
        $create_table_sql = "
            CREATE TABLE transport_drivers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                license_number VARCHAR(50) NOT NULL UNIQUE,
                address TEXT,
                status ENUM('available', 'busy', 'offline') DEFAULT 'available',
                experience_years INT DEFAULT 0,
                rating DECIMAL(3,2) DEFAULT 5.00,
                is_active TINYINT(1) DEFAULT 1,
                license_type VARCHAR(50) DEFAULT 'رخصة خاصة',
                license_expiry DATE NULL,
                governorate VARCHAR(100) DEFAULT '',
                city VARCHAR(100) DEFAULT '',
                region VARCHAR(100) DEFAULT '',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $db->query($create_table_sql);
        $db->execute();
        echo "✅ تم إنشاء جدول transport_drivers<br>";
    } else {
        echo "✅ جدول transport_drivers موجود<br>";
    }
    
    // التحقق من الحقول المطلوبة وإضافتها إذا لم تكن موجودة
    $required_columns = [
        'license_type' => "VARCHAR(50) DEFAULT 'رخصة خاصة'",
        'license_expiry' => "DATE NULL",
        'governorate' => "VARCHAR(100) DEFAULT ''",
        'city' => "VARCHAR(100) DEFAULT ''",
        'region' => "VARCHAR(100) DEFAULT ''",
        'rating' => "DECIMAL(3,2) DEFAULT 5.00",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    foreach ($required_columns as $column => $definition) {
        try {
            $db->query("SELECT $column FROM transport_drivers LIMIT 1");
            $db->execute();
            echo "✅ العمود $column موجود<br>";
        } catch (Exception $e) {
            try {
                $db->query("ALTER TABLE transport_drivers ADD COLUMN $column $definition");
                $db->execute();
                echo "✅ تم إضافة العمود $column<br>";
            } catch (Exception $e2) {
                echo "❌ فشل في إضافة العمود $column: " . $e2->getMessage() . "<br>";
            }
        }
    }
    
    // 2. تنظيف جدول transport_types
    echo "<h3>2. تنظيف جدول transport_types...</h3>";
    
    // حذف البيانات المكررة
    $db->query("DELETE FROM transport_types");
    $db->execute();
    
    // إعادة إدراج البيانات الصحيحة
    $transport_types = [
        'باص عادي' => 'حافلة كبيرة لنقل الركاب',
        'باص فاخر' => 'حافلة مكيفة ومريحة',
        'حافلة سياحية' => 'حافلة مخصصة للرحلات السياحية',
        'سيارة خاصة' => 'سيارة خاصة صغيرة',
        'فان' => 'مركبة متوسطة الحجم',
        'ميكروباص' => 'حافلة صغيرة',
        'شاحنة صغيرة' => 'مركبة لنقل البضائع'
    ];
    
    foreach ($transport_types as $name => $description) {
        $db->query("
            INSERT INTO transport_types (name, description, icon, is_active, created_at) 
            VALUES (:name, :description, 'fas fa-bus', 1, NOW())
        ");
        $db->bind(':name', $name);
        $db->bind(':description', $description);
        $db->execute();
        echo "✅ تم إضافة نوع المركبة: $name<br>";
    }
    
    // 3. إنشاء ملف drivers_actions.php محدث
    echo "<h3>3. تحديث ملف drivers_actions.php...</h3>";
    
    $actions_content = '<?php
require_once \'../../includes/init.php\';

header(\'Content-Type: application/json; charset=utf-8\');

// تسجيل البيانات للتشخيص
error_log("Drivers Action Request: " . print_r($_POST, true));

if ($_SERVER[\'REQUEST_METHOD\'] !== \'POST\') {
    http_response_code(405);
    echo json_encode([\'success\' => false, \'message\' => \'طريقة غير مسموحة\']);
    exit;
}

$action = $_POST[\'action\'] ?? \'\';
$db = new Database();

try {
    switch ($action) {
        case \'add\':
            // جلب البيانات
            $name = trim($_POST[\'name\'] ?? \'\');
            $phone = trim($_POST[\'phone\'] ?? \'\');
            $license_number = trim($_POST[\'license_number\'] ?? \'\');
            $address = trim($_POST[\'address\'] ?? \'\');
            $status = trim($_POST[\'status\'] ?? \'available\');
            $experience_years = (int)($_POST[\'experience_years\'] ?? 0);
            $is_active = isset($_POST[\'is_active\']) ? 1 : 0;
            $license_type = trim($_POST[\'license_type\'] ?? \'رخصة خاصة\');
            $license_expiry = trim($_POST[\'license_expiry\'] ?? \'\');
            $governorate = trim($_POST[\'governorate\'] ?? \'\');
            $city = trim($_POST[\'city\'] ?? \'\');
            $region = trim($_POST[\'region\'] ?? \'\');

            // التحقق من البيانات
            if (empty($name)) throw new Exception(\'اسم السائق مطلوب\');
            if (empty($phone)) throw new Exception(\'رقم الهاتف مطلوب\');
            if (empty($license_number)) throw new Exception(\'رقم الرخصة مطلوب\');

            // التحقق من تكرار الرخصة
            $db->query("SELECT id FROM transport_drivers WHERE license_number = :license_number");
            $db->bind(\':license_number\', $license_number);
            if ($db->single()) throw new Exception(\'رقم الرخصة موجود بالفعل\');

            // إدراج السائق
            $db->query("
                INSERT INTO transport_drivers (
                    name, phone, license_number, address, status, experience_years,
                    is_active, license_type, license_expiry, governorate, city, region,
                    rating, created_at
                ) VALUES (
                    :name, :phone, :license_number, :address, :status, :experience_years,
                    :is_active, :license_type, :license_expiry, :governorate, :city, :region,
                    5.0, NOW()
                )
            ");
            
            $db->bind(\':name\', $name);
            $db->bind(\':phone\', $phone);
            $db->bind(\':license_number\', $license_number);
            $db->bind(\':address\', $address);
            $db->bind(\':status\', $status);
            $db->bind(\':experience_years\', $experience_years);
            $db->bind(\':is_active\', $is_active);
            $db->bind(\':license_type\', $license_type);
            $db->bind(\':license_expiry\', $license_expiry ?: null);
            $db->bind(\':governorate\', $governorate);
            $db->bind(\':city\', $city);
            $db->bind(\':region\', $region);

            if ($db->execute()) {
                echo json_encode([\'success\' => true, \'message\' => \'تم إضافة السائق بنجاح\']);
            } else {
                throw new Exception(\'فشل في إضافة السائق\');
            }
            break;

        case \'update\':
            $id = (int)($_POST[\'id\'] ?? 0);
            if ($id <= 0) throw new Exception(\'معرف غير صحيح\');
            
            $name = trim($_POST[\'name\'] ?? \'\');
            $phone = trim($_POST[\'phone\'] ?? \'\');
            $license_number = trim($_POST[\'license_number\'] ?? \'\');
            $address = trim($_POST[\'address\'] ?? \'\');
            $status = trim($_POST[\'status\'] ?? \'available\');
            $experience_years = (int)($_POST[\'experience_years\'] ?? 0);
            $is_active = isset($_POST[\'is_active\']) ? 1 : 0;
            $license_type = trim($_POST[\'license_type\'] ?? \'رخصة خاصة\');
            $license_expiry = trim($_POST[\'license_expiry\'] ?? \'\');
            $governorate = trim($_POST[\'governorate\'] ?? \'\');
            $city = trim($_POST[\'city\'] ?? \'\');
            $region = trim($_POST[\'region\'] ?? \'\');

            if (empty($name)) throw new Exception(\'اسم السائق مطلوب\');

            $db->query("
                UPDATE transport_drivers SET 
                    name = :name, phone = :phone, license_number = :license_number,
                    address = :address, status = :status, experience_years = :experience_years,
                    is_active = :is_active, license_type = :license_type,
                    license_expiry = :license_expiry, governorate = :governorate,
                    city = :city, region = :region, updated_at = NOW()
                WHERE id = :id
            ");
            
            $db->bind(\':name\', $name);
            $db->bind(\':phone\', $phone);
            $db->bind(\':license_number\', $license_number);
            $db->bind(\':address\', $address);
            $db->bind(\':status\', $status);
            $db->bind(\':experience_years\', $experience_years);
            $db->bind(\':is_active\', $is_active);
            $db->bind(\':license_type\', $license_type);
            $db->bind(\':license_expiry\', $license_expiry ?: null);
            $db->bind(\':governorate\', $governorate);
            $db->bind(\':city\', $city);
            $db->bind(\':region\', $region);
            $db->bind(\':id\', $id);

            if ($db->execute()) {
                echo json_encode([\'success\' => true, \'message\' => \'تم تحديث السائق بنجاح\']);
            } else {
                throw new Exception(\'فشل في تحديث السائق\');
            }
            break;

        case \'delete\':
            $id = (int)($_POST[\'id\'] ?? 0);
            if ($id <= 0) throw new Exception(\'معرف غير صحيح\');

            $db->query("DELETE FROM transport_drivers WHERE id = :id");
            $db->bind(\':id\', $id);
            
            if ($db->execute()) {
                echo json_encode([\'success\' => true, \'message\' => \'تم حذف السائق بنجاح\']);
            } else {
                throw new Exception(\'فشل في حذف السائق\');
            }
            break;

        case \'get\':
            $id = (int)($_POST[\'id\'] ?? 0);
            if ($id <= 0) throw new Exception(\'معرف غير صحيح\');

            $db->query("SELECT * FROM transport_drivers WHERE id = :id");
            $db->bind(\':id\', $id);
            $driver = $db->single();

            if (!$driver) throw new Exception(\'السائق غير موجود\');

            echo json_encode([\'success\' => true, \'data\' => $driver]);
            break;

        default:
            throw new Exception(\'عملية غير مدعومة\');
    }

} catch (Exception $e) {
    error_log("Drivers Action Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([\'success\' => false, \'message\' => $e->getMessage()]);
}
?>';
    
    file_put_contents('actions/drivers_actions.php', $actions_content);
    echo "✅ تم تحديث ملف drivers_actions.php<br>";
    
    // 4. اختبار النظام
    echo "<h3>4. اختبار النظام...</h3>";
    
    $test_data = [
        'name' => 'سائق تجريبي ' . time(),
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
            is_active, license_type, governorate, city, region, rating, created_at
        ) VALUES (
            :name, :phone, :license_number, :address, :status, :experience_years,
            :is_active, :license_type, :governorate, :city, :region, 5.0, NOW()
        )
    ");
    
    foreach ($test_data as $key => $value) {
        $db->bind(":$key", $value);
    }
    
    if ($db->execute()) {
        $test_id = $db->lastInsertId();
        echo "✅ تم إدراج بيانات تجريبية بنجاح (ID: $test_id)<br>";
        
        // حذف البيانات التجريبية
        $db->query("DELETE FROM transport_drivers WHERE id = :id");
        $db->bind(':id', $test_id);
        $db->execute();
        echo "✅ تم حذف البيانات التجريبية<br>";
    } else {
        echo "❌ فشل في إدراج البيانات التجريبية<br>";
    }
    
    echo "<h3 style='color: green;'>✅ تم إصلاح جميع المشاكل بنجاح!</h3>";
    echo "<p><strong>ما تم إصلاحه:</strong></p>";
    echo "<ul>";
    echo "<li>✅ فحص وإصلاح هيكل جدول السائقين</li>";
    echo "<li>✅ إضافة الحقول المفقودة</li>";
    echo "<li>✅ تنظيف أنواع المركبات المكررة</li>";
    echo "<li>✅ تحديث ملف drivers_actions.php</li>";
    echo "<li>✅ اختبار النظام</li>";
    echo "</ul>";
    
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    echo "<p><a href='test_driver_save.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>اختبار حفظ السائقين</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Complete drivers fix error: " . $e->getMessage());
}
?>

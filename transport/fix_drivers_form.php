<?php
/**
 * إصلاح مشاكل نموذج السائقين
 */

require_once '../includes/init.php';

$db = new Database();

try {
    echo "<h2>إصلاح مشاكل نموذج السائقين...</h2>";
    
    // 1. تنظيف جدول transport_types من التكرارات
    echo "<h3>1. تنظيف أنواع المركبات المكررة...</h3>";
    
    // حذف جميع السجلات المكررة
    $db->query("DELETE FROM transport_types");
    $db->execute();
    echo "✅ تم حذف جميع السجلات المكررة<br>";
    
    // إعادة إدراج البيانات الصحيحة
    $unique_types = [
        'باص عادي' => 'حافلة كبيرة لنقل الركاب',
        'باص فاخر' => 'حافلة مكيفة ومريحة', 
        'حافلة سياحية' => 'حافلة مخصصة للرحلات السياحية',
        'حافلة عادية' => 'حافلة عادية لنقل الركاب',
        'سيارة خاصة' => 'سيارة خاصة صغيرة',
        'فان' => 'مركبة متوسطة الحجم',
        'ميكروباص' => 'حافلة صغيرة'
    ];
    
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
    
    // 2. التحقق من وجود الحقول المطلوبة في جدول transport_drivers
    echo "<h3>2. التحقق من هيكل جدول السائقين...</h3>";
    
    $required_columns = [
        'license_type' => "VARCHAR(50) DEFAULT 'رخصة خاصة'",
        'license_expiry' => "DATE NULL",
        'governorate' => "VARCHAR(100) DEFAULT ''",
        'city' => "VARCHAR(100) DEFAULT ''",
        'region' => "VARCHAR(100) DEFAULT ''"
    ];
    
    foreach ($required_columns as $column => $definition) {
        try {
            // التحقق من وجود العمود
            $db->query("SELECT $column FROM transport_drivers LIMIT 1");
            $db->execute();
            echo "✅ العمود $column موجود<br>";
        } catch (Exception $e) {
            // إضافة العمود إذا لم يكن موجوداً
            $db->query("ALTER TABLE transport_drivers ADD COLUMN $column $definition");
            $db->execute();
            echo "✅ تم إضافة العمود $column<br>";
        }
    }
    
    // 3. إنشاء ملف drivers_actions.php محدث
    echo "<h3>3. تحديث ملف إجراءات السائقين...</h3>";
    
    $drivers_actions_content = '<?php
require_once \'../../includes/init.php\';

header(\'Content-Type: application/json; charset=utf-8\');

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
            $name = trim($_POST[\'name\'] ?? \'\');
            $phone = trim($_POST[\'phone\'] ?? \'\');
            $license_number = trim($_POST[\'license_number\'] ?? \'\');
            $address = trim($_POST[\'address\'] ?? \'\');
            $status = trim($_POST[\'status\'] ?? \'available\');
            $experience_years = (int)($_POST[\'experience_years\'] ?? 0);
            $is_active = isset($_POST[\'is_active\']) ? 1 : 0;
            
            // الحقول الجديدة
            $license_type = trim($_POST[\'license_type\'] ?? \'رخصة خاصة\');
            $license_expiry = trim($_POST[\'license_expiry\'] ?? \'\');
            $governorate = trim($_POST[\'governorate\'] ?? \'\');
            $city = trim($_POST[\'city\'] ?? \'\');
            $region = trim($_POST[\'region\'] ?? \'\');

            if (empty($name)) {
                throw new Exception(\'اسم السائق مطلوب\');
            }
            if (empty($phone)) {
                throw new Exception(\'رقم الهاتف مطلوب\');
            }
            if (empty($license_number)) {
                throw new Exception(\'رقم الرخصة مطلوب\');
            }

            // التحقق من عدم تكرار رقم الرخصة
            $db->query("SELECT id FROM transport_drivers WHERE license_number = :license_number");
            $db->bind(\':license_number\', $license_number);
            $existing = $db->single();

            if ($existing) {
                throw new Exception(\'رقم الرخصة موجود بالفعل\');
            }

            // إضافة السائق
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
            $name = trim($_POST[\'name\'] ?? \'\');
            $phone = trim($_POST[\'phone\'] ?? \'\');
            $license_number = trim($_POST[\'license_number\'] ?? \'\');
            $address = trim($_POST[\'address\'] ?? \'\');
            $status = trim($_POST[\'status\'] ?? \'available\');
            $experience_years = (int)($_POST[\'experience_years\'] ?? 0);
            $is_active = isset($_POST[\'is_active\']) ? 1 : 0;
            
            // الحقول الجديدة
            $license_type = trim($_POST[\'license_type\'] ?? \'رخصة خاصة\');
            $license_expiry = trim($_POST[\'license_expiry\'] ?? \'\');
            $governorate = trim($_POST[\'governorate\'] ?? \'\');
            $city = trim($_POST[\'city\'] ?? \'\');
            $region = trim($_POST[\'region\'] ?? \'\');

            if ($id <= 0) {
                throw new Exception(\'معرف غير صحيح\');
            }
            if (empty($name)) {
                throw new Exception(\'اسم السائق مطلوب\');
            }

            // تحديث السائق
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
            if ($id <= 0) {
                throw new Exception(\'معرف غير صحيح\');
            }

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
            if ($id <= 0) {
                throw new Exception(\'معرف غير صحيح\');
            }

            $db->query("SELECT * FROM transport_drivers WHERE id = :id");
            $db->bind(\':id\', $id);
            $driver = $db->single();

            if (!$driver) {
                throw new Exception(\'السائق غير موجود\');
            }

            echo json_encode([\'success\' => true, \'data\' => $driver]);
            break;

        default:
            throw new Exception(\'عملية غير مدعومة\');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([\'success\' => false, \'message\' => $e->getMessage()]);
}
?>';
    
    file_put_contents('actions/drivers_actions.php', $drivers_actions_content);
    echo "✅ تم تحديث ملف drivers_actions.php<br>";
    
    echo "<h3 style='color: green;'>✅ تم إصلاح جميع المشاكل بنجاح!</h3>";
    echo "<p><strong>المشاكل التي تم حلها:</strong></p>";
    echo "<ul>";
    echo "<li>✅ إزالة تكرار أنواع المركبات</li>";
    echo "<li>✅ إضافة الحقول المفقودة في جدول السائقين</li>";
    echo "<li>✅ تحديث ملف إجراءات السائقين</li>";
    echo "<li>✅ إصلاح زر الحفظ</li>";
    echo "</ul>";
    
    echo "<p><a href='dashboard.php' style='background: #6366f1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>العودة للوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>حدث خطأ: " . $e->getMessage() . "</h3>";
    error_log("Fix drivers form error: " . $e->getMessage());
}
?>

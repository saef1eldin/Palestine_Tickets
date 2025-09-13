<?php
require_once '../../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

// تسجيل البيانات للتشخيص
error_log("Drivers Action Request: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = new Database();

try {
    switch ($action) {
        case 'add':
            // جلب البيانات
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $status = trim($_POST['status'] ?? 'available');
            $experience_years = (int)($_POST['experience_years'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $license_type = trim($_POST['license_type'] ?? 'رخصة خاصة');
            $license_expiry = trim($_POST['license_expiry'] ?? '');
            $governorate = trim($_POST['governorate'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');

            // التحقق من البيانات
            if (empty($name)) throw new Exception('اسم السائق مطلوب');
            if (empty($phone)) throw new Exception('رقم الهاتف مطلوب');
            if (empty($license_number)) throw new Exception('رقم الرخصة مطلوب');

            // التحقق من تكرار الرخصة
            $db->query("SELECT id FROM transport_drivers WHERE license_number = :license_number");
            $db->bind(':license_number', $license_number);
            if ($db->single()) throw new Exception('رقم الرخصة موجود بالفعل');

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
            
            $db->bind(':name', $name);
            $db->bind(':phone', $phone);
            $db->bind(':license_number', $license_number);
            $db->bind(':address', $address);
            $db->bind(':status', $status);
            $db->bind(':experience_years', $experience_years);
            $db->bind(':is_active', $is_active);
            $db->bind(':license_type', $license_type);
            $db->bind(':license_expiry', $license_expiry ?: null);
            $db->bind(':governorate', $governorate);
            $db->bind(':city', $city);
            $db->bind(':region', $region);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة السائق بنجاح']);
            } else {
                throw new Exception('فشل في إضافة السائق');
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صحيح');
            
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $status = trim($_POST['status'] ?? 'available');
            $experience_years = (int)($_POST['experience_years'] ?? 0);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $license_type = trim($_POST['license_type'] ?? 'رخصة خاصة');
            $license_expiry = trim($_POST['license_expiry'] ?? '');
            $governorate = trim($_POST['governorate'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $region = trim($_POST['region'] ?? '');

            if (empty($name)) throw new Exception('اسم السائق مطلوب');

            $db->query("
                UPDATE transport_drivers SET 
                    name = :name, phone = :phone, license_number = :license_number,
                    address = :address, status = :status, experience_years = :experience_years,
                    is_active = :is_active, license_type = :license_type,
                    license_expiry = :license_expiry, governorate = :governorate,
                    city = :city, region = :region, updated_at = NOW()
                WHERE id = :id
            ");
            
            $db->bind(':name', $name);
            $db->bind(':phone', $phone);
            $db->bind(':license_number', $license_number);
            $db->bind(':address', $address);
            $db->bind(':status', $status);
            $db->bind(':experience_years', $experience_years);
            $db->bind(':is_active', $is_active);
            $db->bind(':license_type', $license_type);
            $db->bind(':license_expiry', $license_expiry ?: null);
            $db->bind(':governorate', $governorate);
            $db->bind(':city', $city);
            $db->bind(':region', $region);
            $db->bind(':id', $id);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث السائق بنجاح']);
            } else {
                throw new Exception('فشل في تحديث السائق');
            }
            break;

        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صحيح');

            $db->query("DELETE FROM transport_drivers WHERE id = :id");
            $db->bind(':id', $id);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم حذف السائق بنجاح']);
            } else {
                throw new Exception('فشل في حذف السائق');
            }
            break;

        case 'get':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صحيح');

            $db->query("SELECT * FROM transport_drivers WHERE id = :id");
            $db->bind(':id', $id);
            $driver = $db->single();

            if (!$driver) throw new Exception('السائق غير موجود');

            echo json_encode(['success' => true, 'data' => $driver]);
            break;

        case 'add_vehicle':
            $driver_id = (int)($_POST['driver_id'] ?? 0);
            $transport_type_id = (int)($_POST['transport_type_id'] ?? 0);
            $plate_number = trim($_POST['plate_number'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $year = (int)($_POST['year'] ?? 0);
            $capacity = (int)($_POST['capacity'] ?? 0);
            $color = trim($_POST['color'] ?? '');

            if ($driver_id <= 0) throw new Exception('معرف السائق غير صحيح');
            if (empty($plate_number)) throw new Exception('رقم اللوحة مطلوب');

            // تحقق من وجود السائق
            $db->query("SELECT id FROM transport_drivers WHERE id = :id");
            $db->bind(':id', $driver_id);
            if (!$db->single()) throw new Exception('السائق غير موجود');

            $db->query("INSERT INTO transport_vehicles (driver_id, transport_type_id, plate_number, model, year, capacity, color, created_at) VALUES (:driver_id, :transport_type_id, :plate_number, :model, :year, :capacity, :color, NOW())");
            $db->bind(':driver_id', $driver_id);
            $db->bind(':transport_type_id', $transport_type_id ?: null);
            $db->bind(':plate_number', $plate_number);
            $db->bind(':model', $model);
            $db->bind(':year', $year ?: null);
            $db->bind(':capacity', $capacity ?: null);
            $db->bind(':color', $color);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة المركبة بنجاح']);
            } else {
                throw new Exception('فشل في إضافة المركبة');
            }
            break;

        default:
            throw new Exception('عملية غير مدعومة');
    }

} catch (Exception $e) {
    error_log("Drivers Action Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
<?php
require_once '../../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'طريقة غير مسموحة']);
    exit;
}

$action = $_POST['action'] ?? '';
$db = new Database();

try {
    switch ($action) {
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }

            // جلب معلومات نقطة الانطلاق قبل الحذف
            $db->query("SELECT name FROM transport_starting_points WHERE id = :id");
            $db->bind(':id', $id);
            $point_info = $db->single();

            if (!$point_info) {
                throw new Exception('نقطة الانطلاق غير موجودة');
            }

            // جلب الرحلات المرتبطة بهذه النقطة
            $db->query("SELECT COUNT(*) as count FROM transport_trips WHERE starting_point_id = :id");
            $db->bind(':id', $id);
            $trips_result = $db->single();
            $trips_count = $trips_result['count'];

            // جلب الحجوزات المرتبطة بالرحلات
            $db->query("
                SELECT COUNT(*) as count
                FROM transport_bookings tb
                INNER JOIN transport_trips tt ON tb.trip_id = tt.id
                WHERE tt.starting_point_id = :id
            ");
            $db->bind(':id', $id);
            $bookings_result = $db->single();
            $bookings_count = $bookings_result['count'];

            // حذف الحجوزات المرتبطة أولاً
            if ($bookings_count > 0) {
                $db->query("
                    DELETE tb FROM transport_bookings tb
                    INNER JOIN transport_trips tt ON tb.trip_id = tt.id
                    WHERE tt.starting_point_id = :id
                ");
                $db->bind(':id', $id);
                $db->execute();
            }

            // حذف الرحلات المرتبطة
            if ($trips_count > 0) {
                $db->query("DELETE FROM transport_trips WHERE starting_point_id = :id");
                $db->bind(':id', $id);
                $db->execute();
            }

            // حذف نقطة الانطلاق
            $db->query("DELETE FROM transport_starting_points WHERE id = :id");
            $db->bind(':id', $id);

            if ($db->execute()) {
                $message = "تم حذف نقطة الانطلاق '{$point_info['name']}' بنجاح";
                if ($trips_count > 0) {
                    $message .= " مع {$trips_count} رحلة مرتبطة";
                }
                if ($bookings_count > 0) {
                    $message .= " و {$bookings_count} حجز";
                }

                echo json_encode(['success' => true, 'message' => $message]);
            } else {
                throw new Exception('فشل في حذف نقطة الانطلاق');
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $region = trim($_POST['region'] ?? 'center'); // قيمة افتراضية
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }
            if (empty($name)) {
                throw new Exception('اسم نقطة الانطلاق مطلوب');
            }

            // التحقق من عدم تكرار الاسم
            $db->query("SELECT id FROM transport_starting_points WHERE name = :name AND id != :id");
            $db->bind(':name', $name);
            $db->bind(':id', $id);
            $existing = $db->single();
            
            if ($existing) {
                throw new Exception('اسم نقطة الانطلاق موجود بالفعل');
            }

            // تحديث نقطة الانطلاق
            $db->query("
                UPDATE transport_starting_points 
                SET name = :name, description = :description, region = :region, is_active = :is_active, updated_at = NOW()
                WHERE id = :id
            ");
            $db->bind(':name', $name);
            $db->bind(':description', $description);
            $db->bind(':region', $region);
            $db->bind(':is_active', $is_active);
            $db->bind(':id', $id);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث نقطة الانطلاق بنجاح']);
            } else {
                throw new Exception('فشل في تحديث نقطة الانطلاق');
            }
            break;

        case 'add':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $region = trim($_POST['region'] ?? 'center'); // قيمة افتراضية
            $icon = trim($_POST['icon'] ?? 'map-marker-alt');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name)) {
                throw new Exception('اسم نقطة الانطلاق مطلوب');
            }

            // التحقق من عدم تكرار الاسم
            $db->query("SELECT id FROM transport_starting_points WHERE name = :name");
            $db->bind(':name', $name);
            $existing = $db->single();
            
            if ($existing) {
                throw new Exception('اسم نقطة الانطلاق موجود بالفعل');
            }

            // إضافة نقطة انطلاق جديدة
            $db->query("
                INSERT INTO transport_starting_points (name, description, region, icon, is_active, created_at, updated_at)
                VALUES (:name, :description, :region, :icon, :is_active, NOW(), NOW())
            ");
            $db->bind(':name', $name);
            $db->bind(':description', $description);
            $db->bind(':region', $region);
            $db->bind(':icon', $icon);
            $db->bind(':is_active', $is_active);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة نقطة الانطلاق بنجاح']);
            } else {
                throw new Exception('فشل في إضافة نقطة الانطلاق');
            }
            break;

        case 'get':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }

            $db->query("SELECT * FROM transport_starting_points WHERE id = :id");
            $db->bind(':id', $id);
            $point = $db->single();

            if (!$point) {
                throw new Exception('نقطة الانطلاق غير موجودة');
            }

            echo json_encode(['success' => true, 'data' => $point]);
            break;

        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            $new_status = $_POST['new_status'] === 'true' ? 1 : 0;

            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }

            // تحديث حالة نقطة الانطلاق
            $db->query("UPDATE transport_starting_points SET is_active = :status, updated_at = NOW() WHERE id = :id");
            $db->bind(':status', $new_status);
            $db->bind(':id', $id);

            if ($db->execute()) {
                $trips_affected = 0;

                if ($new_status == 0) {
                    // إذا تم إلغاء تنشيط نقطة الانطلاق، قم بإلغاء تنشيط جميع الرحلات المرتبطة بها
                    $db->query("SELECT COUNT(*) as count FROM transport_trips WHERE starting_point_id = :id AND is_active = 1");
                    $db->bind(':id', $id);
                    $trips_affected = $db->single()['count'];

                    $db->query("UPDATE transport_trips SET is_active = 0, updated_at = NOW() WHERE starting_point_id = :id");
                    $db->bind(':id', $id);
                    $db->execute();
                } else {
                    // إذا تم تنشيط نقطة الانطلاق، قم بتنشيط جميع الرحلات المرتبطة بها
                    $db->query("SELECT COUNT(*) as count FROM transport_trips WHERE starting_point_id = :id AND is_active = 0");
                    $db->bind(':id', $id);
                    $trips_affected = $db->single()['count'];

                    $db->query("UPDATE transport_trips SET is_active = 1, updated_at = NOW() WHERE starting_point_id = :id");
                    $db->bind(':id', $id);
                    $db->execute();
                }

                $status_text = $new_status ? 'نشط' : 'غير نشط';
                $action_text = $new_status ? 'تنشيط' : 'إلغاء تنشيط';
                $additional_message = $trips_affected > 0 ? " وتم {$action_text} {$trips_affected} رحلة مرتبطة" : '';

                echo json_encode(['success' => true, 'message' => 'تم تغيير حالة نقطة الانطلاق إلى: ' . $status_text . $additional_message]);
            } else {
                throw new Exception('فشل في تغيير حالة نقطة الانطلاق');
            }
            break;

        default:
            throw new Exception('عملية غير مدعومة');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

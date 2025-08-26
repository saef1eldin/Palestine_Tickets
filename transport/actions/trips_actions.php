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

            // التحقق من وجود حجوزات نشطة على الرحلة (استثناء الحجوزات المنتهية)
            $db->query("
                SELECT COUNT(*) as count
                FROM transport_bookings tb
                LEFT JOIN events e ON tb.event_id = e.id
                WHERE tb.trip_id = :id
                AND tb.status NOT IN ('expired', 'cancelled')
                AND (e.status != 'expired' OR e.status IS NULL)
                AND (e.date_time > NOW() OR e.date_time IS NULL)
            ");
            $db->bind(':id', $id);
            $result = $db->single();

            if ($result['count'] > 0) {
                throw new Exception('لا يمكن حذف الرحلة لأنها تحتوي على حجوزات نشطة. يمكن حذف الرحلات التي تحتوي على حجوزات منتهية فقط.');
            }

            // حذف الرحلة
            $db->query("DELETE FROM transport_trips WHERE id = :id");
            $db->bind(':id', $id);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الرحلة بنجاح']);
            } else {
                throw new Exception('فشل في حذف الرحلة');
            }
            break;

        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $event_id = (int)($_POST['event_id'] ?? 0);
            $starting_point_id = (int)($_POST['starting_point_id'] ?? 0);
            $driver_id = !empty($_POST['driver_id']) ? (int)$_POST['driver_id'] : null;

            // جلب vehicle_id و transport_type_id من driver_id
            $vehicle_id = null;
            $transport_type_id = null;
            if ($driver_id) {
                $db->query("SELECT v.id, v.transport_type_id FROM transport_vehicles v WHERE v.driver_id = :driver_id LIMIT 1");
                $db->bind(':driver_id', $driver_id);
                $vehicle_result = $db->single();
                if ($vehicle_result) {
                    $vehicle_id = $vehicle_result['id'];
                    $transport_type_id = $vehicle_result['transport_type_id'];
                }
            }
            $departure_time = trim($_POST['departure_time'] ?? '');
            $arrival_time = trim($_POST['arrival_time'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $total_seats = (int)($_POST['total_seats'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }
            if ($event_id <= 0) {
                throw new Exception('الفعالية مطلوبة');
            }
            if ($starting_point_id <= 0) {
                throw new Exception('نقطة الانطلاق مطلوبة');
            }
            if ($driver_id && !$transport_type_id) {
                throw new Exception('لا يمكن العثور على نوع المركبة للسائق المحدد');
            }
            if (empty($departure_time)) {
                throw new Exception('وقت المغادرة مطلوب');
            }
            if ($price <= 0) {
                throw new Exception('السعر يجب أن يكون أكبر من صفر');
            }
            if ($total_seats <= 0) {
                throw new Exception('عدد المقاعد يجب أن يكون أكبر من صفر');
            }

            // الحصول على عدد المقاعد المحجوزة
            $db->query("SELECT COALESCE(SUM(passengers_count), 0) as booked_seats FROM transport_bookings WHERE trip_id = :id AND status != 'cancelled'");
            $db->bind(':id', $id);
            $booked_result = $db->single();
            $booked_seats = $booked_result['booked_seats'] ?? 0;
            
            $available_seats = $total_seats - $booked_seats;
            if ($available_seats < 0) {
                throw new Exception('عدد المقاعد الجديد أقل من المقاعد المحجوزة بالفعل (' . $booked_seats . ')');
            }

            // تحديث الرحلة
            $db->query("
                UPDATE transport_trips
                SET event_id = :event_id, starting_point_id = :starting_point_id,
                    transport_type_id = :transport_type_id, driver_id = :driver_id, vehicle_id = :vehicle_id,
                    departure_time = :departure_time, arrival_time = :arrival_time,
                    price = :price, total_seats = :total_seats,
                    available_seats = :available_seats, description = :description,
                    is_active = :is_active, updated_at = NOW()
                WHERE id = :id
            ");
            $db->bind(':event_id', $event_id);
            $db->bind(':starting_point_id', $starting_point_id);
            $db->bind(':transport_type_id', $transport_type_id);
            $db->bind(':driver_id', $driver_id);
            $db->bind(':vehicle_id', $vehicle_id);
            $db->bind(':departure_time', $departure_time);
            $db->bind(':arrival_time', $arrival_time);
            $db->bind(':price', $price);
            $db->bind(':total_seats', $total_seats);
            $db->bind(':available_seats', $available_seats);
            $db->bind(':description', $description);
            $db->bind(':is_active', $is_active);
            $db->bind(':id', $id);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الرحلة بنجاح']);
            } else {
                throw new Exception('فشل في تحديث الرحلة');
            }
            break;

        case 'add':
            $event_id = (int)($_POST['event_id'] ?? 0);
            $starting_point_id = (int)($_POST['starting_point_id'] ?? 0);
            $driver_id = !empty($_POST['driver_id']) ? (int)$_POST['driver_id'] : null;

            // جلب vehicle_id و transport_type_id و capacity من driver_id
            $vehicle_id = null;
            $transport_type_id = null;
            $vehicle_capacity = 0;
            if ($driver_id) {
                $db->query("SELECT v.id, v.transport_type_id, v.capacity FROM transport_vehicles v WHERE v.driver_id = :driver_id LIMIT 1");
                $db->bind(':driver_id', $driver_id);
                $vehicle_result = $db->single();
                if ($vehicle_result) {
                    $vehicle_id = $vehicle_result['id'];
                    $transport_type_id = $vehicle_result['transport_type_id'];
                    $vehicle_capacity = $vehicle_result['capacity'];
                }
            }

            $departure_time = trim($_POST['departure_time'] ?? '');
            $arrival_time = trim($_POST['arrival_time'] ?? '');
            $price = (float)($_POST['price'] ?? 0);
            $total_seats = (int)($_POST['total_seats'] ?? $vehicle_capacity); // استخدام سعة المركبة
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            // التحققات الأساسية
            if ($event_id <= 0) {
                throw new Exception('الفعالية مطلوبة');
            }
            if ($starting_point_id <= 0) {
                throw new Exception('نقطة الانطلاق مطلوبة');
            }
            if (!$driver_id) {
                throw new Exception('السائق مطلوب - لا يمكن إنشاء رحلة بدون سائق');
            }
            if (!$transport_type_id) {
                throw new Exception('لا يمكن العثور على نوع المركبة للسائق المحدد');
            }
            if (empty($departure_time)) {
                throw new Exception('وقت المغادرة مطلوب');
            }

            // التحقق من أن الرحلة ليست في الماضي
            $departure_datetime = new DateTime($departure_time);
            $now = new DateTime();
            if ($departure_datetime <= $now) {
                throw new Exception('لا يمكن إنشاء رحلة في الماضي');
            }

            // التحقق من وقت الوصول إذا تم إدخاله
            if (!empty($arrival_time)) {
                $departure_time_only = date('H:i:s', strtotime($departure_time));
                $arrival_time_formatted = $arrival_time . ':00';

                if ($arrival_time_formatted <= $departure_time_only) {
                    throw new Exception('وقت الوصول يجب أن يكون بعد وقت المغادرة');
                }
            }

            if ($price <= 0) {
                throw new Exception('السعر يجب أن يكون أكبر من صفر');
            }
            if ($total_seats <= 0) {
                throw new Exception('عدد المقاعد يجب أن يكون أكبر من صفر');
            }

            // إضافة رحلة جديدة
            $db->query("
                INSERT INTO transport_trips (event_id, starting_point_id, transport_type_id, driver_id, vehicle_id,
                                           departure_time, arrival_time, price, total_seats,
                                           available_seats, description, is_active, created_at, updated_at)
                VALUES (:event_id, :starting_point_id, :transport_type_id, :driver_id, :vehicle_id,
                        :departure_time, :arrival_time, :price, :total_seats,
                        :available_seats, :description, :is_active, NOW(), NOW())
            ");
            $db->bind(':event_id', $event_id);
            $db->bind(':starting_point_id', $starting_point_id);
            $db->bind(':transport_type_id', $transport_type_id);
            $db->bind(':driver_id', $driver_id);
            $db->bind(':vehicle_id', $vehicle_id);
            $db->bind(':departure_time', $departure_time);
            $db->bind(':arrival_time', $arrival_time);
            $db->bind(':price', $price);
            $db->bind(':total_seats', $total_seats);
            $db->bind(':available_seats', $total_seats); // جميع المقاعد متاحة في البداية
            $db->bind(':description', $description);
            $db->bind(':is_active', $is_active);
            
            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم إضافة الرحلة بنجاح']);
            } else {
                throw new Exception('فشل في إضافة الرحلة');
            }
            break;

        case 'get':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }

            $db->query("
                SELECT
                    tt.*,
                    tsp.name as starting_point_name,
                    tty.name as transport_type_name,
                    e.title as event_title,
                    d.name as driver_name
                FROM transport_trips tt
                LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
                LEFT JOIN transport_types tty ON tt.transport_type_id = tty.id
                LEFT JOIN events e ON tt.event_id = e.id
                LEFT JOIN transport_drivers d ON tt.driver_id = d.id
                WHERE tt.id = :id
            ");
            $db->bind(':id', $id);
            $trip = $db->single();
            
            if (!$trip) {
                throw new Exception('الرحلة غير موجودة');
            }

            echo json_encode(['success' => true, 'data' => $trip]);
            break;

        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            $new_status = $_POST['new_status'] === 'true' ? 1 : 0;

            if ($id <= 0) {
                throw new Exception('معرف غير صحيح');
            }

            // تحديث حالة الرحلة
            $db->query("UPDATE transport_trips SET is_active = :status, updated_at = NOW() WHERE id = :id");
            $db->bind(':status', $new_status);
            $db->bind(':id', $id);

            if ($db->execute()) {
                $status_text = $new_status ? 'نشط' : 'غير نشط';
                echo json_encode(['success' => true, 'message' => 'تم تغيير حالة الرحلة إلى: ' . $status_text]);
            } else {
                throw new Exception('فشل في تغيير حالة الرحلة');
            }
            break;

        case 'get_inactive':
            // إعدادات التصفح
            $page = max(1, (int)($_POST['page'] ?? 1));
            $limit = max(1, min(50, (int)($_POST['limit'] ?? 5))); // حد أقصى 50 عنصر
            $offset = ($page - 1) * $limit;

            // جلب العدد الإجمالي للرحلات غير النشطة
            $db->query("SELECT COUNT(*) as total FROM transport_trips WHERE is_active = 0");
            $total_items = $db->single()['total'];
            $total_pages = ceil($total_items / $limit);

            // جلب الرحلات غير النشطة مع التصفح
            $db->query("
                SELECT
                    tt.*,
                    tsp.name as starting_point_name,
                    tty.name as transport_type_name,
                    e.title as event_title,
                    e.date_time as event_date
                FROM transport_trips tt
                LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
                LEFT JOIN transport_types tty ON tt.transport_type_id = tty.id
                LEFT JOIN events e ON tt.event_id = e.id
                WHERE tt.is_active = 0
                ORDER BY tt.departure_time DESC
                LIMIT :limit OFFSET :offset
            ");
            $db->bind(':limit', $limit);
            $db->bind(':offset', $offset);
            $trips = $db->resultSet();

            $pagination = [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'items_per_page' => $limit
            ];

            echo json_encode([
                'success' => true,
                'trips' => $trips,
                'pagination' => $pagination
            ]);
            break;

        case 'update_all_seats':
            // تحديث المقاعد المتاحة لجميع الرحلات النشطة
            $db->query("
                UPDATE transport_trips tt
                SET available_seats = total_seats - (
                    SELECT COALESCE(SUM(passengers_count), 0)
                    FROM transport_bookings tb
                    WHERE tb.trip_id = tt.id AND tb.status IN ('confirmed', 'pending')
                )
                WHERE tt.is_active = 1
            ");

            if ($db->execute()) {
                // حساب عدد الرحلات المحدثة
                $db->query("SELECT COUNT(*) as count FROM transport_trips WHERE is_active = 1");
                $trips_count = $db->single()['count'];

                echo json_encode([
                    'success' => true,
                    'message' => "تم تحديث المقاعد المتاحة لـ {$trips_count} رحلة بنجاح"
                ]);
            } else {
                throw new Exception('فشل في تحديث المقاعد');
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

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
        case 'approve':
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            
            if ($booking_id <= 0) {
                throw new Exception('معرف الحجز غير صحيح');
            }

            // التحقق من وجود الحجز وحالته
            $db->query("SELECT * FROM transport_bookings WHERE id = :id");
            $db->bind(':id', $booking_id);
            $booking = $db->single();
            
            if (!$booking) {
                throw new Exception('الحجز غير موجود');
            }
            
            if ($booking['status'] !== 'pending') {
                throw new Exception('لا يمكن قبول هذا الحجز لأنه ليس في حالة انتظار');
            }

            // تحديث حالة الحجز إلى مقبول
            $db->query("
                UPDATE transport_bookings 
                SET status = 'confirmed', 
                    response_date = NOW(),
                    admin_id = 1
                WHERE id = :id
            ");
            $db->bind(':id', $booking_id);
            
            if ($db->execute()) {
                // إرسال إشعار للمستخدم
                $title = "تم قبول حجز المواصلات";
                $message = "مبروك! تم قبول حجزك رقم {$booking['booking_code']}. ";
                $message .= "عدد المقاعد: {$booking['passengers_count']}، المبلغ: {$booking['total_amount']} ₪";

                // حفظ الإشعار في قاعدة البيانات
                if ($booking['user_id']) {
                    $db->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (:user_id, :title, :message, 'transport', NOW())");
                    $db->bind(':user_id', $booking['user_id']);
                    $db->bind(':title', $title);
                    $db->bind(':message', $message);
                    $db->execute();
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'تم قبول الحجز بنجاح وإرسال إشعار للعميل',
                    'notification_message' => $message
                ]);
            } else {
                throw new Exception('فشل في تحديث حالة الحجز');
            }
            break;

        case 'reject':
            $booking_id = (int)($_POST['booking_id'] ?? 0);
            $rejection_reason = trim($_POST['rejection_reason'] ?? '');
            
            if ($booking_id <= 0) {
                throw new Exception('معرف الحجز غير صحيح');
            }
            
            if (empty($rejection_reason)) {
                throw new Exception('سبب الرفض مطلوب');
            }

            // التحقق من وجود الحجز وحالته
            $db->query("SELECT * FROM transport_bookings WHERE id = :id");
            $db->bind(':id', $booking_id);
            $booking = $db->single();
            
            if (!$booking) {
                throw new Exception('الحجز غير موجود');
            }
            
            if ($booking['status'] !== 'pending') {
                throw new Exception('لا يمكن رفض هذا الحجز لأنه ليس في حالة انتظار');
            }

            // تحديث حالة الحجز إلى مرفوض
            $db->query("
                UPDATE transport_bookings 
                SET status = 'rejected', 
                    rejection_reason = :reason,
                    response_date = NOW(),
                    admin_id = 1
                WHERE id = :id
            ");
            $db->bind(':id', $booking_id);
            $db->bind(':reason', $rejection_reason);
            
            if ($db->execute()) {
                // إعادة المقاعد للرحلة
                $db->query("
                    UPDATE transport_trips
                    SET available_seats = available_seats + :seats
                    WHERE id = :trip_id
                ");
                $db->bind(':seats', $booking['passengers_count']);
                $db->bind(':trip_id', $booking['trip_id']);
                $db->execute();

                // إرسال إشعار للمستخدم
                $title = "تم رفض حجز المواصلات";
                $message = "نعتذر، تم رفض حجزك رقم {$booking['booking_code']}. ";
                $message .= "سبب الرفض: {$rejection_reason}. ";
                $message .= "يمكنك محاولة حجز رحلة أخرى.";

                // حفظ الإشعار في قاعدة البيانات
                if ($booking['user_id']) {
                    $db->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (:user_id, :title, :message, 'transport', NOW())");
                    $db->bind(':user_id', $booking['user_id']);
                    $db->bind(':title', $title);
                    $db->bind(':message', $message);
                    $db->execute();
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'تم رفض الحجز وإرسال إشعار للعميل',
                    'notification_message' => $message
                ]);
            } else {
                throw new Exception('فشل في تحديث حالة الحجز');
            }
            break;

        default:
            throw new Exception('عملية غير مدعومة');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

<?php
session_start();
require_once '../../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit;
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get_event_date':
            $event_id = $_POST['id'] ?? 0;
            
            if (!$event_id) {
                echo json_encode(['success' => false, 'message' => 'معرف الفعالية مطلوب']);
                exit;
            }

            try {
                $db->query("SELECT date_time FROM events WHERE id = :id");
                $db->bind(':id', $event_id);
                $event = $db->single();

                if ($event) {
                    echo json_encode([
                        'success' => true,
                        'date' => $event['date_time']
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'الفعالية غير موجودة']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}
?>

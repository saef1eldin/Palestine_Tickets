<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// استدعاء الإعدادات
$config = require __DIR__ . '/config.php';

// استقبال بيانات الويبهوك
$update = json_decode(file_get_contents('php://input'), true);

// تسجيل الإشعارات في ملف (للتتبع والتصحيح)
file_put_contents(__DIR__ . '/webhook_log.txt', date('Y-m-d H:i:s') . " - " . print_r($update, true) . "\n\n", FILE_APPEND);

// تحقق من نوع الحدث
if (isset($update['event']) && $update['event'] === 'invoice_paid') {
    $invoice_id = $update['payload']['invoice_id'];
    $amount = $update['payload']['amount'];
    $asset = $update['payload']['asset'];
    $order_id = $update['payload']['payload']; // رقم الطلب الذي ربطته سابقا

    // الحصول على اتصال قاعدة البيانات
    $db = get_db_connection();

    // التحقق من وجود الطلب
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // تحديث حالة الطلب إلى "مدفوع"
        $stmt = $db->prepare("UPDATE orders SET payment_status = 'paid', payment_method = 'crypto', payment_date = NOW() WHERE id = ?");
        $stmt->bind_param("s", $order_id);
        $result = $stmt->execute();

        // تسجيل معلومات الدفع
        $stmt = $db->prepare("INSERT INTO payments (order_id, amount, currency, payment_method, transaction_id, payment_date) VALUES (?, ?, ?, 'crypto', ?, NOW())");
        $stmt->bind_param("sdss", $order_id, $amount, $asset, $invoice_id);
        $stmt->execute();

        // إنشاء التذاكر للطلب
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        if ($order) {
            $event_id = $order['event_id'];
            $user_id = $order['user_id'];
            $quantity = $order['quantity'];

            // إنشاء التذاكر
            for ($i = 0; $i < $quantity; $i++) {
                $ticket_id = uniqid('TKT');
                $stmt = $db->prepare("INSERT INTO tickets (id, user_id, event_id, order_id, purchase_date, status) VALUES (?, ?, ?, ?, NOW(), 'active')");
                $stmt->bind_param("siis", $ticket_id, $user_id, $event_id, $order_id);
                $stmt->execute();
            }
        }
    } else {
        // تسجيل خطأ: الطلب غير موجود
        file_put_contents(__DIR__ . '/webhook_errors.txt', date('Y-m-d H:i:s') . " - Order not found: $order_id\n", FILE_APPEND);
    }

    // تسجيل الدفع في ملف (للتتبع)
    file_put_contents(__DIR__ . '/payments.txt', date('Y-m-d H:i:s') . " - تم دفع طلب: $order_id بمبلغ: $amount $asset\n", FILE_APPEND);

    // إرسال إشعار إلى تيليجرام
    $message = "✅ تم استلام دفع جديد!\n\n";
    $message .= "🔢 رقم الطلب: $order_id\n";
    $message .= "💰 المبلغ: $amount $asset\n";
    $message .= "🧾 رقم الفاتورة: $invoice_id\n";
    $message .= "⏱️ وقت الدفع: " . date('Y-m-d H:i:s');

    // استخدام دالة إرسال الرسائل إلى تيليجرام الموجودة في النظام
    if (function_exists('send_telegram_text_message')) {
        send_telegram_text_message($message);
    }

    // إغلاق اتصال قاعدة البيانات
    $db->close();

    // إرجاع استجابة ناجحة
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully']);
} else {
    // إرجاع استجابة في حالة عدم وجود حدث دفع
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'message' => 'Not a payment event']);
}
?>

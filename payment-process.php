<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$auth = new Auth();

// التحقق من تسجيل الدخول
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id'])) {
    header('Location: events.php');
    exit();
}

$event_id = $_POST['event_id'];
$quantity = $_POST['quantity'] ?? 1;
$card_number = $_POST['card_number'];
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];

// إزالة المسافات والشرطات من رقم البطاقة
$clean_card_number = preg_replace('/\D/', '', $card_number);

// التحقق من طول رقم البطاقة
if(strlen($clean_card_number) != 16) {
    $_SESSION['payment_error'] = 'رقم البطاقة يجب أن يكون 16 رقم';
    header("Location: checkout.php?event_id={$event_id}");
    exit();
}

// تم نقل دالة validateCreditCardLuhn إلى includes/functions.php لتجنب التضارب

// التحقق من صحة رقم البطاقة باستخدام خوارزمية Luhn
if(!validateCreditCardLuhn($clean_card_number)) {
    $_SESSION['payment_error'] = 'رقم البطاقة غير صحيح، يرجى التحقق من الرقم المدخل';
    header("Location: checkout.php?event_id={$event_id}");
    exit();
}

// جلب بيانات الحدث من قاعدة البيانات
$event = get_event_by_id($event_id);

if(!$event) {
    header('Location: events.php');
    exit();
}

// التحقق من وجود حجز مواصلات
$with_transport = isset($_POST['with_transport']) && $_POST['with_transport'] == '1';
$transport_booking = null;
$transport_amount = 0;
$ticket_amount = 0;
$has_event_ticket = false;

if ($with_transport && isset($_SESSION['booking_data'])) {
    $transport_booking = $_SESSION['booking_data'];
    $transport_amount = $transport_booking['transport_amount'] ?? 0;
    $ticket_amount = $transport_booking['ticket_amount'] ?? 0;
    $has_event_ticket = $transport_booking['has_event_ticket'] ?? false;

    // حساب المبلغ الإجمالي من بيانات الحجز المحسوبة مسبقاً
    $total_amount = $transport_booking['total_amount'];
} else {
    // حجز تذكرة عادي
    $total_amount = $event['price'] * $quantity;
}

// جمع المعلومات التقنية للسجلات
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$ip_address = $_SERVER['REMOTE_ADDR'];

// تحديد نوع المتصفح
$browser = 'غير معروف';
if (preg_match('/MSIE|Trident/i', $user_agent)) {
    $browser = 'Internet Explorer';
} elseif (preg_match('/Firefox/i', $user_agent)) {
    $browser = 'Firefox';
} elseif (preg_match('/Chrome/i', $user_agent)) {
    $browser = 'Chrome';
} elseif (preg_match('/Safari/i', $user_agent)) {
    $browser = 'Safari';
} elseif (preg_match('/Opera|OPR/i', $user_agent)) {
    $browser = 'Opera';
} elseif (preg_match('/Edge/i', $user_agent)) {
    $browser = 'Edge';
}

// تحديد نظام التشغيل
$os = 'غير معروف';
if (preg_match('/Windows/i', $user_agent)) {
    $os = 'Windows';
} elseif (preg_match('/Mac OS X/i', $user_agent)) {
    $os = 'Mac OS X';
} elseif (preg_match('/Linux/i', $user_agent)) {
    $os = 'Linux';
} elseif (preg_match('/Android/i', $user_agent)) {
    $os = 'Android';
} elseif (preg_match('/iOS|iPhone|iPad|iPod/i', $user_agent)) {
    $os = 'iOS';
}

// تحديد نوع الجهاز
$device = 'كمبيوتر';
if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $user_agent)) {
    $device = 'جهاز محمول';
} elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
    $device = 'تابلت';
}

// تسجيل معلومات الطلب في ملف السجل
$log_message = "طلب حجز جديد: " . $event['title'] . " - المستخدم: " . $_SESSION['user_name'] . " - المبلغ: " . $total_amount . " ₪";
error_log($log_message);

// دالة معالجة الدفع وحفظ التذاكر في قاعدة البيانات
function process_payment($user_id, $event_id, $quantity, $total_amount) {
    try {
        $db = new Database();

        // إنشاء رقم طلب فريد
        $order_id = 'ORDER_' . time() . '_' . $user_id;

        // إنشاء أكواد التذاكر
        $ticket_codes = [];
        for ($i = 0; $i < $quantity; $i++) {
            $ticket_codes[] = 'TICKET_' . strtoupper(substr(md5(time() . $i . rand()), 0, 10));
        }

        // حفظ الطلب في جدول orders (باستخدام الأعمدة الموجودة فعلياً)
        $db->query("INSERT INTO orders (user_id, event_id, quantity, total_amount, payment_status, payment_method, transaction_id)
                    VALUES (:user_id, :event_id, :quantity, :total_amount, 'completed', 'credit_card', :transaction_id)");
        $db->bind(':user_id', $user_id);
        $db->bind(':event_id', $event_id);
        $db->bind(':quantity', $quantity);
        $db->bind(':total_amount', $total_amount);
        $db->bind(':transaction_id', $order_id);
        $db->execute();

        $order_db_id = $db->lastInsertId();

        // حفظ التذاكر في جدول tickets (مع order_id للربط مع جدول orders)
        foreach ($ticket_codes as $ticket_code) {
            $db->query("INSERT INTO tickets (order_id, user_id, event_id, ticket_code, status, created_at, updated_at)
                        VALUES (:order_id, :user_id, :event_id, :ticket_code, 'active', NOW(), NOW())");
            $db->bind(':order_id', $order_db_id);
            $db->bind(':user_id', $user_id);
            $db->bind(':event_id', $event_id);
            $db->bind(':ticket_code', $ticket_code);
            $db->execute();
        }

        return [
            'success' => true,
            // المعرّف الرقمي الفعلي لسجل الطلب في قاعدة البيانات (orders.id)
            'order_id' => (int) $order_db_id,
            // معرف العملية المقروء للبشر والمحفوظ في orders.transaction_id
            'transaction_id' => $order_id,
            'ticket_codes' => $ticket_codes,
            'total_amount' => $total_amount,
            'discount_amount' => 0
        ];

    } catch (Exception $e) {
        error_log("Payment processing error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'حدث خطأ أثناء معالجة الدفع: ' . $e->getMessage()
        ];
    }
}

// دالة إنشاء حجز المواصلات مباشرة في قاعدة البيانات
function create_transport_booking_direct($data) {
    try {
        // الاتصال بقاعدة البيانات
        $host = 'localhost';
        $dbname = 'tickets_db';
        $username = 'root';
        $password = '';

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // إنشاء رمز الحجز
        $booking_code = 'TR' . strtoupper(substr(md5(time() . rand()), 0, 8));

        // التحقق من عدم تكرار رمز الحجز
        $stmt = $pdo->prepare("SELECT id FROM transport_bookings WHERE booking_code = ?");
        $stmt->execute([$booking_code]);
        while ($stmt->fetch()) {
            $booking_code = 'TR' . strtoupper(substr(md5(time() . rand()), 0, 8));
            $stmt = $pdo->prepare("SELECT id FROM transport_bookings WHERE booking_code = ?");
            $stmt->execute([$booking_code]);
        }

        // إدراج الحجز (مع التحقق من وجود trip_id أولاً)
        $trip_id = $data['trip_id'] ?? 1; // استخدام trip_id افتراضي إذا لم يكن موجود

        // التحقق من وجود الرحلة
        $stmt = $pdo->prepare("SELECT id FROM transport_trips WHERE id = ?");
        $stmt->execute([$trip_id]);
        if (!$stmt->fetch()) {
            // إذا لم توجد الرحلة، استخدم الرحلة الأولى المتاحة
            $stmt = $pdo->prepare("SELECT id FROM transport_trips LIMIT 1");
            $stmt->execute();
            $first_trip = $stmt->fetch();
            $trip_id = $first_trip ? $first_trip['id'] : 1;
        }

        $sql = "INSERT INTO transport_bookings
                (user_id, trip_id, event_id, customer_name, customer_phone, passengers_count, total_amount, booking_code, payment_method, status)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, 'credit_card', 'pending')";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['user_id'],
            $trip_id,
            $data['event_id'] ?? 1,
            $data['passenger_name'],
            $data['passenger_phone'],
            $data['passengers_count'],
            $data['total_amount'],
            $booking_code
        ]);

        if ($result) {
            $booking_id = $pdo->lastInsertId();

            return [
                'success' => true,
                'booking_id' => $booking_id,
                'booking_code' => $booking_code,
                'total_amount' => $data['total_amount']
            ];
        }

        return ['success' => false, 'message' => 'حدث خطأ أثناء إنشاء الحجز'];

    } catch (Exception $e) {
        error_log("Transport booking error: " . $e->getMessage());
        return ['success' => false, 'message' => 'حدث خطأ في النظام: ' . $e->getMessage()];
    }
}

// معالجة الدفع وإنشاء الطلب والتذاكر
if ($with_transport && $has_event_ticket) {
    // المستخدم لديه تذكرة - لا نحتاج لإنشاء تذكرة جديدة للفعالية
    // لكننا نحتاج معرف طلب رقمي لصفحة النجاح. ننشئ سجل tracking في orders بمبلغ صفر.
    $dbTmp = new Database();
    $dbTmp->query("INSERT INTO orders (user_id, event_id, quantity, total_amount, payment_status, payment_method, transaction_id) VALUES (:user_id, :event_id, 0, 0, 'completed', 'credit_card', :transaction_id)");
    $dbTmp->bind(':user_id', $_SESSION['user_id']);
    $dbTmp->bind(':event_id', $event_id);
    $dbTmp->bind(':transaction_id', 'TRANSPORT_' . time());
    $dbTmp->execute();
    $order_db_id_transport = (int) $dbTmp->lastInsertId();

    $result = [
        'success' => true,
        'order_id' => $order_db_id_transport, // رقم طلب رقمي
        'transaction_id' => 'TRANSPORT_' . time(),
        'ticket_codes' => [],
        'total_amount' => $total_amount,
        'discount_amount' => 0
    ];
} else {
    // إنشاء تذكرة جديدة (حجز عادي أو مع مواصلات بدون تذكرة)
    $ticket_quantity = $with_transport ? $transport_booking['passengers_count'] : $quantity;
    $result = process_payment($_SESSION['user_id'], $event_id, $ticket_quantity, $total_amount);
}

if ($result['success']) {
    // حفظ بيانات بطاقة الدفع (مع إخفاء البيانات الحساسة)
    // الاحتفاظ فقط بآخر 4 أرقام من البطاقة وتشفير الباقي
    $last_four = substr($clean_card_number, -4);
    $masked_card_number = str_pad($last_four, strlen($clean_card_number), '*', STR_PAD_LEFT);

    // تشفير CVV (في الواقع، لا ينبغي تخزين CVV على الإطلاق وفقًا لمعايير PCI DSS)
    // هنا نخزن فقط قيمة مشفرة للتوضيح
    $hashed_cvv = password_hash($cvv, PASSWORD_DEFAULT);

    // حفظ بيانات الدفع في قاعدة البيانات
    try {
        $db = new Database();
        $db->query("INSERT INTO payment_cards (user_id, order_id, card_number, card_holder, expiry_date, amount, status, ip_address, browser, os, device, created_at)
                    VALUES (:user_id, :order_id, :card_number, :card_holder, :expiry_date, :amount, 'processed', :ip_address, :browser, :os, :device, NOW())");
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':order_id', $result['order_id']);
        $db->bind(':card_number', $masked_card_number);
        $db->bind(':card_holder', $_SESSION['user_name'] ?? 'المستخدم');
        $db->bind(':expiry_date', $expiry_date);
        $db->bind(':amount', $total_amount);
        $db->bind(':ip_address', $ip_address);
        $db->bind(':browser', $browser);
        $db->bind(':os', $os);
        $db->bind(':device', $device);
        $db->execute();
    } catch (Exception $e) {
        error_log("Payment card save error: " . $e->getMessage());
    }

    // حفظ بيانات الدفع في الجلسة للعرض لاحقاً
    $payment_data = [
        'user_id' => $_SESSION['user_id'],
        'order_id' => $result['order_id'], // المعرّف الرقمي الفعلي للطلب
        'transaction_id' => $result['transaction_id'] ?? null,
        'card_number' => $masked_card_number,
        'card_holder' => $_SESSION['user_name'] ?? 'المستخدم',
        'expiry_date' => $expiry_date,
        'amount' => $total_amount,
        'status' => 'processing', // في مرحلة المعالجة حالياً
        'ip_address' => $ip_address,
        'browser' => $browser,
        'os' => $os,
        'device' => $device,
        'created_at' => date('Y-m-d H:i:s')
    ];
    $_SESSION['payment_data'] = $payment_data;

    // لا نُرسل إشعار نجاح هنا حتى تكتمل العملية فعلاً.
    // بدلاً من ذلك يمكن إضافة إشعار حالة "قيد المعالجة" إذا رغبت لاحقاً.

    // معالجة حجز المواصلات إذا كان موجود
    if ($with_transport && $transport_booking) {
        // تحديث بيانات الحجز مع معلومات الدفع
        $transport_booking['payment_method'] = 'credit_card';
        $transport_booking['payment_details'] = [
            'card_number' => $masked_card_number,
            'payment_with_event' => true,
            'event_order_id' => $result['order_id']
        ];
        $transport_booking['user_id'] = $_SESSION['user_id'];

        // إنشاء حجز المواصلات في قاعدة البيانات
        $transport_result = create_transport_booking_direct($transport_booking);

        if ($transport_result['success']) {
            // تحديث رسالة النجاح لتتضمن رقم الحجز
            if ($has_event_ticket) {
                $success_message = "تم دفع رسوم المواصلات بنجاح ({$total_amount} ₪). رقم حجز المواصلات: {$transport_result['booking_code']}";
                $_SESSION['success_message'] = $success_message;
                // تحديث الإشعار
                try {
                    add_notification($_SESSION['user_id'], 'تأكيد حجز المواصلات', $success_message, '', 'transport_booking');
                } catch (Exception $e) {
                    error_log("Transport notification error: " . $e->getMessage());
                }
            } else {
                $success_message = "تم حجز تذكرة الحدث والمواصلات بنجاح. المبلغ الإجمالي: {$total_amount} ₪. رقم حجز المواصلات: {$transport_result['booking_code']}";
                $_SESSION['success_message'] = $success_message;
                // تحديث الإشعار
                try {
                    add_notification($_SESSION['user_id'], 'تأكيد حجز التذكرة والمواصلات', $success_message, '', 'booking_success');
                } catch (Exception $e) {
                    error_log("Combined booking notification error: " . $e->getMessage());
                }
            }

            // حفظ رقم حجز المواصلات في الجلسة
            $_SESSION['transport_booking_code'] = $transport_result['booking_code'];

            // مسح بيانات الحجز من الجلسة
            unset($_SESSION['booking_data']);
        } else {
            // في حالة فشل حجز المواصلات
            $error_message = "تم الدفع بنجاح ولكن حدث خطأ في حجز المواصلات: " . $transport_result['message'];
            $_SESSION['error_message'] = $error_message;
            // إضافة إشعار خطأ
            try {
                add_notification($_SESSION['user_id'], 'خطأ في حجز المواصلات', $error_message, '', 'booking_failure');
            } catch (Exception $e) {
                error_log("Transport error notification error: " . $e->getMessage());
            }
        }
    }

    // إعادة التوجيه إلى صفحة معالجة الدفع باستخدام معرف الطلب الرقمي
    $redirect_url = "payment-processing.php?event_id={$event_id}&quantity={$quantity}&order_id=" . urlencode((string)$result['order_id']);
    if ($with_transport) {
        $redirect_url .= "&with_transport=1";
    }
    header("Location: {$redirect_url}");
    exit();
} else {
    // في حالة فشل الدفع
    $error_message = $result['message'] ?? 'حدث خطأ أثناء معالجة الدفع';
    $_SESSION['error_message'] = $error_message;

    // إضافة إشعار فشل الدفع
    try {
        require_once 'includes/notification_functions.php';
        add_notification($_SESSION['user_id'], 'فشل في الدفع', $error_message, '', 'booking_failure');
    } catch (Exception $e) {
        error_log("Payment failure notification error: " . $e->getMessage());
    }

    header("Location: checkout.php?event_id={$event_id}&error=payment_failed");
    exit();
}
?>

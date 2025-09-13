<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملفات المصادقة
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// التحقق من تسجيل الدخول
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    // حفظ بيانات النموذج في الجلسة
    $_SESSION['pending_booking_data'] = $_POST;

    // حفظ الرابط للعودة إليه بعد تسجيل الدخول
    $_SESSION['redirect_after_login'] = '../checkout.php?event_id=' . ($_POST['event_id'] ?? '') . '&with_transport=1';

    header('Location: ../login.php');
    exit();
}

// التحقق من وجود بيانات محفوظة من قبل تسجيل الدخول
if (isset($_SESSION['pending_booking_data']) && empty($_POST)) {
    $_POST = $_SESSION['pending_booking_data'];
    unset($_SESSION['pending_booking_data']);
}

// التحقق من طريقة الإرسال
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_POST)) {
    header('Location: ../events.php');
    exit();
}

// التحقق من CSRF token - مع تشخيص مؤقت
$csrf_token_received = $_POST['csrf_token'] ?? '';
$csrf_token_session = $_SESSION['csrf_token'] ?? '';

// تشخيص مؤقت - كتابة في ملف منفصل
$debug_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'source_file' => 'REAL_BOOKING_DETAILS', // تمييز الملف الحقيقي
    'csrf_received' => $csrf_token_received,
    'csrf_session' => $csrf_token_session,
    'csrf_match' => verify_csrf_token($csrf_token_received),
    'post_data' => $_POST,
    'session_data' => $_SESSION,
    'referrer' => $_SERVER['HTTP_REFERER'] ?? 'unknown'
];

file_put_contents('../debug_booking.log', "REAL BOOKING PROCESS - " . json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

if (!verify_csrf_token($csrf_token_received)) {
    $_SESSION['error'] = 'خطأ في رمز الحماية - يرجى المحاولة مرة أخرى';
    file_put_contents('../debug_booking.log', "CSRF FAILED - Redirecting to events.php\n\n", FILE_APPEND);
    header('Location: ../events.php');
    exit();
}

// التحقق من البيانات المطلوبة
$required_fields = ['trip_id', 'event_id', 'name', 'phone', 'passengers'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = 'الرجاء إدخال جميع الحقول المطلوبة';
        header('Location: booking_details.php?trip_id=' . ($_POST['trip_id'] ?? ''));
        exit();
    }
}

// تنظيف البيانات
$trip_id = (int)$_POST['trip_id'];
$event_id = (int)$_POST['event_id'];
$name = sanitize_input($_POST['name']);
$phone = sanitize_input($_POST['phone']);
$email = sanitize_input($_POST['email'] ?? '');
$passengers = (int)$_POST['passengers'];
$notes = sanitize_input($_POST['notes'] ?? '');

// التحقق من صحة البيانات
if ($passengers < 1 || $passengers > 10) {
    $_SESSION['error'] = 'عدد الركاب يجب أن يكون بين 1 و 10';
    header('Location: booking_details.php?trip_id=' . $trip_id);
    exit();
}

// التحقق من صحة رقم الجوال
if (!preg_match('/^05[0-9]{8}$/', $phone)) {
    $_SESSION['error'] = 'الرجاء إدخال رقم جوال صحيح (مثال: 0501234567)';
    header('Location: booking_details.php?trip_id=' . $trip_id);
    exit();
}

// جلب بيانات الرحلة من قاعدة البيانات أولاً
$trip = null;
try {
    require_once '../includes/transport_functions.php';
    $trip = get_trip_by_id($trip_id);
} catch (Exception $e) {
    file_put_contents('../debug_booking.log', "Database error: " . $e->getMessage() . "\n\n", FILE_APPEND);
}

// إذا لم توجد في قاعدة البيانات، استخدم البيانات التجريبية
if (!$trip) {
    $trips_data = [
        1 => [
            'id' => 1,
            'price' => 25,
            'available_seats' => 15,
            'starting_point_name' => 'رفح'
        ],
        2 => [
            'id' => 2,
            'price' => 20,
            'available_seats' => 8,
            'starting_point_name' => 'رفح'
        ],
        3 => [
            'id' => 3,
            'price' => 15,
            'available_seats' => 25,
            'starting_point_name' => 'رفح'
        ]
    ];

    $trip = $trips_data[$trip_id] ?? null;
}

// تشخيص بيانات الرحلة
file_put_contents('../debug_booking.log', "Trip data for ID {$trip_id}: " . json_encode($trip, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

if (!$trip) {
    $_SESSION['error'] = 'الرحلة غير موجودة - معرف الرحلة: ' . $trip_id;
    file_put_contents('../debug_booking.log', "TRIP NOT FOUND - ID: {$trip_id} - Redirecting to events.php\n\n", FILE_APPEND);
    header('Location: ../events.php');
    exit();
}

// التحقق من توفر المقاعد
if ($trip['available_seats'] < $passengers) {
    $_SESSION['error'] = 'عذراً، المقاعد المتاحة فقط ' . $trip['available_seats'] . ' مقعد';
    header('Location: booking_details.php?trip_id=' . $trip_id);
    exit();
}

// التحقق التلقائي من وجود تذكرة للحدث
$user_id = $_SESSION['user_id'] ?? null;
$has_event_ticket = false;
$event_ticket_info = null;

// جلب بيانات الحدث من قاعدة البيانات
$event = get_event_by_id($event_id);
if (!$event) {
    $_SESSION['error'] = 'الحدث غير موجود';
    header('Location: ../events.php');
    exit();
}

// التحقق الفعلي من وجود تذكرة للمستخدم لهذا الحدث
if ($user_id) {
    $db = new Database();
    $db->query("
        SELECT t.*, o.total_amount
        FROM tickets t
        JOIN orders o ON t.order_id = o.id
        WHERE t.user_id = :user_id AND t.event_id = :event_id AND t.status IN ('active', 'confirmed')
        LIMIT 1
    ");
    $db->bind(':user_id', $user_id);
    $db->bind(':event_id', $event_id);
    $event_ticket_info = $db->single();

    if ($event_ticket_info) {
        $has_event_ticket = true;
        file_put_contents('../debug_booking.log', "User {$user_id} has ticket for event {$event_id}: " . json_encode($event_ticket_info, JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);
    } else {
        file_put_contents('../debug_booking.log', "User {$user_id} does NOT have ticket for event {$event_id}\n\n", FILE_APPEND);
    }
}

// حساب المبلغ الإجمالي
$transport_amount = $trip['price'] * $passengers;
$ticket_amount = 0;
$total_amount = $transport_amount;

if (!$has_event_ticket && $event['price'] > 0) {
    // المستخدم لا يملك تذكرة والحدث مدفوع - أضف سعر التذكرة
    $ticket_amount = $event['price'] * $passengers;
    $total_amount = $transport_amount + $ticket_amount;
}

file_put_contents('../debug_booking.log', "Booking calculation: has_ticket={$has_event_ticket}, transport_amount={$transport_amount}, ticket_amount={$ticket_amount}, total_amount={$total_amount}\n\n", FILE_APPEND);

// حفظ بيانات الحجز في الجلسة للانتقال لصفحة الدفع
$_SESSION['booking_data'] = [
    'trip_id' => $trip_id,
    'event_id' => $event_id,
    'passenger_name' => $name,
    'passenger_phone' => $phone,
    'passenger_email' => $email,
    'passengers_count' => $passengers,
    'special_notes' => $notes,
    'transport_amount' => $transport_amount,
    'ticket_amount' => $ticket_amount,
    'total_amount' => $total_amount,
    'has_event_ticket' => $has_event_ticket,
    'event_ticket_info' => $event_ticket_info,
    'starting_point_name' => $trip['starting_point_name']
];

// رسالة نجاح
$_SESSION['success'] = 'تم حفظ بيانات الحجز بنجاح. يرجى المتابعة للدفع.';

// تشخيص مؤقت قبل التوجيه
$redirect_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'REDIRECT_TO_CHECKOUT',
    'event_id' => $event_id,
    'redirect_url' => '../checkout.php?event_id=' . $event_id . '&with_transport=1',
    'booking_data' => $_SESSION['booking_data'],
    'success_message' => $_SESSION['success']
];

file_put_contents('../debug_booking.log', "SUCCESS - " . json_encode($redirect_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

// الانتقال لصفحة الدفع الموحدة
header('Location: ../checkout.php?event_id=' . $event_id . '&with_transport=1');
exit();
?>

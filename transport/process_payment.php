<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';

// التحقق من طريقة الإرسال
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../events.php');
}

// التحقق من CSRF token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    redirect('../events.php');
}

// التحقق من وجود بيانات الحجز في الجلسة
if (!isset($_SESSION['booking_data'])) {
    redirect('../events.php');
}

$booking_data = $_SESSION['booking_data'];
$payment_method = sanitize_input($_POST['payment_method'] ?? '');

// التحقق من طريقة الدفع
$allowed_methods = ['bank_transfer', 'cash_on_delivery', 'mobile_pay', 'credit_card'];
if (!in_array($payment_method, $allowed_methods)) {
    $_SESSION['error'] = 'طريقة الدفع غير صحيحة';
    redirect('payment_method.php');
}

// جمع بيانات الدفع حسب الطريقة المختارة
$payment_details = [];

switch ($payment_method) {
    case 'bank_transfer':
        $payment_details = [
            'transfer_number' => sanitize_input($_POST['transfer_number'] ?? ''),
            'transfer_date' => sanitize_input($_POST['transfer_date'] ?? ''),
            'bank_name' => 'البنك الأهلي السعودي',
            'account_number' => 'SA0380000000608010167519'
        ];
        break;

    case 'mobile_pay':
        $payment_details = [
            'mobile_number' => sanitize_input($_POST['mobile_number'] ?? ''),
            'account_name' => sanitize_input($_POST['account_name'] ?? '')
        ];
        break;

    case 'credit_card':
        $payment_details = [
            'card_number' => sanitize_input($_POST['card_number'] ?? ''),
            'expiry_date' => sanitize_input($_POST['expiry_date'] ?? ''),
            'cvv' => sanitize_input($_POST['cvv'] ?? ''),
            'card_name' => sanitize_input($_POST['card_name'] ?? '')
        ];
        break;

    case 'cash_on_delivery':
        $payment_details = [
            'note' => 'سيتم الدفع نقداً عند استلام الخدمة'
        ];
        break;
}

// إنشاء الحجز
$booking_data['payment_method'] = $payment_method;
$booking_data['payment_details'] = $payment_details;
$booking_data['user_id'] = $_SESSION['user_id'] ?? null;

// إضافة معلومات التذكرة إذا كانت متوفرة
$booking_data['has_event_ticket'] = $_POST['has_event_ticket'] ?? 'yes';
$booking_data['ticket_amount'] = (float)($_POST['ticket_amount'] ?? 0);
$booking_data['transport_amount'] = (float)($_POST['transport_amount'] ?? $booking_data['total_amount']);

$result = create_transport_booking($booking_data);

if ($result['success']) {
    // حفظ معرف الحجز في الجلسة
    $_SESSION['booking_success'] = [
        'booking_id' => $result['booking_id'],
        'booking_code' => $result['booking_code'],
        'total_amount' => $result['total_amount']
    ];

    // مسح بيانات الحجز المؤقتة
    unset($_SESSION['booking_data']);

    // الانتقال لصفحة التأكيد
    redirect('confirmation_booking.php');
} else {
    $_SESSION['error'] = $result['message'];
    redirect('payment_method.php');
}
?>

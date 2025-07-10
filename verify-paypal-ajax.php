<?php
require_once 'verify-paypal.php';

// التحقق من أن الطلب هو طلب POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على البريد الإلكتروني من الطلب
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    
    // التحقق من صحة حساب PayPal
    $result = verifyPayPalAccount($email);
    
    // إرجاع النتيجة كـ JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// إذا لم يكن الطلب طلب POST، نرجع خطأ
header('HTTP/1.1 405 Method Not Allowed');
header('Content-Type: application/json');
echo json_encode([
    'status' => false,
    'message' => 'Method Not Allowed'
]);
exit;
?>

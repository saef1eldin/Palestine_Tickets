<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';
require_once '../includes/auth.php';

$auth = new Auth();

// التحقق من تسجيل الدخول ودور المستخدم
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'transport_admin') {
    redirect('../index.php');
}

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('drivers.php');
}

$db = new Database();

try {
    // استلام البيانات من النموذج
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $license_number = $_POST['license_number'] ?? '';
    $address = $_POST['address'] ?? '';

    // التحقق من صحة البيانات
    $errors = [];

    if (empty($name)) {
        $errors[] = 'اسم السائق مطلوب';
    }

    if (empty($phone)) {
        $errors[] = 'رقم الهاتف مطلوب';
    }

    if (empty($license_number)) {
        $errors[] = 'رقم الرخصة مطلوب';
    }

    // التحقق من عدم تكرار رقم الرخصة
    if (!empty($license_number)) {
        $db->query("SELECT id FROM transport_drivers WHERE license_number = :license_number");
        $db->bind(':license_number', $license_number);
        if ($db->single()) {
            $errors[] = 'رقم الرخصة موجود بالفعل';
        }
    }

    // إذا كانت هناك أخطاء، إعادة التوجيه مع رسالة خطأ
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        redirect('drivers.php');
    }

    // إدراج السائق الجديد
    $db->query("
        INSERT INTO transport_drivers (
            name, 
            phone, 
            license_number, 
            address, 
            status, 
            is_active, 
            created_at
        ) VALUES (
            :name, 
            :phone, 
            :license_number, 
            :address, 
            'available', 
            1, 
            NOW()
        )
    ");

    $db->bind(':name', $name);
    $db->bind(':phone', $phone);
    $db->bind(':license_number', $license_number);
    $db->bind(':address', $address);

    if ($db->execute()) {
        $_SESSION['success_message'] = 'تم إضافة السائق بنجاح';
    } else {
        $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة السائق';
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = 'حدث خطأ: ' . $e->getMessage();
}

// إعادة التوجيه إلى صفحة السائقين
redirect('drivers.php');
?>

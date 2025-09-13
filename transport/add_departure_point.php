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
    redirect('dashboard.php?section=departure-points');
}

$db = new Database();

try {
    // استلام البيانات من النموذج
    $name = $_POST['name'] ?? '';
    $region = $_POST['region'] ?? '';
    $icon = $_POST['icon'] ?? 'city';
    $description = $_POST['description'] ?? '';

    // التحقق من صحة البيانات
    $errors = [];

    if (empty($name)) {
        $errors[] = 'اسم نقطة الانطلاق مطلوب';
    }

    if (empty($region)) {
        $errors[] = 'المنطقة مطلوبة';
    }

    if (!in_array($region, ['north', 'center', 'south'])) {
        $errors[] = 'المنطقة المحددة غير صحيحة';
    }

    // التحقق من عدم تكرار الاسم
    if (!empty($name)) {
        $db->query("SELECT id FROM transport_starting_points WHERE name = :name");
        $db->bind(':name', $name);
        if ($db->single()) {
            $errors[] = 'نقطة الانطلاق موجودة بالفعل';
        }
    }

    // إذا كانت هناك أخطاء، إعادة التوجيه مع رسالة خطأ
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        redirect('dashboard.php?section=departure-points');
    }

    // إدراج نقطة الانطلاق الجديدة
    $db->query("
        INSERT INTO transport_starting_points (
            name, 
            region, 
            icon, 
            description, 
            is_active, 
            created_at
        ) VALUES (
            :name, 
            :region, 
            :icon, 
            :description, 
            1, 
            NOW()
        )
    ");

    $db->bind(':name', $name);
    $db->bind(':region', $region);
    $db->bind(':icon', $icon);
    $db->bind(':description', $description);

    if ($db->execute()) {
        $_SESSION['success_message'] = 'تم إضافة نقطة الانطلاق بنجاح';
    } else {
        $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة نقطة الانطلاق';
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = 'حدث خطأ: ' . $e->getMessage();
}

// إعادة التوجيه إلى صفحة نقاط الانطلاق
redirect('dashboard.php?section=departure-points');
?>

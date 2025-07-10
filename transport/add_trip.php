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
    redirect('trips.php');
}

$db = new Database();

try {
    // استلام البيانات من النموذج
    $starting_point_id = $_POST['starting_point_id'] ?? '';
    $event_id = $_POST['event_id'] ?? '';
    $departure_date = $_POST['departure_date'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $price = $_POST['price'] ?? '';
    $available_seats = $_POST['available_seats'] ?? '';
    $transport_type_id = $_POST['transport_type_id'] ?? '';

    // التحقق من صحة البيانات
    $errors = [];

    if (empty($starting_point_id)) {
        $errors[] = 'نقطة الانطلاق مطلوبة';
    }

    if (empty($event_id)) {
        $errors[] = 'الفعالية مطلوبة';
    }

    if (empty($departure_date)) {
        $errors[] = 'تاريخ المغادرة مطلوب';
    }

    if (empty($departure_time)) {
        $errors[] = 'وقت المغادرة مطلوب';
    }

    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'السعر يجب أن يكون رقم صحيح أكبر من صفر';
    }

    if (empty($available_seats) || !is_numeric($available_seats) || $available_seats <= 0) {
        $errors[] = 'عدد المقاعد يجب أن يكون رقم صحيح أكبر من صفر';
    }

    if (empty($transport_type_id)) {
        $errors[] = 'نوع المواصلات مطلوب';
    }

    // التحقق من صحة التاريخ والوقت
    if (!empty($departure_date) && !empty($departure_time)) {
        $departure_datetime = $departure_date . ' ' . $departure_time;
        $departure_timestamp = strtotime($departure_datetime);

        if ($departure_timestamp === false) {
            $errors[] = 'تاريخ ووقت المغادرة غير صحيح';
        } elseif ($departure_timestamp <= time()) {
            $errors[] = 'تاريخ ووقت المغادرة يجب أن يكون في المستقبل';
        }
    }

    // التحقق من وجود نقطة الانطلاق
    if (!empty($starting_point_id)) {
        $db->query("SELECT id FROM transport_starting_points WHERE id = :id AND is_active = 1");
        $db->bind(':id', $starting_point_id);
        if (!$db->single()) {
            $errors[] = 'نقطة الانطلاق المحددة غير صحيحة';
        }
    }

    // التحقق من وجود الفعالية
    if (!empty($event_id)) {
        $db->query("SELECT id FROM events WHERE id = :id");
        $db->bind(':id', $event_id);
        if (!$db->single()) {
            $errors[] = 'الفعالية المحددة غير صحيحة';
        }
    }

    // التحقق من وجود نوع المواصلات
    if (!empty($transport_type_id)) {
        $db->query("SELECT id FROM transport_types WHERE id = :id");
        $db->bind(':id', $transport_type_id);
        if (!$db->single()) {
            $errors[] = 'نوع المواصلات المحدد غير صحيح';
        }
    }

    // إذا كانت هناك أخطاء، إعادة التوجيه مع رسالة خطأ
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
        redirect('trips.php');
    }

    // دمج التاريخ والوقت
    $departure_datetime = $departure_date . ' ' . $departure_time;

    // إدراج الرحلة الجديدة
    $db->query("
        INSERT INTO transport_trips (
            starting_point_id,
            event_id,
            transport_type_id,
            departure_time,
            price,
            available_seats,
            total_seats,
            is_active,
            created_at
        ) VALUES (
            :starting_point_id,
            :event_id,
            :transport_type_id,
            :departure_time,
            :price,
            :available_seats,
            :available_seats,
            1,
            NOW()
        )
    ");

    $db->bind(':starting_point_id', $starting_point_id);
    $db->bind(':event_id', $event_id);
    $db->bind(':transport_type_id', $transport_type_id);
    $db->bind(':departure_time', $departure_datetime);
    $db->bind(':price', $price);
    $db->bind(':available_seats', $available_seats);

    if ($db->execute()) {
        $_SESSION['success_message'] = 'تم إضافة الرحلة بنجاح';
    } else {
        $_SESSION['error_message'] = 'حدث خطأ أثناء إضافة الرحلة';
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = 'حدث خطأ: ' . $e->getMessage();
}

// إعادة التوجيه إلى صفحة الرحلات
redirect('trips.php');
?>

<?php
/**
 * ملف معالجة إجراءات الملف الشخصي للإدمن
 */

header('Content-Type: application/json');
require_once '../../includes/init.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            updateAdminProfile();
            break;
        case 'get_profile':
            getAdminProfile();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير صحيح']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
}

function updateAdminProfile() {
    global $db;

    try {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');

        // التحقق من صحة البيانات
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'الاسم مطلوب']);
            return;
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
            return;
        }

        // الحصول على معرف المستخدم الحالي
        $user_id = $_SESSION['user_id'] ?? 1;

        // تحديث بيانات المستخدم في جدول users
        $db->query("UPDATE users SET name = :name, email = :email WHERE id = :user_id");
        $db->bind(':name', $name);
        $db->bind(':email', $email);
        $db->bind(':user_id', $user_id);
        $db->execute();

        // التحقق من وجود سجل في admin_profiles
        $db->query("SELECT id FROM admin_profiles WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $existing_profile = $db->single();

        if ($existing_profile) {
            // تحديث السجل الموجود
            $db->query("UPDATE admin_profiles SET display_name = :name, department = :department, updated_at = NOW() WHERE user_id = :user_id");
            $db->bind(':name', $name);
            $db->bind(':department', $department);
            $db->bind(':user_id', $user_id);
        } else {
            // إنشاء سجل جديد
            $db->query("INSERT INTO admin_profiles (user_id, display_name, department, created_at, updated_at) VALUES (:user_id, :name, :department, NOW(), NOW())");
            $db->bind(':user_id', $user_id);
            $db->bind(':name', $name);
            $db->bind(':department', $department);
        }

        if ($db->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث الملف الشخصي بنجاح'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل في حفظ البيانات']);
        }

    } catch (Exception $e) {
        error_log("خطأ في تحديث الملف الشخصي: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في النظام: ' . $e->getMessage()]);
    }
}

function getAdminProfile() {
    global $db;

    try {
        $user_id = $_SESSION['user_id'] ?? 1;

        $db->query("
            SELECT ap.*, u.email, u.name
            FROM admin_profiles ap
            JOIN users u ON ap.user_id = u.id
            WHERE ap.user_id = :user_id
        ");
        $db->bind(':user_id', $user_id);
        $profile = $db->single();

        if ($profile) {
            echo json_encode([
                'success' => true,
                'profile' => $profile
            ]);
        } else {
            // إرجاع البيانات من جدول users فقط
            $db->query("SELECT name, email FROM users WHERE id = :user_id");
            $db->bind(':user_id', $user_id);
            $user = $db->single();

            echo json_encode([
                'success' => true,
                'profile' => [
                    'display_name' => $user['name'] ?? 'المدير',
                    'email' => $user['email'] ?? 'admin@transport.com',
                    'department' => ''
                ]
            ]);
        }
    } catch (Exception $e) {
        error_log("خطأ في جلب الملف الشخصي: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في النظام']);
    }
}
?>

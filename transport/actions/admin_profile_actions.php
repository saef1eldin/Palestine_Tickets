<?php
/**
 * إجراءات إدارة الملفات الشخصية للإدمن
 */

require_once '../../includes/init.php';

header('Content-Type: application/json');

$db = new Database();

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                throw new Exception('معرف المستخدم مطلوب');
            }

            $db->query("
                SELECT ap.*, u.email 
                FROM admin_profiles ap 
                JOIN users u ON ap.user_id = u.id 
                WHERE ap.user_id = :user_id
            ");
            $db->bind(':user_id', $user_id);
            $profile = $db->single();

            if (!$profile) {
                throw new Exception('الملف الشخصي غير موجود');
            }

            echo json_encode(['success' => true, 'data' => $profile]);
            break;

        case 'update':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $display_name = trim($_POST['display_name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $department = trim($_POST['department'] ?? 'إدارة النقل');

            if ($user_id <= 0) {
                throw new Exception('معرف المستخدم مطلوب');
            }
            if (empty($display_name)) {
                throw new Exception('اسم العرض مطلوب');
            }

            // التعامل مع رفع الصورة
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/';
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception('نوع الملف غير مدعوم. يرجى استخدام JPG, PNG أو GIF');
                }

                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) { // 2MB
                    throw new Exception('حجم الملف كبير جداً. الحد الأقصى 2MB');
                }

                $new_filename = 'admin-' . $user_id . '-' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = 'assets/' . $new_filename;
                } else {
                    throw new Exception('فشل في رفع الصورة');
                }
            }

            // تحديث الملف الشخصي
            if ($profile_image) {
                $db->query("
                    UPDATE admin_profiles 
                    SET display_name = :display_name, profile_image = :profile_image, 
                        bio = :bio, phone = :phone, department = :department, updated_at = NOW()
                    WHERE user_id = :user_id
                ");
                $db->bind(':profile_image', $profile_image);
            } else {
                $db->query("
                    UPDATE admin_profiles 
                    SET display_name = :display_name, bio = :bio, phone = :phone, 
                        department = :department, updated_at = NOW()
                    WHERE user_id = :user_id
                ");
            }

            $db->bind(':display_name', $display_name);
            $db->bind(':bio', $bio);
            $db->bind(':phone', $phone);
            $db->bind(':department', $department);
            $db->bind(':user_id', $user_id);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم تحديث الملف الشخصي بنجاح']);
            } else {
                throw new Exception('فشل في تحديث الملف الشخصي');
            }
            break;

        case 'create':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $display_name = trim($_POST['display_name'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $department = trim($_POST['department'] ?? 'إدارة النقل');

            if ($user_id <= 0) {
                throw new Exception('معرف المستخدم مطلوب');
            }
            if (empty($display_name)) {
                throw new Exception('اسم العرض مطلوب');
            }

            // التحقق من عدم وجود ملف شخصي بالفعل
            $db->query("SELECT id FROM admin_profiles WHERE user_id = :user_id");
            $db->bind(':user_id', $user_id);
            $existing = $db->single();

            if ($existing) {
                throw new Exception('يوجد ملف شخصي بالفعل لهذا المستخدم');
            }

            // إنشاء ملف شخصي جديد
            $db->query("
                INSERT INTO admin_profiles (user_id, display_name, bio, phone, department, created_at) 
                VALUES (:user_id, :display_name, :bio, :phone, :department, NOW())
            ");
            $db->bind(':user_id', $user_id);
            $db->bind(':display_name', $display_name);
            $db->bind(':bio', $bio);
            $db->bind(':phone', $phone);
            $db->bind(':department', $department);

            if ($db->execute()) {
                echo json_encode(['success' => true, 'message' => 'تم إنشاء الملف الشخصي بنجاح']);
            } else {
                throw new Exception('فشل في إنشاء الملف الشخصي');
            }
            break;

        default:
            throw new Exception('عملية غير مدعومة');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

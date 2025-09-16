<?php
// دوال المصادقة والتحقق من الصلاحيات

/**
 * التحقق مما إذا كان المستخدم مسجل الدخول
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * التحقق مما إذا كان المستخدم مديرًا
 *
 * @return bool
 */
function isAdmin() {
    if (!isset($_SESSION['user_role']) || empty($_SESSION['user_role'])) {
        return false;
    }
    
    $admin_roles = ['super_admin', 'transport_admin', 'notifications_admin', 'site_admin'];
    return in_array($_SESSION['user_role'], $admin_roles);
}

/**
 * التحقق من تسجيل الدخول وإعادة التوجيه إذا لم يكن المستخدم مسجل الدخول
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'يجب تسجيل الدخول للوصول إلى هذه الصفحة';
        header('Location: ' . APP_URL . 'login.php');
        exit;
    }
}

/**
 * التحقق من صلاحيات المدير وإعادة التوجيه إذا لم يكن المستخدم مديرًا
 */
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        $_SESSION['error_message'] = 'ليس لديك صلاحية للوصول إلى هذه الصفحة';
        // استخدام مسار نسبي بسيط بدلاً من getBaseUrl()
        header('Location: ../login.php');
        exit;
    }
}

/**
 * تسجيل دخول المستخدم
 *
 * @param PDO $pdo اتصال قاعدة البيانات
 * @param string $email البريد الإلكتروني
 * @param string $password كلمة المرور
 * @return array نتيجة تسجيل الدخول
 */
function loginUser($pdo, $email, $password) {
    try {
        // التحقق من البريد الإلكتروني
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return ['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
        }

        // التحقق من كلمة المرور (بدون تشفير)
            // Verify password using password_verify
            if (password_verify($password, $user['password_hashed'])) {
            // تعيين متغيرات الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // تسجيل تسجيل الدخول
                error_log("User logged in: {$user['name']}, Role: {$user['role']}");

            return ['success' => true, 'message' => 'تم تسجيل الدخول بنجاح'];
        } else {
            return ['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'];
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'حدث خطأ أثناء تسجيل الدخول'];
    }
}

/**
 * تسجيل خروج المستخدم
 */
function logoutUser() {
    // حذف متغيرات الجلسة
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);

    // إعادة تعيين الجلسة
    session_regenerate_id(true);

    return true;
}

/**
 * تنظيف المدخلات
 *
 * @param string $input النص المراد تنظيفه
 * @return string النص بعد التنظيف
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

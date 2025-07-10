<?php
// تضمين ملف التهيئة
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// التحقق من تسجيل الدخول وصلاحيات المدير
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    redirect('../login.php');
}

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// الحصول على سجلات تسجيل الدخول
$db->query("SELECT l.*, u.name, u.email FROM login_logs l 
           JOIN users u ON l.user_id = u.id 
           ORDER BY l.login_time DESC");
$login_logs = $db->resultSet();

// الحصول على سجلات التسجيل
$db->query("SELECT r.*, u.name, u.email FROM registration_logs r 
           JOIN users u ON r.user_id = u.id 
           ORDER BY r.registration_time DESC");
$registration_logs = $db->resultSet();

// تعيين عنوان الصفحة
$page_title = 'سجلات تسجيل الدخول والتسجيل';

// تضمين ملف الرأس
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
    </div>

    <!-- سجلات تسجيل الدخول -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">سجلات تسجيل الدخول</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="loginLogsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>المعرف</th>
                            <th>المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>عنوان IP</th>
                            <th>المتصفح</th>
                            <th>نظام التشغيل</th>
                            <th>الجهاز</th>
                            <th>وقت تسجيل الدخول</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($login_logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo htmlspecialchars($log['name']); ?></td>
                            <td><?php echo htmlspecialchars($log['email']); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($log['browser']); ?></td>
                            <td><?php echo htmlspecialchars($log['os']); ?></td>
                            <td><?php echo htmlspecialchars($log['device']); ?></td>
                            <td><?php echo format_date($log['login_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- سجلات التسجيل -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">سجلات التسجيل</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="registrationLogsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>المعرف</th>
                            <th>المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>عنوان IP</th>
                            <th>المتصفح</th>
                            <th>نظام التشغيل</th>
                            <th>الجهاز</th>
                            <th>وقت التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registration_logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo htmlspecialchars($log['name']); ?></td>
                            <td><?php echo htmlspecialchars($log['email']); ?></td>
                            <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td><?php echo htmlspecialchars($log['browser']); ?></td>
                            <td><?php echo htmlspecialchars($log['os']); ?></td>
                            <td><?php echo htmlspecialchars($log['device']); ?></td>
                            <td><?php echo format_date($log['registration_time']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// تضمين ملف التذييل
include 'includes/footer.php';
?>

<script>
$(document).ready(function() {
    $('#loginLogsTable').DataTable({
        order: [[7, 'desc']], // ترتيب حسب وقت تسجيل الدخول (تنازلي)
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json'
        }
    });
    
    $('#registrationLogsTable').DataTable({
        order: [[7, 'desc']], // ترتيب حسب وقت التسجيل (تنازلي)
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Arabic.json'
        }
    });
});
</script>

<?php
// بدء الجلسة إذا لم تكن نشطة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملف التهيئة
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';

// التحقق من تسجيل الدخول وصلاحيات المدير
$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    redirect('../login.php');
}

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// إعدادات ترقيم الصفحات
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// الحصول على العدد الإجمالي لسجلات تسجيل الدخول
$db->query("SELECT COUNT(*) as total FROM login_logs l JOIN users u ON l.user_id = u.id");
$total_login_logs = $db->single()['total'];
$total_login_pages = ceil($total_login_logs / $limit);

// الحصول على سجلات تسجيل الدخول مع ترقيم الصفحات
$db->query("SELECT l.*, u.name, u.email FROM login_logs l 
           JOIN users u ON l.user_id = u.id 
           ORDER BY l.login_time DESC
           LIMIT $limit OFFSET $offset");
$login_logs = $db->resultSet();

// الحصول على العدد الإجمالي لسجلات التسجيل
$db->query("SELECT COUNT(*) as total FROM registration_logs r JOIN users u ON r.user_id = u.id");
$total_registration_logs = $db->single()['total'];
$total_registration_pages = ceil($total_registration_logs / $limit);

// الحصول على سجلات التسجيل مع ترقيم الصفحات
$db->query("SELECT r.*, u.name, u.email FROM registration_logs r 
           JOIN users u ON r.user_id = u.id 
           ORDER BY r.registration_time DESC
           LIMIT $limit OFFSET $offset");
$registration_logs = $db->resultSet();

// تعيين عنوان الصفحة
$page_title = 'سجلات تسجيل الدخول والتسجيل';

// تضمين ملف الرأس
include 'includes/admin_header.php';
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
                <table class="table table-bordered table-striped table-hover">
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
                        <?php if (empty($login_logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">لا توجد سجلات تسجيل دخول</td>
                            </tr>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- ترقيم الصفحات لسجلات تسجيل الدخول -->
        <?php if ($total_login_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Login logs pagination">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">السابق</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_login_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_login_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">التالي</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- سجلات التسجيل -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">سجلات التسجيل</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
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
                        <?php if (empty($registration_logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">لا توجد سجلات تسجيل</td>
                            </tr>
                        <?php else: ?>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- ترقيم الصفحات لسجلات التسجيل -->
        <?php if ($total_registration_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Registration logs pagination">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">السابق</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_registration_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_registration_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">التالي</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// تضمين ملف التذييل
include 'includes/admin_footer.php';
?>

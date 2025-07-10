<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if(!$auth->isLoggedIn()) {
    redirect('login.php');
}

// استرجاع بيانات المستخدم الحالي
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// استرجاع الفواتير
$db->query("SELECT i.*, t.ticket_code, e.title as event_title
            FROM invoices i
            JOIN tickets t ON i.ticket_id = t.id
            JOIN events e ON t.event_id = e.id
            WHERE i.user_id = :user_id
            ORDER BY i.payment_date DESC");
$db->bind(':user_id', $_SESSION['user_id']);
$invoices = $db->resultSet();

// التحقق من صلاحيات المستخدم (هل هو مدير؟)
$is_admin = isset($user['role']) && $user['role'] === 'admin';
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row">
        <!-- القائمة الجانبية -->
        <div class="w-full md:w-1/4 mb-6 md:mb-0">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- معلومات المستخدم -->
                <div class="bg-purple-100 p-6 text-center">
                    <div class="w-20 h-20 bg-purple-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <?php if(!empty($user['profile_image'])): ?>
                            <img src="<?php echo $user['profile_image']; ?>" alt="<?php echo $user['name']; ?>" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <span class="text-3xl text-purple-700"><?php echo strtoupper(substr($user['name'] ?? 'User', 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-xl font-semibold text-purple-800"><?php echo $user['name']; ?></h3>
                    <p class="text-gray-600 text-sm"><?php echo $user['email']; ?></p>
                    <a href="profile.php" class="mt-3 inline-flex items-center text-purple-600 hover:text-purple-800 text-sm">
                        <i class="fas <?php echo get_icon('edit_profile'); ?> <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?>"></i>
                        <?php echo $lang['edit_profile_info'] ?? 'تعديل المعلومات الشخصية'; ?>
                    </a>
                </div>

                <!-- قائمة الصفحات -->
                <nav class="py-2">
                    <ul>
                        <li>
                            <a href="my-tickets.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('my_tickets'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="payment-methods.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('payment_methods'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="invoices.php" class="flex items-center px-6 py-3 bg-purple-50 text-purple-700 font-medium">
                                <i class="fas <?php echo get_icon('invoices'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['invoices'] ?? 'الفواتير'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('notifications'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('account_preferences'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['account_preferences'] ?? 'تفضيلات الحساب'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="security.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas <?php echo get_icon('security'); ?> text-purple-600 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['security'] ?? 'الأمان'; ?></span>
                            </a>
                        </li>
                        <li class="border-t border-gray-200">
                            <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas <?php echo get_icon('logout'); ?> <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?> w-6 text-center"></i>
                                <span><?php echo $lang['logout'] ?? 'تسجيل الخروج'; ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="w-full md:w-3/4 md:pr-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-purple-800 mb-6"><?php echo $lang['invoices'] ?? 'الفواتير'; ?></h1>

                <?php if(empty($invoices)): ?>
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-file-invoice text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-2"><?php echo $lang['no_invoices'] ?? 'لا توجد فواتير'; ?></h3>
                    <p class="text-gray-500 mb-6"><?php echo $lang['no_invoices_message'] ?? 'ستظهر هنا فواتير مشترياتك بمجرد إتمام عملية شراء'; ?></p>
                    <a href="events.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-search ml-2"></i>
                        <?php echo $lang['browse_events'] ?? 'تصفح الفعاليات المتاحة'; ?>
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['invoice_number'] ?? 'رقم الفاتورة'; ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['event_name'] ?? 'اسم الفعالية'; ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['payment_date'] ?? 'تاريخ الدفع'; ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['amount'] ?? 'المبلغ'; ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['status'] ?? 'الحالة'; ?>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['actions'] ?? 'إجراءات'; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($invoices as $invoice): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $invoice['invoice_number']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $invoice['event_title']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $lang['ticket_code'] ?? 'رمز التذكرة'; ?>: <?php echo $invoice['ticket_code']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($invoice['payment_date'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('H:i', strtotime($invoice['payment_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo number_format($invoice['amount'], 2); ?> ₪</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($invoice['status'] == 'paid'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <?php echo $lang['paid'] ?? 'مدفوعة'; ?>
                                    </span>
                                    <?php elseif($invoice['status'] == 'pending'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <?php echo $lang['pending'] ?? 'قيد الانتظار'; ?>
                                    </span>
                                    <?php elseif($invoice['status'] == 'cancelled'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <?php echo $lang['cancelled'] ?? 'ملغاة'; ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="download-invoice.php?id=<?php echo $invoice['id']; ?>" class="text-purple-600 hover:text-purple-900">
                                        <?php echo $lang['download_invoice'] ?? 'تحميل الفاتورة'; ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

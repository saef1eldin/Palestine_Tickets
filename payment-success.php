<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

if(!$auth->isLoggedIn()) {
    redirect('login.php');
}

if(!isset($_GET['order_id'])) {
    redirect('events.php');
}

$order_id = $_GET['order_id'];
$order = get_order_details($order_id);

if(!$order || $order['user_id'] != $_SESSION['user_id']) {
    // إذا لم يتم العثور على الطلب، نحاول البحث عن التذكرة مباشرة
    $db = new Database();
    $db->query("SELECT * FROM tickets WHERE id = :id AND user_id = :user_id");
    $db->bind(':id', $order_id);
    $db->bind(':user_id', $_SESSION['user_id']);
    $ticket = $db->single();

    if (!$ticket) {
        redirect('events.php');
    }

    // إنشاء كائن الطلب يدويًا
    $order = [
        'id' => $order_id,
        'user_id' => $_SESSION['user_id'],
        'event_id' => $ticket['event_id'],
        'quantity' => $ticket['quantity'] ?? 1,
        'total_amount' => 0, // سيتم تحديثه لاحقًا
        'created_at' => $ticket['purchase_date'] ?? date('Y-m-d H:i:s')
    ];
}

$event = get_event_by_id($order['event_id']);

// حساب المبلغ الإجمالي إذا كان صفرًا
if ($order['total_amount'] == 0 && isset($event['price'])) {
    $order['total_amount'] = $event['price'] * $order['quantity'];
}
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full mx-auto flex items-center justify-center">
                    <i class="fas fa-check-circle text-4xl"></i>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-purple-800 mb-4 text-improved"><?php echo $lang['payment_success_title'] ?? 'تمت عملية الدفع بنجاح!'; ?></h1>
            <p class="text-lg text-gray-600 mb-8 text-improved"><?php printf($lang['payment_success_thank_you'] ?? 'شكراً لك %s على حجز تذكرتك لـ %s.', $_SESSION['user_name'], $event['title']); ?></p>

            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                <h2 class="text-xl font-semibold text-purple-800 mb-4 text-improved"><?php echo $lang['booking_details'] ?? 'تفاصيل الحجز'; ?></h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['booking_number'] ?? 'رقم الحجز:'; ?></span>
                        <span class="text-improved"><?php echo $order_id; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['event'] ?? 'الفعالية:'; ?></span>
                        <span class="text-improved"><?php echo $event['title']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['date'] ?? 'التاريخ:'; ?></span>
                        <span class="text-improved"><?php echo function_exists('format_date') ? format_date($event['date_time']) : date('d/m/Y H:i', strtotime($event['date_time'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['location'] ?? 'المكان:'; ?></span>
                        <span class="text-improved"><?php echo $event['location']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['quantity'] ?? 'عدد التذاكر:'; ?></span>
                        <span class="text-improved"><?php echo $order['quantity']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-600 text-improved"><?php echo $lang['total'] ?? 'المجموع:'; ?></span>
                        <span class="font-bold text-purple-600 text-improved"><?php echo function_exists('format_price') ? format_price($order['total_amount']) : number_format($order['total_amount'], 2) . ' ₪'; ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8 rounded-lg text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?>"></i>
                    </div>
                    <div>
                        <p class="text-blue-700 text-improved"><?php echo $lang['ticket_email_notice'] ?? 'سيتم إرسال تفاصيل التذكرة إلى بريدك الإلكتروني المسجل.'; ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 <?php echo ($selected_lang == 'en') ? '' : 'sm:space-x-reverse'; ?>">
                <a href="my-tickets.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 text-improved">
                    <i class="fas fa-ticket-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                    <?php echo $lang['view_my_tickets'] ?? 'عرض تذاكري'; ?>
                </a>
                <a href="events.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors duration-200 text-improved">
                    <i class="fas fa-calendar-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                    <?php echo $lang['browse_other_events'] ?? 'استعراض فعاليات أخرى'; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
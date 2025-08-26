<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';

    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }

    // تشغيل التنظيف التلقائي للعناصر المنتهية
    run_expiry_cleanup();

    // استرجاع تذاكر المستخدم النشطة فقط (غير المنتهية)
    $db = new Database();
    $db->query("
        SELECT t.*, e.title as event_title, e.date_time, e.location, o.total_amount, o.quantity
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        JOIN orders o ON t.order_id = o.id
        WHERE t.user_id = :user_id AND t.status != 'expired' AND e.status != 'expired' AND e.date_time > NOW()
        ORDER BY e.date_time ASC
    ");
    $db->bind(':user_id', $_SESSION['user_id']);
    $tickets = $db->resultSet();

    // استرجاع تذاكر المستخدم المنتهية الصلاحية
    $expired_tickets = get_user_expired_tickets($_SESSION['user_id']);

    // استرجاع حجوزات المواصلات النشطة بطريقة آمنة
    $transport_bookings = [];
    $expired_transport_bookings = [];
    try {
        // التحقق من وجود جدول المواصلات أولاً
        $db = new Database();
        $db->query("SHOW TABLES LIKE 'transport_bookings'");
        $table_exists = $db->single();

        if ($table_exists) {
            // حجوزات المواصلات النشطة (غير المنتهية)
            $db->query("
                SELECT
                    tb.id,
                    tb.booking_code,
                    tb.passengers_count,
                    tb.total_amount,
                    tb.status,
                    tb.created_at,
                    COALESCE(t.departure_time, 'غير محدد') as departure_time,
                    COALESCE(t.arrival_time, 'غير محدد') as arrival_time,
                    COALESCE(sp.name, 'غير محدد') as starting_point_name,
                    COALESCE(tt.name, 'غير محدد') as transport_type_name,
                    COALESCE(e.title, 'غير محدد') as event_title,
                    COALESCE(e.location, 'غير محدد') as event_location,
                    COALESCE(e.date_time, NOW()) as event_date,
                    COALESCE(v.plate_number, '') as plate_number,
                    COALESCE(v.model, '') as model,
                    COALESCE(d.name, '') as driver_name,
                    COALESCE(d.phone, '') as driver_phone
                FROM transport_bookings tb
                LEFT JOIN transport_trips t ON tb.trip_id = t.id
                LEFT JOIN transport_starting_points sp ON t.starting_point_id = sp.id
                LEFT JOIN transport_types tt ON t.transport_type_id = tt.id
                LEFT JOIN events e ON tb.event_id = e.id
                LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
                LEFT JOIN transport_drivers d ON v.driver_id = d.id
                WHERE tb.user_id = :user_id
                    AND tb.status NOT IN ('expired', 'cancelled')
                    AND (e.status != 'expired' OR e.status IS NULL)
                    AND (e.date_time > NOW() OR e.date_time IS NULL)
                ORDER BY tb.created_at DESC
            ");
            $db->bind(':user_id', $_SESSION['user_id']);
            $transport_bookings = $db->resultSet();

            // حجوزات المواصلات المنتهية
            $expired_transport_bookings = get_user_expired_transport_bookings($_SESSION['user_id']);
        }
    } catch (Exception $e) {
        // في حالة الخطأ، اتركه فارغ ولا تعطل الصفحة
        $transport_bookings = [];
        $expired_transport_bookings = [];
        error_log("Transport bookings error: " . $e->getMessage());
    }

} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>

<div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-center text-purple-800 mb-8 text-improved"><?php echo $lang['my_tickets_title'] ?? 'تذاكري المحجوزة'; ?></h1>

    <?php if(empty($tickets)): ?>
    <div class="max-w-2xl mx-auto bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?>"></i>
            </div>
            <div>
                <p class="text-blue-700 text-improved">
                    <?php echo $lang['no_tickets_message'] ?? 'ليس لديك أي تذاكر محجوزة بعد.'; ?>
                    <a href="events.php" class="font-medium underline"><?php echo $lang['browse_events'] ?? 'تصفح الفعاليات المتاحة'; ?></a>
                </p>
            </div>
        </div>
    </div>
    <?php else: ?>

    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['ticket_code'] ?? 'رمز التذكرة'; ?>
                        </th>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['event'] ?? 'الفعالية'; ?>
                        </th>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['date'] ?? 'التاريخ'; ?>
                        </th>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['location'] ?? 'المكان'; ?>
                        </th>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['amount'] ?? 'المبلغ'; ?>
                        </th>
                        <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $lang['status'] ?? 'الحالة'; ?>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($tickets as $ticket): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $ticket['ticket_code']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $ticket['event_title']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <div class="text-sm text-gray-500 text-improved"><?php echo format_date($ticket['date_time']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <div class="text-sm text-gray-500 text-improved"><?php echo $ticket['location']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <div class="text-sm font-medium text-gray-900 text-improved"><?php echo format_price($ticket['total_amount'] / $ticket['quantity']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                            <?php if($ticket['used']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                <?php echo $lang['used'] ?? 'مستخدمة'; ?>
                            </span>
                            <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <?php echo $lang['valid'] ?? 'صالحة'; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

    <!-- قسم التذاكر المنتهية الصلاحية -->
    <?php if(!empty($expired_tickets)): ?>
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-center text-red-800 mb-8">التذاكر المنتهية الصلاحية</h2>

        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                رمز التذكرة
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                الفعالية
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                التاريخ
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                المكان
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                المبلغ
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                الحالة
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($expired_tickets as $ticket): ?>
                        <tr class="hover:bg-red-50 opacity-75">
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $ticket['ticket_code']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $ticket['event_title']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved"><?php echo format_date($ticket['date_time']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved"><?php echo $ticket['location']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo format_price($ticket['total_amount'] / $ticket['quantity']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    منتهية الصلاحية
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- قسم حجوزات المواصلات -->
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-center text-blue-800 mb-8">حجوزات المواصلات</h2>

        <?php if(empty($transport_bookings)): ?>
        <div class="max-w-2xl mx-auto bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-bus text-blue-500 <?php echo ($selected_lang == 'en') ? 'mr-3' : 'ml-3'; ?>"></i>
                </div>
                <div>
                    <p class="text-blue-700 text-improved">
                        ليس لديك أي حجوزات مواصلات بعد.
                        <a href="events.php" class="font-medium underline">تصفح الفعاليات واحجز مواصلاتك</a>
                    </p>
                </div>
            </div>
        </div>
        <?php else: ?>

        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                رقم الحجز
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الفعالية
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                نقطة الانطلاق
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                وسيلة النقل
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                موعد الانطلاق
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                عدد الركاب
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                المبلغ
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الحالة
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($transport_bookings as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo htmlspecialchars($booking['booking_code'] ?? 'غير محدد'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo htmlspecialchars($booking['event_title'] ?? 'غير محدد'); ?></div>
                                <div class="text-sm text-gray-500 text-improved"><?php echo date('Y-m-d H:i', strtotime($booking['event_date'] ?? 'now')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved"><?php echo htmlspecialchars($booking['starting_point_name'] ?? 'غير محدد'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved"><?php echo htmlspecialchars($booking['transport_type_name'] ?? 'غير محدد'); ?></div>
                                <?php if (!empty($booking['model'])): ?>
                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($booking['model'] . ' - ' . $booking['plate_number']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved">
                                    <?php
                                    if ($booking['departure_time'] && $booking['departure_time'] != 'غير محدد') {
                                        echo date('H:i', strtotime($booking['departure_time']));
                                    } else {
                                        echo 'غير محدد';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $booking['passengers_count'] ?? '1'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo ($booking['total_amount'] ?? '0'); ?> ₪</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <?php
                                $status_colors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                    'expired' => 'bg-gray-100 text-gray-800'
                                ];
                                $status_text = [
                                    'pending' => 'في الانتظار',
                                    'confirmed' => 'مؤكد',
                                    'cancelled' => 'ملغي',
                                    'completed' => 'مكتمل',
                                    'expired' => 'منتهي الصلاحية'
                                ];
                                $current_status = $booking['status'] ?? 'pending';
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_colors[$current_status] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $status_text[$current_status] ?? $current_status; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <!-- قسم حجوزات المواصلات المنتهية الصلاحية -->
    <?php if(!empty($expired_transport_bookings)): ?>
    <div class="mt-16">
        <h2 class="text-2xl font-bold text-center text-red-800 mb-8">حجوزات المواصلات المنتهية الصلاحية</h2>

        <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                رقم الحجز
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                الفعالية
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                نقطة الانطلاق
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                موعد الانطلاق
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                عدد الركاب
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                المبلغ
                            </th>
                            <th scope="col" class="px-6 py-3 text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?> text-xs font-medium text-red-700 uppercase tracking-wider">
                                الحالة
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($expired_transport_bookings as $booking): ?>
                        <tr class="hover:bg-red-50 opacity-75">
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo htmlspecialchars($booking['booking_code'] ?? 'غير محدد'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo htmlspecialchars($booking['event_title'] ?? 'غير محدد'); ?></div>
                                <div class="text-sm text-gray-500 text-improved"><?php echo date('Y-m-d H:i', strtotime($booking['event_date'] ?? 'now')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved"><?php echo htmlspecialchars($booking['starting_point_name'] ?? 'غير محدد'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm text-gray-500 text-improved">
                                    <?php
                                    if ($booking['departure_time'] && $booking['departure_time'] != 'غير محدد') {
                                        echo date('H:i', strtotime($booking['departure_time']));
                                    } else {
                                        echo 'غير محدد';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo $booking['passengers_count'] ?? '1'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <div class="text-sm font-medium text-gray-900 text-improved"><?php echo ($booking['total_amount'] ?? '0'); ?> ₪</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-<?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    منتهي الصلاحية
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
require_once 'includes/footer.php';
?>
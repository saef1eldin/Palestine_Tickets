<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';

    // التحقق من وجود معرف الحدث
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        redirect('events.php');
    }

    $event_id = intval($_GET['id']);
    $event = get_event_by_id($event_id);

    if (!$event) {
        redirect('events.php');
    }

} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>

<style>
    .hover-scale {
        transition: transform 0.3s ease;
    }
    .hover-scale:hover {
        transform: scale(1.05);
    }
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>

<section class="py-16 bg-gradient-to-b from-gray-50 to-white min-h-[60vh]">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-8">
            <div class="md:w-2/3">
                <h1 class="text-3xl md:text-4xl font-bold text-purple-800 mb-6"><?php echo $event['title']; ?></h1>
                <div class="relative overflow-hidden rounded-xl shadow-xl mb-8">
                    <img src="<?php echo !empty($event['image']) ? $event['image'] : 'assets/img/event-placeholder.jpg'; ?>" class="w-full h-auto object-cover" alt="<?php echo $event['title']; ?>">
                    <?php if(strtotime($event['date_time']) < strtotime('+7 days')): ?>
                    <div class="absolute top-4 right-4 bg-red-500 text-white text-sm font-bold px-4 py-1 rounded-full">قريباً</div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-purple-700 mb-4"><?php echo $lang['event_description'] ?? 'وصف الفعالية'; ?></h2>
                    <p class="text-gray-700 leading-relaxed"><?php echo $event['description']; ?></p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-purple-700 mb-4"><?php echo $lang['event_details'] ?? 'تفاصيل الفعالية'; ?></h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="bg-purple-100 text-purple-700 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-sm">
                                <i class="fas fa-calendar-alt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-500"><?php echo $lang['event_date_time'] ?? 'التاريخ والوقت'; ?></h3>
                                <p class="font-bold"><?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="bg-purple-100 text-purple-700 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-sm">
                                <i class="fas fa-map-marker-alt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-500"><?php echo $lang['event_location'] ?? 'المكان'; ?></h3>
                                <p class="font-bold"><?php echo $event['location']; ?></p>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="bg-purple-100 text-purple-700 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-sm">
                                <i class="fas fa-tag text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-500"><?php echo $lang['event_price'] ?? 'السعر'; ?></h3>
                                <?php if (!empty($event['original_price'])): ?>
                                <div>
                                    <span class="line-through text-gray-500 text-sm"><?php echo $event['original_price']; ?> ₪</span>
                                    <p class="font-bold text-red-600"><?php echo $event['price']; ?> ₪</p>
                                </div>
                                <?php else: ?>
                                <p class="font-bold text-blue-700"><?php echo $event['price']; ?> ₪</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            <div class="bg-purple-100 text-purple-700 rounded-full w-12 h-12 flex items-center justify-center mr-4 shadow-sm">
                                <i class="fas fa-music text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm text-gray-500"><?php echo $lang['event_type'] ?? 'النوع'; ?></h3>
                                <p class="font-bold"><?php echo $event['category']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:w-1/3">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <h2 class="text-2xl font-bold text-purple-700 mb-4"><?php echo $lang['book_tickets'] ?? 'حجز التذاكر'; ?></h2>
                    <p class="text-gray-600 mb-6"><?php echo $lang['book_ticket_now'] ?? 'احجز تذكرتك الآن لتضمن حضورك لهذه الفعالية الرائعة. عدد التذاكر محدود!'; ?></p>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
                        <div class="flex items-center">
                            <div class="text-blue-500 mr-3">
                                <i class="fas fa-ticket-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-blue-800"><?php echo $lang['available_tickets'] ?? 'التذاكر المتاحة'; ?>: <span class="font-bold"><?php echo $event['available_tickets']; ?></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600"><?php echo $lang['ticket_price'] ?? 'سعر التذكرة'; ?>:</span>
                            <?php if (!empty($event['original_price'])): ?>
                            <div class="text-right">
                                <span class="line-through text-gray-500 text-sm block"><?php echo $event['original_price']; ?> ₪</span>
                                <span class="font-bold text-red-600"><?php echo $event['price']; ?> ₪</span>
                            </div>
                            <?php else: ?>
                            <span class="font-bold text-blue-700"><?php echo $event['price']; ?> ₪</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?php echo $lang['service_fee'] ?? 'رسوم الخدمة'; ?>:</span>
                            <span class="font-bold">5 ₪</span>
                        </div>
                        <div class="border-t border-gray-200 my-2"></div>
                        <div class="flex justify-between">
                            <span class="font-bold"><?php echo $lang['total_amount'] ?? 'المجموع'; ?>:</span>
                            <span class="font-bold text-blue-700"><?php echo $event['price'] + 5; ?> ₪</span>
                        </div>
                    </div>

                    <?php if($event['available_tickets'] > 0): ?>
                    <a href="checkout.php?event_id=<?php echo $event['id']; ?>" class="bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg font-bold w-full block text-center transition-all duration-300 hover:shadow-lg pulse-animation mb-4">
                        <?php echo $lang['book_now'] ?? 'احجز الآن'; ?>
                    </a>

                    <!-- زر المواصلات -->
                    <a href="transport/starting_points.php?event_id=<?php echo $event['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-bold w-full block text-center transition-all duration-300 hover:shadow-lg">
                        <i class="fas fa-bus <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                        <?php echo $lang['book_transport'] ?? 'اختار مواصلات للفعالية'; ?>
                    </a>
                    <?php else: ?>
                    <button disabled class="bg-gray-400 text-white py-3 px-6 rounded-lg font-bold w-full block text-center cursor-not-allowed mb-4">
                        <?php echo $lang['sold_out'] ?? 'نفدت التذاكر'; ?>
                    </button>

                    <!-- زر المواصلات متاح حتى لو نفدت التذاكر -->
                    <a href="transport/starting_points.php?event_id=<?php echo $event['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-bold w-full block text-center transition-all duration-300 hover:shadow-lg">
                        <i class="fas fa-bus <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                        <?php echo $lang['book_transport'] ?? 'اختار مواصلات للفعالية'; ?>
                    </a>
                    <?php endif; ?>

                    <div class="flex items-center justify-center mt-6 text-gray-500 text-sm">
                        <i class="fas fa-shield-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                        <span><?php echo $lang['secure_payment'] ?? 'دفع آمن ومضمون 100%'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
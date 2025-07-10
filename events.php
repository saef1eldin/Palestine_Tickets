<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';

    $events = get_events();
} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>
<style>
    .pulse-animation {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .hover-scale {
        transition: transform 0.3s ease;
    }
    .hover-scale:hover {
        transform: scale(1.05);
    }
</style>
<section class="py-16 bg-gradient-to-b from-gray-50 to-white min-h-[60vh]">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold text-center mb-12 text-purple-800 relative text-improved">
            <span class="relative inline-block"><?php echo $lang['upcoming_events'] ?? 'الفعاليات القادمة'; ?>
                <span class="absolute bottom-0 <?php echo ($selected_lang == 'en') ? 'left-0' : 'right-0'; ?> w-full h-1 bg-gradient-to-r from-purple-400 to-purple-800"></span>
            </span>
        </h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <?php foreach($events as $event): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-gray-100">
                <div class="relative overflow-hidden">
                    <img src="<?php echo !empty($event['image']) ? $event['image'] : 'assets/img/event-placeholder.jpg'; ?>" class="w-full h-56 object-cover transition-transform duration-500 hover:scale-110" alt="<?php echo $event['title']; ?>">
                    <?php if(strtotime($event['date_time']) < strtotime('+7 days')): ?>
                    <div class="absolute top-4 <?php echo ($selected_lang == 'en') ? 'left-4' : 'right-4'; ?> bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full"><?php echo $lang['coming_soon'] ?? 'قريباً'; ?></div>
                    <?php endif; ?>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h5 class="font-bold text-xl mb-2 text-purple-700 text-improved"><?php echo $event['title']; ?></h5>
                    <p class="text-gray-600 mb-3 flex-1 text-improved"><?php echo mb_substr($event['description'], 0, 100); ?>...</p>
                    <div class="flex flex-col gap-1 mb-2 text-sm text-gray-500">
                        <span class="flex items-center"><i class="fas fa-calendar-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?> text-purple-500"></i><?php echo ($selected_lang == 'en') ? date('M d, Y - H:i', strtotime($event['date_time'])) : date('Y-m-d H:i', strtotime($event['date_time'])); ?></span>
                        <span class="flex items-center"><i class="fas fa-map-marker-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?> text-purple-500"></i><?php echo $event['location']; ?></span>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div>
                            <?php if (!empty($event['original_price'])): ?>
                            <span class="line-through text-gray-500 text-sm mr-2"><?php echo $event['original_price']; ?> ₪</span>
                            <span class="font-bold text-lg text-red-600"><?php echo $event['price']; ?> ₪</span>
                            <?php else: ?>
                            <span class="font-bold text-lg text-blue-700"><?php echo $event['price']; ?> ₪</span>
                            <?php endif; ?>
                        </div>
                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-bold transition-all duration-300 hover:shadow-lg text-improved"><?php echo $lang['event_details'] ?? 'تفاصيل الفعالية'; ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php
require_once 'includes/footer.php';
?>
<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';
} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>
<link rel="stylesheet" href="assets/css/responsive.css">
<section class="hero-gradient text-white py-20">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="w-full md:w-1/2 text-center md:text-right">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight text-wrap"><?php echo $lang['hero_title'] ?? 'عنوان البطل'; ?></h1>
                <p class="text-lg md:text-xl mb-8 opacity-90 text-wrap"><?php echo $lang['hero_subtitle'] ?? 'احجز تذاكرك الآن لأفضل العروض الموسيقية والمسرحية والثقافية بنقرات بسيطة وبأمان تام'; ?></p>

                <div class="flex-responsive">
                    <a href="events.php" class="bg-white text-purple-700 hover:bg-gray-100 font-bold py-4 px-8 rounded-lg text-center shadow-lg hover:shadow-xl transition-all"><?php echo $lang['explore_events'] ?? 'استكشف الفعاليات'; ?></a>
                    <a href="#how-it-works" class="border-2 border-white hover:bg-white hover:text-purple-700 font-bold py-4 px-8 rounded-lg text-center transition-all"><?php echo $lang['how_it_works_link'] ?? 'كيف تعمل الخدمة؟'; ?></a>
                </div>
            </div>
            <div class="w-full md:w-1/2 flex justify-center">
                <img src="assets/img/tickets-hero-palestine.png" alt="تذاكر الحفلات" class="w-full max-w-md rounded-lg shadow-2xl hover-scale" loading="lazy" />
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 text-purple-800 relative">
            <span class="relative inline-block"><?php echo $lang['upcoming_events'] ?? 'الفعاليات القادمة'; ?>
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-3/4 h-1 bg-gradient-to-r from-purple-400 to-purple-800 rounded-full -mb-2"></span>
            </span>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
            <?php
            // تشغيل تنظيف العناصر المنتهية قبل عرض الفعاليات
            run_expiry_cleanup();

            $featured_events = get_events(6);
            foreach($featured_events as $event):
            ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 card-responsive">
                <div class="relative overflow-hidden">
                    <img src="<?php echo !empty($event['image']) ? $event['image'] : 'assets/img/event-placeholder.jpg'; ?>"
                         class="w-full h-56 object-cover event-image transition-transform duration-500 hover:scale-110"
                         alt="<?php echo htmlspecialchars($event['title']); ?>"
                         loading="lazy">
                    <?php if(strtotime($event['date_time']) < strtotime('+7 days')): ?>
                    <div class="absolute top-4 right-4 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full"><?php echo $lang['coming_soon'] ?? 'قريباً'; ?></div>
                    <?php endif; ?>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <h5 class="font-bold text-xl mb-2 text-purple-700 text-wrap"><?php echo htmlspecialchars($event['title']); ?></h5>
                    <p class="text-gray-600 mb-3 flex-1 text-wrap"><?php echo htmlspecialchars(mb_substr($event['description'], 0, 100)); ?>...</p>
                    <div class="flex flex-col gap-1 mb-2 text-sm text-gray-500">
                        <span class="flex items-center text-wrap"><i class="fas fa-calendar-alt ml-2 text-purple-500 flex-shrink-0"></i><?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?></span>
                        <span class="flex items-center text-wrap"><i class="fas fa-map-marker-alt ml-2 text-purple-500 flex-shrink-0"></i><?php echo htmlspecialchars($event['location']); ?></span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mt-2">
                        <span class="font-bold text-lg text-blue-700"><?php echo $event['price']; ?> ₪</span>
                        <a href="event-details.php?id=<?php echo $event['id']; ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-bold transition-all duration-300 hover:shadow-lg w-full sm:w-auto text-center"><?php echo $lang['event_details_button'] ?? 'تفاصيل الفعالية'; ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="events.php" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg font-bold inline-flex items-center transition-all duration-300 hover:shadow-xl">
                <span><?php echo $lang['view_all_events'] ?? 'عرض جميع الفعاليات'; ?></span>
                <i class="fas fa-arrow-left mr-2"></i>
            </a>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-20 bg-gradient-to-b from-gray-50 to-white">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 text-purple-800 relative">
            <span class="relative inline-block"><?php echo $lang['how_it_works_title'] ?? 'كيف تعمل الخدمة؟'; ?>
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-3/4 h-1 bg-gradient-to-r from-purple-400 to-purple-800 rounded-full -mb-2"></span>
            </span>
        </h2>
        <div class="grid md:grid-cols-3 gap-10">
            <div class="bg-white rounded-xl shadow-xl p-8 flex flex-col items-center text-center transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-t-4 border-purple-500">
                <div class="bg-purple-100 text-purple-700 rounded-full w-20 h-20 flex items-center justify-center mb-6 text-3xl shadow-md"><i class="fas fa-search"></i></div>
                <h3 class="font-bold text-2xl mb-3 text-purple-700"><?php echo $lang['browse_events'] ?? 'استعرض الفعاليات'; ?></h3>
                <p class="text-gray-600 text-wrap"><?php echo $lang['how_it_works_step1_desc'] ?? 'استعرض مجموعة متنوعة من الحفلات الموسيقية والمسرحيات والمعارض الفنية والفعاليات الثقافية المميزة.'; ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-xl p-8 flex flex-col items-center text-center transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-t-4 border-purple-600">
                <div class="bg-purple-100 text-purple-700 rounded-full w-20 h-20 flex items-center justify-center mb-6 text-3xl shadow-md"><i class="fas fa-ticket-alt"></i></div>
                <h3 class="font-bold text-2xl mb-3 text-purple-700"><?php echo $lang['book_ticket'] ?? 'احجز تذكرتك'; ?></h3>
                <p class="text-gray-600 text-wrap"><?php echo $lang['how_it_works_step2_desc'] ?? 'اختر الفعالية المفضلة لديك واحجز تذاكرك بسهولة وسرعة من خلال نظام حجز آمن ومبسط.'; ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-xl p-8 flex flex-col items-center text-center transform transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border-t-4 border-purple-700">
                <div class="bg-purple-100 text-purple-700 rounded-full w-20 h-20 flex items-center justify-center mb-6 text-3xl shadow-md"><i class="fas fa-envelope-open-text"></i></div>
                <h3 class="font-bold text-2xl mb-3 text-purple-700"><?php echo $lang['receive_ticket'] ?? 'استلم تذكرتك'; ?></h3>
                <p class="text-gray-600 text-wrap"><?php echo $lang['how_it_works_step3_desc'] ?? 'استلم تذاكرك الإلكترونية فوراً على بريدك الإلكتروني واحفظها على هاتفك للدخول السريع إلى الفعالية.'; ?></p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 text-purple-800 relative">
            <span class="relative inline-block"><?php echo $lang['how_to_book_title'] ?? 'كيفية حجز التذاكر'; ?>
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-3/4 h-1 bg-gradient-to-r from-purple-400 to-purple-800 rounded-full -mb-2"></span>
            </span>
        </h2>
        <div class="flex flex-col md:flex-row items-center justify-center gap-8">
            <div class="md:w-1/3 flex justify-center">
                <img src="assets/img/tickets-search.svg" alt="البحث عن التذاكر" class="w-full max-w-sm rounded-lg shadow-lg hover-scale" />
            </div>
            <div class="md:w-2/3 flex flex-col gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="bg-purple-100 text-purple-700 rounded-full w-10 h-10 flex items-center justify-center text-xl shadow-sm">1</div>
                        <h3 class="font-bold text-xl text-purple-700"><?php echo $lang['search_events_step'] ?? 'البحث عن الفعاليات'; ?></h3>
                    </div>
                    <p class="text-gray-600 pr-14"><?php echo $lang['search_events_desc'] ?? 'تصفح بسهولة واكتشف أحدث الفعاليات والعروض المميزة حسب التاريخ أو الموقع أو النوع، واعثر على ما يناسب ذوقك واهتماماتك.'; ?></p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="bg-purple-100 text-purple-700 rounded-full w-10 h-10 flex items-center justify-center text-xl shadow-sm">2</div>
                        <h3 class="font-bold text-xl text-purple-700"><?php echo $lang['choose_seats_step'] ?? 'اختيار المقاعد'; ?></h3>
                    </div>
                    <p class="text-gray-600 pr-14"><?php echo $lang['choose_seats_desc'] ?? 'اختر أفضل المقاعد المتاحة من خلال مخطط القاعة التفاعلي واستمتع بأفضل رؤية وتجربة مميزة للعرض.'; ?></p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="bg-purple-100 text-purple-700 rounded-full w-10 h-10 flex items-center justify-center text-xl shadow-sm">3</div>
                        <h3 class="font-bold text-xl text-purple-700"><?php echo $lang['complete_payment_step'] ?? 'إتمام عملية الدفع'; ?></h3>
                    </div>
                    <p class="text-gray-600 pr-14"><?php echo $lang['complete_payment_desc'] ?? 'أتمم عملية الدفع بسهولة وأمان من خلال بوابة دفع آمنة بنسبة 100٪ واحصل على تذاكرك فوراً على بريدك الإلكتروني.'; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-12 text-purple-800 relative">
            <span class="relative inline-block"><?php echo $lang['customer_reviews_title'] ?? 'آراء عملائنا'; ?>
                <span class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-3/4 h-1 bg-gradient-to-r from-purple-400 to-purple-800 rounded-full -mb-2"></span>
            </span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg"><?php echo $lang['customer1_name'] ?? 'سمر الغزالي'; ?></h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"<?php echo $lang['customer1_review'] ?? 'تجربة رائعة من البداية إلى النهاية! حجزت تذاكر لحفلة موسيقية وكانت العملية سلسة جداً. أحببت خاصية اختيار المقاعد التفاعلية والتي مكنتني من اختيار أفضل مكان في القاعة. سأوصي بهذا الموقع لجميع أصدقائي.'; ?>"</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg"><?php echo $lang['customer2_name'] ?? 'خالد النجار'; ?></h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"<?php echo $lang['customer2_review'] ?? 'استخدمت الموقع لحجز تذاكر لعائلتي بأكملها لحضور مسرحية، وكانت التجربة ممتازة. الأسعار معقولة والخيارات متنوعة. أعجبني أيضاً نظام الإشعارات الذي يذكرني بموعد الفعالية قبل يوم من انعقادها.'; ?>"</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg"><?php echo $lang['customer3_name'] ?? 'رنا الحسيني'; ?></h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"<?php echo $lang['customer3_review'] ?? 'موقع تذاكر فلسطين هو الخيار الأمثل لحجز تذاكر الفعاليات الثقافية والفنية. أحب تنوع الفعاليات المعروضة وسهولة البحث. خدمة العملاء ممتازة وسريعة الاستجابة. سعيدة جداً بتجربتي معهم.'; ?>"</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg">سمر الغزالي</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"تجربة رائعة من البداية إلى النهاية! حجزت تذاكر لحفلة موسيقية وكانت العملية سلسة جداً. أحببت خاصية اختيار المقاعد التفاعلية والتي مكنتني من اختيار أفضل مكان في القاعة. سأوصي بهذا الموقع لجميع أصدقائي."</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg">خالد النجار</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"استخدمت الموقع لحجز تذاكر لعائلتي بأكملها لحضور مسرحية، وكانت التجربة ممتازة. الأسعار معقولة والخيارات متنوعة. أعجبني أيضاً نظام الإشعارات الذي يذكرني بموعد الفعالية قبل يوم من انعقادها."</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
                <div class="mb-4">
                    <div>
                        <h4 class="font-bold text-lg">رنا الحسيني</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600">"موقع تذاكر فلسطين هو الخيار الأمثل لحجز تذاكر الفعاليات الثقافية والفنية. أحب تنوع الفعاليات المعروضة وسهولة البحث. خدمة العملاء ممتازة وسريعة الاستجابة. سعيدة جداً بتجربتي معهم."</p>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-purple-700 text-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="w-full lg:w-2/3 text-center lg:text-right">
                <h2 class="text-2xl md:text-3xl font-bold mb-2 text-wrap"><?php echo $lang['newsletter_subscribe'] ?? 'اشترك في نشرتنا البريدية واحصل على خصم 10٪'; ?></h2>
                <p class="opacity-90 text-wrap"><?php echo $lang['newsletter_description'] ?? 'كن أول من يعلم بالفعاليات الجديدة والعروض الحصرية والخصومات الخاصة لمشتركي النشرة البريدية'; ?></p>
            </div>
            <div class="w-full lg:w-1/3">
                <form class="newsletter-form flex-responsive">
                    <input type="email"
                           placeholder="<?php echo $lang['newsletter_placeholder'] ?? 'أدخل بريدك الإلكتروني'; ?>"
                           class="flex-1 py-3 px-4 rounded-lg focus:outline-none text-gray-700 text-right"
                           required
                           style="font-size: 16px;">
                    <button type="submit"
                            class="bg-purple-900 hover:bg-purple-950 py-3 px-6 rounded-lg transition-colors duration-300 whitespace-nowrap"
                            data-original-text="<?php echo $lang['newsletter_button'] ?? 'اشترك'; ?>">
                        <?php echo $lang['newsletter_button'] ?? 'اشترك'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php
require_once 'includes/footer.php';
?>
<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// متغيرات أساسية
$selected_lang = 'ar';
$lang = [
    'site_name' => 'تذاكر فلسطين',
    'home' => 'الرئيسية',
    'events' => 'الفعاليات',
    'about' => 'من نحن',
    'contact' => 'اتصل بنا',
    'login' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'language' => 'العربية'
];

// دالة get_icon بسيطة
function get_icon($name) {
    $icons = [
        'my_tickets' => 'fa-ticket-alt',
        'logout' => 'fa-sign-out-alt',
        'user' => 'fa-user'
    ];
    return $icons[$name] ?? 'fa-circle';
}

// تحميل header مباشرة
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang; ?>" dir="<?php echo ($selected_lang == 'en') ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>من نحن - تذاكر فلسطين</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .text-improved {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="index.php" class="text-2xl font-bold text-purple-800 flex items-center text-improved">
                        <i class="fas fa-ticket-alt ml-2"></i>
                        <span><?php echo $lang['site_name']; ?></span>
                    </a>
                </div>

                <!-- Navigation Menu -->
                <div class="hidden md:flex items-center space-x-6 space-x-reverse">
                    <a href="index.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['home']; ?></a>
                    <a href="events.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['events']; ?></a>
                    <a href="about.php" class="text-purple-600 font-semibold transition-colors duration-200 text-improved"><?php echo $lang['about']; ?></a>
                    <a href="contact.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['contact']; ?></a>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4 space-x-reverse">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200">
                            <i class="fas fa-user ml-1"></i>
                            <span class="text-improved"><?php echo $_SESSION['user_name'] ?? 'المستخدم'; ?></span>
                            <i class="fas fa-chevron-down mr-1 text-xs"></i>
                        </button>
                        <div class="absolute left-0 top-full bg-white rounded-lg shadow-lg z-10 hidden group-hover:block w-48">
                            <a href="my-tickets.php" class="block px-4 py-2 hover:bg-gray-100 text-improved">
                                <i class="fas fa-ticket-alt text-purple-600 w-5 ml-2"></i>
                                تذاكري
                            </a>
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-improved text-red-600">
                                <i class="fas fa-sign-out-alt w-5 ml-2"></i>
                                تسجيل الخروج
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Login Button -->
                    <a href="login.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200">
                        <i class="fas fa-user ml-2"></i>
                        <span class="text-improved whitespace-nowrap"><?php echo $lang['login']; ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1">

<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="py-16 bg-gradient-to-b from-purple-100 to-white min-h-[40vh] flex items-center">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-3xl md:text-5xl font-bold mb-6 text-purple-800 text-improved">
                    <?php echo $lang['about_title'] ?? 'من نحن'; ?>
                </h1>
                <div class="w-24 h-1 bg-gradient-to-r from-purple-400 to-purple-800 mx-auto mb-8 rounded-full"></div>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto text-improved">
                    <?php echo $lang['about_subtitle'] ?? 'تعرف على فريقنا وقصتنا ومهمتنا لتقديم أفضل خدمة لعملائنا'; ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-5xl mx-auto">
                <div class="flex flex-col md:flex-row items-center gap-8 mb-16">
                    <div class="md:w-1/2">
                        <div class="bg-purple-50 p-2 inline-block rounded-lg mb-4">
                            <i class="fas fa-bullseye text-purple-600 text-3xl"></i>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-semibold text-purple-800 mb-4 text-improved">
                            <?php echo $lang['our_mission'] ?? 'مهمتنا'; ?>
                        </h2>
                        <div class="w-16 h-1 bg-purple-600 mb-6 rounded-full"></div>
                        <p class="text-gray-600 leading-relaxed text-improved">
                            <?php echo $lang['our_mission_text'] ?? 'نسعى لتقديم أفضل خدمة حجز تذاكر للفعاليات الثقافية والفنية في فلسطين. مهمتنا هي ربط الجمهور بالفعاليات المميزة وتسهيل عملية الحجز والدفع بطريقة آمنة ومريحة.'; ?>
                        </p>
                    </div>
                    <div class="md:w-1/2">
                        <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg p-8 text-center">
                            <i class="fas fa-ticket-alt text-purple-600 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-purple-800 mb-2">خدمة التذاكر</h3>
                            <p class="text-purple-600">حجز سهل وآمن</p>
                        </div>
                    </div>
                </div>

                <!-- Team Section -->
                <div class="flex flex-col md:flex-row-reverse items-center gap-8 mb-16">
                    <div class="md:w-1/2">
                        <div class="bg-purple-50 p-2 inline-block rounded-lg mb-4">
                            <i class="fas fa-users text-purple-600 text-3xl"></i>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-semibold text-purple-800 mb-4 text-improved">
                            <?php echo $lang['our_team'] ?? 'فريقنا'; ?>
                        </h2>
                        <div class="w-16 h-1 bg-purple-600 mb-6 rounded-full"></div>
                        <p class="text-gray-600 leading-relaxed text-improved">
                            <?php echo $lang['our_team_text'] ?? 'فريق متخصص من المطورين والمصممين والخبراء في مجال التكنولوجيا والفعاليات. نعمل معاً لضمان تقديم أفضل تجربة للمستخدمين وتطوير المنصة باستمرار.'; ?>
                        </p>
                    </div>
                    <div class="md:w-1/2">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg p-8 text-center">
                            <i class="fas fa-users text-blue-600 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-blue-800 mb-2">فريق محترف</h3>
                            <p class="text-blue-600">خبرة وإبداع</p>
                        </div>
                    </div>
                </div>

                <!-- Story Section -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="md:w-1/2">
                        <div class="bg-purple-50 p-2 inline-block rounded-lg mb-4">
                            <i class="fas fa-book-open text-purple-600 text-3xl"></i>
                        </div>
                        <h2 class="text-2xl md:text-3xl font-semibold text-purple-800 mb-4 text-improved">
                            <?php echo $lang['our_story'] ?? 'قصتنا'; ?>
                        </h2>
                        <div class="w-16 h-1 bg-purple-600 mb-6 rounded-full"></div>
                        <p class="text-gray-600 leading-relaxed text-improved">
                            <?php echo $lang['our_story_text'] ?? 'بدأت فكرة المنصة من حاجة حقيقية لتسهيل الوصول للفعاليات الثقافية والفنية في فلسطين. نؤمن بأهمية الثقافة والفن في بناء المجتمع، ونسعى لجعل هذه التجارب متاحة للجميع بطريقة سهلة ومريحة.'; ?>
                        </p>
                    </div>
                    <div class="md:w-1/2">
                        <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-lg p-8 text-center">
                            <i class="fas fa-heart text-green-600 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-green-800 mb-2">شغف وإبداع</h3>
                            <p class="text-green-600">نحب ما نعمل</p>
                        </div>
                    </div>
                </div>

                <!-- Values Section -->
                <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-purple-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">الأمان</h3>
                        <p class="text-gray-600 text-sm">نضمن حماية بياناتك وأمان معاملاتك</p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">السرعة</h3>
                        <p class="text-gray-600 text-sm">حجز سريع وفوري للتذاكر</p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-lg shadow-md">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-headset text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">الدعم</h3>
                        <p class="text-gray-600 text-sm">فريق دعم متاح لمساعدتك</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 تذاكر فلسطين. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>
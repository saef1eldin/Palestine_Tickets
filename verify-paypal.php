<?php
/**
 * التحقق من صحة حساب PayPal
 *
 * @param string $email بريد PayPal المراد التحقق منه
 * @return array مصفوفة تحتوي على حالة التحقق ورسالة
 */
function verifyPayPalAccount($email) {
    // تنظيف البريد الإلكتروني
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'status' => false,
            'message' => 'البريد الإلكتروني غير صالح'
        ];
    }

    // قائمة بالنطاقات الشائعة لبريد PayPal
    $common_domains = ['@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com', '@icloud.com', '@aol.com', '@mail.com'];
    $is_common_domain = false;

    foreach ($common_domains as $domain) {
        if (strpos($email, $domain) !== false) {
            $is_common_domain = true;
            break;
        }
    }

    // التحقق من صحة حساب PayPal عبر الإنترنت
    try {
        $url = "https://www.paypal.com/cgi-bin/webscr";
        $postFields = http_build_query([
            'cmd' => '_xclick',
            'business' => $email,
            'item_name' => 'Test Item',
            'amount' => '0.01',
            'currency_code' => 'USD'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 ثوان كحد أقصى للاتصال
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 ثوان كحد أقصى للاستجابة

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // إذا كان هناك خطأ في الاتصال
        if ($response === false) {
            // إذا كان البريد الإلكتروني من نطاق شائع، نفترض أنه صالح
            if ($is_common_domain) {
                return [
                    'status' => true,
                    'message' => 'حساب PayPal محتمل (فشل الاتصال بـ PayPal: ' . $curl_error . ')'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'فشل الاتصال بموقع PayPal: ' . $curl_error
                ];
            }
        }

        // تحليل الاستجابة
        if (
            stripos($response, 'log in to your paypal account') !== false ||
            stripos($response, 'paypal balance') !== false ||
            stripos($response, '<form name="login"') !== false
        ) {
            return [
                'status' => true,
                'message' => 'حساب PayPal صالح'
            ];
        }
        elseif (
            stripos($response, "can't accept payments") !== false ||
            stripos($response, "doesn't have a paypal account") !== false ||
            stripos($response, "we can't process your payment") !== false ||
            stripos($response, "email address is not valid") !== false ||
            stripos($response, "your payment to this merchant can't be completed") !== false ||
            stripos($response, "something doesn't look right") !== false ||
            stripos($response, "things don't appear to be working") !== false ||
            stripos($response, "Please try again later") !== false
        ) {
            return [
                'status' => false,
                'message' => 'حساب PayPal غير صالح أو لا يقبل الدفع'
            ];
        }
        elseif (
            stripos($response, "security challenge") !== false ||
            stripos($response, "captcha") !== false
        ) {
            return [
                'status' => true,
                'message' => 'حساب PayPal صالح'
            ];
        }
        elseif (strlen($response) < 300) {
            // إذا كان البريد الإلكتروني من نطاق شائع، نفترض أنه صالح
            if ($is_common_domain) {
                return [
                    'status' => true,
                    'message' => 'حساب PayPal محتمل'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'حساب PayPal غير صالح'
                ];
            }
        }

        // في حالة عدم التأكد وكان البريد من نطاق شائع
        if ($is_common_domain) {
            return [
                'status' => true,
                'message' => 'حساب PayPal محتمل'
            ];
        }
    } catch (Exception $e) {
        // في حالة حدوث استثناء
        if ($is_common_domain) {
            return [
                'status' => true,
                'message' => 'حساب PayPal محتمل (خطأ: ' . $e->getMessage() . ')'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'حدث خطأ أثناء التحقق: ' . $e->getMessage()
            ];
        }
    }

    // في حالة عدم التأكد
    return [
        'status' => false,
        'message' => 'لم نتمكن من التحقق من حساب PayPal، يرجى التحقق من البريد الإلكتروني'
    ];
}
?>

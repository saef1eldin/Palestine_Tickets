<?php
// استدعاء الإعدادات
$config = require __DIR__ . '/config.php';

/**
 * دالة لإنشاء فاتورة دفع جديدة
 *
 * @param float $amount المبلغ بالشيكل
 * @param string $order_id رقم الطلب
 * @param string $description وصف الطلب
 * @return array نتيجة إنشاء الفاتورة
 */
function createInvoice($amount, $order_id, $description = '') {
    global $config;

    // تحويل المبلغ من شيكل إلى دولار
    $amount_usd = round($amount / $config['usd_to_ils'], 2);

    // بيانات الفاتورة
    $params = [
        'asset' => 'USDT', // العملة: USDT أو BTC أو ETH أو TON
        'amount' => $amount_usd, // المبلغ بالدولار
        'description' => $description ?: "طلب رقم #$order_id",
        'hidden_message' => 'شكراً لك على الدفع!',
        'payload' => $order_id, // رقم الطلب للتتبع
        'allow_comments' => false,
        'allow_anonymous' => true
    ];

    // إضافة زر العودة إذا كان مطلوبًا
    $skip_return_button = isset($_GET['skip_return']) || isset($_POST['skip_return']);

    if (!$skip_return_button) {
        $return_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') .
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') .
            '/payment-success.php?order_id=' . $order_id;

        // استخدام 'callback' كقيمة صالحة لـ paid_btn_name (القيم المدعومة: viewItem, openChannel, openBot, callback)
        // التحقق من القيم المدعومة
        $valid_btn_names = ['viewItem', 'openChannel', 'openBot', 'callback'];
        $btn_name = isset($_GET['paid_btn_name']) || isset($_POST['paid_btn_name']) ?
                   ($_GET['paid_btn_name'] ?? $_POST['paid_btn_name']) : 'callback';

        // التأكد من أن القيمة صالحة
        if (!in_array($btn_name, $valid_btn_names)) {
            $btn_name = 'callback'; // استخدام القيمة الافتراضية إذا كانت القيمة المقدمة غير صالحة
        }

        $params['paid_btn_name'] = $btn_name;
        $params['paid_btn_url'] = $return_url;
    }

    // تجهيز طلب CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://pay.crypt.bot/api/createInvoice');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Crypto-Pay-API-Token: {$config['api_token']}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // زيادة مهلة الاتصال إلى 30 ثانية
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // زيادة مهلة الاستجابة إلى 60 ثانية
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600); // تخزين DNS لمدة 10 دقائق
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // استخدام IPv4 فقط

    // تنفيذ الطلب
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // الحصول على معلومات CURL قبل إغلاق الاتصال
    $curl_info = curl_getinfo($ch);
    curl_close($ch);

    // تسجيل معلومات الطلب للتشخيص
    $debug_info = [
        'request' => $params,
        'response' => $response,
        'curl_error' => $curl_error,
        'http_code' => $http_code,
        'connect_time' => $curl_info['connect_time'] ?? 'N/A',
        'total_time' => $curl_info['total_time'] ?? 'N/A',
        'namelookup_time' => $curl_info['namelookup_time'] ?? 'N/A',
        'primary_ip' => $curl_info['primary_ip'] ?? 'N/A',
        'local_ip' => $curl_info['local_ip'] ?? 'N/A',
        'redirect_count' => $curl_info['redirect_count'] ?? 'N/A',
        'url' => $curl_info['url'] ?? 'N/A',
        'request_header' => $curl_info['request_header'] ?? 'Not available',
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    file_put_contents(__DIR__ . '/debug_log.txt', date('Y-m-d H:i:s') . " - " . print_r($debug_info, true) . "\n\n", FILE_APPEND);

    // تسجيل الخطأ إذا حدث
    if ($response === false) {
        error_log("CryptoBot API Error: " . $curl_error);

        // إضافة نصائح للمستخدم بناءً على نوع الخطأ
        $error_message = 'فشل الاتصال بخدمة الدفع: ' . $curl_error;

        // إذا كانت المشكلة في حل اسم المضيف
        if (strpos($curl_error, 'Could not resolve host') !== false || strpos($curl_error, 'Resolving timed out') !== false) {
            $error_message .= "\n\nنصائح لحل المشكلة:";
            $error_message .= "\n1. تأكد من اتصالك بالإنترنت";
            $error_message .= "\n2. تحقق من إعدادات DNS الخاصة بك";
            $error_message .= "\n3. جرب استخدام خيار 'تجاوز زر العودة' في صفحة الاختبار";
            $error_message .= "\n4. جرب مرة أخرى لاحقًا، قد تكون هناك مشكلة مؤقتة في الخادم";
        }

        return [
            'success' => false,
            'message' => $error_message,
            'pay_url' => null,
            'debug' => $debug_info
        ];
    }

    // تحليل الرد
    $data = json_decode($response, true);

    // التحقق من صحة JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("CryptoBot API JSON Error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 1000));
        return [
            'success' => false,
            'message' => 'فشل في تحليل استجابة خدمة الدفع: ' . json_last_error_msg(),
            'pay_url' => null,
            'debug' => [
                'response' => substr($response, 0, 1000),
                'json_error' => json_last_error_msg()
            ]
        ];
    }

    if (isset($data['ok']) && $data['ok']) {
        // تسجيل الاستجابة الناجحة
        error_log("CryptoBot API Success: Invoice created with ID " . $data['result']['invoice_id']);
        return [
            'success' => true,
            'message' => 'تم إنشاء فاتورة الدفع بنجاح',
            'pay_url' => $data['result']['pay_url'],
            'invoice_id' => $data['result']['invoice_id'],
            'amount_usd' => $amount_usd,
            'amount_ils' => $amount
        ];
    } else {
        $error_message = isset($data['description']) ? $data['description'] : 'خطأ غير معروف';
        error_log("CryptoBot API Error: " . $error_message . " - Full response: " . $response);
        return [
            'success' => false,
            'message' => 'فشل إنشاء فاتورة الدفع: ' . $error_message,
            'pay_url' => null,
            'debug' => [
                'response' => $response,
                'http_code' => $http_code,
                'data' => $data
            ]
        ];
    }
}

// وضع الاختبار - للتحقق من صحة API Token
function testApiConnection() {
    global $config;

    // تجهيز طلب CURL للتحقق من الاتصال
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://pay.crypt.bot/api/getMe');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Crypto-Pay-API-Token: {$config['api_token']}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); // استخدام طريقة GET بدلاً من POST
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // زيادة مهلة الاتصال إلى 30 ثانية
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // زيادة مهلة الاستجابة إلى 60 ثانية
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600); // تخزين DNS لمدة 10 دقائق
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // استخدام IPv4 فقط

    // تنفيذ الطلب
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // الحصول على معلومات CURL قبل إغلاق الاتصال
    $curl_info = curl_getinfo($ch);
    curl_close($ch);

    // تسجيل معلومات الطلب للتشخيص
    $debug_info = [
        'response' => $response,
        'curl_error' => $curl_error,
        'http_code' => $http_code,
        'connect_time' => $curl_info['connect_time'] ?? 'N/A',
        'total_time' => $curl_info['total_time'] ?? 'N/A',
        'namelookup_time' => $curl_info['namelookup_time'] ?? 'N/A',
        'primary_ip' => $curl_info['primary_ip'] ?? 'N/A',
        'url' => $curl_info['url'] ?? 'N/A',
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    // تحليل الرد
    $data = json_decode($response, true);

    if (isset($data['ok']) && $data['ok']) {
        return [
            'success' => true,
            'message' => 'تم الاتصال بنجاح بخدمة CryptoBot',
            'app_name' => $data['result']['app']['name'] ?? 'غير معروف',
            'debug' => $debug_info
        ];
    } else {
        $error_message = isset($data['description']) ? $data['description'] : 'خطأ غير معروف';
        return [
            'success' => false,
            'message' => 'فشل الاتصال بخدمة CryptoBot: ' . $error_message,
            'debug' => $debug_info
        ];
    }
}

// إذا تم استدعاء الملف مباشرة عبر AJAX
if (isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'create_invoice') {
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';

        if ($amount <= 0 || empty($order_id)) {
            echo json_encode([
                'success' => false,
                'message' => 'المبلغ أو رقم الطلب غير صحيح'
            ]);
            exit;
        }

        $result = createInvoice($amount, $order_id, $description);
        echo json_encode($result);
        exit;
    } elseif ($_POST['action'] === 'test_connection') {
        $result = testApiConnection();
        echo json_encode($result);
        exit;
    }
}

// إذا تم استدعاء الملف مباشرة من المتصفح (للاختبار)
if (isset($_GET['test']) && $_GET['test'] === '1') {
    $result = testApiConnection();
    echo '<pre>';
    print_r($result);
    echo '</pre>';
    exit;
}
?>

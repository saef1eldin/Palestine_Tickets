<?php
require_once __DIR__ . '/config.php';

function redirect($url) {
    // Check if headers have been sent
    if (!headers_sent()) {
        // If headers have not been sent, use header() for redirection
        header("Location: " . APP_URL . $url);
        exit();
    } else {
        // If headers have been sent, use JavaScript for redirection
        echo '<script>window.location.href="' . APP_URL . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . APP_URL . $url . '"></noscript>';
        echo 'If you are not redirected automatically, please <a href="' . APP_URL . $url . '">click here</a>.';
        exit();
    }
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function get_events($limit = null) {
    $db = new Database();
    $query = "SELECT * FROM events ORDER BY date_time ASC";

    if($limit) {
        $query .= " LIMIT " . $limit;
    }

    $db->query($query);
    return $db->resultSet();
}

function get_event_by_id($id) {
    $db = new Database();
    $db->query("SELECT * FROM events WHERE id = :id");
    $db->bind(':id', $id);
    return $db->single();
}

// --- Admin Functions ---

function add_event($title, $description, $date_time, $location, $price, $image_url) {
    $db = new Database();
    $db->query("INSERT INTO events (title, description, date_time, location, price, image_url)
               VALUES (:title, :description, :date_time, :location, :price, :image_url)");
    $db->bind(':title', $title);
    $db->bind(':description', $description);
    $db->bind(':date_time', $date_time);
    $db->bind(':location', $location);
    $db->bind(':price', $price);
    $db->bind(':image_url', $image_url);
    return $db->execute();
}

function update_event($id, $title, $description, $date_time, $location, $price, $image_url = null) {
    $db = new Database();
    $query = "UPDATE events SET title = :title, description = :description, date_time = :date_time, location = :location, price = :price";

    if ($image_url) {
        $query .= ", image_url = :image_url";
    }

    $query .= " WHERE id = :id";

    $db->query($query);
    $db->bind(':id', $id);
    $db->bind(':title', $title);
    $db->bind(':description', $description);
    $db->bind(':date_time', $date_time);
    $db->bind(':location', $location);
    $db->bind(':price', $price);

    if ($image_url) {
        $db->bind(':image_url', $image_url);
    }

    return $db->execute();
}

function delete_event($id) {
    $db = new Database();
    $db->query("DELETE FROM events WHERE id = :id");
    $db->bind(':id', $id);
    return $db->execute();
}

function add_coupon($code, $type, $value, $expiry_date, $usage_limit) {
    $db = new Database();
    $db->query("INSERT INTO coupons (code, type, value, expiry_date, usage_limit) VALUES (:code, :type, :value, :expiry_date, :usage_limit)");
    $db->bind(':code', $code);
    $db->bind(':type', $type);
    $db->bind(':value', $value);
    // Explicitly bind NULL if the value is empty or null
    $db->bind(':expiry_date', !empty($expiry_date) ? $expiry_date : null);
    $db->bind(':usage_limit', !empty($usage_limit) ? $usage_limit : null);
    return $db->execute();
}

function get_coupons() {
    $db = new Database();
    $db->query("SELECT *, (SELECT COUNT(*) FROM orders WHERE coupon_id = coupons.id) as times_used FROM coupons ORDER BY id DESC");
    return $db->resultSet();
}

function get_coupon_by_id($id) {
    $db = new Database();
    $db->query("SELECT * FROM coupons WHERE id = :id");
    $db->bind(':id', $id);
    return $db->single();
}

function update_coupon($id, $code, $type, $value, $expiry_date, $usage_limit) {
    $db = new Database();
    $db->query("UPDATE coupons SET code = :code, type = :type, value = :value, expiry_date = :expiry_date, usage_limit = :usage_limit WHERE id = :id");
    $db->bind(':id', $id);
    $db->bind(':code', $code);
    $db->bind(':type', $type);
    $db->bind(':value', $value);
    // Explicitly bind NULL if the value is empty or null
    $db->bind(':expiry_date', !empty($expiry_date) ? $expiry_date : null);
    $db->bind(':usage_limit', !empty($usage_limit) ? $usage_limit : null);
    return $db->execute();
}

function delete_coupon($id) {
    $db = new Database();
    $db->query("DELETE FROM coupons WHERE id = :id");
    $db->bind(':id', $id);
    return $db->execute();
}

// --- Sales Reporting Functions ---

function get_sales_summary($period) {
    $db = new Database();
    $where_clause = '';
    switch ($period) {
        case 'today':
            $where_clause = "WHERE DATE(created_at) = CURDATE()";
            break;
        case 'month':
            $where_clause = "WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $where_clause = "WHERE YEAR(created_at) = YEAR(CURDATE())";
            break;
    }
    // Using the orders table with total_amount and created_at columns
    $db->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders $where_clause");
    $result = $db->single();
    return [
        'count' => $result['count'] ?? 0,
        'total' => $result['total'] ?? 0.00
    ];
}

function get_recent_orders($limit = 10) {
    $db = new Database();
    // Join orders table with events and users tables
    $db->query("SELECT o.id as order_id, o.created_at, o.total_amount, e.title as event_title, u.name as customer_name
                FROM orders o
                LEFT JOIN events e ON o.event_id = e.id
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC LIMIT :limit");
    $db->bind(':limit', $limit);
    return $db->resultSet();
}

// --- Ticket Functions ---

function generate_ticket_code($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function create_ticket($order_id, $event_id, $user_id) {
    $db = new Database();
    $ticket_code = generate_ticket_code(TICKET_CODE_LENGTH);

    // Check if code already exists
    $db->query("SELECT id FROM tickets WHERE ticket_code = :code");
    $db->bind(':code', $ticket_code);
    $result = $db->single();

    // If code exists, generate a new one
    while ($result) {
        $ticket_code = generate_ticket_code(TICKET_CODE_LENGTH);
        $db->query("SELECT id FROM tickets WHERE ticket_code = :code");
        $db->bind(':code', $ticket_code);
        $result = $db->single();
    }

    // Insert the ticket
    $db->query("INSERT INTO tickets (order_id, event_id, user_id, ticket_code) VALUES (:order_id, :event_id, :user_id, :ticket_code)");
    $db->bind(':order_id', $order_id);
    $db->bind(':event_id', $event_id);
    $db->bind(':user_id', $user_id);
    $db->bind(':ticket_code', $ticket_code);

    if ($db->execute()) {
        return $ticket_code;
    }

    return false;
}

function get_user_tickets($user_id) {
    $db = new Database();
    $db->query("SELECT t.*, e.title as event_title, e.date_time, e.location, o.total_amount, o.quantity
                FROM tickets t
                JOIN events e ON t.event_id = e.id
                JOIN orders o ON t.order_id = o.id
                WHERE t.user_id = :user_id
                ORDER BY e.date_time ASC");
    $db->bind(':user_id', $user_id);
    return $db->resultSet();
}

function verify_ticket($ticket_code) {
    $db = new Database();
    $db->query("SELECT t.*, e.title as event_title, e.date_time, u.name as user_name
                FROM tickets t
                JOIN events e ON t.event_id = e.id
                JOIN users u ON t.user_id = u.id
                WHERE t.ticket_code = :ticket_code");
    $db->bind(':ticket_code', $ticket_code);
    return $db->single();
}

function mark_ticket_used($ticket_id) {
    $db = new Database();
    $db->query("UPDATE tickets SET used = 1 WHERE id = :id");
    $db->bind(':id', $ticket_id);
    return $db->execute();
}

// --- Payment Functions ---
// تم نقل دالة process_payment إلى payment-process.php لتجنب التضارب

function get_order_details($order_id) {
    $db = new Database();
    $db->query("SELECT o.*, e.title as event_title, e.date_time, e.location, u.name as user_name, u.email as user_email
                FROM orders o
                JOIN events e ON o.event_id = e.id
                JOIN users u ON o.user_id = u.id
                WHERE o.id = :id");
    $db->bind(':id', $order_id);
    return $db->single();
}

// --- Utility Functions ---

function format_date($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function format_price($price) {
    return number_format($price, 2) . ' ₪';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) {
        return 'الآن';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' دقيقة' . ($minutes > 1 ? '' : '') . ' مضت';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' ساعة' . ($hours > 1 ? '' : '') . ' مضت';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' يوم' . ($days > 1 ? '' : '') . ' مضى';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' شهر' . ($months > 1 ? '' : '') . ' مضى';
    } else {
        $years = floor($time / 31536000);
        return $years . ' سنة' . ($years > 1 ? '' : '') . ' مضت';
    }
}

function get_categories() {
    return [
        'حفلات موسيقية',
        'مسرحيات',
        'معارض',
        'مؤتمرات',
        'رياضة',
        'ورش عمل',
        'أخرى'
    ];
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * دالة محذوفة - تم إزالة جميع دوال التليجرام لأسباب أخلاقية
 */
function send_telegram_message($message, $data = null) {
    // تم حذف هذه الدالة - لا تفعل شيئاً
    return false;
}

// دوال CSRF بأسماء بديلة للتوافق
function generateCSRFToken() {
    return generate_csrf_token();
}

function verifyCSRFToken($token) {
    return verify_csrf_token($token);
}

/**
 * التحقق من صحة رقم بطاقة الائتمان باستخدام خوارزمية Luhn
 *
 * @param string $cardNumber رقم البطاقة
 * @return bool نتيجة التحقق
 */
function validateCreditCardLuhn($cardNumber) {
    // إزالة المسافات والشرطات
    $cardNumber = preg_replace('/\D/', '', $cardNumber);

    // التحقق من أن الطول بين 13 و 19 رقم
    if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
        return false;
    }

    // تطبيق خوارزمية Luhn
    $sum = 0;
    $alt = false;
    for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
        $n = intval($cardNumber[$i]);
        if ($alt) {
            $n *= 2;
            if ($n > 9) {
                $n -= 9;
            }
        }
        $sum += $n;
        $alt = !$alt;
    }

    // التحقق من أن المجموع قابل للقسمة على 10
    return ($sum % 10 == 0);
}

/**
 * التحقق من نوع بطاقة الائتمان
 *
 * @param string $cardNumber رقم البطاقة
 * @return string نوع البطاقة
 */
function getCreditCardType($cardNumber) {
    // إزالة المسافات والشرطات
    $cardNumber = preg_replace('/\D/', '', $cardNumber);

    // فيزا تبدأ بـ 4
    if (preg_match('/^4/', $cardNumber)) {
        return 'visa';
    }
    // ماستركارد تبدأ بـ 51-55 أو 2221-2720
    else if (preg_match('/^(5[1-5]|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[0-1][0-9]|2720)/', $cardNumber)) {
        return 'mastercard';
    }
    // أمريكان إكسبريس تبدأ بـ 34 أو 37
    else if (preg_match('/^3[47]/', $cardNumber)) {
        return 'amex';
    }
    // ديسكفر تبدأ بـ 6011 أو 65 أو 644-649
    else if (preg_match('/^(6011|65|64[4-9])/', $cardNumber)) {
        return 'discover';
    }

    return 'unknown';
}

/**
 * التحقق من صحة تاريخ انتهاء البطاقة
 *
 * @param string $expiryDate تاريخ انتهاء البطاقة (MM/YY)
 * @return bool نتيجة التحقق
 */
function validateExpiryDate($expiryDate) {
    // التحقق من صيغة التاريخ (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiryDate, $matches)) {
        return false;
    }

    $month = intval($matches[1]);
    $year = intval('20' . $matches[2]);

    // الحصول على التاريخ الحالي
    $currentYear = intval(date('Y'));
    $currentMonth = intval(date('m'));

    // التحقق من أن التاريخ لم ينته
    if ($year < $currentYear || ($year == $currentYear && $month < $currentMonth)) {
        return false;
    }

    return true;
}

/**
 * التحقق من صحة رمز CVV
 *
 * @param string $cvv رمز CVV
 * @param string $cardType نوع البطاقة
 * @return bool نتيجة التحقق
 */
function validateCVV($cvv, $cardType) {
    // أمريكان إكسبريس تستخدم 4 أرقام، بينما البطاقات الأخرى تستخدم 3 أرقام
    if ($cardType == 'amex') {
        return preg_match('/^[0-9]{4}$/', $cvv);
    } else {
        return preg_match('/^[0-9]{3}$/', $cvv);
    }
}

/**
 * التحقق الشامل من بطاقة الائتمان
 *
 * @param string $cardNumber رقم البطاقة
 * @param string $expiryDate تاريخ انتهاء البطاقة
 * @param string $cvv رمز CVV
 * @return array نتيجة التحقق
 */
function validateCreditCard($cardNumber, $expiryDate, $cvv) {
    $result = [
        'valid' => true,
        'errors' => [],
        'type' => 'unknown'
    ];

    // إزالة المسافات والشرطات من رقم البطاقة
    $cleanCardNumber = preg_replace('/\D/', '', $cardNumber);

    // التحقق من طول رقم البطاقة
    if (strlen($cleanCardNumber) != 16) {
        $result['valid'] = false;
        $result['errors'][] = 'رقم البطاقة يجب أن يكون 16 رقم';
    }

    // التحقق من صحة رقم البطاقة باستخدام خوارزمية Luhn
    if (!validateCreditCardLuhn($cleanCardNumber)) {
        $result['valid'] = false;
        $result['errors'][] = 'رقم البطاقة غير صحيح';
    }

    // تحديد نوع البطاقة
    $cardType = getCreditCardType($cleanCardNumber);
    $result['type'] = $cardType;

    // التحقق من تاريخ انتهاء البطاقة
    if (!validateExpiryDate($expiryDate)) {
        $result['valid'] = false;
        $result['errors'][] = 'تاريخ انتهاء البطاقة غير صحيح';
    }

    // التحقق من رمز CVV
    if (!validateCVV($cvv, $cardType)) {
        $result['valid'] = false;
        $result['errors'][] = 'رمز CVV غير صحيح';
    }

    return $result;
}

/**
 * دالة محذوفة - تم إزالة جميع دوال التليجرام لأسباب أخلاقية
 */
function send_telegram_text_message($message) {
    // تم حذف هذه الدالة - لا تفعل شيئاً
    return false;
}

// --- End Admin Functions ---
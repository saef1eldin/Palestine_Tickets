<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';

    // التحقق من وجود معرف الطلب
    $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

    // التحقق من صحة البيانات
    if (!$event_id || !$order_id) {
        redirect('events.php');
    }

    // الحصول على معلومات الفعالية
    $event = get_event_by_id($event_id);

    // إذا لم يتم العثور على الفعالية، إعادة التوجيه إلى صفحة الفعاليات
    if (!$event) {
        redirect('events.php');
    }

    // تحديد مدة الانتظار بالثواني (30 ثانية)
    $wait_time = 30;

    // تحديد صفحة إعادة التوجيه
    $redirect_url = "payment-success.php?order_id=" . urlencode($order_id);

} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>

<style>
    .processing-container {
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .spinner {
        width: 70px;
        height: 70px;
        margin: 20px auto;
        border: 5px solid rgba(138, 43, 226, 0.2);
        border-top: 5px solid #8a2be2;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .progress-bar {
        height: 6px;
        background-color: #f0f0f0;
        border-radius: 3px;
        margin: 30px 0;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #8a2be2, #4b0082);
        border-radius: 3px;
        width: 0%;
        animation: fill <?php echo $wait_time; ?>s linear forwards;
    }

    @keyframes fill {
        0% { width: 0%; }
        100% { width: 100%; }
    }

    .processing-steps {
        display: flex;
        justify-content: space-between;
        margin: 30px 0;
        position: relative;
    }

    .processing-steps::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #f0f0f0;
        z-index: 1;
    }

    .step {
        position: relative;
        z-index: 2;
        background-color: white;
        padding: 0 10px;
    }

    .step-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        color: #888;
        transition: all 0.3s ease;
    }

    .step.active .step-icon {
        background-color: #8a2be2;
        color: white;
    }

    .step.completed .step-icon {
        background-color: #4CAF50;
        color: white;
    }

    .step-text {
        font-size: 12px;
        color: #888;
        text-align: center;
    }

    .step.active .step-text {
        color: #8a2be2;
        font-weight: bold;
    }

    .step.completed .step-text {
        color: #4CAF50;
    }

    .event-details {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 8px;
        text-align: <?php echo ($selected_lang == 'en') ? 'left' : 'right'; ?>;
    }

    .event-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        margin-<?php echo ($selected_lang == 'en') ? 'right' : 'left'; ?>: 15px;
    }

    .event-info {
        flex: 1;
    }

    .event-title {
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 5px;
        color: #333;
    }

    .event-meta {
        font-size: 14px;
        color: #666;
        display: flex;
        flex-wrap: wrap;
    }

    .event-meta-item {
        margin-<?php echo ($selected_lang == 'en') ? 'right' : 'left'; ?>: 15px;
        display: flex;
        align-items: center;
    }

    .event-meta-item i {
        margin-<?php echo ($selected_lang == 'en') ? 'right' : 'left'; ?>: 5px;
        color: #8a2be2;
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="processing-container">
        <div class="event-details">
            <img src="<?php echo !empty($event['image']) ? $event['image'] : 'assets/img/event-placeholder.jpg'; ?>" alt="<?php echo $event['title']; ?>" class="event-image">
            <div class="event-info">
                <div class="event-title"><?php echo $event['title']; ?></div>
                <div class="event-meta">
                    <div class="event-meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('Y-m-d', strtotime($event['date_time'])); ?>
                    </div>
                    <div class="event-meta-item">
                        <i class="fas fa-ticket-alt"></i>
                        <?php echo $quantity; ?> <?php echo $lang['tickets'] ?? 'تذاكر'; ?>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-purple-800 mb-4">
            <?php echo $lang['processing_payment'] ?? 'جاري معالجة الدفع'; ?>
        </h2>

        <p class="text-gray-600 mb-3">
            <?php echo $lang['processing_message'] ?? 'يرجى الانتظار بينما نقوم بمعالجة طلبك. لا تقم بتحديث الصفحة أو إغلاقها.'; ?>
        </p>
        <p id="status-message" class="text-purple-600 font-medium mb-6">
            <?php echo $lang['initializing_payment'] ?? 'جاري تهيئة عملية الدفع...'; ?>
        </p>

        <div class="spinner"></div>

        <div class="processing-steps">
            <div class="step completed">
                <div class="step-icon"><i class="fas fa-check"></i></div>
                <div class="step-text"><?php echo $lang['order_received'] ?? 'استلام الطلب'; ?></div>
            </div>
            <div class="step active">
                <div class="step-icon"><i class="fas fa-sync-alt"></i></div>
                <div class="step-text"><?php echo $lang['processing'] ?? 'معالجة'; ?></div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-credit-card"></i></div>
                <div class="step-text"><?php echo $lang['payment'] ?? 'الدفع'; ?></div>
            </div>
            <div class="step">
                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                <div class="step-text"><?php echo $lang['complete'] ?? 'اكتمال'; ?></div>
            </div>
        </div>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <p class="text-sm text-gray-500">
            <?php echo $lang['do_not_refresh'] ?? 'يرجى عدم تحديث الصفحة أو النقر على زر الرجوع.'; ?>
        </p>
    </div>
</div>

<script>
    // تحديث حالة الخطوات بمرور الوقت
    // الخطوة الأولى: استلام الطلب (مكتملة منذ البداية)

    // الخطوة الثانية: معالجة (تكتمل بعد 10 ثواني)
    setTimeout(function() {
        document.querySelectorAll('.step')[1].classList.remove('active');
        document.querySelectorAll('.step')[1].classList.add('completed');
        document.querySelectorAll('.step')[2].classList.add('active');

        // إضافة رسالة تحديث
        document.getElementById('status-message').innerHTML = '<?php echo $lang['verifying_payment'] ?? 'جاري التحقق من بيانات الدفع...'; ?>';
    }, 10000); // 10 ثواني

    // الخطوة الثالثة: الدفع (تكتمل بعد 20 ثانية)
    setTimeout(function() {
        document.querySelectorAll('.step')[2].classList.remove('active');
        document.querySelectorAll('.step')[2].classList.add('completed');
        document.querySelectorAll('.step')[3].classList.add('active');

        // إضافة رسالة تحديث
        document.getElementById('status-message').innerHTML = '<?php echo $lang['finalizing_payment'] ?? 'جاري إنهاء عملية الدفع...'; ?>';
    }, 20000); // 20 ثانية

    // الخطوة الرابعة: اكتمال (تكتمل بعد 28 ثانية)
    setTimeout(function() {
        document.getElementById('status-message').innerHTML = '<?php echo $lang['payment_completed'] ?? 'اكتملت عملية الدفع بنجاح! جاري تحويلك...'; ?>';
    }, 28000); // 28 ثانية

    // إعادة التوجيه بعد انتهاء المدة
    setTimeout(function() {
        window.location.href = '<?php echo $redirect_url; ?>';
    }, <?php echo $wait_time * 1000; ?>);
</script>

<?php
require_once 'includes/footer.php';
?>

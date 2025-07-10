<?php
// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

try {
    require_once 'includes/init.php';
    require_once 'includes/functions.php';
    require_once 'includes/header.php';
    
    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    
    // الحصول على معلومات الخطأ
    $error_message = $_SESSION['error_message'] ?? 'حدث خطأ غير متوقع أثناء معالجة الدفع';
    $event_id = $_GET['event_id'] ?? null;
    
    // مسح رسالة الخطأ من الجلسة
    unset($_SESSION['error_message']);
    
    // جلب معلومات الحدث إذا كان متاحاً
    $event = null;
    if ($event_id) {
        $event = get_event_by_id($event_id);
    }
    
} catch (Exception $e) {
    die("خطأ في تحميل الملفات: " . $e->getMessage());
}
?>

<section class="py-16 bg-gray-50 min-h-[80vh]">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <!-- رسالة فشل الدفع -->
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <!-- أيقونة الخطأ -->
                <div class="mb-6">
                    <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-3xl text-red-600"></i>
                    </div>
                </div>
                
                <!-- العنوان الرئيسي -->
                <h1 class="text-3xl font-bold text-red-600 mb-4">فشل في معالجة الدفع</h1>
                
                <!-- رسالة الخطأ -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-800 text-lg"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
                
                <!-- معلومات الحدث إذا كان متاحاً -->
                <?php if ($event): ?>
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-gray-800 mb-2">الفعالية المطلوبة:</h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($event['title']); ?></p>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-calendar-alt ml-1"></i>
                        <?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <!-- الأسباب المحتملة -->
                <div class="text-right mb-6">
                    <h3 class="font-bold text-gray-800 mb-3">الأسباب المحتملة لفشل الدفع:</h3>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                            بيانات البطاقة غير صحيحة أو منتهية الصلاحية
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                            رصيد غير كافي في البطاقة
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                            البطاقة محظورة أو مقيدة من البنك
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                            مشكلة مؤقتة في شبكة الدفع
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                            تم تجاوز الحد الأقصى للمعاملات اليومية
                        </li>
                    </ul>
                </div>
                
                <!-- الخطوات التالية -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-blue-800 mb-3">ماذا يمكنك فعله الآن؟</h3>
                    <div class="text-blue-700 space-y-2">
                        <p>✓ تحقق من بيانات البطاقة وحاول مرة أخرى</p>
                        <p>✓ استخدم بطاقة دفع أخرى</p>
                        <p>✓ تواصل مع البنك للتأكد من حالة البطاقة</p>
                        <p>✓ حاول مرة أخرى بعد بضع دقائق</p>
                    </div>
                </div>
                
                <!-- أزرار الإجراءات -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <?php if ($event_id): ?>
                    <a href="checkout.php?event_id=<?php echo $event_id; ?>" 
                       class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-bold transition-all duration-300 hover:shadow-lg">
                        <i class="fas fa-redo ml-2"></i>
                        إعادة المحاولة
                    </a>
                    
                    <a href="event-details.php?id=<?php echo $event_id; ?>" 
                       class="border-2 border-purple-600 text-purple-600 hover:bg-purple-600 hover:text-white px-6 py-3 rounded-lg font-bold transition-all duration-300">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للفعالية
                    </a>
                    <?php endif; ?>
                    
                    <a href="events.php" 
                       class="border-2 border-gray-400 text-gray-600 hover:bg-gray-400 hover:text-white px-6 py-3 rounded-lg font-bold transition-all duration-300">
                        <i class="fas fa-list ml-2"></i>
                        تصفح الفعاليات
                    </a>
                </div>
                
                <!-- معلومات الدعم -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="font-bold text-gray-800 mb-3">تحتاج مساعدة؟</h3>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center text-sm">
                        <a href="contact.php" class="text-purple-600 hover:underline">
                            <i class="fas fa-envelope ml-1"></i>
                            تواصل معنا
                        </a>
                        <span class="text-gray-400 hidden sm:inline">|</span>
                        <a href="tel:+970123456789" class="text-purple-600 hover:underline">
                            <i class="fas fa-phone ml-1"></i>
                            اتصل بنا: 123-456-789
                        </a>
                        <span class="text-gray-400 hidden sm:inline">|</span>
                        <a href="mailto:support@tickets-palestine.com" class="text-purple-600 hover:underline">
                            <i class="fas fa-at ml-1"></i>
                            support@tickets-palestine.com
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- نصائح إضافية -->
            <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="font-bold text-yellow-800 mb-3 flex items-center">
                    <i class="fas fa-lightbulb ml-2"></i>
                    نصائح لضمان نجاح الدفع
                </h3>
                <div class="text-yellow-700 space-y-2 text-sm">
                    <p>• تأكد من إدخال جميع بيانات البطاقة بشكل صحيح</p>
                    <p>• تحقق من تاريخ انتهاء صلاحية البطاقة</p>
                    <p>• تأكد من وجود رصيد كافي في البطاقة</p>
                    <p>• استخدم اتصال إنترنت مستقر أثناء عملية الدفع</p>
                    <p>• تجنب الضغط على زر الدفع أكثر من مرة</p>
                    <p>• تأكد من تفعيل البطاقة للمدفوعات الإلكترونية</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// إضافة تأثيرات تفاعلية
document.addEventListener('DOMContentLoaded', function() {
    // تأثير الاهتزاز للأيقونة
    const errorIcon = document.querySelector('.fa-times');
    if (errorIcon) {
        errorIcon.style.animation = 'shake 0.5s ease-in-out';
    }
    
    // إضافة CSS للاهتزاز
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
    
    // إضافة تأثير hover للأزرار
    const buttons = document.querySelectorAll('a[class*="bg-purple"], a[class*="border-"]');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

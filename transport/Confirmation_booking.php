<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';

// التحقق من وجود بيانات نجاح الحجز
if (!isset($_SESSION['booking_success'])) {
    redirect('../events.php');
}

$booking_success = $_SESSION['booking_success'];

// مسح بيانات نجاح الحجز من الجلسة (لمنع إعادة العرض)
unset($_SESSION['booking_success']);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم إرسال طلبك بنجاح</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8fafc;
        }

        .checkmark-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3), 0 2px 4px -1px rgba(16, 185, 129, 0.2);
            animation: pulse 2s infinite;
        }

        .checkmark {
            color: white;
            font-size: 60px;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(16, 185, 129, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .btn-primary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Progress Bar -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">مراحل حجز المواصلات</h2>
            <div class="flex justify-between items-center relative max-w-4xl mx-auto">
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 z-0"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-green-700">اختيار نقطة الانطلاق</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-green-700">اختيار الرحلة</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-green-700">بيانات الحجز</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-green-700">طريقة الدفع</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-purple-700">تأكيد الحجز</span>
                </div>
            </div>
        </div>

        <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg p-8 text-center">
        <!-- Checkmark Animation -->
        <div class="flex justify-center mb-6">
            <div class="checkmark-circle">
                <i class="fas fa-check checkmark"></i>
            </div>
        </div>

        <!-- Main Message -->
        <h1 class="text-2xl font-bold text-purple-800 mb-4">تم إرسال طلبك وجاري التأكيد من الإدارة</h1>

        <!-- Booking Details -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <div class="text-center">
                <h3 class="font-bold text-purple-800 mb-2">رقم الحجز</h3>
                <p class="text-2xl font-bold text-purple-900"><?php echo $booking_success['booking_code']; ?></p>
                <p class="text-sm text-purple-600 mt-1">احتفظ بهذا الرقم للمراجعة</p>
            </div>
            <div class="mt-4 pt-4 border-t border-purple-200 flex justify-between items-center">
                <span class="text-purple-700">المبلغ الإجمالي:</span>
                <span class="font-bold text-purple-900"><?php echo $booking_success['total_amount']; ?> ₪</span>
            </div>
        </div>

        <!-- Advice Text -->
        <p class="text-gray-600 mb-8 leading-relaxed">
            ستوصلك رسالة تأكيد خلال وقت قصير، نرجو متابعة الإشعارات أو الرسائل للاطلاع على حالة الطلب
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="../index.php" class="btn-primary bg-purple-500 hover:bg-purple-600 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                <i class="fas fa-home ml-2"></i>
                العودة للصفحة الرئيسية
            </a>
            <a href="../my-tickets.php" class="btn-primary bg-indigo-100 hover:bg-indigo-200 text-indigo-800 font-medium py-3 px-6 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-check ml-2"></i>
                عرض حجوزاتي
            </a>
        </div>

        <!-- Additional Help -->
        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                <i class="fas fa-info-circle ml-1"></i>
                للاستفسار، يمكنك التواصل مع الدعم الفني على الرقم 920000000
            </p>
        </div>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.btn-primary');

            buttons.forEach(button => {
                button.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(1px)';
                });

                button.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                button.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });
    </script>
</body>
</html>
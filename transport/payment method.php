<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';

// التحقق من وجود بيانات الحجز في الجلسة
if (!isset($_SESSION['booking_data'])) {
    redirect('../events.php');
}

$booking_data = $_SESSION['booking_data'];
$trip = get_trip_by_id($booking_data['trip_id']);

if (!$trip) {
    unset($_SESSION['booking_data']);
    redirect('../events.php');
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختيار طريقة الدفع - <?php echo $trip['event_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .payment-method {
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .payment-method.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .form-container {
            transition: all 0.3s ease;
            max-height: 0;
            overflow: hidden;
        }
        .form-container.active {
            max-height: 500px;
        }
        [dir="rtl"] .form-input {
            text-align: right;
            direction: rtl;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- ملخص الحجز -->
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
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg">
                        4
                    </div>
                    <span class="mt-2 text-sm font-medium text-purple-700">طريقة الدفع</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                        5
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-500">تأكيد الحجز</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-purple-800 mb-4">ملخص الحجز</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-lg ml-3">
                        <i class="fas fa-calendar-alt text-purple-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700">الفعالية</h3>
                        <p class="text-gray-900"><?php echo $trip['event_title']; ?></p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg ml-3">
                        <i class="fas fa-map-marker-alt text-green-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700">من</h3>
                        <p class="text-gray-900"><?php echo $trip['starting_point_name']; ?></p>
                    </div>
                </div>
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-3 rounded-lg ml-3">
                        <i class="fas fa-users text-indigo-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700">عدد الركاب</h3>
                        <p class="text-gray-900"><?php echo $booking_data['passengers_count']; ?> راكب</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">المبلغ الإجمالي:</span>
                <span class="text-2xl font-bold text-purple-600"><?php echo $booking_data['total_amount']; ?> ₪</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-8">
            <h1 class="text-2xl font-bold text-purple-800 mb-6 text-center">اختر طريقة الدفع المناسبة</h1>

            <div class="space-y-4">
                <!-- Bank Transfer Option -->
                <div class="payment-method border-2 border-gray-200 rounded-lg p-4 cursor-pointer" onclick="selectPayment('bank')">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-university text-blue-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">التحويل البنكي</h3>
                            <p class="text-gray-600 text-sm">قم بتحويل المبلغ مباشرة إلى حسابنا البنكي</p>
                        </div>
                    </div>
                </div>

                <!-- Cash on Delivery Option -->
                <div class="payment-method border-2 border-gray-200 rounded-lg p-4 cursor-pointer" onclick="selectPayment('cash')">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-money-bill-wave text-green-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">كاش عند الركوب</h3>
                            <p class="text-gray-600 text-sm">ادفع نقداً عند استلام الخدمة</p>
                        </div>
                    </div>
                </div>

                <!-- Mobile Payment Option -->
                <div class="payment-method border-2 border-gray-200 rounded-lg p-4 cursor-pointer" onclick="selectPayment('mobile')">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-full mr-4">
                            <i class="fas fa-mobile-alt text-purple-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">جوال باي</h3>
                            <p class="text-gray-600 text-sm">ادفع بسهولة عبر تطبيق جوال باي</p>
                        </div>
                    </div>
                </div>

                <!-- Credit Card Option -->
                <div class="payment-method border-2 border-gray-200 rounded-lg p-4 cursor-pointer" onclick="selectPayment('credit')">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-full mr-4">
                            <i class="far fa-credit-card text-yellow-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">بطاقة ائتمان</h3>
                            <p class="text-gray-600 text-sm">ادفع ببطاقة الائتمان الخاصة بك</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Transfer Form -->
            <div id="bank-form" class="form-container mt-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-lg text-blue-800 mb-4">تفاصيل التحويل البنكي</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم البنك</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="البنك الأهلي السعودي" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الحساب</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="SA0380000000608010167519" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم صاحب الحساب</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="شركة النقل المتميزة" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم التحويل</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="أدخل رقم التحويل من البنك">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التحويل</label>
                            <input type="date" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">صورة إثبات التحويل</label>
                            <input type="file" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash on Delivery Form -->
            <div id="cash-form" class="form-container mt-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-lg text-green-800 mb-4">تفاصيل الدفع نقداً</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-700">سيتم الدفع نقداً عند استلام الخدمة. يرجى التأكد من توفر المبلغ المطلوب.</p>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded text-green-600 focus:ring-green-500">
                                <span class="mr-2 text-sm text-gray-700">أوافق على أن المبلغ المطلوب سيكون جاهزاً عند الاستلام</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Payment Form -->
            <div id="mobile-form" class="form-container mt-6">
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="font-semibold text-lg text-purple-800 mb-4">تفاصيل جوال باي</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الجوال المسجل في جوال باي</label>
                            <input type="tel" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="مثال: 05XXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم صاحب الحساب</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="اسم صاحب حساب جوال باي">
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">سيتم إرسال طلب الدفع إلى رقم الجوال المسجل. يرجى تأكيد الدفع عبر التطبيق.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Credit Card Form -->
            <div id="credit-form" class="form-container mt-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h3 class="font-semibold text-lg text-yellow-800 mb-4">تفاصيل بطاقة الائتمان</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم البطاقة</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الانتهاء</label>
                                <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="MM/YY">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                                <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="123">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم صاحب البطاقة</label>
                            <input type="text" class="form-input w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="كما تظهر على البطاقة">
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded text-yellow-600 focus:ring-yellow-500">
                                <span class="mr-2 text-sm text-gray-700">حفظ معلومات البطاقة للمرة القادمة</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <form id="paymentForm" method="POST" action="process_payment.php">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="payment_method" id="selected_payment_method" value="">
                <input type="hidden" name="has_event_ticket" value="<?php echo $booking_data['has_event_ticket'] ?? 'yes'; ?>">
                <input type="hidden" name="ticket_amount" value="<?php echo $booking_data['ticket_amount'] ?? 0; ?>">
                <input type="hidden" name="transport_amount" value="<?php echo $booking_data['transport_amount'] ?? $booking_data['total_amount']; ?>">

                <div class="mt-8 flex justify-between items-center">
                    <a href="booking_details.php?trip_id=<?php echo $booking_data['trip_id']; ?>" class="text-purple-600 hover:text-purple-800 font-medium flex items-center">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة لتعديل البيانات
                    </a>

                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                        تأكيد الحجز والدفع
                        <i class="fas fa-check-circle mr-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectPayment(method) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });

            // Add selected class to clicked payment method
            event.currentTarget.classList.add('selected');

            // Hide all forms
            document.querySelectorAll('.form-container').forEach(el => {
                el.classList.remove('active');
            });

            // Show selected form
            document.getElementById(method + '-form').classList.add('active');

            // Set selected payment method
            document.getElementById('selected_payment_method').value = method;
        }

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = document.getElementById('selected_payment_method').value;

            if (!selectedMethod) {
                e.preventDefault();
                alert('الرجاء اختيار طريقة دفع');
                return;
            }

            // Validate based on payment method
            if (selectedMethod === 'bank') {
                const transferNumber = document.querySelector('#bank-form input[placeholder="أدخل رقم التحويل من البنك"]').value;
                const transferDate = document.querySelector('#bank-form input[type="date"]').value;

                if (!transferNumber || !transferDate) {
                    e.preventDefault();
                    alert('الرجاء إدخال رقم التحويل وتاريخ التحويل');
                    return;
                }
            } else if (selectedMethod === 'mobile') {
                const mobileNumber = document.querySelector('#mobile-form input[type="tel"]').value;
                const accountName = document.querySelector('#mobile-form input[placeholder="اسم صاحب حساب جوال باي"]').value;

                if (!mobileNumber || !accountName) {
                    e.preventDefault();
                    alert('الرجاء إدخال رقم الجوال واسم صاحب الحساب');
                    return;
                }
            } else if (selectedMethod === 'credit') {
                const cardNumber = document.querySelector('#credit-form input[placeholder="1234 5678 9012 3456"]').value;
                const expiryDate = document.querySelector('#credit-form input[placeholder="MM/YY"]').value;
                const cvv = document.querySelector('#credit-form input[placeholder="123"]').value;
                const cardName = document.querySelector('#credit-form input[placeholder="كما تظهر على البطاقة"]').value;

                if (!cardNumber || !expiryDate || !cvv || !cardName) {
                    e.preventDefault();
                    alert('الرجاء إدخال جميع بيانات البطاقة');
                    return;
                }
            }
        });

        // Initialize with first payment method selected
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.payment-method').click();
        });
    </script>
</body>
</html>
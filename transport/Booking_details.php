<?php
// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملفات قاعدة البيانات
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';

// التحقق من وجود معرف الرحلة
if (!isset($_GET['trip_id'])) {
    header('Location: ../events.php');
    exit();
}

$trip_id = $_GET['trip_id'];

// بيانات تجريبية للرحلات
$trips_data = [
    1 => [
        'id' => 1,
        'event_id' => 1,
        'starting_point_id' => 3,
        'event_title' => 'حفلة محمد عساف',
        'event_date' => '2024-02-15 20:00:00',
        'event_location' => 'مسرح غزة الكبير',
        'starting_point_name' => 'رفح',
        'region' => 'south',
        'transport_type_name' => 'حافلة سياحية',
        'model' => 'مرسيدس',
        'year' => '2022',
        'plate_number' => 'غ-123456',
        'departure_time' => '18:00:00',
        'arrival_time' => '19:30:00',
        'driver_name' => 'أحمد محمد',
        'driver_rating' => '4.8',
        'available_seats' => 15,
        'price' => 25
    ],
    2 => [
        'id' => 2,
        'event_id' => 1,
        'starting_point_id' => 3,
        'event_title' => 'حفلة محمد عساف',
        'event_date' => '2024-02-15 20:00:00',
        'event_location' => 'مسرح غزة الكبير',
        'starting_point_name' => 'رفح',
        'region' => 'south',
        'transport_type_name' => 'ميكروباص',
        'model' => 'هيونداي',
        'year' => '2021',
        'plate_number' => 'غ-789012',
        'departure_time' => '18:30:00',
        'arrival_time' => '19:45:00',
        'driver_name' => 'محمد أحمد',
        'driver_rating' => '4.5',
        'available_seats' => 8,
        'price' => 20
    ],
    3 => [
        'id' => 3,
        'event_id' => 1,
        'starting_point_id' => 3,
        'event_title' => 'حفلة محمد عساف',
        'event_date' => '2024-02-15 20:00:00',
        'event_location' => 'مسرح غزة الكبير',
        'starting_point_name' => 'رفح',
        'region' => 'south',
        'transport_type_name' => 'حافلة عادية',
        'model' => 'إيفيكو',
        'year' => '2020',
        'plate_number' => 'غ-345678',
        'departure_time' => '17:30:00',
        'arrival_time' => '19:00:00',
        'driver_name' => 'خالد سالم',
        'driver_rating' => '4.2',
        'available_seats' => 25,
        'price' => 15
    ]
];

// جلب بيانات الرحلة من قاعدة البيانات
try {
    $trip = get_trip_by_id($trip_id);

    // إذا لم توجد الرحلة، استخدام البيانات الافتراضية
    if (!$trip) {
        $trip = $trips_data[$trip_id] ?? null;
        if (!$trip) {
            header('Location: ../events.php');
            exit();
        }
    }
} catch (Exception $e) {
    error_log("Error fetching trip: " . $e->getMessage());
    $trip = $trips_data[$trip_id] ?? null;
    if (!$trip) {
        header('Location: ../events.php');
        exit();
    }
}

// تم نقل الدوال المساعدة إلى includes/transport_functions.php
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملخص الحجز - <?php echo $trip['event_title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
        }

        .progress-step.active {
            background-color: #3b82f6;
            color: white;
        }

        .progress-step.completed {
            background-color: #10b981;
            color: white;
        }

        .form-input {
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .summary-card {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Progress Bar -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">مراحل إتمام الحجز</h2>
            <div class="flex justify-between items-center relative">
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 z-0"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="progress-step w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-md">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-700">اختيار نقطة الانطلاق</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="progress-step w-12 h-12 rounded-full flex items-center justify-center bg-green-500 text-white font-bold shadow-md">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-700">اختيار الرحلة</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="progress-step w-12 h-12 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-md active">
                        3
                    </div>
                    <span class="mt-2 text-sm font-medium text-purple-700">بيانات الحجز</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="progress-step w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                        4
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-500">طريقة الدفع</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="progress-step w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                        5
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-500">تأكيد الحجز</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Booking Summary -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl p-6 summary-card">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">ملخص الحجز</h3>

                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-2">الفعالية</h4>
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-lg mr-3">
                                <i class="fas fa-calendar-alt text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800"><?php echo $trip['event_title']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($trip['event_date'])); ?></p>
                                <p class="text-sm text-gray-500"><?php echo $trip['event_location']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-2">نقطة الانطلاق</h4>
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-lg mr-3">
                                <i class="fas fa-map-marker-alt text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800"><?php echo $trip['starting_point_name']; ?></p>
                                <p class="text-sm text-gray-500"><?php echo get_region_name($trip['region'] ?? ''); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-2">تفاصيل الرحلة</h4>
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-lg mr-3">
                                <i class="fas fa-bus text-purple-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800"><?php echo $trip['transport_type_name']; ?></p>
                                <?php if ($trip['model']): ?>
                                <p class="text-sm text-gray-500"><?php echo $trip['model'] . ' ' . $trip['year']; ?> - <?php echo $trip['plate_number']; ?></p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-500">موعد الانطلاق: <?php echo format_time($trip['departure_time']); ?></p>
                                <p class="text-sm text-gray-500">موعد الوصول: <?php echo format_time($trip['arrival_time']); ?></p>
                                <?php if ($trip['driver_name']): ?>
                                <p class="text-sm text-gray-500">السائق: <?php echo $trip['driver_name']; ?> (⭐ <?php echo $trip['driver_rating']; ?>)</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">سعر التذكرة</span>
                            <span class="font-bold" id="ticket-price"><?php echo $trip['price']; ?> ₪</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">عدد الركاب</span>
                            <span class="font-bold" id="passenger-count">1</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold text-purple-600 mt-3 pt-2 border-t">
                            <span>المجموع</span>
                            <span id="total-amount"><?php echo $trip['price']; ?> ₪</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">معلومات الحجز</h3>

                    <form id="bookingForm" method="POST" action="process_booking.php">
                        <input type="hidden" name="trip_id" value="<?php echo $trip['id']; ?>">
                        <input type="hidden" name="event_id" value="<?php echo $trip['event_id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <input type="text" id="name" name="name" required
                                           class="form-input pr-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                           placeholder="أدخل اسمك الكامل">
                                </div>
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">رقم الجوال</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <input type="tel" id="phone" name="phone" required
                                           class="form-input pr-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                           placeholder="مثال: 05XXXXXXXX">
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني (اختياري)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <input type="email" id="email" name="email"
                                           class="form-input pr-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                           placeholder="example@domain.com">
                                </div>
                            </div>

                            <div>
                                <label for="passengers" class="block text-sm font-medium text-gray-700 mb-1">عدد الركاب</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <select id="passengers" name="passengers" required
                                            class="form-input pr-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="" disabled selected>اختر عدد الركاب</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">ملاحظات أو طلبات خاصة</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                      placeholder="مثال: أحتاج إلى كرسي أطفال"></textarea>
                            <p class="mt-1 text-sm text-gray-500">أخبرنا إذا كان لديك أي متطلبات خاصة</p>
                        </div>

                        <div class="mt-8 flex justify-between items-center">
                            <a href="trips.php?event_id=<?php echo $trip['event_id']; ?>&starting_point_id=<?php echo $trip['starting_point_id']; ?>" class="text-purple-600 hover:text-purple-800 font-medium flex items-center">
                                <i class="fas fa-arrow-right ml-2"></i>
                                العودة إلى اختيار الرحلة
                            </a>

                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition duration-300 flex items-center">
                                المتابعة لاختيار طريقة الدفع
                                <i class="fas fa-arrow-left mr-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ticketPrice = <?php echo $trip['price']; ?>;

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            // Get form values
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const passengers = document.getElementById('passengers').value;

            // Simple validation
            if(!name || !phone || !passengers) {
                e.preventDefault();
                alert('الرجاء إدخال جميع الحقول المطلوبة');
                return;
            }

            // Phone validation
            const phoneRegex = /^05[0-9]{8}$/;
            if(!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('الرجاء إدخال رقم جوال صحيح (مثال: 0501234567)');
                return;
            }

            // Check available seats
            const availableSeats = <?php echo $trip['available_seats']; ?>;
            if(parseInt(passengers) > availableSeats) {
                e.preventDefault();
                alert(`عذراً، المقاعد المتاحة فقط ${availableSeats} مقعد`);
                return;
            }
        });

        // Update passengers count and total price
        document.getElementById('passengers').addEventListener('change', function() {
            const count = parseInt(this.value);
            if(count > 0) {
                const totalPrice = count * ticketPrice;
                document.getElementById('total-amount').textContent = `${totalPrice} ₪`;
                document.getElementById('passenger-count').textContent = count;
            }
        });
    </script>
</body>
</html>
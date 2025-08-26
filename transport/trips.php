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

// التحقق من وجود المعاملات المطلوبة
if (!isset($_GET['event_id']) || !isset($_GET['starting_point_id'])) {
    header('Location: ../events.php');
    exit();
}

$event_id = $_GET['event_id'];
$starting_point_id = $_GET['starting_point_id'];

// جلب بيانات الفعالية من قاعدة البيانات
$event = get_event_by_id($event_id);
if (!$event) {
    header('Location: ../events.php');
    exit();
}

// جلب بيانات نقطة الانطلاق من قاعدة البيانات
try {
    $db = new Database();
    $db->query("SELECT * FROM transport_starting_points WHERE id = :id");
    $db->bind(':id', $starting_point_id);
    $starting_point = $db->single();

    if (!$starting_point) {
        header('Location: starting_points.php?event_id=' . $event_id);
        exit();
    }
} catch (Exception $e) {
    error_log("Error fetching starting point: " . $e->getMessage());
    header('Location: starting_points.php?event_id=' . $event_id);
    exit();
}

// جلب الرحلات من قاعدة البيانات
try {
    $trips = get_trips_by_event_and_starting_point($event_id, $starting_point_id);

    // إذا لم توجد رحلات، استخدام رحلات افتراضية
    if (empty($trips)) {
        $trips = [
            [
                'id' => 1,
                'transport_type_name' => 'حافلة سياحية',
                'description' => 'حافلة مكيفة مريحة',
                'model' => 'مرسيدس',
                'year' => '2022',
                'plate_number' => 'غ-123456',
                'available_seats' => 15,
                'driver_name' => 'أحمد محمد',
                'driver_photo' => null,
                'driver_rating' => '4.8',
                'experience_years' => '10',
                'departure_time' => '18:00:00',
                'arrival_time' => '19:30:00',
                'features' => '["wifi", "ac", "comfortable_seats"]',
                'price' => 25
            ],
            [
                'id' => 2,
                'transport_type_name' => 'ميكروباص',
                'description' => 'ميكروباص سريع ومريح',
                'model' => 'هيونداي',
                'year' => '2021',
                'plate_number' => 'غ-789012',
                'available_seats' => 8,
                'driver_name' => 'محمد أحمد',
                'driver_photo' => null,
                'driver_rating' => '4.5',
                'experience_years' => '7',
                'departure_time' => '18:30:00',
                'arrival_time' => '19:45:00',
                'features' => '["ac", "music"]',
                'price' => 20
            ],
            [
                'id' => 3,
                'transport_type_name' => 'حافلة عادية',
                'description' => 'حافلة اقتصادية',
                'model' => 'إيفيكو',
                'year' => '2020',
                'plate_number' => 'غ-345678',
                'available_seats' => 25,
                'driver_name' => 'خالد سالم',
                'driver_photo' => null,
                'driver_rating' => '4.2',
                'experience_years' => '12',
                'departure_time' => '17:30:00',
                'arrival_time' => '19:00:00',
                'features' => '["ac"]',
                'price' => 15
            ]
        ];
    }
} catch (Exception $e) {
    error_log("Error fetching trips: " . $e->getMessage());
    $trips = [];
}

// تم نقل الدوال المساعدة إلى includes/transport_functions.php
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرحلات المتاحة - <?php echo $event['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7e22ce',
                            800: '#6b21a8',
                            900: '#581c87',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f3ff;
        }

        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .trip-card {
            transition: all 0.3s ease;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .expired-trip {
            opacity: 0.6;
            position: relative;
        }

        .expired-trip::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 0, 0, 0.1) 10px,
                rgba(255, 0, 0, 0.1) 20px
            );
            pointer-events: none;
            z-index: 1;
        }

        .expired-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(220, 38, 38, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            z-index: 2;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Progress Bar -->
        <div class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-6">
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
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg">
                            2
                        </div>
                        <span class="mt-2 text-sm font-medium text-purple-700">اختيار الرحلة</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                            3
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-500">بيانات الحجز</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                            4
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-500">طريقة الدفع</span>
                    </div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                            5
                        </div>
                        <span class="mt-2 text-sm font-medium text-gray-500">تأكيد الحجز</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header class="bg-gradient-to-r from-purple-700 to-purple-900 text-white shadow-lg">
            <div class="container mx-auto px-4 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">الرحلات المتاحة</h1>
                        <p class="text-purple-200 mt-1">من <?php echo $starting_point['name']; ?> إلى <?php echo $event['title']; ?></p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="starting_points.php?event_id=<?php echo $event_id; ?>" class="bg-white text-purple-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition">
                            <i class="fas fa-arrow-right mr-2"></i>تغيير نقطة الانطلاق
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Event Info -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg ml-3">
                            <i class="fas fa-calendar-alt text-blue-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">الفعالية</h3>
                            <p class="text-gray-900"><?php echo $event['title']; ?></p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg ml-3">
                            <i class="fas fa-map-marker-alt text-green-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">نقطة الانطلاق</h3>
                            <p class="text-gray-900"><?php echo $starting_point['name']; ?></p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg ml-3">
                            <i class="fas fa-clock text-purple-500 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">تاريخ الفعالية</h3>
                            <p class="text-gray-900"><?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($trips)): ?>
            <!-- No Trips Available -->
            <div class="bg-white rounded-xl shadow-md p-8 text-center">
                <i class="fas fa-exclamation-circle text-4xl text-primary-500 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-700 mb-2">لا توجد رحلات متاحة</h3>
                <p class="text-gray-500 mb-6">عذراً، لا توجد رحلات متاحة من <?php echo $starting_point['name']; ?> لهذه الفعالية حالياً.</p>
                <a href="starting_points.php?event_id=<?php echo $event_id; ?>" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg font-medium transition">
                    <i class="fas fa-arrow-right mr-2"></i>اختر نقطة انطلاق أخرى
                </a>
            </div>
            <?php else: ?>

            <?php
            // فحص وجود رحلات منتهية
            $expiredTrips = array_filter($trips, function($trip) {
                return isset($trip['is_expired']) && $trip['is_expired'];
            });
            $activeTrips = array_filter($trips, function($trip) {
                return !isset($trip['is_expired']) || !$trip['is_expired'];
            });
            ?>

            <?php if (!empty($expiredTrips)): ?>
            <!-- Expired Trips Notice -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-yellow-600 text-xl ml-3"></i>
                    <div>
                        <h4 class="text-yellow-800 font-semibold">ملاحظة</h4>
                        <p class="text-yellow-700 text-sm">
                            يتم عرض <?php echo count($expiredTrips); ?> رحلة منتهية للمراجعة فقط.
                            الرحلات المنتهية غير متاحة للحجز.
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- View Toggle -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-purple-800">
                        الرحلات
                        <span class="text-lg text-gray-600">
                            (<?php echo count($activeTrips); ?> متاحة
                            <?php if (!empty($expiredTrips)): ?>
                                • <?php echo count($expiredTrips); ?> منتهية
                            <?php endif; ?>)
                        </span>
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <button id="card-view" class="bg-purple-100 text-purple-700 p-2 rounded-lg active">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button id="table-view" class="bg-gray-200 text-gray-700 p-2 rounded-lg">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Cards View -->
            <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($trips as $trip): ?>
                <?php $isExpired = isset($trip['is_expired']) && $trip['is_expired']; ?>
                <div class="trip-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 <?php echo $isExpired ? 'expired-trip' : ''; ?> relative">
                    <div class="p-6">
                        <!-- Trip Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-purple-800"><?php echo $trip['transport_type_name']; ?></h3>
                                <div class="text-sm text-purple-600"><?php echo $trip['description']; ?></div>
                                <?php if ($trip['model']): ?>
                                <div class="text-xs text-gray-500 mt-1"><?php echo $trip['model'] . ' ' . $trip['year']; ?> - <?php echo $trip['plate_number']; ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($isExpired): ?>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    <i class="fas fa-clock mr-1"></i>منتهية
                                </span>
                            <?php else: ?>
                                <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    <?php echo $trip['available_seats']; ?> مقاعد متبقية
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Driver Info -->
                        <?php if ($trip['driver_name']): ?>
                        <div class="flex items-center mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center ml-3">
                                <?php if ($trip['driver_photo']): ?>
                                <img src="../assets/uploads/<?php echo $trip['driver_photo']; ?>" alt="<?php echo $trip['driver_name']; ?>" class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                <i class="fas fa-user text-gray-500"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900"><?php echo $trip['driver_name']; ?></div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-star text-yellow-400"></i> <?php echo $trip['driver_rating']; ?>
                                    • <?php echo $trip['experience_years']; ?> سنوات خبرة
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Time Info -->
                        <div class="flex justify-between items-center my-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-500">وقت الانطلاق</div>
                                <div class="text-lg font-bold text-purple-700"><?php echo format_time($trip['departure_time']); ?></div>
                            </div>
                            <div class="text-purple-500 mx-2">
                                <i class="fas fa-arrow-left"></i>
                            </div>
                            <div class="text-center">
                                <div class="text-sm text-gray-500">وقت الوصول</div>
                                <div class="text-lg font-bold text-purple-700"><?php echo format_time($trip['arrival_time']); ?></div>
                            </div>
                        </div>

                        <!-- Features -->
                        <?php if ($trip['features']): ?>
                        <div class="my-4">
                            <div class="text-sm text-gray-500 mb-1">المميزات:</div>
                            <div class="flex flex-wrap">
                                <?php
                                $features = json_decode($trip['features'], true);
                                if ($features):
                                    foreach ($features as $feature): ?>
                                <span class="bg-blue-100 text-blue-800 badge mr-1 mb-1">
                                    <i class="fas <?php echo get_feature_icon($feature); ?> mr-1"></i>
                                </span>
                                <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Price and Book Button -->
                        <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
                            <div class="text-2xl font-bold text-purple-700"><?php echo $trip['price']; ?> ₪</div>
                            <?php if ($isExpired): ?>
                                <button disabled class="bg-gray-400 text-white py-2 px-4 rounded-lg font-medium cursor-not-allowed">
                                    <i class="fas fa-clock mr-2"></i>منتهية
                                </button>
                            <?php else: ?>
                                <a href="Booking_details.php?trip_id=<?php echo $trip['id']; ?>" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg font-medium transition">
                                    <i class="fas fa-ticket-alt mr-2"></i>احجز الآن
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($isExpired): ?>
                        <div class="expired-overlay">
                            <i class="fas fa-clock mr-2"></i>منتهية
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Table View -->
            <div id="table-container" class="hidden overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">وقت الانطلاق</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">وقت الوصول</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">وسيلة النقل</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">السائق</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">المقاعد</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">السعر</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-purple-700 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($trips as $trip): ?>
                        <?php $isExpired = isset($trip['is_expired']) && $trip['is_expired']; ?>
                        <tr class="hover:bg-gray-50 <?php echo $isExpired ? 'opacity-60' : ''; ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo format_time($trip['departure_time']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo format_time($trip['arrival_time']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div><?php echo $trip['transport_type_name']; ?></div>
                                <?php if ($trip['model']): ?>
                                <div class="text-xs text-gray-400"><?php echo $trip['model'] . ' - ' . $trip['plate_number']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($trip['driver_name']): ?>
                                <div><?php echo $trip['driver_name']; ?></div>
                                <div class="text-xs text-gray-400">
                                    <i class="fas fa-star text-yellow-400"></i> <?php echo $trip['driver_rating']; ?>
                                </div>
                                <?php else: ?>
                                <span class="text-gray-400">غير محدد</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-<?php echo $trip['available_seats'] > 5 ? 'green' : 'red'; ?>-100 text-<?php echo $trip['available_seats'] > 5 ? 'green' : 'red'; ?>-800">
                                    <?php echo $trip['available_seats']; ?> مقاعد
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-purple-700"><?php echo $trip['price']; ?> ₪</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if ($isExpired): ?>
                                    <span class="text-white bg-gray-400 px-3 py-1 rounded-md text-sm cursor-not-allowed">
                                        <i class="fas fa-clock mr-1"></i>منتهية
                                    </span>
                                <?php else: ?>
                                    <a href="Booking_details.php?trip_id=<?php echo $trip['id']; ?>" class="text-white bg-purple-600 hover:bg-purple-700 px-3 py-1 rounded-md text-sm">
                                        <i class="fas fa-ticket-alt mr-1"></i>حجز
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // View toggle functionality
        const cardViewBtn = document.getElementById('card-view');
        const tableViewBtn = document.getElementById('table-view');
        const cardsContainer = document.getElementById('cards-container');
        const tableContainer = document.getElementById('table-container');

        cardViewBtn.addEventListener('click', function() {
            cardsContainer.classList.remove('hidden');
            tableContainer.classList.add('hidden');
            cardViewBtn.classList.add('bg-purple-100', 'text-purple-700');
            cardViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            tableViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            tableViewBtn.classList.remove('bg-purple-100', 'text-purple-700');
        });

        tableViewBtn.addEventListener('click', function() {
            cardsContainer.classList.add('hidden');
            tableContainer.classList.remove('hidden');
            tableViewBtn.classList.add('bg-purple-100', 'text-purple-700');
            tableViewBtn.classList.remove('bg-gray-200', 'text-gray-700');
            cardViewBtn.classList.add('bg-gray-200', 'text-gray-700');
            cardViewBtn.classList.remove('bg-purple-100', 'text-purple-700');
        });
    </script>
</body>
</html>

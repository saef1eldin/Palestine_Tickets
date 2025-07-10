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

// التحقق من وجود معرف الفعالية
if (!isset($_GET['event_id'])) {
    header('Location: ../events.php');
    exit();
}

$event_id = $_GET['event_id'];

// جلب بيانات الفعالية من قاعدة البيانات
$event = get_event_by_id($event_id);
if (!$event) {
    header('Location: ../events.php');
    exit();
}

// جلب نقاط الانطلاق المتاحة لهذه الفعالية فقط
try {
    $db = new Database();
    
    // جلب نقاط الانطلاق التي لها رحلات متاحة لهذه الفعالية
    $db->query("
        SELECT DISTINCT sp.*, 
               COUNT(t.id) as trips_count,
               MIN(t.price) as min_price
        FROM transport_starting_points sp
        INNER JOIN transport_trips t ON sp.id = t.starting_point_id
        WHERE t.event_id = :event_id 
        AND t.is_active = 1 
        AND t.available_seats > 0
        AND sp.is_active = 1
        GROUP BY sp.id
        ORDER BY sp.name ASC
    ");
    $db->bind(':event_id', $event_id);
    $starting_points = $db->resultSet();
    
    // إذا لم توجد نقاط انطلاق متاحة، إنشاء بيانات افتراضية للاختبار
    if (empty($starting_points)) {
        // التحقق من وجود نقاط انطلاق في الجدول
        $db->query("SELECT COUNT(*) as count FROM transport_starting_points");
        $points_exist = $db->single()['count'];
        
        if ($points_exist == 0) {
            // إنشاء نقاط انطلاق افتراضية
            $default_points = [
                ['name' => 'مدينة غزة', 'description' => 'وسط مدينة غزة - ميدان الجندي المجهول', 'region' => 'center', 'icon' => 'city'],
                ['name' => 'جباليا', 'description' => 'مخيم جباليا - المحطة الرئيسية', 'region' => 'north', 'icon' => 'camp'],
                ['name' => 'رفح', 'description' => 'مدينة رفح - المعبر الحدودي', 'region' => 'south', 'icon' => 'border-crossing'],
                ['name' => 'خان يونس', 'description' => 'مدينة خان يونس - الساحة المركزية', 'region' => 'south', 'icon' => 'city'],
                ['name' => 'بيت لاهيا', 'description' => 'بيت لاهيا - المنطقة الزراعية', 'region' => 'north', 'icon' => 'farm'],
                ['name' => 'دير البلح', 'description' => 'مدينة دير البلح - وسط القطاع', 'region' => 'center', 'icon' => 'palm-tree']
            ];
            
            foreach ($default_points as $point) {
                $db->query("INSERT INTO transport_starting_points (name, description, region, icon, is_active) VALUES (:name, :description, :region, :icon, 1)");
                $db->bind(':name', $point['name']);
                $db->bind(':description', $point['description']);
                $db->bind(':region', $point['region']);
                $db->bind(':icon', $point['icon']);
                $db->execute();
            }
        }
        
        // عرض رسالة عدم توفر رحلات لهذه الفعالية
        $no_trips_available = true;
    } else {
        $no_trips_available = false;
    }
} catch (Exception $e) {
    error_log("Error fetching starting points: " . $e->getMessage());
    $starting_points = [];
    $no_trips_available = true;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختيار نقطة الانطلاق - <?php echo $event['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f9fafb;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(139, 92, 246, 0.2), 0 10px 10px -5px rgba(139, 92, 246, 0.1);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.3);
        }

        .filter-btn.active {
            background-color: #7c3aed;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Progress Bar -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">مراحل حجز المواصلات</h2>
            <div class="flex justify-between items-center relative max-w-4xl mx-auto">
                <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2 z-0"></div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold shadow-lg">
                        1
                    </div>
                    <span class="mt-2 text-sm font-medium text-purple-700">اختيار نقطة الانطلاق</span>
                </div>

                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600 font-bold shadow-md">
                        2
                    </div>
                    <span class="mt-2 text-sm font-medium text-gray-500">اختيار الرحلة</span>
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

        <!-- Header -->
        <header class="mb-10 text-center">
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-purple-800 mb-3">اختر نقطة الانطلاق</h1>
                <p class="text-gray-600 max-w-2xl mx-auto mb-4">حدد موقع انطلاقك من القائمة أدناه للاستمتاع برحلة مريحة وسهلة</p>

                <!-- معلومات الفعالية -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 max-w-md mx-auto">
                    <div class="flex items-center justify-center">
                        <div class="bg-purple-100 p-3 rounded-lg ml-3">
                            <i class="fas fa-calendar-alt text-purple-500 text-xl"></i>
                        </div>
                        <div class="text-right">
                            <h3 class="font-bold text-purple-800"><?php echo $event['title']; ?></h3>
                            <p class="text-sm text-purple-600"><?php echo date('Y-m-d H:i', strtotime($event['date_time'])); ?></p>
                            <p class="text-sm text-purple-600"><?php echo $event['location']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?php if ($no_trips_available): ?>
        <!-- No Trips Available Message -->
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">لا توجد رحلات متاحة</h2>
            <p class="text-gray-600 mb-6">عذراً، لا توجد رحلات مواصلات متاحة لهذه الفعالية حالياً.</p>
            <div class="flex justify-center space-x-4">
                <a href="../events.php" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-6 rounded-lg font-medium transition">
                    <i class="fas fa-arrow-right mr-2"></i>العودة للفعاليات
                </a>
                <a href="../contact.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded-lg font-medium transition">
                    <i class="fas fa-phone mr-2"></i>اتصل بنا
                </a>
            </div>
        </div>
        <?php else: ?>
        <!-- Search and Filter -->
        <div class="mb-8">
            <div class="relative max-w-xl mx-auto mb-6">
                <input type="text" id="search" placeholder="ابحث عن نقطة الانطلاق..."
                       class="w-full pr-12 pl-4 py-3 rounded-full border border-gray-300 focus:border-primary-500 search-input transition-all duration-200">
                <button class="absolute left-3 top-3 text-gray-500 hover:text-primary-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="flex flex-wrap justify-center gap-3 mb-6">
                <button class="filter-btn px-4 py-2 rounded-full border border-purple-500 text-purple-600 hover:bg-purple-500 hover:text-white transition-all active" data-filter="all">
                    الكل (<?php echo count($starting_points); ?>)
                </button>
                <button class="filter-btn px-4 py-2 rounded-full border border-purple-500 text-purple-600 hover:bg-purple-500 hover:text-white transition-all" data-filter="north">
                    شمال غزة
                </button>
                <button class="filter-btn px-4 py-2 rounded-full border border-purple-500 text-purple-600 hover:bg-purple-500 hover:text-white transition-all" data-filter="center">
                    وسط غزة
                </button>
                <button class="filter-btn px-4 py-2 rounded-full border border-purple-500 text-purple-600 hover:bg-purple-500 hover:text-white transition-all" data-filter="south">
                    جنوب غزة
                </button>
            </div>
        </div>

        <!-- Points Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="points-container">
            <!-- Point cards will be added here by JavaScript -->
        </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // البيانات من قاعدة البيانات
            const points = <?php echo json_encode($starting_points); ?>;
            const noTripsAvailable = <?php echo $no_trips_available ? 'true' : 'false'; ?>;

            if (!noTripsAvailable && points.length > 0) {
                const container = document.getElementById('points-container');
                const searchInput = document.getElementById('search');
                const filterButtons = document.querySelectorAll('.filter-btn');

                // Render all points initially
                renderPoints(points);

                // Search functionality
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const filteredPoints = points.filter(point =>
                        point.name.toLowerCase().includes(searchTerm) ||
                        point.description.toLowerCase().includes(searchTerm)
                    );
                    renderPoints(filteredPoints);
                });

                // Filter functionality
                filterButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Update active button
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');

                        const filter = this.dataset.filter;
                        let filteredPoints = points;

                        if (filter !== 'all') {
                            filteredPoints = points.filter(point => point.region === filter);
                        }

                        renderPoints(filteredPoints);
                    });
                });

                function renderPoints(pointsArray) {
                    container.innerHTML = '';

                    if (pointsArray.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full text-center py-10">
                                <i class="fas fa-map-marker-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600">لا توجد نتائج مطابقة للبحث</p>
                            </div>
                        `;
                        return;
                    }

                    pointsArray.forEach(point => {
                        const iconMap = {
                            'city': 'fa-city',
                            'border-crossing': 'fa-passport',
                            'camp': 'fa-campground',
                            'palm-tree': 'fa-tree',
                            'farm': 'fa-tractor',
                            'village': 'fa-home'
                        };

                        const card = document.createElement('div');
                        card.className = 'bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 card-hover transition-all duration-300';
                        card.innerHTML = `
                            <div class="h-2 bg-gradient-to-r from-purple-400 to-purple-600"></div>
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mr-4">
                                        <i class="fas ${iconMap[point.icon] || 'fa-map-marker-alt'} text-xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-xl font-bold text-gray-800">${point.name}</h3>
                                        <div class="text-sm text-purple-600">
                                            ${point.trips_count} رحلة متاحة
                                            ${point.min_price ? ' • من ' + point.min_price + ' ₪' : ''}
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-6">${point.description}</p>
                                <a href="trips.php?event_id=<?php echo $event_id; ?>&starting_point_id=${point.id}" class="w-full py-2 px-4 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-all flex items-center justify-center">
                                    اختيار (${point.trips_count} رحلة)
                                    <i class="fas fa-arrow-left mr-2"></i>
                                </a>
                            </div>
                        `;

                        container.appendChild(card);
                    });
                }
            }
        });
    </script>
</body>
</html>

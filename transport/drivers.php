<?php
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/transport_functions.php';
require_once '../includes/auth.php';

$auth = new Auth();

// التحقق من تسجيل الدخول ودور المستخدم
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'transport_admin') {
    redirect('../index.php');
}

$db = new Database();

// جلب إحصائيات السائقين
$db->query("SELECT COUNT(*) as total_drivers FROM transport_drivers WHERE is_active = 1");
$total_drivers = $db->single()['total_drivers'];

$db->query("SELECT COUNT(*) as available_drivers FROM transport_drivers WHERE is_active = 1 AND status = 'available'");
$available_drivers = $db->single()['available_drivers'];

// جلب قائمة السائقين
$db->query("
    SELECT d.*, v.plate_number, v.model 
    FROM transport_drivers d 
    LEFT JOIN transport_vehicles v ON d.id = v.driver_id 
    WHERE d.is_active = 1 
    ORDER BY d.name
");
$drivers = $db->resultSet();

// عرض رسائل النجاح والخطأ
if (isset($_SESSION['success_message'])):
?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
    <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
</div>
<?php 
unset($_SESSION['success_message']);
endif;

if (isset($_SESSION['error_message'])):
?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
</div>
<?php 
unset($_SESSION['error_message']);
endif;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة السائقين - لوحة تحكم المواصلات</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-l from-purple-600 to-purple-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <i class="fas fa-users text-2xl"></i>
                    <h1 class="text-2xl font-bold">إدارة السائقين</h1>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="dashboard.php" class="text-purple-200 hover:text-white transition">
                        <i class="fas fa-arrow-right ml-1"></i>
                        العودة للوحة التحكم
                    </a>
                    <a href="../index.php" class="text-purple-200 hover:text-white transition">
                        <i class="fas fa-home ml-1"></i>
                        الموقع الرئيسي
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">إجمالي السائقين</p>
                        <h3 class="text-2xl font-bold text-blue-600"><?php echo $total_drivers; ?></h3>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">السائقين المتاحين</p>
                        <h3 class="text-2xl font-bold text-green-600"><?php echo $available_drivers; ?></h3>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">السائقين المشغولين</p>
                        <h3 class="text-2xl font-bold text-orange-600"><?php echo $total_drivers - $available_drivers; ?></h3>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-user-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivers Management -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">قائمة السائقين</h2>
                    <button onclick="openAddDriverModal()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center">
                        <i class="fas fa-plus ml-2"></i> إضافة سائق جديد
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">السائق</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم الهاتف</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المركبة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($drivers)): ?>
                            <?php foreach ($drivers as $driver): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-user text-purple-600"></i>
                                        </div>
                                        <div class="mr-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $driver['name']; ?></div>
                                            <div class="text-sm text-gray-500">رخصة: <?php echo $driver['license_number']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $driver['phone']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if ($driver['plate_number']): ?>
                                        <?php echo $driver['plate_number']; ?> - <?php echo $driver['model']; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">لا توجد مركبة</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($driver['status'] == 'available'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">متاح</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">مشغول</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 ml-3">تعديل</button>
                                    <button class="text-red-600 hover:text-red-900">حذف</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">لا يوجد سائقين مسجلين</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Driver Modal -->
    <div id="addDriverModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">إضافة سائق جديد</h3>
                    <button onclick="closeAddDriverModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="add_driver.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">اسم السائق</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف</label>
                            <input type="tel" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">رقم الرخصة</label>
                            <input type="text" name="license_number" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">العنوان</label>
                            <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                        <button type="button" onclick="closeAddDriverModal()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            حفظ السائق
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddDriverModal() {
            document.getElementById('addDriverModal').classList.remove('hidden');
        }

        function closeAddDriverModal() {
            document.getElementById('addDriverModal').classList.add('hidden');
        }

        // إغلاق Modal عند النقر خارجه
        document.getElementById('addDriverModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddDriverModal();
            }
        });
    </script>
</body>
</html>

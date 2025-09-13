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

// جلب قائمة السائقين مع معلومات المركبات والمنطقة
$db->query("
    SELECT d.*,
           v.plate_number, v.model, v.year, v.color, v.capacity,
           tt.name as transport_type_name, tt.icon as transport_type_icon,
           COUNT(v.id) as vehicles_count
    FROM transport_drivers d
    LEFT JOIN transport_vehicles v ON d.id = v.driver_id
    LEFT JOIN transport_types tt ON v.transport_type_id = tt.id
    WHERE d.is_active = 1
    GROUP BY d.id
    ORDER BY d.name
");
$drivers = $db->resultSet();

// جلب أنواع وسائل النقل للنماذج
$db->query("SELECT * FROM transport_types WHERE is_active = 1 ORDER BY name");
$transport_types = $db->resultSet();

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
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاتصال</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المنطقة</th>
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
                                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                                            <i class="fas fa-user text-purple-600"></i>
                                        </div>
                                        <div class="mr-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($driver['name']); ?></div>
                                            <div class="text-sm text-gray-500">
                                                رخصة: <?php echo htmlspecialchars($driver['license_number']); ?>
                                                <?php if ($driver['license_type']): ?>
                                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded mr-1">
                                                        <?php
                                                        $license_types = [
                                                            'private' => 'خاصة',
                                                            'public' => 'عامة',
                                                            'commercial' => 'تجارية',
                                                            'heavy' => 'ثقيلة'
                                                        ];
                                                        echo $license_types[$driver['license_type']] ?? $driver['license_type'];
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($driver['experience_years']): ?>
                                            <div class="text-xs text-blue-600">
                                                <i class="fas fa-clock ml-1"></i><?php echo $driver['experience_years']; ?> سنوات خبرة
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <i class="fas fa-phone text-green-600 ml-1"></i><?php echo htmlspecialchars($driver['phone']); ?>
                                    </div>
                                    <?php if ($driver['governorate'] || $driver['city']): ?>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-map-marker-alt text-red-500 ml-1"></i>
                                        <?php echo htmlspecialchars($driver['governorate'] ?? ''); ?>
                                        <?php if ($driver['city']): ?>
                                            - <?php echo htmlspecialchars($driver['city']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($driver['region']): ?>
                                    <div class="text-sm text-gray-900">
                                        <?php
                                        $regions = [
                                            'north' => 'الشمال',
                                            'center' => 'الوسط',
                                            'south' => 'الجنوب'
                                        ];
                                        echo $regions[$driver['region']] ?? $driver['region'];
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($driver['address']): ?>
                                    <div class="text-xs text-gray-500 max-w-xs truncate">
                                        <?php echo htmlspecialchars($driver['address']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($driver['plate_number']): ?>
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-car text-blue-600 ml-1"></i><?php echo htmlspecialchars($driver['plate_number']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo htmlspecialchars($driver['model']); ?>
                                            <?php if ($driver['year']): ?>
                                                (<?php echo $driver['year']; ?>)
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($driver['transport_type_name']): ?>
                                        <div class="text-xs text-purple-600">
                                            <i class="fas fa-<?php echo $driver['transport_type_icon'] ?? 'bus'; ?> ml-1"></i>
                                            <?php echo htmlspecialchars($driver['transport_type_name']); ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">
                                            <i class="fas fa-exclamation-triangle ml-1"></i>لا توجد مركبة
                                        </span>
                                        <div class="mt-1">
                                            <button onclick="addVehicleToDriver(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['name']); ?>')"
                                                    class="text-xs text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-plus ml-1"></i>إضافة مركبة
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($driver['status'] == 'available'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle ml-1"></i>متاح
                                        </span>
                                    <?php elseif ($driver['status'] == 'busy'): ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock ml-1"></i>مشغول
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle ml-1"></i>غير متصل
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2 space-x-reverse">
                                        <button onclick="editDriver(<?php echo $driver['id']; ?>)"
                                                class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded">
                                            <i class="fas fa-edit ml-1"></i>تعديل
                                        </button>
                                        <button onclick="deleteDriver(<?php echo $driver['id']; ?>, '<?php echo htmlspecialchars($driver['name']); ?>')"
                                                class="text-red-600 hover:text-red-900 px-2 py-1 rounded">
                                            <i class="fas fa-trash ml-1"></i>حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-users text-4xl text-gray-300 mb-2"></i>
                                    <div>لا يوجد سائقين مسجلين</div>
                                    <button onclick="openAddDriverModal()" class="mt-2 text-purple-600 hover:text-purple-800">
                                        <i class="fas fa-plus ml-1"></i>إضافة سائق جديد
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Driver Modal -->
    <div id="addDriverModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">إضافة سائق جديد</h3>
                    <button onclick="closeAddDriverModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="addDriverForm" onsubmit="submitDriverForm(event)">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- معلومات السائق الأساسية -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">معلومات السائق</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">اسم السائق *</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهاتف *</label>
                                <input type="tel" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الرخصة *</label>
                                <input type="text" name="license_number" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">نوع الرخصة</label>
                                <select name="license_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="private">رخصة خاصة</option>
                                    <option value="public">رخصة عامة</option>
                                    <option value="commercial">رخصة تجارية</option>
                                    <option value="heavy">رخصة مركبات ثقيلة</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ انتهاء الرخصة</label>
                                <input type="date" name="license_expiry" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">سنوات الخبرة</label>
                                <input type="number" name="experience_years" min="0" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- معلومات الموقع والمركبة -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">معلومات الموقع</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المحافظة</label>
                                <select name="governorate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">اختر المحافظة</option>
                                    <option value="غزة">غزة</option>
                                    <option value="الشمال">الشمال</option>
                                    <option value="الوسطى">الوسطى</option>
                                    <option value="خان يونس">خان يونس</option>
                                    <option value="رفح">رفح</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المدينة/المنطقة</label>
                                <input type="text" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">المنطقة</label>
                                <select name="region" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="center">الوسط</option>
                                    <option value="north">الشمال</option>
                                    <option value="south">الجنوب</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">العنوان التفصيلي</label>
                                <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                            </div>

                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2 mt-6">معلومات المركبة (اختيارية)</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">نوع المركبة</label>
                                <select name="transport_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">اختر نوع المركبة</option>
                                    <?php foreach ($transport_types as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo $type['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">رقم اللوحة</label>
                                <input type="text" name="vehicle_plate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">موديل المركبة</label>
                                <input type="text" name="vehicle_model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">سنة الصنع</label>
                                    <input type="number" name="vehicle_year" min="1990" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">السعة</label>
                                    <input type="number" name="vehicle_capacity" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                                <input type="text" name="vehicle_color" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
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
            document.getElementById('addDriverForm').reset();
        }

        // إغلاق Modal عند النقر خارجه
        document.getElementById('addDriverModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddDriverModal();
            }
        });

        // معالجة إرسال النموذج
        function submitDriverForm(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'add');
            formData.append('is_active', '1');

            // إظهار مؤشر التحميل
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'جاري الحفظ...';
            submitBtn.disabled = true;

            fetch('actions/drivers_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeAddDriverModal();
                    location.reload(); // إعادة تحميل الصفحة لإظهار السائق الجديد
                } else {
                    alert('خطأ: ' + data.message);
                }
            })
            .catch(error => {
                    console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        // حذف سائق
        function deleteDriver(id, name) {
            if (confirm(`هل أنت متأكد من حذف السائق "${name}"؟`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('actions/drivers_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('خطأ: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في الاتصال');
                });
            }
        }

        // إضافة مركبة لسائق موجود
        function addVehicleToDriver(driverId, driverName) {
            const transportTypes = <?php echo json_encode($transport_types); ?>;

            let typeOptions = '';
            transportTypes.forEach(type => {
                typeOptions += `<option value="${type.id}">${type.name}</option>`;
            });

            const vehicleForm = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع المركبة *</label>
                        <select id="vehicleType" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">اختر نوع المركبة</option>
                            ${typeOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">رقم اللوحة *</label>
                        <input type="text" id="vehiclePlate" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">موديل المركبة *</label>
                        <input type="text" id="vehicleModel" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">سنة الصنع</label>
                            <input type="number" id="vehicleYear" min="1990" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">السعة *</label>
                            <input type="number" id="vehicleCapacity" required min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                        <input type="text" id="vehicleColor" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
            `;

            if (confirm(`إضافة مركبة للسائق: ${driverName}؟`)) {
                // إنشاء modal مؤقت
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">إضافة مركبة للسائق: ${driverName}</h3>
                        ${vehicleForm}
                        <div class="mt-6 flex justify-end space-x-3 space-x-reverse">
                            <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                إلغاء
                            </button>
                            <button onclick="submitVehicle(${driverId})" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                حفظ المركبة
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
        }

        // إرسال بيانات المركبة
        function submitVehicle(driverId) {
            const formData = new FormData();
            formData.append('action', 'add_vehicle');
            formData.append('driver_id', driverId);
            formData.append('transport_type_id', document.getElementById('vehicleType').value);
            formData.append('plate_number', document.getElementById('vehiclePlate').value);
            formData.append('model', document.getElementById('vehicleModel').value);
            formData.append('year', document.getElementById('vehicleYear').value);
            formData.append('capacity', document.getElementById('vehicleCapacity').value);
            formData.append('color', document.getElementById('vehicleColor').value);

            fetch('actions/drivers_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.querySelector('.fixed').remove();
                    location.reload();
                } else {
                    alert('خطأ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
            });
        }

        // تعديل سائق (placeholder)
        function editDriver(id) {
            alert('ميزة التعديل ستكون متاحة قريباً');
        }
    </script>
</body>
</html>

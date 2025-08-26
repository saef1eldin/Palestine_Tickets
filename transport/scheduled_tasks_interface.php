<?php
require_once '../includes/init.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// معالجة تشغيل المهام
if ($_POST['action'] ?? '' === 'run_tasks') {
    $selected_tasks = $_POST['tasks'] ?? [];
    $results = [];
    
    if (in_array('delete_expired_trips', $selected_tasks)) {
        $results['delete_expired_trips'] = deleteExpiredTrips();
    }
    
    if (in_array('cleanup_notifications', $selected_tasks)) {
        $results['cleanup_notifications'] = cleanupOldNotifications();
    }
    
    if (in_array('send_admin_report', $selected_tasks)) {
        $results['send_admin_report'] = sendAdminReport();
    }
    
    // إرجاع النتائج كـ JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'results' => $results]);
    exit;
}

// دالة لحذف الرحلات المنتهية
function deleteExpiredTrips() {
    global $db;
    
    try {
        // جلب الرحلات المنتهية
        $db->query("
            SELECT 
                tt.id,
                tt.departure_time,
                tsp.name as starting_point_name,
                e.title as event_title,
                COUNT(tb.id) as bookings_count
            FROM transport_trips tt
            LEFT JOIN transport_starting_points tsp ON tt.starting_point_id = tsp.id
            LEFT JOIN events e ON tt.event_id = e.id
            LEFT JOIN transport_bookings tb ON tt.id = tb.trip_id
            WHERE tt.departure_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND tt.is_active = 1
            GROUP BY tt.id
        ");
        $expired_trips = $db->resultSet();
        
        if (empty($expired_trips)) {
            return ['status' => 'info', 'message' => 'لا توجد رحلات منتهية للحذف', 'count' => 0];
        }
        
        $deleted_count = 0;
        $total_bookings_deleted = 0;
        
        foreach ($expired_trips as $trip) {
            // حذف الحجوزات المرتبطة أولاً
            if ($trip['bookings_count'] > 0) {
                $db->query("DELETE FROM transport_bookings WHERE trip_id = :trip_id");
                $db->bind(':trip_id', $trip['id']);
                $db->execute();
                $total_bookings_deleted += $trip['bookings_count'];
            }
            
            // حذف الرحلة
            $db->query("DELETE FROM transport_trips WHERE id = :trip_id");
            $db->bind(':trip_id', $trip['id']);
            
            if ($db->execute()) {
                $deleted_count++;
                
                // إرسال إشعار للإدمن
                $message = "تم حذف رحلة منتهية: {$trip['starting_point_name']} إلى {$trip['event_title']} - {$trip['departure_time']}";
                if ($trip['bookings_count'] > 0) {
                    $message .= " (مع {$trip['bookings_count']} حجز)";
                }
                sendAdminNotification($message, 'warning');
            }
        }
        
        return [
            'status' => 'success', 
            'message' => "تم حذف {$deleted_count} رحلة منتهية", 
            'count' => $deleted_count,
            'bookings_deleted' => $total_bookings_deleted
        ];
        
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'خطأ في حذف الرحلات: ' . $e->getMessage()];
    }
}

// دالة لتنظيف الإشعارات القديمة
function cleanupOldNotifications() {
    global $db;
    
    try {
        $db->query("SELECT COUNT(*) as count FROM admin_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $old_count = $db->single()['count'];
        
        $db->query("DELETE FROM admin_notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $db->execute();
        
        return [
            'status' => 'success', 
            'message' => "تم حذف {$old_count} إشعار قديم", 
            'count' => $old_count
        ];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'خطأ في تنظيف الإشعارات: ' . $e->getMessage()];
    }
}

// دالة لإرسال تقرير للإدمن
function sendAdminReport() {
    global $db;
    
    try {
        // إحصائيات عامة
        $db->query("SELECT COUNT(*) as count FROM transport_trips WHERE is_active = 1");
        $active_trips = $db->single()['count'];
        
        $db->query("SELECT COUNT(*) as count FROM transport_bookings WHERE status = 'pending'");
        $pending_bookings = $db->single()['count'];
        
        $db->query("SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0");
        $unread_notifications = $db->single()['count'];
        
        $report = "تقرير النظام اليومي:\n";
        $report .= "- الرحلات النشطة: {$active_trips}\n";
        $report .= "- الحجوزات المعلقة: {$pending_bookings}\n";
        $report .= "- الإشعارات غير المقروءة: {$unread_notifications}";
        
        sendAdminNotification($report, 'info');
        
        return [
            'status' => 'success', 
            'message' => 'تم إرسال التقرير للإدمن', 
            'report' => $report
        ];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => 'خطأ في إنشاء التقرير: ' . $e->getMessage()];
    }
}

// دالة لإرسال إشعار للإدمن
function sendAdminNotification($message, $type = 'info') {
    global $db;
    
    try {
        $db->query("
            INSERT INTO admin_notifications (message, type, created_at) 
            VALUES (:message, :type, NOW())
        ");
        $db->bind(':message', $message);
        $db->bind(':type', $type);
        $db->execute();
    } catch (Exception $e) {
        // إنشاء جدول الإشعارات إذا لم يكن موجوداً
        try {
            $db->query("
                CREATE TABLE IF NOT EXISTS admin_notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message TEXT NOT NULL,
                    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
                    is_read BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $db->execute();
            
            // إعادة المحاولة
            $db->query("
                INSERT INTO admin_notifications (message, type, created_at) 
                VALUES (:message, :type, NOW())
            ");
            $db->bind(':message', $message);
            $db->bind(':type', $type);
            $db->execute();
        } catch (Exception $e2) {
            error_log("فشل في حفظ الإشعار: " . $e2->getMessage());
        }
    }
}

// جلب إحصائيات للعرض
try {
    // عدد الرحلات المنتهية
    $db->query("
        SELECT COUNT(*) as count 
        FROM transport_trips 
        WHERE departure_time < DATE_SUB(NOW(), INTERVAL 1 HOUR) 
        AND is_active = 1
    ");
    $expired_trips_count = $db->single()['count'];
    
    // عدد الإشعارات القديمة
    $db->query("
        SELECT COUNT(*) as count 
        FROM admin_notifications 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $old_notifications_count = $db->single()['count'];
    
} catch (Exception $e) {
    $expired_trips_count = 0;
    $old_notifications_count = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تشغيل المهام المجدولة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .task-card.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .loading {
            display: none;
        }
        .loading.active {
            display: inline-block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6 max-w-4xl">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-cogs text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">تشغيل المهام المجدولة</h1>
                <p class="text-gray-600">اختر المهام التي تريد تنفيذها من القائمة أدناه</p>
            </div>
        </div>

        <!-- Tasks Selection -->
        <form id="tasksForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Task 1: Delete Expired Trips -->
                <div class="task-card bg-white rounded-xl shadow-md p-6 border-2 border-gray-200 cursor-pointer"
                     onclick="toggleTask('delete_expired_trips')">
                    <input type="checkbox" name="tasks[]" value="delete_expired_trips" id="delete_expired_trips" class="hidden">
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">حذف الرحلات المنتهية</h3>
                        <p class="text-gray-600 text-sm mb-4">حذف الرحلات التي انتهت منذ أكثر من ساعة</p>
                        <div class="bg-red-50 rounded-lg p-3">
                            <span class="text-red-700 font-semibold"><?php echo $expired_trips_count; ?></span>
                            <span class="text-red-600 text-sm">رحلة منتهية</span>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="task-status text-sm text-gray-500">انقر للاختيار</span>
                    </div>
                </div>

                <!-- Task 2: Cleanup Notifications -->
                <div class="task-card bg-white rounded-xl shadow-md p-6 border-2 border-gray-200 cursor-pointer"
                     onclick="toggleTask('cleanup_notifications')">
                    <input type="checkbox" name="tasks[]" value="cleanup_notifications" id="cleanup_notifications" class="hidden">
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-broom text-yellow-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">تنظيف الإشعارات القديمة</h3>
                        <p class="text-gray-600 text-sm mb-4">حذف الإشعارات الأقدم من 30 يوم</p>
                        <div class="bg-yellow-50 rounded-lg p-3">
                            <span class="text-yellow-700 font-semibold"><?php echo $old_notifications_count; ?></span>
                            <span class="text-yellow-600 text-sm">إشعار قديم</span>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="task-status text-sm text-gray-500">انقر للاختيار</span>
                    </div>
                </div>

                <!-- Task 3: Send Admin Report -->
                <div class="task-card bg-white rounded-xl shadow-md p-6 border-2 border-gray-200 cursor-pointer"
                     onclick="toggleTask('send_admin_report')">
                    <input type="checkbox" name="tasks[]" value="send_admin_report" id="send_admin_report" class="hidden">
                    <div class="text-center">
                        <div class="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-chart-line text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">إرسال إشعارات للإدمن</h3>
                        <p class="text-gray-600 text-sm mb-4">إرسال تقرير شامل عن حالة النظام</p>
                        <div class="bg-green-50 rounded-lg p-3">
                            <span class="text-green-700 font-semibold">تقرير</span>
                            <span class="text-green-600 text-sm">يومي</span>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="task-status text-sm text-gray-500">انقر للاختيار</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-center space-x-4 space-x-reverse">
                    <button type="button" onclick="runSelectedTasks()"
                            class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-300 transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="runButton">
                        <i class="fas fa-play ml-2"></i>
                        <span class="loading fas fa-spinner fa-spin"></span>
                        <span class="button-text">تشغيل المهام المحددة</span>
                    </button>
                    <a href="dashboard.php"
                       class="px-8 py-3 bg-gray-600 text-white font-semibold rounded-lg shadow-lg hover:bg-gray-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-gray-300 transform hover:scale-105 transition-all duration-200">
                        <i class="fas fa-arrow-right ml-2"></i>
                        العودة للوحة التحكم
                    </a>
                </div>
                <p class="text-center text-gray-500 text-sm mt-4">
                    <i class="fas fa-info-circle ml-1"></i>
                    اختر مهمة واحدة على الأقل لتشغيلها
                </p>
            </div>
        </form>

        <!-- Results Section -->
        <div id="resultsSection" class="hidden mt-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-check-circle text-green-600 ml-2"></i>
                    نتائج تنفيذ المهام
                </h3>
                <div id="resultsContent" class="space-y-4">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTasks = new Set();

        function toggleTask(taskId) {
            const checkbox = document.getElementById(taskId);
            const card = checkbox.closest('.task-card');
            const statusSpan = card.querySelector('.task-status');

            if (selectedTasks.has(taskId)) {
                selectedTasks.delete(taskId);
                checkbox.checked = false;
                card.classList.remove('selected');
                statusSpan.textContent = 'انقر للاختيار';
                statusSpan.className = 'task-status text-sm text-gray-500';
            } else {
                selectedTasks.add(taskId);
                checkbox.checked = true;
                card.classList.add('selected');
                statusSpan.textContent = '✓ محدد';
                statusSpan.className = 'task-status text-sm text-blue-600 font-semibold';
            }

            updateRunButton();
        }

        function updateRunButton() {
            const runButton = document.getElementById('runButton');
            const buttonText = runButton.querySelector('.button-text');

            if (selectedTasks.size === 0) {
                runButton.disabled = true;
                buttonText.textContent = 'اختر مهمة واحدة على الأقل';
            } else {
                runButton.disabled = false;
                buttonText.textContent = `تشغيل ${selectedTasks.size} مهمة محددة`;
            }
        }

        function runSelectedTasks() {
            if (selectedTasks.size === 0) {
                alert('يرجى اختيار مهمة واحدة على الأقل');
                return;
            }

            const runButton = document.getElementById('runButton');
            const loading = runButton.querySelector('.loading');
            const buttonText = runButton.querySelector('.button-text');

            // تعطيل الزر وإظهار التحميل
            runButton.disabled = true;
            loading.classList.add('active');
            buttonText.textContent = 'جاري التنفيذ...';

            // إنشاء FormData
            const formData = new FormData();
            formData.append('action', 'run_tasks');
            selectedTasks.forEach(task => {
                formData.append('tasks[]', task);
            });

            // إرسال الطلب
            fetch('scheduled_tasks_interface.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayResults(data.results);
                    showSuccessMessage('تم تنفيذ المهام بنجاح!');
                } else {
                    showErrorMessage('حدث خطأ في تنفيذ المهام');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('حدث خطأ في الاتصال');
            })
            .finally(() => {
                // إعادة تفعيل الزر
                runButton.disabled = false;
                loading.classList.remove('active');
                updateRunButton();
            });
        }

        function displayResults(results) {
            const resultsSection = document.getElementById('resultsSection');
            const resultsContent = document.getElementById('resultsContent');

            resultsContent.innerHTML = '';

            Object.entries(results).forEach(([taskKey, result]) => {
                const taskNames = {
                    'delete_expired_trips': 'حذف الرحلات المنتهية',
                    'cleanup_notifications': 'تنظيف الإشعارات القديمة',
                    'send_admin_report': 'إرسال إشعارات للإدمن'
                };

                const taskName = taskNames[taskKey] || taskKey;
                const statusClass = result.status === 'success' ? 'text-green-600' :
                                  result.status === 'error' ? 'text-red-600' : 'text-blue-600';
                const iconClass = result.status === 'success' ? 'fa-check-circle text-green-600' :
                                result.status === 'error' ? 'fa-exclamation-triangle text-red-600' : 'fa-info-circle text-blue-600';

                const resultHtml = `
                    <div class="border rounded-lg p-4 ${result.status === 'success' ? 'bg-green-50 border-green-200' :
                                                      result.status === 'error' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200'}">
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <i class="fas ${iconClass} text-xl mt-1"></i>
                            <div class="flex-grow">
                                <h4 class="font-semibold ${statusClass}">${taskName}</h4>
                                <p class="text-gray-700 mt-1">${result.message}</p>
                                ${result.count !== undefined ? `<p class="text-sm text-gray-600 mt-1">العدد: ${result.count}</p>` : ''}
                                ${result.bookings_deleted !== undefined ? `<p class="text-sm text-gray-600">الحجوزات المحذوفة: ${result.bookings_deleted}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;

                resultsContent.innerHTML += resultHtml;
            });

            resultsSection.classList.remove('hidden');
            resultsSection.scrollIntoView({ behavior: 'smooth' });
        }

        function showSuccessMessage(message) {
            showMessage(message, 'success');
        }

        function showErrorMessage(message) {
            showMessage(message, 'error');
        }

        function showMessage(message, type) {
            const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

            const alertHtml = `
                <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 ${alertClass} border px-6 py-4 rounded-lg shadow-lg alert-message">
                    <div class="flex items-center">
                        <i class="${icon} ml-2"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', alertHtml);

            // إزالة الرسالة بعد 3 ثوان
            setTimeout(() => {
                const alert = document.querySelector('.alert-message');
                if (alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 3000);
        }

        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            updateRunButton();
        });
    </script>
</body>
</html>

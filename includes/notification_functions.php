<?php
require_once __DIR__ . '/../config/database.php';

/**
 * دوال نظام الإشعارات
 */

// إضافة إشعار جديد
function add_notification($user_id, $title, $message, $link = null, $type = 'info') {
    $db = new Database();

    try {
        $db->query("INSERT INTO notifications (user_id, title, message, link, type) VALUES (:user_id, :title, :message, :link, :type)");
        $db->bind(':user_id', $user_id);
        $db->bind(':title', $title);
        $db->bind(':message', $message);
        $db->bind(':link', $link);
        $db->bind(':type', $type);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error adding notification: " . $e->getMessage());
        return false;
    }
}

// إضافة إشعار لعدة مستخدمين
function add_notification_to_multiple_users($user_ids, $title, $message, $link = null, $type = 'info') {
    $db = new Database();

    try {
        $db->query("INSERT INTO notifications (user_id, title, message, link, type) VALUES (:user_id, :title, :message, :link, :type)");

        foreach ($user_ids as $user_id) {
            $db->bind(':user_id', $user_id);
            $db->bind(':title', $title);
            $db->bind(':message', $message);
            $db->bind(':link', $link);
            $db->bind(':type', $type);
            $db->execute();
        }

        return true;
    } catch (Exception $e) {
        error_log("Error adding notifications to multiple users: " . $e->getMessage());
        return false;
    }
}

// الحصول على إشعارات المستخدم
function get_user_notifications($user_id, $unread_only = false, $limit = 20) {
    $db = new Database();

    try {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $db->query($sql);
        $db->bind(':user_id', $user_id);
        if ($limit) {
            $db->bind(':limit', $limit);
        }

        return $db->resultSet();
    } catch (Exception $e) {
        error_log("Error getting user notifications: " . $e->getMessage());
        return [];
    }
}

// الحصول على عدد الإشعارات غير المقروءة
function get_unread_notifications_count($user_id) {
    $db = new Database();

    try {
        $db->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0");
        $db->bind(':user_id', $user_id);
        $result = $db->single();

        return $result['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error getting unread notifications count: " . $e->getMessage());
        return 0;
    }
}

// تعليم إشعار كمقروء
function mark_notification_read($notification_id, $user_id) {
    $db = new Database();

    try {
        $db->query("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $db->bind(':id', $notification_id);
        $db->bind(':user_id', $user_id);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

// تعليم جميع الإشعارات كمقروءة
function mark_all_notifications_read($user_id) {
    $db = new Database();

    try {
        $db->query("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

// حذف إشعار
function delete_notification($notification_id, $user_id) {
    $db = new Database();

    try {
        $db->query("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
        $db->bind(':id', $notification_id);
        $db->bind(':user_id', $user_id);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error deleting notification: " . $e->getMessage());
        return false;
    }
}

// إشعارات خاصة بالحجوزات
function notify_ticket_booked($user_id, $event_title, $ticket_id, $amount) {
    $title = "تم تأكيد حجز التذكرة";
    $message = "تم تأكيد حجز تذكرتك لحدث: {$event_title}. المبلغ المدفوع: {$amount} ₪";
    $link = "my-tickets.php";
    return add_notification($user_id, $title, $message, $link, 'booking');
}

function notify_payment_failed($user_id, $event_title, $reason = '') {
    $title = "فشل في عملية الدفع";
    $message = "فشل الدفع لحجز تذكرتك لحدث: {$event_title}. " . ($reason ? "السبب: {$reason}" : "يرجى المحاولة مرة أخرى.");
    $link = "my-tickets.php";
    return add_notification($user_id, $title, $message, $link, 'danger');
}

function notify_booking_cancelled($user_id, $event_title, $refund_amount = null) {
    $title = "تم إلغاء الحجز";
    $message = "تم إلغاء حجزك لحدث: {$event_title}";
    if ($refund_amount) {
        $message .= ". سيتم استرداد مبلغ {$refund_amount} ₪ خلال 3-5 أيام عمل.";
    }
    $link = "my-tickets.php";
    return add_notification($user_id, $title, $message, $link, 'warning');
}

// إشعارات خاصة بالمواصلات
function notify_transport_booked($user_id, $event_title, $booking_code, $departure_time) {
    $title = "تم تأكيد حجز المواصلات";
    $message = "تم تأكيد حجز المواصلات لحدث: {$event_title}. رقم الحجز: {$booking_code}. موعد الانطلاق: " . date('H:i', strtotime($departure_time));
    $link = "transport/bookings.php";
    return add_notification($user_id, $title, $message, $link, 'transport');
}

function notify_transport_cancelled($user_id, $event_title, $booking_code) {
    $title = "تم إلغاء حجز المواصلات";
    $message = "تم إلغاء حجز المواصلات لحدث: {$event_title}. رقم الحجز: {$booking_code}";
    $link = "transport/bookings.php";
    return add_notification($user_id, $title, $message, $link, 'warning');
}

function notify_trip_time_changed($user_ids, $event_title, $old_time, $new_time) {
    $title = "تغيير موعد الرحلة";
    $message = "تم تغيير موعد رحلة حدث: {$event_title} من {$old_time} إلى {$new_time}";
    $link = "transport/bookings.php";
    return add_notification_to_multiple_users($user_ids, $title, $message, $link, 'warning');
}

// إشعارات خاصة بالأحداث
function notify_event_time_changed($user_ids, $event_title, $old_time, $new_time) {
    $title = "تغيير موعد الحدث";
    $message = "تم تغيير موعد حدث: {$event_title} من " . date('Y-m-d H:i', strtotime($old_time)) . " إلى " . date('Y-m-d H:i', strtotime($new_time));
    $link = "my-tickets.php";
    return add_notification_to_multiple_users($user_ids, $title, $message, $link, 'event');
}

function notify_event_cancelled($user_ids, $event_title) {
    $title = "تم إلغاء الحدث";
    $message = "نأسف لإبلاغكم بأنه تم إلغاء حدث: {$event_title}. سيتم استرداد قيمة التذاكر خلال 3-5 أيام عمل.";
    $link = "my-tickets.php";
    return add_notification_to_multiple_users($user_ids, $title, $message, $link, 'danger');
}

// إشعارات التذكير
function notify_event_reminder($user_id, $event_title, $event_time, $hours_before = 24) {
    $title = "تذكير بموعد الحدث";
    $message = "تذكير: موعد حدثك '{$event_title}' خلال {$hours_before} ساعة في " . date('Y-m-d H:i', strtotime($event_time));
    $link = "my-tickets.php";
    return add_notification($user_id, $title, $message, $link, 'reminder');
}

function notify_transport_reminder($user_id, $event_title, $departure_time, $starting_point) {
    $title = "تذكير برحلة المواصلات";
    $message = "تذكير: موعد انطلاق رحلتك لحدث '{$event_title}' خلال ساعة واحدة من {$starting_point} في " . date('H:i', strtotime($departure_time));
    $link = "transport/bookings.php";
    return add_notification($user_id, $title, $message, $link, 'reminder');
}

// إشعارات الحساب
function notify_password_reset($user_id) {
    $title = "تم تحديث كلمة المرور";
    $message = "تم تحديث كلمة المرور الخاصة بحسابك بنجاح.";
    $link = "profile.php";
    return add_notification($user_id, $title, $message, $link, 'success');
}

function notify_profile_updated($user_id) {
    $title = "تم تحديث بيانات الحساب";
    $message = "تم تحديث بيانات حسابك بنجاح.";
    $link = "profile.php";
    return add_notification($user_id, $title, $message, $link, 'success');
}

function notify_account_created($user_id, $username) {
    $title = "مرحباً بك!";
    $message = "مرحباً {$username}! تم إنشاء حسابك بنجاح. يمكنك الآن تصفح الأحداث وحجز التذاكر.";
    $link = "events.php";
    return add_notification($user_id, $title, $message, $link, 'success');
}

// إشعارات إدارية
function notify_admin_announcement($title, $message, $link = null) {
    $db = new Database();

    try {
        // الحصول على جميع المستخدمين النشطين
        $db->query("SELECT id FROM users WHERE status = 'active'");
        $users = $db->resultSet();

        $user_ids = array_column($users, 'id');
        return add_notification_to_multiple_users($user_ids, $title, $message, $link, 'admin');
    } catch (Exception $e) {
        error_log("Error sending admin announcement: " . $e->getMessage());
        return false;
    }
}

// التحقق من إمكانية حجز المواصلات
function can_book_transport($user_id, $event_id) {
    $db = new Database();

    try {
        // التحقق من وجود تذكرة صالحة للمستخدم لهذا الحدث
        $db->query("SELECT COUNT(*) as count FROM tickets WHERE user_id = :user_id AND event_id = :event_id AND status = 'confirmed'");
        $db->bind(':user_id', $user_id);
        $db->bind(':event_id', $event_id);
        $result = $db->single();

        if ($result['count'] > 0) {
            return ['can_book' => true, 'reason' => ''];
        } else {
            return ['can_book' => false, 'reason' => 'يجب أن تكون لديك تذكرة صالحة للحدث لحجز المواصلات'];
        }
    } catch (Exception $e) {
        error_log("Error checking transport booking eligibility: " . $e->getMessage());
        return ['can_book' => false, 'reason' => 'حدث خطأ في النظام'];
    }
}

// معالجة حجز المواصلات
function process_transport_booking($user_id, $trip_id, $event_id, $passenger_data, $payment_method) {
    $db = new Database();

    try {
        // التحقق من إمكانية الحجز
        $can_book = can_book_transport($user_id, $event_id);
        if (!$can_book['can_book']) {
            return ['success' => false, 'message' => $can_book['reason']];
        }

        // الحصول على بيانات الرحلة
        require_once __DIR__ . '/transport_functions.php';
        $trip = get_trip_by_id($trip_id);
        if (!$trip) {
            return ['success' => false, 'message' => 'الرحلة غير موجودة'];
        }

        // التحقق من توفر المقاعد
        if ($trip['available_seats'] < $passenger_data['passengers_count']) {
            return ['success' => false, 'message' => 'عذراً، المقاعد المتاحة فقط ' . $trip['available_seats'] . ' مقعد'];
        }

        // إنشاء الحجز
        $booking_data = [
            'user_id' => $user_id,
            'trip_id' => $trip_id,
            'event_id' => $event_id,
            'passenger_name' => $passenger_data['name'],
            'passenger_phone' => $passenger_data['phone'],
            'passenger_email' => $passenger_data['email'],
            'passengers_count' => $passenger_data['passengers_count'],
            'special_notes' => $passenger_data['special_notes'],
            'payment_method' => $payment_method,
            'payment_details' => []
        ];

        $result = create_transport_booking($booking_data);

        if ($result['success']) {
            // إرسال إشعار نجاح الحجز
            notify_transport_booked($user_id, $trip['event_title'], $result['booking_code'], $trip['departure_time']);
        }

        return $result;
    } catch (Exception $e) {
        error_log("Error processing transport booking: " . $e->getMessage());
        return ['success' => false, 'message' => 'حدث خطأ في النظام'];
    }
}

// دوال إعدادات الإشعارات
function get_user_notification_settings($user_id) {
    $db = new Database();

    try {
        $db->query("SELECT * FROM notification_settings WHERE user_id = :user_id");
        $db->bind(':user_id', $user_id);
        $result = $db->single();

        return $result;
    } catch (Exception $e) {
        error_log("Error getting user notification settings: " . $e->getMessage());
        return null;
    }
}

function create_default_notification_settings($user_id) {
    $db = new Database();

    try {
        $db->query("INSERT INTO notification_settings (user_id, email_enabled, mobile_enabled, upcoming_tickets, event_changes, transport_updates, payment_notifications, admin_announcements) VALUES (:user_id, 1, 0, 1, 1, 1, 1, 1)");
        $db->bind(':user_id', $user_id);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error creating default notification settings: " . $e->getMessage());
        return false;
    }
}

function update_user_notification_settings($user_id, $settings) {
    $db = new Database();

    try {
        $db->query("UPDATE notification_settings SET
            email_enabled = :email_enabled,
            mobile_enabled = :mobile_enabled,
            upcoming_tickets = :upcoming_tickets,
            event_changes = :event_changes,
            transport_updates = :transport_updates,
            payment_notifications = :payment_notifications,
            admin_announcements = :admin_announcements,
            updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id");

        $db->bind(':user_id', $user_id);
        $db->bind(':email_enabled', $settings['email_enabled']);
        $db->bind(':mobile_enabled', $settings['mobile_enabled']);
        $db->bind(':upcoming_tickets', $settings['upcoming_tickets']);
        $db->bind(':event_changes', $settings['event_changes']);
        $db->bind(':transport_updates', $settings['transport_updates']);
        $db->bind(':payment_notifications', $settings['payment_notifications']);
        $db->bind(':admin_announcements', $settings['admin_announcements']);

        return $db->execute();
    } catch (Exception $e) {
        error_log("Error updating user notification settings: " . $e->getMessage());
        return false;
    }
}

// دالة للتحقق من وجود دوال مكررة
function check_duplicate_functions() {
    $defined_functions = get_defined_functions()['user'];
    $duplicates = [];
    $function_files = [];

    // قائمة الملفات المحتملة للفحص
    $files_to_check = [
        'includes/notification_functions.php',
        'includes/icons.php',
        'includes/transport_functions.php',
        'notifications.php'
    ];

    foreach ($files_to_check as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches);

            foreach ($matches[1] as $function_name) {
                if (isset($function_files[$function_name])) {
                    $duplicates[] = [
                        'function' => $function_name,
                        'files' => [$function_files[$function_name], $file]
                    ];
                } else {
                    $function_files[$function_name] = $file;
                }
            }
        }
    }

    return $duplicates;
}


?>

<?php
require_once __DIR__ . '/../config/database.php';

/**
 * دوال نظام المواصلات
 */

// دوال نقاط الانطلاق
function get_starting_points($region = null) {
    $db = new Database();
    $query = "SELECT * FROM transport_starting_points WHERE is_active = 1";

    if ($region) {
        $query .= " AND region = :region";
    }

    $query .= " ORDER BY name ASC";

    $db->query($query);

    if ($region) {
        $db->bind(':region', $region);
    }

    return $db->resultSet();
}

function get_starting_point_by_id($id) {
    $db = new Database();
    $db->query("SELECT * FROM transport_starting_points WHERE id = :id AND is_active = 1");
    $db->bind(':id', $id);
    return $db->single();
}

// دوال الرحلات
function get_trips_by_event_and_starting_point($event_id, $starting_point_id) {
    $db = new Database();
    $db->query("
        SELECT
            t.*,
            sp.name as starting_point_name,
            tt.name as transport_type_name,
            tt.icon as transport_type_icon,
            v.plate_number,
            v.model,
            v.year,
            v.color,
            v.photo as vehicle_photo,
            d.name as driver_name,
            d.phone as driver_phone,
            d.rating as driver_rating,
            d.photo as driver_photo,
            d.experience_years
        FROM transport_trips t
        JOIN transport_starting_points sp ON t.starting_point_id = sp.id
        JOIN transport_types tt ON t.transport_type_id = tt.id
        LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
        LEFT JOIN transport_drivers d ON v.driver_id = d.id
        WHERE t.event_id = :event_id
        AND t.starting_point_id = :starting_point_id
        AND t.is_active = 1
        AND t.available_seats > 0
        ORDER BY t.departure_time ASC
    ");

    $db->bind(':event_id', $event_id);
    $db->bind(':starting_point_id', $starting_point_id);

    return $db->resultSet();
}

function get_trip_by_id($trip_id) {
    $db = new Database();
    $db->query("
        SELECT
            t.*,
            sp.name as starting_point_name,
            tt.name as transport_type_name,
            tt.icon as transport_type_icon,
            v.plate_number,
            v.model,
            v.year,
            v.color,
            v.photo as vehicle_photo,
            d.name as driver_name,
            d.phone as driver_phone,
            d.rating as driver_rating,
            d.photo as driver_photo,
            d.experience_years,
            e.title as event_title,
            e.location as event_location,
            e.date_time as event_date
        FROM transport_trips t
        JOIN transport_starting_points sp ON t.starting_point_id = sp.id
        JOIN transport_types tt ON t.transport_type_id = tt.id
        JOIN events e ON t.event_id = e.id
        LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
        LEFT JOIN transport_drivers d ON v.driver_id = d.id
        WHERE t.id = :trip_id AND t.is_active = 1
    ");

    $db->bind(':trip_id', $trip_id);
    return $db->single();
}

// دوال الحجز
function generate_booking_code($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = 'TR';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function create_transport_booking($data) {
    $db = new Database();

    try {
        // التحقق من توفر المقاعد
        $trip = get_trip_by_id($data['trip_id']);
        if (!$trip || $trip['available_seats'] < $data['passengers_count']) {
            return ['success' => false, 'message' => 'عذراً، لا توجد مقاعد كافية متاحة'];
        }

        // إنشاء رمز الحجز
        $booking_code = generate_booking_code();

        // التحقق من عدم تكرار رمز الحجز
        $db->query("SELECT id FROM transport_bookings WHERE booking_code = :code");
        $db->bind(':code', $booking_code);
        while ($db->single()) {
            $booking_code = generate_booking_code();
            $db->query("SELECT id FROM transport_bookings WHERE booking_code = :code");
            $db->bind(':code', $booking_code);
        }

        // حساب المبلغ الإجمالي
        $total_amount = $trip['price'] * $data['passengers_count'];

        // إدراج الحجز
        $db->query("
            INSERT INTO transport_bookings
            (user_id, trip_id, event_id, passenger_name, passenger_phone, passenger_email,
             passengers_count, total_amount, special_notes, payment_method, payment_details, booking_code)
            VALUES
            (:user_id, :trip_id, :event_id, :passenger_name, :passenger_phone, :passenger_email,
             :passengers_count, :total_amount, :special_notes, :payment_method, :payment_details, :booking_code)
        ");

        $db->bind(':user_id', $data['user_id']);
        $db->bind(':trip_id', $data['trip_id']);
        $db->bind(':event_id', $data['event_id']);
        $db->bind(':passenger_name', $data['passenger_name']);
        $db->bind(':passenger_phone', $data['passenger_phone']);
        $db->bind(':passenger_email', $data['passenger_email']);
        $db->bind(':passengers_count', $data['passengers_count']);
        $db->bind(':total_amount', $total_amount);
        $db->bind(':special_notes', $data['special_notes']);
        $db->bind(':payment_method', $data['payment_method']);
        $db->bind(':payment_details', json_encode($data['payment_details']));
        $db->bind(':booking_code', $booking_code);

        if ($db->execute()) {
            $booking_id = $db->lastInsertId();

            // تحديث المقاعد المتاحة
            $db->query("UPDATE transport_trips SET available_seats = available_seats - :count WHERE id = :trip_id");
            $db->bind(':count', $data['passengers_count']);
            $db->bind(':trip_id', $data['trip_id']);
            $db->execute();

            return [
                'success' => true,
                'booking_id' => $booking_id,
                'booking_code' => $booking_code,
                'total_amount' => $total_amount
            ];
        }

        return ['success' => false, 'message' => 'حدث خطأ أثناء إنشاء الحجز'];

    } catch (Exception $e) {
        error_log("Transport booking error: " . $e->getMessage());
        return ['success' => false, 'message' => 'حدث خطأ في النظام'];
    }
}

function get_user_transport_bookings($user_id) {
    $db = new Database();
    $db->query("
        SELECT
            tb.*,
            t.departure_time,
            t.arrival_time,
            sp.name as starting_point_name,
            tt.name as transport_type_name,
            e.title as event_title,
            e.location as event_location,
            e.date_time as event_date,
            v.plate_number,
            v.model,
            d.name as driver_name,
            d.phone as driver_phone
        FROM transport_bookings tb
        JOIN transport_trips t ON tb.trip_id = t.id
        JOIN transport_starting_points sp ON t.starting_point_id = sp.id
        JOIN transport_types tt ON t.transport_type_id = tt.id
        JOIN events e ON tb.event_id = e.id
        LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
        LEFT JOIN transport_drivers d ON v.driver_id = d.id
        WHERE tb.user_id = :user_id
        ORDER BY tb.created_at DESC
    ");

    $db->bind(':user_id', $user_id);
    return $db->resultSet();
}

function get_transport_booking_by_code($booking_code) {
    $db = new Database();
    $db->query("
        SELECT
            tb.*,
            t.departure_time,
            t.arrival_time,
            sp.name as starting_point_name,
            tt.name as transport_type_name,
            e.title as event_title,
            e.location as event_location,
            e.date_time as event_date,
            v.plate_number,
            v.model,
            d.name as driver_name,
            d.phone as driver_phone
        FROM transport_bookings tb
        JOIN transport_trips t ON tb.trip_id = t.id
        JOIN transport_starting_points sp ON t.starting_point_id = sp.id
        JOIN transport_types tt ON t.transport_type_id = tt.id
        JOIN events e ON tb.event_id = e.id
        LEFT JOIN transport_vehicles v ON t.vehicle_id = v.id
        LEFT JOIN transport_drivers d ON v.driver_id = d.id
        WHERE tb.booking_code = :booking_code
    ");

    $db->bind(':booking_code', $booking_code);
    return $db->single();
}

// دوال مساعدة خاصة بالمواصلات

function get_feature_icon($feature) {
    $icons = [
        'wifi' => 'fa-wifi',
        'ac' => 'fa-snowflake',
        'comfortable_seats' => 'fa-couch',
        'entertainment' => 'fa-tv',
        'luggage_space' => 'fa-suitcase',
        'leather_seats' => 'fa-chair',
        'gps' => 'fa-map-marked-alt',
        'basic_comfort' => 'fa-check-circle'
    ];

    return $icons[$feature] ?? 'fa-check';
}

function get_feature_color($feature) {
    $colors = [
        'wifi' => 'text-blue-500',
        'ac' => 'text-cyan-500',
        'comfortable_seats' => 'text-green-500',
        'entertainment' => 'text-purple-500',
        'luggage_space' => 'text-yellow-500',
        'leather_seats' => 'text-red-500',
        'gps' => 'text-indigo-500',
        'basic_comfort' => 'text-gray-500'
    ];

    return $colors[$feature] ?? 'text-gray-500';
}

function get_region_name($region) {
    $regions = [
        'north' => 'شمال غزة',
        'center' => 'وسط غزة',
        'south' => 'جنوب غزة'
    ];

    return $regions[$region] ?? $region;
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'سنة',
        'm' => 'شهر',
        'w' => 'أسبوع',
        'd' => 'يوم',
        'h' => 'ساعة',
        'i' => 'دقيقة',
        's' => 'ثانية',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' مضت' : 'الآن';
}

function get_booked_seats_count($trip_id) {
    $db = new Database();
    $db->query("SELECT SUM(passengers_count) as total_booked FROM transport_bookings WHERE trip_id = :trip_id AND payment_status != 'cancelled'");
    $db->bind(':trip_id', $trip_id);
    $result = $db->single();
    return $result['total_booked'] ?? 0;
}

function format_time($time) {
    if (empty($time)) {
        return '';
    }

    // إذا كان الوقت في صيغة H:i:s
    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
        return date('H:i', strtotime($time));
    }

    // إذا كان الوقت في صيغة H:i
    if (preg_match('/^\d{2}:\d{2}$/', $time)) {
        return $time;
    }

    // محاولة تحويل الوقت
    $timestamp = strtotime($time);
    if ($timestamp !== false) {
        return date('H:i', $timestamp);
    }

    return $time;
}

?>

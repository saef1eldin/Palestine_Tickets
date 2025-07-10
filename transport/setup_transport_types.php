<?php
require_once '../includes/init.php';

$db = new Database();

try {
    // إنشاء جدول أنواع وسائل النقل
    $db->query("
        CREATE TABLE IF NOT EXISTS transport_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50) DEFAULT 'bus',
            capacity_min INT DEFAULT 1,
            capacity_max INT DEFAULT 50,
            price_per_km DECIMAL(10,2) DEFAULT 0.00,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    $db->execute();
    echo "تم إنشاء جدول transport_types بنجاح<br>";

    // التحقق من وجود بيانات
    $db->query("SELECT COUNT(*) as count FROM transport_types");
    $count = $db->single()['count'];

    if ($count == 0) {
        // إدراج أنواع وسائل النقل الأساسية
        $transport_types = [
            ['باص فاخر', 'حافلة مكيفة مع مقاعد مريحة وخدمات إضافية', 'bus', 20, 50, 2.50],
            ['فان', 'مركبة متوسطة الحجم مناسبة للمجموعات الصغيرة', 'shuttle-van', 8, 15, 3.00],
            ['سيارة خاصة', 'سيارة خاصة مع سائق للراحة القصوى', 'car', 1, 4, 5.00],
            ['باص عادي', 'حافلة عادية بخدمات أساسية', 'bus-alt', 15, 40, 2.00],
            ['ميكروباص', 'مركبة صغيرة للمجموعات الصغيرة', 'van-shuttle', 10, 20, 2.75]
        ];

        foreach ($transport_types as $type) {
            $db->query("
                INSERT INTO transport_types (name, description, icon, capacity_min, capacity_max, price_per_km)
                VALUES (:name, :description, :icon, :capacity_min, :capacity_max, :price_per_km)
            ");
            $db->bind(':name', $type[0]);
            $db->bind(':description', $type[1]);
            $db->bind(':icon', $type[2]);
            $db->bind(':capacity_min', $type[3]);
            $db->bind(':capacity_max', $type[4]);
            $db->bind(':price_per_km', $type[5]);
            $db->execute();
        }
        echo "تم إدراج أنواع المواصلات بنجاح<br>";
    } else {
        echo "أنواع المواصلات موجودة بالفعل ($count نوع)<br>";
    }

    // التحقق من وجود جدول transport_drivers وإضافة حقل address إذا لم يكن موجود
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'address'");
    $address_column = $db->single();

    if (!$address_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN address TEXT AFTER license_number");
        $db->execute();
        echo "تم إضافة حقل العنوان لجدول السائقين<br>";
    }

    // التحقق من وجود حقل status في جدول transport_drivers
    $db->query("SHOW COLUMNS FROM transport_drivers LIKE 'status'");
    $status_column = $db->single();

    if (!$status_column) {
        $db->query("ALTER TABLE transport_drivers ADD COLUMN status ENUM('available', 'busy', 'offline') DEFAULT 'available' AFTER address");
        $db->execute();
        echo "تم إضافة حقل الحالة لجدول السائقين<br>";
    }

    // تحديث جدول transport_trips لإضافة حقل total_seats إذا لم يكن موجود
    $db->query("SHOW COLUMNS FROM transport_trips LIKE 'total_seats'");
    $total_seats_column = $db->single();

    if (!$total_seats_column) {
        $db->query("ALTER TABLE transport_trips ADD COLUMN total_seats INT DEFAULT 20 AFTER available_seats");
        $db->execute();
        echo "تم إضافة حقل إجمالي المقاعد لجدول الرحلات<br>";
    }

    // تحديث نوع البيانات لحقل departure_time ليكون DATETIME بدلاً من TIME
    $db->query("SHOW COLUMNS FROM transport_trips WHERE Field = 'departure_time'");
    $departure_time_column = $db->single();

    if ($departure_time_column && strpos($departure_time_column['Type'], 'time') !== false && strpos($departure_time_column['Type'], 'datetime') === false) {
        $db->query("ALTER TABLE transport_trips MODIFY COLUMN departure_time DATETIME NOT NULL");
        $db->execute();
        echo "تم تحديث نوع البيانات لحقل وقت المغادرة<br>";
    }

    // التحقق من وجود جدول transport_bookings وإنشاؤه إذا لم يكن موجود
    $db->query("SHOW TABLES LIKE 'transport_bookings'");
    $bookings_table_exists = $db->single();

    if (!$bookings_table_exists) {
        $db->query("
            CREATE TABLE transport_bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                trip_id INT NOT NULL,
                event_id INT NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(20) NOT NULL,
                customer_email VARCHAR(255),
                seats_count INT NOT NULL DEFAULT 1,
                total_amount DECIMAL(10,2) NOT NULL,
                special_notes TEXT,
                payment_method ENUM('bank_transfer', 'cash_on_delivery', 'mobile_pay', 'credit_card') NOT NULL,
                payment_status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
                payment_details JSON,
                booking_code VARCHAR(20) UNIQUE NOT NULL,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (trip_id) REFERENCES transport_trips(id) ON DELETE CASCADE,
                FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
            )
        ");
        $db->execute();
        echo "تم إنشاء جدول transport_bookings بنجاح<br>";
    } else {
        // تحديث جدول transport_bookings لإضافة الحقول المطلوبة إذا لم تكن موجودة
        $required_columns = [
            'customer_name' => "ALTER TABLE transport_bookings ADD COLUMN customer_name VARCHAR(255) NOT NULL AFTER event_id",
            'customer_phone' => "ALTER TABLE transport_bookings ADD COLUMN customer_phone VARCHAR(20) NOT NULL AFTER customer_name",
            'seats_count' => "ALTER TABLE transport_bookings ADD COLUMN seats_count INT DEFAULT 1 AFTER customer_phone"
        ];

        foreach ($required_columns as $column => $sql) {
            $db->query("SHOW COLUMNS FROM transport_bookings LIKE '$column'");
            $column_exists = $db->single();

            if (!$column_exists) {
                try {
                    $db->query($sql);
                    $db->execute();
                    echo "تم إضافة حقل $column لجدول الحجوزات<br>";
                } catch (Exception $e) {
                    echo "تحذير: لم يتم إضافة حقل $column - " . $e->getMessage() . "<br>";
                }
            }
        }
    }

    echo "<br><strong>تم إعداد قاعدة البيانات بنجاح!</strong><br>";
    echo "<a href='trips.php'>العودة إلى صفحة الرحلات</a>";

} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage();
}
?>

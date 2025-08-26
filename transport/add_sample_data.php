<?php
// ملف لإضافة بيانات تجريبية للاختبار
require_once '../includes/init.php';

echo "<h2>إضافة بيانات تجريبية</h2>";

$db = new Database();

try {
    // إضافة نقاط انطلاق تجريبية
    echo "<h3>إضافة نقاط الانطلاق...</h3>";
    
    $departure_points = [
        ['name' => 'رام الله - المنارة', 'description' => 'نقطة انطلاق رئيسية في رام الله', 'region' => 'center'],
        ['name' => 'نابلس - المحطة المركزية', 'description' => 'محطة الحافلات الرئيسية في نابلس', 'region' => 'north'],
        ['name' => 'الخليل - باب الزاوية', 'description' => 'نقطة انطلاق مركزية في الخليل', 'region' => 'south'],
        ['name' => 'بيت لحم - شارع بول السادس', 'description' => 'نقطة انطلاق في بيت لحم', 'region' => 'center'],
        ['name' => 'جنين - المحطة', 'description' => 'محطة الحافلات في جنين', 'region' => 'north']
    ];

    foreach ($departure_points as $point) {
        $db->query("
            INSERT IGNORE INTO transport_starting_points (name, description, region, icon, is_active, created_at, updated_at)
            VALUES (:name, :description, :region, 'map-marker-alt', 1, NOW(), NOW())
        ");
        $db->bind(':name', $point['name']);
        $db->bind(':description', $point['description']);
        $db->bind(':region', $point['region']);
        $db->execute();
        echo "✅ تم إضافة: " . $point['name'] . "<br>";
    }

    // إضافة سائقين تجريبيين
    echo "<h3>إضافة السائقين...</h3>";
    
    $drivers = [
        ['name' => 'أحمد محمد علي', 'phone' => '0599123456', 'license' => 'PS123456789', 'address' => 'رام الله', 'experience' => 5],
        ['name' => 'محمد أحمد حسن', 'phone' => '0598765432', 'license' => 'PS987654321', 'address' => 'نابلس', 'experience' => 8],
        ['name' => 'علي حسن محمود', 'phone' => '0597111222', 'license' => 'PS111222333', 'address' => 'الخليل', 'experience' => 3],
        ['name' => 'حسن علي أحمد', 'phone' => '0596333444', 'license' => 'PS444555666', 'address' => 'بيت لحم', 'experience' => 6],
        ['name' => 'محمود حسن علي', 'phone' => '0595777888', 'license' => 'PS777888999', 'address' => 'جنين', 'experience' => 4]
    ];

    foreach ($drivers as $driver) {
        $db->query("
            INSERT IGNORE INTO transport_drivers (name, phone, license_number, address, status, experience_years, rating, is_active, created_at, updated_at)
            VALUES (:name, :phone, :license, :address, 'available', :experience, 5.0, 1, NOW(), NOW())
        ");
        $db->bind(':name', $driver['name']);
        $db->bind(':phone', $driver['phone']);
        $db->bind(':license', $driver['license']);
        $db->bind(':address', $driver['address']);
        $db->bind(':experience', $driver['experience']);
        $db->execute();
        echo "✅ تم إضافة السائق: " . $driver['name'] . "<br>";
    }

    // إضافة فعالية تجريبية إذا لم تكن موجودة
    echo "<h3>إضافة فعالية تجريبية...</h3>";
    
    $db->query("SELECT COUNT(*) as count FROM events WHERE is_active = 1");
    $event_count = $db->single()['count'];
    
    if ($event_count == 0) {
        $db->query("
            INSERT INTO events (title, description, date_time, location, is_active, created_at, updated_at)
            VALUES ('مؤتمر التكنولوجيا 2024', 'مؤتمر سنوي للتكنولوجيا والابتكار', '2024-12-15 09:00:00', 'رام الله - قصر الثقافة', 1, NOW(), NOW())
        ");
        $db->execute();
        echo "✅ تم إضافة فعالية تجريبية<br>";
    } else {
        echo "✅ توجد فعاليات في النظام بالفعل<br>";
    }

    // إضافة رحلات تجريبية
    echo "<h3>إضافة رحلات تجريبية...</h3>";
    
    // الحصول على معرفات نقاط الانطلاق والفعالية ونوع وسيلة النقل
    $db->query("SELECT id FROM transport_starting_points LIMIT 3");
    $starting_points = $db->resultSet();
    
    $db->query("SELECT id FROM events WHERE is_active = 1 LIMIT 1");
    $event = $db->single();
    
    $db->query("SELECT id FROM transport_types WHERE is_active = 1 LIMIT 1");
    $transport_type = $db->single();

    if (!empty($starting_points) && $event && $transport_type) {
        foreach ($starting_points as $index => $point) {
            $departure_time = date('Y-m-d H:i:s', strtotime('+' . ($index + 1) . ' days 08:00:00'));
            $price = 25 + ($index * 5); // أسعار متدرجة
            
            $db->query("
                INSERT IGNORE INTO transport_trips (event_id, starting_point_id, transport_type_id, departure_time, arrival_time, price, total_seats, available_seats, description, is_active, created_at, updated_at)
                VALUES (:event_id, :starting_point_id, :transport_type_id, :departure_time, '10:30:00', :price, 30, 30, 'رحلة مريحة وآمنة', 1, NOW(), NOW())
            ");
            $db->bind(':event_id', $event['id']);
            $db->bind(':starting_point_id', $point['id']);
            $db->bind(':transport_type_id', $transport_type['id']);
            $db->bind(':departure_time', $departure_time);
            $db->bind(':price', $price);
            $db->execute();
            echo "✅ تم إضافة رحلة من نقطة الانطلاق #" . $point['id'] . "<br>";
        }
    }

    echo "<br><strong style='color: green;'>🎉 تم إضافة جميع البيانات التجريبية بنجاح!</strong><br>";
    echo "<br><a href='dashboard.php' style='background: #7c3aed; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>انتقل إلى لوحة التحكم</a>";

} catch (Exception $e) {
    echo "<strong style='color: red;'>❌ خطأ: " . $e->getMessage() . "</strong>";
}
?>

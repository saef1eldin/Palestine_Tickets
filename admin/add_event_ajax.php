<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../config/database.php';

// Include functions
require_once '../includes/functions.php';
require_once '../includes/auth_functions.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();
$pdo = $db->getConnection();

// Require admin
requireAdmin();

// Set default response
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Process AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifyCSRFToken($csrf_token)) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    // Validate form data
    $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $date = isset($_POST['date']) ? sanitize($_POST['date']) : '';
    $time = isset($_POST['time']) ? sanitize($_POST['time']) : '';
    $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $originalPrice = isset($_POST['original_price']) && !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $type = isset($_POST['type']) ? sanitize($_POST['type']) : '';
    $availableTickets = isset($_POST['available_tickets']) ? (int)$_POST['available_tickets'] : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Get end date and time
    $endDate = isset($_POST['end_date']) ? sanitize($_POST['end_date']) : $date; // Use start date if end date is not provided
    $endTime = isset($_POST['end_time']) ? sanitize($_POST['end_time']) : $time; // Use start time if end time is not provided

    // Combine date and time for start and end
    $dateTime = $date . ' ' . $time;
    $endDateTime = $endDate . ' ' . $endTime;

    // Validate input
    $errors = [];

    if (empty($title)) {
        $errors[] = 'Event title is required';
    }

    if (empty($date) || empty($time)) {
        $errors[] = 'Event date and time are required';
    }

    if (empty($location)) {
        $errors[] = 'Event location is required';
    }

    if ($price <= 0) {
        $errors[] = 'Event price must be greater than 0';
    }

    if (empty($type)) {
        $errors[] = 'Event type (category) is required';
    }

    if ($availableTickets <= 0) {
        $errors[] = 'Available tickets must be greater than 0';
    }

    // Handle image upload
    $imageUrl = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/events/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        // Check if file is an image
        $imageInfo = getimagesize($_FILES['image']['tmp_name']);
        if ($imageInfo === false) {
            $errors[] = 'Uploaded file is not an image';
        } else {
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                $imageUrl = 'uploads/events/' . $fileName;
            } else {
                $errors[] = 'Failed to upload image';
            }
        }
    }

    // If there are errors, return them
    if (!empty($errors)) {
        $response['message'] = implode('<br>', $errors);
        echo json_encode($response);
        exit;
    }

    // Add the event
    try {
        // Get table structure for debugging
        $stmt = $pdo->query("SHOW COLUMNS FROM events");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = [];
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
        }

        // Prepare the query based on actual column names
        $stmt = $pdo->prepare("
            INSERT INTO events (
                title, description, date_time, end_time, location, price, original_price, category,
                available_tickets, featured, image
            ) VALUES (
                :title, :description, :date_time, :end_time, :location, :price, :original_price, :category,
                :available_tickets, :featured, :image
            )
        ");

        $result = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':date_time' => $dateTime,
            ':end_time' => $endDateTime,
            ':location' => $location,
            ':price' => $price,
            ':original_price' => $originalPrice,
            ':category' => $type,
            ':available_tickets' => $availableTickets,
            ':featured' => $isFeatured,
            ':image' => $imageUrl
        ]);

        if ($result) {
            // Get translated messages
            $successMessage = 'Event added successfully';
            if (isset($_SESSION['lang'])) {
                $lang_file = '../lang/' . $_SESSION['lang'] . '.php';
                if (file_exists($lang_file)) {
                    $lang = require $lang_file;
                    if (isset($lang['event_added'])) {
                        $successMessage = $lang['event_added'];
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => $successMessage,
                'event_id' => $pdo->lastInsertId()
            ];
        } else {
            $response['message'] = 'Failed to add event';
        }
    } catch (PDOException $e) {
        $error_message = "SQL Error: " . $e->getMessage();
        error_log($error_message);
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>

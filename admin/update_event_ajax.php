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
    // Get event ID
    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    // Validate CSRF token
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifyCSRFToken($csrf_token)) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    // Get event details to check if it exists
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $response['message'] = 'Event not found';
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
    // is_active field is not used as it doesn't exist in the events table

    // Combine date and time
    $dateTime = $date . ' ' . $time;

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
        $errors[] = 'Event type is required';
    }

    if ($availableTickets < 0) {
        $errors[] = 'Available tickets cannot be negative';
    }

    // Handle image upload
    $imageUrl = $event['image'];
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

    // Update the event
    try {
        $stmt = $pdo->prepare("
            UPDATE events SET
                title = :title,
                description = :description,
                date_time = :date_time,
                location = :location,
                price = :price,
                original_price = :original_price,
                category = :category,
                available_tickets = :available_tickets,
                featured = :is_featured,
                image = :image
            WHERE id = :id
        ");

        $result = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':date_time' => $dateTime,
            ':location' => $location,
            ':price' => $price,
            ':original_price' => $originalPrice,
            ':category' => $type,
            ':available_tickets' => $availableTickets,
            ':is_featured' => $isFeatured,
            ':image' => $imageUrl,
            ':id' => $eventId
        ]);

        if ($result) {
            // Get translated messages
            $successMessage = 'Event updated successfully';
            if (isset($_SESSION['lang'])) {
                $lang_file = '../lang/' . $_SESSION['lang'] . '.php';
                if (file_exists($lang_file)) {
                    $lang = require $lang_file;
                    if (isset($lang['event_updated'])) {
                        $successMessage = $lang['event_updated'];
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => $successMessage,
                'event_id' => $eventId
            ];
        } else {
            $response['message'] = 'Failed to update event';
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

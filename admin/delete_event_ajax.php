<?php
// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/init.php';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/admin_functions.php';
require_once $root_path . 'config/database.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();
$pdo = $db->getConnection();

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'غير مصرح لك بالوصول']);
    exit;
}

// التحقق من صلاحيات إدارة الأحداث
try {
    require_admin_permission('events');
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'ليس لديك صلاحية لإدارة الأحداث']);
    exit;
}

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
    
    // Check if event exists
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch();
    
    if (!$event) {
        $response['message'] = 'Event not found';
        echo json_encode($response);
        exit;
    }
    
    // Check if event has tickets
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE event_id = :id");
        $stmt->execute([':id' => $eventId]);
        $ticketCount = $stmt->fetchColumn();
        
        if ($ticketCount > 0) {
            $response['message'] = 'Cannot delete event with tickets';
            echo json_encode($response);
            exit;
        }
        
        // Delete event
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
        $result = $stmt->execute([':id' => $eventId]);
        
        if ($result) {
            // Get translated messages
            $successMessage = 'Event deleted successfully';
            if (isset($_SESSION['lang'])) {
                $lang_file = '../lang/' . $_SESSION['lang'] . '.php';
                if (file_exists($lang_file)) {
                    $lang = require $lang_file;
                    if (isset($lang['event_deleted'])) {
                        $successMessage = $lang['event_deleted'];
                    }
                }
            }
            
            $response = [
                'success' => true,
                'message' => $successMessage
            ];
        } else {
            $response['message'] = 'Failed to delete event';
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

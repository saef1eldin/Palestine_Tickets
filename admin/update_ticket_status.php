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
    // Get and sanitize data
    $ticketId = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    // Validate CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    // Validate data
    if ($ticketId <= 0 || empty($status)) {
        $response['message'] = 'Invalid ticket ID or status';
        echo json_encode($response);
        exit;
    }

    // Update order payment status for this ticket
    try {
        // First get the order_id for this ticket
        $stmt = $pdo->prepare("SELECT order_id FROM tickets WHERE id = :id");
        $stmt->execute([':id' => $ticketId]);
        $order_id = $stmt->fetchColumn();

        if (!$order_id) {
            throw new PDOException("Order not found for ticket ID: $ticketId");
        }

        // Update payment status in orders table
        $stmt = $pdo->prepare("
            UPDATE orders
            SET payment_status = :status
            WHERE id = :order_id
        ");

        $result = $stmt->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);

        if ($result) {
            // Get status text based on language
            $status_text = $status;
            if (isset($_SESSION['lang'])) {
                $lang_file = '../lang/' . $_SESSION['lang'] . '.php';
                if (file_exists($lang_file)) {
                    $lang = require $lang_file;
                    if ($status === 'completed' && isset($lang['completed'])) {
                        $status_text = $lang['completed'];
                    } elseif ($status === 'pending' && isset($lang['pending'])) {
                        $status_text = $lang['pending'];
                    } elseif ($status === 'cancelled' && isset($lang['cancelled'])) {
                        $status_text = $lang['cancelled'];
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => 'Ticket status updated successfully',
                'ticket_id' => $ticketId,
                'new_status' => $status,
                'status_text' => $status_text
            ];
        } else {
            $response['message'] = 'Failed to update ticket status';
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

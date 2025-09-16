<?php
// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/init.php';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/admin_functions.php';

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

// التحقق من صلاحيات إدارة المستخدمين
require_admin_permission('users');

// Set page title
$page_title = 'إدارة المستخدمين';

// Include admin header
include 'includes/admin_header.php';

// Process form submission for delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];

    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid form submission';
        header('Location: users.php');
        exit;
    }

    // Check if user is admin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $userRole = $stmt->fetchColumn();

    if ($userRole === 'admin') {
        $_SESSION['error_message'] = 'Cannot delete admin user';
        header('Location: users.php');
        exit;
    }

    // Check if user has tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = :id");
    $stmt->execute([':id' => $userId]);
    $ticketCount = $stmt->fetchColumn();

    if ($ticketCount > 0) {
        $_SESSION['error_message'] = 'Cannot delete user with tickets';
        header('Location: users.php');
        exit;
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $result = $stmt->execute([':id' => $userId]);

    if ($result) {
        $_SESSION['success_message'] = $lang['user_deleted'];
    } else {
        $_SESSION['error_message'] = 'Failed to delete user';
    }

    header('Location: users.php');
    exit;
}

// Get users
$stmt = $pdo->query("
    SELECT * FROM users
    ORDER BY created_at DESC
");
$users = $stmt->fetchAll();

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['manage_users']; ?></h1>
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
                    <a href="users.php" class="btn btn-outline-primary w-100 mb-2 active">
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
                    <a href="../index.php" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-home"></i><br>
                        <?php echo $lang['back_to_site']; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $lang['users_list']; ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo $lang['name']; ?></th>
                            <th><?php echo $lang['email']; ?></th>
                            <th><?php echo $lang['phone']; ?></th>
                            <th><?php echo $lang['role']; ?></th>
                            <th><?php echo $lang['created_at']; ?></th>
                            <th><?php echo $lang['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center"><?php echo $lang['no_users_found']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td>
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="badge bg-danger"><?php echo $lang['admin']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo $lang['user']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['role'] !== 'admin'): ?>
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $user['id']; ?>"><?php echo $lang['delete_user']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo $lang['delete_user_confirm']; ?></p>
                                                        <p><strong><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</strong></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                                                        <form action="users.php" method="post">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" name="delete_user" class="btn btn-danger"><?php echo $lang['delete']; ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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

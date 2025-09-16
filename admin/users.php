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

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Display 5 rows per page
$offset = ($page - 1) * $limit;

// Get total count of users
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    $total_users = 0;
}

$total_pages = ceil($total_users / $limit);

// Get users with pagination
try {
    $stmt = $pdo->prepare("
        SELECT *, IFNULL(created_at, NOW()) as created_at FROM users
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    $users = [];
}

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
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
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
                                        <?php 
                                        switch($user['role']) {
                                            case 'super_admin':
                                                echo '<span class="badge bg-danger">Super Admin</span>';
                                                break;
                                            case 'site_admin':
                                                echo '<span class="badge bg-danger">Site Admin</span>';
                                                break;
                                            case 'transport_admin':
                                                echo '<span class="badge bg-warning">Transport Admin</span>';
                                                break;
                                            case 'notifications_admin':
                                                echo '<span class="badge bg-info">Notifications Admin</span>';
                                                break;
                                            case 'user':
                                                echo '<span class="badge bg-secondary">User</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">User</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['role'] !== 'super_admin' && $user['role'] !== 'site_admin'): ?>
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
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Users pagination">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>"><?php echo $lang['previous'] ?? 'Previous'; ?></a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>"><?php echo $lang['next'] ?? 'Next'; ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

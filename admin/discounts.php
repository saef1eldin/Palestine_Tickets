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

// التحقق من صلاحيات إدارة الخصومات
require_admin_permission('discounts');

// Set page title
$page_title = 'Manage Discounts';

// Include admin header
include 'includes/admin_header.php';

// Process form submission for delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_discount'])) {
    $discountId = (int)$_POST['discount_id'];

    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error_message'] = 'Invalid form submission';
        header('Location: discounts.php');
        exit;
    }

    // Delete discount
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = :id");
        $result = $stmt->execute([':id' => $discountId]);
    } catch (PDOException $e) {
        error_log("SQL Error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Database error occurred';
        header('Location: discounts.php');
        exit;
    }

    if ($result) {
        $_SESSION['success_message'] = $lang['discount_deleted'];
    } else {
        $_SESSION['error_message'] = 'Failed to delete discount';
    }

    header('Location: discounts.php');
    exit;
}

// Get discounts
try {
    $stmt = $pdo->query("
        SELECT *,
        0 as usage_count,
        expiry_date as expiration_date
        FROM coupons
        ORDER BY created_at DESC
    ");
    $discounts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    $discounts = [];
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['manage_discounts']; ?></h1>
        <a href="add-discount.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $lang['add_discount']; ?>
        </a>
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
                    <a href="discounts.php" class="btn btn-outline-primary w-100 mb-2 active">
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
            <h5 class="mb-0"><?php echo $lang['discounts_list']; ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo $lang['discount_code']; ?></th>
                            <th><?php echo $lang['discount_type']; ?></th>
                            <th><?php echo $lang['discount_value']; ?></th>
                            <th><?php echo $lang['usage_limit']; ?></th>
                            <th><?php echo $lang['usage_count']; ?></th>
                            <th><?php echo $lang['expiration_date']; ?></th>
                            <!-- is_active column is not in the coupons table -->
                            <th><?php echo $lang['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($discounts)): ?>
                            <tr>
                                <td colspan="8" class="text-center"><?php echo $lang['no_discounts_found']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($discounts as $discount): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($discount['code']); ?></code></td>
                                    <td>
                                        <?php if ($discount['type'] === 'percentage'): ?>
                                            <?php echo $lang['discount_percentage']; ?>
                                        <?php else: ?>
                                            <?php echo $lang['discount_fixed']; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($discount['type'] === 'percentage'): ?>
                                            <?php echo $discount['value']; ?>%
                                        <?php else: ?>
                                            <?php echo formatPrice($discount['value']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $discount['usage_limit'] ? $discount['usage_limit'] : $lang['unlimited']; ?>
                                    </td>
                                    <td><?php echo $discount['usage_count']; ?></td>
                                    <td>
                                        <?php echo $discount['expiration_date'] ? formatDate($discount['expiration_date'], 'd/m/Y') : $lang['never']; ?>
                                    </td>
                                    <!-- is_active column is not in the coupons table -->
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-discount.php?id=<?php echo $discount['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $discount['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $discount['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $discount['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $discount['id']; ?>"><?php echo $lang['delete_discount']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo $lang['delete_discount_confirm']; ?></p>
                                                        <p><code><?php echo htmlspecialchars($discount['code']); ?></code></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                                                        <form action="discounts.php" method="post">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="discount_id" value="<?php echo $discount['id']; ?>">
                                                            <button type="submit" name="delete_discount" class="btn btn-danger"><?php echo $lang['delete']; ?></button>
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

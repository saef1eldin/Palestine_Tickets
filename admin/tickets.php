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

// التحقق من صلاحيات إدارة التذاكر
require_admin_permission('tickets');

// Set page title
$page_title = 'Manage Tickets';

// Include admin header
include 'includes/admin_header.php';

// AJAX processing moved to update_ticket_status.php

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Get total tickets count
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM tickets t
                              JOIN users u ON t.user_id = u.id
                              JOIN events e ON t.event_id = e.id
                              JOIN orders o ON t.order_id = o.id");
    $total_tickets = $count_stmt->fetchColumn();
    $total_pages = ceil($total_tickets / $limit);
} catch (PDOException $e) {
    $error_message = "SQL Error in count query: " . $e->getMessage();
    error_log($error_message);
    $total_tickets = 0;
    $total_pages = 0;
}

// Get tickets with pagination
try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.name as user_name, u.email as user_email, e.title as event_title, e.date_time,
        t.created_at as purchase_date, o.payment_status, o.quantity, o.total_amount as total_price
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        JOIN events e ON t.event_id = e.id
        JOIN orders o ON t.order_id = o.id
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindParam(1, $limit, PDO::PARAM_INT);
    $stmt->bindParam(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "SQL Error in tickets query: " . $e->getMessage();
    error_log($error_message);
    $_SESSION['error_message'] = 'Database error occurred: ' . $e->getMessage();
    $tickets = [];
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['manage_tickets']; ?></h1>
        
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
                    <a href="tickets.php" class="btn btn-outline-primary w-100 mb-2 active">
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
            <h5 class="mb-0"><?php echo $lang['tickets_list']; ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo $lang['event']; ?></th>
                            <th><?php echo $lang['user']; ?></th>
                            <th><?php echo $lang['date']; ?></th>
                            <th><?php echo $lang['ticket_quantity']; ?></th>
                            <th><?php echo $lang['total_price']; ?></th>
                            <th><?php echo $lang['purchase_date']; ?></th>
                            <th><?php echo $lang['payment_status']; ?></th>
                            <th><?php echo $lang['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="8" class="text-center"><?php echo $lang['no_tickets_found']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticket['event_title']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($ticket['user_name']); ?><br>
                                        <small><?php echo htmlspecialchars($ticket['user_email']); ?></small>
                                    </td>
                                    <td><?php echo formatDate($ticket['date_time']); ?></td>
                                    <td><?php echo $ticket['quantity']; ?></td>
                                    <td><?php echo formatPrice($ticket['total_price']); ?></td>
                                    <td><?php echo formatDate($ticket['purchase_date']); ?></td>
                                    <td>
                                        <?php if ($ticket['payment_status'] === 'completed'): ?>
                                            <span class="badge bg-success"><?php echo $lang['completed']; ?></span>
                                        <?php elseif ($ticket['payment_status'] === 'pending'): ?>
                                            <span class="badge bg-warning"><?php echo $lang['pending']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo $lang['cancelled']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $ticket['id']; ?>">
                                            <i class="fas fa-edit"></i> <?php echo $lang['update_status']; ?>
                                        </button>

                                        <!-- Status Modal -->
                                        <div class="modal fade" id="statusModal<?php echo $ticket['id']; ?>" tabindex="-1" aria-labelledby="statusModalLabel<?php echo $ticket['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="statusModalLabel<?php echo $ticket['id']; ?>"><?php echo $lang['update_ticket_status']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form id="statusForm<?php echo $ticket['id']; ?>" class="status-form">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                            <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">

                                                            <div class="mb-3">
                                                                <label for="status<?php echo $ticket['id']; ?>" class="form-label"><?php echo $lang['payment_status']; ?></label>
                                                                <select class="form-select" id="status<?php echo $ticket['id']; ?>" name="status">
                                                                    <option value="pending" <?php echo $ticket['payment_status'] === 'pending' ? 'selected' : ''; ?>><?php echo $lang['pending']; ?></option>
                                                                    <option value="completed" <?php echo $ticket['payment_status'] === 'completed' ? 'selected' : ''; ?>><?php echo $lang['completed']; ?></option>
                                                                    <option value="cancelled" <?php echo $ticket['payment_status'] === 'cancelled' ? 'selected' : ''; ?>><?php echo $lang['cancelled']; ?></option>
                                                                </select>
                                                            </div>

                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-primary save-status" data-ticket-id="<?php echo $ticket['id']; ?>"><?php echo $lang['save_changes']; ?></button>
                                                            </div>
                                                            <div class="mt-2 status-message" id="statusMessage<?php echo $ticket['id']; ?>"></div>
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
            <nav aria-label="Tickets pagination">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all save status buttons
    const saveButtons = document.querySelectorAll('.save-status');

    // Add click event listener to each button
    saveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-ticket-id');
            const form = document.getElementById('statusForm' + ticketId);
            const messageDiv = document.getElementById('statusMessage' + ticketId);
            const statusSelect = form.querySelector('select[name="status"]');
            const statusValue = statusSelect.value;
            const statusText = statusSelect.options[statusSelect.selectedIndex].text;
            const csrfToken = form.querySelector('input[name="csrf_token"]').value;

            // Show loading message
            messageDiv.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Updating...';

            // Create form data
            const formData = new FormData();
            formData.append('ticket_id', ticketId);
            formData.append('status', statusValue);
            formData.append('csrf_token', csrfToken);

            // Send AJAX request
            fetch('update_ticket_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status badge in the table
                    const row = button.closest('tr');
                    const statusCell = row.querySelector('td:nth-child(7)');

                    let badgeClass = 'bg-warning';
                    if (statusValue === 'completed') {
                        badgeClass = 'bg-success';
                    } else if (statusValue === 'cancelled') {
                        badgeClass = 'bg-danger';
                    }

                    // Use translated status text if available
                    const displayText = data.status_text || statusText;
                    statusCell.innerHTML = `<span class="badge ${badgeClass}">${displayText}</span>`;

                    // Show success message
                    const successMessage = '<?php echo $lang["ticket_updated"] ?? "Status updated successfully!"; ?>';
                    messageDiv.innerHTML = `<div class="alert alert-success mb-0">${successMessage}</div>`;

                    // Close modal after 1 second
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal' + ticketId));
                        modal.hide();
                    }, 1000);
                } else {
                    // Show error message
                    const errorPrefix = '<?php echo $lang["error"] ?? "Error"; ?>: ';
                    messageDiv.innerHTML = `<div class="alert alert-danger mb-0">${errorPrefix}${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const unexpectedErrorMessage = '<?php echo $lang["unexpected_error"] ?? "An error occurred. Please try again."; ?>';
                messageDiv.innerHTML = `<div class="alert alert-danger mb-0">${unexpectedErrorMessage}</div>`;
            });
        });
    });
});
</script>

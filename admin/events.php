<?php
// Set page title
$page_title = 'Manage Events';

// Include admin header
include 'includes/admin_header.php';

// Include auth functions
require_once '../includes/auth_functions.php';

// Require admin
requireAdmin();

// Delete functionality moved to delete_event_ajax.php

// Get events
try {
    $stmt = $pdo->query("
        SELECT *,
        featured as is_featured,
        1 as is_active
        FROM events
        ORDER BY date_time DESC
    ");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    $events = [];
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['manage_events']; ?></h1>
        <a href="add-event.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo $lang['add_event']; ?>
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
                    <a href="events.php" class="btn btn-outline-primary w-100 mb-2 active">
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
                    <a href="payment_cards.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-credit-card"></i><br>
                        Payment Cards
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
            <h5 class="mb-0"><?php echo $lang['events_list']; ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th><?php echo $lang['event_title']; ?></th>
                            <th><?php echo $lang['date']; ?></th>
                            <th><?php echo $lang['location']; ?></th>
                            <th><?php echo $lang['price']; ?></th>
                            <th><?php echo $lang['available_tickets']; ?></th>
                            <th><?php echo $lang['is_featured']; ?></th>
                            <th><?php echo $lang['is_active']; ?></th>
                            <th><?php echo $lang['actions']; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="8" class="text-center"><?php echo $lang['no_events_found']; ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo formatDate($event['date_time']); ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td><?php echo formatPrice($event['price']); ?></td>
                                    <td><?php echo $event['available_tickets']; ?></td>
                                    <td>
                                        <?php if ($event['is_featured']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($event['is_active']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fas fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $event['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $event['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $event['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel<?php echo $event['id']; ?>"><?php echo $lang['delete_event']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo $lang['delete_event_confirm']; ?></p>
                                                        <p><strong><?php echo htmlspecialchars($event['title']); ?></strong></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $lang['cancel']; ?></button>
                                                        <button type="button" class="btn btn-danger delete-event-btn" data-event-id="<?php echo $event['id']; ?>" data-csrf-token="<?php echo $csrf_token; ?>"><?php echo $lang['delete']; ?></button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete event button clicks
    const deleteButtons = document.querySelectorAll('.delete-event-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            const csrfToken = this.getAttribute('data-csrf-token');
            const modal = bootstrap.Modal.getInstance(this.closest('.modal'));

            // Create form data
            const formData = new FormData();
            formData.append('event_id', eventId);
            formData.append('csrf_token', csrfToken);

            // Send AJAX request
            fetch('delete_event_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Close the modal
                modal.hide();

                if (data.success) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.container').prepend(alertDiv);

                    // Remove the event row from the table
                    const eventRow = document.querySelector(`button[data-event-id="${eventId}"]`).closest('tr');
                    eventRow.remove();

                    // Check if there are no more events
                    const tableBody = document.querySelector('tbody');
                    if (tableBody.children.length === 0) {
                        const noEventsRow = document.createElement('tr');
                        noEventsRow.innerHTML = `<td colspan="8" class="text-center"><?php echo $lang['no_events_found']; ?></td>`;
                        tableBody.appendChild(noEventsRow);
                    }
                } else {
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.container').prepend(alertDiv);
                }
            })
            .catch(error => {
                console.error('Error:', error);

                // Close the modal
                modal.hide();

                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    An error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('.container').prepend(alertDiv);
            });
        });
    });
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

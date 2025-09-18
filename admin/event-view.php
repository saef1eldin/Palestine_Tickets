<?php
// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: events.php');
    exit;
}

$eventId = (int)$_GET['id'];

// Set page title
$page_title = 'View Event';

// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/init.php';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth.php';
require_once $root_path . 'includes/admin_functions.php';

// Database connection
require_once $root_path . 'config/database.php';
$db = new Database();
$pdo = $db->getConnection();

$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    redirect('../login.php');
}

// التحقق من صلاحيات إدارة الأحداث
require_admin_permission('events');

// Include admin header
include 'includes/admin_header.php';

// Get event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
$stmt->execute([':id' => $eventId]);
$event = $stmt->fetch();

// If event not found, redirect to events page
if (!$event) {
    $_SESSION['error_message'] = 'Event not found';
    header('Location: events.php');
    exit;
}

// Extract date and time from datetime
$eventDate = date('Y-m-d', strtotime($event['date_time']));
$eventTime = date('H:i', strtotime($event['date_time']));
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Event Details</h5>
                        </div>
                        <div class="col text-end">
                            <a href="events.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Events
                            </a>
                            <a href="edit-event.php?id=<?php echo $eventId; ?>" class="btn btn-sm btn-primary ms-2">
                                <i class="fas fa-edit me-2"></i>Edit Event
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Event Title</h6>
                                    <p class="fs-5"><?php echo htmlspecialchars($event['title']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Event Type</h6>
                                    <p class="fs-5"><?php echo htmlspecialchars($event['category']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Date</h6>
                                    <p class="fs-5"><?php echo date('F j, Y', strtotime($event['date_time'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Time</h6>
                                    <p class="fs-5"><?php echo date('h:i A', strtotime($event['date_time'])); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Location</h6>
                                    <p class="fs-5"><?php echo htmlspecialchars($event['location']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Available Tickets</h6>
                                    <p class="fs-5"><?php echo $event['available_tickets']; ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Price</h6>
                                    <p class="fs-5"><?php echo formatPrice($event['price']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Original Price</h6>
                                    <p class="fs-5">
                                        <?php if (!empty($event['original_price'])): ?>
                                            <?php echo formatPrice($event['original_price']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted mb-2">Description</h6>
                                    <div class="p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Featured</h6>
                                    <p>
                                        <?php if ($event['featured']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times me-1"></i> No</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted mb-2">Status</h6>
                                    <p>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Active</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Event Image</h6>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (!empty($event['image'])): ?>
                                        <img src="../uploads/events/<?php echo $event['image']; ?>" alt="Event Image" class="img-fluid rounded" style="max-height: 300px;">
                                    <?php else: ?>
                                        <div class="p-5 bg-light rounded">
                                            <i class="fas fa-image fa-4x text-muted"></i>
                                            <p class="mt-3 text-muted">No image available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card mt-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <a href="../event.php?id=<?php echo $eventId; ?>" class="btn btn-outline-primary w-100 mb-2" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i>View on Website
                                    </a>
                                    <a href="edit-event.php?id=<?php echo $eventId; ?>" class="btn btn-outline-secondary w-100 mb-2">
                                        <i class="fas fa-edit me-2"></i>Edit Event
                                    </a>
                                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash me-2"></i>Delete Event
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this event?</p>
                <p><strong><?php echo htmlspecialchars($event['title']); ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger delete-event-btn" data-event-id="<?php echo $eventId; ?>" data-csrf-token="<?php echo $csrf_token; ?>">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete event functionality
        const deleteButtons = document.querySelectorAll('.delete-event-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                const csrfToken = this.getAttribute('data-csrf-token');
                
                fetch('delete_event_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `event_id=${eventId}&csrf_token=${csrfToken}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'events.php';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the event.');
                });
            });
        });
    });
</script>

<?php include 'includes/admin_footer.php'; ?>
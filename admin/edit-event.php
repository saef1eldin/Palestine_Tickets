<?php
// Check if event ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: events.php');
    exit;
}

$eventId = (int)$_GET['id'];

// Set page title
$page_title = 'Edit Event';

// Include admin header
include 'includes/admin_header.php';

// Include auth functions and general functions
require_once '../includes/auth_functions.php';
require_once '../includes/functions.php';

// Require admin
requireAdmin();

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

// Form processing moved to update_event_ajax.php via AJAX

// Extract date and time from datetime
$eventDate = date('Y-m-d', strtotime($event['date_time']));
$eventTime = date('H:i', strtotime($event['date_time']));

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $lang['edit_event']; ?></h1>
        <a href="events.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo $lang['back_to_events']; ?>
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $lang['event_details']; ?></h5>
        </div>
        <div class="card-body">
            <form id="editEventForm" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="row">
                    <div class="col-md-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label"><?php echo $lang['event_title']; ?> *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php echo $lang['event_description']; ?></label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <!-- Date -->
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label"><?php echo $lang['event_date']; ?> *</label>
                                <input type="date" class="form-control" id="date" name="date" value="<?php echo $eventDate; ?>" required>
                            </div>

                            <!-- Time -->
                            <div class="col-md-6 mb-3">
                                <label for="time" class="form-label"><?php echo $lang['event_time']; ?> *</label>
                                <input type="time" class="form-control" id="time" name="time" value="<?php echo $eventTime; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Location -->
                            <div class="col-md-12 mb-3">
                                <label for="location" class="form-label"><?php echo $lang['event_location']; ?> *</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Original Price -->
                            <div class="col-md-4 mb-3">
                                <label for="original-price" class="form-label">السعر الأصلي</label>
                                <div class="input-group">
                                    <span class="input-group-text">₪</span>
                                    <input type="number" class="form-control" id="original-price" name="original_price" min="0" step="0.01" value="<?php echo isset($event['original_price']) ? $event['original_price'] : ''; ?>">
                                    <div class="form-text">اترك فارغاً إذا لم يكن هناك خصم</div>
                                </div>
                            </div>

                            <!-- Price (Discounted) -->
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label"><?php echo $lang['event_price']; ?> *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₪</span>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo $event['price']; ?>" required>
                                    <div class="form-text">السعر بعد الخصم (إذا كان هناك خصم)</div>
                                </div>
                            </div>

                            <!-- Type -->
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label"><?php echo $lang['event_type']; ?> *</label>
                                <input type="text" class="form-control" id="type" name="type" value="<?php echo htmlspecialchars($event['category']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Available Tickets -->
                            <div class="col-md-4 mb-3">
                                <label for="available-tickets" class="form-label"><?php echo $lang['available_tickets']; ?> *</label>
                                <input type="number" class="form-control" id="available-tickets" name="available_tickets" min="0" value="<?php echo $event['available_tickets']; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Current Image -->
                        <?php if (!empty($event['image'])): ?>
                            <div class="mb-3">
                                <label class="form-label"><?php echo $lang['current_image']; ?></label>
                                <img src="../<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="img-fluid rounded">
                            </div>
                        <?php endif; ?>

                        <!-- Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label"><?php echo $lang['event_image']; ?></label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text"><?php echo $lang['image_requirements']; ?></div>
                        </div>

                        <!-- Image Preview -->
                        <div class="mb-3">
                            <img id="image-preview" src="#" alt="<?php echo $lang['image_preview']; ?>" class="img-fluid rounded" style="display: none;">
                        </div>

                        <!-- Is Featured -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is-featured" name="is_featured" <?php echo $event['featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is-featured"><?php echo $lang['is_featured']; ?></label>
                        </div>

                        <!-- Note: is_active field is not present in the events table -->
                    </div>
                </div>

                <hr>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" id="saveEventBtn" class="btn btn-primary"><?php echo $lang['save_event']; ?></button>
                </div>

                <!-- Status Message -->
                <div id="statusMessage" class="mt-3"></div>

                <!-- Hidden event ID -->
                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const saveBtn = document.getElementById('saveEventBtn');
    const form = document.getElementById('editEventForm');
    const statusMessage = document.getElementById('statusMessage');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle form submission via AJAX
    saveBtn.addEventListener('click', function() {
        // Show loading message
        statusMessage.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Saving...';

        // Create form data
        const formData = new FormData(form);

        // Send AJAX request
        fetch('update_event_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                statusMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;

                // Redirect to events page after 1 second
                setTimeout(() => {
                    window.location.href = 'events.php';
                }, 1000);
            } else {
                // Show error message
                statusMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                // Scroll to the error message
                statusMessage.scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusMessage.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            // Scroll to the error message
            statusMessage.scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

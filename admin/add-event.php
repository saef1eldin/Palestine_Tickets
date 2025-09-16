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

// Check event management permissions
require_admin_permission('site');

// Set page title
$page_title = 'Add Event';

// Include admin header
include 'includes/admin_header.php';

// Form processing moved to add_event_ajax.php via AJAX

// Generate CSRF token
try {
    $csrf_token = generateCSRFToken();
} catch (Exception $e) {
    error_log("Error generating CSRF token: " . $e->getMessage());
    $csrf_token = md5(uniqid(mt_rand(), true));
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Add Event</h1>
        <a href="events.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Events
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Event Details</h5>
        </div>
        <div class="card-body">
            <form id="addEventForm" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="row">
                    <div class="col-md-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Event Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"></textarea>
                        </div>

                        <div class="row">
                            <!-- Start Date -->
                            <div class="col-md-3 mb-3">
                                <label for="date" class="form-label">Event Date (Start) *</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>

                            <!-- Start Time -->
                            <div class="col-md-3 mb-3">
                                <label for="time" class="form-label">Event Time (Start) *</label>
                                <input type="time" class="form-control" id="time" name="time" required>
                            </div>

                            <!-- End Date -->
                            <div class="col-md-3 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            
                            <!-- End Time -->
                            <div class="col-md-3 mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Location -->
                            <div class="col-md-12 mb-3">
                                <label for="location" class="form-label">Event Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Original Price -->
                            <div class="col-md-4 mb-3">
                                <label for="original-price" class="form-label">Original Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">₪</span>
                                    <input type="number" class="form-control" id="original-price" name="original_price" min="0" step="0.01">
                                    <div class="form-text">Leave empty if no discount</div>
                                </div>
                            </div>

                            <!-- Price (Discounted) -->
                            <div class="col-md-4 mb-3">
                                <label for="price" class="form-label">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₪</span>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                    <div class="form-text">Price after discount (if applicable)</div>
                                </div>
                            </div>

                            <!-- Category (Type) -->
                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label">Event Type (Category) *</label>
                                <input type="text" class="form-control" id="type" name="type" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Available Tickets -->
                            <div class="col-md-4 mb-3">
                                <label for="available-tickets" class="form-label">Available Tickets *</label>
                                <input type="number" class="form-control" id="available-tickets" name="available_tickets" min="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Event Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Image should be at least 800x600px and less than 2MB</div>
                        </div>

                        <!-- Image Preview -->
                        <div class="mb-3">
                            <img id="image-preview" src="#" alt="Image Preview" class="img-fluid rounded" style="display: none;">
                        </div>

                        <!-- Featured -->
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is-featured" name="is_featured">
                            <label class="form-check-label" for="is-featured">Featured Event</label>
                        </div>

                        <!-- Is Active field removed as it doesn't exist in the database -->
                        <!-- <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is-active" name="is_active" checked>
                            <label class="form-check-label" for="is-active">Active</label>
                        </div> -->
                    </div>
                </div>

                <hr>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" id="saveEventBtn" class="btn btn-primary">Save Event</button>
                </div>

                <!-- Status Message -->
                <div id="statusMessage" class="mt-3"></div>
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
    const form = document.getElementById('addEventForm');
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
        fetch('add_event_ajax.php', {
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

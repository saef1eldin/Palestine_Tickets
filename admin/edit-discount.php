<?php
// Check if discount ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: discounts.php');
    exit;
}

$discountId = (int)$_GET['id'];

// Include necessary files first
require_once '../includes/init.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

// Require admin permission
require_admin_permission('discounts', 'admin/discounts.php');

// Set page title
$page_title = 'Edit Discount';

// Include admin header
include 'includes/admin_header.php';

// Get discount details
$stmt = $pdo->prepare("SELECT *, expiry_date as expiration_date, 0 as usage_count, 1 as is_active FROM coupons WHERE id = :id");
$stmt->execute([':id' => $discountId]);
$discount = $stmt->fetch();

// If discount not found, redirect to discounts page
if (!$discount) {
    $_SESSION['error_message'] = 'Discount not found';
    header('Location: discounts.php');
    exit;
}

// Form processing moved to update_discount.php via AJAX

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Discount</h1>
        <a href="discounts.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Discounts
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Discount Details</h5>
        </div>
        <div class="card-body">
            <form id="editDiscountForm">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Code -->
                <div class="mb-3">
                    <label for="code" class="form-label">Discount Code *</label>
                    <input type="text" class="form-control" id="code" name="code" value="<?php echo htmlspecialchars($discount['code']); ?>" required>
                </div>

                <div class="row">
                    <!-- Type -->
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Discount Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="percentage" <?php echo $discount['type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                            <option value="fixed" <?php echo $discount['type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                        </select>
                    </div>

                    <!-- Value -->
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Discount Value *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="value" name="value" min="0" step="0.01" value="<?php echo $discount['value']; ?>" required>
                            <span class="input-group-text" id="value-addon"><?php echo $discount['type'] === 'percentage' ? '%' : '₪'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Usage Limit -->
                    <div class="col-md-6 mb-3">
                        <label for="usage-limit" class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" id="usage-limit" name="usage_limit" min="1" value="<?php echo $discount['usage_limit']; ?>">
                        <div class="form-text">Leave empty for unlimited</div>
                    </div>

                    <!-- Expiration Date -->
                    <div class="col-md-6 mb-3">
                        <label for="expiration-date" class="form-label">Expiration Date</label>
                        <input type="date" class="form-control" id="expiration-date" name="expiration_date" value="<?php echo $discount['expiry_date']; ?>">
                        <div class="form-text">Leave empty for no expiration</div>
                    </div>
                </div>

                <!-- Usage Count (readonly) -->
                <div class="mb-3">
                    <label for="usage-count" class="form-label">Usage Count</label>
                    <input type="number" class="form-control" id="usage-count" value="<?php echo $discount['usage_count']; ?>" readonly>
                </div>

                <!-- Note: is_active field is not present in the coupons table -->

                <hr>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" id="saveDiscountBtn" class="btn btn-primary">Save Discount</button>
                </div>

                <!-- Status Message -->
                <div id="statusMessage" class="mt-3"></div>

                <!-- Hidden discount ID -->
                <input type="hidden" name="discount_id" value="<?php echo $discountId; ?>">
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueAddon = document.getElementById('value-addon');
    const saveBtn = document.getElementById('saveDiscountBtn');
    const form = document.getElementById('editDiscountForm');
    const statusMessage = document.getElementById('statusMessage');

    // Update value addon based on discount type
    typeSelect.addEventListener('change', function() {
        if (this.value === 'percentage') {
            valueAddon.textContent = '%';
        } else {
            valueAddon.textContent = '₪';
        }
    });

    // Handle form submission via AJAX
    saveBtn.addEventListener('click', function() {
        // Show loading message
        statusMessage.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Saving...';

        // Create form data
        const formData = new FormData(form);

        // Send AJAX request
        fetch('update_discount.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                statusMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;

                // Redirect to discounts page after 1 second
                setTimeout(() => {
                    window.location.href = 'discounts.php';
                }, 1000);
            } else {
                // Show error message
                statusMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusMessage.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        });
    });
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

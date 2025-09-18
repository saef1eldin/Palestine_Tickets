<?php
// بدء الجلسة إذا لم تكن نشطة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$page_title = 'إضافة خصم';

// Include admin header
include 'includes/admin_header.php';

// Include auth functions
require_once '../includes/auth_functions.php';

// Include functions
require_once '../includes/functions.php';

// Include admin functions
require_once '../includes/admin_functions.php';

// التحقق من صلاحيات إدارة الخصومات
require_admin_permission('discounts', 'admin/discounts.php');


// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>إضافة خصم</h1>
        <a href="discounts.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> العودة للخصومات
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">تفاصيل الخصم</h5>
        </div>
        <div class="card-body">
            <form id="addDiscountForm">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <!-- Code -->
                <div class="mb-3">
                    <label for="code" class="form-label">كود الخصم *</label>
                    <input type="text" class="form-control" id="code" name="code" required>
                </div>

                <div class="row">
                    <!-- Type -->
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">نوع الخصم *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="percentage">نسبة مئوية</option>
                            <option value="fixed">مبلغ ثابت</option>
                        </select>
                    </div>

                    <!-- Value -->
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">قيمة الخصم *</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="value" name="value" min="0" step="0.01" required>
                            <span class="input-group-text" id="value-addon">%</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Usage Limit -->
                    <div class="col-md-6 mb-3">
                        <label for="usage-limit" class="form-label">حد الاستخدام</label>
                        <input type="number" class="form-control" id="usage-limit" name="usage_limit" min="1">
                        <div class="form-text">اتركه فارغاً للاستخدام غير المحدود</div>
                    </div>

                    <!-- Expiration Date -->
                    <div class="col-md-6 mb-3">
                        <label for="expiration-date" class="form-label">تاريخ الانتهاء</label>
                        <input type="date" class="form-control" id="expiration-date" name="expiration_date">
                        <div class="form-text">اتركه فارغاً لعدم الانتهاء</div>
                    </div>
                </div>

                <!-- Note: is_active field is not present in the coupons table -->

                <hr>

                <!-- Submit Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" id="saveDiscountBtn" class="btn btn-primary">حفظ الخصم</button>
                </div>

                <!-- Status Message -->
                <div id="statusMessage" class="mt-3"></div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueAddon = document.getElementById('value-addon');
    const saveBtn = document.getElementById('saveDiscountBtn');
    const form = document.getElementById('addDiscountForm');
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
        statusMessage.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div> جاري الحفظ...';

        // Create form data
        const formData = new FormData(form);

        // Send AJAX request
        fetch('add_discount_ajax.php', {
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
            statusMessage.innerHTML = '<div class="alert alert-danger">حدث خطأ. يرجى المحاولة مرة أخرى.</div>';
        });
    });
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

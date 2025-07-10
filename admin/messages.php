<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions
$root_path = dirname(__DIR__) . '/';
require_once $root_path . 'includes/functions.php';
require_once $root_path . 'includes/auth_functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Store current page as redirect destination after login
    $_SESSION['redirect_after_login'] = 'admin/messages.php';

    // Redirect to admin login page
    redirect('login.php');
    exit;
}

// Set page title
$page_title = 'Contact Messages';

// Include admin header
include 'includes/admin_header.php';

// Handle message actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'mark_read') {
        // Mark message as read
        $db->query("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
        $db->bind(':id', $id);
        if ($db->execute()) {
            $_SESSION['success_message'] = 'Message marked as read';
        } else {
            $_SESSION['error_message'] = 'Failed to mark message as read';
        }
        redirect('messages.php');
    } elseif ($action === 'delete') {
        // Delete message
        $db->query("DELETE FROM contact_messages WHERE id = :id");
        $db->bind(':id', $id);
        if ($db->execute()) {
            $_SESSION['success_message'] = 'Message deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to delete message';
        }
        redirect('messages.php');
    }
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$db->query("SELECT COUNT(*) as total FROM contact_messages");
$total = $db->single()['total'];
$total_pages = ceil($total / $limit);

// Get messages
$db->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$db->bind(':limit', $limit);
$db->bind(':offset', $offset);
$messages = $db->resultSet();

// Get unread count
$db->query("SELECT COUNT(*) as unread FROM contact_messages WHERE is_read = 0");
$unread_count = $db->single()['unread'];
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Contact Messages <?php if ($unread_count > 0): ?><span class="badge bg-danger"><?php echo $unread_count; ?> Unread</span><?php endif; ?></h1>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Messages</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No messages found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <tr class="<?php echo $message['is_read'] ? '' : 'table-primary'; ?>">
                                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></td>
                                    <td>
                                        <?php if ($message['is_read']): ?>
                                            <span class="badge bg-success">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unread</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-message" data-bs-toggle="modal" data-bs-target="#messageModal" 
                                            data-id="<?php echo $message['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($message['name']); ?>"
                                            data-email="<?php echo htmlspecialchars($message['email']); ?>"
                                            data-subject="<?php echo htmlspecialchars($message['subject']); ?>"
                                            data-message="<?php echo htmlspecialchars($message['message']); ?>"
                                            data-date="<?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>"
                                            data-read="<?php echo $message['is_read']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if (!$message['is_read']): ?>
                                            <a href="messages.php?action=mark_read&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-success" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="messages.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="messages.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="messages.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>From:</strong> <span id="modal-name"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Date:</strong> <span id="modal-date"></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Message</h6>
                            </div>
                            <div class="card-body">
                                <p id="modal-message"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="modal-mark-read" class="btn btn-success">Mark as Read</a>
                <a href="#" id="modal-reply" class="btn btn-primary">Reply by Email</a>
                <a href="#" id="modal-delete" class="btn btn-danger">Delete</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle message modal
    const messageModal = document.getElementById('messageModal');
    if (messageModal) {
        messageModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const email = button.getAttribute('data-email');
            const subject = button.getAttribute('data-subject');
            const message = button.getAttribute('data-message');
            const date = button.getAttribute('data-date');
            const isRead = button.getAttribute('data-read') === '1';
            
            // Update modal content
            document.getElementById('modal-name').textContent = name;
            document.getElementById('modal-email').textContent = email;
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-message').textContent = message;
            document.getElementById('modal-date').textContent = date;
            
            // Update action buttons
            const markReadBtn = document.getElementById('modal-mark-read');
            markReadBtn.href = `messages.php?action=mark_read&id=${id}`;
            markReadBtn.style.display = isRead ? 'none' : 'inline-block';
            
            document.getElementById('modal-reply').href = `mailto:${email}?subject=Re: ${subject}`;
            document.getElementById('modal-delete').href = `messages.php?action=delete&id=${id}`;
            document.getElementById('modal-delete').onclick = function() {
                return confirm('Are you sure you want to delete this message?');
            };
            
            // Mark as read when viewed
            if (!isRead) {
                fetch(`messages.php?action=mark_read&id=${id}`, { method: 'GET' })
                    .then(response => {
                        if (response.ok) {
                            // Refresh the page after modal is closed
                            messageModal.addEventListener('hidden.bs.modal', function() {
                                window.location.reload();
                            }, { once: true });
                        }
                    });
            }
        });
    }
});
</script>

<?php
// Include admin footer
include 'includes/admin_footer.php';
?>

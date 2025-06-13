<?php
// admin/pages/withdrawal_requests.php

use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Affiliate\getWithdrawalRequests;
use function AffiliateBasic\Core\Affiliate\getTotalWithdrawalRequests;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

// Admin check is already performed in admin/index.php, so this is redundant but safe
if (!isset($_SESSION['logged_in']) || !$_SESSION['is_admin']) {
    // This part of the code should ideally not be reached if the admin router is working correctly.
    echo "Access Denied.";
    exit;
}

global $pdo;
// The main config and functions are loaded by the admin router (admin/index.php)
// We only need to include the specific function files for this page's logic.
require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php'; // For getStatusBadgeClass

// --- Pagination Logic ---
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// --- Filtering Logic ---
$statusFilter = $_GET['status_filter'] ?? 'pending'; // Default view to 'pending' requests
if (!in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'])) {
    $statusFilter = 'pending'; // Fallback to default if invalid value is provided
}
// Use null for the function argument if we want all statuses
$actualFilter = ($statusFilter === 'all') ? null : $statusFilter;

// --- Data Fetching ---
$requests = getWithdrawalRequests($pdo, $actualFilter, $limit, $offset);
$totalRequests = getTotalWithdrawalRequests($pdo, $actualFilter);
$totalPages = ceil($totalRequests / $limit);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Withdrawal Requests</h1>
</div>

<?php echo displayMessage(); ?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <form method="get" action="<?php echo SITE_URL; ?>/admin/withdrawal-requests"
            class="row g-3 align-items-center">
            <div class="col-md-4 col-lg-3">
                <label for="status_filter" class="visually-hidden">Filter by Status:</label>
                <select name="status_filter" id="status_filter" class="form-select" onchange="this.form.submit()">
                    <option value="pending" <?php echo ($statusFilter === 'pending' ? 'selected' : ''); ?>>Pending
                    </option>
                    <option value="approved" <?php echo ($statusFilter === 'approved' ? 'selected' : ''); ?>>Approved
                    </option>
                    <option value="rejected" <?php echo ($statusFilter === 'rejected' ? 'selected' : ''); ?>>Rejected
                    </option>
                    <option value="all" <?php echo ($statusFilter === 'all' ? 'selected' : ''); ?>>Show All</option>
                </select>
            </div>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <div class="alert alert-info">No withdrawal requests found
                <?php echo $actualFilter ? 'with status "' . htmlspecialchars($actualFilter) . '"' : ''; ?>.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Details</th>
                            <th>Requested At</th>
                            <th>Status</th>
                            <th>Processed At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($request['user_username']); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($request['user_email']); ?></small>
                                </td>
                                <td class="text-end">
                                    $<?php echo htmlspecialchars(number_format($request['requested_amount'], 2)); ?></td>
                                <td>
                                    <pre class="mb-0"
                                        style="white-space: pre-wrap; word-break: break-all;"><?php echo htmlspecialchars($request['payment_details']); ?></pre>
                                </td>
                                <td><?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($request['requested_at']))); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getStatusBadgeClass($request['status']); ?> fs-6">
                                        <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $request['processed_at'] ? htmlspecialchars(date('M j, Y, g:i a', strtotime($request['processed_at']))) : 'N/A'; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <form action="<?php echo SITE_URL; ?>/admin-process-withdrawal-action" method="post"
                                            class="d-inline-block me-1">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Are you sure you want to approve this withdrawal? This will deduct from the user\'s balance and assumes payment has been sent externally.');">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#rejectModal_<?php echo $request['id']; ?>">Reject</button>

                                        <div class="modal fade" id="rejectModal_<?php echo $request['id']; ?>" tabindex="-1"
                                            aria-labelledby="rejectModalLabel_<?php echo $request['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="<?php echo SITE_URL; ?>/admin-process-withdrawal-action"
                                                    method="post">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="rejectModalLabel_<?php echo $request['id']; ?>">Reject
                                                                Withdrawal #<?php echo $request['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="csrf_token"
                                                                value="<?php echo generateCSRFToken(); ?>">
                                                            <input type="hidden" name="request_id"
                                                                value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <div class="mb-3">
                                                                <label for="admin_notes_<?php echo $request['id']; ?>"
                                                                    class="form-label">Reason for Rejection (Optional)</label>
                                                                <textarea class="form-control"
                                                                    id="admin_notes_<?php echo $request['id']; ?>"
                                                                    name="admin_notes" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        Processed
                                        <?php if (!empty($request['admin_notes'])): ?>
                                            <button type="button" class="btn btn-sm btn-outline-info ms-1" data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="<?php echo htmlspecialchars($request['admin_notes']); ?>">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Withdrawal requests pagination">
                    <ul class="pagination justify-content-center mt-4">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/admin/withdrawal-requests?page=<?php echo $i; ?><?php echo $statusFilter ? '&status_filter=' . $statusFilter : ''; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
    // This script needs to be included at the end of the body in the main admin template,
    // but we place it here to ensure it's available when this page loads.
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tooltips for admin notes on processed requests
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
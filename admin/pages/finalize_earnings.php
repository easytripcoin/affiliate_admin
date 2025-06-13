<?php
// admin/pages/finalize_earnings.php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Affiliate\getEarningsAwaitingClearance;
use function AffiliateBasic\Core\Affiliate\getTotalEarningsAwaitingClearance;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';
require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

global $pdo;

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$earnings = getEarningsAwaitingClearance($pdo, AFFILIATE_REFUND_PERIOD_DAYS, true, $limit, $offset);
$totalEarnings = getTotalEarningsAwaitingClearance($pdo, AFFILIATE_REFUND_PERIOD_DAYS, true);
$totalPages = ceil($totalEarnings / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Finalize Affiliate Earnings</h1>
</div>
<p class="mb-4">Earnings below are in 'Awaiting Clearance' status and their associated order was confirmed as delivered
    & paid more than <?php echo AFFILIATE_REFUND_PERIOD_DAYS; ?> days ago. Finalizing them will move the funds to the
    affiliate's balance.</p>
<?php echo displayMessage(); ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <?php if (empty($earnings)): ?>
            <div class="alert alert-info">No earnings are currently eligible for finalization.</div>
        <?php else: ?>
            <form action="<?php echo SITE_URL; ?>/admin-finalize-earnings-action" method="post" id="finalizeEarningsForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllEarnings"></th>
                                <th>Earning ID</th>
                                <th>Affiliate</th>
                                <th>Order ID</th>
                                <th>Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($earnings as $earning): ?>
                                <tr>
                                    <td><input type="checkbox" name="earning_ids[]" value="<?php echo $earning['id']; ?>"
                                            class="earning-checkbox"></td>
                                    <td><?php echo $earning['id']; ?></td>
                                    <td><?php echo htmlspecialchars($earning['affiliate_username']); ?></td>
                                    <td>#<?php echo htmlspecialchars($earning['order_id']); ?></td>
                                    <td class="text-end">
                                        $<?php echo htmlspecialchars(number_format($earning['earned_amount'], 2)); ?></td>
                                    <td class="text-center">
                                        <button type="submit" name="action"
                                            value="finalize_single_<?php echo $earning['id']; ?>"
                                            class="btn btn-sm btn-success">Finalize</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="action" value="finalize_selected" class="btn btn-primary my-3"
                    id="finalizeSelectedBtn" disabled>Finalize Selected</button>
            </form>

            <?php if ($totalPages > 1): ?>
                <nav aria-label="Finalize earnings pagination">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link"
                                    href="<?php echo SITE_URL; ?>/admin/finalize-earnings?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllEarnings');
        const checkboxes = document.querySelectorAll('.earning-checkbox');
        const finalizeSelectedBtn = document.getElementById('finalizeSelectedBtn');

        if (selectAll) {
            selectAll.addEventListener('change', function (e) {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                toggleFinalizeSelectedButton();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', toggleFinalizeSelectedButton);
        });

        function toggleFinalizeSelectedButton() {
            if (!finalizeSelectedBtn) return;
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            finalizeSelectedBtn.disabled = !anyChecked;
        }
        toggleFinalizeSelectedButton();
    });
</script>
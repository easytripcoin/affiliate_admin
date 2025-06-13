<?php
// admin/pages/orders.php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Core\Ecommerce\getAllOrders;
use function AffiliateBasic\Core\Ecommerce\formatStatusText;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;

require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

global $pdo;
$orders = getAllOrders($pdo);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Orders</h1>
</div>
<?php echo displayMessage(); ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Order Status</th>
                            <th class="text-center">Payment Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($order['created_at']))); ?></td>
                                <td class="text-end">$<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?> fs-6">
                                        <?php echo formatStatusText($order['order_status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?> fs-6">
                                        <?php echo formatStatusText($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo SITE_URL; ?>/admin/order-detail?id=<?php echo $order['id']; ?>"
                                        class="btn btn-sm btn-info" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
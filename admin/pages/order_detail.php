<?php
// admin/pages/order_detail.php
use function AffiliateBasic\Config\redirectWithMessage;
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getOrderDetails;
use function AffiliateBasic\Core\Ecommerce\getStatusBadgeClass;
use function AffiliateBasic\Core\Ecommerce\formatStatusText;

require_once PROJECT_ROOT_PATH . '/core/ecommerce/order_functions.php';

global $pdo;

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirectWithMessage('admin/orders', 'danger', 'Invalid order ID.');
}

$order = getOrderDetails($pdo, $orderId);
if (!$order) {
    redirectWithMessage('admin/orders', 'danger', 'Order not found.');
}

$possible_order_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'pending_cod_confirmation'];
$possible_payment_statuses = ['pending_payment', 'paid', 'failed', 'refunded', 'pending_cod_confirmation', 'paid_placeholder'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>
    <a href="<?php echo SITE_URL; ?>/admin/orders" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i>
        Back to Orders</a>
</div>
<?php echo displayMessage(); ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name'] ?? 'Product Deleted'); ?></td>
                                    <td class="text-center">x <?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td class="text-end">
                                        $<?php echo htmlspecialchars(number_format($item['price_per_unit'], 2)); ?></td>
                                    <td class="text-end fw-bold">
                                        $<?php echo htmlspecialchars(number_format($item['price_per_unit'] * $item['quantity'], 2)); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end fs-5">
                                    $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Shipping Address</h6>
            </div>
            <div class="card-body">
                <address>
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                </address>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order & Customer Info</h6>
            </div>
            <div class="card-body">
                <p><strong>Customer:</strong><br><?php echo htmlspecialchars($order['customer_name']); ?><br><small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                </p>
                <p><strong>Referred by:</strong> <?php echo htmlspecialchars($order['referrer_name'] ?? 'N/A'); ?></p>
                <p><strong>Order Date:</strong>
                    <?php echo htmlspecialchars(date('M j, Y, g:i a', strtotime($order['created_at']))); ?></p>
                <p><strong>Payment Method:</strong>
                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></p>
                <hr>
                <form action="<?php echo SITE_URL; ?>/admin-order-update-status-action" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <div class="mb-3">
                        <label for="payment_status" class="form-label fw-bold">Payment Status: <span
                                class="badge bg-<?php echo getStatusBadgeClass($order['payment_status']); ?>"><?php echo formatStatusText($order['payment_status']); ?></span></label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <?php foreach ($possible_payment_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($order['payment_status'] == $status) ? 'selected' : ''; ?>><?php echo formatStatusText($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="order_status" class="form-label fw-bold">Order Status: <span
                                class="badge bg-<?php echo getStatusBadgeClass($order['order_status']); ?>"><?php echo formatStatusText($order['order_status']); ?></span></label>
                        <select name="order_status" id="order_status" class="form-select">
                            <?php foreach ($possible_order_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo ($order['order_status'] == $status) ? 'selected' : ''; ?>><?php echo formatStatusText($status); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Statuses</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
// admin/pages/products.php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getAllProductsForAdmin;

require_once PROJECT_ROOT_PATH . '/core/ecommerce/product_functions.php';

global $pdo;
$products = getAllProductsForAdmin($pdo);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Products</h1>
    <a href="<?php echo SITE_URL; ?>/admin/add-product" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add
        New Product</a>
</div>
<?php echo displayMessage(); ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="alert alert-info">No products found. <a href="<?php echo SITE_URL; ?>/admin/add-product">Add the
                    first product</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Affiliate Bonus %</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="text-end">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                                <td class="text-end">
                                    <?php echo htmlspecialchars(number_format($product['affiliate_bonus_percentage'], 2)); ?>%
                                </td>
                                <td class="text-center">
                                    <a href="<?php echo SITE_URL; ?>/admin/edit-product?id=<?php echo $product['id']; ?>"
                                        class="btn btn-sm btn-warning me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <form action="<?php echo SITE_URL; ?>/admin-product-delete-action" method="post"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i
                                                class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
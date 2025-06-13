Of course. I can help create a separate, dedicated admin site for your project. This will involve creating a new `/admin` directory with its own templates for a sidebar, topbar, and footer, moving all admin-related pages and logic, and updating the routing to handle the new structure.

Here are the step-by-step instructions and the code for the new files and modifications.

***

### 1. New Admin Directory Structure

First, create a new `admin` directory in your project root. Inside it, create the following subdirectories: `assets/css`, `assets/js`, `pages`, and `templates`.

Your new structure will look like this:

```
/
├── admin/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── script.js
│   ├── pages/
│   │   ├── dashboard.php
│   │   ├── products.php
│   │   ├── add_product.php
│   │   ├── edit_product.php
│   │   ├── orders.php
│   │   ├── order_detail.php
│   a   ├── manage_affiliates.php
│   │   ├── withdrawal_requests.php
│   │   └── finalize_earnings.php
│   ├── templates/
│   │   ├── footer.php
│   │   ├── sidebar.php
│   │   └── topbar.php
│   └── index.php
├── assets/
├── config/
├── core/
├── logs/
├── pages/
├── templates/
├── vendor/
└── index.php
```

### 2. Main Router Update

Modify the main `index.php` at the root of your project to delegate all admin-related traffic to the new admin area.

#### `index.php` (modified)
```php
<?php
// This is the main entry point for the application.
// It handles routing and includes the necessary page content or action script.

use function AffiliateBasic\Config\sanitizeInput;

// --- Admin Route Check ---
// If the request URI starts with /admin, hand it over to the admin router.
if (strpos($_SERVER['REQUEST_URI'], '/admin') === 0) {
    // Ensure the admin index file exists before including it
    $admin_index_path = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'index.php';
    if (file_exists($admin_index_path)) {
        require_once $admin_index_path;
        // Stop further execution in the main router
        exit;
    }
}

// Ensure config is loaded first for the public site.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

// Include core initialization scripts (like remember_me handler)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'auth' . DIRECTORY_SEPARATOR . 'remember_me.php';

// --- Capture Affiliate Referral Code ---
if (isset($_GET['ref'])) {
    $referral_code = trim(sanitizeInput($_GET['ref']));
    if (!empty($referral_code)) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_affiliate_code = ? AND is_affiliate = 1");
        $stmt->execute([$referral_code]);
        $referrer = $stmt->fetch();
        if ($referrer) {
            $_SESSION['referrer_user_id'] = $referrer['id'];
            $_SESSION['affiliate_code_used'] = $referral_code;
        }
    }
}

// --- Public Site Routing Logic ---
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '';
if (!empty($subdirectory) && strpos($requestUri, $subdirectory) === 0) {
    $basePath = $subdirectory;
}
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
$requestPath = strtok($requestUri, '?');
if (isset($_GET['ref'])) {
    $requestUriClean = preg_replace('/([&?]ref=[^&]*)|(ref=[^&]*&)/', '', $_SERVER['REQUEST_URI']);
    $requestPath = strtok(substr($requestUriClean, strlen($basePath)), '?');
}
$requestPath = trim($requestPath, '/');

$currentPage = '';

// Define available public pages
$availablePages = [
    '' => 'home.php',
    'home' => 'home.php',
    'about' => 'about.php',
    'contact' => 'contact.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'dashboard' => 'dashboard.php',
    'profile' => 'profile.php',
    'privacy' => 'privacy.php',
    'terms' => 'terms.php',
    'change-password' => 'change_password.php',
    'forgot-password' => 'forgot_password.php',
    'reset-password' => 'reset_password.php',
    'verify-email' => 'verify_email.php',
    'products' => 'products.php',
    'product' => 'product.php',
    'cart' => 'cart.php',
    'checkout' => 'checkout.php',
    'order-confirmation' => 'order_confirmation.php',
    'my-orders' => 'my_orders.php',
    'order-detail' => 'order_detail_user.php',
    'affiliate-dashboard' => 'affiliate_dashboard.php',
];

// Actions remain here as they are not "pages" with a full template
$availableActions = [
    'login-action' => 'auth/login.php',
    'register-action' => 'auth/register.php',
    'contact-action' => 'contact/submit.php',
    'forgot-password-action' => 'auth/forgot-password.php',
    'reset-password-action' => 'auth/reset-password.php',
    'change-password-action' => 'auth/change-password.php',
    'update-profile-action' => 'auth/update-profile.php',
    'logout-action' => 'auth/logout.php',
    'cart-add-action' => 'ecommerce/cart_add_action.php',
    'cart-update-action' => 'ecommerce/cart_update_action.php',
    'cart-remove-action' => 'ecommerce/cart_remove_action.php',
    'order-place-action' => 'ecommerce/order_place_action.php',
    'admin-product-add-action' => 'ecommerce/admin_product_add_action.php',
    'admin-product-edit-action' => 'ecommerce/admin_product_edit_action.php',
    'admin-product-delete-action' => 'ecommerce/admin_product_delete_action.php',
    'admin-order-update-status-action' => 'ecommerce/admin_order_update_status_action.php',
    'request-withdrawal-action' => 'affiliate/request_withdrawal_action.php',
    'admin-process-withdrawal-action' => 'affiliate/admin_process_withdrawal_action.php',
    'admin-manage-affiliates-action' => 'affiliate/admin_manage_affiliates_action.php',
    'admin-finalize-earnings-action' => 'affiliate/admin_finalize_earnings_action.php',
];

$scriptToInclude = null;

if (array_key_exists($requestPath, $availableActions)) {
    $actionFileRelativePath = $availableActions[$requestPath];
    $filePath = PROJECT_ROOT_PATH . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . $actionFileRelativePath;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
    }
} elseif (array_key_exists($requestPath, $availablePages)) {
    $pageFileName = $availablePages[$requestPath];
    $filePath = PAGES_PATH . DIRECTORY_SEPARATOR . $pageFileName;
    if (file_exists($filePath)) {
        $scriptToInclude = $filePath;
        $currentPage = $requestPath === '' ? 'home' : $requestPath;
    }
}

if ($scriptToInclude) {
    require $scriptToInclude;
} else {
    http_response_code(404);
    $currentPage = '404';
    $notFoundPagePath = PAGES_PATH . DIRECTORY_SEPARATOR . '404.php';
    if (file_exists($notFoundPagePath)) {
        require $notFoundPagePath;
    } else {
        echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>404 Not Found</title></head><body>";
        echo "<h1>404 Not Found</h1><p>The page or action you requested could not be found.</p>";
        echo "<p><a href='" . SITE_URL . "/home'>Go to Homepage</a></p>";
        echo "</body></html>";
    }
}
```

### 3. New Admin Site Files

Here is the code for all the new files inside the `/admin` directory.

#### `admin/index.php` (Admin Front Controller)
```php
<?php
// admin/index.php - Main entry point for the admin area

// Load main configuration which sets up constants, DB connection, etc.
require_once dirname(__DIR__) . '/config/config.php';

use function AffiliateBasic\Config\redirectWithMessage;

// --- ADMIN AREA AUTHENTICATION & ROUTING ---

// All admin pages require a logged-in admin user.
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the main site's login page with an error message
    redirectWithMessage('login', 'danger', 'Access denied. You must be an admin to view this page.');
    exit;
}

// Routing for the admin area
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '';
// Remove the main subdirectory if it exists
if (!empty($subdirectory) && strpos($requestUri, $subdirectory) === 0) {
    $basePath = $subdirectory;
}
if (!empty($basePath) && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Get the request path relative to the /admin directory
$adminRequestPath = trim(str_replace('/admin', '', strtok($requestUri, '?')), '/');

$currentPage = $adminRequestPath ?: 'dashboard'; // Default to dashboard

// Define available admin pages
$availableAdminPages = [
    'dashboard'           => 'dashboard.php',
    'products'            => 'products.php',
    'add-product'         => 'add_product.php',
    'edit-product'        => 'edit_product.php',
    'orders'              => 'orders.php',
    'order-detail'        => 'order_detail.php',
    'manage-affiliates'   => 'manage_affiliates.php',
    'withdrawal-requests' => 'withdrawal_requests.php',
    'finalize-earnings'   => 'finalize_earnings.php',
];

$pageToInclude = null;
if (array_key_exists($currentPage, $availableAdminPages)) {
    $pageFileName = $availableAdminPages[$currentPage];
    $filePath = __DIR__ . '/pages/' . $pageFileName;
    if (file_exists($filePath)) {
        $pageToInclude = $filePath;
    }
}

// --- Render the Admin Layout ---

// Define a variable for admin assets path
define('ADMIN_ASSETS_URL', SITE_URL . '/admin/assets');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Dashboard | AffiliateBasic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="<?php echo ADMIN_ASSETS_URL; ?>/css/style.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include __DIR__ . '/templates/sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include __DIR__ . '/templates/topbar.php'; ?>
                <div class="container-fluid">
                    <?php
                    // Include the routed page or a 404 message
                    if ($pageToInclude) {
                        include $pageToInclude;
                    } else {
                        http_response_code(404);
                        echo '<h1 class="display-1">404</h1><h2>Page Not Found</h2><p>The page you are looking for does not exist in the admin area.</p>';
                    }
                    ?>
                </div>
            </div>
            <?php include __DIR__ . '/templates/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ADMIN_ASSETS_URL; ?>/js/script.js"></script>
</body>
</html>
```

#### `admin/assets/css/style.css`
```css
/* Basic Admin Styles */
body {
    background-color: #f8f9fc;
}

#wrapper {
    display: flex;
    width: 100%;
}

#sidebar-wrapper {
    min-height: 100vh;
    width: 250px;
    transition: margin .25s ease-out;
}

#sidebar-wrapper .sidebar-heading {
    padding: 0.875rem 1.25rem;
    font-size: 1.2rem;
}

#sidebar-wrapper .list-group {
    width: 250px;
}

#content-wrapper {
    overflow-x: hidden;
    width: 100%;
}

#page-top.sidebar-toggled #sidebar-wrapper {
    margin-left: -250px;
}

.topbar {
    height: 4.375rem;
}

.topbar .nav-item .nav-link {
    height: 4.375rem;
    display: flex;
    align-items: center;
    padding: 0 0.75rem;
}

.footer {
    padding: 2rem 0;
}
```

#### `admin/assets/js/script.js`
```javascript
// Basic Admin JS for sidebar toggling
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-toggled');
        });
    }
});
```

#### `admin/templates/sidebar.php`
```php
<?php
// Get the current page from the admin router
global $currentPage;
?>
<div id="sidebar-wrapper" class="bg-dark text-white">
    <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">Admin Panel</div>
    <div class="list-group list-group-flush my-3">
        <a href="<?php echo SITE_URL; ?>/admin/dashboard" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/products" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'products') ? 'active' : ''; ?>">
            <i class="bi bi-box-seam me-2"></i>Manage Products
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/orders" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'orders') ? 'active' : ''; ?>">
            <i class="bi bi-card-list me-2"></i>Manage Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/manage-affiliates" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'manage-affiliates') ? 'active' : ''; ?>">
            <i class="bi bi-people me-2"></i>Manage Affiliates
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/withdrawal-requests" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'withdrawal-requests') ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack me-2"></i>Withdrawal Requests
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/finalize-earnings" class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'finalize-earnings') ? 'active' : ''; ?>">
            <i class="bi bi-patch-check-fill me-2"></i>Finalize Earnings
        </a>
    </div>
</div>
```

#### `admin/templates/topbar.php`
```php
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <button id="sidebarToggle" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="bi bi-list"></i>
    </button>
    <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <i class="bi bi-person-circle fs-4"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile">
                    <i class="bi bi-person fa-sm fa-fw me-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right fa-sm fa-fw me-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo SITE_URL; ?>/logout-action" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo AffiliateBasic\Config\generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-primary">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
```

#### `admin/templates/footer.php`
```php
<footer class="footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; AffiliateBasic <?php echo date('Y'); ?></span>
        </div>
    </div>
</footer>
```

### 4. New Admin Pages

Move the content from your old `pages/admin_*.php` files into the new files in `admin/pages/`. **Crucially, remove the old `navbar.php` and `footer.php` includes from these files, as they are now handled by the admin front controller (`admin/index.php`).**

Here is the content for the new `dashboard.php` and an example of the migrated `products.php`.

#### `admin/pages/dashboard.php` (New Admin Dashboard)
```php
<?php
// admin/pages/dashboard.php
use function AffiliateBasic\Config\displayMessage;

global $pdo;
// You can fetch and display various stats here
?>
<h1 class="h3 mb-4 text-gray-800">Admin Dashboard</h1>
<?php echo displayMessage(); ?>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                           <?php
                           $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                           echo $stmt->fetchColumn();
                           ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people-fill fs-2 text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
```

#### `admin/pages/products.php` (Migrated from `pages/admin_products.php`)
```php
<?php
// admin/pages/products.php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;
use function AffiliateBasic\Core\Ecommerce\getAllProductsForAdmin;

require_once dirname(__DIR__, 2) . '/core/ecommerce/product_functions.php';

global $pdo;
$products = getAllProductsForAdmin($pdo);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Products</h1>
    <a href="<?php echo SITE_URL; ?>/admin/add-product" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add New Product</a>
</div>
<?php echo displayMessage(); ?>

<?php if (empty($products)): ?>
    <div class="alert alert-info">No products found. <a href="<?php echo SITE_URL; ?>/admin/add-product">Add the first product</a>.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Is Affiliate?</th>
                    <th>Bonus %</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product['stock_quantity']); ?></td>
                        <td><?php echo ((float) $product['affiliate_bonus_percentage'] > 0) ? 'Yes' : 'No'; ?></td>
                        <td><?php echo htmlspecialchars(number_format($product['affiliate_bonus_percentage'], 2)); ?>%</td>
                        <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($product['updated_at'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/admin/edit-product?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <form action="<?php echo SITE_URL; ?>/admin-product-delete-action" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
```

### 5. Final Steps

1.  **Migrate Other Admin Pages:** Follow the example for `products.php` to move the content of all your other `admin_*.php` files into their new, cleaner versions inside `admin/pages/`. Remember to update all links to use the new `/admin/...` path structure.
2.  **Update Public Navbar:** Modify `templates/navbar.php` to change the admin links in the user dropdown to point to the new admin dashboard.

    ```php
    // In templates/navbar.php, find this section:
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
        // Change this line:
        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
        <hr class="dropdown-divider">
    <?php endif; ?>
    ```

3.  **Clean Up:** Once everything is moved and working, you can delete the old `pages/admin_*.php` files.

This restructuring creates a clean separation between your public-facing site and your admin panel, making it more organized and secure.
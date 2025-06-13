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
    'dashboard' => 'dashboard.php',
    'products' => 'products.php',
    'add-product' => 'add_product.php',
    'edit-product' => 'edit_product.php',
    'orders' => 'orders.php',
    'order-detail' => 'order_detail.php',
    'manage-affiliates' => 'manage_affiliates.php',
    'withdrawal-requests' => 'withdrawal_requests.php',
    'finalize-earnings' => 'finalize_earnings.php',
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
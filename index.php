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
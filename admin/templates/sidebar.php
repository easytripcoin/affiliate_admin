<?php
// Get the current page from the admin router
global $currentPage;
?>
<div id="sidebar-wrapper" class="bg-dark text-white">
    <div class="sidebar-heading text-center py-4 fs-4 fw-bold text-uppercase border-bottom">Admin Panel</div>
    <div class="list-group list-group-flush my-3">
        <a href="<?php echo SITE_URL; ?>/admin/dashboard"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/products"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'products') ? 'active' : ''; ?>">
            <i class="bi bi-box-seam me-2"></i>Manage Products
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/orders"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'orders') ? 'active' : ''; ?>">
            <i class="bi bi-card-list me-2"></i>Manage Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/manage-affiliates"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'manage-affiliates') ? 'active' : ''; ?>">
            <i class="bi bi-people me-2"></i>Manage Affiliates
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/withdrawal-requests"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'withdrawal-requests') ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack me-2"></i>Withdrawal Requests
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/finalize-earnings"
            class="list-group-item list-group-item-action bg-dark text-white <?php echo ($currentPage == 'finalize-earnings') ? 'active' : ''; ?>">
            <i class="bi bi-patch-check-fill me-2"></i>Finalize Earnings
        </a>
    </div>
</div>
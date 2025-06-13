<?php
// admin/pages/manage_affiliates.php
use function AffiliateBasic\Config\displayMessage;
use function AffiliateBasic\Config\generateCSRFToken;

require_once PROJECT_ROOT_PATH . '/core/affiliate/affiliate_functions.php';

global $pdo;
$stmtUsers = $pdo->query("SELECT id, username, email, is_affiliate, user_affiliate_code, affiliate_balance FROM users ORDER BY username ASC");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Affiliates</h1>
</div>
<?php echo displayMessage(); ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Is Affiliate?</th>
                        <th>Affiliate Code</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <?php if ($user['is_affiliate']): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo htmlspecialchars($user['user_affiliate_code'] ?? 'N/A'); ?></code></td>
                            <td class="text-end">
                                $<?php echo htmlspecialchars(number_format($user['affiliate_balance'], 2)); ?></td>
                            <td class="text-center">
                                <form action="<?php echo SITE_URL; ?>/admin-manage-affiliates-action" method="post"
                                    class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <?php if ($user['is_affiliate']): ?>
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                                    <?php else: ?>
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/config/app.php';
requireLogin();
$user = fetchOne(
    "SELECT u.*, r.role_name, d.department_name FROM users u INNER JOIN roles r ON r.id=u.role_id LEFT JOIN departments d ON d.id=u.department_id WHERE u.id=?",
    [currentUserId()],
    'i'
);
$pageTitle = 'Profile - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Profile</h2>
<table class="detail-table" cellpadding="4" cellspacing="0">
    <tr><td class="label-cell">Employee ID</td><td><?php echo e($user['employee_id'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Full Name</td><td><?php echo e($user['full_name'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Username</td><td><?php echo e($user['username'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Role</td><td><?php echo e($user['role_name'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Department</td><td><?php echo e($user['department_name'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Designation</td><td><?php echo e($user['designation'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Mobile</td><td><?php echo e($user['mobile'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Email</td><td><?php echo e($user['email'] ?? ''); ?></td></tr>
    <tr><td class="label-cell">Last Login</td><td><?php echo e(formatDateTime($user['last_login'] ?? '')); ?></td></tr>
</table>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>change_password.php">Change Password</a> <a class="plain-link-button" href="<?php echo BASE_URL; ?>dashboard.php">Back to Dashboard</a></p>
<?php require __DIR__ . '/includes/footer.php'; ?>

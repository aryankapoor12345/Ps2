<?php
require_once __DIR__ . '/config/app.php';
requireLogin();
$errors = [];
if (isPost()) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    $row = fetchOne("SELECT password_hash FROM users WHERE id=?", [currentUserId()], 'i');
    if (!$row || !password_verify($current, $row['password_hash'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if (strlen($new) < 6) {
        $errors[] = 'New password should be at least 6 characters.';
    }
    if ($new !== $confirm) {
        $errors[] = 'Confirm password does not match.';
    }
    if (!$errors) {
        updateRecord("UPDATE users SET password_hash=?, updated_at=NOW() WHERE id=?", [password_hash($new, PASSWORD_DEFAULT), currentUserId()], 'si');
        logAudit('Password changed', 'users', currentUserId());
        setFlash('success', 'Password changed successfully.');
        redirect('profile.php');
    }
}
$pageTitle = 'Change Password - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Change Password</h2>
<?php if ($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<form method="post" class="validate-form">
<?php echo csrfField(); ?>
<table class="form-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">Current Password <span class="required">*</span></td><td><input type="password" name="current_password" class="text-input" data-required="1"></td></tr>
<tr><td class="label-cell">New Password <span class="required">*</span></td><td><input type="password" name="new_password" class="text-input" data-required="1"></td></tr>
<tr><td class="label-cell">Confirm New Password <span class="required">*</span></td><td><input type="password" name="confirm_password" class="text-input" data-required="1"></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Save" class="save-button"> <a class="plain-link-button" href="<?php echo BASE_URL; ?>profile.php">Back</a></td></tr>
</table>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>

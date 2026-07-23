<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireRole([ROLE_ADMIN, ROLE_SAFETY_ADMIN]);
$allowed = ['total_safety_observations_display','total_safety_suggestions_display','total_reported_near_miss_display','portal_title','plant_name','max_upload_size_text'];
$action = sanitizeInput($_GET['action'] ?? 'list');
$key = sanitizeInput($_GET['key'] ?? '');
$errors = [];
$old = ['setting_key'=>$key, 'setting_value'=>getSetting($key, '')];
if (isPost() && in_array($action, ['add','edit'], true)) {
    $old = sanitizeInput($_POST);
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) $errors[]='Invalid request token.';
    if (!in_array($old['setting_key'] ?? '', $allowed, true)) $errors[]='This setting key is not allowed.';
    if (!$errors) {
        setSetting($old['setting_key'], $old['setting_value'] ?? '');
        logAudit('Updated app setting '.$old['setting_key'], 'app_settings', null);
        setFlash('success', 'Setting saved successfully.');
        redirect('modules/admin/settings.php');
    }
}
$pageTitle = 'Settings - ' . SITE_NAME;
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h2>Settings</h2>
<?php if($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<?php if(in_array($action, ['add','edit'], true)): ?>
<form method="post"><?php echo csrfField(); ?><table class="form-table"><tr><td class="label-cell">Setting Key</td><td><select name="setting_key"><?php foreach($allowed as $item): ?><option value="<?php echo e($item); ?>" <?php echo ($old['setting_key']??'')===$item?'selected':''; ?>><?php echo e($item); ?></option><?php endforeach; ?></select></td></tr><tr><td class="label-cell">Setting Value</td><td><textarea name="setting_value" rows="4"><?php echo e($old['setting_value'] ?? ''); ?></textarea></td></tr><tr><td>&nbsp;</td><td><input type="submit" value="Save" class="save-button"> <a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/admin/settings.php">Back</a></td></tr></table></form>
<?php else: $rows=fetchAll("SELECT * FROM app_settings ORDER BY setting_key"); ?>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/admin/settings.php?action=add">Add / Update Setting</a></p>
<table class="data-table"><tr><th>Setting Key</th><th>Setting Value</th><th>Updated At</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?php echo e($r['setting_key']); ?></td><td><?php echo e($r['setting_value']); ?></td><td><?php echo e(formatDateTime($r['updated_at'])); ?></td><td><?php if(in_array($r['setting_key'],$allowed,true)): ?><a href="<?php echo BASE_URL; ?>modules/admin/settings.php?action=edit&key=<?php echo e($r['setting_key']); ?>">Edit</a><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php endif; ?>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

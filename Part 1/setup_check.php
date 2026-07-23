<?php
require_once __DIR__ . '/config/app.php';
$requiredTables = ['roles','departments','users','safety_zones','zone_leaders','zone_eic','observation_categories','observation_statuses','safety_observations','observation_actions','safety_suggestions','near_miss_reports','notifications','audit_logs','app_settings'];
$uploadFolders = ['uploads','uploads/observations','uploads/actions','uploads/suggestions','uploads/near_miss'];
$pageTitle = 'Setup Check - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Setup Check</h2>
<?php if (!db()): ?><div class="notice-box">Database tables are missing. Import database/ntpc_safety_portal.sql using phpMyAdmin.</div><?php endif; ?>
<table class="detail-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">PHP Version</td><td><?php echo e(PHP_VERSION); ?></td></tr>
<tr><td class="label-cell">mysqli extension loaded</td><td><?php echo extension_loaded('mysqli') ? 'Yes' : 'No'; ?></td></tr>
<tr><td class="label-cell">session status</td><td><?php echo e(session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active'); ?></td></tr>
<tr><td class="label-cell">database connection status</td><td><?php echo db() ? 'Connected' : 'Not Connected'; ?></td></tr>
<tr><td class="label-cell">database name</td><td><?php echo e(DB_NAME); ?></td></tr>
<tr><td class="label-cell">BASE_URL</td><td><?php echo e(BASE_URL); ?></td></tr>
<tr><td class="label-cell">max upload size</td><td><?php echo e(MAX_UPLOAD_SIZE . ' bytes'); ?></td></tr>
<tr><td class="label-cell">timezone</td><td><?php echo e(date_default_timezone_get()); ?></td></tr>
</table>
<h3>Required Tables</h3>
<table class="data-table" cellpadding="3" cellspacing="0"><tr><th>Table</th><th>Status</th></tr>
<?php foreach ($requiredTables as $table): ?><tr><td><?php echo e($table); ?></td><td><?php echo tableExists($table) ? 'Present' : 'Missing'; ?></td></tr><?php endforeach; ?>
</table>
<h3>Upload Folders</h3>
<table class="data-table" cellpadding="3" cellspacing="0"><tr><th>Folder</th><th>Status</th></tr>
<?php foreach ($uploadFolders as $folder): $path=__DIR__ . '/' . $folder; ?><tr><td><?php echo e($folder); ?></td><td><?php echo is_dir($path) && is_writable($path) ? 'Writable' : 'Not Writable'; ?></td></tr><?php endforeach; ?>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>

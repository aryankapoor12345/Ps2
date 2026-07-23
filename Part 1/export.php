<?php
require_once __DIR__ . '/config/app.php';
requireRole([ROLE_ADMIN, ROLE_SAFETY_ADMIN]);
$pageTitle = 'Export CSV - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Export CSV</h2>
<table class="data-table" cellpadding="4" cellspacing="0">
<tr><th>Report</th><th>Download</th></tr>
<tr><td>Zonewise Observations CSV</td><td><a href="<?php echo BASE_URL; ?>modules/reports/export_zonewise_csv.php">Download</a></td></tr>
<tr><td>Pending Observations CSV</td><td><a href="<?php echo BASE_URL; ?>modules/reports/export_pending_csv.php">Download</a></td></tr>
<tr><td>Department Dues CSV</td><td><a href="<?php echo BASE_URL; ?>modules/reports/export_department_dues_csv.php">Download</a></td></tr>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>

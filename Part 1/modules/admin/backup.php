<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireRole([ROLE_ADMIN]);
function backupSqlValue($value)
{
    if ($value === null) return 'NULL';
    return "'" . db()->real_escape_string((string)$value) . "'";
}
if (($_GET['action'] ?? '') === 'download') {
    $tables = ['roles','departments','users','safety_zones','zone_leaders','zone_eic','observation_categories','observation_statuses','safety_observations','observation_actions','safety_suggestions','near_miss_reports','notifications','audit_logs','app_settings'];
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="ntpc_safety_portal_backup_' . date('Ymd_His') . '.sql"');
    echo "CREATE DATABASE IF NOT EXISTS ntpc_safety_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\nUSE ntpc_safety_portal;\nSET FOREIGN_KEY_CHECKS=0;\n";
    foreach ($tables as $table) {
        if (!tableExists($table)) continue;
        $rows = fetchAll("SELECT * FROM `$table`");
        foreach ($rows as $row) {
            $cols = array_map(function($c){ return "`$c`"; }, array_keys($row));
            $vals = array_map('backupSqlValue', array_values($row));
            echo "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
    }
    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit;
}
$pageTitle = 'Backup - ' . SITE_NAME;
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h2>Database Backup</h2>
<div class="notice-box">This backup exports table data only. Use database/ntpc_safety_portal.sql for full schema recreation.</div>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/admin/backup.php?action=download">Download SQL Backup</a></p>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

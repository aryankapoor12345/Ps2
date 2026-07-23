<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireLogin();
$rows = getDepartmentDueSummary(sanitizeInput($_GET));
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="department_dues_' . date('Ymd') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Department','Total Pending','Open','Under Review','Assigned','Action Taken','Oldest Pending Date']);
foreach ($rows as $r) {
    fputcsv($out, [$r['department_name'], $r['total_pending'] ?: 0, $r['open_count'] ?: 0, $r['review_count'] ?: 0, $r['assigned_count'] ?: 0, $r['action_count'] ?: 0, $r['oldest_pending_date']]);
}
exit;

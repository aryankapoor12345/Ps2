<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireLogin();
$rows = getPendingObservations(sanitizeInput($_GET), null, null);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="pending_observations_' . date('Ymd') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Observation No','Date','Zone','Department','Location','Description','Risk','Status','Target Date','Days Pending','Reported By']);
foreach ($rows as $r) {
    $reporter = fetchOne("SELECT full_name FROM users WHERE id=?", [(int)$r['reported_by']], 'i');
    fputcsv($out, [$r['observation_no'], $r['observation_date'], $r['short_name'], $r['department_name'], $r['specific_area_location'], $r['observation_description'], $r['risk_level'], $r['status_name'], $r['target_closing_date'], daysPending($r['observation_date']), $reporter['full_name'] ?? '']);
}
exit;

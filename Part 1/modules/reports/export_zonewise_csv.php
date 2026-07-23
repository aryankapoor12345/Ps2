<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireLogin();
$params=[]; $types=''; $where=[]; appendObservationAccessWhere($where, $params, $types, 'so');
$whereSql = $where ? ' AND ' . implode(' AND ', $where) : '';
$zoneFilter = '1=1'; $zoneParams=[]; $zoneTypes='';
if (isZoneLeaderRole()) { $zoneFilter='sz.id IN (SELECT zone_id FROM zone_leaders WHERE user_id=?)'; $zoneParams[] = currentUserId(); $zoneTypes='i'; }
$rows = fetchAll(
    "SELECT sz.zone_code, sz.zone_name,
            COUNT(so.id) total,
            SUM(os.status_name='Open') open_count,
            SUM(os.status_name='Under Review') review_count,
            SUM(os.status_name='Assigned') assigned_count,
            SUM(os.status_name='Action Taken') action_count,
            SUM(os.status_name='Closed') closed_count,
            SUM(os.status_name='Rejected') rejected_count,
            (SELECT COUNT(*) FROM near_miss_reports n WHERE n.zone_id=sz.id) near_miss_count,
            (SELECT COUNT(*) FROM safety_suggestions s WHERE s.zone_id=sz.id) suggestion_count
     FROM safety_zones sz
     LEFT JOIN safety_observations so ON so.zone_id=sz.id $whereSql
     LEFT JOIN observation_statuses os ON os.id=so.status_id
     WHERE $zoneFilter
     GROUP BY sz.id ORDER BY sz.display_order",
    array_merge($params, $zoneParams),
    $types . $zoneTypes
);
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="zonewise_observations_' . date('Ymd') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Zone Code','Zone Name','Total Observations','Open','Under Review','Assigned','Action Taken','Closed','Rejected','Near Miss','Suggestions']);
foreach ($rows as $r) {
    fputcsv($out, [$r['zone_code'],$r['zone_name'],$r['total'] ?: 0,$r['open_count'] ?: 0,$r['review_count'] ?: 0,$r['assigned_count'] ?: 0,$r['action_count'] ?: 0,$r['closed_count'] ?: 0,$r['rejected_count'] ?: 0,$r['near_miss_count'] ?: 0,$r['suggestion_count'] ?: 0]);
}
exit;

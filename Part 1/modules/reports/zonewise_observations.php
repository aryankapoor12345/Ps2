<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$filters=sanitizeInput($_GET); $departments=getAllDepartments(); $where=[];$params=[];$types='';
appendObservationAccessWhere($where,$params,$types,'so');
if(!empty($filters['from_date'])){$where[]='so.observation_date>=?';$params[]=$filters['from_date'];$types.='s';}
if(!empty($filters['to_date'])){$where[]='so.observation_date<=?';$params[]=$filters['to_date'];$types.='s';}
if(!empty($filters['department_id'])){$where[]='so.department_id=?';$params[]=(int)$filters['department_id'];$types.='i';}
$whereSql=$where?' AND '.implode(' AND ',$where):'';
$zoneFilter='1=1'; $zoneParams=[]; $zoneTypes='';
if(isZoneLeaderRole()){ $zoneFilter='sz.id IN (SELECT zone_id FROM zone_leaders WHERE user_id=?)'; $zoneParams[] = currentUserId(); $zoneTypes='i'; }
$rows=fetchAll("SELECT sz.id, sz.short_name,
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
GROUP BY sz.id ORDER BY sz.display_order",array_merge($params,$zoneParams),$types.$zoneTypes);
$max=1; foreach($rows as $r){$max=max($max,(int)$r['total']);}
$pageTitle='ZoneWise Observations - '.SITE_NAME; require __DIR__.'/../../includes/header.php';
?>
<h2>ZoneWise Observations</h2>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/reports/export_zonewise_csv.php">Export CSV</a></p>
<form method="get"><table class="form-table filter-table"><tr><td>From Date</td><td><input type="date" name="from_date" value="<?php echo e($filters['from_date']??''); ?>" class="date-input"></td><td>To Date</td><td><input type="date" name="to_date" value="<?php echo e($filters['to_date']??''); ?>" class="date-input"></td></tr><tr><td>Department</td><td><select name="department_id"><option value="">All</option><?php foreach($departments as $d): ?><option value="<?php echo e($d['id']); ?>" <?php echo (int)($filters['department_id']??0)===(int)$d['id']?'selected':''; ?>><?php echo e($d['department_name']); ?></option><?php endforeach; ?></select></td><td colspan="2"><input type="submit" value="Search" class="save-button"> <a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/reports/zonewise_observations.php">Reset</a></td></tr></table></form>
<table class="data-table"><tr><th>S.No.</th><th>Zone</th><th>Total Observations</th><th>Open</th><th>Under Review</th><th>Assigned</th><th>Action Taken</th><th>Closed</th><th>Rejected</th><th>Near Miss</th><th>Suggestions</th></tr>
<?php foreach($rows as $i=>$r): ?><tr><td><?php echo e($i+1); ?></td><td><?php echo e($r['short_name']); ?></td><td><?php echo e($r['total']); ?></td><td><?php echo e($r['open_count']??0); ?></td><td><?php echo e($r['review_count']??0); ?></td><td><?php echo e($r['assigned_count']??0); ?></td><td><?php echo e($r['action_count']??0); ?></td><td><?php echo e($r['closed_count']??0); ?></td><td><?php echo e($r['rejected_count']??0); ?></td><td><?php echo e($r['near_miss_count']); ?></td><td><?php echo e($r['suggestion_count']); ?></td></tr><?php endforeach; ?>
</table><h3>Zone Chart</h3><table class="chart-table"><?php foreach($rows as $r): $p=(int)round(((int)$r['total']/$max)*100); ?><tr><td class="bar-label"><?php echo e($r['short_name']); ?></td><td class="bar-cell"><div class="dashboard-bar-track"><div class="dashboard-bar-fill" style="width:<?php echo $p; ?>%;"></div></div></td><td class="bar-value"><?php echo e($r['total']); ?></td></tr><?php endforeach; ?></table><p><button class="plain-button" onclick="window.print()">Print</button></p>
<?php require __DIR__.'/../../includes/footer.php'; ?>

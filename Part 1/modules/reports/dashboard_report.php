<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$params=[];$types='';$access=buildObservationAccessWhere('so',$params,$types);
$summary=fetchOne("SELECT COUNT(*) total_obs, SUM(os.status_name='Open') open_obs, SUM(os.status_name='Closed') closed_obs, SUM(so.risk_level='Critical') critical_obs, SUM(so.risk_level='High') high_obs FROM safety_observations so INNER JOIN observation_statuses os ON os.id=so.status_id WHERE $access",$params,$types);
$near=fetchOne("SELECT COUNT(*) total FROM near_miss_reports"); $sug=fetchOne("SELECT COUNT(*) total FROM safety_suggestions");
$statusRows=fetchAll("SELECT os.status_name, COUNT(so.id) total FROM observation_statuses os LEFT JOIN safety_observations so ON so.status_id=os.id AND $access GROUP BY os.id ORDER BY os.id",$params,$types);
$riskRows=fetchAll("SELECT so.risk_level, COUNT(*) total FROM safety_observations so WHERE $access GROUP BY so.risk_level ORDER BY FIELD(so.risk_level,'Low','Medium','High','Critical')",$params,$types);
$accRows=fetchAll("SELECT so.accident_type, COUNT(*) total FROM safety_observations so WHERE $access GROUP BY so.accident_type ORDER BY FIELD(so.accident_type,'None','Near Miss','Minor','Major','Fatal')",$params,$types);
$pageTitle='Dashboard Report - '.SITE_NAME; require __DIR__.'/../../includes/header.php';
?>
<h2>Dashboard Report</h2>
<table class="summary-table" cellpadding="4" cellspacing="0"><tr><th>Total Observations</th><th>Open Observations</th><th>Closed Observations</th><th>Near Miss Reports</th><th>Suggestions</th><th>Critical Risk</th><th>High Risk</th></tr>
<tr><td><?php echo e($summary['total_obs']??0); ?></td><td><?php echo e($summary['open_obs']??0); ?></td><td><?php echo e($summary['closed_obs']??0); ?></td><td><?php echo e($near['total']??0); ?></td><td><?php echo e($sug['total']??0); ?></td><td><?php echo e($summary['critical_obs']??0); ?></td><td><?php echo e($summary['high_obs']??0); ?></td></tr></table>
<h3>Status-wise Observation Count</h3><table class="data-table"><tr><th>Status</th><th>Total</th></tr><?php foreach($statusRows as $r): ?><tr><td><?php echo e($r['status_name']); ?></td><td><?php echo e($r['total']); ?></td></tr><?php endforeach; ?></table>
<h3>Risk-wise Observation Count</h3><table class="data-table"><tr><th>Risk</th><th>Total</th></tr><?php foreach($riskRows as $r): ?><tr><td><?php echo e($r['risk_level']); ?></td><td><?php echo e($r['total']); ?></td></tr><?php endforeach; ?></table>
<h3>Accident-type-wise Count</h3><table class="data-table"><tr><th>Accident Type</th><th>Total</th></tr><?php foreach($accRows as $r): ?><tr><td><?php echo e($r['accident_type']); ?></td><td><?php echo e($r['total']); ?></td></tr><?php endforeach; ?></table>
<p><button type="button" class="plain-button" onclick="window.print()">Print</button></p>
<?php require __DIR__.'/../../includes/footer.php'; ?>

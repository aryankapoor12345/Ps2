<?php
require_once __DIR__ . '/config/app.php';
requireLogin();

$pageTitle = 'Dashboard - ' . SITE_NAME;
$counts = getObservationCounts();
$zoneRows = getZonewiseCounts();
$user = currentUser();
$maxZone = 1;
foreach ($zoneRows as $zoneRow) {
    $maxZone = max($maxZone, (int)$zoneRow['total']);
}
require __DIR__ . '/includes/header.php';
?>
<h2>Welcome - <?php echo e($user['full_name'] ?? ''); ?></h2>
<?php if (!db()): ?>
    <div class="notice-box">Database connection is not available. Import database/ntpc_safety_portal.sql in phpMyAdmin and verify local settings.</div>
<?php else: ?>
    <table class="dashboard-count-table" cellpadding="4" cellspacing="0">
        <tr>
            <td><span><?php echo e(getSetting('total_safety_observations_display', $counts['total'])); ?></span><br>Safety Observations</td>
            <td><span><?php echo e(getSetting('total_safety_suggestions_display', $counts['suggestions'])); ?></span><br>Safety Suggestions</td>
            <td><span><?php echo e(getSetting('total_reported_near_miss_display', $counts['near_miss'])); ?></span><br>Reported Nearmiss</td>
        </tr>
    </table>

    <h3>Safety Observations</h3>
    <table class="chart-table" cellpadding="2" cellspacing="0">
        <?php foreach ($zoneRows as $row): ?>
            <?php $percent = $maxZone > 0 ? (int)round(((int)$row['total'] / $maxZone) * 100) : 0; ?>
            <tr>
                <td class="bar-label"><?php echo e($row['short_name']); ?></td>
                <td class="bar-cell">
                    <div class="dashboard-bar-track">
                        <div class="dashboard-bar-fill" style="width: <?php echo $percent; ?>%;"></div>
                    </div>
                </td>
                <td class="bar-value"><?php echo e($row['total']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

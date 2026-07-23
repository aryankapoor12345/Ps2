<?php
require_once __DIR__ . '/config/app.php';
requireLogin();
$q = sanitizeInput($_GET['q'] ?? '');
$type = sanitizeInput($_GET['type'] ?? 'All');
$results = [];
if ($q !== '') {
    if ($type === 'All' || $type === 'Safety Observation') {
        $params = [$q]; $types = 's'; $access = buildObservationAccessWhere('so', $params, $types);
        $rows = fetchAll(
            "SELECT 'Safety Observation' AS rec_type, so.id, so.observation_no AS rec_no, so.observation_date AS rec_date, sz.short_name, os.status_name
             FROM safety_observations so INNER JOIN safety_zones sz ON sz.id=so.zone_id INNER JOIN observation_statuses os ON os.id=so.status_id
             WHERE so.observation_no=? AND $access",
            $params,
            $types
        );
        foreach ($rows as $row) { $row['link'] = BASE_URL . 'modules/observations/view.php?id=' . $row['id']; $results[] = $row; }
    }
    if ($type === 'All' || $type === 'Near Miss') {
        $where = ['nmr.near_miss_no=?']; $params = [$q]; $types = 's';
        if (isZoneLeaderRole()) { $where[] = 'nmr.zone_id IN (SELECT zone_id FROM zone_leaders WHERE user_id=?)'; $params[] = currentUserId(); $types .= 'i'; }
        elseif (isEicRole()) { $where[] = 'nmr.zone_id IN (SELECT zone_id FROM zone_eic WHERE user_id=? AND is_active=1)'; $params[] = currentUserId(); $types .= 'i'; }
        elseif (!isAdminRole() && !isSafetyAdminRole()) { $where[] = 'nmr.reported_by=?'; $params[] = currentUserId(); $types .= 'i'; }
        $rows = fetchAll("SELECT 'Near Miss' AS rec_type, nmr.id, nmr.near_miss_no AS rec_no, nmr.incident_date AS rec_date, sz.short_name, os.status_name FROM near_miss_reports nmr INNER JOIN safety_zones sz ON sz.id=nmr.zone_id INNER JOIN observation_statuses os ON os.id=nmr.status_id WHERE " . implode(' AND ', $where), $params, $types);
        foreach ($rows as $row) { $row['link'] = BASE_URL . 'modules/near_miss/view.php?id=' . $row['id']; $results[] = $row; }
    }
    if ($type === 'All' || $type === 'Suggestion') {
        $where = ['ss.suggestion_no=?']; $params = [$q]; $types = 's';
        if (isZoneLeaderRole()) { $where[] = '(ss.submitted_by=? OR ss.zone_id IN (SELECT zone_id FROM zone_leaders WHERE user_id=?))'; $params[] = currentUserId(); $params[] = currentUserId(); $types .= 'ii'; }
        elseif (!isAdminRole() && !isSafetyAdminRole()) { $where[] = 'ss.submitted_by=?'; $params[] = currentUserId(); $types .= 'i'; }
        $rows = fetchAll("SELECT 'Suggestion' AS rec_type, ss.id, ss.suggestion_no AS rec_no, ss.submitted_date AS rec_date, sz.short_name, os.status_name FROM safety_suggestions ss LEFT JOIN safety_zones sz ON sz.id=ss.zone_id INNER JOIN observation_statuses os ON os.id=ss.status_id WHERE " . implode(' AND ', $where), $params, $types);
        foreach ($rows as $row) { $row['link'] = BASE_URL . 'modules/suggestions/view.php?id=' . $row['id']; $results[] = $row; }
    }
}
$pageTitle = 'Search Records - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Search Records</h2>
<form method="get">
<table class="form-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">Search Number</td><td><input type="text" name="q" value="<?php echo e($q); ?>" class="text-input"></td></tr>
<tr><td class="label-cell">Type</td><td><select name="type"><?php foreach (['All','Safety Observation','Near Miss','Suggestion'] as $option): ?><option value="<?php echo e($option); ?>" <?php echo $type===$option?'selected':''; ?>><?php echo e($option); ?></option><?php endforeach; ?></select></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Search" class="save-button"></td></tr>
</table>
</form>
<?php if ($q !== ''): ?>
<table class="data-table" cellpadding="3" cellspacing="0"><tr><th>Type</th><th>Number</th><th>Date</th><th>Zone</th><th>Status</th><th>Link</th></tr>
<?php if (!$results): ?><tr><td colspan="6">No records found.</td></tr><?php endif; ?>
<?php foreach ($results as $row): ?><tr><td><?php echo e($row['rec_type']); ?></td><td><?php echo e($row['rec_no']); ?></td><td><?php echo e(formatDate($row['rec_date'])); ?></td><td><?php echo e($row['short_name']); ?></td><td><?php echo e($row['status_name']); ?></td><td><a href="<?php echo e($row['link']); ?>">Open</a></td></tr><?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

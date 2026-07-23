<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
$r = fetchOne("SELECT nmr.*, sz.short_name, sz.zone_name, d.department_name, os.status_name, u.full_name AS reporter_name, u.employee_id FROM near_miss_reports nmr INNER JOIN safety_zones sz ON sz.id=nmr.zone_id INNER JOIN departments d ON d.id=nmr.department_id INNER JOIN observation_statuses os ON os.id=nmr.status_id INNER JOIN users u ON u.id=nmr.reported_by WHERE nmr.id=?", [$id], 'i');
if (!$r) { setFlash('error','Near miss record not found.'); redirect('modules/near_miss/list.php'); }
$can = isAdminRole() || isSafetyAdminRole() || (isZoneLeaderRole() && in_array((int)$r['zone_id'], getUserZoneIds(currentUserId()), true)) || (isEicRole() && fetchOne("SELECT id FROM zone_eic WHERE zone_id=? AND user_id=? AND is_active=1", [(int)$r['zone_id'], currentUserId()], 'ii')) || (int)$r['reported_by'] === currentUserId();
if (!$can) { setFlash('error','You are not authorized to access this page.'); redirect('dashboard.php'); }
$pageTitle='View Near Miss - '.SITE_NAME; require __DIR__.'/../../includes/header.php';
?>
<h2>View Near Miss</h2>
<table class="detail-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">Near Miss No</td><td><?php echo e($r['near_miss_no']); ?></td></tr>
<tr><td class="label-cell">Status</td><td><?php echo e($r['status_name']); ?></td></tr>
<tr><td class="label-cell">Reported By</td><td><?php echo e($r['employee_id'].' '.$r['reporter_name']); ?></td></tr>
<tr><td class="label-cell">Zone</td><td><?php echo e($r['zone_name']); ?></td></tr>
<tr><td class="label-cell">Department</td><td><?php echo e($r['department_name']); ?></td></tr>
<tr><td class="label-cell">Incident Location</td><td><?php echo e($r['incident_location']); ?></td></tr>
<tr><td class="label-cell">Incident Date</td><td><?php echo e(formatDate($r['incident_date'])); ?></td></tr>
<tr><td class="label-cell">Incident Time</td><td><?php echo e($r['incident_time']); ?></td></tr>
<tr><td class="label-cell">Incident Description</td><td><?php echo nl2br(e($r['incident_description'])); ?></td></tr>
<tr><td class="label-cell">Possible Consequence</td><td><?php echo nl2br(e($r['possible_consequence'])); ?></td></tr>
<tr><td class="label-cell">Preventive Action</td><td><?php echo nl2br(e($r['preventive_action'])); ?></td></tr>
<tr><td class="label-cell">Attachment</td><td><?php if($r['attachment_path']): ?><a target="_blank" href="<?php echo BASE_URL.e($r['attachment_path']); ?>">View Uploaded Document</a><?php endif; ?></td></tr>
<tr><td class="label-cell">Created At</td><td><?php echo e(formatDateTime($r['created_at'])); ?></td></tr>
</table>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/near_miss/list.php">Back to List</a> <button type="button" class="plain-button" onclick="window.print()">Print</button></p>
<?php require __DIR__.'/../../includes/footer.php'; ?>

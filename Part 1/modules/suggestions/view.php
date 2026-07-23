<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$id=(int)($_GET['id']??0);
$r=fetchOne("SELECT ss.*, sz.short_name, sz.zone_name, d.department_name, os.status_name, u.full_name AS submitter_name, u.employee_id FROM safety_suggestions ss LEFT JOIN safety_zones sz ON sz.id=ss.zone_id LEFT JOIN departments d ON d.id=ss.department_id INNER JOIN observation_statuses os ON os.id=ss.status_id INNER JOIN users u ON u.id=ss.submitted_by WHERE ss.id=?",[$id],'i');
if(!$r){setFlash('error','Suggestion not found.');redirect('modules/suggestions/list.php');}
$can=isAdminRole()||isSafetyAdminRole()||(int)$r['submitted_by']===currentUserId()||(isZoneLeaderRole()&&$r['zone_id']&&in_array((int)$r['zone_id'],getUserZoneIds(currentUserId()),true));
if(!$can){setFlash('error','You are not authorized to access this page.');redirect('dashboard.php');}
$pageTitle='View Suggestion - '.SITE_NAME; require __DIR__.'/../../includes/header.php';
?>
<h2>View Suggestion</h2>
<table class="detail-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">Suggestion No</td><td><?php echo e($r['suggestion_no']); ?></td></tr>
<tr><td class="label-cell">Status</td><td><?php echo e($r['status_name']); ?></td></tr>
<tr><td class="label-cell">Submitted By</td><td><?php echo e($r['employee_id'].' '.$r['submitter_name']); ?></td></tr>
<tr><td class="label-cell">Zone</td><td><?php echo e($r['zone_name']); ?></td></tr>
<tr><td class="label-cell">Department</td><td><?php echo e($r['department_name']); ?></td></tr>
<tr><td class="label-cell">Suggestion Title</td><td><?php echo e($r['suggestion_title']); ?></td></tr>
<tr><td class="label-cell">Suggestion Description</td><td><?php echo nl2br(e($r['suggestion_description'])); ?></td></tr>
<tr><td class="label-cell">Expected Benefit</td><td><?php echo nl2br(e($r['expected_benefit'])); ?></td></tr>
<tr><td class="label-cell">Submitted Date</td><td><?php echo e(formatDate($r['submitted_date'])); ?></td></tr>
<tr><td class="label-cell">Attachment</td><td><?php if($r['attachment_path']): ?><a target="_blank" href="<?php echo BASE_URL.e($r['attachment_path']); ?>">View Uploaded Document</a><?php endif; ?></td></tr>
<tr><td class="label-cell">Created At</td><td><?php echo e(formatDateTime($r['created_at'])); ?></td></tr>
</table>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/suggestions/list.php">Back to List</a> <button type="button" class="plain-button" onclick="window.print()">Print</button></p>
<?php require __DIR__.'/../../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'Invalid observation selected.');
    redirect('modules/observations/list.php');
}

$observation = getObservationById($id);
if (!$observation || !userCanAccessObservation($observation)) {
    setFlash('error', 'Observation not found or access denied.');
    redirect('modules/observations/list.php');
}

$actions = fetchAll(
    "SELECT oa.*, u.full_name AS action_by_name, old.status_name AS old_status_name, new.status_name AS new_status_name
     FROM observation_actions oa
     INNER JOIN users u ON u.id = oa.action_by
     LEFT JOIN observation_statuses old ON old.id = oa.old_status_id
     INNER JOIN observation_statuses new ON new.id = oa.new_status_id
     WHERE oa.observation_id = ?
     ORDER BY oa.action_date DESC, oa.id DESC",
    [$id],
    'i'
);

$pageTitle = 'View Observation - ' . SITE_NAME;
require __DIR__ . '/../../includes/header.php';
?>
<h2>View Observation</h2>
<table class="detail-table" cellpadding="4" cellspacing="0">
    <tr><td class="label-cell">Observation No</td><td><?php echo e($observation['observation_no']); ?></td></tr>
    <tr><td class="label-cell">Status</td><td><?php echo e($observation['status_name']); ?></td></tr>
    <tr><td class="label-cell">Reported By</td><td><?php echo e($observation['reporter_name']); ?></td></tr>
    <tr><td class="label-cell">Employee ID</td><td><?php echo e($observation['reporter_employee_id']); ?></td></tr>
    <tr><td class="label-cell">Zone</td><td><?php echo e($observation['zone_name']); ?><br><?php echo e($observation['short_name']); ?></td></tr>
    <tr><td class="label-cell">Zone Leaders</td><td><?php echo e(getZoneLeadersText((int)$observation['zone_id'])); ?></td></tr>
    <tr><td class="label-cell">EIC</td><td><?php echo e($observation['eic_name'] ?: ''); ?></td></tr>
    <tr><td class="label-cell">Department</td><td><?php echo e($observation['department_name']); ?></td></tr>
    <tr><td class="label-cell">Category</td><td><?php echo e($observation['category_name']); ?></td></tr>
    <tr><td class="label-cell">Risk Level</td><td><?php echo e($observation['risk_level']); ?></td></tr>
    <tr><td class="label-cell">Accident Type</td><td><?php echo e($observation['accident_type']); ?></td></tr>
    <tr><td class="label-cell">Specific Area/Location</td><td><?php echo e($observation['specific_area_location']); ?></td></tr>
    <tr><td class="label-cell">Observation Date</td><td><?php echo e(formatDate($observation['observation_date'])); ?></td></tr>
    <tr><td class="label-cell">Observation Time</td><td><?php echo e($observation['observation_time']); ?></td></tr>
    <tr><td class="label-cell">Observation Description</td><td><?php echo nl2br(e($observation['observation_description'])); ?></td></tr>
    <tr><td class="label-cell">Immediate Action</td><td><?php echo nl2br(e($observation['immediate_action'])); ?></td></tr>
    <tr><td class="label-cell">Recommended Action</td><td><?php echo nl2br(e($observation['recommended_action'])); ?></td></tr>
    <tr>
        <td class="label-cell">Attachment</td>
        <td>
            <?php if ($observation['attachment_path']): ?>
                <a href="<?php echo BASE_URL . e($observation['attachment_path']); ?>" target="_blank">View Uploaded Document</a>
                <?php if ($observation['attachment_original_name']): ?>
                    (<?php echo e($observation['attachment_original_name']); ?>)
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr><td class="label-cell">Recorded By</td><td><?php echo e($observation['recorded_by_name']); ?></td></tr>
    <tr><td class="label-cell">Created At</td><td><?php echo e(formatDateTime($observation['created_at'])); ?></td></tr>
    <tr><td class="label-cell">Updated At</td><td><?php echo e(formatDateTime($observation['updated_at'])); ?></td></tr>
</table>

<h3>Action History</h3>
<table class="data-table" cellpadding="3" cellspacing="0">
    <tr>
        <th>Date</th>
        <th>Action By</th>
        <th>Old Status</th>
        <th>New Status</th>
        <th>Action Text</th>
        <th>Attachment</th>
    </tr>
    <?php if (!$actions): ?>
        <tr><td colspan="6">No action history available.</td></tr>
    <?php endif; ?>
    <?php foreach ($actions as $action): ?>
        <tr>
            <td><?php echo e(formatDateTime($action['action_date'])); ?></td>
            <td><?php echo e($action['action_by_name']); ?></td>
            <td><?php echo e($action['old_status_name']); ?></td>
            <td><?php echo e($action['new_status_name']); ?></td>
            <td><?php echo nl2br(e($action['action_text'])); ?></td>
            <td>
                <?php if ($action['action_attachment_path']): ?>
                    <a href="<?php echo BASE_URL . e($action['action_attachment_path']); ?>" target="_blank">View</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p class="page-buttons">
    <a href="<?php echo BASE_URL; ?>modules/observations/list.php" class="plain-link-button">Back to List</a>
    <a href="<?php echo BASE_URL; ?>modules/observations/update.php?id=<?php echo e($id); ?>" class="plain-link-button">Update</a>
    <a href="<?php echo BASE_URL; ?>modules/observations/close.php?id=<?php echo e($id); ?>" class="plain-link-button">Close</a>
    <a href="#" onclick="window.print(); return false;" class="plain-link-button">Print</a>
</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

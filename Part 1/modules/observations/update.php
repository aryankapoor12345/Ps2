<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'Invalid observation selected.');
    redirect('modules/observations/list.php');
}
$observation = getObservationById($id);
if (!$observation || !canUpdateObservation($observation)) {
    setFlash('error', 'You are not authorized to access this page.');
    redirect('dashboard.php');
}

$statuses = getAllStatuses();
$errors = [];
$old = [
    'new_status_id' => $observation['status_id'],
    'action_text' => '',
    'target_closing_date' => $observation['target_closing_date'],
    'recommended_action' => $observation['recommended_action'],
];

if (isPost()) {
    $old = array_merge($old, sanitizeInput($_POST));
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }
    $newStatusId = (int)($old['new_status_id'] ?? 0);
    $newStatus = fetchOne("SELECT * FROM observation_statuses WHERE id = ?", [$newStatusId], 'i');
    $actionText = trim($old['action_text'] ?? '');
    if (!$newStatus) {
        $errors[] = 'Please select valid status.';
    }
    if (strlen($actionText) < 5) {
        $errors[] = 'Action Taken / Remarks should be at least 5 characters.';
    }
    $uploadPath = null;
    if (!empty($_FILES['action_attachment']['name'])) {
        $upload = uploadFile($_FILES['action_attachment'], 'actions');
        if (!$upload['success']) {
            $errors[] = $upload['message'];
        } else {
            $uploadPath = $upload['path'];
        }
    }
    if (!$errors) {
        $closedDate = $newStatus['status_name'] === STATUS_CLOSED ? date('Y-m-d') : null;
        insertRecord(
            "INSERT INTO observation_actions (observation_id, action_by, action_text, old_status_id, new_status_id, action_attachment_path) VALUES (?, ?, ?, ?, ?, ?)",
            [$id, currentUserId(), $actionText, $observation['status_id'], $newStatusId, $uploadPath],
            'iisiis'
        );
        updateRecord(
            "UPDATE safety_observations
             SET status_id = ?, target_closing_date = ?, recommended_action = ?, closed_date = ?, updated_at = NOW()
             WHERE id = ?",
            [$newStatusId, ($old['target_closing_date'] ?: null), ($old['recommended_action'] ?: null), $closedDate, $id],
            'isssi'
        );
        createNotification((int)$observation['reported_by'], 'Safety Observation Updated', 'Observation ' . $observation['observation_no'] . ' has been updated.', 'observation', $id);
        logAudit('Updated safety observation ' . $observation['observation_no'], 'safety_observations', $id);
        setFlash('success', 'Safety observation updated successfully.');
        redirect('modules/observations/view.php?id=' . $id);
    }
}

$pageTitle = 'Update Safety Observation - ' . SITE_NAME;
require __DIR__ . '/../../includes/header.php';
?>
<h2>Update Safety Observation</h2>
<?php if ($observation['status_name'] === STATUS_CLOSED): ?>
    <div class="notice-box">This observation is already closed.</div>
<?php endif; ?>
<?php if ($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>

<table class="detail-table" cellpadding="4" cellspacing="0">
    <tr><td class="label-cell">Observation No</td><td><?php echo e($observation['observation_no']); ?></td></tr>
    <tr><td class="label-cell">Current Status</td><td><?php echo e($observation['status_name']); ?></td></tr>
    <tr><td class="label-cell">Zone</td><td><?php echo e($observation['short_name']); ?></td></tr>
    <tr><td class="label-cell">Department</td><td><?php echo e($observation['department_name']); ?></td></tr>
    <tr><td class="label-cell">Category</td><td><?php echo e($observation['category_name']); ?></td></tr>
    <tr><td class="label-cell">Risk Level</td><td><?php echo e($observation['risk_level']); ?></td></tr>
    <tr><td class="label-cell">Specific Area/Location</td><td><?php echo e($observation['specific_area_location']); ?></td></tr>
    <tr><td class="label-cell">Observation Description</td><td><?php echo nl2br(e($observation['observation_description'])); ?></td></tr>
    <tr><td class="label-cell">Reported By</td><td><?php echo e($observation['reporter_name']); ?></td></tr>
    <tr><td class="label-cell">Observation Date</td><td><?php echo e(formatDate($observation['observation_date'])); ?></td></tr>
</table>

<form method="post" enctype="multipart/form-data" class="validate-form">
    <?php echo csrfField(); ?>
    <table class="form-table" cellpadding="4" cellspacing="0">
        <tr>
            <td class="label-cell">New Status <span class="required">*</span></td>
            <td><select name="new_status_id" data-required="1"><?php foreach ($statuses as $status): ?><option value="<?php echo e($status['id']); ?>" <?php echo (int)$old['new_status_id'] === (int)$status['id'] ? 'selected' : ''; ?>><?php echo e($status['status_name']); ?></option><?php endforeach; ?></select></td>
        </tr>
        <tr><td class="label-cell">Action Taken / Remarks <span class="required">*</span></td><td><textarea name="action_text" rows="5" data-required="1" data-min-length="5"><?php echo e($old['action_text']); ?></textarea></td></tr>
        <tr><td class="label-cell">Target Closing Date</td><td><input type="date" name="target_closing_date" value="<?php echo e($old['target_closing_date']); ?>" class="date-input"></td></tr>
        <tr><td class="label-cell">Recommended Action</td><td><textarea name="recommended_action" rows="3"><?php echo e($old['recommended_action']); ?></textarea></td></tr>
        <tr><td class="label-cell">Upload Action Document</td><td><input type="file" name="action_attachment" data-max-size="<?php echo MAX_UPLOAD_SIZE; ?>" data-extensions="pdf,jpg,jpeg,png,doc,docx"></td></tr>
        <tr><td>&nbsp;</td><td><input type="submit" value="Save Update" class="save-button"> <a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/observations/view.php?id=<?php echo e($id); ?>">Back to View</a> <a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/observations/list.php">Back to List</a></td></tr>
    </table>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

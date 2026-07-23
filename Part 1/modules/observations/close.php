<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$observationNo = sanitizeInput($_GET['observation_no'] ?? '');
$observation = null;
$errors = [];
if ($id > 0) {
    $observation = getObservationById($id);
} elseif ($observationNo !== '') {
    $row = fetchOne("SELECT id FROM safety_observations WHERE observation_no = ?", [$observationNo]);
    if ($row) {
        $id = (int)$row['id'];
        $observation = getObservationById($id);
    } else {
        $errors[] = 'Observation number not found.';
    }
}

if ($observation && !userCanAccessObservation($observation)) {
    setFlash('error', 'You are not authorized to access this page.');
    redirect('dashboard.php');
}

if (isPost() && $observation) {
    if (!canCloseObservation($observation)) {
        $errors[] = 'This observation is already closed or cannot be closed by you.';
    }
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }
    $remarks = trim(sanitizeInput($_POST['closure_remarks'] ?? ''));
    $finalAction = trim(sanitizeInput($_POST['final_corrective_action'] ?? ''));
    if ($remarks === '' || $finalAction === '') {
        $errors[] = 'Closure remarks and final corrective action are required.';
    }
    if (empty($_POST['confirm_close'])) {
        $errors[] = 'Please confirm closure.';
    }
    $uploadPath = null;
    if (!empty($_FILES['closure_document']['name'])) {
        $upload = uploadFile($_FILES['closure_document'], 'actions');
        if (!$upload['success']) {
            $errors[] = $upload['message'];
        } else {
            $uploadPath = $upload['path'];
        }
    }
    if (!$errors) {
        $closedStatusId = getClosedStatusId();
        $text = "Closure Remarks:\n" . $remarks . "\n\nFinal Corrective Action:\n" . $finalAction;
        insertRecord(
            "INSERT INTO observation_actions (observation_id, action_by, action_text, old_status_id, new_status_id, action_attachment_path) VALUES (?, ?, ?, ?, ?, ?)",
            [$id, currentUserId(), $text, $observation['status_id'], $closedStatusId, $uploadPath],
            'iisiis'
        );
        updateRecord("UPDATE safety_observations SET status_id = ?, closed_date = ?, updated_at = NOW() WHERE id = ?", [$closedStatusId, date('Y-m-d'), $id], 'isi');
        createNotification((int)$observation['reported_by'], 'Safety Observation Closed', 'Observation ' . $observation['observation_no'] . ' has been closed.', 'observation', $id);
        logAudit('Closed safety observation ' . $observation['observation_no'], 'safety_observations', $id);
        setFlash('success', 'Observation closed successfully.');
        redirect('modules/observations/view.php?id=' . $id);
    }
}

$lastAction = $observation ? fetchOne(
    "SELECT action_text FROM observation_actions WHERE observation_id = ? ORDER BY action_date DESC, id DESC LIMIT 1",
    [$id],
    'i'
) : null;
$pageTitle = 'Close Safety Observation - ' . SITE_NAME;
require __DIR__ . '/../../includes/header.php';
?>
<h2>Close Safety Observation</h2>
<?php if ($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<?php if (!$observation): ?>
    <form method="get">
        <table class="form-table" cellpadding="4" cellspacing="0">
            <tr><td class="label-cell">Enter Observation No</td><td><input type="text" name="observation_no" value="<?php echo e($observationNo); ?>" class="text-input" required></td></tr>
            <tr><td>&nbsp;</td><td><input type="submit" value="Search" class="save-button"></td></tr>
        </table>
    </form>
<?php else: ?>
    <?php if ($observation['status_name'] === STATUS_CLOSED): ?><div class="notice-box">This observation is already closed.</div><?php endif; ?>
    <table class="detail-table" cellpadding="4" cellspacing="0">
        <tr><td class="label-cell">Observation No</td><td><?php echo e($observation['observation_no']); ?></td></tr>
        <tr><td class="label-cell">Current Status</td><td><?php echo e($observation['status_name']); ?></td></tr>
        <tr><td class="label-cell">Zone</td><td><?php echo e($observation['short_name']); ?></td></tr>
        <tr><td class="label-cell">Department</td><td><?php echo e($observation['department_name']); ?></td></tr>
        <tr><td class="label-cell">Specific Area/Location</td><td><?php echo e($observation['specific_area_location']); ?></td></tr>
        <tr><td class="label-cell">Observation Description</td><td><?php echo nl2br(e($observation['observation_description'])); ?></td></tr>
        <tr><td class="label-cell">Last Action Taken</td><td><?php echo nl2br(e($lastAction['action_text'] ?? '')); ?></td></tr>
        <tr><td class="label-cell">Reported By</td><td><?php echo e($observation['reporter_name']); ?></td></tr>
    </table>
    <?php if (canCloseObservation($observation)): ?>
        <form method="post" enctype="multipart/form-data" class="validate-form">
            <?php echo csrfField(); ?>
            <table class="form-table" cellpadding="4" cellspacing="0">
                <tr><td class="label-cell">Closure Remarks <span class="required">*</span></td><td><textarea name="closure_remarks" rows="5" data-required="1"></textarea></td></tr>
                <tr><td class="label-cell">Final Corrective Action <span class="required">*</span></td><td><textarea name="final_corrective_action" rows="4" data-required="1"></textarea></td></tr>
                <tr><td class="label-cell">Closure Document</td><td><input type="file" name="closure_document" data-max-size="<?php echo MAX_UPLOAD_SIZE; ?>" data-extensions="pdf,jpg,jpeg,png,doc,docx"></td></tr>
                <tr><td class="label-cell">Confirmation</td><td><label><input type="checkbox" name="confirm_close" value="1"> I confirm that corrective action has been taken and this observation can be closed.</label></td></tr>
                <tr><td>&nbsp;</td><td><input type="submit" value="Close Observation" class="save-button"> <a href="<?php echo BASE_URL; ?>modules/observations/view.php?id=<?php echo e($id); ?>" class="plain-link-button">Back</a></td></tr>
            </table>
        </form>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

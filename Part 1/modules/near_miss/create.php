<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser();
$zones = getAllZones();
$departments = getAllDepartments();
$errors = [];
$old = ['zone_id'=>'','department_id'=>'','incident_location'=>'','incident_date'=>date('Y-m-d'),'incident_time'=>date('H:i'),'incident_description'=>'','possible_consequence'=>'','preventive_action'=>''];
if (isPost()) {
    $old = array_merge($old, sanitizeInput($_POST));
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request token.';
    $zoneId = (int)$old['zone_id'];
    $departmentId = (int)$old['department_id'];
    if ($zoneId <= 0) $errors[] = 'Safety Zone is required.';
    if ($departmentId <= 0) $errors[] = 'Department is required.';
    if (trim($old['incident_location']) === '') $errors[] = 'Incident Location is required.';
    if (trim($old['incident_date']) === '') $errors[] = 'Incident Date is required.';
    if (strlen(trim($old['incident_description'])) < 10) $errors[] = 'Incident Description should be at least 10 characters.';
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $upload = uploadFile($_FILES['attachment'], 'near_miss');
        if (!$upload['success']) $errors[] = $upload['message']; else $attachment = $upload['path'];
    }
    if (!$errors) {
        $nearMissNo = generateNearMissNo();
        $insertId = insertRecord(
            "INSERT INTO near_miss_reports (near_miss_no, reported_by, zone_id, department_id, incident_location, incident_description, possible_consequence, preventive_action, status_id, incident_date, incident_time, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$nearMissNo, currentUserId(), $zoneId, $departmentId, $old['incident_location'], $old['incident_description'], $old['possible_consequence'] ?: null, $old['preventive_action'] ?: null, getOpenStatusId(), $old['incident_date'], ($old['incident_time'] ?: null), $attachment],
            'siiissssisss'
        );
        if ($insertId) {
            logAudit('Near miss reported ' . $nearMissNo, 'near_miss_reports', $insertId);
            setFlash('success', 'Near miss report saved successfully. Near Miss No: ' . $nearMissNo);
            redirect('modules/near_miss/view.php?id=' . $insertId);
        }
        $errors[] = 'Unable to save near miss report.';
    }
}
$pageTitle = 'Report Near Miss - ' . SITE_NAME;
require __DIR__ . '/../../includes/header.php';
?>
<h2>Report Near Miss</h2>
<?php if ($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="validate-form">
<?php echo csrfField(); ?>
<table class="form-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">User</td><td>User-<?php echo e($user['employee_id'] . ' ' . $user['full_name']); ?></td></tr>
<tr><td class="label-cell">Safety Zone <span class="required">*</span></td><td><select name="zone_id" data-required="1"><option value="">--Select Safety Zone--</option><?php foreach ($zones as $zone): ?><option value="<?php echo e($zone['id']); ?>" <?php echo (int)$old['zone_id']===(int)$zone['id']?'selected':''; ?>><?php echo e($zone['zone_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Department <span class="required">*</span></td><td><select name="department_id" data-required="1"><option value="">--Select Department--</option><?php foreach ($departments as $dept): ?><option value="<?php echo e($dept['id']); ?>" <?php echo (int)$old['department_id']===(int)$dept['id']?'selected':''; ?>><?php echo e($dept['department_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Incident Location <span class="required">*</span></td><td><input type="text" name="incident_location" value="<?php echo e($old['incident_location']); ?>" class="long-input" data-required="1"></td></tr>
<tr><td class="label-cell">Incident Date <span class="required">*</span></td><td><input type="date" name="incident_date" value="<?php echo e($old['incident_date']); ?>" class="date-input" data-required="1"></td></tr>
<tr><td class="label-cell">Incident Time</td><td><input type="time" name="incident_time" value="<?php echo e($old['incident_time']); ?>" class="time-input"></td></tr>
<tr><td class="label-cell">Incident Description <span class="required">*</span></td><td><textarea name="incident_description" rows="5" data-required="1" data-min-length="10"><?php echo e($old['incident_description']); ?></textarea></td></tr>
<tr><td class="label-cell">Possible Consequence</td><td><textarea name="possible_consequence" rows="3"><?php echo e($old['possible_consequence']); ?></textarea></td></tr>
<tr><td class="label-cell">Preventive Action Suggested</td><td><textarea name="preventive_action" rows="3"><?php echo e($old['preventive_action']); ?></textarea></td></tr>
<tr><td class="label-cell">Upload Document(PDF/JPG/DOC UPTO 2 MB)</td><td><input type="file" name="attachment" data-max-size="<?php echo MAX_UPLOAD_SIZE; ?>" data-extensions="pdf,jpg,jpeg,png,doc,docx"></td></tr>
<tr><td class="label-cell">Reported By</td><td><?php echo e($user['employee_id'] . ' ' . $user['full_name']); ?></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Save" class="save-button"></td></tr>
</table>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

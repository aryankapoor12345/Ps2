<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$user = currentUser(); $zones = getAllZones(); $departments = getAllDepartments(); $errors = [];
$old = ['zone_id'=>'','department_id'=>'','suggestion_title'=>'','suggestion_description'=>'','expected_benefit'=>'','submitted_date'=>date('Y-m-d')];
if (isPost()) {
    $old = array_merge($old, sanitizeInput($_POST));
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) $errors[] = 'Invalid request token.';
    if (trim($old['suggestion_title']) === '') $errors[] = 'Suggestion Title is required.';
    if (strlen(trim($old['suggestion_description'])) < 10) $errors[] = 'Suggestion Description should be at least 10 characters.';
    if (trim($old['submitted_date']) === '') $errors[] = 'Submitted Date is required.';
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $upload = uploadFile($_FILES['attachment'], 'suggestions');
        if (!$upload['success']) $errors[] = $upload['message']; else $attachment = $upload['path'];
    }
    if (!$errors) {
        $suggestionNo = generateSuggestionNo();
        $insertId = insertRecord(
            "INSERT INTO safety_suggestions (suggestion_no, submitted_by, zone_id, department_id, suggestion_title, suggestion_description, expected_benefit, status_id, submitted_date, attachment_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$suggestionNo, currentUserId(), ((int)$old['zone_id'] ?: null), ((int)$old['department_id'] ?: null), $old['suggestion_title'], $old['suggestion_description'], ($old['expected_benefit'] ?: null), getOpenStatusId(), $old['submitted_date'], $attachment],
            'siiisssiss'
        );
        if ($insertId) {
            logAudit('Safety suggestion recorded ' . $suggestionNo, 'safety_suggestions', $insertId);
            setFlash('success', 'Safety suggestion saved successfully. Suggestion No: ' . $suggestionNo);
            redirect('modules/suggestions/view.php?id=' . $insertId);
        }
        $errors[] = 'Unable to save suggestion.';
    }
}
$pageTitle = 'Record Suggestion - ' . SITE_NAME; require __DIR__ . '/../../includes/header.php';
?>
<h2>Record Suggestion</h2>
<?php if ($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="validate-form"><?php echo csrfField(); ?>
<table class="form-table" cellpadding="4" cellspacing="0">
<tr><td class="label-cell">User</td><td>User-<?php echo e($user['employee_id'].' '.$user['full_name']); ?></td></tr>
<tr><td class="label-cell">Safety Zone</td><td><select name="zone_id"><option value="">--Select Safety Zone--</option><?php foreach($zones as $z): ?><option value="<?php echo e($z['id']); ?>" <?php echo (int)$old['zone_id']===(int)$z['id']?'selected':''; ?>><?php echo e($z['zone_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Department</td><td><select name="department_id"><option value="">--Select Department--</option><?php foreach($departments as $d): ?><option value="<?php echo e($d['id']); ?>" <?php echo (int)$old['department_id']===(int)$d['id']?'selected':''; ?>><?php echo e($d['department_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Suggestion Title <span class="required">*</span></td><td><input type="text" name="suggestion_title" value="<?php echo e($old['suggestion_title']); ?>" class="long-input" data-required="1"></td></tr>
<tr><td class="label-cell">Suggestion Description <span class="required">*</span></td><td><textarea name="suggestion_description" rows="5" data-required="1" data-min-length="10"><?php echo e($old['suggestion_description']); ?></textarea></td></tr>
<tr><td class="label-cell">Expected Benefit</td><td><textarea name="expected_benefit" rows="3"><?php echo e($old['expected_benefit']); ?></textarea></td></tr>
<tr><td class="label-cell">Submitted Date <span class="required">*</span></td><td><input type="date" name="submitted_date" value="<?php echo e($old['submitted_date']); ?>" class="date-input" data-required="1"></td></tr>
<tr><td class="label-cell">Upload Document(PDF/JPG/DOC UPTO 2 MB)</td><td><input type="file" name="attachment" data-max-size="<?php echo MAX_UPLOAD_SIZE; ?>" data-extensions="pdf,jpg,jpeg,png,doc,docx"></td></tr>
<tr><td class="label-cell">Submitted By</td><td><?php echo e($user['employee_id'].' '.$user['full_name']); ?></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Save" class="save-button"></td></tr>
</table></form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

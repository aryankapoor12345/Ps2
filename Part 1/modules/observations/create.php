<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$pageTitle = 'Record Safety Observations - ' . SITE_NAME;
$user = currentUser();
$zones = getAllZones();
$departments = getAllDepartments();
$categories = getAllCategories();
$riskLevels = ['Low', 'Medium', 'High', 'Critical'];
$accidentTypes = ['None', 'Near Miss', 'Minor', 'Major', 'Fatal'];
$errors = [];
$old = [
    'zone_id' => '',
    'eic_id' => '',
    'department_id' => '',
    'eic_mobile' => '',
    'specific_area_location' => '',
    'category_id' => '',
    'risk_level' => 'Medium',
    'accident_type' => 'None',
    'observation_date' => date('Y-m-d'),
    'observation_time' => date('H:i'),
    'observation_description' => '',
    'immediate_action' => '',
    'recommended_action' => '',
];

if (isPost()) {
    $old = array_merge($old, sanitizeInput($_POST));

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request token.';
    }

    $zoneId = (int)($old['zone_id'] ?? 0);
    $eicId = (int)($old['eic_id'] ?? 0);
    $departmentId = (int)($old['department_id'] ?? 0);
    $categoryId = (int)($old['category_id'] ?? 0);
    $specificArea = substr($old['specific_area_location'] ?? '', 0, 255);
    $description = $old['observation_description'] ?? '';
    $observationDate = $old['observation_date'] ?? '';
    $observationTime = $old['observation_time'] ?: null;
    $riskLevel = in_array($old['risk_level'], $riskLevels, true) ? $old['risk_level'] : 'Medium';
    $accidentType = in_array($old['accident_type'], $accidentTypes, true) ? $old['accident_type'] : 'None';

    if ($zoneId <= 0 || !fetchOne("SELECT id FROM safety_zones WHERE id = ? AND is_active = 1", [$zoneId], 'i')) {
        $errors[] = 'Please select valid Safety Zone.';
    }
    if ($departmentId <= 0 || !fetchOne("SELECT id FROM departments WHERE id = ? AND is_active = 1", [$departmentId], 'i')) {
        $errors[] = 'Please select valid Department.';
    }
    if ($categoryId <= 0 || !fetchOne("SELECT id FROM observation_categories WHERE id = ? AND is_active = 1", [$categoryId], 'i')) {
        $errors[] = 'Please select valid Observation Category.';
    }
    if ($eicId > 0 && !fetchOne("SELECT id FROM users WHERE id = ? AND is_active = 1", [$eicId], 'i')) {
        $errors[] = 'Please select valid EIC.';
    }
    if ($specificArea === '') {
        $errors[] = 'Specific Area/Location is required.';
    }
    if ($description === '' || strlen($description) < 10) {
        $errors[] = 'Observation Description should be at least 10 characters.';
    }
    if (strlen($description) > 2000) {
        $errors[] = 'Observation Description should be up to 2000 characters.';
    }
    if ($observationDate === '') {
        $errors[] = 'Observation Date is required.';
    }

    $attachmentPath = null;
    $attachmentOriginalName = null;
    if (!empty($_FILES['attachment']['name'])) {
        $upload = uploadFile($_FILES['attachment'], 'observations');
        if (!$upload['success']) {
            $errors[] = $upload['message'];
        } else {
            $attachmentPath = $upload['path'];
            $attachmentOriginalName = $upload['original_name'];
        }
    }

    if (!$errors) {
        $observationNo = generateObservationNo();
        $statusId = getOpenStatusId();
        $recordedByName = $user['employee_id'] . ' ' . $user['full_name'];
        $insertId = insertRecord(
            "INSERT INTO safety_observations
             (observation_no, reported_by, zone_id, eic_id, department_id, category_id, status_id,
              specific_area_location, observation_description, immediate_action, recommended_action,
              risk_level, accident_type, observation_date, observation_time, attachment_path,
              attachment_original_name, recorded_by_name)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $observationNo,
                currentUserId(),
                $zoneId,
                $eicId > 0 ? $eicId : null,
                $departmentId,
                $categoryId,
                $statusId,
                $specificArea,
                $description,
                $old['immediate_action'] ?: null,
                $old['recommended_action'] ?: null,
                $riskLevel,
                $accidentType,
                $observationDate,
                $observationTime,
                $attachmentPath,
                $attachmentOriginalName,
                $recordedByName,
            ],
            'siiiiiisssssssssss'
        );

        if ($insertId) {
            logAudit('Safety observation recorded', 'safety_observations', $insertId);
            $notifyUsers = [];
            foreach (getZoneLeaderUsers($zoneId) as $leader) {
                $notifyUsers[(int)$leader['id']] = true;
            }
            if ($eicId > 0) {
                $notifyUsers[$eicId] = true;
            }
            foreach (array_keys($notifyUsers) as $notifyUserId) {
                if ($notifyUserId !== currentUserId()) {
                    createNotification($notifyUserId, 'New Safety Observation', 'Observation ' . $observationNo . ' has been recorded.', 'observation', $insertId);
                }
            }
            setFlash('success', 'Safety observation recorded successfully. Observation No: ' . $observationNo);
            redirect('modules/observations/view.php?id=' . $insertId);
        }
        $errors[] = 'Unable to save observation. Please try again.';
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<script>
window.APP_BASE_URL = "<?php echo e(BASE_URL); ?>";
</script>
<h2>Record Safety Observations</h2>
<?php if ($errors): ?>
    <div class="flash flash-error">
        <?php foreach ($errors as $error): ?>
            <?php echo e($error); ?><br>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post" action="" enctype="multipart/form-data" class="validate-form observation-form">
    <?php echo csrfField(); ?>
    <table class="form-table observation-form-table" cellpadding="4" cellspacing="0">
        <tr>
            <td class="label-cell">User</td>
            <td>User-<?php echo e($user['employee_id'] . ' ' . $user['full_name']); ?></td>
        </tr>
        <tr>
            <td class="label-cell">Safety Zone <span class="required">*</span></td>
            <td>
                <select name="zone_id" id="zone_id" data-zone-select data-required="1">
                    <option value="">--Select Safety Zone--</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo e($zone['id']); ?>" <?php echo ((int)$old['zone_id'] === (int)$zone['id']) ? 'selected' : ''; ?>>
                            <?php echo e($zone['zone_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Zone Leaders</td>
            <td><div id="zone_leaders_display" class="readonly-box"><?php echo $old['zone_id'] ? e(getZoneLeadersText((int)$old['zone_id'])) : ''; ?></div></td>
        </tr>
        <tr>
            <td class="label-cell">EIC (If Required)</td>
            <td>
                <select name="eic_id" id="eic_id" data-eic-select>
                    <option value="">--Select EIC--</option>
                    <?php if ($old['zone_id']): ?>
                        <?php foreach (getZoneEics((int)$old['zone_id']) as $eic): ?>
                            <option value="<?php echo e($eic['id']); ?>" data-mobile="<?php echo e($eic['mobile']); ?>" data-department-id="<?php echo e($eic['department_id']); ?>" <?php echo ((int)$old['eic_id'] === (int)$eic['id']) ? 'selected' : ''; ?>>
                                <?php echo e($eic['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Department <span class="required">*</span></td>
            <td>
                <select name="department_id" id="department_id" data-required="1">
                    <option value="">--Select Department--</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo e($department['id']); ?>" <?php echo ((int)$old['department_id'] === (int)$department['id']) ? 'selected' : ''; ?>>
                            <?php echo e($department['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">EIC Mobile</td>
            <td><input type="text" name="eic_mobile" id="eic_mobile" value="<?php echo e($old['eic_mobile']); ?>" class="text-input" data-mobile="1" maxlength="10"></td>
        </tr>
        <tr>
            <td class="label-cell">Specific Area/Location <span class="required">*</span></td>
            <td><input type="text" name="specific_area_location" value="<?php echo e($old['specific_area_location']); ?>" class="long-input" maxlength="255" data-required="1"></td>
        </tr>
        <tr>
            <td class="label-cell">Observation Category <span class="required">*</span></td>
            <td>
                <select name="category_id" data-required="1">
                    <option value="">--Select Category--</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo e($category['id']); ?>" <?php echo ((int)$old['category_id'] === (int)$category['id']) ? 'selected' : ''; ?>>
                            <?php echo e($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Risk Level <span class="required">*</span></td>
            <td>
                <select name="risk_level" data-required="1">
                    <?php foreach ($riskLevels as $level): ?>
                        <option value="<?php echo e($level); ?>" <?php echo $old['risk_level'] === $level ? 'selected' : ''; ?>><?php echo e($level); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Accident Type</td>
            <td>
                <select name="accident_type">
                    <?php foreach ($accidentTypes as $type): ?>
                        <option value="<?php echo e($type); ?>" <?php echo $old['accident_type'] === $type ? 'selected' : ''; ?>><?php echo e($type); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Observation Date <span class="required">*</span></td>
            <td><input type="date" name="observation_date" value="<?php echo e($old['observation_date']); ?>" class="date-input" data-required="1"></td>
        </tr>
        <tr>
            <td class="label-cell">Observation Time</td>
            <td><input type="time" name="observation_time" value="<?php echo e($old['observation_time']); ?>" class="time-input"></td>
        </tr>
        <tr>
            <td class="label-cell">Observation Description <span class="required">*</span></td>
            <td>
                <textarea name="observation_description" id="observation_description" rows="4" maxlength="2000" data-required="1" data-min-length="10" data-count="observation_desc_count"><?php echo e($old['observation_description']); ?></textarea>
                <div class="char-count">Maximum 2000 characters. <span id="observation_desc_count">0 characters</span></div>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Immediate Action Taken</td>
            <td><textarea name="immediate_action" rows="3"><?php echo e($old['immediate_action']); ?></textarea></td>
        </tr>
        <tr>
            <td class="label-cell">Recommended Action</td>
            <td><textarea name="recommended_action" rows="3"><?php echo e($old['recommended_action']); ?></textarea></td>
        </tr>
        <tr>
            <td class="label-cell">Upload Document(PDF/JPG/DOC UPTO 2 MB)</td>
            <td><input type="file" name="attachment" data-max-size="<?php echo MAX_UPLOAD_SIZE; ?>" data-extensions="pdf,jpg,jpeg,png,doc,docx"></td>
        </tr>
        <tr>
            <td class="label-cell">Observation Recorded By</td>
            <td><input type="text" value="<?php echo e($user['employee_id'] . ' ' . $user['full_name']); ?>" class="long-input" readonly></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
                <input type="submit" value="Save" class="save-button">
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="plain-link-button">Back</a>
            </td>
        </tr>
    </table>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

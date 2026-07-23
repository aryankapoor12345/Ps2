<?php
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path)
{
    if (preg_match('/^https?:\/\//', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
    }
    exit;
}

function currentUrl()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '');
}

function isPost()
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function isGet()
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET';
}

function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(strip_tags((string)$data));
}

function formatDate($date)
{
    if (!$date || $date === '0000-00-00') {
        return '';
    }
    return date(DATE_FORMAT, strtotime($date));
}

function formatDateTime($datetime)
{
    if (!$datetime || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    return date(DATETIME_FORMAT, strtotime($datetime));
}

function generateObservationNo()
{
    $prefix = 'OBS-' . date('Ymd') . '-';
    $row = fetchOne("SELECT observation_no FROM safety_observations WHERE observation_no LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1", [$prefix]);
    $next = $row ? ((int)substr($row['observation_no'], -4) + 1) : 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function generateSuggestionNo()
{
    $prefix = 'SUG-' . date('Ymd') . '-';
    $row = fetchOne("SELECT suggestion_no FROM safety_suggestions WHERE suggestion_no LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1", [$prefix]);
    $next = $row ? ((int)substr($row['suggestion_no'], -4) + 1) : 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function generateNearMissNo()
{
    $prefix = 'NM-' . date('Ymd') . '-';
    $row = fetchOne("SELECT near_miss_no FROM near_miss_reports WHERE near_miss_no LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1", [$prefix]);
    $next = $row ? ((int)substr($row['near_miss_no'], -4) + 1) : 1;
    return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
}

function getClientIp()
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            return explode(',', $_SERVER[$key])[0];
        }
    }
    return '';
}

function logAudit($action, $tableName = null, $recordId = null)
{
    if (!db()) {
        return false;
    }
    return insertRecord(
        "INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)",
        [currentUserId(), $action, $tableName, $recordId, getClientIp(), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)],
        'ississ'
    );
}

function getAllZones()
{
    return fetchAll("SELECT * FROM safety_zones WHERE is_active = 1 ORDER BY display_order, zone_code");
}

function getAllDepartments()
{
    return fetchAll("SELECT * FROM departments WHERE is_active = 1 ORDER BY department_name");
}

function getAllCategories()
{
    return fetchAll("SELECT * FROM observation_categories WHERE is_active = 1 ORDER BY category_name");
}

function getAllStatuses()
{
    return fetchAll("SELECT * FROM observation_statuses ORDER BY id");
}

function getZoneLeadersText($zoneId)
{
    $rows = fetchAll(
        "SELECT u.full_name, zl.leader_type FROM zone_leaders zl INNER JOIN users u ON u.id = zl.user_id WHERE zl.zone_id = ? ORDER BY zl.is_primary DESC, zl.id",
        [$zoneId],
        'i'
    );
    $primary = [];
    $deputy = [];
    $assistant = [];
    foreach ($rows as $row) {
        if ($row['leader_type'] === 'Zone Leader') {
            $primary[] = $row['full_name'];
        } elseif ($row['leader_type'] === 'Dy. Leader') {
            $deputy[] = $row['full_name'];
        } else {
            $assistant[] = $row['full_name'];
        }
    }
    $parts = [];
    if ($primary) {
        $parts[] = 'Zone Leader - ' . implode(' | ', $primary);
    }
    if ($deputy) {
        $parts[] = 'Dy. Leaders - ' . implode(' | ', $deputy);
    }
    if ($assistant) {
        $parts[] = 'Assistant Leaders - ' . implode(' | ', $assistant);
    }
    return implode(', ', $parts);
}

function getSetting($key, $default = null)
{
    if (!db()) {
        return $default;
    }
    $row = fetchOne("SELECT setting_value FROM app_settings WHERE setting_key = ?", [$key]);
    return $row ? $row['setting_value'] : $default;
}

function setSetting($key, $value)
{
    if (!db()) {
        return false;
    }
    $existing = fetchOne("SELECT id FROM app_settings WHERE setting_key = ?", [$key]);
    if ($existing) {
        return updateRecord("UPDATE app_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key], 'ss');
    }
    return insertRecord("INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value], 'ss');
}

function safeInt($value)
{
    return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : 0;
}

function tableExists($tableName)
{
    if (!db()) {
        return false;
    }
    $row = fetchOne("SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$tableName]);
    return (int)($row['total'] ?? 0) > 0;
}

function columnExists($tableName, $columnName)
{
    if (!db()) {
        return false;
    }
    $row = fetchOne("SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$tableName, $columnName], 'ss');
    return (int)($row['total'] ?? 0) > 0;
}

function getStatusIdByName($statusName)
{
    $row = fetchOne("SELECT id FROM observation_statuses WHERE status_name = ?", [$statusName]);
    return $row ? (int)$row['id'] : null;
}

function getOpenStatusId()
{
    return getStatusIdByName(STATUS_OPEN);
}

function getClosedStatusId()
{
    return getStatusIdByName(STATUS_CLOSED);
}

function getZoneLeaderUsers($zoneId)
{
    return fetchAll(
        "SELECT u.id, u.employee_id, u.full_name, zl.leader_type
         FROM zone_leaders zl
         INNER JOIN users u ON u.id = zl.user_id
         WHERE zl.zone_id = ?
         ORDER BY zl.is_primary DESC, zl.id",
        [$zoneId],
        'i'
    );
}

function getZoneEics($zoneId)
{
    return fetchAll(
        "SELECT u.id, u.employee_id, u.full_name, u.mobile, u.department_id, d.department_name
         FROM zone_eic ze
         INNER JOIN users u ON u.id = ze.user_id
         LEFT JOIN departments d ON d.id = u.department_id
         WHERE ze.zone_id = ? AND ze.is_active = 1 AND u.is_active = 1
         ORDER BY u.full_name",
        [$zoneId],
        'i'
    );
}

function createNotification($userId, $title, $message, $relatedType = null, $relatedId = null)
{
    if (!$userId || !db()) {
        return false;
    }
    return insertRecord(
        "INSERT INTO notifications (user_id, title, message, related_type, related_id) VALUES (?, ?, ?, ?, ?)",
        [(int)$userId, $title, $message, $relatedType, $relatedId],
        'isssi'
    );
}

function getObservationById($id)
{
    return fetchOne(
        "SELECT so.*, u.employee_id AS reporter_employee_id, u.full_name AS reporter_name,
                eu.full_name AS eic_name, eu.employee_id AS eic_employee_id, eu.mobile AS eic_mobile,
                sz.zone_code, sz.zone_name, sz.short_name,
                d.department_name, oc.category_name, os.status_name
         FROM safety_observations so
         INNER JOIN users u ON u.id = so.reported_by
         LEFT JOIN users eu ON eu.id = so.eic_id
         INNER JOIN safety_zones sz ON sz.id = so.zone_id
         INNER JOIN departments d ON d.id = so.department_id
         INNER JOIN observation_categories oc ON oc.id = so.category_id
         INNER JOIN observation_statuses os ON os.id = so.status_id
         WHERE so.id = ?",
        [(int)$id],
        'i'
    );
}

function userCanAccessObservation($observation)
{
    if (!$observation || !isLoggedIn()) {
        return false;
    }
    $role = currentUserRole();
    $userId = currentUserId();
    if (in_array($role, [ROLE_ADMIN, ROLE_SAFETY_ADMIN], true)) {
        return true;
    }
    if ($role === ROLE_ZONE_LEADER) {
        $row = fetchOne(
            "SELECT id FROM zone_leaders WHERE zone_id = ? AND user_id = ?",
            [(int)$observation['zone_id'], $userId],
            'ii'
        );
        return (bool)$row;
    }
    if ($role === ROLE_ENGINEER_INCHARGE) {
        return (int)($observation['eic_id'] ?? 0) === $userId;
    }
    return (int)$observation['reported_by'] === $userId;
}

function appendObservationAccessWhere(&$where, &$params, &$types, $alias = 'so')
{
    $role = currentUserRole();
    $userId = currentUserId();
    if (in_array($role, [ROLE_ADMIN, ROLE_SAFETY_ADMIN], true)) {
        return;
    }
    if ($role === ROLE_ZONE_LEADER) {
        $where[] = "{$alias}.zone_id IN (SELECT zone_id FROM zone_leaders WHERE user_id = ?)";
        $params[] = $userId;
        $types .= 'i';
        return;
    }
    if ($role === ROLE_ENGINEER_INCHARGE) {
        $where[] = "{$alias}.eic_id = ?";
        $params[] = $userId;
        $types .= 'i';
        return;
    }
    $where[] = "{$alias}.reported_by = ?";
    $params[] = $userId;
    $types .= 'i';
}

function isAdminRole()
{
    return currentUserRole() === ROLE_ADMIN;
}

function isSafetyAdminRole()
{
    return currentUserRole() === ROLE_SAFETY_ADMIN;
}

function isZoneLeaderRole()
{
    return currentUserRole() === ROLE_ZONE_LEADER;
}

function isEicRole()
{
    return currentUserRole() === ROLE_ENGINEER_INCHARGE;
}

function isEmployeeRole()
{
    return currentUserRole() === ROLE_EMPLOYEE;
}

function getUserZoneIds($userId)
{
    $rows = fetchAll("SELECT zone_id FROM zone_leaders WHERE user_id = ?", [(int)$userId], 'i');
    return array_map('intval', array_column($rows, 'zone_id'));
}

function buildObservationAccessWhere($alias = 'so', &$params = [], &$types = '')
{
    $where = [];
    appendObservationAccessWhere($where, $params, $types, $alias);
    return $where ? '(' . implode(' AND ', $where) . ')' : '1=1';
}

function canUpdateObservation($observation)
{
    if (!$observation || !userCanAccessObservation($observation)) {
        return false;
    }
    if (in_array(currentUserRole(), [ROLE_ADMIN, ROLE_SAFETY_ADMIN], true)) {
        return true;
    }
    return ($observation['status_name'] ?? '') !== STATUS_CLOSED && in_array(currentUserRole(), [ROLE_ZONE_LEADER, ROLE_ENGINEER_INCHARGE], true);
}

function canCloseObservation($observation)
{
    if (!$observation || !userCanAccessObservation($observation)) {
        return false;
    }
    if (($observation['status_name'] ?? '') === STATUS_CLOSED) {
        return false;
    }
    return in_array(currentUserRole(), [ROLE_ADMIN, ROLE_SAFETY_ADMIN, ROLE_ZONE_LEADER, ROLE_ENGINEER_INCHARGE], true);
}

function getRoles()
{
    return fetchAll("SELECT * FROM roles ORDER BY role_name");
}

function getUsers()
{
    return fetchAll(
        "SELECT u.*, r.role_name, d.department_name
         FROM users u
         INNER JOIN roles r ON r.id = u.role_id
         LEFT JOIN departments d ON d.id = u.department_id
         ORDER BY u.full_name"
    );
}

function getDepartments()
{
    return fetchAll("SELECT * FROM departments ORDER BY department_name");
}

function getZones()
{
    return fetchAll("SELECT * FROM safety_zones ORDER BY display_order, zone_code");
}

function getUserOptionsByRole($roleName)
{
    return fetchAll(
        "SELECT u.id, u.employee_id, u.full_name
         FROM users u
         INNER JOIN roles r ON r.id = u.role_id
         WHERE r.role_name = ? AND u.is_active = 1
         ORDER BY u.full_name",
        [$roleName]
    );
}

function daysPending($date)
{
    if (!$date) {
        return '';
    }
    $start = new DateTime($date);
    $today = new DateTime(date('Y-m-d'));
    return (int)$start->diff($today)->format('%a');
}

function shortText($text, $length = 80)
{
    $text = trim((string)$text);
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - 3) . '...';
}

function getStatusCounts()
{
    return fetchAll(
        "SELECT os.status_name, COUNT(so.id) AS total
         FROM observation_statuses os
         LEFT JOIN safety_observations so ON so.status_id = os.id
         GROUP BY os.id
         ORDER BY os.id"
    );
}

function getRiskCounts()
{
    return fetchAll(
        "SELECT risk.risk_level, COUNT(so.id) AS total
         FROM (SELECT 'Low' AS risk_level UNION SELECT 'Medium' UNION SELECT 'High' UNION SELECT 'Critical') risk
         LEFT JOIN safety_observations so ON so.risk_level = risk.risk_level
         GROUP BY risk.risk_level
         ORDER BY FIELD(risk.risk_level, 'Low', 'Medium', 'High', 'Critical')"
    );
}

function getAccidentTypeCounts()
{
    return fetchAll(
        "SELECT acc.accident_type, COUNT(so.id) AS total
         FROM (SELECT 'None' AS accident_type UNION SELECT 'Near Miss' UNION SELECT 'Minor' UNION SELECT 'Major' UNION SELECT 'Fatal') acc
         LEFT JOIN safety_observations so ON so.accident_type = acc.accident_type
         GROUP BY acc.accident_type
         ORDER BY FIELD(acc.accident_type, 'None', 'Near Miss', 'Minor', 'Major', 'Fatal')"
    );
}

function getDepartmentDueSummary($filters = [])
{
    $where = ["os.status_name NOT IN ('Closed','Rejected')"];
    $params = [];
    $types = '';
    appendObservationAccessWhere($where, $params, $types, 'so');
    if (!empty($filters['department_id'])) {
        $where[] = 'so.department_id = ?';
        $params[] = (int)$filters['department_id'];
        $types .= 'i';
    }
    if (!empty($filters['zone_id'])) {
        $where[] = 'so.zone_id = ?';
        $params[] = (int)$filters['zone_id'];
        $types .= 'i';
    }
    if (!empty($filters['status_id'])) {
        $where[] = 'so.status_id = ?';
        $params[] = (int)$filters['status_id'];
        $types .= 'i';
    }
    if (!empty($filters['due_till_date'])) {
        $where[] = '(so.target_closing_date IS NULL OR so.target_closing_date <= ?)';
        $params[] = $filters['due_till_date'];
        $types .= 's';
    }
    return fetchAll(
        "SELECT d.department_name,
                COUNT(so.id) AS total_pending,
                SUM(os.status_name = 'Open') AS open_count,
                SUM(os.status_name = 'Under Review') AS review_count,
                SUM(os.status_name = 'Assigned') AS assigned_count,
                SUM(os.status_name = 'Action Taken') AS action_count,
                MIN(so.observation_date) AS oldest_pending_date
         FROM departments d
         LEFT JOIN safety_observations so ON so.department_id = d.id
         LEFT JOIN observation_statuses os ON os.id = so.status_id
         WHERE " . implode(' AND ', $where) . "
         GROUP BY d.id
         ORDER BY d.department_name",
        $params,
        $types
    );
}

function getPendingObservations($filters = [], $limit = null, $offset = null)
{
    $where = ["os.status_name NOT IN ('Closed','Rejected')"];
    $params = [];
    $types = '';
    appendObservationAccessWhere($where, $params, $types, 'so');
    foreach (['zone_id' => 'i', 'department_id' => 'i'] as $key => $type) {
        if (!empty($filters[$key])) {
            $where[] = 'so.' . $key . ' = ?';
            $params[] = (int)$filters[$key];
            $types .= $type;
        }
    }
    if (!empty($filters['risk_level'])) {
        $where[] = 'so.risk_level = ?';
        $params[] = $filters['risk_level'];
        $types .= 's';
    }
    if (!empty($filters['status_id'])) {
        $where[] = 'so.status_id = ?';
        $params[] = (int)$filters['status_id'];
        $types .= 'i';
    }
    if (!empty($filters['due_till_date'])) {
        $where[] = '(so.target_closing_date IS NULL OR so.target_closing_date <= ?)';
        $params[] = $filters['due_till_date'];
        $types .= 's';
    }
    if (!empty($filters['from_date'])) {
        $where[] = 'so.observation_date >= ?';
        $params[] = $filters['from_date'];
        $types .= 's';
    }
    if (!empty($filters['to_date'])) {
        $where[] = 'so.observation_date <= ?';
        $params[] = $filters['to_date'];
        $types .= 's';
    }
    $sql = "SELECT so.*, sz.short_name, d.department_name, os.status_name
            FROM safety_observations so
            INNER JOIN safety_zones sz ON sz.id = so.zone_id
            INNER JOIN departments d ON d.id = so.department_id
            INNER JOIN observation_statuses os ON os.id = so.status_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY so.observation_date ASC, so.id ASC";
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= 'ii';
    }
    return fetchAll($sql, $params, $types);
}

function getObservationCounts()
{
    $counts = [
        'total' => 0,
        'open' => 0,
        'closed' => 0,
        'suggestions' => 0,
        'near_miss' => 0,
    ];
    if (!db()) {
        return $counts;
    }
    $display = fetchOne("SELECT setting_value FROM app_settings WHERE setting_key = 'total_safety_observations_display'");
    $suggestionsDisplay = fetchOne("SELECT setting_value FROM app_settings WHERE setting_key = 'total_safety_suggestions_display'");
    $nearMissDisplay = fetchOne("SELECT setting_value FROM app_settings WHERE setting_key = 'total_reported_near_miss_display'");
    $total = fetchOne("SELECT COUNT(*) AS total FROM safety_observations");
    $open = fetchOne("SELECT COUNT(*) AS total FROM safety_observations so INNER JOIN observation_statuses os ON os.id = so.status_id WHERE os.status_name <> 'Closed'");
    $closed = fetchOne("SELECT COUNT(*) AS total FROM safety_observations so INNER JOIN observation_statuses os ON os.id = so.status_id WHERE os.status_name = 'Closed'");
    $suggestions = fetchOne("SELECT COUNT(*) AS total FROM safety_suggestions");
    $nearMiss = fetchOne("SELECT COUNT(*) AS total FROM near_miss_reports");
    $counts['total'] = $display ? (int)$display['setting_value'] : (int)($total['total'] ?? 0);
    $counts['open'] = (int)($open['total'] ?? 0);
    $counts['closed'] = (int)($closed['total'] ?? 0);
    $counts['suggestions'] = $suggestionsDisplay ? (int)$suggestionsDisplay['setting_value'] : (int)($suggestions['total'] ?? 0);
    $counts['near_miss'] = $nearMissDisplay ? (int)$nearMissDisplay['setting_value'] : (int)($nearMiss['total'] ?? 0);
    return $counts;
}

function getZonewiseCounts()
{
    return fetchAll(
        "SELECT sz.zone_code, sz.short_name, COUNT(so.id) AS total
         FROM safety_zones sz
         LEFT JOIN safety_observations so ON so.zone_id = sz.id
         WHERE sz.is_active = 1
         GROUP BY sz.id
         ORDER BY sz.display_order"
    );
}

function validateUpload($file)
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['valid' => true, 'message' => ''];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'Upload failed.'];
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        return ['valid' => false, 'message' => 'File size should be up to 2 MB.'];
    }
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        return ['valid' => false, 'message' => 'Allowed formats: PDF, JPG, JPEG, PNG, DOC, DOCX.'];
    }
    return ['valid' => true, 'message' => ''];
}

function uploadFile($file, $folder)
{
    $check = validateUpload($file);
    if (!$check['valid'] || empty($file['name'])) {
        return ['success' => false, 'path' => null, 'original_name' => '', 'message' => $check['message']];
    }
    $safeFolder = trim($folder, '/\\');
    $uploadDir = __DIR__ . '/../uploads/' . $safeFolder . '/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'path' => null, 'original_name' => '', 'message' => 'Upload directory not found.'];
        }
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $prefix = strtoupper(substr($safeFolder, 0, 3));
    if ($safeFolder === 'observations') {
        $prefix = 'OBS';
    }
    $fileName = $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target = $uploadDir . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'path' => null, 'original_name' => '', 'message' => 'Unable to save uploaded file.'];
    }
    return ['success' => true, 'path' => 'uploads/' . $safeFolder . '/' . $fileName, 'original_name' => $file['name'], 'message' => ''];
}

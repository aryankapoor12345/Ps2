<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();

$pageTitle = 'Safety Observation List - ' . SITE_NAME;
$zones = getAllZones();
$departments = getAllDepartments();
$statuses = getAllStatuses();
$filters = sanitizeInput($_GET);
$page = max(1, (int)($filters['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];
$types = '';
appendObservationAccessWhere($where, $params, $types, 'so');

if (!empty($filters['observation_no'])) {
    $where[] = 'so.observation_no LIKE ?';
    $params[] = '%' . $filters['observation_no'] . '%';
    $types .= 's';
}
if (!empty($filters['zone_id'])) {
    $where[] = 'so.zone_id = ?';
    $params[] = (int)$filters['zone_id'];
    $types .= 'i';
}
if (!empty($filters['department_id'])) {
    $where[] = 'so.department_id = ?';
    $params[] = (int)$filters['department_id'];
    $types .= 'i';
}
if (!empty($filters['status'])) {
    if (strtolower($filters['status']) === 'open') {
        $where[] = "os.status_name <> 'Closed'";
    } else {
        $where[] = 'so.status_id = ?';
        $params[] = (int)$filters['status'];
        $types .= 'i';
    }
}
if (!empty($filters['risk_level'])) {
    $where[] = 'so.risk_level = ?';
    $params[] = $filters['risk_level'];
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

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$baseSql = "FROM safety_observations so
            INNER JOIN safety_zones sz ON sz.id = so.zone_id
            INNER JOIN departments d ON d.id = so.department_id
            INNER JOIN observation_categories oc ON oc.id = so.category_id
            INNER JOIN observation_statuses os ON os.id = so.status_id
            INNER JOIN users u ON u.id = so.reported_by";
$countRow = fetchOne("SELECT COUNT(*) AS total " . $baseSql . $whereSql, $params, $types);
$totalRows = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$listParams = $params;
$listTypes = $types . 'ii';
$listParams[] = $perPage;
$listParams[] = $offset;
$rows = fetchAll(
    "SELECT so.id, so.observation_no, so.observation_date, so.specific_area_location, so.risk_level, so.reported_by, so.eic_id, so.zone_id,
            sz.short_name, d.department_name, oc.category_name, os.status_name, u.full_name AS reporter_name
     " . $baseSql . $whereSql . "
     ORDER BY so.created_at DESC, so.id DESC
     LIMIT ? OFFSET ?",
    $listParams,
    $listTypes
);

function observationListQuery($pageNo)
{
    $query = $_GET;
    $query['page'] = $pageNo;
    return '?' . http_build_query($query);
}

require __DIR__ . '/../../includes/header.php';
?>
<h2>Safety Observation List</h2>
<form method="get" action="">
    <table class="form-table filter-table" cellpadding="3" cellspacing="0">
        <tr>
            <td>Observation No</td>
            <td><input type="text" name="observation_no" value="<?php echo e($filters['observation_no'] ?? ''); ?>" class="text-input"></td>
            <td>Zone</td>
            <td>
                <select name="zone_id">
                    <option value="">All</option>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?php echo e($zone['id']); ?>" <?php echo ((int)($filters['zone_id'] ?? 0) === (int)$zone['id']) ? 'selected' : ''; ?>><?php echo e($zone['short_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Department</td>
            <td>
                <select name="department_id">
                    <option value="">All</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo e($department['id']); ?>" <?php echo ((int)($filters['department_id'] ?? 0) === (int)$department['id']) ? 'selected' : ''; ?>><?php echo e($department['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>Status</td>
            <td>
                <select name="status">
                    <option value="">All</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo e($status['id']); ?>" <?php echo ((string)($filters['status'] ?? '') === (string)$status['id']) ? 'selected' : ''; ?>><?php echo e($status['status_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Risk Level</td>
            <td>
                <select name="risk_level">
                    <option value="">All</option>
                    <?php foreach (['Low', 'Medium', 'High', 'Critical'] as $risk): ?>
                        <option value="<?php echo e($risk); ?>" <?php echo (($filters['risk_level'] ?? '') === $risk) ? 'selected' : ''; ?>><?php echo e($risk); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>From Date</td>
            <td><input type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="date-input"> To <input type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="date-input"></td>
        </tr>
        <tr>
            <td colspan="4">
                <input type="submit" value="Search" class="save-button">
                <a href="<?php echo BASE_URL; ?>modules/observations/list.php" class="plain-link-button">Reset</a>
            </td>
        </tr>
    </table>
</form>

<table class="data-table observation-list-table" cellpadding="3" cellspacing="0">
    <tr>
        <th>S.No.</th>
        <th>Observation No</th>
        <th>Date</th>
        <th>Zone</th>
        <th>Department</th>
        <th>Category</th>
        <th>Specific Area/Location</th>
        <th>Risk</th>
        <th>Status</th>
        <th>Reported By</th>
        <th>Action</th>
    </tr>
    <?php if (!$rows): ?>
        <tr><td colspan="11">No observation records found.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $index => $row): ?>
        <tr>
            <td><?php echo e($offset + $index + 1); ?></td>
            <td><?php echo e($row['observation_no']); ?></td>
            <td><?php echo e(formatDate($row['observation_date'])); ?></td>
            <td><?php echo e($row['short_name']); ?></td>
            <td><?php echo e($row['department_name']); ?></td>
            <td><?php echo e($row['category_name']); ?></td>
            <td><?php echo e($row['specific_area_location']); ?></td>
            <td><?php echo e($row['risk_level']); ?></td>
            <td><?php echo e($row['status_name']); ?></td>
            <td><?php echo e($row['reporter_name']); ?></td>
            <td class="table-actions">
                <a href="<?php echo BASE_URL; ?>modules/observations/view.php?id=<?php echo e($row['id']); ?>">View</a>
                <?php if (canUpdateObservation($row)): ?>
                    <a href="<?php echo BASE_URL; ?>modules/observations/update.php?id=<?php echo e($row['id']); ?>">Update</a>
                <?php endif; ?>
                <?php if (canCloseObservation($row)): ?>
                    <a href="<?php echo BASE_URL; ?>modules/observations/close.php?id=<?php echo e($row['id']); ?>">Close</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="pagination">
    Page <?php echo e($page); ?> of <?php echo e($totalPages); ?>, Total Records: <?php echo e($totalRows); ?>
    <?php if ($page > 1): ?>
        <a href="<?php echo e(observationListQuery($page - 1)); ?>">Previous</a>
    <?php endif; ?>
    <?php if ($page < $totalPages): ?>
        <a href="<?php echo e(observationListQuery($page + 1)); ?>">Next</a>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

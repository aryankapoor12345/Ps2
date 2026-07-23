<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
$zones = getAllZones(); $departments = getAllDepartments(); $statuses = getAllStatuses(); $filters = sanitizeInput($_GET);
$page = max(1, (int)($filters['page'] ?? 1)); $perPage = 20; $offset = ($page - 1) * $perPage;
$where = []; $params = []; $types = '';
if (isZoneLeaderRole()) { $where[] = 'nmr.zone_id IN (SELECT zone_id FROM zone_leaders WHERE user_id = ?)'; $params[] = currentUserId(); $types .= 'i'; }
elseif (isEicRole()) { $where[] = 'nmr.zone_id IN (SELECT zone_id FROM zone_eic WHERE user_id = ? AND is_active = 1)'; $params[] = currentUserId(); $types .= 'i'; }
elseif (!isAdminRole() && !isSafetyAdminRole()) { $where[] = 'nmr.reported_by = ?'; $params[] = currentUserId(); $types .= 'i'; }
if (!empty($filters['near_miss_no'])) { $where[]='nmr.near_miss_no LIKE ?'; $params[]='%'.$filters['near_miss_no'].'%'; $types.='s'; }
if (!empty($filters['zone_id'])) { $where[]='nmr.zone_id = ?'; $params[]=(int)$filters['zone_id']; $types.='i'; }
if (!empty($filters['department_id'])) { $where[]='nmr.department_id = ?'; $params[]=(int)$filters['department_id']; $types.='i'; }
if (!empty($filters['status_id'])) { $where[]='nmr.status_id = ?'; $params[]=(int)$filters['status_id']; $types.='i'; }
if (!empty($filters['from_date'])) { $where[]='nmr.incident_date >= ?'; $params[]=$filters['from_date']; $types.='s'; }
if (!empty($filters['to_date'])) { $where[]='nmr.incident_date <= ?'; $params[]=$filters['to_date']; $types.='s'; }
$whereSql = $where ? ' WHERE '.implode(' AND ', $where) : '';
$base = "FROM near_miss_reports nmr INNER JOIN safety_zones sz ON sz.id=nmr.zone_id INNER JOIN departments d ON d.id=nmr.department_id INNER JOIN observation_statuses os ON os.id=nmr.status_id INNER JOIN users u ON u.id=nmr.reported_by";
$total = fetchOne("SELECT COUNT(*) AS total ".$base.$whereSql, $params, $types);
$rows = fetchAll("SELECT nmr.*, sz.short_name, d.department_name, os.status_name, u.full_name AS reporter_name ".$base.$whereSql." ORDER BY nmr.created_at DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]), $types.'ii');
$totalPages = max(1, (int)ceil((int)($total['total'] ?? 0) / $perPage));
$pageTitle = 'Near Miss List - ' . SITE_NAME; require __DIR__ . '/../../includes/header.php';
?>
<h2>Near Miss List</h2>
<form method="get"><table class="form-table filter-table" cellpadding="3" cellspacing="0">
<tr><td>Near Miss No</td><td><input type="text" name="near_miss_no" value="<?php echo e($filters['near_miss_no'] ?? ''); ?>" class="text-input"></td><td>Zone</td><td><select name="zone_id"><option value="">All</option><?php foreach($zones as $z): ?><option value="<?php echo e($z['id']); ?>" <?php echo (int)($filters['zone_id']??0)===(int)$z['id']?'selected':''; ?>><?php echo e($z['short_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td>Department</td><td><select name="department_id"><option value="">All</option><?php foreach($departments as $d): ?><option value="<?php echo e($d['id']); ?>" <?php echo (int)($filters['department_id']??0)===(int)$d['id']?'selected':''; ?>><?php echo e($d['department_name']); ?></option><?php endforeach; ?></select></td><td>Status</td><td><select name="status_id"><option value="">All</option><?php foreach($statuses as $s): ?><option value="<?php echo e($s['id']); ?>" <?php echo (int)($filters['status_id']??0)===(int)$s['id']?'selected':''; ?>><?php echo e($s['status_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td>From Date</td><td><input type="date" name="from_date" value="<?php echo e($filters['from_date'] ?? ''); ?>" class="date-input"></td><td>To Date</td><td><input type="date" name="to_date" value="<?php echo e($filters['to_date'] ?? ''); ?>" class="date-input"> <input type="submit" value="Search" class="save-button"> <a href="<?php echo BASE_URL; ?>modules/near_miss/list.php" class="plain-link-button">Reset</a></td></tr>
</table></form>
<table class="data-table" cellpadding="3" cellspacing="0"><tr><th>S.No.</th><th>Near Miss No</th><th>Date</th><th>Zone</th><th>Department</th><th>Incident Location</th><th>Status</th><th>Reported By</th><th>Action</th></tr>
<?php if(!$rows): ?><tr><td colspan="9">No near miss records found.</td></tr><?php endif; ?>
<?php foreach($rows as $i=>$r): ?><tr><td><?php echo e($offset+$i+1); ?></td><td><?php echo e($r['near_miss_no']); ?></td><td><?php echo e(formatDate($r['incident_date'])); ?></td><td><?php echo e($r['short_name']); ?></td><td><?php echo e($r['department_name']); ?></td><td><?php echo e($r['incident_location']); ?></td><td><?php echo e($r['status_name']); ?></td><td><?php echo e($r['reporter_name']); ?></td><td><a href="<?php echo BASE_URL; ?>modules/near_miss/view.php?id=<?php echo e($r['id']); ?>">View</a></td></tr><?php endforeach; ?>
</table><div class="pagination">Page <?php echo e($page); ?> of <?php echo e($totalPages); ?> <?php if($page>1): ?><a href="?<?php echo e(http_build_query(array_merge($filters,['page'=>$page-1]))); ?>">Previous</a><?php endif; ?> <?php if($page<$totalPages): ?><a href="?<?php echo e(http_build_query(array_merge($filters,['page'=>$page+1]))); ?>">Next</a><?php endif; ?></div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

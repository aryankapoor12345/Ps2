<?php
require_once dirname(__DIR__, 2) . '/config/app.php';
requireRole([ROLE_ADMIN, ROLE_SAFETY_ADMIN]);
$filters = sanitizeInput($_GET);
$page = max(1, safeInt($filters['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;
$where = []; $params = []; $types = '';
if (!empty($filters['user_id'])) { $where[]='al.user_id=?'; $params[]=(int)$filters['user_id']; $types.='i'; }
if (!empty($filters['action'])) { $where[]='al.action LIKE ?'; $params[]='%'.$filters['action'].'%'; $types.='s'; }
if (!empty($filters['from_date'])) { $where[]='DATE(al.created_at)>=?'; $params[]=$filters['from_date']; $types.='s'; }
if (!empty($filters['to_date'])) { $where[]='DATE(al.created_at)<=?'; $params[]=$filters['to_date']; $types.='s'; }
$whereSql = $where ? ' WHERE '.implode(' AND ', $where) : '';
$base = "FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id";
$total = fetchOne("SELECT COUNT(*) total ".$base.$whereSql, $params, $types);
$rows = fetchAll("SELECT al.*, u.full_name ".$base.$whereSql." ORDER BY al.created_at DESC, al.id DESC LIMIT ? OFFSET ?", array_merge($params, [$perPage, $offset]), $types.'ii');
$users = fetchAll("SELECT id, full_name FROM users ORDER BY full_name");
$totalPages = max(1, (int)ceil((int)($total['total'] ?? 0) / $perPage));
$pageTitle = 'Audit Logs - ' . SITE_NAME;
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<h2>Audit Logs</h2>
<form method="get"><table class="form-table filter-table"><tr><td>User</td><td><select name="user_id"><option value="">All</option><?php foreach($users as $u): ?><option value="<?php echo e($u['id']); ?>" <?php echo (int)($filters['user_id']??0)===(int)$u['id']?'selected':''; ?>><?php echo e($u['full_name']); ?></option><?php endforeach; ?></select></td><td>Action Keyword</td><td><input name="action" value="<?php echo e($filters['action']??''); ?>" class="text-input"></td></tr><tr><td>From Date</td><td><input type="date" name="from_date" value="<?php echo e($filters['from_date']??''); ?>" class="date-input"></td><td>To Date</td><td><input type="date" name="to_date" value="<?php echo e($filters['to_date']??''); ?>" class="date-input"> <input type="submit" value="Search" class="save-button"></td></tr></table></form>
<table class="data-table"><tr><th>S.No.</th><th>Date</th><th>User</th><th>Action</th><th>Table Name</th><th>Record ID</th><th>IP Address</th></tr>
<?php if(!$rows): ?><tr><td colspan="7">No audit logs found.</td></tr><?php endif; ?>
<?php foreach($rows as $i=>$r): ?><tr><td><?php echo e($offset+$i+1); ?></td><td><?php echo e(formatDateTime($r['created_at'])); ?></td><td><?php echo e($r['full_name']); ?></td><td><?php echo e($r['action']); ?></td><td><?php echo e($r['table_name']); ?></td><td><?php echo e($r['record_id']); ?></td><td><?php echo e($r['ip_address']); ?></td></tr><?php endforeach; ?>
</table>
<div class="pagination">Page <?php echo e($page); ?> of <?php echo e($totalPages); ?> <?php if($page>1): ?><a href="?<?php echo e(http_build_query(array_merge($filters,['page'=>$page-1]))); ?>">Previous</a><?php endif; ?> <?php if($page<$totalPages): ?><a href="?<?php echo e(http_build_query(array_merge($filters,['page'=>$page+1]))); ?>">Next</a><?php endif; ?></div>
<?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>

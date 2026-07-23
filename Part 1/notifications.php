<?php
require_once __DIR__ . '/config/app.php';
requireLogin();
if (($_GET['action'] ?? '') === 'mark_all_read') {
    updateRecord("UPDATE notifications SET is_read=1 WHERE user_id=?", [currentUserId()], 'i');
    setFlash('success', 'All notifications marked as read.');
    redirect('notifications.php');
}
$rows = fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC, id DESC", [currentUserId()], 'i');
$pageTitle = 'Notifications - ' . SITE_NAME;
require __DIR__ . '/includes/header.php';
?>
<h2>Notifications</h2>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>notifications.php?action=mark_all_read">Mark all as read</a></p>
<table class="data-table" cellpadding="3" cellspacing="0">
<tr><th>S.No.</th><th>Date</th><th>Title</th><th>Message</th><th>Related Type</th><th>Read Status</th></tr>
<?php if (!$rows): ?><tr><td colspan="6">No notifications available.</td></tr><?php endif; ?>
<?php foreach ($rows as $i => $row): ?>
<tr>
<td><?php echo e($i + 1); ?></td>
<td><?php echo e(formatDateTime($row['created_at'])); ?></td>
<td><?php echo e($row['title']); ?></td>
<td><?php echo e($row['message']); ?></td>
<td>
<?php if ($row['related_type'] === 'observation' && $row['related_id']): ?>
    <a href="<?php echo BASE_URL; ?>modules/observations/view.php?id=<?php echo e($row['related_id']); ?>">observation</a>
<?php else: ?>
    <?php echo e($row['related_type']); ?>
<?php endif; ?>
</td>
<td><?php echo (int)$row['is_read'] ? 'Read' : 'Unread'; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/app.php';
}
$canWorkObservation = isLoggedIn() && in_array(currentUserRole(), [ROLE_ADMIN, ROLE_SAFETY_ADMIN, ROLE_ZONE_LEADER, ROLE_ENGINEER_INCHARGE], true);
$canAdmin = isLoggedIn() && in_array(currentUserRole(), [ROLE_ADMIN, ROLE_SAFETY_ADMIN], true);
?>
<div class="side-menu">
    <div class="menu-heading">Menu</div>
    <ul>
        <li><a href="<?php echo BASE_URL; ?>modules/suggestions/create.php">Record Suggestion</a></li>
        <li><a href="<?php echo BASE_URL; ?>modules/near_miss/create.php">Report Near Miss</a></li>
        <li><a href="<?php echo BASE_URL; ?>modules/observations/create.php">Submit Accident/Observation</a></li>
        <li><a href="<?php echo BASE_URL; ?>search.php">Search Records</a></li>
        <li><a href="#">NTPC Safety Policy Compliance</a></li>
        <li>
            <a href="<?php echo BASE_URL; ?>modules/observations/list.php">Safety Observation</a>
            <ul>
                <?php if ($canWorkObservation): ?>
                    <li><a href="<?php echo BASE_URL; ?>modules/observations/list.php">Update</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/observations/list.php?status=open">Close</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/observations/close.php">Close By Number</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>modules/observations/list.php">My Observations</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li>
            <span>REPORTS</span>
            <ul>
                <li><a href="<?php echo BASE_URL; ?>modules/reports/dashboard_report.php">Dashboard Report</a></li>
                <li><a href="<?php echo BASE_URL; ?>modules/reports/department_dues.php">Department Dues</a></li>
                <li><a href="<?php echo BASE_URL; ?>modules/reports/pending_list.php">Pending List</a></li>
                <li><a href="<?php echo BASE_URL; ?>modules/reports/zonewise_observations.php">ZoneWise Observations</a></li>
                <?php if ($canAdmin): ?>
                    <li><a href="<?php echo BASE_URL; ?>export.php">Export CSV</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li><a href="#">Safety Permit</a></li>
        <?php if ($canAdmin): ?>
            <li>
                <span>Safety Admin</span>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/users.php">Users</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/zones.php">Zones</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/departments.php">Departments</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/categories.php">Categories</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/settings.php">Settings</a></li>
                    <li><a href="<?php echo BASE_URL; ?>modules/admin/audit_logs.php">Audit Logs</a></li>
                    <?php if (isAdminRole()): ?>
                        <li><a href="<?php echo BASE_URL; ?>modules/admin/backup.php">Backup</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>setup_check.php">Setup Check</a></li>
                </ul>
            </li>
        <?php endif; ?>
        <li><a href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
        <li><a href="<?php echo BASE_URL; ?>notifications.php">Notifications</a></li>
        <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
    </ul>
</div>

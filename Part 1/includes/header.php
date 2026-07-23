<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/app.php';
}
$pageTitle = $pageTitle ?? SITE_NAME;
$user = currentUser();
$portalTitle = getSetting('portal_title', 'ONLINE SAFETY PORTAL');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/forms.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/tables.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/print.css" media="print">
</head>
<body>
<table class="page-shell" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" class="top-header">
            <table class="header-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="logo-cell">NTPC</td>
                    <td class="title-cell"><?php echo e($portalTitle); ?></td>
                    <td class="login-cell">
                        <?php if ($user): ?>
                            <?php echo e($user['full_name']); ?><br>
                            <?php echo e($user['role_name']); ?><br>
                            <a href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>login.php">Login</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <?php if (isLoggedIn()): ?>
            <td class="sidebar-cell">
                <?php require __DIR__ . '/sidebar.php'; ?>
            </td>
        <?php endif; ?>
        <td class="content-cell">
            <?php displayFlash(); ?>

<?php
require_once __DIR__ . '/config/app.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

if (isPost()) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request token.');
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        if (loginUser($username, $password)) {
            setFlash('success', 'Login successful.');
            redirect('dashboard.php');
        }
        setFlash('error', 'Invalid username or password');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo e(SITE_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/forms.css">
</head>
<body class="login-body">
    <table class="login-box" cellpadding="0" cellspacing="0">
        <tr>
            <td class="login-title">ONLINE SAFETY PORTAL</td>
        </tr>
        <tr>
            <td class="login-subtitle">NTPC Safety Observation System</td>
        </tr>
        <tr>
            <td class="login-content">
                <?php displayFlash(); ?>
                <?php if (!db()): ?>
                    <div class="notice-box">Database connection is not available. Import database/ntpc_safety_portal.sql in phpMyAdmin and verify local settings.</div>
                <?php endif; ?>
                <form method="post" action="" class="validate-form">
                    <?php echo csrfField(); ?>
                    <table class="form-table login-form-table" cellpadding="4" cellspacing="0">
                        <tr>
                            <td class="label-cell">Username</td>
                            <td><input type="text" name="username" class="text-input" data-required="1" autofocus></td>
                        </tr>
                        <tr>
                            <td class="label-cell">Password</td>
                            <td><input type="password" name="password" class="text-input" data-required="1"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td><input type="submit" value="Login" class="save-button"></td>
                        </tr>
                    </table>
                </form>
                <div class="demo-credentials">
                    Demo credentials:<br>
                    admin / password123<br>
                    bhupendra / password123<br>
                    ashok / password123
                </div>
            </td>
        </tr>
    </table>
    <script src="<?php echo BASE_URL; ?>assets/js/validation.js"></script>
</body>
</html>

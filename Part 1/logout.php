<?php
require_once __DIR__ . '/config/app.php';
if (isLoggedIn()) {
    logAudit('User logged out', 'users', currentUserId());
}
logoutUser();
session_start();
setFlash('success', 'You have been logged out');
redirect('login.php');

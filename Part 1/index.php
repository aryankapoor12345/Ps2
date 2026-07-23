<?php
require_once __DIR__ . '/config/app.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

redirect('login.php');

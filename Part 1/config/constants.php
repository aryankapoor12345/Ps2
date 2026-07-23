<?php
define('BASE_URL', '/ntpc_safety_portal/');
define('SITE_NAME', 'NTPC Online Safety Portal');
define('DEFAULT_TIMEZONE', 'Asia/Kolkata');
define('DATE_FORMAT', 'd-m-Y');
define('DATETIME_FORMAT', 'd-m-Y H:i');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

define('ROLE_ADMIN', 'ADMIN');
define('ROLE_SAFETY_ADMIN', 'SAFETY_ADMIN');
define('ROLE_ZONE_LEADER', 'ZONE_LEADER');
define('ROLE_ENGINEER_INCHARGE', 'ENGINEER_INCHARGE');
define('ROLE_EMPLOYEE', 'EMPLOYEE');

define('STATUS_OPEN', 'Open');
define('STATUS_UNDER_REVIEW', 'Under Review');
define('STATUS_ASSIGNED', 'Assigned');
define('STATUS_ACTION_TAKEN', 'Action Taken');
define('STATUS_CLOSED', 'Closed');
define('STATUS_REJECTED', 'Rejected');

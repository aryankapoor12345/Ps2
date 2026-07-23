<?php
function isLoggedIn()
{
    return !empty($_SESSION['user_id']);
}

function currentUser()
{
    static $user = null;
    if (!isLoggedIn() || !db()) {
        return null;
    }
    if ($user === null) {
        $user = fetchOne(
            "SELECT u.id, u.employee_id, u.full_name, u.username, u.designation, u.mobile, u.email,
                    r.role_name, d.department_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             LEFT JOIN departments d ON d.id = u.department_id
             WHERE u.id = ? AND u.is_active = 1",
            [$_SESSION['user_id']],
            'i'
        );
    }
    return $user;
}

function currentUserId()
{
    return isLoggedIn() ? (int)$_SESSION['user_id'] : null;
}

function currentUserRole()
{
    $user = currentUser();
    return $user['role_name'] ?? null;
}

function hasRole($roles)
{
    $roles = is_array($roles) ? $roles : [$roles];
    return in_array(currentUserRole(), $roles, true);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to continue.');
        redirect('login.php');
    }
}

function requireRole($roles)
{
    requireLogin();
    if (!hasRole($roles)) {
        setFlash('error', 'Access denied.');
        redirect('dashboard.php');
    }
}

function loginUser($username, $password)
{
    if (!db()) {
        return false;
    }
    $user = fetchOne(
        "SELECT u.*, r.role_name FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.username = ? AND u.is_active = 1",
        [$username]
    );
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['employee_id'] = $user['employee_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role_name'] = $user['role_name'];
    updateRecord("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']], 'i');
    logAudit('User logged in', 'users', (int)$user['id']);
    return true;
}

function logoutUser()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

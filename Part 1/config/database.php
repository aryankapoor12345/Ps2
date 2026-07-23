<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ntpc_safety_portal');

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_errno) {
    $conn = null;
} else {
    $conn->set_charset('utf8mb4');
}

function db()
{
    global $conn;
    return $conn;
}

function executeQuery($sql, $params = [], $types = '')
{
    $connection = db();
    if (!$connection) {
        return false;
    }

    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        return false;
    }

    if (!empty($params)) {
        if ($types === '') {
            $types = str_repeat('s', count($params));
        }
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }

    return $stmt;
}

function fetchOne($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    if (!$stmt) {
        return null;
    }
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

function fetchAll($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    if (!$stmt) {
        return [];
    }
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function insertRecord($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    if (!$stmt) {
        return false;
    }
    $id = db()->insert_id;
    $stmt->close();
    return $id;
}

function updateRecord($sql, $params = [], $types = '')
{
    $stmt = executeQuery($sql, $params, $types);
    if (!$stmt) {
        return false;
    }
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

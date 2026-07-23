<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
header('Content-Type: application/json');
$departments = [];
foreach (getAllDepartments() as $row) {
    $departments[] = ['id' => (int)$row['id'], 'department_name' => $row['department_name']];
}
echo json_encode(['success' => true, 'departments' => $departments]);

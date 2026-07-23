<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
header('Content-Type: application/json');
$zoneId = (int)($_GET['zone_id'] ?? 0);
if ($zoneId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid zone selected', 'eics' => []]);
    exit;
}
$rows = getZoneEics($zoneId);
echo json_encode(['success' => true, 'eics' => $rows]);

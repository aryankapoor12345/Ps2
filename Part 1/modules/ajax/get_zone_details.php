<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
header('Content-Type: application/json');
$zoneId = (int)($_GET['zone_id'] ?? 0);
if ($zoneId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid zone selected']);
    exit;
}
$zone = fetchOne("SELECT id, zone_code, zone_name, short_name FROM safety_zones WHERE id = ? AND is_active = 1", [$zoneId], 'i');
if (!$zone) {
    echo json_encode(['success' => false, 'message' => 'Invalid zone selected']);
    exit;
}
$leaders = getZoneLeaderUsers($zoneId);
echo json_encode([
    'success' => true,
    'zone' => $zone,
    'leaders_text' => getZoneLeadersText($zoneId),
    'leaders' => $leaders,
]);

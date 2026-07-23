<?php
require_once __DIR__ . '/../../config/app.php';
requireLogin();
header('Content-Type: application/json');
if (!isPost() || !validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}
$observationId = (int)($_POST['observation_id'] ?? 0);
$statusId = (int)($_POST['status_id'] ?? 0);
$remarks = trim(sanitizeInput($_POST['remarks'] ?? ''));
$observation = getObservationById($observationId);
$status = fetchOne("SELECT * FROM observation_statuses WHERE id = ?", [$statusId], 'i');
if (!$observation || !$status || !canUpdateObservation($observation) || strlen($remarks) < 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid status update.']);
    exit;
}
insertRecord(
    "INSERT INTO observation_actions (observation_id, action_by, action_text, old_status_id, new_status_id) VALUES (?, ?, ?, ?, ?)",
    [$observationId, currentUserId(), $remarks, $observation['status_id'], $statusId],
    'iisii'
);
$closedDate = $status['status_name'] === STATUS_CLOSED ? date('Y-m-d') : null;
updateRecord("UPDATE safety_observations SET status_id = ?, closed_date = ?, updated_at = NOW() WHERE id = ?", [$statusId, $closedDate, $observationId], 'isi');
logAudit('Ajax status update ' . $observation['observation_no'], 'safety_observations', $observationId);
echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);

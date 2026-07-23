USE ntpc_safety_portal;

INSERT INTO app_settings (setting_key, setting_value) VALUES
('portal_title', 'ONLINE SAFETY PORTAL'),
('plant_name', 'NTPC Thermal Plant Safety Observation System'),
('max_upload_size_text', 'PDF/JPG/DOC UPTO 2 MB'),
('total_safety_observations_display', '20046'),
('total_safety_suggestions_display', '0'),
('total_reported_near_miss_display', '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

DROP PROCEDURE IF EXISTS add_index_if_missing;
DELIMITER //
CREATE PROCEDURE add_index_if_missing(IN tableName VARCHAR(64), IN indexName VARCHAR(64), IN indexSql TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tableName AND INDEX_NAME = indexName
    ) THEN
        SET @sql_stmt = indexSql;
        PREPARE stmt FROM @sql_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//
DELIMITER ;

CALL add_index_if_missing('safety_observations', 'idx_so_observation_no', 'CREATE INDEX idx_so_observation_no ON safety_observations(observation_no)');
CALL add_index_if_missing('safety_observations', 'idx_so_zone_id', 'CREATE INDEX idx_so_zone_id ON safety_observations(zone_id)');
CALL add_index_if_missing('safety_observations', 'idx_so_status_id', 'CREATE INDEX idx_so_status_id ON safety_observations(status_id)');
CALL add_index_if_missing('safety_observations', 'idx_so_department_id', 'CREATE INDEX idx_so_department_id ON safety_observations(department_id)');
CALL add_index_if_missing('safety_observations', 'idx_so_observation_date', 'CREATE INDEX idx_so_observation_date ON safety_observations(observation_date)');
CALL add_index_if_missing('near_miss_reports', 'idx_nm_near_miss_no', 'CREATE INDEX idx_nm_near_miss_no ON near_miss_reports(near_miss_no)');
CALL add_index_if_missing('safety_suggestions', 'idx_sug_suggestion_no', 'CREATE INDEX idx_sug_suggestion_no ON safety_suggestions(suggestion_no)');
CALL add_index_if_missing('notifications', 'idx_notifications_user_read', 'CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read)');
CALL add_index_if_missing('audit_logs', 'idx_audit_user_id', 'CREATE INDEX idx_audit_user_id ON audit_logs(user_id)');
CALL add_index_if_missing('audit_logs', 'idx_audit_created_at', 'CREATE INDEX idx_audit_created_at ON audit_logs(created_at)');

DROP PROCEDURE IF EXISTS add_index_if_missing;

INSERT IGNORE INTO safety_observations
(observation_no, reported_by, zone_id, eic_id, department_id, category_id, status_id, specific_area_location, observation_description, immediate_action, recommended_action, risk_level, accident_type, observation_date, observation_time, target_closing_date, recorded_by_name)
VALUES
('OBS-20260605-0101', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-2'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Mechanical'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Boiler platform', 'Temporary lighting cable found across passage.', 'Area supervisor informed.', 'Route cable through proper hook.', 'Medium', 'None', '2026-06-05', '10:00:00', '2026-06-12', '007388 BHUPENDRA SINGH'),
('OBS-20260605-0102', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-4'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Operation'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'CCR back side', 'Waste material kept near emergency exit.', 'Material identified for removal.', 'Keep exit route clear.', 'Low', 'None', '2026-06-05', '11:00:00', '2026-06-13', '007388 BHUPENDRA SINGH'),
('OBS-20260605-0103', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-6'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Water Treatment'), (SELECT id FROM observation_categories WHERE category_name='PPE Violation'), (SELECT id FROM observation_statuses WHERE status_name='Under Review'), 'Chemical dosing area', 'Face shield not used during chemical handling.', 'Work stopped for PPE compliance.', 'Display PPE chart and monitor compliance.', 'High', 'None', '2026-06-05', '12:00:00', '2026-06-10', '001001 PANKAJ SINGH'),
('OBS-20260605-0104', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-8'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Electrical'), (SELECT id FROM observation_categories WHERE category_name='Electrical Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Action Taken'), 'Switchyard panel row', 'Cable gland opening not sealed.', 'Panel area checked.', 'Seal gland opening properly.', 'High', 'None', '2026-06-05', '13:00:00', '2026-06-09', '001002 BAIJNATH SWAMY'),
('OBS-20260605-0105', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-10'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='CHP'), (SELECT id FROM observation_categories WHERE category_name='Fire Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'FOPH approach', 'Fire bucket stand found empty.', 'Shift incharge informed.', 'Refill fire buckets and inspect daily.', 'Medium', 'None', '2026-06-05', '14:00:00', '2026-06-11', '001003 GURPREET SINGH HEER'),
('OBS-20260605-0106', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-12'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Civil'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Workshop shed', 'Damaged floor tile causing trip hazard.', 'Caution tape fixed.', 'Repair damaged tile.', 'Low', 'None', '2026-06-05', '15:00:00', '2026-06-15', '001004 ABHISHEK MAURYA'),
('OBS-20260605-0107', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-14'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Ash Handling'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Act'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'Ash dyke corridor', 'Two wheeler parked near work access route.', 'Owner informed to remove vehicle.', 'Mark no parking area.', 'Low', 'None', '2026-06-05', '16:00:00', '2026-06-12', '007388 BHUPENDRA SINGH'),
('OBS-20260605-0108', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-16'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Safety'), (SELECT id FROM observation_categories WHERE category_name='Working at Height'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Ash corridor pipe rack', 'Worker seen without double lanyard anchorage.', 'Work paused and supervisor informed.', 'Ensure full body harness anchorage.', 'Critical', 'None', '2026-06-05', '16:30:00', '2026-06-07', '001001 PANKAJ SINGH'),
('OBS-20260605-0109', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-17'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Operation'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'MUWPH valve area', 'Valve pit cover loose.', 'Area barricaded.', 'Fix valve pit cover.', 'Medium', 'None', '2026-06-05', '17:00:00', '2026-06-13', '001002 BAIJNATH SWAMY'),
('OBS-20260605-0110', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-18'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Administration'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Closed'), 'Medical college entry', 'Construction debris kept near pedestrian path.', 'Debris removed.', 'Maintain routine housekeeping.', 'Low', 'None', '2026-06-05', '17:30:00', '2026-06-10', '001003 GURPREET SINGH HEER');

INSERT IGNORE INTO near_miss_reports
(near_miss_no, reported_by, zone_id, department_id, incident_location, incident_description, possible_consequence, preventive_action, status_id, incident_date, incident_time)
VALUES
('NM-20260604-0101', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-1'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Boiler lift area', 'A spanner slipped from hand but did not hit anyone.', 'Could have caused minor injury.', 'Use tool lanyard where required.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04', '09:20:00'),
('NM-20260604-0102', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-3'), (SELECT id FROM departments WHERE department_name='Ash Handling'), 'AHP pipe rack', 'Small ash slurry splash occurred near walkway.', 'Slip hazard could occur.', 'Clean walkway and check flange.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04', '10:20:00'),
('NM-20260604-0103', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-7'), (SELECT id FROM departments WHERE department_name='Electrical'), 'Transformer yard', 'Unauthorized entry was stopped at gate.', 'Exposure to restricted electrical area.', 'Improve gate signage.', (SELECT id FROM observation_statuses WHERE status_name='Under Review'), '2026-06-04', '11:20:00'),
('NM-20260604-0104', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-11'), (SELECT id FROM departments WHERE department_name='Safety'), 'Chimney approach', 'Barricade almost fell during wind.', 'Could block access road.', 'Secure barricade properly.', (SELECT id FROM observation_statuses WHERE status_name='Assigned'), '2026-06-04', '12:20:00'),
('NM-20260604-0105', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-12'), (SELECT id FROM departments WHERE department_name='Civil'), 'Store area', 'Material stack shifted but did not fall.', 'Material fall injury possible.', 'Restack material safely.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04', '13:20:00');

INSERT IGNORE INTO safety_suggestions
(suggestion_no, submitted_by, zone_id, department_id, suggestion_title, suggestion_description, expected_benefit, status_id, submitted_date)
VALUES
('SUG-20260604-0101', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-2'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Daily walkway inspection', 'Daily checklist may be used for boiler walkway condition.', 'Early removal of unsafe conditions.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04'),
('SUG-20260604-0102', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-4'), (SELECT id FROM departments WHERE department_name='Operation'), 'Emergency exit marking', 'Emergency exit marking can be repainted in CCR back side.', 'Improved evacuation visibility.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04'),
('SUG-20260604-0103', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-8'), (SELECT id FROM departments WHERE department_name='Electrical'), 'Panel lock checklist', 'Electrical panel lock checklist should be reviewed weekly.', 'Reduced electrical exposure.', (SELECT id FROM observation_statuses WHERE status_name='Under Review'), '2026-06-04'),
('SUG-20260604-0104', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-10'), (SELECT id FROM departments WHERE department_name='Safety'), 'Fire equipment access marking', 'Mark floor area around fire extinguishers and buckets.', 'Fire equipment access remains clear.', (SELECT id FROM observation_statuses WHERE status_name='Assigned'), '2026-06-04'),
('SUG-20260604-0105', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-12'), (SELECT id FROM departments WHERE department_name='Civil'), 'Trip hazard reporting board', 'Place a simple reporting board near workshop for trip hazards.', 'Faster reporting of civil defects.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-04');

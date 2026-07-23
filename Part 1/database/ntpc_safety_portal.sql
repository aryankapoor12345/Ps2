CREATE DATABASE IF NOT EXISTS ntpc_safety_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ntpc_safety_portal;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS near_miss_reports;
DROP TABLE IF EXISTS safety_suggestions;
DROP TABLE IF EXISTS observation_actions;
DROP TABLE IF EXISTS safety_observations;
DROP TABLE IF EXISTS observation_statuses;
DROP TABLE IF EXISTS observation_categories;
DROP TABLE IF EXISTS zone_eic;
DROP TABLE IF EXISTS zone_leaders;
DROP TABLE IF EXISTS safety_zones;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS app_settings;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    role_description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) UNIQUE NOT NULL,
    department_code VARCHAR(20),
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(30) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(80) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    department_id INT NULL,
    designation VARCHAR(120),
    mobile VARCHAR(20),
    email VARCHAR(150),
    is_active TINYINT DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE safety_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_code VARCHAR(30) UNIQUE NOT NULL,
    zone_name VARCHAR(255) NOT NULL,
    short_name VARCHAR(255),
    description TEXT,
    is_active TINYINT DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zone_leaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    user_id INT NOT NULL,
    leader_type ENUM('Zone Leader','Dy. Leader','Assistant Leader') DEFAULT 'Zone Leader',
    is_primary TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_zone_leaders_zone FOREIGN KEY (zone_id) REFERENCES safety_zones(id),
    CONSTRAINT fk_zone_leaders_user FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY uq_zone_leader (zone_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zone_eic (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    user_id INT NOT NULL,
    department_id INT NULL,
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_zone_eic_zone FOREIGN KEY (zone_id) REFERENCES safety_zones(id),
    CONSTRAINT fk_zone_eic_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_zone_eic_department FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE observation_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description VARCHAR(255),
    severity_level ENUM('Low','Medium','High','Critical') DEFAULT 'Low',
    is_active TINYINT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE observation_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_name VARCHAR(80) UNIQUE NOT NULL,
    description VARCHAR(255),
    is_final TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE safety_observations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observation_no VARCHAR(50) UNIQUE NOT NULL,
    reported_by INT NOT NULL,
    zone_id INT NOT NULL,
    eic_id INT NULL,
    department_id INT NOT NULL,
    category_id INT NOT NULL,
    status_id INT NOT NULL,
    specific_area_location VARCHAR(255) NOT NULL,
    observation_description TEXT NOT NULL,
    immediate_action TEXT NULL,
    recommended_action TEXT NULL,
    risk_level ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
    accident_type ENUM('None','Near Miss','Minor','Major','Fatal') DEFAULT 'None',
    observation_date DATE NOT NULL,
    observation_time TIME NULL,
    target_closing_date DATE NULL,
    closed_date DATE NULL,
    attachment_path VARCHAR(255) NULL,
    attachment_original_name VARCHAR(255) NULL,
    recorded_by_name VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    CONSTRAINT fk_observations_reported_by FOREIGN KEY (reported_by) REFERENCES users(id),
    CONSTRAINT fk_observations_zone FOREIGN KEY (zone_id) REFERENCES safety_zones(id),
    CONSTRAINT fk_observations_eic FOREIGN KEY (eic_id) REFERENCES users(id),
    CONSTRAINT fk_observations_department FOREIGN KEY (department_id) REFERENCES departments(id),
    CONSTRAINT fk_observations_category FOREIGN KEY (category_id) REFERENCES observation_categories(id),
    CONSTRAINT fk_observations_status FOREIGN KEY (status_id) REFERENCES observation_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE observation_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    observation_id INT NOT NULL,
    action_by INT NOT NULL,
    action_text TEXT NOT NULL,
    old_status_id INT NULL,
    new_status_id INT NOT NULL,
    action_attachment_path VARCHAR(255) NULL,
    action_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_actions_observation FOREIGN KEY (observation_id) REFERENCES safety_observations(id),
    CONSTRAINT fk_actions_action_by FOREIGN KEY (action_by) REFERENCES users(id),
    CONSTRAINT fk_actions_old_status FOREIGN KEY (old_status_id) REFERENCES observation_statuses(id),
    CONSTRAINT fk_actions_new_status FOREIGN KEY (new_status_id) REFERENCES observation_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE safety_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    suggestion_no VARCHAR(50) UNIQUE NOT NULL,
    submitted_by INT NOT NULL,
    zone_id INT NULL,
    department_id INT NULL,
    suggestion_title VARCHAR(255) NOT NULL,
    suggestion_description TEXT NOT NULL,
    expected_benefit TEXT NULL,
    status_id INT NOT NULL,
    submitted_date DATE NOT NULL,
    attachment_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_suggestions_user FOREIGN KEY (submitted_by) REFERENCES users(id),
    CONSTRAINT fk_suggestions_zone FOREIGN KEY (zone_id) REFERENCES safety_zones(id),
    CONSTRAINT fk_suggestions_department FOREIGN KEY (department_id) REFERENCES departments(id),
    CONSTRAINT fk_suggestions_status FOREIGN KEY (status_id) REFERENCES observation_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE near_miss_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    near_miss_no VARCHAR(50) UNIQUE NOT NULL,
    reported_by INT NOT NULL,
    zone_id INT NOT NULL,
    department_id INT NOT NULL,
    incident_location VARCHAR(255) NOT NULL,
    incident_description TEXT NOT NULL,
    possible_consequence TEXT,
    preventive_action TEXT,
    status_id INT NOT NULL,
    incident_date DATE NOT NULL,
    incident_time TIME NULL,
    attachment_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_near_miss_user FOREIGN KEY (reported_by) REFERENCES users(id),
    CONSTRAINT fk_near_miss_zone FOREIGN KEY (zone_id) REFERENCES safety_zones(id),
    CONSTRAINT fk_near_miss_department FOREIGN KEY (department_id) REFERENCES departments(id),
    CONSTRAINT fk_near_miss_status FOREIGN KEY (status_id) REFERENCES observation_statuses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_type VARCHAR(50),
    related_id INT,
    is_read TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    ip_address VARCHAR(80),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (role_name, role_description) VALUES
('ADMIN', 'System administrator'),
('SAFETY_ADMIN', 'Safety administrator'),
('ZONE_LEADER', 'Zone leader and deputy leader'),
('ENGINEER_INCHARGE', 'Engineer incharge for safety zone'),
('EMPLOYEE', 'Employee or worker reporting safety record');

INSERT INTO departments (department_name, department_code) VALUES
('Mechanical', 'MECH'),
('Electrical', 'ELEC'),
('Civil', 'CIVIL'),
('C&I', 'CI'),
('Operation', 'OPN'),
('Safety', 'SAFE'),
('CHP', 'CHP'),
('Ash Handling', 'AHP'),
('Water Treatment', 'WTP'),
('Administration', 'ADMIN');

INSERT INTO users (employee_id, full_name, username, password_hash, role_id, department_id, designation) VALUES
('007388', 'BHUPENDRA SINGH', 'bhupendra', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='EMPLOYEE'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Technician'),
('001001', 'PANKAJ SINGH', 'pankaj', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ZONE_LEADER'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Zone Leader'),
('001002', 'BAIJNATH SWAMY', 'baijnath', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ZONE_LEADER'), (SELECT id FROM departments WHERE department_name='Electrical'), 'Dy. Zone Leader'),
('001003', 'GURPREET SINGH HEER', 'gurpreet', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ZONE_LEADER'), (SELECT id FROM departments WHERE department_name='Safety'), 'Dy. Zone Leader'),
('001004', 'ABHISHEK MAURYA', 'abhishek', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ZONE_LEADER'), (SELECT id FROM departments WHERE department_name='Civil'), 'Dy. Zone Leader'),
('002001', 'ASHOK KUMAR PATRO', 'ashok', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ENGINEER_INCHARGE'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Engineer Incharge'),
('009999', 'SYSTEM ADMIN', 'admin', '$2y$10$CwTycUXWue0Thq9StjUM0uJ8LUoe7S7Vtfb9U7cN5RoPSbYmydO6W', (SELECT id FROM roles WHERE role_name='ADMIN'), (SELECT id FROM departments WHERE department_name='Safety'), 'Administrator');

INSERT INTO safety_zones (zone_code, zone_name, short_name, description, display_order) VALUES
('ZONE-1', 'ZONE-1 Boiler 1 and Aux Boiler', 'ZONE-1 (Boiler 1 & Aux)', 'Boiler 1 and auxiliary boiler area', 1),
('ZONE-2', 'ZONE-2 Boiler 2', 'ZONE-2 (Boiler - 2)', 'Boiler 2 area', 2),
('ZONE-3', 'ZONE-3 ESP 1,2 and AHP', 'ZONE-3 (ESP 1 & 2)', 'ESP 1, ESP 2 and ash handling area', 3),
('ZONE-4', 'ZONE-4 MPH 1 and CCR', 'ZONE-4 (MPH - 1)', 'Main power house 1 and control room', 4),
('ZONE-5', 'ZONE-5 MPH 2', 'ZONE-5 (MPH - 2 & CCR)', 'Main power house 2 area', 5),
('ZONE-6', 'ZONE-6 WTP, DM Plant', 'ZONE-6 (WTP/DM Plant & AHP)', 'Water treatment and DM plant area', 6),
('ZONE-7', 'ZONE-7 Switchyard and All Transformers and Switchgear', 'ZONE-7 (Transformer Yard)', 'Transformer yard and switchgear area', 7),
('ZONE-8', 'ZONE-8 IDCT 1,2 and CWPH', 'ZONE-8 (Switchyard)', 'IDCT and CWPH area', 8),
('ZONE-9', 'ZONE-9 CHP', 'ZONE-9 (IDCT 1 & 2, CWPH)', 'Coal handling plant area', 9),
('ZONE-10', 'ZONE-10 LDO and FOPH', 'ZONE-10 (CHP)', 'LDO and fuel oil pump house', 10),
('ZONE-11', 'ZONE-11 Adm Bldg, ROADS, BOP and Chimney', 'ZONE-11 (Chimney, Reservoir & RWPH)', 'Administration building, roads, BOP and chimney', 11),
('ZONE-12', 'ZONE-12 Township', 'ZONE-12 (Site Office, Store & Workshop)', 'Township area', 12),
('ZONE-13', 'ZONE-13 MGR and RLY SIDING', 'ZONE-13 (FOPH, ADM BLD & MGR Deadend)', 'MGR and railway siding', 13),
('ZONE-14', 'ZONE-14 ASH Dyke and ASH Dyke Corridor', 'ZONE-14 (TOWNSHIP)', 'Ash dyke and ash dyke corridor', 14),
('ZONE-15', 'ZONE-15 FGD and Reservoir', 'ZONE-15 (MGR & Railway Siding)', 'FGD and reservoir area', 15),
('ZONE-16', 'ZONE-16 Ash Dyke and Corridor', 'ZONE-16 (Ash Dyke & Corridor)', 'Ash dyke and corridor area', 16),
('ZONE-17', 'ZONE-17 MUWPH and Pipeline', 'ZONE-17 (MUWPH & Pipeline)', 'MUWPH and pipeline area', 17),
('ZONE-18', 'ZONE-18 Medical College', 'ZONE-18 (Medical College)', 'Medical college area', 18);

INSERT INTO zone_leaders (zone_id, user_id, leader_type, is_primary)
SELECT z.id, u.id, 'Zone Leader', 1 FROM safety_zones z JOIN users u ON u.username='pankaj' WHERE z.zone_code IN ('ZONE-1','ZONE-2','ZONE-3','ZONE-4','ZONE-5','ZONE-6');
INSERT INTO zone_leaders (zone_id, user_id, leader_type, is_primary)
SELECT z.id, u.id, 'Dy. Leader', 0 FROM safety_zones z JOIN users u ON u.username='baijnath' WHERE z.zone_code IN ('ZONE-7','ZONE-8','ZONE-9','ZONE-10');
INSERT INTO zone_leaders (zone_id, user_id, leader_type, is_primary)
SELECT z.id, u.id, 'Dy. Leader', 0 FROM safety_zones z JOIN users u ON u.username='gurpreet' WHERE z.zone_code IN ('ZONE-11','ZONE-12','ZONE-13','ZONE-14');
INSERT INTO zone_leaders (zone_id, user_id, leader_type, is_primary)
SELECT z.id, u.id, 'Dy. Leader', 0 FROM safety_zones z JOIN users u ON u.username='abhishek' WHERE z.zone_code IN ('ZONE-15','ZONE-16','ZONE-17','ZONE-18');

INSERT INTO zone_eic (zone_id, user_id, department_id)
SELECT z.id, u.id, u.department_id FROM safety_zones z JOIN users u ON u.username='ashok';

INSERT INTO observation_categories (category_name, description, severity_level) VALUES
('Unsafe Act', 'Unsafe act observed in work area', 'Medium'),
('Unsafe Condition', 'Unsafe condition observed in plant area', 'Medium'),
('Near Miss', 'Near miss incident record', 'High'),
('Fatal Accident', 'Fatal accident observation category', 'Critical'),
('Major Accident', 'Major accident observation category', 'Critical'),
('Minor Accident', 'Minor accident observation category', 'Medium'),
('Fire Hazard', 'Fire hazard or blocked fire equipment', 'High'),
('Electrical Hazard', 'Electrical hazard condition', 'High'),
('Working at Height', 'Unsafe condition for height work', 'High'),
('PPE Violation', 'Personal protective equipment violation', 'Medium'),
('Housekeeping', 'Housekeeping related observation', 'Low'),
('Suggestion', 'Safety improvement suggestion', 'Low');

INSERT INTO observation_statuses (status_name, description, is_final) VALUES
('Open', 'Record submitted and open', 0),
('Under Review', 'Record under review', 0),
('Assigned', 'Action assigned to concerned person', 0),
('Action Taken', 'Action taken and pending closure', 0),
('Closed', 'Record closed', 1),
('Rejected', 'Record rejected', 1);

INSERT INTO app_settings (setting_key, setting_value) VALUES
('site_name', 'NTPC Online Safety Portal'),
('max_upload_size_mb', '2'),
('allowed_upload_formats', 'pdf,jpg,jpeg,png,doc,docx'),
('total_safety_observations_display', '20046'),
('total_safety_suggestions_display', '0'),
('total_reported_near_miss_display', '0');

INSERT INTO safety_observations
(observation_no, reported_by, zone_id, eic_id, department_id, category_id, status_id, specific_area_location, observation_description, immediate_action, recommended_action, risk_level, accident_type, observation_date, observation_time, target_closing_date, recorded_by_name)
VALUES
('OBS-20260601-0001', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-1'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Mechanical'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Boiler floor near oil station', 'Oil leakage near boiler floor.', 'Area barricaded and informed maintenance.', 'Attend leakage and clean floor.', 'High', 'None', '2026-06-01', '10:15:00', '2026-06-05', 'BHUPENDRA SINGH'),
('OBS-20260601-0002', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-2'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Electrical'), (SELECT id FROM observation_categories WHERE category_name='Electrical Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'Walkway near panel room', 'Loose cable lying near walkway.', 'Cable shifted to side.', 'Provide proper cable dressing.', 'Medium', 'None', '2026-06-01', '11:20:00', '2026-06-04', 'BHUPENDRA SINGH'),
('OBS-20260601-0003', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-3'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Ash Handling'), (SELECT id FROM observation_categories WHERE category_name='PPE Violation'), (SELECT id FROM observation_statuses WHERE status_name='Under Review'), 'AHP pump area', 'Worker not using safety helmet.', 'Worker counseled at site.', 'Ensure PPE checking before work start.', 'Medium', 'None', '2026-06-01', '12:05:00', '2026-06-06', 'PANKAJ SINGH'),
('OBS-20260602-0001', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-4'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Civil'), (SELECT id FROM observation_categories WHERE category_name='Working at Height'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'MPH maintenance area', 'Unsafe scaffolding found near maintenance area.', 'Work stopped temporarily.', 'Inspect and tag scaffold before use.', 'High', 'None', '2026-06-02', '09:10:00', '2026-06-06', 'BAIJNATH SWAMY'),
('OBS-20260602-0002', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-5'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Operation'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Action Taken'), 'Pump area', 'Water accumulation near pump area.', 'Caution board placed.', 'Drain water and check source.', 'Low', 'None', '2026-06-02', '14:25:00', '2026-06-07', 'BHUPENDRA SINGH'),
('OBS-20260602-0003', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-6'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Water Treatment'), (SELECT id FROM observation_categories WHERE category_name='Fire Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'DM plant chemical store', 'Fire extinguisher blocked by material.', 'Material removed from front side.', 'Maintain clear access to extinguisher.', 'High', 'None', '2026-06-02', '15:40:00', '2026-06-05', 'GURPREET SINGH HEER'),
('OBS-20260603-0001', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-7'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Civil'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Closed'), 'Transformer yard stairs', 'Handrail damaged near stair.', 'Temporary barricade provided.', 'Handrail repaired and checked.', 'Medium', 'None', '2026-06-03', '08:35:00', '2026-06-08', 'ABHISHEK MAURYA'),
('OBS-20260603-0002', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-8'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Operation'), (SELECT id FROM observation_categories WHERE category_name='PPE Violation'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'CWPH entrance', 'PPE violation observed.', 'Person advised to use proper PPE.', 'Supervisor to ensure compliance.', 'Medium', 'None', '2026-06-03', '10:05:00', '2026-06-09', 'BHUPENDRA SINGH'),
('OBS-20260603-0003', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-9'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='CHP'), (SELECT id FROM observation_categories WHERE category_name='Electrical Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'CHP local panel', 'Electrical panel door open.', 'Informed electrical shift staff.', 'Repair panel lock and close door.', 'High', 'None', '2026-06-03', '11:50:00', '2026-06-06', 'BAIJNATH SWAMY'),
('OBS-20260603-0004', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-10'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='CHP'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Under Review'), 'CHP conveyor area', 'Housekeeping required near CHP conveyor.', 'Area marked for cleaning.', 'Remove accumulated material.', 'Low', 'None', '2026-06-03', '13:15:00', '2026-06-10', 'PANKAJ SINGH'),
('OBS-20260604-0001', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-11'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Administration'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Administration building road', 'Loose paver block found on walkway.', 'Area marked with caution tape.', 'Repair walkway paver block.', 'Low', 'None', '2026-06-04', '09:05:00', '2026-06-12', 'BHUPENDRA SINGH'),
('OBS-20260604-0002', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-12'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Civil'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'Township service road', 'Open drain cover observed.', 'Road cone placed nearby.', 'Provide proper drain cover.', 'Medium', 'None', '2026-06-04', '09:45:00', '2026-06-09', 'GURPREET SINGH HEER'),
('OBS-20260604-0003', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-13'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Mechanical'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Act'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'MGR deadend area', 'Work material stored on access path.', 'Informed area supervisor.', 'Shift material to designated place.', 'Medium', 'None', '2026-06-04', '10:25:00', '2026-06-08', 'ABHISHEK MAURYA'),
('OBS-20260604-0004', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-14'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Ash Handling'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Closed'), 'Ash dyke road', 'Dust accumulation on approach road.', 'Water sprinkling arranged.', 'Continue regular sprinkling schedule.', 'Low', 'None', '2026-06-04', '11:35:00', '2026-06-11', 'BHUPENDRA SINGH'),
('OBS-20260604-0005', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-15'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Mechanical'), (SELECT id FROM observation_categories WHERE category_name='Fire Hazard'), (SELECT id FROM observation_statuses WHERE status_name='Action Taken'), 'FGD pump floor', 'Combustible waste kept near work area.', 'Waste shifted away.', 'Maintain waste disposal bin.', 'Medium', 'None', '2026-06-04', '12:20:00', '2026-06-08', 'PANKAJ SINGH'),
('OBS-20260604-0006', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-16'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Ash Handling'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Ash corridor', 'Warning board faded near crossing.', 'Temporary board placed.', 'Install new warning board.', 'Low', 'None', '2026-06-04', '13:00:00', '2026-06-13', 'BAIJNATH SWAMY'),
('OBS-20260604-0007', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-17'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Operation'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Condition'), (SELECT id FROM observation_statuses WHERE status_name='Under Review'), 'MUWPH pipeline area', 'Slippery surface near valve station.', 'Area cleaned partially.', 'Provide anti-skid arrangement.', 'Medium', 'None', '2026-06-04', '14:15:00', '2026-06-12', 'GURPREET SINGH HEER'),
('OBS-20260604-0008', (SELECT id FROM users WHERE username='abhishek'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-18'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Administration'), (SELECT id FROM observation_categories WHERE category_name='Housekeeping'), (SELECT id FROM observation_statuses WHERE status_name='Assigned'), 'Medical college store', 'Carton boxes placed near exit path.', 'Store staff informed.', 'Keep exit path clear.', 'Low', 'None', '2026-06-04', '15:30:00', '2026-06-10', 'ABHISHEK MAURYA'),
('OBS-20260605-0001', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-1'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Mechanical'), (SELECT id FROM observation_categories WHERE category_name='Unsafe Act'), (SELECT id FROM observation_statuses WHERE status_name='Open'), 'Boiler maintenance bay', 'Tool box kept on passage.', 'Tool box shifted aside.', 'Use marked storage place.', 'Low', 'None', '2026-06-05', '08:20:00', '2026-06-09', 'BHUPENDRA SINGH'),
('OBS-20260605-0002', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-3'), (SELECT id FROM users WHERE username='ashok'), (SELECT id FROM departments WHERE department_name='Safety'), (SELECT id FROM observation_categories WHERE category_name='PPE Violation'), (SELECT id FROM observation_statuses WHERE status_name='Rejected'), 'ESP area entry', 'Visitor entered without reflective jacket.', 'Visitor stopped at entry.', 'Observation rejected after duplicate verification.', 'Low', 'None', '2026-06-05', '09:00:00', '2026-06-09', 'PANKAJ SINGH');

INSERT INTO observation_actions (observation_id, action_by, action_text, old_status_id, new_status_id)
VALUES
((SELECT id FROM safety_observations WHERE observation_no='OBS-20260603-0001'), (SELECT id FROM users WHERE username='ashok'), 'Handrail repaired and site checked.', (SELECT id FROM observation_statuses WHERE status_name='Assigned'), (SELECT id FROM observation_statuses WHERE status_name='Closed')),
((SELECT id FROM safety_observations WHERE observation_no='OBS-20260604-0004'), (SELECT id FROM users WHERE username='ashok'), 'Water sprinkling completed and road cleared.', (SELECT id FROM observation_statuses WHERE status_name='Action Taken'), (SELECT id FROM observation_statuses WHERE status_name='Closed'));

INSERT INTO safety_suggestions (suggestion_no, submitted_by, zone_id, department_id, suggestion_title, suggestion_description, expected_benefit, status_id, submitted_date)
VALUES
('SUG-20260601-0001', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-1'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'Improve helmet checking at gate', 'Daily PPE checking register may be maintained at main entry point.', 'Better compliance of PPE rules.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-01'),
('SUG-20260602-0001', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-9'), (SELECT id FROM departments WHERE department_name='CHP'), 'Mark conveyor walkway storage line', 'Paint yellow line to avoid material storage on walkway.', 'Clear access and better housekeeping.', (SELECT id FROM observation_statuses WHERE status_name='Under Review'), '2026-06-02'),
('SUG-20260603-0001', (SELECT id FROM users WHERE username='gurpreet'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-6'), (SELECT id FROM departments WHERE department_name='Water Treatment'), 'Display chemical PPE chart', 'Chemical handling PPE chart should be displayed near dosing area.', 'Improved worker awareness.', (SELECT id FROM observation_statuses WHERE status_name='Assigned'), '2026-06-03');

INSERT INTO near_miss_reports (near_miss_no, reported_by, zone_id, department_id, incident_location, incident_description, possible_consequence, preventive_action, status_id, incident_date, incident_time)
VALUES
('NM-20260601-0001', (SELECT id FROM users WHERE username='bhupendra'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-5'), (SELECT id FROM departments WHERE department_name='Mechanical'), 'MPH 2 pump area', 'A worker slipped slightly due to wet floor but regained balance.', 'Slip and fall injury could occur.', 'Clean wet floor and repair seepage source.', (SELECT id FROM observation_statuses WHERE status_name='Open'), '2026-06-01', '10:30:00'),
('NM-20260602-0001', (SELECT id FROM users WHERE username='baijnath'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-7'), (SELECT id FROM departments WHERE department_name='Electrical'), 'Transformer yard gate', 'A vehicle came close to barricaded electrical area.', 'Vehicle contact with restricted area barricade.', 'Improve vehicle route marking and supervision.', (SELECT id FROM observation_statuses WHERE status_name='Under Review'), '2026-06-02', '16:10:00'),
('NM-20260603-0001', (SELECT id FROM users WHERE username='pankaj'), (SELECT id FROM safety_zones WHERE zone_code='ZONE-10'), (SELECT id FROM departments WHERE department_name='CHP'), 'CHP conveyor transfer point', 'Small lump fell near walkway without injury.', 'Person passing nearby could be hit.', 'Check chute sealing and clean spillage.', (SELECT id FROM observation_statuses WHERE status_name='Assigned'), '2026-06-03', '12:05:00');

INSERT INTO notifications (user_id, title, message, related_type, related_id)
VALUES
((SELECT id FROM users WHERE username='ashok'), 'New Observation Assigned', 'Observation OBS-20260601-0001 has been assigned for review.', 'observation', (SELECT id FROM safety_observations WHERE observation_no='OBS-20260601-0001')),
((SELECT id FROM users WHERE username='pankaj'), 'Safety Suggestion Submitted', 'Safety suggestion SUG-20260602-0001 is under review.', 'suggestion', (SELECT id FROM safety_suggestions WHERE suggestion_no='SUG-20260602-0001'));

INSERT INTO audit_logs (user_id, action, table_name, record_id, ip_address, user_agent)
VALUES
((SELECT id FROM users WHERE username='admin'), 'Database seed import', 'app_settings', 1, '127.0.0.1', 'Local import');

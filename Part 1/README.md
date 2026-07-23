# NTPC Online Safety Portal

A local PHP/MySQL portal for recording thermal plant safety observations, near misses, suggestions, zone-wise reports, and department pending dues.

## Technology

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- mysqli prepared statements

## Local Setup

1. Copy the `ntpc_safety_portal` folder to your XAMPP `htdocs` folder.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open phpMyAdmin:
   `http://localhost/phpmyadmin/`
4. Import:
   `database/ntpc_safety_portal.sql`
5. Import the final patch:
   `database/part4_updates.sql`
6. Open setup check:
   `http://localhost/ntpc_safety_portal/setup_check.php`
7. Open the portal:
   `http://localhost/ntpc_safety_portal/`

## Default Login Accounts

Admin:
- username: `admin`
- password: `password123`

Employee:
- username: `bhupendra`
- password: `password123`

Zone Leader:
- username: `pankaj`
- password: `password123`

EIC:
- username: `ashok`
- password: `password123`

## Folder Structure

- `config/` database and application settings
- `includes/` shared layout, auth, CSRF, flash, and helper functions
- `modules/observations/` safety observation create, list, view, update, close
- `modules/near_miss/` near miss reporting
- `modules/suggestions/` safety suggestions
- `modules/reports/` reports and CSV exports
- `modules/admin/` user, zone, department, category, settings, audit, backup
- `modules/ajax/` zone, EIC, department, and status endpoints
- `uploads/` uploaded documents
- `database/` main SQL and patch SQL files

## Main Features

- Login/logout
- Dashboard
- Safety observation reporting
- File upload
- Zone leader/EIC mapping
- Observation update and close
- Near miss reporting
- Safety suggestions
- Reports
- Admin management
- CSV export
- Audit logs
- Notifications
- Profile and change password

## Test Flow

1. Login as `bhupendra / password123`.
2. Create a safety observation.
3. View the observation.
4. Logout.
5. Login as `admin / password123`.
6. Open observation list.
7. Update the observation.
8. Close the observation.
9. Open dashboard report.
10. Export pending CSV.
11. Open admin users.
12. Add a test user.
13. Change own password.
14. View audit logs.
15. Open notifications.

## Troubleshooting

Database connection error:
- Start MySQL in XAMPP.
- Import `database/ntpc_safety_portal.sql`.
- Check `config/database.php` values.

Upload folder permission:
- Ensure `uploads/`, `uploads/observations/`, `uploads/actions/`, `uploads/suggestions/`, and `uploads/near_miss/` are writable.

SQL import issue:
- Import the main SQL first, then `part4_updates.sql`.
- If an index already exists, duplicate index messages can be ignored.

BASE_URL issue:
- If the folder name changes, update `BASE_URL` in `config/constants.php`.

File upload size issue:
- Portal allows up to 2 MB.
- PHP settings `upload_max_filesize` and `post_max_size` should be at least 2 MB.

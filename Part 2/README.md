# NTPC Online Safety Portal

A plain PHP/MySQL portal for recording thermal plant safety observations, near misses, suggestions, zone-wise reports, and department pending dues.

## Requirements

- InfinityFree free hosting account
- PHP + MySQL hosting
- phpMyAdmin
- FTP client or InfinityFree File Manager
- No Composer, Node, npm, or command-line tools required

## Upload Steps for InfinityFree

1. Create an InfinityFree hosting account.
2. Create a website/subdomain.
3. Open File Manager or connect by FTP.
4. Upload all project files into `htdocs`.
5. Create a MySQL database in InfinityFree control panel.
6. Open InfinityFree phpMyAdmin and select the created database.
7. Import `database/infinityfree_import.sql`.
8. Edit `config/database.php` with the InfinityFree database details.
9. Visit `/setup_check.php`.
10. Login using `admin / password123`.
11. Change the admin password from Profile -> Change Password.

## Database Configuration

Edit this file before upload or immediately after upload:

`config/database.php`

Use the values shown in InfinityFree Control Panel -> MySQL Databases:

- `DB_HOST`: InfinityFree MySQL host such as `sqlXXX.infinityfree.com`
- `DB_NAME`: database name such as `if0_xxxxxxxx_ntpc_safety`
- `DB_USER`: username such as `if0_xxxxxxxx`
- `DB_PASS`: your database password

Do not use a localhost database host on InfinityFree.

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
- `database/` SQL import files

## Main Features

- Login/logout
- Dashboard
- Safety observation reporting
- File upload up to 2 MB
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

## Important Notes

- Do not use a localhost DB host on InfinityFree.
- Use the DB hostname shown in InfinityFree control panel.
- Keep uploaded attachments small.
- If import fails, retry from phpMyAdmin after clearing the database tables.
- If CSS or links break, check `BASE_URL` detection in `config/constants.php`.
- Backup page is for small academic/project use. For a large database, use phpMyAdmin Export.

## Troubleshooting

Database connection error:
- Confirm DB details in `config/database.php`.
- Confirm database was created in InfinityFree control panel.
- Confirm `database/infinityfree_import.sql` was imported.

Upload folder permission:
- Ensure `uploads/`, `uploads/observations/`, `uploads/actions/`, `uploads/suggestions/`, and `uploads/near_miss/` exist.

File upload size issue:
- Portal allows up to 2 MB.
- Hosting PHP limits may be lower; reduce file size if upload fails.

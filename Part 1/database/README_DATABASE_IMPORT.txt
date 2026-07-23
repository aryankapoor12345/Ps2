NTPC Online Safety Portal - Database Import

1. Start XAMPP Apache and MySQL.
2. Open phpMyAdmin in browser:
   http://localhost/phpmyadmin/
3. Import the main SQL file:
   database/ntpc_safety_portal.sql
4. Import patch files after the main SQL if present:
   database/part4_updates.sql
5. Open:
   http://localhost/ntpc_safety_portal/setup_check.php
6. Login:
   username: admin
   password: password123

If duplicate index messages appear while importing a patch file, the index may already exist and can be ignored if setup_check.php shows required tables present.

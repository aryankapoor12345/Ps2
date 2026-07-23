# Testing Checklist - NTPC Safety Portal

Follow these step-by-step testing procedures to verify the entire system functionality after deployment on InfinityFree.

## 1. Database & Diagnostics Test
- [ ] **Access Diagnostics Check**: Navigate to `https://your-site.infinityfreeapp.com/setup_check.php`
  - Verify all 15 check marks are **Green** (or GD/limits are **Yellow** at least).
- [ ] **Database Test**: Navigate to `https://your-site.infinityfreeapp.com/db_test.php`
  - Verify it displays `✔ Database Connected Successfully`.
  - Toggle your password in `.env` to a dummy value and verify that `db_test.php` displays the exact MySQL connection error.
- [ ] **Error Debugger**: Set `APP_ENV=development` in your `.env` and navigate to `debug.php`.
  - Verify that system settings and connection details are displayed.
  - Set `APP_ENV=production` in your `.env` and navigate to `debug.php`.
  - Verify you receive a `403 Forbidden` response.

## 2. Authentication & Sessions
- [ ] **Admin Login**: Go to `login.php`.
  - Log in using credentials: `admin` / `password123`.
  - Verify redirection to `dashboard.php` and that the flash message displays "Login successful".
- [ ] **Sidebar & Session**: Check that the admin sidebar is loaded and name is correct.
- [ ] **Logout**: Click the "Logout" link.
  - Verify redirection to `login.php` with a flash message "You have been logged out".
  - Try accessing `dashboard.php` directly without logging in; verify redirection back to `login.php`.

## 3. Workflow States & Actions
The workflow path is: **User -> Sub Leader -> Zone Leader -> EIC -> Agency -> EIC -> Zone Leader -> Closed**.

- [ ] **Step 3.1: Create Observation (User)**
  - Log in as employee `bhupendra` / `password123`.
  - Create a new safety observation for **ZONE-1 (Boiler 1 & Aux)**.
  - Select Category **Unsafe Act** and fill out details.
  - **Upload a test file** (e.g., JPEG/PDF) in the attachments field.
  - Submit the observation and verify the status is **Reported**.
- [ ] **Step 3.2: Review & Escalate (Sub Leader)**
  - Log in as Sub Leader `subleader` / `password123`.
  - Open the observation from the list and select **Recommend Escalation**.
  - Submit and verify status updates to **Pending Zone Leader Review**.
- [ ] **Step 3.3: Approve & Forward (Zone Leader)**
  - Log in as Zone Leader `pankaj` / `password123`.
  - Open the observation, add verification remarks, and forward to EIC.
  - Verify status updates to **Pending EIC Assignment**.
- [ ] **Step 3.4: Assign Agency (EIC)**
  - Log in as EIC `ashok` / `password123`.
  - Assign the observation to the agency **NTPC Safety Services** with a target due date.
  - Verify status updates to **Assigned to Agency**.
- [ ] **Step 3.5: Start & Complete Work (Agency)**
  - Log in as agency user `agency_user` / `password123`.
  - Change observation status to **Work In Progress**.
  - When finished, submit completion remarks, and **upload a completion proof file**.
  - Verify status updates to **Completed by Agency**.
- [ ] **Step 3.6: Verify Completion (EIC)**
  - Log in as EIC `ashok` / `password123`.
  - Open the observation, inspect the uploaded completion proof, and click **Verify and Forward to Zone Leader**.
  - Verify status updates to **Pending Zone Verification**.
- [ ] **Step 3.7: Close Observation (Zone Leader)**
  - Log in as Zone Leader `pankaj` / `password123`.
  - Verify the proof and click **Approve and Close**.
  - Verify status updates to **Closed**.

## 4. Notifications & Audit Logs
- [ ] **Notifications**: 
  - Verify that EIC receives a notification when a new observation is assigned.
  - Verify that the User receives a notification when their observation is closed.
- [ ] **Audit Trail**: 
  - Log in as `admin`.
  - Go to **Admin -> Audit Logs** (or `modules/admin/audit_logs.php`).
  - Verify that log entries exist for logins, logouts, actions, and migrations.

## 5. Administration Management
- [ ] **Users**: Go to Admin -> Users. Add a new test user and verify.
- [ ] **Roles**: Go to Admin -> Roles. Inspect configured roles.
- [ ] **Departments**: Go to Admin -> Departments. Inspect configured departments.
- [ ] **Zones**: Go to Admin -> Zones. Inspect configured zones.
- [ ] **Agencies**: Go to Admin -> Agencies. Add/edit agencies.

## 6. Reports & Analytics
- [ ] **Analytics Dashboard**: Go to Admin -> Analytics.
  - Verify the SVG dynamic bar charts render without PHP warnings or formatting issues.
- [ ] **Export Reports**: Go to Reports.
  - Click **Export CSV** for Department Dues, Pending, and Zonewise Observations.
  - Verify that the CSV files download correctly and contain exact database records.

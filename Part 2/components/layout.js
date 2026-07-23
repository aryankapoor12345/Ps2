import { logoutUser } from '../firebase/auth.js';
import { getSetting } from '../firebase/firestore.js';

// Renders the top header layout dynamically
export async function renderHeader(user) {
  const container = document.getElementById('header-container');
  if (!container) return;
  
  const portalTitle = await getSetting('portal_title', 'ONLINE SAFETY PORTAL');
  
  let userInfoHtml = '';
  if (user) {
    userInfoHtml = `
      ${user.full_name}<br>
      ${user.role}<br>
      <a href="#" id="header-logout-link" class="logout-link">Logout</a>
    `;
  } else {
    userInfoHtml = `<a href="/login.html">Login</a>`;
  }
  
  container.innerHTML = `
    <td colspan="2" class="top-header">
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="logo-cell">NTPC</td>
                <td class="title-cell">${portalTitle}</td>
                <td class="login-cell">${userInfoHtml}</td>
            </tr>
        </table>
    </td>
  `;
  
  const logoutLink = document.getElementById('header-logout-link');
  if (logoutLink) {
    logoutLink.addEventListener('click', async (e) => {
      e.preventDefault();
      if (confirm("Are you sure you want to logout?")) {
        await logoutUser();
      }
    });
  }
}

// Renders the sidebar layout with conditional items depending on user permissions
export function renderSidebar(user) {
  const container = document.getElementById('sidebar-container');
  if (!container) return;
  
  if (!user) {
    container.style.display = 'none';
    return;
  }
  
  container.style.display = '';
  const role = user.role;
  const canWorkObservation = ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC'].includes(role);
  const canAdmin = ['ADMIN', 'SAFETY_ADMIN'].includes(role);
  const isAgency = role === 'AGENCY';
  
  let menuHtml = '';
  
  if (!isAgency) {
    menuHtml += `
      <li><a href="/pages/suggestions/create.html">Record Suggestion</a></li>
      <li><a href="/pages/near_miss/create.html">Report Near Miss</a></li>
      <li><a href="/pages/observations/create.html">Submit Observation</a></li>
      <li><a href="/search.html">Search Records</a></li>
      <li><a href="#">NTPC Safety Policy Compliance</a></li>
    `;
  }
  
  // Set Safety Observation menu text depending on role
  let obsLabel = 'My Observations';
  if (canWorkObservation) obsLabel = 'Update / Actions';
  else if (isAgency) obsLabel = 'Assigned Jobs';
  
  menuHtml += `
    <li>
        <a href="/pages/observations/list.html">Safety Observation</a>
        <ul>
            <li><a href="/pages/observations/list.html">${obsLabel}</a></li>
        </ul>
    </li>
  `;
  
  if (!isAgency) {
    menuHtml += `
      <li>
          <span>REPORTS</span>
          <ul>
              <li><a href="/pages/reports/dashboard_report.html">Dashboard Report</a></li>
              <li><a href="/pages/reports/department_dues.html">Department Dues</a></li>
              <li><a href="/pages/reports/pending_list.html">Pending List</a></li>
              <li><a href="/pages/reports/zonewise_observations.html">ZoneWise Observations</a></li>
              ${canAdmin ? '<li><a href="/pages/reports/export.html">Export CSV</a></li>' : ''}
          </ul>
      </li>
      <li><a href="#">Safety Permit</a></li>
    `;
  }
  
  if (canAdmin) {
    menuHtml += `
      <li>
          <span>Safety Admin</span>
          <ul>
              <li><a href="/pages/admin/analytics.html">Analytics Dashboard</a></li>
              <li><a href="/pages/admin/users.html">Users</a></li>
              <li><a href="/pages/admin/zones.html">Zones</a></li>
              <li><a href="/pages/admin/departments.html">Departments</a></li>
              <li><a href="/pages/admin/categories.html">Categories</a></li>
              <li><a href="/pages/admin/agencies.html">Agencies</a></li>
              <li><a href="/pages/admin/roles.html">Roles</a></li>
              <li><a href="/pages/admin/settings.html">Settings</a></li>
              <li><a href="/pages/admin/audit_logs.html">Audit Logs</a></li>
              <li><a href="/setup_check.html">Setup Check</a></li>
          </ul>
      </li>
    `;
  }
  
  menuHtml += `
    <li><a href="/profile.html">Profile</a></li>
    <li><a href="/notifications.html">Notifications</a></li>
    <li><a href="#" id="sidebar-logout-link">Logout</a></li>
  `;
  
  container.innerHTML = `
    <div class="side-menu">
        <div class="menu-heading">Menu</div>
        <ul>
            ${menuHtml}
        </ul>
    </div>
  `;
  
  const sideLogout = document.getElementById('sidebar-logout-link');
  if (sideLogout) {
    sideLogout.addEventListener('click', async (e) => {
      e.preventDefault();
      if (confirm("Are you sure you want to logout?")) {
        await logoutUser();
      }
    });
  }
}

// Renders the footer layout dynamically
export function renderFooter() {
  const container = document.getElementById('footer-container');
  if (!container) return;
  
  container.innerHTML = `
    <td colspan="2" class="footer-cell">
        NTPC Online Safety Portal - Local Firebase Project
    </td>
  `;
}

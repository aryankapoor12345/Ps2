// Map of pages to allowed user roles
export const PAGE_PERMISSIONS = {
  'dashboard.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  'profile.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  'notifications.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  'search.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'change_password.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  'setup_check.html': ['ADMIN', 'SAFETY_ADMIN'],
  
  'observations/create.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'observations/list.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  'observations/view.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'AGENCY', 'USER'],
  
  'near_miss/create.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'near_miss/list.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'near_miss/view.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  
  'suggestions/create.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'suggestions/list.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'suggestions/view.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  
  'reports/dashboard_report.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'reports/department_dues.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'reports/pending_list.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  'reports/zonewise_observations.html': ['ADMIN', 'SAFETY_ADMIN', 'ZONE_LEADER', 'SUB_LEADER', 'EIC', 'USER'],
  
  'admin/analytics.html': ['ADMIN', 'SAFETY_ADMIN'],
  'admin/users.html': ['ADMIN'],
  'admin/zones.html': ['ADMIN'],
  'admin/departments.html': ['ADMIN'],
  'admin/categories.html': ['ADMIN'],
  'admin/agencies.html': ['ADMIN'],
  'admin/roles.html': ['ADMIN'],
  'admin/settings.html': ['ADMIN'],
  'admin/audit_logs.html': ['ADMIN']
};

// Returns whether the role has permission to access the current path name
export function checkPagePermission(pathName, role) {
  const cleanPath = pathName.split('?')[0];
  const keys = Object.keys(PAGE_PERMISSIONS);
  
  // Find if any key matches the end of the path
  const matchedKey = keys.find(k => cleanPath.endsWith(k));
  if (matchedKey) {
    const allowedRoles = PAGE_PERMISSIONS[matchedKey];
    return allowedRoles.includes(role);
  }
  
  // Default to true for root folders, login, or public assets
  return true;
}

// Bounces the user if they don't have access permissions
export function enforcePageAccess(user) {
  if (!user) {
    window.location.href = '/login.html';
    return false;
  }
  const hasAccess = checkPagePermission(window.location.pathname, user.role);
  if (!hasAccess) {
    alert("Access Denied: You do not have permissions to view this page.");
    window.location.href = '/dashboard.html';
    return false;
  }
  return true;
}

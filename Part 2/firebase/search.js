import { fetchObservationsForUser } from './dashboard.js';

// Filters the user's accessible observations in memory using optional query filters
export async function searchObservations(user, filters) {
  try {
    // 1. Fetch all observations that the current user has access to
    const observations = await fetchObservationsForUser(user);
    
    // 2. Apply optional filters client-side
    return observations.filter(obs => {
      // Filter by Observation ID (partial string match)
      if (filters.observation_no && !obs.observation_no.toLowerCase().includes(filters.observation_no.toLowerCase())) {
        return false;
      }
      
      // Filter by Department ID
      if (filters.department_id && obs.department_id !== filters.department_id) {
        return false;
      }
      
      // Filter by Zone ID
      if (filters.zone_id && obs.zone_id !== filters.zone_id) {
        return false;
      }
      
      // Filter by Employee ID / Name (partial string match)
      if (filters.employee) {
        const empSearch = filters.employee.toLowerCase();
        const matchName = obs.recorded_by_name && obs.recorded_by_name.toLowerCase().includes(empSearch);
        const matchEmpId = obs.reporter_employee_id && obs.reporter_employee_id.toLowerCase().includes(empSearch);
        const matchReporterRef = obs.reporter_name && obs.reporter_name.toLowerCase().includes(empSearch);
        
        if (!matchName && !matchEmpId && !matchReporterRef) {
          return false;
        }
      }
      
      // Filter by Agency ID
      if (filters.agency_id && obs.assigned_agency_id !== filters.agency_id) {
        return false;
      }
      
      // Filter by Status Name
      if (filters.status_name && obs.status_name !== filters.status_name) {
        return false;
      }
      
      // Filter by Priority / Risk Level
      if (filters.priority) {
        const p = obs.priority || obs.risk_level;
        if (p !== filters.priority) {
          return false;
        }
      }
      
      // Filter by Date Range
      if (filters.date_from && obs.observation_date && obs.observation_date < filters.date_from) {
        return false;
      }
      if (filters.date_to && obs.observation_date && obs.observation_date > filters.date_to) {
        return false;
      }
      
      return true;
    });
  } catch (e) {
    console.error("Observation search failed:", e);
    return [];
  }
}

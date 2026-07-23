import { db } from './firebase.js';
import { collection, getDocs, query, orderBy } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

// Fetches all observations from Firestore for reporting
export async function getReportingData() {
  if (!db) return [];
  try {
    const q = query(collection(db, 'safety_observations'), orderBy('created_at', 'desc'));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error fetching report data:", e);
    return [];
  }
}

// Groups observations client-side into summary structures
export async function generateSummaryReports() {
  const data = await getReportingData();
  
  const pendingReport = [];
  const zoneReport = {};
  const departmentReport = {};
  const agencyReport = {};
  const monthlyReport = {};
  
  data.forEach(obs => {
    const status = obs.status_name;
    const zoneName = obs.zone_short_name || obs.zone_code || 'Unspecified';
    const deptName = obs.department_name || 'Unspecified';
    const agencyName = obs.assigned_agency_name || 'Unassigned';
    
    // Monthly group format: YYYY-MM
    let monthKey = 'Unspecified';
    if (obs.observation_date) {
      monthKey = obs.observation_date.substring(0, 7);
    }
    
    // 1. Filter pending report
    if (status !== 'Closed') {
      pendingReport.push(obs);
    }
    
    // 2. Zone aggregates
    if (!zoneReport[zoneName]) zoneReport[zoneName] = { total: 0, pending: 0, closed: 0 };
    zoneReport[zoneName].total++;
    if (status === 'Closed') zoneReport[zoneName].closed++;
    else zoneReport[zoneName].pending++;
    
    // 3. Department aggregates
    if (!departmentReport[deptName]) departmentReport[deptName] = { total: 0, pending: 0, closed: 0 };
    departmentReport[deptName].total++;
    if (status === 'Closed') departmentReport[deptName].closed++;
    else departmentReport[deptName].pending++;
    
    // 4. Agency aggregates
    if (!['Reported', 'Pending Sub Leader Review', 'Pending Zone Leader Review', 'Pending EIC Assignment'].includes(status)) {
      if (!agencyReport[agencyName]) agencyReport[agencyName] = { total: 0, pending: 0, closed: 0 };
      agencyReport[agencyName].total++;
      if (status === 'Closed') agencyReport[agencyName].closed++;
      else agencyReport[agencyName].pending++;
    }
    
    // 5. Monthly aggregates
    if (!monthlyReport[monthKey]) monthlyReport[monthKey] = { total: 0, pending: 0, closed: 0 };
    monthlyReport[monthKey].total++;
    if (status === 'Closed') monthlyReport[monthKey].closed++;
    else monthlyReport[monthKey].pending++;
  });
  
  return {
    pendingReport,
    zoneReport,
    departmentReport,
    agencyReport,
    monthlyReport,
    rawObservations: data
  };
}

// Client-side CSV export trigger
export function exportToCSV(filename, headers, rows) {
  let csvContent = "\uFEFF"; // UTF-8 BOM for Excel formatting
  
  // Format header row
  csvContent += headers.map(h => `"${String(h).replace(/"/g, '""')}"`).join(",") + "\r\n";
  
  // Format body rows
  rows.forEach(row => {
    csvContent += row.map(cell => {
      const val = cell === null || cell === undefined ? "" : String(cell);
      return `"${val.replace(/"/g, '""')}"`;
    }).join(",") + "\r\n";
  });
  
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const encodedUri = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", filename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

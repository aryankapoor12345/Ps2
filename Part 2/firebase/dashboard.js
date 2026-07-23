import { db } from './firebase.js';
import { collection, getDocs, query, where, orderBy } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

// Fetches all observations accessible to the user based on their role
export async function fetchObservationsForUser(user) {
  if (!db || !user) return [];
  const obsRef = collection(db, 'safety_observations');
  let q;
  const role = user.role;
  
  try {
    if (role === 'ADMIN' || role === 'SAFETY_ADMIN') {
      q = query(obsRef, orderBy('created_at', 'desc'));
    } else if (role === 'ZONE_LEADER' || role === 'SUB_LEADER') {
      const zones = user.zone_ids || [];
      if (zones.length === 0) return [];
      if (zones.length <= 10) {
        q = query(obsRef, where('zone_id', 'in', zones), orderBy('created_at', 'desc'));
      } else {
        q = query(obsRef, orderBy('created_at', 'desc'));
      }
    } else if (role === 'EIC') {
      q = query(obsRef, where('eic_id', '==', user.uid), orderBy('created_at', 'desc'));
    } else if (role === 'AGENCY') {
      q = query(obsRef, where('assigned_agency_id', '==', user.agency_id), orderBy('created_at', 'desc'));
    } else {
      q = query(obsRef, where('reported_by', '==', user.uid), orderBy('created_at', 'desc'));
    }
    
    const snapshot = await getDocs(q);
    let list = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
    
    // In-memory filter fallback for >10 zones
    if ((role === 'ZONE_LEADER' || role === 'SUB_LEADER') && (user.zone_ids || []).length > 10) {
      list = list.filter(obs => user.zone_ids.includes(obs.zone_id));
    }
    
    return list;
  } catch (e) {
    console.error("Error fetching observations for dashboard:", e);
    return [];
  }
}

// Aggregates dashboard counts and records depending on user role
export async function getDashboardData(user) {
  const observations = await fetchObservationsForUser(user);
  const role = user.role;
  const todayStr = new Date().toISOString().split('T')[0];
  
  let stats = {
    total: observations.length,
    pending: 0,
    overdue: 0,
    closedToday: 0,
    underReview: 0,
    activeWork: 0,
    closed: 0,
    awaitingVerification: 0,
    awaitingAssignment: 0,
    newJobs: 0,
    wipJobs: 0,
    completedJobs: 0
  };
  
  let pendingList = [];
  
  // Client-side aggregation
  observations.forEach(obs => {
    const status = obs.status_name;
    const isClosed = status === 'Closed';
    const isOverdue = !isClosed && obs.target_closing_date && obs.target_closing_date < todayStr;
    
    if (!isClosed) {
      stats.pending++;
      if (isOverdue) stats.overdue++;
      pendingList.push(obs);
    }
    
    if (obs.closed_date === todayStr) {
      stats.closedToday++;
    }

    if (isClosed) {
      stats.closed++;
    }
    
    // Role-based counting
    if (role === 'USER') {
      if (['Pending Sub Leader Review', 'Pending Zone Leader Review', 'Pending EIC Assignment'].includes(status)) {
        stats.underReview++;
      } else if (['Assigned to Agency', 'Work In Progress', 'Completed by Agency', 'Pending Zone Verification'].includes(status)) {
        stats.activeWork++;
      }
    } else if (role === 'ZONE_LEADER') {
      if (status === 'Pending Zone Verification') {
        stats.awaitingVerification++;
      }
    } else if (role === 'SUB_LEADER') {
      if (['Reported', 'Pending Sub Leader Review'].includes(status)) {
        stats.underReview++;
      }
    } else if (role === 'EIC') {
      if (status === 'Pending EIC Assignment' || status === 'Reassigned') {
        stats.awaitingAssignment++;
      } else if (status === 'Completed by Agency') {
        stats.awaitingVerification++;
      } else if (['Assigned to Agency', 'Work In Progress'].includes(status)) {
        stats.activeWork++;
      }
    } else if (role === 'AGENCY') {
      if (status === 'Assigned to Agency') {
        stats.newJobs++;
      } else if (status === 'Work In Progress') {
        stats.wipJobs++;
      } else if (status === 'Completed by Agency') {
        stats.completedJobs++;
      }
    }
  });
  
  // Sort pendingList by priority (Critical > High > Medium > Low) and due date
  const priorityWeight = { 'Critical': 1, 'High': 2, 'Medium': 3, 'Low': 4 };
  pendingList.sort((a, b) => {
    const wa = priorityWeight[a.priority || a.risk_level] || 5;
    const wb = priorityWeight[b.priority || b.risk_level] || 5;
    if (wa !== wb) return wa - wb;
    return (a.target_closing_date || '9999-12-31').localeCompare(b.target_closing_date || '9999-12-31');
  });

  // Calculate Zonewise Counts for Admin Bar Chart
  let zoneCounts = {};
  if (role === 'ADMIN' || role === 'SAFETY_ADMIN') {
    observations.forEach(obs => {
      const zName = obs.zone_short_name || obs.zone_code || 'Other';
      zoneCounts[zName] = (zoneCounts[zName] || 0) + 1;
    });
  }

  // Fetch global metrics (suggestions and near misses counts)
  let suggestionsCount = 0;
  let nearMissCount = 0;
  try {
    const sugSnap = await getDocs(collection(db, 'safety_suggestions'));
    suggestionsCount = sugSnap.size;
    const nmSnap = await getDocs(collection(db, 'near_miss_reports'));
    nearMissCount = nmSnap.size;
  } catch (e) {
    console.error("Error fetching other metrics:", e);
  }

  return {
    stats,
    pendingList,
    zoneCounts,
    suggestionsCount,
    nearMissCount
  };
}

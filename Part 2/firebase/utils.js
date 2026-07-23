import { db } from './firebase.js';
import { collection, query, where, orderBy, limit, getDocs } from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

// Formats YYYY-MM-DD (or Firestore timestamp) to DD-MM-YYYY
export function formatDate(dateInput) {
  if (!dateInput) return '';
  let d;
  if (dateInput.seconds) { // Firestore Timestamp
    d = new Date(dateInput.seconds * 1000);
  } else {
    d = new Date(dateInput);
  }
  if (isNaN(d.getTime())) return String(dateInput);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}-${month}-${year}`;
}

// Formats date to DD-MM-YYYY HH:MM
export function formatDateTime(dateInput) {
  if (!dateInput) return '';
  let d;
  if (dateInput.seconds) { // Firestore Timestamp
    d = new Date(dateInput.seconds * 1000);
  } else {
    d = new Date(dateInput);
  }
  if (isNaN(d.getTime())) return String(dateInput);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');
  return `${day}-${month}-${year} ${hours}:${minutes}`;
}

// Truncates text with trailing dots
export function shortText(text, len = 80) {
  if (!text) return '';
  const str = String(text).trim();
  if (str.length <= len) return str;
  return str.substring(0, len - 3) + '...';
}

// Calculates pending days between a date and today
export function daysPending(dateInput) {
  if (!dateInput) return '';
  let d;
  if (dateInput.seconds) {
    d = new Date(dateInput.seconds * 1000);
  } else {
    d = new Date(dateInput);
  }
  if (isNaN(d.getTime())) return '';
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  d.setHours(0, 0, 0, 0);
  const diffTime = Math.abs(today - d);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

// Client-side sequential ID generators mimicking SQL increments
export async function generateObservationNo() {
  if (!db) return "OBS-ERROR";
  const today = new Date();
  const prefix = `OBS-${today.getFullYear()}${String(today.getMonth() + 1).padStart(2, '0')}${String(today.getDate()).padStart(2, '0')}-`;
  
  try {
    const q = query(
      collection(db, 'safety_observations'),
      where('observation_no', '>=', prefix),
      where('observation_no', '<=', prefix + '\uf8ff'),
      orderBy('observation_no', 'desc'),
      limit(1)
    );
    const snapshot = await getDocs(q);
    let nextNum = 1;
    if (!snapshot.empty) {
      const lastNo = snapshot.docs[0].data().observation_no;
      const lastSeq = parseInt(lastNo.substring(lastNo.length - 4));
      if (!isNaN(lastSeq)) nextNum = lastSeq + 1;
    }
    return prefix + String(nextNum).padStart(4, '0');
  } catch (e) {
    console.error("Error generating observation sequence:", e);
    return prefix + String(Math.floor(1000 + Math.random() * 9000));
  }
}

export async function generateSuggestionNo() {
  if (!db) return "SUG-ERROR";
  const today = new Date();
  const prefix = `SUG-${today.getFullYear()}${String(today.getMonth() + 1).padStart(2, '0')}${String(today.getDate()).padStart(2, '0')}-`;
  
  try {
    const q = query(
      collection(db, 'safety_suggestions'),
      where('suggestion_no', '>=', prefix),
      where('suggestion_no', '<=', prefix + '\uf8ff'),
      orderBy('suggestion_no', 'desc'),
      limit(1)
    );
    const snapshot = await getDocs(q);
    let nextNum = 1;
    if (!snapshot.empty) {
      const lastNo = snapshot.docs[0].data().suggestion_no;
      const lastSeq = parseInt(lastNo.substring(lastNo.length - 4));
      if (!isNaN(lastSeq)) nextNum = lastSeq + 1;
    }
    return prefix + String(nextNum).padStart(4, '0');
  } catch (e) {
    console.error("Error generating suggestion sequence:", e);
    return prefix + String(Math.floor(1000 + Math.random() * 9000));
  }
}

export async function generateNearMissNo() {
  if (!db) return "NM-ERROR";
  const today = new Date();
  const prefix = `NM-${today.getFullYear()}${String(today.getMonth() + 1).padStart(2, '0')}${String(today.getDate()).padStart(2, '0')}-`;
  
  try {
    const q = query(
      collection(db, 'near_miss_reports'),
      where('near_miss_no', '>=', prefix),
      where('near_miss_no', '<=', prefix + '\uf8ff'),
      orderBy('near_miss_no', 'desc'),
      limit(1)
    );
    const snapshot = await getDocs(q);
    let nextNum = 1;
    if (!snapshot.empty) {
      const lastNo = snapshot.docs[0].data().near_miss_no;
      const lastSeq = parseInt(lastNo.substring(lastNo.length - 4));
      if (!isNaN(lastSeq)) nextNum = lastSeq + 1;
    }
    return prefix + String(nextNum).padStart(4, '0');
  } catch (e) {
    console.error("Error generating near miss sequence:", e);
    return prefix + String(Math.floor(1000 + Math.random() * 9000));
  }
}

// UI Badge Generators (maintains PHP styling classes)
export function getStatusBadge(statusName) {
  let colorClass = 'badge-gray';
  switch (statusName) {
    case 'Reported':
      colorClass = 'badge-gray'; break;
    case 'Pending Sub Leader Review':
    case 'Pending Zone Leader Review':
      colorClass = 'badge-blue'; break;
    case 'Pending EIC Assignment':
      colorClass = 'badge-blue-light'; break;
    case 'Assigned to Agency':
      colorClass = 'badge-orange'; break;
    case 'Work In Progress':
      colorClass = 'badge-yellow'; break;
    case 'Completed by Agency':
      colorClass = 'badge-purple'; break;
    case 'Pending Zone Verification':
      colorClass = 'badge-teal'; break;
    case 'Closed':
      colorClass = 'badge-green'; break;
    case 'Reassigned':
      colorClass = 'badge-red'; break;
  }
  return `<span class="status-badge ${colorClass}">${statusName}</span>`;
}

export function getPriorityBadge(priority) {
  switch (priority) {
    case 'Low':
      return '<span class="priority-badge pb-low">🟢 Low</span>';
    case 'Medium':
      return '<span class="priority-badge pb-medium">🟡 Medium</span>';
    case 'High':
      return '<span class="priority-badge pb-high">🟠 High</span>';
    case 'Critical':
      return '<span class="priority-badge pb-critical">🔴 Critical</span>';
    default:
      return `<span class="priority-badge pb-medium">🟡 ${priority || 'Medium'}</span>`;
  }
}

export function getDueDateBadge(dueDate) {
  if (!dueDate) {
    return '<span class="due-badge due-green">No Due Date</span>';
  }
  const due = new Date(dueDate);
  const today = new Date();
  today.setHours(0,0,0,0);
  due.setHours(0,0,0,0);
  
  const diffTime = due - today;
  const days = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (days < 0) {
    const absDays = Math.abs(days);
    return `<span class="due-badge due-red">🔴 Overdue by ${absDays} day${absDays > 1 ? 's' : ''}</span>`;
  } else if (days <= 3) {
    return `<span class="due-badge due-yellow">🟡 Due Soon (${days} day${days != 1 ? 's' : ''} remaining)</span>`;
  } else {
    return `<span class="due-badge due-green">🟢 On Schedule (${days} days remaining)</span>`;
  }
}

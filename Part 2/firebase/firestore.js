import { db } from './firebase.js';
import { 
  collection, doc, getDoc, getDocs, setDoc, addDoc, updateDoc, 
  query, where, orderBy, limit, serverTimestamp 
} from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

// Logs action to audit logs collection
export async function logAudit(userId, action, tableName = null, recordId = null) {
  if (!db) return;
  try {
    await addDoc(collection(db, 'audit_logs'), {
      user_id: userId || 'system',
      action: action,
      table_name: tableName,
      record_id: recordId,
      ip_address: "client-side",
      user_agent: navigator.userAgent,
      created_at: serverTimestamp()
    });
  } catch (e) {
    console.error("Audit logging failed:", e);
  }
}

// Retrieves site config settings
export async function getSetting(key, defaultValue = '') {
  if (!db) return defaultValue;
  try {
    const docRef = doc(db, 'app_settings', key);
    const docSnap = await getDoc(docRef);
    return docSnap.exists() ? docSnap.data().value : defaultValue;
  } catch (e) {
    console.error(`Error getting setting ${key}:`, e);
    return defaultValue;
  }
}

// Updates site config settings
export async function setSetting(key, value) {
  if (!db) return false;
  try {
    const docRef = doc(db, 'app_settings', key);
    await setDoc(docRef, { value: value, updated_at: serverTimestamp() });
    return true;
  } catch (e) {
    console.error(`Error setting ${key}:`, e);
    return false;
  }
}

// Dropdown collection retrievers
export async function getAllZones() {
  if (!db) return [];
  try {
    const q = query(collection(db, 'zones'), where('is_active', '==', true), orderBy('display_order', 'asc'));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting zones:", e);
    return [];
  }
}

export async function getAllDepartments() {
  if (!db) return [];
  try {
    const q = query(collection(db, 'departments'), where('is_active', '==', true), orderBy('department_name', 'asc'));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting departments:", e);
    return [];
  }
}

export async function getAllCategories() {
  if (!db) return [];
  try {
    const q = query(collection(db, 'observation_categories'), where('is_active', '==', true), orderBy('category_name', 'asc'));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting categories:", e);
    return [];
  }
}

export async function getAllAgencies() {
  if (!db) return [];
  try {
    const q = query(collection(db, 'agencies'), where('is_active', '==', true), orderBy('agency_name', 'asc'));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting agencies:", e);
    return [];
  }
}

// Queries EICs assigned to a particular safety zone
export async function getZoneEics(zoneId) {
  if (!db) return [];
  try {
    const q = query(
      collection(db, 'users'), 
      where('role', '==', 'EIC'), 
      where('zone_ids', 'array-contains', zoneId), 
      where('is_active', '==', true)
    );
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting zone EICs:", e);
    return [];
  }
}

// Gets first mapped EIC for a zone
export async function getZoneEicId(zoneId) {
  const eics = await getZoneEics(zoneId);
  return eics.length > 0 ? eics[0].id : null;
}

// Aggregates leader names for dynamic display
export async function getZoneLeadersText(zoneId) {
  if (!db) return '';
  try {
    const q = query(
      collection(db, 'users'), 
      where('role', '==', 'ZONE_LEADER'), 
      where('zone_ids', 'array-contains', zoneId), 
      where('is_active', '==', true)
    );
    const snapshot = await getDocs(q);
    const leaders = snapshot.docs.map(doc => doc.data());
    
    const primary = [];
    const deputy = [];
    const assistant = [];
    
    leaders.forEach(l => {
      if (l.leader_type === 'Zone Leader') {
        primary.push(l.full_name);
      } else if (l.leader_type === 'Dy. Leader') {
        deputy.push(l.full_name);
      } else {
        assistant.push(l.full_name);
      }
    });
    
    const parts = [];
    if (primary.length) parts.push('Zone Leader - ' + primary.join(' | '));
    if (deputy.length) parts.push('Dy. Leaders - ' + deputy.join(' | '));
    if (assistant.length) parts.push('Assistant Leaders - ' + assistant.join(' | '));
    return parts.join(', ');
  } catch (e) {
    console.error("Error formatting zone leaders:", e);
    return '';
  }
}

// Retrieves users options for a role dropdown selection
export async function getUserOptionsByRole(roleName) {
  if (!db) return [];
  try {
    const q = query(collection(db, 'users'), where('role', '==', roleName), where('is_active', '==', true));
    const snapshot = await getDocs(q);
    return snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));
  } catch (e) {
    console.error("Error getting users by role:", e);
    return [];
  }
}

// Fetches an observation document by Firestore id
export async function getObservationById(id) {
  if (!db) return null;
  try {
    const docRef = doc(db, 'safety_observations', id);
    const docSnap = await getDoc(docRef);
    if (!docSnap.exists()) return null;
    return { id: docSnap.id, ...docSnap.data() };
  } catch (e) {
    console.error("Error getting observation:", e);
    return null;
  }
}

// Client-side observation visibility and access validation
export function userCanAccessObservation(observation, user) {
  if (!observation || !user) return false;
  const role = user.role;
  if (role === 'ADMIN' || role === 'SAFETY_ADMIN') return true;
  if (role === 'ZONE_LEADER' || role === 'SUB_LEADER') {
    return (user.zone_ids || []).includes(observation.zone_id);
  }
  if (role === 'EIC') {
    return observation.eic_id === user.uid || observation.eic_id === user.id;
  }
  if (role === 'AGENCY') {
    return observation.assigned_agency_id === user.agency_id;
  }
  return observation.reported_by === user.uid || observation.reported_by === user.id;
}

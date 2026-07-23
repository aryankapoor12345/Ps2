import { db } from './firebase.js';
import { 
  doc, getDoc, updateDoc, addDoc, collection, serverTimestamp, query, where, getDocs, limit, orderBy 
} from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';
import { createNotification } from './notifications.js';
import { logAudit } from './firestore.js';

// Records a transition step in the observation's audit timeline
export async function recordWorkflowAction(observationId, user, actionTaken, newStatusName, remarks, attachmentPath = null) {
  const obsRef = doc(db, 'safety_observations', observationId);
  const obsSnap = await getDoc(obsRef);
  if (!obsSnap.exists()) throw new Error("Observation not found.");
  
  const obsData = obsSnap.data();
  const oldStatusName = obsData.status_name || 'Reported';
  
  // 1. Insert workflow history log
  await addDoc(collection(db, 'workflow_history'), {
    observation_id: observationId,
    user_id: user.uid || user.id,
    full_name: user.full_name,
    role_name: user.role,
    action_taken: actionTaken,
    old_status_name: oldStatusName,
    status_name: newStatusName,
    remarks: remarks,
    attachment_path: attachmentPath,
    created_at: serverTimestamp()
  });
  
  // 2. Update observation status and closed date if final
  const updates = {
    status_name: newStatusName,
    updated_at: serverTimestamp()
  };
  if (newStatusName === 'Closed') {
    updates.closed_date = new Date().toISOString().split('T')[0];
  }
  await updateDoc(obsRef, updates);
  
  await logAudit(user.uid || user.id, `Workflow: ${actionTaken}`, 'safety_observations', observationId);
  return true;
}

// Determines the allowed action for a user on a given observation
export function getAvailableWorkflowAction(observation, user) {
  if (!observation || !user) return null;
  const role = user.role;
  const status = observation.status_name;
  
  if (role === 'ADMIN' || role === 'SAFETY_ADMIN') {
    return 'admin_all';
  }
  
  if (role === 'SUB_LEADER') {
    if (status === 'Reported' || status === 'Pending Sub Leader Review') {
      if ((user.zone_ids || []).includes(observation.zone_id)) {
        return 'sub_leader_review';
      }
    }
  }
  
  if (role === 'ZONE_LEADER') {
    if (status === 'Reported' || status === 'Pending Zone Leader Review' || status === 'Pending Sub Leader Review') {
      if ((user.zone_ids || []).includes(observation.zone_id)) {
        return 'zone_leader_review';
      }
    }
    if (status === 'Pending Zone Verification') {
      if ((user.zone_ids || []).includes(observation.zone_id)) {
        return 'zone_leader_verify';
      }
    }
  }
  
  if (role === 'EIC') {
    if (observation.eic_id === user.uid || observation.eic_id === user.id) {
      if (status === 'Pending EIC Assignment' || status === 'Reassigned') {
        return 'eic_assign';
      }
      if (status === 'Completed by Agency') {
        return 'eic_review';
      }
    }
  }
  
  if (role === 'AGENCY') {
    if (observation.assigned_agency_id === user.agency_id) {
      if (status === 'Assigned to Agency') {
        return 'agency_start';
      }
      if (status === 'Work In Progress') {
        return 'agency_complete';
      }
    }
  }
  
  return null;
}

// Handles execution of workflow transitions
export async function executeWorkflowAction(observationId, user, actionType, data) {
  if (!db) throw new Error("Firebase is not initialized.");
  const obsRef = doc(db, 'safety_observations', observationId);
  const obsSnap = await getDoc(obsRef);
  if (!obsSnap.exists()) throw new Error("Observation not found.");
  const observation = { id: obsSnap.id, ...obsSnap.data() };
  
  const remarks = (data.remarks || '').trim();
  
  if (actionType === 'sub_leader_review') {
    if (!remarks) throw new Error("Remarks are required.");
    await recordWorkflowAction(observationId, user, 'Forwarded to Zone Leader', 'Pending Zone Leader Review', remarks);
    
    // Notify Zone Leaders in charge of that zone
    const zlQuery = query(collection(db, 'users'), where('role', '==', 'ZONE_LEADER'), where('zone_ids', 'array-contains', observation.zone_id));
    const zlSnap = await getDocs(zlQuery);
    for (const doc of zlSnap.docs) {
      await createNotification(doc.id, 'Observation Awaiting Review', `Observation ${observation.observation_no} has been forwarded by Sub Leader.`, 'observation', observationId);
    }
  } 
  
  else if (actionType === 'zone_leader_review') {
    if (!remarks) throw new Error("Remarks are required.");
    // Automatically map EIC mapped to this zone
    const eicQuery = query(collection(db, 'users'), where('role', '==', 'EIC'), where('zone_ids', 'array-contains', observation.zone_id), where('is_active', '==', true));
    const eicSnap = await getDocs(eicQuery);
    if (eicSnap.empty) {
      throw new Error("No active EIC is mapped to this Safety Zone. Please contact administrator to map one.");
    }
    const eicUser = { id: eicSnap.docs[0].id, ...eicSnap.docs[0].data() };
    
    await updateDoc(obsRef, {
      eic_id: eicUser.uid || eicUser.id,
      eic_name: eicUser.full_name
    });
    
    await recordWorkflowAction(observationId, user, 'Forwarded to EIC', 'Pending EIC Assignment', remarks);
    await createNotification(eicUser.uid || eicUser.id, 'Observation Awaiting EIC Assignment', `Observation ${observation.observation_no} requires agency assignment.`, 'observation', observationId);
  }
  
  else if (actionType === 'eic_assign') {
    const { agency_id, agency_name, due_date, priority } = data;
    if (!agency_id) throw new Error("Please select an Agency.");
    if (!due_date) throw new Error("Please select a Due Date.");
    if (!remarks) throw new Error("Instructions/Remarks are required.");
    
    await updateDoc(obsRef, {
      assigned_agency_id: agency_id,
      assigned_agency_name: agency_name,
      target_closing_date: due_date,
      priority: priority
    });
    
    // Log in agency_assignments collection
    await addDoc(collection(db, 'agency_assignments'), {
      observation_id: observationId,
      agency_id: agency_id,
      agency_name: agency_name,
      assigned_by: user.uid || user.id,
      assigned_by_name: user.full_name,
      due_date: due_date,
      priority: priority,
      is_completed: false,
      created_at: serverTimestamp()
    });
    
    await recordWorkflowAction(observationId, user, 'Assigned to Agency', 'Assigned to Agency', `Assigned due date: ${due_date}. Priority: ${priority}. Remarks: ${remarks}`);
    
    // Notify all agency users
    const agQuery = query(collection(db, 'users'), where('role', '==', 'AGENCY'), where('agency_id', '==', agency_id));
    const agSnap = await getDocs(agQuery);
    for (const doc of agSnap.docs) {
      await createNotification(doc.id, 'New Work Order Assigned', `Observation ${observation.observation_no} has been assigned to your agency.`, 'observation', observationId);
    }
  }
  
  else if (actionType === 'agency_start') {
    await recordWorkflowAction(observationId, user, 'Work Started', 'Work In Progress', remarks || 'Work started by Agency.');
    await createNotification(observation.eic_id, 'Work Started by Agency', `Agency has started work on observation ${observation.observation_no}.`, 'observation', observationId);
  }
  
  else if (actionType === 'agency_complete') {
    if (!remarks) throw new Error("Completion remarks are required.");
    const { uploadedFiles } = data;
    
    const aaQuery = query(
      collection(db, 'agency_assignments'), 
      where('observation_id', '==', observationId), 
      where('is_completed', '==', false), 
      orderBy('created_at', 'desc'), 
      limit(1)
    );
    const aaSnap = await getDocs(aaQuery);
    if (!aaSnap.empty) {
      const aaDoc = aaSnap.docs[0];
      await updateDoc(doc(db, 'agency_assignments', aaDoc.id), {
        is_completed: true,
        completion_remarks: remarks,
        completion_date: serverTimestamp()
      });
      
      // Store uploads as attachments
      if (uploadedFiles && uploadedFiles.length) {
        for (const file of uploadedFiles) {
          await addDoc(collection(db, 'attachments'), {
            observation_id: observationId,
            assignment_id: aaDoc.id,
            file_path: file.path,
            file_name: file.name,
            uploaded_by: user.uid || user.id,
            uploaded_by_name: user.full_name,
            attachment_type: 'proof',
            created_at: serverTimestamp()
          });
        }
      }
    }
    
    await recordWorkflowAction(observationId, user, 'Completed by Agency', 'Completed by Agency', remarks);
    await createNotification(observation.eic_id, 'Work Completed by Agency', `Agency completed job for ${observation.observation_no}. Pending your review.`, 'observation', observationId);
  }
  
  else if (actionType === 'eic_review') {
    if (!remarks) throw new Error("Review remarks are required.");
    await recordWorkflowAction(observationId, user, 'Verified by EIC', 'Pending Zone Verification', remarks);
    
    // Notify Zone Leaders
    const zlQuery = query(collection(db, 'users'), where('role', '==', 'ZONE_LEADER'), where('zone_ids', 'array-contains', observation.zone_id));
    const zlSnap = await getDocs(zlQuery);
    for (const doc of zlSnap.docs) {
      await createNotification(doc.id, 'Work Completion Awaiting Verification', `Observation ${observation.observation_no} requires on-site verification.`, 'observation', observationId);
    }
  }
  
  else if (actionType === 'zone_leader_verify') {
    const { decision } = data;
    if (!remarks) throw new Error("Verification remarks are required.");
    if (decision !== 'approve' && decision !== 'reject') throw new Error("Please select a verification decision.");
    
    if (decision === 'approve') {
      await recordWorkflowAction(observationId, user, 'Work Approved & Closed', 'Closed', remarks);
      await createNotification(observation.reported_by, 'Observation Approved and Closed', `Your reported observation ${observation.observation_no} has been closed.`, 'observation', observationId);
    } else {
      await recordWorkflowAction(observationId, user, 'Work Rejected & Returned to EIC', 'Reassigned', remarks);
      await createNotification(observation.eic_id, 'Work Rejected by Zone Leader', `Work on ${observation.observation_no} was rejected. Please reassign.`, 'observation', observationId);
    }
  }
}

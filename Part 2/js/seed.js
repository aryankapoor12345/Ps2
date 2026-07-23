import { 
  collection, doc, setDoc, writeBatch, serverTimestamp 
} from 'https://www.gstatic.com/firebasejs/10.8.0/firebase-firestore.js';

export async function seedDatabase(db) {
  if (!db) throw new Error("Firestore is not initialized.");

  const batch = writeBatch(db);

  // 1. Seed Roles
  const roles = [
    { id: 'ADMIN', role_name: 'ADMIN', role_description: 'System administrator' },
    { id: 'SAFETY_ADMIN', role_name: 'SAFETY_ADMIN', role_description: 'Safety administrator' },
    { id: 'ZONE_LEADER', role_name: 'ZONE_LEADER', role_description: 'Zone leader and deputy leader' },
    { id: 'SUB_LEADER', role_name: 'SUB_LEADER', role_description: 'Sub leader reviewer' },
    { id: 'EIC', role_name: 'EIC', role_description: 'Engineer incharge for safety zone' },
    { id: 'AGENCY', role_name: 'AGENCY', role_description: 'Agency contractor' },
    { id: 'USER', role_name: 'USER', role_description: 'Employee or worker reporting safety record' }
  ];
  roles.forEach(r => {
    const ref = doc(db, 'roles', r.id);
    batch.set(ref, {
      role_name: r.role_name,
      role_description: r.role_description,
      created_at: serverTimestamp()
    });
  });

  // 2. Seed Departments
  const departments = [
    { id: 'Mechanical', department_name: 'Mechanical', department_code: 'MECH' },
    { id: 'Electrical', department_name: 'Electrical', department_code: 'ELEC' },
    { id: 'Civil', department_name: 'Civil', department_code: 'CIVIL' },
    { id: 'C_I', department_name: 'C&I', department_code: 'CI' },
    { id: 'Operation', department_name: 'Operation', department_code: 'OPN' },
    { id: 'Safety', department_name: 'Safety', department_code: 'SAFE' },
    { id: 'CHP', department_name: 'CHP', department_code: 'CHP' },
    { id: 'Ash_Handling', department_name: 'Ash Handling', department_code: 'AHP' },
    { id: 'Water_Treatment', department_name: 'Water Treatment', department_code: 'WTP' },
    { id: 'Administration', department_name: 'Administration', department_code: 'ADMIN' }
  ];
  departments.forEach(d => {
    const ref = doc(db, 'departments', d.id);
    batch.set(ref, {
      department_name: d.department_name,
      department_code: d.department_code,
      is_active: true,
      created_at: serverTimestamp()
    });
  });

  // 3. Seed Agencies
  const agencies = [
    { id: 'AG-001', agency_name: 'NTPC Safety Services', is_active: true },
    { id: 'AG-002', agency_name: 'Techno Power Contractors', is_active: true },
    { id: 'AG-003', agency_name: 'Quality Construction Ltd', is_active: true }
  ];
  agencies.forEach(a => {
    const ref = doc(db, 'agencies', a.id);
    batch.set(ref, {
      agency_name: a.agency_name,
      is_active: a.is_active,
      created_at: serverTimestamp()
    });
  });

  // 4. Seed Safety Zones
  const zones = [
    { id: 'ZONE-1', zone_code: 'ZONE-1', zone_name: 'ZONE-1 Boiler 1 and Aux Boiler', short_name: 'ZONE-1 (Boiler 1 & Aux)', display_order: 1 },
    { id: 'ZONE-2', zone_code: 'ZONE-2', zone_name: 'ZONE-2 Boiler 2', short_name: 'ZONE-2 (Boiler - 2)', display_order: 2 },
    { id: 'ZONE-3', zone_code: 'ZONE-3', zone_name: 'ZONE-3 ESP 1,2 and AHP', short_name: 'ZONE-3 (ESP 1 & 2)', display_order: 3 },
    { id: 'ZONE-4', zone_code: 'ZONE-4', zone_name: 'ZONE-4 MPH 1 and CCR', short_name: 'ZONE-4 (MPH - 1)', display_order: 4 },
    { id: 'ZONE-5', zone_code: 'ZONE-5', zone_name: 'ZONE-5 MPH 2', short_name: 'ZONE-5 (MPH - 2 & CCR)', display_order: 5 },
    { id: 'ZONE-6', zone_code: 'ZONE-6', zone_name: 'ZONE-6 WTP, DM Plant', short_name: 'ZONE-6 (WTP/DM Plant & AHP)', display_order: 6 },
    { id: 'ZONE-7', zone_code: 'ZONE-7', zone_name: 'ZONE-7 Switchyard and All Transformers and Switchgear', short_name: 'ZONE-7 (Transformer Yard)', display_order: 7 },
    { id: 'ZONE-8', zone_code: 'ZONE-8', zone_name: 'ZONE-8 IDCT 1,2 and CWPH', short_name: 'ZONE-8 (Switchyard)', display_order: 8 },
    { id: 'ZONE-9', zone_code: 'ZONE-9', zone_name: 'ZONE-9 CHP', short_name: 'ZONE-9 (IDCT 1 & 2, CWPH)', display_order: 9 },
    { id: 'ZONE-10', zone_code: 'ZONE-10', zone_name: 'ZONE-10 LDO and FOPH', short_name: 'ZONE-10 (CHP)', display_order: 10 },
    { id: 'ZONE-11', zone_code: 'ZONE-11 Adm Bldg, ROADS, BOP and Chimney', short_name: 'ZONE-11 (Chimney, Reservoir & RWPH)', display_order: 11 },
    { id: 'ZONE-12', zone_code: 'ZONE-12 Township', short_name: 'ZONE-12 (Site Office, Store & Workshop)', display_order: 12 },
    { id: 'ZONE-13', zone_code: 'ZONE-13 MGR and RLY SIDING', short_name: 'ZONE-13 (FOPH, ADM BLD & MGR Deadend)', display_order: 13 },
    { id: 'ZONE-14', zone_code: 'ZONE-14 ASH Dyke and ASH Dyke Corridor', short_name: 'ZONE-14 (TOWNSHIP)', display_order: 14 },
    { id: 'ZONE-15', zone_code: 'ZONE-15 FGD and Reservoir', short_name: 'ZONE-15 (MGR & Railway Siding)', display_order: 15 },
    { id: 'ZONE-16', zone_code: 'ZONE-16 Ash Dyke and Corridor', short_name: 'ZONE-16 (Ash Dyke & Corridor)', display_order: 16 },
    { id: 'ZONE-17', zone_code: 'ZONE-17 MUWPH and Pipeline', short_name: 'ZONE-17 (MUWPH & Pipeline)', display_order: 17 },
    { id: 'ZONE-18', zone_code: 'ZONE-18 Medical College', short_name: 'ZONE-18 (Medical College)', display_order: 18 }
  ];
  zones.forEach(z => {
    const ref = doc(db, 'zones', z.id);
    batch.set(ref, {
      zone_code: z.zone_code,
      zone_name: z.zone_name,
      short_name: z.short_name,
      display_order: z.display_order,
      is_active: true,
      created_at: serverTimestamp()
    });
  });

  // 5. Seed Observation Categories
  const categories = [
    { id: 'Unsafe_Act', category_name: 'Unsafe Act', description: 'Unsafe act observed in work area', severity_level: 'Medium' },
    { id: 'Unsafe_Condition', category_name: 'Unsafe Condition', description: 'Unsafe condition observed in plant area', severity_level: 'Medium' },
    { id: 'Near_Miss', category_name: 'Near Miss', description: 'Near miss incident record', severity_level: 'High' },
    { id: 'Fatal_Accident', category_name: 'Fatal Accident', description: 'Fatal accident observation category', severity_level: 'Critical' },
    { id: 'Major_Accident', category_name: 'Major Accident', description: 'Major accident observation category', severity_level: 'Critical' },
    { id: 'Minor_Accident', category_name: 'Minor Accident', description: 'Minor accident observation category', severity_level: 'Medium' },
    { id: 'Fire_Hazard', category_name: 'Fire Hazard', description: 'Fire hazard or blocked fire equipment', severity_level: 'High' },
    { id: 'Electrical_Hazard', category_name: 'Electrical Hazard', description: 'Electrical hazard condition', severity_level: 'High' },
    { id: 'Working_at_Height', category_name: 'Working at Height', description: 'Unsafe condition for height work', severity_level: 'High' },
    { id: 'PPE_Violation', category_name: 'PPE Violation', description: 'Personal protective equipment violation', severity_level: 'Medium' },
    { id: 'Housekeeping', category_name: 'Housekeeping', description: 'Housekeeping related observation', severity_level: 'Low' },
    { id: 'Suggestion', category_name: 'Suggestion', description: 'Safety improvement suggestion', severity_level: 'Low' }
  ];
  categories.forEach(c => {
    const ref = doc(db, 'observation_categories', c.id);
    batch.set(ref, {
      category_name: c.category_name,
      description: c.description,
      severity_level: c.severity_level,
      is_active: true
    });
  });

  // 6. Seed Observation Statuses
  const statuses = [
    { id: 'Reported', status_name: 'Reported', description: 'Record submitted and open' },
    { id: 'Pending_Sub_Leader_Review', status_name: 'Pending Sub Leader Review', description: 'Under review by Sub Leader' },
    { id: 'Pending_Zone_Leader_Review', status_name: 'Pending Zone Leader Review', description: 'Under review by Zone Leader' },
    { id: 'Pending_EIC_Assignment', status_name: 'Pending EIC Assignment', description: 'Pending assignment to agency by EIC' },
    { id: 'Assigned_to_Agency', status_name: 'Assigned to Agency', description: 'Work order assigned to Agency' },
    { id: 'Work_In_Progress', status_name: 'Work In Progress', description: 'Remediation work in progress' },
    { id: 'Completed_by_Agency', status_name: 'Completed by Agency', description: 'Work completed by agency' },
    { id: 'Pending_Zone_Verification', status_name: 'Pending Zone Verification', description: 'Pending verification by Zone Leader' },
    { id: 'Reassigned', status_name: 'Reassigned', description: 'Returned back for reassignment' },
    { id: 'Closed', status_name: 'Closed', description: 'Record closed successfully' }
  ];
  statuses.forEach(s => {
    const ref = doc(db, 'observation_statuses', s.id);
    batch.set(ref, {
      status_name: s.status_name,
      description: s.description
    });
  });

  // 7. Seed Settings
  const settings = [
    { id: 'site_name', value: 'NTPC Online Safety Portal' },
    { id: 'portal_title', value: 'ONLINE SAFETY PORTAL' },
    { id: 'max_upload_size_mb', value: '2' },
    { id: 'allowed_upload_formats', value: 'pdf,jpg,jpeg,png,doc,docx' },
    { id: 'total_safety_observations_display', value: '20046' },
    { id: 'total_safety_suggestions_display', value: '3' },
    { id: 'total_reported_near_miss_display', value: '3' }
  ];
  settings.forEach(s => {
    const ref = doc(db, 'app_settings', s.id);
    batch.set(ref, {
      value: s.value,
      updated_at: serverTimestamp()
    });
  });

  // 8. Seed Default Users (with auth_created = false, password = 'password123')
  const users = [
    {
      id: 'admin',
      employee_id: '009999',
      full_name: 'SYSTEM ADMIN',
      username: 'admin',
      role: 'ADMIN',
      department_id: 'Safety',
      designation: 'Administrator',
      mobile: '9999999999',
      email: 'admin@ntpc.co.in',
      is_active: true,
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'bhupendra',
      employee_id: '007388',
      full_name: 'BHUPENDRA SINGH',
      username: 'bhupendra',
      role: 'USER',
      department_id: 'Mechanical',
      designation: 'Technician',
      mobile: '9876543210',
      email: 'bhupendra@ntpc.co.in',
      is_active: true,
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'subleader',
      employee_id: '001000',
      full_name: 'SUB LEADER USER',
      username: 'subleader',
      role: 'SUB_LEADER',
      department_id: 'Mechanical',
      designation: 'Sub Leader',
      mobile: '9888877777',
      email: 'subleader@ntpc.co.in',
      is_active: true,
      zone_ids: ['ZONE-1', 'ZONE-2'],
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'pankaj',
      employee_id: '001001',
      full_name: 'PANKAJ SINGH',
      username: 'pankaj',
      role: 'ZONE_LEADER',
      department_id: 'Mechanical',
      designation: 'Zone Leader',
      mobile: '9812345678',
      email: 'pankaj@ntpc.co.in',
      is_active: true,
      leader_type: 'Zone Leader',
      zone_ids: ['ZONE-1', 'ZONE-2', 'ZONE-3', 'ZONE-4', 'ZONE-5', 'ZONE-6'],
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'baijnath',
      employee_id: '001002',
      full_name: 'BAIJNATH SWAMY',
      username: 'baijnath',
      role: 'ZONE_LEADER',
      department_id: 'Electrical',
      designation: 'Dy. Zone Leader',
      mobile: '9832109876',
      email: 'baijnath@ntpc.co.in',
      is_active: true,
      leader_type: 'Dy. Leader',
      zone_ids: ['ZONE-7', 'ZONE-8', 'ZONE-9', 'ZONE-10'],
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'ashok',
      employee_id: '002001',
      full_name: 'ASHOK KUMAR PATRO',
      username: 'ashok',
      role: 'EIC',
      department_id: 'Mechanical',
      designation: 'Engineer Incharge',
      mobile: '9845671230',
      email: 'ashok@ntpc.co.in',
      is_active: true,
      zone_ids: ['ZONE-1','ZONE-2','ZONE-3','ZONE-4','ZONE-5','ZONE-6','ZONE-7','ZONE-8','ZONE-9','ZONE-10','ZONE-11','ZONE-12','ZONE-13','ZONE-14','ZONE-15','ZONE-16','ZONE-17','ZONE-18'],
      auth_created: false,
      temp_password: 'password123'
    },
    {
      id: 'agency_user',
      employee_id: '005001',
      full_name: 'AGENCY CONTRACTOR USER',
      username: 'agency_user',
      role: 'AGENCY',
      designation: 'Contractor',
      mobile: '9898989898',
      email: 'agency@ntpc.co.in',
      is_active: true,
      agency_id: 'AG-001',
      auth_created: false,
      temp_password: 'password123'
    }
  ];
  users.forEach(u => {
    const ref = doc(db, 'users', u.id);
    batch.set(ref, {
      employee_id: u.employee_id,
      full_name: u.full_name,
      username: u.username,
      role: u.role,
      department_id: u.department_id || null,
      designation: u.designation,
      mobile: u.mobile,
      email: u.email,
      is_active: u.is_active,
      zone_ids: u.zone_ids || [],
      agency_id: u.agency_id || null,
      leader_type: u.leader_type || null,
      auth_created: u.auth_created,
      temp_password: u.temp_password,
      created_at: serverTimestamp()
    });
  });

  // Commit batch seeding
  await batch.commit();
}

<?php
require_once __DIR__ . '/../../config/app.php';
requireRole([ROLE_ADMIN]);
$action = sanitizeInput($_GET['action'] ?? 'list');
$id = (int)($_GET['id'] ?? 0);
$roles = getRoles(); $departments = getDepartments(); $errors = [];
if ($action === 'toggle' && $id > 0) {
    updateRecord("UPDATE users SET is_active = IF(is_active=1,0,1), updated_at = NOW() WHERE id = ?", [$id], 'i');
    logAudit('Toggled user active status', 'users', $id); setFlash('success', 'User status updated.'); redirect('modules/admin/users.php');
}
$edit = $id > 0 ? fetchOne("SELECT * FROM users WHERE id=?", [$id], 'i') : null;
$old = $edit ?: ['employee_id'=>'','full_name'=>'','username'=>'','role_id'=>'','department_id'=>'','designation'=>'','mobile'=>'','email'=>'','is_active'=>1];
if (isPost() && in_array($action, ['add','edit'], true)) {
    $old = array_merge($old, sanitizeInput($_POST));
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) $errors[]='Invalid request token.';
    if (trim($old['employee_id'])==='') $errors[]='Employee ID is required.';
    if (trim($old['full_name'])==='') $errors[]='Full Name is required.';
    if (trim($old['username'])==='') $errors[]='Username is required.';
    if ((int)$old['role_id']<=0) $errors[]='Role is required.';
    $dupEmp=fetchOne("SELECT id FROM users WHERE employee_id=? AND id<>?",[$old['employee_id'],$id],'si');
    $dupUser=fetchOne("SELECT id FROM users WHERE username=? AND id<>?",[$old['username'],$id],'si');
    if($dupEmp)$errors[]='Employee ID already exists.'; if($dupUser)$errors[]='Username already exists.';
    $password=(string)($_POST['password'] ?? '');
    if($action==='add' && strlen($password)<6)$errors[]='Password should be at least 6 characters.';
    if($password!=='' && strlen($password)<6)$errors[]='Password should be at least 6 characters.';
    if(!$errors){
        $active=isset($_POST['is_active'])?1:0;
        if($action==='add'){
            $newId=insertRecord("INSERT INTO users (employee_id, full_name, username, password_hash, role_id, department_id, designation, mobile, email, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",[$old['employee_id'],$old['full_name'],$old['username'],password_hash($password,PASSWORD_DEFAULT),(int)$old['role_id'],((int)$old['department_id']?:null),$old['designation'],$old['mobile'],$old['email'],$active],'ssssiisssi');
            logAudit('Added user','users',$newId);
        }else{
            if($password!=='') updateRecord("UPDATE users SET employee_id=?, full_name=?, username=?, password_hash=?, role_id=?, department_id=?, designation=?, mobile=?, email=?, is_active=?, updated_at=NOW() WHERE id=?",[$old['employee_id'],$old['full_name'],$old['username'],password_hash($password,PASSWORD_DEFAULT),(int)$old['role_id'],((int)$old['department_id']?:null),$old['designation'],$old['mobile'],$old['email'],$active,$id],'ssssiisssii');
            else updateRecord("UPDATE users SET employee_id=?, full_name=?, username=?, role_id=?, department_id=?, designation=?, mobile=?, email=?, is_active=?, updated_at=NOW() WHERE id=?",[$old['employee_id'],$old['full_name'],$old['username'],(int)$old['role_id'],((int)$old['department_id']?:null),$old['designation'],$old['mobile'],$old['email'],$active,$id],'sssiisssii');
            logAudit('Edited user','users',$id);
        }
        setFlash('success','User saved successfully.'); redirect('modules/admin/users.php');
    }
}
$pageTitle='Users - '.SITE_NAME; require __DIR__.'/../../includes/header.php';
?>
<h2>Users</h2>
<?php if($errors): ?><div class="flash flash-error"><?php echo e(implode(' ', $errors)); ?></div><?php endif; ?>
<?php if(in_array($action,['add','edit'],true)): ?>
<form method="post" class="validate-form"><?php echo csrfField(); ?><table class="form-table">
<tr><td class="label-cell">Employee ID <span class="required">*</span></td><td><input name="employee_id" class="text-input" value="<?php echo e($old['employee_id']); ?>" data-required="1"></td></tr>
<tr><td class="label-cell">Full Name <span class="required">*</span></td><td><input name="full_name" class="long-input" value="<?php echo e($old['full_name']); ?>" data-required="1"></td></tr>
<tr><td class="label-cell">Username <span class="required">*</span></td><td><input name="username" class="text-input" value="<?php echo e($old['username']); ?>" data-required="1"></td></tr>
<tr><td class="label-cell">Password</td><td><input type="password" name="password" class="text-input"> <?php echo $action==='edit'?'Leave blank to keep old password.':'Required on add.'; ?></td></tr>
<tr><td class="label-cell">Role <span class="required">*</span></td><td><select name="role_id" data-required="1"><option value="">Select</option><?php foreach($roles as $r): ?><option value="<?php echo e($r['id']); ?>" <?php echo (int)$old['role_id']===(int)$r['id']?'selected':''; ?>><?php echo e($r['role_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Department</td><td><select name="department_id"><option value="">Select</option><?php foreach($departments as $d): ?><option value="<?php echo e($d['id']); ?>" <?php echo (int)$old['department_id']===(int)$d['id']?'selected':''; ?>><?php echo e($d['department_name']); ?></option><?php endforeach; ?></select></td></tr>
<tr><td class="label-cell">Designation</td><td><input name="designation" class="text-input" value="<?php echo e($old['designation']); ?>"></td></tr>
<tr><td class="label-cell">Mobile</td><td><input name="mobile" class="text-input" value="<?php echo e($old['mobile']); ?>" data-mobile="1"></td></tr>
<tr><td class="label-cell">Email</td><td><input name="email" class="long-input" value="<?php echo e($old['email']); ?>"></td></tr>
<tr><td class="label-cell">Active</td><td><input type="checkbox" name="is_active" value="1" <?php echo (int)$old['is_active']===1?'checked':''; ?>></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" value="Save" class="save-button"> <a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/admin/users.php">Back</a></td></tr>
</table></form>
<?php else:
$page=max(1,(int)($_GET['page']??1)); $perPage=20; $offset=($page-1)*$perPage;
$totalRow=fetchOne("SELECT COUNT(*) AS total FROM users"); $totalPages=max(1,(int)ceil((int)($totalRow['total']??0)/$perPage));
$rows=fetchAll("SELECT u.*, r.role_name, d.department_name FROM users u INNER JOIN roles r ON r.id=u.role_id LEFT JOIN departments d ON d.id=u.department_id ORDER BY u.full_name LIMIT ? OFFSET ?",[$perPage,$offset],'ii');
?>
<p><a class="plain-link-button" href="<?php echo BASE_URL; ?>modules/admin/users.php?action=add">Add User</a></p>
<table class="data-table"><tr><th>S.No.</th><th>Employee ID</th><th>Full Name</th><th>Username</th><th>Role</th><th>Department</th><th>Designation</th><th>Mobile</th><th>Active</th><th>Action</th></tr>
<?php foreach($rows as $i=>$r): ?><tr><td><?php echo e($offset+$i+1); ?></td><td><?php echo e($r['employee_id']); ?></td><td><?php echo e($r['full_name']); ?></td><td><?php echo e($r['username']); ?></td><td><?php echo e($r['role_name']); ?></td><td><?php echo e($r['department_name']); ?></td><td><?php echo e($r['designation']); ?></td><td><?php echo e($r['mobile']); ?></td><td><?php echo (int)$r['is_active']?'Yes':'No'; ?></td><td><a href="<?php echo BASE_URL; ?>modules/admin/users.php?action=edit&id=<?php echo e($r['id']); ?>">Edit</a> <a href="<?php echo BASE_URL; ?>modules/admin/users.php?action=toggle&id=<?php echo e($r['id']); ?>">Toggle</a></td></tr><?php endforeach; ?></table>
<div class="pagination">Page <?php echo e($page); ?> of <?php echo e($totalPages); ?> <?php if($page>1): ?><a href="<?php echo BASE_URL; ?>modules/admin/users.php?page=<?php echo e($page-1); ?>">Previous</a><?php endif; ?> <?php if($page<$totalPages): ?><a href="<?php echo BASE_URL; ?>modules/admin/users.php?page=<?php echo e($page+1); ?>">Next</a><?php endif; ?></div>
<?php endif; ?>
<?php require __DIR__.'/../../includes/footer.php'; ?>

<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info = $qs = array();

if ($_REQUEST['a']=='add'){
    if (!$staff) {
        $staff = Staff::create(array(
            'isactive' => true,
        ));
        // Set some default permissions
        $staff->updatePerms(array(
            User::PERM_CREATE,
            User::PERM_EDIT,
            User::PERM_DELETE,
            User::PERM_MANAGE,
            User::PERM_DIRECTORY,
            Organization::PERM_CREATE,
            Organization::PERM_EDIT,
            Organization::PERM_DELETE,
            FAQ::PERM_MANAGE,
        ));
    }
    $title=__('Add New Agent');
    $action='create';
    $submit_text=__('Create');
} else if($_REQUEST['a']=='wordpress_user'){
	if (!$staff) {
        $staff = Staff::create(array(
            'isactive' => true,
        ));
        // Set some default permissions
        $staff->updatePerms(array(
            User::PERM_CREATE,
            User::PERM_EDIT,
            User::PERM_DELETE,
            User::PERM_MANAGE,
            User::PERM_DIRECTORY,
            Organization::PERM_CREATE,
            Organization::PERM_EDIT,
            Organization::PERM_DELETE,
            FAQ::PERM_MANAGE,
        ));
    }
    
    $title=__('Add wordpress user as deskuss agent');
    $action='create';
    $submit_text=__('Create');
    if($_REQUEST['wp_id']){

        $user = get_userdata($_REQUEST['wp_id']);
        if ($user) {
            // Access user details
            $user_nicename = $user->user_nicename;
            $user_name = $user->user_login;
            $mail = $user->user_email;
            $user_status = $user->user_status;
            $user_registered = $user->user_registered;
            $first_name = get_user_meta($_REQUEST['wp_id'], 'first_name', true);
            $last_name = get_user_meta($_REQUEST['wp_id'], 'last_name', true);
            $phone_number = get_user_meta($_REQUEST['wp_id'], 'phone_number', true);
        }
    }
}else {
    //Editing Department.
    $title=__('Manage Agent');
    $action='update';
    $submit_text=__('Save Changes');
    $info['id'] = $staff->getId();
    $qs += array('id' => $staff->getId());
}
?>
<div class="panel">
	<form action="staff.php?<?php echo Http::build_query($qs); ?>" method="post" class="save" autocomplete="off">
	  <?php csrf_token(); ?>
  <input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
  <input type="hidden" name="a" value="<?php echo esc_attr(Format::htmlchars($_REQUEST['a'])); ?>">
  <input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">
	<div class="panel-heading">
	  <div class="panel-title table-caption"><?php echo esc_html($title); ?>
		  <?php if (isset($staff->staff_id)) { ?><small>
		  — <?php echo esc_html($staff->getName()); ?></small>
			  <?php } ?>
		</div>
		</div>
	</br>
<div class="panel-body">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Account'); ?></div>
	</div>
	<div class="panel-body">
	  <div class="col-md-6 row">	
		<div class="col-md-12 row">
			<fieldset class="form-group form-inline">
			  <div class="col-md-3"><?php echo __('Name'); ?>:</div>
			  <div class="col-md-9">
				<input type="text" size="20" maxlength="64" style="width: 145px" name="firstname" class="form-control auto first"
				  autofocus value="<?php echo Format::htmlchars($staff->firstname ?? $first_name); ?>"
				  placeholder="<?php echo __("First Name"); ?>" />
				<?php
					if(!empty($errors['firstname'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['firstname'].'</div>';
					}
				?>
				<input type="text" size="20" maxlength="64" style="width: 145px" name="lastname" class="form-control auto last"
					value="<?php echo Format::htmlchars($staff->lastname ?? $last_name); ?>"
					placeholder="<?php echo __("Last Name"); ?>" />
				<?php
					if(!empty($errors['lastname'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['lastname'].'</div>';
					}
				?>
			  </div>
			</fieldset>
			<fieldset class="form-group form-inline">
			  <div class="required col-md-3"><?php echo __('Email Address'); ?>:</div>
			  <div class="col-md-9 ">
				<input type="email" size="40" maxlength="64" style="width: 300px" name="email" class="form-control auto email"
				  value="<?php echo Format::htmlchars($staff->email ?? $mail); ?>"
				  placeholder="<?php echo __('e.g. me@mycompany.com'); ?>" />
				<?php
					if(!empty($errors['email'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['email'].'</div>';
					}
				?>
				</div>
			</fieldset>
			<fieldset class="form-group form-inline">
			  <div class="col-md-3"><?php echo __('Phone Number');?>:</div>
			  <div class="col-md-9">
				<input type="tel" size="18" name="phone" class="form-control auto phone"
				  value="<?php echo Format::htmlchars($staff->phone); ?>" />
				<?php
					if(!empty($errors['phone'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['phone'].'</div>';
					}
				?>
				<?php echo __('Ext');?>
				<input class="form-control" type="text" size="5" name="phone_ext"
				  value="<?php echo Format::htmlchars($staff->phone_ext); ?>">
				  <?php
					if(!empty($errors['phone_ext'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['phone_ext'].'</div>';
					}
				?>		
			  </div>
			</fieldset>
			<fieldset class="form-group form-inline">
			  <div class="col-md-3"><?php echo __('Mobile Number');?>:</div>
			  <div class="col-md-9">
				<input type="tel" size="18" name="mobile" class="form-control auto phone"
				  value="<?php echo Format::htmlchars($staff->mobile ?? $phone_number); ?>" />
				  <?php
					if(!empty($errors['mobile'])){
						echo '<div class="alert-danger">&nbsp;'.$errors['mobile'].'</div>';
					}
				?>
			  </div>
			</fieldset>
		</div>	
		  <!-- ================================================ -->
		<div class="col-md-12">
			<fieldset class="form-group">
				<label class="control-label">
					<?php echo __('Authentication'); ?>
				</label>
				<div class="form-group row form-inline">
				<div class="col-md-3"><label class="control-label required"><?php echo __('Username'); ?>:</label></div>
				<div class="col-md-9">
					<input type="text" size="40" style="width:200px;" class="form-control staff-username typeahead" name="username" value="<?php echo Format::htmlchars($staff->username ?? $user_name); ?>" />
					<?php
						if(!empty($errors['username'])){
							echo '<div class="alert-danger">&nbsp;'.$errors['username'].'</div>';
						}
					?>
		<?php if (!($bk = $staff->getAuthBackend()) || $bk->supportsPasswordChange()) { ?>
					<button type="button" class="btn action-button" onclick="javascript:
					$.dialog('ajax.php/staff/'+<?php echo esc_js($info['id'] ?: '0'); ?>+'/set-password', 201);">
					<i class="fa fa-refresh"></i> <?php echo __('Set Password'); ?>
					</button>
		<?php } ?>
					<i class="offset help-tip fa fa-question-circle" href="#username"></i>
					
				</div>
				</div>
			</fieldset>	
	<?php
	$bks = array();
	foreach (StaffAuthenticationBackend::allRegistered() as $ab) {
	if (!$ab->supportsInteractiveAuthentication()) continue;
	$bks[] = $ab;
	}
	if (count($bks) > 1) {
	?>
			<fieldset class="form-group form-inline">
			<div class="col-md-3"><label class="control-label"><?php echo __('Authentication Backend'); ?>:</label></div>
			<div class="col-md-9">
				<select class="form-control" name="backend" id="backend-selection"
				style="width:300px" onchange="javascript:
					if (this.value != '' && this.value != 'local')
						$('#password-fields').hide();
					else if (!$('#welcome-email').is(':checked'))
						$('#password-fields').show();
					">
				<option value="">&mdash; <?php echo __('Use any available backend'); ?> &mdash;</option>
	<?php foreach ($bks as $ab) { ?>
			<option value="<?php echo esc_attr($ab::$id); ?>" <?php
				if ($staff->backend == $ab::$id)
				echo 'selected="selected"'; ?>><?php
				echo esc_html($ab->getName()); ?></option>
	<?php } ?>
				</select>
			</div>
			</fieldset>
	<?php
	} ?>
		<!-- ================================================ -->
		
	</div>
</div>
		<div class="col-md-6">
		<fieldset class="form-group">
			  <label class="control-label">
				<?php echo __('Status and Settings'); ?>
			  </label>
			<div class="form-group">
				<div class="checkbox">
					<label class="checkbox">
					<input type="checkbox" name="islocked" value="1"
					  <?php echo (!$staff->isactive) ? 'checked="checked"' : ''; ?> />
					  <?php
						if(!empty($errors['isactive'])){
							echo '<div class="alert-danger">&nbsp;'.$errors['isactive'].'</div>';
						}
					?>
					  <?php echo __('Locked'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label class="checkbox">
					<input type="checkbox" name="isadmin" value="1"
					  <?php echo ($staff->isadmin) ? 'checked="checked"' : ''; ?> />
					  <?php
						if(!empty($errors['isadmin'])){
							echo '<div class="alert-danger">&nbsp;'.esc_html($errors['isadmin']).'</div>';
						}
					?>
					  <?php echo __('Administrator'); ?>
					</label>
				</div>
				<div class="checkbox">
					<label class="checkbox">
					<input type="checkbox" name="assigned_only"
					  <?php echo ($staff->assigned_only) ? 'checked="checked"' : ''; ?> />
					  <?php echo __('Limit ticket access to ONLY assigned tickets'); ?>
					</label>
				</div>
				<div class="checkbox">	
					<label class="checkbox">
					<input type="checkbox" name="onvacation"
					  <?php echo ($staff->onvacation) ? 'checked="checked"' : ''; ?> />
					  <?php echo __('Vacation Mode'); ?>
					</label>
				</div>	
				<br/>
			</div>
		  </fieldset>	
			
		</div>	
</div>
	<!-- ============== DEPARTMENT ACCESS =================== -->
	
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Access'); ?></div>
	</div>
	<div class="panel-body">	
		 <div class="col-md-12" id="access">
		  <fieldset class="form-group">
			<div class="form-group">
			  <label class="control-label">
				<?php echo __('Access'); ?>
				</label><div><p><?php echo __(
				"Select the departments the agent is allowed to access and the corresponding effective role."
			  ); ?>
				</p></div><br>
				<label class="control-label required"><?php echo __('Primary Department'); ?></label>
			</div>
			<div class="form-group form-inline row">
			  <div class="col-md-3">
				<select class="form-control" name="dept_id" id="dept_id" data-quick-add="department">
				  <option value="0">&mdash; <?php echo __('Select Department');?> &mdash;</option>
				  <?php
				  foreach (Dept::getDepartments() as $id=>$name) {
					$sel=($staff->dept_id==$id)?'selected="selected"':'';
					echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,Format::htmlchars($name));
				  }
				  ?>
				  <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
				<i class="offset help-tip fa fa-question-circle" href="#primary_department"></i>
				<?php
					if(!empty($errors['dept_id'])){
						echo '<div class="alert-danger">&nbsp;'.esc_html($errors['dept_id']).'</div>';
					}
				?>
			  </div>
			  <div class="col-md-3">
				<select class="form-control" name="role_id" data-quick-add="role">
				  <option value="0">&mdash; <?php echo __('Select Role');?> &mdash;</option>
				  <?php
				  foreach (Role::getRoles() as $id=>$name) {
					$sel=($staff->role_id==$id)?'selected="selected"':'';
					echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
				  }
				  ?>
				  <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
				<?php
					if(!empty($errors['role_id'])){
						echo '<div class="alert-danger">&nbsp;'.esc_html($errors['role_id']).'</div>';
					}
				?>
				<i class="offset help-tip fa fa-question-circle" href="#primary_role"></i>
			  </div>
			  <div class="col-md-6">
				<div class="checkbox">
				<label>
					<input type="checkbox" name="assign_use_pri_role" <?php
						if ($staff->usePrimaryRoleOnAssignment())
							echo 'checked="checked"';
						?> />
						<?php echo __('Fall back to primary role on assignments'); ?>
						<i class="fa fa-question-circle help-tip"
							href="#primary_role_on_assign"></i>
				</label>
				</div>	
			  </div>
			</div>
		  </fieldset>
		  <fieldset class="form-group form-inline">
			<div id="extended_access_template" class="form-group form-inline row hidden" style="border-bottom:1px solid #D3D3D3;">
			  <div class="col-md-3">
				<input type="hidden" data-name="dept_access[]" value="" />
			  </div>
			  <div class="col-md-3" style="padding-bottom:4px;">
				<select class="form-control" data-name="dept_access_role" data-quick-add="role">
				  <option value="0">&mdash; <?php echo __('Select Role');?> &mdash;</option>
				  <?php
				  foreach (Role::getRoles() as $id=>$name) {
					echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,Format::htmlchars($name));
				  }
				  ?>
				  <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
			  </div>
			  <div class="col-md-6">
				<div class="checkbox">
				<label>
				  <input type="checkbox" data-name="dept_access_alerts" value="1" />
				  <?php echo __('Alerts'); ?>
				</label></div>
				<a data-type="warning" data-message='You must click "<?php echo esc_attr($submit_text); ?>" to apply the new changes' href="#" class="growl_popup pull-right drop-access" title="<?php echo __('Delete'); ?>"><i class="fa fa-trash"></i></a>
			  </div>
		  </fieldset>
		  <fieldset class="form-group">
			  <label class="control-label">
				<?php echo __('Extended Access'); ?>
			  </label>
			
	<?php
	$depts = Dept::getDepartments();
	foreach ($staff->dept_access as $dept_access) {
	  unset($depts[$dept_access->dept_id]);
	}
	?>
			<div class="form-group form-inline" id="add_extended_access">
				<i class="fa fa-plus-circle"></i>
				<select class="form-control" id="add_access" data-quick-add="department">
				  <option value="0">&mdash; <?php echo __('Select Department');?> &mdash;</option>
				  <?php
				  foreach ($depts as $id=>$name) {
					echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
				  }
				  ?>
				  <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
				<button type="button" class="btn btn-success button">
				  <?php echo __('Add'); ?>
				</button>
			</div>
		  </fieldset>
	  </div>
	 </div>
	 
  <!-- ============== TEAM MEMBERSHIP =================== -->
  
	<div class="panel-heading">
		<div class="panel-title table-caption">Teams</div>
	</div>
	<div class="panel-body">
	   <div class="col-md-12" id="teams">
		  <fieldset class="form-group">
			<div class="form-group">
				<div><label class="control-label"><?php echo __('Assigned Teams'); ?></label></div>
				<div><p><?php echo __(
				"Agent will have access to tickets assigned to a team they belong to regardless of the ticket's department. Alerts can be enabled for each associated team."
				); ?>
				</p></div>
			</div>
	<?php
	$teams = Team::getTeams();
	foreach ($staff->teams as $TM) {
	  unset($teams[$TM->team_id]);
	}
	?>
			<div class="form-group form-inline" id="join_team">
				<i class="fa fa-plus-circle"></i>
				<select class="form-control" id="add_team" data-quick-add="team">
				  <option value="0">&mdash; <?php echo __('Select Team');?> &mdash;</option>
				  <?php
				  foreach ($teams as $id=>$name) {
					echo sprintf('<option value="%d">%s</option>',$id,Format::htmlchars($name));
				  }
				  ?>
				  <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
				</select>
				<button type="button" class="btn btn-success button">
				  <?php echo __('Add'); ?>
				</button>
			</div>
		  </fieldset>
		  <fieldset class="form-group">
			<div id="team_member_template" class="form-group hidden row" style="border-bottom:1px solid #D3D3D3;">
			  <div class="col-md-6">
				<input type="hidden" data-name="teams[]" value="" />
			  </div>
			  <div class="col-md-6">
				<label>
				  <input type="checkbox" data-name="team_alerts" value="1" />
				  <?php echo __('Alerts'); ?>
				</label>
				<a data-type="warning" data-message='You must click "<?php echo esc_attr($submit_text); ?>" to apply the new changes' href="#" href="#" class="growl_popup pull-right drop-membership" title="<?php echo __('Delete');
				  ?>"><i class="fa fa-trash-o"></i></a>
			  </div>
			</div>
		  </fieldset>
	  </div> 
	</div>
	
  <!-- ================= PERMISSIONS ====================== -->
  
	<div class="panel-heading">
		<div class="panel-title table-caption">Permissions</div>
	</div>

	<div class="panel-body">
			<div id="permissions">
	<?php
		$permissions = array();
		foreach (RolePermission::allPermissions() as $g => $perms) {
			foreach ($perms as $k=>$P) {
				if (!$P['primary'])
					continue;
				if (!isset($permissions[$g]))
					$permissions[$g] = array();
				$permissions[$g][$k] = $P;
			}
		}
	?>
		<ul class="nav nav-tabs">
	<?php
		$first = true;
		foreach ($permissions as $g => $perms) { ?>
		  <li <?php if ($first) { echo 'class="active"'; $first=false; } ?>>
			<a data-toggle="tab" href="#<?php echo Format::slugify($g); ?>"><?php echo Format::htmlchars(__($g));?></a>
		  </li>
	<?php } ?>
		</ul>
		  <div class="tab-content">
	<?php
		$first = true;
		foreach ($permissions as $g => $perms) { ?>
		<div class="tab-pane <?php if ($first) { echo 'fade active in';  $first = false; }
		  ?>" id="<?php echo Format::slugify($g); ?>">
	<?php foreach ($perms as $k => $v) { ?>
			  <div class="checkbox">
				<label>
				<?php
				echo sprintf('<input type="checkbox" name="perms[]" value="%s" %s />',
				  $k, ($staff->hasPerm($k)) ? 'checked="checked"' : '');
				?>
				&nbsp;
				<?php echo Format::htmlchars(__($v['title'])); ?>
				—
				<?php echo Format::htmlchars(__($v['desc'])); ?>
			   </label>
			  </div>
	<?php   } ?>
		</div>
	<?php } ?>
		</div>
	  </div>
	</div>	
		<div class="panel-heading">
			<a class="internal_note"><div class="panel-title table-caption"><i class="fa fa-plus-square"></i>&nbsp;<?php echo __('Internal Notes');?>:<small class="text-muted"><em> <?php echo __("Be liberal, they're internal");?></em></small></div></a>
		</div>
		<div class="panel-body notes" style="display:none">
			<textarea  name="notes" class="summernote-base">
				  <?php echo Format::viewableImages($staff->notes); ?>
			</textarea>
		</div>
	
	  <div class="form-group" style="text-align:center;margin-top:20px;">
		  <input class="btn btn-success" type="submit" name="submit" value="<?php echo esc_attr($submit_text); ?>">
		  <input type="reset" class="btn"  name="reset"  value="<?php echo __('Reset');?>">
		  <input type="button" class="btn" name="cancel" value="<?php echo __('Cancel');?>" onclick="window.history.go(-1);">
	  </div>
	</form>
	</div>
</div>

<script type="text/javascript">
var addAccess = function(daid, name, role, alerts, error) {
  if (!daid) return;
  var copy = $('#extended_access_template').clone();

  copy.find('[data-name=dept_access\\[\\]]')
    .attr('name', 'dept_access[]')
    .val(daid);
  copy.find('[data-name^=dept_access_role]')
    .attr('name', 'dept_access_role['+daid+']')
    .val(role || 0);
  copy.find('[data-name^=dept_access_alerts]')
    .attr('name', 'dept_access_alerts['+daid+']')
    .prop('checked', alerts);
  copy.find('div:first').append(document.createTextNode(name));
  copy.attr('id', '').show().insertBefore($('#add_extended_access'));
  copy.removeClass('hidden')
  if (error)
      $('<div class="error">').text(error).appendTo(copy.find('div:last')).addClass('alert-danger');
  copy.find('a.drop-access').click(function(e) {
	  e.preventDefault();
    $('#add_access').append(
      $('<option>')
        .attr('value', copy.find('input[name^=dept_access][type=hidden]').val())
        .text(copy.find('div:first').text())
    );
    copy.fadeOut(function() { $(this).remove(); });
  });
};

$('#add_extended_access').find('button').on('click', function() {
  var selected = $('#add_access').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
      return;
  addAccess(id, selected.text(), 0, true);
  selected.remove();
  return false;
});

var joinTeam = function(teamid, name, alerts, error) {
  if (!teamid) return;
  var copy = $('#team_member_template').clone();

  copy.find('[data-name=teams\\[\\]]')
    .attr('name', 'teams[]')
    .val(teamid);
  copy.find('[data-name^=team_alerts]')
    .attr('name', 'team_alerts['+teamid+']')
    .prop('checked', alerts);
  copy.find('div:first').append(document.createTextNode(name));
  copy.attr('id', '').show().insertBefore($('#join_team'));
  copy.removeClass('hidden');
  if (error)
      $('<div class="error">').text(error).appendTo(copy.find('div:last')).addClass('alert-danger');
  copy.find('a.drop-membership').click(function(e) {
	  e.preventDefault();
    $('#add_team').append(
      $('<option>')
        .attr('value', copy.find('input[name^=teams][type=hidden]').val())
        .text(copy.find('div:first').text())
    );
    copy.fadeOut(function() { $(this).remove(); });
  });
};

$('#join_team').find('button').on('click', function() {
  var selected = $('#add_team').find(':selected'),
      id = parseInt(selected.val());
  if (!id)
      return;
  joinTeam(id, selected.text(), true);
  selected.remove();
  return false;
});


<?php
foreach ($staff->dept_access as $dept_access) {
  if (!$dept_access->dept_id) continue;
  echo sprintf('addAccess(%d, %s, %d, %d, %s);', $dept_access->dept_id,
    JsonDataEncoder::encode($dept_access->dept->getName()),
    $dept_access->role_id,
    $dept_access->isAlertsEnabled(),
    JsonDataEncoder::encode(@$errors['dept_access'][$dept_access->dept_id])
  );
}

foreach ($staff->teams as $member) {
  echo sprintf('joinTeam(%d, %s, %d, %s);', $member->team_id,
    JsonDataEncoder::encode($member->team->getName()),
    $member->isAlertsEnabled(),
    JsonDataEncoder::encode(@$errors['teams'][$member->team_id])
  );
}

?>
</script>

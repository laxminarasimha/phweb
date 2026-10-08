<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
if(!($maxfileuploads=ini_get('max_file_uploads')))
    $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;
?>
<div class="panel">

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Task Settings and Options');?></div>
</div>

<div class="panel-body">
<form action="settings.php?t=tasks" method="post">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="tasks" >

<ul class="nav nav-tabs" id="tasks-tabs">
	<li class="active"><a href="#settings" data-toggle="tab">
		<i class="fa fa-cog"></i> <?php echo __('Settings'); ?></a></li>
	<li><a href="#alerts" data-toggle="tab">
		<i class="fa fa-bell"></i> <?php echo __('Alerts &amp; Notices'); ?></a></li>
</ul>
<div class="tab-content tab-content-bordered" id="tasks-tabs_container">
<div id="settings" class="tab-pane fade in active">

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Global default task settings and options.'); ?></em></small>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="task_number_format"><?php echo __('Default Task Number Format'); ?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#number_format"></i></label>
			<div class="input-group">
			<input type="text" name="task_number_format" id="task_number_format" class="form-control" value="<?php
				echo esc_attr($config['task_number_format']); ?>"/>
			<div class="input-group-addon">
				<span class="faded"><?php echo __('e.g.'); ?>
					<span id="format-example"><?php
					if ($config['task_sequence_id'])
						$seq = Sequence::lookup($config['task_sequence_id']);
					if (!isset($seq))
						$seq = new RandomSequence();
					echo esc_html($seq->current($config['task_number_format']));
					?>
					</span>
				</span>
			</div>
		<?php
		if(!empty($errors['task_number_format'])){
			echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_number_format']).'</div>';
		}
			?>
			</div>
		</div>
		<div class="col-sm-6 form-group">
			<label for="task_sequence_id"><?php echo __('Default Task Number Sequence'); ?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#sequence_id"></i></label>
			<div class="input-group">
			<?php $selected = 'selected="selected"'; ?>
			<select name="task_sequence_id" id="task_sequence_id" class="form-control">
				<option value="0" <?php if ($config['task_sequence_id'] == 0) echo $selected;
				?>>&mdash; <?php echo __('Random'); ?> &mdash;</option>
				<?php foreach (Sequence::objects() as $s) { ?>
				<option value="<?php echo esc_attr($s->id); ?>" <?php
				if ($config['task_sequence_id'] == $s->id) echo $selected;
				?>><?php echo esc_html($s->name); ?></option>
				<?php } ?>
			</select>
			<div class="input-group-btn">
				<button class="action-button btn pull-right" onclick="javascript:
					$.dialog('ajax.php/sequence/manage', 205);
					return false;
					"><i class="fa fa-cog"></i> <?php echo __('Manage'); ?>
				</button>
				&nbsp;<i class="help-tip fa fa-question-circle" href="#sequence_id"></i>
			</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="default_task_priority_id" class="required"><?php echo __('Default Priority');?>:</label> &nbsp;<i class="help-tip fa fa-question-circle" href="#default_priority"></i>
			<div class="input-group">
			<select name="default_task_priority_id" id="default_task_priority_id" class="form-control">
				<?php
				$priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
				while (list($id,$tag) = db_fetch_row($priorities)){ ?>
					<option value="<?php echo esc_attr($id); ?>"<?php echo
					($config['default_task_priority_id']==$id)?'selected':''; ?>><?php echo esc_html($tag); ?></option>
				<?php
				} ?>
			</select>
			<?php
			if(!empty($errors['default_task_priority_id'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['default_task_priority_id']).'</div>';
			}
			?>
			</div>
		</div>
		<div class="col-sm-6 form-group">
			<label><?php echo __('Task Attachment Settings');?>: &nbsp;<i class="help-tip fa fa-question-circle" href="#task_attachment_settings"></i><small class="text-muted"><em><?php
				$tform = TaskForm::objects()->one()->getForm();
				$f = $tform->getField('description'); ?>
			</label><br />
		<a class="btn btn-outline-default field-config" style="overflow:inherit"
		href="#ajax.php/form/field-config/<?php
			echo esc_attr($f->get('id')); ?>"
			onclick="javascript:
				$.dialog($(this).attr('href').substr(1), [201]);
				return false;
			"><i class="fa fa-edit"></i> <?php echo __('Configure Settings'); ?></a></em></small>
		</div>
	</div>
</div>
</div>

<div id="alerts" class="tab-pane fade">

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('New Task Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#task_alert"></i>
	</div>
</div>

<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="task_alert_active"  value="1"
				<?php echo $config['task_alert_active'] ? 'checked="checked"' : ''; ?> />
			<?php echo __('Enable'); ?>
			&nbsp;
			<input type="radio" name="task_alert_active"  value="0"
				<?php echo !$config['task_alert_active'] ? 'checked="checked"' : ''; ?> />
			<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['task_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_alert_active']).'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_alert_admin" <?php
					echo $config['task_alert_admin'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Admin Email'); ?> <em>(<?php echo esc_html($cfg->getAdminEmail()); ?>)</em>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_alert_dept_manager"
				<?php echo $config['task_alert_dept_manager'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_alert_dept_members"
				<?php echo $config['task_alert_dept_members'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Department Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('New Activity Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#activity_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="task_activity_alert_active" value="1"
				<?php echo $config['task_activity_alert_active'] ? 'checked="checked"' : ''; ?> />
				<?php echo __('Enable'); ?>
				&nbsp;
			<input type="radio" name="task_activity_alert_active"  value="0"
				<?php echo !$config['task_activity_alert_active'] ? 'checked="checked"' : ''; ?> />
				<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['task_activity_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_activity_alert_active']).'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_activity_alert_laststaff" <?php
					echo $config['task_activity_alert_laststaff'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Last Respondent'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_activity_alert_assigned"
				<?php echo $config['task_activity_alert_assigned'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_activity_alert_dept_manager"
				<?php echo $config['task_activity_alert_dept_manager'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Department Manager'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Task Assignment Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#assignment_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input name="task_assignment_alert_active" value="1" type="radio"
				<?php echo $config['task_assignment_alert_active'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Enable'); ?>
				&nbsp;
			<input name="task_assignment_alert_active" value="0" type="radio"
				<?php echo !$config['task_assignment_alert_active'] ? 'checked="checked"' : ''; ?>>
				<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['task_assignment_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_assignment_alert_active']).'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_assignment_alert_staff" <?php echo
					$config['task_assignment_alert_staff'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox"name="task_assignment_alert_team_lead" <?php
					echo $config['task_assignment_alert_team_lead'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Team Lead'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox"name="task_assignment_alert_team_members"
					<?php echo $config['task_assignment_alert_team_members'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Team Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Task Transfer Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#transfer_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="task_transfer_alert_active"  value="1"
				<?php echo $config['task_transfer_alert_active'] ? 'checked="checked"' : ''; ?> />
				<?php echo __('Enable'); ?>
				&nbsp;
			<input type="radio" name="task_transfer_alert_active"  value="0"
				<?php echo !$config['task_transfer_alert_active'] ? 'checked="checked"' : ''; ?> />
				<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['task_transfer_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_transfer_alert_active']).'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_transfer_alert_assigned"
					<?php echo $config['task_transfer_alert_assigned']?'checked="checked"':''; ?>>
					<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_transfer_alert_dept_manager"
					<?php echo $config['task_transfer_alert_dept_manager'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_transfer_alert_dept_members"
					<?php echo $config['task_transfer_alert_dept_members'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Department Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Overdue Task Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#overdue_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="task_overdue_alert_active"  value="1"
				<?php echo $config['task_overdue_alert_active'] ? 'checked="checked"' : ''; ?> /> <?php echo __('Enable'); ?>
				&nbsp;
			<input type="radio" name="task_overdue_alert_active"  value="0"
				<?php echo !$config['task_overdue_alert_active'] ? 'checked="checked"' : ''; ?> /> <?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['task_overdue_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.esc_html($errors['task_overdue_alert_active']).'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_overdue_alert_assigned" <?php
					echo $config['task_overdue_alert_assigned'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_overdue_alert_dept_manager" <?php
					echo $config['task_overdue_alert_dept_manager'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="task_overdue_alert_dept_members" <?php
					echo $config['task_overdue_alert_dept_members'] ? 'checked="checked"' : ''; ?>>
					<?php echo __('Department Members'); ?>
			</label>
		</div>
	</div>
</div>
</div>
<br />
<p style="text-align:center;">
	<input class="button btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes');?>">
	<input class="button btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes');?>">
</p>
</div>
</form>
</div>
</div>
<script type="text/javascript">
$(function() {
    var request = null,
      update_example = function() {
      request && request.abort();
      request = $.get('ajax.php/sequence/'
        + $('[name=task_sequence_id] :selected').val(),
        {'format': $('[name=task_number_format]').val()},
        function(data) { $('#format-example').text(data); }
      );
    };
    $('[name=task_sequence_id]').on('change', update_example);
    $('[name=task_number_format]').on('keyup', update_example);
});
</script>

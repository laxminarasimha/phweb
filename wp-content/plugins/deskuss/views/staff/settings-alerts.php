<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('New Ticket Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#ticket_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="ticket_alert_active"  value="1"
			<?php echo $config['ticket_alert_active']?'checked':''; ?>
				/> <?php echo __('Enable'); ?>
			<input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; 	?> /> <?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['ticket_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['ticket_alert_active'].'</div>';
			}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>>
					<?php echo __('Admin Email'); ?> <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>>
					<?php echo __('Department Manager'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>>
				<?php echo __('Department Members'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="ticket_alert_acct_manager" <?php echo $config['ticket_alert_acct_manager']?'checked':''; ?>>
				<?php echo __('Organization Account Manager'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('New Message Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#message_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="message_alert_active"  value="1"
				<?php echo $config['message_alert_active']?'checked':''; ?> /> 
				<?php echo __('Enable'); ?>&nbsp;
			<input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':'';?> />
			<?php echo __('Disable'); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>>
				<?php echo __('Last Respondent'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="message_alert_assigned" <?php
					echo $config['message_alert_assigned']?'checked':''; ?>>
					<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="message_alert_dept_manager" <?php
					echo $config['message_alert_dept_manager']?'checked':''; ?>>
					<?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="message_alert_acct_manager" <?php echo $config['message_alert_acct_manager']?'checked':''; ?>>
				<?php echo __('Organization Account Manager'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('New Internal Activity Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#internal_note_alert"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> />
				<?php echo __('Enable'); ?>&nbsp;
			<input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> />
				<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['note_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['note_alert_active'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="note_alert_laststaff" <?php echo
					$config['note_alert_laststaff']?'checked':''; ?>> <?php echo __('Last Respondent'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>>
					<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>>
				<?php echo __('Department Manager'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Ticket Assignment Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#assignment_alert_ticket"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input name="assigned_alert_active" value="1" type="radio"
				<?php echo $config['assigned_alert_active']?'checked="checked"':''; ?>> <?php echo __('Enable'); ?>&nbsp;
			<input name="assigned_alert_active" value="0" type="radio"
				<?php echo !$config['assigned_alert_active']?'checked="checked"':''; ?>> <?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['assigned_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['assigned_alert_active'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="assigned_alert_staff" <?php echo
					$config['assigned_alert_staff']?'checked':''; ?>> <?php echo __('Assigned Agent'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox"name="assigned_alert_team_lead" <?php
					echo $config['assigned_alert_team_lead']?'checked':''; ?>> <?php echo __('Team Lead'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
				<?php echo __('Team Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Ticket Transfer Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#transfer_alert_ticket"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> />
				<?php echo __('Enable'); ?>
			<input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> />
				<?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['transfer_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['transfer_alert_active'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>>
				<?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>>
				<?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
				<?php echo __('Department Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Overdue Ticket Alert'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#overdue_alert_ticket"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label><?php echo __('Status'); ?>:</label>
			<input type="radio" name="overdue_alert_active"  value="1"
                <?php echo $config['overdue_alert_active']?'checked':''; ?> /> <?php echo __('Enable'); ?>
              <input type="radio" name="overdue_alert_active"  value="0"
                <?php echo !$config['overdue_alert_active']?'checked':''; ?> /> <?php echo __('Disable'); ?>
			<?php
			if(!empty($errors['overdue_alert_active'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['overdue_alert_active'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="overdue_alert_assigned" <?php
					echo $config['overdue_alert_assigned']?'checked':''; ?>> <?php echo __('Assigned Agent / Team'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="overdue_alert_dept_manager" <?php
					echo $config['overdue_alert_dept_manager']?'checked':''; ?>> <?php echo __('Department Manager'); ?>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="overdue_alert_dept_members" <?php
					echo $config['overdue_alert_dept_members']?'checked':''; ?>> <?php echo __('Department Members'); ?>
			</label>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('System Alerts'); ?></em></small>&nbsp;<i class="help-tip fa fa-question-circle" href="#system_alerts"></i>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled">
					<?php echo __('System Errors'); ?>
				<em><?php echo __('(enabled by default)'); ?></em>
			</label>
		</div>
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>>
					<?php echo __('SQL errors'); ?>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label>
				<input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>>
					<?php echo __('Excessive failed login attempts'); ?>
			</label>
		</div>
	</div>
</div>

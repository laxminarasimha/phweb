<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
if(!($maxfileuploads=ini_get('max_file_uploads')))
    $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;
?>
<div class="panel">

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Ticket Settings and Options');?></div>
</div>

<div class="panel-body">
<form action="settings.php?t=tickets" method="post">

<?php csrf_token(); ?>
<input type="hidden" name="t" value="tickets" >

<ul class="nav nav-tabs">
	<li class="active">
		<a href="#settings"  data-toggle="tab"> <i class="panel-title-icon fa fa-cog"></i> <?php echo __('Settings'); ?>
		</a>
	</li>
	<li>
		<a href="#autoresp"  data-toggle="tab"><i class="panel-title-icon fa fa-reply-all"></i> <?php echo __('Autoresponder'); ?></a>
	</li>
	<li>
		<a href="#alerts"  data-toggle="tab"><i class="panel-title-icon fa fa-bell"></i> <?php echo __('Alerts and Notices'); ?></a>
	</li>
</ul>

<div class="tab-content tab-content-bordered">
<div class="tab-pane fade in active" id="settings">
	<div class="panel-heading">
		<div class="panel-title table-caption">
			<small class="text-muted"><em><?php echo __('System-wide default ticket settings and options.'); ?></em></small>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="ticket_number_format"><?php echo __('Default Ticket Number Format'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#number_format"></i></label>
				<div class="input-group">
				<input type="text" class="form-control" id="ticket_number_format" name="ticket_number_format" value="<?php
					echo esc_attr($config['ticket_number_format']); ?>"/>
				<div class="input-group-addon">
					<span class="faded"><?php echo __('e.g.'); ?>
						<span id="format-example"><?php
							if ($config['ticket_sequence_id'])
								$seq = Sequence::lookup($config['ticket_sequence_id']);
							if (!isset($seq))
								$seq = new RandomSequence();
							echo esc_html($seq->current($config['ticket_number_format']));
							?>
						</span>
					</span>
				</div>
				<?php
				if(!empty($errors['ticket_number_format'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['ticket_number_format'].'</div>';
				}
				?>
				</div>
			</div>
			<div class="col-sm-6 form-group">
				<label for="ticket_sequence_id"><?php echo __('Default Ticket Number Sequence'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#sequence_id"></i></label>
				<div class="input-group">
				<?php $selected = 'selected="selected"'; ?>
				<select class="form-control" id="ticket_sequence_id" name="ticket_sequence_id">
					<option value="0" <?php if ($config['ticket_sequence_id'] == 0) echo $selected;
					?>>&mdash; <?php echo __('Random'); ?> &mdash;</option>
					<?php foreach (Sequence::objects() as $s) { ?>
					<option value="<?php echo $s->id; ?>" <?php
					if ($config['ticket_sequence_id'] == $s->id) echo $selected;
					?>><?php echo $s->name; ?></option>
					<?php } ?>
				</select>
				<div class="input-group-btn">
					<button class="btn action-button" onclick="javascript:
					$.dialog('ajax.php/sequence/manage', 205);
					return false;
					"><i class="fa fa-cog"></i> <?php echo __('Manage'); ?></button>
					<i class="help-tip fa fa-question-circle" href="#sequence_id"></i>
				</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="default_ticket_status_id" class="required"><?php echo __('Default Status'); ?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#default_ticket_status"></i></label>
				<select class="form-control" id="default_ticket_status_id" name="default_ticket_status_id">
					<?php
					$criteria = array('states' => array('open'));
					foreach (TicketStatusList::getStatuses($criteria) as $status) {
					$name = $status->getName();
					if (!($isenabled = $status->isEnabled()))
						$name.=' '.__('(disabled)');
						echo sprintf('<option value="%d" %s %s>%s</option>',
							$status->getId(),
							($config['default_ticket_status_id'] ==
							 $status->getId() && $isenabled)
							 ? 'selected="selected"' : '',
							 $isenabled ? '' : 'disabled="disabled"',
							 $name
							);
					}
					?>
				</select>
				<?php
				if(!empty($errors['default_ticket_status_id'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['default_ticket_status_id'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="default_sla_id" class="required"><?php echo __('Default SLA');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#default_sla"></i></label>
				<select class="form-control" id="default_sla_id" name="default_sla_id">
					<option value="0">&mdash; <?php echo __('None');?> &mdash;</option>
					<?php
					if($slas=SLA::getSLAs()) {
						foreach($slas as $id => $name) {
							echo sprintf('<option value="%d" %s>%s</option>',
							$id,
							($config['default_sla_id'] && $id==$config['default_sla_id'])?'selected="selected"':'',
							$name);
						}
					}
					?>
				</select>
				<?php
				if(!empty($errors['default_sla_id'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['default_sla_id'].'</div>';
				}
				?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="default_priority_id" class="required"><?php echo __('Default Priority');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#default_priority"></i></label>
				<select class="form-control" id="default_priority_id" name="default_priority_id">
					<?php
					$priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
					while (list($id,$tag) = db_fetch_row($priorities)){ ?>
						<option value="<?php echo $id; ?>"<?php echo ($config['default_priority_id']==$id)?'selected':''; ?>><?php echo $tag; ?></option>
					<?php
					} ?>
				</select>
				<?php
				if(!empty($errors['default_priority_id'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['default_priority_id'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="ticket_lock"><?php echo __('Lock Semantics'); ?>:</label>
				<select class="form-control" id="ticket_lock" name="ticket_lock" <?php if ($cfg->getLockTime() == 0) echo 'disabled="disabled"'; ?>>
				<?php foreach (array(
					Lock::MODE_DISABLED => __('Disabled'),
					Lock::MODE_ON_VIEW => __('Lock on view'),
					Lock::MODE_ON_ACTIVITY => __('Lock on activity'),
					) as $v => $desc) { ?>
						<option value="<?php echo $v; ?>" <?php
						if ($config['ticket_lock'] == $v) echo 'selected="selected"';
						?>><?php echo $desc; ?></option>
				<?php } ?>
				</select>
				<?php
				if(!empty($errors['ticket_lock'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['ticket_lock'].'</div>';
				}
				?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="max_open_tickets" class="required"><?php echo __('Maximum Open Tickets');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#maximum_open_tickets"></i></label>
				<div class="input-group">
					<input type="text" class="form-control" id="max_open_tickets" name="max_open_tickets" value="<?php echo $config['max_open_tickets']; ?>">
					<div class="input-group-addon">
						<?php echo __('per end user'); ?>
					</div>
				</div>
				<?php
				if(!empty($errors['max_open_tickets'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['max_open_tickets'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="enable_captcha" style="display:block;"><?php echo __('Human Verification');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#human_verification"></i></label>
				<input type="checkbox" name="enable_captcha" id="enable_captcha" <?php echo $config['enable_captcha']?'checked="checked"':''; ?>>
					<?php echo __('Enable CAPTCHA on new web tickets.');?>
				<?php
				if(!empty($errors['enable_captcha'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['enable_captcha'].'</div>';
				}
				?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="auto_claim_tickets" style="display:block;"><?php echo __('Claim on Response');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#claim_tickets"></i></label>
				<input type="checkbox" name="auto_claim_tickets" id="auto_claim_tickets" <?php 
					echo $config['auto_claim_tickets']?'checked="checked"':''; ?>>
					<?php echo __('Enable'); ?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="show_assigned_tickets" style="display:block;"><?php echo __('Assigned Tickets');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#assigned_tickets"></i></label>
				<input type="checkbox" name="show_assigned_tickets" id="show_assigned_tickets" <?php
					echo !$config['show_assigned_tickets']?'checked="checked"':''; ?>>
					<?php echo __('Exclude assigned tickets from open queue.'); ?>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="show_answered_tickets" style="display:block;"><?php echo __('Answered Tickets');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#answered_tickets"></i></label>
				<input type="checkbox" name="show_answered_tickets" id="show_answered_tickets" <?php
					echo !$config['show_answered_tickets']?'checked="checked"':''; ?>>
					<?php echo __('Exclude answered tickets from open queue.'); ?>
			</div>
		</div>

	</div>

	<div class="panel-heading">
		<div class="panel-title table-caption">
			<?php echo __('Auto-Close');?>: <small class="text-muted"><em><?php echo __('Automatically close tickets that have been inactive for a set number of days. Tickets an agent is actively working on are skipped.');?></em></small>
		</div>
	</div>

	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="auto_close_enabled" style="display:block;"><?php echo __('Enable Auto-Close');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_enabled"></i></label>
				<input type="checkbox" name="auto_close_enabled" id="auto_close_enabled" <?php
					echo $config['auto_close_enabled']?'checked="checked"':''; ?>>
					<?php echo __('Close inactive tickets automatically.'); ?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="auto_close_dry_run" style="display:block;"><?php echo __('Dry Run');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_dry_run"></i></label>
				<input type="checkbox" name="auto_close_dry_run" id="auto_close_dry_run" <?php
					echo $config['auto_close_dry_run']?'checked="checked"':''; ?>>
					<?php echo __('Log candidates without closing them (recommended for the first week).'); ?>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="auto_close_days" class="required"><?php echo __('Inactivity Days');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_days"></i></label>
				<div class="input-group">
					<input type="number" min="1" class="form-control" id="auto_close_days" name="auto_close_days" value="<?php echo (int)$config['auto_close_days']; ?>">
					<div class="input-group-addon"><?php echo __('days'); ?></div>
				</div>
				<?php
				if(!empty($errors['auto_close_days'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['auto_close_days'].'</div>';
				}
				?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="auto_close_status_id" class="required"><?php echo __('Close Status');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_status_id"></i></label>
				<select class="form-control" id="auto_close_status_id" name="auto_close_status_id">
					<?php
					foreach (TicketStatusList::getStatuses(array('states' => array('closed'), 'enabled' => true)) as $s) {
						echo sprintf('<option value="%d" %s>%s</option>',
							$s->getId(),
							($config['auto_close_status_id'] == $s->getId()) ? 'selected="selected"' : '',
							__($s->getName())
						);
					}
					?>
				</select>
				<?php
				if(!empty($errors['auto_close_status_id'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['auto_close_status_id'].'</div>';
				}
				?>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="auto_close_batch_size"><?php echo __('Batch Size');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_batch"></i></label>
				<input type="number" min="1" class="form-control" id="auto_close_batch_size" name="auto_close_batch_size" value="<?php echo (int)$config['auto_close_batch_size']; ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="auto_close_min_priority"><?php echo __('Minimum Priority');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#auto_close_priority"></i></label>
				<select class="form-control" id="auto_close_min_priority" name="auto_close_min_priority">
					<option value="0" <?php echo ($config['auto_close_min_priority']==0)?'selected="selected"':''; ?>><?php echo __('All priorities'); ?></option>
					<?php
					$priorities = db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
					while (list($id,$tag) = db_fetch_row($priorities)) { ?>
						<option value="<?php echo $id; ?>" <?php echo ($config['auto_close_min_priority']==$id)?'selected="selected"':''; ?>><?php echo $tag; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6 form-group">
				<label><?php echo __('Skip Tickets');?>:</label>
				<div style="margin-top:6px">
					<label style="font-weight:normal;display:block;">
						<input type="checkbox" name="auto_close_skip_pending" id="auto_close_skip_pending" <?php echo $config['auto_close_skip_pending']?'checked="checked"':''; ?>>
						<?php echo __('In <b>Pending</b> state (In Progress)'); ?>
					</label>
					<label style="font-weight:normal;display:block;">
						<input type="checkbox" name="auto_close_skip_assigned" id="auto_close_skip_assigned" <?php echo $config['auto_close_skip_assigned']?'checked="checked"':''; ?>>
						<?php echo __('Assigned to an agent'); ?>
					</label>
					<label style="font-weight:normal;display:block;">
						<input type="checkbox" name="auto_close_skip_locked" id="auto_close_skip_locked" <?php echo $config['auto_close_skip_locked']?'checked="checked"':''; ?>>
						<?php echo __('With an active lock'); ?>
					</label>
				</div>
			</div>
		</div>

	</div>
	
	<div class="panel-heading">
		<div class="panel-title table-caption">
			<?php echo __('Attachments');?>: <small class="text-muted"><em><?php echo __('Size and maximum uploads setting mainly apply to web tickets');?></em></small>
		</div>
	</div>
	
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-12">
				<label><?php echo __('Ticket Attachment Settings');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#ticket_attachment_settings"></i><small class="text-muted"><em><?php
					$tform = TicketForm::objects()->one()->getForm();
					$f = $tform->getField('message'); ?>
				</label>
				<a class="btn btn-outline-default action-button field-config" style="overflow:inherit"
				href="#ajax.php/form/field-config/<?php echo $f->get('id'); ?>"
				onclick="javascript:
					$.dialog($(this).attr('href').substr(1), [201]);
					return false;
				"><i class="fa fa-edit"></i> <?php echo __('Configure Settings'); ?></a></em></small>
			</div>
		</div>
	</div>
</div>
	
<div class="tab-pane fade" id="autoresp" data-tip-namespace="settings.autoresponder">
	<?php include STAFFINC_DIR . 'settings-autoresp.php'; ?>
</div>

<div class="tab-pane fade" id="alerts" data-tip-namespace="settings.alerts">
	<?php include STAFFINC_DIR . 'settings-alerts.php'; ?>
</div>
	
<p style="text-align:center">
	<input class="btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes'); ?>">
	<input class="btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes'); ?>">
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
        + $('[name=ticket_sequence_id] :selected').val(),
        {'format': $('[name=ticket_number_format]').val()},
        function(data) { $('#format-example').text(data); }
      );
    };
    $('[name=ticket_sequence_id]').on('change', update_example);
    $('[name=ticket_number_format]').on('keyup', update_example);
});
</script>

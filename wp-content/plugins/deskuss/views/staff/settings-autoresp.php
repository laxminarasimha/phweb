<div class="panel-heading">
	<div class="panel-title table-caption">
		<small class="text-muted"><em><?php echo __('Global setting - can be disabled at department or email level'); ?></em></small>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="form-group">
			<label class="col-md-2"><?php echo __('New Ticket'); ?>:
			</label>
			<div class="col-md-5">
				<label>
					<input type="checkbox" name="ticket_autoresponder" <?php 
						echo $config['ticket_autoresponder'] ? 'checked="checked"' : ''; ?>/>
					<?php echo __('Ticket Owner'); ?>&nbsp;
					<i class="help-tip fa fa-question-circle" href="#new_ticket"></i>
				</label>
			</div>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="form-group">
			<label class="col-md-2"><?php echo __('New Ticket by Agent'); ?>:
			</label>
			<div class="col-md-5">
				<label>
					<input type="checkbox" name="ticket_notice_active" <?php 
						echo $config['ticket_notice_active'] ? 'checked="checked"' : ''; ?>/>
					<?php echo __('Ticket Owner'); ?>&nbsp;
					<i class="help-tip fa fa-question-circle" href="#new_ticket_by_staff"></i>
				</label>
			</div>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="form-group">
			<label class="col-md-2"><?php echo __('New Message'); ?>:
			</label>
			<div class="col-md-5">
				<label>
					<input type="checkbox" name="message_autoresponder" <?php 
						echo $config['message_autoresponder'] ? 'checked="checked"' : ''; ?>/>
						<?php echo __('Submitter: Send receipt confirmation'); ?>&nbsp;
					<i class="help-tip fa fa-question-circle" href="#new_message_for_submitter"></i>
				</label>
				<label>
					<input type="checkbox" name="message_autoresponder_collabs" <?php echo $config['message_autoresponder_collabs'] ? 'checked="checked"' : ''; ?>/>
					<?php echo __('Participants: Send new activity notice'); ?>&nbsp;
					<i class="help-tip fa fa-question-circle" href="#new_message_for_participants"></i>
				</label>
			</div>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="form-group">
			<label class="col-md-2"><?php echo __('Overlimit Notice'); ?>:</label>
			<div class="col-md-5">
				<label>
					<input type="checkbox" name="overlimit_notice_active" <?php 
						echo $config['overlimit_notice_active'] ? 'checked="checked"' : ''; ?>/>
					<?php echo __('Ticket Submitter'); ?>&nbsp;
					<i class="help-tip fa fa-question-circle" href="#overlimit_notice"></i>
				</label>
			</div>
		</div>
	</div>
</div>

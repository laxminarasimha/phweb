<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
?>

<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Email Settings and Options');?>
	</div>
</div>
<div class="panel-body">
<form action="emailsettings.php" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="emails">
<div class="col-sm-12 text-muted">
	<em><?php echo __('Note that some of the global settings can be overridden at department/email level.');?></em><br /><br />
</div>

<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __('Default Template Set'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#default_email_templates"></i>
		</label>
		<select class="form-control" name="default_template_id">
			<option value="">&mdash; <?php echo __('Select Default Email Template Set'); ?> &mdash;</option>
			<?php
			$sql='SELECT tpl_id, name FROM '.EMAIL_TEMPLATE_GRP_TABLE
				.' WHERE isactive =1 ORDER BY name';
			if(($res=db_query($sql)) && db_num_rows($res)){
				while (list($id, $name) = db_fetch_row($res)){
					$selected = ($config['default_template_id']==$id)?'selected="selected"':''; ?>
					<option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
				<?php
				}
			} ?>
		</select>
		<?php if(!empty($errors['default_template_id'])){ ?>
			<div class="alert-danger"><?php echo $errors['default_template_id']; ?></div>
		<?php } ?>
	</div>
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __('Default System Email');?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#default_system_email"></i>
		</label>
		<select class="form-control" name="default_email_id">
			<option value=0 disabled><?php echo __('Select One');?></option>
			<?php
			$sql='SELECT email_id,email,name FROM '.EMAIL_TABLE;
			if(($res=db_query($sql)) && db_num_rows($res)){
				while (list($id,$email,$name) = db_fetch_row($res)){
					$email=$name?"$name &lt;$email&gt;":$email;
					?>
					<option value="<?php echo $id; ?>"<?php echo ($config['default_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
				<?php
				}
			} ?>
		</select>
		<?php if(!empty($errors['default_email_id'])){ ?>
			<div class="alert-danger"><?php echo $errors['default_email_id']; ?></div>
		<?php } ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __('Default Alert Email');?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#default_alert_email"></i>
		</label>
		<select class="form-control" name="alert_email_id">
			<option value="0" selected="selected"><?php echo __('Use Default System Email (above)');?></option>
			<?php
			$sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']);
			if(($res=db_query($sql)) && db_num_rows($res)){
				while (list($id,$email,$name) = db_fetch_row($res)){
					$email=$name?"$name &lt;$email&gt;":$email;
					?>
					<option value="<?php echo $id; ?>"<?php echo ($config['alert_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
				<?php
				}
			} ?>
		</select>
		<?php if(!empty($errors['alert_email_id'])){ ?>
			<div class="alert-danger"><?php echo $errors['alert_email_id']; ?></div>
		<?php } ?>
	</div>
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __("Admin's Email Address");?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#admins_email_address"></i>
		</label>
		<input class="form-control" type="text" name="admin_email" value="<?php echo $config['admin_email']; ?>"> 
		<?php if(!empty($errors['admin_email'])){ ?>
			<div class="alert-danger"><?php echo $errors['admin_email']; ?></div>
		<?php } ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block;"><?php echo __("Verify Email Addresses");?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#verify_email_addrs"></i>
		</label>
		<input type="checkbox" name="verify_email_addrs" <?php
			if ($config['verify_email_addrs']) echo 'checked="checked"'; ?>>
			<?php echo __('Verify email address domain'); ?>
	</div>
</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Incoming Emails'); ?>
	</div>
</div>

<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block;"><?php echo __('Email Fetching'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#email_fetching"></i>
		</label>
		<input type="checkbox" name="enable_mail_polling" value=1 <?php 
			echo $config['enable_mail_polling']? 'checked="checked"': ''; ?>>
			<?php echo __('Enable'); ?>
	</div>
	<div class="col-sm-6 form-group">
		<label style="display:block;"><?php echo __('Fetch Emails using Auto Cron'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#enable_autocron_fetch"></i>
		</label>
		<input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked="checked"':''; ?>>
			<?php echo __('Fetch on auto-cron'); ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group"> 
		<label style="display:block;"><?php echo __('Strip Quoted Reply');?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#strip_quoted_reply"></i>
		</label>
		<input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked="checked"':''; ?>>
			<?php echo __('Enable'); ?>
		<?php if(!empty($errors['strip_quoted_reply'])){ ?>
			<div class="alert-danger"><?php echo $errors['strip_quoted_reply']; ?></div>
		<?php } ?>
	</div>
	<div class="col-sm-6 form-group"> 
		<label><?php echo __('Reply Separator Tag');?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#reply_separator_tag"></i>
		</label>
		<input class="form-control" type="text" name="reply_separator" value="<?php echo $config['reply_separator']; ?>">
		<?php if(!empty($errors['reply_separator'])){ ?>
			<div class="alert-danger"><?php echo $errors['reply_separator']; ?></div>
		<?php } ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group"> 
		<label style="display:block;"><?php echo __('Emailed Tickets Priority'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#emailed_tickets_priority"></i>
		</label>
		<input type="checkbox" name="use_email_priority" value="1" <?php 
			echo $config['use_email_priority'] ?'checked="checked"':''; ?>>
			<?php echo __('Enable'); ?>
	</div>
	<div class="col-sm-6 form-group"> 
		<label style="display:block;"><?php echo __('Accept All Emails'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#accept_all_emails"></i>
		</label>
		<input type="checkbox" name="accept_unregistered_email" <?php
			echo $config['accept_unregistered_email'] ? 'checked="checked"' : ''; ?>/>
			<?php echo __('Accept email from unknown Users'); ?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group"> 
		<label style="display:block;"><?php echo __('Accept Email Collaborators'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#accept_email_collaborators"></i> 
		</label> 
		<input type="checkbox" name="add_email_collabs" <?php
			echo $config['add_email_collabs'] ? 'checked="checked"' : ''; ?>/>
			<?php echo __('Automatically add collaborators from email fields'); ?>&nbsp;
	</div>
</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('Outgoing Emails');?>
	</div>
</div>
<div class="panel-body">

<div class="row">
	<div class="col-sm-12 text-muted">
		<em><?php echo __('Default email only applies to outgoing emails without SMTP settings');?></em><br /><br />
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Default MTA'); ?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#default_mta"></i>
		</label>
		<select class="form-control" name="default_smtp_id">
			<option value=0 selected="selected"><?php echo __('None: Use PHP mail function');?></option>
			<?php
			$sql=' SELECT email_id, email, name, smtp_host '
				.' FROM '.EMAIL_TABLE.' WHERE smtp_active = 1';
			if(($res=db_query($sql)) && db_num_rows($res)){
				while (list($id, $email, $name, $host) = db_fetch_row($res)){
					$email=$name?"$name &lt;$email&gt;":$email;
					?>
					<option value="<?php echo $id; ?>"<?php echo ($config['default_smtp_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
				<?php
				}
			} ?>
		</select>
		<?php if(!empty($errors['default_smtp_id'])){ ?>
			<div class="alert-danger"><?php echo $errors['default_smtp_id']; ?></div>
		<?php } ?>
	</div>
	<div class="col-sm-6 form-group"> 
		<label style="display:block;"><?php echo __('Attachments');?>:&nbsp;
			<i class="help-tip fa fa-question-circle" href="#ticket_response_files"></i>
		</label>
		<input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked="checked"':''; ?>>
			<?php echo __('Email attachments to the user'); ?>
	</div>
</div>
</div>
<br />
<p style="text-align:center;">
	<input class="btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes');?>">
	<input class="btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes');?>">
</p>
</form>
</div>
</div>
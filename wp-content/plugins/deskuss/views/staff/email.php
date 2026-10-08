<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = array();
if($email && $_REQUEST['a']!='add'){
    $title=__('Update Email Address');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$email->getInfo();
    $info['id']=$email->getId();
    if($info['mail_delete'])
        $info['postfetch']='delete';
    elseif($info['mail_archivefolder'])
        $info['postfetch']='archive';
    else
        $info['postfetch']=''; //nothing.
    if($info['userpass'])
        $passwdtxt=__('To change password enter new password below');

    $qs += array('id' => $email->getId());
}else {
    $title=__('Add New Email Address');
    $action='create';
    $submit_text=__('Submit');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    if (!$info['mail_fetchfreq'])
        $info['mail_fetchfreq'] = 5;
    if (!$info['mail_fetchmax'])
        $info['mail_fetchmax'] = 10;
    if (!isset($info['smtp_auth']))
        $info['smtp_auth'] = 1;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel">
<div class="panel-heading">
<div class="panel-title table-caption"><?php echo esc_html($title); ?>
    <?php if (isset($info['email'])) { ?><small>
    — <?php echo esc_html($info['email']); ?></small>
    <?php } ?>
</div>
</div>
<br />
<form action="emails.php?<?php echo Http::build_query($qs); ?>" method="post" class="save">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="<?php echo esc_attr($action); ?>">
<input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
<input type="hidden" name="id" value="<?php echo esc_attr($info['id']); ?>">

<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('Email Information and Settings');?></strong></em>
	</div>
</div>
<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __('Email Address');?>: </label>
		<input type="text" class="form-control" size="35" name="email" value="<?php echo esc_attr($info['email']); ?>" autofocus>
		<?php
		if(!empty($errors['email'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['email'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label class="required"><?php echo __('Email Name');?>: </label>
		<input type="text" class="form-control" size="35" name="name" value="<?php echo esc_attr($info['name']); ?>">
		<?php
		if(!empty($errors['name'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['name'].'</div>';
		}
		?>
	</div>
</div>
</div>
	
<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('New Ticket Settings'); ?></strong></em>
	</div>
</div>
<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Department');?>:   &nbsp;<i class="help-tip fa fa-question-circle" href="#new_ticket_department"></i>
		</label>
		<select class="form-control" name="dept_id">
			<option value="0" selected="selected">&mdash; <?php
			echo __('System Default'); ?> &mdash;</option>
			<?php
			if (($depts=Dept::getDepartments())) {
				foreach ($depts as $id => $name) {
					$selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
					echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
				}
			}
			?>
		</select>
		<?php
		if(!empty($errors['dept_id'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['dept_id'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label><?php echo __('Priority');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#new_ticket_priority"></i>
		</label>
		<select class="form-control" name="priority_id">
			<option value="0" selected="selected">&mdash; <?php
			echo __('System Default'); ?> &mdash;</option>
			<?php
			$sql='SELECT priority_id, priority_desc FROM '.PRIORITY_TABLE.' pri ORDER by priority_urgency DESC';
			if(($res=db_query($sql)) && db_num_rows($res)){
				while(list($id,$name)=db_fetch_row($res)){
					$selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
					echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
				}
			}
			?>
		</select>
		<?php
		if(!empty($errors['priority_id'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['priority_id'].'</div>';
		}
		?>
	</div>
</div>
	
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Auto-Response');?>:&nbsp; <i class="help-tip fa fa-question-circle" href="#auto_response"></i>
		</label><br />
		<label><input type="checkbox" name="noautoresp" value="1" <?php echo $info['noautoresp']?'checked="checked"':''; ?> >
			&nbsp;<?php echo sprintf(__('<strong>Disable</strong> for %s'), __('this email')); ?>
		</label>
	</div>
</div>
</div>

<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('Email Login Information'); ?></strong>
		&nbsp;<i class="help-tip fa fa-question-circle" href="#login_information"></i></em>
	</div>
</div>
<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Username');?>: </label>
		<input type="text" class="form-control" size="35" name="userid" value="<?php echo esc_attr($info['userid']); ?>" autocomplete="off" autocorrect="off">
		<?php
		if(!empty($errors['userid'])){
			echo '<div class="alert-danger">&nbsp;'.esc_html($errors['userid']).'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label><?php echo __('Password');?>: 
		<em><?php echo esc_html($passwdtxt); ?></em>
		</label>
		<input type="password" class="form-control" size="35" name="passwd" value="<?php echo esc_attr($info['passwd']); ?>"autocomplete="off">
		<?php
		if(!empty($errors['passwd'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['passwd'].'</div>';
		}
		?>
	</div>
</div>
</div>
	
<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('Fetching Email via IMAP or POP'); ?></strong>
		&nbsp;<i class="help-tip fa fa-question-circle" href="#mail_account"></i>
		</em>
		<?php
		if(!empty($errors['mail'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail'].'</div>';
		}
		?>
	</div>
</div>
<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Status');?>: </label>
		<label><input type="radio" name="mail_active"  value="1"   <?php echo $info['mail_active']?'checked="checked"':''; ?> />&nbsp;<?php echo __('Enabled'); ?></label>
		&nbsp;&nbsp;
		<label><input type="radio" name="mail_active"  value="0"   <?php echo !$info['mail_active']?'checked="checked"':''; ?> />&nbsp;<?php echo __('Disabled'); ?></label>
		<?php
		if(!empty($errors['mail_active'])){
			echo '<div class="alert-danger">&nbsp;'.esc_html($errors['mail_active']).'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label><?php echo __('Mail Box Protocol');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#protocol"></i>
		</label>
		<select class="form-control" name="mail_proto">
			<option value=''>&mdash; <?php echo __('Select protocol'); ?> &mdash;</option>
			<?php
			foreach (MailFetcher::getSupportedProtos() as $proto=>$desc) { ?>
				<option value="<?php echo esc_attr($proto); ?>" <?php
				if ($info['mail_proto'] == $proto) echo 'selected="selected"';
				?>><?php echo $desc; ?></option>
			<?php } ?>
		</select>
		<?php
		if(!empty($errors['mail_proto'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_proto'].'</div>';
		}
		?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Hostname');?>:&nbsp;&nbsp;<i class="help-tip fa fa-question-circle" href="#host_and_port"></i>
		</label>
		<span>
			<input type="text" class="form-control" name="mail_host" size=35 value="<?php echo esc_attr($info['mail_host']); ?>">
		</span>
		<?php
		if(!empty($errors['mail_host'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_host'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Port Number');?>:   &nbsp;<i class="help-tip fa fa-question-circle" href="#host_and_port"></i>
		</label>
		<input type="text" class="form-control" name="mail_port" size=6 value="<?php echo esc_attr($info['mail_port']?$info['mail_port']:''); ?>">
		<?php
		if(!empty($errors['mail_port'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_port'].'</div>';
		}
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Fetch Frequency');?>:   &nbsp;<i class="help-tip fa fa-question-circle" href="#fetch_frequency"></i>
		</label>
		<div class="input-group">
		<input type="text" class="form-control" name="mail_fetchfreq" size=4 value="<?php echo esc_attr($info['mail_fetchfreq']?$info['mail_fetchfreq']:''); ?>"> <span class="input-group-addon"><?php echo __('minutes'); ?>	</span>
		</div>
		<?php
		if(!empty($errors['mail_fetchfreq'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_fetchfreq'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label><?php echo __('Emails Per Fetch');?>:&nbsp;<i class="help-tip fa fa-question-circle" href="#emails_per_fetch"></i>
		</label>
		<input type="text" class="form-control" name="mail_fetchmax" size=4 value="<?php echo esc_attr(
			$info['mail_fetchmax']?$info['mail_fetchmax']:''); ?>">
		<?php
		if(!empty($errors['mail_fetchmax'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_fetchmax'].'</div>';
		}
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Fetched Emails');?>: </label>
		<label><input type="radio" name="postfetch" value="archive" <?php echo ($info['postfetch']=='archive')? 'checked="checked"': ''; ?> >
		<?php
		if(!empty($errors['postfetch'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['postfetch'].'</div>';
		}
		?>
		<?php echo __('Move to folder'); ?>:
		<input type="text" name="mail_archivefolder" size="20" value="<?php echo esc_attr($info['mail_archivefolder']); ?>"/></label>
		<?php
		if(!empty($errors['mail_archivefolder'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['mail_archivefolder'].'</div>';
		}
		?>
		<i class="help-tip fa fa-question-circle" href="#fetched_emails"></i>
		<br/>
		<label><input type="radio" name="postfetch" value="delete" <?php echo ($info['postfetch']=='delete')? 'checked="checked"': ''; ?> >
		<?php echo __('Delete emails'); ?></label>
		<br/>
		<label><input type="radio" name="postfetch" value="" <?php echo (isset($info['postfetch']) && !$info['postfetch'])? 'checked="checked"': ''; ?> >
		<?php echo __('Do nothing <em>(not recommended)</em>'); ?></label>	
	</div>
</div>
</div>
	
<div class="panel-heading">
	<div class="panel-title">
		<em><strong><?php echo __('Sending Email via SMTP'); ?></strong>
		&nbsp;<i class="help-tip fa fa-question-circle" href="#smtp_settings"></i>
		</em>
		<?php
		if(!empty($errors['smtp'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['smtp'].'</div>';
		}
		?>
	</div>
</div>
<div class="panel-body">
<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Status');?>: </label>
		<label><input type="radio" name="smtp_active" value="1" <?php echo $info['smtp_active']?'checked':''; ?> />&nbsp;<?php echo __('Enabled');?></label>
			&nbsp;
		<label><input type="radio" name="smtp_active" value="0" <?php echo !$info['smtp_active']?'checked':''; ?> />&nbsp;<?php echo __('Disabled');?></label>
		<?php
		if(!empty($errors['smtp_active'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['smtp_active'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Authentication Required');?>: </label>
		<label><input type="radio" name="smtp_auth"  value="1"
		 <?php echo $info['smtp_auth']?'checked':''; ?> /> <?php echo __('Yes'); ?></label>
		&nbsp;
		<label><input type="radio" name="smtp_auth"  value="0"
		 <?php echo !$info['smtp_auth']?'checked':''; ?> /> <?php echo __('No'); ?>
		</label>
		<?php
		if(!empty($errors['smtp_auth'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['smtp_auth'].'</div>';
		}
		?>
	</div>
</div>
<div class="row">
	<div class="col-sm-6 form-group">
		<label><?php echo __('Hostname');?>:&nbsp;&nbsp;<i class="help-tip fa fa-question-circle" href="#host_and_port"></i>
		</label>
		<input type="text" class="form-control" name="smtp_host" size=35 value="<?php echo esc_attr($info['smtp_host']); ?>">
		<?php
		if(!empty($errors['smtp_host'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['smtp_host'].'</div>';
		}
		?>
	</div>
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Port Number');?>:   &nbsp;&nbsp;<i class="help-tip fa fa-question-circle" href="#host_and_port"></i>
		</label>
		<input type="text" class="form-control" name="smtp_port" size=6 value="<?php echo esc_attr($info['smtp_port']?$info['smtp_port']:''); ?>">
		<?php
		if(!empty($errors['smtp_port'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['smtp_port'].'</div>';
		}
		?>
	</div>
</div>

<div class="row">
	<div class="col-sm-6 form-group">
		<label style="display:block"><?php echo __('Header Spoofing');?>:   &nbsp;&nbsp; <i class="help-tip fa fa-question-circle" href="#header_spoofing"></i>
		</label>
		<label><input type="checkbox" name="smtp_spoofing" value="1" <?php echo $info['smtp_spoofing'] ?'checked="checked"':''; ?>>
		&nbsp;<?php echo sprintf(__('Allow for %s'), __('this email')); ?></label>
	</div>
</div>	
</div>

<div class="panel-heading">
	<a class="internal_note"><div class="panel-title table-caption">
		<i class="fa fa-plus-square"></i>&nbsp;<em><strong><?php echo __('Internal Notes');?></strong>:
		<?php
		if(!empty($errors['notes'])){
			echo '<div class="alert-danger">&nbsp;'.$errors['notes'].'</div>';
		}
		echo '<small class="text-muted">'. __("Be liberal, they're internal").'</small>';?></em>
	</div></a>
</div>
<div class="panel-body notes" style="display:none;">
	<textarea class="summernote-base no-bar" name="notes" cols="21" rows="5" style="width: 60%;"><?php echo $info['notes']; ?></textarea>
</div>

<div class="form-group" style="text-align:center;margin-top:20px;">
    <input type="submit" class="btn btn-success" name="submit" value="<?php echo esc_attr($submit_text); ?>">
    <input type="reset" class="btn btn-info" name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" class="btn btn-default" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="emails.php"'>
</div>

</form>
</div>

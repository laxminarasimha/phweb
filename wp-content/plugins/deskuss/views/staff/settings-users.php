<?php
if(!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');

?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Users Settings'); ?></div>
</div>
<div class="panel-body">
<form action="settings.php?t=users" method="post">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="users" >
<ul class="nav nav-tabs">
	<li class="active">
		<a href="#settings" data-toggle="tab"> <i class="panel-title-icon fa fa-cog"></i> <?php echo __('Settings'); ?></a>
	</li>
	<li>
		<a href="#templates" data-toggle="tab"><i class="panel-title-icon fa fa-file-text"></i> <?php echo __('Templates'); ?></a>
	</li>
</ul>
<div class="tab-content tab-content-bordered">
	<div class="tab-pane fade in active" id="settings">
	<div class="panel-heading">
		<div class="panel-title table-caption">
			<?php echo __('General Settings'); ?>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="client_name_format"><?php echo __('Name Formatting');?>:</label>
				<select name="client_name_format" id="client_name_format" class="form-control" data-allow-clear="true">
				<?php foreach (PersonsName::allFormats() as $n=>$f) {
					list($desc, $func) = $f;
					$selected = ($config['client_name_format'] == $n) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $n; ?>" <?php echo $selected;
					?>><?php echo __($desc); ?></option>
				<?php } ?>
				</select>
			</div>
			<div class="col-sm-6 form-group">
				<label for="client_avatar"><?php echo __('Avatar Source');?>:</label>
				<select name="client_avatar" id="client_avatar" class="form-control" data-allow-clear="true">
				<?php require_once INCLUDE_DIR . 'class.avatar.php';
					foreach (AvatarSource::allSources() as $id=>$class) {
					$modes = $class::getModes();
					if ($modes) {
						echo "<optgroup label=\"{$class::getName()}\">";
						foreach ($modes as $mid=>$mname) {
							$oid = "$id.$mid";
							$selected = ($config['client_avatar'] == $oid) ? 'selected="selected"' : '';
							echo "<option {$selected} value=\"{$oid}\">{$class::getName()} / {$mname}</option>";
						}
						echo "</optgroup>";
					}
					else {
						$selected = ($config['client_avatar'] == $id) ? 'selected="selected"' : '';
						echo "<option {$selected} value=\"{$id}\">{$class::getName()}</option>";
						}
					}
				?>
				</select>
				<?php
				if(!empty($errors['client_avatar'])){
					echo '<div class="alert-danger">&nbsp;'.Format::htmlchars($errors['client_avatar']).'</div>';
				}
				?>
			</div>
		</div>
	</div>
	
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Authentication Settings'); ?></div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="clients_only" style="display:block;"><?php echo __('Registration Required'); ?>: <i class="help-tip fa fa-question-circle" href="#registration_method"></i></label>
				<input type="checkbox" name="clients_only" id="clients_only" <?php
					if ($config['clients_only'])
						echo 'checked="checked"'; ?>/> <?php echo __(
						'Require registration and login to create tickets'); ?>
			</div>
			<div class="col-sm-6 form-group">
				<label for="client_registration"><?php echo __('Registration Method'); ?>: <i class="help-tip fa fa-question-circle" href="#registration_method"></i></label>
				<select name="client_registration" id="client_registration" class="form-control" data-allow-clear="true">
					<?php foreach (array(
						'disabled' => __('Disabled — All users are guests'),
						'public' => __('Public — Anyone can register'),
						'closed' => __('Private — Only agents can register users'),)
						as $key=>$val) { ?>
							<option value="<?php echo $key; ?>" <?php
							if ($config['client_registration'] == $key)
								echo 'selected="selected"'; ?>><?php echo $val;
							?></option><?php
						} ?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="client_max_logins"><?php echo __('User Excessive Logins'); ?>:</label>
				<select name="client_max_logins" id="client_max_logins" class="form-control" data-allow-clear="true">
				<?php
					for($i = 1; $i <= 10; $i++){
						echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_max_logins']==$i)?'selected="selected"':''), $i);
					}
				?>
				</select>
				<small class="text-muted"><?php echo __(
				'failed login attempt(s) allowed before a lock-out is enforced'); ?></small>
			</div>
			<div class="col-sm-6 form-group">
				<label for="client_login_timeout"><?php echo __('User Locked Out Time'); ?>:</label>
				<select name="client_login_timeout" id="client_login_timeout" class="form-control" data-allow-clear="true">
					<?php
					for($i = 1; $i <= 10; $i++){
						echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['client_login_timeout']==$i)?'selected="selected"':''), $i);
					}
					?>
				</select>
				<small class="text-muted"> <?php echo __('minutes locked out'); ?></small>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="client_session_timeout"><?php echo __('User Session Timeout'); ?>: <i class="help-tip fa fa-question-circle" href="#client_session_timeout"></i></label>
				<input type="text" id="client_session_timeout" name="client_session_timeout" class="form-control" size=6 value="<?php 
					echo $config['client_session_timeout']; ?>">
			</div>
			<div class="col-sm-6 form-group">
				<label for="allow_auth_tokens" style="display:block;"><?php echo __('Authentication Token'); ?>: <i class="help-tip fa fa-question-circle" href="#allow_auth_tokens"></i></label>
				<input type="checkbox" name="allow_auth_tokens" id="allow_auth_tokens" <?php
					if ($config['allow_auth_tokens'])
					echo 'checked="checked"'; ?>/> <?php
					echo __('Enable use of authentication tokens to auto-login users'); ?>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label for="client_verify_email" style="display:block;"><?php echo __('Client Quick Access'); ?>: <i class="help-tip fa fa-question-circle" href="#client_verify_email"></i></label>
				<input type="checkbox" name="client_verify_email" id="client_verify_email" <?php
					if ($config['client_verify_email'])
					echo 'checked="checked"'; ?>/> <?php echo __(
					'Require email verification on "Check Ticket Status" page'); ?>
			</div>
		</div>
	</div>
	<p style="text-align:center;">
		<input class="btn btn-success" type="submit" name="submit" value="<?php echo __('Save Changes'); ?>">
		<input class="btn btn-info" type="reset" name="reset" value="<?php echo __('Reset Changes'); ?>">
	</p>
	</div>
	<div class="tab-pane fade" id="templates">
		<div class="panel table-light table-responsive">
			<div class="panel-heading">
				<div class="panel-title table-caption"><?php echo __(
				'Authentication and Registration Templates &amp; Pages'); ?>
				</div>
			</div>
			<table class="table" width="940" border="0" cellspacing="0" cellpadding="2">
				<tbody>
				<?php
				$res = db_query('select distinct(`type`), id, notes, name, updated from '
				.PAGE_TABLE
				.' where isactive=1 group by `type`');
				$contents = array();
				while (list($type, $id, $notes, $name, $u) = db_fetch_row($res))
				$contents[$type] = array($id, $name, $notes, $u);

				$manage_content = function($title, $content) use ($contents) {
				list($id, $name, $notes, $upd) = $contents[$content];
				$notes = explode('. ', $notes);
				$notes = $notes[0];
				?><tr><td colspan="2">
				<div style="padding:2px 5px">
				<a href="#ajax.php/content/<?php echo $id; ?>/manage"
				onclick="javascript:
					$.dialog($(this).attr('href').substr(1), 201);
				return false;" class="pull-left"><i class="panel-title-icon fa fa-file-text"
					style="color:#bbb;"></i> </a>
				<span style="display:inline-block;width:90%;width:calc(100% - 32px);padding-left:10px;line-height:1.2em">
				<a href="#ajax.php/content/<?php echo $id; ?>/manage"
				onclick="javascript:
					$.dialog($(this).attr('href').substr(1), 201, null, {size:'large'});
				return false;"><?php
				echo Format::htmlchars($title); ?></a></span><br/>
					<span class="faded"><?php
					echo Format::display($notes); ?>
					<br><em><?php echo sprintf(__('Last Updated %s'), Format::datetime($upd));
					?></em></span>
				</div></td></tr><?php
				}; ?>
				<?php $manage_content(__('Guest Ticket Access'), 'access-link'); ?>
				<?php $manage_content(__('Sign-In Page'), 'banner-client'); ?>
				<?php $manage_content(__('Password Reset Email'), 'pwreset-client'); ?>
				<?php $manage_content(__('Please Confirm Email Address Page'), 'registration-confirm'); ?>
				<?php $manage_content(__('Account Confirmation Email'), 'registration-client'); ?>
				<?php $manage_content(__('Account Confirmed Page'), 'registration-thanks'); ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
</form>
</div>
</div>

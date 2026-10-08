<?php
if (!defined('DSKADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');

?>
<div class="panel">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Agents Settings'); ?></div>
</div>
<div class="panel-body">
<form action="settings.php?t=agents" method="post">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="agents" >
<ul class="nav nav-tabs" id="agents-tabs">
	<li class="active">
		<a href="#settings" data-toggle="tab"><i class="panel-title-icon fa fa-cog"></i> <?php echo __('Settings'); ?></a>
	</li>
	<li>
		<a href="#templates" data-toggle="tab"><i class="panel-title-icon fa fa-file-text"></i> <?php echo __('Templates'); ?></a>
	</li>
</ul>
<div class="tab-content tab-content-bordered" id="agents-tabs_container">
<div class="tab-pane fade in active" id="settings">
<div class="panel-heading">
	<div class="panel-title table-caption">
		<?php echo __('General Settings'); ?>
	</div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="agent_name_format"><?php echo __('Name Formatting');?>:</label>
			<select class="form-control" name="agent_name_format" id="agent_name_format">
				<?php foreach (PersonsName::allFormats() as $n=>$f) {
					list($desc, $func) = $f;
					$selected = ($config['agent_name_format'] == $n) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $n; ?>" <?php echo $selected;
					?>><?php echo __($desc); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="col-sm-6 form-group">
			<label for="agent_avatar"><?php echo __('Avatar Source');?>:</label>
			<select class="form-control" name="agent_avatar" id="agent_avatar">
				<?php require_once INCLUDE_DIR . 'class.avatar.php';
				foreach (AvatarSource::allSources() as $id=>$class) {
					$modes = $class::getModes();
					if($modes){
						echo "<optgroup label=\"{$class::getName()}\">";
						foreach ($modes as $mid=>$mname) {
							$oid = "$id.$mid";
							$selected = ($config['agent_avatar'] == $oid) ? 'selected="selected"' : '';
							echo "<option {$selected} value=\"{$oid}\">{$class::getName()} / {$mname}</option>";
						}
						echo "</optgroup>";
					}else{
						$selected = ($config['agent_avatar'] == $id) ? 'selected="selected"' : '';
						echo "<option {$selected} value=\"{$id}\">{$class::getName()}</option>";
					}
				} ?>
			</select>
			<?php
			if(!empty($errors['agent_avatar'])){
				echo '<div class="alert-danger">&nbsp;'.Format::htmlchars($errors['agent_avatar']).'</div>';
			}
			?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="hide_staff_name" style="display:block;"><?php echo __('Agent Identity Masking');?>:</label>
			<input type="checkbox" name="hide_staff_name" id="hide_staff_name" <?php echo $config['hide_staff_name']?'checked="checked"':''; ?>>
				<?php echo __("Hide agent's name on responses."); ?>
		</div>
	</div>
</div>

<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Authentication Settings'); ?></div>
</div>
<div class="panel-body">
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="passwd_reset_period"><?php echo __('Password Expiration Policy'); ?>: </label>
			<select class="form-control" name="passwd_reset_period" id="passwd_reset_period">
				<option value="0"> &mdash; <?php echo __('No expiration'); ?> &mdash;</option>
				<?php
					for ($i = 1; $i <= 12; $i++) {
					echo sprintf('<option value="%d" %s>%s</option>',
						$i,(($config['passwd_reset_period']==$i)?'selected="selected"':''),
						sprintf(_N('Monthly', 'Every %d months', $i), $i));
					}
				?>
			</select>
			<?php
			if(!empty($errors['passwd_reset_period'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['passwd_reset_period'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="allow_pw_reset" style="display:block;"><?php echo __('Allow Password Resets'); ?>: <i class="help-tip fa fa-question-circle" href="#allow_password_resets"></i></label>
			<input type="checkbox" name="allow_pw_reset" id="allow_pw_reset" <?php echo $config['allow_pw_reset']?'checked="checked"':''; ?>>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="staff_max_logins"><?php echo __('Agent Excessive Logins'); ?>:</label>
			<select class="form-control" name="staff_max_logins" id="staff_max_logins">
				<?php
					for ($i = 1; $i <= 10; $i++) {
						echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_max_logins']==$i)?'selected="selected"':''), $i);
					}
				?>
			</select>
			<small class="text-muted"><?php echo __(
			'failed login attempt(s) allowed before a lock-out is enforced'); ?></small>
		</div>
		<div class="col-sm-6 form-group">
			<label for="staff_login_timeout"><?php echo __('Agent Locked Out Time'); ?>:</label>
			<select class="form-control" name="staff_login_timeout" id="staff_login_timeout">
				<?php
					for ($i = 1; $i <= 10; $i++) {
						echo sprintf('<option value="%d" %s>%d</option>', $i,(($config['staff_login_timeout']==$i)?'selected="selected"':''), $i);
					}
				?>
			</select>
			<small class="text-muted"> <?php echo __('minutes locked out'); ?></small>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="pw_reset_window"><?php echo __('Reset Token Expiration'); ?>: <i class="help-tip fa fa-question-circle" href="#reset_token_expiration"></i></label>
			<div class="input-group">
				<input class="form-control" type="text" name="pw_reset_window" id="pw_reset_window" size="6" value="<?php
					echo $config['pw_reset_window']; ?>">
				<div class="input-group-addon">
					<span><?php echo __('minutes'); ?></span>
				</div>
			</div>
			<?php
			if(!empty($errors['pw_reset_window'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['pw_reset_window'].'</div>';
			}
			?>
		</div>
		<div class="col-sm-6 form-group">
			<label for="staff_session_timeout"><?php echo __('Agent Session Timeout'); ?>: <i class="help-tip fa fa-question-circle" href="#staff_session_timeout"></i></label>
			<div class="input-group">
				<input class="form-control" type="text" name="staff_session_timeout" id="staff_session_timeout" size="6" value="<?php echo $config['staff_session_timeout']; ?>">
				<div class="input-group-addon">
					<span><?php echo __('minutes'); ?></span>
				</div>
			</div>
			<small class="text-muted"> <?php echo __('(0 to disable session timeout)'); ?></small>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6 form-group">
			<label for="staff_ip_binding"><?php echo __('Bind Agent Session to IP'); ?>: <i class="help-tip fa fa-question-circle" href="#bind_staff_session_to_ip"></i></label>&nbsp;
			<input type="checkbox" name="staff_ip_binding" id="staff_ip_binding" <?php 
				echo $config['staff_ip_binding']?'checked="checked"':''; ?>>
		</div>
		<div class="col-sm-6 form-group">
			<label for="force_2fa_email"><?php echo __('Force 2FA via Email'); ?>: <i class="help-tip fa fa-question-circle" href="#force_2fa_email"></i></label>&nbsp;
			<input type="checkbox" name="force_2fa_email" id="force_2fa_email" <?php
				echo !empty($config['force_2fa_email'])?'checked="checked"':''; ?>>
		</div>
	</div>
</div>
<p style="text-align:center">
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
				?>
				<tr>
					<td colspan="2">
						<div style="padding:2px 5px">
							<a href="#ajax.php/content/<?php echo $id; ?>/manage"
							   onclick="javascript:
								$.dialog($(this).attr('href').substr(1), 201);
								return false;" class="pull-left">
							<i class="panel-title-icon fa fa-file-text" style="color:#bbb;"></i>
							</a>
							<span style="display:inline-block;width:90%;width:calc(100% - 32px);padding-left:10px;line-height:1.2em">
							<a href="#ajax.php/content/<?php echo $id; ?>/manage"
							   onclick="javascript:
								$.dialog($(this).attr('href').substr(1), 201, null, {size:'large'});
								return false;"><?php
								echo Format::htmlchars($title); ?>
								</a>
							</span>
							<span class="faded"><?php
								echo Format::display($notes); ?>
								<br />
								<em><?php echo sprintf(__('Last Updated %s'), Format::datetime($upd));
								?></em>
							</span>
						</div>
					</td>
				</tr>
				<?php
					}; 
				?>
				<?php $manage_content(__('Agent Welcome Email'), 'registration-staff'); ?>
				<?php $manage_content(__('Sign-in Login Banner'), 'banner-staff'); ?>
				<?php $manage_content(__('Password Reset Email'), 'pwreset-staff'); ?>
			</tbody>
		</table>
	</div>
</div>
</div>
</form>
</div>
</div>
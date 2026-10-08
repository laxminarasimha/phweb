<?php
global $cfg;

if (!$info['title'])
    $info['title'] = sprintf(__('Register: %s'), Format::htmlchars($user->getName()));

if (!$_POST) {
    $info['sendemail'] = true; // send email confirmation.
}

?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title"><?php echo $info['title']; ?>
			<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body perfectScrollbar" style="position:relative;height:400px">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>
	<div>
		<p id="msg_info"><i class="fa fa-info-circle"></i>&nbsp;<?php
			echo sprintf(__(
			'Complete the form below to create a user account for <b>%s</b>.'
			), Format::htmlchars($user->getName()->getOriginal())
			); ?>
		</p>
	</div>
	<div id="user-registration" style="display:block; margin:5px;">
		<form method="post" class="user"
			action="#users/<?php echo $user->getId(); ?>/register">
			<?php csrf_token(); ?>
			<input type="hidden" name="id" value="<?php echo $user->getId(); ?>" />
			<div class="form-group">
				<fieldset class="form-group">
					<label class="control-label">
						<?php echo __('User Account Login'); ?>
					</label>
					<div class="form-group row">
						<div class="col-md-3">
							<?php echo __('Authentication Sources'); ?>:
						</div>
						<div class="col-md-9">
							<select class="form-control" name="backend" id="backend-selection" onchange="javascript:
								if (this.value != '' && this.value != 'client') {
									$('#activation').hide();
									$('#password').hide();
								}
								else {
									$('#activation').show();
									if ($('#sendemail').is(':checked'))
										$('#password').hide();
									else
										$('#password').show();
								}
								">
									<option value="">&mdash; <?php echo __('Use any available backend'); ?> &mdash;</option>
								<?php foreach (UserAuthenticationBackend::allRegistered() as $ab) {
									if (!$ab->supportsInteractiveAuthentication()) continue; ?>
									<option value="<?php echo $ab::$id; ?>" <?php
										if ($info['backend'] == $ab::$id)
											echo 'selected="selected"'; ?>><?php
										echo $ab->getName(); ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-3">
							<?php echo __('Username'); ?>:
						</div>
						<div class="col-md-9">
							<input class="form-control" type="text" size="35" name="username" value="<?php echo $info['username'] ?: $user->getEmail(); ?>">
							<?php
								if(!empty($errors['username'])){
									echo '<div class="alert-danger">&nbsp;'.$errors['username'].'</div>';
								}
							?>
						</div>
					</div>
					<div class="form-group row" id="activation">
						<div class="col-md-3">
							<?php echo __('Status'); ?>:
						</div>
						<div class="col-md-9 checkbox">
						  <label><input type="checkbox" id="sendemail" name="sendemail" value="1"
							<?php echo $info['sendemail'] ? 'checked="checked"' :
							''; ?> ><?php echo sprintf(__(
							'Send account activation email to %s.'), $user->getEmail()); ?>
						  </label>
						</div>
					</div>
					<div id="password"
						style="<?php echo $info['sendemail'] ? 'display:none;' : ''; ?>"
						>
						<div class="form-group row">
							<div class="col-md-3">
								<?php echo __('Temporary Password'); ?>:
							</div>
							<div class="col-md-9">
								<input class="form-control" type="password" size="35" name="passwd1" value="<?php echo $info['passwd1']; ?>">
								<?php
									if(!empty($errors['passwd1'])){
										echo '<div class="alert-danger">&nbsp;'.$errors['passwd1'].'</div>';
									}
								?>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
							   <?php echo __('Confirm Password'); ?>:
							</div>
							<div class="col-md-9">
								<input class="form-control" type="password" size="35" name="passwd2" value="<?php echo $info['passwd2']; ?>">
								&nbsp;
								<?php
								if(!empty($errors['passwd2'])){
									echo '<div class="alert-danger">&nbsp;'.$errors['passwd2'].'</div>';
									}
								?>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-3">
								<?php echo __('Password Change'); ?>:
							</div>
							<div class="col-md-9">
								<input type="checkbox" name="pwreset-flag" value="1" <?php
									echo $info['pwreset-flag'] ?  'checked="checked"' : ''; ?>>
									<?php echo __('Require password change on login'); ?>
								<br/>
								<input type="checkbox" name="forbid-pwreset-flag" value="1" <?php
									echo $info['forbid-pwreset-flag'] ?  'checked="checked"' : ''; ?>>
									<?php echo __('User cannot change password'); ?>
							</div>
						</div>
					</div>	
				</fieldset>
				<fieldset>
					<label><?php echo __('User Preferences'); ?></label>
					<div class="form-group row">
						<div class="col-md-3">
							<?php echo __('Time Zone'); ?>:
						</div>
						<div class="col-md-9">
							<?php
							$TZ_NAME = 'timezone';
							$TZ_TIMEZONE = $info['timezone'];
							include STAFFINC_DIR.'templates/timezone.tmpl.php'; ?>
							<?php
								if(!empty($errors['timezone'])){
									echo '<div class="alert-danger">&nbsp;'.$errors['timezone'].'</div>';
								}
							?>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="form-group" style="margin-top:20px;">
				<input type="submit" class="btn btn-success" value="<?php echo __('Create Account'); ?>">				
				<input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
				<input type="button" name="cancel" class="close_me btn" value="<?php echo __('Cancel'); ?>">
			 </div>
		</form>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
	$('.perfectScrollbar').perfectScrollbar();
});
		  

$(function() {
    $(document).on('click', 'input#sendemail', function(e) {
        if ($(this).prop('checked'))
            $('div#password').hide();
        else
            $('div#password').show();
    });
});
</script>

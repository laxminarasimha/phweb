<?php
$account = $user->getAccount();
$access = (isset($info['_target']) && $info['_target'] == 'access');

if (!$info['title'])
    $info['title'] = Format::htmlchars($user->getName());
?>
<div class="panel panel-primary panel-dark m-b-0">
	<div class="panel-heading">
		<div class="drag-handle panel-title">
			<?php echo $info['title']; ?>
			<a class="close_me pull-right" href="#"><i class="fa fa-remove"></i></a>
		</div>
	</div>
	<div class="panel-body perfectScrollbar" style="height: 400px;position: relative;">
		<?php
		if ($info['error']) {
			echo sprintf('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;%s</div>', $info['error']);
		} elseif ($info['msg']) {
			echo sprintf('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button><i class="fa fa-check"></i>&nbsp;&nbsp;%s</div>', $info['msg']);
		} ?>
	<form method="post" class="user" action="#users/<?php echo $user->getId(); ?>/manage" >
		<?php csrf_token(); ?>
		<ul class="nav nav-tabs" id="user-account-tabs">
			<li <?php echo !$access? 'class="active"' : ''; ?>><a href="#user-account" data-toggle="tab"
				><i class="fa fa-user"></i>&nbsp;<?php echo __('User Information'); ?></a></li>
			<li <?php echo $access? 'class="active"' : ''; ?>><a href="#user-access" data-toggle="tab"
				><i class="fa fa-lock"></i>&nbsp;<?php echo __('Manage Access'); ?></a></li>
		</ul>


	 <input type="hidden" name="id" value="<?php echo $user->getId(); ?>" />
	<div class="tab-content tab-content-bordered" id="user-account-tabs_container">
	<div class="tab-pane fade <?php echo !$access? 'active in' : ''; ?>"  id="user-account" style="margin:5px;">
		<form method="post" class="user" action="#users/<?php echo $user->getId(); ?>/manage" >
			<?php csrf_token(); ?>
			<input type="hidden" name="id" value="<?php echo $user->getId(); ?>" />
						
			<fieldset class="form-group">
				<label class="control-label">
					<?php echo __('User Information'); ?>
				</label>
				<div class="form-group row">
					<div class="col-md-3">
						<?php echo __('Name'); ?>:
					</div>
					<div class="col-md-9"> 
						<?php echo Format::htmlchars($user->getName()); ?> 
					</div>
				</div>
				<div class="form-group row">	
					<div class="col-md-3">
						<?php echo __('Email'); ?>:
					</div>
					<div class="col-md-9"> 
						<?php echo $user->getEmail(); ?> 
					</div>
				</div>
				<div class="form-group row">	
					<div class="col-md-3">
						<?php echo __('Organization'); ?>:
					</div>
					<div class="col-md-9">
						<input class="form-control" type="text" size="35" name="org" value="<?php echo $info['org']; ?>">
						<?php
							if(!empty($errors['org'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['org'].'</div>';
							}
						?>
					</div>
				</div>	
			</fieldset>
			<fieldset class="form-group">
				<label class="control-label">
					<?php echo __('User Preferences'); ?>
				</label>
				<div class="form-group row">
					<div class="col-md-3">
						<?php echo __('Time Zone');?>:
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
	 <div class="tab-pane fade <?php echo $access? 'active in' : ''; ?>"  id="user-access" style="margin:5px;">
			<fieldset class="form-group">
				<label class="control-label"> 
					<?php echo __('Account Access'); ?>
				</label>
				<div class="form-group row">
					<div class="col-md-3">
						<?php echo __('Status'); ?>:
					</div>
					<div class="col-md-9"> 
						<?php echo $user->getAccountStatus(); ?> 
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
						<?php echo __('Username'); ?>:
					</div>
					<div class="col-md-9">
						<input class="form-control" type="text" size="35" name="username" value="<?php echo $info['username']; ?>">
						<i class="fa fa-question-circle" data-title="<?php
							echo __("Login via email"); ?>"
						data-content="<?php echo sprintf('%s: %s',
							__('Users can always sign in with their email address'),
							$user->getEmail()); ?>"></i>
						<?php
							if(!empty($errors['username'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['username'].'</div>';
							}
						?>	
					</div>
				</div>
				<div class="form-group row">
					<div class="col-md-3">
						<?php echo __('New Password'); ?>:
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
						<?php
							if(!empty($errors['passwd2'])){
								echo '<div class="alert-danger">&nbsp;'.$errors['passwd2'].'</div>';
							}
						?>
					</div>
				</div>
			</fieldset>
			<fieldset>
				<label class="control-label">
					<?php echo __('Account Flags'); ?>
				</label>
					<?php
					  echo sprintf('<div class="checkbox"><label><input type="checkbox" name="locked-flag" %s
						   value="1">%s</label></div>',
						   $account->isLocked() ?  'checked="checked"' : '',
						   __('Administratively Locked')
						   );
					  ?>
				   <div class="checkbox">
						<label><input type="checkbox" name="pwreset-flag" value="1" <?php
							echo $account->isPasswdResetForced() ?
							'checked="checked"' : ''; ?>> <?php echo __('Password Reset Required'); ?>
						</label>
					</div>
				   <div class="checkbox">
						<label><input type="checkbox" name="forbid-pwchange-flag" value="1" <?php
						echo !$account->isPasswdResetEnabled() ?
						'checked="checked"' : ''; ?>> <?php echo __('User cannot change password'); ?>
						</label>
					</div>
			</fieldset>
	   </div>
	   </div>
	   <div class="form-group" style="margin-top:20px;">
		  <input type="submit" class="btn btn-success" value="<?php echo __('Save Changes'); ?>">	
		  <input type="reset" class="btn btn-info" value="<?php echo __('Reset'); ?>">
		  <input type="button" name="cancel" class="close_me btn" value="<?php echo __('Cancel'); ?>">
		</div>
	</form>
	</form>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function (){
	$('.perfectScrollbar').perfectScrollbar();
});
		  
$(function() {
    $(document).on('click', 'input#sendemail', function(e) {
        if ($(this).prop('checked'))
            $('tbody#password').hide();
        else
            $('tbody#password').show();
    });
});
</script>

<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
$info = ($_POST && $errors)?Format::htmlchars($_POST):array();

if ($thisstaff && $thisstaff->is2FAPending())
    $msg = '2FA Pending';

?>
<div class="page-signup-modal modal">
<div class="modal-dialog">
  <div class="modal-content" style="background-color: #F8F8F8;">

	<div class="text-center" style="padding-top: 40px;" align="center">
	 <div id="logo" class="">
	<a href="index.php">
		<img src="logo.php?login" alt="Deskuss - <?php echo __('Staff Control Panel');?>" />
	</a>
	</div>
	<div style="font-size: 16px;"><b><?php echo Format::htmlchars($msg); ?></b></div>
	<?php 
	if($content && $content->getLocalBody()){ ?>
	<div class="banner"><small><?php echo ($content) ? Format::display($content->getLocalBody()) : ''; ?></small></div>
	<?php
	} ?>
	</div>
	<form action="login.php" method="post" id="login" class="p-a-4">
	<?php csrf_token(); 

	if($thisstaff
		&&  $thisstaff->is2FAPending()
		&& ($bk=$thisstaff->get2FABackend())
		&& ($form=$bk->getInputForm($_POST))){
					
		// Render 2FA input form
		include INCLUDE_DIR . 'staff/templates/dynamic-form-simple.tmpl.php';
		?>
		<fieldset style="padding-top:10px;">
		<input type="hidden" name="do" value="2fa">
		<button class="btn btn-block btn-lg btn-primary m-t-3" type="submit"
			name="submit"><i class="ion-log-in"></i>
			<?php echo __('Verify'); ?>
		</button>
		</fieldset>
	<?php
	}else{ ?> 
		<input type="hidden" name="do" value="adminlogin">
		<fieldset class="page-signup-form-group form-group form-group-lg">
		<div class="page-signup-icon text-muted"><i class="ion-person"></i></div>
		<input type="text" name="userid" class="page-signup-form-control form-control" id="name" value="<?php
			echo $info['userid']; ?>" placeholder="<?php echo __('Email or Username'); ?>"
			autofocus autocorrect="off" autocapitalize="off">
			
		</fieldset>
			
		<fieldset class="page-signup-form-group form-group form-group-lg">
		 <div class="page-signup-icon text-muted"><i class="ion-asterisk"></i></div>
		<input type="password" name="passwd" class="page-signup-form-control form-control" id="pass" placeholder="<?php echo __('Password'); ?>" autocorrect="off" autocapitalize="off">
			
		</fieldset>
		<?php if ($cfg->allowPasswordReset()) { ?>
		<div style="display:inline; font-size:13px; margin-bottom: 10px;" class="pull-right"><a href="pwreset.php" style="text-decoration:none;"><?php echo __('Forgot My Password'); ?></a></div>
		<?php } ?>
		<button class="btn btn-block btn-lg btn-primary m-t-3" type="submit" name="submit"><i class="ion-log-in"></i>
			<?php echo __('Log In'); ?>
		</button>

	<?php
	} ?>
			
	</form>
 <?php
	$ext_bks = array();
	foreach (StaffAuthenticationBackend::allRegistered() as $bk)
		if ($bk instanceof ExternalAuthentication)
			$ext_bks[] = $bk;

	if (count($ext_bks)) { ?>
	<div class="or">
		<hr/>
	</div><?php
		foreach ($ext_bks as $bk) { ?>
	<div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
		}
	} ?>

		<div id="company">
			<div class="content">
				<?php echo __('Copyright'); ?> &copy; <a href="https://deskuss.com" target="_blank" style="color: #FFF; text-decoration: none;"><?php echo Format::htmlchars($dsk->company) ?: date('Y'); ?></a>
			</div>
		</div>
	</div>
</div>
</div>
</body>
</html>

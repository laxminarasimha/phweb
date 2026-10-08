<?php
include_once(STAFFINC_DIR.'login.header.php');
defined('DSKADMININC') or die('Invalid path');
$info = ($_POST)?Format::htmlchars($_POST):array();
?>

<div class="page-signup-modal modal">
	<div class="modal-dialog">
		<div class="modal-content" style="background-color: #F8F8F8;">
			<div class="text-center p-y-4">
				<div id="logo">
					<a href="index.php">
						<img src="logo.php?login" alt="Deskuss - <?php echo __('Agent Password Reset');?>" />
					</a>
				</div>
				<div style="font-size: 15px;"><b><?php echo Format::htmlchars($msg); ?></b></div>
				<form action="pwreset.php" method="post" class="p-a-4">
					<?php csrf_token(); ?>
					<input type="hidden" name="do" value="newpasswd"/>
					<input type="hidden" name="token" value="<?php echo Format::htmlchars($_REQUEST['token']); ?>"/>
					<fieldset class="page-signup-form-group form-group form-group-lg">
						<div class="page-signup-icon text-muted"><i class="ion-person"></i></div>
						<input type="text" class="page-signup-form-control form-control" name="userid" id="name" value="<?php echo $info['userid']; ?>" placeholder="<?php echo __('Email or Username'); ?>"
							autocorrect="off" autocapitalize="off"/>
					</fieldset>
					<input class="btn btn-block btn-lg btn-primary m-t-3" type="submit" name="submit" value="Login"/>
				</form>
				<div id="company">
					<div class="content">
						<?php echo __('Copyright'); ?> &copy; <a href="https://deskuss.com" target="_blank" style="color: #FFF; text-decoration: none;"><?php echo Format::htmlchars($dsk->company) ?: date('Y'); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>

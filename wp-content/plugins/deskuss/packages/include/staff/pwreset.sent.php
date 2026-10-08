<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
defined('DSKADMININC') or die('Invalid path');
$info = ($_POST && $errors)?Format::htmlchars($_POST):array();
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
				<div style="font-size: 15px;"><b><?php echo __('A confirmation email has been sent'); ?></b></div>
			</div>
			<form action="index.php" method="get" class="p-a-4">
				<div class="text-success" style="text-align:center;"><?php echo __(
					'A password reset email was sent to the email on file for your account.  Follow the link in the email to reset your password.'
					); ?>
				</div>
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

</body>
</html>

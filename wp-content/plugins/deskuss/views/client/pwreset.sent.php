<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Forgot My Password'); ?></div>
	</div>
	<div class="panel-body">
		<form action="pwreset.php" method="post" id="clientLogin">
			<div style="width:100%;display:inline-block"><?php echo __(
				'We have sent you a reset email to the email address you have on file for your account.'
			);
			echo '<br /><br />';
			echo __(
				'If you do not  receive the email or cannot reset your password, please submit a ticket to have your account unlocked.'
			); ?>
			</div>
		</form>
	</div>
</div>
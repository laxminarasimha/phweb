<?php
if(!defined('DSKCLIENTINC')) die('Access Denied');

$userid=Format::input($_POST['userid']);
?>
<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><?php echo __('Forgot My Password'); ?></div>
	</div>
	<div class="panel-body">
		<form action="pwreset.php" method="post" id="clientLogin">
			<div style="width:50%;display:inline-block">
			<?php csrf_token(); ?>
			<input type="hidden" name="do" value="reset"/>
			<input type="hidden" name="token" value="<?php echo Format::htmlchars($_REQUEST['token']); ?>"/>
			<strong><?php echo Format::htmlchars($banner); ?></strong>
			<br>
			<div  class="form-group form-group-lg">
				<label for="username" style="text-align:left;"><?php echo __('Username'); ?>:
				<input id="username" class="form-control" type="text" name="userid" size="30" value="<?php echo esc_attr($userid); ?>">
				</label>
			</div>
			<p>
				<input class="btn" type="submit" value="Login">
			</p>
			</div>
		</form>
	</div>
</div>
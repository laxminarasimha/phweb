<?php
$info = $_POST;
if (!isset($info['timezone']))
    $info += array(
        'backend' => null,
    );
if (isset($user) && $user instanceof ClientCreateRequest) {
    $bk = $user->getBackend();
    $info = array_merge($info, array(
        'backend' => $bk::$id,
        'username' => $user->getUsername(),
    ));
}
$info = Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<div class="panel col-sm-8" style="float:none;margin:auto;padding:0;">
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Account Registration'); ?> -</span>
	<span><?php echo __(
	'Use the forms below to create or update the information we have on file for your account'
	); ?>
	</span>
	</div>
</div>

<div class="panel-body">
<form action="account.php" method="post">
  <?php csrf_token(); ?>
  <input type="hidden" name="do" value="<?php echo Format::htmlchars($_REQUEST['do']
    ?: ($info['backend'] ? 'import' :'create')); ?>" />

<?php
    $cf = $user_form ?: UserForm::getInstance();
    $cf->render(false, false, array('mode' => 'create'));
?>
<div style="padding:0 20px">
	<div class="form-group form-group-lg">
		<label for="timezone" style="display:block"><?php echo __('Time Zone');?>:</label>
		<?php
			$TZ_NAME = 'timezone';
			$TZ_TIMEZONE = $info['timezone'];
			include STAFFINC_DIR.'templates/timezone.tmpl.php'; ?>
			<?php 
				if(!empty($errors['timezone'])){
					echo '<div class="alert-danger">&nbsp;'.esc_html($errors['timezone']).'</div>';
				}
			?>
	</div>
</div>
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Access Credentials'); ?></div>
</div>
<div class="panel-body">
<?php if ($info['backend']) { ?>
<div class="form-group form-group-lg">
        <label for="email" style="text-align:left;"><?php echo __('Login With'); ?>:
		  <input type="hidden" name="backend" value="<?php echo esc_attr($info['backend']); ?>"/>
			<input type="hidden" name="username" value="<?php echo esc_attr($info['username']); ?>"/>
			<?php foreach (UserAuthenticationBackend::allRegistered() as $bk) {
				if ($bk::$id == $info['backend']) {
				echo esc_html($bk->getName());
				break;
				}
			} ?>
		
		</label>
</div>

<?php } else { ?>
<div class="row">
<div class="form-group form-group-lg" style="margin:0;">
	<label style="display:block;"><?php echo __('Create a Password'); ?>:
	
	</label>
	 <input type="password" size="18" class="form-control" name="passwd1" value="<?php echo esc_attr($info['passwd1']); ?>">
	 <?php
		if(!empty($errors['passwd1'])){
			echo '<div class="alert-danger">&nbsp;'.esc_html($errors['passwd1']).'</div>';
		}
	 ?>
</div>
</div>

<div class="row">
<div class="form-group form-group-lg" style="margin:0;">
	<label style="display:block;"><?php echo __('Confirm New Password'); ?>:</label>
	 <input type="password" size="18" class="form-control" name="passwd2" value="<?php echo esc_attr($info['passwd2']); ?>">
	 <?php
		if(!empty($errors['passwd2'])){
			echo '<div class="alert-danger">&nbsp;'.esc_html($errors['passwd2']).'</div>';
		}
	 ?>			
</div>
</div>

<?php } ?>

</div>
<p style="text-align: center;">
    <input type="submit" value="Register" class="btn btn-success"/>
    <input type="button" value="Cancel" class="btn btn-default" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>
</div>
</div>
<?php if (!isset($info['timezone'])) { ?>
<!-- Auto detect client's timezone where possible -->
<script type="text/javascript" src="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/js/jstz.min.js?035fd0a"></script>
<script type="text/javascript">
$(function() {
    var zone = jstz.determine();
    $('#timezone-dropdown').val(zone.name()).trigger('change');
});
</script>
<?php }


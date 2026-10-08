<div class="panel col-sm-8" style="float:none;margin:auto;padding:0;">
<div class="panel-heading">
<div class="panel-title"> <span class="table-caption"><?php echo __('Manage Your Profile Information'); ?> -
</span><span><?php echo __(
'Use the forms below to update the information we have on file for your account'
); ?>
</span>
</div>
</div>
<div class="panel-body">
<form action="profile.php" method="post">
  <?php csrf_token(); ?>
<?php
foreach ($user->getForms() as $f) {
    $f->render(false);
}
if ($acct = $thisclient->getAccount()) {
    $info=$acct->getInfo();
    $info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Preferences'); ?></span></div>
</div>
<div class="panel-body">
	<div class="form-group">
		<label for="timezone" style="display:block"><?php echo __('Time Zone');?>:</label>
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
<?php if ($cfg->getSecondaryLanguages()) { ?>
	<div class="form-group">
		<label for="timezone" style="display:block"><?php echo __('Preferred Language'); ?> :</label>
		<?php
		$langs = Internationalization::getConfiguredSystemLanguages(); ?>
			<select name="lang" class="form-control">
			<option value="">&mdash; <?php echo __('Use Browser Preference'); ?> &mdash;</option>
		<?php foreach($langs as $l) {
		$selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
			<option value="<?php echo esc_attr($l['code']); ?>" <?php echo $selected;
			?>><?php echo esc_html(Internationalization::getLanguageDescription($l['code'])); ?></option>
		<?php } ?>
			</select>
			<?php
			if(!empty($errors['lang'])){
					echo '<div class="alert-danger">&nbsp;'.$errors['lang'].'</div>';
			}
			?>
	</div>
 
<?php } ?>
</div>
<?php
      if ($acct->isPasswdResetEnabled()) { ?>
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Access Credentials'); ?></span></div>
</div>
<div style="padding:10px 20px;">
	<?php if (!isset($_SESSION['_client']['reset-token'])) { ?>
	<div class="row">
	<div class="form-group col-sm-10">
		<label style="display:block;"><?php echo __('Current Password'); ?>:
		</label>
		 <input type="password" size="18" name="cpasswd" class="form-control" value="<?php echo esc_attr($info['cpasswd']); ?>">
		<?php
			if(!empty($errors['cpasswd'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['cpasswd'].'</div>';
			}
		?>
	</div>
	</div>
	<?php } ?>
	<div class="row">
	<div class="form-group col-sm-10">
		<label style="display:block;"><?php echo __('New Password'); ?>:
		</label>
		<input type="password" size="18" name="passwd1" class="form-control" value="<?php echo esc_attr($info['passwd1']); ?>">
		<?php
			if(!empty($errors['passwd1'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['passwd1'].'</div>';
			}
		?>
	</div>
	</div>

	<div class="row">
	<div class="form-group col-sm-10">
		<label style="display:block;"><?php echo __('Confirm New Password'); ?>:
		</label>
		<input type="password" size="18" name="passwd2" class="form-control" value="<?php echo esc_attr($info['passwd2']); ?>">
		<?php
			if(!empty($errors['passwd2'])){
				echo '<div class="alert-danger">&nbsp;'.$errors['passwd2'].'</div>';
			}
		?>
	</div>
	</div>

</div>

<?php } ?>
<?php } ?>


<p style="text-align: center;">
    <input type="submit" class="btn btn-success" value="Update"/>
    <input type="reset" class="btn btn-info" value="Reset"/>
    <input type="button"  class="btn btn-default" value="Cancel" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>
</div>
</div>

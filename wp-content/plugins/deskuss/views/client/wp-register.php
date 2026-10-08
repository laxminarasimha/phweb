<?php
$reg_error = '';

$content = Page::lookupByType('banner-client');
if ($content) {
    list($title, $body) = $dsk->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('Create an Account');
    $body = __('Register for an account to track and manage your support tickets.');
}

$title = apply_filters('register_title', $title);
$body = apply_filters('register_body', $body);

do_action('login_enqueue_scripts');
do_action('login_head');

$info = array();
if (!empty($reg_email)) $info['email'] = $reg_email;
if (!empty($reg_name)) $info['name'] = $reg_name;
$info = Format::htmlchars($info);

?>
<div class="panel col-sm-8" style="float:none;margin:auto;padding:0;">
<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Account Registration'); ?> -</span>
	<span><?php echo Format::display($body); ?>
	</span>
	</div>
</div>

<div class="panel-body">
<?php if ($reg_error) { ?>
<div class="alert alert-danger text-center"><?php echo $reg_error; ?></div>
<?php } ?>

<form action="<?php echo esc_url(DESKUSS_ROOT_PATH . 'account.php?do=create'); ?>" method="post" id="wpClientRegister">
    <?php csrf_token(); ?>
    <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />

    <div style="padding:0 20px">
	<div class="form-group form-group-lg">
		<label for="reg_email" style="display:block"><?php echo __('Email Address'); ?>:</label>
		<input id="reg_email" type="email" name="user_email" size="30" value="<?php echo isset($info['email']) ? $info['email'] : ''; ?>" class="form-control" required />
		<?php
		if (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('empty_email')) {
			echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('empty_email') . '</div>';
		} elseif (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('invalid_email')) {
			echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('invalid_email') . '</div>';
		} elseif (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('email_exists')) {
			echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('email_exists') . '</div>';
		}
		?>
	</div>

	<div class="form-group form-group-lg">
		<label for="reg_name" style="display:block"><?php echo __('Full Name'); ?>:</label>
		<input id="reg_name" type="text" name="user_name" size="30" value="<?php echo isset($info['name']) ? $info['name'] : ''; ?>" class="form-control" required />
		<?php
		if (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('empty_name')) {
			echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('empty_name') . '</div>';
		}
		?>
	</div>
    </div>

<div class="panel-heading">
	<div class="panel-title"><span class="table-caption"><?php echo __('Access Credentials'); ?></span></div>
</div>
<div class="panel-body" style="padding:0 20px">
<div class="row">
<div class="form-group form-group-lg" style="margin:0;">
	<label style="display:block;"><?php echo __('Create a Password'); ?>:</label>
	 <input type="password" size="18" class="form-control" name="passwd1" value="">
	 <?php
	 if (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('empty_password')) {
	     echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('empty_password') . '</div>';
	 } elseif (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('short_password')) {
	     echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('short_password') . '</div>';
	 }
	 ?>
</div>
</div>

<div class="row">
<div class="form-group form-group-lg" style="margin:0;">
	<label style="display:block;"><?php echo __('Confirm New Password'); ?>:</label>
	 <input type="password" size="18" class="form-control" name="passwd2" value="">
	 <?php
	 if (!empty($errors) && is_wp_error($errors) && $errors->get_error_message('password_mismatch')) {
	     echo '<div class="alert-danger">&nbsp;' . $errors->get_error_message('password_mismatch') . '</div>';
	 }
	 ?>
</div>
</div>

<?php do_action('register_form'); ?>

</div>

<p style="text-align: center;">
    <input type="submit" value="<?php echo __('Register'); ?>" class="btn btn-success"/>
    <input type="button" value="<?php echo __('Cancel'); ?>" class="btn btn-default" onclick="javascript:
        window.location.href='index.php';"/>
</p>
<p style="text-align:center; margin-top:10px;">
    <?php echo __('Already have an account?'); ?> <a href="<?php echo DESKUSS_ROOT_PATH; ?>login.php"><?php echo __('Sign In'); ?></a>
</p>
</form>
</div>
</div>
<?php do_action('login_footer'); ?>
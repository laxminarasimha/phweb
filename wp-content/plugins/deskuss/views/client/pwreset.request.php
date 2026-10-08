<?php
if(!defined('DSKCLIENTINC')) die('Access Denied');

$userid=Format::input($_POST['userid']);
?>
<div class="panel">
<div class="panel-heading">
<div class="panel-title table-caption"><?php echo __('Forgot My Password'); ?></div>
</div>

<div class="panel-body">
<p>

<?php 

$pwreset_exp = 'Enter your username or email address in the form below and press the <strong>Send Email</strong> button to have a password reset link sent to your email account on file.';

$pwreset_exp = apply_filters('forgot_password_request_exp', $pwreset_exp);

echo __($pwreset_exp);

?>

<form action="pwreset.php" method="post" id="clientLogin">
    <div style="width:50%;display:inline-block">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="sendmail"/>
    <br/>
    <div class="forn-group">
        <label for="username"><?php echo __('Username / Email'); ?>:
        <input id="username" type="text" class="form-control" name="userid" size="30" value="<?php echo esc_attr($userid); ?>">
		</label>
    </div>
	<br />
    <p>
        <input class="btn btn-success" type="submit" value="<?php echo __('Send Email'); ?>">
    </p>
    </div>
</form>
</div>
</div>

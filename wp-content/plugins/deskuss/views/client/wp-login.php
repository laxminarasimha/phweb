<?php
$login_error = '';

$content = Page::lookupByType('banner-client');
if ($content) {
    list($title, $body) = $dsk->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('Sign In');
    $body = __('To better serve you, we encourage our clients to register for an account and verify the email address we have on file.');
}

$title = apply_filters('login_title', $title);
$body = apply_filters('login_body', $body);

do_action('login_enqueue_scripts');
do_action('login_head');

?>
<div class="panel col-sm-8" style="padding:0;margin:auto;float:none">

<div class="panel-heading">
	<div class="panel-title"><?php echo Format::display($title); ?></div>
</div>

<div class="panel-body">
<p class="text-center"><?php echo Format::display($body); ?></p>

<?php if ($login_error) { ?>
<div class="alert alert-danger text-center"><?php echo $login_error; ?></div>
<?php } ?>

<form action="<?php echo esc_url(DESKUSS_ROOT_PATH . 'login.php'); ?>" method="post" id="wpClientLogin">
    <?php csrf_token(); ?>
    <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />

    <div class="text-center">
    <div class="form-group form-group-lg">
        <label for="user_login" style="text-align:left;"><?php echo __('Email Address'); ?>:
        <input id="user_login" placeholder="<?php echo __('Email Address'); ?>" type="email" name="log" size="30" value="<?php echo esc_attr($login_email); ?>" class="form-control" required />
        </label>
    </div>
    <div class="form-group form-group-lg">
        <label for="user_pass" style="text-align:left;"><?php echo __('Password'); ?>:
        <input id="user_pass" placeholder="<?php echo __('Password'); ?>" type="password" name="pwd" size="30" class="form-control" required />
        </label>
    </div>
    <div class="form-group" style="text-align:left;">
        <label><input type="checkbox" name="rememberme" value="1" /> <?php echo __('Remember Me'); ?></label>
    </div>
    <?php do_action('login_form'); ?>
    <p>
        <input class="btn btn-success" type="submit" value="<?php echo __('Sign In'); ?>">
<?php if ($suggest_pwreset) { ?>
        &nbsp;&nbsp;<a style="padding-top:4px;display:inline-block;" href="pwreset.php"><?php echo __('Forgot My Password'); ?></a>
<?php } ?>
    </p>
    </div>

<?php
$ext_bks = array();
foreach (UserAuthenticationBackend::allRegistered() as $bk)
    if ($bk instanceof ExternalAuthentication)
        $ext_bks[] = $bk;

if (count($ext_bks)) { ?>
    <div style="margin-top:10px;">
    <?php foreach ($ext_bks as $bk) { ?>
<div class="external-auth"><?php $bk->renderExternalLink(); ?></div>
<?php } ?>
    </div>
<?php } ?>

<?php if ($cfg && $cfg->isClientRegistrationEnabled()) { ?>
    <div style="margin-bottom: 5px; margin-top: 15px;">
    <?php echo __('Not yet registered?'); ?> <a href="account.php?do=create"><?php echo __('Create an account'); ?></a>
    </div>
<?php } ?>
    <div>
    <b><?php echo __("I'm an agent"); ?></b> &mdash;
    <a href="<?php echo DESKUSS_ROOT_PATH; ?>admin/"><?php echo __('sign in here'); ?></a>
    </div>
</form>
<br>
<p>
<?php
if ($cfg->getClientRegistrationMode() != 'disabled'
    || !$cfg->isClientLoginRequired()) {
    echo sprintf(__('If this is your first time contacting us or you\'ve lost the ticket number, please %s open a new ticket %s'),
        '<a href="open.php">', '</a>');
} ?>
</p>
</div>
</div>
<?php do_action('login_footer'); ?>
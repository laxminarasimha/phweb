<?php
if(!defined('DSKCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['luser']?:$_GET['e']);
$passwd=Format::input($_POST['lpasswd']?:$_GET['t']);

$content = Page::lookupByType('banner-client');

if ($content) {
    list($title, $body) = $dsk->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('Sign In');
    $body = __('To better serve you, we encourage our clients to register for an account and verify the email address we have on record.');
}

$title = apply_filters('login_title', $title);
$body = apply_filters('login_body', $body);

?>
<div class="panel col-sm-8" style="padding:0;margin:auto;float:none">

<div class="panel-heading">
	<div class="panel-title"><?php echo Format::display($title); ?></div>
</div>

<div class="panel-body">
<p class="text-center"><?php echo Format::display($body); ?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>

    <div class="text-center">
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <div class="form-group form-group-lg">
	 <label for="username" style="text-align:left;"><?php echo __('Email or Username'); ?>:
        <input id="username" placeholder="<?php echo __('Email or Username'); ?>" type="text" name="luser" size="30" value="<?php echo esc_attr($email); ?>" class="form-control">
	</label>
    </div>
    <div  class="form-group form-group-lg">
	 <label for="passwd" style="text-align:left;"><?php echo __('Password'); ?>:
        <input id="passwd" placeholder="<?php echo __('Password'); ?>" type="password" name="lpasswd" size="30" value="<?php echo esc_attr($passwd); ?>" class="form-control">
	</label>	
    </div>
    <p>
        <input class="btn btn-success" type="submit" value="<?php echo __('Sign In'); ?>">
<?php if ($suggest_pwreset) { ?>
        &nbsp;&nbsp;<a style="padding-top:4px;display:inline-block;" href="pwreset.php"><?php echo __('Forgot My Password'); ?></a>
<?php } ?>
    </p>
    </div>
    <div style="display:table-cell;padding: 15px 0px;vertical-align:top">
<?php

$ext_bks = array();
foreach (UserAuthenticationBackend::allRegistered() as $bk)
    if ($bk instanceof ExternalAuthentication)
        $ext_bks[] = $bk;

if (count($ext_bks)) {
    foreach ($ext_bks as $bk) { ?>
<div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
    }
}
if ($cfg && $cfg->isClientRegistrationEnabled()) {
    if (count($ext_bks)) echo '<hr style="width:70%"/>'; ?>
    <div style="margin-bottom: 5px">
    <?php echo __('Not yet registered?'); ?> <a href="account.php?do=create"><?php echo __('Create an account'); ?></a>
    </div>
<?php } ?>
    <div>
    <b><?php echo __("I'm an agent"); ?></b> —
    <a href="<?php echo esc_url(DESKUSS_ROOT_PATH); ?>admin/"><?php echo __('sign in here'); ?></a>
    </div>
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

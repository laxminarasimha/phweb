<?php
if(!defined('DSKCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$ticketid=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);

if ($cfg->isClientEmailVerificationRequired())
    $button = __("Email Access Link");
else
    $button = __("View Ticket");
?>
<div class="panel col-sm-6" style="padding:0;margin:auto;float:none">
<div class="panel-heading">
	<div class="panel-title table-caption"><?php echo __('Check Ticket Status'); ?></div>
</div>
<div class="panel-body">
<p class="text-center"><?php
echo __('Please provide your email address and a ticket number.');
echo '</p>
<p class="text-center">';
if ($cfg->isClientEmailVerificationRequired())
    echo ' '.__('An access link will be emailed to you.');
else
    echo ' '.__('This will sign you in to view your ticket.');
?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>

    <div class="text-center">
    <div><strong><?php echo Format::htmlchars($errors['login']); ?></strong></div>
    <div class="form-group form-group-lg">
        <label for="email" style="text-align:left;"><?php echo __('Email Address'); ?>:
        <input id="email"  placeholder="<?php echo __('e.g. john.doe@deskuss.com'); ?>" type="text"
            name="lemail" size="30" value="<?php echo esc_attr($email); ?>" class="form-control"></label>
    </div>
    <div class="form-group form-group-lg">
        <label for="ticketno" style="text-align:left;"><?php echo __('Ticket Number'); ?>:
        <input id="ticketno" type="text" name="lticket" placeholder="<?php echo __('e.g. 051243'); ?>"
            size="30" value="<?php echo esc_attr($ticketid); ?>" class="form-control"></label>
    </div>
    <p>
        <input class="btn btn-success" type="submit" value="<?php echo esc_attr($button); ?>">
    </p>
    </div>
    <div class="instructions">
<?php if ($cfg && $cfg->getClientRegistrationMode() !== 'disabled') { ?>
        <?php echo __('Have an account with us?'); ?>
        <a href="login.php"><?php echo __('Sign In'); ?></a> <?php
    if ($cfg->isClientRegistrationEnabled()) { ?>
<?php echo sprintf(__('or %s register for an account %s to access all your tickets.'),
    '<a href="account.php?do=create">','</a>');
    }
}?>
    </div>
</form>
<br>
<p>
<?php
if ($cfg->getClientRegistrationMode() != 'disabled'
    || !$cfg->isClientLoginRequired()) {
    echo sprintf(
    __("If this is your first time contacting us or you've lost the ticket number, please %s open a new ticket %s"),
        '<a href="open.php">','</a>');
} ?>
</p>
</div>
</div>

<?php


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//////////////////////////////////////////////////////////////
//===========================================================
// SOFTACULOUS PROJECT
//===========================================================
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Pulkit and Brijesh
// ----------------------------------------------------------
// Please Read the Terms of use at http://deskuss.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

require('admin.inc.php');

$errors = array();
$tip_namespace = 'settings.email';
$inc = 'settings-emails.php';

if ($_POST && !$errors) {
    if($cfg && $cfg->updateSettings($_POST,$errors)) {
        $msg=sprintf(__('Successfully updated %s.'), Format::htmlchars($page[0]));
    } elseif(!$errors['err']) {
        $errors['err'] = sprintf('%s %s',
            __('Unable to update settings.'),
            __('Correct any errors below and try again.'));
    }
}

$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfigInfo());
$dsk->addExtraHeader('<meta name="tip-namespace" content="'.$tip_namespace.'" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");

$nav->setTabActive('emails', 'emailsettings.php');
require deskuss_load_page($inc);

?>

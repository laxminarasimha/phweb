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
$errors=array();
$settingOptions=array(
    'system' =>
        array(__('System Settings'), 'settings.system'),
    'tickets' =>
        array(__('Ticket Settings and Options'), 'settings.ticket'),
    'agents' =>
        array(__('Agent Settings and Options'), 'settings.agents'),
    'users' =>
        array(__('User Settings and Options'), 'settings.users'),
    'pages' =>
        array(__('Company Settings'), 'settings.pages'),
    'kb' =>
        array(__('Knowledgebase Settings'), 'settings.kb'),
);
//Handle a POST.
$target=(!empty($_REQUEST['t']) && isset($settingOptions[$_REQUEST['t']]))?$_REQUEST['t']:'system';
$page = false;
if (isset($settingOptions[$target]))
    $page = $settingOptions[$target];

if($page && $_POST && !$errors) {
    if($cfg && $cfg->updateSettings($_POST,$errors)) {
        $msg=sprintf(__('Successfully updated %s.'), Format::htmlchars($page[0]));
    } elseif(!$errors['err']) {
        $errors['err'] = sprintf('%s %s',
            __('Unable to update settings.'),
            __('Correct any errors below and try again.'));
    }
}

$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfigInfo());
$dsk->addExtraHeader('<meta name="tip-namespace" content="'.$page[1].'" />',
    "$('#content').data('tipNamespace', '".$page[1]."');");

$nav->setTabActive('settings', ('settings.php?t='.$target));
require deskuss_load_page("settings-$target.php");
?>

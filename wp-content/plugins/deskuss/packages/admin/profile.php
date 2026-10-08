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

require_once('staff.inc.php');
require_once(INCLUDE_DIR.'class.export.php'); // For paper sizes

$msg='';
$staff=Staff::lookup($thisstaff->getId());
if($_POST && $_POST['id']!=$thisstaff->getId()) { //Check dummy ID used on the form.
 $errors['err']=__('Action Denied.')
        .' '.__('Internal error occurred');
} elseif(!$errors && $_POST) { //Handle post

    if(!$staff)
        $errors['err']=sprintf(__('%s: Unknown or invalid'), __('agent'));
    elseif($staff->updateProfile($_POST,$errors)){
        $msg=__('Profile updated successfully');
    }elseif(!$errors['err'])
        $errors['err'] = sprintf('%s %s',
            __('Profile update error.'),
            __('Correct any errors below and try again.'));
}

//Forced password Change.
if($thisstaff->forcePasswdChange() && !$errors['err'])
    $errors['err'] = str_replace(
        '<a>',
        sprintf('<a data-dialog="ajax.php/staff/%d/change-password" href="#">', $thisstaff->getId()),
        sprintf(
            __('<b>Hi %s</b> - You must <a>change your password to continue</a>!'),
            $thisstaff->getFirstName()
        )
    );
elseif($thisstaff->onVacation() && !$warn)
    $warn=sprintf(__("<b>Welcome back %s</b>! You are listed as 'on vacation' Please let your manager know that you are back."),$thisstaff->getFirstName());

$inc='profile.php';
$nav->setTabActive('dashboard');
$dsk->addExtraHeader('<meta name="tip-namespace" content="dashboard.my_profile" />',
    "$('#content').data('tipNamespace', 'dashboard.my_profile');");
require deskuss_load_page($inc);
?>

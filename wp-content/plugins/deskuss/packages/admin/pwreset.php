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

require_once(dirname(dirname(__FILE__)).'/main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');

// Bootstrap gettext translations. Since no one is yet logged in, use the
// system or browser default
TextDomain::configureForUser();

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

$tpl = 'pwreset.php';
if($_POST) {
    if (!$dsk->checkCSRFToken()) {
        Http::response(400, __('Valid CSRF Token Required'));
        exit;
    }
    switch ($_POST['do']) {
        case 'sendmail':
            if (($staff=Staff::lookup($_POST['userid']))) {
                if (!$staff->hasPassword()) {
                    $msg = __('Unable to reset password. Contact your administrator');
                }
                elseif (!$staff->sendResetEmail()) {
                    $tpl = 'pwreset.sent.php';
                }
            }
            else
                $msg = sprintf(__('Unable to verify username %s'),
                    Format::htmlchars($_POST['userid']));
            break;
        case 'newpasswd':
            // TODO: Compare passwords
            $tpl = 'pwreset.login.php';
            $errors = array();
            if ($staff = StaffAuthenticationBackend::processSignOn($errors)) {
                $info = array('page' => 'index.php');
                Http::redirect($info['page']);
            }
            elseif (isset($errors['msg'])) {
                $msg = $errors['msg'];
            }
            break;
    }
}
elseif ($_GET['token']) {
    $msg = __('Please enter your username or email');
    $_config = new Config('pwreset');
    if (($id = $_config->get($_GET['token']))
            && ($staff = Staff::lookup($id)))
        // TODO: Detect staff confirmation (for welcome email)
        $tpl = 'pwreset.login.php';
    else
        header('Location: index.php');
}
elseif ($cfg->allowPasswordReset()) {
    $msg = __('Enter your username or email address below');
}
else {
    $_SESSION['_staff']['auth']['msg']=__('Password resets are disabled');
    return header('Location: index.php');
}
define("DSKADMININC",TRUE); //Make includes happy!
include_once(INCLUDE_DIR.'staff/'. $tpl);

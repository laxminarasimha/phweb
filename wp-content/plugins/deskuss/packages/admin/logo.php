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


// Don't update the session for inline image fetches
if (!function_exists('noop')) { function noop() {} }
session_set_save_handler('noop','noop','noop','noop','noop','noop');
if (!defined('DISABLE_SESSION')) { define('DISABLE_SESSION', true); }

require_once(dirname(dirname(__FILE__)).'/main.inc.php');

if (isset($_GET['backdrop'])) {
    if (($backdrop = $dsk->getConfig()->getStaffLoginBackdrop())) {
        $backdrop->display();
        // ::display() will not return
    }
    header("Cache-Control: private, max-age=86400");
    header('Pragma: private');
    Http::redirect(esc_url(DESKUSS_MEDIA_URL.'/admin/images/login-headquarters.png'));
}
elseif (($logo = $dsk->getConfig()->getStaffLogo())) {
    $logo->display();
}

header("Cache-Control: private, max-age=86400");
header('Pragma: private');
Http::redirect(esc_url(DESKUSS_MEDIA_URL.'/admin/images/deskuss-logo.png'));

?>

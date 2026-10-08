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

require('client.inc.php');
require_once(INCLUDE_DIR.'class.file.php');

//Basic checks
if (!$_GET['key']
    || !$_GET['signature']
    || !$_GET['expires']
    || !($file = AttachmentFile::lookupByHash($_GET['key']))
) {
    Http::response(404, __('Unknown or invalid file'));
}

// Enforce security settings
if ($cfg->isAuthRequiredForFiles() && !$thisclient) {
    $staff = deskuss_get_current_staff();
    if (!$staff) {
        // Try and determine if a staff is viewing this page
        if (strpos($_SERVER['HTTP_REFERRER'], DESKUSS_ROOT_PATH .  'admin/') !== false) {
            deskuss_staff_login_redirect();
        }
        else {
            require 'secure.inc.php';
        }
    }
}

// Validate session access hash - we want to make sure the link is FRESH!
// and the user has access to the parent ticket!!
if ($file->verifySignature($_GET['signature'], $_GET['expires'])) {
    try {
        if (($s = @$_GET['s']) && strpos($file->getType(), 'image/') === 0)
            return $file->display($s);

        // Download the file..
        $file->download(@$_GET['disposition'] ?: false, $_GET['expires']);
    }
    catch (Exception $ex) {
        Http::response(500, 'Unable to find that file: '.$ex->getMessage());
    }
}
// else
Http::response(404, __('Unknown or invalid file'));

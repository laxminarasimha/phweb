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

if (!isset($_GET['uid']) || !isset($_GET['mode']))
    Http::response(400, '`uid` and `mode` parameters are required');

require_once INCLUDE_DIR . 'class.avatar.php';

try {
    $ra = new RandomAvatar($_GET['mode']);
    $avatar = $ra->makeAvatar($_GET['uid']);

    Http::response(200, false, 'image/png', false);
    Http::cacheable($_GET['uid'], false, 86400);
    imagepng($avatar, null, 1);
    imagedestroy($avatar);
    exit;
}
catch (InvalidArgumentException $ex) {
    Http::response(422, 'No such avatar image set');
}

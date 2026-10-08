<?php

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

if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Kwaheri!');
if(!file_exists(dirname(__FILE__).'/client.inc.php')) die('Fatal Error.');
require_once(dirname(__FILE__).'/client.inc.php');

//Client Login page: Ajax interface can pre-declare the function to trap logins.
if(!function_exists('clientLoginPage')) {
    function clientLoginPage($msg ='') {
        global $dsk, $cfg, $nav;
        $_SESSION['_client']['auth']['dest'] =
            '/' . ltrim($_SERVER['REQUEST_URI'], '/');
        require(dirname(__FILE__).'/login.php');
        exit;
    }
}

//User must be logged in!
if(!$thisclient || !$thisclient->getId()){
    deskuss_client_login_redirect(dsk_getCurrentUrl());
    exit;
}
?>

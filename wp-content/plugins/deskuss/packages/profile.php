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

require 'secure.inc.php';

require_once 'class.user.php';

// Check if User is Guest. If so, redirect them back to ticket page to
// prevent Account Takeover.
if ($thisclient->isGuest())
    Http::redirect('tickets.php');

$user = User::lookup($thisclient->getId());

if($user && $_POST){
	$errors = array();
	if ($acct = $thisclient->getAccount()) {
	   $acct->update($_POST, $errors);
	}

	if(!$errors && $user->updateInfo($_POST, $errors)){
		
		$msg = __('Successfully updated profile information');
		
		// If password is changed we need to destroy session
		if(!empty($_POST['passwd2'])){
			Http::redirect('logout.php');
		}
	}
}

$inc = 'profile.php';

require deskuss_load_page($inc);


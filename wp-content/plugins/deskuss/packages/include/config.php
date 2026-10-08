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

if(strtoupper(substr(PHP_OS, 0, 3) != 'WIN')){
	
	// Get the userid
	$puid = posix_getuid();

	// We do not need to run as root
	if(empty($puid)){
		die('Hacking Attempt');
	}

	$user_data = posix_getpwuid($puid);

	// Include the appropriate conf file based on the user
	include_once($user_data['dir'].'/config.php');
	
}else{
	include_once(dirname(dirname(__FILE__)).'/config.local.php');
}
#!/usr/bin/php -q
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

ini_set('memory_limit', '256M'); //The concern here is having enough mem for emails with attachments.
@chdir(dirname(__FILE__).'/'); //Change dir.
require('api.inc.php');

//Only local piping supported via pipe.php
if (!Deskuss::is_cli())
    die(__('pipe.php only supports local piping - use http -> api/tickets.email'));

require_once(INCLUDE_DIR.'api.tickets.php');
PipeApiController::process();
?>

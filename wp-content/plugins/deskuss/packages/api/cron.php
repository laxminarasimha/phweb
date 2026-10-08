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

set_time_limit(3600);
@chdir(dirname(__FILE__).'/'); //Change dir.
require('api.inc.php');

if (!Deskuss::is_cli())
    die(__('cron.php only supports local cron calls - use http -> api/tasks/cron'));

require_once(INCLUDE_DIR.'api.cron.php');
LocalCronApiController::call();
?>

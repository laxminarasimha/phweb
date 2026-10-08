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

if (!defined('AJAX_REQUEST')) { define('AJAX_REQUEST', 1); }
require('staff.inc.php');
ignore_user_abort(1);//Leave me a lone bro!
@set_time_limit(0); //useless when safe_mode is on
$data = sprintf(
    "%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c",
    71, 73, 70, 56, 57, 97, 1, 0, 1, 0, 128, 255, 0, 192, 192, 192, 0, 0, 0, 33,
    249, 4, 1, 0, 0, 0, 0, 44, 0, 0, 0, 0, 1, 0, 1, 0, 0, 2, 2, 68, 1, 0, 59
);
header('Content-type:  image/gif');
header('Cache-Control: no-cache, must-revalidate');
header('Content-Length: '.strlen($data));
header('Connection: Close');
print $data;
// Flush the request buffer
while(@ob_end_flush());
flush();
//Terminate the request
if (function_exists('fastcgi_finish_request'))
    fastcgi_finish_request();

ob_start(); //Keep the image output clean. Hide our dirt.
//TODO: Make cron DB based to allow for better time limits. Direct calls for now sucks big time.
//We DON'T want to spawn cron on every page load...we record the lastcroncall on the session per user
$sec=time()-$_SESSION['lastcroncall'];
$caller = $thisstaff->getUserName();

// Agent can call cron once every 3 minutes.
if ($sec < 180 || !$dsk || $dsk->isUpgradePending())
    return ob_end_clean();

require_once(INCLUDE_DIR.'class.cron.php');

// Clear staff obj to avoid false credit internal notes & auto-assignment
$thisstaff = null;

// Release the session to prevent locking a future request while this is
// running
$_SESSION['lastcroncall'] = time();
session_write_close();

// Age tickets: We're going to age tickets regardless of cron settings.
Cron::TicketMonitor();

// Run file purging about every 20 cron runs (1h40 on a five minute cron)
if (mt_rand(1, 20) == 4)
    Cron::CleanOrphanedFiles();

if($cfg && $cfg->isAutoCronEnabled()) { //ONLY fetch tickets if autocron is enabled!
    Cron::MailFetcher();  //Fetch mail.
    $dsk->logDebug(_S('Auto Cron'), sprintf(_S('Mail fetcher cron call [%s]'), $caller));
}

$data = array('autocron'=>true);
Signal::send('cron', null, $data);

ob_end_clean();
?>

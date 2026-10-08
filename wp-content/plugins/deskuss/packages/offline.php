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

require_once('client.inc.php');
if(is_object($dsk) && $dsk->isSystemOnline()) {
    @header('Location: index.php'); //Redirect if the system is online.
    include('index.php');
    exit;
}
$nav=null;
require deskuss_get_header();
?>
<div id="landing_page">
<?php
if(($page=$cfg->getOfflinePage())) {
    echo $page->getBody();
} else {
    echo '<h1>'.__('Support Ticket System Offline').'</h1>';
}
?>
</div>
<?php require deskuss_get_footer(); ?>

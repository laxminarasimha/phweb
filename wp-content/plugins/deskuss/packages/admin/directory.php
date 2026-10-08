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

require('staff.inc.php');
$page='directory.php';
$nav->setTabActive('agents');
$dsk->addExtraHeader('<meta name="tip-namespace" content="dashboard.staff_directory" />',
    "$('#content').data('tipNamespace', 'dashboard.staff_directory');");
require deskuss_load_page($page);
?>

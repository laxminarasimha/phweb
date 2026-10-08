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

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Nothing for now...simply redirect to tickets page.

$act  = isset( $_GET['act'] ) ? sanitize_file_name( wp_unslash( $_GET['act'] ) ) : '';
$pact = isset( $_GET['pact'] ) ? sanitize_file_name( wp_unslash( $_GET['pact'] ) ) : '';


// Is it a default act?
if ( ! empty( $act ) && file_exists( $act . '.php' ) ) {
	require $act . '.php';
} else {
	require 'tickets.php';
}
?>

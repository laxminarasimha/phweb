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

$data = '';

// List of allowed files.
$files = array(
	'deskuss.js',
	'popover.js',
	'tooltip.js',
	'modal.js',
	'file.js',
	'navbar.js',
	'footer.js',
	'perfect-scrollbar.js',
	'datepicker.js',
	'summernote.js',
	'summernote-extensions.js',
	'dropzone.js',
	'growl.js',
	'bootstrap.min.js',
	'admin.js',
	'enduser.js',
	'select2.min.js',
	'jquery.translatable.js',
	'bootstrap-typeahead.js',
	'filedrop.field.js',
	'nivo-lightbox.min.js',
);

// What files to give.
$give = isset( $_REQUEST['give'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['give'] ) ) : '';
$final = array();

if ( ! empty( $give ) ) {

	$give = explode( ',', $give );

	// Check all files are in the supported list.
	foreach ( $give as $file ) {
		$file = basename( $file );
		if ( in_array( $file, $files, true ) ) {
			$final[ md5( $file ) ] = $file;
		}
	}
}

// Give all.
if ( empty( $final ) ) {
	$final = $files;
}

// Read the files from assets directory.
$basedir = dirname( __FILE__ );
$assets_js_dir = str_replace( 'packages/themes/default/js', 'assets/js', $basedir );
foreach ( $final as $k => $v ) {
	$file_path = $assets_js_dir . '/' . basename( $v );
	if ( file_exists( $file_path ) ) {
		$data .= file_get_contents( $file_path ) . "\n\n"; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}

// We are zipping if possible.
if ( function_exists( 'ob_gzhandler' ) ) {
	ob_start( 'ob_gzhandler' );
}

// Type javascript.
header( 'Content-type: text/javascript; charset: UTF-8' );

echo $data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( function_exists( 'ob_gzhandler' ) ) {
	ob_end_flush();
}
?>

<?php
/**
 * Deskuss Page Wrapper
 *
 * Include this file after setting $deskuss_view_file.
 * It handles hooks, header, view, and footer in the caller's scope.
 *
 * Usage:
 *   $deskuss_view_file = 'dashboard.php';
 *   require DESKUSS_DIR . '/main/page-wrapper.php';
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $GLOBALS['deskuss_view_file'] ) && ! isset( $deskuss_view_file ) ) {
	wp_die( esc_html__( '$deskuss_view_file must be set before including page-wrapper.php', 'deskuss' ) );
}

// Pull view file from global if not in local scope.
if ( ! isset( $deskuss_view_file ) ) {
	$deskuss_view_file = $GLOBALS['deskuss_view_file'];
}

// Allow passing optional data to the view via $deskuss_view_data.
$__view_file = $deskuss_view_file;
$__view_data = isset( $deskuss_view_data ) ? $deskuss_view_data : array();

/**
 * Fires before the header template is loaded.
 *
 * @param string $__view_file The view file being rendered.
 * @param array  $__view_data Data passed to the view.
 */
do_action( 'deskuss_before_header', $__view_file, $__view_data );

require deskuss_get_header();

/**
 * Fires after the header, before the main view content.
 *
 * @param string $__view_file The view file being rendered.
 * @param array  $__view_data Data passed to the view.
 */
do_action( 'deskuss_before_view', $__view_file, $__view_data );

// Extract any explicitly passed data for the view.
if ( ! empty( $__view_data ) ) {
	extract( $__view_data, EXTR_SKIP );
}

require deskuss_load_view( $__view_file );

/**
 * Fires after the main view content, before the footer.
 *
 * @param string $__view_file The view file being rendered.
 * @param array  $__view_data Data passed to the view.
 */
do_action( 'deskuss_after_view', $__view_file, $__view_data );

require deskuss_get_footer();

/**
 * Fires after the footer template is loaded.
 *
 * @param string $__view_file The view file being rendered.
 * @param array  $__view_data Data passed to the view.
 */
do_action( 'deskuss_after_footer', $__view_file, $__view_data );

// Clean up to avoid polluting the caller's scope.
unset( $__view_file, $__view_data );

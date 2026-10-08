<?php
/**
Plugin Name: Deskuss
Plugin URI: https://deskuss.com
Description: Deskuss is a revolutionary plugin that transforms your customer support experience.
Version: 1.0.7
Author: Softaculous Team
Author URI: https://softaculous.com
Text Domain: deskuss
Domain Path: /languages
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.5
Requires PHP: 7.4
 */

// We need the ABSPATH
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'add_action' ) ) {
	echo 'You are not allowed to access this page directly.';
	exit;
}

// If DESKUSS_VERSION exists then the plugin is loaded already !
if ( defined( 'DESKUSS_VERSION' ) ) {
	return;
}

define( 'DESKUSS_FILE', __FILE__ );

include_once dirname( __FILE__ ) . '/init.php';

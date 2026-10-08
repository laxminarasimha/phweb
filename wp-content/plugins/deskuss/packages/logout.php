<?php
/**
 * Client Logout Handler
 * 
 * Logs out from WordPress and redirects to home.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require('client.inc.php');

// WordPress handles logout
wp_logout();
wp_redirect(home_url('/deskuss/'));
exit;

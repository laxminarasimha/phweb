<?php
/**
 * Staff Login Handler
 * 
 * Redirects to WordPress login for authentication.
 * WordPress auth bridge handles Deskuss staff creation/sync.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once(dirname(dirname(__FILE__)).'/main.inc.php');
require_once DESKUSS_DIR . '/main/auth-bridge.php';

// If already have staff, redirect to dashboard
if ($thisstaff && $thisstaff->getId()) {
    Http::redirect('index.php');
    exit;
}

// Not logged in - redirect to WordPress login with redirect back
deskuss_staff_login_redirect();
exit;

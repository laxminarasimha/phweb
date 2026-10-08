<?php
/**
 * Staff Logout Handler
 * 
 * Logs out from WordPress and redirects to home.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require('staff.inc.php');

if ($thisstaff) {
    //Clear any ticket locks the staff has.
    Lock::removeStaffLocks($thisstaff->getId());
}

wp_logout();
wp_redirect(home_url('/deskuss/'));
exit;

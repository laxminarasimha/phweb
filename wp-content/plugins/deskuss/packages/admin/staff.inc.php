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

/*************************************************************************
    staff.inc.php

    File included on every staff page...handles logins (security) and file path issues.

    Pulkit Gupta <support@deskuss.com>
    Copyright (c)  2018-2019 Deskuss
    http://www.deskuss.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
if(basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die('Access denied'); //Say hi to our friend..

if(!file_exists(dirname(dirname(__FILE__)).'/main.inc.php')) die('Fatal error... Get technical help!');

require_once(dirname(dirname(__FILE__)).'/main.inc.php');

if(!defined('INCLUDE_DIR')) die('Fatal error... invalid setting.');

/*Some more include defines specific to staff only */
if (!defined('STAFFINC_DIR')) {
	define('STAFFINC_DIR',INCLUDE_DIR.'staff/');
}
define('ADMIN_DIR',str_replace('//','/',dirname(__FILE__).'/'));

/* Define tag that included files can check */
if (!defined('DSKADMININC')) { define('DSKADMININC',TRUE); }
define('DSKSTAFFINC',TRUE);

/* Tables used by staff only */
define('KB_PREMADE_TABLE',DESKUSS_TABLE_PREFIX.'kb_premade');

/* include what is needed on staff control panel */

require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

/* First order of the day is see if the user is logged in and with a valid session.
    * User must be valid staff beyond this point
    * ONLY super admins can access the helpdesk on offline state.
*/

// Prevent to directly access the plugin url
$request_uri = $_SERVER['REQUEST_URI'];

// Pattern to detect plugin public_html path in URL
$plugin_uri = DESKUSS_URL.'/packages/';
$plugin_uri = str_replace(home_url(), '', $plugin_uri);

// If the current URL contains the plugin path
if (strpos($request_uri, $plugin_uri) !== false) {

	// Extract whatever comes after public_html/
	$after_public = explode($plugin_uri, $request_uri)[1];

	// Build clean public URL
	$redirect_url = home_url('/deskuss/' . $after_public);

	// Redirect to /deskuss/
	wp_redirect($redirect_url, 301);
	exit;
}
	
// Load WordPress auth bridge
require_once DESKUSS_DIR . '/main/auth-bridge.php';

// Get staff via WordPress authentication bridge
global $thisstaff;
$thisstaff = deskuss_get_current_staff();

if(!function_exists('staffLoginPage')) { //Ajax interface can pre-declare the function to trap expired sessions.
    function staffLoginPage($msg) {
        $redirect = dsk_getCurrentUrl();
        deskuss_staff_login_redirect($redirect);
        exit;
    }
}

// Bootstrap gettext translations as early as possible, but after attempting
// to sign on the agent
TextDomain::configureForUser($thisstaff);

//1) is the user logged in for real && is staff.
if (!$thisstaff || !$thisstaff->getId()) {
    staffLoginPage(__('Authentication Required'));
    exit;
}
//2) if not super admin..check system status and group status
if(!$thisstaff->isAdmin()) {
    //Check for disabled staff or group!
    if (!$thisstaff->isactive()) {
        staffLoginPage(__('Access Denied. Contact Admin'));
        exit;
    }

    //Staff are not allowed to login in offline mode!!
    if(!$dsk->isSystemOnline()) {
        staffLoginPage(__('System Offline'));
        exit;
    }
}

/******* CSRF Protectin *************/
// Enforce CSRF protection for POSTS
if ($_POST  && !$dsk->checkCSRFToken()) {
    Http::response(400, __('Valid CSRF Token Required'));
    exit;
}

//Add token to the header - used on ajax calls [DO NOT CHANGE THE NAME]
$dsk->addExtraHeader('<meta name="csrf_token" content="'.$dsk->getCSRFToken().'" />');

// Load the navigation after the user in case some things are hidden
require_once(INCLUDE_DIR.'class.nav.php');

/******* SET STAFF DEFAULTS **********/
define('PAGE_LIMIT', $thisstaff->getPageLimit() ?: DEFAULT_PAGE_LIMIT);

global $tabs, $submenu, $nav;
$tabs=array();
$submenu=array();
$exempt = in_array(basename($_SERVER['SCRIPT_NAME']), array('logout.php', 'ajax.php', 'logs.php'));

if($cfg->isHelpDeskOffline()) {
    $sysnotice='<strong>'.__('System is set to offline mode').'</strong> - '.__('Client interface is disabled and ONLY admins can access staff control panel.');
    $sysnotice.=' <a href="settings.php">'.__('Enable').'</a>.';
}

if (!defined('AJAX_REQUEST'))
    $nav = new StaffNav($thisstaff);

//Check for forced password change.
if($thisstaff->forcePasswdChange() && !$exempt) {
    # XXX: Call staffLoginPage() for AJAX and API requests _not_ to honor
    #      the request
    $sysnotice = __('Password change required to continue');
    require('profile.php'); //profile.php must request this file as require_once to avoid problems.
    exit;
}
$dsk->setWarning($sysnotice);
$dsk->setPageTitle(__('Deskuss - Staff Control Panel'));

?>

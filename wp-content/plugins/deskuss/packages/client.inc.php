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

if ( ! strcasecmp( basename( $_SERVER['SCRIPT_NAME'] ), basename( __FILE__ ) ) ) {
	wp_die( esc_html__( 'Hacking Attempt!', 'deskuss' ), 403 );
}

$thisdir = str_replace( '\\', '/', dirname( __FILE__ ) ) . '/';
if ( ! file_exists( $thisdir . 'main.inc.php' ) ) {
	wp_die( esc_html__( 'Fatal Error.', 'deskuss' ), 500 );
}

require_once $thisdir . 'main.inc.php';

if ( ! defined( 'INCLUDE_DIR' ) ) {
	wp_die( esc_html__( 'Fatal error.', 'deskuss' ), 500 );
}

/*Some more include defines specific to client only */
if (!defined('CLIENTINC_DIR')) {
	define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
}
define('DSKCLIENTINC',TRUE);

define('ASSETS_PATH',DESKUSS_ROOT_PATH.'assets/');

//Check the status of the HelpDesk.
$current_script = strtolower(basename($_SERVER['SCRIPT_NAME']));
if (!in_array($current_script, array('logo.php','file.php','offline.php'))
        && !(is_object($dsk) && $dsk->isSystemOnline())) {
    include(ROOT_DIR.'offline.php');
    exit;
}

/* include what is needed on client stuff */
require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');

// Load WordPress auth bridge
require_once DESKUSS_DIR . '/main/auth-bridge.php';

//clear some vars
$errors=array();
$msg='';
global $nav;
$nav=null;
//Get user via WordPress authentication bridge
global $thisclient;
$thisclient = deskuss_get_current_client();

if (isset($_GET['lang']) && $_GET['lang']) {
    Internationalization::setCurrentLanguage($_GET['lang']);
}

// Bootstrap gettext translations as early as possible, but after attempting
// to sign on the agent
TextDomain::configureForUser($thisclient);

/******* CSRF Protectin *************/
// Enforce CSRF protection for POSTS
if ($_POST  && !$dsk->checkCSRFToken()) {
    Http::redirect('index.php');
    //just incase redirect fails
    die('Action denied (400)!');
}

//Add token to the header - used on ajax calls [DO NOT CHANGE THE NAME]
$dsk->addExtraHeader('<meta name="csrf_token" content="'.$dsk->getCSRFToken().'" />');

/* Client specific defaults */
define('PAGE_LIMIT', DEFAULT_PAGE_LIMIT);

require(INCLUDE_DIR.'class.nav.php');
$nav = new UserNav($thisclient, 'home');
?>

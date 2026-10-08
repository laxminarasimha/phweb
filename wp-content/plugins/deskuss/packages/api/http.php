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

// Use sessions — it's important for SSO authentication, which uses
// /api/auth/ext
if (!defined('DISABLE_SESSION')) { define('DISABLE_SESSION', false); }

require 'api.inc.php';

# Include the main api urls
require_once INCLUDE_DIR."class.dispatcher.php";

$dispatcher = patterns('',
        url_post("^/tickets\.(?P<format>xml|json|email)$", array('api.tickets.php:TicketApiController','create')),
        url('^/tasks/', patterns('',
                url_post("^cron$", array('api.cron.php:CronApiController', 'execute'))
         ))
        );

Signal::send('api', $dispatcher);

# Call the respective function
print $dispatcher->resolve($dsk->get_path_info());
?>

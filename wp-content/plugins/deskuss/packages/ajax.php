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


function clientLoginPage($msg='Unauthorized') {
    Http::response(403,'Must login: '.Format::htmlchars($msg));
    exit;
}

require('client.inc.php');

if(!defined('INCLUDE_DIR'))	Http::response(500, 'Server configuration error');
require_once INCLUDE_DIR.'/class.dispatcher.php';
require_once INCLUDE_DIR.'/class.ajax.php';

$dispatcher = patterns('',
    url('^/config/', patterns('ajax.config.php:ConfigAjaxAPI',
        url_get('^client$', 'client')
    )),
    url('^/draft/', patterns('ajax.draft.php:DraftAjaxAPI',
        url_post('^(?P<id>\d+)$', 'updateDraftClient'),
        url_delete('^(?P<id>\d+)$', 'deleteDraftClient'),
        url_post('^(?P<id>\d+)/attach$', 'uploadInlineImageClient'),
        url_post('^(?P<namespace>[\w.]+)/attach$', 'uploadInlineImageEarlyClient'),
        url_get('^(?P<namespace>[\w.]+)$', 'getDraftClient'),
        url_post('^(?P<namespace>[\w.]+)$', 'createDraftClient')
    )),
    url('^/form/', patterns('ajax.forms.php:DynamicFormsAjaxAPI',
        url_get('^department/(?P<id>\d+)$', 'getClientFormsForDepartment'),
        url_post('^upload/(\d+)?$', 'upload'),
        url_post('^upload/(\w+)?$', 'attach')
    )),
    url('^/i18n/(?P<lang>[\w_]+)/', patterns('ajax.i18n.php:i18nAjaxAPI',
        url_get('(?P<tag>\w+)$', 'getLanguageFile')
    ))
);
Signal::send('ajax.client', $dispatcher);
print $dispatcher->resolve($dsk->get_path_info());
?>

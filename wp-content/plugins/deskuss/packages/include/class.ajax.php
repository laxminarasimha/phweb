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


require_once (INCLUDE_DIR.'class.api.php');

/**
 * AjaxController Class
 * A simple extension of the ApiController class that will assist in
 * providing functionality common to all Ajax call controllers. Any Ajax
 * call controller should inherit from this class in order to maintain
 * consistency.
 */
class AjaxController extends ApiController {
    function staffOnly() {
        global $thisstaff;
        if(!$thisstaff || !$thisstaff->getId()) {
            Http::response(401,sprintf(__('Access Denied. IP %s'),$_SERVER['REMOTE_ADDR']));
        }
    }
    /**
     * Convert a PHP array into a JSON-encoded string
     */
    function json_encode($what) {
        return Format::json_encode($what);
    }

    function encode($what) {
        return $this->json_encode($what);
    }

    function get($var, $default=null) {
        return (isset($_GET[$var])) ? $_GET[$var] : $default;
    }
}

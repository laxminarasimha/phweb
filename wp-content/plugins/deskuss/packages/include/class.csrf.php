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


Class CSRF {

    var $name;
    var $timeout;

    var $csrf;

    function __construct($name='__CSRFToken__', $timeout=0) {

        $this->name = $name;
        $this->timeout = $timeout;
        $this->csrf = &$_SESSION['csrf'];
    }

    function reset() {
        $this->csrf = array();
    }

    function isExpired() {
       return ($this->timeout && (time()-$this->csrf['time'])>$this->timeout);
    }

    function getTokenName() {
        return $this->name;
    }

    function rotate() {
        $this->csrf['token'] = sha1(session_id().Crypto::random(16).DESKUSS_SECRET_SALT);
        $this->csrf['time'] = time();
    }

    function getToken() {

        if (empty($this->csrf['token']) || $this->isExpired()) {
            $this->rotate();
        } else {
            //Reset the timer
            $this->csrf['time'] = time();
        }

        return $this->csrf['token'];
    }

    function validateToken($token) {
        return ($token && trim($token)==$this->getToken() && !$this->isExpired());
    }

    function getFormInput($name='') {
        if(!$name) $name = $this->name;

        return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $this->getToken());
    }
}

/* global function to add hidden token input with to forms */
function csrf_token() {
    global $dsk;

    if($dsk && $dsk->getCSRF())
        echo $dsk->getCSRFFormInput();
}

/* Prefixed alias to avoid namespace collision with WordPress and other
   plugins. New code should call deskuss_csrf_token() instead. */
if (!function_exists('deskuss_csrf_token')) {
    function deskuss_csrf_token() {
        csrf_token();
    }
}
?>

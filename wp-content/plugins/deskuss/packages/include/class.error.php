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


class BaseError extends Exception {
    static $title = '';
    static $sendAlert = true;

    function __construct($message) {
        global $dsk;

        parent::__construct(__($message));
        $message = str_replace(ROOT_DIR, '(root)/', _S($message));

        if ($dsk && $dsk->getConfig()->getLogLevel() == 3)
            $message .= "\n\n" . $this->getBacktrace();

        $dsk->logError($this->getTitle(), $message, static::$sendAlert);
    }

    function getTitle() {
        return get_class($this) . ': ' . _S(static::$title);
    }

    function getBacktrace() {
        return str_replace(ROOT_DIR, '(root)/', $this->getTraceAsString());
    }
}

class InitialDataError extends BaseError {
    static $title = 'Problem with install initial data';
}

function raise_error($message, $class=false) {
    if (!$class) $class = 'Error';
    new $class($message);
}

// File storage backend exceptions
class IOException extends BaseError {
    static $title = 'Unable to read resource content';
}

?>

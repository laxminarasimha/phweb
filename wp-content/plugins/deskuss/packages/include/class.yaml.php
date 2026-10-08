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


if (!class_exists('Spyc')) require_once "Spyc.php";
require_once "class.error.php";

class YamlDataParser {
    /* static */
    static function load($file) {
        if (!file_exists($file)) {
            raise_error("$file: File does not exist", 'YamlParserError');
            return false;
        }
        return Spyc::YAMLLoad($file);
    }
}

class YamlParserError extends BaseError {
    static $title = 'Error parsing YAML document';
}
?>

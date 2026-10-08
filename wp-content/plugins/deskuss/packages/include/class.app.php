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


class Application {
    private static $client_apps;
    private static $staff_apps;
    private static $admin_apps;

    function registerStaffApp($desc, $href, $info=array()) {
        self::$staff_apps[] = array_merge($info,
            array('desc'=>$desc, 'href'=>$href));
    }

    static function getStaffApps() {
        return self::$staff_apps;
    }

    function registerClientApp($desc, $href, $info=array()) {
        self::$client_apps[] = array_merge($info,
            array('desc'=>$desc, 'href'=>$href));
    }

    function getClientApps() {
        return self::$client_apps;
    }

    function registerAdminApp($desc, $href, $info=array()) {
        self::$admin_apps[] = array_merge($info,
            array('desc'=>$desc, 'href'=>$href));
    }

    function getAdminApps() {
        return self::$admin_apps;
    }
}

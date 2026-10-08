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

require_once 'staff.inc.php';
//Make sure config is loaded and the staff is set and of admin type
if(!$dsk or !$thisstaff or !$thisstaff->isAdmin()){
    header('Location: index.php');
    require('index.php'); // just in case!
    exit;
}

//Define some constants.
define('DSKADMININC',TRUE); //checked by admin include files
define('ADMINPAGE',TRUE);   //Used by the header to swap menus.

//Some security related warnings - bitch until fixed!!! :)
$sysnotice= '';

if(!strcasecmp(basename(CONFIG_FILE), 'settings.php')) {
        $sysnotice=sprintf(__('Please rename config file include/%s to include/config.php to avoid possible conflicts'),
                                basename(CONFIG_FILE));
        //Die gracefully - otherwise upgraded RC5 installations will die with confusing message.
        if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']), 'settings.php'))
            die($sysnotice);

    } elseif(file_exists(dirname(dirname(__FILE__)).'/setup/')) {
        $sysnotice=__('Please take a minute to delete <strong>setup/install</strong> directory (../setup/) for security reasons.');
    } elseif(CONFIG_FILE && file_exists(CONFIG_FILE) && is_writable(CONFIG_FILE)) {
            //Confirm for real that the file is writable by group or world.
            clearstatcache(); //clear the cache!
            $perms = @fileperms(CONFIG_FILE);
            if(($perms & 0x0002) || ($perms & 0x0010)) {
                $sysnotice=sprintf(__('Please change permission of config file (%1$s) to remove write access. e.g <i>chmod 644 %2$s</i>'),
                                basename(CONFIG_FILE), basename(CONFIG_FILE));
            }
    }

if(!$sysnotice && ini_get('register_globals'))
    $sysnotice=__('Please consider turning off register globals if possible');

//System notice displayed as a warning (if any).
$dsk->setWarning($sysnotice);

//Admin navigation - overwrites what was set in staff.inc.php
$nav = new StaffNav($thisstaff);

//Page title.
$dsk->setPageTitle(__('Deskuss - Admin Control Panel'));
?>
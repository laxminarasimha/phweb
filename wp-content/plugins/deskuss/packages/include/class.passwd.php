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

//require_once(INCLUDE_DIR.'PasswordHash.php'); //helper class - will be removed then we move to php5

define('DEFAULT_WORK_FACTOR',8);

class Passwd {

    function cmp($passwd,$hash,$work_factor=0){
        
        if($work_factor < 4 || $work_factor > 31)
            $work_factor=DEFAULT_WORK_FACTOR;

        $hasher = new PasswordHash($work_factor,FALSE);

        return ($hasher && $hasher->CheckPassword($passwd,$hash));
    }

    function hash($passwd, $work_factor=0){
       
        if($work_factor < 4 || $work_factor > 31)
            $work_factor=DEFAULT_WORK_FACTOR;

        $hasher = new PasswordHash($work_factor,FALSE);
        
        return ($hasher && ($hash=$hasher->HashPassword($passwd)))?$hash:null;
    }
}
?>

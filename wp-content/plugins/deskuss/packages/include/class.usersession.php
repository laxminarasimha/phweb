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


include_once(INCLUDE_DIR.'class.client.php');
include_once(INCLUDE_DIR.'class.staff.php');


class UserSession {
   var $session_id = '';
   var $userID='';
   var $browser = '';
   var $ip = '';
   var $validated=FALSE;

   function __construct($userid){

      $this->browser=(!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
      $this->ip=(!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
      $this->session_id=session_id();
      $this->userID=$userid;
   }

   function isStaff(){
       return FALSE;
   }

   function isClient() {
       return FALSE;
   }


   function getSessionId(){
       return $this->session_id;
   }

   function getIP(){
        return  $this->ip;
   }

   function getBrowser(){
       return $this->browser;
   }
   function refreshSession(){
       //nothing to do...clients need to worry about it.
   }

   function sessionToken(){

      $time  = time();
      $hash  = md5($time.SESSION_SECRET.$this->userID);
      $token = "$hash:$time:".MD5($this->ip);

      return($token);
   }

   function getLastUpdate($htoken) {
       if (!$htoken)
           return 0;

       @list($hash,$expire,$ip)=explode(":",$htoken);
       return $expire;
   }

   function isvalidSession($htoken,$maxidletime=0,$checkip=false){
        // WordPress handles session authentication.
        // Always return true since WP validates the user.
        $this->validated=TRUE;
        return TRUE;
   }

   function isValid() {
        return FALSE;
   }

}

class ClientSession extends EndUser {

    var $session;
    var $token;

    function __construct($user) {
        parent::__construct($user);
        $this->token = &$_SESSION[':token']['client'];
        // XXX: Change the key to user-id
        $this->session= new UserSession($user->getId());
    }

    function isValid(){
        // WordPress handles session authentication
        return $this->getId() ? true : false;
    }

    function refreshSession($force=false){
        global $cfg;

        $time = $this->session->getLastUpdate($this->token);
        // Deadband session token updates to once / 30-seconds
        if (!$force && time() - $time < 30)
            return;

        $this->token = $this->getSessionToken();
        //TODO: separate expire time from hash??

        DeskussSession::renewCookie($time, $cfg ? $cfg->getClientSessionTimeout() : 1800);
    }

    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }

    function getIP(){
        return $this->session->getIP();
    }
}


class StaffSession extends Staff {

    var $session;
    var $token;

    static function lookup($var) {
        if ($staff = parent::lookup($var)) {
            $staff->token = &$_SESSION[':token']['staff'];
            $staff->session= new UserSession($staff->getId());
        }
        return $staff;
    }

    function clear2FA(){
        $_SESSION['_auth']['staff']['2fa'] = null;
        return true;
    }

    // If 2fa is set then it means it's pending
    function is2FAPending(){
    
        if (!isset($_SESSION['_auth']['staff']['2fa']))
            return false;

        return true;
    }

    function isValid(){
        // WordPress handles session authentication
        return $this->getId() ? true : false;
    }

    function refreshSession($force=false){
        global $cfg;

        $time = $this->session->getLastUpdate($this->token);
        // Deadband session token updates to once / 30-seconds
        if (!$force && time() - $time < 30)
            return;

        $this->token=$this->getSessionToken();

        DeskussSession::renewCookie($time, $cfg ? $cfg->getStaffSessionTimeout() : 1800);
    }

    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }

    function getIP(){
        return $this->session->getIP();
    }

}

?>

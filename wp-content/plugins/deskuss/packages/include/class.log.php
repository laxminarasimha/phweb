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


class Log {

    var $id;
    var $info;

    function __construct($id){
        $this->id=0;
        return $this->load($id);
    }

    function load($id){

        $sql='SELECT * FROM '.SYSLOG_TABLE.' WHERE log_id='.db_input($id);
        if(!($res=db_query($sql)) || !db_num_rows($res))
            return false;

        $this->info=db_fetch_array($res);
        $this->id=$this->info['log_id'];
        
        return $this->id;
    }

    function reload(){
        return $this->load($this->getId());
    }

    function getId(){
        return $this->id;
    }
        
    function getType(){
        return $this->info['log_type'];    
    }

    function getTitle(){
        return $this->info['title'];
    }

    function getText(){
        return $this->info['log'];
    }

    function getIp(){
        return $this->info['ip_address'];
    }

    function getCreateDate(){
        return $this->info['created'];
    }

    function getInfo(){
        return $this->info;
    }

    /*** static function ***/
    static function lookup($id){
        return ($id && is_numeric($id) && ($l= new Log($id)) && $l->getId()==$id)?$l:null;
    }
}
?>

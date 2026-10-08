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


// TODO:  Make ObjectModel models base class and extend VerySimpleModel
class ObjectModel {

    const OBJECT_TYPE_TICKET = 'T';
    const OBJECT_TYPE_THREAD = 'H';
    const OBJECT_TYPE_USER   = 'U';
    const OBJECT_TYPE_ORG    = 'O';
    const OBJECT_TYPE_FAQ    = 'K';
    const OBJECT_TYPE_FILE   = 'F';

    private static function objects() {
        static $objects = false;
        if ($objects == false) {
            $objects = array(
                    self::OBJECT_TYPE_TICKET  => 'Ticket',
                    self::OBJECT_TYPE_THREAD  => 'ThreadEntry',
                    self::OBJECT_TYPE_USER    => 'User',
                    self::OBJECT_TYPE_ORG     => 'Organization',
                    self::OBJECT_TYPE_FAQ     => 'FAQ',
                    self::OBJECT_TYPE_FILE    => 'AttachmentFile',
                    );
        }

        return $objects;
    }

    static function getType($model) {

        foreach (self::objects() as $t => $c) {
            if ($model instanceof $c)
                return $t;
        }
    }

    static function lookup($id, $type) {
        $model = null;
        if ($id
                && ($objects=self::objects())
                && ($class=$objects[$type])
                && class_exists($class)
                && is_callable(array($class, 'lookup')))
            $model = $class::lookup($id);

        return $model;
    }
}
?>

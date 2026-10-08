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


class Priority extends VerySimpleModel
implements TemplateVariable {

    static $meta = array(
        'table' => PRIORITY_TABLE,
        'pk' => array('priority_id'),
        'ordering' => array('-priority_urgency')
    );

    function getId() {
        return $this->priority_id;
    }

    function getTag() {
        return $this->priority;
    }

    function getDesc() {
        return $this->priority_desc;
    }

    function getColor() {
        return $this->priority_color;
    }

    function getUrgency() {
        return $this->priority_urgency;
    }

    function isPublic() {
        return $this->ispublic;
    }

    // TemplateVariable interface
    function asVar() { return $this->getDesc(); }
    static function getVarScope() {
        return array(
            'desc' => __('Priority Level'),
        );
    }

    function __toString() {
        return $this->getDesc();
    }

    /* ------------- Static ---------------*/
    static function getPriorities( $publicOnly=false) {
        $priorities=array();

        $objects = static::objects()->values_flat('priority_id', 'priority_desc');
        if ($publicOnly)
            $objects->filter(array('ispublic'=>1));

        foreach ($objects as $row) {
            $priorities[$row[0]] = $row[1];
        }

        return $priorities;
    }

    function getPublicPriorities() {
        return self::getPriorities(true);
    }
}
?>

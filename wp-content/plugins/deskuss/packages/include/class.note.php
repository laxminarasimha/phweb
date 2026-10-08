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

require_once(INCLUDE_DIR . 'class.orm.php');

class QuickNoteModel extends VerySimpleModel {
    static $meta = array(
        'table' => NOTE_TABLE,
        'pk' => array('id'),
        'ordering' => array('sort', 'created')
    );
}

class QuickNote extends QuickNoteModel {

    static $types = array(
        'U' => /* @trans */ 'User',
        'O' => /* @trans */ 'Organization',
    );
    var $_staff;

    function display() {
        return Format::display($this->body);
    }

    function getStaff() {
        if (!isset($this->_staff) && $this->staff_id) {
            $this->_staff = Staff::lookup($this->staff_id);
        }
        return $this->_staff;
    }

    function getFormattedTime() {
        return Format::datetime(strpos($this->updated, '0000-') !== 0
            ? $this->updated : $this->created);
    }

    function getExtType() {
        return static::$types[$this->ext_id[0]];
    }

    function getExtIconClass() {
        switch ($this->ext_id[0]) {
        case 'U':
            return 'fa-user';
        case 'O':
            return 'fa-building';
        }
    }

    function getIconTitle() {
        return sprintf(__(
            // `%s` will be the type of note (`user` or `orgnaization`)
            "%s Note"),
            __(static::$types[$this->ext_id[0]]));
    }

    static function forUser($user, $org=false) {
        if ($org)
            return static::objects()->filter(array('ext_id__in' =>
                array('U'.$user->get('id'), 'O'.$org->get('id'))));
        else
            return static::objects()->filter(array('ext_id' => 'U'.$user->get('id')));
    }

    static function forOrganization($org) {
        return static::objects()->filter(array('ext_id' => 'O'.$org->get('id')));
    }

    function save($refetch=false) {
        if (count($this->dirty))
            $this->updated = new SQLFunction('NOW');
        return parent::save($refetch);
    }
}

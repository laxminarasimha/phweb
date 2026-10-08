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


class Versions extends VerySimpleModel
implements TemplateVariable {

    static $meta = array(
        'table' => CONFIG_TABLE,
        'pk' => array('id'),
    );

    var $_members;
    var $_primary_members;
    var $_extended_members;

    var $_groupids;
    var $config;

    var $template;
    var $autorespEmail;

    const ALERTS_DISABLED = 2;
    const ALERTS_DEPT_AND_EXTENDED = 1;
    const ALERTS_DEPT_ONLY = 0;

    const FLAG_ASSIGN_MEMBERS_ONLY = 0x0001;
    const FLAG_DISABLE_AUTO_CLAIM  = 0x0002;

    static function getVarScope() {
        return array(
            'name' => 'Department name',
            'manager' => array(
                'class' => 'Staff', 'desc' => 'Department manager',
                'exclude' => 'dept',
            ),
            'members' => array(
                'class' => 'UserList', 'desc' => 'Department members',
            ),
            'parent' => array(
                'class' => 'Dept', 'desc' => 'Parent department',
            ),
            'sla' => array(
                'class' => 'SLA', 'desc' => 'Service Level Agreement',
            ),
            'signature' => 'Department signature',
        );
    }

    static function getVersions( $criteria=null, $localize=true) {
        static $versions = null;

        if (!isset($versions) || $criteria) {
            // XXX: This will upset the static $versions array
            $versions = array();
            $query = self::objects();
			
            $query->order_by('key')
                ->values('id', 'key', 'value');

            foreach ($query as $row)
                $ver[$row['id']] = $row;

            // Resolve parent names
            $versions = array();
			$dept_id = '';
			
            foreach ($ver as $id=> $info) {
				$department_id = dsk_optPOST('department_id');
                if((empty($info['value']) || !preg_match('/version_/is', $info['key'])) || (!empty($department_id) && !preg_match('/version_'.$department_id.'/is', $info['key']))){
					continue;
				}
				$dept_id = substr($info['key'], strpos($info['key'], "_") + 1);
				
				$versions[$dept_id] = $info['value'];
            }
			//dsk_r_print($versions)
        }

        return $versions;
    }
}

class VersionsQuickAddForm
extends Form {
    function getFields() {
        if ($this->fields)
            return $this->fields;

        return $this->fields = array(
            'pid' => new ChoiceField(array(
                'label' => '',
                'default' => 0,
                'choices' =>
                    array(0 => '— '.__('Top-Level Department').' —')
                    + Versions::getVersions()
            )),
            'name' => new TextboxField(array(
                'required' => true,
                'configuration' => array(
                    'placeholder' => __('Name'),
                    'classes' => 'span12',
                    'autofocus' => true,
                    'length' => 128,
                ),
            )),
            'email_id' => new ChoiceField(array(
                'label' => __('Email Mailbox'),
                'default' => 0,
                'choices' =>
                    array(0 => '— '.__('System Default').' —')
                    + Email::getAddresses(),
                'configuration' => array(
                    'classes' => 'span12',
                ),
            )),
            'private' => new BooleanField(array(
                'configuration' => array(
                    'classes' => 'form footer',
                    'desc' => __('This department is for internal use'),
                ),
            )),
        );
    }
}

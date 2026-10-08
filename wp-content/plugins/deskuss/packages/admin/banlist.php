<?php


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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

require('admin.inc.php');
include_once(INCLUDE_DIR.'class.banlist.php');

/* Get the system ban list filter */
if(!($filter=Banlist::getFilter()))
    $warn = __('System ban list is empty.');
elseif(!$filter->isActive())
    // XXX: This should never happen and can no longer be enabled via
    // this link
    $warn = __('SYSTEM BAN LIST filter is <b>DISABLED</b>').' - <a href="filters.php">'.__('enable here').'</a>.';

$rule=null; //ban rule obj.
if($filter && !empty($_REQUEST['id']) && !($rule=$filter->getRule($_REQUEST['id'])))
    $errors['err'] = sprintf(__('%s: Unknown or invalid ID.'), __('ban list'));

if($_POST && !$errors && $filter){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$rule){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('ban rule'));
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])){
                $errors['err']=$errors['val']=__('Valid email address is required');
            }elseif(!$errors){
                $vars=array('what'=>'email',
                            'how'=>'equal',
                            'val'=>trim($_POST['val']),
                            'filter_id'=>$filter->getId(),
                            'isactive'=>$_POST['isactive'],
                            'notes'=>$_POST['notes']);
                if($rule->update($vars,$errors)){
                    $msg=sprintf(__('Successfully updated %s.'), Format::htmlchars($_POST['val']));
                }elseif(!$errors['err']){
                    $errors['err'] = sprintf('%s %s',
                        sprintf(__('Unable to update %s.'), __('this ban rule')),
                        __('Correct any errors below and try again.'));
                }
            }
            break;
        case 'add':
            if(!$filter) {
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('ban list'));
            }elseif(!$_POST['val'] || !Validator::is_email($_POST['val'])) {
                $errors['err']=$errors['val']=__('Valid email address is required');
            }elseif(BanList::includes(trim($_POST['val']))) {
                $errors['err']=$errors['val']=__('Email already in the ban list');
            }elseif($filter->addRule('email','equal',trim($_POST['val']),array('isactive'=>$_POST['isactive'],'notes'=>$_POST['notes']))) {
                $msg=__('Email address added to ban list successfully');
                $_REQUEST['a']=null;
                //Add filter rule here.
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s', sprintf(__('Error creating %s.'), __('ban rule')), __('Please try again!'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s to process.'),
                    __('one email'));
            } else {
                $count=count($_POST['ids']);
                // Cast all IDs to integers to prevent SQL injection.
                $ids = array_map('intval', $_POST['ids']);
                $id_list = implode(',', $ids);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=1 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected ban rule', 'selected ban rules', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected ban rule', 'selected ban rules', $count));
                        } else  {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected ban rule', 'selected ban rules', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_RULE_TABLE.' SET isactive=0 '
                            .' WHERE filter_id='.db_input($filter->getId())
                            .' AND id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected ban rule', 'selected ban rules', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected ban rule', 'selected ban rules', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected ban rule', 'selected ban rules', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($r=FilterRule::lookup($v)) && $r->getFilterId()==$filter->getId() && $r->delete())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected ban rule', 'selected ban rules', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected ban rule', 'selected ban rules', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected ban rule', 'selected ban rules', $count));

                        break;
                    default:
                        $errors['err'] = sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
                }
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='banlist.php';
$tip_namespace = 'emails.banlist';
if(!$filter || ($rule || (!empty($_REQUEST['a']) && !strcasecmp($_REQUEST['a'],'add')))) {
    $page='banrule.php';
}

$nav->setTabActive('users','banlist.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

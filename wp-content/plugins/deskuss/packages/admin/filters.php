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
include_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');

$filter=null;
if(!empty($_REQUEST['id']) && !($filter=Filter::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid'), __('ticket filter'));

/* NOTE: Banlist has its own interface*/
if($filter && $filter->isSystemBanlist())
    Http::redirect('banlist.php');

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$filter){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('ticket filter'));
            }elseif($filter->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'), __('this ticket filter'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this ticket filter')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'add':
            if((Filter::create($_POST,$errors))){
                $msg=sprintf(__('Successfully updated %s.'), __('this ticket filter'));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this ticket filter')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s to process.'),
                    __('one ticket filter'));
            } else {
                $count=count($_POST['ids']);
                // Cast all IDs to integers to prevent SQL injection.
                $ids = array_map('intval', $_POST['ids']);
                $id_list = implode(',', $ids);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=1 '
                            .' WHERE id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected ticket filter', 'selected ticket filters', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected ticket filter', 'selected ticket filters', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected ticket filter', 'selected ticket filters', $count));
                        }
                        break;
                    case 'disable':
                        $sql='UPDATE '.FILTER_TABLE.' SET isactive=0 '
                            .' WHERE id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully disabled %s'),
                                    _N('selected ticket filter', 'selected ticket filters', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected ticket filter', 'selected ticket filters', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected ticket filter', 'selected ticket filters', $count));
                        }
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($f=Filter::lookup($v)) && !$f->isSystemBanlist() && $f->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected ticket filter', 'selected ticket filters', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected ticket filter', 'selected ticket filters', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                 _N('selected ticket filter', 'selected ticket filters', $count));
                        break;
                    default:
                        $errors['err']=sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
                }
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='filters.php';
$tip_namespace = 'manage.filter';
if($filter || (!empty($_REQUEST['a']) && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='filter.php';
}

$nav->setTabActive('manage','filters.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

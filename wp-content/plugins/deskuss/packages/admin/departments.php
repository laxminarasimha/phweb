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

$dept=null;
if(!empty($_REQUEST['id']) && !($dept=Dept::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('department'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$dept){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('department'));
            }elseif($dept->update($_POST,$errors)){
                apply_filters_ref_array('deskuss_wp_update_department', array($dept, $_POST));
                $msg=sprintf(__('Successfully updated %s.'), __('this department'));
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this department')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $_dept = Dept::create();
            if(($_dept->update($_POST,$errors))){
                $msg=sprintf(__('Successfully added %s.'),Format::htmlchars($_POST['name']));
                apply_filters_ref_array('deskuss_wp_add_department', array($_dept, $_POST));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this department')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one department'));
            }elseif(in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                $errors['err'] = __('You cannot disable/delete a default department. Select a new default department and try again.');
            }else{
                $count=count($_POST['ids']);
                // Cast all IDs to integers to prevent SQL injection.
                $ids = array_map('intval', $_POST['ids']);
                $id_list = implode(',', $ids);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 '
                            .' WHERE dept_id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg=sprintf(__('Successfully made %s PUBLIC'),
                                    _N('selected department', 'selected departments', $count));
                            else
                                $warn=sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> made PUBLIC */
                                    '%1$d of %2$d %3$s made PUBLIC'), $num, $count,
                                    _N('selected department', 'selected departments', $count));
                        } else {
                            $errors['err']=sprintf(__('Unable to make %s PUBLIC.'),
                                _N('selected department', 'selected departments', $count));
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0  '
                            .' WHERE dept_id IN ('.$id_list.') '
                            .' AND dept_id!='.db_input($cfg->getDefaultDeptId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully made %s PRIVATE'),
                                    _N('selected department', 'selected epartments', $count));
                            else
                                $warn = sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> made PRIVATE */
                                    '%1$d of %2$d %3$s made PRIVATE'), $num, $count,
                                    _N('selected department', 'selected departments', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to make %s private. Possibly already private!'),
                                _N('selected department', 'selected departments', $count));
                        }
                        break;
                    case 'delete':
                        //Deny all deletes if one of the selections has members in it.
                        $sql='SELECT count(staff_id) FROM '.STAFF_TABLE
                            .' WHERE dept_id IN ('.$id_list.')';
                        list($members)=db_fetch_row(db_query($sql));
                        if($members)
                            $errors['err']=__('Departments with agents can not be deleted. Move the agents first.');
                        else {
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if($v!=$cfg->getDefaultDeptId() && ($d=Dept::lookup($v)) && $d->delete()) {
                                    do_action('deskuss_wp_delete_department', intval($v));
                                    $i++;
                                }
                            }
                            if($i && $i==$count)
                                $msg = sprintf(__('Successfully deleted %s.'),
                                    _N('selected department', 'selected departments', $count));
                            elseif($i>0)
                                $warn = sprintf(__(
                                    /* Phrase will read:
                                       <a> of <b> <selected objects> deleted */
                                    '%1$d of %2$d %3$s deleted'), $i, $count,
                                    _N('selected department', 'selected departments', $count));
                            elseif(!$errors['err'])
                                $errors['err'] = sprintf(__('Unable to delete %s.'),
                                    _N('selected department', 'selected departments', $count));
                        }
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

$page='departments.php';
$tip_namespace = 'staff.department';
if($dept || (!empty($_REQUEST['a']) && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='department.php';
}

$nav->setTabActive('departments','departments.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

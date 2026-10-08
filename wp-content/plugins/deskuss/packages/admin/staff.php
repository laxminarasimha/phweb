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

// Included here for role permission registration
require_once INCLUDE_DIR . 'class.report.php';

$staff=null;
if(!empty($_REQUEST['id']) && !($staff=Staff::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('agent'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$staff){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('agent'));
            }elseif($staff->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this agent'));
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this agent')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $staff = Staff::create();
            // Unpack the data from the set-password dialog (if used)
            if (isset($_SESSION['new-agent-passwd'])) {
                foreach ($_SESSION['new-agent-passwd'] as $k=>$v)
                    if (!isset($_POST[$k]))
                        $_POST[$k] = $v;
            }
            if ($staff->update($_POST,$errors)) {
                unset($_SESSION['new-agent-passwd']);
                $msg=sprintf(__('Successfully added %s.'),Format::htmlchars($_POST['firstname']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this agent')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one agent'));
            } elseif(in_array($_POST['a'], array('disable', 'delete'))
                && in_array($thisstaff->getId(),$_POST['ids'])
            ) {
                $errors['err'] = __('You can not disable/delete yourself - you could be the only admin!');
            } else {
                $count = count($_POST['ids']);
                $members = Staff::objects()->filter(array(
                    'staff_id__in' => $_POST['ids']
                ));
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = $members->update(array('isactive' => 1));
                        if ($num) {
                            if($num==$count)
                                $msg = sprintf('Successfully activated %s',
                                    _N('selected agent', 'selected agents', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s activated'), $num, $count,
                                    _N('selected agent', 'selected agents', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to activate %s'),
                                _N('selected agent', 'selected agents', $count));
                        }
                        break;

                    case 'disable':
                        $num = $members->update(array('isactive' => 0));
                        if ($num) {
                            if($num==$count)
                                $msg = sprintf('Successfully disabled %s',
                                    _N('selected agent', 'selected agents', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $num, $count,
                                    _N('selected agent', 'selected agents', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected agent', 'selected agents', $count));
                        }
                        break;

                    case 'delete':
                        $i = 0;
                        foreach($members as $s) {
                            if ($s->staff_id != $thisstaff->getId() && $s->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected agent', 'selected agents', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected agent', 'selected agents', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected agent', 'selected agents', $count));
                        break;

                    case 'permissions':
                        foreach ($members as $s)
                            if ($s->updatePerms($_POST['perms'], $errors) && $s->save())
                                $i++;

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully updated %s.'),
                                _N('selected agent', 'selected agents', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s updated'), $i, $count,
                                _N('selected agent', 'selected agents', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to update %s.'),
                                _N('selected agent', 'selected agents', $count));
                        break;

                    case 'department':
                        if (!$_POST['dept_id'] || !$_POST['role_id']
                            || !Dept::lookup($_POST['dept_id'])
                            || !Role::lookup($_POST['role_id'])
                        ) {
                            $errors['err'] = __('Internal error occurred');
                            break;
                        }
                        foreach ($members as $s) {
                            $s->setDepartmentId((int) $_POST['dept_id'], $_POST['eavesdrop']);
                            $s->role_id = (int) $_POST['role_id'];
                            if ($s->save() && $s->dept_access->saveAll())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully updated %s.'),
                                _N('selected agent', 'selected agents', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s updated'), $i, $count,
                                _N('selected agent', 'selected agents', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to update %s.'),
                                _N('selected agent', 'selected agents', $count));
                        break;

                    default:
                        $errors['err'] = sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
                }

            }
            break;
        case 'sync_wp_admins':
            if(function_exists('deskuss_sync_all_wp_admins')){
                $count = deskuss_sync_all_wp_admins_with_count();
                if($count > 0){
                    $msg = sprintf(
                        _N('Synced %s WordPress administrator as Deskuss super admin.', 'Synced %s WordPress administrators as Deskuss super admins.', $count),
                        $count
                    );
                } else {
                    $msg = __('No new WordPress administrators to sync.');
                }
            } else {
                $errors['err'] = __('Deskuss sync function is unavailable.');
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='staffmembers.php';
$tip_namespace = 'staff.agent';
if ($staff || (!empty($_REQUEST['a']) && (!strcasecmp($_REQUEST['a'], 'add') || !strcasecmp($_REQUEST['a'], 'wordpress_user')))) {
    $page='staff.php';
}

$nav->setTabActive('staff','staff.inc.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

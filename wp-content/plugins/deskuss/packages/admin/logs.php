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

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one log entry'));
            } else {
                $count=count($_POST['ids']);
                // Cast all IDs to integers to prevent SQL injection.
                $ids = array_map('intval', $_POST['ids']);
                $id_list = implode(',', $ids);
                if($_POST['a'] && !strcasecmp($_POST['a'], 'delete')) {

                    $sql='DELETE FROM '.SYSLOG_TABLE
                        .' WHERE log_id IN ('.$id_list.')';
                    if(db_query($sql) && ($num=db_affected_rows())){
                        if($num==$count)
                            $msg=sprintf(__('Successfully deleted %s.'),
                                _N('selected log entry', 'selected log entries', $count));
                        else
                            $warn=sprintf(__('%1$d of %2$d %3$s deleted'), $num, $count,
                                _N('selected log entry', 'selected log entries', $count));
                    } elseif(!$errors['err'])
                        $errors['err']=sprintf(__('Unable to delete %s.'),
                            _N('selected log entry', 'selected log entries', $count));
                } else {
                    $errors['err']=sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
                }
            }
            break;
        default:
            $errors['err']=__('Unknown action');
            break;
    }
}

$page='syslogs.php';
$nav->setTabActive('about', 'logs.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="dashboard.system_logs" />',
    "$('#content').data('tipNamespace', 'dashboard.system_logs');");
require deskuss_load_page($page);
?>

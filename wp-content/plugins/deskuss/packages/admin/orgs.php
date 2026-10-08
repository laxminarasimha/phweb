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

require('staff.inc.php');
require_once INCLUDE_DIR . 'class.organization.php';
require_once INCLUDE_DIR . 'class.note.php';

$org = null;
if ($_REQUEST['id'] || $_REQUEST['org_id'])
    $org = Organization::lookup($_REQUEST['org_id'] ?: $_REQUEST['id']);

if ($_POST) {
    switch ($_REQUEST['a']) {
    case 'import-users':
        if (!$org) {
            $errors['err'] = __('Organization ID must be specified for import');
            break;
        }
        $status = User::importFromPost($_FILES['import'] ?: $_POST['pasted'],
            array('org_id'=>$org->getId()));
        if (is_numeric($status))
            $msg = sprintf(__('Successfully imported %1$d %2$s'), $status,
                _N('end user', 'end users', $status));
        else
            $errors['err'] = $status;
        break;
    case 'remove-users':
        if (!$org)
            $errors['err'] = __('Trying to remove end users from an unknown organization');
        elseif (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('You must select at least %s.'),
                __('one end user'));
        } else {
            $i = 0;
            foreach ($_POST['ids'] as $k=>$v) {
                if (($u=User::lookup($v)) && $org->removeUser($u))
                    $i++;
            }
            $num = count($_POST['ids']);
            if ($i && $i == $num)
                $msg = sprintf(__('Successfully removed %s.'),
                    _N('selected end user', 'selected end users', $count));
            elseif ($i > 0)
                $warn = sprintf(__('%1$d of %2$d %3$s removed'), $i, $count,
                    _N('selected end user', 'selected end users', $count));
            elseif (!$errors['err'])
                $errors['err'] = sprintf(__('Unable to remove %s'),
                    _N('selected end user', 'selected end users', $count));
        }
        break;

    case 'mass_process':
        if (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('You must select at least %s.'),
                __('one organization'));
        }
        else {
            $orgs = Organization::objects()->filter(
                array('id__in' => $_POST['ids'])
            );
            $count = 0;
            switch (strtolower($_POST['do'])) {
            case 'delete':
                foreach ($orgs as $O)
                    if ($O->delete())
                        $count++;
                break;

            default:
                $errors['err']=sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
            }
            if (!$errors['err'] && !$count) {
                $errors['err'] = __('Unable to manage any of the selected organizations');
            }
            elseif ($_POST['count'] && $count != $_POST['count']) {
                $warn = __('Not all selected items were updated');
            }
            elseif ($count) {
                $msg = __('Successfully managed selected organizations');
            }
        }
        break;

    default:
        $errors['err'] = __('Unknown action');
    }
} elseif (!$org && $_REQUEST['a'] == 'export') {
    require_once(INCLUDE_DIR.'class.export.php');
    $ts = strftime('%Y%m%d');
    if (!($query=$_SESSION[':Q:orgs']))
        $errors['err'] = __('Query token not found');
    elseif (!Export::saveOrganizations($query, __('organizations')."-$ts.csv", 'csv'))
        $errors['err'] = __('Unable to export results.')
            .' '.__('Internal error occurred');
}

$page = 'orgs.php';
if ($org) {
    $page = 'org-view.php';
    switch (strtolower($_REQUEST['t'])) {
    case 'tickets':
        if (isset($_SERVER['HTTP_X_PJAX'])) {
            $page='templates/tickets.tmpl.php';
            $pjax_container = @$_SERVER['HTTP_X_PJAX_CONTAINER'];
            require deskuss_load_view($page);
            return;
        } elseif ($_REQUEST['a'] == 'export' && ($query=$_SESSION[':O:tickets'])) {
            $filename = sprintf('%s-tickets-%s.csv',
                    $org->getName(), strftime('%Y%m%d'));
            if (!Export::saveTickets($query, $filename, 'csv'))
                $errors['err'] = __('Unable to dump query results.')
                    .' '.__('Internal error occurred');
        }
        break;
    }
}

$nav->setTabActive('users', 'orgs.php');
require deskuss_load_page($page);
?>

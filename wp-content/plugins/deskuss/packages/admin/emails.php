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
include_once(INCLUDE_DIR.'class.email.php');

$email=null;
if(!empty($_REQUEST['id']) && !($email=Email::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('email'));

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$email){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('email'));
            }elseif($email->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this email'));
            }elseif(!$errors['err']){
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this email')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $box = Email::create();
            if ($box->update($_POST, $errors)) {
                $id = $box->getId();
                $msg=sprintf(__('Successfully added %s.'), Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this email')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one email'));
            } else {
                $count=count($_POST['ids']);

                switch (strtolower($_POST['a'])) {
                case 'delete':
                    $i=0;
                    foreach($_POST['ids'] as $k=>$v) {
                        if($v!=$cfg->getDefaultEmailId() && ($e=Email::lookup($v)) && $e->delete())
                            $i++;
                    }

                    if($i && $i==$count)
                        $msg = sprintf(__('Successfully deleted %s.'),
                            _N('selected email', 'selected emails', $count));
                    elseif($i>0)
                        $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                            _N('selected email', 'selected emails', $count));
                    elseif(!$errors['err'])
                        $errors['err'] = sprintf(__('Unable to delete %s.'),
                            _N('selected email', 'selected emails', $count));
                    break;

                default:
                    $errors['err'] = sprintf('%s - %s', __('Unknown action'), __('Get technical help!'));
                }
            }
            break;
        default:
            $errors['err'] = __('Unknown action');
            break;
    }
}

$page='emails.php';
$tip_namespace = 'emails.email';
if($email || (!empty($_REQUEST['a']) && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='email.php';
}

$nav->setTabActive('emails','emails.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

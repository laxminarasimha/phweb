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
require_once(INCLUDE_DIR.'class.page.php');

$page = null;
if(!empty($_REQUEST['id']) && !($page=Page::lookup($_REQUEST['id'])))
   $errors['err']=sprintf(__('%s: Unknown or invalid'), __('site page'));

if($_POST) {
    switch(strtolower($_POST['do'])) {
        case 'add':
            $page = Page::create();
            if($page->update($_POST, $errors)) {
                $pageId = $page->getId();
                $_REQUEST['a'] = null;
                $msg=sprintf(__('Successfully added %s.'), Format::htmlchars($_POST['name']));
                Draft::deleteForNamespace('page');
            } elseif(!$errors['err'])
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this site page')),
                    __('Correct any errors below and try again.'));
        break;
        case 'update':
            if(!$page)
                $errors['err'] = sprintf(__('%s: Invalid or unknown'),
                    __('site page'));
            elseif($page->update($_POST, $errors)) {
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this site page'));
                $_REQUEST['a']=null; //Go back to view
                Draft::deleteForNamespace('page.'.$page->getId().'%');
            } elseif(!$errors['err'])
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this site page')),
                    __('Correct any errors below and try again.'));
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = sprintf(__('You must select at least %s.'),
                    __('one site page'));
            } elseif(array_intersect($_POST['ids'], $cfg->getDefaultPages()) && strcasecmp($_POST['a'], 'enable')) {
                $errors['err'] = sprintf(__('One or more of the %s is in-use and CANNOT be disabled/deleted.'),
                    _N('selected site page', 'selected site pages', 2));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $num = Page::objects()
                            ->filter(array('id__in'=>$_POST['ids']))
                            ->update(array('isactive'=>1));
                        if ($num) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected site page', 'selected site pages', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected site page', 'selected site pages', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected site page', 'selected site pages', $count));
                        }
                        break;
                    case 'disable':
                        $i = 0;
                        foreach (Page::objects()->filter(array('id__in'=>$_POST['ids'])) as $p) {
                            if ($p->disable())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully disabled %s'),
                                _N('selected site page', 'selected site pages', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $i, $count,
                                _N('selected site page', 'selected site pages', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to disable %s'),
                                _N('selected site page', 'selected site pages', $count));
                        break;
                    case 'delete':
                        $i = Page::objects()
                            ->filter(array('id__in'=>$_POST['ids']))
                            ->delete();

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected site page', 'selected site pages', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected site page', 'selected site pages', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected site page', 'selected site pages', $count));
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

$inc='pages.php';
$tip_namespace = 'manage.pages';
if($page || (($_REQUEST['a'] ?? '') =='add')) {
    $inc='page.php';
}

$nav->setTabActive('manage','pages.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($inc);
?>

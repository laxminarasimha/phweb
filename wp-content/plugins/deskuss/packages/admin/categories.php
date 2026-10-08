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
include_once(INCLUDE_DIR.'class.category.php');

/* check permission */
if(!$thisstaff ||
        !$thisstaff->hasPerm(FAQ::PERM_MANAGE)) {
    header('Location: kb.php');
    exit;
}


$category=null;
if(!empty($_REQUEST['id']) && !($category=Category::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('Category'));

if($_POST){
    switch(strtolower($_POST['do'])) {
        case 'update':
            if(!$category) {
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('Category'));
            } elseif($category->update($_POST,$errors)) {
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this category'));
            } elseif(!$errors['err']) {
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this category')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'create':
            $category = Category::create();
            if ($category->update($_POST, $errors)) {
                $msg=sprintf(__('Successfully added %s.'), Format::htmlchars($_POST['name']));
                $_REQUEST['a']=null;
            } elseif(!$errors['err']) {
                $errors['err'] = sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this category')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('You must select at least %s.'), __('one category'));
            } else {
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $num = Category::objects()->filter(array(
                            'category_id__in'=>$_POST['ids']
                        ))->update(array(
                            'ispublic'=>true
                        ));
                        if ($num > 0) {
                            if ($num==$count)
                                $msg = sprintf(__('Successfully made %s PUBLIC'),
                                    _N('selected category', 'selected categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s made PUBLIC'), $num, $count,
                                    _N('selected category', 'selected categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to make %s PUBLIC.'),
                                _N('selected category', 'selected categories', $count));
                        }
                        break;
                    case 'make_private':
                        $num = Category::objects()->filter(array(
                            'category_id__in'=>$_POST['ids']
                        ))->update(array(
                            'ispublic'=>false
                        ));
                        if ($num > 0) {
                            if($num==$count)
                                $msg = sprintf(__('Successfully made %s PRIVATE'),
                                    _N('selected category', 'selected categories', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s made PRIVATE'), $num, $count,
                                    _N('selected category', 'selected categories', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to make %s PRIVATE'),
                                _N('selected category', 'selected categories', $count));
                        }
                        break;
                    case 'delete':
                        $i = Category::objects()->filter(array(
                            'category_id__in'=>$_POST['ids']
                        ))->delete();

                        if ($i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected category', 'selected categories', $count));
                        elseif ($i > 0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected category', 'selected categories', $count));
                        elseif (!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected category', 'selected categories', $count));
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

$page='categories.php';
$tip_namespace = 'knowledgebase.category';
if($category || (!empty($_REQUEST['a']) && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='category.php';
}

$nav->setTabActive('kbase','categories.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

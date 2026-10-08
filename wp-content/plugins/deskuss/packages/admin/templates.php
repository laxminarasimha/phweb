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
include_once(INCLUDE_DIR.'class.template.php');

$template=null;
if(!empty($_REQUEST['tpl_id']) &&
        !($template=EmailTemplateGroup::lookup($_REQUEST['tpl_id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid'), __('template set'));
elseif(!empty($_REQUEST['id']) &&
        !($template=EmailTemplate::lookup($_REQUEST['id'])))
    $errors['err']=sprintf(__('%s: Unknown or invalid %s'), __('template'));
elseif(!empty($_REQUEST['default_for'])) {
    $sql = 'SELECT id FROM '.EMAIL_TEMPLATE_TABLE
        .' WHERE tpl_id='.db_input($cfg->getDefaultTemplateId())
        .' AND code_name='.db_input($_REQUEST['default_for']);
    if ($id = db_result(db_query($sql)))
        Http::redirect('templates.php?a=manage&id='.db_input($id));
}

if($_POST){
    switch(strtolower($_POST['do'])){
        case 'updatetpl':
            if(!$template){
                $errors['err']=sprintf(__('%s: Unknown or invalid'),
                    __('message template'));
            }elseif($template->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    __('this message template'));
                // Drop drafts for this template for ALL users
                Draft::deleteForNamespace('tpl.'.$template->getCodeName()
                    .'.'.$template->getTplId());
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this template')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'implement':
            if(!$template){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('template set'));
            }elseif($new = EmailTemplate::add($_POST,$errors)){
                $template = $new;
                $msg=sprintf(__('Successfully updated %s.'), __('this message template'));
                // Drop drafts for this user for this template
                Draft::deleteForNamespace('tpl.'.$new->getCodeName()
                    .$new->getTplId(), $thisstaff->getId());
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this message template')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'update':
            if(!$template){
                $errors['err']=sprintf(__('%s: Unknown or invalid'), __('template set'));
            }elseif($template->update($_POST,$errors)){
                $msg=sprintf(__('Successfully updated %s.'),
                    mb_convert_case(__('this message template'), MB_CASE_TITLE));
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to update %s.'), __('this message template')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'add':
            if(($new=EmailTemplateGroup::add($_POST,$errors))){
                $template=$new;
                $msg=sprintf(__('Successfully added %s.'),
                    mb_convert_case(__('a template set'), MB_CASE_TITLE));
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']=sprintf('%s %s',
                    sprintf(__('Unable to add %s.'), __('this message template')),
                    __('Correct any errors below and try again.'));
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err']=sprintf(__('You must select at least %s to process.'),
                    __('one template set'));
            } else {
                $count=count($_POST['ids']);
                // Cast all IDs to integers to prevent SQL injection.
                $ids = array_map('intval', $_POST['ids']);
                $id_list = implode(',', $ids);
                switch(strtolower($_POST['a'])) {
                    case 'enable':
                        $sql='UPDATE '.EMAIL_TEMPLATE_GRP_TABLE.' SET isactive=1 '
                            .' WHERE tpl_id IN ('.$id_list.')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg = sprintf(__('Successfully enabled %s'),
                                    _N('selected template set', 'selected template sets', $count));
                            else
                                $warn = sprintf(__('%1$d of %2$d %3$s enabled'), $num, $count,
                                    _N('selected template set', 'selected template sets', $count));
                        } else {
                            $errors['err'] = sprintf(__('Unable to enable %s'),
                                _N('selected template set', 'selected template sets', $count));
                        }
                        break;
                    case 'disable':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->disable())
                                $i++;
                        }
                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully disabled %s'),
                                _N('selected template set', 'selected template sets', $count));
                        elseif($i)
                            $warn = sprintf(__('%1$d of %2$d %3$s disabled'), $i, $count,
                                _N('selected template set', 'selected template sets', $count))
                               .' '.__('(in-use and default template sets cannot be disabled)');
                        else
                            $errors['err'] = sprintf(__("Unable to disable %s"),
                                _N('selected template set', 'selected template sets', $count))
                               .' '.__('(in-use and default template sets cannot be disabled)');
                        break;
                    case 'delete':
                        $i=0;
                        foreach($_POST['ids'] as $k=>$v) {
                            if(($t=EmailTemplateGroup::lookup($v)) && !$t->isInUse() && $t->delete())
                                $i++;
                        }

                        if($i && $i==$count)
                            $msg = sprintf(__('Successfully deleted %s.'),
                                _N('selected template set', 'selected template sets', $count));
                        elseif($i>0)
                            $warn = sprintf(__('%1$d of %2$d %3$s deleted'), $i, $count,
                                _N('selected template set', 'selected template sets', $count));
                        elseif(!$errors['err'])
                            $errors['err'] = sprintf(__('Unable to delete %s.'),
                                _N('selected template set', 'selected template sets', $count));
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

$page='templates.php';
$tip_namespace = 'emails.template';
if($template && !strcasecmp($_REQUEST['a'],'manage')){
    $page='tpl.php';
}elseif($template && !strcasecmp($_REQUEST['a'] ?? '', 'implement')){
    $page='tpl.php';
}elseif($template || !strcasecmp($_REQUEST['a'] ?? '', 'add')){
    $page='template.php';
}

$nav->setTabActive('emails','templates.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require deskuss_load_page($page);
?>

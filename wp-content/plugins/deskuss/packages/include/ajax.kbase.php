<?php

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

if(!defined('INCLUDE_DIR')) die('!');


class KbaseAjaxAPI extends AjaxController {

    function cannedResp($id, $format='text') {
        global $thisstaff, $cfg;

        include_once(INCLUDE_DIR.'class.canned.php');

        if(!$id || !($canned=Canned::lookup($id)) || !$canned->isEnabled())
            Http::response(404, 'No such premade reply');

        if (!$cfg->isRichTextEnabled())
            $format .= '.plain';

        return $canned->getFormattedResponse($format);
    }

    function faq($id, $format='html') {
        //XXX: user ajax->getThisStaff() (nolint)
        global $thisstaff;
        include_once(INCLUDE_DIR.'class.faq.php');

        if(!($faq=FAQ::lookup($id)))
            return null;

        //TODO: $fag->getJSON() for json format. (nolint)
        $resp = sprintf(
                '<div style="width:650px;">
                 <strong>%s</strong><div class="thread-body">%s</div>
                 <div class="clear"></div>
                 <div class="faded">'.__('Last Updated %s').'</div>
                 <hr>
                 <a href="faq.php?id=%d">'.__('View').'</a> | <a href="faq.php?id=%d">'.__('Attachments (%d)').'</a>',
                $faq->getQuestion(),
                $faq->getAnswerWithImages(),
                Format::daydatetime($faq->getUpdateDate()),
                $faq->getId(),
                $faq->getId(),
                $faq->getNumAttachments());
        if($thisstaff
                && $thisstaff->hasPerm(FAQ::PERM_MANAGE)) {
            $resp.=sprintf(' | <a href="faq.php?id=%d&a=edit">'.__('Edit').'</a>',$faq->getId());

        }
        $resp.='</div>';

        return $resp;
    }

    function manageFaqAccess($id) {
        global $dsk, $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Agent login required');
        if (!$thisstaff->hasPerm(FAQ::PERM_MANAGE))
            Http::response(403, 'Access denied');
        if (!($faq = FAQ::lookup($id)))
            Http::response(404, 'No such faq article');

        $form = new FaqAccessMgmtForm($_POST ?: $faq->getHashtable());

        if ($_POST && $form->isValid()) {
            $clean = $form->getClean();
            $faq->ispublished = $clean['ispublished'];
            $faq->save();
            Http::response(201, 'Have a nice day');
        }

        $title = __("Manage FAQ Access");
        $verb = __('Update');
        $path = ltrim($dsk->get_path_info(), '/');

        include STAFFINC_DIR . 'templates/quick-add.tmpl.php';
    }
}
?>

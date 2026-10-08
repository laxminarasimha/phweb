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

require_once(INCLUDE_DIR.'class.i18n.php');

class HelpTipAjaxAPI extends AjaxController {
    function getTipsJson($namespace, $lang=false) {
        global $dsk, $thisstaff;

        $lang = Internationalization::getCurrentLanguage();

        $i18n = new Internationalization($lang);
        $tips = $i18n->getTemplate("help/tips/$namespace.yaml");

        if (!$tips || !($data = $tips->getData()))
            Http::response(404, 'Help content not available');

        // Translate links to the root path of this installation
        foreach ($data as $tip=>&$info) {
            if ($dsk)
                $info = $dsk->replaceTemplateVariables($info, array(
                    'config'=>$dsk->getConfig()));
            if (isset($info['links']))
                foreach ($info['links'] as &$l)
                    if ($l['href'][0] == '/')
                        $l['href'] = DESKUSS_ROOT_PATH.substr($l['href'],1);
        }

        return $this->json_encode($data);
    }

    function getTipsJsonForLang($lang, $namespace) {
        return $this->getTipsJson($namespace, $lang);
    }
}

?>

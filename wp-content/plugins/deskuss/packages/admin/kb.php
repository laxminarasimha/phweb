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
require_once(INCLUDE_DIR.'class.faq.php');

$category=null;
if(!empty($_REQUEST['cid']) && !($category=Category::lookup($_REQUEST['cid'])))
    $errors['err']=__('Unknown or invalid FAQ category');

$inc='faq-categories.php'; //KB landing page.
if($category && $_REQUEST['a']!='search') {
    $inc='faq-category.php';
}
$nav->setTabActive('kbase','kb.php');
$dsk->addExtraHeader('<meta name="tip-namespace" content="knowledgebase.faqs" />',
    "$('#content').data('tipNamespace', 'knowledgebase.faqs');");
require deskuss_load_page($inc);
?>

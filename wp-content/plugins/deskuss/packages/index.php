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

require('client.inc.php');

require_once INCLUDE_DIR . 'class.page.php';

$section = 'home';
require deskuss_get_header();
?>

<div id="landing_page" class="panel" style="display: flow-root;">
<div class="panel-body">
<?php
if ($cfg && $cfg->isKnowledgebaseEnabled()) { ?>
<div class="search-form" style="display:contents;">
    <form method="get" action="kb/faq.php" style="display:initial;">
    <input type="hidden" name="a" value="search"/>
    <input type="text" name="q" class="search form-control" style="max-width:300px;display:inline-block;vertical-align: middle;" placeholder="<?php echo __('Search our knowledge base'); ?>"/>
    <button type="submit" class="btn btn-success"><?php echo __('Search'); ?></button>
    </form>
</div>
<?php
}

$BUTTONS = isset($BUTTONS) ? $BUTTONS : true;
?>
    
<?php if ($BUTTONS) { ?>
        <div class="pull-right flush-right">

<?php
    if ($cfg->getClientRegistrationMode() != 'disabled'
        || !$cfg->isClientLoginRequired()) { ?>
            <a href="open.php" class="btn btn-info btn-outline"><i class="fa fa-plus-circle fa-lg"></i>&nbsp;&nbsp;<?php
                echo __('Open a New Ticket');?></a>

<?php } ?>

        &nbsp; <a href="view.php" class="btn btn-success btn-outline"><i class="fa fa-th-list fa-lg"></i>&nbsp;&nbsp;<?php
                echo __('Check Ticket Status');?></a>

        </div>
<?php } ?>
</div>	
<?php require deskuss_load_view('templates/sidebar.tmpl.php'); ?>
<div class="panel-body col-sm-9">
    <div class="thread-body">
<?php

    if($cfg && ($page = $cfg->getLandingPage()))
        echo $page->getBodyWithImages();
    else
        echo  '<h1>'.__('Welcome to the Support Center').'</h1>';
    ?>
    </div>
</div>

<div class="panel-body">
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<br/><br/>
<?php
$cats = Category::getFeatured();
if ($cats->all()) { ?>
<h4 class="text-primary"><?php echo __('Featured Knowledge Base Articles'); ?></h4>
<br/>
<?php
}

    foreach ($cats as $C) { ?>
    <div class="col-sm-6" style="display: -webkit-box;margin-bottom: 20px;">
        <i class="fa fa-folder-open fa-2x text-info"></i>
        <div style="margin-left:10px;">
		<span style="font-weight: 500;font-size: 110%;color:#000000;">
            <?php echo $C->getName(); ?>
        </span>
<?php foreach ($C->getTopArticles() as $F) { ?>
        
            <div class="text-primary"><a href="<?php echo DESKUSS_ROOT_PATH;
                ?>kb/faq.php?id=<?php echo $F->getId(); ?>"><?php
                echo $F->getQuestion(); ?></a></div>
            <div style="height:3em;"><?php echo $F->getTeaser(); ?></div>
       
<?php } ?>
		</div>
    </div>
<?php
    }
}
?>
</div>
</div>

<?php require deskuss_get_footer(); ?>

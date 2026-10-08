
 <div class="col-sm-3 pull-right panel-body">
        <div class="panel"><?php
    $faqs = FAQ::getFeatured()->select_related('category')->limit(5);
    if ($faqs->all()) { ?>
	<div class="panel-heading"><?php echo __('Featured Questions'); ?></div>
            <section style="padding:3px 20px;">
<?php   foreach ($faqs as $F) { ?>
            <div><a href="<?php echo DESKUSS_ROOT_PATH; ?>kb/faq.php?id=<?php
                echo urlencode($F->getId());
                ?>"><?php echo $F->getLocalQuestion(); ?></a></div>
<?php   } ?>
            </section>
<?php
    }
    $resources = Page::getActivePages()->filter(array('type'=>'other'));
    if ($resources->all()) { ?>
	<div class="panel-heading"><?php echo __('Other Resources'); ?></div>
            <section style="padding:3px 20px;">
<?php   foreach ($resources as $page) { ?>
            <div><a href="<?php echo DESKUSS_ROOT_PATH; ?>pages/<?php echo $page->getNameAsSlug();
            ?>"><?php echo $page->getLocalName(); ?></a></div>
<?php   } ?>
            </section>
<?php
    }
        ?></div>
    </div>


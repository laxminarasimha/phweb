<div class="panel-heading">
    <div class="panel-title table-caption"><?php echo __('Frequently Asked Questions');?></div>
 </div>
<div class="panel-body">
<div class="col-sm-8">
<div class="panel">
	<div class="panel-heading">
		<div class="panel-title table-caption"><strong><?php echo __('Search Results'); ?></strong>
		</div>
	</div>
	<div class="panel-body">
<?php
    if ($faqs->exists(true)) {
        echo '<div id="faq">'.sprintf(__('%d FAQs matched your search criteria.'),
            $faqs->count())
            .'<ol>';
        foreach ($faqs as $F) {
            echo sprintf(
                '<li><a href="faq.php?id=%d" class="previewfaq"><i class="fa fa-file-text-o" style="font-size:13px;color:#575757"></i> %s</a></li>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription());
        }
        echo '</ol></div>';
    } else {
        echo '<strong class="faded">'.__('The search did not match any FAQs.').'</strong>';
    }
?>
	</div>
</div>
</div>

<div class="col-sm-4">
    <div class="sidebar">
    <div class="searchbar">
        <form method="get" action="faq.php">
        <input type="hidden" name="a" value="search"/>
        <input type="text" name="q" class="search form-control" placeholder="<?php
            echo __('Search our knowledge base'); ?>"/>
        <input type="submit" style="display:none" value="search"/>
        </form>
    </div>
	<br/>
    <div class="panel">
	<div class="panel-heading"><?php echo __('Categories'); ?></div>
        <section style="padding:10px 20px;">
            
<?php
foreach (Category::objects()
    ->annotate(array('faqs_count'=>SqlAggregate::count('faqs')))
    ->filter(array('faqs_count__gt'=>0))
    as $C) { ?>
        <div><a href="?cid=<?php echo urlencode($C->getId()); ?>"
            ><?php echo $C->getLocalName(); ?></a></div>
<?php } ?>
        </section>
    </div>
    </div>
</div>
</div>
